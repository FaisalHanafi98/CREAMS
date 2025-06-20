{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', ucfirst($role) . ' Dashboard - CREAMS')

@section('content')
<div class="dashboard-container" data-role="{{ $role }}">
    {{-- Dashboard Header --}}
    <div class="dashboard-header">
        <div class="dashboard-welcome">
            <h1 class="dashboard-title">Welcome back, <span class="user-name">{{ session('name') }}</span></h1>
            <p class="dashboard-subtitle">{{ ucfirst($role) }} Dashboard - {{ Carbon\Carbon::now()->format('l, F j, Y') }}</p>
        </div>
        <div class="dashboard-actions">
            <button class="btn-refresh" onclick="DashboardManager.refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn-customize" onclick="DashboardManager.openCustomization()">
                <i class="fas fa-cog"></i> Customize
            </button>
        </div>
    </div>

    {{-- System Alerts (if any) --}}
    @if(isset($systemAlerts) && count($systemAlerts) > 0)
    <div class="system-alerts">
        @foreach($systemAlerts as $alert)
        <div class="alert alert-{{ $alert['type'] ?? 'info' }} alert-dismissible">
            <i class="fas fa-{{ $alert['icon'] ?? 'info-circle' }}"></i>
            <span>{{ $alert['message'] ?? 'System notification' }}</span>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Quick Stats Section --}}
    <div class="stats-section">
        @switch($role)
            @case('admin')
                @include('dashboard.partials.admin-stats', ['stats' => $stats])
                @break
            @case('supervisor')
                @include('dashboard.partials.supervisor-stats', ['stats' => $stats])
                @break
            @case('teacher')
                @include('dashboard.partials.teacher-stats', ['stats' => $stats])
                @break
            @case('trainee')
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['my_activities'] ?? 0 }}</h3>
                        <p class="stat-label">My Activities</p>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['completed_sessions'] ?? 0 }}</h3>
                        <p class="stat-label">Completed Sessions</p>
                    </div>
                </div>
                @break
            @case('ajk')
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['total_events'] ?? 0 }}</h3>
                        <p class="stat-label">Events</p>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['active_volunteers'] ?? 0 }}</h3>
                        <p class="stat-label">Volunteers</p>
                    </div>
                </div>
                @break
            @case('parent')
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon"><i class="fas fa-child"></i></div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['child_progress'] ?? 0 }}%</h3>
                        <p class="stat-label">Child Progress</p>
                    </div>
                </div>
                <div class="stat-card stat-card-info">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['upcoming_sessions'] ?? 0 }}</h3>
                        <p class="stat-label">Upcoming Sessions</p>
                    </div>
                </div>
                @break
        @endswitch
    </div>

    {{-- Main Dashboard Content --}}
    <div class="dashboard-content">
        <div class="row">
            {{-- Left Column - Main Content --}}
            <div class="col-lg-8">
                {{-- Recent Activities Card - Role-based visibility --}}
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clock"></i> Recent Activities
                        </h2>
                        <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if(count($recentActivities) > 0)
                            <div class="activity-list">
                                @foreach($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <h4 class="activity-title">{{ $activity['title'] ?? $activity['activity_name'] ?? 'Untitled Activity' }}</h4>
                                        <p class="activity-meta">
                                            <span class="activity-date">{{ $activity['date'] ?? $activity['created_at'] ?? 'No date' }}</span>
                                            @if($role === 'admin' || $role === 'supervisor')
                                                {{-- Admin/Supervisor can see all details --}}
                                                <span class="activity-centre">{{ $activity['centre'] ?? 'Unknown Centre' }}</span>
                                                <span class="activity-teacher">by {{ $activity['teacher'] ?? 'Unknown Teacher' }}</span>
                                            @elseif($role === 'teacher')
                                                {{-- Teachers can only see their own activities --}}
                                                @if(isset($activity['teacher_id']) && $activity['teacher_id'] == session('id'))
                                                    <span class="activity-status">{{ $activity['status'] ?? 'Active' }}</span>
                                                @endif
                                            @endif
                                        </p>
                                    </div>
                                    <div class="activity-status">
                                        <span class="badge badge-{{ $activity['status'] === 'completed' ? 'success' : 'primary' }}">
                                            {{ ucfirst($activity['status'] ?? 'active') }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>No recent activities found</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- User Access Analytics - Role-based data --}}
                <div class="content-card mt-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-users"></i> User Access Analytics
                        </h2>
                    </div>
                    <div class="card-body">
                        @if($role === 'admin')
                            {{-- Admin sees all users --}}
                            <div class="access-stats">
                                <h5>All System Users</h5>
                                <div class="user-access-grid">
                                    <div class="access-item">
                                        <span class="access-label">Total Users</span>
                                        <span class="access-value">{{ $totalUsers }}</span>
                                    </div>
                                    <div class="access-item">
                                        <span class="access-label">Active Today</span>
                                        <span class="access-value">{{ $stats['active_today'] ?? 0 }}</span>
                                    </div>
                                    <div class="access-item">
                                        <span class="access-label">Last Week</span>
                                        <span class="access-value">{{ $stats['active_week'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        @elseif($role === 'supervisor')
                            {{-- Supervisor sees their centre's users --}}
                            <div class="access-stats">
                                <h5>Centre Users ({{ session('centre_id') }})</h5>
                                <div class="user-access-grid">
                                    <div class="access-item">
                                        <span class="access-label">Teachers</span>
                                        <span class="access-value">{{ $stats['centre_teachers'] ?? 0 }}</span>
                                    </div>
                                    <div class="access-item">
                                        <span class="access-label">Active Today</span>
                                        <span class="access-value">{{ $stats['centre_active_today'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        @elseif($role === 'teacher')
                            {{-- Teacher sees only other teachers --}}
                            <div class="access-stats">
                                <h5>Fellow Teachers</h5>
                                <div class="user-access-grid">
                                    <div class="access-item">
                                        <span class="access-label">Other Teachers</span>
                                        <span class="access-value">{{ $stats['fellow_teachers'] ?? 0 }}</span>
                                    </div>
                                    <div class="access-item">
                                        <span class="access-label">Online Now</span>
                                        <span class="access-value">{{ $stats['teachers_online'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Other roles see limited info --}}
                            <div class="access-stats">
                                <h5>Your Activity</h5>
                                <div class="user-access-grid">
                                    <div class="access-item">
                                        <span class="access-label">Last Login</span>
                                        <span class="access-value">{{ session('login_time') ?? 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Charts Section - Role-based data --}}
                @if(count($charts) > 0)
                <div class="content-card mt-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="charts-grid">
                            @foreach($charts as $chartKey => $chart)
                                @if($role === 'admin' || 
                                    ($role === 'supervisor' && in_array($chartKey, ['centre_performance', 'teacher_stats'])) ||
                                    ($role === 'teacher' && in_array($chartKey, ['my_sessions', 'student_progress'])))
                                <div class="chart-container">
                                    <h6>{{ $chart['title'] ?? ucfirst(str_replace('_', ' ', $chartKey)) }}</h6>
                                    <canvas id="chart-{{ $chartKey }}" data-chart="{{ json_encode($chart) }}"></canvas>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column - Sidebar Widgets --}}
            <div class="col-lg-4">
                {{-- Notifications Widget --}}
                @include('dashboard.widgets.notifications', ['notifications' => $notifications])

                {{-- Quick Actions Widget --}}
                @include('dashboard.widgets.quick-actions', ['quickActions' => $quickActions])

                {{-- Calendar Widget --}}
                <div class="widget-card">
                    <div class="widget-header">
                        <h3 class="widget-title">
                            <i class="fas fa-calendar"></i> Calendar
                        </h3>
                    </div>
                    <div class="widget-body">
                        <p class="text-muted text-center">Calendar widget coming soon</p>
                    </div>
                </div>

                {{-- System Health Widget (Admin only) --}}
                @if($role === 'admin' && isset($systemHealth))
                <div class="widget-card">
                    <div class="widget-header">
                        <h3 class="widget-title">
                            <i class="fas fa-heartbeat"></i> System Health
                        </h3>
                    </div>
                    <div class="widget-body">
                        <div class="health-stats">
                            <div class="health-item">
                                <span class="health-label">Database</span>
                                <span class="health-status health-{{ $systemHealth['database'] ?? 'unknown' }}">
                                    {{ ucfirst($systemHealth['database'] ?? 'unknown') }}
                                </span>
                            </div>
                            <div class="health-item">
                                <span class="health-label">Cache</span>
                                <span class="health-status health-{{ $systemHealth['cache'] ?? 'unknown' }}">
                                    {{ ucfirst($systemHealth['cache'] ?? 'unknown') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Role-specific widgets --}}
                @switch($role)
                    @case('supervisor')
                        <div class="widget-card">
                            <div class="widget-header">
                                <h3 class="widget-title">
                                    <i class="fas fa-clipboard-check"></i> Pending Approvals
                                </h3>
                            </div>
                            <div class="widget-body">
                                <p class="text-center">{{ $stats['pending_approvals'] ?? 0 }} pending items</p>
                            </div>
                        </div>
                        @break
                    @case('teacher')
                        <div class="widget-card">
                            <div class="widget-header">
                                <h3 class="widget-title">
                                    <i class="fas fa-check-circle"></i> Attendance Reminder
                                </h3>
                            </div>
                            <div class="widget-body">
                                <p class="text-center">{{ $stats['pending_attendance'] ?? 0 }} pending</p>
                            </div>
                        </div>
                        @break
                    @case('trainee')
                        <div class="widget-card">
                            <div class="widget-header">
                                <h3 class="widget-title">
                                    <i class="fas fa-trophy"></i> Achievements
                                </h3>
                            </div>
                            <div class="widget-body">
                                <p class="text-center">{{ count($stats['achievements'] ?? []) }} achievements</p>
                            </div>
                        </div>
                        @break
                @endswitch
            </div>
        </div>
    </div>

    {{-- Chart Containers (Hidden, populated by JS) --}}
    <div class="chart-containers hidden">
        @foreach($charts ?? [] as $chartId => $chartData)
            <div id="chart-{{ $chartId }}" class="chart-container" data-chart='@json($chartData)'></div>
        @endforeach
    </div>
</div>

{{-- Dashboard Customization Modal --}}
<div class="modal fade" id="dashboardCustomizationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customize Dashboard</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Dashboard Customization Options --}}
                <div class="customization-form">
                    <div class="form-group">
                        <label for="dashboard-theme">Dashboard Theme</label>
                        <select class="form-control" id="dashboard-theme">
                            <option value="light">Light Theme</option>
                            <option value="dark">Dark Theme</option>
                            <option value="auto">Auto (System)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Widget Visibility</label>
                        <div class="widget-toggles">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show-notifications" checked>
                                <label class="form-check-label" for="show-notifications">
                                    Show Notifications
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show-quick-actions" checked>
                                <label class="form-check-label" for="show-quick-actions">
                                    Show Quick Actions
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show-calendar" checked>
                                <label class="form-check-label" for="show-calendar">
                                    Show Calendar
                                </label>
                            </div>
                            @if($role === 'admin')
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show-system-health" checked>
                                <label class="form-check-label" for="show-system-health">
                                    Show System Health
                                </label>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="refresh-interval">Auto Refresh Interval</label>
                        <select class="form-control" id="refresh-interval">
                            <option value="0">Never</option>
                            <option value="30">30 seconds</option>
                            <option value="60">1 minute</option>
                            <option value="300" selected>5 minutes</option>
                            <option value="600">10 minutes</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="DashboardManager.saveCustomization()">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-widgets.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-charts.css') }}">
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('js/dashboard-manager.js') }}"></script>
<script src="{{ asset('js/dashboard-charts.js') }}"></script>
<script src="{{ asset('js/dashboard-widgets.js') }}"></script>
<script>
    // Initialize dashboard with server data
    document.addEventListener('DOMContentLoaded', function() {
        DashboardManager.init({
            role: '{{ $role }}',
            userId: '{{ $userId ?? session("id") }}',
            refreshInterval: 300000, // 5 minutes
            apiEndpoints: {
                refresh: '{{ url("/dashboard/api/refresh") }}',
                customize: '{{ url("/dashboard/api/customize") }}',
                notifications: '{{ url("/dashboard/api/notifications") }}'
            }
        });
    });
</script>
@endsection