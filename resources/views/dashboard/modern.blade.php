@extends('layouts.app')

@section('title', 'Dashboard - CREAMS')

@section('content')
<div class="enhanced-dashboard">
    <!-- Role-based Access Denied Messages -->
    @include('components.role-access-denied')

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
                            @if(isset($todays_centre_activities) && count($todays_centre_activities) > 0)
                                @php
                                    $centreName = session('centre_name', 'your centre');
                                    $userSessions = isset($my_schedule) ? count(array_filter($my_schedule, function($session) {
                                        return isset($session['date']) && date('Y-m-d', strtotime($session['date'])) === date('Y-m-d');
                                    })) : 0;
                                @endphp
                                @if($role === 'admin')
                                    <span class="subtitle-highlight">• {{ $centreName }} has {{ count($todays_centre_activities) }} activity {{ count($todays_centre_activities) == 1 ? 'session' : 'sessions' }} scheduled today</span>
                                @else
                                    <span class="subtitle-highlight">• {{ $centreName }} has {{ count($todays_centre_activities) }} {{ count($todays_centre_activities) == 1 ? 'session' : 'sessions' }} scheduled today</span>
                                @endif
                                @if($userSessions > 0)
                                    <span class="subtitle-highlight">• You have {{ $userSessions }} {{ $userSessions == 1 ? 'session' : 'sessions' }} to conduct today</span>
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 text-end">
                    <div class="header-actions">
                        <div class="dashboard-datetime">
                            <div class="datetime-display">
                                <div class="date-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span id="currentDate">{{ now()->format('l, F j, Y') }}</span>
                                </div>
                                <div class="time-info">
                                    <i class="fas fa-clock"></i>
                                    <span id="currentTime">{{ now()->format('g:i A') }}</span>
                                </div>
                                <div class="weather-info" id="weatherWidget">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Loading weather...</span>
                                </div>
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
                @if($role === 'admin')
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-bs-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">
                        <i class="fas fa-chart-line me-2"></i>General
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ $role !== 'admin' ? 'active' : '' }}" id="personal-tab" data-bs-toggle="pill" href="#personal" role="tab" aria-controls="personal" aria-selected="{{ $role !== 'admin' ? 'true' : 'false' }}">
                        <i class="fas fa-user me-2"></i>Personal
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="dashboardTabContent">
        @if($role === 'admin')
        <!-- General Tab -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">

    <!-- Enhanced Quick Stats with Drill-down -->
    <div class="row g-4 mb-4">
        @if(in_array($role, ['admin', 'supervisor']))
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card-enhanced primary-card">
                <div class="stats-icon-enhanced">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stats-content-enhanced">
                    <h3 class="stats-number-enhanced">{{ $stats_flat['total_users'] ?? 0 }}</h3>
                    <p class="stats-label-enhanced">Active Staff</p>
                    <div class="stats-trend-enhanced positive">
                        <i class="fas fa-check-circle"></i> In Service
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
                    <p class="stats-label-enhanced">Ongoing Activities</p>
                    <div class="stats-trend-enhanced positive">
                        <i class="fas fa-arrow-up"></i> Running
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card-enhanced info-card">
                <div class="stats-icon-enhanced">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stats-content-enhanced">
                    <h3 class="stats-number-enhanced">{{ $stats_flat['sessions_this_week'] ?? 0 }}</h3>
                    <p class="stats-label-enhanced">Sessions This Week</p>
                    <div class="stats-trend-enhanced {{ ($stats_flat['sessions_this_week'] ?? 0) > 0 ? 'positive' : 'stable' }}">
                        <i class="fas {{ ($stats_flat['sessions_this_week'] ?? 0) > 0 ? 'fa-arrow-up' : 'fa-minus' }}"></i> {{ ($stats_flat['sessions_this_week'] ?? 0) > 0 ? 'Active' : 'None' }}
                    </div>
                </div>
            </div>
        </div>
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
            @if(isset($recent_activities_centre) && count($recent_activities_centre) > 0)
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
                            $activityRole = $activity['user_role'] ?? 'system';
                            $activityAction = $activity['action'] ?? 'action';
                            $activityDescription = $activity['description'] ?? '';

                            // Role color mapping
                            $roleColors = [
                                'admin' => '#dc3545',
                                'supervisor' => '#fd7e14',
                                'teacher' => '#0d6efd',
                                'ajk' => '#6f42c1',
                                'parent' => '#20c997',
                                'system' => '#6c757d'
                            ];
                            $roleColor = $roleColors[$activityRole] ?? '#6c757d';

                            // Action result mapping
                            $actionLabels = [
                                'created' => 'Created',
                                'updated' => 'Updated',
                                'deleted' => 'Deleted'
                            ];
                            $actionLabel = $actionLabels[$activityAction] ?? ucfirst($activityAction);
                        @endphp
                        <div class="timeline-item-enhanced" data-category="{{ $activityType }}" data-status="{{ strtolower($activityStatus) }}" data-id="{{ $activity['id'] ?? '' }}" data-type="{{ $activityType }}">
                            <div class="timeline-marker-enhanced timeline-{{ $activityType }}">
                                <i class="fas fa-{{ $activityIcon }}"></i>
                            </div>
                            <div class="timeline-content-enhanced">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">{{ $activityTitle }}</h6>
                                    <span class="timeline-time">{{ $activityTime }}</span>
                                </div>
                                @if($activityDescription)
                                    <div class="timeline-description">
                                        <small class="text-muted">{{ $activityDescription }}</small>
                                    </div>
                                @endif
                                <div class="timeline-meta">
                                    <span class="timeline-category activity-{{ strtolower($activityType) }}">
                                        {{ $activity['category_name'] }}
                                    </span>
                                    <span class="timeline-action action-{{ strtolower($activityAction) }}">
                                        {{ $actionLabel }}
                                    </span>
                                    <span class="timeline-status status-{{ strtolower($activityStatus) }}">
                                        {{ ucfirst($activityStatus) }}
                                    </span>
                                    @if($activityUser !== 'System')
                                        <span class="timeline-user" style="background-color: {{ $roleColor }}15; color: {{ $roleColor }}; border: 1px solid {{ $roleColor }}40;">
                                            <i class="fas fa-user-circle"></i> {{ $activityUser }}
                                            <span style="font-size: 0.75em; opacity: 0.8;">({{ ucfirst($activityRole) }})</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <!-- No Recent Activities -->
            <div class="dashboard-widget">
                <div class="widget-content text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No recent activities to display</p>
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
        @endif

        <!-- Personal Tab -->
        <div class="tab-pane fade {{ $role !== 'admin' ? 'show active' : '' }}" id="personal" role="tabpanel">

            <!-- Data Source Indicator -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="data-source-indicator">
                        <div class="data-source-content">
                            <i class="fas fa-database"></i>
                            <span class="data-source-text">
                                <strong>Live Data:</strong>
                                Statistics updated from database records •
                                Centre: {{ session('centre_name', 'Your Centre') }} •
                                Last updated: {{ now()->format('M j, Y \a\t g:i A') }}
                            </span>
                            <button class="data-refresh-btn" onclick="refreshPersonalStats()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Statistics Section - DATA-BASED -->
            <div class="row g-4 mb-4">
                @php
                    // Dynamic stat card configurations based on role
                    if ($role === 'trainee') {
                        $statCards = [
                            [
                                'icon' => 'fas fa-graduation-cap',
                                'value' => $personal_stats['user_activities'] ?? 0,
                                'label' => 'Enrolled Activities',
                                'sublabel' => 'Currently participating in',
                                'trend_icon' => 'fas fa-book-open',
                                'trend_text' => $personal_stats['user_activities'] > 0 ? 'Active Learner' : 'Ready to Start',
                                'color' => 'primary'
                            ],
                            [
                                'icon' => 'fas fa-calendar-week',
                                'value' => $personal_stats['weekly_sessions'] ?? 0,
                                'label' => 'This Week\'s Sessions',
                                'sublabel' => 'Scheduled activities',
                                'trend_icon' => 'fas fa-clock',
                                'trend_text' => $personal_stats['weekly_sessions'] > 0 ? 'Busy Week' : 'Light Schedule',
                                'color' => 'success'
                            ],
                            [
                                'icon' => 'fas fa-chart-line',
                                'value' => ($personal_stats['completion_rate'] ?? 0) . '%',
                                'label' => 'Activity Completion',
                                'sublabel' => 'Progress in enrolled activities',
                                'trend_icon' => ($personal_stats['completion_rate'] ?? 0) >= 70 ? 'fas fa-trophy' : 'fas fa-target',
                                'trend_text' => ($personal_stats['completion_rate'] ?? 0) >= 70 ? 'Great Progress' : 'Building Up',
                                'color' => 'info'
                            ],
                            [
                                'icon' => 'fas fa-user-check',
                                'value' => ($personal_stats['avg_attendance'] ?? 0) . '%',
                                'label' => 'Attendance Rate',
                                'sublabel' => 'Present in scheduled sessions',
                                'trend_icon' => ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'fas fa-star' : 'fas fa-thumbs-up',
                                'trend_text' => ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'Excellent' : 'Good',
                                'color' => 'warning'
                            ]
                        ];
                    } elseif ($role === 'ajk') {
                        $statCards = [
                            [
                                'icon' => 'fas fa-tools',
                                'value' => $personal_stats['user_activities'] ?? 0,
                                'label' => 'Facilities Managed',
                                'sublabel' => 'Assets under your care',
                                'trend_icon' => 'fas fa-building',
                                'trend_text' => $personal_stats['user_activities'] > 0 ? 'Facility Manager' : 'Ready to Manage',
                                'color' => 'primary'
                            ],
                            [
                                'icon' => 'fas fa-tasks',
                                'value' => $personal_stats['weekly_sessions'] ?? 0,
                                'label' => 'This Week\'s Tasks',
                                'sublabel' => 'Maintenance & facility tasks',
                                'trend_icon' => 'fas fa-wrench',
                                'trend_text' => $personal_stats['weekly_sessions'] > 0 ? 'Active Tasks' : 'All Clear',
                                'color' => 'success'
                            ],
                            [
                                'icon' => 'fas fa-percentage',
                                'value' => ($personal_stats['completion_rate'] ?? 0) . '%',
                                'label' => 'Task Completion',
                                'sublabel' => 'Facility management efficiency',
                                'trend_icon' => ($personal_stats['completion_rate'] ?? 0) >= 80 ? 'fas fa-check-circle' : 'fas fa-clock',
                                'trend_text' => ($personal_stats['completion_rate'] ?? 0) >= 80 ? 'Efficient' : 'In Progress',
                                'color' => 'info'
                            ],
                            [
                                'icon' => 'fas fa-clipboard-check',
                                'value' => $personal_stats['avg_attendance'] ?? 0,
                                'label' => 'Tasks Completed',
                                'sublabel' => 'Maintenance tasks finished',
                                'trend_icon' => 'fas fa-medal',
                                'trend_text' => $personal_stats['avg_attendance'] > 10 ? 'Productive' : 'Getting Started',
                                'color' => 'warning'
                            ]
                        ];
                    } else {
                        // For teacher, admin, supervisor
                        $statCards = [
                            [
                                'icon' => 'fas fa-chalkboard-teacher',
                                'value' => $personal_stats['user_activities'] ?? 0,
                                'label' => 'My Activities',
                                'sublabel' => 'Activities I created/teach',
                                'trend_icon' => 'fas fa-lightbulb',
                                'trend_text' => $personal_stats['user_activities'] > 0 ? 'Active Educator' : 'Ready to Create',
                                'color' => 'info'
                            ],
                            [
                                'icon' => 'fas fa-calendar-day',
                                'value' => $personal_stats['weekly_sessions'] ?? 0,
                                'label' => 'This Week\'s Sessions',
                                'sublabel' => 'Sessions I\'m conducting',
                                'trend_icon' => 'fas fa-users',
                                'trend_text' => $personal_stats['weekly_sessions'] > 0 ? 'Teaching Week' : 'Planning Time',
                                'color' => 'success'
                            ],
                            [
                                'icon' => 'fas fa-chart-bar',
                                'value' => ($personal_stats['completion_rate'] ?? 0) . '%',
                                'label' => 'Session Completion',
                                'sublabel' => 'Scheduled sessions conducted',
                                'trend_icon' => ($personal_stats['completion_rate'] ?? 0) >= 85 ? 'fas fa-award' : 'fas fa-chart-line',
                                'trend_text' => ($personal_stats['completion_rate'] ?? 0) >= 85 ? 'Excellent' : 'Good Progress',
                                'color' => 'primary'
                            ],
                            [
                                'icon' => 'fas fa-user-graduate',
                                'value' => ($personal_stats['avg_attendance'] ?? 0) . '%',
                                'label' => 'Student Attendance',
                                'sublabel' => 'Avg attendance in my sessions',
                                'trend_icon' => ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'fas fa-star' : 'fas fa-thumbs-up',
                                'trend_text' => ($personal_stats['avg_attendance'] ?? 0) >= 90 ? 'Outstanding' : 'Good Engagement',
                                'color' => 'warning'
                            ]
                        ];
                    }
                @endphp

                @foreach($statCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card-enhanced {{ $card['color'] }}-card" data-stat-type="{{ strtolower(str_replace(' ', '-', $card['label'])) }}">
                        <div class="stats-icon-enhanced">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="stats-content-enhanced">
                            <h3 class="stats-number-enhanced" id="stat-{{ $index }}">{{ $card['value'] }}</h3>
                            <p class="stats-label-enhanced">{{ $card['label'] }}</p>
                            <small class="stats-sublabel">{{ $card['sublabel'] }}</small>
                            <div class="stats-trend-enhanced positive">
                                <i class="{{ $card['trend_icon'] }}"></i> {{ $card['trend_text'] }}
                            </div>
                        </div>
                        <div class="stats-overlay">
                            <div class="stats-detail-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
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

                                <!-- My Schedule Card - REDESIGNED -->
                                <div class="content-card-modern schedule-card-redesigned">
                                    <div class="card-header-redesigned">
                                        <div class="header-content-flex">
                                            <div class="header-left">
                                                <div class="header-icon-redesigned">
                                                    <i class="fas fa-calendar-week"></i>
                                                </div>
                                                <div class="header-text-redesigned">
                                                    <h3 class="card-title-redesigned">My Schedule</h3>
                                                    <p class="card-subtitle-redesigned">Today • {{ now()->format('l, M j, Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="header-actions-redesigned">
                                                <button class="action-btn today-btn" onclick="scrollToToday()">
                                                    <i class="fas fa-calendar-day"></i>
                                                    Today
                                                </button>
                                                <button class="action-btn attendance-btn" onclick="markAttendance()" id="attendanceBtn">
                                                    <i class="fas fa-user-check"></i>
                                                    Mark Attendance
                                                </button>
                                                <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="action-btn view-all-btn">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    View All
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-content-redesigned">
                                        @if(isset($calendar_data) && isset($calendar_data['events']) && count($calendar_data['events']) > 0)
                                            <!-- Week Navigation -->
                                            <div class="week-navigation">
                                                <button class="nav-btn prev-week" onclick="changeWeek(-1)">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <div class="current-week-display">
                                                    <span class="week-text">Week {{ $calendar_data['week_start']->format('W') ?? now()->format('W') }}</span>
                                                    <span class="week-range">{{ ($calendar_data['week_start']->format('M j') ?? now()->startOfWeek()->format('M j')) }} - {{ ($calendar_data['week_end']->format('M j') ?? now()->endOfWeek()->format('M j')) }}</span>
                                                </div>
                                                <button class="nav-btn next-week" onclick="changeWeek(1)">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </div>

                                            <!-- Schedule Grid Layout -->
                                            <div class="schedule-grid-container">
                                                @php
                                                    // Group events by date for grid display
                                                    $eventsByDate = [];
                                                    $weekDays = [];
                                                    
                                                    // Use the week from calendar_data or default to current week
                                                    $weekStart = $calendar_data['week_start'] ?? now()->startOfWeek();
                                                    
                                                    // Generate week days
                                                    for ($i = 0; $i < 7; $i++) {
                                                        $date = $weekStart->copy()->addDays($i);
                                                        $dateKey = $date->format('Y-m-d');
                                                        $weekDays[$dateKey] = [
                                                            'date' => $date,
                                                            'events' => []
                                                        ];
                                                    }
                                                    
                                                    // Add events to respective days using proper full_date
                                                    foreach($calendar_data['events'] as $event) {
                                                        $dateKey = $event['full_date'] ?? null;
                                                        if ($dateKey && isset($weekDays[$dateKey])) {
                                                            $weekDays[$dateKey]['events'][] = $event;
                                                        }
                                                    }
                                                @endphp

                                                <div class="schedule-week-grid">
                                                    @foreach($weekDays as $dateKey => $dayData)
                                                        @php
                                                            $dayIsHolidayCheck = isset($calendar_data['holidays'][$dateKey]);
                                                        @endphp
                                                        <div class="day-column {{ $dayData['date']->isToday() ? 'today-column' : '' }} {{ count($dayData['events']) > 0 ? 'has-events' : '' }} {{ $dayIsHolidayCheck ? 'holiday-column' : '' }}">
                                                            <!-- Day Header -->
                                                            <div class="day-header">
                                                                <div class="day-info">
                                                                    <span class="day-name">{{ $dayData['date']->format('D') }}</span>
                                                                    <span class="day-number {{ $dayData['date']->isToday() ? 'today-number' : '' }}">
                                                                        {{ $dayData['date']->format('j') }}
                                                                    </span>
                                                                </div>
                                                                @php
                                                                    $dayIsHoliday = isset($calendar_data['holidays'][$dateKey]) ? $calendar_data['holidays'][$dateKey] : null;
                                                                @endphp
                                                                @if($dayIsHoliday)
                                                                    <div class="holiday-badge" title="{{ $dayIsHoliday->name }}">
                                                                        <i class="fas fa-umbrella-beach"></i> Holiday
                                                                    </div>
                                                                @elseif($dayData['date']->isToday())
                                                                    <div class="today-badge">Today</div>
                                                                @endif
                                                            </div>

                                                            <!-- Day Events -->
                                                            <div class="day-events">
                                                                @php
                                                                    $isHoliday = isset($calendar_data['holidays'][$dateKey]) ? $calendar_data['holidays'][$dateKey] : null;
                                                                @endphp

                                                                @if(count($dayData['events']) > 0)
                                                                    @foreach(array_slice($dayData['events'], 0, 3) as $event)
                                                                        <div class="event-card event-{{ $event['color'] ?? 'primary' }} clickable-event"
                                                                             onclick="navigateToSession('{{ $event['activity_id'] ?? '' }}', '{{ $event['session_id'] ?? $event['id'] ?? '' }}')"
                                                                             style="cursor: pointer;"
                                                                             title="Click to view session details">
                                                                            <div class="event-time">{{ $event['time'] }}</div>
                                                                            <div class="event-title-short">{{ Str::limit($event['title'], 20) }}</div>
                                                                            @if(isset($event['location']) && $event['location'])
                                                                                <div class="event-location-short">
                                                                                    <i class="fas fa-map-pin"></i>
                                                                                    {{ Str::limit($event['location'], 15) }}
                                                                                </div>
                                                                            @endif
                                                                            <div class="click-indicator">
                                                                                <i class="fas fa-external-link-alt"></i>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                    @if(count($dayData['events']) > 3)
                                                                        <div class="more-events-indicator">
                                                                            +{{ count($dayData['events']) - 3 }} more
                                                                        </div>
                                                                    @endif
                                                                @elseif($isHoliday)
                                                                    <div class="holiday-indicator">
                                                                        <i class="fas fa-umbrella-beach"></i>
                                                                        <span class="holiday-name">{{ $isHoliday->name }}</span>
                                                                        @if($isHoliday->type === 'state')
                                                                            <span class="holiday-type">{{ $isHoliday->state }}</span>
                                                                        @else
                                                                            <span class="holiday-type">Public Holiday</span>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <div class="no-events">
                                                                        <i class="fas fa-coffee"></i>
                                                                        <span>Free Day</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Today's Agenda (if today has events) -->
                                            @if(isset($weekDays[now()->format('Y-m-d')]) && count($weekDays[now()->format('Y-m-d')]['events']) > 0)
                                                <div class="today-agenda">
                                                    <div class="agenda-header">
                                                        <h4>
                                                            <i class="fas fa-star"></i>
                                                            Today's Agenda
                                                        </h4>
                                                    </div>
                                                    <div class="agenda-events">
                                                        @foreach($weekDays[now()->format('Y-m-d')]['events'] as $event)
                                                            <div class="agenda-event clickable-agenda" 
                                                                 onclick="navigateToSession('{{ $event['activity_id'] ?? '' }}', '{{ $event['session_id'] ?? $event['id'] ?? '' }}')"
                                                                 style="cursor: pointer;"
                                                                 title="Click to view session details">
                                                                <div class="agenda-time">
                                                                    <span class="time-badge">{{ $event['time'] }}</span>
                                                                </div>
                                                                <div class="agenda-content">
                                                                    <h5 class="agenda-title">{{ $event['title'] }}</h5>
                                                                    <div class="agenda-details">
                                                                        @if(isset($event['location']) && $event['location'])
                                                                            <span class="detail-item">
                                                                                <i class="fas fa-location-dot"></i>
                                                                                {{ $event['location'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($event['participants']) && $event['participants'])
                                                                            <span class="detail-item">
                                                                                <i class="fas fa-users"></i>
                                                                                {{ $event['participants'] }} participants
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="agenda-status">
                                                                    <span class="status-dot status-{{ $event['color'] ?? 'primary' }}"></span>
                                                                    <span class="status-text">{{ ucfirst($event['status'] ?? 'scheduled') }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                        @else
                                            <!-- Week Navigation (show even when empty) -->
                                            <div class="week-navigation">
                                                <button class="nav-btn prev-week" onclick="changeWeek(-1)">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <div class="current-week-display">
                                                    <span class="week-text">Week {{ now()->format('W') }}</span>
                                                    <span class="week-range">{{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j') }}</span>
                                                </div>
                                                <button class="nav-btn next-week" onclick="changeWeek(1)">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Enhanced Empty State -->
                                            <div class="schedule-empty-state-redesigned">
                                                <div class="empty-illustration-redesigned">
                                                    <div class="empty-calendar-icon">
                                                        <i class="fas fa-calendar-check"></i>
                                                        <div class="floating-elements">
                                                            <div class="floating-dot dot-1"></div>
                                                            <div class="floating-dot dot-2"></div>
                                                            <div class="floating-dot dot-3"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="empty-content">
                                                    <h4>All Clear! 🎉</h4>
                                                    <p>You have no scheduled sessions for this week. Perfect time to plan ahead or take a well-deserved break!</p>
                                                    <div class="empty-actions">
                                                        <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="btn btn-primary-modern">
                                                            <i class="fas fa-plus"></i>
                                                            Add New Session
                                                        </a>
                                                        <a href="{{ route($role . '.activities') }}" class="btn btn-outline-modern">
                                                            <i class="fas fa-eye"></i>
                                                            Browse Activities
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
                                            @if($role === 'admin')
                                                {{-- Admin: Full access to all functions --}}
                                                <a href="{{ route('activities.create') }}" class="quick-action-btn">
                                                    <i class="fas fa-plus"></i>
                                                    <span>Create Activity</span>
                                                </a>
                                                <a href="{{ route('activities.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-clipboard-check"></i>
                                                    <span>Mark Attendance</span>
                                                </a>
                                                <a href="{{ route('staffs.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-users"></i>
                                                    <span>Manage Staff</span>
                                                </a>
                                                <a href="{{ route('trainees.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-user-graduate"></i>
                                                    <span>Manage Trainees</span>
                                                </a>
                                                <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="quick-action-btn">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>View Schedule</span>
                                                </a>
                                                <a href="{{ route('profile.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-user-cog"></i>
                                                    <span>Profile Settings</span>
                                                </a>
                                            @elseif($role === 'supervisor')
                                                {{-- Supervisor: Can create activities, manage staff, mark attendance --}}
                                                <a href="{{ route('activities.create') }}" class="quick-action-btn">
                                                    <i class="fas fa-plus"></i>
                                                    <span>Create Activity</span>
                                                </a>
                                                <a href="{{ route('activities.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-clipboard-check"></i>
                                                    <span>Mark Attendance</span>
                                                </a>
                                                <a href="{{ route('staffs.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-users"></i>
                                                    <span>View Staff</span>
                                                </a>
                                                <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="quick-action-btn">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>View Schedule</span>
                                                </a>
                                                <a href="{{ route('profile.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-user-cog"></i>
                                                    <span>Profile Settings</span>
                                                </a>
                                            @elseif($role === 'teacher')
                                                {{-- Teacher: Can create activities and mark attendance --}}
                                                <a href="{{ route('activities.create') }}" class="quick-action-btn">
                                                    <i class="fas fa-plus"></i>
                                                    <span>Create Activity</span>
                                                </a>
                                                <a href="{{ route('activities.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-clipboard-check"></i>
                                                    <span>Mark Attendance</span>
                                                </a>
                                                <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="quick-action-btn">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>View Schedule</span>
                                                </a>
                                                <a href="{{ route('profile.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-user-cog"></i>
                                                    <span>Profile Settings</span>
                                                </a>
                                            @elseif($role === 'ajk')
                                                {{-- AJK: Can view staff, mark attendance, view schedule --}}
                                                <a href="{{ route('activities.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-clipboard-check"></i>
                                                    <span>Mark Attendance</span>
                                                </a>
                                                <a href="{{ route('staffs.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-users"></i>
                                                    <span>View Staff</span>
                                                </a>
                                                <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="quick-action-btn">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>View Schedule</span>
                                                </a>
                                                <a href="{{ route('profile.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-user-cog"></i>
                                                    <span>Profile Settings</span>
                                                </a>
                                            @else
                                                {{-- Default: Basic access --}}
                                                <a href="{{ route('staffs.schedule', ['encrypted_id' => $user_encrypted_id]) }}" class="quick-action-btn">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>View Schedule</span>
                                                </a>
                                                <a href="{{ route('profile.home') }}" class="quick-action-btn">
                                                    <i class="fas fa-user-cog"></i>
                                                    <span>Profile Settings</span>
                                                </a>
                                            @endif
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

/* Dashboard Date/Time Display */
.dashboard-datetime {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.datetime-display {
    background: rgba(255, 255, 255, 0.15);
    padding: 1rem 1.5rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.date-info, .time-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
}

.date-info {
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.date-info i, .time-info i {
    font-size: 1.1rem;
    opacity: 0.9;
}

.time-info {
    font-size: 1.1rem;
    font-weight: 600;
}

.weather-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 500;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.weather-info i {
    font-size: 1.1rem;
    opacity: 0.9;
}

.weather-info span {
    font-weight: 600;
}

.weather-info small {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-left: 0.25rem;
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
    font-size: 0.7rem;
    text-transform: uppercase;
}

.timeline-action {
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
}

.timeline-description {
    margin: 0.5rem 0;
    padding: 0.5rem;
    background: #f8f9fa;
    border-left: 3px solid #dee2e6;
    border-radius: 0.25rem;
}

.action-created {
    background: #d1fae5;
    color: #065f46;
}

.action-updated {
    background: #dbeafe;
    color: #1e40af;
}

.action-deleted {
    background: #fee2e2;
    color: #991b1b;
}

.status-active, .status-success {
    background: #c6f6d5;
    color: #276749;
}

.status-info {
    background: #bee3f8;
    color: #2c5282;
}

.status-warning {
    background: #feebc8;
    color: #7c2d12;
}

.status-danger {
    background: #fed7d7;
    color: #742a2a;
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

/* ===== REDESIGNED SCHEDULE STYLES ===== */

.schedule-card-redesigned {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.schedule-card-redesigned:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.25);
}

.card-header-redesigned {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: none;
    padding: 1.5rem;
}

.header-content-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon-redesigned {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.header-text-redesigned .card-title-redesigned {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.header-text-redesigned .card-subtitle-redesigned {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    margin: 0;
}

.header-actions-redesigned {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

.card-content-redesigned {
    padding: 1.5rem;
    background: white;
}

/* Week Navigation */
.week-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fc;
    border-radius: 12px;
}

.nav-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nav-btn:hover {
    background: #5a67d8;
    transform: scale(1.1);
}

.current-week-display {
    text-align: center;
}

.week-text {
    display: block;
    font-weight: 600;
    font-size: 1.1rem;
    color: #2d3748;
}

.week-range {
    display: block;
    font-size: 0.875rem;
    color: #718096;
    margin-top: 0.25rem;
}

/* Schedule Grid */
.schedule-week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.day-column {
    background: #f7fafc;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.day-column.today-column {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.day-column.holiday-column {
    border-color: #fdcb6e;
    background: linear-gradient(135deg, rgba(255, 234, 167, 0.15), rgba(253, 203, 110, 0.15));
}

.day-column.holiday-column .day-header {
    background: linear-gradient(135deg, rgba(255, 234, 167, 0.4), rgba(253, 203, 110, 0.4));
}

.day-column.has-events {
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.day-column:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.day-header {
    padding: 1rem 0.75rem;
    background: rgba(255, 255, 255, 0.7);
    text-align: center;
    position: relative;
}

.day-column.today-column .day-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.day-name {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
    opacity: 0.8;
}

.day-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.day-number.today-number {
    color: white;
}

.today-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
}

.holiday-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    color: #e17055;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 700;
    box-shadow: 0 2px 4px rgba(225, 112, 85, 0.3);
    border: 1px solid #e17055;
}

.holiday-badge i {
    font-size: 0.65rem;
}

.day-events {
    padding: 0.75rem;
    min-height: 120px;
}

.event-card {
    background: #667eea;
    color: white;
    padding: 0.5rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.event-card.event-primary { background: #667eea; }
.event-card.event-success { background: #48bb78; }
.event-card.event-warning { background: #ed8936; }
.event-card.event-danger { background: #f56565; }
.event-card.event-info { background: #4299e1; }

.event-time {
    font-weight: 600;
    margin-bottom: 0.25rem;
    opacity: 0.9;
}

.event-title-short {
    font-weight: 500;
    line-height: 1.3;
    margin-bottom: 0.25rem;
}

.event-location-short {
    opacity: 0.8;
    font-size: 0.625rem;
}

.event-location-short i {
    margin-right: 0.25rem;
}

.more-events-indicator {
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.375rem 0.5rem;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.more-events-indicator:hover {
    background: #cbd5e0;
}

.no-events {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100px;
    color: #a0aec0;
    font-size: 0.75rem;
}

.no-events i {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.holiday-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100px;
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
}

.holiday-indicator i {
    font-size: 1.5rem;
    color: #e17055;
    margin-bottom: 0.5rem;
}

.holiday-name {
    font-weight: 600;
    font-size: 0.75rem;
    color: #2d3748;
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.holiday-type {
    font-size: 0.65rem;
    color: #e17055;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Today's Agenda */
.today-agenda {
    background: linear-gradient(135deg, #ffeaa7, #fab1a0);
    border-radius: 15px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.agenda-header h4 {
    color: #2d3748;
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.agenda-header i {
    color: #ed8936;
}

.agenda-event {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.agenda-event:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.agenda-time .time-badge {
    background: #667eea;
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    white-space: nowrap;
}

.agenda-content {
    flex-grow: 1;
}

.agenda-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.agenda-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #718096;
    font-size: 0.875rem;
}

.detail-item i {
    font-size: 0.75rem;
}

.agenda-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-dot.status-primary { background: #667eea; }
.status-dot.status-success { background: #48bb78; }
.status-dot.status-warning { background: #ed8936; }
.status-dot.status-danger { background: #f56565; }

.status-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4a5568;
    text-transform: capitalize;
}

/* Enhanced Empty State */
.schedule-empty-state-redesigned {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-illustration-redesigned {
    position: relative;
    margin-bottom: 2rem;
}

.empty-calendar-icon {
    position: relative;
    display: inline-block;
    font-size: 4rem;
    color: #e2e8f0;
}

.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.floating-dot {
    position: absolute;
    width: 8px;
    height: 8px;
    background: #667eea;
    border-radius: 50%;
    animation: float 2s ease-in-out infinite;
}

.floating-dot.dot-1 {
    top: 20%;
    left: 20%;
    animation-delay: 0s;
}

.floating-dot.dot-2 {
    top: 30%;
    right: 25%;
    animation-delay: 0.7s;
}

.floating-dot.dot-3 {
    bottom: 25%;
    left: 30%;
    animation-delay: 1.4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.empty-content h4 {
    color: #2d3748;
    font-weight: 700;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.empty-content p {
    color: #718096;
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.empty-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary-modern {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
}

.btn-outline-modern {
    background: transparent;
    border: 2px solid #e2e8f0;
    color: #4a5568;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-outline-modern:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    color: #2d3748;
    text-decoration: none;
    transform: translateY(-2px);
}

/* Clickable Event Styles */
.clickable-event {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.clickable-event:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #667eea;
}

.clickable-agenda {
    transition: all 0.3s ease;
    border-radius: 8px;
    padding: 0.5rem;
    margin: 0.25rem 0;
}

.clickable-agenda:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.click-indicator {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: #667eea;
    font-size: 0.7rem;
}

.clickable-event:hover .click-indicator {
    opacity: 1;
}

/* Responsive Design for New Schedule */
@media (max-width: 768px) {
    .schedule-week-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .day-header {
        padding: 0.75rem 0.5rem;
    }
    
    .day-number {
        font-size: 1.25rem;
    }
    
    .agenda-event {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .agenda-details {
        gap: 0.5rem;
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .header-content-flex {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .schedule-week-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .day-column {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
    }
    
    .day-header {
        padding: 0;
        background: none;
        min-width: 60px;
    }
    
    .day-column.today-column .day-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 10px;
        padding: 0.5rem;
    }
    
    .day-events {
        padding: 0;
        min-height: auto;
        flex-grow: 1;
    }
}

/* ===== DATA-BASED STATISTICS ENHANCEMENTS ===== */

.stats-card-enhanced {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}

.stats-card-enhanced:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stats-card-enhanced:hover .stats-overlay {
    opacity: 1;
}

.stats-sublabel {
    color: #6c757d;
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

.stats-overlay {
    position: absolute;
    top: 0;
    right: 0;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0 0 0 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-detail-icon {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.875rem;
}

.data-source-indicator {
    background: linear-gradient(135deg, #f8f9fc, #e9ecef);
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.data-source-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.data-source-content i {
    color: #32bdea;
    font-size: 1.1rem;
    margin-right: 0.5rem;
}

.data-source-text {
    flex-grow: 1;
    color: #495057;
    font-size: 0.875rem;
    line-height: 1.4;
}

.data-refresh-btn {
    background: #32bdea;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.data-refresh-btn:hover {
    background: #2ba3d4;
    transform: translateY(-1px);
}

.data-refresh-btn i {
    font-size: 0.75rem;
}

.data-refresh-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.data-refresh-btn.refreshing i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Enhanced stat card animations */
.stats-number-enhanced {
    transition: all 0.3s ease;
}

.stats-card-enhanced:hover .stats-number-enhanced {
    transform: scale(1.05);
}

/* Role-specific stat card colors */
.stats-card-enhanced[data-stat-type="enrolled-activities"] {
    border-left: 4px solid #28a745;
}

.stats-card-enhanced[data-stat-type="facilities-managed"] {
    border-left: 4px solid #ffc107;
}

.stats-card-enhanced[data-stat-type="my-activities"] {
    border-left: 4px solid #007bff;
}

.stats-card-enhanced[data-stat-type*="completion"] {
    border-left: 4px solid #17a2b8;
}

.stats-card-enhanced[data-stat-type*="attendance"] {
    border-left: 4px solid #fd7e14;
}

/* Loading states for real-time updates */
.stat-loading {
    opacity: 0.6;
    pointer-events: none;
}

.stat-loading .stats-number-enhanced {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .data-source-content {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .data-source-text {
        margin-bottom: 0.5rem;
    }
    
    .stats-sublabel {
        font-size: 0.7rem;
    }
    
    .stats-overlay {
        display: none;
    }
}
</style>

<script>
// Real-time Clock Update
function updateDateTime() {
    const now = new Date();

    // Update time
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        const displayMinutes = minutes < 10 ? '0' + minutes : minutes;
        timeElement.textContent = `${displayHours}:${displayMinutes} ${ampm}`;
    }
}

// Update time every second
setInterval(updateDateTime, 1000);
updateDateTime(); // Initial update

// Weather Widget - Fetch weather for centre location
async function fetchWeather() {
    const weatherWidget = document.getElementById('weatherWidget');
    if (!weatherWidget) return;

    try {
        // Using wttr.in - free weather API that doesn't require API key
        // Get centre name for location-based weather
        const centreName = '{{ session("centre_name", "Gombak") }}';
        const location = centreName.includes('Gombak') ? 'Gombak,Malaysia' : 'Kuala Lumpur,Malaysia';

        // Fetch weather data in simple JSON format
        const response = await fetch(`https://wttr.in/${encodeURIComponent(location)}?format=j1`);

        if (!response.ok) {
            throw new Error('Weather service unavailable');
        }

        const data = await response.json();
        const current = data.current_condition[0];
        const temp = current.temp_C;
        const feelsLike = current.FeelsLikeC;
        const desc = current.weatherDesc[0].value;
        const humidity = current.humidity;

        // Weather icon mapping
        const weatherCode = parseInt(current.weatherCode);
        let weatherIcon = 'fa-cloud';

        // Map weather codes to icons
        if (weatherCode === 113) weatherIcon = 'fa-sun';
        else if ([116, 119, 122].includes(weatherCode)) weatherIcon = 'fa-cloud';
        else if ([143, 248, 260].includes(weatherCode)) weatherIcon = 'fa-smog';
        else if ([176, 263, 266, 281, 284, 293, 296, 299, 302, 305, 308, 311, 314, 317, 353, 356, 359].includes(weatherCode)) weatherIcon = 'fa-cloud-rain';
        else if ([179, 182, 185, 227, 230, 317, 320, 323, 326, 329, 332, 335, 338, 350, 362, 365, 368, 371, 374, 377].includes(weatherCode)) weatherIcon = 'fa-snowflake';
        else if ([200, 386, 389, 392, 395].includes(weatherCode)) weatherIcon = 'fa-bolt';

        // Get location name for display
        const locationName = centreName.includes('Gombak') ? 'Gombak' : 'Kuala Lumpur';

        // Update widget
        weatherWidget.innerHTML = `
            <i class="fas ${weatherIcon}"></i>
            <span>${temp}°C</span>
            <small>${desc} • ${locationName}</small>
        `;
        weatherWidget.title = `Feels like ${feelsLike}°C • Humidity: ${humidity}% • ${locationName}, Malaysia`;

    } catch (error) {
        console.error('Weather fetch error:', error);
        weatherWidget.innerHTML = `
            <i class="fas fa-cloud"></i>
            <span>Weather unavailable</span>
        `;
    }
}

// Fetch weather on load and refresh every 30 minutes
fetchWeather();
setInterval(fetchWeather, 30 * 60 * 1000);

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


function navigateToSession(activityId, sessionId) {
    if (activityId && sessionId) {
        console.log('Navigating to session:', activityId, sessionId);
        // Navigate to the session-specific attendance page
        window.location.href = `{{ url('activities') }}/${activityId}/sessions/${sessionId}/attendance`;
    } else {
        console.error('Missing activity ID or session ID:', activityId, sessionId);
        alert('Session information not available');
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

// ===== NEW SCHEDULE FUNCTIONALITY =====

function scrollToToday() {
    const todayColumn = document.querySelector('.today-column');
    if (todayColumn) {
        todayColumn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Add a brief highlight effect
        todayColumn.style.transform = 'scale(1.02)';
        setTimeout(() => {
            todayColumn.style.transform = '';
        }, 300);
    }
}

let currentWeekOffset = 0;
let isLoadingWeek = false;

function changeWeek(direction) {
    if (isLoadingWeek) return; // Prevent multiple simultaneous requests
    
    currentWeekOffset += direction;
    isLoadingWeek = true;
    
    // Add loading effect
    const scheduleContainer = document.querySelector('.schedule-grid-container');
    const emptyState = document.querySelector('.schedule-empty-state-redesigned');
    const weekNavigation = document.querySelector('.week-navigation');
    
    // Disable navigation buttons
    const navButtons = document.querySelectorAll('.nav-btn');
    navButtons.forEach(btn => btn.disabled = true);
    
    if (scheduleContainer) {
        scheduleContainer.style.opacity = '0.5';
        scheduleContainer.style.transform = 'translateY(10px)';
    }
    
    if (emptyState) {
        emptyState.style.opacity = '0.5';
    }
    
    // Fetch new calendar data from server
    fetch(`{{ route('dashboard.week-calendar') }}?week_offset=${currentWeekOffset}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update week display
            const weekTextElement = document.querySelector('.week-text');
            const weekRangeElement = document.querySelector('.week-range');
            
            if (weekTextElement && data.week_info) {
                weekTextElement.textContent = `Week ${data.week_info.week_number}`;
            }
            
            if (weekRangeElement && data.week_info) {
                weekRangeElement.textContent = `${data.week_info.week_start} - ${data.week_info.week_end}`;
            }
            
            // Update calendar content dynamically
            updateCalendarContent(data.calendar_data);
        } else {
            console.error('Failed to load calendar data:', data.error);
            // Revert the offset
            currentWeekOffset -= direction;
        }
    })
    .catch(error => {
        console.error('Error fetching calendar data:', error);
        // Revert the offset
        currentWeekOffset -= direction;
    })
    .finally(() => {
        // Remove loading effects
        isLoadingWeek = false;
        navButtons.forEach(btn => btn.disabled = false);
        
        if (scheduleContainer) {
            scheduleContainer.style.opacity = '';
            scheduleContainer.style.transform = '';
        }
        
        if (emptyState) {
            emptyState.style.opacity = '';
        }
    });
}

// Function to update calendar content dynamically
function updateCalendarContent(calendarData) {
    const scheduleContainer = document.querySelector('.schedule-grid-container');
    const emptyState = document.querySelector('.schedule-empty-state-redesigned');
    const cardContent = document.querySelector('.card-content-redesigned');
    
    if (!calendarData || !calendarData.events || calendarData.events.length === 0) {
        // Show empty state
        if (scheduleContainer) {
            scheduleContainer.style.display = 'none';
        }
        if (emptyState) {
            emptyState.style.display = 'block';
        }
        return;
    }
    
    // Hide empty state
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    // Show schedule container
    if (scheduleContainer) {
        scheduleContainer.style.display = 'block';
    }
    
    // Generate week days for the current calendar data
    const weekDays = {};
    // Use the formatted date string from backend
    const weekStart = new Date(calendarData.week_start_formatted);
    
    // Generate 7 days starting from week start
    for (let i = 0; i < 7; i++) {
        const date = new Date(weekStart);
        date.setDate(weekStart.getDate() + i);
        const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD format
        weekDays[dateKey] = {
            date: date,
            events: []
        };
    }
    
    // Group events by date
    calendarData.events.forEach(event => {
        const dateKey = event.full_date;
        if (weekDays[dateKey]) {
            weekDays[dateKey].events.push(event);
        }
    });
    
    // Generate HTML for the schedule grid
    let scheduleGridHTML = '<div class="schedule-week-grid">';
    
    Object.keys(weekDays).forEach(dateKey => {
        const dayData = weekDays[dateKey];
        const date = dayData.date;
        const isToday = isDateToday(date);
        const hasEvents = dayData.events.length > 0;
        
        scheduleGridHTML += `
            <div class="day-column ${isToday ? 'today-column' : ''} ${hasEvents ? 'has-events' : ''}">
                <!-- Day Header -->
                <div class="day-header">
                    <div class="day-info">
                        <span class="day-name">${date.toLocaleDateString('en-US', { weekday: 'short' })}</span>
                        <span class="day-number ${isToday ? 'today-number' : ''}">
                            ${date.getDate()}
                        </span>
                    </div>
                    ${isToday ? '<div class="today-badge">Today</div>' : ''}
                </div>

                <!-- Day Events -->
                <div class="day-events">`;
        
        if (hasEvents) {
            // Show up to 3 events
            const eventsToShow = dayData.events.slice(0, 3);
            eventsToShow.forEach(event => {
                scheduleGridHTML += `
                    <div class="event-card event-${event.color || 'primary'} clickable-event" 
                         onclick="navigateToSession('${event.activity_id || ''}', '${event.session_id || event.id || ''}')"
                         style="cursor: pointer;"
                         title="Click to view session details">
                        <div class="event-time">${event.time}</div>
                        <div class="event-title-short">${truncateString(event.title, 20)}</div>
                        ${event.location ? `
                            <div class="event-location-short">
                                <i class="fas fa-map-pin"></i>
                                ${truncateString(event.location, 15)}
                            </div>
                        ` : ''}
                        <div class="click-indicator">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                    </div>`;
            });
            
            // Show "more events" indicator if needed
            if (dayData.events.length > 3) {
                scheduleGridHTML += `
                    <div class="more-events-indicator">
                        +${dayData.events.length - 3} more
                    </div>`;
            }
        } else {
            // No events
            scheduleGridHTML += `
                <div class="no-events">
                    <i class="fas fa-coffee"></i>
                    <span>Free Day</span>
                </div>`;
        }
        
        scheduleGridHTML += `
                </div>
            </div>`;
    });
    
    scheduleGridHTML += '</div>';
    
    // Update the schedule container
    if (scheduleContainer) {
        scheduleContainer.innerHTML = scheduleGridHTML;
    }
    
    // Re-attach event listeners to new elements
    attachEventCardListeners();
}

// Helper function to check if date is today
function isDateToday(date) {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
}

// Helper function to truncate strings
function truncateString(str, maxLength) {
    if (!str) return '';
    return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
}

// Function to attach event listeners to dynamically created event cards
function attachEventCardListeners() {
    const eventCards = document.querySelectorAll('.event-card.clickable-event');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
        
        card.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1.02)';
            }, 100);
        });
    });
    
    // Attach listeners to "more events" indicators
    const moreEventIndicators = document.querySelectorAll('.more-events-indicator');
    moreEventIndicators.forEach(indicator => {
        indicator.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Show all events for this day');
        });
    });
}

// Add click handlers for event cards
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to event cards
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
        
        card.addEventListener('click', function(e) {
            // Handle event card click - could open modal or navigate
            const eventTitle = this.querySelector('.event-title-short')?.textContent;
            console.log(`Clicked event: ${eventTitle}`);
            
            // Add click animation
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1.02)';
            }, 100);
        });
    });
    
    // Add click handlers for "more events" indicators
    const moreEventIndicators = document.querySelectorAll('.more-events-indicator');
    moreEventIndicators.forEach(indicator => {
        indicator.addEventListener('click', function(e) {
            e.stopPropagation();
            // This could open a modal showing all events for the day
            console.log('Show all events for this day');
        });
    });
    
    // Add smooth hover animation to day columns
    const dayColumns = document.querySelectorAll('.day-column');
    dayColumns.forEach(column => {
        column.addEventListener('mouseenter', function() {
            if (!this.classList.contains('today-column')) {
                this.style.transform = 'translateY(-2px)';
            }
        });
        
        column.addEventListener('mouseleave', function() {
            if (!this.classList.contains('today-column')) {
                this.style.transform = '';
            }
        });
    });
});

// ===== PERSONAL STATISTICS FUNCTIONALITY =====

function refreshPersonalStats() {
    const refreshBtn = document.querySelector('.data-refresh-btn');
    const statCards = document.querySelectorAll('.stats-card-enhanced');
    
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.classList.add('refreshing');
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refreshing...';
    }
    
    // Add loading state to all stat cards
    statCards.forEach(card => {
        card.classList.add('stat-loading');
    });
    
    // In a real application, you would make an AJAX call to refresh the data
    // For now, we'll simulate the refresh process
    setTimeout(() => {
        // Simulate data refresh with slight changes to show it's working
        const statNumbers = document.querySelectorAll('.stats-number-enhanced');
        statNumbers.forEach((number, index) => {
            const currentValue = number.textContent;
            
            // Add a subtle animation to show the refresh worked
            number.style.transform = 'scale(1.1)';
            number.style.color = '#32bdea';
            
            setTimeout(() => {
                number.style.transform = '';
                number.style.color = '';
            }, 300);
        });
        
        // Remove loading states
        statCards.forEach(card => {
            card.classList.remove('stat-loading');
        });
        
        // Reset refresh button
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.classList.remove('refreshing');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }
        
        // Update the timestamp
        const dataSourceText = document.querySelector('.data-source-text');
        if (dataSourceText) {
            const now = new Date();
            const timeString = now.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            dataSourceText.innerHTML = dataSourceText.innerHTML.replace(
                /Last updated: .+$/,
                `Last updated: ${timeString}`
            );
        }
        
        console.log('Personal statistics refreshed successfully');
    }, 2000);
}

// Add click handlers for stat cards to show more details
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stats-card-enhanced');
    
    statCards.forEach((card, index) => {
        card.addEventListener('click', function() {
            const statType = this.dataset.statType;
            const statValue = this.querySelector('.stats-number-enhanced').textContent;
            const statLabel = this.querySelector('.stats-label-enhanced').textContent;
            
            // Here you could show a modal with more detailed information
            console.log(`Clicked on ${statLabel}: ${statValue}`);
            
            // Add click animation
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // In a real implementation, you might:
            // - Show a detailed modal with historical data
            // - Navigate to a detailed report page
            // - Display a tooltip with more information
        });
    });
});

// Mark Attendance Function
function markAttendance() {
    const attendanceBtn = document.getElementById('attendanceBtn');
    const originalHTML = attendanceBtn.innerHTML;

    // Get current user ID (unique per user)
    const userId = '{{ $user_encrypted_id ?? session("id", "guest") }}';

    // Check if already marked today (per user)
    const today = new Date().toDateString();
    const lastMarkedKey = `attendance_last_marked_${userId}`;
    const lastTimeKey = `attendance_time_${userId}`;
    const lastMarked = localStorage.getItem(lastMarkedKey);

    if (lastMarked === today) {
        // Show already marked message
        const markedTime = localStorage.getItem(lastTimeKey);
        attendanceBtn.innerHTML = '<i class="fas fa-check-circle"></i> Already Marked Today';
        attendanceBtn.classList.add('attendance-marked');

        setTimeout(() => {
            attendanceBtn.innerHTML = originalHTML;
            attendanceBtn.classList.remove('attendance-marked');
        }, 3000);

        if (markedTime) {
            showAttendanceNotification('You already marked attendance today at ' + markedTime, 'success');
        }
        return;
    }

    // Show confirmation dialog
    const confirmed = confirm('Are you sure you want to mark your attendance for today?');
    if (!confirmed) {
        return;
    }

    // Show loading state
    attendanceBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';
    attendanceBtn.disabled = true;

    // Simulate attendance marking (replace with actual API call)
    setTimeout(() => {
        // Mark as successful
        attendanceBtn.innerHTML = '<i class="fas fa-check-circle"></i> Marked Successfully!';
        attendanceBtn.classList.add('attendance-success');

        // Store in localStorage to remember for today (per user)
        const currentTime = new Date().toLocaleTimeString();
        localStorage.setItem(lastMarkedKey, today);
        localStorage.setItem(lastTimeKey, currentTime);

        // Reset button after 3 seconds
        setTimeout(() => {
            attendanceBtn.innerHTML = '<i class="fas fa-user-check"></i> Mark Attendance';
            attendanceBtn.classList.remove('attendance-success');
            attendanceBtn.disabled = false;
        }, 3000);

        // Show success notification
        showAttendanceNotification('Attendance marked successfully at ' + currentTime, 'success');

    }, 1500); // Simulate API delay
}

// Show attendance notification
function showAttendanceNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `attendance-notification attendance-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Hide after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Check attendance status on page load
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toDateString();
    const userId = '{{ $user_encrypted_id ?? session("id", "guest") }}';
    const lastMarkedKey = `attendance_last_marked_${userId}`;
    const lastTimeKey = `attendance_time_${userId}`;
    const lastMarked = localStorage.getItem(lastMarkedKey);
    const attendanceBtn = document.getElementById('attendanceBtn');

    if (lastMarked === today && attendanceBtn) {
        attendanceBtn.innerHTML = '<i class="fas fa-check-circle"></i> Marked Today';
        attendanceBtn.classList.add('attendance-marked');

        const markedTime = localStorage.getItem(lastTimeKey);
        if (markedTime) {
            attendanceBtn.title = `Attendance marked at ${markedTime}`;
        }
    }
});
</script>

<style>
/* Attendance Button Styles */
.attendance-btn {
    background: linear-gradient(135deg, #2ed573, #1dd1a1);
    color: white;
    border: none;
    transition: all 0.3s ease;
}

.attendance-btn:hover {
    background: linear-gradient(135deg, #1dd1a1, #00d2d3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46, 213, 115, 0.4);
}

.attendance-btn.attendance-marked {
    background: linear-gradient(135deg, #ffa726, #ff9800);
    cursor: default;
}

.attendance-btn.attendance-success {
    background: linear-gradient(135deg, #2ed573, #1dd1a1);
    animation: pulse 0.6s ease-in-out;
}

.attendance-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 9999;
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease;
    max-width: 300px;
}

.attendance-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.attendance-notification.attendance-success {
    border-left: 4px solid #2ed573;
}

.attendance-notification.attendance-success i {
    color: #2ed573;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>
@endsection