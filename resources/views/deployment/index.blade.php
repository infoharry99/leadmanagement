<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexteck Shared Hosting Deployment Runner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .deploy-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            max-width: 850px;
            width: 100%;
        }
        .terminal-box {
            background-color: #020617;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            color: #38bdf8;
            max-height: 280px;
            overflow-y: auto;
            white-space: pre-wrap;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="deploy-card p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between border-bottom border-secondary pb-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-white"><i class="bi bi-database-fill-gear text-primary me-2"></i>Database Deployment Runner</h4>
                <p class="text-secondary small mb-0">Shared Hosting Helper for Migrations, Seeders & Cache</p>
            </div>
            <span class="badge {{ $dbConnected ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                <i class="bi {{ $dbConnected ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                DB: {{ $dbConnected ? 'Connected (' . $tablesCount . ' tables)' : 'Connection Failed' }}
            </span>
        </div>

        @if($status === 'success' && !empty($message))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ $message }}</div>
            </div>
        @elseif($status === 'error')
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ $message }}</div>
            </div>
        @endif

        <div class="mb-4">
            <h6 class="text-light fw-bold mb-3"><i class="bi bi-play-btn text-warning me-2"></i>Select Deployment Action</h6>
            <div class="row g-2">
                <div class="col-md-6">
                    <a href="{{ url('/deploy?action=migrate-seed&token=' . $token) }}" class="btn btn-primary w-100 py-2.5 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-database-fill-add me-2"></i><strong>Run Migrate & Seed</strong> (Recommended)</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ url('/deploy?action=migrate&token=' . $token) }}" class="btn btn-outline-info w-100 py-2.5 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-arrow-repeat me-2"></i>Run Migrations Only</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ url('/deploy?action=seed&token=' . $token) }}" class="btn btn-outline-warning w-100 py-2.5 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-people-fill me-2"></i>Run Seeders (Demo Leads & Users)</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ url('/deploy?action=storage-link&token=' . $token) }}" class="btn btn-outline-secondary text-light w-100 py-2.5 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-link-45deg me-2"></i>Create Storage Link</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ url('/deploy?action=process-queue&token=' . $token) }}" class="btn btn-success w-100 py-2.5 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-send-check-fill me-2"></i><strong>Process Queue (Send Pending Emails)</strong></span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="col-md-12">
                    <a href="{{ url('/deploy?action=optimize-clear&token=' . $token) }}" class="btn btn-outline-light w-100 py-2 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-brush me-2"></i>Clear App & View Cache (optimize:clear)</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-semibold"><i class="bi bi-terminal me-1"></i>Artisan Command Output</span>
                <span class="text-secondary small">Database: <code>{{ $databaseName }}</code></span>
            </div>
            <div class="terminal-box">{{ $output ?: 'No output recorded.' }}</div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top border-secondary gap-3">
            <span class="text-secondary small">
                <i class="bi bi-shield-lock me-1"></i>Secured with <code>DEPLOY_TOKEN</code>. Remove or change after setup.
            </span>
            <div class="d-flex gap-2">
                <a href="{{ url('/deploy?action=status&token=' . $token) }}" class="btn btn-sm btn-dark border border-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </a>
                <a href="{{ route('login') }}" class="btn btn-sm btn-success px-3 fw-bold">
                    Go to Login Screen <i class="bi bi-box-arrow-in-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
