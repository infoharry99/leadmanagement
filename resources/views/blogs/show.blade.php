@extends('layouts.app')

@section('title', 'Blog Details: ' . $blog->title)

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <a href="{{ route('blogs.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1">
            <i class="bi bi-arrow-left"></i> Back to Blog CMS
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $blog->title }}</h4>
            <span class="badge bg-{{ $blog->status === 'published' ? 'success' : 'secondary' }} px-2 py-1">
                {{ ucfirst($blog->status) }}
            </span>
        </div>
        <p class="text-muted small mb-0">Slug: <code>/blog/{{ $blog->slug }}</code> • Revision #{{ $blog->revision_number }}</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('public.blog.show', $blog->slug) }}" target="_blank" class="btn btn-outline-info btn-sm shadow-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Public Page
        </a>
        <a href="{{ route('blogs.edit', $blog) }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-pencil"></i> Edit Article
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Rendered Content Preview -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 px-3 fw-bold">
                <i class="bi bi-eye text-primary me-2"></i>Rendered Article Preview
            </div>
            <div class="card-body p-4">
                <div class="text-muted mb-3 fst-italic">{{ $blog->summary }}</div>
                <div class="article-body" style="line-height: 1.8;">
                    {!! $blog->body !!}
                </div>
            </div>
        </div>

        <!-- AEO FAQ Preview -->
        @if(!empty($blog->aeo_geo_faq))
            <div class="card shadow-sm mb-4 border-info">
                <div class="card-header bg-info bg-opacity-10 fw-bold">
                    <i class="bi bi-question-circle text-info me-2"></i>Configured Answer Engine FAQs (AEO/GEO)
                </div>
                <div class="card-body p-3">
                    <div class="accordion" id="faqAccordion">
                        @foreach($blog->aeo_geo_faq as $i => $faq)
                            <div class="accordion-item mb-2 border rounded">
                                <h2 class="accordion-header" id="heading_{{ $i }}">
                                    <button class="accordion-button collapsed py-2 px-3 fw-semibold small" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $i }}">
                                        {{ $faq['question'] }}
                                    </button>
                                </h2>
                                <div id="collapse_{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body small text-muted py-2 px-3 bg-light">
                                        {{ $faq['answer'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- JSON-LD Schema Inspector -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 px-3 fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-braces text-success me-2"></i>Structured Data (JSON-LD Schema)</span>
                <span class="badge bg-light text-dark border">Article + FAQPage + Organization</span>
            </div>
            <div class="card-body p-3">
                <pre class="bg-dark text-white p-3 rounded small mb-0" style="max-height: 250px; overflow-y: auto;"><code>{{ json_encode($blog->generateJsonLdSchema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    </div>

    <!-- SEO & Metadata Sidebar -->
    <div class="col-lg-4">
        <!-- SEO Score Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-speedometer2 text-primary me-2"></i>SEO Completeness</span>
                <span class="badge bg-{{ $blog->seo_score >= 80 ? 'success' : 'warning text-dark' }} fs-6">
                    {{ $blog->seo_score }}/100
                </span>
            </div>
            <div class="card-body p-3 small">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Primary Keyword:</span>
                        <strong class="text-dark">{{ $blog->primary_keyword }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Search Intent:</span>
                        <span class="badge bg-light text-dark border">{{ ucfirst($blog->search_intent) }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Word Count:</span>
                        <span>{{ str_word_count(strip_tags($blog->body)) }} words</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Robots Directive:</span>
                        <span><code>{{ $blog->robots_directive }}</code></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Google SERP Snippet Preview -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold small text-uppercase">
                <i class="bi bi-google text-danger me-1"></i>Search Result Snippet
            </div>
            <div class="card-body p-3">
                <div class="small text-muted mb-1 text-truncate">{{ $blog->canonical_url ?: url('/blog/' . $blog->slug) }}</div>
                <div class="fw-semibold text-primary fs-6 mb-1">{{ $blog->meta_title ?: $blog->title }}</div>
                <div class="small text-muted" style="line-height: 1.4;">{{ Str::limit($blog->meta_description ?: $blog->summary, 150) }}</div>
            </div>
        </div>

        <!-- Revision History Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-transparent fw-bold small text-uppercase">
                <i class="bi bi-clock-history me-1"></i>Revision History ({{ $blog->revisions->count() }})
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush small">
                    @forelse($blog->revisions as $rev)
                        <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Revision #{{ $rev->revision_number }}</div>
                                <small class="text-muted">By {{ $rev->user?->name ?? 'Author' }}</small>
                            </div>
                            <small class="text-muted">{{ $rev->created_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <div class="p-3 text-muted text-center">No previous revisions.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
