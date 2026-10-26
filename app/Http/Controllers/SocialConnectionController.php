<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SocialConnectionController extends Controller
{
    /**
     * Settings → Social Media Connections Page
     */
    public function index()
    {
        $accounts = SocialAccount::all()->keyBy('platform');

        $platforms = [
            'facebook' => [
                'name' => 'Facebook Pages',
                'description' => 'Publish posts, photos, and videos to Facebook business pages via Meta Graph API.',
                'icon' => 'bi-facebook',
                'color' => '#1877F2',
                'account' => $accounts->get('facebook'),
                'scopes' => ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts'],
                'api_docs' => 'https://developers.facebook.com/docs/pages-api',
            ],
            'instagram' => [
                'name' => 'Instagram Professional',
                'description' => 'Publish photos, carousels, and reels to Instagram Creator & Business accounts.',
                'icon' => 'bi-instagram',
                'color' => '#E4405F',
                'account' => $accounts->get('instagram'),
                'scopes' => ['instagram_basic', 'instagram_content_publish', 'pages_show_list'],
                'api_docs' => 'https://developers.facebook.com/docs/instagram-platform/instagram-graph-api',
            ],
            'linkedin' => [
                'name' => 'LinkedIn Profile & Pages',
                'description' => 'Share professional posts, articles, and media via LinkedIn Share / Posts API.',
                'icon' => 'bi-linkedin',
                'color' => '#0A66C2',
                'account' => $accounts->get('linkedin'),
                'scopes' => ['openid', 'profile', 'w_member_social'],
                'api_docs' => 'https://learn.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/share-on-linkedin',
            ],
            'tiktok' => [
                'name' => 'TikTok for Business',
                'description' => 'Upload and schedule public videos using TikTok Content Posting API.',
                'icon' => 'bi-tiktok',
                'color' => '#000000',
                'account' => $accounts->get('tiktok'),
                'scopes' => ['video.publish', 'video.upload'],
                'api_docs' => 'https://developers.tiktok.com/doc/content-posting-api-get-started',
            ],
        ];

        return view('social_scheduler.connections', compact('platforms'));
    }

    /**
     * Get clean HTTPS OAuth Redirect URI matching live domain
     */
    protected function getNormalizedRedirectUri(string $platform): string
    {
        $redirectUri = route('social.oauth.callback', ['platform' => $platform]);

        // Ensure HTTPS on production or live domain
        if (
            request()->secure() ||
            request()->header('X-Forwarded-Proto') === 'https' ||
            (request()->getHost() && str_contains(request()->getHost(), 'nexteck.uk')) ||
            !app()->environment('local')
        ) {
            $redirectUri = str_replace('http://', 'https://', $redirectUri);
        }

        // Dynamically fix host if APP_URL was left as localhost/127.0.0.1 on a live server
        if (str_contains($redirectUri, '127.0.0.1') || str_contains($redirectUri, 'localhost')) {
            if (request()->getHost() && !str_contains(request()->getHost(), '127.0.0.1')) {
                $redirectUri = str_replace(
                    ['http://127.0.0.1:8000', 'http://127.0.0.1', 'http://localhost'],
                    request()->getSchemeAndHttpHost(),
                    $redirectUri
                );
            }
        }

        return $redirectUri;
    }

    /**
     * Redirect to Official OAuth Authorization URL
     */
    public function oauthRedirect(string $platform)
    {
        $redirectUri = $this->getNormalizedRedirectUri($platform);

        switch ($platform) {
            case 'facebook':
                $appId = env('FACEBOOK_APP_ID') ?: env('META_APP_ID');
                if (empty($appId)) {
                    return back()->with('error', 'Meta App ID is not configured. Please enter your Meta credentials in Settings → API Configuration.');
                }
                $scopes = 'pages_show_list,pages_read_engagement,pages_manage_posts';
                $url = "https://www.facebook.com/v20.0/dialog/oauth?client_id={$appId}&redirect_uri=" . urlencode($redirectUri) . "&scope={$scopes}&response_type=code";
                return redirect()->away($url);

            case 'instagram':
                $appId = env('INSTAGRAM_CLIENT_ID') ?: env('FACEBOOK_APP_ID') ?: env('META_APP_ID');
                if (empty($appId)) {
                    return back()->with('error', 'Meta / Instagram App ID is not configured. Please enter your Meta credentials in Settings → API Configuration.');
                }
                // Only request Instagram scopes (do NOT include pages_manage_posts)
                $scopes = 'instagram_basic,instagram_content_publish,pages_show_list';
                $url = "https://www.facebook.com/v20.0/dialog/oauth?client_id={$appId}&redirect_uri=" . urlencode($redirectUri) . "&scope={$scopes}&response_type=code";
                return redirect()->away($url);

            case 'linkedin':
                $clientId = env('LINKEDIN_CLIENT_ID');
                if (empty($clientId)) {
                    return back()->with('error', 'LinkedIn Client ID is not configured. Please enter your credentials in Settings → API Configuration.');
                }
                $scopes = 'openid profile w_member_social';
                $url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$clientId}&redirect_uri=" . urlencode($redirectUri) . "&scope=" . urlencode($scopes);
                return redirect()->away($url);

            case 'tiktok':
                $clientKey = env('TIKTOK_CLIENT_KEY');
                if (empty($clientKey)) {
                    return back()->with('error', 'TikTok Client Key is not configured. Please enter your credentials in Settings → API Configuration.');
                }
                $scopes = 'video.publish,video.upload';
                $url = "https://www.tiktok.com/v2/auth/authorize/?client_key={$clientKey}&scope={$scopes}&response_type=code&redirect_uri=" . urlencode($redirectUri);
                return redirect()->away($url);

            default:
                return back()->with('error', 'Unsupported platform for OAuth authorization.');
        }
    }

    /**
     * Handle OAuth Callback from Platform & Meta Webhook Handshake
     */
    public function oauthCallback(string $platform, Request $request)
    {
        // 1. Meta Webhook Verification Handshake (hub.mode, hub.challenge, hub.verify_token)
        $hubMode = $request->input('hub_mode') ?? $request->query('hub.mode') ?? $request->query('hub_mode');
        $hubChallenge = $request->input('hub_challenge') ?? $request->query('hub.challenge') ?? $request->query('hub_challenge');
        $hubVerifyToken = $request->input('hub_verify_token') ?? $request->query('hub.verify_token') ?? $request->query('hub_verify_token');

        if ($hubMode === 'subscribe' && !empty($hubChallenge)) {
            Log::info("Meta Webhook verification handshake received for {$platform} with token: {$hubVerifyToken}");
            return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
        }

        // 2. Incoming Webhook Event from Meta
        if ($request->isMethod('post') && in_array($platform, ['facebook', 'instagram'])) {
            Log::info("Meta Webhook event payload for {$platform}: " . json_encode($request->all()));
            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        }

        // 3. OAuth Error Handling
        if ($request->has('error')) {
            return redirect()->route('social.connections.index')
                ->with('error', "OAuth Authorization failed: " . ($request->query('error_description') ?? $request->query('error')));
        }

        // 4. OAuth Code Exchange
        $code = $request->query('code');
        if (empty($code)) {
            return redirect()->route('social.connections.index')
                ->with('error', 'No authorization code received from platform.');
        }

        $redirectUri = $this->getNormalizedRedirectUri($platform);

        try {
            switch ($platform) {
                case 'facebook':
                case 'instagram':
                    return $this->handleMetaCallback($code, $redirectUri, $platform);

                case 'linkedin':
                    return $this->handleLinkedInCallback($code, $redirectUri);

                case 'tiktok':
                    return $this->handleTikTokCallback($code, $redirectUri);
            }
        } catch (Throwable $e) {
            Log::error("OAuth Callback error for {$platform}: " . $e->getMessage());
            return redirect()->route('social.connections.index')
                ->with('error', "Connection failed: " . $e->getMessage());
        }

        return redirect()->route('social.connections.index')->with('success', "Connected {$platform} account successfully!");
    }

    /**
     * Dedicated Meta Webhook Endpoint
     */
    public function handleMetaWebhook(Request $request)
    {
        $hubMode = $request->input('hub_mode') ?? $request->query('hub.mode') ?? $request->query('hub_mode');
        $hubChallenge = $request->input('hub_challenge') ?? $request->query('hub.challenge') ?? $request->query('hub_challenge');

        if ($hubMode === 'subscribe' && !empty($hubChallenge)) {
            return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
        }

        if ($request->isMethod('post')) {
            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        }

        return response('Meta Webhook Endpoint Active', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Manual Connection (For Page ID / System User Tokens / Prototyping)
     */
    public function connectManual(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:facebook,instagram,linkedin,tiktok',
            'account_name' => 'required|string|max:255',
            'account_id' => 'required|string|max:255',
            'access_token' => 'nullable|string',
        ]);

        $token = !empty($validated['access_token']) 
            ? $validated['access_token'] 
            : 'oauth_token_' . bin2hex(random_bytes(16));

        SocialAccount::updateOrCreate(
            ['platform' => $validated['platform'], 'account_id' => $validated['account_id']],
            [
                'account_name' => $validated['account_name'],
                'access_token' => $token,
                'token_expires_at' => now()->addDays(60),
                'is_active' => true,
                'metadata' => [
                    'connection_type' => 'manual',
                    'connected_at' => now()->toIso8601String(),
                ],
            ]
        );

        return redirect()->route('social.connections.index')->with('success', "Connected {$validated['platform']} account ({$validated['account_name']}) successfully!");
    }

    /**
     * Disconnect / Revoke Account
     */
    public function disconnect(SocialAccount $account)
    {
        $platform = ucfirst($account->platform);
        $account->update(['is_active' => false]);

        return redirect()->route('social.connections.index')->with('success', "{$platform} account disconnected successfully.");
    }

    /**
     * Meta (Facebook / Instagram) Token Exchange
     */
    protected function handleMetaCallback(string $code, string $redirectUri, string $platform)
    {
        $appId = env('FACEBOOK_APP_ID') ?: env('META_APP_ID');
        $appSecret = env('FACEBOOK_APP_SECRET') ?: env('META_APP_SECRET');

        // Exchange code for user access token
        $tokenRes = Http::get('https://graph.facebook.com/v20.0/oauth/access_token', [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$tokenRes->successful()) {
            throw new \Exception("Meta token exchange failed: " . $tokenRes->body());
        }

        $userToken = $tokenRes->json('access_token');

        // Fetch user pages
        $pagesRes = Http::get('https://graph.facebook.com/v20.0/me/accounts', [
            'access_token' => $userToken,
            'fields' => 'name,access_token,id,instagram_business_account',
        ]);

        $pages = $pagesRes->json('data') ?? [];

        if (empty($pages)) {
            // Fallback to user profile
            $userRes = Http::get('https://graph.facebook.com/v20.0/me', ['access_token' => $userToken]);
            SocialAccount::updateOrCreate(
                ['platform' => 'facebook', 'account_id' => $userRes->json('id') ?? 'user_fb'],
                [
                    'account_name' => $userRes->json('name') ?? 'Facebook User',
                    'access_token' => $userToken,
                    'token_expires_at' => now()->addDays(60),
                    'is_active' => true,
                ]
            );
        } else {
            // Save first Page
            $firstPage = $pages[0];
            SocialAccount::updateOrCreate(
                ['platform' => 'facebook', 'account_id' => $firstPage['id']],
                [
                    'account_name' => $firstPage['name'],
                    'access_token' => $firstPage['access_token'],
                    'token_expires_at' => now()->addDays(60),
                    'is_active' => true,
                ]
            );

            // If Page has connected Instagram account, connect Instagram too!
            if (!empty($firstPage['instagram_business_account']['id'])) {
                $igId = $firstPage['instagram_business_account']['id'];
                SocialAccount::updateOrCreate(
                    ['platform' => 'instagram', 'account_id' => $igId],
                    [
                        'account_name' => $firstPage['name'] . ' (Instagram)',
                        'access_token' => $firstPage['access_token'],
                        'token_expires_at' => now()->addDays(60),
                        'is_active' => true,
                    ]
                );
            }
        }

        return redirect()->route('social.connections.index')->with('success', 'Meta account authorized and connected!');
    }

    /**
     * LinkedIn Token Exchange
     */
    protected function handleLinkedInCallback(string $code, string $redirectUri)
    {
        $clientId = env('LINKEDIN_CLIENT_ID');
        $clientSecret = env('LINKEDIN_CLIENT_SECRET');

        $tokenRes = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (!$tokenRes->successful()) {
            throw new \Exception("LinkedIn token exchange failed: " . $tokenRes->body());
        }

        $accessToken = $tokenRes->json('access_token');
        $expiresIn = $tokenRes->json('expires_in', 5184000); // ~60 days

        // Fetch User Info
        $userRes = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
        $profile = $userRes->json();

        SocialAccount::updateOrCreate(
            ['platform' => 'linkedin', 'account_id' => $profile['sub'] ?? 'li_' . rand(1000, 9999)],
            [
                'account_name' => $profile['name'] ?? 'LinkedIn User',
                'avatar_url' => $profile['picture'] ?? null,
                'access_token' => $accessToken,
                'token_expires_at' => now()->addSeconds($expiresIn),
                'is_active' => true,
            ]
        );

        return redirect()->route('social.connections.index')->with('success', 'LinkedIn account connected successfully!');
    }

    /**
     * TikTok Token Exchange
     */
    protected function handleTikTokCallback(string $code, string $redirectUri)
    {
        $clientKey = env('TIKTOK_CLIENT_KEY');
        $clientSecret = env('TIKTOK_CLIENT_SECRET');

        $tokenRes = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => $clientKey,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if (!$tokenRes->successful()) {
            throw new \Exception("TikTok token exchange failed: " . $tokenRes->body());
        }

        $data = $tokenRes->json('data') ?? [];
        $accessToken = $data['access_token'] ?? null;
        $openId = $data['open_id'] ?? 'tt_' . rand(1000, 9999);

        SocialAccount::updateOrCreate(
            ['platform' => 'tiktok', 'account_id' => $openId],
            [
                'account_name' => 'TikTok User (' . substr($openId, 0, 8) . ')',
                'access_token' => $accessToken,
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 86400),
                'refresh_token' => $data['refresh_token'] ?? null,
                'is_active' => true,
            ]
        );

        return redirect()->route('social.connections.index')->with('success', 'TikTok account connected successfully!');
    }
}
