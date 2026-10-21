@extends('layouts.app')

@section('title', 'Lead Management CRM')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Leads CRM Pipeline</h4>
        <p class="text-muted small mb-0">Manage prospect relationships, qualification stages, bulk assignment, and engagement scores.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-download"></i> Export Filtered CSV
        </a>
        <a href="{{ route('leads.import') }}" class="btn btn-outline-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-upload"></i> Import CSV
        </a>
        <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Add New Lead
        </a>
    </div>
</div>

<!-- Advanced Search & Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('leads.index') }}">
            <div class="row g-2 align-items-center">
                <!-- Search term -->
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search name, email, company..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-2 col-md-4">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $stKey => $stLabel)
                            <option value="{{ $stKey }}" {{ ($filters['status'] ?? '') === $stKey ? 'selected' : '' }}>
                                {{ $stLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Source Filter -->
                <div class="col-lg-2 col-md-4">
                    <select name="source_id" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        @foreach($sources as $src)
                            <option value="{{ $src->id }}" {{ ($filters['source_id'] ?? '') == $src->id ? 'selected' : '' }}>
                                {{ $src->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Industry Filter -->
                <div class="col-lg-2 col-md-4">
                    <select name="industry" class="form-select form-select-sm">
                        <option value="">All Industries</option>
                        @foreach($industries as $ind)
                            <option value="{{ $ind }}" {{ ($filters['industry'] ?? '') === $ind ? 'selected' : '' }}>
                                {{ $ind }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Assigned User Filter -->
                <div class="col-lg-2 col-md-4">
                    <select name="assigned_user_id" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ($filters['assigned_user_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter & Reset Buttons -->
                <div class="col-lg-1 col-md-4 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions & Table Card -->
<form id="bulkActionForm" action="{{ route('leads.bulk-action') }}" method="POST">
    @csrf
    <div class="card shadow-sm">
        <div class="card-header bg-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-semibold text-muted">Bulk Actions:</span>
                <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm" style="width: 160px;" onchange="toggleBulkActionInputs(this.value)">
                    <option value="">Choose Action...</option>
                    <option value="assign">Assign to User</option>
                    <option value="status">Update Status</option>
                    <option value="tag">Attach Tag</option>
                </select>

                <!-- Bulk Assign Input -->
                <select name="bulk_user_id" id="bulkUserSelect" class="form-select form-select-sm d-none" style="width: 170px;">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>

                <!-- Bulk Status Input -->
                <select name="bulk_status" id="bulkStatusSelect" class="form-select form-select-sm d-none" style="width: 150px;">
                    @foreach($statuses as $stKey => $stLabel)
                        <option value="{{ $stKey }}">{{ $stLabel }}</option>
                    @endforeach
                </select>

                <!-- Bulk Tag Input -->
                <select name="bulk_tag_ids[]" id="bulkTagSelect" class="form-select form-select-sm d-none" style="width: 160px;">
                    @foreach($tags as $tg)
                        <option value="{{ $tg->id }}">{{ $tg->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold" id="applyBulkBtn" disabled>
                    Apply to Selected
                </button>
            </div>

            <div class="text-muted small">
                Showing {{ $leads->firstItem() ?? 0 }} - {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} leads
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 38px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th>Lead / Contact</th>
                        <th>Company & Role</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Engagement</th>
                        <th>Assignee</th>
                        <th>Next Follow-Up</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>
                                <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="form-check-input lead-checkbox" onclick="updateBulkButtonState()">
                            </td>
                            <td>
                                <div class="fw-bold">
                                    <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $lead->name }}
                                    </a>
                                    @if($lead->isSuppressed())
                                        <span class="badge bg-danger ms-1" title="On permanent suppression list" style="font-size: 0.65rem;">Suppressed</span>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-envelope me-1"></i>{{ $lead->email }}
                                    @if($lead->phone)
                                        <span class="mx-1">•</span><i class="bi bi-telephone me-1"></i>{{ $lead->phone }}
                                    @endif
                                </div>
                                @if($lead->tags->isNotEmpty())
                                    <div class="d-flex gap-1 mt-1">
                                        @foreach($lead->tags as $tag)
                                            <span class="badge" style="background-color: {{ $tag->color }}15; color: {{ $tag->color }}; font-size: 0.68rem; border: 1px solid {{ $tag->color }}40;">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $lead->company?->name ?? '—' }}</div>
                                <small class="text-muted">{{ $lead->job_title ?? $lead->industry ?? 'B2B Lead' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $lead->source?->name ?? 'Direct' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-status-{{ $lead->status }} px-2 py-1">
                                    {{ $statuses[$lead->status] ?? ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                        <div class="progress-bar bg-success" style="width: {{ min(100, $lead->email_engagement_score) }}%"></div>
                                    </div>
                                    <span class="small fw-semibold text-muted">{{ $lead->email_engagement_score }}</span>
                                </div>
                            </td>
                            <td>
                                <small class="fw-semibold text-muted">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</small>
                            </td>
                            <td>
                                @if($lead->next_followup_at)
                                    <small class="{{ $lead->next_followup_at->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $lead->next_followup_at->format('M d, Y') }}
                                        @if($lead->next_followup_at->isPast())
                                            <i class="bi bi-exclamation-circle" title="Overdue"></i>
                                        @endif
                                    </small>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                                        <li><a class="dropdown-item" href="{{ route('leads.show', $lead) }}"><i class="bi bi-eye text-primary me-2"></i>View Timeline</a></li>
                                        <li><a class="dropdown-item" href="{{ route('leads.edit', $lead) }}"><i class="bi bi-pencil text-secondary me-2"></i>Edit Lead</a></li>
                                        <li>
                                            <form action="{{ route('leads.archive', $lead) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="bi bi-archive me-2"></i>Archive Lead
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Move this lead to trash?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-people text-muted fs-1 mb-2 d-block"></i>
                                <h6 class="fw-bold">No leads found</h6>
                                <p class="text-muted small mb-3">Try adjusting your filters or import new prospects.</p>
                                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">Add First Lead</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="card-footer bg-white py-2 px-3">
                {{ $leads->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</form>

<script>
    function toggleSelectAll(master) {
        document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = master.checked);
        updateBulkButtonState();
    }

    function updateBulkButtonState() {
        const anyChecked = document.querySelectorAll('.lead-checkbox:checked').length > 0;
        const select = document.getElementById('bulkActionSelect');
        document.getElementById('applyBulkBtn').disabled = !(anyChecked && select.value !== '');
    }

    function toggleBulkActionInputs(action) {
        document.getElementById('bulkUserSelect').classList.toggle('d-none', action !== 'assign');
        document.getElementById('bulkStatusSelect').classList.toggle('d-none', action !== 'status');
        document.getElementById('bulkTagSelect').classList.toggle('d-none', action !== 'tag');
        updateBulkButtonState();
    }
</script>
@endsection
