{{-- Today's Sessions --}}
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-calendar-day"></i> Today's Sessions
        </h2>
        <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-primary">
            View All Sessions
        </a>
    </div>
    <div class="card-body">
        @if($todaySessions->count() > 0)
            <div class="session-list">
                @foreach($todaySessions as $session)
                    <div class="session-item">
                        <div class="session-time">
                            <i class="fas fa-clock"></i>
                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                        </div>
                        <div class="session-details">
                            <h4>{{ $session->activity->activity_name }}</h4>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> {{ $session->location }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-users"></i> {{ $session->enrollments->count() }} students
                            </p>
                        </div>
                        <div class="session-actions">
                            <a href="{{ route('activities.attendance', [$session->activity_id, $session->id]) }}" 
                               class="btn btn-sm btn-success">
                                <i class="fas fa-clipboard-check"></i> Mark Attendance
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No sessions scheduled for today</p>
            </div>
        @endif
    </div>
</div>

{{-- Weekly Schedule --}}
<div class="content-card mt-4">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-calendar-week"></i> This Week's Schedule
        </h2>
    </div>
    <div class="card-body">
        <div class="week-schedule">
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                <div class="day-schedule">
                    <h5 class="day-name">{{ $day }}</h5>
                    @if(isset($weekSchedule[$day]))
                        @foreach($weekSchedule[$day] as $session)
                            <div class="schedule-slot">
                                <span class="time">{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</span>
                                <span class="activity">{{ $session->activity->activity_name }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="no-sessions">No sessions</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>