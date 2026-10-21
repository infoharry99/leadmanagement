<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\SuppressionList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadService
{
    /**
     * Create a new Lead with compliance check & duplicate prevention
     */
    public function createLead(array $data): Lead
    {
        $email = strtolower(trim($data['email']));

        // 1. Suppression check
        if (SuppressionList::isSuppressed($email)) {
            throw ValidationException::withMessages([
                'email' => 'This email address is on the permanent suppression list and cannot be contacted.',
            ]);
        }

        // 2. Duplicate active email check
        $existing = Lead::where('email', $email)->where('is_archived', false)->first();
        if ($existing) {
            throw ValidationException::withMessages([
                'email' => 'A lead with this email address already exists (Lead ID #' . $existing->id . ' - ' . $existing->name . ').',
            ]);
        }

        return DB::transaction(function () use ($data, $email) {
            // Match or create company if domain/name provided
            $companyId = $data['company_id'] ?? null;
            if (!$companyId && !empty($data['company_name'])) {
                $domain = !empty($data['website']) ? parse_url($data['website'], PHP_URL_HOST) : null;
                $company = Company::firstOrCreate(
                    ['name' => trim($data['company_name'])],
                    [
                        'domain' => $domain,
                        'industry' => $data['industry'] ?? null,
                        'website' => $data['website'] ?? null,
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                    ]
                );
                $companyId = $company->id;
            }

            $lead = Lead::create([
                'company_id' => $companyId,
                'assigned_user_id' => $data['assigned_user_id'] ?? auth()->id(),
                'name' => trim($data['name']),
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'industry' => $data['industry'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'lead_source_id' => $data['lead_source_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'status' => $data['status'] ?? Lead::STATUS_NEW,
                'consent_basis' => $data['consent_basis'] ?? 'legitimate_interest',
                'consent_source' => $data['consent_source'] ?? 'Direct Entry',
                'consent_at' => !empty($data['consent_at']) ? $data['consent_at'] : now(),
                'email_engagement_score' => $data['email_engagement_score'] ?? 0,
                'next_followup_at' => $data['next_followup_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'custom_fields' => $data['custom_fields'] ?? null,
                'is_archived' => false,
            ]);

            // Sync tags if supplied
            if (!empty($data['tag_ids'])) {
                $lead->tags()->sync($data['tag_ids']);
            }

            // Record initial timeline entry
            $lead->recordActivity('status_change', 'Lead created with status: ' . ucfirst($lead->status));

            AuditLog::log('lead_created', Lead::class, $lead->id, null, $lead->toArray());

            return $lead;
        });
    }

    /**
     * Update an existing Lead
     */
    public function updateLead(Lead $lead, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $data) {
            $oldValues = $lead->toArray();
            $oldStatus = $lead->status;
            $oldAssigned = $lead->assigned_user_id;

            // Email check if changed
            if (isset($data['email']) && strtolower(trim($data['email'])) !== strtolower($lead->email)) {
                $newEmail = strtolower(trim($data['email']));
                if (SuppressionList::isSuppressed($newEmail)) {
                    throw ValidationException::withMessages([
                        'email' => 'This email address is on the permanent suppression list.',
                    ]);
                }
                $existing = Lead::where('email', $newEmail)->where('id', '!=', $lead->id)->where('is_archived', false)->first();
                if ($existing) {
                    throw ValidationException::withMessages([
                        'email' => 'Another lead with this email address already exists.',
                    ]);
                }
                $lead->email = $newEmail;
            }

            // Update company if company name provided
            if (!empty($data['company_name'])) {
                $company = Company::firstOrCreate(['name' => trim($data['company_name'])]);
                $lead->company_id = $company->id;
            } elseif (isset($data['company_id'])) {
                $lead->company_id = $data['company_id'];
            }

            $lead->name = $data['name'] ?? $lead->name;
            $lead->phone = $data['phone'] ?? $lead->phone;
            $lead->website = $data['website'] ?? $lead->website;
            $lead->industry = $data['industry'] ?? $lead->industry;
            $lead->job_title = $data['job_title'] ?? $lead->job_title;
            $lead->country = $data['country'] ?? $lead->country;
            $lead->city = $data['city'] ?? $lead->city;
            $lead->lead_source_id = $data['lead_source_id'] ?? $lead->lead_source_id;
            $lead->status = $data['status'] ?? $lead->status;
            $lead->assigned_user_id = $data['assigned_user_id'] ?? $lead->assigned_user_id;
            $lead->consent_basis = $data['consent_basis'] ?? $lead->consent_basis;
            $lead->consent_source = $data['consent_source'] ?? $lead->consent_source;
            $lead->next_followup_at = $data['next_followup_at'] ?? $lead->next_followup_at;
            $lead->notes = $data['notes'] ?? $lead->notes;

            if (isset($data['custom_fields'])) {
                $lead->custom_fields = $data['custom_fields'];
            }

            $lead->save();

            if (isset($data['tag_ids'])) {
                $lead->tags()->sync($data['tag_ids']);
            }

            // Status transition activity
            if ($oldStatus !== $lead->status) {
                $lead->recordActivity('status_change', "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($lead->status));
            }

            // Reassignment activity
            if ($oldAssigned !== $lead->assigned_user_id) {
                $newAssignee = $lead->assignedUser?->name ?? 'Unassigned';
                $lead->recordActivity('manager_assigned', "Lead assigned to {$newAssignee}");
            }

            AuditLog::log('lead_updated', Lead::class, $lead->id, $oldValues, $lead->toArray());

            return $lead;
        });
    }

    /**
     * Bulk assign leads to a user
     */
    public function bulkAssign(array $leadIds, int $userId): int
    {
        $count = 0;
        foreach ($leadIds as $id) {
            $lead = Lead::find($id);
            if ($lead) {
                $oldAssigned = $lead->assigned_user_id;
                $lead->assigned_user_id = $userId;
                $lead->save();
                $lead->recordActivity('manager_assigned', "Lead reassigned via bulk action");
                $count++;
            }
        }
        return $count;
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $leadIds, string $status): int
    {
        $count = 0;
        foreach ($leadIds as $id) {
            $lead = Lead::find($id);
            if ($lead && $lead->status !== $status) {
                $old = $lead->status;
                $lead->status = $status;
                $lead->save();
                $lead->recordActivity('status_change', "Status bulk updated from " . ucfirst($old) . " to " . ucfirst($status));
                $count++;
            }
        }
        return $count;
    }

    /**
     * Bulk add tags
     */
    public function bulkAddTags(array $leadIds, array $tagIds): int
    {
        $count = 0;
        foreach ($leadIds as $id) {
            $lead = Lead::find($id);
            if ($lead) {
                $lead->tags()->syncWithoutDetaching($tagIds);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Import Leads from CSV file with column mapping and duplicate check
     */
    public function importFromCsv(string $filePath, array $columnMap, bool $updateDuplicates = false, ?int $assignedUserId = null): array
    {
        $results = [
            'total' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped_suppressed' => 0,
            'skipped_duplicate' => 0,
            'errors' => [],
        ];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $results['errors'][] = 'CSV file not found or not readable.';
            return $results;
        }

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            $results['errors'][] = 'Empty CSV file.';
            fclose($handle);
            return $results;
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $results['total']++;

            $rowData = [];
            foreach ($columnMap as $leadField => $csvHeaderIndex) {
                if (isset($row[$csvHeaderIndex])) {
                    $rowData[$leadField] = trim($row[$csvHeaderIndex]);
                }
            }

            if (empty($rowData['email']) || empty($rowData['name'])) {
                $results['errors'][] = "Row {$rowNumber}: Name and Email are required.";
                continue;
            }

            $email = strtolower($rowData['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $results['errors'][] = "Row {$rowNumber}: Invalid email format ({$email}).";
                continue;
            }

            // Check suppression
            if (SuppressionList::isSuppressed($email)) {
                $results['skipped_suppressed']++;
                continue;
            }

            // Check existing
            $existing = Lead::where('email', $email)->first();
            if ($existing) {
                if ($updateDuplicates) {
                    $this->updateLead($existing, array_merge($rowData, [
                        'assigned_user_id' => $assignedUserId ?? $existing->assigned_user_id,
                    ]));
                    $results['updated']++;
                } else {
                    $results['skipped_duplicate']++;
                }
                continue;
            }

            // Create new
            try {
                $this->createLead(array_merge($rowData, [
                    'assigned_user_id' => $assignedUserId ?? auth()->id(),
                    'consent_basis' => $rowData['consent_basis'] ?? 'legitimate_interest',
                    'consent_source' => 'CSV Bulk Import',
                    'consent_at' => now(),
                ]));
                $results['imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Row {$rowNumber} ({$email}): " . $e->getMessage();
            }
        }

        fclose($handle);
        return $results;
    }

    /**
     * Build filtered query for Leads
     */
    public function getFilteredQuery(array $filters): Builder
    {
        $query = Lead::with(['company', 'assignedUser', 'source', 'tags']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source_id'])) {
            $query->where('lead_source_id', $filters['source_id']);
        }

        if (!empty($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        if (!empty($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', $filters['assigned_user_id']);
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', function ($tq) use ($filters) {
                $tq->where('lead_tags.id', $filters['tag_id']);
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['archived']) && $filters['archived'] === '1') {
            $query->archived();
        } else {
            $query->active();
        }

        return $query->latest();
    }
}
