@extends('layouts.app')

@section('title', 'AJK Dashboard - CREAMS')

@push('styles')
<style>
    :root {
        --ajk-primary: #9f7aea;
        --ajk-secondary: #b794f6;
        --ajk-accent: #c4b5fd;
        --dark-color: #2d3748;
        --light-bg: #fbfaff;
        --border-color: #e2e8f0;
        --gradient-ajk: linear-gradient(135deg, var(--ajk-primary), var(--ajk-secondary));
        --shadow-primary: 0 10px 30px rgba(159, 122, 234, 0.3);
    }

    /* AJK Dashboard Container */
    .ajk-dashboard {
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
        border-left: 3px solid var(--ajk-primary);
    }

    .dashboard-customization.active {
        right: 0;
    }

    .customization-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--gradient-ajk);
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
        background: var(--ajk-primary);
    }

    .widget-toggle input:checked + .toggle-slider::before {
        transform: translateX(25px);
    }

    /* Enhanced AJK Header */
    .ajk-header-enhanced {
        background: var(--gradient-ajk);
        color: white;
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-primary);
        border-radius: 0 0 30px 30px;
        margin-bottom: 30px;
    }

    .ajk-header-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="ajk-pattern" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="2" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="2" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23ajk-pattern)"/></svg>');
        opacity: 0.3;
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .ajk-welcome {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }

    .ajk-avatar {
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

    .ajk-info h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 8px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .ajk-subtitle {
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
        background: var(--card-color, var(--ajk-primary));
        transition: width 0.3s ease;
    }

    .stat-card-enhanced:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        border-color: var(--card-color, var(--ajk-primary));
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
        background: linear-gradient(135deg, var(--card-color, var(--ajk-primary)), var(--card-color-light, var(--ajk-secondary)));
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        box-shadow: 0 8px 20px rgba(159, 122, 234, 0.4);
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

    .stat-change-enhanced.urgent {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
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
        color: var(--ajk-primary);
        font-size: 1.2rem;
    }

    .widget-content-enhanced {
        padding: 25px 30px;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Facility Status Enhanced */
    .facility-grid-enhanced {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .facility-card-enhanced {
        background: linear-gradient(135deg, #fafafa, #ffffff);
        border-radius: 16px;
        padding: 25px 20px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .facility-card-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--facility-color, var(--ajk-primary));
        transition: width 0.3s ease;
    }

    .facility-card-enhanced:hover {
        border-color: var(--facility-color, var(--ajk-primary));
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(159, 122, 234, 0.2);
    }

    .facility-card-enhanced:hover::before {
        width: 8px;
    }

    .facility-card-enhanced.excellent {
        --facility-color: #10b981;
    }

    .facility-card-enhanced.good {
        --facility-color: #3b82f6;
    }

    .facility-card-enhanced.needs-attention {
        --facility-color: #f59e0b;
    }

    .facility-card-enhanced.critical {
        --facility-color: #ef4444;
    }

    .facility-icon-enhanced {
        width: 60px;
        height: 60px;
        background: var(--gradient-ajk);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin: 0 auto 15px;
        box-shadow: 0 6px 15px rgba(159, 122, 234, 0.3);
    }

    .facility-name {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .facility-status {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 12px;
    }

    .facility-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin: 0 auto;
    }

    .indicator-excellent { background: #10b981; }
    .indicator-good { background: #3b82f6; }
    .indicator-needs-attention { background: #f59e0b; }
    .indicator-critical { background: #ef4444; }

    /* Maintenance Overview Enhanced */
    .maintenance-overview-enhanced {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .maintenance-overview-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="1" opacity="0.1"/></svg>');
        transform: translate(50%, -50%);
    }

    .maintenance-header-enhanced {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        position: relative;
        z-index: 2;
    }

    .maintenance-title-enhanced {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
    }

    .maintenance-stats-enhanced {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 25px;
        position: relative;
        z-index: 2;
    }

    .maintenance-stat-enhanced {
        text-align: center;
        padding: 20px;
        background: rgba(255,255,255,0.1);
        border-radius: 16px;
        backdrop-filter: blur(10px);
    }

    .maintenance-stat-value-enhanced {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .maintenance-stat-label-enhanced {
        font-size: 0.8rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Task Items Enhanced */
    .task-item-enhanced {
        display: flex;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .task-item-enhanced:last-child {
        border-bottom: none;
    }

    .task-item-enhanced:hover {
        background: #fafafa;
        margin: 0 -15px;
        padding: 20px 15px;
        border-radius: 12px;
    }

    .task-priority-enhanced {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        color: white;
        min-width: 50px;
    }

    .priority-high { background: #ef4444; }
    .priority-medium { background: #f59e0b; }
    .priority-low { background: #3b82f6; }

    .task-content {
        flex: 1;
    }

    .task-title-enhanced {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 6px;
        font-size: 1.1rem;
    }

    .task-meta-enhanced {
        color: #64748b;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .task-status-enhanced {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: rgba(236, 72, 153, 0.1);
        color: #be185d;
    }

    .status-progress {
        background: rgba(251, 146, 60, 0.1);
        color: #c2410c;
    }

    .status-completed {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
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
        color: var(--ajk-primary);
        background: rgba(159, 122, 234, 0.1);
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
        background: linear-gradient(90deg, transparent, rgba(159, 122, 234, 0.1), transparent);
        transition: left 0.6s ease;
    }

    .action-card-enhanced:hover {
        border-color: var(--ajk-primary);
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(159, 122, 234, 0.2);
        text-decoration: none;
        color: var(--dark-color);
    }

    .action-card-enhanced:hover::before {
        left: 100%;
    }

    .action-icon-enhanced {
        width: 60px;
        height: 60px;
        background: var(--gradient-ajk);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin: 0 auto 15px;
        box-shadow: 0 6px 15px rgba(159, 122, 234, 0.3);
    }

    .action-label-enhanced {
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Schedule Items Enhanced */
    .schedule-item-enhanced {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .schedule-item-enhanced:last-child {
        border-bottom: none;
    }

    .schedule-item-enhanced:hover {
        background: #fafafa;
        margin: 0 -15px;
        padding: 15px;
        border-radius: 12px;
    }

    .schedule-time-enhanced {
        background: var(--gradient-ajk);
        color: white;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.8rem;
        margin-right: 15px;
        min-width: 80px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(159, 122, 234, 0.3);
    }

    .schedule-content {
        flex: 1;
    }

    .schedule-title-enhanced {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 4px;
        font-size: 1rem;
    }

    .schedule-meta-enhanced {
        color: #64748b;
        font-size: 0.8rem;
    }

    /* Notification Items Enhanced */
    .notification-item-enhanced {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 12px;
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .notification-item-enhanced:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .notification-item-enhanced.info {
        background: rgba(59, 130, 246, 0.05);
        border-color: #3b82f6;
    }

    .notification-item-enhanced.warning {
        background: rgba(251, 146, 60, 0.05);
        border-color: #f59e0b;
    }

    .notification-item-enhanced.critical {
        background: rgba(239, 68, 68, 0.05);
        border-color: #ef4444;
    }

    .notification-icon-enhanced {
        margin-right: 15px;
        margin-top: 2px;
        font-size: 18px;
    }

    .notification-content-enhanced {
        flex: 1;
    }

    .notification-title-enhanced {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 4px;
        font-size: 1rem;
    }

    .notification-message-enhanced {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .notification-time-enhanced {
        color: #94a3b8;
        font-size: 0.75rem;
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
        .ajk-header-enhanced {
            padding: 30px 0;
            border-radius: 0 0 20px 20px;
        }

        .ajk-info h1 {
            font-size: 2rem;
        }

        .stats-grid-enhanced {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .facility-grid-enhanced {
            grid-template-columns: repeat(2, 1fr);
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

        .maintenance-stats-enhanced {
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
<div class="ajk-dashboard">
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
                <span>Facility Status</span>
                <label>
                    <input type="checkbox" checked data-widget="facility-status">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Pending Tasks</span>
                <label>
                    <input type="checkbox" checked data-widget="pending-tasks">
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
                <span>Support Schedule</span>
                <label>
                    <input type="checkbox" checked data-widget="support-schedule">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Notifications</span>
                <label>
                    <input type="checkbox" checked data-widget="notifications">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="widget-toggle">
                <span>Equipment Overview</span>
                <label>
                    <input type="checkbox" checked data-widget="equipment-overview">
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
            
            <button class="btn btn-primary w-100" onclick="resetDashboard()">
                <i class="fas fa-redo me-2"></i>Reset to Default
            </button>
        </div>
    </div>

    <!-- Enhanced Dashboard Header -->
    <div class="ajk-header-enhanced">
        <div class="container-fluid">
            <div class="header-content">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="ajk-welcome">
                            <div class="ajk-avatar">
                                {{ strtoupper(substr($user['name'] ?? 'A', 0, 2)) }}
                            </div>
                            <div class="ajk-info">
                                <h1>AJK Support Dashboard</h1>
                                <p class="ajk-subtitle mb-0">Welcome back, {{ $user['name'] ?? 'AJK' }}! Keep the centre running smoothly.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="header-actions">
                            <a href="{{ route('reports.ajk') }}" class="header-btn">
                                <i class="fas fa-clipboard-list me-2"></i>Reports
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
            <i class="fas fa-tools me-2"></i>
            Load: {{ $performance['load_time'] ?? '0' }}ms
            <span class="ms-2 badge badge-light">{{ $performance['cache_status'] ?? 'miss' }}</span>
        </div>
    </div>

    <div class="container-fluid main-content">
        <!-- Enhanced Statistics Grid -->
        <div class="stats-grid-enhanced animate-on-scroll">
            <div class="stat-card-enhanced" style="--card-color: #3b82f6; --card-color-light: #60a5fa;" onclick="showStatDetails('trainees')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="active_trainees">{{ $stats['active_trainees'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Active Trainees</div>
                <div class="stat-change-enhanced positive">
                    <i class="fas fa-users"></i>
                    <span>Currently enrolled</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced" style="--card-color: #10b981; --card-color-light: #34d399;" onclick="showStatDetails('sessions')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="today_sessions">{{ $stats['today_sessions'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Today's Sessions</div>
                <div class="stat-change-enhanced neutral">
                    <i class="fas fa-calendar-check"></i>
                    <span>Support needed</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced" style="--card-color: #f59e0b; --card-color-light: #fbbf24;" onclick="showStatDetails('tasks')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="pending_tasks">{{ $stats['pending_tasks'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Pending Tasks</div>
                <div class="stat-change-enhanced {{ ($stats['pending_tasks'] ?? 0) > 5 ? 'urgent' : 'neutral' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>{{ ($stats['pending_tasks'] ?? 0) > 5 ? 'High workload' : 'Manageable' }}</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced" style="--card-color: #ef4444; --card-color-light: #f87171;" onclick="showStatDetails('maintenance')">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value-enhanced" data-stat="maintenance_alerts">{{ $stats['maintenance_alerts'] ?? 0 }}</div>
                <div class="stat-label-enhanced">Maintenance Alerts</div>
                <div class="stat-change-enhanced {{ ($stats['maintenance_alerts'] ?? 0) > 0 ? 'urgent' : 'positive' }}">
                    @if(($stats['maintenance_alerts'] ?? 0) > 0)
                        <i class="fas fa-wrench"></i>
                        <span>Needs attention</span>
                    @else
                        <i class="fas fa-check-circle"></i>
                        <span>All up to date</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Maintenance Overview Enhanced -->
        <div class="maintenance-overview-enhanced animate-on-scroll">
            <div class="maintenance-header-enhanced">
                <div>
                    <div class="maintenance-title-enhanced">Maintenance & Support Overview</div>
                    <div class="mt-2" style="opacity: 0.9;">{{ now()->format('l, F j, Y') }}</div>
                </div>
                <div>
                    <i class="fas fa-wrench" style="font-size: 40px; opacity: 0.8;"></i>
                </div>
            </div>
            <div class="maintenance-stats-enhanced">
                <div class="maintenance-stat-enhanced">
                    <div class="maintenance-stat-value-enhanced">{{ $support['facilities']['total'] ?? 0 }}</div>
                    <div class="maintenance-stat-label-enhanced">Total Facilities</div>
                </div>
                <div class="maintenance-stat-enhanced">
                    <div class="maintenance-stat-value-enhanced">{{ $support['equipment']['working'] ?? 0 }}</div>
                    <div class="maintenance-stat-label-enhanced">Equipment Working</div>
                </div>
                <div class="maintenance-stat-enhanced">
                    <div class="maintenance-stat-value-enhanced">{{ $support['maintenance']['due_this_week'] ?? 0 }}</div>
                    <div class="maintenance-stat-label-enhanced">Due This Week</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-8">
                <!-- Facility Status Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="facility-status">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-building"></i>
                            Facility Status
                        </h3>
                        <a href="{{ route('facilities.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cog me-1"></i>Manage Facilities
                        </a>
                    </div>
                    <div class="widget-content-enhanced">
                        <div class="facility-grid-enhanced">
                            @if(isset($support['facilities']['list']) && count($support['facilities']['list']) > 0)
                                @foreach($support['facilities']['list'] as $facility)
                                    <div class="facility-card-enhanced {{ $facility['status'] ?? 'good' }}">
                                        <div class="facility-icon-enhanced">
                                            <i class="fas {{ $facility['icon'] ?? 'fa-building' }}"></i>
                                        </div>
                                        <div class="facility-name">{{ $facility['name'] ?? 'Facility' }}</div>
                                        <div class="facility-status">{{ ucfirst($facility['status'] ?? 'Good') }}</div>
                                        <div class="facility-indicator indicator-{{ $facility['status'] ?? 'good' }}"></div>
                                    </div>
                                @endforeach
                            @else
                                <div class="facility-card-enhanced excellent">
                                    <div class="facility-icon-enhanced">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="facility-name">Main Hall</div>
                                    <div class="facility-status">Excellent</div>
                                    <div class="facility-indicator indicator-excellent"></div>
                                </div>
                                <div class="facility-card-enhanced good">
                                    <div class="facility-icon-enhanced">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                    <div class="facility-name">Gym</div>
                                    <div class="facility-status">Good</div>
                                    <div class="facility-indicator indicator-good"></div>
                                </div>
                                <div class="facility-card-enhanced needs-attention">
                                    <div class="facility-icon-enhanced">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div class="facility-name">Computer Lab</div>
                                    <div class="facility-status">Needs Attention</div>
                                    <div class="facility-indicator indicator-needs-attention"></div>
                                </div>
                                <div class="facility-card-enhanced good">
                                    <div class="facility-icon-enhanced">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                    <div class="facility-name">Cafeteria</div>
                                    <div class="facility-status">Good</div>
                                    <div class="facility-indicator indicator-good"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pending Tasks Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="pending-tasks">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-tasks"></i>
                            Pending Tasks
                        </h3>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View All Tasks
                        </a>
                    </div>
                    <div class="widget-content-enhanced">
                        @if(isset($support['pending_tasks']) && count($support['pending_tasks']) > 0)
                            @foreach(array_slice($support['pending_tasks'], 0, 5) as $task)
                                <div class="task-item-enhanced">
                                    <div class="task-priority-enhanced priority-{{ strtolower($task['priority'] ?? 'medium') }}">
                                        {{ strtoupper(substr($task['priority'] ?? 'M', 0, 1)) }}
                                    </div>
                                    <div class="task-content">
                                        <div class="task-title-enhanced">{{ $task['title'] ?? 'Support Task' }}</div>
                                        <div class="task-meta-enhanced">
                                            <span><i class="fas fa-info-circle me-1"></i>{{ $task['description'] ?? 'No description available' }}</span>
                                            <span><i class="fas fa-calendar me-1"></i>Due: {{ isset($task['due_date']) ? $task['due_date']->format('M j') : 'TBD' }}</span>
                                        </div>
                                    </div>
                                    <div class="task-status-enhanced status-{{ strtolower($task['status'] ?? 'pending') }}">
                                        {{ ucfirst($task['status'] ?? 'pending') }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                <h5 class="text-success">All Caught Up!</h5>
                                <p class="text-muted">Great job! No pending tasks at the moment.</p>
                                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create New Task
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
                            <a href="{{ route('maintenance.create') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-wrench"></i>
                                </div>
                                <div class="action-label-enhanced">Schedule Maintenance</div>
                            </a>
                            
                            <a href="{{ route('assets.index') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="action-label-enhanced">Manage Assets</div>
                            </a>
                            
                            <a href="{{ route('tasks.create') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-plus-square"></i>
                                </div>
                                <div class="action-label-enhanced">New Task</div>
                            </a>
                            
                            <a href="{{ route('reports.ajk') }}" class="action-card-enhanced">
                                <div class="action-icon-enhanced">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="action-label-enhanced">Support Reports</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Today's Support Schedule Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="support-schedule">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-calendar-check"></i>
                            Today's Support Schedule
                        </h3>
                    </div>
                    <div class="widget-content-enhanced">
                        @if(isset($schedule['today']) && count($schedule['today']) > 0)
                            @foreach($schedule['today'] as $item)
                                <div class="schedule-item-enhanced">
                                    <div class="schedule-time-enhanced">
                                        {{ isset($item->time) ? $item->time->format('H:i') : 'TBD' }}
                                    </div>
                                    <div class="schedule-content">
                                        <div class="schedule-title-enhanced">{{ $item->title ?? 'Support Task' }}</div>
                                        <div class="schedule-meta-enhanced">{{ $item->location ?? 'Various locations' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="schedule-item-enhanced">
                                <div class="schedule-time-enhanced">09:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-title-enhanced">Morning Facility Check</div>
                                    <div class="schedule-meta-enhanced">All areas</div>
                                </div>
                            </div>
                            <div class="schedule-item-enhanced">
                                <div class="schedule-time-enhanced">12:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-title-enhanced">Lunch Setup Support</div>
                                    <div class="schedule-meta-enhanced">Cafeteria</div>
                                </div>
                            </div>
                            <div class="schedule-item-enhanced">
                                <div class="schedule-time-enhanced">14:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-title-enhanced">Equipment Maintenance</div>
                                    <div class="schedule-meta-enhanced">Computer Lab</div>
                                </div>
                            </div>
                            <div class="schedule-item-enhanced">
                                <div class="schedule-time-enhanced">16:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-title-enhanced">End of Day Cleanup</div>
                                    <div class="schedule-meta-enhanced">Main areas</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Support Notifications Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="notifications">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-bell"></i>
                            Support Notifications
                        </h3>
                    </div>
                    <div class="widget-content-enhanced">
                        @if(isset($notifications) && count($notifications) > 0)
                            @foreach(array_slice($notifications, 0, 4) as $notification)
                                <div class="notification-item-enhanced {{ $notification['type'] ?? 'info' }}">
                                    <div class="notification-icon-enhanced">
                                        @if(($notification['type'] ?? 'info') === 'critical')
                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                        @elseif(($notification['type'] ?? 'info') === 'warning')
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                        @else
                                            <i class="fas fa-info-circle text-info"></i>
                                        @endif
                                    </div>
                                    <div class="notification-content-enhanced">
                                        <div class="notification-title-enhanced">{{ $notification['title'] ?? 'Notification' }}</div>
                                        <div class="notification-message-enhanced">{{ $notification['message'] ?? 'No details available' }}</div>
                                        <div class="notification-time-enhanced">{{ isset($notification['created_at']) ? $notification['created_at']->diffForHumans() : 'Just now' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="notification-item-enhanced info">
                                <div class="notification-icon-enhanced">
                                    <i class="fas fa-info-circle text-info"></i>
                                </div>
                                <div class="notification-content-enhanced">
                                    <div class="notification-title-enhanced">System Status</div>
                                    <div class="notification-message-enhanced">All systems operating normally</div>
                                    <div class="notification-time-enhanced">Just now</div>
                                </div>
                            </div>
                            <div class="notification-item-enhanced warning">
                                <div class="notification-icon-enhanced">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <div class="notification-content-enhanced">
                                    <div class="notification-title-enhanced">Scheduled Maintenance</div>
                                    <div class="notification-message-enhanced">Computer lab maintenance scheduled for tomorrow</div>
                                    <div class="notification-time-enhanced">2 hours ago</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Equipment Overview Enhanced -->
                <div class="widget-container-enhanced animate-on-scroll" data-widget="equipment-overview">
                    <div class="widget-header-enhanced">
                        <h3 class="widget-title-enhanced">
                            <i class="fas fa-tools"></i>
                            Equipment Overview
                        </h3>
                    </div>
                    <div class="widget-content-enhanced">
                        <div class="maintenance-stats-enhanced">
                            <div class="maintenance-stat-enhanced text-center mb-3">
                                <div class="maintenance-stat-value-enhanced text-success">{{ $support['equipment']['working'] ?? 15 }}</div>
                                <div class="maintenance-stat-label-enhanced text-muted">Working</div>
                            </div>
                            <div class="maintenance-stat-enhanced text-center mb-3">
                                <div class="maintenance-stat-value-enhanced text-warning">{{ $support['equipment']['maintenance'] ?? 2 }}</div>
                                <div class="maintenance-stat-label-enhanced text-muted">Under Maintenance</div>
                            </div>
                            <div class="maintenance-stat-enhanced text-center">
                                <div class="maintenance-stat-value-enhanced text-danger">{{ $support['equipment']['broken'] ?? 1 }}</div>
                                <div class="maintenance-stat-label-enhanced text-muted">Needs Repair</div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ isset($support['equipment']) ? round(($support['equipment']['working'] / ($support['equipment']['working'] + $support['equipment']['maintenance'] + $support['equipment']['broken'])) * 100) : 80 }}%"></div>
                            </div>
                            <small class="text-muted">Overall equipment health: {{ isset($support['equipment']) ? round(($support['equipment']['working'] / ($support['equipment']['working'] + $support['equipment']['maintenance'] + $support['equipment']['broken'])) * 100) : 80 }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav-bar d-lg-none">
        <a href="{{ route('ajk.dashboard') }}" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('facilities.index') }}" class="mobile-nav-item">
            <i class="fas fa-building"></i>
            <span>Facilities</span>
        </a>
        <a href="{{ route('tasks.index') }}" class="mobile-nav-item">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('maintenance.index') }}" class="mobile-nav-item">
            <i class="fas fa-wrench"></i>
            <span>Maintenance</span>
        </a>
        <a href="#" class="mobile-nav-item" onclick="toggleCustomization()">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>

    <!-- Real-time Update Indicator -->
    <div id="updateIndicator" class="position-fixed" style="top: 20px; right: 20px; z-index: 1060; display: none;">
        <div class="badge" style="background: var(--ajk-primary); color: white;">
            <i class="fas fa-sync-alt fa-spin me-1"></i> Updating...
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// AJK Dashboard Manager
class AJKDashboardManager {
    constructor() {
        this.lastUpdateTime = {{ time() }};
        this.updateInterval = null;
        this.isCustomizationOpen = false;
        this.widgets = new Set(['facility-status', 'pending-tasks', 'quick-actions', 'support-schedule', 'notifications', 'equipment-overview']);
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
        this.updateInterval = setInterval(() => this.fetchUpdates(), 90000); // Every 90 seconds for AJK
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

        // Facility card interactions
        document.querySelectorAll('.facility-card-enhanced').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
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
        const settings = localStorage.getItem('ajk_dashboard_settings');
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
        localStorage.setItem('ajk_dashboard_settings', JSON.stringify(settings));
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
    dashboardManager = new AJKDashboardManager();
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
        localStorage.removeItem('ajk_dashboard_settings');
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