<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Services\SocialMediaService;
use Illuminate\Console\Command;

class PublishScheduledSocialPostsCommand extends Command
{
    protected $signature = 'nexteck:publish-social';
    protected $description = 'Publish scheduled social media posts that are due';

    public function handle(SocialMediaService $socialService): int
    {
        $this->info('Checking for scheduled social posts...');

        $duePosts = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($duePosts as $post) {
            $this->line("Publishing post #{$post->id}: {$post->title}");
            $socialService->publishPost($post);
            $count++;
        }

        $this->info("Published {$count} due social posts.");
        return Command::SUCCESS;
    }
}
