@extends('layouts.app')

@section('title', 'Create Email Template')

@section('content')
<div class="mb-4">
    <a href="{{ route('templates.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Templates
    </a>
    <h4 class="fw-bold mb-1">Create Email Template</h4>
    <p class="text-muted small">Compose reusable messaging with personalized dynamic variables and compliance links.</p>
</div>

<form action="{{ route('templates.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">Template Details</div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Cold Outreach - CTO Value Proposition">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select">
                            <option value="cold_outreach">Cold Outreach</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="case_study">Case Study Showcase</option>
                            <option value="newsletter">Product Newsletter</option>
                            <option value="re_engagement">Re-engagement</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required placeholder="e.g. Accelerating @{{company_name}}'s digital engineering with Nexteck">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">HTML Body Content <span class="text-danger">*</span></label>
                        <textarea name="body_html" class="form-control font-monospace" rows="10" required placeholder="<p>Hi @{{first_name}},</p><p>We help @{{company_name}} build scalable Laravel applications...</p><p><a href='@{{unsubscribe_url}}'>Unsubscribe</a></p>">{{ old('body_html') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Plain Text Version (Optional)</label>
                        <textarea name="body_plain" class="form-control" rows="4" placeholder="Hi @{{first_name}}, We help @{{company_name}}... Unsubscribe: @{{unsubscribe_url}}">{{ old('body_plain') }}</textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Save Template</button>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-bold"><i class="bi bi-code-slash text-primary me-2"></i>Available Variables</div>
                <div class="card-body p-3 small">
                    <p class="text-muted">Click or copy any variable into your subject line or email body:</p>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><code>&#123;&#123;first_name&#125;&#125;</code> — Recipient first name</li>
                        <li><code>&#123;&#123;full_name&#125;&#125;</code> — Recipient complete name</li>
                        <li><code>&#123;&#123;company_name&#125;&#125;</code> — Lead's company or organization</li>
                        <li><code>&#123;&#123;job_title&#125;&#125;</code> — Lead's professional title</li>
                        <li><code>&#123;&#123;industry&#125;&#125;</code> — Target industry</li>
                        <li><code>&#123;&#123;service_name&#125;&#125;</code> — Nexteck service showcase</li>
                        <li><code>&#123;&#123;website&#125;&#125;</code> — Lead company website</li>
                        <li><code>&#123;&#123;unsubscribe_url&#125;&#125;</code> — <strong>Mandatory</strong> tokenized opt-out link</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
