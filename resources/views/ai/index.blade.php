@extends('layouts.app')

@section('title', 'AI Content Studio')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">AI Content Generation Studio</h4>
        <p class="text-muted small mb-0">Generate high-converting platform-specific copy, TikTok scripts, LinkedIn articles, and Nexteck service CTAs.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Generator Configuration Column -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-magic text-primary me-2"></i>Generate Multi-Variation Copy
            </div>
            <div class="card-body p-4">
                <form action="{{ route('ai.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Platform <span class="text-danger">*</span></label>
                        <select name="platform" class="form-select" required>
                            <option value="linkedin">LinkedIn (Professional Thought Leadership)</option>
                            <option value="instagram">Instagram (Multi-Slide Carousel & Captions)</option>
                            <option value="tiktok">TikTok (High-Retention Video Hooks & Scripts)</option>
                            <option value="facebook">Facebook (Educational & Case Highlights)</option>
                            <option value="blog">Engineering Blog Outline & Concept</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Core Topic or Angle <span class="text-danger">*</span></label>
                        <input type="text" name="topic" class="form-control" value="{{ old('topic', 'Why Modern Enterprise Teams Choose Laravel for Scalable Microservices') }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nexteck Service Focus <span class="text-danger">*</span></label>
                            <select name="service_focus" class="form-select" required>
                                <option value="laravel_development">Laravel Enterprise Systems</option>
                                <option value="software_development">Custom Software Architecture</option>
                                <option value="web_applications">High-Performance Web Apps</option>
                                <option value="mobile_apps">Cross-Platform Mobile Apps</option>
                                <option value="ecommerce">Headless E-Commerce</option>
                                <option value="automation">B2B Workflow Automation</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tone of Voice</label>
                            <select name="tone" class="form-select">
                                <option value="authoritative">Authoritative & Technical</option>
                                <option value="professional">Professional & Conversational</option>
                                <option value="direct">Direct & Problem-Solving</option>
                                <option value="visionary">Visionary & Growth-Oriented</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Audience</label>
                        <input type="text" name="audience" class="form-control" value="{{ old('audience', 'CTOs, VP Engineering, and Product Leaders') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Desired Call to Action (CTA)</label>
                        <input type="text" name="call_to_action" class="form-control" value="{{ old('call_to_action', 'Book a 15-minute architectural consultation with Nexteck') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Keywords to Emphasize</label>
                        <input type="text" name="keywords" class="form-control" value="{{ old('keywords', 'scalability, high throughput, sub-100ms latency, maintainability') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Prohibited Claims / Guardrails</label>
                        <input type="text" name="prohibited_claims" class="form-control" value="{{ old('prohibited_claims', 'No unverified pricing, no unrealistic 100% guarantees') }}">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                        <i class="bi bi-sparkles me-1"></i> Generate Variations
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Generations History Column -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-clock-history text-secondary me-2"></i>Recent AI Generations
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentGenerations as $gen)
                        <a href="{{ route('ai.show', $gen) }}" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                    {{ $gen->platform }}
                                </span>
                                <small class="text-muted">{{ $gen->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="fw-bold text-dark text-truncate">{{ $gen->topic }}</div>
                            <small class="text-muted">{{ count($gen->output_variations) }} variations generated • {{ $gen->user?->name ?? 'User' }}</small>
                        </a>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-magic fs-2 mb-2 d-block"></i>
                            No generations saved yet. Use the studio form to generate copy.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
