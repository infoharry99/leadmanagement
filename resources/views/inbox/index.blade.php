@extends('layouts.app')

@section('title', 'Unified Engagement Inbox')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Unified Engagement Inbox</h4>
        <p class="text-muted small mb-0">Centralized hub for email replies, social comments, inbound messages, and AI intent triage.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#simulateInboundModal">
            <i class="bi bi-robot me-1"></i> Simulate Inbound Reply
        </button>
    </div>
</div>

<!-- Intent Filter Chips -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('inbox.index', ['channel' => $channel]) }}" class="badge text-decoration-none px-3 py-2 border {{ empty($intent) ? 'bg-dark text-white' : 'bg-white text-dark' }}">
        All Intents
    </a>
    <a href="{{ route('inbox.index', ['channel' => $channel, 'intent' => 'interested']) }}" class="badge text-decoration-none px-3 py-2 border {{ $intent === 'interested' ? 'bg-success text-white' : 'bg-white text-success' }}">
        <i class="bi bi-star-fill me-1"></i> Interested
    </a>
    <a href="{{ route('inbox.index', ['channel' => $channel, 'intent' => 'meeting_request']) }}" class="badge text-decoration-none px-3 py-2 border {{ $intent === 'meeting_request' ? 'bg-primary text-white' : 'bg-white text-primary' }}">
        <i class="bi bi-calendar-event me-1"></i> Meeting Request
    </a>
    <a href="{{ route('inbox.index', ['channel' => $channel, 'intent' => 'information_request']) }}" class="badge text-decoration-none px-3 py-2 border {{ $intent === 'information_request' ? 'bg-info text-white' : 'bg-white text-info' }}">
        <i class="bi bi-info-circle me-1"></i> Info Request
    </a>
    <a href="{{ route('inbox.index', ['channel' => $channel, 'intent' => 'out_of_office']) }}" class="badge text-decoration-none px-3 py-2 border {{ $intent === 'out_of_office' ? 'bg-secondary text-white' : 'bg-white text-secondary' }}">
        <i class="bi bi-airplane me-1"></i> Out of Office
    </a>
    <a href="{{ route('inbox.index', ['channel' => $channel, 'intent' => 'not_interested']) }}" class="badge text-decoration-none px-3 py-2 border {{ $intent === 'not_interested' ? 'bg-danger text-white' : 'bg-white text-danger' }}">
        <i class="bi bi-x-circle me-1"></i> Not Interested
    </a>
</div>

