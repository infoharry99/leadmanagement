@extends('social_scheduler.layouts.social')

@section('title', 'Create Social Media Post')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('social.posts.dashboard') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
                <h3 class="fw-bold text-dark mb-0">Create New Social Post</h3>
            </div>
            <div>
                <a href="{{ route('social.connections.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-link-45deg me-1"></i> Manage Connected Accounts
                </a>
            </div>
        </div>

        <form action="{{ route('social.posts.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
            @csrf

            <div class="row g-4">
                <!-- Left: Content, Media & Scheduling -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 class="fw-bold text-dark mb-3">1. Post Content</h5>

                        <!-- Description / Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label fw-semibold text-dark">
                                Post Caption / Description <span class="text-danger">*</span>
                            </label>
                            <textarea name="content" id="content" rows="6" class="form-control rounded-3 @error('content') is-invalid @enderror" placeholder="Write your post caption here... What's new?" required>{{ old('content') }}</textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted">Supports emojis, links, and line breaks.</small>
                                <small class="text-muted" id="charCounter">0 characters</small>
                            </div>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hashtags -->
                        <div class="mb-4">
                            <label for="hashtags" class="form-label fw-semibold text-dark">Hashtags</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                                    <i class="bi bi-hash"></i>
                                </span>
                                <input type="text" name="hashtags" id="hashtags" class="form-control border-start-0 rounded-end-3 @error('hashtags') is-invalid @enderror" placeholder="growth marketing business launch" value="{{ old('hashtags') }}">
                            </div>
                            <small class="text-muted">Separate with spaces or commas. The scheduler formats them automatically.</small>
                            @error('hashtags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Media Upload -->
                        <h5 class="fw-bold text-dark mb-2">2. Upload Media</h5>
                        <p class="text-muted small mb-3">Upload an image or video file. Required for Instagram and TikTok.</p>

                        <div class="mb-3">
                            <div class="border border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative" id="dropArea">
                                <input type="file" name="media" id="mediaInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm">
                                
                                <div id="uploadPlaceholder">
                                    <div class="rounded-circle bg-white shadow-sm d-inline-flex p-3 mb-2 text-primary">
                                        <i class="bi bi-cloud-arrow-up-fill fs-3"></i>
                                    </div>
                                    <div class="fw-semibold text-dark mb-1">Click to upload or drag & drop</div>
                                    <div class="text-muted small">Images (JPG, PNG, WEBP) or Videos (MP4, MOV) up to 50MB</div>
                                </div>

                                <!-- Dynamic Preview Container -->
                                <div id="previewContainer" class="d-none mt-2">
                                    <div class="position-relative d-inline-block">
                                        <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded-3 border shadow-sm d-none" style="max-height: 240px;">
                                        <video id="videoPreview" controls class="rounded-3 border shadow-sm d-none" style="max-height: 240px; max-width: 100%;"></video>
                                        <button type="button" id="removeMediaBtn" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 translate-middle shadow" title="Remove media">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div id="mediaFileName" class="small fw-semibold text-muted mt-2"></div>
                                </div>
                            </div>
                            @error('media')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Scheduling / Publish Now -->
                        <h5 class="fw-bold text-dark mb-3">3. Publishing Schedule</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="card h-100 border p-3 rounded-3 cursor-pointer action-type-card" id="cardActionNow">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="radio" name="action_type" value="now" class="form-check-input mt-0" {{ old('action_type', 'now') === 'now' ? 'checked' : '' }} id="radioNow">
                                        <div>
                                            <div class="fw-bold text-dark">Publish Immediately</div>
                                            <small class="text-muted">Post right now to selected accounts</small>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="col-sm-6">
                                <label class="card h-100 border p-3 rounded-3 cursor-pointer action-type-card" id="cardActionSchedule">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="radio" name="action_type" value="schedule" class="form-check-input mt-0" {{ old('action_type') === 'schedule' ? 'checked' : '' }} id="radioSchedule">
                                        <div>
                                            <div class="fw-bold text-dark">Schedule for Later</div>
                                            <small class="text-muted">Pick a date & time to auto-publish</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Schedule Pickers -->
                        <div id="scheduleFields" class="{{ old('action_type') === 'schedule' ? '' : 'd-none' }} bg-light p-3 rounded-3 border">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="scheduled_date" class="form-label fw-semibold text-dark small">Schedule Date</label>
                                    <input type="date" name="scheduled_date" id="scheduled_date" class="form-control rounded-2 @error('scheduled_date') is-invalid @enderror" min="{{ now()->toDateString() }}" value="{{ old('scheduled_date', now()->addHours(2)->toDateString()) }}">
                                    @error('scheduled_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="scheduled_time" class="form-label fw-semibold text-dark small">Schedule Time</label>
                                    <input type="time" name="scheduled_time" id="scheduled_time" class="form-control rounded-2 @error('scheduled_time') is-invalid @enderror" value="{{ old('scheduled_time', now()->addHours(2)->format('H:i')) }}">
                                    @error('scheduled_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-clock me-1"></i> Current server timezone: {{ config('app.timezone') }}. Laravel Scheduler runs automatically.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Right: Platform Selection & Restrictions -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 80px;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-dark mb-0">Select Target Platforms</h5>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="selectAllPlatforms">Select All</button>
                        </div>
                        <p class="text-muted small mb-3">Choose the platforms to publish to. Only platforms with connected accounts are eligible.</p>

                        @error('platforms')
                            <div class="alert alert-danger py-2 small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="d-flex flex-column gap-3 mb-4">
                            @foreach ($platforms as $key => $p)
                                @php
                                    $isConnected = !empty($p['account']) && $p['account']->is_active;
                                @endphp
                                <div class="border rounded-3 p-3 platform-card {{ $isConnected ? '' : 'bg-light opacity-75' }}" data-platform="{{ $key }}">
                                    <div class="d-flex align-items-start gap-3">
                                        <input type="checkbox" name="platforms[]" value="{{ $key }}" id="platform_{{ $key }}" class="form-check-input mt-1 platform-checkbox" {{ $isConnected ? (is_array(old('platforms')) && in_array($key, old('platforms')) ? 'checked' : '') : 'disabled' }}>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label for="platform_{{ $key }}" class="fw-bold text-dark mb-0 cursor-pointer d-flex align-items-center gap-2">
                                                    <i class="bi {{ $p['icon'] }} fs-5" style="color: {{ $p['color'] }};"></i>
                                                    {{ $p['name'] }}
                                                </label>
                                                @if ($isConnected)
                                                    <span class="badge bg-success-subtle text-success small rounded-pill">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Connected
                                                    </span>
                                                @else
                                                    <a href="{{ route('social.connections.index') }}" class="badge bg-secondary-subtle text-secondary small rounded-pill text-decoration-none">
                                                        <i class="bi bi-plus-circle me-1"></i> Connect
                                                    </a>
                                                @endif
                                            </div>

                                            @if ($isConnected)
                                                <div class="small text-muted mt-1">
                                                    Account: <span class="fw-semibold text-dark">{{ $p['account']->account_name }}</span>
                                                </div>
                                            @endif

                                            <!-- Platform Restrictions Note -->
                                            <div class="small mt-1 text-muted d-flex align-items-center gap-1">
                                                <i class="bi bi-info-circle text-primary"></i>
                                                <span>{{ $p['restriction'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Dynamic Restrictions Warning Box -->
                        <div id="restrictionWarning" class="alert alert-warning py-2 px-3 small rounded-3 d-none mb-3">
                            <div class="d-flex align-items-center gap-2 fw-semibold">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span id="warningMessage"></span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-brand w-100 py-3 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="submitBtn">
                            <i class="bi bi-send-fill" id="btnIcon"></i>
                            <span id="btnText">Publish Now</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.cursor-pointer { cursor: pointer; }
.action-type-card:has(input:checked) {
    border-color: var(--brand-primary) !important;
    background-color: #F5F3FF;
}
.platform-card:has(input:checked) {
    border-color: var(--brand-primary) !important;
    background-color: #FAFAFF;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contentTextarea = document.getElementById('content');
    const charCounter = document.getElementById('charCounter');
    const radioNow = document.getElementById('radioNow');
    const radioSchedule = document.getElementById('radioSchedule');
    const scheduleFields = document.getElementById('scheduleFields');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');
    const mediaInput = document.getElementById('mediaInput');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');
    const removeMediaBtn = document.getElementById('removeMediaBtn');
    const mediaFileName = document.getElementById('mediaFileName');
    const platformCheckboxes = document.querySelectorAll('.platform-checkbox');
    const selectAllBtn = document.getElementById('selectAllPlatforms');
    const restrictionWarning = document.getElementById('restrictionWarning');
    const warningMessage = document.getElementById('warningMessage');

    let currentMediaType = null; // 'image', 'video', or null

    // Character counter
    contentTextarea.addEventListener('input', function () {
        const count = this.value.length;
        charCounter.textContent = `${count} characters`;
        validateRestrictions();
    });

    // Schedule toggle
    function updateScheduleUI() {
        if (radioSchedule.checked) {
            scheduleFields.classList.remove('d-none');
            btnText.textContent = 'Schedule Post';
            btnIcon.className = 'bi bi-calendar-check-fill';
        } else {
            scheduleFields.classList.add('d-none');
            btnText.textContent = 'Publish Now';
            btnIcon.className = 'bi bi-send-fill';
        }
    }
    radioNow.addEventListener('change', updateScheduleUI);
    radioSchedule.addEventListener('change', updateScheduleUI);
    updateScheduleUI();

    // Select all platforms
    selectAllBtn.addEventListener('click', function () {
        const enabledCheckboxes = document.querySelectorAll('.platform-checkbox:not(:disabled)');
        const allChecked = Array.from(enabledCheckboxes).every(cb => cb.checked);
        enabledCheckboxes.forEach(cb => cb.checked = !allChecked);
        selectAllBtn.textContent = allChecked ? 'Select All' : 'Deselect All';
        validateRestrictions();
    });

    // File Preview Handler
    mediaInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            resetMediaPreview();
            return;
        }

        mediaFileName.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        const fileType = file.type;

        uploadPlaceholder.classList.add('d-none');
        previewContainer.classList.remove('d-none');

        if (fileType.startsWith('image/')) {
            currentMediaType = 'image';
            imagePreview.src = URL.createObjectURL(file);
            imagePreview.classList.remove('d-none');
            videoPreview.classList.add('d-none');
            videoPreview.pause();
        } else if (fileType.startsWith('video/')) {
            currentMediaType = 'video';
            videoPreview.src = URL.createObjectURL(file);
            videoPreview.classList.remove('d-none');
            imagePreview.classList.add('d-none');
        }

        validateRestrictions();
    });

    // Remove Media
    removeMediaBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        resetMediaPreview();
    });

    function resetMediaPreview() {
        mediaInput.value = '';
        currentMediaType = null;
        imagePreview.src = '#';
        imagePreview.classList.add('d-none');
        videoPreview.pause();
        videoPreview.src = '';
        videoPreview.classList.add('d-none');
        previewContainer.classList.add('d-none');
        uploadPlaceholder.classList.remove('d-none');
        validateRestrictions();
    }

    // Platform Restrictions Live Validator
    function validateRestrictions() {
        const warnings = [];
        const isInstagramChecked = document.getElementById('platform_instagram')?.checked;
        const isTikTokChecked = document.getElementById('platform_tiktok')?.checked;

        if (isInstagramChecked && !currentMediaType) {
            warnings.push('Instagram requires an image or video file.');
        }

        if (isTikTokChecked && currentMediaType !== 'video') {
            warnings.push('TikTok requires an uploaded video file (.mp4/.mov).');
        }

        if (warnings.length > 0) {
            warningMessage.textContent = warnings.join(' ');
            restrictionWarning.classList.remove('d-none');
        } else {
            restrictionWarning.classList.add('d-none');
        }
    }

    platformCheckboxes.forEach(cb => {
        cb.addEventListener('change', validateRestrictions);
    });

    validateRestrictions();
});
</script>
@endpush
