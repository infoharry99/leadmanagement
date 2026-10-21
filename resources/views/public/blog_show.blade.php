<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $blog->meta_title ?: $blog->title }}</title>
    <meta name="description" content="{{ $blog->meta_description ?: $blog->summary }}">
    <link rel="canonical" href="{{ $blog->canonical_url ?: url('/blog/' . $blog->slug) }}">
    <meta name="robots" content="{{ $blog->robots_directive ?? 'index, follow' }}">

    <!-- Open Graph Metadata -->
    <meta property="og:title" content="{{ $blog->og_title ?: $blog->title }}">
    <meta property="og:description" content="{{ $blog->og_description ?: $blog->summary }}">
    <meta property="og:url" content="{{ $blog->canonical_url ?: url('/blog/' . $blog->slug) }}">
    <meta property="og:type" content="article">
    @if($blog->og_image)
        <meta property="og:image" content="{{ $blog->og_image }}">
    @endif

    <!-- JSON-LD Structured Data Schema -->
    <script type="application/ld+json">
        {!! json_encode($blog->generateJsonLdSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    <!-- Google Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #0F172A; }
        .article-content { font-size: 1.05rem; line-height: 1.8; color: #334155; }
        .article-content h2 { margin-top: 2rem; margin-bottom: 1rem; font-weight: 700; color: #0F172A; }
        .article-content h3 { margin-top: 1.5rem; margin-bottom: 0.75rem; font-weight: 600; color: #1E293B; }
        .article-content p { margin-bottom: 1.25rem; }
        .article-content ul, .article-content ol { margin-bottom: 1.25rem; }
    </style>
</head>
<body>
    <!-- Public Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3" style="background-color: #0F172A !important;">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('public.blog.index') }}">
                <div class="rounded-2 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 800;">N</div>
                NEXTECK
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="{{ route('public.blog.index') }}" class="text-white-50 text-decoration-none small">All Articles</a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">CRM Portal</a>
            </div>
        </div>
    </nav>

    <!-- Article Header -->
    <header class="py-5 bg-white border-bottom">
        <div class="container" style="max-width: 820px;">
            <div class="mb-3">
                <span class="badge bg-light text-primary border text-uppercase px-2.5 py-1">{{ $blog->category?->name ?? 'Article' }}</span>
                <span class="text-muted small ms-2">{{ $blog->published_at ? $blog->published_at->format('F d, Y') : 'Recent' }}</span>
            </div>
            <h1 class="fw-extrabold display-6 mb-3 text-dark">{{ $blog->title }}</h1>
            <p class="lead text-secondary mb-4">{{ $blog->summary }}</p>

            <div class="d-flex align-items-center gap-3 pt-3 border-top">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                    {{ substr($blog->author?->name ?? 'N', 0, 1) }}
                </div>
                <div>
                    <div class="fw-semibold">{{ $blog->author?->name ?? 'Nexteck Editorial Team' }}</div>
                    <small class="text-muted">Enterprise Software Architecture Specialist</small>
                </div>
            </div>
        </div>
    </header>

    <!-- Article Main Body -->
    <main class="container py-5" style="max-width: 820px;">
        <article class="article-content bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-5">
            {!! $blog->body !!}
        </article>

        <!-- Answer Engine Optimization (AEO) FAQs -->
        @if(!empty($blog->aeo_geo_faq))
            <section class="card shadow-sm border-0 bg-light p-4 rounded-4 mb-5">
                <h4 class="fw-bold mb-3"><i class="bi bi-question-circle-fill text-primary me-2"></i>Frequently Asked Questions</h4>
                <div class="accordion" id="publicFaqAccordion">
                    @foreach($blog->aeo_geo_faq as $i => $faq)
                        <div class="accordion-item mb-2 border rounded-3 bg-white">
                            <h3 class="accordion-header" id="faqHeading_{{ $i }}">
                                <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse_{{ $i }}">
                                    {{ $faq['question'] }}
                                </button>
                            </h3>
                            <div id="faqCollapse_{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#publicFaqAccordion">
                                <div class="accordion-body text-secondary small py-3" style="line-height: 1.6;">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <footer class="py-4 text-center text-muted small border-top bg-white">
        <div class="container">
            © {{ date('Y') }} Nexteck Technologies. Custom Software, Web Applications & Lead Generation Systems.
        </div>
    </footer>
</body>
</html>
