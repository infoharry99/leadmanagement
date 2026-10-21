@extends('layouts.app')

@section('title', 'Weekly Content Calendar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Weekly Content & Publishing Calendar</h4>
        <p class="text-muted small mb-0">Plan daily content across LinkedIn, Instagram, Facebook, TikTok, and Nexteck's engineering blog.</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('calendar.index', ['view' => 'week']) }}" class="btn {{ $view === 'week' ? 'btn-dark' : 'btn-light border' }}">
                <i class="bi bi-calendar3-week"></i> Week View
            </a>
            <a href="{{ route('calendar.index', ['view' => 'list']) }}" class="btn {{ $view === 'list' ? 'btn-dark' : 'btn-light border' }}">
                <i class="bi bi-list-ul"></i> List View
            </a>
        </div>
        <a href="{{ route('calendar.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Plan Content
        </a>
    </div>
</div>

@if($view === 'week')
    <!-- Week Grid View (7 Days) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-calendar-event text-primary me-2"></i>Week of {{ $startOfWeek->format('F d, Y') }} - {{ $endOfWeek->format('F d, Y') }}</span>
            <span class="badge bg-light text-muted border">Active Plan</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                @php
                    $daysOfWeek = [
                        ['day' => 'Monday', 'date' => $startOfWeek->copy()],
                        ['day' => 'Tuesday', 'date' => $startOfWeek->copy()->addDays(1)],
                        ['day' => 'Wednesday', 'date' => $startOfWeek->copy()->addDays(2)],
                        ['day' => 'Thursday', 'date' => $startOfWeek->copy()->addDays(3)],
                        ['day' => 'Friday', 'date' => $startOfWeek->copy()->addDays(4)],
                        ['day' => 'Saturday', 'date' => $startOfWeek->copy()->addDays(5)],
                        ['day' => 'Sunday', 'date' => $startOfWeek->copy()->addDays(6)],
                    ];
                @endphp

                @foreach($daysOfWeek as $d)
                    @php
                        $dayDateStr = $d['date']->toDateString();
                        $dayItems = $items->where('scheduled_date', $d['date']);
                    @endphp
                    <div class="col" style="min-width: 140px;">
                        <div class="p-2 border rounded-3 bg-light h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-dark">{{ $d['day'] }}</span>
                                <span class="text-muted small" style="font-size: 0.72rem;">{{ $d['date']->format('M d') }}</span>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                @forelse($dayItems as $item)
                                    @php
                                        $platformIcon = match($item->platform) {
                                            'linkedin' => 'bi-linkedin text-primary',
                                            'instagram' => 'bi-instagram text-danger',
                                            'facebook' => 'bi-facebook text-primary',
                                            'tiktok' => 'bi-tiktok text-dark',
                                            'blog' => 'bi-journal-richtext text-success',
                                            default => 'bi-globe text-secondary',
                                        };
                                        $statusClass = match($item->status) {
                                            'published' => 'border-success',
                                            'approved', 'scheduled' => 'border-primary',
                                            'rejected' => 'border-danger',
                                            default => 'border-secondary',
                                        };
                                    @endphp
                                    <div class="card p-2 shadow-sm border-start border-3 {{ $statusClass }}" style="font-size: 0.78rem;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <i class="bi {{ $platformIcon }}"></i>
                                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">
                                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                            </span>
                                        </div>
                                        <div class="fw-semibold text-truncate mb-1" title="{{ $item->title }}">{{ $item->title }}</div>
                                        <div class="text-muted small" style="font-size: 0.68rem;">{{ substr($item->scheduled_time, 0, 5) }} • {{ $item->assignedUser?->name ?? 'Team' }}</div>
                                        
                                        <div class="mt-2 pt-1 border-top d-flex gap-1">
                                            @if($item->status === 'planned' || $item->status === 'draft')
                                                <form action="{{ route('calendar.status', $item) }}" method="POST" class="w-100">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn btn-outline-success py-0 px-1 w-100" style="font-size: 0.65rem;">Approve</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-muted" style="font-size: 0.72rem;">
                                        <em>No posts scheduled</em>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <!-- List View -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Platform</th>
                        <th>Content Title</th>
                        <th>Format</th>
                        <th>Target Audience</th>
                        <th>Date & Time</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">
                                    {{ $item->platform }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $item->title }}</div>
                                <small class="text-muted">{{ Str::limit($item->campaign_goal, 50) }}</small>
                            </td>
                            <td><span class="badge bg-light text-muted border">{{ ucfirst($item->content_type) }}</span></td>
                            <td><small class="text-muted">{{ $item->target_audience ?? 'General Tech' }}</small></td>
                            <td>
                                <div>{{ $item->scheduled_date->format('M d, Y') }}</div>
                                <small class="text-muted">{{ substr($item->scheduled_time, 0, 5) }}</small>
                            </td>
                            <td><small class="text-muted">{{ $item->assignedUser?->name ?? 'Unassigned' }}</small></td>
                            <td>
                                <span class="badge bg-{{ $item->status === 'published' ? 'success' : ($item->status === 'approved' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($item->status !== 'approved' && $item->status !== 'published')
                                    <form action="{{ route('calendar.status', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No content calendar items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
