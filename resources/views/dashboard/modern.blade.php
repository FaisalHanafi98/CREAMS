@extends('layouts.app')

@section('title', 'Dashboard - CREAMS')

@section('content')
<div class="enhanced-dashboard">
    <!-- Enhanced Header Section -->
    <div class="dashboard-header-enhanced mb-4">
        <div class="header-gradient"></div>
        <div class="header-pattern"></div>
        <div class="header-content">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7">
                    <div class="welcome-section">
                        <div class="user-greeting">
                            <div class="greeting-badge">
                                @php
                                    $hour = date('H');
                                    $greeting = $hour < 12 ? '🌅 Good Morning' : ($hour < 17 ? '☀️ Good Afternoon' : '🌙 Good Evening');
                                    $roleEmoji = [
                                        'admin' => '👨‍💼',
                                        'teacher' => '👩‍🏫', 
                                        'supervisor' => '👨‍💼',
                                        'ajk' => '👷‍♂️',
                                        'trainee' => '🎓'
                                    ];
                                @endphp
                                <span class="greeting-time">{{ $greeting }}</span>
                                <span class="user-role">{{ $roleEmoji[$role ?? 'admin'] ?? '👤' }} {{ ucfirst($role ?? 'User') }}</span>
                            </div>
                        </div>
                        <h1 class="dashboard-title-enhanced">
                            <span class="title-icon">{{ $roleEmoji[$role ?? 'admin'] ?? '👋' }}</span>
                            Welcome back, <span class="user-name-highlight">{{ $user_name ?? session('name', 'User') }}</span>!
                        </h1>
                        <p class="dashboard-subtitle-enhanced">
                            <span class="subtitle-primary">Your {{ ucfirst($role ?? 'user') }} dashboard is ready.</span>
                            <span class="subtitle-secondary">Today is {{ $current_time ?? now()->format('l, F j, Y') }}</span>
                            @if(isset($todays_centre_activities) && count($todays_centre_activities) > 0)
                                <span class="subtitle-highlight">• {{ count($todays_centre_activities) }} activities scheduled today</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 text-end">
                    <div class="header-actions">
                        <div class="dashboard-stats-mini">
                            <div class="stat-mini">
                                <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ count($todays_centre_activities ?? []) }}</div>
                                    <div class="stat-label">Today</div>
                                </div>
                            </div>
                            <div class="stat-mini">
                                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ count($todays_centre_activities ?? []) + count($upcoming_sessions ?? []) }}</div>
                                    <div class="stat-label">Centre Activities</div>
                                </div>
                            </div>
                            <div class="stat-mini">
                                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ $personal_stats['completion_rate'] ?? 0 }}%</div>
                                    <div class="stat-label">{{ $role === 'trainee' ? 'Attendance' : 'Completion' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="performance-indicator">
                            <div class="performance-circle">
                                <span class="performance-text">{{ $performance['cache_status'] ?? 'Ready' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Navigation Tabs -->
    <div class="dashboard-navigation-tabs mb-4">
        <div class="tab-navigation-enhanced">
            <ul class="nav nav-pills dashboard-nav-pills justify-content-center" id="dashboardTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">
                        <i class="fas fa-chart-line mr-2"></i>General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="personal-tab" data-toggle="pill" href="#personal" role="tab" aria-controls="personal" aria-selected="false">
                        <i class="fas fa-user mr-2"></i>Personal
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="dashboardTabContent">
        <!-- General Tab -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">

    <!-- Enhanced Quick Stats with Drill-down -->
    <div class="row g-4 mb-4">
        @if(in_array($role, ['admin', 'supervisor']))
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card-enhanced primary-card">
                <div class="stats-icon-enhanced">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-content-enhanced">
                    <h3 class="stats-number-enhanced">{{ $stats_flat['total_users'] ?? 0 }}</h3>
                    <p class="stats-label-enhanced">Total Users</p>
                    <div class="stats-trend-enhanced positive">
                        <i class="fas fa-arrow-up"></i> Active
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card-enhanced success-card">
                <div class="stats-icon-enhanced">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stats-content-enhanced">
                    <h3 class="stats-number-enhanced">{{ $stats_flat['total_trainees'] ?? 0 }}</h3>
                    <p class="stats-label-enhanced">Active Trainees</p>
                    <div class="stats-trend-enhanced positive">
                        <i class="fas fa-arrow-up"></i> Growing
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card-enhanced warning-card">
                <div class="stats-icon-enhanced">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stats-content-enhanced">
                    <h3 class="stats-number-enhanced">{{ $stats_flat['total_activities'] ?? 0 }}</h3>
                    <p class="stats-label-enhanced">Active Programs</p>
                    <div class="stats-trend-enhanced positive">
                        <i class="fas fa-arrow-up"></i> Running
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($role, ['admin', 'supervisor']))
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card-enhanced info-card">
                <div class="stats-icon-enhanced">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stats-content-enhanced">
                    <h3 class="stats-number-enhanced">{{ $stats_flat['active_centres'] ?? 1 }}</h3>
                    <p class="stats-label-enhanced">Active Centres</p>
                    <div class="stats-trend-enhanced stable">
                        <i class="fas fa-minus"></i> Stable
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

            <!-- Main Dashboard Content -->
            <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Current Active Sessions with Real-time Updates -->
            @if(isset($current_sessions) && count($current_sessions) > 0)
            <div class="dashboard-widget active-sessions-widget" id="currentSessionsWidget">
                <div class="widget-header">
                    <h5 class="widget-title">
                        <span class="title-icon">🔴</span>
                        Current Active Sessions
                        <span class="live-indicator">● LIVE</span>
                    </h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshWidget('current-sessions')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="sessions-grid">
                        @foreach($current_sessions as $session)
                        <div class="session-card active">
                            <div class="session-header">
                                <h6 class="session-title">{{ $session->activity_name }}</h6>
                                <span class="session-status ongoing">Ongoing</span>
                            </div>
                            <div class="session-details">
                                <div class="session-meta">
                                    <span class="session-time">
                                        <i class="fas fa-clock"></i> 
                                        {{ $session->start_time }} - {{ $session->end_time }}
                                    </span>
                                    <span class="session-participants">
                                        <i class="fas fa-users"></i> {{ $session->current_participants }}/{{ $session->max_participants }}
                                    </span>
                                </div>
                                <div class="session-progress">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: {{ $session->progress ?? 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Interactive Activity Timeline -->
            <!-- Debug: Check if data exists -->
            @php 
                $debug_activities = $recent_activities_centre ?? []; 
                $debug_count = count($debug_activities);
            @endphp
            
            @if($debug_count > 0)
            <div class="dashboard-widget activity-timeline-widget" id="activityTimelineWidget">
                <div class="widget-header">
                    <h5 class="widget-title">
                        <span class="title-icon">📋</span>
                        <span id="activity-title">Recent Changes</span>
                    </h5>
                    <div class="widget-filters">
                        <select class="form-control form-control-sm" onchange="filterActivities(this.value)" id="activity-filter">
                            <option value="all">All Changes</option>
                            <option value="activity">Activities</option>
                            <option value="user">Users</option>
                            <option value="trainee">Trainees</option>
                            <option value="session">Sessions</option>
                        </select>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="activity-timeline-enhanced">
                        @foreach($recent_activities_centre as $activity)
                        @php
                            $activityType = $activity['type'];
                            $activityStatus = $activity['status'];
                            $activityTitle = $activity['title'];
                            $activityTime = $activity['time'];
                            $activityIcon = $activity['icon'];
                            $activityUser = $activity['user_name'];
                        @endphp
                        <div class="timeline-item-enhanced" data-category="{{ $activityType }}" data-status="{{ strtolower($activityStatus) }}" data-id="{{ $activity['id'] ?? '' }}" data-type="{{ $activityType }}" onclick="navigateToItem(this)">
                            <div class="timeline-marker-enhanced timeline-{{ $activityType }}">
                                <i class="fas fa-{{ $activityIcon }}"></i>
                            </div>
                            <div class="timeline-content-enhanced">
                                <div class="timeline-header">
                                    <h6 class="timeline-title timeline-clickable">{{ $activityTitle }}</h6>
                                    <span class="timeline-time">{{ $activityTime }}</span>
                                </div>
                                <div class="timeline-meta">
                                    <span class="timeline-category activity-{{ $activityType }}">{{ $activity['category_name'] }}</span>
                                    @if($activityUser !== 'System')
                                        <span class="timeline-user">by {{ $activityUser }}</span>
                                    @endif
                                    <span class="timeline-status status-{{ strtolower($activityStatus) }}">{{ ucfirst($activityStatus) }}</span>
                                </div>
                                @if(isset($activity['condition']) && $activity['condition'])
                                    <div class="timeline-detail">
                                        <small class="text-muted">Condition: {{ $activity['condition'] }}</small>
                                    </div>
                                @endif
                                @if(isset($activity['session_date']) && $activity['session_date'])
                                    <div class="timeline-detail">
                                        <small class="text-muted">Date: {{ $activity['session_date'] }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <!-- Debug: Show when no recent activities data -->
            <div class="dashboard-widget">
                <div class="widget-content text-center py-4">
                    <div class="alert alert-info">
                        <h5>Debug: Recent Activities Section</h5>
                        <p>Recent activities data count: {{ $debug_count }}</p>
                        <p>Data exists: {{ isset($recent_activities_centre) ? 'Yes' : 'No' }}</p>
                        @if(isset($recent_activities_centre))
                            <p>Data type: {{ gettype($recent_activities_centre) }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if(count($recent_activities_centre ?? []) === 0 && count($todays_centre_activities ?? []) === 0)
            <div class="dashboard-widget">
                <div class="widget-content text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-calendar-day fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Centre Activities Today</h4>
                        <p class="text-muted">The centre schedule is clear for today. Check back later for updates!</p>
                        @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                        <a href="{{ route('activities.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Activity
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Weekly Calendar Widget -->
            <div class="dashboard-widget calendar-widget" id="calendarWidget">
                <div class="widget-header">
                    <h5 class="widget-title">
                        <span class="title-icon">📅</span>
                        Today's Schedule
                    </h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="showFullCalendar()">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="calendar-events-enhanced">
                        @forelse($todays_centre_activities as $event)
                        <div class="calendar-event-enhanced calendar-event-clickable" data-event-status="{{ $event['status'] }}" data-event-id="{{ $event['id'] ?? '' }}" onclick="navigateToActivity('{{ $event['id'] ?? '' }}')">
                            <div class="event-date-enhanced">
                                <span class="event-day">{{ $event['day'] }}</span>
                                <span class="event-date-num">{{ $event['date'] }}</span>
                            </div>
                            <div class="event-details-enhanced">
                                <h6 class="event-title">{{ $event['title'] }}</h6>
                                <p class="event-time">{{ $event['time'] }}@if($event['end_time']) - {{ $event['end_time'] }}@endif</p>
                                @if(isset($event['location']) && $event['location'])
                                    <p class="event-location text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $event['location'] }}</p>
                                @endif
                                @if(isset($event['teacher']) && $event['teacher'])
                                    <p class="event-teacher text-muted"><i class="fas fa-user mr-1"></i>{{ $event['teacher'] }}</p>
                                @endif
                            </div>
                            <div class="event-status-enhanced">
                                <span class="status-dot status-{{ $event['status'] }}"></span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p class="mb-2">No centre activities scheduled for today</p>
                            @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                                <a href="{{ route('activities.create') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Create New Session
                                </a>
                            @endif
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
        </div> <!-- End General Tab -->

        <!-- Personal Tab -->
        <div class="tab-pane fade" id="personal" role="tabpanel">
            
            <!-- Personal Statistics Section -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card-enhanced primary-card">
                        <div class="stats-icon-enhanced">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stats-content-enhanced">
                            <h3 class="stats-number-enhanced">{{ $personal_stats['user_activities'] }}</h3>
                            <p class="stats-label-enhanced">My {{ $role === 'trainee' ? 'Enrolled Activities' : 'Activities' }}</p>
                            <div class="stats-trend-enhanced positive">
                                <i class="fas fa-arrow-up"></i> {{ $role === 'trainee' ? 'Enrolled' : 'Active' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card-enhanced success-card">
                        <div class="stats-icon-enhanced">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stats-content-enhanced">
                            <h3 class="stats-number-enhanced">{{ $personal_stats['weekly_sessions'] }}</h3>
                            <p class="stats-label-enhanced">This Week's Sessions</p>
                            <div class="stats-trend-enhanced positive">
                                <i class="fas fa-plus"></i> Scheduled
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card-enhanced info-card">
                        <div class="stats-icon-enhanced">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stats-content-enhanced">
                            <h3 class="stats-number-enhanced">{{ $personal_stats['completion_rate'] }}%</h3>
                            <p class="stats-label-enhanced">{{ $role === 'trainee' ? 'Attendance Rate' : 'Completion Rate' }}</p>
                            <div class="stats-trend-enhanced {{ ($personal_stats['completion_rate'] ?? 0) >= 80 ? 'positive' : 'neutral' }}">
                                <i class="fas fa-{{ ($personal_stats['completion_rate'] ?? 0) >= 80 ? 'arrow-up' : 'minus' }}"></i> 
                                {{ ($personal_stats['completion_rate'] ?? 0) >= 80 ? 'Excellent' : 'Average' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card-enhanced warning-card">
                        <div class="stats-icon-enhanced">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-content-enhanced">
                            <h3 class="stats-number-enhanced">{{ $personal_stats['avg_attendance'] }}%</h3>
                            <p class="stats-label-enhanced">Average {{ $role === 'trainee' ? 'Participation' : 'Teaching' }}</p>
                            <div class="stats-trend-enhanced {{ ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'positive' : 'neutral' }}">
                                <i class="fas fa-{{ ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'star' : 'circle' }}"></i> 
                                {{ ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'Outstanding' : 'Good' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Personal Content -->
                <div class="col-12">
                    <!-- Personal Dashboard Cards -->
                    <div class="personal-dashboard-section">
                        <div class="personal-content-modern">
                            <div class="content-grid-modern">
                                
                                <!-- Recent Activities Card -->
                                <div class="content-card-modern activities-card">
                                    <div class="card-header-modern">
                                        <div class="header-icon">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <div class="header-text">
                                            <h3>Recent Activities</h3>
                                            <p>Your latest interactions</p>
                                        </div>
                                        <div class="header-action">
                                            <button class="btn-icon-modern" onclick="refreshActivities()">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-content-modern">
                                        @if(isset($recent_activities) && count($recent_activities) > 0)
                                            @foreach(array_slice((array)$recent_activities, 0, 4) as $activity)
                                            @php
                                                $activityType = $activity['type'];
                                                $activityTitle = $activity['title'];
                                                $activityTime = $activity['time'];
                                                $activityStatus = $activity['status'];
                                            @endphp
                                            <div class="activity-item-modern">
                                                <div class="activity-icon-modern activity-{{ $activityType }}">
                                                    <i class="fas fa-circle"></i>
                                                </div>
                                                <div class="activity-details-modern">
                                                    <div class="activity-title-modern">{{ $activityTitle }}</div>
                                                    <div class="activity-meta-modern">
                                                        {{ $activityTime }} • {{ ucfirst($activityType) }}
                                                    </div>
                                                </div>
                                                <div class="activity-status-modern">
                                                    <span class="status-indicator status-{{ strtolower($activityStatus) }}"></span>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="empty-state-modern">
                                                <i class="fas fa-clipboard"></i>
                                                <h4>No Recent Activities</h4>
                                                <p>Your activities will appear here once you start using the system.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- My Schedule Card -->
                                <div class="content-card-modern schedule-card">
                                    <div class="card-header-modern">
                                        <div class="header-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="header-text">
                                            <h3>My Schedule</h3>
                                            <p>Upcoming sessions & events</p>
                                        </div>
                                        <div class="header-action">
                                            <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="btn-icon-modern" title="View Full Schedule">
                                                <i class="fas fa-expand"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-content-modern">
                                        @if(isset($calendar_events) && count($calendar_events) > 0)
                                            <!-- Calendar Header -->
                                            <div class="calendar-header-modern mb-3">
                                                <div class="current-week-info">
                                                    <h5 class="text-primary mb-1">{{ now()->format('F Y') }}</h5>
                                                    <small class="text-muted">Week {{ now()->format('W') }} • {{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j') }}</small>
                                                </div>
                                            </div>

                                            <!-- Modern Schedule Timeline -->
                                            <div class="schedule-timeline-modern">
                                                @if(count($calendar_events) > 0)
                                                    @php
                                                        // Group events by date for timeline display
                                                        $eventsByDate = [];
                                                        foreach($calendar_events as $event) {
                                                            $eventDate = \Carbon\Carbon::parse($event['date'] . ' ' . $event['month'] . ' ' . now()->format('Y'));
                                                            $dateKey = $eventDate->format('Y-m-d');
                                                            if (!isset($eventsByDate[$dateKey])) {
                                                                $eventsByDate[$dateKey] = [
                                                                    'date' => $eventDate,
                                                                    'events' => []
                                                                ];
                                                            }
                                                            $eventsByDate[$dateKey]['events'][] = $event;
                                                        }
                                                        
                                                        // Sort by date
                                                        ksort($eventsByDate);
                                                    @endphp

                                                    <div class="timeline-container">
                                                        @foreach($eventsByDate as $dateKey => $dayData)
                                                            <div class="timeline-day {{ $dayData['date']->isToday() ? 'today' : '' }}">
                                                                <div class="timeline-date">
                                                                    <div class="date-badge">
                                                                        <span class="day-number">{{ $dayData['date']->format('d') }}</span>
                                                                        <span class="day-name">{{ $dayData['date']->format('D') }}</span>
                                                                        <span class="month-name">{{ $dayData['date']->format('M') }}</span>
                                                                    </div>
                                                                    @if($dayData['date']->isToday())
                                                                        <span class="today-indicator">Today</span>
                                                                    @elseif($dayData['date']->isTomorrow())
                                                                        <span class="tomorrow-indicator">Tomorrow</span>
                                                                    @endif
                                                                </div>
                                                                
                                                                <div class="timeline-events">
                                                                    @foreach($dayData['events'] as $event)
                                                                        <div class="timeline-event">
                                                                            <div class="event-time-badge">
                                                                                <i class="fas fa-clock"></i>
                                                                                {{ $event['time'] }}
                                                                            </div>
                                                                            <div class="event-content">
                                                                                <h6 class="event-title">{{ $event['title'] }}</h6>
                                                                                <div class="event-details">
                                                                                    @if(isset($event['location']) && $event['location'])
                                                                                        <span class="event-location">
                                                                                            <i class="fas fa-map-marker-alt"></i>
                                                                                            {{ $event['location'] }}
                                                                                        </span>
                                                                                    @endif
                                                                                    @if(isset($event['participants']) && $event['participants'])
                                                                                        <span class="event-participants">
                                                                                            <i class="fas fa-users"></i>
                                                                                            {{ $event['participants'] }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="event-status">
                                                                                    <span class="status-badge status-{{ $event['color'] ?? 'primary' }}">
                                                                                        {{ ucfirst($event['status'] ?? 'scheduled') }}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="schedule-empty-state">
                                                        <div class="empty-illustration">
                                                            <i class="fas fa-calendar-day"></i>
                                                        </div>
                                                        <h5>No Upcoming Sessions</h5>
                                                        <p>Your schedule is clear for the next week. Enjoy your free time!</p>
                                                        <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="btn btn-outline-primary">
                                                            <i class="fas fa-calendar-plus"></i>
                                                            View Full Calendar
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>

                                    </div>
                                </div>

                                <!-- Personal Performance Card -->
                                <div class="content-card-modern performance-card">
                                    <div class="card-header-modern">
                                        <div class="header-icon">
                                            <i class="fas fa-chart-bar"></i>
                                        </div>
                                        <div class="header-text">
                                            <h3>My Performance</h3>
                                            <p>Your activity statistics</p>
                                        </div>
                                    </div>
                                    <div class="card-content-modern">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="performance-metric">
                                                    <div class="metric-number">{{ $personal_stats['user_activities'] }}</div>
                                                    <div class="metric-label">My Activities</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="performance-metric">
                                                    <div class="metric-number">{{ $personal_stats['completion_rate'] }}%</div>
                                                    <div class="metric-label">Completion Rate</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="performance-metric">
                                                    <div class="metric-number">{{ $personal_stats['weekly_sessions'] }}</div>
                                                    <div class="metric-label">This Week</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="performance-metric">
                                                    <div class="metric-number">{{ $personal_stats['avg_attendance'] }}%</div>
                                                    <div class="metric-label">Attendance</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions Card -->
                                <div class="content-card-modern actions-card">
                                    <div class="card-header-modern">
                                        <div class="header-icon">
                                            <i class="fas fa-bolt"></i>
                                        </div>
                                        <div class="header-text">
                                            <h3>Quick Actions</h3>
                                            <p>Frequently used features</p>
                                        </div>
                                    </div>
                                    <div class="card-content-modern">
                                        <div class="quick-actions-grid">
                                            @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                                            <a href="{{ route('activities.create') }}" class="quick-action-btn">
                                                <i class="fas fa-plus"></i>
                                                <span>Create Activity</span>
                                            </a>
                                            <a href="{{ route('activities.home') }}" class="quick-action-btn">
                                                <i class="fas fa-clipboard-check"></i>
                                                <span>Mark Attendance</span>
                                            </a>
                                            @endif
                                            <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="quick-action-btn">
                                                <i class="fas fa-calendar"></i>
                                                <span>View Schedule</span>
                                            </a>
                                            <a href="{{ route('profile.home') }}" class="quick-action-btn">
                                                <i class="fas fa-user-cog"></i>
                                                <span>Profile Settings</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End Personal Tab -->
    </div> <!-- End Tab Content -->

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-nav-bar d-lg-none">
        <div class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </div>
        <div class="mobile-nav-item">
            <i class="fas fa-calendar"></i>
            <span>Schedule</span>
        </div>
        <div class="mobile-nav-item">
            <i class="fas fa-users"></i>
            <span>Activities</span>
        </div>
        <div class="mobile-nav-item">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </div>
        <div class="mobile-nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </div>
    </div>
</div>

<style>
/* Enhanced Dashboard Styles */
.enhanced-dashboard {
    background: #f8f9fc;
    min-height: 100vh;
    padding: 0;
}

/* Dashboard Tabs Styling */
.dashboard-tabs-container {
    margin-bottom: 2rem;
}

.dashboard-nav-pills {
    background-color: #f8f9fa;
    border-radius: 12px;
    padding: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    justify-content: center;
    border: none;
}

.dashboard-nav-pills .nav-link {
    color: #6c757d;
    background: transparent;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 500;
    transition: all 0.3s ease;
    margin: 0 4px;
    position: relative;
}

.dashboard-nav-pills .nav-link:hover {
    color: #495057;
    background-color: #e9ecef;
    transform: translateY(-1px);
}

.dashboard-nav-pills .nav-link.active {
    background: linear-gradient(135deg, #c850c0 0%, #32bdea 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(200, 80, 192, 0.4);
}

.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Personal Tab Specific Styles */
.performance-metric {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.performance-metric:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.performance-metric .metric-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #c850c0;
    margin-bottom: 0.25rem;
}

.performance-metric .metric-label {
    font-size: 0.875rem;
    color: #718096;
    font-weight: 500;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 1rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.quick-action-btn:hover {
    color: #c850c0;
    border-color: #c850c0;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(200, 80, 192, 0.2);
    text-decoration: none;
}

.quick-action-btn i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-dot-modern {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.status-dot-modern.status-primary { background: #c850c0; }
.status-dot-modern.status-success { background: #28a745; }
.status-dot-modern.status-warning { background: #ffc107; }
.status-dot-modern.status-info { background: #17a2b8; }

.btn-icon-modern {
    background: none;
    border: none;
    color: #718096;
    font-size: 1rem;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.btn-icon-modern:hover {
    background: #f1f3f4;
    color: #c850c0;
}

.status-indicator {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
}

.status-indicator.status-active { background: #28a745; }
.status-indicator.status-completed { background: #17a2b8; }
.status-indicator.status-pending { background: #ffc107; }
.status-indicator.status-cancelled { background: #dc3545; }

/* Enhanced Header */
.dashboard-header-enhanced {
    background: linear-gradient(135deg, #c850c0 0%, #32bdea 100%);
    color: white;
    padding: 2rem 0;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
}

.header-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.header-content {
    position: relative;
    z-index: 2;
    padding: 0 2rem;
}

.header-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='7' cy='7' r='3'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    z-index: 1;
}

/* Enhanced Greeting Section */
.user-greeting {
    margin-bottom: 1rem;
}

.greeting-badge {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 0.5rem;
}

.greeting-time {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.user-role {
    background: rgba(255, 255, 255, 0.15);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.user-name-highlight {
    background: linear-gradient(45deg, #fff, #f0f8ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Enhanced Subtitle */
.dashboard-subtitle-enhanced {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    line-height: 1.4;
}

.subtitle-primary {
    font-size: 1.1rem;
    font-weight: 500;
    opacity: 0.95;
}

.subtitle-secondary {
    font-size: 0.95rem;
    opacity: 0.8;
}

.subtitle-highlight {
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    display: inline-block;
    margin-top: 0.25rem;
    font-weight: 500;
}

/* Mini Dashboard Stats */
.dashboard-stats-mini {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    justify-content: flex-end;
}

.stat-mini {
    background: rgba(255, 255, 255, 0.15);
    padding: 0.75rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
    min-width: 70px;
    transition: all 0.3s ease;
}

.stat-mini:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.stat-mini .stat-icon {
    font-size: 1.25rem;
    margin-bottom: 0.25rem;
    opacity: 0.9;
}

.stat-mini .stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.125rem;
}

.stat-mini .stat-label {
    font-size: 0.7rem;
    opacity: 0.8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dashboard-title-enhanced {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.title-icon {
    margin-right: 1rem;
    animation: wave 2s ease-in-out infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-20deg); }
}

.dashboard-subtitle-enhanced {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.highlight-text {
    background: rgba(255,255,255,0.2);
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
}

/* Enhanced Stats Cards */
.stats-card-enhanced {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    border-left: 4px solid #c850c0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card-enhanced:hover {
    transform: translateY(-0.25rem);
    box-shadow: 0 1rem 2rem rgba(0,0,0,0.15);
}

.stats-card-enhanced.primary-card { border-left-color: #c850c0; }
.stats-card-enhanced.success-card { border-left-color: #28a745; }
.stats-card-enhanced.warning-card { border-left-color: #ffc107; }
.stats-card-enhanced.info-card { border-left-color: #17a2b8; }

.stats-icon-enhanced {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #c850c0, #32bdea);
}

.stats-number-enhanced {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stats-label-enhanced {
    color: #718096;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.stats-trend-enhanced {
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.stats-trend-enhanced.positive { color: #28a745; }
.stats-trend-enhanced.stable { color: #6c757d; }

/* Dashboard Widgets */
.dashboard-widget {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.widget-header {
    background: linear-gradient(135deg, #f8f9fc, #e9ecef);
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.widget-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.live-indicator {
    background: #dc3545;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.widget-content {
    padding: 1.25rem;
}

/* Session Cards */
.sessions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.session-card {
    background: #f8f9fc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.session-card:hover {
    background: white;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.session-title {
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    font-size: 1rem;
}

.session-status {
    padding: 0.25rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.session-status.ongoing {
    background: #fed7d7;
    color: #c53030;
}

.session-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #718096;
}

/* Activity Timeline */
.activity-timeline-enhanced {
    position: relative;
}

.timeline-item-enhanced {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f3f4;
}

/* Timeline Markers for Different Types */
.timeline-marker-enhanced.timeline-activity {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    border-color: #4f46e5;
}

.timeline-marker-enhanced.timeline-user {
    background: linear-gradient(135deg, #059669, #10b981);
    border-color: #059669;
}

.timeline-marker-enhanced.timeline-trainee {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    border-color: #dc2626;
}

.timeline-marker-enhanced.timeline-session {
    background: linear-gradient(135deg, #d97706, #f59e0b);
    border-color: #d97706;
}

.timeline-marker-enhanced.timeline-general {
    background: linear-gradient(135deg, #6b7280, #9ca3af);
    border-color: #6b7280;
}

/* Timeline Meta Styling */
.timeline-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.timeline-category.activity-activity {
    background: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
}

.timeline-category.activity-user {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.timeline-category.activity-trainee {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.timeline-category.activity-session {
    background: rgba(217, 119, 6, 0.1);
    color: #d97706;
}

.timeline-user {
    font-size: 0.75rem;
    color: #6b7280;
    font-style: italic;
}

.timeline-detail {
    margin-top: 0.25rem;
}

/* Widget Filters Styling */
.widget-filters {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.widget-filters select {
    min-width: 120px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background-color: white;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.widget-filters select:focus {
    outline: none;
    border-color: #c850c0;
    box-shadow: 0 0 0 3px rgba(200, 80, 192, 0.1);
}

/* Clickable Timeline Items */
.timeline-item-enhanced {
    cursor: pointer;
    transition: all 0.3s ease;
}

.timeline-item-enhanced:hover {
    background-color: #f8f9fa;
    border-radius: 8px;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-clickable {
    color: #c850c0;
    text-decoration: none;
}

.timeline-clickable:hover {
    color: #a043a0;
    text-decoration: underline;
}

/* Clickable Calendar Events */
.calendar-event-clickable {
    cursor: pointer;
    transition: all 0.3s ease;
}

.calendar-event-clickable:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.event-teacher {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.timeline-item-enhanced:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.timeline-marker-enhanced {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #c850c0, #32bdea);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content-enhanced {
    flex: 1;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.timeline-title {
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    font-size: 0.9rem;
}

.timeline-time {
    font-size: 0.75rem;
    color: #718096;
}

.timeline-meta {
    display: flex;
    gap: 0.75rem;
    font-size: 0.75rem;
}

.timeline-category {
    color: #718096;
    text-transform: uppercase;
    font-weight: 600;
}

.timeline-status {
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #c6f6d5;
    color: #276749;
}

/* Calendar Events */
.calendar-events-enhanced {
    max-height: 400px;
    overflow-y: auto;
}

.calendar-event-enhanced {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    background: #f8f9fc;
    transition: all 0.3s ease;
}

.calendar-event-enhanced:hover {
    background: white;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.1);
}

.event-date-enhanced {
    text-align: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.event-day {
    display: block;
    font-size: 0.75rem;
    color: #718096;
    text-transform: uppercase;
    font-weight: 600;
}

.event-date-num {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: #2d3748;
}

.event-details-enhanced {
    flex: 1;
}

.event-title {
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
}

.event-time {
    font-size: 0.875rem;
    color: #718096;
    margin: 0;
}

.status-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background: #c850c0;
}

.status-primary { background: #c850c0; }
.status-success { background: #28a745; }
.status-warning { background: #ffc107; }
.status-info { background: #17a2b8; }

/* Personal Dashboard Styles */
.personal-content-modern {
    margin-top: 1.5rem;
}

.content-card-modern {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.card-header-modern {
    background: linear-gradient(135deg, #f8f9fc, #e9ecef);
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #c850c0, #32bdea);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.header-text h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
}

.header-text p {
    margin: 0;
    font-size: 0.875rem;
    color: #718096;
}

.card-content-modern {
    padding: 1.25rem;
}

.activity-item-modern {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.activity-item-modern:last-child {
    border-bottom: none;
}

.activity-icon-modern {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #c850c0, #32bdea);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    margin-right: 0.75rem;
}

.activity-details-modern {
    flex: 1;
}

.activity-title-modern {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 0.125rem;
}

.activity-meta-modern {
    font-size: 0.75rem;
    color: #718096;
}

.schedule-item-modern {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.schedule-item-modern:last-child {
    border-bottom: none;
}

.schedule-date-modern {
    text-align: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.date-day {
    font-size: 0.75rem;
    color: #718096;
    text-transform: uppercase;
    font-weight: 600;
}

.date-num {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2d3748;
}

.schedule-details-modern {
    flex: 1;
}

.schedule-title-modern {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.schedule-time-modern {
    font-size: 0.75rem;
    color: #718096;
    margin-bottom: 0.125rem;
}

.schedule-location-modern {
    font-size: 0.75rem;
    color: #718096;
}

.empty-state-modern {
    text-align: center;
    padding: 2rem 1rem;
    color: #718096;
}

.empty-state-modern i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state-modern h4 {
    margin-bottom: 0.5rem;
    color: #4a5568;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

/* Mobile Navigation */
.mobile-nav-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-around;
    padding: 0.75rem 0;
    z-index: 1000;
    box-shadow: 0 -0.25rem 0.5rem rgba(0,0,0,0.1);
}

.mobile-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.5rem;
    color: #718096;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    min-width: 4rem;
}

.mobile-nav-item.active {
    color: #c850c0;
    background: rgba(200, 80, 192, 0.1);
}

.mobile-nav-item i {
    font-size: 1.25rem;
    margin-bottom: 0.25rem;
}

.mobile-nav-item span {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Performance Indicator */
.performance-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.performance-circle {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255,255,255,0.3);
}

.performance-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}


.tab-navigation-enhanced {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 0.5rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.3);
}

.dashboard-nav-pills .nav-link {
    padding: 0.75rem 1.5rem;
    margin: 0 0.25rem;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    background: transparent;
    color: #6c757d;
}

.dashboard-nav-pills .nav-link.active {
    background: linear-gradient(135deg, #32bdea, #c850c0);
    color: white;
    box-shadow: 0 4px 15px rgba(50, 189, 234, 0.3);
}

.dashboard-nav-pills .nav-link:hover:not(.active) {
    background: rgba(50, 189, 234, 0.1);
    color: #32bdea;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        padding: 0 1rem;
    }
    
    .dashboard-title-enhanced {
        font-size: 2rem;
    }
    
    .sessions-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-card-enhanced {
        padding: 1rem;
    }
    
    .stats-number-enhanced {
        font-size: 1.5rem;
    }
    
    .mobile-nav-bar {
        display: flex;
    }
    
    .enhanced-dashboard {
        padding-bottom: 5rem;
    }
}

@media (min-width: 769px) {
    .mobile-nav-bar {
        display: none;
    }
}
</style>

<script>
// Dashboard functionality
function refreshWidget(widgetName) {
    console.log('Refreshing widget:', widgetName);
    // Add refresh logic here
}

function filterActivities(category) {
    const items = document.querySelectorAll('.timeline-item-enhanced');
    const titleElement = document.getElementById('activity-title');
    
    // Update title based on filter
    const titles = {
        'all': 'Recent Changes',
        'activity': 'Recent Centre Activities',
        'user': 'Recent Users Updates', 
        'trainee': 'Recent Trainees Updates',
        'session': 'Recent Sessions Updates'
    };
    
    if (titleElement) {
        titleElement.textContent = titles[category] || 'Recent Changes';
    }
    
    // Filter items
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function navigateToItem(element) {
    const itemType = element.dataset.type;
    const itemId = element.dataset.id;
    
    if (!itemId) return;
    
    let url = '';
    switch (itemType) {
        case 'activity':
            url = `{{ route('activities.show', '') }}/${itemId}`;
            break;
        case 'user':
            url = `{{ route('staffs.profile', '') }}/${itemId}`;
            break;
        case 'trainee':
            url = `{{ route('traineeprofile', '') }}/${itemId}`;
            break;
        case 'session':
            url = `{{ route('activities.sessions', '') }}/${itemId}`;
            break;
    }
    
    if (url) {
        window.location.href = url;
    }
}

function showFullCalendar() {
    window.location.href = "{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}";
}

function navigateToActivity(sessionId) {
    if (sessionId) {
        // Navigate to the attendance marking for the specific session
        window.location.href = `/enhanced-attendance/session/${sessionId}/form`;
    }
}

function refreshActivities() {
    // Add refresh logic for activities
    console.log('Refreshing activities...');
}

// Auto-refresh every 5 minutes
setInterval(() => {
    console.log('Auto-refreshing dashboard...');
    // Add auto-refresh logic
}, 300000);
</script>
@endsection