@extends('layouts.app')

@section('title', 'Parent Dashboard')

@push('styles')
<style>
    .parent-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #805ad5 0%, #9f7aea 100%);
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
        background: var(--card-color, #805ad5);
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
        background: linear-gradient(135deg, var(--card-color, #805ad5), var(--card-color-light, #9f7aea));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        box-shadow: 0 4px 15px rgba(128, 90, 213, 0.3);
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
    
    .children-overview-card {
        background: linear-gradient(135deg, #805ad5 0%, #9f7aea 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .overview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .overview-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .overview-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .child-card {
        background: rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .child-card:last-child {
        margin-bottom: 0;
    }
    
    .child-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .child-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        font-size: 13px;
    }
    
    .child-stat {
        text-align: center;
    }
    
    .child-stat-value {
        font-weight: 600;
        display: block;
        margin-bottom: 2px;
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
        background: linear-gradient(135deg, #805ad5, #9f7aea);
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
        border-color: #805ad5;
        color: #805ad5;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(128, 90, 213, 0.15);
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
        
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .child-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="parent-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2 font-weight-bold">Welcome, {{ $user['name'] ?? 'Parent' }}!</h1>
                    <p class="mb-0 opacity-90">Stay connected with your child's learning journey and progress.</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="performance-indicator">
                        <i class="fas fa-heart mr-2"></i>
                        {{ $stats['children_enrolled'] ?? 0 }} Child{{ ($stats['children_enrolled'] ?? 0) != 1 ? 'ren' : '' }} Enrolled
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
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['children_enrolled'] ?? 0 }}</div>
                <div class="stat-label">Children Enrolled</div>
                <div class="stat-change positive">
                    <i class="fas fa-heart"></i>
                    Active learners
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #48bb78; --card-color-light: #68d391;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['activities_this_month'] ?? 0 }}</div>
                <div class="stat-label">Activities This Month</div>
                <div class="stat-change positive">
                    <i class="fas fa-plus-circle"></i>
                    Active participation
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #ed8936; --card-color-light: #f6ad55;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</div>
                <div class="stat-label">Average Attendance</div>
                <div class="stat-change {{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'positive' : 'neutral' }}">
                    <i class="fas fa-{{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'arrow-up' : 'minus' }}"></i>
                    {{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'Excellent!' : 'Improving' }}
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #9f7aea; --card-color-light: #b794f6;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['progress_reports'] ?? 0 }}</div>
                <div class="stat-label">Progress Reports</div>
                <div class="stat-change positive">
                    <i class="fas fa-clipboard-list"></i>
                    Available for review
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Children Overview -->
                <div class="children-overview-card">
                    <div class="overview-header">
                        <div>
                            <div class="overview-title">Children Overview</div>
                            <div class="overview-subtitle">Track your children's progress and activities</div>
                        </div>
                        <div>
                            <i class="fas fa-child" style="font-size: 32px; opacity: 0.8;"></i>
                        </div>
                    </div>
                    
                    @if(isset($children_progress) && count($children_progress) > 0)
                        @foreach($children_progress as $child)
                            <div class="child-card">
                                <div class="child-name">{{ $child->name ?? 'Child' }}</div>
                                <div class="child-stats">
                                    <div class="child-stat">
                                        <span class="child-stat-value">{{ $child->activities_enrolled ?? 0 }}</span>
                                        <span>Activities</span>
                                    </div>
                                    <div class="child-stat">
                                        <span class="child-stat-value">{{ number_format($child->attendance_rate ?? 0, 1) }}%</span>
                                        <span>Attendance</span>
                                    </div>
                                    <div class="child-stat">
                                        <span class="child-stat-value">{{ $child->progress_score ?? 0 }}%</span>
                                        <span>Progress</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="child-card">
                            <div class="child-name">No children enrolled yet</div>
                            <p class="mb-0 opacity-80">Contact the centre to enroll your children in activities.</p>
                        </div>
                    @endif
                </div>

                <!-- Upcoming Activities -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Upcoming Activities</h3>
                        <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-purple">View All Activities</a>
                    </div>
                    
                    @if(isset($upcoming_activities) && count($upcoming_activities) > 0)
                        @foreach($upcoming_activities as $activity)
                            <div class="activity-item">
                                <div class="activity-status">
                                    {{ isset($activity->session_date) ? $activity->session_date->format('M j') : 'TBD' }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $activity->name ?? 'Activity' }}</div>
                                    <div class="activity-meta">
                                        {{ $activity->category ?? 'General' }} • 
                                        {{ isset($activity->start_time) ? $activity->start_time->format('g:i A') : 'Time TBD' }} • 
                                        {{ $activity->child_name ?? 'Your child' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar fa-2x mb-3 text-muted"></i>
                            <p>No upcoming activities scheduled. Check back later for updates!</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Reports -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Recent Progress Reports</h3>
                        <a href="{{ route('reports.parent') }}" class="btn btn-sm btn-outline-purple">View All Reports</a>
                    </div>
                    
                    @if(isset($recent_reports) && count($recent_reports) > 0)
                        @foreach($recent_reports as $report)
                            <div class="activity-item">
                                <div class="activity-status" style="background: #4299e1;">
                                    {{ isset($report->created_at) ? $report->created_at->format('M j') : 'Recent' }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $report->child_name ?? 'Child' }} - {{ $report->title ?? 'Progress Report' }}</div>
                                    <div class="activity-meta">{{ $report->summary ?? 'Click to view full report' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0">No progress reports available yet. Reports will appear here as activities are completed.</p>
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
                        <a href="{{ route('children.index') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="action-label">My Children</div>
                        </a>
                        
                        <a href="{{ route('reports.parent') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-label">Progress Reports</div>
                        </a>
                        
                        <a href="{{ route('activities.index') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="action-label">Activities</div>
                        </a>
                        
                        <a href="{{ route('messages.index') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="action-label">Messages</div>
                        </a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Notifications</h3>
                    </div>
                    
                    <ul class="upcoming-list">
                        @if(isset($notifications) && count($notifications) > 0)
                            @foreach(array_slice($notifications, 0, 5) as $notification)
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

                <!-- Contact Information -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Need Help?</h3>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-phone mr-2"></i>
                        <strong>Centre Contact:</strong><br>
                        Have questions? Contact your centre for assistance with your child's activities.
                    </div>
                    
                    <a href="{{ route('contact') }}" class="btn btn-outline-purple btn-block">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Centre
                    </a>
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

<style>
.btn-outline-purple {
    color: #805ad5;
    border-color: #805ad5;
}

.btn-outline-purple:hover {
    color: white;
    background-color: #805ad5;
    border-color: #805ad5;
}
</style>
@endpush