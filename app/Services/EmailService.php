<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Models\Lead;
use App\Models\SuppressionList;
use App\Models\Unsubscribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailService
{
    /**
     * Send an email to a campaign recipient with tokenized unsubscribe URL and variable replacement
     */
    public function sendCampaignEmail(CampaignRecipient $recipient): bool
    {
        $campaign = $recipient->campaign;
        $lead = $recipient->lead;

        // 1. Mandatory Suppression check
        if (SuppressionList::isSuppressed($recipient->email)) {
            $recipient->update([
                'status' => 'suppressed',
                'error_message' => 'Suppressed from marketing communications.',
            ]);
            return false;
        }

        // 2. Lead eligibility check
        if (!$lead || !$lead->isEligibleForMarketing()) {
            $recipient->update([
                'status' => 'suppressed',
                'error_message' => 'Lead is not eligible for marketing outreach.',
            ]);
            return false;
        }

        // 3. Generate tokenized unsubscribe link
        $token = hash('sha256', $recipient->email . config('app.key') . $campaign->id);
        $unsubscribeUrl = route('unsubscribe.show', ['token' => $token, 'email' => urlencode($recipient->email)]);

        // 4. Render template variables
        $template = $campaign->template;
        $variables = [
            'first_name' => explode(' ', $lead->name)[0],
            'last_name' => explode(' ', $lead->name)[1] ?? '',
            'full_name' => $lead->name,
            'company_name' => $lead->company ? $lead->company->name : 'Your Team',
            'industry' => $lead->industry ?: 'Technology',
            'job_title' => $lead->job_title ?: 'Leader',
            'service_name' => 'Custom Software, Laravel Web Applications, and Digital Automation',
            'website' => $lead->website ?: config('app.url'),
            'unsubscribe_url' => $unsubscribeUrl,
        ];

        $rendered = $template ? $template->render($variables, $unsubscribeUrl) : [
            'subject' => $campaign->subject,
            'body_html' => '<p>' . nl2br(e($campaign->subject)) . '</p>',
            'body_plain' => $campaign->subject,
        ];

        // 5. Send Email via Mailer (log / smtp / queue)
        try {
            $defaultSender = config('mail.from.address');
            $fromEmail = ($campaign->from_email && $campaign->from_email !== 'outreach@nexteck.com') 
                ? $campaign->from_email 
                : ($defaultSender ?: 'outreach@nexteck.com');
            $fromName = $campaign->from_name ?: config('mail.from.name');

            Mail::html($rendered['body_html'], function ($message) use ($recipient, $rendered, $fromEmail, $fromName, $campaign, $unsubscribeUrl) {
                $message->to($recipient->email, $recipient->lead->name)
                        ->from($fromEmail, $fromName)
                        ->subject($rendered['subject']);

                if ($campaign->reply_to) {
                    $message->replyTo($campaign->reply_to);
                }

                // Standard RFC 8058 One-Click List-Unsubscribe Header
                $message->getHeaders()->addTextHeader('List-Unsubscribe', "<{$unsubscribeUrl}>");
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                $message->getHeaders()->addTextHeader('X-Campaign-ID', (string) $campaign->id);
                $message->getHeaders()->addTextHeader('X-Recipient-ID', (string) $recipient->id);
            });

            // 6. Record database records
            return DB::transaction(function () use ($recipient, $lead, $campaign, $rendered, $fromEmail) {
                $recipient->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'delivered_at' => now(), // marked delivered by default; updated on webhook
                ]);

                $emailMessage = EmailMessage::create([
                    'lead_id' => $lead->id,
                    'campaign_id' => $campaign->id,
                    'campaign_recipient_id' => $recipient->id,
                    'message_id' => (string) Str::uuid(),
                    'direction' => 'outbound',
                    'subject' => $rendered['subject'],
                    'body_html' => $rendered['body_html'],
                    'body_text' => $rendered['body_plain'],
                    'from_email' => $fromEmail,
                    'to_email' => $recipient->email,
                    'headers' => [
                        'X-Campaign-ID' => $campaign->id,
                    ],
                ]);

                EmailEvent::create([
                    'email_message_id' => $emailMessage->id,
                    'event_type' => 'sent',
                    'payload' => ['to' => $recipient->email],
                    'occurred_at' => now(),
                ]);

                $campaign->increment('sent_count');
                $campaign->increment('delivered_count');

                $lead->update(['last_contacted_at' => now()]);
                $lead->recordActivity('email_sent', "Sent campaign email: '{$rendered['subject']}'", null, [
                    'campaign_id' => $campaign->id,
                    'message_id' => $emailMessage->id,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Failed to send campaign email to {$recipient->email}: " . $e->getMessage());
            $recipient->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process an Unsubscribe request with immediate suppression & audit
     */
    public function processUnsubscribe(string $email, ?string $reason = null, ?int $campaignId = null): Unsubscribe
    {
        $email = strtolower(trim($email));

        return DB::transaction(function () use ($email, $reason, $campaignId) {
            $token = hash('sha256', $email . config('app.key'));

            // 1. Permanent suppression
            SuppressionList::suppress(
                email: $email,
                reason: 'unsubscribed',
                source: 'email_unsubscribe',
                token: $token,
                notes: $reason ?: 'User opted out via unsubscribe link.'
            );

            // 2. Find matching lead
            $lead = Lead::where('email', $email)->first();

            // 3. Create Unsubscribe audit record
            $unsub = Unsubscribe::create([
                'lead_id' => $lead?->id,
                'email' => $email,
                'campaign_id' => $campaignId,
                'token' => $token,
                'reason' => $reason ?: 'Opt-out requested',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            // 4. Update campaign stats if linked
            if ($campaignId) {
                EmailCampaign::where('id', $campaignId)->increment('unsubscribe_count');
            }

            // 5. Update lead status & stop active sequence enrollments
            if ($lead) {
                $lead->recordActivity('unsubscribed', 'Contact unsubscribed from all marketing communications');

                // Stop all active sequence enrollments
                foreach ($lead->sequenceEnrollments()->where('status', 'active')->get() as $enrollment) {
                    $enrollment->stop('unsubscribed');
                }
            }

            AuditLog::log('contact_unsubscribed', SuppressionList::class, null, null, ['email' => $email, 'reason' => $reason]);

            return $unsub;
        });
    }

    /**
     * Record a hard bounce or complaint and suppress permanently
     */
    public function handleBounceOrComplaint(string $email, string $type, ?string $details = null): void
    {
        $email = strtolower(trim($email));

        DB::transaction(function () use ($email, $type, $details) {
            SuppressionList::suppress(
                email: $email,
                reason: $type === 'bounce' ? 'hard_bounce' : 'complaint',
                source: 'bounce_webhook',
                notes: $details
            );

            $lead = Lead::where('email', $email)->first();
            if ($lead) {
                $lead->recordActivity($type === 'bounce' ? 'email_bounced' : 'spam_complaint', "Contact encountered {$type}: {$details}");

                foreach ($lead->sequenceEnrollments()->where('status', 'active')->get() as $enrollment) {
                    $enrollment->stop($type === 'bounce' ? 'bounced_stopped' : 'complaint_stopped');
                }
            }
        });
    }
}
