<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexteck Engineering Blog | Custom Software, Laravel & Digital Automation</title>
    <meta name="description" content="Technical breakdowns, enterprise Laravel architectures, and software engineering insights from the Nexteck team.">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #0F172A; }
        .hero { background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 100%); color: #fff; padding: 4.5rem 0; }
        .card-blog { border: 1px solid #E2E8F0; border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-blog:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
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
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">CRM Portal</a>
            </div>
        </div>
    </nav>

    <!-- Hero Header -->
    <header class="hero text-center">
        <div class="container">
            <span class="badge bg-primary bg-opacity-25 text-white mb-2 px-3 py-1.5 fw-semibold border border-primary border-opacity-50">Engineering & Architecture</span>
            <h1 class="fw-extrabold display-5 mb-2">Nexteck Insights</h1>
            <p class="text-white-50 lead mx-auto" style="max-width: 620px;">Deep-dives into scalable software development, Laravel architectures, answer engine optimization, and enterprise automation.</p>
        </div>
    </header>

    <!-- Articles Grid -->
    <main class="container py-5">
        <div class="row g-4">
            @forelse($blogs as $blog)
                <div class="col-lg-4 col-md-6">
                    <div class="card card-blog h-100 shadow-sm bg-white">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-primary border">{{ $blog->category?->name ?? 'Architecture' }}</span>
                                <small class="text-muted">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Recent' }}</small>
                            </div>
                            <h5 class="fw-bold mb-2">
                                <a href="{{ route('public.blog.show', $blog->slug) }}" class="text-dark text-decoration-none">
                                    {{ $blog->title }}
                                </a>
                            </h5>
                            <p class="text-muted small flex-grow-1" style="line-height: 1.6;">
                                {{ Str::limit($blog->summary, 120) }}
                            </p>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted">By {{ $blog->author?->name ?? 'Nexteck Team' }}</small>
                                <a href="{{ route('public.blog.show', $blog->slug) }}" class="text-primary fw-semibold small text-decoration-none">
                                    Read Article <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No articles currently published.</p>
                </div>
            @endforelse
        </div>

        @if($blogs->hasPages())
            <div class="mt-5 d-flex justify-content-center">
                {{ $blogs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </main>

    <footer class="py-4 text-center text-muted small border-top bg-white">
        <div class="container">
            © {{ date('Y') }} Nexteck Technologies. Custom Software, Web Applications & Lead Generation Systems.
        </div>
    </footer>
</body>
</html>
