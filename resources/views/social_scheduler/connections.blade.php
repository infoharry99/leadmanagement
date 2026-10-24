@extends('social_scheduler.layouts.social')

@section('title', 'Social Media Connections - Settings')

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary">Settings</span>
            <span class="text-muted small">/</span>
            <span class="text-muted small">Social Media Connections</span>
        </div>
        <h2 class="fw-bold text-dark mb-1">Connected Social Media Accounts</h2>
        <p class="text-muted mb-0">Authorize official platform APIs to enable direct publishing and automated scheduling.</p>
    </div>
    <div class="col-md-5 text-md-end">
        <a href="{{ route('social.config.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2">
            <i class="bi bi-key-fill text-warning me-1"></i> API Credentials & Setup Guides
        </a>
    </div>
</div>

<!-- Platforms Grid -->
<div class="row g-4 mb-4">
    @foreach ($platforms as $key => $p)
        @php
            $account = $p['account'];
            $isConnected = !empty($account) && $account->is_active;
            $isExpired = $isConnected && $account->isTokenExpired();
        @endphp
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                <div class="card-body p-4 d-flex flex-column">
                    <!-- Platform Header -->
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: {{ $p['color'] }}15; color: {{ $p['color'] }};">
                                <i class="bi {{ $p['icon'] }} fs-4"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $p['name'] }}</h5>
                                <a href="{{ $p['api_docs'] }}" target="_blank" class="small text-muted text-decoration-none">
                                    Official API Docs <i class="bi bi-box-arrow-up-right small"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if ($isConnected)
                            @if ($isExpired)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill small">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Token Expired
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                                    <i class="bi bi-check-circle-fill me-1"></i> Connected
                                </span>
                            @endif
                        @else
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill small">
                                <i class="bi bi-circle me-1"></i> Not Connected
                            </span>
                        @endif
                    </div>

                    <p class="text-muted small mb-3">{{ $p['description'] }}</p>

                    <!-- Account Info if connected -->
                    @if ($isConnected)
                        <div class="bg-light rounded-3 p-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">Account / Handle:</span>
                                <span class="small fw-bold text-dark">{{ $account->account_name }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">Account / Page ID:</span>
                                <code class="small text-dark">{{ $account->account_id }}</code>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Token Status:</span>
                                <span class="small {{ $isExpired ? 'text-danger fw-bold' : 'text-success' }}">
                                    @if ($account->token_expires_at)
                                        Expires {{ $account->token_expires_at->diffForHumans() }}
                                    @else
                                        Long-lived Active Token
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Permissions / Scopes -->
                    <div class="mb-4 mt-auto">
                        <small class="text-muted fw-semibold d-block mb-1">Authorized Permissions:</small>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($p['scopes'] as $scope)
                                <span class="badge bg-light text-dark border small">{{ $scope }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        @if ($isConnected)
                            <!-- Reconnect OAuth -->
                            <a href="{{ route('social.oauth.redirect', ['platform' => $key]) }}" class="btn btn-outline-primary btn-sm rounded-3 flex-grow-1 py-2">
                                <i class="bi bi-arrow-repeat me-1"></i> Reconnect
                            </a>

                            <!-- Disconnect -->
                            <form action="{{ route('social.connections.disconnect', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Disconnect this account? Scheduled posts for {{ $p['name'] }} will fail until reconnected.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 py-2 px-3">
                                    <i class="bi bi-x-circle me-1"></i> Disconnect
                                </button>
                            </form>
                        @else
                            <!-- Connect via OAuth -->
                            <a href="{{ route('social.oauth.redirect', ['platform' => $key]) }}" class="btn btn-primary btn-sm rounded-3 flex-grow-1 py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Connect with OAuth
                            </a>

                            <!-- Manual Token Modal Trigger -->
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 py-2 px-3" data-bs-toggle="modal" data-bs-target="#manualModal_{{ $key }}">
                                <i class="bi bi-code-slash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Token Connect Modal -->
        @if (!$isConnected)
            <div class="modal fade" id="manualModal_{{ $key }}" tabindex="-1" aria-labelledby="manualModalLabel_{{ $key }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <form action="{{ route('social.connections.manual') }}" method="POST">
                            @csrf
                            <input type="hidden" name="platform" value="{{ $key }}">
                            
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-dark" id="manualModalLabel_{{ $key }}">
                                    Manual Connect: {{ $p['name'] }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            
                            <div class="modal-body py-3">
                                <p class="text-muted small">
                                    Useful for developer tokens, testing with Graph API Explorer, or when OAuth popup is restricted.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Account / Page Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control rounded-3" placeholder="e.g. Nexteck Official" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Platform Account / Page ID <span class="text-danger">*</span></label>
                                    <input type="text" name="account_id" class="form-control rounded-3" placeholder="e.g. 1048576293847" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">OAuth Access Token <span class="text-danger">*</span></label>
                                    <textarea name="access_token" rows="3" class="form-control rounded-3 font-monospace small" placeholder="Paste access token here..." required></textarea>
                                </div>
                            </div>

                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-3 fw-semibold">Save & Connect</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

<!-- Security & Storage Notice -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
    <div class="d-flex align-items-start gap-3">
        <div class="rounded-circle bg-white shadow-sm p-3 text-primary">
            <i class="bi bi-shield-lock-fill fs-4"></i>
        </div>
        <div>
            <h6 class="fw-bold text-dark mb-1">Encrypted OAuth Token Storage</h6>
            <p class="text-muted small mb-0">
                All social access tokens and refresh tokens are encrypted at rest using AES-256-CBC with your application key. 
                Tokens are only decrypted in memory when executing publishing jobs. When tokens expire, click "Reconnect" to refresh the token via official OAuth.
            </p>
        </div>
    </div>
</div>
@endsection
