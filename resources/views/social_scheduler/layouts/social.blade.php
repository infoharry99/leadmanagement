<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Social Media Post Scheduler')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --brand-primary: #4F46E5;
            --brand-primary-hover: #4338CA;
            --brand-dark: #0F172A;
            --brand-bg: #F8FAFC;
            --brand-card-bg: #FFFFFF;
            --brand-border: #E2E8F0;
            --brand-text: #1E293B;
            --brand-muted: #64748B;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--brand-bg);
            color: var(--brand-text);
            min-height: 100vh;
        }

        .navbar-brand-badge {
            background: linear-gradient(135deg, #4F46E5, #06B6D4);
            color: white;
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        .nav-link.active-nav {
            color: var(--brand-primary) !important;
            font-weight: 600;
            border-bottom: 2px solid var(--brand-primary);
        }

        .card-stat {
            background: #FFFFFF;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            transition: all 0.2s ease;
        }
        .card-stat:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .badge-platform {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-facebook { background-color: #E7F3FF; color: #1877F2; }
        .badge-instagram { background-color: #FCE8EF; color: #E4405F; }
        .badge-linkedin { background-color: #E8F4F9; color: #0A66C2; }
        .badge-tiktok { background-color: #F1F1F2; color: #000000; }

        .btn-brand {
            background-color: var(--brand-primary);
            color: white;
            font-weight: 600;
            border: none;
        }
        .btn-brand:hover {
            background-color: var(--brand-primary-hover);
            color: white;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2 shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="{{ route('social.posts.dashboard') }}">
                <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2" style="width: 36px; height: 36px;">
                    <i class="bi bi-calendar2-range-fill"></i>
                </span>
                <span>Post Scheduler</span>
                <span class="navbar-brand-badge">Official APIs</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#socialNavbar" aria-controls="socialNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="socialNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ request()->routeIs('social.posts.dashboard') && !request()->has('status') ? 'active-nav' : '' }}" href="{{ route('social.posts.dashboard') }}">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ request()->routeIs('social.posts.create') ? 'active-nav' : '' }}" href="{{ route('social.posts.create') }}">
                            <i class="bi bi-plus-circle-fill me-1"></i> Create Post
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ request()->query('status') === 'scheduled' ? 'active-nav' : '' }}" href="{{ route('social.posts.index', ['status' => 'scheduled']) }}">
                            <i class="bi bi-clock-history me-1"></i> Scheduled
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ request()->query('status') === 'published' ? 'active-nav' : '' }}" href="{{ route('social.posts.index', ['status' => 'published']) }}">
                            <i class="bi bi-check-circle-fill me-1"></i> Published
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ request()->query('status') === 'failed' ? 'active-nav' : '' }}" href="{{ route('social.posts.index', ['status' => 'failed']) }}">
                            <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> Failed
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center gap-2">
                    <!-- Settings Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle py-2 d-flex align-items-center gap-1 {{ request()->routeIs('social.connections.*') || request()->routeIs('social.config.*') ? 'active-nav' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-fill me-1"></i> Social Settings
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('social.connections.index') }}">
                                    <i class="bi bi-link-45deg text-primary fs-5"></i>
                                    <div>
                                        <div class="fw-semibold">Social Media Connections</div>
                                        <small class="text-muted">Connect Instagram, FB, LinkedIn, TikTok</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('social.config.index') }}">
                                    <i class="bi bi-key-fill text-warning fs-5"></i>
                                    <div>
                                        <div class="fw-semibold">API Configuration</div>
                                        <small class="text-muted">App IDs, secrets, redirect URIs & docs</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Quick Post Button -->
                    <li class="nav-item ms-lg-2">
                        <a href="{{ route('social.posts.create') }}" class="btn btn-brand btn-sm px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-plus-lg"></i>
                            <span>Create Post</span>
                        </a>
                    </li>

                    <!-- User Account / Logout -->
                    @auth
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle p-0 d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                                <div class="text-muted small">{{ auth()->user()->email }}</div>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content Container -->
    <main class="py-4">
        <div class="container">
            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2 fw-bold mb-2">
                        <i class="bi bi-shield-exclamation fs-5"></i>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
