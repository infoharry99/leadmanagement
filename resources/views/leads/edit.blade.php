@extends('layouts.app')

@section('title', 'Edit Lead: ' . $lead->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Lead Details
    </a>
    <h4 class="fw-bold mb-1">Edit Lead: {{ $lead->name }}</h4>
    <p class="text-muted small">Update profile, status progression, and assigned account manager.</p>
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

<form action="{{ route('leads.update', $lead) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i>Contact Information
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $lead->company?->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $lead->job_title) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company Website</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website', $lead->website) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Industry</label>
                            <input type="text" name="industry" class="form-control" value="{{ old('industry', $lead->industry) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $lead->country) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-sticky text-warning me-2"></i>Notes & Scope
                </div>
                <div class="card-body p-3">
                    <textarea name="notes" class="form-control" rows="4">{{ old('notes', $lead->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-sliders text-info me-2"></i>Pipeline Settings
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lead Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $stKey => $stLabel)
                                <option value="{{ $stKey }}" {{ old('status', $lead->status) === $stKey ? 'selected' : '' }}>
                                    {{ $stLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lead Source</label>
                        <select name="lead_source_id" class="form-select">
                            <option value="">Select source...</option>
                            @foreach($sources as $src)
                                <option value="{{ $src->id }}" {{ old('lead_source_id', $lead->lead_source_id) == $src->id ? 'selected' : '' }}>
                                    {{ $src->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Account Manager</label>
                        <select name="assigned_user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('assigned_user_id', $lead->assigned_user_id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Next Follow-Up</label>
                        <input type="datetime-local" name="next_followup_at" class="form-control" value="{{ old('next_followup_at', $lead->next_followup_at?->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tags</label>
                        <select name="tag_ids[]" class="form-select" multiple style="height: 110px;">
                            @foreach($tags as $t)
                                <option value="{{ $t->id }}" {{ in_array($t->id, old('tag_ids', $lead->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                Save Changes
            </button>
        </div>
    </div>
</form>
@endsection
