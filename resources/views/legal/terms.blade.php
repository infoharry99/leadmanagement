<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | Nexteck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; color: #1E293B; }
        .legal-card { background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 2.5rem; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 860px;">
        <div class="text-center mb-4">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 mb-2" style="width: 44px; height: 44px;">
                <i class="bi bi-file-earmark-text fs-4"></i>
            </span>
            <h2 class="fw-bold text-dark">Terms of Service</h2>
            <p class="text-muted small">Last updated: October 24, 2026 • Nexteck Social Media Scheduler</p>
        </div>

        <div class="legal-card shadow-sm">
            <h5 class="fw-bold text-dark mb-3">1. Agreement to Terms</h5>
            <p class="text-muted leading-relaxed">
                By accessing or using Nexteck Social Media Post Scheduler, you agree to be bound by these Terms of Service and all applicable platform policies, including the Meta Platform Terms, Instagram Community Guidelines, LinkedIn API Terms of Use, and TikTok Developer Terms.
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">2. Authorized Use & Platform Compliance</h5>
            <p class="text-muted">You agree that you will only connect social media accounts that you own or have explicit authorization to manage. You agree NOT to:</p>
            <ul class="text-muted">
                <li>Post or distribute spam, unauthorized promotional campaigns, or malicious code.</li>
                <li>Violate any intellectual property rights, trademarks, or copyrights in uploaded media.</li>
                <li>Attempt to bypass platform API rate limits or automated publishing restrictions.</li>
            </ul>

            <h5 class="fw-bold text-dark mb-3 mt-4">3. Third-Party Platform Integrations</h5>
            <p class="text-muted">
                Our application integrates directly with third-party social networks via official developer APIs. We are not responsible for account suspensions, API policy changes, or content removal initiated by Meta, LinkedIn, or TikTok.
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">4. Account Termination & Revocation</h5>
            <p class="text-muted">
                You may disconnect your social accounts or terminate your account at any time. Upon disconnection, stored tokens are permanently revoked and erased.
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">5. Contact Information</h5>
            <p class="text-muted mb-0">
                For questions regarding these Terms of Service, please contact:
                <br>
                <strong>Email:</strong> <a href="mailto:info@nexteck.in">info@nexteck.in</a>
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
