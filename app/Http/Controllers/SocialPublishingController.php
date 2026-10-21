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

    public function index()
    {
        $accounts = SocialAccount::all();
        $posts = SocialPost::with(['postAccounts.account', 'createdBy'])
            ->latest()
            ->paginate(15);

        return view('social.index', compact('accounts', 'posts'));
    }

    public function create()
    {
        $accounts = SocialAccount::where('is_active', true)->get();
        return view('social.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'target_platforms' => 'required|array|min:1',
            'target_platforms.*' => 'in:linkedin,facebook,instagram,tiktok',
            'scheduled_at' => 'nullable|date',
            'publish_now' => 'nullable|boolean',
        ]);

        $publishNow = $request->boolean('publish_now');

        $post = SocialPost::create([
            'title' => $validated['title'] ?? 'Social Post',
            'content' => $validated['content'],
            'target_platforms' => $validated['target_platforms'],
            'status' => $publishNow ? 'publishing' : (!empty($validated['scheduled_at']) ? 'scheduled' : 'draft'),
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'created_by_id' => auth()->id(),
        ]);

        if ($publishNow) {
            $this->socialService->publishPost($post);
            return redirect()->route('social.index')->with('success', 'Post published to designated social accounts.');
        }

        return redirect()->route('social.index')->with('success', $post->status === 'scheduled' ? 'Post scheduled successfully.' : 'Post saved as draft.');
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
