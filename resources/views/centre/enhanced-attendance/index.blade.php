@extends('layouts.app')

@section('title', 'Enhanced Attendance Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Enhanced Attendance Header -->
    <div class="attendance-header mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">
                                    <i class="fas fa-clipboard-check text-primary"></i> 
                                    Enhanced Attendance Dashboard
                                </h2>
                                <p class="text-muted mb-0">
                                    {{ $centre->centre_name }} - {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                                </p>
                            </div>
                            <div class="header-actions">
                                <div class="input-group" style="width: 200px;">
                                    <input type="date" class="form-control" id="dateSelector" 
                                           value="{{ $selectedDate }}" onchange="changeDate(this.value)">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="setToday()">
                                            <i class="fas fa-calendar-day"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Overview Cards -->
    <div class="overview-cards mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h3 class="mb-1">{{ $attendanceOverview['total_trainees'] }}</h3>
                        <p class="text-muted mb-0">Total Trainees</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-user-check text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h3 class="mb-1">{{ $attendanceOverview['present_today'] }}</h3>
                        <p class="text-muted mb-0">Present Today</p>
                        <small class="text-success">
                            {{ $attendanceOverview['total_trainees'] > 0 ? round(($attendanceOverview['present_today'] / $attendanceOverview['total_trainees']) * 100, 1) : 0 }}% Attendance Rate
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-calendar-alt text-info" style="font-size: 2rem;"></i>
                        </div>
                        <h3 class="mb-1">{{ $attendanceOverview['total_sessions'] }}</h3>
                        <p class="text-muted mb-0">Scheduled Sessions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-check-circle text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <h3 class="mb-1">{{ $attendanceOverview['completed_sessions'] }}</h3>
                        <p class="text-muted mb-0">Completed Sessions</p>
                        <small class="text-warning">
                            {{ $attendanceOverview['total_sessions'] > 0 ? round(($attendanceOverview['completed_sessions'] / $attendanceOverview['total_sessions']) * 100, 1) : 0 }}% Complete
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Activity Sessions Panel -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> 
                        Today's Activity Sessions
                    </h4>
                </div>
                <div class="card-body">
                    @if($activitySessions->count() > 0)
                        <div class="sessions-timeline">
                            @foreach($activitySessions as $session)
                                <div class="session-card mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            <div class="time-badge">
                                                <div class="time">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</div>
                                                <div class="duration text-muted">
                                                    {{ \Carbon\Carbon::parse($session->start_time)->diffInMinutes(\Carbon\Carbon::parse($session->end_time)) }}min
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-1">{{ $session->activity->activity_name }}</h5>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-tag"></i> {{ $session->activity->category->category_name ?? 'Uncategorized' }}
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-map-marker-alt"></i> {{ $session->venue }}
                                                @if($session->room_number)
                                                    - Room {{ $session->room_number }}
                                                @endif
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-user-tie"></i> {{ $session->teacher->name ?? 'Unassigned' }}
                                            </p>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="participants-info">
                                                <span class="badge badge-info">{{ optional($session->enrollments)->count() ?? 0 }} enrolled</span>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            @if($session->attendance_marked)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Completed
                                                </span>
                                            @else
                                                <a href="{{ route('centre.enhanced-attendance.mark-session', $session->id) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if(optional($session->enrollments)->count() > 0)
                                        <div class="enrolled-trainees mt-2">
                                            <small class="text-muted">Enrolled Trainees:</small>
                                            <div class="trainee-avatars">
                                                @foreach(optional($session->enrollments)->take(5) ?? collect() as $enrollment)
                                                    <span class="avatar-sm" title="{{ $enrollment->trainee->trainee_name }}">
                                                        {{ substr($enrollment->trainee->trainee_name, 0, 2) }}
                                                    </span>
                                                @endforeach
                                                @if(optional($session->enrollments)->count() > 5)
                                                    <span class="avatar-sm more">+{{ optional($session->enrollments)->count() - 5 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                            <h4 class="text-muted mt-3">No Sessions Scheduled</h4>
                            <p class="text-muted">No activity sessions are scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Analytics Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> 
                        Attendance Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Weekly Attendance Rate -->
                    <div class="analytics-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Weekly Attendance Rate</span>
                            <span class="badge badge-success">{{ $analytics['weekly_attendance_rate'] }}%</span>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-success" style="width: {{ $analytics['weekly_attendance_rate'] }}%"></div>
                        </div>
                    </div>

                    <!-- Learning Progress Summary -->
                    @if(isset($analytics['learning_progress_summary']) && count($analytics['learning_progress_summary']) > 0)
                        <div class="analytics-item">
                            <h6>Today's Learning Progress</h6>
                            @foreach($analytics['learning_progress_summary'] as $level => $count)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small>{{ $level }}</small>
                                    <span class="badge badge-info">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('centre.enhanced-attendance.analytics') }}" class="btn btn-outline-success btn-sm btn-block">
                        <i class="fas fa-chart-bar"></i> View Detailed Analytics
                    </a>
                </div>
            </div>

            <!-- Quick Actions Panel -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i> 
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="{{ route('centre.enhanced-attendance.analytics') }}" class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-chart-bar"></i> Attendance Reports
                        </a>
                        <a href="{{ route('centre.enhanced-attendance.export') }}?format=excel" class="btn btn-outline-success btn-block mb-2">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a>
                        <a href="{{ route('activities.index') }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fas fa-calendar-alt"></i> Manage Activities
                        </a>
                        <a href="{{ route('trainees.home') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-users"></i> Manage Trainees
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.session-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.session-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
}

.time-badge {
    background-color: #007bff;
    color: white;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.time-badge .time {
    font-weight: bold;
    font-size: 1.1rem;
}

.time-badge .duration {
    font-size: 0.8rem;
    opacity: 0.8;
}

.avatar-sm {
    display: inline-block;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    text-align: center;
    line-height: 30px;
    font-size: 11px;
    font-weight: bold;
    margin-right: 5px;
    text-transform: uppercase;
}

.avatar-sm.more {
    background-color: #6c757d;
}

.trainee-avatars {
    margin-top: 5px;
}

.analytics-item {
    border-bottom: 1px solid #e9ecef;
}

.analytics-item:last-child {
    border-bottom: none;
}

.attendance-header .header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sessions-timeline {
    max-height: 600px;
    overflow-y: auto;
}

.overview-cards .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.overview-cards .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

.stat-icon i {
    opacity: 0.8;
}
</style>
@endpush

@push('scripts')
<script>
function changeDate(date) {
    window.location.href = `{{ route('centre.enhanced-attendance.index') }}?date=${date}`;
}

function setToday() {
    const today = new Date().toISOString().split('T')[0];
    changeDate(today);
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 300000);

// Initialize tooltips
$(document).ready(function() {
    $('[title]').tooltip();
});
</script>
@endpush
@endsection