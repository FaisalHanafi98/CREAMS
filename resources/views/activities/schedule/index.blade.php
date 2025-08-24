@extends('layouts.app')

@section('title', 'Activity Schedule Repository')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt me-2"></i>Activity Schedule Repository
            </h1>
            <p class="mb-0 text-muted">Complete archive of all activity sessions across all centres</p>
        </div>
        <div class="text-end">
            <span class="badge bg-info fs-6">{{ isset($sessions) && method_exists($sessions, 'total') ? $sessions->total() : $sessions->count() }} Total Sessions</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('activities.schedule') }}" id="scheduleFilters">
                <div class="row g-3 align-items-end">
                    <!-- Search Type & Value -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">SEARCH BY</label>
                        <div class="input-group">
                            <select name="search_type" class="form-select form-select-sm" style="flex: 0 0 auto; width: 120px;">
                                <option value="activity" {{ request('search_type') == 'activity' ? 'selected' : '' }}>Activity</option>
                                <option value="staff" {{ request('search_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="trainee" {{ request('search_type') == 'trainee' ? 'selected' : '' }}>Trainee</option>
                                <option value="room" {{ request('search_type') == 'room' ? 'selected' : '' }}>Room</option>
                            </select>
                            <input type="text" name="search_value" class="form-control form-control-sm" 
                                   placeholder="Enter search term..." 
                                   value="{{ request('search_value') }}">
                        </div>
                    </div>

                    <!-- Centre Filter -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">CENTRE</label>
                        <select name="centre" class="form-select form-select-sm">
                            <option value="">All Centres</option>
                            @if(isset($centres))
                                @foreach($centres as $centre)
                                    <option value="{{ $centre->centre_id }}" {{ request('centre') == $centre->centre_id ? 'selected' : '' }}>
                                        {{ $centre->centre_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">CATEGORY</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @if(isset($categories))
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">STATUS</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="future" {{ request('status') == 'future' ? 'selected' : '' }}>Future</option>
                            <option value="progress" {{ request('status') == 'progress' ? 'selected' : '' }}>Progress</option>
                            <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">DATE RANGE</label>
                        <select name="date_range" class="form-select form-select-sm">
                            <option value="">All Dates</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>Past Sessions</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <!-- Clear Filters -->
                @if(request()->hasAny(['search_type', 'search_value', 'centre', 'category', 'status', 'date_range']))
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('activities.schedule') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Clear All Filters
                            </a>
                            <span class="ms-2 small text-muted">
                                Showing {{ $sessions->count() }} of {{ isset($sessions) && method_exists($sessions, 'total') ? $sessions->total() : $sessions->count() }} sessions
                            </span>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($sessions->count() > 0)
                <div class="table-responsive">
                    <div class="sessions-list">
                        @foreach($sessions as $session)
                            <div class="session-item border-bottom">
                                <div class="row align-items-center py-3 px-4">
                                    <!-- Activity Info -->
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-start">
                                            <div class="activity-color-indicator me-3" 
                                                 style="background-color: {{ $session->color_code ?? '#007bff' }}; width: 4px; height: 40px; border-radius: 2px;"></div>
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark">{{ $session->activity->activity_name ?? 'Unknown Activity' }}</h6>
                                                <div class="activity-badges mb-1">
                                                    <span class="badge bg-light text-dark border small">
                                                        {{ $session->activity->activity_type ?? 'General' }}
                                                    </span>
                                                    @if($session->activity->difficulty_level ?? false)
                                                        <span class="badge bg-info text-white small ms-1">
                                                            <i class="fas fa-layer-group me-1"></i>{{ $session->activity->difficulty_level }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Learning Outcomes Indicator -->
                                                @if(isset($session->activity->learning_outcomes) && $session->activity->learning_outcomes->count() > 0)
                                                    <div class="learning-outcomes-preview">
                                                        <small class="text-muted">
                                                            <i class="fas fa-graduation-cap me-1"></i>
                                                            {{ $session->activity->learning_outcomes->count() }} Learning Outcomes
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date & Time -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="fw-bold text-primary">
                                                {{ $session->scheduled_date ? \Carbon\Carbon::parse($session->scheduled_date)->format('M d, Y') : 'Not Set' }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('g:i A') : 'Not set' }}
                                                @if($session->end_time)
                                                    - {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
                                                @endif
                                            </div>
                                            @if($session->duration_minutes)
                                                <div class="small text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $session->duration_minutes }}min
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Staff & Location -->
                                    <div class="col-md-2">
                                        <div>
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-user-tie me-1"></i>INSTRUCTOR
                                            </div>
                                            <div class="fw-medium">{{ $session->teacher->name ?? 'Not Assigned' }}</div>
                                        </div>
                                        <div class="mt-2">
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-map-marker-alt me-1"></i>LOCATION
                                            </div>
                                            <div class="small">{{ $session->room_details ?? 'Location TBD' }}</div>
                                        </div>
                                    </div>

                                    <!-- Participants -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="position-relative">
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: {{ $session->max_capacity > 0 ? (($session->enrollments->count() ?? 0) / $session->max_capacity) * 100 : 0 }}%"></div>
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    {{ $session->enrollments->count() ?? 0 }}/{{ $session->max_capacity ?? 'N/A' }} participants
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status & Actions -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                @php
                                                    $statusMap = [
                                                        'scheduled' => ['badge' => 'bg-primary', 'text' => 'Future'],
                                                        'ongoing' => ['badge' => 'bg-warning', 'text' => 'Progress'],
                                                        'completed' => ['badge' => 'bg-success', 'text' => 'Done'],
                                                        'cancelled' => ['badge' => 'bg-danger', 'text' => 'Cancelled']
                                                    ];
                                                    $statusInfo = $statusMap[$session->status ?? 'scheduled'] ?? ['badge' => 'bg-secondary', 'text' => 'Unknown'];
                                                @endphp
                                                <span class="badge {{ $statusInfo['badge'] }} px-3 py-2">
                                                    {{ $statusInfo['text'] }}
                                                </span>
                                                @if($session->activity->centre ?? false)
                                                    <div class="small text-muted mt-1">
                                                        <i class="fas fa-building me-1"></i>{{ $session->activity->centre->centre_name }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="action-buttons">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('activities.show', $session->activity_id) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                    @if(in_array($role ?? session('role'), ['admin', 'supervisor', 'teacher']))
                                                        <a href="{{ route('activities.sessions', $session->activity_id) }}" class="btn btn-outline-success btn-sm">
                                                            <i class="fas fa-users me-1"></i>Manage
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                @if(method_exists($sessions, 'links'))
                    <div class="d-flex justify-content-between align-items-center p-4 border-top bg-light">
                        <div class="small text-muted">
                            Showing {{ $sessions->firstItem() }} to {{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions
                        </div>
                        <div>
                            {{ $sessions->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif

            @else
                <!-- No Sessions -->
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-times fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No sessions found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search_type', 'search_value', 'centre', 'category', 'status', 'date_range']))
                            Try adjusting your filters to see more sessions.
                        @else
                            No activity sessions have been scheduled yet.
                        @endif
                    </p>
                    @if(request()->hasAny(['search_type', 'search_value', 'centre', 'category', 'status', 'date_range']))
                        <a href="{{ route('activities.schedule') }}" class="btn btn-outline-primary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.session-item:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.activity-color-indicator {
    min-width: 4px;
    flex-shrink: 0;
}

.sessions-list .session-item:last-child {
    border-bottom: none !important;
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
}

/* Filter form styling */
.form-select-sm, .form-control-sm {
    font-size: 0.875rem;
}

.input-group .form-select {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.activity-badges .badge {
    margin-right: 4px;
}

.action-buttons .btn-group {
    margin-bottom: 5px;
}

.action-buttons .btn-sm {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .session-item .row > div {
        margin-bottom: 1rem;
    }
    
    .session-item .row > div:last-child {
        margin-bottom: 0;
    }
    
    .action-buttons .btn-group {
        flex-direction: column;
    }
    
    .action-buttons .btn-sm {
        margin-bottom: 2px;
    }
}
</style>
@endsection