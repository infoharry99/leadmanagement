<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\SocialMediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishSocialPostJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public SocialPost $post)
    {
    }

    public function handle(SocialMediaService $socialMediaService): void
    {
        // Don't publish if already published
        if ($this->post->status === 'published') {
            return;
        }

        $this->post->update(['status' => 'publishing']);

        $results = $socialMediaService->publishPost($this->post);

        Log::info("PublishSocialPostJob completed for Post #{$this->post->id}: {$results['success']} published, {$results['failed']} failed.");
    }

    public function failed(Throwable $exception): void
    {
        Log::error("PublishSocialPostJob completely failed for Post #{$this->post->id}: " . $exception->getMessage());

        $this->post->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
