<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\EmailMessage;
use App\Models\InboundReply;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\ManagerHandover;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboundReplyService
{
    public function __construct(
        protected SequenceEngineService $sequenceEngine,
        protected AiContentService $aiService
    ) {}

    /**
     * Ingest an inbound email reply from webhook, IMAP, or email simulation
     */
    public function processReply(string $fromEmail, string $subject, string $body, ?string $messageId = null, ?int $campaignId = null): InboundReply
    {
        $cleanEmail = strtolower(trim($fromEmail));

        return DB::transaction(function () use ($cleanEmail, $subject, $body, $messageId, $campaignId) {
            // 1. Match to Lead
            $lead = Lead::where('email', $cleanEmail)->first();

            // 2. Pause contact's automated marketing sequences immediately
            if ($lead) {
                $this->sequenceEngine->stopSequencesForLead($lead->id, 'reply');
            }

            // 3. Record Email Message
            $emailMessage = EmailMessage::create([
                'lead_id' => $lead?->id,
                'campaign_id' => $campaignId,
                'message_id' => $messageId ?: (string) \Illuminate\Support\Str::uuid(),
                'direction' => 'inbound',
                'subject' => $subject,
                'body_text' => $body,
                'from_email' => $cleanEmail,
                'to_email' => config('mail.from.address'),
            ]);

            // 4. Classify reply using AI Content Service (or rule-based fallback)
            $classification = $this->aiService->classifyReplyIntent($body, $subject);

            // 5. Create InboundReply record
            $reply = InboundReply::create([
                'lead_id' => $lead?->id,
                'email_message_id' => $emailMessage->id,
                'campaign_id' => $campaignId,
                'from_email' => $cleanEmail,
                'subject' => $subject,
                'body' => $body,
                'ai_classification' => $classification['intent'], // interested, not_interested, information_request, meeting_request, out_of_office, uncertain
                'ai_confidence' => $classification['confidence'],
                'ai_summary' => $classification['summary'],
                'is_reviewed' => false,
                'status' => 'unassigned',
                'received_at' => now(),
            ]);

            // 6. If Lead exists, update lead engagement and status
            if ($lead) {
                $lead->increment('email_engagement_score', 25);
                $lead->recordActivity('reply_received', "Received inbound reply: '{$subject}' (Intent: {$classification['intent']})", null, [
                    'reply_id' => $reply->id,
                    'intent' => $classification['intent'],
                    'confidence' => $classification['confidence'],
                ]);

                // Auto-advance status if positive intent detected
                if (in_array($classification['intent'], ['interested', 'meeting_request'])) {
                    if ($lead->status === Lead::STATUS_NEW || $lead->status === Lead::STATUS_CONTACTED) {
                        $lead->update(['status' => Lead::STATUS_INTERESTED]);
                        $lead->recordActivity('status_change', "Lead status updated to Interested based on reply intent");
                    }
                }

                // 7. Resolve assigned manager
                $manager = $lead->assignedUser ?: User::whereIn('role', ['admin', 'sales_manager'])->first();
                $managerId = $manager?->id;

                // 8. Create Manager Handover
                $handover = ManagerHandover::create([
                    'lead_id' => $lead->id,
                    'inbound_reply_id' => $reply->id,
                    'assigned_manager_id' => $managerId,
                    'handover_reason' => in_array($classification['intent'], ['interested', 'meeting_request']) ? 'positive_reply' : 'inbound_message',
                    'urgency' => in_array($classification['intent'], ['interested', 'meeting_request']) ? 'urgent' : 'normal',
                    'status' => 'pending',
                    'handoff_notes' => "AI Classification: {$classification['intent']} ({$classification['confidence']}%). Summary: {$classification['summary']}",
                ]);

                // 9. Create follow-up task
                LeadTask::create([
                    'lead_id' => $lead->id,
                    'assigned_user_id' => $managerId,
                    'title' => "Respond to reply from {$lead->name} ({$classification['intent']})",
                    'description' => $classification['summary'],
                    'due_date' => now()->addHours(24),
                    'priority' => in_array($classification['intent'], ['interested', 'meeting_request']) ? 'urgent' : 'high',
                    'status' => 'pending',
                ]);

                // 10. Send in-app notification to Manager
                if ($managerId) {
                    AppNotification::notify(
                        userId: $managerId,
                        title: "New Lead Reply: {$lead->name}",
                        message: "{$lead->name} replied to '{$subject}'. AI classification: {$classification['intent']}",
                        type: 'handover',
                        actionUrl: route('inbox.show', $reply->id)
                    );
                }
            }

            AuditLog::log('inbound_reply_processed', InboundReply::class, $reply->id, null, [
                'from' => $cleanEmail,
                'intent' => $classification['intent'],
            ]);

            return $reply;
        });
    }
}
