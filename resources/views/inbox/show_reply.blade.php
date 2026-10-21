@extends('layouts.app')

@section('title', 'Reply: ' . $reply->subject)

@section('content')
<div class="mb-4">
    <a href="{{ route('inbox.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Unified Inbox
    </a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $reply->subject }}</h4>
            <p class="text-muted small mb-0">From: <strong>{{ $reply->from_email }}</strong> • Received: {{ $reply->received_at->format('M d, Y • h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($reply->status !== 'resolved')
                <form action="{{ route('inbox.resolve', $reply) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Mark Resolved
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Message Thread Column -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-chat-text text-primary me-2"></i>Inbound Prospect Message</span>
                <span class="badge bg-light text-dark border">{{ $reply->received_at->diffForHumans() }}</span>
            </div>
            <div class="card-body p-4">
                <div class="p-3 bg-light rounded-3 text-dark mb-4" style="font-size: 0.95rem; line-height: 1.6;">
                    {!! nl2br(e($reply->body)) !!}
                </div>

                <!-- Manager Reply Composer -->
                <div class="card border">
                    <div class="card-header bg-light py-2 px-3 fw-bold small text-muted">
                        <i class="bi bi-reply-fill me-1"></i>Compose Manager Response
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ route('inbox.resolve', $reply) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="reply_body" class="form-control" rows="4" placeholder="Hi {{ $reply->lead ? explode(' ', $reply->lead->name)[0] : 'there' }}, thanks for getting back to us. I'd be delighted to walk you through our Laravel engineering capabilities..."></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Automated marketing sequence paused for this lead.</small>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                                    <i class="bi bi-send me-1"></i> Send Reply & Update Handover
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Intelligence & Handover Column -->
    <div class="col-lg-4">
        <!-- AI Triage Analysis Card -->
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-robot me-1"></i>AI Triage & Intent Score</span>
                <span class="badge bg-white text-primary">{{ round($reply->ai_confidence * 100) }}% Confidence</span>
            </div>
            <div class="card-body p-3 small">
                <div class="mb-3">
                    <div class="text-muted small fw-semibold">Detected Intent:</div>
                    <span class="badge bg-{{ in_array($reply->ai_classification, ['interested', 'meeting_request']) ? 'success' : 'secondary' }} fs-6 px-3 py-1.5 mt-1">
                        {{ ucfirst(str_replace('_', ' ', $reply->ai_classification)) }}
                    </span>
                </div>
                <div class="mb-3">
                    <div class="text-muted small fw-semibold">AI Executive Summary:</div>
                    <p class="text-dark bg-light p-2.5 rounded border mt-1 mb-0">
                        {{ $reply->ai_summary ?? 'Intent analyzed automatically upon inbound arrival.' }}
                    </p>
                </div>
                <div class="text-muted" style="font-size: 0.72rem;">
                    <i class="bi bi-info-circle me-1"></i>AI suggestions are advisory. Consequential communications require manager confirmation.
                </div>
            </div>
        </div>

        <!-- Manager Assignment Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold small text-uppercase">
                <i class="bi bi-person-fill-check text-success me-1"></i>Assign Conversation
            </div>
            <div class="card-body p-3">
                <form action="{{ route('inbox.assign', $reply) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign To Manager / Rep</label>
                        <select name="user_id" class="form-select form-select-sm" required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $reply->lead?->assigned_user_id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                        Reassign Lead & Conversation
                    </button>
                </form>
            </div>
        </div>

        @if($reply->lead)
            <!-- Lead Mini Profile -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-bold small text-uppercase">
                    <i class="bi bi-person-badge text-info me-1"></i>Prospect Profile
                </div>
                <div class="card-body p-3 small">
                    <div class="fw-bold mb-1">{{ $reply->lead->name }}</div>
                    <div class="text-muted mb-2">{{ $reply->lead->company?->name }} • {{ $reply->lead->job_title }}</div>
                    <div class="mb-2">Status: <span class="badge badge-status-{{ $reply->lead->status }}">{{ ucfirst($reply->lead->status) }}</span></div>
                    <a href="{{ route('leads.show', $reply->lead) }}" class="btn btn-sm btn-light border w-100 mt-2">
                        View Complete Lead CRM Record
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
