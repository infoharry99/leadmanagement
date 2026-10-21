<?php

namespace App\Http\Controllers;

use App\Models\LeadTask;
use App\Models\User;
use Illuminate\Http\Request;

class TaskManagerController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $priority = $request->input('priority');
        $assignedUserId = $request->input('assigned_user_id');

        $query = LeadTask::with(['lead.company', 'assignedUser', 'createdBy']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($priority) {
            $query->where('priority', $priority);
        }
        if ($assignedUserId) {
            $query->where('assigned_user_id', $assignedUserId);
        }

        $tasks = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->paginate(15);

        $users = User::all();

        return view('tasks.index', compact('tasks', 'status', 'priority', 'assignedUserId', 'users'));
    }

    public function complete(LeadTask $task)
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($task->lead) {
            $task->lead->recordActivity('task_completed', "Task completed: '{$task->title}'");
        }

        return back()->with('success', 'Task marked as completed.');
    }

    public function updateStatus(Request $request, LeadTask $task)
    {
        $status = $request->input('status', 'in_progress');
        $task->update([
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);

        return back()->with('success', "Task status changed to {$status}.");
    }
}
