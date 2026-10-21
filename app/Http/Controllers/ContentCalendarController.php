<?php

namespace App\Http\Controllers;

use App\Models\ContentCalendar;
use App\Models\SocialPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContentCalendarController extends Controller
{
    public function index(Request $request)
    {
        $platform = $request->input('platform');
        $status = $request->input('status');
        $view = $request->input('view', 'week'); // week or list

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $query = ContentCalendar::with(['assignedUser', 'socialPost']);

        if ($platform) {
            $query->where('platform', $platform);
        }
        if ($status) {
            $query->where('status', $status);
        }

        if ($view === 'week') {
            $items = $query->whereBetween('scheduled_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->orderBy('scheduled_date')
                ->orderBy('scheduled_time')
                ->get();
        } else {
            $items = $query->orderBy('scheduled_date', 'desc')->paginate(15);
        }

        $users = User::all();

        return view('calendar.index', compact('items', 'platform', 'status', 'view', 'startOfWeek', 'endOfWeek', 'users'));
    }

    public function create()
    {
        $users = User::all();
        return view('calendar.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'platform' => 'required|in:linkedin,instagram,facebook,tiktok,blog',
            'content_type' => 'required|string',
            'target_audience' => 'nullable|string',
            'industry' => 'nullable|string',
            'campaign_goal' => 'nullable|string',
            'objective' => 'nullable|string',
            'content_draft' => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $item = ContentCalendar::create(array_merge($validated, [
            'status' => 'planned',
        ]));

        return redirect()->route('calendar.index')->with('success', "Content planned for {$item->scheduled_date->format('M d, Y')}");
    }

    public function updateStatus(Request $request, ContentCalendar $calendar)
    {
        $status = $request->input('status');
        $validStatuses = ['planned', 'draft', 'approval_requested', 'approved', 'scheduled', 'published', 'rejected'];

        if (!in_array($status, $validStatuses, true)) {
            return back()->with('error', 'Invalid status.');
        }

        $calendar->update(['status' => $status]);

        // If approved and content draft exists, create or link social post
        if ($status === 'approved' && !empty($calendar->content_draft) && !$calendar->social_post_id) {
            $socialPost = SocialPost::create([
                'title' => $calendar->title,
                'content' => $calendar->content_draft,
                'target_platforms' => [$calendar->platform],
                'status' => 'scheduled',
                'scheduled_at' => Carbon::parse($calendar->scheduled_date->toDateString() . ' ' . $calendar->scheduled_time),
                'approved_by_id' => auth()->id(),
                'created_by_id' => $calendar->assigned_user_id ?: auth()->id(),
            ]);

            $calendar->update(['social_post_id' => $socialPost->id]);
        }

        return back()->with('success', "Content item status updated to " . ucfirst(str_replace('_', ' ', $status)));
    }
}
