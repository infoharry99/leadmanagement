@extends('social_scheduler.layouts.social')

@section('title', 'Social Post Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <!-- Navigation Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('social.posts.dashboard') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
                <h3 class="fw-bold text-dark mb-0">Post Details</h3>
            </div>
            <div class="d-flex gap-2">
                @if ($post->status === 'failed')
                    <form action="{{ route('social.posts.retry', $post) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-3 px-3 py-2">
                            <i class="bi bi-arrow-repeat me-1"></i> Retry Publishing
                        </button>
                    </form>
                @endif
                <form action="{{ route('social.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-3 py-2">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Content & Media Preview -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small">Post Content</span>
                        @if ($post->status === 'scheduled')
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-clock me-1"></i> Scheduled
                            </span>
                        @elseif ($post->status === 'published')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-check-circle me-1"></i> Published
                            </span>
                        @elseif ($post->status === 'failed')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-x-circle me-1"></i> Failed
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill small">
                                {{ ucfirst($post->status) }}
                            </span>
                        @endif
                    </div>

                    <!-- Caption Content -->
                    <div class="fs-6 text-dark lh-base mb-3" style="white-space: pre-wrap;">{{ $post->content }}</div>

                    <!-- Hashtags -->
                    @if (!empty($post->hashtags) && is_array($post->hashtags))
                        <div class="mb-4">
                            @foreach ($post->hashtags as $tag)
                                <span class="badge bg-light text-primary border me-1 mb-1">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Media Preview -->
                    @if (!empty($post->media_urls) && count($post->media_urls) > 0)
                        <div class="border-top pt-3">
                            <span class="text-muted small d-block mb-2">Attached Media:</span>
                            @foreach ($post->media_urls as $mediaUrl)
                                @php
                                    $isVideo = preg_match('/\.(mp4|mov|webm)$/i', $mediaUrl);
                                @endphp
                                @if ($isVideo)
                                    <video controls class="w-100 rounded-3 shadow-sm border" style="max-height: 380px;">
                                        <source src="{{ $mediaUrl }}">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <img src="{{ $mediaUrl }}" alt="Attached Image" class="img-fluid rounded-3 shadow-sm border w-100" style="max-height: 420px; object-fit: contain; background: #f8fafc;">
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Platform Delivery Status & Details -->
            <div class="col-md-5">
                <!-- Timing & Info Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h6 class="fw-bold text-dark mb-3">Schedule & Timing</h6>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created:</span>
                            <span class="fw-semibold text-dark">{{ $post->created_at->format('M d, Y • h:i A') }}</span>
                        </div>
                        @if ($post->scheduled_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Scheduled For:</span>
                                <span class="fw-semibold text-primary">{{ $post->scheduled_at->format('M d, Y • h:i A') }}</span>
                            </div>
                        @endif
                        @if ($post->published_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Published At:</span>
                                <span class="fw-semibold text-success">{{ $post->published_at->format('M d, Y • h:i A') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created By:</span>
                            <span class="fw-semibold text-dark">{{ $post->createdBy->name ?? 'System' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Per-Platform Status -->
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold text-dark mb-3">Platform Publishing Status</h6>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($post->postAccounts as $pa)
                            @php
                                $acc = $pa->account;
                                $platform = $acc->platform ?? 'unknown';
                                $icon = match($platform) {
                                    'facebook' => 'bi-facebook text-primary',
                                    'instagram' => 'bi-instagram text-danger',
                                    'linkedin' => 'bi-linkedin text-primary',
                                    'tiktok' => 'bi-tiktok text-dark',
                                    default => 'bi-globe text-secondary',
                                };
                            @endphp
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $icon }} fs-5"></i>
                                        <div>
                                            <span class="fw-bold text-dark text-capitalize">{{ $platform }}</span>
                                            <small class="text-muted d-block">{{ $acc->account_name ?? 'Account' }}</small>
                                        </div>
                                    </div>
                                    <div>
                                        @if ($pa->status === 'published')
                                            <span class="badge bg-success-subtle text-success small">Published</span>
                                        @elseif ($pa->status === 'failed')
                                            <span class="badge bg-danger-subtle text-danger small">Failed</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary small">{{ ucfirst($pa->status) }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($pa->platform_post_id)
                                    <div class="small text-muted mt-2">
                                        Post ID: <code class="text-dark">{{ $pa->platform_post_id }}</code>
                                    </div>

                                    @if (str_starts_with($pa->platform_post_id, 'fb_post_') || str_starts_with($pa->platform_post_id, 'ig_post_'))
                                        <div class="alert alert-warning py-2 px-3 small mt-2 mb-0 rounded-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                                            <strong>Sandbox / Demo Post:</strong> This post was simulated because the connected account was authorized with a placeholder/demo token. To publish real posts directly to your live Facebook Page or Instagram, please connect using official OAuth in <strong>Settings &rarr; Social Media Connections</strong>.
                                        </div>
                                    @else
                                        <div class="mt-2">
                                            @if ($platform === 'facebook')
                                                <a href="https://facebook.com/{{ $pa->platform_post_id }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> View Live on Facebook
                                                </a>
                                            @elseif ($platform === 'instagram')
                                                <a href="https://instagram.com" target="_blank" class="btn btn-sm btn-outline-danger rounded-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Open Instagram Profile
                                                </a>
                                            @elseif ($platform === 'linkedin')
                                                <a href="https://www.linkedin.com/feed/update/{{ $pa->platform_post_id }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> View Live on LinkedIn
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @endif

                                @if ($pa->status === 'failed' && $pa->error_message)
                                    <div class="alert alert-danger py-2 px-3 small mt-2 mb-0 rounded-2">
                                        <strong>API Error:</strong> {{ $pa->error_message }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <!-- If no postAccounts records yet, show target platforms -->
                            @foreach ($post->target_platforms ?? [] as $platform)
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-dark text-capitalize">
                                            <i class="bi bi-globe me-1"></i> {{ $platform }}
                                        </span>
                                        <span class="badge bg-secondary-subtle text-secondary small">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endforelse
                    </div>

                    @if ($post->status === 'failed' && $post->error_message)
                        <div class="alert alert-danger py-2 px-3 small mt-3 mb-0 rounded-3">
                            <div class="fw-bold mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Overall Error Log:
                            </div>
                            <div>{{ $post->error_message }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
