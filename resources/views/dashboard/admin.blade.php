@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .admin-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        background: var(--card-color, #667eea);
        transition: width 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
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
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, var(--card-color, #667eea), var(--card-color-light, #8b9cf8));
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .stat-value {
        font-size: 42px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
        line-height: 1;
        transition: all 0.3s ease;
    }
    
    .stat-label {
        color: #718096;
        font-size: 15px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 12px;
    }
    
    .stat-change {
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .stat-change.positive {
        color: #48bb78;
    }
    
    .stat-change.negative {
        color: #f56565;
    }
    
    .stat-change.neutral {
        color: #a0aec0;
    }
    
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        transition: all 0.3s ease;
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f7fafc;
    }
    
    .chart-title {
        font-size: 20px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .chart-container {
        position: relative;
        height: 280px;
    }
    
    .recent-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        margin-bottom: 25px;
    }
    
    .recent-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f7fafc;
    }
    
    .recent-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .recent-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f7fafc;
        transition: all 0.2s ease;
    }
    
    .recent-item:last-child {
        border-bottom: none;
    }
    
    .recent-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: 600;
        color: white;
        font-size: 16px;
    }
    
    .recent-info {
        flex: 1;
    }
    
    .recent-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 15px;
    }
    
    .recent-meta {
        font-size: 13px;
        color: #718096;
    }
    
    .recent-time {
        font-size: 12px;
        color: #a0aec0;
        font-weight: 500;
    }
    
    .alert-section {
        margin-bottom: 30px;
    }
    
    .alert-item {
        background: white;
        border-left: 5px solid;
        border-radius: 8px;
        padding: 20px 25px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .alert-item.info {
        border-color: #4299e1;
    }
    
    .alert-item.warning {
        border-color: #f6ad55;
    }
    
    .alert-item.danger {
        border-color: #fc8181;
    }
    
    .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    }
    
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 20px;
    }
    
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 25px 15px;
        border: 2px solid #f7fafc;
        border-radius: 12px;
        text-decoration: none;
        color: #4a5568;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        text-decoration: none;
    }
    
    .action-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }
    
    .action-label {
        font-size: 14px;
        font-weight: 600;
        text-align: center;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .stat-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .chart-grid {
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
<div class="admin-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2 font-weight-bold">Admin Dashboard</h1>
                    <p class="mb-0 opacity-90">Welcome back, {{ $user['name'] ?? 'Administrator' }}! Here's your system overview.</p>
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
        <!-- System Alerts -->
        @if(isset($alerts) && count($alerts) > 0)
            <div class="alert-section">
                @foreach($alerts as $alert)
                    <div class="alert-item {{ $alert['type'] }}">
                        <strong>{{ ucfirst($alert['type']) }}:</strong> {{ $alert['message'] }}
                        @if(isset($alert['action']) && $alert['action'] !== '#')
                            <a href="{{ $alert['action'] }}" class="float-right text-decoration-none">
                                Take Action <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Statistics Grid -->
        <div class="stat-grid">
            <div class="stat-card" style="--card-color: #4299e1; --card-color-light: #63b3ed;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value" data-stat="total_users">{{ number_format($stats['total_users'] ?? 0) }}</div>
                <div class="stat-label">Total Users</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    {{ $stats['user_growth_rate'] ?? 0 }}% from last month
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #48bb78; --card-color-light: #68d391;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-value" data-stat="total_trainees">{{ number_format($stats['total_trainees'] ?? 0) }}</div>
                <div class="stat-label">Total Trainees</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    {{ $stats['trainee_growth_rate'] ?? 0 }}% from last month
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #ed8936; --card-color-light: #f6ad55;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
                <div class="stat-value" data-stat="total_activities">{{ number_format($stats['total_activities'] ?? 0) }}</div>
                <div class="stat-label">Total Activities</div>
                <div class="stat-change neutral">
                    <i class="fas fa-minus"></i>
                    Stable
                </div>
            </div>
            
            <div class="stat-card" style="--card-color: #9f7aea; --card-color-light: #b794f6;">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-value" data-stat="total_sessions">{{ number_format($stats['total_sessions'] ?? 0) }}</div>
                <div class="stat-label">Total Sessions</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    {{ $stats['system_utilization'] ?? 0 }}% utilization
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="chart-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">User Growth Trend</h3>
                    <select class="form-control form-control-sm" style="width: auto;" id="userGrowthPeriod">
                        <option value="6">Last 6 Months</option>
                        <option value="12">Last Year</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Activity Distribution</h3>
                    <button class="btn btn-sm btn-outline-primary">View All</button>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-md-6">
                <div class="recent-section">
                    <div class="recent-header">
                        <h3 class="chart-title">Recent Users</h3>
                        <a href="{{ route('staffs.home') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <ul class="recent-list">
                        @foreach($recent['users'] ?? [] as $user)
                            <li class="recent-item">
                                <div class="recent-avatar">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="recent-info">
                                    <div class="recent-name">{{ $user->name ?? 'Unknown User' }}</div>
                                    <div class="recent-meta">{{ $user->email ?? 'No email' }} • {{ ucfirst($user->role ?? 'user') }}</div>
                                </div>
                                <div class="recent-time">
                                    {{ isset($user->created_at) ? $user->created_at->diffForHumans() : 'Unknown' }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="recent-section">
                    <div class="recent-header">
                        <h3 class="chart-title">Recent Trainees</h3>
                        <a href="{{ route('trainees.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <ul class="recent-list">
                        @foreach($recent['trainees'] ?? [] as $trainee)
                            <li class="recent-item">
                                <div class="recent-avatar" style="background: linear-gradient(135deg, #48bb78, #68d391);">
                                    {{ strtoupper(substr($trainee->name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="recent-info">
                                    <div class="recent-name">{{ $trainee->name ?? 'Unknown Trainee' }}</div>
                                    <div class="recent-meta">{{ $trainee->condition ?? 'No condition' }} • Age {{ $trainee->age ?? 'N/A' }}</div>
                                </div>
                                <div class="recent-time">
                                    {{ isset($trainee->created_at) ? $trainee->created_at->diffForHumans() : 'Unknown' }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="chart-title mb-4">Quick Actions</h3>
            <div class="action-grid">
                <a href="{{ route('users.create') }}" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="action-label">Add User</div>
                </a>
                
                <a href="{{ route('trainees.create') }}" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="action-label">Add Trainee</div>
                </a>
                
                <a href="{{ route('activities.create') }}" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="action-label">Create Activity</div>
                </a>
                
                <a href="{{ route('assets.create') }}" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="action-label">Add Asset</div>
                </a>
                
                <a href="#" class="action-btn" onclick="generateReport()">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-label">Generate Report</div>
                </a>
                
                <a href="#" class="action-btn" onclick="refreshDashboard()">
                    <div class="action-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="action-label">Refresh Data</div>
                </a>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let userGrowthChart, activityChart;
let lastUpdateTime = {{ time() }};
let updateInterval;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    startRealTimeUpdates();
    initializeStatCounters();
});

// Initialize charts
function initializeCharts() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    userGrowthChart = new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($charts['user_growth']['labels'] ?? []) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode($charts['user_growth']['data'] ?? []) !!},
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Activity Distribution Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityData = {!! json_encode($charts['activity_distribution'] ?? []) !!};
    
    activityChart = new Chart(activityCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(activityData),
            datasets: [{
                data: Object.values(activityData),
                backgroundColor: [
                    '#4299e1',
                    '#48bb78',
                    '#ed8936',
                    '#9f7aea',
                    '#f56565',
                    '#38b2ac',
                    '#ecc94b',
                    '#ed64a6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

// Start real-time updates
function startRealTimeUpdates() {
    updateInterval = setInterval(fetchUpdates, 30000); // Every 30 seconds
}

// Fetch real-time updates
function fetchUpdates() {
    const indicator = document.getElementById('updateIndicator');
    indicator.style.display = 'block';
    
    fetch(`{{ route('dashboard.updates') }}?last_update=${lastUpdateTime}&include_stats=true`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.stats) {
                updateStatValues(data.stats);
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
            const currentValue = parseInt(element.textContent.replace(/,/g, ''));
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
            element.textContent = number_format(end);
            clearInterval(timer);
        } else {
            element.textContent = number_format(Math.floor(current));
        }
    }, 16);
}

// Initialize stat counters
function initializeStatCounters() {
    const statElements = document.querySelectorAll('[data-stat]');
    statElements.forEach(element => {
        const finalValue = parseInt(element.textContent.replace(/,/g, ''));
        element.textContent = '0';
        animateValue(element, 0, finalValue, 2000);
    });
}

// Refresh dashboard data
function refreshDashboard() {
    const indicator = document.getElementById('updateIndicator');
    indicator.style.display = 'block';
    
    fetch('{{ route("dashboard.refresh-stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatValues(data.stats);
                if (data.charts) {
                    updateCharts(data.charts);
                }
                showNotification('Dashboard refreshed successfully', 'success');
            }
        })
        .catch(error => {
            console.error('Refresh failed:', error);
            showNotification('Failed to refresh dashboard', 'danger');
        })
        .finally(() => {
            indicator.style.display = 'none';
        });
}

// Update charts with new data
function updateCharts(chartData) {
    if (chartData.user_growth && userGrowthChart) {
        userGrowthChart.data.labels = chartData.user_growth.labels;
        userGrowthChart.data.datasets[0].data = chartData.user_growth.data;
        userGrowthChart.update();
    }
    
    if (chartData.activity_distribution && activityChart) {
        activityChart.data.labels = Object.keys(chartData.activity_distribution);
        activityChart.data.datasets[0].data = Object.values(chartData.activity_distribution);
        activityChart.update();
    }
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

// Utility functions
function number_format(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function generateReport() {
    console.log('Generating report...');
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
@endpush