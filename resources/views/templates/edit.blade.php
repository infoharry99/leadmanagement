@extends('layouts.app')

@section('title', 'Edit Email Template')

@section('content')
<div class="mb-4">
    <a href="{{ route('templates.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Templates
    </a>
    <h4 class="fw-bold mb-1">Edit Template: {{ $template->name }}</h4>
    <p class="text-muted small">Update messaging copy and variables.</p>
</div>

<form action="{{ route('templates.update', $template) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">Template Details</div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select">
                            <option value="cold_outreach" {{ $template->category === 'cold_outreach' ? 'selected' : '' }}>Cold Outreach</option>
                            <option value="follow_up" {{ $template->category === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="case_study" {{ $template->category === 'case_study' ? 'selected' : '' }}>Case Study Showcase</option>
                            <option value="newsletter" {{ $template->category === 'newsletter' ? 'selected' : '' }}>Product Newsletter</option>
                            <option value="re_engagement" {{ $template->category === 're_engagement' ? 'selected' : '' }}>Re-engagement</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">HTML Body Content <span class="text-danger">*</span></label>
                        <textarea name="body_html" class="form-control font-monospace" rows="10" required>{{ old('body_html', $template->body_html) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Plain Text Version (Optional)</label>
                        <textarea name="body_plain" class="form-control" rows="4">{{ old('body_plain', $template->body_plain) }}</textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Save Changes</button>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-bold"><i class="bi bi-code-slash text-primary me-2"></i>Available Variables</div>
                <div class="card-body p-3 small">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><code>&#123;&#123;first_name&#125;&#125;</code> — Recipient first name</li>
                        <li><code>&#123;&#123;full_name&#125;&#125;</code> — Recipient complete name</li>
                        <li><code>&#123;&#123;company_name&#125;&#125;</code> — Lead's company</li>
                        <li><code>&#123;&#123;industry&#125;&#125;</code> — Industry vertical</li>
                        <li><code>&#123;&#123;service_name&#125;&#125;</code> — Nexteck services</li>
                        <li><code>&#123;&#123;unsubscribe_url&#125;&#125;</code> — Tokenized unsubscribe link</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
