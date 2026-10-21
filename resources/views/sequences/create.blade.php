@extends('layouts.app')

@section('title', 'Build Follow-Up Sequence')

@section('content')
<div class="mb-4">
    <a href="{{ route('sequences.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i> Back to Sequences
    </a>
    <h4 class="fw-bold mb-1">Build Multi-Stage Follow-Up Sequence</h4>
    <p class="text-muted small">Configure automated Phase A (weekly), Phase B (twice-monthly), and Phase C (monthly) outreach steps.</p>
</div>

<form action="{{ route('sequences.store') }}" method="POST">
    @csrf
    <div class="row g-4 mb-4">
        <!-- Sequence Parameters -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 80px;">
                <div class="card-header bg-transparent fw-bold">
                    <i class="bi bi-sliders text-primary me-2"></i>Sequence Parameters
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sequence Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', 'B2B Custom Software Outreach Sequence') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', 'Automated touchpoints spanning 60-90 days with automatic stop on reply.') }}</textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Window Start</label>
                            <input type="time" name="send_window_start" class="form-control form-control-sm" value="09:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Window End</label>
                            <input type="time" name="send_window_end" class="form-control form-control-sm" value="17:00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Timezone</label>
                        <select name="timezone" class="form-select form-select-sm">
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">US Eastern (America/New_York)</option>
                            <option value="America/Chicago">US Central (America/Chicago)</option>
                            <option value="America/Los_Angeles">US Pacific (America/Los_Angeles)</option>
                            <option value="Europe/London">London (GMT/BST)</option>
                            <option value="Asia/Kolkata">India (Asia/Kolkata)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="exclude_weekends" value="1" id="excludeWeekends" checked>
                            <label class="form-check-label small fw-semibold" for="excludeWeekends">Exclude Saturdays & Sundays</label>
                        </div>
                    </div>

                    <div class="p-2.5 bg-light rounded small border mb-3">
                        <i class="bi bi-shield-check text-success me-1"></i> Sequence automatically halts if lead replies, unsubscribes, bounces, or converts.
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                        Save & Launch Sequence
                    </button>
                </div>
            </div>
        </div>

        <!-- Multi-Stage Steps Builder -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold fs-6">Configured Outreach Stages (Phase A, B, C)</span>
                <span class="badge bg-light text-muted border">Default Sequence Template</span>
            </div>

            <!-- Step 1: Initial -->
            <div class="card shadow-sm mb-3 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary">Step 1 • Initial Send (Day 0)</span>
                        <span class="text-muted small">Phase A (Weekly Outreach)</span>
                    </div>
                    <input type="hidden" name="steps[0][step_order]" value="1">
                    <input type="hidden" name="steps[0][phase]" value="phase_a">
                    <input type="hidden" name="steps[0][delay_days]" value="0">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Subject Line</label>
                        <input type="text" name="steps[0][subject]" class="form-control form-control-sm" value="Accelerating @{{company_name}}'s software roadmap with Nexteck">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Message Template</label>
                        <textarea name="steps[0][body_template]" class="form-control form-control-sm" rows="3">Hi @{{first_name}}, I noticed the impressive work @{{company_name}} is doing in @{{industry}}. At Nexteck, we architect scalable Laravel web platforms and custom software. Would you be open to a 10-minute discovery call next week? Best regards, Nexteck Team.</textarea>
                    </div>
                </div>
            </div>

            <!-- Step 2: Follow-up 1 (Day 7) -->
            <div class="card shadow-sm mb-3 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-info text-dark">Step 2 • Follow-Up 1 (+7 Days)</span>
                        <span class="text-muted small">Phase A (Weekly Outreach)</span>
                    </div>
                    <input type="hidden" name="steps[1][step_order]" value="2">
                    <input type="hidden" name="steps[1][phase]" value="phase_a">
                    <input type="hidden" name="steps[1][delay_days]" value="7">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Subject Line</label>
                        <input type="text" name="steps[1][subject]" class="form-control form-control-sm" value="Quick follow-up regarding engineering capacity at @{{company_name}}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Message Template</label>
                        <textarea name="steps[1][body_template]" class="form-control form-control-sm" rows="3">Hi @{{first_name}}, following up on my previous note. Many engineering leaders we speak with in @{{industry}} are looking to reduce cloud costs and modernize backends without bloating their in-house payroll. Does this align with your priorities for @{{company_name}} this quarter?</textarea>
                    </div>
                </div>
            </div>

            <!-- Step 3: Follow-up 2 (Day 14) -->
            <div class="card shadow-sm mb-3 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-info text-dark">Step 3 • Follow-Up 2 (+7 Days)</span>
                        <span class="text-muted small">Phase A (Case Study Showcase)</span>
                    </div>
                    <input type="hidden" name="steps[2][step_order]" value="3">
                    <input type="hidden" name="steps[2][phase]" value="phase_a">
                    <input type="hidden" name="steps[2][delay_days]" value="7">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Subject Line</label>
                        <input type="text" name="steps[2][subject]" class="form-control form-control-sm" value="Case study: How we reduced API latency by 45% for enterprise platforms">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Message Template</label>
                        <textarea name="steps[2][body_template]" class="form-control form-control-sm" rows="3">Hi @{{first_name}}, I wanted to share a brief breakdown of how we solved complex queue bottlenecks for an enterprise client using Laravel and asynchronous background pipelines. Let me know if you would like me to send over the PDF summary.</textarea>
                    </div>
                </div>
            </div>

            <!-- Step 4: Phase B Twice Monthly (+14 Days) -->
            <div class="card shadow-sm mb-3 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-warning text-dark">Step 4 • Check-In (+14 Days)</span>
                        <span class="text-muted small">Phase B (Twice Monthly Touchpoint)</span>
                    </div>
                    <input type="hidden" name="steps[3][step_order]" value="4">
                    <input type="hidden" name="steps[3][phase]" value="phase_b">
                    <input type="hidden" name="steps[3][delay_days]" value="14">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Subject Line</label>
                        <input type="text" name="steps[3][subject]" class="form-control form-control-sm" value="Checking in on @{{company_name}}'s software roadmap">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Message Template</label>
                        <textarea name="steps[3][body_template]" class="form-control form-control-sm" rows="3">Hi @{{first_name}}, checking in to see if any new software initiatives or web app modernizations have come up for @{{company_name}}. Nexteck is ready to assist whenever you need senior engineering velocity.</textarea>
                    </div>
                </div>
            </div>

            <!-- Step 5: Phase C Monthly (+30 Days) -->
            <div class="card shadow-sm mb-3 border-start border-4 border-secondary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary">Step 5 • Monthly Keeping-In-Touch (+30 Days)</span>
                        <span class="text-muted small">Phase C (Monthly Touchpoint)</span>
                    </div>
                    <input type="hidden" name="steps[4][step_order]" value="5">
                    <input type="hidden" name="steps[4][phase]" value="phase_c">
                    <input type="hidden" name="steps[4][delay_days]" value="30">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Subject Line</label>
                        <input type="text" name="steps[4][subject]" class="form-control form-control-sm" value="Nexteck resources & staying connected with @{{company_name}}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Message Template</label>
                        <textarea name="steps[4][body_template]" class="form-control form-control-sm" rows="3">Hi @{{first_name}}, I know how busy quarterly cycles get. We publish weekly engineering breakdowns on Laravel architecture and digital automation on our blog. If you ever need software development support, feel free to reply directly to this thread.</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
