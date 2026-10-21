<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\FollowupSequence;
use App\Models\Lead;
use App\Models\SequenceEnrollment;
use App\Models\SequenceStep;
use App\Models\SuppressionList;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SequenceEngineService
{
    /**
     * Enroll a Lead into a follow-up sequence
     */
    public function enrollLead(Lead $lead, FollowupSequence $sequence): SequenceEnrollment
    {
        if (!$lead->isEligibleForMarketing()) {
            throw new \InvalidArgumentException("Lead is not eligible for marketing sequences.");
        }

        $firstStep = $sequence->steps()->where('step_order', 1)->first();
        if (!$firstStep) {
            throw new \RuntimeException("Sequence has no configured steps.");
        }

        // Calculate next run date
        $nextRun = $this->calculateNextValidRunTime($sequence, $firstStep->delay_days);

        $enrollment = SequenceEnrollment::updateOrCreate(
            [
                'followup_sequence_id' => $sequence->id,
                'lead_id' => $lead->id,
            ],
            [
                'current_step_id' => $firstStep->id,
                'status' => 'active',
                'next_run_at' => $nextRun,
                'stop_reason' => null,
            ]
        );

        $lead->recordActivity('sequence_enrolled', "Enrolled into follow-up sequence: '{$sequence->name}'");

        return $enrollment;
    }

    /**
     * Process all due sequence enrollments
     */
    public function processDueEnrollments(): int
    {
        $now = now();
        $processed = 0;

        $dueEnrollments = SequenceEnrollment::with(['sequence', 'lead.company', 'currentStep'])
            ->where('status', 'active')
            ->where('next_run_at', '<=', $now)
            ->get();

        foreach ($dueEnrollments as $enrollment) {
            $sequence = $enrollment->sequence;
            $lead = $enrollment->lead;
            $step = $enrollment->currentStep;

            if (!$sequence || $sequence->status !== 'active') {
                continue;
            }

            // Stop condition check
            if ($this->shouldStopEnrollment($lead)) {
                $enrollment->stop('ineligible_or_disqualified');
                continue;
            }

            // Check if current time is within sending window
            if (!$this->isWithinSendingWindow($sequence)) {
                // Postpone to start of next sending window
                $enrollment->update(['next_run_at' => $this->getNextWindowStart($sequence)]);
                continue;
            }

            // Check weekend exclusion
            if ($sequence->exclude_weekends && $now->isWeekend()) {
                $enrollment->update(['next_run_at' => now()->next(Carbon::MONDAY)->setTimeFromTimeString($sequence->send_window_start)]);
                continue;
            }

            // Execute the current step
            $this->executeStep($enrollment, $step);
            $processed++;
        }

        return $processed;
    }

    /**
     * Execute a specific sequence step for an enrolled lead
     */
    protected function executeStep(SequenceEnrollment $enrollment, SequenceStep $step): void
    {
        $lead = $enrollment->lead;
        $sequence = $enrollment->sequence;

        // Render template
        $token = hash('sha256', $lead->email . config('app.key') . 'seq_' . $sequence->id);
        $unsubscribeUrl = route('unsubscribe.show', ['token' => $token, 'email' => urlencode($lead->email)]);

        $variables = [
            'first_name' => explode(' ', $lead->name)[0],
            'full_name' => $lead->name,
            'company_name' => $lead->company ? $lead->company->name : 'Your Company',
            'industry' => $lead->industry ?: 'Technology',
            'unsubscribe_url' => $unsubscribeUrl,
        ];

        $subject = str_replace(
            ['{{first_name}}', '{{company_name}}', '{{industry}}'],
            [$variables['first_name'], $variables['company_name'], $variables['industry']],
            $step->subject
        );

        $body = str_replace(
            ['{{first_name}}', '{{company_name}}', '{{industry}}', '{{unsubscribe_url}}'],
            [$variables['first_name'], $variables['company_name'], $variables['industry'], $unsubscribeUrl],
            $step->body_template
        );

        try {
            Mail::html("<p>" . nl2br(e($body)) . "</p><p style='font-size:11px;color:#9ca3af;'><a href='{$unsubscribeUrl}'>Unsubscribe</a></p>", function ($message) use ($lead, $subject, $unsubscribeUrl) {
                $message->to($lead->email, $lead->name)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject($subject);

                $message->getHeaders()->addTextHeader('List-Unsubscribe', "<{$unsubscribeUrl}>");
            });

            $lead->recordActivity('sequence_step_sent', "Sent sequence step #{$step->step_order} ({$step->phase}): '{$subject}'");
            $lead->update(['last_contacted_at' => now()]);

            // Find next step in sequence
            $nextStep = $sequence->steps()
                ->where('step_order', '>', $step->step_order)
                ->orderBy('step_order')
                ->first();

            if ($nextStep) {
                $nextRun = $this->calculateNextValidRunTime($sequence, $nextStep->delay_days);
                $enrollment->update([
                    'current_step_id' => $nextStep->id,
                    'last_sent_at' => now(),
                    'next_run_at' => $nextRun,
                ]);
            } else {
                // Sequence completed
                $enrollment->update([
                    'status' => 'completed',
                    'last_sent_at' => now(),
                    'next_run_at' => null,
                ]);
                $lead->recordActivity('sequence_completed', "Successfully completed all stages of sequence: '{$sequence->name}'");
            }
        } catch (\Exception $e) {
            Log::error("Sequence execution failed for Lead #{$lead->id}: " . $e->getMessage());
        }
    }

    /**
     * Check whether automated marketing sequence should immediately stop
     */
    public function shouldStopEnrollment(Lead $lead): bool
    {
        if (SuppressionList::isSuppressed($lead->email)) {
            return true;
        }

        if ($lead->is_archived) {
            return true;
        }

        if (in_array($lead->status, [Lead::STATUS_INTERESTED, Lead::STATUS_QUALIFIED, Lead::STATUS_CONVERTED, Lead::STATUS_NOT_INTERESTED, Lead::STATUS_LOST])) {
            return true;
        }

        // Stop if inbound reply received
        if ($lead->inboundReplies()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Stop all active sequence enrollments for a given lead
     */
    public function stopSequencesForLead(int $leadId, string $reason): void
    {
        $enrollments = SequenceEnrollment::where('lead_id', $leadId)->where('status', 'active')->get();
        foreach ($enrollments as $enrollment) {
            $enrollment->stop($reason);
        }
    }

    protected function isWithinSendingWindow(FollowupSequence $sequence): bool
    {
        $now = now();
        $startTime = $sequence->send_window_start ?: '09:00:00';
        $endTime = $sequence->send_window_end ?: '18:00:00';
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $currentTime = Carbon::parse($now->format('H:i:s'));
        return $currentTime->between($start, $end);
    }

    protected function getNextWindowStart(FollowupSequence $sequence): Carbon
    {
        $now = now();
        $startTime = $sequence->send_window_start ?: '09:00:00';
        $endTime = $sequence->send_window_end ?: '18:00:00';
        if ($now->greaterThanOrEqualTo(Carbon::parse($endTime))) {
            return $now->addDay()->setTimeFromTimeString($startTime);
        }
        return $now->setTimeFromTimeString($startTime);
    }

    protected function calculateNextValidRunTime(FollowupSequence $sequence, int $delayDays): Carbon
    {
        $runAt = now()->addDays($delayDays);

        if ($sequence->exclude_weekends) {
            while ($runAt->isWeekend()) {
                $runAt->addDay();
            }
        }

        $startTime = $sequence->send_window_start ?: '09:00:00';
        return $runAt->setTimeFromTimeString($startTime);
    }
}
