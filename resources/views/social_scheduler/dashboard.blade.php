@extends('social_scheduler.layouts.social')

@section('title', 'Social Post Scheduler Dashboard')

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-md-7">
        <h2 class="fw-bold mb-1 text-dark">Social Media Post Scheduler</h2>
        <p class="text-muted mb-0">Publish immediately or schedule posts across Instagram, Facebook Pages, LinkedIn, and TikTok.</p>
    </div>
    <div class="col-md-5 text-md-end d-flex gap-2 justify-content-md-end">
        <a href="{{ route('social.connections.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2">
            <i class="bi bi-gear-fill me-1"></i> Social Settings
        </a>
        <a href="{{ route('social.posts.create') }}" class="btn btn-brand rounded-3 px-3 py-2 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Create Post
        </a>
    </div>
</div>

<!-- Simple KPI Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('social.posts.index', ['status' => 'scheduled']) }}" class="text-decoration-none">
            <div class="card-stat border-primary-subtle {{ request()->query('status') === 'scheduled' ? 'border-primary shadow-sm' : '' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Scheduled</span>
                    <span class="badge bg-primary-subtle text-primary rounded-circle p-2">
                        <i class="bi bi-clock-history fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($counts['scheduled'] ?? 0) }}</h3>
                <small class="text-muted">Awaiting publication</small>
            </div>
        </a>
    </div>

    <div class="col-6 col-lg-3">
        <a href="{{ route('social.posts.index', ['status' => 'published']) }}" class="text-decoration-none">
            <div class="card-stat border-success-subtle {{ request()->query('status') === 'published' ? 'border-success shadow-sm' : '' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Published</span>
                    <span class="badge bg-success-subtle text-success rounded-circle p-2">
                        <i class="bi bi-check2-circle fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($counts['published'] ?? 0) }}</h3>
                <small class="text-muted">Live on social accounts</small>
            </div>
        </a>
    </div>

    <div class="col-6 col-lg-3">
        <a href="{{ route('social.posts.index', ['status' => 'failed']) }}" class="text-decoration-none">
            <div class="card-stat border-danger-subtle {{ request()->query('status') === 'failed' ? 'border-danger shadow-sm' : '' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Failed</span>
                    <span class="badge bg-danger-subtle text-danger rounded-circle p-2">
                        <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($counts['failed'] ?? 0) }}</h3>
                <small class="text-danger">Action required</small>
            </div>
        </a>
    </div>

    <div class="col-6 col-lg-3">
        <a href="{{ route('social.connections.index') }}" class="text-decoration-none">
            <div class="card-stat">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Connected Accounts</span>
                    <span class="badge bg-secondary-subtle text-dark rounded-circle p-2">
                        <i class="bi bi-link-45deg fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $connectedAccountsCount ?? 0 }} / 4</h3>
                <small class="text-muted">Platforms authorized</small>
            </div>
        </a>
    </div>
</div>

