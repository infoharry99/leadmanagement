<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignEmailJob;
use App\Models\EmailCampaign;
use App\Services\EmailService;
use Illuminate\Console\Command;

class DispatchScheduledCampaignsCommand extends Command
{
    protected $signature = 'nexteck:dispatch-scheduled-campaigns';
    protected $description = 'Find and dispatch all due scheduled email campaigns whose scheduled_at time has arrived';

    public function handle(EmailService $emailService): int
    {
        $dueCampaigns = EmailCampaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($dueCampaigns->isEmpty()) {
            $this->line('No scheduled campaigns currently due for dispatch.');
            return self::SUCCESS;
        }

        $this->info("Found {$dueCampaigns->count()} due scheduled campaign(s). Dispatching...");

        foreach ($dueCampaigns as $campaign) {
            $campaign->update(['status' => 'running']);

            $recipients = $campaign->recipients()
                ->whereIn('status', ['pending', 'queued'])
                ->get();

            $this->line("Dispatching {$recipients->count()} recipient(s) for campaign: {$campaign->name}");

            foreach ($recipients as $recipient) {
                if (config('queue.default') === 'sync') {
                    $emailService->sendCampaignEmail($recipient);
                } else {
                    $recipient->update(['status' => 'queued']);
                    SendCampaignEmailJob::dispatch($recipient);
                }
            }
        }

        $this->info('Scheduled campaigns successfully dispatched.');
        return self::SUCCESS;
    }
}
