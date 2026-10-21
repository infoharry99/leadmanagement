<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nexteck Lead Engine') | Lead Generation & Marketing Automation</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --nx-sidebar-bg: #0F172A;
            --nx-sidebar-hover: #1E293B;
            --nx-sidebar-active: #2563EB;
            --nx-primary: #4F46E5;
            --nx-primary-hover: #4338CA;
            --nx-bg: #F8FAFC;
            --nx-card-bg: #FFFFFF;
            --nx-border: #E2E8F0;
            --nx-text-primary: #0F172A;
            --nx-text-muted: #64748B;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--nx-bg);
            color: var(--nx-text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .nx-sidebar {
            width: 260px;
            background-color: var(--nx-sidebar-bg);
            height: 100vh;
            max-height: 100vh;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.25s ease-in-out;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .nx-sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            flex-shrink: 0;
        }

        .nx-brand-logo {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #4F46E5, #06B6D4);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .nx-brand-name {
            color: #FFFFFF;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 1.2;
        }

        .nx-brand-subtitle {
            font-size: 0.7rem;
            color: #94A3B8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nx-sidebar-nav {
            padding: 1rem 0.75rem 3.5rem 0.75rem;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .nx-sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .nx-sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }

        .nx-sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .nx-sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        .nx-nav-header {
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #64748B;
            padding: 0.75rem 0.75rem 0.35rem 0.75rem;
            margin-top: 0.5rem;
        }

        .nx-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.75rem;
            color: #94A3B8;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .nx-nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            color: #94A3B8;
            transition: color 0.15s ease;
        }

        .nx-nav-link:hover {
            color: #FFFFFF;
            background-color: var(--nx-sidebar-hover);
        }

        .nx-nav-link:hover i {
            color: #FFFFFF;
        }

        .nx-nav-link.active {
            color: #FFFFFF;
            background-color: var(--nx-sidebar-active);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .nx-nav-link.active i {
            color: #FFFFFF;
        }

        .nx-nav-badge {
            margin-left: auto;
            font-size: 0.72rem;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        /* Main Content wrapper */
        .nx-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease-in-out;
        }

        /* Top Navbar */
        .nx-topbar {
            height: 64px;
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--nx-border);
            padding: 0 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .nx-content-body {
            padding: 1.75rem;
            flex-grow: 1;
        }

        /* SaaS Components */
        .card {
            border: 1px solid var(--nx-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            background-color: #FFFFFF;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--nx-border);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--nx-primary);
            border-color: var(--nx-primary);
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: var(--nx-primary-hover);
            border-color: var(--nx-primary-hover);
        }

        .badge-status-new { background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
        .badge-status-contacted { background-color: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
        .badge-status-interested { background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .badge-status-qualified { background-color: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; }
        .badge-status-converted { background-color: #FDF4FF; color: #86198F; border: 1px solid #F5D0FE; }
        .badge-status-not_interested { background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }
        .badge-status-lost { background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }

        @media (max-width: 992px) {
            .nx-sidebar {
                transform: translateX(-100%);
            }
            .nx-sidebar.show {
                transform: translateX(0);
            }
            .nx-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="nx-sidebar" id="nxSidebar">
        <a href="{{ route('dashboard') }}" class="nx-sidebar-brand">
            <div class="nx-brand-logo">N</div>
            <div>
                <div class="nx-brand-name">NEXTECK</div>
                <div class="nx-brand-subtitle">Lead Engine CRM</div>
            </div>
        </a>

        <div class="nx-sidebar-nav">
            <div class="nx-nav-header">Main Hub</div>
            <a href="{{ route('dashboard') }}" class="nx-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>

            <div class="nx-nav-header">Lead Generation CRM</div>
            <a href="{{ route('leads.index') }}" class="nx-nav-link {{ request()->routeIs('leads.*') && !request()->routeIs('leads.import') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Leads Management
            </a>
            <a href="{{ route('leads.import') }}" class="nx-nav-link {{ request()->routeIs('leads.import') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-arrow-up"></i> Import & Export
            </a>
            <a href="{{ route('handovers.index') }}" class="nx-nav-link {{ request()->routeIs('handovers.*') ? 'active' : '' }}">
                <i class="bi bi-person-check-fill"></i> Manager Handover
                @php
                    $pendingHandovers = \App\Models\ManagerHandover::where('status', 'pending')->count();
                @endphp
                @if($pendingHandovers > 0)
                    <span class="badge bg-danger nx-nav-badge">{{ $pendingHandovers }}</span>
                @endif
            </a>
            <a href="{{ route('tasks.index') }}" class="nx-nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <i class="bi bi-check2-circle"></i> Follow-Up Tasks
            </a>

            <div class="nx-nav-header">Marketing Automation</div>
            <a href="{{ route('campaigns.index') }}" class="nx-nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
                <i class="bi bi-send-fill"></i> Email Campaigns
            </a>
            <a href="{{ route('sequences.index') }}" class="nx-nav-link {{ request()->routeIs('sequences.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Follow-Up Sequences
            </a>
            <a href="{{ route('templates.index') }}" class="nx-nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper"></i> Email Templates
            </a>
            <a href="{{ route('inbox.index') }}" class="nx-nav-link {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
                <i class="bi bi-chat-left-dots-fill"></i> Unified Inbox
                @php
                    $unreadReplies = \App\Models\InboundReply::where('status', 'unassigned')->count();
                @endphp
                @if($unreadReplies > 0)
                    <span class="badge bg-primary nx-nav-badge">{{ $unreadReplies }}</span>
                @endif
            </a>

            <div class="nx-nav-header">Content & AI Studio</div>
            <a href="{{ route('calendar.index') }}" class="nx-nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Content Calendar
            </a>
            <a href="{{ route('ai.index') }}" class="nx-nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
                <i class="bi bi-magic"></i> AI Content Studio
            </a>
            <a href="{{ route('social.index') }}" class="nx-nav-link {{ request()->routeIs('social.*') ? 'active' : '' }}">
                <i class="bi bi-share-fill"></i> Social Publishing
            </a>

            <div class="nx-nav-header">SEO, AEO & CMS</div>
            <a href="{{ route('blogs.index') }}" class="nx-nav-link {{ request()->routeIs('blogs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-richtext"></i> Blog CMS & AEO
            </a>
            <a href="{{ route('reports.index') }}" class="nx-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Reports & Analytics
            </a>
            
            <div class="nx-nav-header">Public Endpoints</div>
            <a href="{{ route('public.blog.index') }}" target="_blank" class="nx-nav-link">
                <i class="bi bi-box-arrow-up-right"></i> View Website Blog
            </a>
            <a href="{{ route('public.sitemap') }}" target="_blank" class="nx-nav-link">
                <i class="bi bi-diagram-2"></i> XML Sitemap
            </a>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="nx-main">
        <!-- Top Navigation -->
        <header class="nx-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" onclick="document.getElementById('nxSidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-md-flex align-items-center text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Nexteck Enterprise Engine v2.4 | Active Queue Worker
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- User Profile & Quick Role Switcher -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm d-flex align-items-center gap-2 border px-3 py-1.5" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.75rem; font-weight: 700;">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-start d-none d-sm-block">
                            <div class="fw-semibold lh-1" style="font-size: 0.82rem;">{{ auth()->user()->name ?? 'User' }}</div>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Admin')) }}</small>
                        </div>
                        <i class="bi bi-chevron-down text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                        <li class="dropdown-header text-uppercase text-muted" style="font-size: 0.68rem;">Quick Role Switcher</li>
                        <li>
                            <form action="{{ route('quick-login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="admin">
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                    <i class="bi bi-person-fill-gear text-primary"></i> Login as Admin
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick-login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="sales_manager">
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                    <i class="bi bi-person-badge text-success"></i> Login as Sales Manager
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick-login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="sales_rep">
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                    <i class="bi bi-person text-info"></i> Login as Sales Rep
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <strong>Import notices:</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Page Body Content -->
        <main class="nx-content-body">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
