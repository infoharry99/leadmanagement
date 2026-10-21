@extends('layouts.app')

@section('title', 'Follow-Up Sequences Engine')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Automated Follow-Up Sequences Engine</h4>
        <p class="text-muted small mb-0">Multi-phase automated nurturing sequences with weekend exclusions and reply-triggered pausing.</p>
    </div>
    <a href="{{ route('sequences.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
        <i class="bi bi-plus-lg"></i> Build New Sequence
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Sequence Name</th>
                    <th>Configured Stages</th>
                    <th>Sending Window</th>
                    <th>Exclusions</th>
                    <th>Enrolled Leads</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sequences as $seq)
                    <tr>
                        <td>
                            <div class="fw-bold">
                                <a href="{{ route('sequences.show', $seq) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $seq->name }}
                                </a>
                            </div>
                            <small class="text-muted">{{ Str::limit($seq->description, 50) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">
                                {{ $seq->steps_count }} configured steps
                            </span>
                        </td>
                        <td>
                            <small class="text-dark fw-semibold">
                                <i class="bi bi-clock me-1"></i>{{ substr($seq->send_window_start, 0, 5) }} - {{ substr($seq->send_window_end, 0, 5) }} ({{ $seq->timezone }})
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-light text-muted border">
                                {{ $seq->exclude_weekends ? 'Weekends Excluded' : 'All 7 Days' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $seq->enrollments_count }}</span> active leads
                        </td>
                        <td>
                            <span class="badge bg-{{ $seq->status === 'active' ? 'success' : 'secondary' }} px-2 py-1">
                                {{ ucfirst($seq->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('sequences.show', $seq) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-gear-fill me-1"></i> Manage Sequence
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-diagram-3 text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold">No follow-up sequences configured</h6>
                            <a href="{{ route('sequences.create') }}" class="btn btn-primary btn-sm mt-2">Build First Sequence</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
