@extends('layouts.app')

@section('title', 'Main Admin Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Executive Marketing & CRM Dashboard</h4>
        <p class="text-muted small mb-0">Real-time performance metrics, lead acquisition pipeline, and automation health.</p>
    </div>

    <!-- Date Period Filter -->
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold"><i class="bi bi-funnel"></i> Timeframe:</span>
            <select name="period" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="width: 140px;">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
                <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last 12 Months</option>
            </select>
        </form>
        <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> Add New Lead
        </a>
    </div>
</div>

<!-- Primary KPI Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total & New Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3 shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-white-50 text-uppercase small fw-bold tracking-wider" style="font-size: 0.72rem;">Total Active Leads</span>
                    <h2 class="fw-bold mt-1 mb-0">{{ number_format($totalLeads) }}</h2>
                </div>
                <div class="p-2 rounded-3 bg-white bg-opacity-20 text-white">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-white-50 small mt-3">
                <span class="badge bg-white text-primary fw-bold">+{{ $newLeadsThisPeriod }} new</span>
                <span>added in last {{ $days }} days</span>
            </div>
        </div>
    </div>

    <!-- Outreach & Delivery Rate -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3 shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0EA5E9 0%, #0369A1 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-white-50 text-uppercase small fw-bold" style="font-size: 0.72rem;">Emails Delivered</span>
                    <h2 class="fw-bold mt-1 mb-0">{{ number_format($emailsDelivered) }}</h2>
                </div>
                <div class="p-2 rounded-3 bg-white bg-opacity-20 text-white">
                    <i class="bi bi-envelope-check-fill fs-4"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-white-50 small mt-3">
                <span>Total Sent: <strong>{{ number_format($emailsSent) }}</strong></span>
                <span class="mx-1">•</span>
                <span class="badge bg-danger bg-opacity-75 text-white">{{ $emailsBounced }} bounced</span>
            </div>
        </div>
    </div>

    <!-- Replies & Qualified Intent -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3 shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #10B981 0%, #047857 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-white-50 text-uppercase small fw-bold" style="font-size: 0.72rem;">Replies Received</span>
                    <h2 class="fw-bold mt-1 mb-0">{{ number_format($repliesReceived) }}</h2>
                </div>
                <div class="p-2 rounded-3 bg-white bg-opacity-20 text-white">
                    <i class="bi bi-chat-heart-fill fs-4"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-white-50 small mt-3">
                <span class="badge bg-white text-success fw-bold">{{ $positiveReplies }} positive</span>
                <span>interested / meetings</span>
            </div>
        </div>
    </div>

    <!-- Conversion & Handover Pipeline -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3 shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #F59E0B 0%, #B45309 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-white-50 text-uppercase small fw-bold" style="font-size: 0.72rem;">Lead Conversion Rate</span>
                    <h2 class="fw-bold mt-1 mb-0">{{ $conversionRate }}%</h2>
                </div>
                <div class="p-2 rounded-3 bg-white bg-opacity-20 text-white">
                    <i class="bi bi-trophy-fill fs-4"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-white-50 small mt-3">
                <span class="badge bg-white text-dark fw-bold">{{ $handoverQueue->count() }} in handover</span>
                <span>queue awaiting manager</span>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Operational Stats Bar -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-light rounded-3 text-primary"><i class="bi bi-shield-x fs-4"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">Suppression List</div>
                    <h4 class="fw-bold mb-0">{{ number_format($suppressionCount) }}</h4>
                    <small class="text-muted">Unsubscribes & Hard Bounces</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-light rounded-3 text-info"><i class="bi bi-share fs-4"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">Social Media Posts</div>
                    <h4 class="fw-bold mb-0">{{ $socialPublished }}</h4>
                    <small class="text-muted">{{ $socialScheduled }} scheduled | {{ $socialFailed }} failed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-light rounded-3 text-warning"><i class="bi bi-chat-quote fs-4"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">Comments to Answer</div>
                    <h4 class="fw-bold mb-0">{{ $commentsAttention }}</h4>
                    <small class="text-muted">Requiring team attention</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-light rounded-3 text-success"><i class="bi bi-journal-check fs-4"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">Blogs & Articles</div>
                    <h4 class="fw-bold mb-0">{{ $blogsPublished }}</h4>
                    <small class="text-muted">{{ $blogsPending }} in draft / review</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Data Visualization Row -->
<div class="row g-4 mb-4">
    <!-- Leads by Status Chart -->
    <div class="col-lg-5">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Leads by Status</span>
                <span class="badge bg-light text-muted border">{{ count($leadsByStatus) }} Statuses</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center p-3">
                <div style="height: 250px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-center mt-3 small">
                    @foreach($leadsByStatus as $st => $count)
                        <span class="badge badge-status-{{ $st }} px-2 py-1">
                            {{ ucfirst(str_replace('_', ' ', $st)) }}: {{ $count }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Leads by Source Chart -->
    <div class="col-lg-7">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-bar-chart-fill text-success me-2"></i>Acquisition Channels (Leads by Source)</span>
                <span class="badge bg-light text-muted border">Live Database Records</span>
            </div>
            <div class="card-body p-3">
                <div style="height: 270px; width: 100%;">
                    <canvas id="sourceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Follow-Up Tasks Due & Manager Handover Queue -->
<div class="row g-4 mb-4">
    <!-- Tasks Due Today -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold"><i class="bi bi-alarm-fill text-danger me-2"></i>Follow-Up Tasks Due Today</span>
                </div>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($tasksDueToday->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check-circle text-success fs-2 mb-2 d-block"></i>
                        No pending follow-ups due today! Great work.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($tasksDueToday as $task)
                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 320px;">{{ $task->title }}</div>
                                    <small class="text-muted d-block">
                                        For: <strong>{{ $task->lead?->name }}</strong> ({{ $task->lead?->company?->name ?? 'Direct' }})
                                    </small>
                                    <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning text-dark' : 'secondary') }} mt-1" style="font-size: 0.68rem;">
                                        Priority: {{ ucfirst($task->priority) }}
                                    </span>
                                </div>
                                <div>
                                    <form action="{{ route('tasks.complete', $task) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check2"></i> Done
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Manager Handover Queue -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold"><i class="bi bi-person-fill-exclamation text-warning me-2"></i>Manager Handover Queue</span>
                </div>
                <a href="{{ route('handovers.index') }}" class="btn btn-sm btn-outline-secondary">View Queue</a>
            </div>
            <div class="card-body p-0">
                @if($handoverQueue->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox text-muted fs-2 mb-2 d-block"></i>
                        Handover queue is clear. No leads waiting for sales manager assignment.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($handoverQueue as $handover)
                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $handover->lead?->name }}</div>
                                    <small class="text-muted d-block">{{ $handover->lead?->company?->name }} • {{ $handover->lead?->industry }}</small>
                                    <span class="badge bg-danger mt-1" style="font-size: 0.68rem;">{{ ucfirst($handover->urgency) }} Handover</span>
                                    <small class="text-secondary ms-1">{{ Str::limit($handover->handoff_notes, 60) }}</small>
                                </div>
                                <div>
                                    <form action="{{ route('handovers.acknowledge', $handover) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Acknowledge
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Timeline Activities & Integration Health -->
<div class="row g-4">
    <!-- Recent Activities Feed -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-activity text-primary me-2"></i>Recent CRM & Outreach Activity</span>
                <span class="text-muted small">Real-time audit log</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Lead Contact</th>
                                <th>Description</th>
                                <th>User / System</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $act)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucfirst(str_replace('_', ' ', $act->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($act->lead)
                                            <a href="{{ route('leads.show', $act->lead) }}" class="fw-semibold text-decoration-none text-dark">
                                                {{ $act->lead->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $act->description }}</td>
                                    <td><small class="text-muted">{{ $act->user?->name ?? 'System Queue' }}</small></td>
                                    <td><small class="text-muted">{{ $act->created_at->diffForHumans() }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No recent activities found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Integrations Health Status -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="bi bi-hdd-network text-info me-2"></i>Connected Services & Health
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-column gap-3">
                    @foreach($integrationHealth as $service)
                        <div class="p-2.5 border rounded-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold small text-capitalize">{{ str_replace('_', ' ', $service->service_name) }}</div>
                                <small class="text-muted">Checked: {{ $service->last_health_check_at ? $service->last_health_check_at->diffForHumans() : 'Recently' }}</small>
                            </div>
                            <span class="badge bg-{{ $service->health_status === 'healthy' ? 'success' : 'secondary' }} d-flex align-items-center gap-1">
                                <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> {{ ucfirst($service->health_status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Status Doughnut Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusLabels = {!! json_encode(array_keys($leadsByStatus)) !!}.map(s => s.replace('_', ' ').toUpperCase());
        const statusData = {!! json_encode(array_values($leadsByStatus)) !!};
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#3B82F6', '#10B981', '#059669', '#6366F1', '#A855F7', '#64748B', '#EF4444'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '65%'
            }
        });

        // Source Bar Chart
        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        const sourceLabels = {!! json_encode(array_keys($leadsBySource)) !!};
        const sourceData = {!! json_encode(array_values($leadsBySource)) !!};

        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Leads Generated',
                    data: sourceData,
                    backgroundColor: '#4F46E5',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endpush
