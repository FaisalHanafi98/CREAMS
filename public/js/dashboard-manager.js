/**
 * Dashboard Manager - Handles dashboard functionality including refresh, customization, and real-time updates
 * 
 * CREAMS - Care Rehabilitation Centre Management System
 */

class DashboardManager {
    constructor() {
        this.config = {
            role: '',
            userId: '',
            refreshInterval: 300000, // 5 minutes default
            apiEndpoints: {
                refresh: '/dashboard/api/refresh',
                customize: '/dashboard/api/customize',
                notifications: '/dashboard/api/notifications'
            }
        };
        
        this.refreshTimer = null;
        this.isRefreshing = false;
        
        // Bind methods to maintain context
        this.refreshDashboard = this.refreshDashboard.bind(this);
        this.openCustomization = this.openCustomization.bind(this);
        this.saveCustomization = this.saveCustomization.bind(this);
        this.handleNotificationViewAll = this.handleNotificationViewAll.bind(this);
    }

    /**
     * Initialize the dashboard manager with configuration
     * @param {Object} config - Configuration object
     */
    init(config) {
        this.config = { ...this.config, ...config };
        
        console.log('Dashboard Manager initialized', {
            role: this.config.role,
            userId: this.config.userId,
            refreshInterval: this.config.refreshInterval
        });
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Start auto-refresh if enabled
        if (this.config.refreshInterval > 0) {
            this.startAutoRefresh();
        }
        
        // Load saved customizations
        this.loadCustomizations();
    }

