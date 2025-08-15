@extends('layouts.app')

@section('title', 'Teacher Dashboard - CREAMS')

@push('styles')
<style>
    :root {
        --teacher-primary: #48bb78;
        --teacher-secondary: #68d391;
        --teacher-accent: #9ae6b4;
        --dark-color: #2d3748;
        --light-bg: #f7fafc;
        --border-color: #e2e8f0;
        --gradient-teacher: linear-gradient(135deg, var(--teacher-primary), var(--teacher-secondary));
    }

    /* Teacher Dashboard Container */
    .teacher-dashboard {
        background: var(--light-bg);
        min-height: 100vh;
        padding: 0;
    }

    /* Dashboard Customization Panel */
    .dashboard-customization {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 20px rgba(0,0,0,0.1);
        z-index: 1050;
        transition: right 0.3s ease;
        border-left: 3px solid var(--teacher-primary);
    }

    .dashboard-customization.active {
        right: 0;
    }

    .customization-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--gradient-teacher);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .widget-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
    }

    .toggle-slider {
        width: 50px;
        height: 25px;
        background: #ccc;
        border-radius: 25px;
        position: relative;
        transition: background 0.3s;
    }

    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 21px;
        height: 21px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: transform 0.3s;
    }

    .widget-toggle input:checked + .toggle-slider {
        background: var(--teacher-primary);
    }

    .widget-toggle input:checked + .toggle-slider::before {
        transform: translateX(25px);
    }

    /* Enhanced Teacher Header */
    .teacher-header-enhanced {
        background: var(--gradient-teacher);
        color: white;
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
        border-radius: 0 0 30px 30px;
        margin-bottom: 30px;
    }

    .teacher-header-enhanced::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.15);
        border-radius: 50%;
        animation: teacherFloat 6s ease-in-out infinite;
    }

    @keyframes teacherFloat {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }

    .welcome-section h1 {
        font-size: 2.8rem;
        font-weight: 700;
        margin: 0;
        text-shadow: 0 3px 6px rgba(0,0,0,0.3);
        position: relative;
        z-index: 2;
    }

    .teacher-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-top: 10px;
        position: relative;
        z-index: 2;
    }

    /* Enhanced Statistics Grid */
    .teacher-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin: -60px 30px 30px;
        position: relative;
        z-index: 10;
    }

    .teacher-stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border-left: 5px solid var(--teacher-primary);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .teacher-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 70px;
        height: 70px;
        background: linear-gradient(45deg, rgba(72, 187, 120, 0.1), rgba(104, 211, 145, 0.1));
        border-radius: 50%;
        transform: translate(25px, -25px);
    }

    .teacher-stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .teacher-stat-card.sessions { border-left-color: var(--teacher-primary); }
    .teacher-stat-card.trainees { border-left-color: var(--teacher-secondary); }
    .teacher-stat-card.attendance { border-left-color: #38b2ac; }
    .teacher-stat-card.progress { border-left-color: #4299e1; }

    .stat-icon-teacher {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .stat-icon-teacher.primary { background: var(--gradient-teacher); }
    .stat-icon-teacher.secondary { background: linear-gradient(135deg, var(--teacher-secondary), var(--teacher-accent)); }
    .stat-icon-teacher.teal { background: linear-gradient(135deg, #38b2ac, #4fd1c7); }
    .stat-icon-teacher.blue { background: linear-gradient(135deg, #4299e1, #63b3ed); }

    .stat-number-teacher {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--dark-color);
        margin-bottom: 8px;
        position: relative;
        z-index: 2;
        line-height: 1;
    }

    .stat-label-teacher {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    /* Enhanced Main Content Grid */
    .teacher-content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        padding: 0 30px 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Enhanced Widgets */
    .teacher-widget-enhanced {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .teacher-widget-enhanced:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .widget-header-teacher {
        background: var(--gradient-teacher);
        color: white;
        padding: 25px;
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .widget-title-teacher {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .widget-content-teacher {
        padding: 25px;
        max-height: 500px;
        overflow-y: auto;
    }

    /* Schedule Items */
    .schedule-item-teacher {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 12px;
        background: #f7fafc;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .schedule-item-teacher:hover {
        background: white;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .schedule-time-teacher {
        background: var(--gradient-teacher);
        color: white;
        padding: 12px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        margin-right: 15px;
        min-width: 85px;
        text-align: center;
    }

    .schedule-info-teacher {
        flex: 1;
    }

    .schedule-title-teacher {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 4px;
        font-size: 14px;
    }

    .schedule-meta-teacher {
        font-size: 12px;
        color: #6c757d;
    }

    .schedule-status-teacher {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-scheduled { background: #bee3f8; color: #2b6cb0; }
    .status-ongoing { background: #fed7d7; color: #c53030; }
    .status-completed { background: #c6f6d5; color: #276749; }

    /* Performance Card */
    .performance-card-teacher {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .performance-header-teacher {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .performance-score-teacher {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .performance-label-teacher {
        font-size: 14px;
        opacity: 0.9;
    }

    .performance-metrics-teacher {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .metric-teacher {
        text-align: center;
    }

    .metric-value-teacher {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .metric-label-teacher {
        font-size: 11px;
        opacity: 0.8;
    }

    /* Quick Actions */
    .quick-actions-teacher {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .action-btn-teacher {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 15px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        text-decoration: none;
        color: var(--dark-color);
        transition: all 0.3s ease;
        background: white;
        position: relative;
        overflow: hidden;
    }

    .action-btn-teacher::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-teacher);
        transition: all 0.3s ease;
        z-index: 1;
    }

    .action-btn-teacher:hover::before {
        left: 0;
    }

    .action-btn-teacher:hover {
        border-color: var(--teacher-primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(72, 187, 120, 0.25);
        text-decoration: none;
    }

    .action-icon-teacher {
        font-size: 24px;
        margin-bottom: 8px;
        position: relative;
        z-index: 2;
    }

    .action-label-teacher {
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    /* Trainee List */
    .trainee-item-teacher {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .trainee-item-teacher:last-child {
        border-bottom: none;
    }

    .trainee-avatar-teacher {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--gradient-teacher);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
        font-size: 14px;
    }

    .trainee-info-teacher {
        flex: 1;
    }

    .trainee-name-teacher {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 2px;
        font-size: 13px;
    }

    .trainee-meta-teacher {
        font-size: 11px;
        color: #6c757d;
    }

    /* Progress Indicator */
    .progress-indicator {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        position: relative;
        background: #e2e8f0;
    }

    .progress-indicator::before {
        content: attr(data-progress) '%';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 10px;
        font-weight: 600;
        color: var(--dark-color);
    }

    /* Mobile Responsive */
    @media (max-width: 1200px) {
        .teacher-content-grid {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 0 20px 20px;
        }
        
        .teacher-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            margin: -60px 20px 30px;
        }
    }

    @media (max-width: 768px) {
        .welcome-section h1 {
            font-size: 2rem;
        }
        
        .teacher-stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .dashboard-customization {
            width: 100%;
            right: -100%;
        }

        .quick-actions-teacher {
            grid-template-columns: 1fr;
        }

        .performance-metrics-teacher {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* Mobile Bottom Navigation */
    .mobile-nav-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-around;
        padding: 10px 0;
        z-index: 1000;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
    }

    .mobile-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px;
        color: #6c757d;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 10px;
        min-width: 60px;
    }

    .mobile-nav-item.active {
        color: var(--teacher-primary);
        background: rgba(72, 187, 120, 0.1);
    }

    .mobile-nav-item i {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .mobile-nav-item span {
        font-size: 11px;
        font-weight: 500;
    }

    /* Real-time Update Indicator */
    .update-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        display: none;
    }

    .update-badge {
        background: var(--teacher-primary);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
    }

    /* Scrollbar Styling */
    .widget-content-teacher::-webkit-scrollbar {
        width: 6px;
    }

    .widget-content-teacher::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .widget-content-teacher::-webkit-scrollbar-thumb {
        background: var(--teacher-primary);
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div class="teacher-dashboard">
    <!-- Dashboard Customization Panel -->
    <div class="dashboard-customization" id="customizationPanel">
        <div class="customization-header">
            <h5><i class="fas fa-palette me-2"></i>Customize Dashboard</h5>
            <button class="btn-close" onclick="toggleCustomization()"></button>
        </div>
        <div class="customization-content p-3">
            <div class="widget-toggles">
                <h6 class="mb-3">Visible Widgets</h6>
                <label class="widget-toggle">
                    <span>Teaching Statistics</span>
                    <input type="checkbox" id="toggle-stats" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
                <label class="widget-toggle">
                    <span>Today's Schedule</span>
                    <input type="checkbox" id="toggle-schedule" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
                <label class="widget-toggle">
                    <span>Trainee Progress</span>
                    <input type="checkbox" id="toggle-progress" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
                <label class="widget-toggle">
                    <span>Quick Actions</span>
                    <input type="checkbox" id="toggle-actions" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Enhanced Teacher Header -->
    <div class="teacher-header-enhanced">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="welcome-section">
                        <h1>
                            <i class="fas fa-chalkboard-teacher me-3"></i>
                            Welcome back, {{ $user['name'] ?? session('name', 'Teacher') }}!
                        </h1>
                        <p class="teacher-subtitle">
                            Ready to inspire and educate? Here's your teaching overview for today.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="header-actions">
                        <button class="btn btn-light btn-lg me-3" onclick="toggleCustomization()" title="Customize Dashboard">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="performance-indicator">
                            <i class="fas fa-clock me-2"></i>
                            Load Time: {{ $performance['load_time'] ?? '0' }}ms
                            <span class="ms-2 badge bg-light text-dark">{{ $performance['cache_status'] ?? 'miss' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="teacher-stats-grid" id="statsWidget">
        <div class="teacher-stat-card sessions">
            <div class="stat-icon-teacher primary">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-number-teacher">{{ $stats['today_sessions'] ?? 0 }}</div>
            <div class="stat-label-teacher">Today's Sessions</div>
            @if(($stats['pending_attendance'] ?? 0) > 0)
                <div class="alert alert-warning alert-sm mt-2 mb-0 py-1">
                    <i class="fas fa-exclamation-triangle"></i> {{ $stats['pending_attendance'] }} need attendance
                </div>
            @else
                <div class="alert alert-success alert-sm mt-2 mb-0 py-1">
                    <i class="fas fa-check-circle"></i> All up to date
                </div>
            @endif
        </div>
        
        <div class="teacher-stat-card trainees">
            <div class="stat-icon-teacher secondary">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-number-teacher">{{ $stats['total_trainees'] ?? 0 }}</div>
            <div class="stat-label-teacher">Active Trainees</div>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" style="width: {{ min(($stats['total_trainees'] ?? 0) * 3, 100) }}%; background: var(--teacher-secondary);"></div>
            </div>
        </div>
        
        <div class="teacher-stat-card attendance">
            <div class="stat-icon-teacher teal">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-number-teacher">{{ number_format($stats['average_attendance'] ?? 0, 1) }}%</div>
            <div class="stat-label-teacher">Attendance Rate</div>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" style="width: {{ $stats['average_attendance'] ?? 0 }}%; background: #38b2ac;"></div>
            </div>
        </div>
        
        <div class="teacher-stat-card progress">
            <div class="stat-icon-teacher blue">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-number-teacher">{{ $stats['week_sessions'] ?? 0 }}</div>
            <div class="stat-label-teacher">This Week's Sessions</div>
            <small class="text-muted">{{ $stats['completed_sessions'] ?? 0 }} completed</small>
        </div>
    </div>

    <!-- Enhanced Main Content Grid -->
    <div class="teacher-content-grid">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Performance Summary -->
            <div class="performance-card-teacher" id="performanceWidget">
                <div class="performance-header-teacher">
                    <div>
                        <div class="performance-score-teacher">{{ $performance['overall_score'] ?? 85 }}%</div>
                        <div class="performance-label-teacher">Teaching Performance Score</div>
                    </div>
                    <div>
                        <i class="fas fa-trophy" style="font-size: 32px; opacity: 0.8;"></i>
                    </div>
                </div>
                <div class="performance-metrics-teacher">
                    <div class="metric-teacher">
                        <div class="metric-value-teacher">{{ $performance['attendance_rate'] ?? 88 }}%</div>
                        <div class="metric-label-teacher">Attendance Rate</div>
                    </div>
                    <div class="metric-teacher">
                        <div class="metric-value-teacher">{{ $performance['completion_rate'] ?? 92 }}%</div>
                        <div class="metric-label-teacher">Session Completion</div>
                    </div>
                    <div class="metric-teacher">
                        <div class="metric-value-teacher">{{ $performance['trainee_progress'] ?? 78 }}%</div>
                        <div class="metric-label-teacher">Trainee Progress</div>
                    </div>
                    <div class="metric-teacher">
                        <div class="metric-value-teacher">{{ $performance['feedback_score'] ?? 4.2 }}/5</div>
                        <div class="metric-label-teacher">Feedback Score</div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="teacher-widget-enhanced" id="scheduleWidget">
                <div class="widget-header-teacher">
                    <h3 class="widget-title-teacher">
                        <i class="fas fa-calendar-day"></i>
                        Today's Schedule
                    </h3>
                    <div>
                        <button class="btn btn-light btn-sm" onclick="refreshWidget('schedule')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="{{ route('activities.schedule') }}" class="btn btn-light btn-sm ms-2">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="widget-content-teacher">
                    @if(isset($schedule['today']) && count($schedule['today']) > 0)
                        @foreach($schedule['today'] as $session)
                            <div class="schedule-item-teacher">
                                <div class="schedule-time-teacher">
                                    {{ isset($session->start_time) ? $session->start_time->format('H:i') : 'TBD' }}
                                </div>
                                <div class="schedule-info-teacher">
                                    <div class="schedule-title-teacher">{{ $session->activity_name ?? 'Session' }}</div>
                                    <div class="schedule-meta-teacher">
                                        {{ $session->participants_count ?? 0 }} participants • 
                                        {{ $session->location ?? 'Main Hall' }}
                                    </div>
                                </div>
                                <div class="schedule-status-teacher status-{{ strtolower($session->status ?? 'scheduled') }}">
                                    {{ ucfirst($session->status ?? 'scheduled') }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-3"></i>
                            <p class="mb-2">No sessions scheduled for today</p>
                            <p class="small">Enjoy your free time!</p>
                            <a href="{{ route('activities.create') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Create New Session
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Trainee Progress Overview -->
            <div class="teacher-widget-enhanced" id="progressWidget">
                <div class="widget-header-teacher">
                    <h3 class="widget-title-teacher">
                        <i class="fas fa-chart-bar"></i>
                        Trainee Progress Overview
                    </h3>
                    <a href="{{ route('trainees.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <div class="widget-content-teacher">
                    @if(isset($trainees['progress_alerts']) && count($trainees['progress_alerts']) > 0)
                        @foreach($trainees['progress_alerts'] as $alert)
                            <div class="schedule-item-teacher">
                                <div class="schedule-time-teacher" style="background: {{ $alert['type'] === 'improvement' ? 'var(--teacher-primary)' : '#f6ad55' }};">
                                    {{ $alert['type'] === 'improvement' ? '↗' : '⚠' }}
                                </div>
                                <div class="schedule-info-teacher">
                                    <div class="schedule-title-teacher">{{ $alert['trainee_name'] ?? 'Unknown' }}</div>
                                    <div class="schedule-meta-teacher">{{ $alert['message'] ?? 'Progress update' }}</div>
                                </div>
                                <div class="progress-indicator" data-progress="{{ $alert['progress'] ?? 75 }}"></div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-thumbs-up fa-2x mb-2 text-success"></i>
                            <p class="mb-0">All trainees are progressing well!</p>
                            <small>No alerts at this time.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="sidebar-content">
            <!-- Quick Actions -->
            <div class="teacher-widget-enhanced" id="actionsWidget">
                <div class="widget-header-teacher">
                    <h3 class="widget-title-teacher">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="widget-content-teacher">
                    <div class="quick-actions-teacher">
                        <a href="{{ route('activities.home') }}" class="action-btn-teacher">
                            <div class="action-icon-teacher">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="action-label-teacher">Mark Attendance</div>
                        </a>
                        
                        <a href="{{ route('activities.create') }}" class="action-btn-teacher">
                            <div class="action-icon-teacher">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-label-teacher">New Session</div>
                        </a>
                        
                        <a href="{{ route('trainees.index') }}" class="action-btn-teacher">
                            <div class="action-icon-teacher">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="action-label-teacher">View Trainees</div>
                        </a>
                        
                        <a href="{{ route('reports.teacher') }}" class="action-btn-teacher">
                            <div class="action-icon-teacher">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-label-teacher">My Reports</div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Sessions -->
            <div class="teacher-widget-enhanced">
                <div class="widget-header-teacher">
                    <h3 class="widget-title-teacher">
                        <i class="fas fa-calendar-week"></i>
                        Upcoming Sessions
                    </h3>
                </div>
                <div class="widget-content-teacher">
                    @if(isset($schedule['upcoming']) && count($schedule['upcoming']) > 0)
                        @foreach(array_slice($schedule['upcoming'], 0, 5) as $session)
                            <div class="trainee-item-teacher">
                                <div class="trainee-avatar-teacher">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="trainee-info-teacher">
                                    <div class="trainee-name-teacher">{{ $session->activity_name ?? 'Session' }}</div>
                                    <div class="trainee-meta-teacher">
                                        {{ isset($session->session_date) ? $session->session_date->format('M j, g:i A') : 'TBD' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-calendar-times fa-lg mb-2"></i>
                            <p class="mb-0 small">No upcoming sessions</p>
                            <small>Schedule is clear for now</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Enrollments -->
            <div class="teacher-widget-enhanced">
                <div class="widget-header-teacher">
                    <h3 class="widget-title-teacher">
                        <i class="fas fa-user-plus"></i>
                        Recent Enrollments
                    </h3>
                </div>
                <div class="widget-content-teacher">
                    @if(isset($trainees['recent_enrollments']) && count($trainees['recent_enrollments']) > 0)
                        @foreach($trainees['recent_enrollments'] as $enrollment)
                            <div class="trainee-item-teacher">
                                <div class="trainee-avatar-teacher">
                                    {{ strtoupper(substr($enrollment->trainee_name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="trainee-info-teacher">
                                    <div class="trainee-name-teacher">{{ $enrollment->trainee_name ?? 'New Trainee' }}</div>
                                    <div class="trainee-meta-teacher">
                                        Enrolled {{ isset($enrollment->created_at) ? $enrollment->created_at->diffForHumans() : 'recently' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-inbox fa-lg mb-2"></i>
                            <p class="mb-0 small">No recent enrollments</p>
                            <small>Check back later</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-nav-bar d-lg-none">
        <a href="{{ route('teacher.dashboard') }}" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('activities.home') }}" class="mobile-nav-item">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="{{ route('trainees.index') }}" class="mobile-nav-item">
            <i class="fas fa-user-graduate"></i>
            <span>Trainees</span>
        </a>
        <a href="{{ route('activities.schedule') }}" class="mobile-nav-item">
            <i class="fas fa-calendar"></i>
            <span>Schedule</span>
        </a>
        <a href="#" class="mobile-nav-item" onclick="toggleCustomization()">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>

    <!-- Real-time Update Indicator -->
    <div class="update-indicator" id="updateIndicator">
        <div class="update-badge">
            <i class="fas fa-sync-alt fa-spin me-1"></i> Updating...
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
class TeacherDashboardManager {
    constructor() {
        this.refreshInterval = null;
        this.isRefreshing = false;
        this.widgets = {
            stats: true,
            schedule: true,
            progress: true,
            actions: true
        };
        this.lastUpdateTime = {{ time() }};
        this.init();
    }

    init() {
        this.initWidgetToggles();
        this.initRealTimeUpdates();
        this.initAnimations();
        this.loadUserPreferences();
    }

    initWidgetToggles() {
        const toggles = document.querySelectorAll('.widget-toggle input[type="checkbox"]');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                const widgetName = e.target.id.replace('toggle-', '');
                this.toggleWidget(widgetName, e.target.checked);
            });
        });
    }

    toggleWidget(widgetName, show) {
        this.widgets[widgetName] = show;
        const widget = document.getElementById(widgetName + 'Widget');
        if (widget) {
            widget.style.display = show ? 'block' : 'none';
        }
        this.saveUserPreferences();
    }

    initRealTimeUpdates() {
        // Auto-refresh dashboard data every 45 seconds for teachers
        this.refreshInterval = setInterval(() => {
            this.fetchRealTimeUpdates();
        }, 45000);
    }

    async fetchRealTimeUpdates() {
        const indicator = document.getElementById('updateIndicator');
        indicator.style.display = 'block';
        
        try {
            const response = await fetch(`{{ route('dashboard.updates') }}?last_update=${this.lastUpdateTime}&include_stats=true`);
            const data = await response.json();
            
            if (data.success) {
                if (data.stats) {
                    this.updateStatValues(data.stats);
                }
                if (data.updates && data.updates.length > 0) {
                    this.showNotifications(data.updates);
                }
                this.lastUpdateTime = data.timestamp;
            }
        } catch (error) {
            console.error('Real-time update failed:', error);
        } finally {
            indicator.style.display = 'none';
        }
    }

    async refreshWidget(widgetType) {
        if (this.isRefreshing) return;
        
        this.isRefreshing = true;
        const button = event.target.closest('button');
        const icon = button.querySelector('i');
        
        icon.classList.add('fa-spin');
        button.disabled = true;

        try {
            const response = await fetch(`{{ route('dashboard.refresh-widget') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ widget: widgetType })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateWidgetContent(widgetType, data.html);
                this.showNotification('Widget refreshed successfully!', 'success');
            } else {
                throw new Error(data.error || 'Failed to refresh widget');
            }
        } catch (error) {
            console.error('Widget refresh failed:', error);
            this.showNotification('Failed to refresh widget', 'error');
        } finally {
            this.isRefreshing = false;
            icon.classList.remove('fa-spin');
            button.disabled = false;
        }
    }

    updateWidgetContent(widgetType, html) {
        const widget = document.getElementById(widgetType + 'Widget');
        if (widget) {
            const content = widget.querySelector('.widget-content-teacher');
            if (content) {
                content.innerHTML = html;
                this.initAnimations();
            }
        }
    }

    updateStatValues(stats) {
        // Update stat numbers with animation
        const statMappings = {
            'today_sessions': '.teacher-stat-card.sessions .stat-number-teacher',
            'total_trainees': '.teacher-stat-card.trainees .stat-number-teacher',
            'average_attendance': '.teacher-stat-card.attendance .stat-number-teacher',
            'week_sessions': '.teacher-stat-card.progress .stat-number-teacher'
        };

        Object.entries(statMappings).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && stats[key] !== undefined) {
                const currentValue = parseFloat(element.textContent.replace(/[^0-9.]/g, ''));
                this.animateNumber(element, currentValue, stats[key], key === 'average_attendance');
            }
        });
    }

    animateNumber(element, start, end, isPercent = false) {
        const duration = 1000;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const current = start + (end - start) * this.easeOutCubic(progress);
            
            if (isPercent) {
                element.textContent = current.toFixed(1) + '%';
            } else {
                element.textContent = Math.floor(current);
            }
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    initAnimations() {
        // Animate stat cards
        const statCards = document.querySelectorAll('.teacher-stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animate schedule items
        const scheduleItems = document.querySelectorAll('.schedule-item-teacher');
        scheduleItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 600 + (index * 100));
        });
    }

    showNotifications(updates) {
        updates.forEach(update => {
            this.showNotification(update.message, update.type);
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 1050; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    saveUserPreferences() {
        localStorage.setItem('teacher_dashboard_widgets', JSON.stringify(this.widgets));
    }

    loadUserPreferences() {
        const saved = localStorage.getItem('teacher_dashboard_widgets');
        if (saved) {
            this.widgets = { ...this.widgets, ...JSON.parse(saved) };
            Object.keys(this.widgets).forEach(widget => {
                const toggle = document.getElementById(`toggle-${widget}`);
                if (toggle) {
                    toggle.checked = this.widgets[widget];
                    this.toggleWidget(widget, this.widgets[widget]);
                }
            });
        }
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Global functions
function toggleCustomization() {
    const panel = document.getElementById('customizationPanel');
    panel.classList.toggle('active');
}

function refreshWidget(type) {
    if (window.teacherDashboard) {
        window.teacherDashboard.refreshWidget(type);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.teacherDashboard = new TeacherDashboardManager();
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (window.teacherDashboard) {
            window.teacherDashboard.destroy();
        }
    });
});
</script>
@endpush