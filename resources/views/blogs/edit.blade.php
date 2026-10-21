@extends('layouts.app')

@section('title', 'Edit Blog: ' . $blog->title)

@section('content')
<div class="mb-4">
    <a href="{{ route('blogs.show', $blog) }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Article Details
    </a>
    <h4 class="fw-bold mb-1">Edit Article: {{ $blog->title }}</h4>
    <p class="text-muted small">Update content, keywords, and Answer Engine Optimization (AEO) FAQs.</p>
</div>

<form action="{{ route('blogs.update', $blog) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>Article Body & Structure
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Article Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $blog->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">URL Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $blog->slug) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Executive Summary</label>
                        <textarea name="summary" class="form-control" rows="2">{{ old('summary', $blog->summary) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Article Body Content (HTML) <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="14" required>{{ old('body', $blog->body) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- AEO FAQ Builder -->
            <div class="card shadow-sm mb-4 border-info">
                <div class="card-header bg-info bg-opacity-10 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-question-circle text-info me-2"></i>AEO & GEO Q&A Entities</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFaqRow()">
                        <i class="bi bi-plus-lg"></i> Add Q&A Entity
                    </button>
                </div>
                <div class="card-body p-3" id="faqContainer">
                    @forelse($blog->aeo_geo_faq ?? [] as $faq)
                        <div class="p-3 bg-light rounded-3 border mb-2 faq-row">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-semibold mb-0">Question</label>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.faq-row').remove()">Remove</button>
                            </div>
                            <input type="text" name="aeo_faq_questions[]" class="form-control form-control-sm mb-2" value="{{ $faq['question'] ?? '' }}">
                            <label class="form-label small fw-semibold">Direct Answer</label>
                            <textarea name="aeo_faq_answers[]" class="form-control form-control-sm" rows="2">{{ $faq['answer'] ?? '' }}</textarea>
                        </div>
                    @empty
                        <div class="p-3 bg-light rounded-3 border mb-2 faq-row">
                            <label class="form-label small fw-semibold">Question</label>
                            <input type="text" name="aeo_faq_questions[]" class="form-control form-control-sm mb-2" placeholder="e.g. Why is Laravel suitable for enterprise platforms?">
                            <label class="form-label small fw-semibold">Direct Answer</label>
                            <textarea name="aeo_faq_answers[]" class="form-control form-control-sm" rows="2" placeholder="Factual direct answer..."></textarea>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-search text-success me-2"></i>SEO & Search Intent
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Primary Keyword <span class="text-danger">*</span></label>
                        <input type="text" name="primary_keyword" class="form-control form-control-sm" value="{{ old('primary_keyword', $blog->primary_keyword) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Secondary Keywords</label>
                        <input type="text" name="secondary_keywords" class="form-control form-control-sm" value="{{ old('secondary_keywords', is_array($blog->secondary_keywords) ? implode(', ', $blog->secondary_keywords) : '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Search Intent</label>
                        <select name="search_intent" class="form-select form-select-sm">
                            <option value="informational" {{ $blog->search_intent === 'informational' ? 'selected' : '' }}>Informational</option>
                            <option value="commercial" {{ $blog->search_intent === 'commercial' ? 'selected' : '' }}>Commercial Investigation</option>
                            <option value="transactional" {{ $blog->search_intent === 'transactional' ? 'selected' : '' }}>Transactional</option>
                            <option value="navigational" {{ $blog->search_intent === 'navigational' ? 'selected' : '' }}>Navigational</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm" value="{{ old('meta_title', $blog->meta_title) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description" class="form-control form-control-sm" rows="3">{{ old('meta_description', $blog->meta_description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control form-control-sm" value="{{ old('canonical_url', $blog->canonical_url) }}">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-tags text-warning me-2"></i>Publishing Settings
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Publication Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="draft" {{ $blog->status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="review" {{ $blog->status === 'review' ? 'selected' : '' }}>Under Review</option>
                            <option value="published" {{ $blog->status === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">Select category...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $blog->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tags</label>
                        <select name="tag_ids[]" class="form-select form-select-sm" multiple style="height: 100px;">
                            @foreach($tags as $tg)
                                <option value="{{ $tg->id }}" {{ in_array($tg->id, old('tag_ids', $blog->tags->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $tg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                Save & Recalculate SEO Score
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
            <input type="text" name="aeo_faq_questions[]" class="form-control form-control-sm mb-2" placeholder="e.g. Question...">
            <label class="form-label small fw-semibold">Direct Answer</label>
            <textarea name="aeo_faq_answers[]" class="form-control form-control-sm" rows="2" placeholder="Direct answer..."></textarea>
        `;
        container.appendChild(row);
    }
</script>
@endsection
