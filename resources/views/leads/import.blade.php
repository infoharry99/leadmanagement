@extends('layouts.app')

@section('title', 'Import Leads CSV')

@section('content')
<div class="mb-4">
    <a href="{{ route('leads.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Leads Pipeline
    </a>
    <h4 class="fw-bold mb-1">Bulk Lead Import</h4>
    <p class="text-muted small">Upload prospect lists via CSV with duplicate detection and automatic suppression checking.</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-cloud-arrow-up text-primary me-2"></i>Upload CSV File
            </div>
            <div class="card-body p-4">
                <form action="{{ route('leads.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Choose CSV Document <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv,text/plain" required>
                        <div class="form-text small">Standard comma-separated CSV with headers (Name, Email, Company, Job Title, Industry, etc.).</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Assign Imported Leads To</label>
                        <select name="assigned_user_id" class="form-select">
                            <option value="">Leave Unassigned</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role)) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="update_duplicates" value="1" id="updateDuplicatesSwitch">
                            <label class="form-check-label fw-semibold small" for="updateDuplicatesSwitch">
                                Update existing leads if email matches
                            </label>
                        </div>
                        <div class="form-text small">If unchecked, existing leads with the same email will be safely skipped.</div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="fw-semibold small text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i>Suppression Enforcement</div>
                        <p class="small text-muted mb-0">Contacts appearing on your permanent suppression list (opted-out, hard-bounced) will be automatically skipped and will never be imported into active outreach pipelines.</p>
                    </div>

                    <button type="submit" class="btn btn-primary py-2 px-4 fw-bold">
                        <i class="bi bi-upload me-1"></i> Start Import Process
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Instructions & Sample Structure -->
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-info-circle text-info me-2"></i>Expected CSV Headers
            </div>
            <div class="card-body p-3 small">
                <p class="text-muted">The engine automatically recognizes common variations of column headers:</p>
                <ul class="list-unstyled mb-3 d-flex flex-column gap-1.5">
                    <li><strong class="text-primary">Name</strong>: <code>Name</code>, <code>Full Name</code>, <code>Contact Name</code></li>
                    <li><strong class="text-primary">Email</strong>: <code>Email</code>, <code>Email Address</code> (Required)</li>
                    <li><strong>Company</strong>: <code>Company</code>, <code>Company Name</code></li>
                    <li><strong>Job Title</strong>: <code>Job Title</code>, <code>Title</code>, <code>Role</code></li>
                    <li><strong>Industry</strong>: <code>Industry</code>, <code>Sector</code></li>
                    <li><strong>Phone</strong>: <code>Phone</code>, <code>Mobile</code>, <code>Tel</code></li>
                    <li><strong>Website</strong>: <code>Website</code>, <code>URL</code>, <code>Domain</code></li>
                    <li><strong>Location</strong>: <code>Country</code>, <code>City</code></li>
                </ul>

                <div class="p-2.5 bg-dark text-white rounded font-monospace" style="font-size: 0.72rem;">
                    Name,Email,Company,Job Title,Industry,Phone<br>
                    John Smith,john@techwave.io,TechWave,CTO,Software,+15550190<br>
                    Lisa Wang,lisa@retailpro.com,RetailPro,VP Ops,E-Commerce,+15550191
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
