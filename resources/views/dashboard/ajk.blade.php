@extends('layouts.app')

@section('title', 'AJK Dashboard')

@push('styles')
<style>
    .ajk-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.1;
    }
    
    .dashboard-header .container-fluid {
        position: relative;
        z-index: 1;
    }
    
    .performance-indicator {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        backdrop-filter: blur(10px);
    }
    
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--card-color, #9f7aea);
        transition: width 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .stat-card:hover::before {
        width: 8px;
    }
    
    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--card-color, #9f7aea), var(--card-color-light, #b794f6));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        box-shadow: 0 4px 15px rgba(159, 122, 234, 0.3);
    }
    
    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
        line-height: 1;
    }
    
    .stat-label {
        color: #718096;
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
    }
    
    .stat-change {
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .stat-change.positive { color: #48bb78; }
    .stat-change.neutral { color: #a0aec0; }
    .stat-change.urgent { color: #f56565; }
    
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .main-content {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .sidebar-content {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .card-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f7fafc;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .facility-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .facility-card {
        background: #f7fafc;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        border-left: 4px solid;
    }
    
    .facility-card.excellent {
        border-color: #48bb78;
    }
    
    .facility-card.good {
        border-color: #4299e1;
    }
    
    .facility-card.needs-attention {
        border-color: #f6ad55;
    }
    
    .facility-card.critical {
        border-color: #f56565;
    }
    
    .facility-card:hover {
        background: #edf2f7;
        transform: translateY(-2px);
    }
    
    .facility-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, #9f7aea, #b794f6);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 22px;
        color: white;
    }
    
    .facility-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 15px;
    }
    
    .facility-status {
        font-size: 13px;
        color: #718096;
        margin-bottom: 12px;
    }
    
    .facility-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin: 0 auto;
    }
    
    .indicator-excellent { background: #48bb78; }
    .indicator-good { background: #4299e1; }
    .indicator-needs-attention { background: #f6ad55; }
    .indicator-critical { background: #f56565; }
    
    .maintenance-overview {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .maintenance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .maintenance-title {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }
    
    .maintenance-date {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .maintenance-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    
    .maintenance-stat {
        text-align: center;
    }
    
    .maintenance-stat-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .maintenance-stat-label {
        font-size: 12px;
        opacity: 0.8;
    }
    
    .task-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .task-item:last-child {
        border-bottom: none;
    }
    
    .task-priority {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: 600;
        font-size: 12px;
        color: white;
    }
    
    .priority-high { background: #f56565; }
    .priority-medium { background: #f6ad55; }
    .priority-low { background: #4299e1; }
    
    .task-info {
        flex: 1;
    }
    
    .task-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }
    
    .task-meta {
        font-size: 13px;
        color: #718096;
    }
    
    .task-status {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending { background: #fed7e2; color: #97266d; }
    .status-progress { background: #fefcbf; color: #744210; }
    .status-completed { background: #c6f6d5; color: #276749; }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
    }
    
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 15px;
        border: 2px solid #f7fafc;
        border-radius: 12px;
        text-decoration: none;
        color: #4a5568;
        transition: all 0.3s ease;
        background: white;
    }
    
    .action-btn:hover {
        border-color: #9f7aea;
        color: #9f7aea;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(159, 122, 234, 0.15);
        text-decoration: none;
    }
    
    .action-icon {
        font-size: 28px;
        margin-bottom: 8px;
    }
    
    .action-label {
        font-size: 13px;
        font-weight: 600;
        text-align: center;
    }
    
    .schedule-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .schedule-item:last-child {
        border-bottom: none;
    }
    
    .schedule-time {
        background: linear-gradient(135deg, #9f7aea, #b794f6);
        color: white;
        padding: 6px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
        margin-right: 12px;
        min-width: 70px;
        text-align: center;
    }
    
    .schedule-info {
        flex: 1;
    }
    
    .schedule-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 3px;
        font-size: 14px;
    }
    
    .schedule-meta {
        font-size: 12px;
        color: #718096;
    }
    
    .notification-item {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 10px;
        border-left: 4px solid;
    }
    
    .notification-item.info {
        background: #ebf8ff;
        border-color: #4299e1;
    }
    
    .notification-item.warning {
        background: #fffbeb;
        border-color: #f6ad55;
    }
    
    .notification-item.critical {
        background: #fed7d7;
        border-color: #f56565;
    }
    
    .notification-icon {
        margin-right: 12px;
        margin-top: 2px;
        font-size: 16px;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .notification-message {
        color: #718096;
        font-size: 13px;
        margin-bottom: 6px;
    }
    
    .notification-time {
        color: #a0aec0;
        font-size: 11px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .facility-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .performance-indicator {
            position: static;
            margin-top: 15px;
            text-align: center;
        }
        
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="ajk-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2 font-weight-bold">AJK Support Dashboard</h1>
                    <p class="mb-0 opacity-90">Welcome back, {{ $user['name'] ?? 'AJK' }}! Keep the centre running smoothly.</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="performance-indicator">
                        <i class="fas fa-tools mr-2"></i>
                        Load Time: {{ $performance['load_time'] ?? '0' }}ms
                        <span class="ml-2 badge badge-light">{{ $performance['cache_status'] ?? 'miss' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Statistics Grid -->
        <div class="stat-grid">
            <div class="stat-card" style="--card-color: #4299e1; --card-color-light: #63b3ed;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['active_trainees'] ?? 0 }}</div>
                <div class="stat-label">Active Trainees</div>
                <div class="stat-change positive">
                    <i class="fas fa-users"></i>
                    Currently enrolled
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #48bb78; --card-color-light: #68d391;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['today_sessions'] ?? 0 }}</div>
                <div class="stat-label">Today's Sessions</div>
                <div class="stat-change neutral">
                    <i class="fas fa-calendar-check"></i>
                    Support needed
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #f6ad55; --card-color-light: #fbb042;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['pending_tasks'] ?? 0 }}</div>
                <div class="stat-label">Pending Tasks</div>
                <div class="stat-change {{ ($stats['pending_tasks'] ?? 0) > 5 ? 'urgent' : 'neutral' }}">
                    <i class="fas fa-clipboard-list"></i>
                    {{ ($stats['pending_tasks'] ?? 0) > 5 ? 'High workload' : 'Manageable' }}
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #f56565; --card-color-light: #fc8181;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['maintenance_alerts'] ?? 0 }}</div>
                <div class="stat-label">Maintenance Alerts</div>
                <div class="stat-change {{ ($stats['maintenance_alerts'] ?? 0) > 0 ? 'urgent' : 'positive' }}">
                    @if(($stats['maintenance_alerts'] ?? 0) > 0)
                        <i class="fas fa-wrench"></i>
                        Needs attention
                    @else
                        <i class="fas fa-check-circle"></i>
                        All up to date
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Maintenance Overview -->
                <div class="maintenance-overview">
                    <div class="maintenance-header">
                        <div>
                            <div class="maintenance-title">Maintenance & Support Overview</div>
                            <div class="maintenance-date">{{ now()->format('l, F j, Y') }}</div>
                        </div>
                        <div>
                            <i class="fas fa-wrench" style="font-size: 32px; opacity: 0.8;"></i>
                        </div>
                    </div>
                    <div class="maintenance-stats">
                        <div class="maintenance-stat">
                            <div class="maintenance-stat-value">{{ $support['facilities']['total'] ?? 0 }}</div>
                            <div class="maintenance-stat-label">Total Facilities</div>
                        </div>
                        <div class="maintenance-stat">
                            <div class="maintenance-stat-value">{{ $support['equipment']['working'] ?? 0 }}</div>
                            <div class="maintenance-stat-label">Equipment Working</div>
                        </div>
                        <div class="maintenance-stat">
                            <div class="maintenance-stat-value">{{ $support['maintenance']['due_this_week'] ?? 0 }}</div>
                            <div class="maintenance-stat-label">Due This Week</div>
                        </div>
                    </div>
                </div>

                <!-- Facility Status -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Facility Status</h3>
                        <a href="{{ route('facilities.index') }}" class="btn btn-sm btn-outline-primary">Manage Facilities</a>
                    </div>
                    
                    <div class="facility-grid">
                        @if(isset($support['facilities']['list']) && count($support['facilities']['list']) > 0)
                            @foreach($support['facilities']['list'] as $facility)
                                <div class="facility-card {{ $facility['status'] ?? 'good' }}">
                                    <div class="facility-icon">
                                        <i class="fas {{ $facility['icon'] ?? 'fa-building' }}"></i>
                                    </div>
                                    <div class="facility-name">{{ $facility['name'] ?? 'Facility' }}</div>
                                    <div class="facility-status">{{ ucfirst($facility['status'] ?? 'Good') }}</div>
                                    <div class="facility-indicator indicator-{{ $facility['status'] ?? 'good' }}"></div>
                                </div>
                            @endforeach
                        @else
                            <div class="facility-card good">
                                <div class="facility-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="facility-name">Main Hall</div>
                                <div class="facility-status">Excellent</div>
                                <div class="facility-indicator indicator-excellent"></div>
                            </div>
                            <div class="facility-card good">
                                <div class="facility-icon">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                                <div class="facility-name">Gym</div>
                                <div class="facility-status">Good</div>
                                <div class="facility-indicator indicator-good"></div>
                            </div>
                            <div class="facility-card needs-attention">
                                <div class="facility-icon">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div class="facility-name">Computer Lab</div>
                                <div class="facility-status">Needs Attention</div>
                                <div class="facility-indicator indicator-needs-attention"></div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Pending Tasks</h3>
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">View All Tasks</a>
                    </div>
                    
                    @if(isset($support['pending_tasks']) && count($support['pending_tasks']) > 0)
                        @foreach(array_slice($support['pending_tasks'], 0, 5) as $task)
                            <div class="task-item">
                                <div class="task-priority priority-{{ strtolower($task['priority'] ?? 'medium') }}">
                                    {{ strtoupper(substr($task['priority'] ?? 'M', 0, 1)) }}
                                </div>
                                <div class="task-info">
                                    <div class="task-title">{{ $task['title'] ?? 'Support Task' }}</div>
                                    <div class="task-meta">
                                        {{ $task['description'] ?? 'No description available' }} • 
                                        Due: {{ isset($task['due_date']) ? $task['due_date']->format('M j') : 'TBD' }}
                                    </div>
                                </div>
                                <div class="task-status status-{{ strtolower($task['status'] ?? 'pending') }}">
                                    {{ ucfirst($task['status'] ?? 'pending') }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-3 text-success"></i>
                            <p>Great job! No pending tasks at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="sidebar-content">
                <!-- Quick Actions -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="{{ route('maintenance.create') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="action-label">Schedule Maintenance</div>
                        </a>
                        
                        <a href="{{ route('assets.index') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="action-label">Manage Assets</div>
                        </a>
                        
                        <a href="{{ route('tasks.create') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus-square"></i>
                            </div>
                            <div class="action-label">New Task</div>
                        </a>
                        
                        <a href="{{ route('reports.ajk') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="action-label">Support Reports</div>
                        </a>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Today's Support Schedule</h3>
                    </div>
                    
                    @if(isset($schedule['today']) && count($schedule['today']) > 0)
                        @foreach($schedule['today'] as $item)
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    {{ isset($item->time) ? $item->time->format('H:i') : 'TBD' }}
                                </div>
                                <div class="schedule-info">
                                    <div class="schedule-title">{{ $item->title ?? 'Support Task' }}</div>
                                    <div class="schedule-meta">{{ $item->location ?? 'Various locations' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="schedule-item">
                            <div class="schedule-time">09:00</div>
                            <div class="schedule-info">
                                <div class="schedule-title">Morning Facility Check</div>
                                <div class="schedule-meta">All areas</div>
                            </div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-time">14:00</div>
                            <div class="schedule-info">
                                <div class="schedule-title">Equipment Maintenance</div>
                                <div class="schedule-meta">Computer Lab</div>
                            </div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-time">16:00</div>
                            <div class="schedule-info">
                                <div class="schedule-title">End of Day Cleanup</div>
                                <div class="schedule-meta">Main areas</div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Notifications -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Support Notifications</h3>
                    </div>
                    
                    @if(isset($notifications) && count($notifications) > 0)
                        @foreach(array_slice($notifications, 0, 4) as $notification)
                            <div class="notification-item {{ $notification['type'] ?? 'info' }}">
                                <div class="notification-icon">
                                    @if(($notification['type'] ?? 'info') === 'critical')
                                        <i class="fas fa-exclamation-circle text-danger"></i>
                                    @elseif(($notification['type'] ?? 'info') === 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    @else
                                        <i class="fas fa-info-circle text-info"></i>
                                    @endif
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">{{ $notification['title'] ?? 'Notification' }}</div>
                                    <div class="notification-message">{{ $notification['message'] ?? 'No details available' }}</div>
                                    <div class="notification-time">{{ isset($notification['created_at']) ? $notification['created_at']->diffForHumans() : 'Just now' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="notification-item info">
                            <div class="notification-icon">
                                <i class="fas fa-info-circle text-info"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">System Status</div>
                                <div class="notification-message">All systems operating normally</div>
                                <div class="notification-time">Just now</div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Equipment Status -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Equipment Overview</h3>
                    </div>
                    
                    <div class="maintenance-stats">
                        <div class="maintenance-stat text-center mb-3">
                            <div class="maintenance-stat-value text-success">{{ $support['equipment']['working'] ?? 15 }}</div>
                            <div class="maintenance-stat-label text-muted">Working</div>
                        </div>
                        <div class="maintenance-stat text-center mb-3">
                            <div class="maintenance-stat-value text-warning">{{ $support['equipment']['maintenance'] ?? 2 }}</div>
                            <div class="maintenance-stat-label text-muted">Under Maintenance</div>
                        </div>
                        <div class="maintenance-stat text-center">
                            <div class="maintenance-stat-value text-danger">{{ $support['equipment']['broken'] ?? 1 }}</div>
                            <div class="maintenance-stat-label text-muted">Needs Repair</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Updates Indicator -->
<div id="updateIndicator" class="position-fixed" style="top: 20px; right: 20px; z-index: 1050; display: none;">
    <div class="badge badge-purple">
        <i class="fas fa-sync-alt fa-spin mr-1"></i> Updating...
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let lastUpdateTime = {{ time() }};
let updateInterval;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    startRealTimeUpdates();
    initializeStatCounters();
});

// Start real-time updates
function startRealTimeUpdates() {
    updateInterval = setInterval(fetchUpdates, 90000); // Every 90 seconds for AJK
}

// Fetch real-time updates
function fetchUpdates() {
    const indicator = document.getElementById('updateIndicator');
    indicator.style.display = 'block';
    
    fetch(`{{ route('dashboard.updates') }}?last_update=${lastUpdateTime}&include_stats=true`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.stats) {
                    updateStatValues(data.stats);
                }
                if (data.updates && data.updates.length > 0) {
                    showNotifications(data.updates);
                }
                lastUpdateTime = data.timestamp;
            }
        })
        .catch(error => {
            console.error('Update fetch failed:', error);
        })
        .finally(() => {
            indicator.style.display = 'none';
        });
}

// Update stat values with animation
function updateStatValues(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            const currentValue = parseInt(element.textContent.replace(/[^0-9]/g, ''));
            const newValue = stats[key];
            
            if (currentValue !== newValue) {
                animateValue(element, currentValue, newValue, 1000);
            }
        }
    });
}

// Animate number changes
function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            element.textContent = Math.floor(end);
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Initialize stat counters
function initializeStatCounters() {
    const statElements = document.querySelectorAll('.stat-value');
    statElements.forEach(element => {
        const finalValue = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        element.textContent = '0';
        animateValue(element, 0, finalValue, 1500);
    });
}

// Show notifications
function showNotifications(updates) {
    updates.forEach(update => {
        showNotification(update.message, update.type);
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 80px; right: 20px; z-index: 1050; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
@endpush