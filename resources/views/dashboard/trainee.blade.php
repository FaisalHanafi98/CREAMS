@extends('layouts.app')

@section('title', 'Trainee Dashboard')

@push('styles')
<style>
    .trainee-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
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
        background: var(--card-color, #38a169);
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
        background: linear-gradient(135deg, var(--card-color, #38a169), var(--card-color-light, #48bb78));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        box-shadow: 0 4px 15px rgba(56, 161, 105, 0.3);
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
    
    .progress-card {
        background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .progress-score {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .progress-label {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-status {
        background: linear-gradient(135deg, #38a169, #48bb78);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
        margin-right: 15px;
        min-width: 80px;
        text-align: center;
    }
    
    .activity-info {
        flex: 1;
    }
    
    .activity-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }
    
    .activity-meta {
        font-size: 13px;
        color: #718096;
    }
    
    .upcoming-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .upcoming-item {
        padding: 12px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .upcoming-item:last-child {
        border-bottom: none;
    }
    
    .upcoming-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .upcoming-time {
        font-size: 12px;
        color: #a0aec0;
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
        
        .performance-indicator {
            position: static;
            margin-top: 15px;
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="trainee-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2 font-weight-bold">Welcome, {{ $user['name'] ?? 'Trainee' }}!</h1>
                    <p class="mb-0 opacity-90">Ready to learn and grow? Here's your progress overview.</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="performance-indicator">
                        <i class="fas fa-star mr-2"></i>
                        Progress Score: {{ $stats['progress_score'] ?? 0 }}%
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
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['activities_enrolled'] ?? 0 }}</div>
                <div class="stat-label">Activities Enrolled</div>
                <div class="stat-change positive">
                    <i class="fas fa-plus-circle"></i>
                    Active enrollments
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #48bb78; --card-color-light: #68d391;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['activities_completed'] ?? 0 }}</div>
                <div class="stat-label">Activities Completed</div>
                <div class="stat-change positive">
                    <i class="fas fa-trophy"></i>
                    Well done!
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #ed8936; --card-color-light: #f6ad55;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</div>
                <div class="stat-label">Attendance Rate</div>
                <div class="stat-change {{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'positive' : 'neutral' }}">
                    <i class="fas fa-{{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'arrow-up' : 'minus' }}"></i>
                    {{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'Excellent!' : 'Keep it up!' }}
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #9f7aea; --card-color-light: #b794f6;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['progress_score'] ?? 0, 1) }}%</div>
                <div class="stat-label">Overall Progress</div>
                <div class="stat-change positive">
                    <i class="fas fa-star"></i>
                    Personal best
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Progress Summary -->
                <div class="progress-card">
                    <div class="progress-header">
                        <div>
                            <div class="progress-score">{{ $stats['progress_score'] ?? 0 }}%</div>
                            <div class="progress-label">Learning Progress</div>
                        </div>
                        <div>
                            <i class="fas fa-user-graduate" style="font-size: 32px; opacity: 0.8;"></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Activities Enrolled:</strong> {{ $stats['activities_enrolled'] ?? 0 }}</p>
                            <p class="mb-2"><strong>Activities Completed:</strong> {{ $stats['activities_completed'] ?? 0 }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Attendance Rate:</strong> {{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</p>
                            <p class="mb-2"><strong>Progress Reports:</strong> {{ count($progress_reports ?? []) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">My Recent Activities</h3>
                        <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-success">View All Activities</a>
                    </div>
                    
                    @if(isset($recent_activities) && count($recent_activities) > 0)
                        @foreach($recent_activities as $activity)
                            <div class="activity-item">
                                <div class="activity-status">
                                    {{ $activity->status ?? 'Active' }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $activity->name ?? 'Activity' }}</div>
                                    <div class="activity-meta">
                                        {{ $activity->category ?? 'General' }} • 
                                        Last session: {{ isset($activity->last_session) ? $activity->last_session->format('M j') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-graduation-cap fa-2x mb-3 text-muted"></i>
                            <p>No recent activities. Check with your teacher for new enrollments!</p>
                        </div>
                    @endif
                </div>

                <!-- Progress Reports -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">My Progress Reports</h3>
                        <a href="{{ route('reports.trainee') }}" class="btn btn-sm btn-outline-success">View All Reports</a>
                    </div>
                    
                    @if(isset($progress_reports) && count($progress_reports) > 0)
                        @foreach($progress_reports as $report)
                            <div class="activity-item">
                                <div class="activity-status" style="background: #4299e1;">
                                    {{ isset($report->created_at) ? $report->created_at->format('M j') : 'Recent' }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $report->title ?? 'Progress Report' }}</div>
                                    <div class="activity-meta">{{ $report->summary ?? 'No summary available' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0">No progress reports available yet. Keep participating to get your first report!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="sidebar-content">
                <!-- Upcoming Sessions -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Upcoming Sessions</h3>
                    </div>
                    
                    <ul class="upcoming-list">
                        @if(isset($upcoming_sessions) && count($upcoming_sessions) > 0)
                            @foreach(array_slice($upcoming_sessions, 0, 5) as $session)
                                <li class="upcoming-item">
                                    <div class="upcoming-title">{{ $session->activity_name ?? 'Session' }}</div>
                                    <div class="upcoming-time">
                                        {{ isset($session->session_date) ? $session->session_date->format('M j, g:i A') : 'TBD' }}
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="upcoming-item">
                                <div class="upcoming-title">No upcoming sessions</div>
                                <div class="upcoming-time">Schedule is clear for now</div>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Notifications -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Notifications</h3>
                    </div>
                    
                    <ul class="upcoming-list">
                        @if(isset($notifications) && count($notifications) > 0)
                            @foreach(array_slice($notifications, 0, 3) as $notification)
                                <li class="upcoming-item">
                                    <div class="upcoming-title">{{ $notification->title ?? 'Notification' }}</div>
                                    <div class="upcoming-time">
                                        {{ isset($notification->created_at) ? $notification->created_at->diffForHumans() : 'Recently' }}
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="upcoming-item">
                                <div class="upcoming-title">No new notifications</div>
                                <div class="upcoming-time">You're all caught up!</div>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Quick Tips -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Learning Tips</h3>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb mr-2"></i>
                        <strong>Pro Tip:</strong> Regular attendance helps you make faster progress in your learning journey!
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-trophy mr-2"></i>
                        <strong>Achievement:</strong> Keep up your great attendance rate!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeStatCounters();
});

// Initialize stat counters with animation
function initializeStatCounters() {
    const statElements = document.querySelectorAll('.stat-value');
    statElements.forEach(element => {
        const finalValue = parseFloat(element.textContent.replace(/[^0-9.]/g, ''));
        element.textContent = '0';
        animateValue(element, 0, finalValue, 1500);
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
            if (element.textContent.includes('%')) {
                element.textContent = Math.floor(end) + '%';
            } else {
                element.textContent = Math.floor(end);
            }
            clearInterval(timer);
        } else {
            if (element.textContent.includes('%')) {
                element.textContent = Math.floor(current) + '%';
            } else {
                element.textContent = Math.floor(current);
            }
        }
    }, 16);
}
</script>
@endpush