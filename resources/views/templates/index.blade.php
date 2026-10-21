@extends('layouts.app')

@section('title', 'Email Templates')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Reusable Email Templates</h4>
        <p class="text-muted small mb-0">Create personalized cold outreach, follow-ups, and reactivation templates with variable tokens.</p>
    </div>
    <a href="{{ route('templates.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
        <i class="bi bi-plus-lg"></i> New Template
    </a>
</div>

<div class="row g-4">
    @forelse($templates as $tmpl)
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">{{ $tmpl->name }}</span>
                        <span class="badge bg-light text-dark border ms-1">{{ ucfirst(str_replace('_', ' ', $tmpl->category)) }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                            <li><a class="dropdown-item" href="{{ route('templates.edit', $tmpl) }}"><i class="bi bi-pencil me-2"></i>Edit Template</a></li>
                            <li>
                                <form action="{{ route('templates.destroy', $tmpl) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="small text-muted mb-2">Subject: <strong class="text-dark">{{ $tmpl->subject }}</strong></div>
                    <div class="p-3 bg-light rounded border small text-secondary" style="max-height: 150px; overflow-y: auto;">
                        {!! $tmpl->body_html !!}
                    </div>

                    <div class="mt-3">
                        <small class="text-muted fw-semibold">Personalized Variables:</small>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($tmpl->variables_schema ?? ['first_name', 'company_name', 'unsubscribe_url'] as $var)
                                <code class="badge bg-white text-primary border" style="font-size: 0.72rem;">&#123;&#123;{{ $var }}&#125;&#125;</code>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between">
                    <span>Used in {{ $tmpl->campaigns->count() }} campaigns</span>
                    <span>Created by {{ $tmpl->createdBy?->name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-envelope-paper text-muted fs-1 mb-2 d-block"></i>
            <h6 class="fw-bold">No templates found</h6>
            <a href="{{ route('templates.create') }}" class="btn btn-primary btn-sm">Create First Template</a>
        </div>
    @endforelse
</div>

@if($templates->hasPages())
    <div class="mt-4">
        {{ $templates->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
