@extends('layouts.app')

@section('title', 'Compose Social Post')

@section('content')
<div class="mb-4">
    <a href="{{ route('social.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Social Hub
    </a>
    <h4 class="fw-bold mb-1">Compose Multi-Platform Social Post</h4>
    <p class="text-muted small">Publish directly or schedule across LinkedIn, Facebook, Instagram, and TikTok.</p>
</div>

<form action="{{ route('social.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Post Content
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Internal Reference Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Scaling with Laravel 12 Best Practices">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Post Copy <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="8" required placeholder="Write your post copy, hashtags, and links here...">{{ old('content') }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Scheduled Date & Time (Optional)</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control">
                            <div class="form-text small">Leave blank to publish immediately or save as draft.</div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="publish_now" value="1" id="publishNowCheck" checked>
                                <label class="form-check-label small fw-semibold" for="publishNowCheck">
                                    Publish Immediately to Active Accounts
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-4 py-2.5 fw-bold shadow-sm">
                Submit Social Post
            </button>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-share text-info me-2"></i>Target Social Platforms
                </div>
                <div class="card-body p-3">
                    <p class="text-muted small">Select connected networks:</p>

                    <div class="form-check p-2 border rounded-3 mb-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="target_platforms[]" value="linkedin" id="plat_li" checked>
                        <label class="form-check-label fw-semibold small text-primary" for="plat_li">
                            <i class="bi bi-linkedin me-1"></i> LinkedIn
                        </label>
                    </div>

                    <div class="form-check p-2 border rounded-3 mb-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="target_platforms[]" value="facebook" id="plat_fb" checked>
                        <label class="form-check-label fw-semibold small text-primary" for="plat_fb">
                            <i class="bi bi-facebook me-1"></i> Facebook
                        </label>
                    </div>

                    <div class="form-check p-2 border rounded-3 mb-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="target_platforms[]" value="instagram" id="plat_ig">
                        <label class="form-check-label fw-semibold small text-danger" for="plat_ig">
                            <i class="bi bi-instagram me-1"></i> Instagram
                        </label>
                    </div>

                    <div class="form-check p-2 border rounded-3 mb-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="target_platforms[]" value="tiktok" id="plat_tt">
                        <label class="form-check-label fw-semibold small text-dark" for="plat_tt">
                            <i class="bi bi-tiktok me-1"></i> TikTok
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
