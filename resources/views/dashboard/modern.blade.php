@extends('layouts.app')

@section('title', 'Dashboard - CREAMS')

@section('content')
<div class="enhanced-dashboard">
    <!-- Dashboard Customization Panel -->
    <div class="dashboard-customization" id="customizationPanel">
        <div class="customization-header">
            <h5><i class="fas fa-palette me-2"></i>Customize Your Dashboard</h5>
            <button class="btn-close" onclick="toggleCustomization()"></button>
        </div>
        <div class="customization-content">
            <div class="widget-toggles">
                <div class="toggle-group">
                    <h6>Visible Widgets</h6>
                    <label class="widget-toggle">
                        <input type="checkbox" id="toggle-stats" checked>
                        <span class="toggle-slider"></span>
                        Quick Statistics
                    </label>
                    <label class="widget-toggle">
                        <input type="checkbox" id="toggle-sessions" checked>
                        <span class="toggle-slider"></span>
                        Current Sessions
                    </label>
                    <label class="widget-toggle">
                        <input type="checkbox" id="toggle-notifications" checked>
                        <span class="toggle-slider"></span>
                        Notifications
                    </label>
                    <label class="widget-toggle">
                        <input type="checkbox" id="toggle-calendar" checked>
                        <span class="toggle-slider"></span>
                        Weekly Calendar
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Header Section -->
    <div class="dashboard-header-enhanced mb-4">
        <div class="header-gradient"></div>
        <div class="header-content">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7">
                    <div class="welcome-section">
                        <h1 class="dashboard-title-enhanced">
                            <span class="title-icon">👋</span>
                            Welcome back, {{ $user_name }}!
                        </h1>
                        <p class="dashboard-subtitle-enhanced">
                            {{ ucfirst($role) }} Dashboard • {{ $current_time }}
                        </p>
                        <div class="status-indicators">
                            <span class="status-badge status-online">
                                <i class="fas fa-circle"></i> Online
                            </span>
                            <span class="status-badge status-centre">
                                <i class="fas fa-building"></i> {{ $centre_info->centre_name ?? 'System Wide' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5">
                    <div class="header-actions">
                        <!-- Page Toggle (Admin Only) -->
                        @if($role === 'admin')
                        <div class="page-toggle mb-3">
                            <button class="toggle-btn active" id="generalPageBtn" onclick="switchPage('general')">
                                <i class="fas fa-chart-line"></i> General
                            </button>
                            <button class="toggle-btn" id="personalPageBtn" onclick="switchPage('personal')">
                                <i class="fas fa-user"></i> Personal
                            </button>
                        </div>
                        @endif
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button class="btn btn-icon" onclick="toggleCustomization()" title="Customize Dashboard">
                                <i class="fas fa-palette"></i>
                            </button>
                            <button class="btn btn-icon refresh-btn" onclick="refreshDashboard()" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-icon" type="button" data-bs-toggle="dropdown" title="Settings">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="toggleTheme()"><i class="fas fa-moon me-2"></i>Dark Mode</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Book Container -->
    <div class="dashboard-book" id="dashboardBook">
        <!-- General Page (Left Page) -->
        <div class="dashboard-page general-page active" id="generalPage">
            <!-- Unified Smart Notifications -->
            @if((isset($system_alerts) && count($system_alerts) > 0) || (isset($notifications) && count($notifications) > 0))
    <div class="unified-notifications-bar mb-4">
        <div class="notification-header">
            <h5 class="notification-title">
                <i class="fas fa-bell me-2"></i>
                Important Updates & Alerts
                <span class="notification-count-badge">{{ (count($system_alerts ?? []) + count($notifications ?? [])) }}</span>
            </h5>
            <div class="notification-actions">
                <button class="btn btn-sm btn-outline-secondary" onclick="markAllRead()">
                    <i class="fas fa-check-double me-1"></i> Mark All Read
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="toggleNotificationView()">
                    <i class="fas fa-expand-alt me-1"></i> Expand
                </button>
            </div>
        </div>
        <div class="notification-carousel" id="notificationCarousel">
            @if(isset($system_alerts))
                @foreach($system_alerts as $alert)
                <div class="notification-item notification-{{ $alert['type'] }} system-alert" data-type="{{ $alert['type'] }}">
                    <div class="notification-priority priority-high">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="notification-icon">
                        <i class="{{ $alert['icon'] }}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header-info">
                            <span class="notification-type-label">System Alert</span>
                            <span class="notification-time">{{ now()->diffForHumans() }}</span>
                        </div>
                        <div class="notification-message">{{ $alert['message'] }}</div>
                        @if(isset($alert['action_url']))
                        <div class="notification-actions-inline">
                            <a href="{{ $alert['action_url'] }}" class="notification-action btn btn-sm btn-primary">
                                <i class="fas fa-arrow-right me-1"></i>{{ $alert['action_text'] }}
                            </a>
                        </div>
                        @endif
                    </div>
                    <button class="notification-close" onclick="dismissNotification(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endforeach
            @endif
            @if(isset($notifications))
                @foreach(array_slice((array)$notifications, 0, 3) as $notification)
                <div class="notification-item notification-{{ $notification['type'] ?? 'info' }} user-notification" data-type="{{ $notification['type'] ?? 'info' }}">
                    <div class="notification-priority priority-normal">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header-info">
                            <span class="notification-type-label">Notification</span>
                            <span class="notification-time">{{ $notification['time'] }}</span>
                        </div>
                        <div class="notification-message">{{ $notification['message'] }}</div>
                    </div>
                    <button class="notification-close" onclick="dismissNotification(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endforeach
            @endif
        </div>
    </div>
    @endif

    <!-- Enhanced Quick Stats with Drill-down -->
    <div class="quick-stats-enhanced mb-4" id="quickStats">
        <div class="row">
            @if(isset($quick_stats))
                @foreach($quick_stats as $index => $stat)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="stat-card-enhanced stat-card-{{ $stat['color'] ?? 'primary' }}" 
                         data-stat-type="{{ strtolower(str_replace(' ', '_', $stat['title'])) }}"
                         onclick="showStatDetails('{{ $stat['title'] }}', {{ $stat['value'] }})">
                        <div class="stat-card-content">
                            <div class="stat-icon-enhanced">
                                <i class="{{ $stat['icon'] ?? 'fas fa-chart-bar' }}"></i>
                            </div>
                            <div class="stat-details">
                                <div class="stat-number-enhanced">
                                    <span class="counter" data-target="{{ $stat['value'] ?? 0 }}">0</span>
                                </div>
                                <div class="stat-label-enhanced">{{ $stat['title'] ?? 'Statistic' }}</div>
                                @if(isset($stat['trend']))
                                <div class="stat-trend-enhanced">
                                    <i class="fas fa-arrow-up"></i> {{ $stat['trend'] }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="stat-sparkline" id="sparkline-{{ $index }}"></div>
                        <div class="drill-down-indicator">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
        <div class="row">
            <!-- Left Column - Primary Content -->
            <div class="col-lg-8 col-md-12">
                
                <!-- Current Active Sessions with Real-time Updates -->
                @if(isset($current_sessions) && count($current_sessions) > 0)
                <div class="dashboard-widget active-sessions-widget" id="currentSessionsWidget">
                    <div class="widget-header">
                        <h5 class="widget-title">
                            <span class="title-icon">🎯</span>
                            Current Active Sessions
                            <span class="live-indicator">
                                <span class="live-dot"></span>
                                LIVE
                            </span>
                        </h5>
                        <div class="widget-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshSessions()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="sessions-grid-enhanced">
                            @foreach($current_sessions as $session)
                            <div class="session-card-enhanced" data-session-id="{{ $session['session_id'] ?? '' }}">
                                <div class="session-status-bar status-{{ strtolower($session['status']) }}"></div>
                                <div class="session-header">
                                    <h6 class="session-activity">{{ $session['activity'] }}</h6>
                                    <span class="session-status-badge status-{{ strtolower($session['status']) }}">
                                        {{ $session['status'] }}
                                    </span>
                                </div>
                                <div class="session-details">
                                    <div class="session-info-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span>{{ $session['teacher'] }}</span>
                                    </div>
                                    <div class="session-info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $session['time'] }}</span>
                                    </div>
                                    <div class="session-info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $session['venue'] }}</span>
                                    </div>
                                </div>
                                <div class="session-actions">
                                    <button class="btn btn-sm btn-primary">View Details</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Interactive Activity Timeline -->
                @if(isset($recent_activities) && count($recent_activities) > 0)
                <div class="dashboard-widget activity-timeline-widget" id="activityTimelineWidget">
                    <div class="widget-header">
                        <h5 class="widget-title">
                            <span class="title-icon">📋</span>
                            Recent Activities
                        </h5>
                        <div class="widget-filters">
                            <select class="form-select form-select-sm" onchange="filterActivities(this.value)">
                                <option value="all">All Activities</option>
                                <option value="rehabilitation">Rehabilitation</option>
                                <option value="academic">Academic</option>
                            </select>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="activity-timeline-enhanced">
                            @foreach($recent_activities as $activity)
                            @php
                                $activityType = $activity['type'] ?? 'general';
                                $activityStatus = $activity['status'] ?? 'active';
                                $activityTitle = $activity['title'] ?? 'Activity';
                                $activityTime = $activity['time'] ?? 'Recently';
                            @endphp
                            <div class="timeline-item-enhanced" data-category="{{ $activityType }}" data-status="{{ strtolower($activityStatus) }}">
                                <div class="timeline-marker-enhanced">
                                    <div class="marker-dot"></div>
                                </div>
                                <div class="timeline-content-enhanced">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">{{ $activityTitle }}</h6>
                                        <span class="timeline-time">{{ $activityTime }}</span>
                                    </div>
                                    <div class="timeline-status">
                                        <span class="status-badge status-{{ $activityStatus }}">
                                            {{ ucfirst($activityStatus) }}
                                        </span>
                                        <span class="type-badge type-{{ $activityType }}">
                                            {{ ucfirst($activityType) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <!-- Right Column - Secondary Content -->
            <div class="col-lg-4 col-md-12">
                
                <!-- Enhanced Quick Actions with Categories -->
                <div class="dashboard-widget quick-actions-widget" id="quickActionsWidget">
                    <div class="widget-header">
                        <h5 class="widget-title">
                            <span class="title-icon">⚡</span>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="widget-content">
                        <div class="quick-actions-enhanced">
                            @if($role == 'admin')
                                <div class="action-category">
                                    <h6 class="category-title">Administration</h6>
                                    <div class="action-grid">
                                        <a href="{{ route('staffs.register') }}" class="quick-action-enhanced" data-category="admin">
                                            <div class="action-icon">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <span class="action-label">Add User</span>
                                        </a>
                                        <a href="{{ route('centres.create') }}" class="quick-action-enhanced" data-category="admin">
                                            <div class="action-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <span class="action-label">Add Centre</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            
                            @if(in_array($role, ['admin', 'supervisor']))
                                <div class="action-category">
                                    <h6 class="category-title">Management</h6>
                                    <div class="action-grid">
                                        <a href="{{ route('trainees.create') }}" class="quick-action-enhanced" data-category="management">
                                            <div class="action-icon">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <span class="action-label">Add Trainee</span>
                                        </a>
                                        <a href="{{ route('activities.create') }}" class="quick-action-enhanced" data-category="management">
                                            <div class="action-icon">
                                                <i class="fas fa-plus-circle"></i>
                                            </div>
                                            <span class="action-label">Create Activity</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="action-category">
                                <h6 class="category-title">Personal</h6>
                                <div class="action-grid">
                                    <a href="{{ route('profile') }}" class="quick-action-enhanced" data-category="personal">
                                        <div class="action-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="action-label">My Profile</span>
                                    </a>
                                    <a href="{{ route('profile') }}#letter-generator" class="quick-action-enhanced" data-category="personal">
                                        <div class="action-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <span class="action-label">Generate Letter</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notifications Widget (if more notifications exist) -->
                @if(isset($notifications) && count($notifications) > 3)
                <div class="dashboard-widget additional-notifications-widget" id="additionalNotificationsWidget">
                    <div class="widget-header">
                        <h5 class="widget-title">
                            <span class="title-icon">📬</span>
                            More Notifications
                            <span class="notification-count">{{ count($notifications) - 3 }}</span>
                        </h5>
                        <div class="widget-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="showAllNotifications()">
                                <i class="fas fa-list me-1"></i> View All
                            </button>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="notifications-summary">
                            <div class="notification-stats">
                                <div class="stat-item">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <span>{{ count(array_filter((array)$notifications, function($n) { return ($n['type'] ?? '') === 'info'; })) }} Info</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <span>{{ count(array_filter((array)$notifications, function($n) { return ($n['type'] ?? '') === 'warning'; })) }} Warnings</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>{{ count(array_filter((array)$notifications, function($n) { return ($n['type'] ?? '') === 'success'; })) }} Updates</span>
                                </div>
                            </div>
                            <div class="quick-actions-notifications">
                                <button class="btn btn-sm btn-outline-success" onclick="markAllRead()">
                                    <i class="fas fa-check-double me-1"></i> Mark All Read
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="openNotificationCenter()">
                                    <i class="fas fa-cog me-1"></i> Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Weekly Calendar Widget -->
                @if(isset($calendar_events) && count($calendar_events) > 0)
                <div class="dashboard-widget calendar-widget" id="calendarWidget">
                    <div class="widget-header">
                        <h5 class="widget-title">
                            <span class="title-icon">📅</span>
                            This Week's Schedule
                        </h5>
                        <div class="widget-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="showFullCalendar()">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="calendar-events-enhanced">
                            @foreach($calendar_events as $event)
                            <div class="calendar-event-enhanced" data-event-status="{{ $event['status'] }}">
                                <div class="event-date-enhanced">
                                    <span class="event-day">{{ $event['day'] }}</span>
                                    <span class="event-date-num">{{ $event['date'] }}</span>
                                </div>
                                <div class="event-details-enhanced">
                                    <h6 class="event-title">{{ $event['title'] }}</h6>
                                    <p class="event-time">{{ $event['time'] }}</p>
                                </div>
                                <div class="event-status-enhanced">
                                    <span class="status-dot status-{{ $event['color'] }}"></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-nav-bar d-lg-none">
        <div class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </div>
        <div class="mobile-nav-item" onclick="showQuickActions()">
            <i class="fas fa-bolt"></i>
            <span>Actions</span>
        </div>
        <div class="mobile-nav-item" onclick="showNotifications()">
            <i class="fas fa-bell"></i>
            <span>Alerts</span>
            @if(isset($notifications) && count($notifications) > 0)
            <span class="mobile-badge">{{ count($notifications) }}</span>
            @endif
        </div>
        <div class="mobile-nav-item" onclick="showProfile()">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </div>
    </div>
</div>

<!-- Stat Details Modal -->
<div class="modal fade" id="statDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statModalTitle">Statistics Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statModalContent">
                <!-- Dynamic content loaded here -->
            </div>
        </div>
    </div>
</div>
        </div>

        <!-- Personal Page (Right Page) -->
        <div class="dashboard-page personal-page" id="personalPage">
            <div class="personal-dashboard-redesign">
                <!-- Modern Personal Header with Gradient -->
                <div class="personal-hero-section">
                    <div class="hero-background">
                        <div class="hero-pattern"></div>
                    </div>
                    <div class="hero-content">
                        <div class="personal-avatar-modern">
                            @php
                                $avatarPath = session('user_avatar') ?? session('avatar');
                                $avatarUrl = $avatarPath ? asset('storage/avatars/' . $avatarPath) : asset('images/default-avatar.svg');
                            @endphp
                            <div class="avatar-container">
                                <img src="{{ $avatarUrl }}" alt="Profile" class="avatar-img" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback" style="display: none;">
                                    {{ substr($user_name, 0, 1) }}
                                </div>
                                <div class="avatar-status"></div>
                            </div>
                        </div>
                        <div class="hero-text">
                            <h1 class="hero-greeting">
                                <span class="greeting-time">{{ date('H') < 12 ? 'Good Morning' : (date('H') < 18 ? 'Good Afternoon' : 'Good Evening') }}</span>,
                                <span class="user-name">{{ $user_name }}! 👋</span>
                            </h1>
                            <p class="hero-subtitle">
                                <i class="fas fa-shield-alt me-2"></i>{{ ucfirst($role) }} Dashboard
                                <span class="mx-2">•</span>
                                <i class="fas fa-building me-2"></i>{{ $centre_info->centre_name ?? 'System Wide' }}
                            </p>
                            <div class="hero-actions">
                                <a href="{{ route('profile') }}" class="btn-hero btn-hero-primary">
                                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                                </a>
                                <button class="btn-hero btn-hero-secondary" onclick="openQuickSettings()">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modern Statistics Cards -->
                <div class="personal-stats-modern">
                    <div class="stats-grid-modern">
                        <div class="stat-card-modern stat-primary">
                            <div class="stat-icon-modern">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-content-modern">
                                <div class="stat-number-modern" data-count="{{ $quick_stats[0]['value'] ?? 0 }}">0</div>
                                <div class="stat-label-modern">My Activities</div>
                                <div class="stat-trend-modern">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+12% this week</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern stat-success">
                            <div class="stat-icon-modern">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content-modern">
                                <div class="stat-number-modern" data-count="{{ count($upcoming_sessions ?? []) }}">0</div>
                                <div class="stat-label-modern">Upcoming Sessions</div>
                                <div class="stat-trend-modern">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>{{ count($upcoming_sessions ?? []) > 0 ? 'Next: Today' : 'None scheduled' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern stat-warning">
                            <div class="stat-icon-modern">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="stat-content-modern">
                                <div class="stat-number-modern" data-count="{{ count($notifications ?? []) }}">0</div>
                                <div class="stat-label-modern">Notifications</div>
                                <div class="stat-trend-modern">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ count($notifications ?? []) > 0 ? 'Latest: 1h ago' : 'All caught up!' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern stat-info">
                            <div class="stat-icon-modern">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content-modern">
                                <div class="stat-number-modern" data-count="{{ $progress_summary['percentage'] ?? 85 }}">0</div>
                                <div class="stat-label-modern">Overall Progress</div>
                                <div class="stat-trend-modern">
                                    <i class="fas fa-trophy"></i>
                                    <span>Excellent work!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modern Content Grid -->
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
                                        $activityType = $activity['type'] ?? 'general';
                                        $activityTitle = $activity['title'] ?? 'Activity';
                                        $activityTime = $activity['time'] ?? 'Recently';
                                        $activityStatus = $activity['status'] ?? 'active';
                                    @endphp
                                    <div class="activity-item-modern">
                                        <div class="activity-icon-modern activity-{{ $activityType }}">
                                            <i class="fas fa-{{ $activityType === 'rehabilitation' ? 'heartbeat' : ($activityType === 'academic' ? 'graduation-cap' : 'circle') }}"></i>
                                        </div>
                                        <div class="activity-details-modern">
                                            <div class="activity-title-modern">{{ $activityTitle }}</div>
                                            <div class="activity-meta-modern">
                                                <span class="time">{{ $activityTime }}</span>
                                                <span class="separator">•</span>
                                                <span class="status status-{{ $activityStatus }}">
                                                    {{ ucfirst($activityStatus) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="activity-action-modern">
                                            <button class="btn-sm-modern">View</button>
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

                        <!-- Schedule Card -->
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
                                    <button class="btn-icon-modern" onclick="openFullCalendar()">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-content-modern">
                                @if(isset($calendar_events) && count($calendar_events) > 0)
                                    @foreach(array_slice((array)$calendar_events, 0, 4) as $event)
                                    <div class="schedule-item-modern">
                                        <div class="schedule-date-modern">
                                            <div class="date-day">{{ $event['day'] ?? 'Mon' }}</div>
                                            <div class="date-num">{{ $event['date'] ?? '01' }}</div>
                                        </div>
                                        <div class="schedule-details-modern">
                                            <div class="schedule-title-modern">{{ $event['title'] ?? 'Event' }}</div>
                                            <div class="schedule-time-modern">
                                                <i class="fas fa-clock me-1"></i>{{ $event['time'] ?? '10:00 AM' }}
                                            </div>
                                        </div>
                                        <div class="schedule-status-modern">
                                            <div class="status-dot-modern status-{{ $event['color'] ?? 'primary' }}"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="empty-state-modern">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>No Upcoming Events</h4>
                                        <p>Your calendar is clear. Time to plan something!</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Actions Card -->
                        <div class="content-card-modern actions-card">
                            <div class="card-header-modern">
                                <div class="header-icon">
                                    <i class="fas fa-rocket"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Quick Actions</h3>
                                    <p>Common tasks & shortcuts</p>
                                </div>
                            </div>
                            <div class="card-content-modern">
                                <div class="actions-grid-modern">
                                    <a href="{{ route('profile') }}" class="action-btn-modern action-primary">
                                        <i class="fas fa-user-edit"></i>
                                        <span>Edit Profile</span>
                                    </a>
                                    @if(in_array($role, ['admin', 'supervisor', 'teacher']))
                                    <a href="{{ route('activities.create') }}" class="action-btn-modern action-success">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>New Activity</span>
                                    </a>
                                    @endif
                                    <button onclick="openReports()" class="action-btn-modern action-info">
                                        <i class="fas fa-chart-bar"></i>
                                        <span>Reports</span>
                                    </button>
                                    <button onclick="openHelp()" class="action-btn-modern action-warning">
                                        <i class="fas fa-question-circle"></i>
                                        <span>Help</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Card -->
                        <div class="content-card-modern notifications-modern-card">
                            <div class="card-header-modern">
                                <div class="header-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Recent Notifications</h3>
                                    <p>Stay updated on important changes</p>
                                </div>
                                <div class="header-action">
                                    <button class="btn-icon-modern" onclick="markAllNotificationsRead()">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-content-modern">
                                @if(isset($notifications) && count($notifications) > 0)
                                    @foreach(array_slice((array)$notifications, 0, 3) as $notification)
                                    <div class="notification-item-modern notification-{{ $notification['type'] ?? 'info' }}">
                                        <div class="notification-icon-modern">
                                            <i class="fas fa-{{ $notification['type'] === 'warning' ? 'exclamation-triangle' : ($notification['type'] === 'success' ? 'check-circle' : 'info-circle') }}"></i>
                                        </div>
                                        <div class="notification-content-modern">
                                            <div class="notification-message-modern">{{ $notification['message'] ?? 'Notification message' }}</div>
                                            <div class="notification-time-modern">{{ $notification['time'] ?? 'Recently' }}</div>
                                        </div>
                                        <div class="notification-action-modern">
                                            <button class="btn-dismiss-modern" onclick="dismissNotificationPersonal(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="empty-state-modern">
                                        <i class="fas fa-bell-slash"></i>
                                        <h4>All Caught Up!</h4>
                                        <p>No new notifications at the moment.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@if(isset($error))
<div class="alert alert-danger mt-3">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
</div>
@endif
@endsection

@section('styles')
<style>
/* Sidebar-Matching Dashboard Theme */
:root {
    /* Primary Colors (Matching Sidebar) */
    --primary-color: #32bdea;
    --secondary-color: #c850c0;
    --success-color: #2ed573;
    --warning-color: #ffa502;
    --info-color: #1e90ff;
    --danger-color: #ff4757;
    
    /* Gradients using sidebar colors */
    --primary-gradient: linear-gradient(135deg, #32bdea 0%, #c850c0 100%);
    --success-gradient: linear-gradient(135deg, #2ed573 0%, #1e90ff 100%);
    --warning-gradient: linear-gradient(135deg, #ffa502 0%, #ff4757 100%);
    --info-gradient: linear-gradient(135deg, #1e90ff 0%, #32bdea 100%);
    
    /* Background Colors (Matching Sidebar) */
    --bg-body: #f0f2f5;
    --bg-light: #f8f9fa;
    --bg-white: #ffffff;
    --bg-card: #ffffff;
    
    /* Text Colors (Matching Sidebar) */
    --text-primary: #555;
    --text-secondary: #6c757d;
    --text-muted: #888;
    --text-white: #ffffff;
    --text-dark: #1a2a3a;
    
    /* Border and Shadow (Matching Sidebar) */
    --border-color: #e9ecef;
    --border-light: #f1f5f9;
    --card-shadow: 0 0 10px rgba(0,0,0,0.05);
    --hover-shadow: 0 5px 15px rgba(0,0,0,0.1);
    --active-bg: rgba(50, 189, 234, 0.05);
    --active-bg-strong: rgba(50, 189, 234, 0.1);
    
    /* Interactive Elements */
    --border-radius: 10px;
    --border-radius-sm: 8px;
    --transition: all 0.3s ease;
    --hover-transform: translateY(-3px);
    
    /* Flip book colors */
    --book-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    --page-gradient: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

/* Sidebar-Matching Body Styling */
body {
    background: var(--bg-body) !important;
    font-family: 'Poppins', sans-serif;
    color: var(--text-primary);
    line-height: 1.6;
}

.enhanced-dashboard {
    padding: 0;
    background: var(--bg-light);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

/* Subtle gradient overlay using sidebar colors */
.enhanced-dashboard::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 80%, rgba(50, 189, 234, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(200, 80, 192, 0.02) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

/* Dashboard Customization Panel */
.dashboard-customization {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
    z-index: 1050;
    transition: right 0.3s ease;
}

.dashboard-customization.active {
    right: 0;
}

.customization-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.customization-content {
    padding: 20px;
}

.widget-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}

.widget-toggle input {
    display: none;
}

.toggle-slider {
    position: relative;
    width: 50px;
    height: 24px;
    background: #cbd5e0;
    border-radius: 12px;
    transition: var(--transition);
    cursor: pointer;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    top: 2px;
    left: 2px;
    transition: var(--transition);
}

input:checked + .toggle-slider {
    background: #667eea;
}

input:checked + .toggle-slider::before {
    transform: translateX(26px);
}

/* Enhanced Header */
.dashboard-header-enhanced {
    position: relative;
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 30px;
    overflow: hidden;
    z-index: 1;
}

.header-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
}

.header-content {
    padding: 30px;
}

.welcome-section {
    margin-bottom: 0;
}

.dashboard-title-enhanced {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.title-icon {
    font-size: 1.8rem;
}

.dashboard-subtitle-enhanced {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 16px;
}

.status-indicators {
    display: flex;
    gap: 12px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-online {
    background: #d4edda;
    color: #155724;
}

.status-centre {
    background: #e7f3ff;
    color: #0056b3;
}


.search-result-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.search-result-item:hover {
    background: #f8fafc;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-loading, .search-no-results {
    padding: 16px;
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    transition: var(--transition);
}

.btn-icon:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-2px);
}

/* Unified Smart Notifications */
.unified-notifications-bar {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 0;
    border: 1px solid var(--border-light);
    overflow: hidden;
}

.notification-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.notification-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.notification-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    margin-left: 10px;
    font-weight: 700;
}

.notification-actions {
    display: flex;
    gap: 8px;
}

.notification-actions .btn {
    color: white;
    border-color: rgba(255,255,255,0.3);
    font-size: 0.8rem;
    padding: 6px 12px;
}

.notification-actions .btn:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.5);
    color: white;
}

.notification-carousel {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    border-radius: 12px;
    border-left: 4px solid;
    transition: var(--transition);
    position: relative;
    background: #fafbfc;
    border: 1px solid #e2e8f0;
}

.notification-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.system-alert {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border-left-color: #e53e3e;
}

.user-notification {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    border-left-color: #3182ce;
}

.notification-priority {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

.priority-high {
    background: #fed7d7;
    color: #c53030;
}

.priority-normal {
    background: #e6fffa;
    color: #319795;
}

.notification-header-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.notification-type-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #4a5568;
}

.notification-time {
    font-size: 0.75rem;
    color: #718096;
}

.notification-actions-inline {
    margin-top: 12px;
}

.notification-actions-inline .btn {
    font-size: 0.8rem;
    padding: 6px 12px;
}

.notification-warning {
    background: #fffbf0;
    border-left-color: #f6ad55;
}

.notification-danger {
    background: #fed7d7;
    border-left-color: #fc8181;
}

.notification-info {
    background: #ebf8ff;
    border-left-color: #63b3ed;
}

.notification-icon {
    margin-right: 12px;
    font-size: 1.2rem;
}

.notification-content {
    flex: 1;
}

.notification-message {
    font-size: 0.9rem;
    margin: 0;
}

.notification-action {
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    margin-left: 8px;
}

.notification-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: var(--transition);
}

.notification-close:hover {
    background: rgba(0, 0, 0, 0.1);
}

/* Enhanced Quick Stats */
.quick-stats-enhanced .row {
    margin: -8px;
}

.quick-stats-enhanced .row > div {
    padding: 8px;
}

.stat-card-enhanced {
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    overflow: hidden;
    cursor: pointer;
    position: relative;
    height: 140px;
    z-index: 1;
}

.stat-card-enhanced:hover {
    transform: var(--hover-transform);
    box-shadow: var(--hover-shadow);
}

.stat-card-content {
    padding: 24px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    height: 100%;
}

.stat-icon-enhanced {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-card-primary .stat-icon-enhanced {
    background: var(--primary-gradient);
    color: var(--text-white);
}

.stat-card-success .stat-icon-enhanced {
    background: var(--success-gradient);
    color: var(--text-white);
}

.stat-card-info .stat-icon-enhanced {
    background: var(--info-gradient);
    color: var(--text-white);
}

.stat-card-warning .stat-icon-enhanced {
    background: var(--warning-gradient);
    color: var(--text-white);
}

.stat-details {
    flex: 1;
    min-width: 0;
}

.stat-number-enhanced {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-label-enhanced {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.stat-trend-enhanced {
    font-size: 0.75rem;
    color: #48bb78;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-sparkline {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 100%);
}

.drill-down-indicator {
    position: absolute;
    top: 12px;
    right: 12px;
    color: var(--text-secondary);
    font-size: 0.8rem;
    opacity: 0;
    transition: var(--transition);
}

.stat-card-enhanced:hover .drill-down-indicator {
    opacity: 1;
}

/* Dashboard Widgets */
.dashboard-widget {
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 24px;
    overflow: hidden;
    transition: var(--transition);
    z-index: 1;
}

.dashboard-widget:hover {
    box-shadow: var(--hover-shadow);
    transform: var(--hover-transform);
}

.widget-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-light);
}

.widget-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.widget-content {
    padding: 24px;
}

/* Live Indicator */
.live-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.7rem;
    color: #48bb78;
    font-weight: 600;
    text-transform: uppercase;
}

.live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #48bb78;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Enhanced Sessions Grid */
.sessions-grid-enhanced {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}

.session-card-enhanced {
    background: #fafbfc;
    border-radius: 12px;
    padding: 18px;
    border-left: 4px solid #48bb78;
    transition: var(--transition);
    position: relative;
}

.session-card-enhanced:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.session-status-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 12px 12px 0 0;
}

.status-ongoing {
    background: #48bb78;
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.session-activity {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.3;
}

.session-status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: capitalize;
}

.session-details {
    margin-bottom: 16px;
}

.session-info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.session-info-item i {
    width: 14px;
    color: #667eea;
}

.session-actions {
    display: flex;
    gap: 8px;
}

/* Enhanced Activity Timeline */
.activity-timeline-enhanced {
    position: relative;
}

.timeline-item-enhanced {
    display: flex;
    padding-bottom: 24px;
    position: relative;
}

.timeline-item-enhanced:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 10px;
    top: 24px;
    bottom: -12px;
    width: 2px;
    background: linear-gradient(to bottom, #e2e8f0, transparent);
}

.timeline-marker-enhanced {
    margin-right: 16px;
    position: relative;
}

.marker-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-gradient);
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
}

.timeline-content-enhanced {
    flex: 1;
    min-width: 0;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.timeline-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.3;
}

.timeline-time {
    font-size: 0.8rem;
    color: var(--text-secondary);
    white-space: nowrap;
}

.timeline-status {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.type-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: capitalize;
}

.type-rehabilitation {
    background: #e6fffa;
    color: #065f46;
    border: 1px solid #10b981;
}

.type-academic {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #3b82f6;
}

.type-general {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #9ca3af;
}

/* Enhanced Quick Actions */
.quick-actions-enhanced {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.action-category {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
}

.action-category:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.category-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.quick-action-enhanced {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px 12px;
    background: var(--bg-light);
    border-radius: var(--border-radius-sm);
    text-decoration: none;
    color: var(--text-primary);
    transition: var(--transition);
    border: 1px solid var(--border-color);
    text-align: center;
}

.quick-action-enhanced:hover {
    color: var(--primary-color);
    background: var(--active-bg);
    transform: var(--hover-transform);
    text-decoration: none;
    box-shadow: var(--hover-shadow);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius-sm);
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 1.2rem;
    color: var(--primary-color);
    transition: var(--transition);
}

.quick-action-enhanced:hover .action-icon {
    background: rgba(50, 189, 234, 0.1);
    color: var(--primary-color);
}

.action-label {
    font-size: 0.8rem;
    font-weight: 500;
    line-height: 1.2;
}

/* Enhanced Notifications */
.notifications-list-enhanced {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item-enhanced {
    display: flex;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}

.notification-item-enhanced:last-child {
    border-bottom: none;
}

.notification-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #667eea;
    margin-right: 12px;
    margin-top: 6px;
    flex-shrink: 0;
}

.notification-content-enhanced {
    flex: 1;
    min-width: 0;
}

.notification-dismiss {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    opacity: 0;
    transition: var(--transition);
}

.notification-item-enhanced:hover .notification-dismiss {
    opacity: 1;
}

.notification-count {
    background: #fc8181;
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 0.7rem;
    margin-left: 8px;
}

/* Additional Notifications Widget */
.additional-notifications-widget .widget-content {
    padding: 16px;
}

.notifications-summary {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.notification-stats {
    display: flex;
    justify-content: space-around;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    text-align: center;
}

.stat-item i {
    font-size: 1.2rem;
}

.quick-actions-notifications {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.quick-actions-notifications .btn {
    flex: 1;
    font-size: 0.8rem;
    padding: 8px 12px;
}

/* Enhanced Calendar Events */
.calendar-events-enhanced {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.calendar-event-enhanced {
    display: flex;
    align-items: center;
    padding: 12px;
    background: #fafbfc;
    border-radius: 8px;
    transition: var(--transition);
}

.calendar-event-enhanced:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.event-date-enhanced {
    text-align: center;
    margin-right: 16px;
    min-width: 50px;
}

.event-day {
    display: block;
    font-size: 0.7rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    font-weight: 600;
}

.event-date-num {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.event-details-enhanced {
    flex: 1;
    min-width: 0;
}

.event-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 2px;
    color: var(--text-primary);
    line-height: 1.3;
}

.event-time {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin: 0;
}

.event-status-enhanced {
    margin-left: 12px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-primary {
    background: #667eea;
}

.status-success {
    background: #48bb78;
}

.status-info {
    background: #4299e1;
}

.status-secondary {
    background: #a0aec0;
}

/* Mobile Bottom Navigation */
.mobile-nav-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e2e8f0;
    display: flex;
    z-index: 1000;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

.mobile-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px 8px;
    color: var(--text-secondary);
    font-size: 0.7rem;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
}

.mobile-nav-item.active {
    color: #667eea;
}

.mobile-nav-item i {
    font-size: 1.2rem;
    margin-bottom: 4px;
}

.mobile-badge {
    position: absolute;
    top: 8px;
    right: 20%;
    background: #fc8181;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 0.6rem;
    min-width: 16px;
    text-align: center;
}

/* Counter Animation */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.counter {
    animation: countUp 0.5s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
    .enhanced-dashboard {
        padding-bottom: 80px;
    }
    
    .header-content {
        padding: 20px;
    }
    
    .dashboard-title-enhanced {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .global-search-container {
        margin-bottom: 20px;
    }
    
    .quick-stats-enhanced {
        margin-bottom: 20px;
    }
    
    .action-grid {
        grid-template-columns: 1fr;
    }
    
    .sessions-grid-enhanced {
        grid-template-columns: 1fr;
    }
    
    .dashboard-customization {
        width: 100%;
        right: -100%;
    }
}

@media (max-width: 576px) {
    .stat-card-enhanced {
        height: auto;
        min-height: 120px;
    }
    
    .stat-card-content {
        padding: 18px;
    }
    
    .widget-header {
        padding: 16px 20px;
    }
    
    .widget-content {
        padding: 20px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --text-primary: #f7fafc;
        --text-secondary: #cbd5e0;
        --bg-light: #1a202c;
    }
    
    .enhanced-dashboard {
        background: var(--bg-light);
    }
    
    .dashboard-widget,
    .stat-card-enhanced,
    .dashboard-header-enhanced {
        background: #2d3748;
        color: var(--text-primary);
    }
    
    .session-card-enhanced,
    .quick-action-enhanced,
    .calendar-event-enhanced {
        background: #4a5568;
    }
}

/* Dashboard Book Container */
.dashboard-book {
    perspective: 2000px;
    width: 100%;
    height: auto;
    position: relative;
    margin: 0 auto;
    transform-style: preserve-3d;
}

.dashboard-page {
    width: 100%;
    min-height: 600px;
    position: absolute;
    top: 0;
    left: 0;
    background: var(--page-gradient);
    border-radius: var(--border-radius);
    box-shadow: var(--book-shadow);
    backface-visibility: hidden;
    transform-origin: left center;
    transition: transform var(--flip-duration) var(--flip-easing);
    overflow: hidden;
    border: 1px solid var(--border-light);
}

.dashboard-page.active {
    position: relative;
    transform: rotateY(0deg);
    z-index: 2;
}

.dashboard-page.flipping {
    transform: rotateY(-180deg);
    z-index: 1;
}

.general-page {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.personal-page {
    background: linear-gradient(135deg, #fff5f5 0%, #f0f8ff 100%);
    transform: rotateY(180deg);
}

.personal-page.active {
    transform: rotateY(0deg);
}

/* Page Toggle Styles */
.page-toggle {
    display: flex;
    background: var(--bg-white);
    border-radius: 50px;
    padding: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid var(--border-light);
}

.toggle-btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    background: transparent;
    border-radius: 46px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 120px;
}

.toggle-btn.active {
    background: var(--primary-gradient);
    color: white;
    box-shadow: 0 2px 8px rgba(50, 189, 234, 0.3);
}

.toggle-btn:hover:not(.active) {
    background: var(--active-bg);
    color: var(--primary-color);
}

/* Personal Page Specific Styles */
.personal-page {
    padding: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    overflow-y: auto;
    max-height: 100vh;
}

.personal-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: var(--primary-gradient);
    color: white;
    border-radius: var(--border-radius);
    position: relative;
    overflow: hidden;
}

.personal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
    opacity: 0.5;
}

.personal-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 15px;
    border: 4px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    position: relative;
    z-index: 1;
}

.personal-greeting {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
}

.personal-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.personal-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.personal-stat-card {
    background: var(--bg-white);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid var(--border-light);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.personal-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-gradient);
}

.personal-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--hover-shadow);
}

.personal-stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.personal-stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: var(--primary-gradient);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.personal-stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.personal-stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.personal-widget {
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid var(--border-light);
    margin-bottom: 20px;
    overflow: hidden;
}

.personal-widget-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border-light);
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.personal-widget-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.personal-widget-content {
    padding: 25px;
}

.personal-activity-item {
    padding: 15px 0;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    gap: 15px;
}

.personal-activity-item:last-child {
    border-bottom: none;
}

.personal-activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    opacity: 0.8;
}

.personal-activity-content {
    flex: 1;
}

.personal-activity-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.personal-activity-time {
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* Modern Personal Dashboard Styles */
.personal-dashboard-redesign {
    height: 100%;
    overflow-y: auto;
    background: #f8fafc;
}

/* Hero Section */
.personal-hero-section {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 30px;
    margin-bottom: 30px;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.1;
}

.hero-pattern {
    background-image: radial-gradient(circle at 25% 25%, white 2px, transparent 2px),
                      radial-gradient(circle at 75% 75%, white 2px, transparent 2px);
    background-size: 50px 50px;
    height: 100%;
    width: 100%;
}

.hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 30px;
}

.personal-avatar-modern .avatar-container {
    position: relative;
    width: 90px;
    height: 90px;
}

.personal-avatar-modern .avatar-img,
.personal-avatar-modern .avatar-fallback {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.3);
    object-fit: cover;
}

.personal-avatar-modern .avatar-fallback {
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
}

.avatar-status {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    background: #48bb78;
    border: 3px solid white;
    border-radius: 50%;
}

.hero-text {
    flex: 1;
}

.hero-greeting {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    line-height: 1.2;
}

.greeting-time {
    color: rgba(255,255,255,0.9);
}

.user-name {
    color: white;
}

.hero-subtitle {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.8);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.hero-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-hero {
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    border: 2px solid;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    font-size: 0.9rem;
}

.btn-hero-primary {
    background: white;
    color: #667eea;
    border-color: white;
}

.btn-hero-primary:hover {
    background: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-hero-secondary {
    background: transparent;
    color: white;
    border-color: rgba(255,255,255,0.5);
    cursor: pointer;
}

.btn-hero-secondary:hover {
    background: rgba(255,255,255,0.1);
    border-color: white;
    transform: translateY(-2px);
}

/* Modern Statistics */
.personal-stats-modern {
    padding: 0 30px;
    margin-bottom: 30px;
}

.stats-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.stat-card-modern {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-primary::before { background: linear-gradient(90deg, #667eea, #764ba2); }
.stat-success::before { background: linear-gradient(90deg, #48bb78, #38a169); }
.stat-warning::before { background: linear-gradient(90deg, #ed8936, #dd6b20); }
.stat-info::before { background: linear-gradient(90deg, #4299e1, #3182ce); }

.stat-icon-modern {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 16px;
    color: white;
}

.stat-primary .stat-icon-modern { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-success .stat-icon-modern { background: linear-gradient(135deg, #48bb78, #38a169); }
.stat-warning .stat-icon-modern { background: linear-gradient(135deg, #ed8936, #dd6b20); }
.stat-info .stat-icon-modern { background: linear-gradient(135deg, #4299e1, #3182ce); }

.stat-number-modern {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label-modern {
    font-size: 1rem;
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 12px;
}

.stat-trend-modern {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #48bb78;
    font-weight: 500;
}

/* Modern Content Grid */
.personal-content-modern {
    padding: 0 30px 30px;
}

.content-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
}

.content-card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-card-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.card-header-modern {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8fafc;
}

.header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.header-text {
    flex: 1;
}

.header-text h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 4px 0;
}

.header-text p {
    font-size: 0.85rem;
    color: #718096;
    margin: 0;
}

.btn-icon-modern {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #e2e8f0;
    border: none;
    color: #4a5568;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-icon-modern:hover {
    background: #cbd5e0;
    color: #2d3748;
}

.card-content-modern {
    padding: 24px;
}

/* Activity Items */
.activity-item-modern {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.activity-item-modern:last-child {
    border-bottom: none;
}

.activity-icon-modern {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
}

.activity-rehabilitation { background: linear-gradient(135deg, #48bb78, #38a169); }
.activity-academic { background: linear-gradient(135deg, #4299e1, #3182ce); }
.activity-general { background: linear-gradient(135deg, #a0aec0, #718096); }

.activity-details-modern {
    flex: 1;
}

.activity-title-modern {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 4px;
}

.activity-meta-modern {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    color: #718096;
}

.separator {
    color: #cbd5e0;
}

.status {
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 500;
    font-size: 0.75rem;
}

.status-active { background: #c6f6d5; color: #22543d; }
.status-scheduled { background: #bee3f8; color: #2a4365; }
.status-completed { background: #d6f5d6; color: #22543d; }

.btn-sm-modern {
    padding: 6px 12px;
    border-radius: 6px;
    background: #e2e8f0;
    border: none;
    color: #4a5568;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-sm-modern:hover {
    background: #cbd5e0;
}

/* Schedule Items */
.schedule-item-modern {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.schedule-item-modern:last-child {
    border-bottom: none;
}

.schedule-date-modern {
    text-align: center;
    background: #f7fafc;
    border-radius: 10px;
    padding: 12px;
    min-width: 60px;
}

.date-day {
    font-size: 0.7rem;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
}

.date-num {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
}

.schedule-details-modern {
    flex: 1;
}

.schedule-title-modern {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 4px;
}

.schedule-time-modern {
    font-size: 0.85rem;
    color: #718096;
}

.status-dot-modern {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-primary { background: #667eea; }
.status-success { background: #48bb78; }
.status-warning { background: #ed8936; }
.status-info { background: #4299e1; }

/* Action Buttons */
.actions-grid-modern {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.action-btn-modern {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px 16px;
    border-radius: 12px;
    text-decoration: none;
    border: 2px solid;
    transition: all 0.2s ease;
    font-weight: 500;
    cursor: pointer;
}

.action-primary {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.action-primary:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.action-success {
    background: #48bb78;
    color: white;
    border-color: #48bb78;
}

.action-success:hover {
    background: #38a169;
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.action-info {
    background: #4299e1;
    color: white;
    border-color: #4299e1;
}

.action-info:hover {
    background: #3182ce;
    transform: translateY(-2px);
}

.action-warning {
    background: #ed8936;
    color: white;
    border-color: #ed8936;
}

.action-warning:hover {
    background: #dd6b20;
    transform: translateY(-2px);
}

/* Notification Items */
.notification-item-modern {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.notification-item-modern:last-child {
    border-bottom: none;
}

.notification-icon-modern {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.notification-info .notification-icon-modern {
    background: #bee3f8;
    color: #2b6cb0;
}

.notification-warning .notification-icon-modern {
    background: #fbb6ce;
    color: #c53030;
}

.notification-success .notification-icon-modern {
    background: #c6f6d5;
    color: #22543d;
}

.notification-content-modern {
    flex: 1;
}

.notification-message-modern {
    font-weight: 500;
    color: #1a202c;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notification-time-modern {
    font-size: 0.8rem;
    color: #718096;
}

.btn-dismiss-modern {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: transparent;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-dismiss-modern:hover {
    background: #e2e8f0;
    color: #4a5568;
}

/* Empty States */
.empty-state-modern {
    text-align: center;
    padding: 40px 20px;
    color: #a0aec0;
}

.empty-state-modern i {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.6;
}

.empty-state-modern h4 {
    font-size: 1.1rem;
    color: #4a5568;
    margin-bottom: 8px;
}

.empty-state-modern p {
    font-size: 0.9rem;
    color: #718096;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-book {
        perspective: 1000px;
    }
    
    .page-toggle {
        margin-bottom: 20px;
    }
    
    .toggle-btn {
        padding: 10px 15px;
        font-size: 0.8rem;
        min-width: 100px;
    }
    
    .personal-stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .personal-stat-card {
        padding: 15px;
    }
    
    .personal-widget-content {
        padding: 20px;
    }
    
    /* Modern Personal Dashboard Mobile */
    .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .hero-greeting {
        font-size: 1.8rem;
    }
    
    .personal-hero-section {
        padding: 30px 20px;
    }
    
    .personal-stats-modern,
    .personal-content-modern {
        padding: 0 20px 20px;
    }
    
    .stats-grid-modern {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .content-grid-modern {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .actions-grid-modern {
        grid-template-columns: 1fr;
    }
    
    .stat-card-modern {
        padding: 20px;
    }
    
    .stat-number-modern {
        font-size: 2rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Page switching functionality with flip book animation
function switchPage(page) {
    const generalPage = document.getElementById('generalPage');
    const personalPage = document.getElementById('personalPage');
    const generalBtn = document.getElementById('generalPageBtn');
    const personalBtn = document.getElementById('personalPageBtn');
    
    if (page === 'personal') {
        // Switch to personal page
        generalPage.classList.add('flipping');
        generalPage.classList.remove('active');
        personalPage.classList.add('active');
        personalPage.classList.remove('flipping');
        
        // Update button states
        generalBtn.classList.remove('active');
        personalBtn.classList.add('active');
        
        // Add subtle animation feedback
        personalBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            personalBtn.style.transform = 'scale(1)';
        }, 150);
        
    } else {
        // Switch to general page
        personalPage.classList.add('flipping');
        personalPage.classList.remove('active');
        generalPage.classList.add('active');
        generalPage.classList.remove('flipping');
        
        // Update button states
        generalBtn.classList.add('active');
        personalBtn.classList.remove('active');
        
        // Add subtle animation feedback
        generalBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            generalBtn.style.transform = 'scale(1)';
        }, 150);
    }
}
// Enhanced Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    initializeCounters();
    initializeNotifications();
    loadUserPreferences();
});

// Initialize dashboard functionality
function initializeDashboard() {
    // Add smooth scrolling
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            trigger: 'hover'
        });
    });
}

// Animate counters
function initializeCounters() {
    const counters = document.querySelectorAll('.counter');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const step = target / (duration / 50);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 50);
    };
    
    // Use Intersection Observer for performance
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    });
    
    counters.forEach(counter => observer.observe(counter));
}


// Dashboard customization
function toggleCustomization() {
    const panel = document.getElementById('customizationPanel');
    panel.classList.toggle('active');
}

// Widget toggle functionality
function toggleWidget(widgetId, show) {
    const widget = document.getElementById(widgetId);
    if (widget) {
        widget.style.display = show ? 'block' : 'none';
        saveUserPreferences();
    }
}

// Save user preferences
function saveUserPreferences() {
    const preferences = {
        widgets: {
            stats: document.getElementById('toggle-stats')?.checked || true,
            sessions: document.getElementById('toggle-sessions')?.checked || true,
            notifications: document.getElementById('toggle-notifications')?.checked || true,
            calendar: document.getElementById('toggle-calendar')?.checked || true
        },
        theme: 'light' // Always use vivid light theme
    };
    
    localStorage.setItem('dashboardPreferences', JSON.stringify(preferences));
}

// Load user preferences
function loadUserPreferences() {
    const preferences = JSON.parse(localStorage.getItem('dashboardPreferences') || '{}');
    
    if (preferences.widgets) {
        Object.keys(preferences.widgets).forEach(widget => {
            const toggle = document.getElementById(`toggle-${widget}`);
            const widgetElement = document.getElementById(`${widget}Widget`);
            
            if (toggle && widgetElement) {
                toggle.checked = preferences.widgets[widget];
                widgetElement.style.display = preferences.widgets[widget] ? 'block' : 'none';
            }
        });
    }
    
    // Always use vivid light theme - force remove dark mode
    document.body.classList.remove('dark-mode');
    preferences.theme = 'light';
}

// Show stat details modal
function showStatDetails(title, value) {
    const modal = new bootstrap.Modal(document.getElementById('statDetailsModal'));
    document.getElementById('statModalTitle').textContent = title + ' Details';
    
    // Mock detailed content (replace with actual data)
    const content = `
        <div class="stat-details-content">
            <div class="row">
                <div class="col-md-6">
                    <h6>Current Value</h6>
                    <p class="h3 text-primary">${value}</p>
                </div>
                <div class="col-md-6">
                    <h6>Trend Analysis</h6>
                    <p class="text-success">↗ +12% from last month</p>
                </div>
            </div>
            <div class="mt-3">
                <h6>Breakdown by Category</h6>
                <div class="progress-stacked">
                    <div class="progress-bar bg-primary" style="width: 40%">Rehabilitation (40%)</div>
                    <div class="progress-bar bg-success" style="width: 35%">Academic (35%)</div>
                    <div class="progress-bar bg-info" style="width: 25%">Other (25%)</div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('statModalContent').innerHTML = content;
    modal.show();
}

// Initialize real-time notifications
function initializeNotifications() {
    // Check for new notifications every 30 seconds
    setInterval(checkForNotifications, 30000);
}

// Check for new notifications
function checkForNotifications() {
    // Make API call for new notifications
    fetch('/api/notifications/check')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                updateNotificationBadge(data.count);
                if (data.latest) {
                    showNotificationToast(data.latest);
                }
            }
        })
        .catch(error => console.log('Notification check failed:', error));
}

// Update notification badge
function updateNotificationBadge(count) {
    const badges = document.querySelectorAll('.notification-count, .mobile-badge');
    badges.forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    });
}

// Show notification toast
function showNotificationToast(notification) {
    const toast = document.createElement('div');
    toast.className = 'toast notification-toast position-fixed top-0 end-0 m-3';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="toast-header">
            <i class="fas fa-bell text-primary me-2"></i>
            <strong class="me-auto">New Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${notification.message}
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Filter activities in timeline
function filterActivities(category) {
    const items = document.querySelectorAll('.timeline-item-enhanced');
    
    items.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        if (category === 'all' || itemCategory === category) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Refresh functions
function refreshDashboard() {
    const refreshBtn = document.querySelector('.refresh-btn i');
    refreshBtn.classList.add('fa-spin');
    
    // Simulate refresh
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function refreshSessions() {
    const sessionsWidget = document.getElementById('currentSessionsWidget');
    if (sessionsWidget) {
        const loader = document.createElement('div');
        loader.className = 'text-center p-3';
        loader.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        
        sessionsWidget.querySelector('.widget-content').appendChild(loader);
        
        // Simulate API call
        setTimeout(() => {
            loader.remove();
            // Update sessions data here
        }, 1500);
    }
}

// Dismiss notification
function dismissNotification(button) {
    const notification = button.closest('.notification-item, .notification-item-enhanced');
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    
    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Mark all notifications as read
function markAllRead() {
    const notifications = document.querySelectorAll('.notification-item-enhanced');
    notifications.forEach(notification => {
        notification.style.opacity = '0.5';
    });
    
    // Update notification count
    updateNotificationBadge(0);
}

// Mobile navigation functions
function showQuickActions() {
    const widget = document.getElementById('quickActionsWidget');
    if (widget) {
        widget.scrollIntoView({ behavior: 'smooth' });
    }
}

function showNotifications() {
    const widget = document.getElementById('notificationsWidget');
    if (widget) {
        widget.scrollIntoView({ behavior: 'smooth' });
    }
}

function showProfile() {
    window.location.href = '/profile';
}

function showFullCalendar() {
    window.location.href = '/calendar';
}

// Theme toggle (disabled - always use vivid light theme)
function toggleTheme() {
    // Force vivid light theme always
    document.body.classList.remove('dark-mode');
    localStorage.setItem('theme', 'light');
    
    // Add subtle animation feedback
    document.body.style.transform = 'scale(1.001)';
    setTimeout(() => {
        document.body.style.transform = 'scale(1)';
    }, 150);
}

// Add widget toggle event listeners
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.widget-toggle input');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const widgetId = this.id.replace('toggle-', '') + 'Widget';
            toggleWidget(widgetId, this.checked);
        });
    });
});

// Add smooth transitions for better UX
const addTransitions = () => {
    const elements = document.querySelectorAll('.stat-card-enhanced, .dashboard-widget, .session-card-enhanced');
    elements.forEach(el => {
        el.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', addTransitions);

// Unified Notifications Functions
function toggleNotificationView() {
    const carousel = document.getElementById('notificationCarousel');
    carousel.classList.toggle('expanded');
    
    if (carousel.classList.contains('expanded')) {
        carousel.style.maxHeight = 'none';
    } else {
        carousel.style.maxHeight = '400px';
    }
}

function showAllNotifications() {
    // Redirect to notifications page or show modal
    window.location.href = '/notifications';
}

function openNotificationCenter() {
    // Open notification settings modal
    alert('Notification settings would open here');
}

// Modern Personal Dashboard Functions
function initializePersonalCounters() {
    const counters = document.querySelectorAll('.stat-number-modern[data-count]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 50);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 50);
    });
}

function refreshActivities() {
    const btn = event.target;
    btn.classList.add('fa-spin');
    
    // Simulate refresh
    setTimeout(() => {
        btn.classList.remove('fa-spin');
        // Could add API call here to refresh activities
    }, 1500);
}

function openFullCalendar() {
    window.location.href = '/calendar';
}

function openReports() {
    window.location.href = '/reports';
}

function openHelp() {
    window.location.href = '/help';
}

function openQuickSettings() {
    // Open settings modal or redirect
    alert('Quick settings would open here');
}

function markAllNotificationsRead() {
    const notifications = document.querySelectorAll('.notification-item-modern');
    notifications.forEach(notification => {
        notification.style.opacity = '0.6';
    });
}

function dismissNotificationPersonal(button) {
    const notification = button.closest('.notification-item-modern');
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    
    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Initialize personal dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePersonalCounters();
});
</script>
@endsection