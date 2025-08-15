@extends('layouts.app')

@section('title', 'Supervisor Dashboard - CREAMS')

@push('styles')
<style>
    :root {
        --supervisor-primary: #f6ad55;
        --supervisor-secondary: #fbd38d;
        --supervisor-accent: #fed7a7;
        --dark-color: #2d3748;
        --light-bg: #fffdf7;
        --border-color: #e2e8f0;
        --gradient-supervisor: linear-gradient(135deg, var(--supervisor-primary), var(--supervisor-secondary));
        --shadow-primary: 0 10px 30px rgba(246, 173, 85, 0.3);
    }

    /* Supervisor Dashboard Container */
    .supervisor-dashboard {
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
        border-left: 3px solid var(--supervisor-primary);
    }

    .dashboard-customization.active {
        right: 0;
    }

    .customization-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--gradient-supervisor);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .customization-content {
        padding: 20px;
        max-height: calc(100vh - 80px);
        overflow-y: auto;
    }

    .widget-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
    }

    .widget-toggle input {
        display: none;
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
        background: var(--supervisor-primary);
    }

    .widget-toggle input:checked + .toggle-slider::before {
        transform: translateX(25px);
    }

    /* Enhanced Supervisor Header */
    .supervisor-header-enhanced {
        background: var(--gradient-supervisor);
        color: white;
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-primary);
        border-radius: 0 0 30px 30px;
        margin-bottom: 30px;
    }

    .supervisor-header-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="supervisor-pattern" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="2" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="2" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23supervisor-pattern)"/></svg>');
        opacity: 0.3;
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .supervisor-welcome {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }

    .supervisor-avatar {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        backdrop-filter: blur(10px);
    }

    .supervisor-info h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 8px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .supervisor-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .header-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .header-btn {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        font-weight: 500;
    }

    .header-btn:hover {
        background: rgba(255,255,255,0.3);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    /* Statistics Grid Enhanced */
    .stats-grid-enhanced {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card-enhanced {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .stat-card-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: var(--card-color, var(--supervisor-primary));
        transition: width 0.3s ease;
    }

    .stat-card-enhanced:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        border-color: var(--card-color, var(--supervisor-primary));
    }

    .stat-card-enhanced:hover::before {
        width: 12px;
    }

    .stat-header-enhanced {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
    }

    .stat-icon-enhanced {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--card-color, var(--supervisor-primary)), var(--card-color-light, var(--supervisor-secondary)));
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        box-shadow: 0 8px 20px rgba(246, 173, 85, 0.4);
        position: relative;
        overflow: hidden;
    }

    .stat-icon-enhanced::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: rotate(45deg);
        transition: all 0.6s ease;
        opacity: 0;
    }

    .stat-card-enhanced:hover .stat-icon-enhanced::before {
        opacity: 1;
        transform: rotate(45deg) translate(100%, 100%);
    }

    .stat-value-enhanced {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--dark-color);
        margin-bottom: 8px;
        line-height: 1;
    }

    .stat-label-enhanced {
        color: #64748b;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }

    .stat-change-enhanced {
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 20px;
        background: rgba(0,0,0,0.05);
    }

    .stat-change-enhanced.positive {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }

    .stat-change-enhanced.neutral {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
    }

    .stat-change-enhanced.warning {
        background: rgba(251, 146, 60, 0.1);
        color: #ea580c;
    }

    /* Widget Container Enhanced */
    .widget-container-enhanced {
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 25px;
    }

    .widget-container-enhanced:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .widget-header-enhanced {
        padding: 25px 30px 20px;
        border-bottom: 2px solid #f8fafc;
        background: linear-gradient(135deg, #fafafa, #ffffff);
    }

    .widget-title-enhanced {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--dark-color);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .widget-title-enhanced i {
        color: var(--supervisor-primary);
        font-size: 1.2rem;
    }

    .widget-content-enhanced {
        padding: 25px 30px;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Team Management Enhanced */
    .team-grid-enhanced {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
    }

    .team-member-enhanced {
        background: linear-gradient(135deg, #fafafa, #ffffff);
        border-radius: 16px;
        padding: 25px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .team-member-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--supervisor-primary);
        transition: width 0.3s ease;
    }

    .team-member-enhanced:hover {
        border-color: var(--supervisor-primary);
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(246, 173, 85, 0.2);
    }

    .team-member-enhanced:hover::before {
        width: 8px;
    }

    .member-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .member-avatar-enhanced {
        width: 60px;
        height: 60px;
        background: var(--gradient-supervisor);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        color: white;
        box-shadow: 0 6px 15px rgba(246, 173, 85, 0.3);
    }

    .member-details h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 4px;
    }

    .member-role {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .member-stats-enhanced {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .member-stat-item {
        text-align: center;
        padding: 15px;
        background: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .member-stat-item:hover {
        border-color: var(--supervisor-primary);
        box-shadow: 0 4px 12px rgba(246, 173, 85, 0.2);
    }

    .stat-number {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--supervisor-primary);
        display: block;
        margin-bottom: 4px;
    }

    .stat-text {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Mobile Navigation */
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
        border-radius: 12px;
        min-width: 60px;
    }

    .mobile-nav-item.active {
        color: var(--supervisor-primary);
        background: rgba(246, 173, 85, 0.1);
    }

    .mobile-nav-item i {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .mobile-nav-item span {
        font-size: 11px;
        font-weight: 500;
    }

    /* Quick Actions Enhanced */
    .quick-actions-enhanced {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
    }

    .action-card-enhanced {
        background: linear-gradient(135deg, #ffffff, #fafafa);
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        padding: 25px 20px;
        text-align: center;
        text-decoration: none;
        color: var(--dark-color);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .action-card-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(246, 173, 85, 0.1), transparent);
        transition: left 0.6s ease;
    }

    .action-card-enhanced:hover {
        border-color: var(--supervisor-primary);
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(246, 173, 85, 0.2);
        text-decoration: none;
        color: var(--dark-color);
    }

    .action-card-enhanced:hover::before {
        left: 100%;
    }

    .action-icon-enhanced {
        width: 60px;
        height: 60px;
        background: var(--gradient-supervisor);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin: 0 auto 15px;
        box-shadow: 0 6px 15px rgba(246, 173, 85, 0.3);
    }

    .action-label-enhanced {
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Schedule Overview Enhanced */
    .schedule-overview-enhanced {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .schedule-overview-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="1" opacity="0.1"/></svg>');
        transform: translate(50%, -50%);
    }

    .schedule-header-enhanced {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        position: relative;
        z-index: 2;
    }

    .schedule-title-enhanced {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
    }

    .schedule-stats-enhanced {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 25px;
        position: relative;
        z-index: 2;
    }

    .schedule-stat-enhanced {
        text-align: center;
        padding: 20px;
        background: rgba(255,255,255,0.1);
        border-radius: 16px;
        backdrop-filter: blur(10px);
    }

    .schedule-stat-value-enhanced {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .schedule-stat-label-enhanced {
        font-size: 0.8rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Activity Items Enhanced */
    .activity-item-enhanced {
        display: flex;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .activity-item-enhanced:last-child {
        border-bottom: none;
    }

    .activity-item-enhanced:hover {
        background: #fafafa;
        margin: 0 -15px;
        padding: 20px 15px;
        border-radius: 12px;
    }

    .activity-time-enhanced {
        background: var(--gradient-supervisor);
        color: white;
        padding: 12px 16px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-right: 20px;
        min-width: 90px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(246, 173, 85, 0.3);
    }

    .activity-content {
        flex: 1;
    }

    .activity-title-enhanced {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 6px;
        font-size: 1.1rem;
    }

    .activity-meta-enhanced {
        color: #64748b;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .activity-status-enhanced {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }

    .status-scheduled {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .status-completed {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    /* Performance Indicator */
    .performance-indicator-enhanced {
        position: absolute;
        top: 25px;
        right: 25px;
        background: rgba(255,255,255,0.2);
        padding: 10px 16px;
        border-radius: 25px;
        font-size: 0.8rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
        z-index: 3;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .supervisor-header-enhanced {
            padding: 30px 0;
            border-radius: 0 0 20px 20px;
        }

        .supervisor-info h1 {
            font-size: 2rem;
        }

        .stats-grid-enhanced {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .team-grid-enhanced {
            grid-template-columns: 1fr;
        }

        .header-actions {
            flex-direction: column;
            gap: 10px;
        }

        .performance-indicator-enhanced {
            position: static;
            margin-top: 20px;
            text-align: center;
        }

        .quick-actions-enhanced {
            grid-template-columns: repeat(2, 1fr);
        }

        .schedule-stats-enhanced {
            grid-template-columns: repeat(2, 1fr);
        }

        .mobile-nav-bar {
            display: flex;
        }

        .main-content {
            padding-bottom: 80px;
        }
    }

    @media (min-width: 769px) {
        .mobile-nav-bar {
            display: none;
        }
    }

    /* Loading Animation */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .loading {
        animation: pulse 1.5s ease-in-out infinite;
    }

    /* Scroll animations */
    .animate-on-scroll {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .animate-on-scroll.animated {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

@section('content')
<div class="supervisor-dashboard">
    <!-- Dashboard Customization Panel -->
    <div class="dashboard-customization" id="customizationPanel">
        <div class="customization-header">
            <h5 class="mb-0">Dashboard Settings</h5>
            <button class="btn btn-link text-white p-0" onclick="toggleCustomization()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="customization-content">
            <h6 class="text-muted mb-3">Widget Visibility</h6>
            
            <div class="widget-toggle">
                <span>Team Performance</span>
                <label>
                    <input type="checkbox" checked data-widget="team-performance">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Today's Activities</span>
                <label>
                    <input type="checkbox" checked data-widget="activities">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Quick Actions</span>
                <label>
                    <input type="checkbox" checked data-widget="quick-actions">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Centre Alerts</span>
                <label>
                    <input type="checkbox" checked data-widget="alerts">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Recent Enrollments</span>
                <label>
                    <input type="checkbox" checked data-widget="enrollments">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <hr class="my-4">
            
            <h6 class="text-muted mb-3">Dashboard Theme</h6>
            <div class="btn-group-vertical w-100">
                <button class="btn btn-outline-primary btn-sm" onclick="setTheme('light')">Light Theme</button>
                <button class="btn btn-outline-primary btn-sm" onclick="setTheme('dark')">Dark Theme</button>
                <button class="btn btn-outline-primary btn-sm" onclick="setTheme('auto')">Auto Theme</button>
            </div>
            
            <hr class="my-4">
            
            <button class="btn btn-warning w-100" onclick="resetDashboard()">
                <i class="fas fa-redo me-2"></i>Reset to Default
            </button>
        </div>
    </div>

    <!-- Enhanced Dashboard Header -->
    <div class="supervisor-header-enhanced">
        <div class="container-fluid">
            <div class="header-content">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="supervisor-welcome">
                            <div class="supervisor-avatar">
                                {{ strtoupper(substr($user['name'] ?? 'S', 0, 2)) }}
                            </div>
                            <div class="supervisor-info">
                                <h1>Supervisor Dashboard</h1>
                                <p class="supervisor-subtitle mb-0">Welcome back, {{ $user['name'] ?? 'Supervisor' }}! Manage your centre effectively.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="header-actions">
                            <a href="{{ route('reports.supervisor') }}" class="header-btn">
                                <i class="fas fa-chart-line me-2"></i>Reports
                            </a>
                            <button class="header-btn" onclick="toggleCustomization()">
                                <i class="fas fa-cog me-2"></i>Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="performance-indicator-enhanced">
            <i class="fas fa-tachometer-alt me-2"></i>
            Load: {{ $performance['load_time'] ?? '0' }}ms
            <span class="ms-2 badge badge-light">{{ $performance['cache_status'] ?? 'miss' }}</span>
        </div>
    </div>

    <div class="container-fluid main-content">
        <!-- Enhanced Statistics Grid -->
        <div class="stats-grid-enhanced animate-on-scroll">
            <div class="stat-card-enhanced" style="--card-color: #4299e1; --card-color-light: #63b3ed;" onclick="showStatDetails('trainees')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="centre_trainees">{{ $stats['centre_trainees'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Centre Trainees</div>
                <div class="stat-change-enhanced positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Active participants</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced" style="--card-color: #48bb78; --card-color-light: #68d391;" onclick="showStatDetails('teachers')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="centre_teachers">{{ $stats['centre_teachers'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Teaching Staff</div>
                <div class="stat-change-enhanced neutral">
                    <i class="fas fa-users"></i>
                    <span>Your team</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced" style="--card-color: #f6ad55; --card-color-light: #fbd38d;" onclick="showStatDetails('sessions')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="today_sessions">{{ $stats['today_sessions'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Today's Sessions</div>
                <div class="stat-change-enhanced positive">
                    <i class="fas fa-calendar-check"></i>
                    <span>In progress</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced" style="--card-color: #9f7aea; --card-color-light: #b794f6;" onclick="showStatDetails('attendance')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="attendance_rate">{{ number_format($stats['attendance_rate'] ?? 0, 1) }}%</div>
                <div class="stat-label-enhanced">Attendance Rate</div>
                <div class="stat-change-enhanced {{ ($stats['attendance_rate'] ?? 0) >= 80 ? 'positive' : 'warning' }}">
                    <i class="fas fa-percentage"></i>
                    <span>Centre average</span>
                </div>
            </div>
        </div>

        <!-- Schedule Overview Enhanced -->
        <div class="schedule-overview-enhanced animate-on-scroll">
            <div class="schedule-header-enhanced">
                <div>
                    <div class="schedule-title-enhanced">Today's Centre Overview</div>
                    <div class="mt-2" style="opacity: 0.9;">{{ now()->format('l, F j, Y') }}</div>
                </div>
                <div>
                    <i class="fas fa-building" style="font-size: 40px; opacity: 0.8;"></i>
                </div>
            </div>
            <div class="schedule-stats-enhanced">
                <div class="schedule-stat-enhanced">
                    <div class="schedule-stat-value-enhanced">{{ $schedule['today_count'] ?? 0 }}</div>
                    <div class="schedule-stat-label-enhanced">Sessions Today</div>
                </div>
                <div class="schedule-stat-enhanced">
                    <div class="schedule-stat-value-enhanced">{{ $schedule['week_count'] ?? 0 }}</div>
                    <div class="schedule-stat-label-enhanced">This Week</div>
                </div>
                <div class="schedule-stat-enhanced">
                    <div class="schedule-stat-value-enhanced">{{ number_format($stats['completion_rate'] ?? 0, 1) }}%</div>
                    <div class="schedule-stat-label-enhanced">Completion Rate</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-8">
                <!-- Team Performance Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="team-performance">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-users"></i>
                            Team Performance
                        </h3>
                        <a href="{{ route('staffs.home') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-cog me-1"></i>Manage Team
                        </a>
                    </div>
                    <div class="widget-content-enhanced">
                        <div class="team-grid-enhanced">
                            @if(isset($team['teachers']) && count($team['teachers']) > 0)
                                @foreach($team['teachers'] as $teacher)
                                    <div class="team-member-enhanced">
                                        <div class="member-header">
                                            <div class="member-avatar-enhanced">
                                                {{ strtoupper(substr($teacher->name ?? 'T', 0, 1)) }}
                                            </div>
                                            <div class="member-details">
                                                <h4>{{ $teacher->name ?? 'Unknown' }}</h4>
                                                <div class="member-role">{{ ucfirst($teacher->role ?? 'teacher') }}</div>
                                            </div>
                                        </div>
                                        <div class="member-stats-enhanced">
                                            <div class="member-stat-item">
                                                <span class="stat-number">{{ $teacher->sessions_count ?? 0 }}</span>
                                                <span class="stat-text">Sessions</span>
                                            </div>
                                            <div class="member-stat-item">
                                                <span class="stat-number">{{ $teacher->attendance_rate ?? 0 }}%</span>
                                                <span class="stat-text">Attendance</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                    <h5 class="text-muted">No Teaching Staff</h5>
                                    <p class="text-muted">No teaching staff assigned to your centre yet.</p>
                                    <a href="{{ route('staffs.register') }}" class="btn btn-warning">
                                        <i class="fas fa-plus me-2"></i>Add Staff
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Today's Activities Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="activities">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-calendar-check"></i>
                            Today's Activities
                        </h3>
                        <a href="{{ route('activities.home') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-eye me-1"></i>View All
                        </a>
                    </div>
                    <div class="widget-content-enhanced">
                        @if(isset($schedule['today']) && count($schedule['today']) > 0)
                            @foreach($schedule['today'] as $activity)
                                <div class="activity-item-enhanced">
                                    <div class="activity-time-enhanced">
                                        {{ isset($activity->start_time) ? $activity->start_time->format('H:i') : 'TBD' }}
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title-enhanced">{{ $activity->name ?? 'Activity' }}</div>
                                        <div class="activity-meta-enhanced">
                                            <span><i class="fas fa-user me-1"></i>{{ $activity->teacher_name ?? 'Unassigned' }}</span>
                                            <span><i class="fas fa-users me-1"></i>{{ $activity->participants_count ?? 0 }} participants</span>
                                        </div>
                                    </div>
                                    <div class="activity-status-enhanced status-{{ strtolower($activity->status ?? 'scheduled') }}">
                                        {{ ucfirst($activity->status ?? 'scheduled') }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-check fa-3x mb-3 text-muted"></i>
                                <h5 class="text-muted">No Activities Today</h5>
                                <p class="text-muted">No activities scheduled for today at your centre.</p>
                                <a href="{{ route('activities.create') }}" class="btn btn-warning">
                                    <i class="fas fa-plus me-2"></i>Create Activity
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="col-lg-4">
                <!-- Quick Actions Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="quick-actions">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="widget-content-enhanced">
                        <div class="quick-actions-enhanced">
                            <a href="{{ route('staffs.register') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="action-label-enhanced">Add Staff</div>
                            </a>
                            
                            <a href="{{ route('activities.create') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="action-label-enhanced">New Activity</div>
                            </a>
                            
                            <a href="{{ route('reports.supervisor') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="action-label-enhanced">Centre Reports</div>
                            </a>
                            
                            <a href="{{ route('trainees.index') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="action-label-enhanced">Manage Trainees</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Centre Alerts Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="alerts">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-bell"></i>
                            Centre Alerts
                        </h3>
                    </div>
                    <div class="widget-content-enhanced">
                        @if(isset($alerts) && count($alerts) > 0)
                            @foreach($alerts as $alert)
                                <div class="alert alert-{{ $alert['type'] ?? 'info' }} border-0 mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            @if(($alert['type'] ?? 'info') === 'warning')
                                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                            @elseif(($alert['type'] ?? 'info') === 'success')
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                <i class="fas fa-info-circle text-info"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $alert['title'] ?? 'Notification' }}</h6>
                                            <small>{{ $alert['message'] ?? 'No details available' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                <h6 class="text-success">All Good!</h6>
                                <p class="text-muted mb-0">All systems running smoothly!</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Enrollments Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="enrollments">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-user-plus"></i>
                            Recent Enrollments
                        </h3>
                    </div>
                    <div class="widget-content-enhanced">
                        @if(isset($team['trainees']) && count($team['trainees']) > 0)
                            @foreach(array_slice($team['trainees'], 0, 5) as $trainee)
                                <div class="activity-item-enhanced">
                                    <div class="member-avatar-enhanced" style="width: 40px; height: 40px; font-size: 16px; margin-right: 15px;">
                                        {{ strtoupper(substr($trainee->name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title-enhanced">{{ $trainee->name ?? 'Unknown' }}</div>
                                        <div class="activity-meta-enhanced">
                                            <span><i class="fas fa-clock me-1"></i>Enrolled {{ isset($trainee->created_at) ? $trainee->created_at->diffForHumans() : 'recently' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-plus fa-2x mb-3 text-muted"></i>
                                <p class="text-muted mb-0">No recent enrollments</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav-bar d-lg-none">
        <a href="{{ route('supervisor.dashboard') }}" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('staffs.home') }}" class="mobile-nav-item">
            <i class="fas fa-users"></i>
            <span>Team</span>
        </a>
        <a href="{{ route('activities.home') }}" class="mobile-nav-item">
            <i class="fas fa-calendar"></i>
            <span>Activities</span>
        </a>
        <a href="{{ route('trainees.index') }}" class="mobile-nav-item">
            <i class="fas fa-user-graduate"></i>
            <span>Trainees</span>
        </a>
        <a href="#" class="mobile-nav-item" onclick="toggleCustomization()">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>

    <!-- Real-time Update Indicator -->
    <div id="updateIndicator" class="position-fixed" style="top: 20px; right: 20px; z-index: 1060; display: none;">
        <div class="badge" style="background: var(--supervisor-primary); color: white;">
            <i class="fas fa-sync-alt fa-spin me-1"></i> Updating...
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Supervisor Dashboard Manager
class SupervisorDashboardManager {
    constructor() {
        this.lastUpdateTime = {{ time() }};
        this.updateInterval = null;
        this.isCustomizationOpen = false;
        this.widgets = new Set(['team-performance', 'activities', 'quick-actions', 'alerts', 'enrollments']);
        this.initialize();
    }

    initialize() {
        this.startRealTimeUpdates();
        this.initializeAnimations();
        this.initializeStatCounters();
        this.loadDashboardSettings();
        this.setupEventListeners();
    }

    startRealTimeUpdates() {
        this.updateInterval = setInterval(() => this.fetchUpdates(), 60000); // Every 60 seconds
    }

    async fetchUpdates() {
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
            console.error('Update fetch failed:', error);
        } finally {
            indicator.style.display = 'none';
        }
    }

    updateStatValues(stats) {
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                const currentValue = parseInt(element.textContent.replace(/[^0-9.]/g, ''));
                const newValue = stats[key];
                
                if (currentValue !== newValue) {
                    this.animateValue(element, currentValue, newValue, 1000);
                }
            }
        });
    }

    animateValue(element, start, end, duration) {
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

    initializeStatCounters() {
        const statElements = document.querySelectorAll('.stat-value-enhanced');
        statElements.forEach(element => {
            const finalValue = parseInt(element.textContent.replace(/[^0-9.]/g, ''));
            element.textContent = '0';
            setTimeout(() => {
                this.animateValue(element, 0, finalValue, 2000);
            }, 500);
        });
    }

    initializeAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    setupEventListeners() {
        // Widget toggles
        document.querySelectorAll('.widget-toggle input').forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                const widget = e.target.dataset.widget;
                this.toggleWidget(widget, e.target.checked);
            });
        });

        // Stat card clicks
        document.querySelectorAll('.stat-card-enhanced').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    }

    toggleWidget(widgetName, show) {
        const widget = document.querySelector(`[data-widget="${widgetName}"]`);
        if (widget) {
            widget.style.display = show ? 'block' : 'none';
            if (show) {
                this.widgets.add(widgetName);
            } else {
                this.widgets.delete(widgetName);
            }
            this.saveDashboardSettings();
        }
    }

    loadDashboardSettings() {
        const settings = localStorage.getItem('supervisor_dashboard_settings');
        if (settings) {
            try {
                const parsed = JSON.parse(settings);
                this.widgets = new Set(parsed.widgets || []);
                
                // Apply widget visibility
                document.querySelectorAll('[data-widget]').forEach(widget => {
                    const widgetName = widget.dataset.widget;
                    const isVisible = this.widgets.has(widgetName);
                    widget.style.display = isVisible ? 'block' : 'none';
                    
                    const toggle = document.querySelector(`input[data-widget="${widgetName}"]`);
                    if (toggle) {
                        toggle.checked = isVisible;
                    }
                });
            } catch (error) {
                console.error('Failed to load dashboard settings:', error);
            }
        }
    }

    saveDashboardSettings() {
        const settings = {
            widgets: Array.from(this.widgets),
            timestamp: Date.now()
        };
        localStorage.setItem('supervisor_dashboard_settings', JSON.stringify(settings));
    }

    showNotifications(updates) {
        updates.forEach(update => {
            this.showNotification(update.message, update.type);
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 1060; min-width: 300px; max-width: 400px;';
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

    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    }
}

// Initialize dashboard manager
let dashboardManager;
document.addEventListener('DOMContentLoaded', function() {
    dashboardManager = new SupervisorDashboardManager();
});

// Global functions for dashboard interaction
function toggleCustomization() {
    const panel = document.getElementById('customizationPanel');
    panel.classList.toggle('active');
    dashboardManager.isCustomizationOpen = !dashboardManager.isCustomizationOpen;
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('dashboard_theme', theme);
    dashboardManager.showNotification(`Theme changed to ${theme}`, 'success');
}

function resetDashboard() {
    if (confirm('Are you sure you want to reset the dashboard to default settings?')) {
        localStorage.removeItem('supervisor_dashboard_settings');
        localStorage.removeItem('dashboard_theme');
        location.reload();
    }
}

function showStatDetails(statType) {
    // This would open a modal with detailed statistics
    dashboardManager.showNotification(`Detailed ${statType} statistics coming soon!`, 'info');
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (dashboardManager) {
        dashboardManager.destroy();
    }
});

// Handle visibility change for performance
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        if (dashboardManager && dashboardManager.updateInterval) {
            clearInterval(dashboardManager.updateInterval);
        }
    } else {
        if (dashboardManager) {
            dashboardManager.startRealTimeUpdates();
        }
    }
});
</script>
@endpush