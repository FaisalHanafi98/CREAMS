@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@push('styles')
<style>
    .teacher-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
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
        background: var(--card-color, #48bb78);
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
        background: linear-gradient(135deg, var(--card-color, #48bb78), var(--card-color-light, #68d391));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
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
    
    .schedule-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .schedule-item:last-child {
        border-bottom: none;
    }
    
    .schedule-time {
        background: linear-gradient(135deg, #48bb78, #68d391);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
        margin-right: 15px;
        min-width: 80px;
        text-align: center;
    }
    
    .schedule-info {
        flex: 1;
    }
    
    .schedule-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }
    
    .schedule-meta {
        font-size: 13px;
        color: #718096;
    }
    
    .schedule-status {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-scheduled { background: #bee3f8; color: #2b6cb0; }
    .status-ongoing { background: #fbb6ce; color: #b83280; }
    .status-completed { background: #c6f6d5; color: #276749; }
    
    .performance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .performance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .performance-score {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .performance-label {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .performance-metrics {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .metric {
        text-align: center;
    }
    
    .metric-value {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .metric-label {
        font-size: 12px;
        opacity: 0.8;
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
        border-color: #48bb78;
        color: #48bb78;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(72, 187, 120, 0.15);
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
        
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="teacher-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2 font-weight-bold">Welcome back, {{ $user['name'] ?? 'Teacher' }}!</h1>
                    <p class="mb-0 opacity-90">Ready to inspire and educate? Here's your teaching overview for today.</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="performance-indicator">
                        <i class="fas fa-clock mr-2"></i>
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
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['today_sessions'] ?? 0 }}</div>
                <div class="stat-label">Today's Sessions</div>
                <div class="stat-change {{ ($stats['pending_attendance'] ?? 0) > 0 ? 'urgent' : 'positive' }}">
                    @if(($stats['pending_attendance'] ?? 0) > 0)
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $stats['pending_attendance'] }} need attendance
                    @else
                        <i class="fas fa-check-circle"></i>
                        All up to date
                    @endif
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #48bb78; --card-color-light: #68d391;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['total_trainees'] ?? 0 }}</div>
                <div class="stat-label">Active Trainees</div>
                <div class="stat-change positive">
                    <i class="fas fa-users"></i>
                    Across all sessions
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #ed8936; --card-color-light: #f6ad55;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['week_sessions'] ?? 0 }}</div>
                <div class="stat-label">This Week's Sessions</div>
                <div class="stat-change neutral">
                    <i class="fas fa-calendar"></i>
                    {{ $stats['completed_sessions'] ?? 0 }} completed
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #9f7aea; --card-color-light: #b794f6;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['average_attendance'] ?? 0, 1) }}%</div>
                <div class="stat-label">Attendance Rate</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    Monthly average
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Performance Summary -->
                <div class="performance-card">
                    <div class="performance-header">
                        <div>
                            <div class="performance-score">{{ $performance['overall_score'] ?? 85 }}%</div>
                            <div class="performance-label">Teaching Performance Score</div>
                        </div>
                        <div>
                            <i class="fas fa-trophy" style="font-size: 32px; opacity: 0.8;"></i>
                        </div>
                    </div>
                    <div class="performance-metrics">
                        <div class="metric">
                            <div class="metric-value">{{ $performance['attendance_rate'] ?? 88 }}%</div>
                            <div class="metric-label">Attendance Rate</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">{{ $performance['completion_rate'] ?? 92 }}%</div>
                            <div class="metric-label">Session Completion</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">{{ $performance['trainee_progress'] ?? 78 }}%</div>
                            <div class="metric-label">Trainee Progress</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">{{ $performance['feedback_score'] ?? 4.2 }}/5</div>
                            <div class="metric-label">Feedback Score</div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Today's Schedule</h3>
                        <a href="{{ route('activities.schedule') }}" class="btn btn-sm btn-outline-success">View Full Schedule</a>
                    </div>
                    
                    @if(isset($schedule['today']) && count($schedule['today']) > 0)
                        @foreach($schedule['today'] as $session)
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    {{ isset($session->start_time) ? $session->start_time->format('H:i') : 'TBD' }}
                                </div>
                                <div class="schedule-info">
                                    <div class="schedule-title">{{ $session->activity_name ?? 'Session' }}</div>
                                    <div class="schedule-meta">
                                        {{ $session->participants_count ?? 0 }} participants • 
                                        {{ $session->location ?? 'Main Hall' }}
                                    </div>
                                </div>
                                <div class="schedule-status status-{{ strtolower($session->status ?? 'scheduled') }}">
                                    {{ ucfirst($session->status ?? 'scheduled') }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-3 text-muted"></i>
                            <p>No sessions scheduled for today. Enjoy your free time!</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Trainees Performance -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Trainee Progress Overview</h3>
                        <a href="{{ route('trainees.index') }}" class="btn btn-sm btn-outline-success">View All Trainees</a>
                    </div>
                    
                    @if(isset($trainees['progress_alerts']) && count($trainees['progress_alerts']) > 0)
                        @foreach($trainees['progress_alerts'] as $alert)
                            <div class="schedule-item">
                                <div class="schedule-time" style="background: {{ $alert['type'] === 'improvement' ? '#48bb78' : '#f6ad55' }};">
                                    {{ $alert['type'] === 'improvement' ? '↗' : '⚠' }}
                                </div>
                                <div class="schedule-info">
                                    <div class="schedule-title">{{ $alert['trainee_name'] ?? 'Unknown' }}</div>
                                    <div class="schedule-meta">{{ $alert['message'] ?? 'Progress update' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0">All trainees are progressing well! No alerts at this time.</p>
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
                        <a href="{{ route('activities.home') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="action-label">Mark Attendance</div>
                        </a>
                        
                        <a href="{{ route('activities.create') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-label">New Session</div>
                        </a>
                        
                        <a href="{{ route('trainees.index') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="action-label">View Trainees</div>
                        </a>
                        
                        <a href="{{ route('reports.teacher') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-label">My Reports</div>
                        </a>
                    </div>
                </div>

                <!-- Upcoming Sessions -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Upcoming Sessions</h3>
                    </div>
                    
                    <ul class="upcoming-list">
                        @if(isset($schedule['upcoming']) && count($schedule['upcoming']) > 0)
                            @foreach(array_slice($schedule['upcoming'], 0, 5) as $session)
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

                <!-- Recent Enrollments -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Recent Enrollments</h3>
                    </div>
                    
                    <ul class="upcoming-list">
                        @if(isset($trainees['recent_enrollments']) && count($trainees['recent_enrollments']) > 0)
                            @foreach($trainees['recent_enrollments'] as $enrollment)
                                <li class="upcoming-item">
                                    <div class="upcoming-title">{{ $enrollment->trainee_name ?? 'New Trainee' }}</div>
                                    <div class="upcoming-time">
                                        Enrolled {{ isset($enrollment->created_at) ? $enrollment->created_at->diffForHumans() : 'recently' }}
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="upcoming-item">
                                <div class="upcoming-title">No recent enrollments</div>
                                <div class="upcoming-time">Check back later</div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Updates Indicator -->
<div id="updateIndicator" class="position-fixed" style="top: 20px; right: 20px; z-index: 1050; display: none;">
    <div class="badge badge-success">
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
    updateInterval = setInterval(fetchUpdates, 45000); // Every 45 seconds for teachers
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