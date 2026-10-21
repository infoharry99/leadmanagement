<?php

namespace App\Http\Controllers;

use App\Models\InboundReply;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadTask;
use App\Models\ManagerHandover;
use App\Models\SocialComment;
use App\Models\SocialMessage;
use App\Models\User;
use App\Services\InboundReplyService;
use App\Services\SocialMediaService;
use Illuminate\Http\Request;

class UnifiedInboxController extends Controller
{
    public function __construct(
        protected InboundReplyService $inboundService,
        protected SocialMediaService $socialService
    ) {}

    public function index(Request $request)
    {
        $channel = $request->input('channel', 'email');
        $intent = $request->input('intent');
        $status = $request->input('status');

        // 1. Email Replies Query
        $repliesQuery = InboundReply::with(['lead.company', 'lead.assignedUser', 'campaign'])
            ->latest('received_at');

        if ($intent) {
            $repliesQuery->where('ai_classification', $intent);
        }
        if ($status) {
            $repliesQuery->where('status', $status);
        }
        $emailReplies = $repliesQuery->paginate(15, ['*'], 'email_page');

        // 2. Social Comments Query
        $commentsQuery = SocialComment::with('account')->latest('commented_at');
        if ($status) {
            $commentsQuery->where('status', $status);
        }
        $socialComments = $commentsQuery->paginate(15, ['*'], 'comments_page');

        // 3. Social Messages Query
        $socialMessages = SocialMessage::with('account')->latest('received_at')->paginate(15, ['*'], 'messages_page');

        // 4. Manager Handovers Queue
        $handovers = ManagerHandover::with(['lead.company', 'assignedManager', 'reply'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $users = User::all();

        return view('inbox.index', compact(
            'channel',
            'intent',
            'status',
            'emailReplies',
            'socialComments',
            'socialMessages',
            'handovers',
            'users'
        ));
    }

    public function showReply(InboundReply $reply)
    {
        $reply->load(['lead.company', 'lead.assignedUser', 'campaign', 'message', 'handover.assignedManager']);
        $users = User::all();
        $statuses = Lead::getStatuses();

        return view('inbox.show_reply', compact('reply', 'users', 'statuses'));
    }

    public function assignReply(Request $request, InboundReply $reply)
    {
        $userId = (int) $request->input('user_id');
        $user = User::findOrFail($userId);

        $reply->update(['status' => 'assigned', 'is_reviewed' => true, 'reviewed_by_id' => auth()->id()]);

        if ($reply->lead) {
            $reply->lead->update(['assigned_user_id' => $userId]);
            $reply->lead->recordActivity('manager_assigned', "Assigned to {$user->name} via Unified Inbox");
        }

        if ($reply->handover) {
            $reply->handover->update(['assigned_manager_id' => $userId, 'status' => 'acknowledged']);
        }

        return back()->with('success', "Conversation assigned to {$user->name}.");
    }

    public function resolveReply(InboundReply $reply)
    {
        $reply->update(['status' => 'resolved', 'is_reviewed' => true, 'reviewed_by_id' => auth()->id()]);
        if ($reply->handover) {
            $reply->handover->update(['status' => 'converted']);
        }

        return back()->with('success', 'Conversation marked as resolved.');
    }

    public function simulateInbound(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $reply = $this->inboundService->processReply($validated['email'], $validated['subject'], $validated['body']);

        return redirect()->route('inbox.show', $reply->id)->with('success', 'Simulated inbound message ingested and classified.');
    }

    public function replyToComment(Request $request, SocialComment $comment)
    {
        $request->validate(['reply_text' => 'required|string']);

        $this->socialService->replyToComment($comment, $request->input('reply_text'));

        return back()->with('success', 'Comment response posted.');
    }
}
