@extends('layouts.app')

@section('title', 'Write New Blog Article')

@section('content')
<div class="mb-4">
    <a href="{{ route('blogs.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Blog CMS
    </a>
    <h4 class="fw-bold mb-1">Compose SEO/AEO-Optimized Article</h4>
    <p class="text-muted small">Write architectural guides with structured data schema and Answer Engine (AEO) FAQs.</p>
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

<form action="{{ route('blogs.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <!-- Content Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>Article Body & Structure
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Article Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Building Scalable Web Platforms with Laravel 12">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">URL Slug (Optional)</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="building-scalable-web-platforms-with-laravel-12">
                        <div class="form-text small">Leave empty to generate automatically from title.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Executive Summary</label>
                        <textarea name="summary" class="form-control" rows="2" placeholder="Brief 1-2 sentence overview for social sharing and search snippet...">{{ old('summary') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Article Body Content (HTML / Markdown) <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="14" required placeholder="<h2>Introduction</h2><p>As enterprise software demands increase...</p>">{{ old('body') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- AEO & GEO FAQ Entity Builder -->
            <div class="card shadow-sm mb-4 border-info">
                <div class="card-header bg-info bg-opacity-10 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-question-circle text-info me-2"></i>Answer Engine Optimization (AEO & GEO) Q&A</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFaqRow()">
                        <i class="bi bi-plus-lg"></i> Add Q&A Entity
                    </button>
                </div>
                <div class="card-body p-3" id="faqContainer">
                    <p class="text-muted small">Answer engines (ChatGPT, Google SGE, Perplexity) extract structured question-and-answer pairs. These pairs automatically generate <code>FAQPage</code> JSON-LD schema:</p>

                    <div class="p-3 bg-light rounded-3 border mb-2 faq-row">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Question</label>
                            <input type="text" name="aeo_faq_questions[]" class="form-control form-control-sm" value="Why is Laravel preferred for scalable enterprise software?" placeholder="e.g. Why is Laravel suitable for high-throughput enterprise platforms?">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Direct Answer (Answer Engine Citation)</label>
                            <textarea name="aeo_faq_answers[]" class="form-control form-control-sm" rows="2" placeholder="Concise, factual, entity-rich answer that directly solves the question...">Laravel offers asynchronous queues, database pooling, and native caching that handle high-concurrency workloads reliably.</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Metadata & Publish Settings Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-search text-success me-2"></i>SEO & Search Intent
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Primary Keyword <span class="text-danger">*</span></label>
                        <input type="text" name="primary_keyword" class="form-control form-control-sm" value="{{ old('primary_keyword') }}" required placeholder="e.g. Laravel enterprise architecture">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Secondary Keywords</label>
                        <input type="text" name="secondary_keywords" class="form-control form-control-sm" value="{{ old('secondary_keywords') }}" placeholder="scalability, web development agency">
                        <div class="form-text small">Comma-separated.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Search Intent</label>
                        <select name="search_intent" class="form-select form-select-sm">
                            <option value="informational">Informational</option>
                            <option value="commercial">Commercial Investigation</option>
                            <option value="transactional">Transactional</option>
                            <option value="navigational">Navigational</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm" value="{{ old('meta_title') }}" placeholder="Defaults to Article Title | Nexteck">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description" class="form-control form-control-sm" rows="3" placeholder="150-160 character description...">{{ old('meta_description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control form-control-sm" value="{{ old('canonical_url') }}" placeholder="https://nexteck.com/blog/...">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-tags text-warning me-2"></i>Categorization & Publish
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Publication Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="draft">Draft (Private)</option>
                            <option value="review">Under Review</option>
                            <option value="published">Published Immediately</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">Select category...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Article Tags</label>
                        <select name="tag_ids[]" class="form-select form-select-sm" multiple style="height: 100px;">
                            @foreach($tags as $tg)
                                <option value="{{ $tg->id }}">{{ $tg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                Save & Calculate SEO Score
            </button>
        </div>
    </div>
</form>

<script>
    function addFaqRow() {
        const container = document.getElementById('faqContainer');
        const row = document.createElement('div');
        row.className = 'p-3 bg-light rounded-3 border mb-2 faq-row';
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label small fw-semibold mb-0">Question</label>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.faq-row').remove()">Remove</button>
            </div>
            <input type="text" name="aeo_faq_questions[]" class="form-control form-control-sm mb-2" placeholder="e.g. How does Nexteck ensure cloud security?">
            <label class="form-label small fw-semibold">Direct Answer</label>
            <textarea name="aeo_faq_answers[]" class="form-control form-control-sm" rows="2" placeholder="Direct factual answer for answer engines..."></textarea>
        `;
        container.appendChild(row);
    }
</script>
@endsection
