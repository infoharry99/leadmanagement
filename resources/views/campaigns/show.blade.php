@extends('layouts.app')

@section('title', 'Campaign: ' . $campaign->name)

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <a href="{{ route('campaigns.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1">
            <i class="bi bi-arrow-left"></i> Back to Campaigns
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $campaign->name }}</h4>
            <span class="badge bg-{{ $campaign->status === 'completed' ? 'success' : ($campaign->status === 'running' ? 'primary' : 'secondary') }} px-2 py-1">
                {{ ucfirst($campaign->status) }}
            </span>
        </div>
        <p class="text-muted small mb-0">Subject: <em>"{{ $campaign->subject }}"</em> • From: <strong>{{ $campaign->from_name }}</strong> ({{ $campaign->from_email }})</p>
    </div>

    <div class="d-flex gap-2">
        @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
            <form action="{{ route('campaigns.dispatch', $campaign) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm shadow-sm d-flex align-items-center gap-1 fw-bold px-3">
                    <i class="bi bi-send-fill"></i> Dispatch Campaign to Queues
                </button>
            </form>
        @endif
        @if($campaign->status === 'running')
            <form action="{{ route('campaigns.pause', $campaign) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-pause-fill"></i> Pause Campaign
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Campaign KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card p-3 shadow-sm text-center">
            <div class="text-muted small fw-semibold text-uppercase">Total Audience</div>
            <h3 class="fw-bold my-1">{{ number_format($campaign->total_recipients) }}</h3>
            <small class="text-muted">Targeted Leads</small>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card p-3 shadow-sm text-center">
            <div class="text-muted small fw-semibold text-uppercase text-primary">Sent</div>
            <h3 class="fw-bold my-1 text-primary">{{ number_format($campaign->sent_count) }}</h3>
            <small class="text-muted">Dispatched</small>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card p-3 shadow-sm text-center">
            <div class="text-muted small fw-semibold text-uppercase text-success">Delivered</div>
            <h3 class="fw-bold my-1 text-success">{{ number_format($campaign->delivered_count) }}</h3>
            <small class="text-muted">{{ $campaign->sent_count > 0 ? round(($campaign->delivered_count / $campaign->sent_count) * 100, 1) : 0 }}% rate</small>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card p-3 shadow-sm text-center">
            <div class="text-muted small fw-semibold text-uppercase text-danger">Bounced</div>
            <h3 class="fw-bold my-1 text-danger">{{ number_format($campaign->bounced_count) }}</h3>
            <small class="text-muted">Suppressed</small>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card p-3 shadow-sm text-center">
            <div class="text-muted small fw-semibold text-uppercase text-success">Replies</div>
            <h3 class="fw-bold my-1 text-success">{{ number_format($campaign->reply_count) }}</h3>
            <small class="text-muted">Inbound Responses</small>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card p-3 shadow-sm text-center">
            <div class="text-muted small fw-semibold text-uppercase text-secondary">Opt-Outs</div>
            <h3 class="fw-bold my-1 text-secondary">{{ number_format($campaign->unsubscribe_count) }}</h3>
            <small class="text-muted">Unsubscribed</small>
        </div>
    </div>
</div>

<!-- Audience Recipients Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-people text-primary me-2"></i>Target Audience Recipients ({{ $recipients->total() }})</span>
        <span class="text-muted small">Showing {{ $recipients->firstItem() ?? 0 }} - {{ $recipients->lastItem() ?? 0 }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Lead Name</th>
                    <th>Email Address</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th>Delivered At</th>
                    <th>Error Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recipients as $rec)
                    <tr>
                        <td>
                            @if($rec->lead)
                                <a href="{{ route('leads.show', $rec->lead) }}" class="fw-bold text-decoration-none text-dark hover-primary">
                                    {{ $rec->lead->name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $rec->email }}</td>
                        <td>{{ $rec->lead?->company?->name ?? '—' }}</td>
                        <td>
                            @php
                                $statusBadge = match($rec->status) {
                                    'sent', 'delivered' => 'bg-success',
                                    'queued' => 'bg-primary',
                                    'pending' => 'bg-secondary',
                                    'bounced', 'failed' => 'bg-danger',
                                    'suppressed' => 'bg-dark',
                                    default => 'bg-light text-dark',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} px-2 py-1">
                                {{ ucfirst($rec->status) }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $rec->sent_at ? $rec->sent_at->format('M d, Y H:i') : '—' }}</small></td>
                        <td><small class="text-muted">{{ $rec->delivered_at ? $rec->delivered_at->format('M d, Y H:i') : '—' }}</small></td>
                        <td><small class="text-danger">{{ $rec->error_message ?? '—' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No recipients in this campaign.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($recipients->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $recipients->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
