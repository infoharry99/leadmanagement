<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | Nexteck</title>
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
                <i class="bi bi-shield-check fs-4"></i>
            </span>
            <h2 class="fw-bold text-dark">Privacy Policy</h2>
            <p class="text-muted small">Last updated: October 24, 2026 • Nexteck Social Media Scheduler</p>
        </div>

        <div class="legal-card shadow-sm">
            <h5 class="fw-bold text-dark mb-3">1. Introduction</h5>
            <p class="text-muted leading-relaxed">
                Nexteck ("we", "our", or "us") provides social media scheduling, lead management, and marketing automation solutions. This Privacy Policy describes how we collect, store, protect, and handle data when you connect your social media accounts (including Facebook Pages, Instagram Professional, LinkedIn, and TikTok).
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">2. Information We Collect</h5>
            <p class="text-muted">When you connect your social media accounts via official OAuth APIs, we may access:</p>
            <ul class="text-muted">
                <li><strong>Profile Information:</strong> Public account names, handles, profile avatars, and platform account IDs.</li>
                <li><strong>Page & Account Identifiers:</strong> List of managed Facebook Pages and linked Instagram Professional accounts.</li>
                <li><strong>OAuth Access Tokens:</strong> Scoped, temporary access tokens required to publish posts on your behalf.</li>
                <li><strong>Content & Media:</strong> Post text, hashtags, photos, and videos you explicitly upload to schedule or publish.</li>
            </ul>

            <h5 class="fw-bold text-dark mb-3 mt-4">3. How We Use Your Information</h5>
            <p class="text-muted">We use your authorized permissions strictly to:</p>
            <ul class="text-muted">
                <li>Publish and schedule user-authored posts, photos, and videos to your chosen platforms.</li>
                <li>Verify your account connection status and token validity.</li>
                <li>Display publication statuses (Scheduled, Published, Failed) and platform delivery logs.</li>
            </ul>
            <div class="alert alert-info py-2 px-3 small rounded-3">
                <i class="bi bi-info-circle-fill me-1"></i> We do <strong>not</strong> sell, rent, or trade your personal data or social media tokens to third parties or advertising networks.
            </div>

            <h5 class="fw-bold text-dark mb-3 mt-4">4. Token Storage and Security</h5>
            <p class="text-muted">
                All OAuth access tokens and refresh tokens are encrypted at rest using AES-256-CBC encryption. Tokens are only decrypted in memory at the exact moment a scheduled publishing job is executed.
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">5. User Data Deletion Rights</h5>
            <p class="text-muted">
                You have the absolute right to revoke access and request deletion of your stored data at any time. You can disconnect your accounts directly via <strong>Settings → Social Media Connections</strong> or review our full <a href="{{ route('legal.data_deletion') }}" class="fw-semibold text-primary">Data Deletion Instructions</a>.
            </p>

            <h5 class="fw-bold text-dark mb-3 mt-4">6. Contact Information</h5>
            <p class="text-muted mb-0">
                If you have any questions regarding this Privacy Policy or your data, please contact our Data Protection Officer at:
                <br>
                <strong>Email:</strong> <a href="mailto:info@nexteck.in">info@nexteck.in</a>
                <br>
                <strong>Website:</strong> <a href="https://leadcrm.nexteck.uk" target="_blank">leadcrm.nexteck.uk</a>
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
