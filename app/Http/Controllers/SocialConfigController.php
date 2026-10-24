<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SocialConfigController extends Controller
{
    /**
     * Settings → API Configuration & Developer Instructions
     */
    public function index()
    {
        $redirectUris = [
            'facebook' => route('social.oauth.callback', ['platform' => 'facebook']),
            'instagram' => route('social.oauth.callback', ['platform' => 'instagram']),
            'linkedin' => route('social.oauth.callback', ['platform' => 'linkedin']),
            'tiktok' => route('social.oauth.callback', ['platform' => 'tiktok']),
        ];

        $credentials = [
            'facebook_app_id' => env('FACEBOOK_APP_ID', ''),
            'facebook_app_secret' => env('FACEBOOK_APP_SECRET', ''),
            'instagram_client_id' => env('INSTAGRAM_CLIENT_ID', env('FACEBOOK_APP_ID', '')),
            'instagram_client_secret' => env('INSTAGRAM_CLIENT_SECRET', env('FACEBOOK_APP_SECRET', '')),
            'linkedin_client_id' => env('LINKEDIN_CLIENT_ID', ''),
            'linkedin_client_secret' => env('LINKEDIN_CLIENT_SECRET', ''),
            'tiktok_client_key' => env('TIKTOK_CLIENT_KEY', ''),
            'tiktok_client_secret' => env('TIKTOK_CLIENT_SECRET', ''),
        ];

        $accounts = SocialAccount::all()->keyBy('platform');

        $platformGuides = [
            'meta' => [
                'name' => 'Meta (Facebook Pages & Instagram)',
                'portal_url' => 'https://developers.facebook.com/apps/',
                'app_type' => 'Business',
                'products' => ['Facebook Login for Business', 'Instagram Graph API', 'Pages API'],
                'permissions' => [
                    'pages_show_list' => 'Retrieve list of managed Facebook Pages and their IDs.',
                    'pages_read_engagement' => 'Read page status, metadata, and audience engagement.',
                    'pages_manage_posts' => 'Create and publish feed posts, photos, and videos to Pages.',
                    'instagram_basic' => 'Read Instagram Professional account profile info and media.',
                    'instagram_content_publish' => 'Publish photos, reels, and carousel media to Instagram.',
                ],
                'notes' => 'Facebook and Instagram use the same Meta App. Ensure your Instagram account is an Instagram Professional (Business or Creator) account linked to your Facebook Page in Meta Business Suite.',
            ],
            'linkedin' => [
                'name' => 'LinkedIn API',
                'portal_url' => 'https://www.linkedin.com/developers/apps',
                'app_type' => 'Standard App',
                'products' => ['Share on LinkedIn', 'Sign In with LinkedIn using OpenID Connect'],
                'permissions' => [
                    'w_member_social' => 'Create, edit, and delete posts on behalf of the authenticated member.',
                    'openid' => 'Authenticate the user using modern OpenID Connect.',
                    'profile' => 'Retrieve the member name and profile URN.',
                ],
                'notes' => 'Request "Share on LinkedIn" under the Products tab in the LinkedIn Developer Portal. It provides instant self-serve access to w_member_social.',
            ],
            'tiktok' => [
                'name' => 'TikTok Content Posting API',
                'portal_url' => 'https://developers.tiktok.com/apps/',
                'app_type' => 'Web App',
                'products' => ['Content Posting API', 'Login Kit'],
                'permissions' => [
                    'video.publish' => 'Directly publish video content to TikTok on behalf of the creator.',
                    'video.upload' => 'Upload video files into TikTok media servers.',
                ],
                'notes' => 'TikTok Content Posting API requires video files (.mp4 or .mov) with a minimum duration of 3 seconds and maximum 10 minutes. Standalone text and images cannot be published to TikTok.',
            ],
        ];

        return view('social_scheduler.config', compact('redirectUris', 'credentials', 'accounts', 'platformGuides'));
    }

    /**
     * Save API credentials to .env file
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'facebook_app_id' => 'nullable|string|max:100',
            'facebook_app_secret' => 'nullable|string|max:100',
            'instagram_client_id' => 'nullable|string|max:100',
            'instagram_client_secret' => 'nullable|string|max:100',
            'linkedin_client_id' => 'nullable|string|max:100',
            'linkedin_client_secret' => 'nullable|string|max:100',
            'tiktok_client_key' => 'nullable|string|max:100',
            'tiktok_client_secret' => 'nullable|string|max:100',
        ]);

        $envFile = base_path('.env');

        if (!File::exists($envFile)) {
            return back()->with('error', '.env file does not exist.');
        }

        $envContent = File::get($envFile);

        $keysToUpdate = [
            'FACEBOOK_APP_ID' => $validated['facebook_app_id'] ?? '',
            'FACEBOOK_APP_SECRET' => $validated['facebook_app_secret'] ?? '',
            'INSTAGRAM_CLIENT_ID' => $validated['instagram_client_id'] ?: ($validated['facebook_app_id'] ?? ''),
            'INSTAGRAM_CLIENT_SECRET' => $validated['instagram_client_secret'] ?: ($validated['facebook_app_secret'] ?? ''),
            'LINKEDIN_CLIENT_ID' => $validated['linkedin_client_id'] ?? '',
            'LINKEDIN_CLIENT_SECRET' => $validated['linkedin_client_secret'] ?? '',
            'TIKTOK_CLIENT_KEY' => $validated['tiktok_client_key'] ?? '',
            'TIKTOK_CLIENT_SECRET' => $validated['tiktok_client_secret'] ?? '',
        ];

        foreach ($keysToUpdate as $key => $value) {
            $valueStr = trim($value);
            // Escape quotes if needed
            if (str_contains($valueStr, ' ') && !str_starts_with($valueStr, '"')) {
                $valueStr = '"' . $valueStr . '"';
            }

            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$valueStr}", $envContent);
            } else {
                $envContent .= "\n{$key}={$valueStr}";
            }
        }

        File::put($envFile, $envContent);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // Ignore cache clear error if running in restricted environments
        }

        return back()->with('success', 'API credentials updated successfully! You can now connect your accounts.');
    }
}
