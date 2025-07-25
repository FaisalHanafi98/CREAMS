<!-- Schedule Widget - CREATED_AT: JULY 7 - AUTO BY CLAUDE -->
<div class="widget-card">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-calendar-day"></i> Today's Schedule
        </h3>
        <div class="widget-actions">
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                    aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Schedule Actions:</div>
                    <a class="dropdown-item" href="{{ route('activities.schedule') }}">View Full Schedule</a>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a class="dropdown-item" href="{{ route('activities.create') }}">Create Activity</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="widget-body">
            @if(isset($todaysSessions) && $todaysSessions->count() > 0)
                <div class="schedule-list">
                    @foreach($todaysSessions->take(5) as $session)
                        <div class="schedule-item d-flex align-items-center justify-content-between mb-3">
                            <div class="schedule-info">
                                <div class="schedule-time text-primary font-weight-bold">
                                    {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : 'Time TBA' }}
                                </div>
                                <div class="schedule-activity">
                                    <a href="{{ route('activities.show', $session->activity_id ?? 0) }}" class="text-dark text-decoration-none">
                                        {{ $session->activity->activity_name ?? 'Unknown Activity' }}
                                    </a>
                                </div>
                                <div class="schedule-meta text-muted small">
                                    <i class="fas fa-user"></i> {{ $session->teacher?->name ?? 'Not assigned' }} |
                                    <i class="fas fa-users"></i> {{ $session->enrollments->count() ?? 0 }} enrolled
                                </div>
                            </div>
                            <div class="schedule-status">
                                <span class="badge badge-{{ $session->status == 'completed' ? 'success' : ($session->status == 'ongoing' ? 'warning' : 'info') }}">
                                    {{ ucfirst($session->status ?? 'scheduled') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    @if($todaysSessions->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('activities.schedule') }}" class="btn btn-outline-primary btn-sm">
                                View {{ $todaysSessions->count() - 5 }} more sessions
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-calendar-day fa-2x text-muted"></i>
                    </div>
                    <h6 class="text-muted">No sessions scheduled for today</h6>
                    <p class="text-muted small mb-3">Check the full schedule for upcoming sessions.</p>
                    <a href="{{ route('activities.schedule') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calendar-alt"></i> View Schedule
                    </a>
                </div>
            @endif
    </div>
</div>