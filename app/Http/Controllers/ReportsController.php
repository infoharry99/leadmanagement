<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\EmailCampaign;
use App\Models\InboundReply;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadTask;
use App\Models\ManagerHandover;
use App\Models\SocialPost;
use App\Models\SuppressionList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $dateFrom = now()->subDays($days)->startOfDay();

        // 1. Leads by Source
        $leadsBySource = Lead::select('lead_source_id', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('lead_source_id')
            ->with('source')
            ->get();

        // 2. Leads by Status Breakdown
        $leadsByStatus = Lead::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 3. Campaign Performance
        $campaigns = EmailCampaign::with('template')
            ->latest()
            ->take(10)
            ->get();

        $totalSent = EmailCampaign::sum('sent_count');
        $totalDelivered = EmailCampaign::sum('delivered_count');
        $totalBounced = EmailCampaign::sum('bounced_count');
        $totalReplies = EmailCampaign::sum('reply_count');
        $totalUnsubscribes = EmailCampaign::sum('unsubscribe_count');

        $deliveryRate = $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) : 0;
        $bounceRate = $totalSent > 0 ? round(($totalBounced / $totalSent) * 100, 1) : 0;
        $replyRate = $totalDelivered > 0 ? round(($totalReplies / $totalDelivered) * 100, 1) : 0;

        // 4. Inbound Reply Classification Breakdown
        $replyClassifications = InboundReply::select('ai_classification', DB::raw('count(*) as count'))
            ->groupBy('ai_classification')
            ->pluck('count', 'ai_classification')
            ->toArray();

        // 5. Manager Performance
        $managers = User::whereIn('role', ['admin', 'sales_manager', 'sales_rep'])->get();
        $managerStats = [];
        foreach ($managers as $m) {
            $assignedLeads = Lead::where('assigned_user_id', $m->id)->count();
            $completedTasks = LeadTask::where('assigned_user_id', $m->id)->where('status', 'completed')->count();
            $pendingTasks = LeadTask::where('assigned_user_id', $m->id)->where('status', 'pending')->count();
            $convertedLeads = Lead::where('assigned_user_id', $m->id)->where('status', Lead::STATUS_CONVERTED)->count();

            $managerStats[] = [
                'user' => $m,
                'assigned_leads' => $assignedLeads,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'converted_leads' => $convertedLeads,
            ];
        }

        // 6. Social Media & Blog stats
        $socialPostsPublished = SocialPost::where('status', 'published')->count();
        $blogsPublished = Blog::where('status', 'published')->count();

        return view('reports.index', compact(
            'days',
            'leadsBySource',
            'leadsByStatus',
            'campaigns',
            'totalSent',
            'totalDelivered',
            'totalBounced',
            'totalReplies',
            'totalUnsubscribes',
            'deliveryRate',
            'bounceRate',
            'replyRate',
            'replyClassifications',
            'managerStats',
            'socialPostsPublished',
            'blogsPublished'
        ));
    }
}
