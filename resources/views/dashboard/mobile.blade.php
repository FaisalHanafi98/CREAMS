@extends('layouts.app')

@section('title', 'Mobile Dashboard')

@push('styles')
<style>
    .mobile-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 10px;
    }
    
    .mobile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .mobile-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .mobile-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .mobile-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .mobile-stat {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .mobile-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 18px;
        color: white;
    }
    
    .mobile-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .mobile-stat-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .mobile-section {
        background: white;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .mobile-section-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f7fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .mobile-section-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .mobile-section-content {
        padding: 15px 20px;
    }
    
    .mobile-activity-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .mobile-activity-item:last-child {
        border-bottom: none;
    }
    
    .mobile-activity-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 14px;
        color: white;
    }
    
    .mobile-activity-info {
        flex: 1;
    }
    
    .mobile-activity-title {
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 2px;
    }
    
    .mobile-activity-meta {
        font-size: 12px;
        color: #718096;
    }
    
    .mobile-quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .mobile-action-btn {
        background: white;
        border-radius: 12px;
        padding: 20px 10px;
        text-align: center;
        text-decoration: none;
        color: #4a5568;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .mobile-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        color: #667eea;
        text-decoration: none;
    }
    
    .mobile-action-icon {
        font-size: 24px;
        margin-bottom: 8px;
        color: #667eea;
    }
    
    .mobile-action-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .mobile-notification {
        background: #ebf8ff;
        border-left: 4px solid #4299e1;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        font-size: 14px;
    }
    
    .mobile-notification.warning {
        background: #fffbeb;
        border-color: #f6ad55;
    }
    
    .mobile-notification.success {
        background: #f0fff4;
        border-color: #48bb78;
    }
    
    .mobile-notification.error {
        background: #fed7d7;
        border-color: #f56565;
    }
    
    .mobile-update-indicator {
        position: fixed;
        top: 10px;
        right: 10px;
        background: #667eea;
        color: white;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 12px;
        z-index: 1000;
        display: none;
    }
    
    .mobile-footer {
        text-align: center;
        padding: 20px;
        color: #718096;
        font-size: 12px;
    }
    
    /* Pull-to-refresh styles */
    .pull-to-refresh {
        position: relative;
        overflow: hidden;
    }
    
    .ptr-content {
        transition: transform 0.3s ease;
    }
    
    .ptr-indicator {
        position: absolute;
        top: -60px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 40px;
        background: #667eea;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .ptr-indicator.active {
        top: 10px;
    }
    
    /* Swipe navigation */
    .swipe-container {
        position: relative;
        overflow: hidden;
    }
    
    .swipe-content {
        display: flex;
        transition: transform 0.3s ease;
    }
    
    .swipe-panel {
        min-width: 100%;
        flex-shrink: 0;
    }
    
    .swipe-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 15px;
    }
    
    .swipe-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e0;
        transition: background 0.3s ease;
    }
    
    .swipe-dot.active {
        background: #667eea;
    }
</style>
@endpush

