@extends('layouts.app')

@section('title', ucfirst(session('role')) . ' Dashboard - CREAMS')

<!-- CREATED_AT: JULY 7 - AUTO BY CLAUDE -->

@section('styles')
<style>
    /* Dashboard-specific styles */
    .dashboard-header {
        margin-bottom: 20px;
    }
    
    .dashboard-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark-color);
    }
    
    .dashboard-subtitle {
        color: #6c757d;
        font-size: 14px;
    }
    
    .date-display {
        display: inline-flex;
        align-items: center;
        background: var(--light-color);
        border-radius: 20px;
        padding: 8px 15px;
        color: #6c757d;
        font-size: 14px;
    }
    
    .date-display i {
        margin-right: 8px;
    }
    
    /* Stats cards */
    .stats-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        transition: all var(--transition-speed) ease;
        border-left: 4px solid transparent;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    /* Recent access card */
    .recent-access-card {
        border-radius: 10px;
        overflow: hidden;
        transition: all var(--transition-speed) ease;
    }
    
    .recent-access-header {
        background: var(--light-color);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .recent-access-title {
        margin: 0;
        font-weight: 600;
    }
    
    .section-action {
        color: #6c757d;
        text-decoration: none;
        font-size: 13px;
        transition: all var(--transition-speed) ease;
    }
    
    .section-action:hover {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .recent-items {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .recent-item {
        border-bottom: 1px solid var(--border-color);
        transition: all var(--transition-speed) ease;
    }
    
    .recent-link {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        color: #555;
        text-decoration: none;
    }
    
    .recent-link:hover {
        background-color: rgba(50, 189, 234, 0.05);
        color: #555;
        text-decoration: none;
    }
    
    .recent-icon {
        width: 40px;
        height: 40px;
        background: var(--light-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        margin-right: 15px;
    }
    
    .recent-content {
        flex-grow: 1;
    }
    
    .recent-name {
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .recent-meta {
        color: #6c757d;
        font-size: 12px;
    }
    
    .recent-time {
        color: #6c757d;
        font-size: 12px;
        margin-left: 15px;
    }
    
    /* Rehab categories */
    .rehab-category {
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--light-color);
        transition: all var(--transition-speed) ease;
        margin-bottom: 10px;
    }
    
    .rehab-category:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .rehab-category-autism {
        background: linear-gradient(to right, rgba(79, 172, 254, 0.15), rgba(0, 242, 254, 0.15));
        border-left: 3px solid #4facfe;
    }
    
    .rehab-category-hearing {
        background: linear-gradient(to right, rgba(255, 154, 158, 0.15), rgba(250, 208, 196, 0.15));
        border-left: 3px solid #ff9a9e;
    }
    
    .rehab-category-visual {
        background: linear-gradient(to right, rgba(51, 204, 255, 0.15), rgba(0, 196, 154, 0.15));
        border-left: 3px solid #33ccff;
    }
    
    .rehab-category-physical {
        background: linear-gradient(to right, rgba(246, 211, 101, 0.15), rgba(253, 160, 133, 0.15));
        border-left: 3px solid #f6d365;
    }
    
    .rehab-category-learning {
        background: linear-gradient(to right, rgba(200, 80, 192, 0.15), rgba(65, 88, 208, 0.15));
        border-left: 3px solid #c850c0;
    }
    
    .rehab-category-speech {
        background: linear-gradient(to right, rgba(161, 140, 209, 0.15), rgba(251, 194, 235, 0.15));
        border-left: 3px solid #a18cd1;
    }
    
    .rehab-category-title {
        font-weight: 500;
        font-size: 14px;
    }
    
    .rehab-category-count {
        font-weight: 600;
        font-size: 18px;
        color: #555;
    }
    
    /* User table */
    .avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
    }
    
    .avatar-sm img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">{{ ucfirst(session('role')) }} Dashboard</h1>
                <p class="dashboard-subtitle">Welcome back! Here's an overview of your system.</p>
            </div>
            <div class="col-auto">
                <div class="date-display">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="current-date">{{ date('l, F d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="card recent-access-card mb-4">
        <div class="card-header recent-access-header">
            <h5 class="recent-access-title"><i class="fas fa-history"></i> Recent Activities</h5>
            <div class="d-flex align-items-center">
                <button id="refresh-activities" class="btn btn-sm btn-outline-primary me-2" title="Refresh Activities">
                    <i class="fas fa-sync-alt"></i>
                </button>
                @if(session('role') === 'admin' || session('role') === 'supervisor')
                    <a href="{{ route('activities.home') }}" class="section-action">View All</a>
                @endif
            </div>
        </div>
        <div class="card-body recent-access-body p-0">
            @php
                // Get recent activities from dashboard data or fallback
                $displayActivities = [];
                
                // Try to get from dashboard service data first
                if (isset($data['recentActivities']) && !empty($data['recentActivities'])) {
                    $displayActivities = $data['recentActivities'];
                } elseif (isset($recentActivities) && !empty($recentActivities)) {
                    $displayActivities = $recentActivities;
                } else {
                    // Fallback to get recent activities from the database directly
                    try {
                        $role = session('role');
                        $userId = session('id');
                        
                        if ($role === 'admin') {
                            // Admin sees all recent activities
                            $displayActivities = DB::table('activities')
                                ->leftJoin('users', 'activities.created_by', '=', 'users.id')
                                ->leftJoin('centres', 'activities.centre_id', '=', 'centres.id')
                                ->select(
                                    'activities.id',
                                    'activities.activity_name as name',
                                    'activities.created_at',
                                    'activities.activity_status as status',
                                    'users.name as creator_name',
                                    'centres.name as centre_name'
                                )
                                ->orderBy('activities.created_at', 'desc')
                                ->limit(5)
                                ->get()
                                ->map(function($activity) {
                                    return [
                                        'id' => $activity->id,
                                        'name' => $activity->name,
                                        'creator' => $activity->creator_name ?? 'System',
                                        'centre' => $activity->centre_name ?? 'Unknown Centre',
                                        'created_at' => $activity->created_at,
                                        'status' => $activity->status ? 'active' : 'inactive',
                                        'action' => 'created',
                                        'type' => 'activity'
                                    ];
                                })
                                ->toArray();
                        } elseif ($role === 'teacher' || $role === 'supervisor') {
                            // Staff sees their own activities and centre activities
                            $centreId = session('centre_id');
                            $query = DB::table('activities')
                                ->leftJoin('users', 'activities.created_by', '=', 'users.id')
                                ->leftJoin('centres', 'activities.centre_id', '=', 'centres.id')
                                ->select(
                                    'activities.id',
                                    'activities.activity_name as name',
                                    'activities.created_at',
                                    'activities.activity_status as status',
                                    'users.name as creator_name',
                                    'centres.name as centre_name'
                                );
                            
                            if ($centreId) {
                                $query->where('activities.centre_id', $centreId);
                            }
                            
                            $displayActivities = $query
                                ->orderBy('activities.created_at', 'desc')
                                ->limit(5)
                                ->get()
                                ->map(function($activity) {
                                    return [
                                        'id' => $activity->id,
                                        'name' => $activity->name,
                                        'creator' => $activity->creator_name ?? 'System',
                                        'centre' => $activity->centre_name ?? 'Unknown Centre',
                                        'created_at' => $activity->created_at,
                                        'status' => $activity->status ? 'active' : 'inactive',
                                        'action' => 'created',
                                        'type' => 'activity'
                                    ];
                                })
                                ->toArray();
                        } else {
                            // Other roles see limited activity info
                            $displayActivities = [];
                        }
                    } catch (Exception $e) {
                        Log::error('Error getting recent activities for dashboard', ['error' => $e->getMessage()]);
                        $displayActivities = [];
                    }
                }
            @endphp

            @if(!empty($displayActivities))
                <div class="p-3">
                    <p class="mb-3"><small class="text-muted">You last logged in: {{ session('login_time', 'Unknown') }}</small></p>
                    
                    @if(session('role') === 'admin')
                        <h6 class="text-primary mb-3">System Activities</h6>
                    @else
                        <h6 class="text-primary mb-3">Recent Activities in Your Centre</h6>
                    @endif
                    
                    <div class="activity-list">
                        @foreach($displayActivities as $activity)
                            <div class="activity-item d-flex align-items-center py-2">
                                <div class="activity-icon me-3">
                                    <i class="fas fa-{{ $activity['type'] === 'activity' ? 'dumbbell' : 'clock' }} text-primary"></i>
                                </div>
                                <div class="activity-content flex-grow-1">
                                    <div class="activity-title">
                                        <strong>{{ $activity['name'] }}</strong>
                                        @if(isset($activity['creator']) && session('role') === 'admin')
                                            <small class="text-muted">by {{ $activity['creator'] }}</small>
                                        @endif
                                    </div>
                                    <div class="activity-meta">
                                        <small class="text-muted">
                                            @if(isset($activity['centre']))
                                                <span class="me-2"><i class="fas fa-building me-1"></i>{{ $activity['centre'] }}</span>
                                            @endif
                                            <span><i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}</span>
                                        </small>
                                    </div>
                                </div>
                                <div class="activity-status">
                                    <span class="badge badge-{{ $activity['status'] === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($activity['status']) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-4 text-center">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No recent activities</h6>
                        <p class="text-muted mb-0">Activities will appear here as they are created</p>
                        @if(session('role') === 'admin' || session('role') === 'teacher')
                            <a href="{{ route('activities.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i>Create Activity
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content - Three Column Layout -->
    <div class="row">
        <!-- Left Column - Staff Management -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users mr-1"></i> Staff Management
                    </h5>
                    @php
                        $role = session('role');
                        $staffUrl = '';
                        
                        // Use proper route checking to avoid 404 errors
                        if (Route::has($role . '.users')) {
                            $staffUrl = route($role . '.users');
                        } elseif (Route::has('users')) {
                            $staffUrl = route('users');
                        } else {
                            $staffUrl = route('dashboard');
                        }
                    @endphp
                    <a href="{{ $staffUrl }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($data['stats'] as $index => $stat)
                            @if(in_array($stat['title'], ['Supervisors', 'Teachers', 'AJKs', 'Total Users']))
                                <div class="col-6 mb-4">
                                    <div class="card h-100 stats-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="stats-icon mr-3 p-3 rounded" style="background: linear-gradient(135deg, var(--primary-color-{{ $index }}, #4e73df), var(--secondary-color-{{ $index }}, #224abe)); color: white;">
                                                    <i class="fas fa-{{ $stat['icon'] }}"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $stat['title'] }}</h6>
                                                    <small class="text-muted">{{ $stat['change'] }}</small>
                                                </div>
                                            </div>
                                            <h3 class="mb-0">{{ $stat['value'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Recent Users Table -->
                    @if(isset($data['userManagement']))
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Users</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['userManagement']['recentUsers'] as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm mr-2">
                                                            <img src="{{ $user->profile_picture ?? asset('images/default-avatar.png') }}" alt="Profile" class="rounded-circle">
                                                        </div>
                                                        {{ $user->name }}
                                                    </div>
                                                </td>
                                                <td><span class="badge badge-info">{{ ucfirst($user->role) }}</span></td>
                                                <td>
                                                    @if($user->status === 'active')
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $viewerRole = session('role');
                                                        $userRole = $user->role;
                                                        $userUrl = '#';
                                                        
                                                        // Determine the correct route based on roles
                                                        if (Route::has("{$viewerRole}.user.view")) {
                                                            $userUrl = route("{$viewerRole}.user.view", ['id' => $user->id]);
                                                        } elseif ($userRole == 'teacher' && Route::has("{$viewerRole}.teacher.view")) {
                                                            $userUrl = route("{$viewerRole}.teacher.view", ['id' => $user->id]);
                                                        } elseif (Route::has('users')) {
                                                            $userUrl = route('users');
                                                        } else {
                                                            $userUrl = route('dashboard');
                                                        }
                                                    @endphp
                                                    <a href="{{ $userUrl }}" class="btn btn-sm btn-primary user-action" data-id="{{ $user->id }}" data-role="{{ $user->role }}">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Middle Column - Trainee Management -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate mr-1"></i> Trainee Management
                    </h5>
                    <a href="{{ route('trainees.home') }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <!-- Trainee Stats -->
                    <div class="row">
                        <div class="col-6 mb-4">
                            <div class="card h-100 stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="stats-icon mr-3 p-3 rounded" style="background: linear-gradient(135deg, #00c49a, #00e676); color: white;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Total Trainees</h6>
                                            <small class="text-muted">Active enrollment</small>
                                        </div>
                                    </div>
                                    <h3 class="mb-0">{{ $traineeStats['total'] ?? 124 }}</h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-4">
                            <div class="card h-100 stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="stats-icon mr-3 p-3 rounded" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Attendance</h6>
                                            <small class="text-muted">Last 30 days avg</small>
                                        </div>
                                    </div>
                                    <h3 class="mb-0">{{ $traineeStats['attendance'] ?? '85%' }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rehabilitation Categories Card -->
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Rehabilitation Categories</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="rehab-category rehab-category-autism">
                                        <div class="rehab-category-title">Autism Spectrum Disorder</div>
                                        <div class="rehab-category-count">{{ $rehabStats['autism'] ?? 12 }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="rehab-category rehab-category-hearing">
                                        <div class="rehab-category-title">Hearing Impairment</div>
                                        <div class="rehab-category-count">{{ $rehabStats['hearing'] ?? 8 }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="rehab-category rehab-category-visual">
                                        <div class="rehab-category-title">Visual Impairment</div>
                                        <div class="rehab-category-count">{{ $rehabStats['visual'] ?? 5 }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="rehab-category rehab-category-physical">
                                        <div class="rehab-category-title">Physical Disability</div>
                                        <div class="rehab-category-count">{{ $rehabStats['physical'] ?? 15 }}</div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('activities.categories') }}" class="btn btn-sm btn-primary">View All Categories</a>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="card mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Activities</h5>
                            <a href="{{ route('activities.home') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($recentActivities) && count($recentActivities) > 0)
                                        @foreach($recentActivities as $activity)
                                            <tr class="activity-row" data-id="{{ $activity->id }}">
                                                <td>{{ $activity->name }}</td>
                                                <td>{{ $activity->date->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $activity->status_color }}">
                                                        {{ ucfirst($activity->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center">No recent activities</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Schedule Sessions Widget -->
                    <div class="card mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                Today's Schedule
                            </h5>
                            <a href="{{ route('activities.schedule') }}" class="btn btn-sm btn-outline-primary">View Full Schedule</a>
                        </div>
                        <div class="card-body">
                            @php
                                // Get today's sessions for the dashboard
                                $todaySessions = \App\Models\ActivitySession::with(['activity', 'teacher'])
                                    ->where('scheduled_date', today())
                                    ->whereIn('status', ['scheduled', 'ongoing'])
                                    ->orderBy('start_time')
                                    ->take(5)
                                    ->get();
                            @endphp
                            
                            @if($todaySessions->count() > 0)
                                @foreach($todaySessions as $session)
                                    <div class="d-flex align-items-center mb-3 {{ $loop->last ? '' : 'border-bottom pb-3' }}">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="schedule-time-badge bg-primary text-white rounded px-2 py-1 small">
                                                {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $session->activity->activity_name }}</h6>
                                            <div class="small text-muted">
                                                @if($session->teacher)
                                                    <i class="fas fa-user me-1"></i>{{ $session->teacher->name }}
                                                @endif
                                                @if($session->activity->category)
                                                    <span class="ms-2">
                                                        @switch($session->activity->category)
                                                            @case('Physical Therapy')
                                                                <i class="fas fa-dumbbell text-success me-1"></i>
                                                                @break
                                                            @case('Speech Therapy')
                                                                <i class="fas fa-comments text-info me-1"></i>
                                                                @break
                                                            @case('Occupational Therapy')
                                                                <i class="fas fa-tools text-warning me-1"></i>
                                                                @break
                                                            @case('Music Therapy')
                                                                <i class="fas fa-music text-purple me-1"></i>
                                                                @break
                                                            @default
                                                                <i class="fas fa-book text-primary me-1"></i>
                                                        @endswitch
                                                        {{ $session->activity->category }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($session->status === 'ongoing')
                                                <span class="badge bg-success">Live</span>
                                            @else
                                                <span class="badge bg-primary">Scheduled</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No sessions scheduled today</p>
                                    @if(session('role') !== 'ajk')
                                        <a href="{{ route('activities.home') }}" class="btn btn-sm btn-outline-primary mt-2">
                                            Schedule Activities
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Widget -->
            @include('dashboard.schedule-widget')
        </div>
        
        <!-- Right Column - center Management -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building mr-1"></i> Centre Management
                    </h5>
                    @php
                        $role = session('role');
                        $centersUrl = '';
                        
                        // Use proper route checking to avoid 404 errors
                        if (Route::has($role . '.centers')) {
                            $centersUrl = route($role . '.centers');
                        } elseif (Route::has('centers')) {
                            $centersUrl = route('centers');
                        } else {
                            $centersUrl = route('dashboard');
                        }
                    @endphp
                    <a href="{{ $centersUrl }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <!-- Add center statistics and charts here -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Centre Capacity
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">73%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: 73%" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assets Overview -->
                    <div class="card asset-management-section">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-boxes"></i> Assets Overview</h5>
                            @php
                                $role = session('role');
                                $assetsUrl = '';
                                
                                // Use proper route checking to avoid 404 errors
                                if (Route::has($role . '.assets')) {
                                    $assetsUrl = route($role . '.assets');
                                } elseif (Route::has('assets')) {
                                    $assetsUrl = route('assets');
                                } else {
                                    $assetsUrl = route('dashboard');
                                }
                            @endphp
                            <a href="{{ $assetsUrl }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Asset Name</th>
                                        <th>Centre</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($recentAssets) && count($recentAssets) > 0)
                                        @foreach($recentAssets as $asset)
                                            <tr class="asset-row" data-id="{{ $asset->id }}">
                                                <td>{{ $asset->name }}</td>
                                                {{ $item->center_name ?? $item->centre_name ?? 'Center Name' }}
                                                <td>
                                                    @if($asset->quantity > 10)
                                                        <span class="badge badge-success">In Stock</span>
                                                    @elseif($asset->quantity > 0)
                                                        <span class="badge badge-warning">Low Stock</span>
                                                    @else
                                                        <span class="badge badge-danger">Out of Stock</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                <span class="text-muted">Asset data unavailable</span>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-link"></i> Quick Links</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <a href="{{ route('traineesregistrationpage') }}" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-user-plus"></i> Add Trainee
                                    </a>
                                </div>
                                <div class="col-6 mb-2">
                                    <a href="{{ route('activities.categories') }}" class="btn btn-outline-success btn-block">
                                        <i class="fas fa-heartbeat"></i> Activities
                                    </a>
                                </div>
                                <div class="col-6 mb-2">
                                    @php
                                        $role = session('role');
                                        $reportsUrl = '';
                                        
                                        // Use proper route checking to avoid 404 errors
                                        if (Route::has($role . '.reports')) {
                                            $reportsUrl = route($role . '.reports');
                                        } else {
                                            $reportsUrl = route('dashboard');
                                        }
                                    @endphp
                                    <a href="{{ $reportsUrl }}" class="btn btn-outline-info btn-block">
                                        <i class="fas fa-chart-bar"></i> Reports
                                    </a>
                                </div>
                                <div class="col-6 mb-2">
                                    @php
                                        $role = session('role');
                                        $settingsUrl = '';
                                        
                                        // Use proper route checking to avoid 404 errors
                                        if (Route::has($role . '.settings')) {
                                            $settingsUrl = route($role . '.settings');
                                        } else {
                                            $settingsUrl = route('dashboard');
                                        }
                                    @endphp
                                    <a href="{{ $settingsUrl }}" class="btn btn-outline-secondary btn-block">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Set CSS variables for consistent styling
    document.documentElement.style.setProperty('--primary-color-0', '#32bdea');
    document.documentElement.style.setProperty('--secondary-color-0', '#25a6cf');
    document.documentElement.style.setProperty('--primary-color-1', '#1cc88a');
    document.documentElement.style.setProperty('--secondary-color-1', '#169a6b');
    document.documentElement.style.setProperty('--primary-color-2', '#36b9cc');
    document.documentElement.style.setProperty('--secondary-color-2', '#258391');
    document.documentElement.style.setProperty('--primary-color-3', '#f6c23e');
    document.documentElement.style.setProperty('--secondary-color-3', '#dda20a');

    // Display current date
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    $('#current-date').text(now.toLocaleDateString('en-US', options));

    // Handle refresh activities button
    $('#refresh-activities').click(function() {
        const btn = $(this);
        const icon = btn.find('i');
        
        // Show loading state
        btn.prop('disabled', true);
        icon.addClass('fa-spin');
        
        // Simulate refresh (in real implementation, make AJAX call)
        setTimeout(function() {
            // Reset button state
            btn.prop('disabled', false);
            icon.removeClass('fa-spin');
            
            // Show success message
            const notification = $(`
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <i class="fas fa-check-circle me-2"></i>Activities refreshed successfully!
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto remove notification
            setTimeout(function() {
                notification.alert('close');
            }, 3000);
            
            // Reload page to get fresh data
            setTimeout(function() {
                window.location.reload();
            }, 1500);
            
        }, 1500);
    });

    // Add click handlers for staff users
    $('.user-action').click(function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userRole = $(this).data('role') || 'user';
        const userName = $(this).closest('tr').find('td:first-child').text().trim();
        
        // Get the href attribute from the anchor
        const userUrl = $(this).attr('href');
        
        // Track this user access
        window.trackDetailedItem(userName, userUrl, 'User');
        
        // Navigate to the URL
        window.location.href = userUrl;
    });
    
    // Track trainee clicks
    $('.activity-row').click(function(e) {
        const activityId = $(this).data('id');
        const activityName = $(this).find('td:first-child').text().trim();
        
        // Create URL based on the current role
        const role = "{{ session('role') }}";
        let activityUrl = '';
        
        // Try role-specific URL first
        if (typeof routeExists === 'function' && routeExists(role + '.activity.view')) {
            activityUrl = '/' + role + '/activity/view/' + activityId;
        } else {
            // Fall back to common route
            activityUrl = '/activities/' + activityId;
        }
        
        // Track this activity access
        window.trackDetailedItem(activityName, activityUrl, 'Activity');
        
        // Redirect to activity page
        window.location.href = activityUrl;
    });
    
    // Track asset clicks
    $('.asset-row').click(function(e) {
        const assetId = $(this).data('id');
        const assetName = $(this).find('td:first-child').text().trim();
        
        // Create URL based on the current role
        const role = "{{ session('role') }}";
        let assetUrl = '';
        
        // Try role-specific URL first
        if (typeof routeExists === 'function' && routeExists(role + '.asset.view')) {
            assetUrl = '/' + role + '/asset/view/' + assetId;
        } else {
            // Fall back to common route
            assetUrl = '/assets/' + assetId;
        }
        
        // Track this asset access
        window.trackDetailedItem(assetName, assetUrl, 'Asset');
        
        // Redirect to asset page
        window.location.href = assetUrl;
    });
    
    // Animate rehab categories
    $('.rehab-category').each(function(index) {
        const category = $(this);
        setTimeout(function() {
            category.addClass('animated fadeInUp');
        }, index * 100);
    });
    
    // Track category clicks
    $('.rehab-category').click(function() {
        const categoryName = $(this).find('.rehab-category-title').text();
        const categoryType = categoryName.toLowerCase().replace(/\s+/g, '-');
        const categoryUrl = '/rehabilitation/categories/' + categoryType;
        
        // Track this category access
        window.trackDetailedItem(categoryName, categoryUrl, 'Rehabilitation Category');
        
        // Redirect to category page
        window.location.href = categoryUrl;
    });

    // Helper function to check if a route exists
    window.routeExists = function(routeName) {
        return false; // This would normally be determined server-side
    };
});
</script>
@endsection