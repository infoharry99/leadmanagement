<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SocialMediaService
{
    /**
     * Validate platform media and content requirements before publishing
     */
    public function validatePlatformRestrictions(array $platforms, ?string $content, ?array $mediaUrls): array
    {
        $errors = [];

        foreach ($platforms as $platform) {
            $hasMedia = !empty($mediaUrls) && count($mediaUrls) > 0;
            $firstMedia = $hasMedia ? $mediaUrls[0] : null;
            $isVideo = $firstMedia && preg_match('/\.(mp4|mov|webm)$/i', $firstMedia);
            $isImage = $firstMedia && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $firstMedia);

            switch ($platform) {
                case 'instagram':
                    if (!$hasMedia) {
                        $errors['instagram'] = 'Instagram requires an image or video file. Text-only posts are not supported by the Instagram Graph API.';
                    }
                    break;

                case 'tiktok':
                    if (!$hasMedia || !$isVideo) {
                        $errors['tiktok'] = 'TikTok requires a video file (.mp4, .mov, or .webm). Images and text-only posts are not eligible for publishing.';
                    }
                    break;

                case 'facebook':
                case 'linkedin':
                    if (empty(trim($content ?? '')) && !$hasMedia) {
                        $errors[$platform] = ucfirst($platform) . ' post must contain either text content or an image/video.';
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Publish a post to all designated target social accounts
     */
    public function publishPost(SocialPost $post): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];
        $targetPlatforms = $post->target_platforms ?? [];

        // Check if there are active connected accounts for the requested platforms
        $accounts = SocialAccount::whereIn('platform', $targetPlatforms)
            ->where('is_active', true)
            ->get();

        if ($accounts->isEmpty()) {
            $errorMessage = 'No active connected accounts found for: ' . implode(', ', array_map('ucfirst', $targetPlatforms)) . '. Please connect your accounts in Settings → Social Connections.';
            $post->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);
            return ['success' => 0, 'failed' => count($targetPlatforms), 'errors' => [$errorMessage]];
        }

        // Validate platform media restrictions
        $restrictionErrors = $this->validatePlatformRestrictions($targetPlatforms, $post->content, $post->media_urls);
        $allSuccessful = true;

        foreach ($accounts as $account) {
            $postAccount = SocialPostAccount::firstOrCreate(
                ['social_post_id' => $post->id, 'social_account_id' => $account->id],
                ['status' => 'pending']
            );

            // Platform-specific rule check
            if (isset($restrictionErrors[$account->platform])) {
                $postAccount->update([
                    'status' => 'failed',
                    'error_message' => $restrictionErrors[$account->platform],
                ]);
                $results['failed']++;
                $results['errors'][] = "({$account->account_name}) " . $restrictionErrors[$account->platform];
                $allSuccessful = false;
                continue;
            }

            // Check token expiration
            if ($account->isTokenExpired()) {
                $error = "OAuth token for {$account->account_name} has expired. Please reconnect in Settings → Social Connections.";
                $postAccount->update([
                    'status' => 'failed',
                    'error_message' => $error,
                ]);
                $results['failed']++;
                $results['errors'][] = $error;
                $allSuccessful = false;
                continue;
            }

            try {
                $response = $this->dispatchToPlatform($account, $post);

                if ($response['success']) {
                    $postAccount->update([
                        'status' => 'published',
                        'platform_post_id' => $response['platform_post_id'] ?? null,
                        'published_at' => now(),
                        'error_message' => null,
                        'engagement_metrics' => ['likes' => 0, 'comments' => 0, 'shares' => 0],
                    ]);
                    $results['success']++;
                } else {
                    $postAccount->update([
                        'status' => 'failed',
                        'error_message' => $response['error'] ?? 'Publishing failed.',
                    ]);
                    $results['failed']++;
                    $results['errors'][] = "({$account->platform}) " . ($response['error'] ?? 'Unknown error');
                    $allSuccessful = false;
                }
            } catch (Throwable $e) {
                Log::error("Social publishing exception on {$account->platform}: " . $e->getMessage());
                $postAccount->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $results['failed']++;
                $results['errors'][] = "({$account->platform}) " . $e->getMessage();
                $allSuccessful = false;
            }
        }

        // Update overall post status
        $finalStatus = $allSuccessful && $results['success'] > 0
            ? 'published'
            : ($results['success'] > 0 ? 'published' : 'failed');

        $post->update([
            'status' => $finalStatus,
            'published_at' => $results['success'] > 0 ? now() : null,
            'error_message' => !empty($results['errors']) ? implode(' | ', $results['errors']) : null,
        ]);

        if (class_exists(AuditLog::class)) {
            AuditLog::log('social_post_published', SocialPost::class, $post->id, null, $results);
        }

        return $results;
    }

    /**
     * Dispatch to official platform adapter
     */
    protected function dispatchToPlatform(SocialAccount $account, SocialPost $post): array
    {
        return match ($account->platform) {
            'facebook' => $this->publishToFacebook($account, $post),
            'instagram' => $this->publishToInstagram($account, $post),
            'linkedin' => $this->publishToLinkedIn($account, $post),
            'tiktok' => $this->publishToTikTok($account, $post),
            default => ['success' => false, 'error' => "Unsupported platform: {$account->platform}"],
        };
    }

    /**
     * Facebook Pages Graph API
     */
    protected function publishToFacebook(SocialAccount $account, SocialPost $post): array
    {
        $token = $account->access_token;
        $pageId = $account->account_id;
        $mediaUrls = $post->media_urls ?? [];
        $hasMedia = !empty($mediaUrls);
        $fullText = $this->buildFullPostText($post);

        // Real API Call if real token present
        if (!empty($token) && !str_starts_with($token, 'oauth_token_') && !str_starts_with($token, 'mock_')) {
            $endpoint = "https://graph.facebook.com/v20.0/{$pageId}/";

            if ($hasMedia) {
                $firstMedia = $mediaUrls[0];
                $isVideo = preg_match('/\.(mp4|mov|webm)$/i', $firstMedia);
                $relativePath = str_replace('/storage/', '', $firstMedia);
                $localFullPath = Storage::disk('public')->path($relativePath);

                if ($isVideo) {
                    // Video Post: Stream direct file if available, or fallback to public URL
                    if (file_exists($localFullPath)) {
                        $res = Http::attach('source', file_get_contents($localFullPath), basename($localFullPath))
                            ->post($endpoint . 'videos', [
                                'description' => $fullText,
                                'access_token' => $token,
                            ]);
                    } else {
                        $res = Http::post($endpoint . 'videos', [
                            'file_url' => $this->resolvePublicMediaUrl($firstMedia),
                            'description' => $fullText,
                            'access_token' => $token,
                        ]);
                    }
                } else {
                    // Photo Post: Direct multipart upload streams image directly to Facebook
                    if (file_exists($localFullPath)) {
                        $res = Http::attach('source', file_get_contents($localFullPath), basename($localFullPath))
                            ->post($endpoint . 'photos', [
                                'caption' => $fullText,
                                'access_token' => $token,
                            ]);
                    } else {
                        $res = Http::post($endpoint . 'photos', [
                            'url' => $this->resolvePublicMediaUrl($firstMedia),
                            'caption' => $fullText,
                            'access_token' => $token,
                        ]);
                    }
                }
            } else {
                // Text Status Post
                $res = Http::post($endpoint . 'feed', [
                    'message' => $fullText,
                    'access_token' => $token,
                ]);
            }

            if ($res->successful()) {
                return ['success' => true, 'platform_post_id' => $res->json('id') ?? $res->json('post_id')];
            }

            $errorMsg = $res->json('error.message') ?? $res->body();
            return ['success' => false, 'error' => "Meta Graph API Error: {$errorMsg}"];
        }

        // Allow mock sandbox in automated testing
        if (app()->environment('testing')) {
            return [
                'success' => true,
                'platform_post_id' => 'fb_post_' . rand(100000000000, 999999999999),
            ];
        }

        return [
            'success' => false,
            'error' => "Placeholder/demo access token detected for Facebook Page '{$account->account_name}'. To publish directly to your live Facebook page, please click 'Reconnect' in Settings → Social Media Connections or provide a valid Meta Page Access Token.",
        ];
    }

    /**
     * Instagram Graph API (2-step container workflow)
     */
    protected function publishToInstagram(SocialAccount $account, SocialPost $post): array
    {
        $token = $account->access_token;
        $igUserId = $account->account_id;
        $mediaUrls = $post->media_urls ?? [];

        if (empty($mediaUrls)) {
            return ['success' => false, 'error' => 'Instagram requires an image or video file.'];
        }

        $fullText = $this->buildFullPostText($post);
        $firstMedia = $mediaUrls[0];
        $isVideo = preg_match('/\.(mp4|mov|webm)$/i', $firstMedia);
        $publicMediaUrl = $this->resolvePublicMediaUrl($firstMedia);

        if (!empty($token) && !str_starts_with($token, 'oauth_token_') && !str_starts_with($token, 'mock_')) {
            $endpoint = "https://graph.facebook.com/v20.0/{$igUserId}/";

            // Step 1: Create Media Container
            $containerParams = [
                'caption' => $fullText,
                'access_token' => $token,
            ];

            if ($isVideo) {
                $containerParams['media_type'] = 'REELS';
                $containerParams['video_url'] = $publicMediaUrl;
            } else {
                $containerParams['image_url'] = $publicMediaUrl;
            }

            $containerRes = Http::post($endpoint . 'media', $containerParams);

            if (!$containerRes->successful()) {
                $errorMsg = $containerRes->json('error.message') ?? $containerRes->body();
                return ['success' => false, 'error' => "Instagram Media Container Error: {$errorMsg}"];
            }

            $creationId = $containerRes->json('id');

            // Step 2: Publish the Container
            $publishRes = Http::post($endpoint . 'media_publish', [
                'creation_id' => $creationId,
                'access_token' => $token,
            ]);

            if ($publishRes->successful()) {
                return ['success' => true, 'platform_post_id' => $publishRes->json('id')];
            }

            $errorMsg = $publishRes->json('error.message') ?? $publishRes->body();
            return ['success' => false, 'error' => "Instagram Publish Error: {$errorMsg}"];
        }

        if (app()->environment('testing')) {
            return [
                'success' => true,
                'platform_post_id' => 'ig_post_' . rand(100000000000, 999999999999),
            ];
        }

        return [
            'success' => false,
            'error' => "Placeholder/demo access token detected for Instagram ('{$account->account_name}'). Please connect your Instagram Business account via Official OAuth in Settings → Social Media Connections.",
        ];
    }

    /**
     * Resolve public media URL, fixing localhost / 127.0.0.1 on production servers
     */
    protected function resolvePublicMediaUrl(string $mediaPath): string
    {
        if (str_starts_with($mediaPath, 'http://') || str_starts_with($mediaPath, 'https://')) {
            $url = $mediaPath;
        } else {
            $url = url($mediaPath);
        }

        // Replace localhost/127.0.0.1 with actual host if request is from a live domain
        if (str_contains($url, '127.0.0.1') || str_contains($url, 'localhost')) {
            if (request() && request()->getHost() && !str_contains(request()->getHost(), '127.0.0.1')) {
                $url = str_replace(
                    ['http://127.0.0.1:8000', 'http://127.0.0.1', 'http://localhost'],
                    request()->getSchemeAndHttpHost(),
                    $url
                );
            }
        }

        return $url;
    }

    /**
     * LinkedIn Share API
     */
    protected function publishToLinkedIn(SocialAccount $account, SocialPost $post): array
    {
        $token = $account->access_token;
        $authorUrn = str_starts_with($account->account_id, 'urn:li:') 
            ? $account->account_id 
            : 'urn:li:person:' . $account->account_id;
        $fullText = $this->buildFullPostText($post);

        if (!empty($token) && !str_starts_with($token, 'oauth_token_') && !str_starts_with($token, 'mock_')) {
            $payload = [
                'author' => $authorUrn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => ['text' => $fullText],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'],
            ];

            $res = Http::withToken($token)
                ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                ->post('https://api.linkedin.com/v2/ugcPosts', $payload);

            if ($res->successful()) {
                return ['success' => true, 'platform_post_id' => $res->json('id')];
            }

            $errorMsg = $res->json('message') ?? $res->body();
            return ['success' => false, 'error' => "LinkedIn API Error: {$errorMsg}"];
        }

        if (app()->environment('testing')) {
            return [
                'success' => true,
                'platform_post_id' => 'urn:li:share:' . rand(1000000000, 9999999999),
            ];
        }

        return [
            'success' => false,
            'error' => "Placeholder/demo access token detected for LinkedIn ('{$account->account_name}'). Please connect your LinkedIn account via Official OAuth in Settings → Social Media Connections.",
        ];
    }

    /**
     * TikTok Content Posting API
     */
    protected function publishToTikTok(SocialAccount $account, SocialPost $post): array
    {
        $token = $account->access_token;
        $mediaUrls = $post->media_urls ?? [];

        if (empty($mediaUrls)) {
            return ['success' => false, 'error' => 'TikTok requires a video file (.mp4, .mov).'];
        }

        $firstMedia = $mediaUrls[0];
        $fullText = $this->buildFullPostText($post);
        $publicMediaUrl = $this->resolvePublicMediaUrl($firstMedia);

        if (!empty($token) && !str_starts_with($token, 'oauth_token_') && !str_starts_with($token, 'mock_')) {
            $payload = [
                'post_info' => [
                    'title' => mb_substr($fullText, 0, 150),
                    'privacy_level' => 'PUBLIC_TO_EVERYONE',
                    'disable_duet' => false,
                    'disable_stitch' => false,
                    'disable_comment' => false,
                ],
                'source_info' => [
                    'source' => 'PULL_FROM_URL',
                    'video_url' => $publicMediaUrl,
                ],
            ];

            $res = Http::withToken($token)
                ->post('https://open.tiktokapis.com/v2/post/publish/video/init/', $payload);

            if ($res->successful() && $res->json('data.publish_id')) {
                return ['success' => true, 'platform_post_id' => $res->json('data.publish_id')];
            }

            $errorMsg = $res->json('error.message') ?? $res->body();
            return ['success' => false, 'error' => "TikTok API Error: {$errorMsg}"];
        }

        if (app()->environment('testing')) {
            return [
                'success' => true,
                'platform_post_id' => 'tt_publish_' . rand(10000000, 99999999),
            ];
        }

        return [
            'success' => false,
            'error' => "Placeholder/demo access token detected for TikTok ('{$account->account_name}'). Please connect your TikTok account via Official OAuth in Settings → Social Media Connections.",
        ];
    }

    /**
     * Append hashtags cleanly to content
     */
    protected function buildFullPostText(SocialPost $post): string
    {
        $text = trim($post->content ?? '');
        $hashtags = $post->hashtags ?? [];

        if (!empty($hashtags)) {
            $tagsString = implode(' ', array_map(function ($tag) {
                $tag = trim($tag);
                return str_starts_with($tag, '#') ? $tag : '#' . $tag;
            }, $hashtags));

            $text .= "\n\n" . $tagsString;
        }

        return trim($text);
    }
}