@section('content')
<div class="mobile-dashboard">
    <!-- Update Indicator -->
    <div id="mobileUpdateIndicator" class="mobile-update-indicator">
        <i class="fas fa-sync-alt fa-spin"></i> Updating...
    </div>

    <!-- Header -->
    <div class="mobile-header">
        <div class="mobile-title">CREAMS Mobile</div>
        <div class="mobile-subtitle">{{ ucfirst(session('role', 'User')) }} • {{ session('name', 'Guest') }}</div>
    </div>

    <!-- Stats Grid -->
    <div class="mobile-stats">
        @foreach(array_slice($stats ?? [], 0, 4) as $key => $value)
            <div class="mobile-stat">
                <div class="mobile-stat-icon">
                    @if($key === 'active_trainees' || $key === 'total_trainees')
                        <i class="fas fa-user-graduate"></i>
                    @elseif($key === 'today_sessions' || $key === 'total_sessions')
                        <i class="fas fa-calendar-day"></i>
                    @elseif($key === 'pending_tasks' || $key === 'total_activities')
                        <i class="fas fa-tasks"></i>
                    @else
                        <i class="fas fa-chart-bar"></i>
                    @endif
                </div>
                <div class="mobile-stat-value">{{ $value }}</div>
                <div class="mobile-stat-label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
            </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="mobile-quick-actions">
        @if(isset($quick_actions) && count($quick_actions) > 0)
            @foreach($quick_actions as $action)
                <a href="{{ route($action['route']) }}" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <i class="{{ $action['icon'] }}"></i>
                    </div>
                    <div class="mobile-action-label">{{ $action['label'] }}</div>
                </a>
            @endforeach
        @else
            <a href="{{ route('trainees.home') }}" class="mobile-action-btn">
                <div class="mobile-action-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="mobile-action-label">Trainees</div>
            </a>
            
            <a href="{{ route('activities.home') }}" class="mobile-action-btn">
                <div class="mobile-action-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="mobile-action-label">Activities</div>
            </a>
            
            <a href="{{ route('profile') }}" class="mobile-action-btn">
                <div class="mobile-action-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="mobile-action-label">Profile</div>
            </a>
        @endif
    </div>

    <!-- Recent Activities -->
    <div class="mobile-section">
        <div class="mobile-section-header">
            <h3 class="mobile-section-title">Recent Activities</h3>
            <i class="fas fa-chevron-right" style="color: #cbd5e0; font-size: 12px;"></i>
        </div>
        <div class="mobile-section-content">
            @if(isset($recent['activities']) && count($recent['activities']) > 0)
                @foreach($recent['activities'] as $activity)
                    <div class="mobile-activity-item">
                        <div class="mobile-activity-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="mobile-activity-info">
                            <div class="mobile-activity-title">{{ $activity['title'] ?? 'Activity' }}</div>
                            <div class="mobile-activity-meta">{{ $activity['time'] ?? 'Recently' }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="mobile-activity-item">
                    <div class="mobile-activity-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="mobile-activity-info">
                        <div class="mobile-activity-title">Today's Sessions</div>
                        <div class="mobile-activity-meta">Check your schedule</div>
                    </div>
                </div>
                
                <div class="mobile-activity-item">
                    <div class="mobile-activity-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="mobile-activity-info">
                        <div class="mobile-activity-title">Trainee Progress</div>
                        <div class="mobile-activity-meta">Review and update</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Notifications -->
    @if(isset($recent['notifications']) && count($recent['notifications']) > 0)
        <div class="mobile-section">
            <div class="mobile-section-header">
                <h3 class="mobile-section-title">Notifications</h3>
                <span style="background: #f56565; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;">{{ count($recent['notifications']) }}</span>
            </div>
            <div class="mobile-section-content">
                @foreach($recent['notifications'] as $notification)
                    <div class="mobile-notification {{ $notification['type'] ?? 'info' }}">
                        {{ $notification['message'] ?? 'New notification' }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- System Status -->
    <div class="mobile-section">
        <div class="mobile-section-header">
            <h3 class="mobile-section-title">System Status</h3>
            <div style="width: 10px; height: 10px; background: #48bb78; border-radius: 50%;"></div>
        </div>
        <div class="mobile-section-content">
            <div style="font-size: 14px; color: #718096;">
                All systems operational • Last updated: {{ now()->format('H:i') }}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mobile-footer">
        CREAMS Mobile Dashboard • Built for Performance
    </div>
</div>
@endsection

@push('scripts')
<script>
// Mobile-specific JavaScript
let lastUpdateTime = {{ time() }};
let updateInterval;
let currentPanel = 0;

document.addEventListener('DOMContentLoaded', function() {
    initializeMobileDashboard();
    startMobileUpdates();
    initializePullToRefresh();
    initializeSwipeNavigation();
});

function initializeMobileDashboard() {
    // Add touch-friendly interactions
    document.querySelectorAll('.mobile-action-btn, .mobile-stat').forEach(element => {
        element.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        element.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Animate stats on load
    document.querySelectorAll('.mobile-stat-value').forEach(element => {
        const finalValue = parseInt(element.textContent);
        element.textContent = '0';
        animateValue(element, 0, finalValue, 1000);
    });
}

function startMobileUpdates() {
    // More frequent updates for mobile users (every 2 minutes)
    updateInterval = setInterval(fetchMobileUpdates, 120000);
}

function fetchMobileUpdates() {
    const indicator = document.getElementById('mobileUpdateIndicator');
    indicator.style.display = 'block';
    
    fetch(`{{ route('dashboard.updates') }}?last_update=${lastUpdateTime}&mobile=true`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateMobileStats(data.stats);
                showMobileNotifications(data.updates);
                lastUpdateTime = data.timestamp;
            }
        })
        .catch(error => {
            console.error('Mobile update failed:', error);
        })
        .finally(() => {
            indicator.style.display = 'none';
        });
}

function updateMobileStats(stats) {
    if (!stats) return;
    
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            const currentValue = parseInt(element.textContent);
            const newValue = stats[key];
            
            if (currentValue !== newValue) {
                animateValue(element, currentValue, newValue, 800);
            }
        }
    });
}

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

function showMobileNotifications(updates) {
    updates.forEach(update => {
        const notification = document.createElement('div');
        notification.className = `mobile-notification ${update.type || 'info'}`;
        notification.style.cssText = 'position: fixed; top: 70px; left: 10px; right: 10px; z-index: 1001;';
        notification.textContent = update.message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 4000);
    });
}

function initializePullToRefresh() {
    let startY = 0;
    let currentY = 0;
    let pulling = false;
    
    const container = document.querySelector('.mobile-dashboard');
    const content = container;
    
    container.addEventListener('touchstart', function(e) {
        if (window.scrollY === 0) {
            startY = e.touches[0].clientY;
            pulling = true;
        }
    });
    
    container.addEventListener('touchmove', function(e) {
        if (pulling && window.scrollY === 0) {
            currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            
            if (diff > 0 && diff < 100) {
                e.preventDefault();
                content.style.transform = `translateY(${diff * 0.5}px)`;
            }
        }
    });
    
    container.addEventListener('touchend', function() {
        if (pulling && currentY - startY > 60) {
            // Trigger refresh
            fetchMobileUpdates();
        }
        
        content.style.transform = 'translateY(0)';
        pulling = false;
    });
}

function initializeSwipeNavigation() {
    // Implementation for swipe navigation between dashboard sections
    let startX = 0;
    let currentX = 0;
    
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
    });
    
    document.addEventListener('touchmove', function(e) {
        currentX = e.touches[0].clientX;
    });
    
    document.addEventListener('touchend', function() {
        const diff = startX - currentX;
        
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                // Swipe left - could navigate to next section
                console.log('Swiped left');
            } else {
                // Swipe right - could navigate to previous section
                console.log('Swiped right');
            }
        }
    });
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
@endpush