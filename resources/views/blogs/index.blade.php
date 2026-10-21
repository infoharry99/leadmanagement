@extends('layouts.app')

@section('title', 'Blog CMS & SEO Hub')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Blog CMS & SEO/AEO/GEO Engine</h4>
        <p class="text-muted small mb-0">Publish search-optimized articles, manage Answer Engine (AEO) FAQs, and monitor JSON-LD structured data.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('public.blog.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Public Blog
        </a>
        <a href="{{ route('public.sitemap') }}" target="_blank" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-diagram-2 me-1"></i> Sitemap.xml
        </a>
        <a href="{{ route('blogs.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Write Article
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Article Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Primary Keyword</th>
                    <th>SEO Completeness</th>
                    <th>Published At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blogs as $blog)
                    <tr>
                        <td>
                            <div class="fw-bold">
                                <a href="{{ route('blogs.show', $blog) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $blog->title }}
                                </a>
                            </div>
                            <small class="text-muted">Slug: <code>/blog/{{ $blog->slug }}</code> • Rev #{{ $blog->revision_number }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $blog->category?->name ?? 'General' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $blog->status === 'published' ? 'success' : 'secondary' }} px-2 py-1">
                                {{ ucfirst($blog->status) }}
                            </span>
                        </td>
                        <td>
                            <small class="text-secondary fw-semibold">{{ $blog->primary_keyword ?? '—' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $blog->seo_score >= 80 ? 'success' : ($blog->seo_score >= 50 ? 'warning' : 'danger') }}" style="width: {{ $blog->seo_score }}%"></div>
                                </div>
                                <span class="small fw-bold {{ $blog->seo_score >= 80 ? 'text-success' : 'text-muted' }}">
                                    {{ $blog->seo_score }}/100
                                </span>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Draft' }}</small>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                                    <li><a class="dropdown-item" href="{{ route('blogs.show', $blog) }}"><i class="bi bi-eye text-primary me-2"></i>Inspect SEO & JSON-LD</a></li>
                                    <li><a class="dropdown-item" href="{{ route('blogs.edit', $blog) }}"><i class="bi bi-pencil text-secondary me-2"></i>Edit Article</a></li>
                                    <li><a class="dropdown-item" href="{{ route('public.blog.show', $blog->slug) }}" target="_blank"><i class="bi bi-box-arrow-up-right text-info me-2"></i>View Live Page</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('Delete this blog?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-richtext text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold">No articles published yet</h6>
                            <a href="{{ route('blogs.create') }}" class="btn btn-primary btn-sm mt-2">Write First Article</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($blogs->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            {{ $blogs->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
