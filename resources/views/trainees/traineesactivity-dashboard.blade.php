@extends('layouts.app')

@section('title', 'My Activities')

@section('styles')
<style>
    .activity-dashboard {
        padding: 20px;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    
    .schedule-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }
    
    .schedule-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .schedule-item {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        transition: background 0.3s ease;
    }
    
    .schedule-item:hover {
        background: #f8f9fa;
    }
    
    .schedule-time {
        background: var(--primary-color);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: 600;
        margin-right: 20px;
        min-width: 100px;
        text-align: center;
    }
    
    .schedule-details h6 {
        margin: 0 0 5px 0;
        color: var(--dark-color);
    }
    
    .schedule-details p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    
    .progress-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }
    
    .progress-item {
        margin-bottom: 20px;
    }
    
    .progress-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .progress-bar-container {
        background: #f0f0f0;
        border-radius: 10px;
        height: 10px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
</style>
@endsection

@section('content')
<div class="activity-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-card">
        <h1>Welcome back, {{ $trainee->trainee_first_name }}!</h1>
        <p>Here's your activity overview for today, {{ date('l, F j, Y') }}</p>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(50, 189, 234, 0.1); color: var(--primary-color);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value">{{ $enrollments->count() }}</div>
            <div class="stat-label">Enrolled Activities</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(46, 213, 115, 0.1); color: #2ed573;">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-value">{{ $attendanceStats['rate'] }}%</div>
            <div class="stat-label">Attendance Rate (30 days)</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(255, 165, 2, 0.1); color: #ffa502;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">{{ $todaySchedule->count() }}</div>
            <div class="stat-label">Classes Today</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(200, 80, 192, 0.1); color: var(--secondary-color);">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-value">{{ $attendanceStats['present'] }}</div>
            <div class="stat-label">Classes Attended</div>
        </div>
    </div>
    
    <div class="row">
        <!-- Today's Schedule -->
        <div class="col-lg-8">
            <div class="schedule-card">
                <div class="schedule-header">
                    <h4 class="mb-0">Today's Schedule</h4>
                </div>
                <div class="schedule-body">
                    @forelse($todaySchedule as $enrollment)
                    <div class="schedule-item">
                        <div class="schedule-time">
                            {{ Carbon\Carbon::parse($enrollment->session->start_time)->format('g:i A') }}
                        </div>
                        <div class="schedule-details">
                            <h6>{{ $enrollment->session->activity->activity_name }}</h6>
                            <p>
                                <i class="fas fa-user"></i> {{ $enrollment->session->teacher->name }} | 
                                <i class="fas fa-map-marker-alt"></i> {{ $enrollment->session->location ?? 'TBA' }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h5>No Classes Today</h5>
                        <p>Enjoy your day off!</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <!-- My Progress -->
            <div class="progress-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">My Progress</h4>
                    <a href="{{ route('trainee.progress') }}" class="btn btn-sm btn-primary">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                @foreach($enrollments->take(3) as $enrollment)
                <div class="progress-item">
                    <div class="progress-header">
                        <h6>{{ $enrollment->session->activity->activity_name }}</h6>
                        <span>{{ $enrollment->attendance->whereIn('status', ['Present', 'Late'])->count() }}/{{ $enrollment->attendance->count() }} classes</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: {{ $enrollment->attendance->count() > 0 ? ($enrollment->attendance->whereIn('status', ['Present', 'Late'])->count() / $enrollment->attendance->count() * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Upcoming Activities -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Activities</h5>
                </div>
                <div class="card-body">
                    @forelse($upcomingActivities as $day)
                    <div class="mb-3">
                        <h6 class="text-primary">{{ $day['date']->format('l, M j') }}</h6>
                        @foreach($day['sessions'] as $enrollment)
                        <div class="pl-3 mb-2">
                            <small class="d-block">
                                <strong>{{ Carbon\Carbon::parse($enrollment->session->start_time)->format('g:i A') }}</strong> - 
                                {{ $enrollment->session->activity->activity_name }}
                            </small>
                        </div>
                        @endforeach
                    </div>
                    @empty
                    <p class="text-muted text-center">No upcoming activities</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('trainee.schedule') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-calendar"></i> View Full Schedule
                    </a>
                    <a href="{{ route('trainee.progress') }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-chart-line"></i> View Progress Report
                    </a>
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-search"></i> Browse Activities
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection