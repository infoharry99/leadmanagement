@extends('layouts.app')

@section('title', 'Add New Lead')

@section('content')
<div class="mb-4">
    <a href="{{ route('leads.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Leads Pipeline
    </a>
    <h4 class="fw-bold mb-1">Create New Lead</h4>
    <p class="text-muted small">Record prospect details, assign account owner, and document lawful consent basis.</p>
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

<form action="{{ route('leads.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <!-- Contact & Company Information -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i>Contact Information
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Sarah Connor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="sarah@enterprise.com">
                            <div class="form-text small">Checked automatically against duplicate database & suppression lists.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="e.g. Acme Tech Solutions">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}" placeholder="e.g. CTO / VP Engineering">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company Website</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website') }}" placeholder="https://acme.com">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Industry</label>
                            <input type="text" name="industry" class="form-control" value="{{ old('industry') }}" placeholder="e.g. Software & SaaS">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country') }}" placeholder="United States">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="Austin">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Background -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-sticky text-warning me-2"></i>Discovery Notes & Project Scope
                </div>
                <div class="card-body p-3">
                    <textarea name="notes" class="form-control" rows="4" placeholder="Detail any project requirements, stack preferences, or conversation notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Pipeline Assignment & Compliance Consent -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-sliders text-info me-2"></i>CRM & Pipeline Settings
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lead Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $stKey => $stLabel)
                                <option value="{{ $stKey }}" {{ old('status', 'new') === $stKey ? 'selected' : '' }}>
                                    {{ $stLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lead Source</label>
                        <select name="lead_source_id" class="form-select">
                            <option value="">Select acquisition source...</option>
                            @foreach($sources as $src)
                                <option value="{{ $src->id }}" {{ old('lead_source_id') == $src->id ? 'selected' : '' }}>
                                    {{ $src->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Manager / Rep</label>
                        <select name="assigned_user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('assigned_user_id', auth()->id()) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Next Follow-Up Date</label>
                        <input type="datetime-local" name="next_followup_at" class="form-control" value="{{ old('next_followup_at') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tags</label>
                        <select name="tag_ids[]" class="form-select" multiple style="height: 110px;">
                            @foreach($tags as $t)
                                <option value="{{ $t->id }}" {{ (collect(old('tag_ids'))->contains($t->id)) ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Hold Ctrl / Cmd to select multiple tags.</div>
                    </div>
                </div>
            </div>

            <!-- Lawful Basis & Consent Audit -->
            <div class="card shadow-sm mb-4 border-info">
                <div class="card-header bg-info bg-opacity-10 fw-bold text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Consent & Lawful Outreach Basis
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Legal Basis</label>
                        <select name="consent_basis" class="form-select form-select-sm">
                            <option value="legitimate_interest" {{ old('consent_basis') === 'legitimate_interest' ? 'selected' : '' }}>Legitimate Interest (B2B)</option>
                            <option value="explicit_consent" {{ old('consent_basis') === 'explicit_consent' ? 'selected' : '' }}>Explicit Opt-In / Consent</option>
                            <option value="contractual" {{ old('consent_basis') === 'contractual' ? 'selected' : '' }}>Contractual / Pre-contractual</option>
                            <option value="public_record" {{ old('consent_basis') === 'public_record' ? 'selected' : '' }}>Public Business Registry</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Consent Source</label>
                        <input type="text" name="consent_source" class="form-control form-control-sm" value="{{ old('consent_source', 'Direct CRM Entry') }}" placeholder="e.g. LinkedIn public profile">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                Save & Initialize Lead
            </button>
        </div>
    </div>
</form>
@endsection
