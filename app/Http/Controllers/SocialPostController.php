<?php

namespace App\Http\Controllers;

use App\Jobs\PublishSocialPostJob;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostAccount;
use App\Services\SocialMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocialPostController extends Controller
{
    public function __construct(protected SocialMediaService $socialService)
    {
    }

    /**
     * Simple Dashboard:
     * Shows only Create Post, Scheduled Posts, Published Posts, Failed Posts, and Social Media Settings.
     */
    public function dashboard(Request $request)
    {
        $statusFilter = $request->query('status');

        $counts = [
            'total' => SocialPost::count(),
            'scheduled' => SocialPost::where('status', 'scheduled')->count(),
            'published' => SocialPost::where('status', 'published')->count(),
            'failed' => SocialPost::where('status', 'failed')->count(),
        ];

        $connectedAccountsCount = SocialAccount::where('is_active', true)->count();

        $query = SocialPost::with(['postAccounts.account', 'createdBy'])->latest();

        if (in_array($statusFilter, ['scheduled', 'published', 'failed', 'draft'])) {
            $query->where('status', $statusFilter);
        }

        $posts = $query->paginate(15);

        return view('social_scheduler.dashboard', compact('counts', 'connectedAccountsCount', 'posts', 'statusFilter'));
    }

    /**
     * All Posts List (filterable by Scheduled, Published, Failed)
     */
    public function index(Request $request)
    {
        return $this->dashboard($request);
    }

    /**
     * Create Post Form
     */
    public function create()
    {
        $accounts = SocialAccount::all()->keyBy('platform');
        
        $platforms = [
            'facebook' => [
                'name' => 'Facebook Pages',
                'icon' => 'bi-facebook',
                'color' => '#1877F2',
                'account' => $accounts->get('facebook'),
                'restriction' => 'Accepts text, images, and videos.',
            ],
            'instagram' => [
                'name' => 'Instagram',
                'icon' => 'bi-instagram',
                'color' => '#E4405F',
                'account' => $accounts->get('instagram'),
                'restriction' => 'Requires an image or video file.',
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => 'bi-linkedin',
                'color' => '#0A66C2',
                'account' => $accounts->get('linkedin'),
                'restriction' => 'Accepts text, images, and videos.',
            ],
            'tiktok' => [
                'name' => 'TikTok',
                'icon' => 'bi-tiktok',
                'color' => '#000000',
                'account' => $accounts->get('tiktok'),
                'restriction' => 'Requires a video file (.mp4, .mov, 3s-10m).',
            ],
        ];

        return view('social_scheduler.create', compact('platforms'));
    }

    /**
     * Store & Publish/Schedule Post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'hashtags' => 'nullable|string|max:500',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'in:facebook,instagram,linkedin,tiktok',
            'action_type' => 'required|in:now,schedule',
            'scheduled_date' => 'required_if:action_type,schedule|nullable|date',
            'scheduled_time' => 'required_if:action_type,schedule|nullable|string',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm|max:51200', // 50MB max
        ]);

        $mediaUrls = [];

        // Handle Media File Upload
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('social_media', $filename, 'public');
            $mediaUrls[] = Storage::url($path);
        }

        // Validate platform requirements
        $restrictionErrors = $this->socialService->validatePlatformRestrictions(
            $validated['platforms'],
            $validated['content'],
            $mediaUrls
        );

        if (!empty($restrictionErrors)) {
            return back()->withInput()->withErrors($restrictionErrors);
        }

        // Process Hashtags into clean array
        $hashtags = [];
        if (!empty($validated['hashtags'])) {
            $rawTags = preg_split('/[\s,]+/', $validated['hashtags']);
            foreach ($rawTags as $tag) {
                $cleaned = trim($tag, "# \t\n\r\0\x0B");
                if (!empty($cleaned)) {
                    $hashtags[] = '#' . $cleaned;
                }
            }
        }

        $isScheduled = $validated['action_type'] === 'schedule';
        $scheduledAt = null;

        if ($isScheduled) {
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_date'] . ' ' . ($validated['scheduled_time'] ?? '12:00'));
            if ($scheduledAt->isPast()) {
                return back()->withInput()->withErrors(['scheduled_date' => 'Scheduled time must be in the future.']);
            }
        }

        $post = SocialPost::create([
            'title' => Str::limit($validated['content'], 60),
            'content' => $validated['content'],
            'target_platforms' => $validated['platforms'],
            'media_urls' => $mediaUrls,
            'hashtags' => $hashtags,
            'status' => $isScheduled ? 'scheduled' : 'pending_approval',
            'scheduled_at' => $scheduledAt,
            'created_by_id' => auth()->id(),
        ]);

        // Attach post to target accounts
        $accounts = SocialAccount::whereIn('platform', $validated['platforms'])->get();
        foreach ($accounts as $account) {
            SocialPostAccount::create([
                'social_post_id' => $post->id,
                'social_account_id' => $account->id,
                'status' => 'pending',
            ]);
        }

        if ($isScheduled) {
            return redirect()->route('social.posts.index', ['status' => 'scheduled'])
                ->with('success', 'Post scheduled successfully for ' . $scheduledAt->format('M d, Y • h:i A') . '!');
        }

        // Publish Immediately
        if (config('queue.default') === 'sync') {
            $this->socialService->publishPost($post);
        } else {
            PublishSocialPostJob::dispatch($post);
        }

        return redirect()->route('social.posts.index')
            ->with('success', 'Post published to selected platforms successfully!');
    }

    /**
     * Show Post Details & Platform Logs
     */
    public function show(SocialPost $post)
    {
        $post->load(['postAccounts.account', 'createdBy']);
        return view('social_scheduler.show', compact('post'));
    }

    /**
     * Retry Publishing a Failed Post
     */
    public function retry(SocialPost $post)
    {
        $post->update(['status' => 'publishing']);
        $results = $this->socialService->publishPost($post);

        if ($results['success'] > 0) {
            return back()->with('success', 'Publishing retry dispatched successfully!');
        }

        return back()->with('error', 'Retry failed: ' . ($post->error_message ?? 'Unable to publish.'));
    }

    /**
     * Delete Post
     */
    public function destroy(SocialPost $post)
    {
        // Delete media files from storage if present
        if (!empty($post->media_urls)) {
            foreach ($post->media_urls as $url) {
                $relativePath = str_replace('/storage/', '', $url);
                Storage::disk('public')->delete($relativePath);
            }
        }

        $post->delete();

        return back()->with('success', 'Post deleted successfully.');
    }
}
