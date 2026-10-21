@extends('layouts.app')

@section('title', 'Plan Content Item')

@section('content')
<div class="mb-4">
    <a href="{{ route('calendar.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Calendar
    </a>
    <h4 class="fw-bold mb-1">Plan Scheduled Content</h4>
    <p class="text-muted small">Design target topics, select platforms, and assign team owners.</p>
</div>

<form action="{{ route('calendar.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-calendar3 text-primary me-2"></i>Content Parameters
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Content Topic / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. 5 Signs Your Web App Architecture Needs Modernization">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Target Platform <span class="text-danger">*</span></label>
                            <select name="platform" class="form-select" required>
                                <option value="linkedin">LinkedIn (Professional & Thought Leadership)</option>
                                <option value="instagram">Instagram (Carousels & Behind-The-Scenes)</option>
                                <option value="facebook">Facebook (Educational & Case Studies)</option>
                                <option value="tiktok">TikTok (Short-Form Video Hooks & Scripts)</option>
                                <option value="blog">Nexteck Engineering Blog</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Format / Asset Type <span class="text-danger">*</span></label>
                            <select name="content_type" class="form-select" required>
                                <option value="post">Standard Text / Graphic Post</option>
                                <option value="carousel">Multi-Slide Carousel</option>
                                <option value="video_script">Short-Form Video Script</option>
                                <option value="blog">Long-Form Architectural Article</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Scheduled Date <span class="text-danger">*</span></label>
                            <input type="date" name="scheduled_date" class="form-control" value="{{ old('scheduled_date', now()->addDay()->toDateString()) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Scheduled Time <span class="text-danger">*</span></label>
                            <input type="time" name="scheduled_time" class="form-control" value="{{ old('scheduled_time', '10:00') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Content Draft Copy</label>
                            <textarea name="content_draft" class="form-control" rows="5" placeholder="Draft the copy, captions, hashtags, or video script notes here...">{{ old('content_draft') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Save to Calendar Plan</button>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-bullseye text-info me-2"></i>Strategy & Goals
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Audience</label>
                        <input type="text" name="target_audience" class="form-control form-control-sm" value="{{ old('target_audience', 'CTOs & Engineering Leads') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Industry</label>
                        <input type="text" name="industry" class="form-control form-control-sm" value="{{ old('industry', 'Enterprise Tech') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Campaign Goal</label>
                        <input type="text" name="campaign_goal" class="form-control form-control-sm" value="{{ old('campaign_goal', 'Thought leadership & Inbound leads') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign Owner</label>
                        <select name="assigned_user_id" class="form-select form-select-sm">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ auth()->id() == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
