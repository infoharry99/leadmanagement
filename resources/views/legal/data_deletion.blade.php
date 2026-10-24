<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data Deletion Instructions | Nexteck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; color: #1E293B; }
        .legal-card { background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 2.5rem; }
        .step-num { width: 32px; height: 32px; background: #EEF2FF; color: #4F46E5; font-weight: bold; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 860px;">
        <div class="text-center mb-4">
            <span class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-3 p-2 mb-2" style="width: 44px; height: 44px;">
                <i class="bi bi-trash3-fill fs-4"></i>
            </span>
            <h2 class="fw-bold text-dark">User Data Deletion Instructions</h2>
            <p class="text-muted small">Compliant with Meta Platform Terms Section 4.a (Facebook & Instagram)</p>
        </div>

        <div class="legal-card shadow-sm">
            <p class="text-muted leading-relaxed">
                Nexteck provides tools to schedule and publish social media posts. In accordance with Meta Platform Terms, you have the right to request the deletion of your personal data and revoke all application permissions at any time.
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">Method 1: Disconnect via Nexteck Application (Instant)</h5>
            <p class="text-muted small">You can delete your stored tokens and credentials directly within our application:</p>
            <div class="d-flex flex-column gap-2 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">1</span>
                    <div>Log in to your account at <a href="{{ route('social.connections.index') }}" class="fw-semibold">leadcrm.nexteck.uk</a>.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">2</span>
                    <div>Navigate to <strong>Settings → Social Media Connections</strong> in the top navigation bar.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">3</span>
                    <div>Find your connected <strong>Facebook Page</strong> or <strong>Instagram Professional</strong> account and click the <strong class="text-danger">Disconnect</strong> button.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">4</span>
                    <div>All stored OAuth access tokens, page IDs, and connection metadata are immediately and permanently wiped from our database.</div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="fw-bold text-dark mb-3">Method 2: Remove App via Facebook / Meta Account Settings</h5>
            <p class="text-muted small">If you wish to revoke permissions directly from Meta without logging into our app, follow these steps:</p>
            <div class="d-flex flex-column gap-2 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">1</span>
                    <div>Go to your Facebook Profile's <strong>Settings &amp; privacy</strong> &gt; <strong>Settings</strong>.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">2</span>
                    <div>Scroll down to the <strong>Permissions</strong> section and click <strong>Apps and Websites</strong>.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">3</span>
                    <div>Locate <strong>Nexteck</strong> (or your app name) in the list of active apps.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">4</span>
                    <div>Click the <strong>Remove</strong> button. Optionally check the box to delete any posts published by the app, then click <strong>Remove</strong> to confirm.</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="step-num flex-shrink-0">5</span>
                    <div>Facebook will notify our server via our automated data deletion callback, and all associated session tokens will be permanently deleted.</div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="fw-bold text-dark mb-3">Method 3: Submit a Manual Erasure Request</h5>
            <p class="text-muted leading-relaxed">
                You can also email our support team directly to request a complete purge of all user records, uploaded media files, and logs associated with your email:
                <br><br>
                <strong>Email:</strong> <a href="mailto:info@nexteck.in?subject=User%20Data%20Deletion%20Request">info@nexteck.in</a>
                <br>
                <strong>Subject:</strong> User Data Deletion Request
                <br><br>
                Please include your registered email address and social media handle. Our team will process your deletion request and confirm completion within <strong>48 business hours</strong>.
            </p>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left"></i> Return to Application
            </a>
        </div>
    </div>
</body>
</html>
