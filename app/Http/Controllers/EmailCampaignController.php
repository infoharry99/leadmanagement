<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignEmailJob;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\SuppressionList;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailCampaignController extends Controller
{
    public function __construct(protected EmailService $emailService)
    {
    }

    public function index()
    {
        $campaigns = EmailCampaign::with(['template', 'createdBy'])
            ->latest()
            ->paginate(10);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $templates = EmailTemplate::all();
        $industries = Lead::select('industry')->whereNotNull('industry')->distinct()->pluck('industry');
        $sources = LeadSource::where('is_active', true)->get();
        $statuses = Lead::getStatuses();

        return view('campaigns.create', compact('templates', 'industries', 'sources', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_template_id' => 'required|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'from_name' => 'required|string|max:255',
            'from_email' => 'required|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'scheduled_at' => 'nullable|date',
            'industry' => 'nullable|string',
            'lead_status' => 'nullable|string',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
        ]);

        $filters = [
            'industry' => $validated['industry'] ?? null,
            'status' => $validated['lead_status'] ?? null,
            'lead_source_id' => $validated['lead_source_id'] ?? null,
        ];

        $campaign = EmailCampaign::create([
            'name' => $validated['name'],
            'email_template_id' => $validated['email_template_id'],
            'subject' => $validated['subject'],
            'from_name' => $validated['from_name'],
            'from_email' => $validated['from_email'],
            'reply_to' => $validated['reply_to'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => !empty($validated['scheduled_at']) ? 'scheduled' : 'draft',
            'target_segment_filter' => array_filter($filters),
            'created_by_id' => auth()->id(),
        ]);

        // Build Audience Recipients from Leads
        $leadQuery = Lead::active();
        if (!empty($filters['industry'])) {
            $leadQuery->where('industry', $filters['industry']);
        }
        if (!empty($filters['status'])) {
            $leadQuery->where('status', $filters['status']);
        }
        if (!empty($filters['lead_source_id'])) {
            $leadQuery->where('lead_source_id', $filters['lead_source_id']);
        }

        $leads = $leadQuery->get();
        $recipientCount = 0;

        foreach ($leads as $lead) {
            // Check suppression
            if ($lead->isSuppressed()) {
                continue;
            }

            CampaignRecipient::firstOrCreate(
                [
                    'email_campaign_id' => $campaign->id,
                    'lead_id' => $lead->id,
                ],
                [
                    'email' => $lead->email,
                    'status' => 'pending',
                    'idempotency_key' => hash('sha256', "camp_{$campaign->id}_lead_{$lead->id}"),
                ]
            );
            $recipientCount++;
        }

        $campaign->update(['total_recipients' => $recipientCount]);

        return redirect()->route('campaigns.show', $campaign)->with('success', "Campaign '{$campaign->name}' created with {$recipientCount} eligible recipients.");
    }

    public function show(EmailCampaign $campaign)
    {
        $campaign->load(['template', 'createdBy', 'recipients.lead']);
        $recipients = $campaign->recipients()->with('lead.company')->paginate(20);

        return view('campaigns.show', compact('campaign', 'recipients'));
    }

    public function dispatchCampaign(EmailCampaign $campaign)
    {
        if ($campaign->status === 'completed') {
            return back()->with('error', 'This campaign has already completed.');
        }

        $campaign->update(['status' => 'running']);

        $pendingRecipients = $campaign->recipients()->where('status', 'pending')->get();

        foreach ($pendingRecipients as $recipient) {
            $recipient->update(['status' => 'queued']);
            SendCampaignEmailJob::dispatch($recipient);
        }

        return back()->with('success', "Dispatched {$pendingRecipients->count()} campaign emails to background queue workers.");
    }

    public function pause(EmailCampaign $campaign)
    {
        $campaign->update(['status' => 'paused']);
        return back()->with('success', 'Campaign paused.');
    }

    public function cancel(EmailCampaign $campaign)
    {
        $campaign->update(['status' => 'cancelled']);
        return back()->with('success', 'Campaign cancelled.');
    }
}
