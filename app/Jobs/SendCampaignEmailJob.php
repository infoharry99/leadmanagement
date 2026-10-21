<?php

namespace App\Jobs;

use App\Models\CampaignRecipient;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // 1 minute backoff between retries

    public function __construct(public CampaignRecipient $recipient)
    {
    }

    public function handle(EmailService $emailService): void
    {
        // Check if already sent (Idempotency)
        if ($this->recipient->status === 'sent' || $this->recipient->status === 'delivered') {
            return;
        }

        $success = $emailService->sendCampaignEmail($this->recipient);

        if (!$success && $this->recipient->status === 'failed') {
            Log::warning("Campaign email send failed for recipient ID #{$this->recipient->id}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendCampaignEmailJob completely failed for recipient ID #{$this->recipient->id}: " . $exception->getMessage());
        $this->recipient->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
