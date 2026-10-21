<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed Successfully | Nexteck</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .unsub-card { max-width: 520px; width: 100%; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; padding: 2.5rem; text-align: center; }
    </style>
</head>
<body>
    <div class="unsub-card">
        <div class="rounded-circle bg-success bg-opacity-10 text-success mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
            <i class="bi bi-check-circle-fill fs-2"></i>
        </div>
        <h4 class="fw-bold mb-2 text-dark">Preferences Updated</h4>
        <div class="badge bg-light text-muted border mb-3">{{ $email }}</div>

        <div class="p-3 bg-light rounded-3 text-secondary small text-start mb-4" style="line-height: 1.6;">
            {{ $serviceInfo }}
        </div>

        <div class="text-muted small" style="font-size: 0.75rem;">
            Your address has been added to our permanent suppression database.
        </div>
    </div>
</body>
</html>
