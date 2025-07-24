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
            @if(session('role') === 'teacher' || session('role') === 'supervisor' || session('role') === 'admin')
                <button class="btn-attendance" onclick="openAttendanceQuickAccess()">
                    <i class="fas fa-clipboard-check"></i> Quick Attendance
                </button>
            @endif
            <button class="btn-refresh" onclick="refreshDashboardData()" id="refresh-btn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn-customize" onclick="showInDevelopmentMessage()" title="Feature in development" data-toggle="tooltip" data-placement="bottom">
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
                        <a href="{{ route('activities.home') }}" class="btn btn-sm btn-outline-primary">
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

                {{-- Analytics Section - Simple Stats (replaces problematic charts) --}}
                <div class="content-card mt-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-bar"></i> Analytics Overview
                        </h2>
                        <small class="text-muted">Real-time system statistics</small>
                    </div>
                    <div class="card-body">
                        @if($role === 'admin')
                            {{-- Admin Analytics --}}
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-primary">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['total_users'] ?? 0 }}</h4>
                                            <span class="text-muted">Total Users</span>
                                            @if(isset($stats['recent_growth']['users']) && $stats['recent_growth']['users'] > 0)
                                                <small class="text-success">+{{ $stats['recent_growth']['users'] }} this month</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-success">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['total_trainees'] ?? 0 }}</h4>
                                            <span class="text-muted">Trainees</span>
                                            @if(isset($stats['recent_growth']['trainees']) && $stats['recent_growth']['trainees'] > 0)
                                                <small class="text-success">+{{ $stats['recent_growth']['trainees'] }} this month</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-info">
                                            <i class="fas fa-dumbbell"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['total_activities'] ?? 0 }}</h4>
                                            <span class="text-muted">Activities</span>
                                            @if(isset($stats['recent_growth']['activities']) && $stats['recent_growth']['activities'] > 0)
                                                <small class="text-success">+{{ $stats['recent_growth']['activities'] }} this month</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-warning">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['total_centres'] ?? 0 }}</h4>
                                            <span class="text-muted">Centres</span>
                                            <small class="text-info">{{ $stats['active_centres'] ?? 0 }} active</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Role Distribution Quick View --}}
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">Staff Distribution</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="role-stat">
                                                <span class="role-label">Teachers</span>
                                                <span class="role-count">{{ $stats['teachers'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="role-stat">
                                                <span class="role-label">Supervisors</span>
                                                <span class="role-count">{{ $stats['supervisors'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="role-stat">
                                                <span class="role-label">Administrators</span>
                                                <span class="role-count">{{ $stats['administrators'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($role === 'supervisor')
                            {{-- Supervisor Analytics --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-primary">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['centre_teachers'] ?? 0 }}</h4>
                                            <span class="text-muted">Centre Teachers</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-success">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['centre_active_today'] ?? 0 }}</h4>
                                            <span class="text-muted">Active Today</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-info">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['pending_approvals'] ?? 0 }}</h4>
                                            <span class="text-muted">Pending Items</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($role === 'teacher')
                            {{-- Teacher Analytics --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-primary">
                                            <i class="fas fa-dumbbell"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $mySessions ?? 0 }}</h4>
                                            <span class="text-muted">My Sessions</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-success">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $myTrainees ?? 0 }}</h4>
                                            <span class="text-muted">My Trainees</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-info">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $upcomingClasses ?? 0 }}</h4>
                                            <span class="text-muted">Upcoming Classes</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Default Analytics for other roles --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-primary">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $stats['my_activities'] ?? 0 }}</h4>
                                            <span class="text-muted">My Activities</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="analytics-stat">
                                        <div class="stat-icon text-success">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4>{{ $completedSessions ?? 0 }}</h4>
                                            <span class="text-muted">Completed Sessions</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Statistics are updated in real-time. Charts will be available in a future update.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Sidebar Widgets --}}
            <div class="col-lg-4">
                {{-- Notifications Widget --}}
                @include('dashboard.widgets.notifications', ['notifications' => $notifications])

                {{-- Schedule Widget --}}
                @include('dashboard.schedule-widget', ['todaysSessions' => $todaysSessions])

                {{-- Recent Users Widget --}}
                <div class="widget-card">
                    <div class="widget-header">
                        <h3 class="widget-title">
                            <i class="fas fa-users-cog"></i> Recent Access
                        </h3>
                        <small class="text-muted">Latest user activity</small>
                    </div>
                    <div class="widget-body">
                        @php
                            // Get recent users based on last access time
                            $recentUsers = [];
                            try {
                                $query = \App\Models\Users::select('id', 'name', 'role', 'avatar', 'user_last_accessed_at')
                                    ->whereNotNull('user_last_accessed_at')
                                    ->orderBy('user_last_accessed_at', 'desc');
                                
                                // Role-based filtering
                                if (session('role') === 'admin') {
                                    // Admin sees all users
                                    $recentUsers = $query->limit(8)->get();
                                } elseif (session('role') === 'supervisor') {
                                    // Supervisor sees users from their centre
                                    $centreId = session('centre_id');
                                    if ($centreId) {
                                        $recentUsers = $query->where('centre_id', $centreId)->limit(6)->get();
                                    }
                                } elseif (session('role') === 'teacher') {
                                    // Teacher sees other teachers and their supervisors
                                    $recentUsers = $query->whereIn('role', ['teacher', 'supervisor'])->limit(5)->get();
                                }
                            } catch (Exception $e) {
                                Log::error('Error getting recent users', ['error' => $e->getMessage()]);
                                $recentUsers = collect();
                            }
                        @endphp

                        @if($recentUsers->count() > 0)
                            <div class="recent-users-list">
                                @foreach($recentUsers as $user)
                                    <div class="recent-user-item">
                                        <div class="user-avatar-small">
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                                                     alt="{{ $user->name }}" 
                                                     onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
                                            @else
                                                <img src="{{ asset('images/default-avatar.svg') }}" alt="{{ $user->name }}">
                                            @endif
                                            <div class="role-indicator role-{{ $user->role }}"></div>
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name">{{ Str::limit($user->name, 20) }}</div>
                                            <div class="user-meta">
                                                <span class="user-role">{{ ucfirst($user->role) }}</span>
                                                <span class="access-time">
                                                    {{ $user->user_last_accessed_at ? \Carbon\Carbon::parse($user->user_last_accessed_at)->diffForHumans() : 'Unknown' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="access-indicator">
                                            @php
                                                $lastAccess = $user->user_last_accessed_at ? \Carbon\Carbon::parse($user->user_last_accessed_at) : null;
                                                $isOnline = $lastAccess && $lastAccess->diffInMinutes(now()) < 15;
                                                $isRecentlyActive = $lastAccess && $lastAccess->diffInHours(now()) < 24;
                                            @endphp
                                            @if($isOnline)
                                                <span class="status-dot status-online" title="Online now"></span>
                                            @elseif($isRecentlyActive)
                                                <span class="status-dot status-recent" title="Active today"></span>
                                            @else
                                                <span class="status-dot status-offline" title="Offline"></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(session('role') === 'admin')
                                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-block btn-light mt-3">
                                    <i class="fas fa-users me-1"></i>View All Users
                                </a>
                            @endif
                        @else
                            <div class="empty-recent-users">
                                <i class="fas fa-user-clock fa-2x text-muted mb-2"></i>
                                <p class="text-muted text-center mb-0">No recent activity</p>
                                <small class="text-muted">User access will appear here</small>
                            </div>
                        @endif
                    </div>
                </div>

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
                        <small class="text-muted">Real-time system status</small>
                    </div>
                    <div class="widget-body">
                        <div class="health-stats">
                            <div class="health-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="health-label">
                                        <i class="fas fa-database text-info"></i> Database
                                    </span>
                                    <span class="health-status health-{{ $systemHealth['database'] ?? 'unknown' }}">
                                        @if(($systemHealth['database'] ?? '') === 'healthy')
                                            <i class="fas fa-check-circle text-success"></i> Connected
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i> Error
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="health-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="health-label">
                                        <i class="fas fa-memory text-warning"></i> Cache
                                    </span>
                                    <span class="health-status health-{{ $systemHealth['cache'] ?? 'unknown' }}">
                                        @if(($systemHealth['cache'] ?? '') === 'healthy')
                                            <i class="fas fa-check-circle text-success"></i> Active
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i> Error
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="health-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="health-label">
                                        <i class="fas fa-hdd text-primary"></i> Storage
                                    </span>
                                    <span class="health-status health-{{ $systemHealth['storage'] ?? 'unknown' }}">
                                        @if(($systemHealth['storage'] ?? '') === 'healthy')
                                            <i class="fas fa-check-circle text-success"></i> Available
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i> Error
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="health-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="health-label">
                                        <i class="fas fa-server text-secondary"></i> Overall
                                    </span>
                                    <span class="health-status health-{{ $systemHealth['overall'] ?? 'unknown' }}">
                                        @if(($systemHealth['overall'] ?? '') === 'healthy')
                                            <i class="fas fa-check-circle text-success"></i> Healthy
                                        @elseif(($systemHealth['overall'] ?? '') === 'degraded')
                                            <i class="fas fa-exclamation-triangle text-warning"></i> Degraded
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i> Unhealthy
                                        @endif
                                    </span>
                                </div>
                            </div>
                            @if(isset($systemHealth['last_check']))
                                <div class="mt-3 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        Last check: {{ \Carbon\Carbon::parse($systemHealth['last_check'])->diffForHumans() }}
                                    </small>
                                </div>
                            @endif
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

</div>

{{-- Quick Attendance Modal --}}
<div class="modal fade" id="quickAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-check me-2"></i>Quick Attendance
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="attendance-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading today's sessions...</p>
                </div>
                
                <div id="attendance-content" style="display: none;">
                    <div class="attendance-header mb-3">
                        <h6 class="text-muted">Today's Sessions - {{ \Carbon\Carbon::now()->format('l, F j, Y') }}</h6>
                    </div>
                    
                    <div id="sessions-list">
                        <!-- Sessions will be loaded here via JavaScript -->
                    </div>
                    
                    <div id="no-sessions" class="text-center py-4" style="display: none;">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No sessions scheduled for today</h6>
                        <p class="text-muted mb-0">Check back tomorrow or view the full schedule</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-alt me-1"></i>View Full Attendance
                </a>
            </div>
        </div>
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
                <button type="button" class="btn btn-primary" onclick="showNotification('Customization saved!', 'success'); $('#dashboardCustomizationModal').modal('hide');">
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
@endsection

@section('scripts')
<script>
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Refresh dashboard data
    function refreshDashboardData() {
        showNotification('Refreshing dashboard...', 'info');
        window.location.reload();
    }

    // Show notification helper function
    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        const iconClass = type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show dashboard-notification" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('body').append(notification);
        
        // Auto-remove notification after 3 seconds
        setTimeout(function() {
            $('.dashboard-notification').alert('close');
        }, 3000);
    }

    // Show in development message for customize button
    function showInDevelopmentMessage() {
        const modal = `
            <div class="modal fade" id="inDevelopmentModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-tools text-warning me-2"></i>In Development
                            </h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <i class="fas fa-cog fa-3x text-muted mb-3 fa-spin"></i>
                            <h6>Dashboard Customization</h6>
                            <p class="text-muted mb-0">This feature is currently under development and will be available in a future update.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Understood</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#inDevelopmentModal').remove();
        
        // Add modal to body and show it
        $('body').append(modal);
        $('#inDevelopmentModal').modal('show');
        
        // Clean up modal after it's hidden
        $('#inDevelopmentModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    // Quick Attendance Modal Functions
    function openAttendanceQuickAccess() {
        $('#quickAttendanceModal').modal('show');
        loadTodaysSessions();
    }

    function loadTodaysSessions() {
        $('#attendance-loading').show();
        $('#attendance-content').hide();
        
        // Simulate loading today's sessions (replace with actual API call)
        setTimeout(function() {
            const role = '{{ $role }}';
            const userId = '{{ session("id") }}';
            
            // Mock data for demonstration
            const mockSessions = [
                {
                    id: 1,
                    name: 'Physical Therapy Session A',
                    time: '09:00 - 10:00',
                    venue: 'Room 101',
                    trainees: 8,
                    status: 'pending'
                },
                {
                    id: 2,
                    name: 'Speech Therapy Group B',
                    time: '14:00 - 15:00',
                    venue: 'Room 203',
                    trainees: 5,
                    status: 'completed'
                }
            ];
            
            $('#attendance-loading').hide();
            
            if (mockSessions.length > 0) {
                renderSessionsList(mockSessions);
                $('#attendance-content').show();
            } else {
                $('#no-sessions').show();
                $('#attendance-content').show();
            }
        }, 1000);
    }

    function renderSessionsList(sessions) {
        const sessionsList = $('#sessions-list');
        sessionsList.empty();
        
        sessions.forEach(function(session) {
            const statusClass = session.status === 'completed' ? 'success' : 
                               session.status === 'ongoing' ? 'warning' : 'primary';
            const statusText = session.status === 'completed' ? 'Completed' :
                              session.status === 'ongoing' ? 'Ongoing' : 'Pending';
            
            const sessionHtml = `
                <div class="session-card mb-3">
                    <div class="session-header d-flex justify-content-between align-items-center">
                        <h6 class="session-title mb-0">${session.name}</h6>
                        <span class="badge badge-${statusClass}">${statusText}</span>
                    </div>
                    <div class="session-details mt-2">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted"><i class="fas fa-clock me-1"></i>${session.time}</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>${session.venue}</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted"><i class="fas fa-users me-1"></i>${session.trainees} trainees</small>
                            </div>
                        </div>
                    </div>
                    <div class="session-actions mt-2">
                        ${session.status === 'pending' ? 
                            `<button class="btn btn-sm btn-primary" onclick="markAttendance(${session.id})">
                                <i class="fas fa-check me-1"></i>Mark Attendance
                            </button>` :
                            `<button class="btn btn-sm btn-outline-primary" onclick="viewAttendance(${session.id})">
                                <i class="fas fa-eye me-1"></i>View Attendance
                            </button>`
                        }
                    </div>
                </div>
            `;
            
            sessionsList.append(sessionHtml);
        });
    }

    function markAttendance(sessionId) {
        // Show success message and redirect to attendance page
        alert('Redirecting to attendance marking for session ' + sessionId);
        // In real implementation, redirect to specific attendance route
        window.location.href = '/attendance/session/' + sessionId;
    }

    function viewAttendance(sessionId) {
        // Redirect to view attendance for this session
        window.location.href = '/attendance/session/' + sessionId + '/view';
    }
</script>
@endsection