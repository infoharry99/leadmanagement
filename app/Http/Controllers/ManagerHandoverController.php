<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\ManagerHandover;
use App\Models\User;
use Illuminate\Http\Request;

class ManagerHandoverController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $handovers = ManagerHandover::with(['lead.company', 'assignedManager', 'reply'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        $users = User::whereIn('role', ['admin', 'sales_manager', 'sales_rep'])->get();

        return view('handovers.index', compact('handovers', 'status', 'users'));
    }

    public function acknowledge(ManagerHandover $handover)
    {
        $handover->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'assigned_manager_id' => auth()->id(),
        ]);

        if ($handover->lead) {
            $handover->lead->recordActivity('manager_assigned', "Manager handover accepted by " . auth()->user()->name);
        }

        return back()->with('success', 'Handover acknowledged. You are now the assigned manager.');
    }

    public function reassign(Request $request, ManagerHandover $handover)
    {
        $userId = (int) $request->input('assigned_manager_id');
        $user = User::findOrFail($userId);

        $handover->update([
            'assigned_manager_id' => $userId,
            'handoff_notes' => $handover->handoff_notes . "\n[Reassigned to " . $user->name . " by " . auth()->user()->name . " at " . now()->toDateTimeString() . "]",
        ]);

        if ($handover->lead) {
            $handover->lead->update(['assigned_user_id' => $userId]);
            $handover->lead->recordActivity('manager_assigned', "Lead reassigned to {$user->name}");
        }

        return back()->with('success', "Lead handover reassigned to {$user->name}.");
    }

    public function updateStatus(Request $request, ManagerHandover $handover)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,acknowledged,in_progress,converted,closed',
            'handoff_notes' => 'nullable|string',
            'lead_status' => 'nullable|string|in:' . implode(',', array_keys(Lead::getStatuses())),
        ]);

        $handover->update([
            'status' => $validated['status'],
            'handoff_notes' => $validated['handoff_notes'] ?? $handover->handoff_notes,
        ]);

        if (!empty($validated['lead_status']) && $handover->lead) {
            $handover->lead->update(['status' => $validated['lead_status']]);
            $handover->lead->recordActivity('status_change', "Status updated to " . ucfirst($validated['lead_status']) . " during manager handover");
        }

        return back()->with('success', 'Handover record updated.');
    }
}
