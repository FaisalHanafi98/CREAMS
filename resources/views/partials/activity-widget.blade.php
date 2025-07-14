<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-tasks mr-1"></i> Activity Overview
        </h5>
        <a href="{{ route('activities.index') }}" class="btn btn-sm btn-primary">
            View All
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="small-stat-card">
                    <div class="stat-icon-small bg-info">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content-small">
                        <h4>{{ $activityStats['todays_sessions'] ?? 0 }}</h4>
                        <p>Today's Sessions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="small-stat-card">
                    <div class="stat-icon-small bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content-small">
                        <h4>{{ $activityStats['attendance_today'] ?? 0 }}</h4>
                        <p>Present Today</p>
                    </div>
                </div>
            </div>
        </div>
        
        @if(session('role') === 'teacher' && isset($todaySessions))
            <hr>
            <h6 class="font-weight-bold">Your Schedule Today:</h6>
            @forelse($todaySessions as $session)
                <div class="schedule-item">
                    <i class="fas fa-clock text-muted"></i>
                    <span class="ml-2">
                        {{ date('g:i A', strtotime($session->start_time)) }} - 
                        {{ $session->activity->activity_name }} 
                        ({{ $session->class_name }})
                    </span>
                    <a href="{{ route('activities.attendance', $session->id) }}" 
                       class="btn btn-sm btn-outline-primary float-right">
                        Mark Attendance
                    </a>
                </div>
            @empty
                <p class="text-muted small">No sessions scheduled today</p>
            @endforelse
        @endif
    </div>
</div>