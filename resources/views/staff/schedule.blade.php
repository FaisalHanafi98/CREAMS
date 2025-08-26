@extends('layouts.app')

@section('title')
{{ $staffMember->name }} - Schedule | CREAMS
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #c850c0;
        --success-color: #2ed573;
        --danger-color: #ff4757;
        --warning-color: #ffa502;
        --info-color: #1e90ff;
        --dark-color: #1a2a3a;
        --light-color: #f8f9fa;
        --border-color: #e9ecef;
        --transition-speed: 0.3s;
        --soft-gray: #f8f9fa;
        --warm-white: #ffffff;
    }

    body {
        background: white;
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
        overflow-x: hidden;
    }

    .paper-card {
        background: var(--warm-white);
        border-radius: 16px;
        box-shadow: 
            0 4px 6px rgba(0, 0, 0, 0.05),
            0 10px 15px rgba(0, 0, 0, 0.08),
            0 0 0 1px rgba(255, 255, 255, 0.9);
        position: relative;
        overflow: hidden;
    }

    .paper-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 25%, var(--success-color) 50%, var(--warning-color) 100%);
        border-radius: 16px 16px 0 0;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }

    .staff-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .staff-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        object-fit: cover;
    }

    .staff-photo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: var(--light-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #6c757d;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .staff-name {
        font-size: 2.2rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }

    .staff-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        font-weight: 400;
    }

    .role-badge {
        display: inline-block;
        padding: 0.5rem 1.2rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .role-admin { background: linear-gradient(45deg, #dc3545, #c82333); color: white; }
    .role-supervisor { background: linear-gradient(45deg, #fd7e14, #e55a00); color: white; }
    .role-teacher { background: linear-gradient(45deg, #32bdea, #0fa3cc); color: white; }
    .role-ajk { background: linear-gradient(45deg, #c850c0, #a843a0); color: white; }

    .schedule-layout {
        display: block;
    }

    .calendar-view {
        background: var(--warm-white);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        margin-left: 0;
    }

    .calendar-header {
        background: var(--dark-color);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .calendar-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0;
    }

    .week-grid {
        display: grid;
        grid-template-columns: 100px repeat(7, 1fr);
        background: var(--soft-gray);
        border-bottom: 1px solid #E2E8F0;
    }

    .time-header {
        padding: 1rem 0.5rem;
        text-align: center;
        font-weight: 700;
        font-size: 0.85rem;
        background: var(--dark-color);
        color: white;
        border-right: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .day-header {
        padding: 1rem 0.5rem;
        text-align: center;
        color: var(--dark-color);
        background: var(--warm-white);
        border-right: 1px solid #E2E8F0;
        transition: all 0.3s ease;
        position: relative;
    }

    .day-header:last-child {
        border-right: none;
    }

    .day-header.today {
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        color: white;
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(50, 189, 234, 0.3);
    }

    .day-header.weekend {
        background: var(--soft-gray);
        color: #64748B;
    }

    .day-header.weekend.today {
        background: linear-gradient(135deg, var(--sage), #A8C47B);
        color: white;
    }

    .day-name {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .day-date {
        font-size: 0.8rem;
        opacity: 0.8;
        margin-bottom: 0.25rem;
    }

    .time-now {
        font-size: 0.75rem;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-weight: 600;
        margin-top: 0.25rem;
        display: inline-block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .time-slots {
        padding: 1rem 0.5rem 2rem 0.5rem;
    }

    .time-slot {
        margin-bottom: 2rem;
        position: relative;
        display: flex;
        align-items: center;
    }

    .time-label {
        width: 100px;
        font-size: 0.9rem;
        color: var(--dark-color);
        font-weight: 600;
        text-align: center;
        margin-right: 1rem;
        flex-shrink: 0;
        padding: 0.75rem;
        background: transparent;
        border: none;
    }

    .session-timeline {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        align-items: center;
        justify-items: center;
    }

    .time-cell {
        background: var(--dark-color);
        color: white;
        padding: 0.75rem;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        font-size: 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .session-block {
        padding: 0.8rem 0.6rem;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        max-width: 160px;
        margin: 0 auto;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .session-block::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s;
    }

    .session-block:hover::before {
        left: 100%;
    }

    .session-block:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .session-rehab { background: linear-gradient(135deg, var(--primary-color), var(--info-color)); color: white; }
    .session-therapy { background: linear-gradient(135deg, var(--success-color), #4ed573); color: white; }
    .session-education { background: linear-gradient(135deg, var(--warning-color), #ffb347); color: white; }
    .session-sports { background: linear-gradient(135deg, var(--secondary-color), #d565d0); color: white; }

    .sidebar-panels {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .panel {
        background: var(--warm-white);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease;
    }

    .panel:hover {
        transform: translateY(-2px);
    }

    .panel-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--soft-gray);
    }

    .panel-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.1rem;
        color: white;
    }

    .icon-stats { background: var(--primary-color); }
    .icon-activities { background: var(--success-color); }
    .icon-sessions { background: var(--secondary-color); }

    .panel-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
        margin: 0;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .metric-card {
        background: var(--soft-gray);
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .metric-card:hover {
        border-color: var(--primary-color);
        background: white;
        transform: scale(1.05);
    }

    .metric-number {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 0.25rem;
    }

    .metric-label {
        font-size: 0.8rem;
        color: #64748B;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .list-item {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        background: var(--soft-gray);
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }

    .list-item:hover {
        background: white;
        border-left-color: var(--primary-color);
        transform: translateX(4px);
    }

    .item-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
    }

    .item-meta {
        font-size: 0.85rem;
        color: #64748B;
    }

    .floating-hours {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: var(--dark-color);
        color: white;
        padding: 1.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(26, 42, 58, 0.3);
        text-align: center;
        min-width: 120px;
        z-index: 1000;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .hours-number {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .hours-label {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .btn-organic {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .btn-organic:hover {
        background: var(--info-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(50, 189, 234, 0.4);
        color: white;
    }

    .btn-light-organic {
        background: var(--warm-white);
        color: var(--dark-color);
        border: 2px solid var(--border-color);
    }

    .btn-light-organic:hover {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
    }

    .empty-state-organic {
        text-align: center;
        padding: 4rem 2rem;
        color: #64748B;
    }

    .empty-illustration {
        width: 150px;
        height: 150px;
        margin: 0 auto 2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .empty-illustration::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: spin 4s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 1024px) {
        .floating-hours {
            position: relative;
            margin: 2rem auto 0;
            animation: none;
        }
    }

    @media (max-width: 768px) {
        .week-grid {
            grid-template-columns: 100px 1fr;
            gap: 0;
        }
        
        .week-grid .day-header:not(:first-child):not(:nth-child(2)) {
            display: none;
        }
        
        .time-slot {
            flex-direction: column;
            align-items: stretch;
        }
        
        .time-label {
            margin-bottom: 1rem;
            margin-right: 0;
            font-size: 0.8rem;
            padding: 0.5rem;
        }
        
        .session-timeline {
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }
        
        .metric-grid {
            grid-template-columns: 1fr;
        }
        
        .time-header {
            font-size: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding-left: 0.5rem; padding-right: 1rem;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staffs.home') }}">Staff Directory</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staffs.profile', $staffMember->encrypted_id) }}">{{ $staffMember->name }}</a></li>
            <li class="breadcrumb-item active">Schedule</li>
        </ol>
    </nav>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show paper-card mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show paper-card mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Staff Header -->
    <div class="staff-header">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($staffMember->avatar)
                    <img src="{{ asset('storage/avatars/' . $staffMember->avatar) }}" 
                         alt="{{ $staffMember->name }}" class="staff-photo"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="staff-photo-placeholder" style="display: none;">
                        <i class="fas fa-user"></i>
                    </div>
                @else
                    <div class="staff-photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
            </div>
            <div class="col">
                <h1 class="staff-name">{{ $staffMember->name }}</h1>
                <p class="staff-subtitle">Weekly Schedule & Activity Overview</p>
                <div class="d-flex gap-3 align-items-center">
                    <span class="role-badge role-{{ strtolower($staffMember->role) }}">
                        {{ ucfirst($staffMember->role) }}
                    </span>
                </div>
                <p class="mt-3 mb-0" style="color: rgba(255, 255, 255, 0.9);">
                    <i class="fas fa-envelope me-2"></i>{{ $staffMember->email }}
                </p>
                <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                    <i class="fas fa-building me-2"></i>{{ $staffMember->centre->centre_name ?? 'No Centre Assigned' }}
                </p>
            </div>
            <div class="col-auto">
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="btn-organic me-3">
                    <i class="fas fa-plus"></i>
                    New Activity
                </a>
                @endif
                <a href="{{ route('staffs.profile', $staffMember->encrypted_id) }}" class="btn-organic btn-light-organic">
                    <i class="fas fa-arrow-left"></i>
                    Back to Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Info Panels Above Calendar -->
    <div class="row mb-4">
        <!-- Stats Panel -->
        <div class="col-md-4">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-icon icon-stats">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="panel-title">Overview Stats</h3>
                </div>
                
                <div class="metric-grid">
                    <div class="metric-card">
                        <div class="metric-number">{{ count($schedules) }}</div>
                        <div class="metric-label">Weekly Sessions</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-number">{{ isset($sessions) ? count($sessions) : 0 }}</div>
                        <div class="metric-label">Total Sessions</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-number">{{ count($activities) }}</div>
                        <div class="metric-label">Activities</div>
                    </div>
                    <div class="metric-card">
                        @php
                            $totalEnrolled = isset($sessions) ? collect($sessions)->sum('enrolled_count') : 0;
                        @endphp
                        <div class="metric-number">{{ $totalEnrolled }}</div>
                        <div class="metric-label">Total Enrolled</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Panel -->
        <div class="col-md-4">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-icon icon-activities">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="panel-title">My Activities</h3>
                </div>
                
                @if(count($activities) > 0)
                    @foreach($activities->take(4) as $activity)
                        <div class="list-item">
                            <div class="item-title">{{ Str::limit($activity->activity_name, 30) }}</div>
                            <div class="item-meta">
                                {{ $activity->category ?? 'General' }} • {{ $activity->duration_minutes ?? 60 }}min
                                @if($activity->is_active)
                                    <span style="color: var(--sage); font-weight: 600;"> • Active</span>
                                @else
                                    <span style="color: #64748B; font-weight: 600;"> • Inactive</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    
                    @if(count($activities) > 4)
                        <div class="text-center mt-2">
                            <small style="color: #64748B;">+ {{ count($activities) - 4 }} more activities</small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-3" style="color: #64748B;">
                        <i class="fas fa-tasks fa-2x mb-2"></i>
                        <p>No activities assigned</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sessions Panel -->
        <div class="col-md-4">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-icon icon-sessions">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="panel-title">Recent Sessions</h3>
                </div>
                
                @if(isset($sessions) && count($sessions) > 0)
                    @foreach($sessions->take(3) as $session)
                        <div class="list-item">
                            <div class="item-title">{{ Str::limit($session->activity_name, 25) }}</div>
                            <div class="item-meta">
                                {{ \Carbon\Carbon::parse($session->session_date)->format('M j, Y') }} at 
                                {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                <span style="color: var(--mint); font-weight: 600;">
                                    • {{ $session->enrolled_count }} enrolled
                                </span>
                            </div>
                        </div>
                    @endforeach
                    
                    @if(count($sessions) > 3)
                        <div class="text-center mt-2">
                            <small style="color: #64748B;">+ {{ count($sessions) - 3 }} more sessions</small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-3" style="color: #64748B;">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <p>No recent sessions</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Calendar Timeline View -->
    <div class="calendar-view">
        <div class="calendar-header">
            <h2 class="calendar-title">Weekly Schedule Calendar</h2>
        </div>

        <!-- Days of Week Header -->
        <div class="week-grid">
            <!-- Time Header -->
            <div class="time-header">
                <i class="fas fa-clock me-1"></i>
                Time
            </div>
            
            @php
                $today = now();
                $startOfWeek = $today->copy()->startOfWeek();
                $days = [
                    ['short' => 'Mon', 'full' => 'Monday'],
                    ['short' => 'Tue', 'full' => 'Tuesday'], 
                    ['short' => 'Wed', 'full' => 'Wednesday'],
                    ['short' => 'Thu', 'full' => 'Thursday'],
                    ['short' => 'Fri', 'full' => 'Friday'],
                    ['short' => 'Sat', 'full' => 'Saturday'],
                    ['short' => 'Sun', 'full' => 'Sunday']
                ];
            @endphp
            
            @foreach($days as $index => $day)
                @php
                    $dayDate = $startOfWeek->copy()->addDays($index);
                    $isToday = $dayDate->isToday();
                    $isWeekend = $dayDate->isWeekend();
                @endphp
                <div class="day-header {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}">
                    <div class="day-name">{{ $day['short'] }}</div>
                    <div class="day-date">{{ $dayDate->format('M j') }}</div>
                    @if($isToday)
                        <div class="time-now">{{ now()->format('g:i A') }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Time Slots -->
        <div class="time-slots">
            @if(count($schedules) > 0)
                @php
                    $groupedSchedules = collect($schedules)->groupBy('day_of_week');
                    // Get all unique real start times from the schedules, not hardcoded times
                    $timeSlots = collect($schedules)->pluck('start_time')->unique()->sort()->mapWithKeys(function($time) {
                        $formatted = date('H:i', strtotime($time));
                        $display = date('g:i A', strtotime($time));
                        return [$formatted => $display];
                    })->toArray();
                @endphp

                @foreach($timeSlots as $time => $displayTime)
                    <div class="time-slot">
                        <!-- Time Label -->
                        <div class="time-label">
                            {{ $displayTime }}
                        </div>
                        
                        <!-- Session Timeline -->
                        <div class="session-timeline">
                            @for($day = 1; $day <= 7; $day++)
                                @php
                                    $daySchedules = $groupedSchedules->get($day, collect());
                                    $sessionForTime = $daySchedules->first(function($schedule) use ($time) {
                                        return date('H:i', strtotime($schedule->start_time)) === $time;
                                    });
                                @endphp
                                
                                @if($sessionForTime)
                                    @php
                                        $category = strtolower($sessionForTime->category ?? 'general');
                                        $sessionClass = match($category) {
                                            'rehabilitation' => 'session-rehab',
                                            'therapy' => 'session-therapy',
                                            'education' => 'session-education',
                                            'sports' => 'session-sports',
                                            default => 'session-rehab'
                                        };
                                    @endphp
                                    <div class="session-block {{ $sessionClass }}" 
                                         onclick="window.location.href='{{ route('activities.sessions', $sessionForTime->activity_id) }}'"
                                         style="cursor: pointer;"
                                         title="Click to view {{ $sessionForTime->activity_name }} sessions">
                                        {{ Str::limit($sessionForTime->activity_name, 12) }}
                                        @if($sessionForTime->location)
                                            <br><small style="opacity: 0.8;">{{ $sessionForTime->location }}</small>
                                        @endif
                                    </div>
                                @else
                                    <div></div>
                                @endif
                            @endfor
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state-organic">
                    <div class="empty-illustration">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 1rem; color: var(--dark-color);">No Schedule Available</h4>
                    <p style="margin-bottom: 2rem;">This staff member doesn't have any scheduled activities yet.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="btn-organic">
                        <i class="fas fa-plus"></i>
                        Create First Activity
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Floating Weekly Hours -->
    <div class="floating-hours">
        @php
            $scheduleMinutes = collect($schedules)->sum(function($schedule) {
                return (strtotime($schedule->end_time ?? '00:00') - strtotime($schedule->start_time ?? '00:00')) / 60;
            });
            
            $sessionMinutes = isset($sessions) ? collect($sessions)->sum('duration_minutes') : 0;
            $totalMinutes = $sessionMinutes > 0 ? $sessionMinutes : $scheduleMinutes;
            $totalHours = round($totalMinutes / 60, 1);
        @endphp
        <div class="hours-number">{{ $totalHours }}h</div>
        <div class="hours-label">Weekly Hours</div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function viewSession(activityName) {
    // Show a nice notification about the session
    if (activityName) {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="background: var(--coral); color: white; padding: 1rem 2rem; border-radius: 12px; position: fixed; top: 2rem; right: 2rem; z-index: 9999; box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3); animation: slideIn 0.3s ease;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>${activityName}</strong>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 1.2rem; margin-left: 1rem; cursor: pointer;">×</button>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

function editActivity(activityId) {
    if (activityId && activityId > 0) {
        window.location.href = `/activities/${activityId}/edit`;
    } else {
        console.log('Activity ID not available');
    }
}

// Add hover effects and animations
document.addEventListener('DOMContentLoaded', function() {
    // Add some interactive sparkle to the page
    const floatingHours = document.querySelector('.floating-hours');
    if (floatingHours) {
        floatingHours.addEventListener('click', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
            setTimeout(() => {
                this.style.transform = '';
            }, 300);
        });
    }

    // Live time update for today's column
    function updateCurrentTime() {
        const timeNowElement = document.querySelector('.time-now');
        if (timeNowElement) {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            timeNowElement.textContent = timeString;
        }
    }

    // Update time every minute
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);

    // Add CSS keyframes for slideIn animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection