@extends('layouts.app')

@section('title', 'Staff Directory - CREAMS')

@section('styles')
<style>
    /* Staff Directory Styles */
    .teacher-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
        height: 100%;
        border: 1px solid rgba(0,0,0,0.1);
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
    
    .teacher-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12);
    }
    
    .teacher-header {
        padding: 15px 20px;
        background: linear-gradient(to right, var(--primary-color), var(--primary-color-light));
        color: white;
        position: relative;
    }
    
    .role-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        background: rgba(255,255,255,0.2);
    }
    
    .teacher-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin: -60px auto 15px;
        display: block;
        background-color: #f8f9fa;
    }
    
    .teacher-name {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .teacher-role {
        font-size: 0.9rem;
        color: #6c757d;
        text-align: center;
        margin-bottom: 15px;
    }
    
    .teacher-body {
        padding: 15px 20px;
    }
    
    .teacher-details {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .teacher-details li {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px dashed #eee;
    }
    
    .teacher-details li:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-weight: 500;
        width: 40%;
        color: #555;
    }
    
    .detail-value {
        width: 60%;
        color: #333;
    }
    
    .teacher-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #eee;
        text-align: center;
    }
    
    /* Filter Section */
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: var(--primary-color);
    }
    
    /* Teachers Sections Styles */
    .teachers-section {
        margin-bottom: 50px;
    }
    
    .section-header {
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-color);
        margin: 0;
    }
    
    .section-action {
        color: var(--primary-color);
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
    }
    
    .section-action:hover {
        text-decoration: underline;
    }
    
    /* Stats Cards */
    .stats-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12);
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .stats-title {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .stats-value {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
    }
    
    /* Activity Sections */
    .activity-section {
        margin-top: 50px;
    }
    
    .activity-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .activity-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: var(--primary-color-light);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .activity-content {
        flex-grow: 1;
    }
    
    .activity-title {
        font-weight: 500;
        margin-bottom: 2px;
    }
    
    .activity-teachers {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    /* Empty State */
    .empty-state {
        padding: 50px 20px;
        text-align: center;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .empty-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    
    .empty-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 10px;
    }
    
    .empty-description {
        color: #adb5bd;
        margin-bottom: 20px;
    }
    
    /* Role Colors */
    .role-admin {
        background-color: #4e73df !important;
    }
    
    .role-supervisor {
        background-color: #1cc88a !important;
    }
    
    .role-teacher {
        background-color: #36b9cc !important;
    }
    
    .role-ajk {
        background-color: #f6c23e !important;
    }
    
    /* Tabs for activity sections */
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 10px 20px;
    }
    
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid var(--primary-color);
        color: var(--primary-color);
        background: transparent;
    }
    
    /* Search Box */
    .search-bar {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-bar .form-control {
        padding-left: 45px;
        height: 50px;
        border-radius: 25px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .search-bar .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }
    
    /* Loading state */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 10px;
    }
    
    /* Dashboard charts */
    .chart-container {
        height: 250px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staff Directory</h1>
        <div>
            @if(in_array($currentUserRole, ['admin', 'supervisor']))
            <a href="{{ route('auth.registerpage') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus fa-sm"></i> Add Staff
            </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm ml-2">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Dashboard
            </a>
        </div>
    </div>

    @if(isset($error))
    <div class="alert alert-danger">
        {{ $error }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <!-- Top Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <div class="stats-title">Total Staff</div>
                <div class="stats-value">{{ $stats['total_users'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon role-teacher">
                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                </div>
                <div class="stats-title">Teachers</div>
                <div class="stats-value">{{ $stats['teachers_count'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon role-supervisor">
                    <i class="fas fa-user-tie fa-2x"></i>
                </div>
                <div class="stats-title">Supervisors</div>
                <div class="stats-value">{{ $stats['supervisors_count'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon role-admin">
                    <i class="fas fa-user-shield fa-2x"></i>
                </div>
                <div class="stats-title">Administrators</div>
                <div class="stats-value">{{ $stats['admins_count'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-1"></i> Filter Staff
            </h6>
        </div>
        <div class="card-body">
            <form id="filter-form" action="{{ route('teachershome') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="role-filter">Role</label>
                        <select class="form-control" id="role-filter" name="role">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ $filters['role'] == $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="activity-filter">Activity</label>
                        <select class="form-control" id="activity-filter" name="activity">
                            <option value="">All Activities</option>
                            @foreach($activities as $activity)
                                <option value="{{ $activity }}" {{ $filters['activity'] == $activity ? 'selected' : '' }}>
                                    {{ $activity }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="center-filter">center</label>
                        <select class="form-control" id="center-filter" name="center">
                            <option value="">All Centres</option>
                            @foreach($centres as $centre)
                                <option value="{{ $centre->centre_id }}" {{ $filters['centre'] == $centre->centre_id ? 'selected' : '' }}>
                                    {{ $centre->centre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search-input">Search</label>
                        <div class="search-bar">
                            <input type="text" class="form-control" id="search-input" name="search" placeholder="Search by name, email..." value="{{ $filters['search'] ?? '' }}">
                            <span class="search-icon">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('teachershome') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-sync-alt mr-1"></i> Reset Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users mr-1"></i> Staff Directory
            </h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="activityTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab">
                        All Staff
                    </a>
                </li>
                @foreach($activities as $index => $activity)
                    <li class="nav-item">
                        <a class="nav-link" id="activity-{{ $index }}-tab" data-toggle="tab" href="#activity-{{ $index }}" role="tab">
                            {{ $activity }}
                        </a>
                    </li>
                @endforeach
            </ul>
            
            <div class="tab-content mt-4" id="activityTabsContent">
                <!-- All Staff Tab -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div class="row">
                        @forelse($users as $user)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="teacher-card">
                                    <div class="teacher-header role-{{ $user->role }}">
                                        <span class="role-badge">{{ ucfirst($user->role) }}</span>
                                    </div>
                                    @if(isset($user->avatar) && $user->avatar)
                                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                                             alt="{{ $user->user_name }}" 
                                             class="teacher-avatar"
                                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                    @else
                                        <img src="{{ asset('images/default-avatar.png') }}" 
                                             alt="{{ $user->user_name }}" 
                                             class="teacher-avatar">
                                    @endif
                                    <div class="teacher-body">
                                        <h5 class="teacher-name">{{ $user->user_name }}</h5>
                                        <div class="teacher-role">{{ ucfirst($user->role) }}</div>
                                        <ul class="teacher-details">
                                            <li>
                                                <span class="detail-label">ID:</span>
                                                <span class="detail-value">{{ $user->id }}</span>
                                            </li>
                                            <li>
                                                <span class="detail-label">Major:</span>
                                                <span class="detail-value">{{ $user->user_activity_1 }}</span>
                                            </li>
                                            <li>
                                                <span class="detail-label">Minor:</span>
                                                <span class="detail-value">{{ $user->user_activity_2 ?? 'N/A' }}</span>
                                            </li>
                                            <li>
                                                <span class="detail-label">Centre:</span>
                                                <span class="detail-value">{{ $user->center_name ?? 'Not Assigned' }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="teacher-footer">
                                        <a href="{{ route('updateuser', ['id' => $user->id]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-user mr-1"></i> View Profile
                                        </a>
                                        @if(in_array($currentUserRole, ['admin', 'supervisor']) && 
                                            ($currentUserRole == 'admin' || $user->role != 'admin'))
                                            <a href="{{ route('updateuser', ['id' => $user->id]) }}" class="btn btn-info btn-sm ml-2">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-users-slash"></i>
                                    </div>
                                    <h3 class="empty-title">No Staff Found</h3>
                                    <p class="empty-description">No staff members match your current filters.</p>
                                    <a href="{{ route('teachershome') }}" class="btn btn-primary">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset Filters
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Activity Tabs -->
                @foreach($activities as $index => $activity)
                    <div class="tab-pane fade" id="activity-{{ $index }}" role="tabpanel">
                        <div class="row">
                            @php
                                $activityUsers = $users->where('user_activity_1', $activity);
                            @endphp
                            
                            @forelse($activityUsers as $user)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="teacher-card">
                                        <div class="teacher-header role-{{ $user->role }}">
                                            <span class="role-badge">{{ ucfirst($user->role) }}</span>
                                        </div>
                                        @if(isset($user->avatar) && $user->avatar)
                                            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                                                 alt="{{ $user->user_name }}" 
                                                 class="teacher-avatar"
                                                 onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                        @else
                                            <img src="{{ asset('images/default-avatar.png') }}" 
                                                 alt="{{ $user->user_name }}" 
                                                 class="teacher-avatar">
                                        @endif
                                        <div class="teacher-body">
                                            <h5 class="teacher-name">{{ $user->user_name }}</h5>
                                            <div class="teacher-role">{{ ucfirst($user->role) }}</div>
                                            <ul class="teacher-details">
                                                <li>
                                                    <span class="detail-label">ID:</span>
                                                    <span class="detail-value">{{ $user->id }}</span>
                                                </li>
                                                <li>
                                                    <span class="detail-label">Major:</span>
                                                    <span class="detail-value">{{ $user->user_activity_1 }}</span>
                                                </li>
                                                <li>
                                                    <span class="detail-label">Minor:</span>
                                                    <span class="detail-value">{{ $user->user_activity_2 ?? 'N/A' }}</span>
                                                </li>
                                                <li>
                                                    <span class="detail-label">Centre:</span>
                                                    <span class="detail-value">{{ $user->center_name ?? 'Not Assigned' }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="teacher-footer">
                                            <a href="{{ route('updateuser', ['id' => $user->id]) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-user mr-1"></i> View Profile
                                            </a>
                                            @if(in_array($currentUserRole, ['admin', 'supervisor']) && 
                                                ($currentUserRole == 'admin' || $user->role != 'admin'))
                                                <a href="{{ route('updateuser', ['id' => $user->id]) }}" class="btn btn-info btn-sm ml-2">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-users-slash"></i>
                                        </div>
                                        <h3 class="empty-title">No Staff in {{ $activity }}</h3>
                                        <p class="empty-description">There are no staff members assigned to this activity.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Analysis Section: Stats by Role and center -->
    <div class="row">
        <!-- Role Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie mr-1"></i> Staff by Role
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="roleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- center Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-1"></i> Staff by Centre
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="centerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Activity Distribution -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-line mr-1"></i> Staff by Activity Area
            </h6>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize tabs
    $('.nav-tabs a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Handle filter form submission with AJAX
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("teachershome") }}',
            type: 'GET',
            data: formData,
            beforeSend: function() {
                // Show loading state
                $('.tab-content').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            },
            success: function(response) {
                // Reload the page with the new filters
                window.location.href = '{{ route("teachershome") }}?' + formData;
            },
            error: function(xhr) {
                // Remove loading overlay
                $('.loading-overlay').remove();
                
                // Show error message
                alert('An error occurred while filtering staff. Please try again.');
                console.error(xhr.responseText);
            }
        });
    });
    
    // Role Distribution Chart
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    const roleChart = new Chart(roleCtx, {
        type: 'pie',
        data: {
            labels: ['Admin', 'Supervisor', 'Teacher', 'AJK'],
            datasets: [{
                data: [
                    {{ $stats['admins_count'] ?? 0 }},
                    {{ $stats['supervisors_count'] ?? 0 }},
                    {{ $stats['teachers_count'] ?? 0 }},
                    {{ $stats['ajks_count'] ?? 0 }}
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: true,
                position: 'bottom'
            },
            cutoutPercentage: 0,
        },
    });
    
    // center Distribution Chart
    const centerData = @json($stats['center_breakdown'] ?? []);
    const centerLabels = Object.keys(centerData);
    const centerValues = Object.values(centerData);
    
    const centerCtx = document.getElementById('centerChart').getContext('2d');
    const centerChart = new Chart(centerCtx, {
        type: 'bar',
        data: {
            labels: centerLabels,
            datasets: [{
                label: 'Staff Count',
                data: centerValues,
                backgroundColor: '#4e73df',
                hoverBackgroundColor: '#2e59d9',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 6
                    },
                    maxBarThickness: 25,
                }],
                yAxes: [{
                    ticks: {
                        min: 0,
                        maxTicksLimit: 5,
                        padding: 10,
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
        }
    });
    
    // Activity Distribution Chart
    const activityData = @json($stats['activity_breakdown'] ?? []);
    const activityLabels = Object.keys(activityData);
    const activityValues = Object.values(activityData);
    
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'horizontalBar',
        data: {
            labels: activityLabels,
            datasets: [{
                label: 'Staff Count',
                data: activityValues,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#5a5c69', '#858796', '#4e73df', '#1cc88a', '#36b9cc', 
                ],
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        min: 0,
                        maxTicksLimit: 5,
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
        }
    });
});
</script>
@endsection