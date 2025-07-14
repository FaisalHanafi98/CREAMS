// ===========================
// CREAMS Dashboard JS
// ===========================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dashboard components
    initializeAnimations();
    initializeCalendar();
    initializeNotifications();
    initializeCharts();
    initializeQuickActions();
    initializeAutoRefresh();
    initializeTimeDisplay();
});

// Animations
function initializeAnimations() {
    // Animate stats cards on load
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animate content cards
    const contentCards = document.querySelectorAll('.content-card, .widget-card');
    contentCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateX(0)';
        }, (statCards.length * 100) + (index * 100));
    });
}

// Calendar Widget
function initializeCalendar() {
    const calendarContainer = document.querySelector('.calendar-widget');
    if (!calendarContainer) return;
    
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
        
        let calendarHTML = `
            <div class="calendar-header">
                <div class="calendar-month">${monthNames[currentMonth]} ${currentYear}</div>
                <div class="calendar-nav">
                    <button onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="calendar-grid">
        `;
        
        // Day headers
        const dayHeaders = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        dayHeaders.forEach(day => {
            calendarHTML += `<div class="calendar-day-header">${day}</div>`;
        });
        
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            calendarHTML += '<div class="calendar-day empty"></div>';
        }
        
        // Days of month
        for (let day = 1; day <= daysInMonth; day++) {
            const isToday = day === currentDate.getDate() && 
                           currentMonth === currentDate.getMonth() && 
                           currentYear === currentDate.getFullYear();
            
            const hasEvent = Math.random() > 0.8; // Simulate events
            
            calendarHTML += `
                <div class="calendar-day ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : ''}" 
                     data-date="${currentYear}-${currentMonth + 1}-${day}">
                    ${day}
                </div>
            `;
        }
        
        calendarHTML += '</div>';
        calendarContainer.innerHTML = calendarHTML;
        
        // Add click handlers
        document.querySelectorAll('.calendar-day:not(.empty)').forEach(day => {
            day.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                showDayEvents(date);
            });
        });
    }
    
    // Make changeMonth function global
    window.changeMonth = function(direction) {
        currentMonth += direction;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        } else if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    };
    
    // Initialize calendar
    renderCalendar();
}

// Show events for a specific day
function showDayEvents(date) {
    // This would typically fetch events from the server
    console.log('Showing events for:', date);
    // You can implement a modal or redirect to events page
}

// Notifications
function initializeNotifications() {
    // Mark notification as read when clicked
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('unread');
            updateNotificationCount();
        });
    });
    
    // Update notification count
    updateNotificationCount();
}

// Update notification badge count
function updateNotificationCount() {
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
    const badge = document.querySelector('.widget-header .badge');
    
    if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
    }
}

// Charts (if using Chart.js)
function initializeCharts() {
    // Activity distribution chart
    const chartCanvas = document.getElementById('activityChart');
    if (chartCanvas && typeof Chart !== 'undefined') {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Physical Therapy', 'Speech Therapy', 'Academic', 'Other'],
                datasets: [{
                    data: [30, 25, 35, 10],
                    backgroundColor: [
                        '#32bdea',
                        '#c850c0',
                        '#28a745',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
    }
}

// Quick Actions
function initializeQuickActions() {
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    
    quickActionBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.05)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-3px) scale(1)';
        });
    });
}

// Auto-refresh dashboard data
function initializeAutoRefresh() {
    // Refresh stats every 5 minutes
    setInterval(() => {
        refreshDashboardStats();
    }, 300000);
}

// Refresh dashboard statistics
function refreshDashboardStats() {
    // Simulate fetching new data
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        const currentValue = parseInt(stat.textContent);
        const change = Math.floor(Math.random() * 5) - 2; // Random change -2 to +2
        const newValue = Math.max(0, currentValue + change);
        
        // Animate the change
        animateValue(stat, currentValue, newValue, 1000);
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
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.round(current);
        }
    }, 16);
}

// Display current time
function initializeTimeDisplay() {
    const updateTime = () => {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        const dateString = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Update time display if element exists
        const timeDisplay = document.getElementById('currentTime');
        if (timeDisplay) {
            timeDisplay.textContent = `${dateString} - ${timeString}`;
        }
    };
    
    // Update immediately and then every second
    updateTime();
    setInterval(updateTime, 1000);
}

// Session countdown timer
function initializeSessionTimers() {
    const sessionItems = document.querySelectorAll('.session-item');
    
    sessionItems.forEach(item => {
        const timeElement = item.querySelector('.session-time');
        if (!timeElement) return;
        
        const sessionTime = timeElement.textContent;
        const [hours, minutes] = sessionTime.match(/(\d+):(\d+)/).slice(1);
        const sessionDate = new Date();
        sessionDate.setHours(parseInt(hours), parseInt(minutes), 0);
        
        // Check if session is upcoming
        if (sessionDate > new Date()) {
            const updateCountdown = () => {
                const now = new Date();
                const diff = sessionDate - now;
                
                if (diff > 0) {
                    const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
                    const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    
                    const countdownElement = item.querySelector('.countdown');
                    if (countdownElement) {
                        countdownElement.textContent = `Starts in ${hoursLeft}h ${minutesLeft}m`;
                    }
                }
            };
            
            // Add countdown element
            const countdownSpan = document.createElement('span');
            countdownSpan.className = 'countdown text-warning ml-2';
            timeElement.appendChild(countdownSpan);
            
            // Update countdown
            updateCountdown();
            setInterval(updateCountdown, 60000); // Update every minute
        }
    });
}

// Initialize session timers
document.addEventListener('DOMContentLoaded', initializeSessionTimers);

// Export functions for use in other scripts
window.DashboardModule = {
    refreshDashboardStats,
    updateNotificationCount,
    showDayEvents,
    animateValue
};