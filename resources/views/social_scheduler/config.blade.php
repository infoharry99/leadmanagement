@extends('social_scheduler.layouts.social')

@section('title', 'API Configuration & Setup Guides - Settings')

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary">Settings</span>
            <span class="text-muted small">/</span>
            <span class="text-muted small">API Configuration &amp; Developer Guides</span>
        </div>
        <h2 class="fw-bold text-dark mb-1">Social API Credentials &amp; Setup Guides</h2>
        <p class="text-muted mb-0">Configure developer API credentials and follow step-by-step setup guides for each platform.</p>
    </div>
    <div class="col-md-5 text-md-end">
        <a href="{{ route('social.connections.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2">
            <i class="bi bi-link-45deg me-1"></i> Go to Connections
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left: API Credentials & Redirect URLs -->
    <div class="col-lg-6">
        <!-- Credentials Form -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-key-fill text-warning"></i> Developer API Credentials
            </h5>
            <p class="text-muted small mb-4">
                Enter your developer app credentials below. These values are stored securely in your application's <code>.env</code> file.
            </p>

            <form action="{{ route('social.config.update') }}" method="POST">
                @csrf

                <!-- Meta (Facebook & Instagram) -->
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-meta text-primary"></i> Meta (Facebook Pages &amp; Instagram)
                        </div>
                        @if (!empty($credentials['facebook_app_id']))
                            <span class="badge bg-success-subtle text-success small">Configured</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary small">Not Set</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Meta App ID (Facebook App ID)</label>
                        <input type="text" name="facebook_app_id" class="form-control form-control-sm rounded-2 font-monospace" placeholder="e.g. 1124088806732272" value="{{ old('facebook_app_id', $credentials['facebook_app_id']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Meta App Secret</label>
                        <input type="password" name="facebook_app_secret" class="form-control form-control-sm rounded-2 font-monospace" placeholder="••••••••••••••••••••" value="{{ old('facebook_app_secret', $credentials['facebook_app_secret']) }}">
                    </div>

                    <!-- Dedicated Instagram App ID (Optional) -->
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-semibold text-dark mb-0 d-flex align-items-center gap-1">
                                <i class="bi bi-instagram text-danger"></i> Dedicated Instagram App ID <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                        </div>
                        <input type="text" name="instagram_client_id" class="form-control form-control-sm rounded-2 font-monospace mb-2" placeholder="e.g. 2799091900487330" value="{{ old('instagram_client_id', $credentials['instagram_client_id']) }}">
                        <label class="form-label small fw-semibold text-dark mb-1">Dedicated Instagram App Secret <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="password" name="instagram_client_secret" class="form-control form-control-sm rounded-2 font-monospace" placeholder="••••••••••••••••••••" value="{{ old('instagram_client_secret', $credentials['instagram_client_secret']) }}">
                        <small class="text-muted d-block mt-1">If your Instagram use case in Meta has its own App ID/Secret, enter them above. Otherwise, leave blank to use the Meta App ID.</small>
                    </div>
                </div>

                <!-- LinkedIn -->
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-linkedin" style="color: #0A66C2;"></i> LinkedIn API
                        </div>
                        @if (!empty($credentials['linkedin_client_id']))
                            <span class="badge bg-success-subtle text-success small">Configured</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary small">Not Set</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted mb-1">LinkedIn Client ID</label>
                        <input type="text" name="linkedin_client_id" class="form-control form-control-sm rounded-2 font-monospace" placeholder="e.g. 78yq8z1xxxxxx" value="{{ old('linkedin_client_id', $credentials['linkedin_client_id']) }}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold text-muted mb-1">LinkedIn Client Secret</label>
                        <input type="password" name="linkedin_client_secret" class="form-control form-control-sm rounded-2 font-monospace" placeholder="••••••••••••••••••••" value="{{ old('linkedin_client_secret', $credentials['linkedin_client_secret']) }}">
                    </div>
                </div>

                <!-- TikTok -->
                <div class="border rounded-3 p-3 mb-4 bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-tiktok text-dark"></i> TikTok Content Posting API
                        </div>
                        @if (!empty($credentials['tiktok_client_key']))
                            <span class="badge bg-success-subtle text-success small">Configured</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary small">Not Set</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted mb-1">TikTok Client Key</label>
                        <input type="text" name="tiktok_client_key" class="form-control form-control-sm rounded-2 font-monospace" placeholder="e.g. awxxxxxxxxxx" value="{{ old('tiktok_client_key', $credentials['tiktok_client_key']) }}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold text-muted mb-1">TikTok Client Secret</label>
                        <input type="password" name="tiktok_client_secret" class="form-control form-control-sm rounded-2 font-monospace" placeholder="••••••••••••••••••••" value="{{ old('tiktok_client_secret', $credentials['tiktok_client_secret']) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-brand w-100 py-2 rounded-3 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Credentials to .env
                </button>
            </form>
        </div>

        <!-- Authorized Redirect URLs -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary"></i> Authorized OAuth Redirect URLs
            </h5>
            <p class="text-muted small mb-3">
                Copy and paste these exact callback URLs into each platform's developer portal under OAuth 2.0 Redirect URIs.
            </p>

            <div class="d-flex flex-column gap-3">
                @foreach ($redirectUris as $platform => $uri)
                    <div>
                        <label class="form-label small fw-semibold text-dark mb-1 text-capitalize">{{ $platform }} Redirect URI</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace bg-light" value="{{ $uri }}" id="uri_{{ $platform }}" readonly>
                            <button type="button" class="btn btn-outline-secondary copy-btn" data-target="uri_{{ $platform }}">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Meta App Basic Settings & Legal URLs -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
            <h5 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-check text-success"></i> Meta App Settings &amp; Legal URLs
            </h5>
            <p class="text-muted small mb-3">
                Paste these URLs into Meta for Developers under <strong>App settings &rarr; Basic</strong> to resolve submission requirements:
            </p>

            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-dark mb-1">Privacy Policy URL</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ route('legal.privacy') }}" id="legal_privacy" readonly>
                        <button type="button" class="btn btn-outline-secondary copy-btn" data-target="legal_privacy">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold text-dark mb-1">Terms of Service URL</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ route('legal.terms') }}" id="legal_terms" readonly>
                        <button type="button" class="btn btn-outline-secondary copy-btn" data-target="legal_terms">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold text-dark mb-1">User Data Deletion Instructions URL</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ route('legal.data_deletion') }}" id="legal_deletion" readonly>
                        <button type="button" class="btn btn-outline-secondary copy-btn" data-target="legal_deletion">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Step-by-Step Developer Setup Guides -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-journal-code text-info"></i> Official Developer Setup Guides
            </h5>
            <p class="text-muted small mb-4">
                Detailed instructions for creating developer apps, requesting required permissions, and passing API approvals.
            </p>

            <div class="accordion accordion-flush" id="guidesAccordion">
                <!-- Meta Guide -->
                <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingMeta">
                        <button class="accordion-button fw-bold text-dark bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMeta" aria-expanded="true" aria-controls="collapseMeta">
                            <i class="bi bi-meta text-primary me-2 fs-5"></i> Meta (Facebook Pages & Instagram)
                        </button>
                    </h2>
                    <div id="collapseMeta" class="accordion-collapse collapse show" aria-labelledby="headingMeta" data-bs-parent="#guidesAccordion">
                        <div class="accordion-body small">
                            <ol class="ps-3 mb-3">
                                <li class="mb-2">Go to <a href="https://developers.facebook.com/apps/" target="_blank" class="fw-semibold">Meta for Developers <i class="bi bi-box-arrow-up-right small"></i></a> and click <strong>Create App</strong>.</li>
                                <li class="mb-2">Select <strong>Business</strong> as the App Type.</li>
                                <li class="mb-2">Add the following products/use cases to your app:
                                    <ul class="mt-1">
                                        <li><strong>Facebook Login for Business</strong></li>
                                        <li><strong>Pages API</strong></li>
                                        <li><strong>Instagram Graph API</strong></li>
                                    </ul>
                                </li>
                                <li class="mb-2">Under <em>Facebook Login → Settings</em>, paste the <strong>Facebook Redirect URI</strong> and <strong>Instagram Redirect URI</strong> from the left panel.</li>
                                <li class="mb-2">Link your <strong>Instagram Business/Creator Account</strong> to your <strong>Facebook Page</strong> in Meta Business Suite.</li>
                                <li class="mb-2">Copy your <strong>App ID</strong> and <strong>App Secret</strong> from <em>App settings → Basic</em> into the credentials form on the left.</li>
                            </ol>
                            <div class="bg-light p-2 rounded-2 border">
                                <strong class="d-block text-dark mb-1">Required Permissions:</strong>
                                <code>pages_show_list</code>, <code>pages_read_engagement</code>, <code>pages_manage_posts</code>, <code>instagram_basic</code>, <code>instagram_content_publish</code>.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LinkedIn Guide -->
                <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingLinkedIn">
                        <button class="accordion-button collapsed fw-bold text-dark bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLinkedIn" aria-expanded="false" aria-controls="collapseLinkedIn">
                            <i class="bi bi-linkedin me-2 fs-5" style="color: #0A66C2;"></i> LinkedIn Developer Setup
                        </button>
                    </h2>
                    <div id="collapseLinkedIn" class="accordion-collapse collapse" aria-labelledby="headingLinkedIn" data-bs-parent="#guidesAccordion">
                        <div class="accordion-body small">
                            <ol class="ps-3 mb-3">
                                <li class="mb-2">Go to the <a href="https://www.linkedin.com/developers/apps" target="_blank" class="fw-semibold">LinkedIn Developer Portal <i class="bi bi-box-arrow-up-right small"></i></a> and click <strong>Create App</strong>.</li>
                                <li class="mb-2">Enter your App name and associate your company's LinkedIn Page.</li>
                                <li class="mb-2">Navigate to the <strong>Products</strong> tab and request access to:
                                    <ul class="mt-1">
                                        <li><strong>Share on LinkedIn</strong> (Grants <code>w_member_social</code> permission instantly).</li>
                                        <li><strong>Sign In with LinkedIn using OpenID Connect</strong> (Grants <code>openid</code> and <code>profile</code>).</li>
                                    </ul>
                                </li>
                                <li class="mb-2">Under the <strong>Auth</strong> tab, add the <strong>LinkedIn Redirect URI</strong> into <em>Authorized redirect URLs for your app</em>.</li>
                                <li class="mb-2">Copy the <strong>Client ID</strong> and <strong>Primary Client Secret</strong> into the credentials form on the left.</li>
                            </ol>
                            <div class="bg-light p-2 rounded-2 border">
                                <strong class="d-block text-dark mb-1">Required Scopes:</strong>
                                <code>openid</code>, <code>profile</code>, <code>w_member_social</code>.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TikTok Guide -->
                <div class="accordion-item border rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingTikTok">
                        <button class="accordion-button collapsed fw-bold text-dark bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTikTok" aria-expanded="false" aria-controls="collapseTikTok">
                            <i class="bi bi-tiktok text-dark me-2 fs-5"></i> TikTok Content Posting API
                        </button>
                    </h2>
                    <div id="collapseTikTok" class="accordion-collapse collapse" aria-labelledby="headingTikTok" data-bs-parent="#guidesAccordion">
                        <div class="accordion-body small">
                            <ol class="ps-3 mb-3">
                                <li class="mb-2">Register at the <a href="https://developers.tiktok.com/" target="_blank" class="fw-semibold">TikTok for Developers Portal <i class="bi bi-box-arrow-up-right small"></i></a> and create a Web App.</li>
                                <li class="mb-2">Apply for the <strong>Content Posting API</strong> capability.</li>
                                <li class="mb-2">Under App Details, add the <strong>TikTok Redirect URI</strong> in the <em>Redirect domains</em> section.</li>
                                <li class="mb-2">Copy your <strong>Client Key</strong> and <strong>Client Secret</strong> into the credentials form.</li>
                            </ol>
                            <div class="alert alert-warning py-2 px-3 small mb-2">
                                <i class="bi bi-camera-video me-1"></i> <strong>Publishing Rule:</strong> TikTok API strictly requires video files (.mp4 or .mov) with duration between 3 seconds and 10 minutes.
                            </div>
                            <div class="bg-light p-2 rounded-2 border">
                                <strong class="d-block text-dark mb-1">Required Scopes:</strong>
                                <code>video.publish</code>, <code>video.upload</code>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                navigator.clipboard.writeText(input.value).then(() => {
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check2"></i> Copied!';
                    this.classList.replace('btn-outline-secondary', 'btn-success');
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.replace('btn-success', 'btn-outline-secondary');
                    }, 2000);
                });
            }
        });
    });
});
</script>
@endpush