<!-- Channel Tabs -->
<div class="card shadow-sm">
    <div class="card-header bg-white p-0">
        <ul class="nav nav-tabs card-header-tabs m-0 px-3 border-0">
            <li class="nav-item">
                <a class="nav-link py-3 fw-semibold small {{ $channel === 'email' ? 'active' : '' }}" href="{{ route('inbox.index', ['channel' => 'email', 'intent' => $intent]) }}">
                    <i class="bi bi-envelope-open-fill me-1 text-primary"></i> Email Replies ({{ $emailReplies->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 fw-semibold small {{ $channel === 'comments' ? 'active' : '' }}" href="{{ route('inbox.index', ['channel' => 'comments']) }}">
                    <i class="bi bi-chat-quote-fill me-1 text-info"></i> Social Comments ({{ $socialComments->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 fw-semibold small {{ $channel === 'messages' ? 'active' : '' }}" href="{{ route('inbox.index', ['channel' => 'messages']) }}">
                    <i class="bi bi-messenger me-1 text-success"></i> Direct Messages ({{ $socialMessages->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 fw-semibold small {{ $channel === 'handovers' ? 'active' : '' }}" href="{{ route('inbox.index', ['channel' => 'handovers']) }}">
                    <i class="bi bi-person-fill-exclamation me-1 text-warning"></i> Handover Queue ({{ $handovers->count() }})
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        @if($channel === 'email')
            <!-- Email Replies Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Prospect & Subject</th>
                            <th>AI Intent Classification</th>
                            <th>Confidence</th>
                            <th>Status</th>
                            <th>Received At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emailReplies as $reply)
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        <a href="{{ route('inbox.show', $reply) }}" class="text-decoration-none text-dark hover-primary">
                                            {{ $reply->subject }}
                                        </a>
                                    </div>
                                    <small class="text-muted">
                                        From: <strong>{{ $reply->lead?->name ?? $reply->from_email }}</strong>
                                        @if($reply->lead?->company)
                                            ({{ $reply->lead->company->name }})
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $intentBadge = match($reply->ai_classification) {
                                            'interested', 'meeting_request' => 'bg-success',
                                            'information_request' => 'bg-info text-dark',
                                            'out_of_office' => 'bg-secondary',
                                            'not_interested' => 'bg-danger',
                                            default => 'bg-light text-dark border',
                                        };
                                    @endphp
                                    <span class="badge {{ $intentBadge }} px-2 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $reply->ai_classification)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ round($reply->ai_confidence * 100) }}%</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $reply->status === 'resolved' ? 'success' : ($reply->status === 'assigned' ? 'primary' : 'warning text-dark') }}">
                                        {{ ucfirst($reply->status) }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $reply->received_at->diffForHumans() }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('inbox.show', $reply) }}" class="btn btn-sm btn-outline-primary">
                                        View Thread
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No email replies found matching current filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($emailReplies->hasPages())
                <div class="card-footer bg-white py-2 px-3">
                    {{ $emailReplies->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @elseif($channel === 'comments')
            <!-- Social Comments List -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Platform & Author</th>
                            <th>Comment Content</th>
                            <th>Sentiment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($socialComments as $comment)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase me-1">{{ $comment->account?->platform ?? 'Social' }}</span>
                                    <strong>{{ $comment->author_name }}</strong>
                                </td>
                                <td>{{ $comment->content }}</td>
                                <td>
                                    <span class="badge bg-{{ $comment->sentiment === 'lead_inquiry' ? 'success' : 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $comment->sentiment)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $comment->status === 'responded' ? 'success' : 'warning text-dark' }}">
                                        {{ ucfirst($comment->status) }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $comment->commented_at->diffForHumans() }}</small></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#replyCommentModal_{{ $comment->id }}">
                                        Respond
                                    </button>

                                    <!-- Reply Comment Modal -->
                                    <div class="modal fade" id="replyCommentModal_{{ $comment->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('inbox.comment.reply', $comment) }}" method="POST">
                                                @csrf
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Reply to {{ $comment->author_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="p-2 bg-light rounded small mb-3">
                                                            "{{ $comment->content }}"
                                                        </div>
                                                        <label class="form-label small fw-semibold">Your Response</label>
                                                        <textarea name="reply_text" class="form-control" rows="3" required placeholder="Type reply from Nexteck Official..."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Post Reply</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No social comments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($channel === 'messages')
            <!-- Social DMs List -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Platform & Sender</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($socialMessages as $msg)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase me-1">{{ $msg->account?->platform ?? 'Social' }}</span>
                                    <strong>{{ $msg->sender_name }}</strong>
                                </td>
                                <td>{{ $msg->message_text }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($msg->status) }}</span></td>
                                <td><small class="text-muted">{{ $msg->received_at->diffForHumans() }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No direct messages received.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($channel === 'handovers')
            <!-- Handover Queue List -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Prospect</th>
                            <th>Company</th>
                            <th>Handover Reason</th>
                            <th>Urgency</th>
                            <th>Assigned Manager</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($handovers as $h)
                            <tr>
                                <td>
                                    <strong>{{ $h->lead?->name }}</strong>
                                    <div class="text-muted small">{{ $h->lead?->email }}</div>
                                </td>
                                <td>{{ $h->lead?->company?->name ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $h->handover_reason)) }}</span></td>
                                <td><span class="badge bg-{{ $h->urgency === 'urgent' ? 'danger' : 'warning text-dark' }}">{{ ucfirst($h->urgency) }}</span></td>
                                <td>{{ $h->assignedManager?->name ?? 'Unassigned' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('handovers.acknowledge', $h) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Acknowledge</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No pending handovers in queue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Simulate Inbound Reply -->
<div class="modal fade" id="simulateInboundModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('inbox.simulate') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Simulate Inbound Email Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Test automatic intent classification, sequence pausing, and manager notifications:</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sender Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="marcus.vance@acrotech.io" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Subject Line <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="Re: Accelerating AcroTech Solutions software roadmap with Nexteck" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Body Content <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="4" required>Hi team, thanks for following up. We are actually planning a major refactor of our billing engine to Laravel 12 next month. Can we schedule a Zoom call next Tuesday at 2 PM EST to discuss your team's availability and pricing?</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Ingest & Run AI Triage</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
