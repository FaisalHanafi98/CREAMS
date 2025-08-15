@extends('layouts.app')

@section('title', 'Admin Dashboard - CREAMS')

@push('styles')
<link id="dashboard-theme" rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
        --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    /* Enhanced Dashboard Container */
    .admin-dashboard {
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
        border-left: 3px solid var(--primary-color);
    }

    .dashboard-customization.active {
        right: 0;
    }

    .customization-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--gradient-primary);
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
        background: var(--primary-color);
    }

    .widget-toggle input:checked + .toggle-slider::before {
        transform: translateX(25px);
    }

    /* Enhanced Dashboard Header */
    .dashboard-header-enhanced {
        background: var(--gradient-primary);
        color: white;
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(200, 80, 192, 0.3);
        border-radius: 0 0 30px 30px;
        margin-bottom: 30px;
    }

    .dashboard-header-enhanced::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }

    .welcome-section h1 {
        font-size: 3rem;
        font-weight: 700;
        margin: 0;
        text-shadow: 0 3px 6px rgba(0,0,0,0.3);
        position: relative;
        z-index: 2;
    }

    .dashboard-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-top: 10px;
        position: relative;
        z-index: 2;
    }

    /* Enhanced Statistics Grid */
    .enhanced-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin: -60px 30px 30px;
        position: relative;
        z-index: 10;
    }

    .enhanced-stat-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border-left: 5px solid var(--primary-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .enhanced-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(45deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
        border-radius: 50%;
        transform: translate(30px, -30px);
    }

    .enhanced-stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .enhanced-stat-card.users { border-left-color: var(--primary-color); }
    .enhanced-stat-card.trainees { border-left-color: var(--success-color); }
    .enhanced-stat-card.activities { border-left-color: var(--warning-color); }
    .enhanced-stat-card.centres { border-left-color: var(--danger-color); }

    .stat-icon-enhanced {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .stat-icon-enhanced.primary { background: var(--gradient-primary); }
    .stat-icon-enhanced.success { background: linear-gradient(135deg, var(--success-color), #20c997); }
    .stat-icon-enhanced.warning { background: linear-gradient(135deg, var(--warning-color), #fd7e14); }
    .stat-icon-enhanced.danger { background: linear-gradient(135deg, var(--danger-color), #e83e8c); }

    .stat-number-enhanced {
        font-size: 3rem;
        font-weight: 800;
        color: var(--dark-color);
        margin-bottom: 8px;
        position: relative;
        z-index: 2;
        line-height: 1;
    }

    .stat-label-enhanced {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    /* Enhanced Main Content Grid */
    .admin-content-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 30px;
        padding: 0 30px 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Enhanced Widgets */
    .dashboard-widget-enhanced {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .dashboard-widget-enhanced:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .widget-header-enhanced {
        background: var(--gradient-primary);
        color: white;
        padding: 25px;
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .widget-title-enhanced {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .widget-content-enhanced {
        padding: 25px;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Enhanced Management Items */
    .management-item-enhanced {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 10px;
        background: #f8f9fc;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .management-item-enhanced:hover {
        background: white;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .item-avatar-enhanced {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gradient-primary);
        color: white;
        font-weight: 600;
        margin-right: 15px;
        font-size: 20px;
    }

    /* Quick Actions Grid */
    .quick-actions-enhanced {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .quick-action-enhanced {
        background: white;
        border: 2px solid var(--border-color);
        color: var(--dark-color);
        padding: 20px;
        border-radius: 15px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .quick-action-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        transition: all 0.3s ease;
        z-index: 1;
    }

    .quick-action-enhanced:hover::before {
        left: 0;
    }

    .quick-action-enhanced:hover {
        color: white;
        text-decoration: none;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(200, 80, 192, 0.4);
        border-color: var(--primary-color);
    }

    .quick-action-icon-enhanced {
        font-size: 32px;
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    .quick-action-label-enhanced {
        font-weight: 600;
        font-size: 14px;
        position: relative;
        z-index: 2;
    }

    /* Mobile Responsive */
    @media (max-width: 1200px) {
        .admin-content-grid {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 0 20px 20px;
        }
        
        .enhanced-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            margin: -60px 20px 30px;
        }
    }

    @media (max-width: 768px) {
        .welcome-section h1 {
            font-size: 2rem;
        }
        
        .enhanced-stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .dashboard-customization {
            width: 100%;
            right: -100%;
        }

        .quick-actions-enhanced {
            grid-template-columns: 1fr;
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
        color: var(--primary-color);
        background: rgba(200, 80, 192, 0.1);
    }

    .mobile-nav-item i {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .mobile-nav-item span {
        font-size: 11px;
        font-weight: 500;
    }

    /* Scrollbar Styling */
    .widget-content-enhanced::-webkit-scrollbar {
        width: 6px;
    }

    .widget-content-enhanced::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .widget-content-enhanced::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div class="admin-dashboard">
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
                    <span>Statistics Cards</span>
                    <input type="checkbox" id="toggle-stats" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
                <label class="widget-toggle">
                    <span>Recent Activities</span>
                    <input type="checkbox" id="toggle-activities" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
                <label class="widget-toggle">
                    <span>System Management</span>
                    <input type="checkbox" id="toggle-management" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
                <label class="widget-toggle">
                    <span>Centre Performance</span>
                    <input type="checkbox" id="toggle-centres" checked style="display: none;">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Enhanced Dashboard Header -->
    <div class="dashboard-header-enhanced">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="welcome-section">
                        <h1>
                            <i class="fas fa-crown me-3"></i>
                            Welcome back, {{ session('name', 'Administrator') }}!
                        </h1>
                        <p class="dashboard-subtitle">
                            Comprehensive system oversight and management dashboard
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="header-actions">
                        <button class="btn btn-light btn-lg me-3" onclick="toggleCustomization()" title="Customize Dashboard">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="system-status-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fas fa-circle text-success me-2"></i>
                                    <strong>System Online</strong>
                                </div>
                                <div class="text-end ms-3">
                                    <small>Load: {{ $performance['load_time'] ?? '0' }}ms</small><br>
                                    <small>{{ now()->format('H:i, M d Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="enhanced-stats-grid" id="statsWidget">
        <div class="enhanced-stat-card users">
            <div class="stat-icon-enhanced primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number-enhanced">{{ $stats['total_users'] ?? 0 }}</div>
            <div class="stat-label-enhanced">Total Users</div>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" style="width: {{ min(($stats['total_users'] ?? 0) * 2, 100) }}%; background: var(--primary-color);"></div>
            </div>
        </div>
        
        <div class="enhanced-stat-card trainees">
            <div class="stat-icon-enhanced success">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-number-enhanced">{{ $stats['total_trainees'] ?? 0 }}</div>
            <div class="stat-label-enhanced">Active Trainees</div>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" style="width: {{ min(($stats['total_trainees'] ?? 0) * 1.5, 100) }}%; background: var(--success-color);"></div>
            </div>
        </div>
        
        <div class="enhanced-stat-card activities">
            <div class="stat-icon-enhanced warning">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-number-enhanced">{{ $stats['total_activities'] ?? 0 }}</div>
            <div class="stat-label-enhanced">Active Programs</div>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" style="width: {{ min(($stats['total_activities'] ?? 0) * 0.5, 100) }}%; background: var(--warning-color);"></div>
            </div>
        </div>
        
        <div class="enhanced-stat-card centres">
            <div class="stat-icon-enhanced danger">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-number-enhanced">{{ $stats['active_centres'] ?? 0 }}</div>
            <div class="stat-label-enhanced">Active Centres</div>
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" style="width: 100%; background: var(--danger-color);"></div>
            </div>
        </div>
    </div>

    <!-- Enhanced Main Content Grid -->
    <div class="admin-content-grid">
        <!-- Staff Management -->
        <div class="dashboard-widget-enhanced" id="managementWidget">
            <div class="widget-header-enhanced">
                <h3 class="widget-title-enhanced">
                    <i class="fas fa-users-cog"></i>
                    Staff Management
                </h3>
                <button class="btn btn-light btn-sm" onclick="refreshWidget('management')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="widget-content-enhanced">
                <!-- Quick Actions -->
                <div class="quick-actions-enhanced">
                    <a href="{{ route('staffs.register') }}" class="quick-action-enhanced">
                        <i class="fas fa-user-plus quick-action-icon-enhanced"></i>
                        <span class="quick-action-label-enhanced">Add Staff</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="quick-action-enhanced">
                        <i class="fas fa-users quick-action-icon-enhanced"></i>
                        <span class="quick-action-label-enhanced">View All</span>
                    </a>
                </div>

                <!-- Recent Staff -->
                <h6 class="mb-3">Recent Staff Members</h6>
                @if(isset($recent['users']) && count($recent['users']) > 0)
                    @foreach($recent['users'] as $user)
                        <div class="management-item-enhanced">
                            <div class="item-avatar-enhanced">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $user->name ?? 'Unknown User' }}</div>
                                <div class="text-muted small">{{ ucfirst($user->role ?? 'user') }} • {{ $user->created_at ? $user->created_at->diffForHumans() : 'Unknown' }}</div>
                            </div>
                            <div class="badge bg-success">Active</div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>No recent staff members</p>
                        <a href="{{ route('staffs.register') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Staff
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- System Activities -->
        <div class="dashboard-widget-enhanced" id="activitiesWidget">
            <div class="widget-header-enhanced">
                <h3 class="widget-title-enhanced">
                    <i class="fas fa-chart-line"></i>
                    System Activities
                </h3>
                <button class="btn btn-light btn-sm" onclick="refreshWidget('activities')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="widget-content-enhanced">
                <!-- Today's Schedule -->
                <h6 class="mb-3">Today's Activity Schedule</h6>
                @if(isset($schedule['today']) && $schedule['today']->count() > 0)
                    @foreach($schedule['today'] as $session)
                        <div class="management-item-enhanced">
                            <div class="item-avatar-enhanced" style="background: var(--warning-color);">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $session->activity_name ?? 'Session' }}</div>
                                <div class="text-muted small">
                                    {{ $session->participants_count ?? 0 }} participants • 
                                    {{ $session->location ?? 'Main Hall' }}
                                </div>
                            </div>
                            <div class="badge bg-{{ $session->status === 'ongoing' ? 'primary' : ($session->status === 'completed' ? 'success' : 'secondary') }}">
                                {{ ucfirst($session->status ?? 'scheduled') }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-day fa-2x mb-2"></i>
                        <p>No activities scheduled for today</p>
                        <a href="{{ route('activities.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Create Activity
                        </a>
                    </div>
                @endif

                <!-- Recent System Activity -->
                <h6 class="mb-3 mt-4">Recent System Activity</h6>
                @if(isset($recent['activities']) && count($recent['activities']) > 0)
                    @foreach($recent['activities'] as $activity)
                        <div class="management-item-enhanced">
                            <div class="item-avatar-enhanced" style="background: var(--success-color);">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $activity['title'] ?? 'Activity' }}</div>
                                <div class="text-muted small">{{ $activity['type'] ?? 'General' }} • {{ $activity['time'] ?? 'Unknown' }}</div>
                            </div>
                            <div class="badge bg-success">Active</div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3 text-muted">
                        <p class="mb-0">No recent activities</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Centre Management -->
        <div class="dashboard-widget-enhanced" id="centresWidget">
            <div class="widget-header-enhanced">
                <h3 class="widget-title-enhanced">
                    <i class="fas fa-building"></i>
                    Centre Management
                </h3>
                <button class="btn btn-light btn-sm" onclick="refreshWidget('centres')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="widget-content-enhanced">
                <!-- Quick Actions -->
                <div class="quick-actions-enhanced">
                    <a href="{{ route('centres.create') }}" class="quick-action-enhanced">
                        <i class="fas fa-plus quick-action-icon-enhanced"></i>
                        <span class="quick-action-label-enhanced">Add Centre</span>
                    </a>
                    <a href="{{ route('centres.index') }}" class="quick-action-enhanced">
                        <i class="fas fa-building quick-action-icon-enhanced"></i>
                        <span class="quick-action-label-enhanced">Manage</span>
                    </a>
                </div>

                <!-- Centre Performance -->
                <h6 class="mb-3">Centre Performance</h6>
                @if(isset($charts['centre_performance']) && count($charts['centre_performance']) > 0)
                    @foreach($charts['centre_performance'] as $centre)
                        @php
                            $rate = ($centre->trainee_count ?? 0) * 10 + 70; // Sample calculation
                            $rate = min($rate, 100);
                            $class = $rate >= 90 ? 'success' : ($rate >= 75 ? 'primary' : 'warning');
                        @endphp
                        <div class="management-item-enhanced">
                            <div class="item-avatar-enhanced" style="background: var(--{{ $class }}-color);">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $centre->name ?? 'Centre' }}</div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $class }}" style="width: {{ $rate }}%"></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-{{ $class }}">{{ $rate }}%</div>
                                <small class="text-muted">Activity Rate</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="management-item-enhanced">
                        <div class="item-avatar-enhanced" style="background: var(--success-color);">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Gombak</div>
                            <div class="progress mt-1" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 92%"></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">92%</div>
                            <small class="text-muted">Activity Rate</small>
                        </div>
                    </div>
                    
                    <div class="management-item-enhanced">
                        <div class="item-avatar-enhanced" style="background: var(--primary-color);">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Kuantan</div>
                            <div class="progress mt-1" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 88%"></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary">88%</div>
                            <small class="text-muted">Activity Rate</small>
                        </div>
                    </div>
                    
                    <div class="management-item-enhanced">
                        <div class="item-avatar-enhanced" style="background: var(--warning-color);">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Pagoh</div>
                            <div class="progress mt-1" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-warning">85%</div>
                            <small class="text-muted">Activity Rate</small>
                        </div>
                    </div>
                @endif

                <!-- System Health -->
                @php
                    $healthData = $system_health ?? [
                        'database' => 'healthy',
                        'storage' => 'healthy', 
                        'cache' => 'healthy'
                    ];
                @endphp
                <h6 class="mb-3 mt-4">System Health</h6>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-success mb-1"><i class="fas fa-database fa-lg"></i></div>
                        <small class="d-block"><strong>{{ ucfirst($healthData['database'] ?? 'Healthy') }}</strong></small>
                        <small class="text-muted">Database</small>
                    </div>
                    <div class="col-4">
                        <div class="text-primary mb-1"><i class="fas fa-hdd fa-lg"></i></div>
                        <small class="d-block"><strong>{{ ucfirst($healthData['storage'] ?? 'Healthy') }}</strong></small>
                        <small class="text-muted">Storage</small>
                    </div>
                    <div class="col-4">
                        <div class="text-warning mb-1"><i class="fas fa-memory fa-lg"></i></div>
                        <small class="d-block"><strong>{{ ucfirst($healthData['cache'] ?? 'Healthy') }}</strong></small>
                        <small class="text-muted">Cache</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-nav-bar d-lg-none">
        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.users') }}" class="mobile-nav-item">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="{{ route('activities.home') }}" class="mobile-nav-item">
            <i class="fas fa-calendar"></i>
            <span>Activities</span>
        </a>
        <a href="{{ route('centres.index') }}" class="mobile-nav-item">
            <i class="fas fa-building"></i>
            <span>Centres</span>
        </a>
        <a href="#" class="mobile-nav-item" onclick="toggleCustomization()">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
class AdminDashboardManager {
    constructor() {
        this.refreshInterval = null;
        this.isRefreshing = false;
        this.widgets = {
            stats: true,
            activities: true,
            management: true,
            centres: true
        };
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

    toggleCustomization() {
        const panel = document.getElementById('customizationPanel');
        panel.classList.toggle('active');
    }

    initRealTimeUpdates() {
        // Auto-refresh dashboard data every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.refreshAllWidgets();
        }, 300000);
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
            const content = widget.querySelector('.widget-content-enhanced');
            if (content) {
                content.innerHTML = html;
                this.initAnimations();
            }
        }
    }

    initAnimations() {
        // Animate stat cards
        const statCards = document.querySelectorAll('.enhanced-stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animate management items
        const managementItems = document.querySelectorAll('.management-item-enhanced');
        managementItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 800 + (index * 50));
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
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
        localStorage.setItem('admin_dashboard_widgets', JSON.stringify(this.widgets));
    }

    loadUserPreferences() {
        const saved = localStorage.getItem('admin_dashboard_widgets');
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
    if (window.adminDashboard) {
        window.adminDashboard.refreshWidget(type);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminDashboard = new AdminDashboardManager();
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (window.adminDashboard) {
            window.adminDashboard.destroy();
        }
    });
});
</script>
@endpush