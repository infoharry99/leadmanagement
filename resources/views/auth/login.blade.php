<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexteck Lead Engine - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            padding: 2.25rem;
        }
        .brand-badge {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4F46E5, #06B6D4);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.35);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-badge mx-auto mb-3">N</div>
            <h4 class="fw-bold mb-1">Nexteck Lead Engine</h4>
            <p class="text-muted small">Marketing Automation & CRM Platform</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small border-0 shadow-sm mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" value="{{ old('email', 'admin@nexteck.com') }}" required class="form-control border-start-0" placeholder="name@company.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" value="password" required class="form-control border-start-0" placeholder="••••••••">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="rememberMe" checked>
                    <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3" style="background-color: #4F46E5; border-color: #4F46E5;">
                Sign In to Dashboard
            </button>
        </form>

        <div class="position-relative my-3 text-center">
            <hr class="text-muted">
            <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 small text-muted">Quick Demo Login</span>
        </div>

        <div class="d-grid gap-2">
            <form action="{{ route('quick-login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="admin">
                <button type="submit" class="btn btn-light btn-sm w-100 border text-start d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-fill-gear text-primary me-2"></i><strong>Admin</strong> (Alexander Nexteck)</span>
                    <span class="badge bg-primary">admin@nexteck.com</span>
                </button>
            </form>

            <form action="{{ route('quick-login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="sales_manager">
                <button type="submit" class="btn btn-light btn-sm w-100 border text-start d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge text-success me-2"></i><strong>Sales Manager</strong> (Sarah Jenkins)</span>
                    <span class="badge bg-success">manager@nexteck.com</span>
                </button>
            </form>

            <form action="{{ route('quick-login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="sales_rep">
                <button type="submit" class="btn btn-light btn-sm w-100 border text-start d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person text-info me-2"></i><strong>Sales Rep</strong> (David Miller)</span>
                    <span class="badge bg-secondary">sales@nexteck.com</span>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
