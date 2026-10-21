@extends('layouts.app')

@section('title', 'Email Outreach Campaigns')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Email Marketing & Campaigns</h4>
        <p class="text-muted small mb-0">Orchestrate targeted email campaigns, queue-based bulk sending, and monitor reply rates.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-envelope-paper"></i> Manage Templates
        </a>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Create Campaign
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Campaign</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Recipients</th>
                    <th>Sent / Delivered</th>
                    <th>Replies</th>
                    <th>Scheduled / Ran</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $camp)
                    <tr>
                        <td>
                            <div class="fw-bold">
                                <a href="{{ route('campaigns.show', $camp) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $camp->name }}
                                </a>
                            </div>
                            <small class="text-muted">{{ Str::limit($camp->subject, 40) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $camp->template?->name ?? 'Custom' }}</span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($camp->status) {
                                    'completed' => 'bg-success',
                                    'running' => 'bg-primary',
                                    'scheduled' => 'bg-info text-dark',
                                    'paused' => 'bg-warning text-dark',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ ucfirst($camp->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ number_format($camp->total_recipients) }}</span> leads
                        </td>
                        <td>
                            <div>
                                <span class="fw-semibold text-success">{{ number_format($camp->delivered_count) }}</span> / {{ number_format($camp->sent_count) }}
                                @if($camp->bounced_count > 0)
                                    <span class="text-danger small">({{ $camp->bounced_count }} bounced)</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 border border-success border-opacity-25">
                                {{ $camp->reply_count }} replies
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $camp->completed_at ? $camp->completed_at->format('M d, Y') : ($camp->scheduled_at ? $camp->scheduled_at->format('M d, Y H:i') : 'Draft') }}
                            </small>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                                    <li><a class="dropdown-item" href="{{ route('campaigns.show', $camp) }}"><i class="bi bi-eye text-primary me-2"></i>View Stats & Audience</a></li>
                                    @if($camp->status === 'draft' || $camp->status === 'scheduled')
                                        <li>
                                            <form action="{{ route('campaigns.dispatch', $camp) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-success"><i class="bi bi-send me-2"></i>Send Campaign Now</button>
                                            </form>
                                        </li>
                                    @endif
                                    @if($camp->status === 'running')
                                        <li>
                                            <form action="{{ route('campaigns.pause', $camp) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-warning"><i class="bi bi-pause me-2"></i>Pause Sending</button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-send text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold">No campaigns yet</h6>
                            <p class="text-muted small mb-3">Launch targeted email sequences to qualified lead segments.</p>
                            <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm">Create First Campaign</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($campaigns->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $campaigns->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
