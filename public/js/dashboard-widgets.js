/**
 * Dashboard Widgets Manager
 * CREAMS - Care Rehabilitation Centre Management System
 */

class DashboardWidgets {
    constructor() {
        this.widgets = new Map();
        this.isInitialized = false;
        this.updateIntervals = new Map();
    }

    /**
     * Initialize all dashboard widgets
     */
    init() {
        if (this.isInitialized) return;
        
        console.log('Initializing Dashboard Widgets...');
        
        // Initialize different widget types
        this.initializeQuickActions();
        this.initializeNotifications();
        this.initializeCalendar();
        this.initializeSystemHealth();
        this.initializeProgressWidgets();
        this.initializeWeatherWidget();
        
        // Setup global event listeners
        this.setupEventListeners();
        
        this.isInitialized = true;
        console.log('Dashboard Widgets initialized successfully');
    }

    /**
     * Initialize quick actions widget
     */
    initializeQuickActions() {
        const quickActionsWidgets = document.querySelectorAll('.quick-actions-list');
        
        quickActionsWidgets.forEach(widget => {
            // Add hover effects and click tracking
            const actionItems = widget.querySelectorAll('.quick-action-item');
            
            actionItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    // Add click animation
                    item.classList.add('clicked');
                    setTimeout(() => item.classList.remove('clicked'), 200);
                    
                    // Track click
                    console.log('Quick action clicked:', item.textContent.trim());
                });
            });
        });
    }

    /**
     * Initialize notifications widget
     */
    initializeNotifications() {
        const notificationWidgets = document.querySelectorAll('.notification-list');
        
        notificationWidgets.forEach(widget => {
            // Add click handlers for notification items
            const notificationItems = widget.querySelectorAll('.notification-item');
            
            notificationItems.forEach(item => {
                item.addEventListener('click', () => {
                    // Mark as read
                    item.classList.remove('unread');
                    item.classList.add('read');
                    
                    // Update unread count
                    this.updateNotificationCount();
                });
            });
            
            // Auto-refresh notifications
            this.startNotificationRefresh(widget);
        });
    }

    /**
     * Initialize calendar widget
     */
    initializeCalendar() {
        const calendarWidgets = document.querySelectorAll('.calendar-widget');
        
        calendarWidgets.forEach(widget => {
            this.renderCalendar(widget);
            this.setupCalendarNavigation(widget);
        });
    }

    /**
     * Render calendar for widget
     * @param {Element} widget - Calendar widget element
     */
    renderCalendar(widget) {
        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();
        
        // Update header
        const monthHeader = widget.querySelector('.calendar-month');
        if (monthHeader) {
            monthHeader.textContent = new Intl.DateTimeFormat('en-US', {
                month: 'long',
                year: 'numeric'
            }).format(now);
        }
        
        // Generate calendar grid
        const calendarGrid = widget.querySelector('.calendar-grid');
        if (calendarGrid) {
            calendarGrid.innerHTML = this.generateCalendarHTML(currentYear, currentMonth);
        }
    }

    /**
     * Generate calendar HTML
     * @param {number} year - Year
     * @param {number} month - Month (0-11)
     * @returns {string} Calendar HTML
     */
    generateCalendarHTML(year, month) {
        const now = new Date();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();
        
        let html = '';
        
        // Day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            html += `<div class="calendar-day-header">${day}</div>`;
        });
        
        // Previous month's trailing days
        const prevMonth = new Date(year, month - 1, 0);
        const prevMonthDays = prevMonth.getDate();
        
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const day = prevMonthDays - i;
            html += `<div class="calendar-day other-month">${day}</div>`;
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const isToday = (year === now.getFullYear() && 
                           month === now.getMonth() && 
                           day === now.getDate());
            
            const hasEvent = this.hasEventOnDate(year, month, day);
            
            let classes = 'calendar-day';
            if (isToday) classes += ' today';
            if (hasEvent) classes += ' has-event';
            
            html += `<div class="${classes}" data-date="${year}-${month}-${day}">${day}</div>`;
        }
        
        // Next month's leading days
        const totalCells = 42; // 6 rows × 7 days
        const usedCells = startingDayOfWeek + daysInMonth;
        const remainingCells = totalCells - usedCells;
        
        for (let day = 1; day <= remainingCells; day++) {
            html += `<div class="calendar-day other-month">${day}</div>`;
        }
        
        return html;
    }

    /**
     * Check if date has events (mock implementation)
     * @param {number} year - Year
     * @param {number} month - Month
     * @param {number} day - Day
     * @returns {boolean} Has events
     */
    hasEventOnDate(year, month, day) {
        // Mock implementation - in real app, check against actual events
        const mockEventDays = [5, 12, 18, 25];
        return mockEventDays.includes(day);
    }

    /**
     * Setup calendar navigation
     * @param {Element} widget - Calendar widget
     */
    setupCalendarNavigation(widget) {
        const prevBtn = widget.querySelector('.calendar-prev');
        const nextBtn = widget.querySelector('.calendar-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                // Navigate to previous month
                console.log('Previous month clicked');
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                // Navigate to next month
                console.log('Next month clicked');
            });
        }
    }

    /**
     * Initialize system health widget
     */
    initializeSystemHealth() {
        const healthWidgets = document.querySelectorAll('.health-metrics, .health-stats');
        
        healthWidgets.forEach(widget => {
            // Start periodic health checks
            this.startHealthMonitoring(widget);
        });
    }

    /**
     * Start health monitoring
     * @param {Element} widget - Health widget element
     */
    startHealthMonitoring(widget) {
        const updateHealth = () => {
            // Mock health data - in real app, fetch from API
            const healthMetrics = [
                { name: 'Database', status: 'healthy', value: '99.9%' },
                { name: 'Cache', status: 'healthy', value: 'Redis OK' },
                { name: 'Storage', status: 'warning', value: '78% used' },
                { name: 'Memory', status: 'healthy', value: '4.2GB free' }
            ];
            
            this.updateHealthWidget(widget, healthMetrics);
        };
        
        // Update immediately and then every 30 seconds
        updateHealth();
        const intervalId = setInterval(updateHealth, 30000);
        this.updateIntervals.set(widget, intervalId);
    }

    /**
     * Update health widget display
     * @param {Element} widget - Health widget
     * @param {Array} metrics - Health metrics
     */
    updateHealthWidget(widget, metrics) {
        const container = widget.closest('.widget-card') || widget;
        let healthHTML = '';
        
        metrics.forEach(metric => {
            healthHTML += `
                <div class="health-metric status-${metric.status}">
                    <div class="health-metric-label">
                        <div class="health-metric-icon">
                            <i class="fas fa-${this.getHealthIcon(metric.status)}"></i>
                        </div>
                        ${metric.name}
                    </div>
                    <div class="health-metric-value">${metric.value}</div>
                </div>
            `;
        });
        
        widget.innerHTML = healthHTML;
    }

    /**
     * Get icon for health status
     * @param {string} status - Health status
     * @returns {string} Icon class
     */
    getHealthIcon(status) {
        const icons = {
            healthy: 'check-circle',
            warning: 'exclamation-triangle',
            error: 'times-circle'
        };
        return icons[status] || 'question-circle';
    }

    /**
     * Initialize progress widgets
     */
    initializeProgressWidgets() {
        const progressWidgets = document.querySelectorAll('.progress-widget');
        
        progressWidgets.forEach(widget => {
            this.animateProgressBars(widget);
        });
    }

    /**
     * Animate progress bars
     * @param {Element} widget - Progress widget
     */
    animateProgressBars(widget) {
        const progressBars = widget.querySelectorAll('.progress-bar');
        
        progressBars.forEach((bar, index) => {
            setTimeout(() => {
                const targetWidth = bar.dataset.progress || '0%';
                bar.style.width = targetWidth;
            }, index * 200);
        });
    }

    /**
     * Initialize weather widget
     */
    initializeWeatherWidget() {
        const weatherWidgets = document.querySelectorAll('.weather-widget');
        
        weatherWidgets.forEach(widget => {
            this.loadWeatherData(widget);
        });
    }

    /**
     * Load weather data (mock implementation)
     * @param {Element} widget - Weather widget
     */
    loadWeatherData(widget) {
        // Mock weather data - in real app, fetch from weather API
        const weatherData = {
            temperature: '28°C',
            description: 'Partly Cloudy',
            icon: 'fa-cloud-sun',
            humidity: '65%',
            windSpeed: '12 km/h'
        };
        
        this.updateWeatherWidget(widget, weatherData);
    }

    /**
     * Update weather widget
     * @param {Element} widget - Weather widget
     * @param {Object} data - Weather data
     */
    updateWeatherWidget(widget, data) {
        widget.innerHTML = `
            <div class="weather-icon">
                <i class="fas ${data.icon}"></i>
            </div>
            <div class="weather-temp">${data.temperature}</div>
            <div class="weather-desc">${data.description}</div>
            <div class="weather-details">
                <div>Humidity: ${data.humidity}</div>
                <div>Wind: ${data.windSpeed}</div>
            </div>
        `;
    }

    /**
     * Start notification refresh interval
     * @param {Element} widget - Notification widget
     */
    startNotificationRefresh(widget) {
        const refreshNotifications = () => {
            // In real app, fetch from API
            console.log('Refreshing notifications...');
        };
        
        // Refresh every 2 minutes
        const intervalId = setInterval(refreshNotifications, 120000);
        this.updateIntervals.set(widget, intervalId);
    }

    /**
     * Update notification count
     */
    updateNotificationCount() {
        const unreadItems = document.querySelectorAll('.notification-item.unread');
        const countElements = document.querySelectorAll('.notification-count, .badge');
        
        countElements.forEach(element => {
            element.textContent = unreadItems.length;
            
            if (unreadItems.length === 0) {
                element.style.display = 'none';
            } else {
                element.style.display = 'flex';
            }
        });
    }

    /**
     * Setup global event listeners
     */
    setupEventListeners() {
        // Handle widget refresh buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.widget-refresh, .widget-refresh *')) {
                const widget = e.target.closest('.widget-card');
                if (widget) {
                    this.refreshWidget(widget);
                }
            }
        });
        
        // Handle widget minimize/maximize
        document.addEventListener('click', (e) => {
            if (e.target.matches('.widget-toggle, .widget-toggle *')) {
                const widget = e.target.closest('.widget-card');
                if (widget) {
                    this.toggleWidget(widget);
                }
            }
        });
        
        // Handle calendar day clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('other-month')) {
                this.handleCalendarDayClick(e.target);
            }
        });
    }

    /**
     * Refresh a widget
     * @param {Element} widget - Widget element
     */
    refreshWidget(widget) {
        const refreshBtn = widget.querySelector('.widget-refresh');
        if (refreshBtn) {
            refreshBtn.classList.add('fa-spin');
            
            setTimeout(() => {
                refreshBtn.classList.remove('fa-spin');
                
                // Determine widget type and refresh accordingly
                if (widget.querySelector('.notification-list')) {
                    this.refreshNotificationWidget(widget);
                } else if (widget.querySelector('.calendar-grid')) {
                    this.refreshCalendarWidget(widget);
                } else if (widget.querySelector('.health-metrics')) {
                    this.refreshHealthWidget(widget);
                }
                
                console.log('Widget refreshed');
            }, 1000);
        }
    }

    /**
     * Refresh notification widget
     * @param {Element} widget - Widget element
     */
    refreshNotificationWidget(widget) {
        const notificationList = widget.querySelector('.notification-list');
        if (notificationList) {
            // Add loading state
            notificationList.style.opacity = '0.5';
            
            setTimeout(() => {
                notificationList.style.opacity = '1';
                this.updateNotificationCount();
            }, 500);
        }
    }

    /**
     * Refresh calendar widget
     * @param {Element} widget - Widget element
     */
    refreshCalendarWidget(widget) {
        this.renderCalendar(widget);
    }

    /**
     * Refresh health widget
     * @param {Element} widget - Widget element
     */
    refreshHealthWidget(widget) {
        const healthMetrics = widget.querySelector('.health-metrics');
        if (healthMetrics) {
            this.startHealthMonitoring(healthMetrics);
        }
    }

    /**
     * Toggle widget visibility
     * @param {Element} widget - Widget element
     */
    toggleWidget(widget) {
        const body = widget.querySelector('.widget-body');
        const toggleBtn = widget.querySelector('.widget-toggle');
        
        if (body && toggleBtn) {
            const isVisible = body.style.display !== 'none';
            
            body.style.display = isVisible ? 'none' : 'block';
            toggleBtn.innerHTML = isVisible ? 
                '<i class="fas fa-plus"></i>' : 
                '<i class="fas fa-minus"></i>';
            
            widget.classList.toggle('widget-minimized', isVisible);
        }
    }

    /**
     * Handle calendar day click
     * @param {Element} dayElement - Calendar day element
     */
    handleCalendarDayClick(dayElement) {
        // Remove previous selection
        const calendar = dayElement.closest('.calendar-grid');
        if (calendar) {
            calendar.querySelectorAll('.calendar-day.selected').forEach(day => {
                day.classList.remove('selected');
            });
        }
        
        // Add selection to clicked day
        dayElement.classList.add('selected');
        
        const date = dayElement.dataset.date;
        console.log('Calendar day selected:', date);
        
        // You can implement day detail view or event creation here
    }

    /**
     * Add new notification
     * @param {Object} notification - Notification data
     */
    addNotification(notification) {
        const notificationLists = document.querySelectorAll('.notification-list');
        
        notificationLists.forEach(list => {
            const notificationHTML = `
                <div class="notification-item unread">
                    <div class="notification-content">
                        <p>${notification.message}</p>
                        <small class="text-muted">${notification.time || 'Just now'}</small>
                    </div>
                </div>
            `;
            
            list.insertAdjacentHTML('afterbegin', notificationHTML);
            
            // Limit to 5 notifications
            const items = list.querySelectorAll('.notification-item');
            if (items.length > 5) {
                items[items.length - 1].remove();
            }
        });
        
        this.updateNotificationCount();
    }

    /**
     * Update widget data
     * @param {string} widgetType - Widget type
     * @param {Object} data - New data
     */
    updateWidgetData(widgetType, data) {
        switch (widgetType) {
            case 'notifications':
                if (data.newNotification) {
                    this.addNotification(data.newNotification);
                }
                break;
            case 'health':
                const healthWidgets = document.querySelectorAll('.health-metrics');
                healthWidgets.forEach(widget => {
                    this.updateHealthWidget(widget, data.metrics);
                });
                break;
            case 'weather':
                const weatherWidgets = document.querySelectorAll('.weather-widget');
                weatherWidgets.forEach(widget => {
                    this.updateWeatherWidget(widget, data);
                });
                break;
        }
    }

    /**
     * Destroy all widget intervals
     */
    destroy() {
        this.updateIntervals.forEach(intervalId => {
            clearInterval(intervalId);
        });
        this.updateIntervals.clear();
        this.widgets.clear();
        this.isInitialized = false;
        console.log('Dashboard Widgets destroyed');
    }
}

// Create global instance
window.DashboardWidgets = new DashboardWidgets();

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.DashboardWidgets.init();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardWidgets;
}