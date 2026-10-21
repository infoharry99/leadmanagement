@extends('layouts.app')

@section('title', 'Sequence: ' . $sequence->name)

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <a href="{{ route('sequences.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1">
            <i class="bi bi-arrow-left"></i> Back to Sequences
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $sequence->name }}</h4>
            <span class="badge bg-{{ $sequence->status === 'active' ? 'success' : 'secondary' }} px-2 py-1">
                {{ ucfirst($sequence->status) }}
            </span>
        </div>
        <p class="text-muted small mb-0">{{ $sequence->description }}</p>
    </div>

    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#enrollLeadModal">
        <i class="bi bi-person-plus-fill me-1"></i> Enroll Leads
    </button>
</div>

<div class="row g-4 mb-4">
    <!-- Sequence Parameters Card -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold small text-uppercase text-muted">
                <i class="bi bi-clock-history me-1"></i>Execution Window
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Sending Hours:</span>
                    <strong class="text-dark">{{ substr($sequence->send_window_start, 0, 5) }} - {{ substr($sequence->send_window_end, 0, 5) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Timezone:</span>
                    <span>{{ $sequence->timezone }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Exclude Weekends:</span>
                    <span class="badge bg-{{ $sequence->exclude_weekends ? 'success' : 'secondary' }}">
                        {{ $sequence->exclude_weekends ? 'Active (Mon-Fri only)' : 'Inactive' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Steps:</span>
                    <span>{{ $sequence->steps->count() }} stages</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Stage Steps Preview -->
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold small text-uppercase text-muted">
                <i class="bi bi-diagram-3 me-1"></i>Sequence Stages Roadmap
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-nowrap overflow-auto gap-2 pb-2">
                    @foreach($sequence->steps as $step)
                        <div class="p-2.5 border rounded-3 bg-light" style="min-width: 170px; font-size: 0.8rem;">
                            <div class="fw-bold text-primary mb-1">Step #{{ $step->step_order }}</div>
                            <span class="badge bg-white text-dark border mb-1" style="font-size: 0.65rem;">
                                {{ ucfirst(str_replace('_', ' ', $step->phase)) }} (+{{ $step->delay_days }}d)
                            </span>
                            <div class="text-truncate text-secondary" title="{{ $step->subject }}">{{ $step->subject }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrolled Leads Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-people text-primary me-2"></i>Enrolled Leads ({{ $enrollments->total() }})</span>
        <span class="text-muted small">Showing {{ $enrollments->firstItem() ?? 0 }} - {{ $enrollments->lastItem() ?? 0 }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Lead Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Current Stage</th>
                    <th>Last Sent</th>
                    <th>Next Run At</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enr)
                    <tr>
                        <td>
                            @if($enr->lead)
                                <a href="{{ route('leads.show', $enr->lead) }}" class="fw-bold text-decoration-none text-dark hover-primary">
                                    {{ $enr->lead->name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $enr->lead?->email }}</td>
                        <td>{{ $enr->lead?->company?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                Step #{{ $enr->currentStep?->step_order ?? 1 }} ({{ $enr->currentStep?->phase ?? 'Phase A' }})
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $enr->last_sent_at ? $enr->last_sent_at->diffForHumans() : 'Never' }}</small></td>
                        <td>
                            <small class="fw-semibold {{ $enr->next_run_at && $enr->next_run_at->isPast() ? 'text-danger' : 'text-primary' }}">
                                {{ $enr->next_run_at ? $enr->next_run_at->format('M d, Y H:i') : '—' }}
                            </small>
                        </td>
                        <td>
                            @php
                                $statusBadge = match($enr->status) {
                                    'active' => 'bg-success',
                                    'paused' => 'bg-warning text-dark',
                                    'completed' => 'bg-primary',
                                    'replied_stopped' => 'bg-info text-dark',
                                    'unsubscribed_stopped' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} px-2 py-1">
                                {{ ucfirst(str_replace('_', ' ', $enr->status)) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if($enr->status === 'active')
                                <form action="{{ route('sequences.enrollments.pause', $enr) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Pause sequence for this lead">
                                        <i class="bi bi-pause-fill"></i>
                                    </button>
                                </form>
                            @elseif($enr->status === 'paused')
                                <form action="{{ route('sequences.enrollments.resume', $enr) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Resume sequence">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No leads currently enrolled in this sequence.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($enrollments->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $enrollments->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Modal: Enroll Leads -->
<div class="modal fade" id="enrollLeadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('sequences.enroll', $sequence) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Enroll Eligible Leads into Sequence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Select active leads to enroll into <strong>{{ $sequence->name }}</strong>:</p>
                    <div style="max-height: 350px; overflow-y: auto;" class="border rounded p-2">
                        @forelse($eligibleLeads as $lead)
                            <div class="form-check p-2 border-bottom">
                                <input class="form-check-input" type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" id="lead_{{ $lead->id }}">
                                <label class="form-check-label d-flex justify-content-between w-100 pe-3" for="lead_{{ $lead->id }}">
                                    <span><strong>{{ $lead->name }}</strong> ({{ $lead->email }})</span>
                                    <span class="text-muted small">{{ $lead->company?->name }} • {{ $lead->industry }}</span>
                                </label>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">All active leads are already enrolled or suppressed.</div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" {{ $eligibleLeads->isEmpty() ? 'disabled' : '' }}>Enroll Selected Leads</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
