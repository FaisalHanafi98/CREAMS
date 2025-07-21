@extends('layouts.app')

@section('title', 'Supervisor Dashboard')

@push('styles')
<style>
    .supervisor-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
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
        background: var(--card-color, #ed8936);
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
        background: linear-gradient(135deg, var(--card-color, #ed8936), var(--card-color-light, #f6ad55));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        box-shadow: 0 4px 15px rgba(237, 137, 54, 0.3);
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
    .stat-change.warning { color: #f6ad55; }
    
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
    
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .team-card {
        background: #f7fafc;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        border-left: 4px solid #ed8936;
    }
    
    .team-card:hover {
        background: #edf2f7;
        transform: translateY(-2px);
    }
    
    .team-member {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .member-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ed8936, #f6ad55);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: 600;
        color: white;
        font-size: 16px;
    }
    
    .member-info {
        flex: 1;
    }
    
    .member-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }
    
    .member-role {
        font-size: 13px;
        color: #718096;
    }
    
    .member-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        font-size: 12px;
    }
    
    .member-stat {
        text-align: center;
        padding: 8px;
        background: white;
        border-radius: 8px;
    }
    
    .stat-number {
        font-weight: 700;
        color: #2d3748;
        display: block;
    }
    
    .stat-text {
        color: #718096;
        font-size: 11px;
    }
    
    .schedule-overview {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .schedule-title {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }
    
    .schedule-date {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .schedule-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    
    .schedule-stat {
        text-align: center;
    }
    
    .schedule-stat-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .schedule-stat-label {
        font-size: 12px;
        opacity: 0.8;
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
    
    .activity-time {
        background: linear-gradient(135deg, #ed8936, #f6ad55);
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
    
    .activity-status {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-active { background: #c6f6d5; color: #276749; }
    .status-scheduled { background: #bee3f8; color: #2b6cb0; }
    .status-completed { background: #e6fffa; color: #234e52; }
    
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
        border-color: #ed8936;
        color: #ed8936;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(237, 137, 54, 0.15);
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
    
    .alert-item {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 10px;
        border-left: 4px solid;
    }
    
    .alert-item.info {
        background: #ebf8ff;
        border-color: #4299e1;
    }
    
    .alert-item.warning {
        background: #fffbeb;
        border-color: #f6ad55;
    }
    
    .alert-item.success {
        background: #f0fff4;
        border-color: #48bb78;
    }
    
    .alert-icon {
        margin-right: 12px;
        margin-top: 2px;
        font-size: 16px;
    }
    
    .alert-content {
        flex: 1;
    }
    
    .alert-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .alert-message {
        color: #718096;
        font-size: 13px;
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
        
        .team-grid {
            grid-template-columns: 1fr;
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
<div class="supervisor-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2 font-weight-bold">Supervisor Dashboard</h1>
                    <p class="mb-0 opacity-90">Welcome back, {{ $user['name'] ?? 'Supervisor' }}! Manage your centre effectively.</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="performance-indicator">
                        <i class="fas fa-tachometer-alt mr-2"></i>
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
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['centre_trainees'] ?? 0 }}</div>
                <div class="stat-label">Centre Trainees</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    Active participants
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #48bb78; --card-color-light: #68d391;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['centre_teachers'] ?? 0 }}</div>
                <div class="stat-label">Teaching Staff</div>
                <div class="stat-change neutral">
                    <i class="fas fa-users"></i>
                    Your team
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #ed8936; --card-color-light: #f6ad55;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['today_sessions'] ?? 0 }}</div>
                <div class="stat-label">Today's Sessions</div>
                <div class="stat-change positive">
                    <i class="fas fa-calendar-check"></i>
                    In progress
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #9f7aea; --card-color-light: #b794f6;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</div>
                <div class="stat-label">Attendance Rate</div>
                <div class="stat-change {{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'positive' : 'warning' }}">
                    <i class="fas fa-percentage"></i>
                    Centre average
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Schedule Overview -->
                <div class="schedule-overview">
                    <div class="schedule-header">
                        <div>
                            <div class="schedule-title">Today's Centre Overview</div>
                            <div class="schedule-date">{{ now()->format('l, F j, Y') }}</div>
                        </div>
                        <div>
                            <i class="fas fa-building" style="font-size: 32px; opacity: 0.8;"></i>
                        </div>
                    </div>
                    <div class="schedule-stats">
                        <div class="schedule-stat">
                            <div class="schedule-stat-value">{{ $schedule['today_count'] ?? 0 }}</div>
                            <div class="schedule-stat-label">Sessions Today</div>
                        </div>
                        <div class="schedule-stat">
                            <div class="schedule-stat-value">{{ $schedule['week_count'] ?? 0 }}</div>
                            <div class="schedule-stat-label">This Week</div>
                        </div>
                        <div class="schedule-stat">
                            <div class="schedule-stat-value">{{ number_format($stats['completion_rate'] ?? 0, 1) }}%</div>
                            <div class="schedule-stat-label">Completion Rate</div>
                        </div>
                    </div>
                </div>

                <!-- Team Performance -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Team Performance</h3>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-warning">Manage Team</a>
                    </div>
                    
                    <div class="team-grid">
                        @if(isset($team['teachers']) && count($team['teachers']) > 0)
                            @foreach($team['teachers'] as $teacher)
                                <div class="team-card">
                                    <div class="team-member">
                                        <div class="member-avatar">
                                            {{ strtoupper(substr($teacher->name ?? 'T', 0, 1)) }}
                                        </div>
                                        <div class="member-info">
                                            <div class="member-name">{{ $teacher->name ?? 'Unknown' }}</div>
                                            <div class="member-role">{{ ucfirst($teacher->role ?? 'teacher') }}</div>
                                        </div>
                                    </div>
                                    <div class="member-stats">
                                        <div class="member-stat">
                                            <span class="stat-number">{{ $teacher->sessions_count ?? 0 }}</span>
                                            <span class="stat-text">Sessions</span>
                                        </div>
                                        <div class="member-stat">
                                            <span class="stat-number">{{ $teacher->attendance_rate ?? 0 }}%</span>
                                            <span class="stat-text">Attendance</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-2x mb-3 text-muted"></i>
                                <p>No teaching staff assigned to your centre yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Today's Activities -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Today's Activities</h3>
                        <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
                    </div>
                    
                    @if(isset($schedule['today']) && count($schedule['today']) > 0)
                        @foreach($schedule['today'] as $activity)
                            <div class="activity-item">
                                <div class="activity-time">
                                    {{ isset($activity->start_time) ? $activity->start_time->format('H:i') : 'TBD' }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $activity->name ?? 'Activity' }}</div>
                                    <div class="activity-meta">
                                        Teacher: {{ $activity->teacher_name ?? 'Unassigned' }} • 
                                        {{ $activity->participants_count ?? 0 }} participants
                                    </div>
                                </div>
                                <div class="activity-status status-{{ strtolower($activity->status ?? 'scheduled') }}">
                                    {{ ucfirst($activity->status ?? 'scheduled') }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-3 text-muted"></i>
                            <p>No activities scheduled for today at your centre.</p>
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
                        <a href="{{ route('users.create') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-label">Add Staff</div>
                        </a>
                        
                        <a href="{{ route('activities.create') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-label">New Activity</div>
                        </a>
                        
                        <a href="{{ route('reports.supervisor') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-label">Centre Reports</div>
                        </a>
                        
                        <a href="{{ route('trainees.index') }}" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="action-label">Manage Trainees</div>
                        </a>
                    </div>
                </div>

                <!-- Centre Alerts -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Centre Alerts</h3>
                    </div>
                    
                    @if(isset($alerts) && count($alerts) > 0)
                        @foreach($alerts as $alert)
                            <div class="alert-item {{ $alert['type'] ?? 'info' }}">
                                <div class="alert-icon">
                                    @if(($alert['type'] ?? 'info') === 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    @elseif(($alert['type'] ?? 'info') === 'success')
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-info-circle text-info"></i>
                                    @endif
                                </div>
                                <div class="alert-content">
                                    <div class="alert-title">{{ $alert['title'] ?? 'Notification' }}</div>
                                    <div class="alert-message">{{ $alert['message'] ?? 'No details available' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                            <p class="mb-0">All systems running smoothly!</p>
                        </div>
                    @endif
                </div>

                <!-- Weekly Schedule Summary -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">This Week</h3>
                    </div>
                    
                    <div class="schedule-stats">
                        <div class="schedule-stat text-center mb-3">
                            <div class="schedule-stat-value text-primary">{{ $schedule['week']['total_sessions'] ?? 0 }}</div>
                            <div class="schedule-stat-label text-muted">Total Sessions</div>
                        </div>
                        <div class="schedule-stat text-center mb-3">
                            <div class="schedule-stat-value text-success">{{ $schedule['week']['completed'] ?? 0 }}</div>
                            <div class="schedule-stat-label text-muted">Completed</div>
                        </div>
                        <div class="schedule-stat text-center">
                            <div class="schedule-stat-value text-warning">{{ $schedule['week']['upcoming'] ?? 0 }}</div>
                            <div class="schedule-stat-label text-muted">Upcoming</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Trainees -->
                <div class="card-section">
                    <div class="section-header">
                        <h3 class="section-title">Recent Enrollments</h3>
                    </div>
                    
                    @if(isset($team['trainees']) && count($team['trainees']) > 0)
                        @foreach(array_slice($team['trainees'], 0, 5) as $trainee)
                            <div class="activity-item">
                                <div class="member-avatar" style="width: 35px; height: 35px; font-size: 14px; margin-right: 10px;">
                                    {{ strtoupper(substr($trainee->name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-title">{{ $trainee->name ?? 'Unknown' }}</div>
                                    <div class="activity-meta">
                                        Enrolled {{ isset($trainee->created_at) ? $trainee->created_at->diffForHumans() : 'recently' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0">No recent enrollments</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Updates Indicator -->
<div id="updateIndicator" class="position-fixed" style="top: 20px; right: 20px; z-index: 1050; display: none;">
    <div class="badge badge-warning">
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
    updateInterval = setInterval(fetchUpdates, 60000); // Every 60 seconds for supervisors
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