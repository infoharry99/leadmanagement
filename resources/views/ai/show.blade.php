@extends('layouts.app')

@section('title', 'AI Variations: ' . $generation->topic)

@section('content')
<div class="mb-4">
    <a href="{{ route('ai.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to AI Studio
    </a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $generation->topic }}</h4>
            <p class="text-muted small mb-0">Platform: <span class="badge bg-light text-dark border text-uppercase">{{ $generation->platform }}</span> • Service: <strong>{{ ucfirst(str_replace('_', ' ', $generation->service_focus)) }}</strong> • Tone: {{ $generation->tone }}</p>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
            Generated via {{ $generation->provider }}
        </span>
    </div>
</div>

<div class="row g-4">
    @foreach($generation->output_variations as $index => $variation)
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">Variation #{{ $index + 1 }}: {{ $variation['title'] ?? 'Generated Copy' }}</span>
                    <button class="btn btn-sm btn-light border" onclick="navigator.clipboard.writeText(document.getElementById('varText_{{ $index }}').innerText); alert('Copied to clipboard!');">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="p-3 bg-light rounded text-dark small" id="varText_{{ $index }}" style="white-space: pre-wrap; font-family: inherit; line-height: 1.6; max-height: 320px; overflow-y: auto;">{{ $variation['content'] }}</div>

                    <hr class="my-3">

                    <!-- Actions: Save to Calendar or Social -->
                    <div class="d-flex flex-wrap gap-2">
                        <!-- Save to Calendar Form -->
                        <form action="{{ route('ai.save-to-calendar', $generation) }}" method="POST" class="d-flex align-items-center gap-1 flex-grow-1">
                            @csrf
                            <input type="hidden" name="title" value="{{ $generation->topic }} (Var #{{ $index + 1 }})">
                            <input type="hidden" name="content" value="{{ $variation['content'] }}">
                            <input type="date" name="scheduled_date" class="form-control form-control-sm" value="{{ now()->addDay()->toDateString() }}" style="width: 140px;" required>
                            <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold text-nowrap">
                                <i class="bi bi-calendar-plus me-1"></i> Add to Calendar
                            </button>
                        </form>

                        <!-- Save to Social Post -->
                        <form action="{{ route('ai.save-to-social') }}" method="POST">
                            @csrf
                            <input type="hidden" name="title" value="{{ $generation->topic }}">
                            <input type="hidden" name="platform" value="{{ $generation->platform }}">
                            <input type="hidden" name="content" value="{{ $variation['content'] }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary fw-semibold">
                                <i class="bi bi-share me-1"></i> Create Post Draft
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
