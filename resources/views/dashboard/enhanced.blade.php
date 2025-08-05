@extends('layouts.app')

@section('title', 'Enhanced Dashboard - CREAMS')

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
                        <!-- Global Search -->
                        <div class="global-search-container">
                            <div class="search-input-wrapper">
                                <input type="text" class="global-search-input" placeholder="Search trainees, activities..." id="globalSearch">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                            <div class="search-results" id="searchResults"></div>
                        </div>
                        
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

    <!-- Smart Notifications Bar -->
    @if(isset($system_alerts) && count($system_alerts) > 0)
    <div class="smart-notifications-bar mb-4">
        <div class="notification-carousel" id="notificationCarousel">
            @foreach($system_alerts as $alert)
            <div class="notification-item notification-{{ $alert['type'] }}" data-type="{{ $alert['type'] }}">
                <div class="notification-icon">
                    <i class="{{ $alert['icon'] }}"></i>
                </div>
                <div class="notification-content">
                    <span class="notification-message">{{ $alert['message'] }}</span>
                    @if(isset($alert['action_url']))
                    <a href="{{ $alert['action_url'] }}" class="notification-action">{{ $alert['action_text'] }}</a>
                    @endif
                </div>
                <button class="notification-close" onclick="dismissNotification(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endforeach
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
                            <div class="timeline-item-enhanced" data-category="{{ strtolower($activity['status']) }}">
                                <div class="timeline-marker-enhanced">
                                    <div class="marker-dot"></div>
                                </div>
                                <div class="timeline-content-enhanced">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                        <span class="timeline-time">{{ $activity['time'] }}</span>
                                    </div>
                                    <div class="timeline-status">
                                        <span class="status-badge status-{{ $activity['status'] }}">
                                            {{ ucfirst($activity['status']) }}
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

                <!-- Smart Notifications Panel -->
                @if(isset($notifications) && count($notifications) > 0)
                <div class="dashboard-widget notifications-widget" id="notificationsWidget">
                    <div class="widget-header">
                        <h5 class="widget-title">
                            <span class="title-icon">🔔</span>
                            Notifications
                            <span class="notification-count">{{ count($notifications) }}</span>
                        </h5>
                        <div class="widget-actions">
                            <button class="btn btn-sm btn-outline-secondary" onclick="markAllRead()">
                                Mark All Read
                            </button>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="notifications-list-enhanced">
                            @foreach($notifications as $notification)
                            <div class="notification-item-enhanced notification-{{ $notification['type'] }}" 
                                 data-notification-id="{{ $notification['id'] ?? '' }}">
                                <div class="notification-indicator"></div>
                                <div class="notification-content-enhanced">
                                    <p class="notification-message">{{ $notification['message'] }}</p>
                                    <small class="notification-time">{{ $notification['time'] ?? 'recently' }}</small>
                                </div>
                                <button class="notification-dismiss" onclick="dismissNotification(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endforeach
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

@if(isset($error))
<div class="alert alert-danger mt-3">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
</div>
@endif
@endsection

@section('styles')
<style>
/* Enhanced Dashboard Styles */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    --warning-gradient: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
    --info-gradient: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --hover-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    --border-radius: 16px;
    --text-primary: #2d3748;
    --text-secondary: #718096;
    --bg-light: #f8fafc;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-dashboard {
    padding: 0;
    background: var(--bg-light);
    min-height: 100vh;
    position: relative;
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
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 30px;
    overflow: hidden;
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

/* Global Search */
.global-search-container {
    position: relative;
    margin-bottom: 15px;
}

.search-input-wrapper {
    position: relative;
}

.global-search-input {
    width: 100%;
    padding: 12px 40px 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    font-size: 0.9rem;
    transition: var(--transition);
    background: #f8fafc;
}

.global-search-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: var(--hover-shadow);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
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

/* Smart Notifications Bar */
.smart-notifications-bar {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 20px;
}

