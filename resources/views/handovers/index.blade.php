@extends('layouts.app')

@section('title', 'Manager Handover Queue')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Sales Manager Handover Queue</h4>
        <p class="text-muted small mb-0">High-intent replies and qualified leads prioritized for sales leadership engagement.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('handovers.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-dark' : 'btn-light border' }}">
            Pending Only
        </a>
        <a href="{{ route('handovers.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-light border' }}">
            All Handovers
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Lead Contact</th>
                    <th>Company & Role</th>
                    <th>Handover Reason & Notes</th>
                    <th>Urgency</th>
                    <th>Assigned Manager</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($handovers as $h)
                    <tr>
                        <td>
                            <div class="fw-bold">
                                @if($h->lead)
                                    <a href="{{ route('leads.show', $h->lead) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $h->lead->name }}
                                    </a>
                                @else
                                    <span>Direct Contact</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $h->lead?->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $h->lead?->company?->name ?? '—' }}</div>
                            <small class="text-muted">{{ $h->lead?->job_title ?? 'Lead' }}</small>
                        </td>
                        <td style="max-width: 280px;">
                            <span class="badge bg-light text-dark border mb-1">
                                {{ ucfirst(str_replace('_', ' ', $h->handover_reason)) }}
                            </span>
                            <div class="text-truncate text-secondary small" title="{{ $h->handoff_notes }}">
                                {{ $h->handoff_notes }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $h->urgency === 'urgent' ? 'danger' : ($h->urgency === 'immediate' ? 'danger' : 'warning text-dark') }} px-2 py-1">
                                {{ ucfirst($h->urgency) }}
                            </span>
                        </td>
                        <td>
                            <span class="small fw-semibold text-primary">{{ $h->assignedManager?->name ?? 'Unassigned' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $h->status === 'converted' ? 'success' : ($h->status === 'acknowledged' ? 'primary' : 'warning text-dark') }}">
                                {{ ucfirst(str_replace('_', ' ', $h->status)) }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $h->created_at->diffForHumans() }}</small></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                @if($h->status === 'pending')
                                    <form action="{{ route('handovers.acknowledge', $h) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="Accept ownership of this lead">
                                            Acknowledge
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#reassignModal_{{ $h->id }}" title="Reassign to another manager">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>

                            <!-- Reassign Modal -->
                            <div class="modal fade" id="reassignModal_{{ $h->id }}" tabindex="-1">
                                <div class="modal-dialog text-start">
                                    <form action="{{ route('handovers.reassign', $h) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Reassign Lead Handover</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label small fw-semibold">Select Manager / Rep</label>
                                                <select name="assigned_manager_id" class="form-select" required>
                                                    @foreach($users as $u)
                                                        <option value="{{ $u->id }}" {{ $h->assigned_manager_id == $u->id ? 'selected' : '' }}>
                                                            {{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role)) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Reassign</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-person-check text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold">No handovers found in this view</h6>
                            <p class="small text-muted mb-0">Leads with positive replies or qualification requests will appear here automatically.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($handovers->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $handovers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
