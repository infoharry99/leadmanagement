<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\LeadTask;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leadService)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'status', 'source_id', 'industry', 'assigned_user_id', 'tag_id', 'date_from', 'date_to', 'archived'
        ]);

        $leads = $this->leadService->getFilteredQuery($filters)->paginate(15)->withQueryString();

        $sources = LeadSource::where('is_active', true)->get();
        $tags = LeadTag::all();
        $users = User::all();
        $industries = Lead::select('industry')->whereNotNull('industry')->distinct()->pluck('industry');
        $statuses = Lead::getStatuses();

        return view('leads.index', compact('leads', 'filters', 'sources', 'tags', 'users', 'industries', 'statuses'));
    }

    public function create()
    {
        $sources = LeadSource::where('is_active', true)->get();
        $tags = LeadTag::all();
        $users = User::all();
        $companies = Company::orderBy('name')->get();
        $statuses = Lead::getStatuses();

        return view('leads.create', compact('sources', 'tags', 'users', 'companies', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'status' => 'nullable|string|in:' . implode(',', array_keys(Lead::getStatuses())),
            'assigned_user_id' => 'nullable|exists:users,id',
            'consent_basis' => 'nullable|string',
            'consent_source' => 'nullable|string',
            'notes' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:lead_tags,id',
        ]);

        $lead = $this->leadService->createLead($validated);

        return redirect()->route('leads.show', $lead)->with('success', "Lead {$lead->name} created successfully.");
    }

    public function show(Lead $lead)
    {
        $lead->load([
            'company',
            'assignedUser',
            'source',
            'tags',
            'activities.user',
            'notes.user',
            'tasks.assignedUser',
            'campaignRecipients.campaign',
            'sequenceEnrollments.sequence',
            'inboundReplies',
            'handovers.assignedManager'
        ]);

        $statuses = Lead::getStatuses();
        $users = User::all();
        $tags = LeadTag::all();

        return view('leads.show', compact('lead', 'statuses', 'users', 'tags'));
    }

    public function edit(Lead $lead)
    {
        $sources = LeadSource::where('is_active', true)->get();
        $tags = LeadTag::all();
        $users = User::all();
        $companies = Company::orderBy('name')->get();
        $statuses = Lead::getStatuses();

        return view('leads.edit', compact('lead', 'sources', 'tags', 'users', 'companies', 'statuses'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'status' => 'required|string|in:' . implode(',', array_keys(Lead::getStatuses())),
            'assigned_user_id' => 'nullable|exists:users,id',
            'consent_basis' => 'nullable|string',
            'consent_source' => 'nullable|string',
            'notes' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:lead_tags,id',
        ]);

        $this->leadService->updateLead($lead, $validated);

        return redirect()->route('leads.show', $lead)->with('success', "Lead updated successfully.");
    }

    public function destroy(Lead $lead)
    {
        $lead->recordActivity('lead_deleted', 'Lead soft-deleted');
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead moved to trash.');
    }

    public function archive(Lead $lead)
    {
        $lead->update(['is_archived' => true]);
        $lead->recordActivity('lead_archived', 'Lead moved to archive');

        return back()->with('success', 'Lead archived.');
    }

    public function restore(int $id)
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        if ($lead->trashed()) {
            $lead->restore();
        }
        $lead->update(['is_archived' => false]);
        $lead->recordActivity('lead_restored', 'Lead restored to active CRM pipeline');

        return back()->with('success', 'Lead restored.');
    }

    public function addNote(Request $request, Lead $lead)
    {
        $request->validate(['note' => 'required|string']);

        $note = LeadNote::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'note' => $request->input('note'),
        ]);

        $lead->recordActivity('note_added', 'Added a private note');

        return back()->with('success', 'Note added to lead timeline.');
    }

    public function addTask(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $task = LeadTask::create([
            'lead_id' => $lead->id,
            'assigned_user_id' => $validated['assigned_user_id'] ?? auth()->id(),
            'created_by_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'priority' => $validated['priority'],
            'status' => 'pending',
        ]);

        $lead->recordActivity('task_created', "Created task: '{$task->title}' (Due: {$task->due_date->format('M d, Y')})");

        return back()->with('success', 'Follow-up task created.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $leadIds = $request->input('lead_ids', []);

        if (empty($leadIds)) {
            return back()->with('error', 'Please select at least one lead.');
        }

        if ($action === 'assign') {
            $userId = (int) $request->input('bulk_user_id');
            $count = $this->leadService->bulkAssign($leadIds, $userId);
            return back()->with('success', "Assigned {$count} leads.");
        }

        if ($action === 'status') {
            $status = $request->input('bulk_status');
            $count = $this->leadService->bulkUpdateStatus($leadIds, $status);
            return back()->with('success', "Updated status for {$count} leads.");
        }

        if ($action === 'tag') {
            $tagIds = $request->input('bulk_tag_ids', []);
            $count = $this->leadService->bulkAddTags($leadIds, $tagIds);
            return back()->with('success', "Added tags to {$count} leads.");
        }

        return back()->with('error', 'Invalid bulk action selected.');
    }
}