.notification-carousel {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notification-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 8px;
    border-left: 4px solid;
    transition: var(--transition);
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
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    border: none;
    overflow: hidden;
    cursor: pointer;
    position: relative;
    height: 140px;
}

.stat-card-enhanced:hover {
    transform: translateY(-4px);
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
    color: white;
}

.stat-card-success .stat-icon-enhanced {
    background: var(--success-gradient);
    color: white;
}

.stat-card-info .stat-icon-enhanced {
    background: var(--info-gradient);
    color: white;
}

.stat-card-warning .stat-icon-enhanced {
    background: var(--warning-gradient);
    color: white;
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
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 24px;
    overflow: hidden;
    transition: var(--transition);
}

.dashboard-widget:hover {
    box-shadow: var(--hover-shadow);
}

.widget-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.widget-title {
    font-size: 1.1rem;
    font-weight: 600;
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
    background: #fafbfc;
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-primary);
    transition: var(--transition);
    border: 2px solid transparent;
    text-align: center;
}

.quick-action-enhanced:hover {
    background: var(--primary-gradient);
    color: white;
    transform: translateY(-2px);
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(102, 126, 234, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 1.2rem;
    color: #667eea;
    transition: var(--transition);
}

.quick-action-enhanced:hover .action-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
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
</style>
@endsection

@section('scripts')
<script>
// Enhanced Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    initializeCounters();
    initializeSearch();
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

// Global search functionality
function initializeSearch() {
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
}

// Perform search
function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    
    // Show loading state
    searchResults.innerHTML = '<div class="search-loading">Searching...</div>';
    searchResults.style.display = 'block';
    
    // Simulate API call (replace with actual search endpoint)
    setTimeout(() => {
        const mockResults = [
            { type: 'trainee', name: 'Ahmad Rahman', id: '123' },
            { type: 'activity', name: 'Physical Therapy', id: '456' },
            { type: 'session', name: 'Morning Session - Room A', id: '789' }
        ].filter(item => item.name.toLowerCase().includes(query.toLowerCase()));
        
        if (mockResults.length > 0) {
            searchResults.innerHTML = mockResults.map(result => 
                `<div class="search-result-item" onclick="navigateToResult('${result.type}', '${result.id}')">
                    <i class="fas fa-${getResultIcon(result.type)} me-2"></i>
                    ${result.name}
                    <small class="text-muted">${result.type}</small>
                </div>`
            ).join('');
        } else {
            searchResults.innerHTML = '<div class="search-no-results">No results found</div>';
        }
    }, 500);
}

// Get icon for search result type
function getResultIcon(type) {
    const icons = {
        'trainee': 'user-graduate',
        'activity': 'tasks',
        'session': 'calendar-alt',
        'user': 'user'
    };
    return icons[type] || 'circle';
}

// Navigate to search result
function navigateToResult(type, id) {
    const routes = {
        'trainee': '/trainees/',
        'activity': '/activities/',
        'session': '/sessions/',
        'user': '/users/'
    };
    
    if (routes[type]) {
        window.location.href = routes[type] + id;
    }
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
            stats: document.getElementById('toggle-stats').checked,
            sessions: document.getElementById('toggle-sessions').checked,
            notifications: document.getElementById('toggle-notifications').checked,
            calendar: document.getElementById('toggle-calendar').checked
        },
        theme: document.body.classList.contains('dark-mode') ? 'dark' : 'light'
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
    
    if (preferences.theme === 'dark') {
        document.body.classList.add('dark-mode');
    }
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
    // Simulate API call for new notifications
    fetch('/api/notifications/check')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                updateNotificationBadge(data.count);
                showNotificationToast(data.latest);
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
    toast.className = 'toast notification-toast';
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
    document.getElementById('quickActionsWidget').scrollIntoView({ behavior: 'smooth' });
}

function showNotifications() {
    document.getElementById('notificationsWidget').scrollIntoView({ behavior: 'smooth' });
}

function showProfile() {
    window.location.href = '/profile';
}

function showFullCalendar() {
    window.location.href = '/calendar';
}

// Theme toggle
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
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
</script>
@endsection