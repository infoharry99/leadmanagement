@extends('layouts.app')

@section('title', 'Lead Profile: ' . $lead->name)

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <a href="{{ route('leads.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1">
            <i class="bi bi-arrow-left"></i> Back to Leads List
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $lead->name }}</h4>
            <span class="badge badge-status-{{ $lead->status }} px-2 py-1">
                {{ $statuses[$lead->status] ?? ucfirst($lead->status) }}
            </span>
            @if($lead->isSuppressed())
                <span class="badge bg-danger" title="Suppressed from marketing communications">Suppressed</span>
            @endif
        </div>
        <p class="text-muted small mb-0">{{ $lead->job_title ?? 'Contact' }} at <strong>{{ $lead->company?->name ?? 'Direct Prospect' }}</strong></p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-secondary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-pencil"></i> Edit Lead
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="bi bi-alarm"></i> Add Task
        </button>
        <button type="button" class="btn btn-outline-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="bi bi-file-earmark-plus"></i> Add Note
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Lead & Company Profile -->
    <div class="col-lg-4">
        <!-- Lead Overview Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-badge text-primary me-2"></i>Contact Details</span>
                <span class="badge bg-light text-dark border">Score: {{ $lead->email_engagement_score }}/100</span>
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 small">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Email:</span>
                        <a href="mailto:{{ $lead->email }}" class="fw-semibold text-decoration-none">{{ $lead->email }}</a>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Phone:</span>
                        <span class="fw-semibold">{{ $lead->phone ?? '—' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Industry:</span>
                        <span class="fw-semibold">{{ $lead->industry ?? '—' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Location:</span>
                        <span class="fw-semibold">{{ implode(', ', array_filter([$lead->city, $lead->country])) ?: '—' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Lead Source:</span>
                        <span class="badge bg-light text-dark border">{{ $lead->source?->name ?? 'Direct' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Account Owner:</span>
                        <span class="fw-semibold text-primary">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Last Contacted:</span>
                        <span>{{ $lead->last_contacted_at ? $lead->last_contacted_at->diffForHumans() : 'Never' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Next Follow-Up:</span>
                        <span class="{{ $lead->next_followup_at && $lead->next_followup_at->isPast() ? 'text-danger fw-bold' : '' }}">
                            {{ $lead->next_followup_at ? $lead->next_followup_at->format('M d, Y H:i') : 'None scheduled' }}
                        </span>
                    </li>
                </ul>

                @if($lead->tags->isNotEmpty())
                    <hr class="my-3">
                    <div class="small fw-semibold text-muted mb-2">Attached Tags:</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($lead->tags as $t)
                            <span class="badge" style="background-color: {{ $t->color }}15; color: {{ $t->color }}; border: 1px solid {{ $t->color }}40;">
                                {{ $t->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Company Details Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-building text-info me-2"></i>Company Relationship
            </div>
            <div class="card-body p-3">
                @if($lead->company)
                    <div class="fw-bold fs-6 mb-1">{{ $lead->company->name }}</div>
                    <div class="text-muted small mb-3">
                        @if($lead->company->website)
                            <a href="{{ $lead->company->website }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-globe me-1"></i>{{ $lead->company->website }}
                            </a>
                        @endif
                    </div>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Domain:</span>
                            <span class="fw-semibold">{{ $lead->company->domain ?? '—' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Team Size:</span>
                            <span>{{ $lead->company->size ?? '—' }} employees</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Other Leads in Co:</span>
                            <span class="badge bg-secondary">{{ $lead->company->leads()->where('id', '!=', $lead->id)->count() }} colleagues</span>
                        </li>
                    </ul>
                @else
                    <div class="text-muted small">No company associated with this lead.</div>
                @endif
            </div>
        </div>

        <!-- Consent & Compliance Audit Card -->
        <div class="card shadow-sm border-info">
            <div class="card-header bg-info bg-opacity-10 fw-bold small text-dark">
                <i class="bi bi-shield-check text-success me-1"></i>Consent & Compliance Basis
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Lawful Basis:</span>
                    <span class="fw-bold">{{ ucfirst(str_replace('_', ' ', $lead->consent_basis)) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Source:</span>
                    <span>{{ $lead->consent_source ?? 'Direct' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Recorded:</span>
                    <span>{{ $lead->consent_at ? $lead->consent_at->format('M d, Y') : $lead->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Tabs for Timeline, Notes, Tasks, Emails -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 px-3 border-0" id="leadTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3 fw-semibold small" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button">
                            <i class="bi bi-clock-history me-1"></i> Activity Timeline ({{ $lead->activities->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold small" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button">
                            <i class="bi bi-sticky me-1"></i> Private Notes ({{ $lead->notes->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold small" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button">
                            <i class="bi bi-check2-circle me-1"></i> Tasks ({{ $lead->tasks->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold small" id="emails-tab" data-bs-toggle="tab" data-bs-target="#emails" type="button">
                            <i class="bi bi-envelope me-1"></i> Inbound Replies ({{ $lead->inboundReplies->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold small" id="sequences-tab" data-bs-toggle="tab" data-bs-target="#sequences" type="button">
                            <i class="bi bi-diagram-3 me-1"></i> Sequences ({{ $lead->sequenceEnrollments->count() }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="leadTabsContent">
                    <!-- Tab 1: Timeline Feed -->
                    <div class="tab-pane fade show active" id="timeline" role="tabpanel">
                        <div class="position-relative ps-4" style="border-left: 2px solid #E2E8F0;">
                            @forelse($lead->activities as $act)
                                <div class="position-relative mb-4">
                                    <!-- Bullet -->
                                    <div class="position-absolute rounded-circle bg-white border border-2 border-primary" style="width: 12px; height: 12px; left: -25px; top: 4px;"></div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-light text-dark border small">
                                            {{ ucfirst(str_replace('_', ' ', $act->type)) }}
                                        </span>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            {{ $act->created_at->format('M d, Y • h:i A') }} ({{ $act->created_at->diffForHumans() }})
                                        </span>
                                    </div>
                                    <p class="mb-1 text-dark small fw-medium">{{ $act->description }}</p>
                                    @if($act->user)
                                        <small class="text-muted">By: {{ $act->user->name }}</small>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">No activity logged yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 2: Notes -->
                    <div class="tab-pane fade" id="notes" role="tabpanel">
                        <form action="{{ route('leads.notes.store', $lead) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-2">
                                <textarea name="note" class="form-control" rows="3" required placeholder="Type a note about this contact, conversation, or requirement..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Add Note</button>
                        </form>

                        <div class="d-flex flex-column gap-3">
                            @forelse($lead->notes as $note)
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold small text-primary">{{ $note->user?->name ?? 'User' }}</span>
                                        <span class="text-muted small" style="font-size: 0.72rem;">{{ $note->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mb-0 small text-dark">{{ $note->note }}</p>
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">No notes added yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 3: Tasks -->
                    <div class="tab-pane fade" id="tasks" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold small">Follow-Up Action Items</span>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                <i class="bi bi-plus-lg"></i> New Task
                            </button>
                        </div>

                        <div class="list-group list-group-flush">
                            @forelse($lead->tasks as $task)
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold {{ $task->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $task->title }}
                                        </div>
                                        <small class="text-muted d-block">
                                            Due: <strong>{{ $task->due_date->format('M d, Y') }}</strong> • Assigned to: {{ $task->assignedUser?->name ?? 'Unassigned' }}
                                        </small>
                                        <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning text-dark' : 'secondary') }} mt-1" style="font-size: 0.65rem;">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    </div>
                                    <div>
                                        @if($task->status !== 'completed')
                                            <form action="{{ route('tasks.complete', $task) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    Mark Done
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">No tasks linked to this lead.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 4: Inbound Replies -->
                    <div class="tab-pane fade" id="emails" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            @forelse($lead->inboundReplies as $reply)
                                <div class="p-3 border rounded-3 bg-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="fw-bold">{{ $reply->subject }}</span>
                                            <div class="text-muted small">From: {{ $reply->from_email }}</div>
                                        </div>
                                        <span class="badge bg-{{ in_array($reply->ai_classification, ['interested', 'meeting_request']) ? 'success' : 'secondary' }}">
                                            AI Intent: {{ ucfirst(str_replace('_', ' ', $reply->ai_classification)) }}
                                        </span>
                                    </div>
                                    <div class="p-2 bg-light rounded text-dark small mb-2">
                                        {{ nl2br(e($reply->body)) }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $reply->received_at->format('M d, Y • h:i A') }}</small>
                                        <a href="{{ route('inbox.show', $reply) }}" class="btn btn-sm btn-outline-primary">
                                            Open in Inbox
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">No inbound email replies received yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 5: Sequence Enrollments -->
                    <div class="tab-pane fade" id="sequences" role="tabpanel">
                        @forelse($lead->sequenceEnrollments as $enr)
                            <div class="p-3 border rounded-3 mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $enr->sequence?->name }}</div>
                                    <small class="text-muted">
                                        Current Step: #{{ $enr->currentStep?->step_order ?? 1 }} ({{ $enr->currentStep?->phase ?? 'Phase A' }})
                                        • Next Send: <strong>{{ $enr->next_run_at ? $enr->next_run_at->format('M d, Y H:i') : 'None' }}</strong>
                                    </small>
                                </div>
                                <span class="badge bg-{{ $enr->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $enr->status)) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4">Lead is not enrolled in any automated follow-up sequences.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Task -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('leads.tasks.store', $lead) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Schedule Task for {{ $lead->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Discovery call follow-up">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Due Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="due_date" class="form-control" required value="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign To</label>
                        <select name="assigned_user_id" class="form-select">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $lead->assigned_user_id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Details / Instructions</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Note -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('leads.notes.store', $lead) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Note for {{ $lead->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="note" class="form-control" rows="4" required placeholder="Type notes here..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