<!-- Posts Table Section -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-dark">Social Posts</h5>
            @if(request()->filled('status'))
                <span class="badge bg-secondary text-uppercase">{{ request()->query('status') }}</span>
                <a href="{{ route('social.posts.dashboard') }}" class="btn btn-sm btn-link text-decoration-none p-0 text-muted">
                    <i class="bi bi-x-circle"></i> Clear filter
                </a>
            @endif
        </div>

        <ul class="nav nav-pills small">
            <li class="nav-item">
                <a class="nav-link py-1 px-3 rounded-pill {{ !request()->filled('status') ? 'active bg-primary text-white' : 'text-muted' }}" href="{{ route('social.posts.dashboard') }}">
                    All ({{ $counts['total'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 rounded-pill {{ request()->query('status') === 'scheduled' ? 'active bg-primary text-white' : 'text-muted' }}" href="{{ route('social.posts.index', ['status' => 'scheduled']) }}">
                    Scheduled ({{ $counts['scheduled'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 rounded-pill {{ request()->query('status') === 'published' ? 'active bg-primary text-white' : 'text-muted' }}" href="{{ route('social.posts.index', ['status' => 'published']) }}">
                    Published ({{ $counts['published'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 rounded-pill {{ request()->query('status') === 'failed' ? 'active bg-danger text-white' : 'text-muted' }}" href="{{ route('social.posts.index', ['status' => 'failed']) }}">
                    Failed ({{ $counts['failed'] }})
                </a>
            </li>
        </ul>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th style="width: 80px;" class="ps-4">Media</th>
                    <th style="min-width: 280px;">Content</th>
                    <th style="min-width: 170px;">Target Platforms</th>
                    <th style="min-width: 150px;">Date & Timing</th>
                    <th style="min-width: 120px;">Status</th>
                    <th style="width: 130px;" class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <!-- Media Preview -->
                        <td class="ps-4">
                            @if (!empty($post->media_urls) && count($post->media_urls) > 0)
                                @php
                                    $media = $post->media_urls[0];
                                    $isVideo = preg_match('/\.(mp4|mov|webm)$/i', $media);
                                @endphp
                                @if ($isVideo)
                                    <div class="rounded-3 bg-dark text-white d-flex align-items-center justify-content-center position-relative" style="width: 56px; height: 56px;">
                                        <i class="bi bi-play-circle-fill fs-4 text-white"></i>
                                    </div>
                                @else
                                    <img src="{{ $media }}" alt="Thumbnail" class="rounded-3 object-fit-cover border" style="width: 56px; height: 56px;">
                                @endif
                            @else
                                <div class="rounded-3 bg-light text-muted d-flex align-items-center justify-content-center border" style="width: 56px; height: 56px;">
                                    <i class="bi bi-chat-text fs-5"></i>
                                </div>
                            @endif
                        </td>

                        <!-- Post Content & Hashtags -->
                        <td>
                            <a href="{{ route('social.posts.show', $post) }}" class="text-decoration-none text-dark fw-semibold d-block text-truncate" style="max-width: 380px;">
                                {{ $post->content }}
                            </a>
                            @if (!empty($post->hashtags) && is_array($post->hashtags))
                                <div class="small text-primary mt-1 text-truncate" style="max-width: 380px;">
                                    {{ implode(' ', $post->hashtags) }}
                                </div>
                            @endif

                            @if ($post->status === 'failed' && $post->error_message)
                                <div class="small text-danger mt-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span class="text-truncate" style="max-width: 380px;">{{ $post->error_message }}</span>
                                </div>
                            @endif
                        </td>

                        <!-- Target Platforms -->
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($post->target_platforms ?? [] as $platform)
                                    @php
                                        $badgeClass = match($platform) {
                                            'facebook' => 'badge-facebook',
                                            'instagram' => 'badge-instagram',
                                            'linkedin' => 'badge-linkedin',
                                            'tiktok' => 'badge-tiktok',
                                            default => 'badge-secondary',
                                        };
                                        $icon = match($platform) {
                                            'facebook' => 'bi-facebook',
                                            'instagram' => 'bi-instagram',
                                            'linkedin' => 'bi-linkedin',
                                            'tiktok' => 'bi-tiktok',
                                            default => 'bi-globe',
                                        };
                                    @endphp
                                    <span class="badge-platform {{ $badgeClass }}">
                                        <i class="bi {{ $icon }}"></i> {{ ucfirst($platform) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <!-- Date & Timing -->
                        <td>
                            @if ($post->status === 'scheduled')
                                <div class="fw-semibold text-primary small">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $post->scheduled_at ? $post->scheduled_at->format('M d, Y') : 'Scheduled' }}
                                </div>
                                <small class="text-muted">
                                    {{ $post->scheduled_at ? $post->scheduled_at->format('h:i A') : '' }}
                                    ({{ $post->scheduled_at ? $post->scheduled_at->diffForHumans() : '' }})
                                </small>
                            @elseif ($post->status === 'published')
                                <div class="fw-semibold text-success small">
                                    <i class="bi bi-check-all me-1"></i>
                                    {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Published' }}
                                </div>
                                <small class="text-muted">
                                    {{ $post->published_at ? $post->published_at->format('h:i A') : '' }}
                                </small>
                            @else
                                <div class="small text-muted">
                                    Created {{ $post->created_at->diffForHumans() }}
                                </div>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @if ($post->status === 'scheduled')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                    <i class="bi bi-clock me-1"></i> Scheduled
                                </span>
                            @elseif ($post->status === 'published')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> Published
                                </span>
                            @elseif ($post->status === 'failed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Failed
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">
                                    {{ ucfirst($post->status) }}
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('social.posts.show', $post) }}" class="btn btn-sm btn-outline-secondary rounded-2 px-2" title="View Delivery Details">
                                    <i class="bi bi-eye me-1"></i> Details
                                </a>

                                @if ($post->status === 'failed')
                                    <form action="{{ route('social.posts.retry', $post) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-2" title="Retry Publish">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('social.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-4">
                                <div class="rounded-circle bg-light d-inline-flex p-4 mb-3 text-muted">
                                    <i class="bi bi-calendar2-plus fs-1"></i>
                                </div>
                                <h5 class="fw-bold text-dark">No posts found</h5>
                                <p class="text-muted small mb-3">Create your first social media post or schedule one for later.</p>
                                <a href="{{ route('social.posts.create') }}" class="btn btn-brand btn-sm px-3 py-2 rounded-3">
                                    <i class="bi bi-plus-lg me-1"></i> Create Post Now
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($posts->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