    /**
     * Set up event listeners for dashboard interactions
     */
    setupEventListeners() {
        // Handle "View All Notifications" clicks
        const viewAllButtons = document.querySelectorAll('[href*="notifications"], .view-all-notifications');
        viewAllButtons.forEach(button => {
            button.addEventListener('click', this.handleNotificationViewAll);
        });
        
        // Handle modal close events
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal') || e.target.classList.contains('close')) {
                this.closeModals();
            }
        });
        
        // Handle escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModals();
            }
        });
    }

    /**
     * Refresh dashboard data
     */
    async refreshDashboard() {
        if (this.isRefreshing) {
            console.log('Dashboard refresh already in progress');
            return;
        }

        this.isRefreshing = true;
        
        // Update button state
        const refreshBtn = document.querySelector('.btn-refresh');
        if (refreshBtn) {
            refreshBtn.classList.add('refreshing');
            const icon = refreshBtn.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
        }

        try {
            console.log('Refreshing dashboard data...');
            
            const response = await fetch(this.config.apiEndpoints.refresh, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            const data = await response.json();
            
            if (data.success) {
                // Update dashboard sections with new data
                this.updateDashboardData(data.data);
                
                // Show success message
                this.showNotification('Dashboard refreshed successfully', 'success');
                
                console.log('Dashboard refreshed successfully');
            } else {
                throw new Error(data.message || 'Failed to refresh dashboard');
            }
        } catch (error) {
            console.error('Dashboard refresh failed:', error);
            this.showNotification('Failed to refresh dashboard: ' + error.message, 'error');
        } finally {
            this.isRefreshing = false;
            
            // Reset button state
            if (refreshBtn) {
                refreshBtn.classList.remove('refreshing');
                const icon = refreshBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-spin');
                }
            }
        }
    }

    /**
     * Update dashboard data elements
     * @param {Object} data - New dashboard data
     */
    updateDashboardData(data) {
        try {
            // Update statistics
            if (data.stats) {
                this.updateStatistics(data.stats);
            }
            
            // Update recent activities
            if (data.recent_activities) {
                this.updateRecentActivities(data.recent_activities);
            }
            
            // Update notifications
            if (data.notifications) {
                this.updateNotifications(data.notifications);
            }
            
            // Update charts
            if (data.charts) {
                this.updateCharts(data.charts);
            }
            
            // Update last refresh time
            const refreshTime = document.querySelector('.last-refresh-time');
            if (refreshTime) {
                refreshTime.textContent = 'Last updated: ' + new Date().toLocaleTimeString();
            }
        } catch (error) {
            console.error('Error updating dashboard data:', error);
        }
    }

    /**
     * Update statistics cards
     * @param {Object} stats - Statistics data
     */
    updateStatistics(stats) {
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"], .stat-value[data-key="${key}"]`);
            if (element) {
                // Animate the number change
                this.animateNumber(element, parseInt(element.textContent) || 0, stats[key]);
            }
        });
    }

    /**
     * Update recent activities section
     * @param {Array} activities - Recent activities data
     */
    updateRecentActivities(activities) {
        const container = document.querySelector('.activity-list');
        if (!container) return;

        container.innerHTML = '';
        
        if (activities.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No recent activities found</p>
                </div>
            `;
            return;
        }

        activities.forEach(activity => {
            const activityElement = document.createElement('div');
            activityElement.className = 'activity-item';
            activityElement.innerHTML = `
                <div class="activity-info">
                    <h4 class="activity-title">${activity.title || activity.activity_name || 'Untitled Activity'}</h4>
                    <p class="activity-meta">
                        <span class="activity-date">${activity.date || activity.created_at || 'No date'}</span>
                        ${this.config.role === 'admin' || this.config.role === 'supervisor' ? `
                            <span class="activity-centre">${activity.centre || 'Unknown Centre'}</span>
                            <span class="activity-teacher">by ${activity.teacher || 'Unknown Teacher'}</span>
                        ` : ''}
                    </p>
                </div>
                <div class="activity-status">
                    <span class="badge badge-${activity.status === 'completed' ? 'success' : 'primary'}">
                        ${activity.status ? activity.status.charAt(0).toUpperCase() + activity.status.slice(1) : 'Active'}
                    </span>
                </div>
            `;
            container.appendChild(activityElement);
        });
    }

    /**
     * Update notifications
     * @param {Array} notifications - Notifications data
     */
    updateNotifications(notifications) {
        // Update notification count
        const countElement = document.querySelector('.notification-count, #notificationCount');
        if (countElement) {
            countElement.textContent = notifications.filter(n => !n.read).length;
        }
        
        // Update notification list in widgets
        const notificationsList = document.querySelector('.notification-list');
        if (notificationsList && notifications.length > 0) {
            notificationsList.innerHTML = notifications.slice(0, 5).map(notification => `
                <div class="notification-item ${!notification.read ? 'unread' : ''}">
                    <div class="notification-content">
                        <p>${notification.message || notification.content}</p>
                        <small class="text-muted">${notification.time || notification.created_at || 'Just now'}</small>
                    </div>
                </div>
            `).join('');
        }
    }

    /**
     * Update charts
     * @param {Object} charts - Charts data
     */
    updateCharts(charts) {
        // This would integrate with Chart.js to update existing charts
        // For now, just log the data
        console.log('Charts data received:', charts);
        
        // Trigger custom event for chart updates
        document.dispatchEvent(new CustomEvent('dashboard:charts-updated', {
            detail: { charts }
        }));
    }

    /**
     * Animate number changes
     * @param {Element} element - Target element
     * @param {number} from - Starting number
     * @param {number} to - Ending number
     */
    animateNumber(element, from, to) {
        const duration = 1000; // 1 second
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.round(from + (to - from) * progress);
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    /**
     * Open customization modal
     */
    openCustomization() {
        const modal = document.getElementById('dashboardCustomizationModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            
            // Load current settings
            this.loadCurrentSettings();
        } else {
            console.error('Customization modal not found');
            this.showNotification('Customization panel is not available', 'error');
        }
    }

    /**
     * Load current customization settings into the modal
     */
    loadCurrentSettings() {
        try {
            // Load from localStorage or use defaults
            const settings = JSON.parse(localStorage.getItem('dashboard_settings') || '{}');
            
            // Theme setting
            const themeSelect = document.getElementById('dashboard-theme');
            if (themeSelect) {
                themeSelect.value = settings.theme || 'light';
            }
            
            // Refresh interval
            const refreshSelect = document.getElementById('refresh-interval');
            if (refreshSelect) {
                refreshSelect.value = settings.refresh_interval || 300;
            }
            
            // Widget visibility
            const widgets = settings.widgets || {};
            Object.keys(widgets).forEach(widgetKey => {
                const checkbox = document.getElementById(`show-${widgetKey}`);
                if (checkbox) {
                    checkbox.checked = widgets[widgetKey] !== false;
                }
            });
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    /**
     * Save customization settings
     */
    async saveCustomization() {
        try {
            // Collect form data
            const formData = {
                theme: document.getElementById('dashboard-theme')?.value || 'light',
                refresh_interval: parseInt(document.getElementById('refresh-interval')?.value) || 300,
                widgets: {}
            };
            
            // Collect widget visibility settings
            const widgetCheckboxes = document.querySelectorAll('[id^="show-"]');
            widgetCheckboxes.forEach(checkbox => {
                const widgetKey = checkbox.id.replace('show-', '');
                formData.widgets[widgetKey] = checkbox.checked;
            });
            
            console.log('Saving customization settings:', formData);
            
            // Save to server
            const response = await fetch(this.config.apiEndpoints.customize, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Save to localStorage for immediate use
                localStorage.setItem('dashboard_settings', JSON.stringify(formData));
                
                // Apply settings
                this.applyCustomizations(formData);
                
                // Close modal
                this.closeModals();
                
                // Show success message
                this.showNotification('Settings saved successfully', 'success');
                
                // Update refresh interval if changed
                if (formData.refresh_interval !== this.config.refreshInterval) {
                    this.config.refreshInterval = formData.refresh_interval * 1000; // Convert to milliseconds
                    this.startAutoRefresh();
                }
            } else {
                throw new Error(data.message || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Error saving customization:', error);
            this.showNotification('Failed to save settings: ' + error.message, 'error');
        }
    }

    /**
     * Apply customization settings
     * @param {Object} settings - Settings to apply
     */
    applyCustomizations(settings) {
        // Apply theme
        if (settings.theme) {
            document.body.className = document.body.className.replace(/theme-\w+/g, '');
            document.body.classList.add(`theme-${settings.theme}`);
        }
        
        // Apply widget visibility
        if (settings.widgets) {
            Object.keys(settings.widgets).forEach(widgetKey => {
                const widget = document.querySelector(`[data-widget="${widgetKey}"], .${widgetKey}-widget`);
                if (widget) {
                    widget.style.display = settings.widgets[widgetKey] ? 'block' : 'none';
                }
            });
        }
    }

    /**
     * Load saved customizations
     */
    loadCustomizations() {
        try {
            const settings = JSON.parse(localStorage.getItem('dashboard_settings') || '{}');
            if (Object.keys(settings).length > 0) {
                this.applyCustomizations(settings);
                
                // Update refresh interval
                if (settings.refresh_interval) {
                    this.config.refreshInterval = settings.refresh_interval * 1000;
                }
            }
        } catch (error) {
            console.error('Error loading customizations:', error);
        }
    }

    /**
     * Handle "View All Notifications" click
     * @param {Event} e - Click event
     */
    handleNotificationViewAll(e) {
        // Don't prevent default - let it navigate normally
        console.log('Navigating to notifications page');
    }

    /**
     * Start auto-refresh timer
     */
    startAutoRefresh() {
        // Clear existing timer
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        // Only start if interval is greater than 0
        if (this.config.refreshInterval > 0) {
            this.refreshTimer = setInterval(() => {
                this.refreshDashboard();
            }, this.config.refreshInterval);
            
            console.log(`Auto-refresh started: ${this.config.refreshInterval / 1000} seconds`);
        }
    }

    /**
     * Stop auto-refresh timer
     */
    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
            console.log('Auto-refresh stopped');
        }
    }

    /**
     * Close all open modals
     */
    closeModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
            modal.classList.remove('show');
        });
    }

    /**
     * Show notification message
     * @param {string} message - Message to show
     * @param {string} type - Type of notification (success, error, info, warning)
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `dashboard-notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add to page
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Get icon for notification type
     * @param {string} type - Notification type
     * @returns {string} Icon class
     */
    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Create global instance
window.DashboardManager = new DashboardManager();

// Auto-initialize if config is available
document.addEventListener('DOMContentLoaded', function() {
    if (window.dashboardConfig) {
        window.DashboardManager.init(window.dashboardConfig);
    }
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardManager;
}