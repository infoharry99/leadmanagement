@extends('layouts.app')

@section('title', 'Social Media Management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Centralized Social Media Hub</h4>
        <p class="text-muted small mb-0">Publish posts, monitor multi-platform account health, track engagement, and manage conversations.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#connectAccountModal">
            <i class="bi bi-link-45deg"></i> Connect Account
        </button>
        <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-pencil-square"></i> Create Post
        </a>
    </div>
</div>

<!-- Connected Accounts Health Row -->
<div class="row g-3 mb-4">
    @forelse($accounts as $acc)
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $pIcon = match($acc->platform) {
                                'linkedin' => 'bi-linkedin text-primary',
                                'facebook' => 'bi-facebook text-primary',
                                'instagram' => 'bi-instagram text-danger',
                                'tiktok' => 'bi-tiktok text-dark',
                                default => 'bi-globe text-secondary',
                            };
                        @endphp
                        <i class="bi {{ $pIcon }} fs-4"></i>
                        <span class="fw-bold small text-uppercase">{{ $acc->platform }}</span>
                    </div>
                    <span class="badge bg-{{ $acc->is_active && !$acc->isTokenExpired() ? 'success' : 'danger' }} d-flex align-items-center gap-1">
                        <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                        {{ $acc->isTokenExpired() ? 'Expired' : ($acc->is_active ? 'Healthy' : 'Inactive') }}
                    </span>
                </div>
                <div class="fw-semibold text-dark text-truncate">{{ $acc->account_name }}</div>
                <div class="text-muted small mt-2 d-flex justify-content-between">
                    <span>Token Expiry:</span>
                    <span class="{{ $acc->isTokenExpired() ? 'text-danger fw-bold' : '' }}">
                        {{ $acc->token_expires_at ? $acc->token_expires_at->diffForHumans() : 'No expiry set' }}
                    </span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card p-4 text-center text-muted">
                No social media accounts connected. Connect your official LinkedIn, Facebook, Instagram, or TikTok accounts.
            </div>
        </div>
    @endforelse
</div>

<!-- Published & Scheduled Posts History -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Post Publishing History & Performance</span>
        <span class="text-muted small">Showing {{ $posts->firstItem() ?? 0 }} - {{ $posts->lastItem() ?? 0 }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Post Content</th>
                    <th>Platforms</th>
                    <th>Status</th>
                    <th>Engagement Metrics</th>
                    <th>Scheduled / Published</th>
                    <th>Author</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $p)
                    <tr>
                        <td style="max-width: 320px;">
                            <div class="fw-bold text-truncate mb-1">{{ $p->title ?? 'Post' }}</div>
                            <small class="text-muted text-truncate d-block">{{ Str::limit($p->content, 90) }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @foreach($p->target_platforms ?? [] as $pl)
                                    <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;">
                                        {{ $pl }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @php
                                $statusBadge = match($p->status) {
                                    'published' => 'bg-success',
                                    'scheduled' => 'bg-info text-dark',
                                    'failed' => 'bg-danger',
                                    'publishing' => 'bg-primary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} px-2 py-1">
                                {{ ucfirst($p->status) }}
                            </span>
                            @if($p->error_message)
                                <small class="text-danger d-block mt-1" style="font-size: 0.68rem;" title="{{ $p->error_message }}">
                                    {{ Str::limit($p->error_message, 25) }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @php
                                $likes = 0; $comments = 0; $impressions = 0;
                                foreach($p->postAccounts as $pa) {
                                    $m = $pa->engagement_metrics ?? [];
                                    $likes += $m['likes'] ?? 0;
                                    $comments += $m['comments'] ?? 0;
                                    $impressions += $m['impressions'] ?? 0;
                                }
                            @endphp
                            <small class="d-flex align-items-center gap-2 text-muted">
                                <span><i class="bi bi-heart-fill text-danger me-1"></i>{{ $likes }}</span>
                                <span><i class="bi bi-chat-fill text-primary me-1"></i>{{ $comments }}</span>
                                @if($impressions > 0)
                                    <span><i class="bi bi-eye-fill text-secondary me-1"></i>{{ number_format($impressions) }}</span>
                                @endif
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $p->published_at ? $p->published_at->format('M d, Y H:i') : ($p->scheduled_at ? $p->scheduled_at->format('M d, Y H:i') : 'Draft') }}
                            </small>
                        </td>
                        <td><small class="text-muted">{{ $p->createdBy?->name ?? 'User' }}</small></td>
                        <td class="text-end">
                            @if($p->status === 'failed')
                                <form action="{{ route('social.retry', $p) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-arrow-clockwise"></i> Retry
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No social posts created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Modal: Connect Social Account -->
<div class="modal fade" id="connectAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('social.connect') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Connect Social Platform Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Platform</label>
                        <select name="platform" class="form-select" required>
                            <option value="linkedin">LinkedIn</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="tiktok">TikTok</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Account Display Name</label>
                        <input type="text" name="account_name" class="form-control" placeholder="e.g. Nexteck Official" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Platform Account ID / Page ID</label>
                        <input type="text" name="account_id" class="form-control" placeholder="e.g. nexteck-page-123" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Connect via OAuth</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
