<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preferences | Nexteck</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .unsub-card { max-width: 480px; width: 100%; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; padding: 2.25rem; }
    </style>
</head>
<body>
    <div class="unsub-card">
        <div class="text-center mb-4">
            <div class="rounded-circle bg-light text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-weight: 800; font-size: 1.5rem;">N</div>
            <h4 class="fw-bold mb-1">Update Email Preferences</h4>
            <p class="text-muted small">Manage communications from Nexteck Technologies</p>
        </div>

        @if($isSuppressed)
            <div class="alert alert-info small border-0 shadow-sm text-center">
                This email address is already on our permanent suppression list and will not receive marketing outreach.
            </div>
        @else
            <form action="{{ route('unsubscribe.process', $token) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address to Remove</label>
                    <input type="email" name="email" value="{{ $email }}" class="form-control" required readonly style="background-color: #F1F5F9;">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Reason for Unsubscribing (Optional)</label>
                    <select name="reason" class="form-select form-select-sm">
                        <option value="No longer relevant">The content is no longer relevant to my role</option>
                        <option value="Too many emails">Receiving too many communications</option>
                        <option value="Never signed up">I did not sign up or consent to this email</option>
                        <option value="Competitor / In-house">We develop our software in-house</option>
                        <option value="Other">Other reason</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold shadow-sm">
                    Unsubscribe from Marketing Emails
                </button>
            </form>
        @endif

        <div class="mt-4 pt-3 border-top text-center text-muted small" style="font-size: 0.75rem;">
            Nexteck respects your privacy. Requests are processed immediately across all marketing databases.
        </div>
    </div>
</body>
</html>
