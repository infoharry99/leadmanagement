@extends('layouts.app')

@section('title', 'Tasks & Reminders')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Team Follow-Up Tasks & Reminders</h4>
        <p class="text-muted small mb-0">Track due dates, overdue escalations, and rep assignments across all pipeline leads.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-dark' : 'btn-light border' }}">
            Pending Only
        </a>
        <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="btn btn-sm {{ $status === 'completed' ? 'btn-dark' : 'btn-light border' }}">
            Completed
        </a>
        <a href="{{ route('tasks.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-light border' }}">
            All Tasks
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Task Description</th>
                    <th>Associated Lead</th>
                    <th>Due Date</th>
                    <th>Priority</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $t)
                    <tr>
                        <td>
                            @if($t->status !== 'completed')
                                <form action="{{ route('tasks.complete', $t) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle p-1" style="width: 26px; height: 26px;" title="Mark Completed">
                                        <i class="bi bi-check" style="font-size: 0.8rem;"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold {{ $t->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                {{ $t->title }}
                            </div>
                            @if($t->description)
                                <small class="text-muted">{{ Str::limit($t->description, 60) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($t->lead)
                                <a href="{{ route('leads.show', $t->lead) }}" class="fw-semibold text-decoration-none text-dark hover-primary">
                                    {{ $t->lead->name }}
                                </a>
                                <small class="text-muted d-block">{{ $t->lead->company?->name }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $t->isOverdue() ? 'text-danger fw-bold' : 'text-dark' }}">
                                {{ $t->due_date->format('M d, Y • h:i A') }}
                                @if($t->isOverdue())
                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">OVERDUE</span>
                                @endif
                            </span>
                        </td>
                        <td>
                            @php
                                $priorityBadge = match($t->priority) {
                                    'urgent' => 'bg-danger',
                                    'high' => 'bg-warning text-dark',
                                    'medium' => 'bg-info text-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $priorityBadge }} px-2 py-1">
                                {{ ucfirst($t->priority) }}
                            </span>
                        </td>
                        <td>
                            <small class="fw-semibold text-muted">{{ $t->assignedUser?->name ?? 'Unassigned' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $t->status === 'completed' ? 'success' : 'light text-dark border' }}">
                                {{ ucfirst($t->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if($t->status !== 'completed')
                                <form action="{{ route('tasks.complete', $t) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Done</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-circle text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold">No tasks found</h6>
                            <p class="small text-muted mb-0">Follow-up tasks created from leads or reply handovers will appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $tasks->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
