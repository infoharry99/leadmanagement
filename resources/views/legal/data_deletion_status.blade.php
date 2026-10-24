<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Deletion Status | Nexteck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; color: #1E293B; }
        .legal-card { background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 2.5rem; text-align: center; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 600px;">
        <div class="legal-card shadow-sm">
            <div class="rounded-circle bg-success-subtle text-success d-inline-flex p-3 mb-3">
                <i class="bi bi-check-circle-fill fs-2"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Data Deletion Request Processed</h3>
            <p class="text-muted small mb-4">
                Your data deletion request has been registered and verified with our data management system.
            </p>

            <div class="bg-light p-3 rounded-3 border mb-4 text-start small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Confirmation Code:</span>
                    <code class="text-dark fw-bold">{{ $code }}</code>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Status:</span>
                    <span class="badge bg-success-subtle text-success">Completed</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Timestamp:</span>
                    <span class="text-dark">{{ now()->toRfc850String() }}</span>
                </div>
            </div>

            <p class="text-muted small mb-0">
                All associated tokens and active session permissions have been revoked. If you have any questions, reach out to <a href="mailto:info@nexteck.in">info@nexteck.in</a>.
            </p>
        </div>
    </div>
</body>
</html>
