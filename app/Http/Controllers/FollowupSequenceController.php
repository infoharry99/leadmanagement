<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\FollowupSequence;
use App\Models\Lead;
use App\Models\SequenceEnrollment;
use App\Models\SequenceStep;
use App\Services\SequenceEngineService;
use Illuminate\Http\Request;

class FollowupSequenceController extends Controller
{
    public function __construct(protected SequenceEngineService $sequenceEngine)
    {
    }

    public function index()
    {
        $sequences = FollowupSequence::with(['createdBy'])
            ->withCount(['steps', 'enrollments'])
            ->latest()
            ->paginate(10);

        return view('sequences.index', compact('sequences'));
    }

    public function create()
    {
        $templates = EmailTemplate::all();
        return view('sequences.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'send_window_start' => 'required',
            'send_window_end' => 'required',
            'timezone' => 'required|string',
            'exclude_weekends' => 'nullable|boolean',
            'steps' => 'required|array|min:1',
            'steps.*.step_order' => 'required|integer',
            'steps.*.phase' => 'required|in:phase_a,phase_b,phase_c',
            'steps.*.delay_days' => 'required|integer|min:0',
            'steps.*.subject' => 'required|string',
            'steps.*.body_template' => 'required|string',
        ]);

        $sequence = FollowupSequence::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'send_window_start' => $validated['send_window_start'],
            'send_window_end' => $validated['send_window_end'],
            'timezone' => $validated['timezone'],
            'exclude_weekends' => $request->boolean('exclude_weekends', true),
            'exclude_holidays' => true,
            'created_by_id' => auth()->id(),
        ]);

        foreach ($validated['steps'] as $s) {
            SequenceStep::create([
                'followup_sequence_id' => $sequence->id,
                'step_order' => $s['step_order'],
                'phase' => $s['phase'],
                'delay_days' => $s['delay_days'],
                'subject' => $s['subject'],
                'body_template' => $s['body_template'],
            ]);
        }

        return redirect()->route('sequences.show', $sequence)->with('success', "Follow-up sequence '{$sequence->name}' created.");
    }

    public function show(FollowupSequence $sequence)
    {
        $sequence->load(['steps', 'createdBy']);
        $enrollments = $sequence->enrollments()->with(['lead.company', 'currentStep'])->paginate(15);
        $eligibleLeads = Lead::active()->whereDoesntHave('sequenceEnrollments', function ($q) use ($sequence) {
            $q->where('followup_sequence_id', $sequence->id);
        })->take(50)->get();

        return view('sequences.show', compact('sequence', 'enrollments', 'eligibleLeads'));
    }

    public function enroll(Request $request, FollowupSequence $sequence)
    {
        $leadIds = $request->input('lead_ids', []);
        if (empty($leadIds)) {
            return back()->with('error', 'Please select at least one lead to enroll.');
        }

        $enrolled = 0;
        foreach ($leadIds as $id) {
            $lead = Lead::find($id);
            if ($lead && $lead->isEligibleForMarketing()) {
                $this->sequenceEngine->enrollLead($lead, $sequence);
                $enrolled++;
            }
        }

        return back()->with('success', "Successfully enrolled {$enrolled} leads into sequence.");
    }

    public function pauseEnrollment(SequenceEnrollment $enrollment)
    {
        $enrollment->update(['status' => 'paused']);
        return back()->with('success', 'Enrollment paused.');
    }

    public function resumeEnrollment(SequenceEnrollment $enrollment)
    {
        $enrollment->update(['status' => 'active', 'next_run_at' => now()->addMinutes(10)]);
        return back()->with('success', 'Enrollment resumed.');
    }
}
