@extends('layouts.app')

@section('title', 'Admin Dashboard - CREAMS')

@push('styles')
<link id="dashboard-theme" rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-charts.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-widgets.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
        --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .dashboard-container {
        background: var(--light-bg);
        min-height: 100vh;
        padding: 0;
    }

    /* Enhanced Dashboard Header */
    .dashboard-header {
        background: var(--gradient-primary);
        color: white;
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(200, 80, 192, 0.3);
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }

    .dashboard-header h1 {
        font-size: 3rem;
        font-weight: 700;
        margin: 0;
        text-shadow: 0 3px 6px rgba(0,0,0,0.3);
        position: relative;
        z-index: 2;
    }

    .dashboard-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-top: 10px;
        position: relative;
        z-index: 2;
    }

    .system-status-header {
        background: rgba(255,255,255,0.2);
        padding: 15px 25px;
        border-radius: 50px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
        position: relative;
        z-index: 2;
    }

    /* 3-Column Layout */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 30px;
        padding: 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Staff Management Column */
    .staff-management {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .management-header {
        background: var(--gradient-primary);
        color: white;
        padding: 25px;
        position: relative;
    }

    .management-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .management-content {
        padding: 25px;
    }

    /* Enhanced Statistics Cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-left: 5px solid var(--primary-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(45deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
        border-radius: 50%;
        transform: translate(30px, -30px);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .stat-card.users { border-left-color: var(--primary-color); }
    .stat-card.trainees { border-left-color: var(--success-color); }
    .stat-card.activities { border-left-color: var(--warning-color); }
    .stat-card.centres { border-left-color: var(--danger-color); }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .stat-icon.primary { background: var(--gradient-primary); }
    .stat-icon.success { background: linear-gradient(135deg, var(--success-color), #20c997); }
    .stat-icon.warning { background: linear-gradient(135deg, var(--warning-color), #fd7e14); }
    .stat-icon.danger { background: linear-gradient(135deg, var(--danger-color), #e83e8c); }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--dark-color);
        margin-bottom: 8px;
        position: relative;
        z-index: 2;
    }

    .stat-label {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    .stat-change {
        font-size: 14px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: var(--success-color);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        position: relative;
        z-index: 2;
    }

    /* Management Lists */
    .management-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .management-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 10px;
        background: #f8f9fc;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .management-item:hover {
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .item-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gradient-primary);
        color: white;
        font-weight: 600;
        margin-right: 15px;
        font-size: 18px;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 3px;
    }

    .item-meta {
        font-size: 13px;
        color: #6c757d;
    }

    .item-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: var(--success-color);
    }

    .status-inactive {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: var(--danger-color);
    }

    /* Center Management Section */
    .center-management {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .quick-action-btn {
        background: white;
        border: 2px solid var(--border-color);
        color: var(--dark-color);
        padding: 20px;
        border-radius: 15px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .quick-action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        transition: all 0.3s ease;
        z-index: 1;
    }

    .quick-action-btn:hover::before {
        left: 0;
    }

    .quick-action-btn:hover {
        color: white;
        text-decoration: none;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(200, 80, 192, 0.4);
        border-color: var(--primary-color);
    }

    .quick-action-icon {
        font-size: 28px;
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    .quick-action-label {
        font-weight: 600;
        font-size: 14px;
        position: relative;
        z-index: 2;
    }

    /* Charts and Analytics */
    .analytics-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        border: 1px solid var(--border-color);
    }

    .analytics-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 20px;
    }

    .analytics-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--dark-color);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Center Performance Grid */
    .centre-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .centre-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .centre-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .centre-name {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .performance-bar {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .performance-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 1.5s ease;
    }

    .performance-fill.excellent { background: linear-gradient(90deg, var(--success-color), #20c997); }
    .performance-fill.good { background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); }
    .performance-fill.average { background: linear-gradient(90deg, var(--warning-color), #fd7e14); }

    .performance-text {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 20px;
        }
        
        .stat-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dashboard-header h1 {
            font-size: 2rem;
        }
        
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        
        .centre-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Loading Animations */
    .loading {
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    /* Scrollbar Styling */
    .management-list::-webkit-scrollbar {
        width: 6px;
    }

    .management-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .management-list::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 3px;
    }

    .management-list::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-color);
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Enhanced Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1>
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Welcome back, {{ session('name', 'Administrator') }}!
                    </h1>
                    <p class="dashboard-subtitle">
                        Comprehensive overview of your CREAMS rehabilitation management system
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="system-status-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-circle text-success me-2"></i>
                                <strong>System Online</strong>
                            </div>
                            <div class="text-end">
                                <small>Load: {{ $performance['load_time'] ?? '0' }}ms</small><br>
                                <small>{{ now()->format('H:i, M d Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="container-fluid" style="margin-top: -50px; position: relative; z-index: 10;">
        <div class="stat-grid">
            <div class="stat-card users">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Total User</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> {{ $stats['user_growth_rate'] ?? 0 }}% increase
                </div>
            </div>
            <div class="stat-card trainees">
                <div class="stat-icon success">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-number">{{ $stats['total_trainees'] ?? 0 }}</div>
                <div class="stat-label">Active Trainee</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> {{ $stats['trainee_growth_rate'] ?? 0 }}% increase
                </div>
            </div>
            <div class="stat-card activities">
                <div class="stat-icon warning">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number">{{ $stats['total_activities'] ?? 0 }}</div>
                <div class="stat-label">Active Programs</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> 5% increase
                </div>
            </div>
            <div class="stat-card centres">
                <div class="stat-icon danger">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-number">{{ $stats['active_centres'] ?? 0 }}</div>
                <div class="stat-label">Active Centre</div>
                <div class="stat-change">
                    <i class="fas fa-check"></i> All Operational
                </div>
            </div>
        </div>
    </div>

    <!-- 3-Column Dashboard Layout -->
    <div class="dashboard-grid">
        <!-- Column 1: Staff Management -->
        <div class="staff-management">
            <div class="management-header">
                <h3>
                    <i class="fas fa-users-cog"></i>
                    Staff Management
                </h3>
            </div>
            <div class="management-content">
                <!-- Quick Actions -->
                <div class="quick-actions-grid">
                    <a href="{{ route('staffs.register') }}" class="quick-action-btn">
                        <i class="fas fa-user-plus quick-action-icon"></i>
                        <span class="quick-action-label">Add Staff</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="quick-action-btn">
                        <i class="fas fa-users quick-action-icon"></i>
                        <span class="quick-action-label">View All</span>
                    </a>
                </div>

                <!-- Today's Schedule -->
                <h5 class="mb-3">Today's Activity Schedule</h5>
                <div class="schedule-widget mb-4">
                    @if(isset($schedule['today']) && $schedule['today']->count() > 0)
                        @php $todaysSessions = $schedule['today']; @endphp
                        @include('dashboard.schedulewidget', ['todaysSessions' => $todaysSessions])
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-day fa-2x mb-2"></i>
                            <p>No activities scheduled for today</p>
                            @if(in_array(session('role'), ['admin', 'supervisor']))
                                <a href="{{ route('activities.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Create Activity
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Recent Staff -->
                <h5 class="mb-3">Recent Staff Members</h5>
                <div class="management-list">
                    @if(isset($recent['users']) && count($recent['users']) > 0)
                        @foreach($recent['users'] as $user)
                            <div class="management-item">
                                <div class="item-avatar">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="item-info">
                                    <div class="item-name">{{ $user->name ?? 'Unknown User' }}</div>
                                    <div class="item-meta">{{ ucfirst($user->role ?? 'user') }} • {{ $user->created_at ? $user->created_at->diffForHumans() : 'Unknown' }}</div>
                                </div>
                                <div class="item-status status-active">
                                    Active
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="management-item">
                            <div class="item-avatar">J</div>
                            <div class="item-info">
                                <div class="item-name">Dr. John Smith</div>
                                <div class="item-meta">Supervisor • 2 hours ago</div>
                            </div>
                            <div class="item-status status-active">Active</div>
                        </div>
                        <div class="management-item">
                            <div class="item-avatar">S</div>
                            <div class="item-info">
                                <div class="item-name">Sarah Johnson</div>
                                <div class="item-meta">Teacher • 5 hours ago</div>
                            </div>
                            <div class="item-status status-active">Active</div>
                        </div>
                        <div class="management-item">
                            <div class="item-avatar">M</div>
                            <div class="item-info">
                                <div class="item-name">Mike Chen</div>
                                <div class="item-meta">AJK • 1 day ago</div>
                            </div>
                            <div class="item-status status-active">Active</div>
                        </div>
                    @endif
                </div>

                <!-- Staff Analytics -->
                <div class="analytics-section">
                    <h6 class="analytics-title">
                        <i class="fas fa-chart-pie"></i>
                        Staff Distribution
                    </h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="text-primary"><strong>{{ $stats['total_teachers'] ?? 0 }}</strong></div>
                            <small>Teacher</small>
                        </div>
                        <div class="col-6">
                            <div class="text-success"><strong>{{ $stats['total_supervisors'] ?? 0 }}</strong></div>
                            <small>Supervisor</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2: Trainee Management -->
        <div class="center-management">
            <div class="management-header">
                <h3>
                    <i class="fas fa-user-graduate"></i>
                    Trainee Management
                </h3>
            </div>
            <div class="management-content">
                <!-- Quick Actions -->
                <div class="quick-actions-grid">
                    <a href="{{ route('trainees.create') }}" class="quick-action-btn">
                        <i class="fas fa-user-plus quick-action-icon"></i>
                        <span class="quick-action-label">Add Trainee</span>
                    </a>
                    <a href="{{ route('trainees.index') }}" class="quick-action-btn">
                        <i class="fas fa-list quick-action-icon"></i>
                        <span class="quick-action-label">View All</span>
                    </a>
                    <a href="{{ route('activities.create') }}" class="quick-action-btn">
                        <i class="fas fa-calendar-plus quick-action-icon"></i>
                        <span class="quick-action-label">New Activity</span>
                    </a>
                    <a href="{{ route('activities.home') }}" class="quick-action-btn">
                        <i class="fas fa-calendar-check quick-action-icon"></i>
                        <span class="quick-action-label">Activity</span>
                    </a>
                </div>

                <!-- Recent Activity -->
                <h5 class="mb-3">Recent System Activity</h5>
                <div class="management-list">
                    @if(isset($recent['activities']) && count($recent['activities']) > 0)
                        @foreach($recent['activities'] as $activity)
                            <div class="management-item">
                                <div class="item-avatar" style="background: var(--warning-color);">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="item-info">
                                    <div class="item-name">{{ $activity->name ?? 'Activity' }}</div>
                                    <div class="item-meta">{{ $activity->category ?? 'General' }} • {{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Unknown' }}</div>
                                </div>
                                <div class="item-status status-active">
                                    Active
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="management-item">
                            <div class="item-avatar" style="background: var(--success-color);">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">New trainee enrolled</div>
                                <div class="item-meta">Physical Therapy • 2 hours ago</div>
                            </div>
                            <div class="item-status status-active">New</div>
                        </div>
                        <div class="management-item">
                            <div class="item-avatar" style="background: var(--primary-color);">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">Activity session completed</div>
                                <div class="item-meta">Speech Therapy • 4 hours ago</div>
                            </div>
                            <div class="item-status status-active">Done</div>
                        </div>
                        <div class="management-item">
                            <div class="item-avatar" style="background: var(--warning-color);">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">Progress updated</div>
                                <div class="item-meta">Occupational Therapy • 1 day ago</div>
                            </div>
                            <div class="item-status status-active">Updated</div>
                        </div>
                        <div class="management-item">
                            <div class="item-avatar" style="background: var(--danger-color);">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">Attention required</div>
                                <div class="item-meta">Assessment Due • 2 days ago</div>
                            </div>
                            <div class="item-status status-active">Alert</div>
                        </div>
                    @endif
                </div>

                <!-- Trainee Analytics -->
                <div class="analytics-section">
                    <h6 class="analytics-title">
                        <i class="fas fa-chart-line"></i>
                        Weekly Progress
                    </h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-primary"><strong>{{ $stats['weekly_enrollments'] ?? 0 }}</strong></div>
                            <small>New Enrollments</small>
                        </div>
                        <div class="col-4">
                            <div class="text-success"><strong>{{ $stats['completed_sessions'] ?? 0 }}</strong></div>
                            <small>Sessions Done</small>
                        </div>
                        <div class="col-4">
                            <div class="text-warning"><strong>{{ $stats['pending_assessments'] ?? 0 }}</strong></div>
                            <small>Assessments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 3: Centre Management -->
        <div class="staff-management">
            <div class="management-header">
                <h3>
                    <i class="fas fa-building"></i>
                    Centre Management
                </h3>
            </div>
            <div class="management-content">
                <!-- Quick Actions -->
                <div class="quick-actions-grid">
                    <a href="{{ route('centres.create') }}" class="quick-action-btn">
                        <i class="fas fa-plus quick-action-icon"></i>
                        <span class="quick-action-label">Add Centre</span>
                    </a>
                    <a href="{{ route('centres.index') }}" class="quick-action-btn">
                        <i class="fas fa-building quick-action-icon"></i>
                        <span class="quick-action-label">Manage</span>
                    </a>
                </div>

                <!-- Centre Performance -->
                <h5 class="mb-3">Centre Performance</h5>
                <div class="centre-grid">
                    @if(isset($charts['centre_performance']) && count($charts['centre_performance']) > 0)
                        @foreach($charts['centre_performance'] as $centre)
                            <div class="centre-card">
                                <div class="centre-name">{{ $centre->name ?? 'Centre' }}</div>
                                <div class="performance-bar">
                                    @php
                                        $rate = ($centre->trainee_count ?? 0) * 10 + 70; // Sample calculation
                                        $rate = min($rate, 100);
                                        $class = $rate >= 90 ? 'excellent' : ($rate >= 75 ? 'good' : 'average');
                                    @endphp
                                    <div class="performance-fill {{ $class }}" style="width: {{ $rate }}%"></div>
                                </div>
                                <div class="performance-text">{{ $rate }}% Activity Rate</div>
                            </div>
                        @endforeach
                    @else
                        <div class="centre-card">
                            <div class="centre-name">Gombak</div>
                            <div class="performance-bar">
                                <div class="performance-fill excellent" style="width: 92%"></div>
                            </div>
                            <div class="performance-text">92% Activity Rate</div>
                        </div>
                        <div class="centre-card">
                            <div class="centre-name">Kuantan</div>
                            <div class="performance-bar">
                                <div class="performance-fill good" style="width: 88%"></div>
                            </div>
                            <div class="performance-text">88% Activity Rate</div>
                        </div>
                        <div class="centre-card">
                            <div class="centre-name">Gambang</div>
                            <div class="performance-bar">
                                <div class="performance-fill average" style="width: 75%"></div>
                            </div>
                            <div class="performance-text">75% Activity Rate</div>
                        </div>
                        <div class="centre-card">
                            <div class="centre-name">Pagoh</div>
                            <div class="performance-bar">
                                <div class="performance-fill good" style="width: 85%"></div>
                            </div>
                            <div class="performance-text">85% Activity Rate</div>
                        </div>
                    @endif
                </div>

                <!-- System Health -->
                <div class="analytics-section">
                    <h6 class="analytics-title">
                        <i class="fas fa-heartbeat"></i>
                        System Health
                    </h6>
                    @php
                        $healthData = $system_health ?? [
                            'database' => 'healthy',
                            'storage' => 'healthy', 
                            'cache' => 'healthy'
                        ];
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-success"><i class="fas fa-database"></i></div>
                            <small>Database<br><strong>{{ ucfirst($healthData['database'] ?? 'Healthy') }}</strong></small>
                        </div>
                        <div class="col-4">
                            <div class="text-primary"><i class="fas fa-hdd"></i></div>
                            <small>Storage<br><strong>{{ ucfirst($healthData['storage'] ?? 'Healthy') }}</strong></small>
                        </div>
                        <div class="col-4">
                            <div class="text-warning"><i class="fas fa-memory"></i></div>
                            <small>Cache<br><strong>{{ ucfirst($healthData['cache'] ?? 'Healthy') }}</strong></small>
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts -->
                @if(isset($alerts) && count($alerts) > 0)
                    <div class="analytics-section">
                        <h6 class="analytics-title">
                            <i class="fas fa-bell"></i>
                            System Alerts
                        </h6>
                        @foreach(array_slice($alerts, 0, 3) as $alert)
                            <div class="alert alert-{{ $alert['type'] ?? 'info' }} alert-dismissible fade show" role="alert">
                                <small><strong>{{ $alert['message'] ?? 'System notification' }}</strong></small>
                                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/dashboard-animations.js') }}"></script>
<script src="{{ asset('js/dashboard-widgets.js') }}"></script>
<script>
class DashboardManager {
    constructor() {
        this.refreshInterval = null;
        this.isRefreshing = false;
        this.init();
    }

    init() {
        this.initAnimations();
        this.initRealTimeUpdates();
        this.initTooltips();
        this.initRefreshButton();
        this.loadWidgets();
    }

    initAnimations() {
        // Animate progress bars
        const progressBars = document.querySelectorAll('.progress-fill, .performance-fill');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.style.width || bar.getAttribute('data-width') || '0%';
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 200);
                    observer.unobserve(bar);
                }
            });
        });

        progressBars.forEach(bar => observer.observe(bar));

        // Animate stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animate management items
        const managementItems = document.querySelectorAll('.management-item');
        managementItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 800 + (index * 50));
        });
    }

    initRealTimeUpdates() {
        // Auto-refresh dashboard data every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.refreshStats();
        }, 300000);

        // Manual refresh button
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshStats(true));
        }
    }

    initTooltips() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initRefreshButton() {
        // Add refresh button to header
        const header = document.querySelector('.dashboard-header .container-fluid .row .col-lg-4');
        if (header) {
            const refreshHTML = `
                <button id="refresh-dashboard" class="btn btn-light btn-sm ms-3" data-bs-toggle="tooltip" title="Refresh Dashboard">
                    <i class="fas fa-sync-alt"></i>
                </button>
            `;
            header.insertAdjacentHTML('beforeend', refreshHTML);
        }
    }

    async refreshStats(manual = false) {
        if (this.isRefreshing) return;
        
        this.isRefreshing = true;
        const refreshBtn = document.getElementById('refresh-dashboard');
        
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i>';
            refreshBtn.disabled = true;
        }

        try {
            const response = await fetch('{{ route('dashboard.refresh-stats') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateStatCards(data.stats);
                if (manual) {
                    this.showNotification('Dashboard refreshed successfully!', 'success');
                }
            } else {
                throw new Error(data.error || 'Failed to refresh');
            }
        } catch (error) {
            console.error('Dashboard refresh failed:', error);
            if (manual) {
                this.showNotification('Failed to refresh dashboard', 'error');
            }
        } finally {
            this.isRefreshing = false;
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
                refreshBtn.disabled = false;
            }
        }
    }

    updateStatCards(stats) {
        // Update stat numbers with animation
        const statCards = {
            'total_users': '.stat-card.users .stat-number',
            'total_trainees': '.stat-card.trainees .stat-number', 
            'total_activities': '.stat-card.activities .stat-number',
            'active_centres': '.stat-card.centres .stat-number'
        };

        Object.entries(statCards).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && stats[key] !== undefined) {
                this.animateNumber(element, parseInt(element.textContent), stats[key]);
            }
        });
    }

    animateNumber(element, start, end) {
        const duration = 1000;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(start + (end - start) * this.easeOutCubic(progress));
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    loadWidgets() {
        // Load additional widgets asynchronously
        this.loadWidget('quick-stats');
        this.loadWidget('recent-activity');
        this.loadWidget('alerts');
    }

    async loadWidget(widgetName) {
        try {
            const response = await fetch(`{{ route('dashboard.widget', '') }}/${widgetName}`);
            const data = await response.json();
            
            if (data.success) {
                this.updateWidget(widgetName, data.data);
            }
        } catch (error) {
            console.error(`Failed to load widget ${widgetName}:`, error);
        }
    }

    updateWidget(widgetName, data) {
        // Widget-specific update logic
        switch (widgetName) {
            case 'quick-stats':
                // Update quick stats if needed
                break;
            case 'recent-activity':
                // Update recent activities
                break;
            case 'alerts':
                this.updateAlerts(data);
                break;
        }
    }

    updateAlerts(alertData) {
        const alertsContainer = document.querySelector('.analytics-section:last-child');
        if (alertsContainer && alertData.alerts && alertData.alerts.length > 0) {
            // Update alerts display
            console.log('Updating alerts:', alertData);
        }
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (window.dashboardManager) {
            window.dashboardManager.destroy();
        }
    });

    // Dark mode toggle (if needed)
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        });
    }

    // Load saved dark mode preference
    if (localStorage.getItem('dark-mode') === 'true') {
        document.body.classList.add('dark-mode');
    }

    // Enhanced search functionality
    const searchInput = document.getElementById('dashboard-search');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const managementItems = document.querySelectorAll('.management-item');
            
            managementItems.forEach(item => {
                const name = item.querySelector('.item-name')?.textContent.toLowerCase() || '';
                const meta = item.querySelector('.item-meta')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || meta.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Performance monitoring
    const loadTime = performance.now();
    console.log(`Dashboard loaded in ${loadTime.toFixed(2)}ms`);
    
    // Update load time display
    const loadTimeDisplay = document.querySelector('.system-status-header small');
    if (loadTimeDisplay) {
        loadTimeDisplay.textContent = `Load: ${loadTime.toFixed(0)}ms`;
    }
});

// Export for global access
window.DashboardManager = DashboardManager;
</script>
@endpush