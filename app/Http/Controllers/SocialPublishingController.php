<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialMediaService;
use Illuminate\Http\Request;

class SocialPublishingController extends Controller
{
    public function __construct(protected SocialMediaService $socialService)
    {
    }

    public function index(Request $request)
    {
        return app(SocialPostController::class)->dashboard($request);
    }

    public function create()
    {
        return app(SocialPostController::class)->create();
    }

    public function store(Request $request)
    {
        return app(SocialPostController::class)->store($request);
    }

    public function retry(SocialPost $post)
    {
        $this->socialService->publishPost($post);
        return back()->with('success', 'Publishing retry dispatched.');
    }

    public function connectAccount(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:linkedin,facebook,instagram,tiktok',
            'account_name' => 'required|string|max:255',
            'account_id' => 'required|string|max:255',
        ]);

        SocialAccount::updateOrCreate(
            ['platform' => $validated['platform'], 'account_id' => $validated['account_id']],
            [
                'account_name' => $validated['account_name'],
                'access_token' => 'oauth_token_' . bin2hex(random_bytes(16)),
                'token_expires_at' => now()->addDays(60),
                'is_active' => true,
            ]
        );

        return back()->with('success', "Connected {$validated['platform']} account successfully.");
    }
}
