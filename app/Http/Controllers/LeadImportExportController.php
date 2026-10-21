<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadImportExportController extends Controller
{
    public function __construct(protected LeadService $leadService)
    {
    }

    public function showImport()
    {
        $users = User::all();
        return view('leads.import', compact('users'));
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'assigned_user_id' => 'nullable|exists:users,id',
            'update_duplicates' => 'nullable|boolean',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // Detect CSV header to build column mapping
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        fclose($handle);

        if (!$headers) {
            return back()->with('error', 'CSV file appears to be empty or unreadable.');
        }

        // Auto-match headers to lead fields
        $columnMap = [];
        $headerNormalized = array_map(fn($h) => strtolower(trim(str_replace([' ', '_', '-'], '', $h))), $headers);

        $possibleFields = [
            'name' => ['name', 'fullname', 'contactname', 'leadname'],
            'email' => ['email', 'emailaddress', 'mail'],
            'phone' => ['phone', 'phonenumber', 'mobile', 'tel'],
            'company_name' => ['company', 'companyname', 'organization'],
            'website' => ['website', 'url', 'web', 'domain'],
            'industry' => ['industry', 'sector', 'vertical'],
            'job_title' => ['jobtitle', 'title', 'role', 'position'],
            'country' => ['country', 'nation'],
            'city' => ['city', 'town'],
        ];

        foreach ($possibleFields as $field => $synonyms) {
            foreach ($headerNormalized as $index => $colName) {
                if (in_array($colName, $synonyms, true)) {
                    $columnMap[$field] = $index;
                    break;
                }
            }
        }

        if (!isset($columnMap['email'])) {
            return back()->with('error', 'Could not locate an Email column in the uploaded CSV header. Please ensure an "Email" column exists.');
        }

        // Run Import Service
        $updateDuplicates = $request->boolean('update_duplicates');
        $assignedUserId = $request->input('assigned_user_id');

        $result = $this->leadService->importFromCsv($path, $columnMap, $updateDuplicates, $assignedUserId);

        $msg = "Import completed! Total: {$result['total']} | Imported: {$result['imported']} | Updated: {$result['updated']} | Suppressed contacts skipped: {$result['skipped_suppressed']} | Duplicates skipped: {$result['skipped_duplicate']}";

        return redirect()->route('leads.index')->with('success', $msg)->with('import_errors', $result['errors']);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only([
            'search', 'status', 'source_id', 'industry', 'assigned_user_id', 'tag_id', 'date_from', 'date_to', 'archived'
        ]);

        $query = $this->leadService->getFilteredQuery($filters);

        $filename = 'nexteck_leads_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Company',
                'Job Title',
                'Industry',
                'Country',
                'City',
                'Status',
                'Lead Source',
                'Assigned User',
                'Consent Basis',
                'Engagement Score',
                'Last Contacted',
                'Created At',
            ]);

            $query->chunk(200, function ($leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->email,
                        $lead->phone,
                        $lead->company ? $lead->company->name : '',
                        $lead->job_title,
                        $lead->industry,
                        $lead->country,
                        $lead->city,
                        $lead->status,
                        $lead->source ? $lead->source->name : '',
                        $lead->assignedUser ? $lead->assignedUser->name : '',
                        $lead->consent_basis,
                        $lead->email_engagement_score,
                        $lead->last_contacted_at ? $lead->last_contacted_at->toDateTimeString() : '',
                        $lead->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
