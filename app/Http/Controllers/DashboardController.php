<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\EmailCampaign;
use App\Models\InboundReply;
use App\Models\IntegrationSetting;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadTask;
use App\Models\ManagerHandover;
use App\Models\SocialComment;
use App\Models\SocialPost;
use App\Models\SuppressionList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->input('period', 30);
        $startDate = now()->subDays($days)->startOfDay();

        // 1. Lead Metrics
        $totalLeads = Lead::where('is_archived', false)->count();
        $newLeadsThisPeriod = Lead::where('is_archived', false)->where('created_at', '>=', $startDate)->count();
        $convertedLeads = Lead::where('status', Lead::STATUS_CONVERTED)->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Leads by status
        $leadsByStatus = Lead::where('is_archived', false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Leads by source
        $leadsBySource = Lead::where('leads.is_archived', false)
            ->join('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->select('lead_sources.name', DB::raw('count(*) as count'))
            ->groupBy('lead_sources.name')
            ->pluck('count', 'name')
            ->toArray();

        // Leads by industry
        $leadsByIndustry = Lead::where('is_archived', false)
            ->whereNotNull('industry')
            ->select('industry', DB::raw('count(*) as count'))
            ->groupBy('industry')
            ->pluck('count', 'industry')
            ->toArray();

        // 2. Email Campaign Performance
        $emailsSent = (int) EmailCampaign::sum('sent_count');
        $emailsDelivered = (int) EmailCampaign::sum('delivered_count');
        $emailsBounced = (int) EmailCampaign::sum('bounced_count');
        $repliesReceived = (int) InboundReply::count();
        $positiveReplies = (int) InboundReply::whereIn('ai_classification', ['interested', 'meeting_request'])->count();
        $suppressionCount = (int) SuppressionList::count();

        // 3. Social Media Metrics
        $socialPublished = (int) SocialPost::where('status', 'published')->count();
        $socialScheduled = (int) SocialPost::where('status', 'scheduled')->count();
        $socialFailed = (int) SocialPost::where('status', 'failed')->count();
        $commentsAttention = (int) SocialComment::where('status', 'unread')->count();

        // 4. Content & Blogs
        $blogsPublished = (int) Blog::where('status', 'published')->count();
        $blogsPending = (int) Blog::whereIn('status', ['draft', 'review'])->count();

        // 5. Follow-Up Tasks Due Today
        $tasksDueToday = LeadTask::with('lead')
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', now()->toDateString())
            ->orderBy('priority', 'desc')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // 6. Manager Handover Queue
        $handoverQueue = ManagerHandover::with(['lead.company', 'assignedManager'])
            ->where('status', 'pending')
            ->orderByRaw("CASE urgency WHEN 'urgent' THEN 1 WHEN 'immediate' THEN 2 ELSE 3 END")
            ->take(5)
            ->get();

        // 7. Recent Activity Feed
        $recentActivities = LeadActivity::with(['lead', 'user'])
            ->latest('created_at')
            ->take(8)
            ->get();

        // 8. Integrations Health Status
        $integrationHealth = IntegrationSetting::all();

        return view('dashboard.index', compact(
            'days',
            'totalLeads',
            'newLeadsThisPeriod',
            'conversionRate',
            'leadsByStatus',
            'leadsBySource',
            'leadsByIndustry',
            'emailsSent',
            'emailsDelivered',
            'emailsBounced',
            'repliesReceived',
            'positiveReplies',
            'suppressionCount',
            'socialPublished',
            'socialScheduled',
            'socialFailed',
            'commentsAttention',
            'blogsPublished',
            'blogsPending',
            'tasksDueToday',
            'handoverQueue',
            'recentActivities',
            'integrationHealth'
        ));
    }
}
