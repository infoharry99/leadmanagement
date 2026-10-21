@extends('layouts.app')

@section('title', 'Create Email Campaign')

@section('content')
<div class="mb-4">
    <a href="{{ route('campaigns.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Campaigns
    </a>
    <h4 class="fw-bold mb-1">Create Outreach Campaign</h4>
    <p class="text-muted small">Target segmented prospects, select approved templates, and configure queue dispatching.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <div class="fw-bold mb-1">Please correct the following errors:</div>
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('campaigns.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-envelope text-primary me-2"></i>Campaign Settings & Content
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Q4 Enterprise Modernization Outreach">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Email Subject Line <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required placeholder="e.g. Accelerating @{{company_name}}'s software roadmap with Nexteck">
                            <div class="form-text small">Supports personalized tokens: <code>&#123;&#123;first_name&#125;&#125;</code>, <code>&#123;&#123;company_name&#125;&#125;</code>, <code>&#123;&#123;industry&#125;&#125;</code></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Select Email Template <span class="text-danger">*</span></label>
                            <select name="email_template_id" class="form-select" required>
                                <option value="">Choose approved template...</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" {{ old('email_template_id') == $tmpl->id ? 'selected' : '' }}>
                                        {{ $tmpl->name }} ({{ ucfirst($tmpl->category) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">From Name <span class="text-danger">*</span></label>
                            <input type="text" name="from_name" class="form-control" value="{{ old('from_name', 'Nexteck Outreach Team') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">From Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="from_email" class="form-control" value="{{ old('from_email', 'outreach@nexteck.com') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Reply-To Address</label>
                            <input type="email" name="reply_to" class="form-control" value="{{ old('reply_to', 'replies@nexteck.com') }}" placeholder="replies@nexteck.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Schedule Time (Optional)</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audience Filters Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-funnel text-success me-2"></i>Target Audience Filters
                </div>
                <div class="card-body p-3">
                    <p class="text-muted small">Only non-suppressed, eligible leads matching these criteria will be queued:</p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Filter by Industry</label>
                        <select name="industry" class="form-select form-select-sm">
                            <option value="">All Industries</option>
                            @foreach($industries as $ind)
                                <option value="{{ $ind }}" {{ old('industry') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Filter by Status</label>
                        <select name="lead_status" class="form-select form-select-sm">
                            <option value="">Any Status (New & Contacted)</option>
                            <option value="new" {{ old('lead_status') === 'new' ? 'selected' : '' }}>New Leads</option>
                            <option value="contacted" {{ old('lead_status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Filter by Source</label>
                        <select name="lead_source_id" class="form-select form-select-sm">
                            <option value="">All Lead Sources</option>
                            @foreach($sources as $src)
                                <option value="{{ $src->id }}" {{ old('lead_source_id') == $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-2.5 bg-light rounded small border mb-3">
                        <i class="bi bi-shield-lock text-success me-1"></i> Permanent suppression list will automatically filter out opted-out contacts.
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                        Create & Build Audience
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
