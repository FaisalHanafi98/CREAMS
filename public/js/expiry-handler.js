/**
 * Expiry Handler - Automatic redirect system for session/auth expiry
 * CREAMS - Community-based REhAbilitation Management System
 */

class ExpiryHandler {
    constructor() {
        this.previousPage = this.getPreviousPage();
        this.fallbackPages = {
            admin: '/dashboard',
            supervisor: '/dashboard', 
            teacher: '/dashboard',
            ajk: '/dashboard',
            default: '/login'
        };
        this.sessionCheckInterval = null;
        this.warningShown = false;
        this.init();
    }

    init() {
        // Store current page as previous page for future use
        this.storePreviousPage();
        
        // Disable automatic session monitoring for now
        // this.startSessionMonitoring();
        
        // Handle AJAX errors globally
        this.setupAjaxErrorHandling();
        
        // Handle page visibility changes
        this.setupVisibilityChangeHandling();
        
        // Handle beforeunload to store current page
        this.setupBeforeUnloadHandling();
    }

    getPreviousPage() {
        return sessionStorage.getItem('creams_previous_page') || 
               localStorage.getItem('creams_previous_page') || 
               document.referrer || 
               null;
    }

    storePreviousPage() {
        const currentPage = window.location.pathname + window.location.search;
        const previousPage = sessionStorage.getItem('creams_current_page');
        
        if (previousPage && previousPage !== currentPage) {
            sessionStorage.setItem('creams_previous_page', previousPage);
            localStorage.setItem('creams_previous_page', previousPage);
        }
        
        sessionStorage.setItem('creams_current_page', currentPage);
    }

    startSessionMonitoring() {
        // Check session every 5 minutes (300 seconds) instead of 30 seconds
        this.sessionCheckInterval = setInterval(() => {
            this.checkSessionStatus();
        }, 300000);
    }

    async checkSessionStatus() {
        try {
            const response = await fetch('/api/session-check', {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                if (response.status === 401 || response.status === 419) {
                    this.handleSessionExpiry('Session expired');
                    return;
                }
                if (response.status === 403) {
                    this.handleAuthorizationError('Access denied');
                    return;
                }
            }

            const data = await response.json();
            
            if (!data.authenticated) {
                this.handleSessionExpiry('Authentication required');
                return;
            }

            // Handle session warning (5 minutes before expiry)
            if (data.expires_in && data.expires_in <= 300) {
                this.showSessionWarning(data.expires_in);
            }

        } catch (error) {
            console.warn('Session check failed:', error);
            // Don't redirect on network errors, might be temporary
        }
    }

    setupAjaxErrorHandling() {
        // jQuery AJAX error handling
        if (window.jQuery) {
            $(document).ajaxError((event, xhr, settings) => {
                this.handleAjaxError(xhr, settings);
            });
        }

        // Fetch API error handling
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                if (response.status === 401 || response.status === 419) {
                    this.handleSessionExpiry('Session expired during request');
                } else if (response.status === 403) {
                    this.handleAuthorizationError('Access denied during request');
                }
                return response;
            } catch (error) {
                throw error;
            }
        };
    }

    handleAjaxError(xhr, settings) {
        if (xhr.status === 401 || xhr.status === 419) {
            this.handleSessionExpiry('Session expired during request');
        } else if (xhr.status === 403) {
            this.handleAuthorizationError('Access denied during request');
        } else if (xhr.status === 500 && xhr.responseText.includes('session')) {
            this.handleSessionExpiry('Server session error');
        }
    }

    setupVisibilityChangeHandling() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Page became visible again, check session immediately
                setTimeout(() => this.checkSessionStatus(), 1000);
            }
        });
    }

    setupBeforeUnloadHandling() {
        window.addEventListener('beforeunload', () => {
            this.storePreviousPage();
        });
    }

    handleSessionExpiry(message = 'Session expired') {
        if (this.warningShown) return; // Prevent multiple redirects
        this.warningShown = true;
        
        console.log('Session expired, redirecting...', message);
        
        // Clear any running intervals
        if (this.sessionCheckInterval) {
            clearInterval(this.sessionCheckInterval);
        }

        // Store expiry info for post-login redirect
        this.storeExpiryInfo(message);
        
        // Show notification and redirect
        this.showExpiryNotification(message, () => {
            this.redirectAfterExpiry();
        });
    }

    handleAuthorizationError(message = 'Access denied') {
        console.log('Authorization error, redirecting...', message);
        
        this.showExpiryNotification(message, () => {
            this.redirectToPreviousPage() || this.redirectToFallback();
        });
    }

    storeExpiryInfo(message) {
        const expiryInfo = {
            message: message,
            timestamp: new Date().toISOString(),
            intendedPage: window.location.pathname + window.location.search,
            userRole: this.getCurrentUserRole()
        };
        
        sessionStorage.setItem('creams_expiry_info', JSON.stringify(expiryInfo));
        localStorage.setItem('creams_expiry_info', JSON.stringify(expiryInfo));
    }

    getCurrentUserRole() {
        // Try to get role from various sources
        return sessionStorage.getItem('user_role') ||
               localStorage.getItem('user_role') ||
               document.querySelector('meta[name="user-role"]')?.content ||
               'default';
    }

    showExpiryNotification(message, callback) {
        // Create modern notification
        const notification = this.createNotification(
            'Session Notice',
            message + '. You will be redirected back after re-authentication.',
            'warning'
        );

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            this.removeNotification(notification);
            callback();
        }, 5000);
    }

    showSessionWarning(expiresInSeconds) {
        if (this.warningShown) return;
        
        const minutes = Math.floor(expiresInSeconds / 60);
        const seconds = expiresInSeconds % 60;
        const timeString = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
        
        const notification = this.createNotification(
            'Session Warning',
            `Your session will expire in ${timeString}. Please save your work.`,
            'info',
            true // dismissible
        );

        // Auto dismiss after 10 seconds
        setTimeout(() => {
            this.removeNotification(notification);
        }, 10000);
    }

    createNotification(title, message, type = 'info', dismissible = false) {
        const notification = document.createElement('div');
        notification.className = `expiry-notification expiry-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    ${this.getNotificationIcon(type)}
                </div>
                <div class="notification-text">
                    <div class="notification-title">${title}</div>
                    <div class="notification-message">${message}</div>
                </div>
                ${dismissible ? '<button class="notification-dismiss">&times;</button>' : ''}
            </div>
        `;

        // Add dismiss functionality
        if (dismissible) {
            notification.querySelector('.notification-dismiss').addEventListener('click', () => {
                this.removeNotification(notification);
            });
        }

        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 100);
        
        return notification;
    }

    getNotificationIcon(type) {
        const icons = {
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>',
            error: '<i class="fas fa-times-circle"></i>',
            success: '<i class="fas fa-check-circle"></i>'
        };
        return icons[type] || icons.info;
    }

    removeNotification(notification) {
        notification.classList.add('hide');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    redirectAfterExpiry() {
        // Try to redirect to login with return URL
        const currentPage = encodeURIComponent(window.location.pathname + window.location.search);
        const loginUrl = `/login?return=${currentPage}`;
        
        window.location.href = loginUrl;
    }

    redirectToPreviousPage() {
        const previousPage = this.getPreviousPage();
        if (previousPage && previousPage !== window.location.pathname) {
            window.location.href = previousPage;
            return true;
        }
        return false;
    }

    redirectToFallback() {
        const userRole = this.getCurrentUserRole();
        const fallbackUrl = this.fallbackPages[userRole] || this.fallbackPages.default;
        window.location.href = fallbackUrl;
    }

    // Static method to handle post-login redirect
    static handlePostLoginRedirect() {
        const expiryInfo = sessionStorage.getItem('creams_expiry_info') || 
                          localStorage.getItem('creams_expiry_info');
        
        if (expiryInfo) {
            try {
                const info = JSON.parse(expiryInfo);
                
                // Clear expiry info
                sessionStorage.removeItem('creams_expiry_info');
                localStorage.removeItem('creams_expiry_info');
                
                // Show success message
                const notification = new ExpiryHandler().createNotification(
                    'Welcome Back',
                    'You have been successfully re-authenticated.',
                    'success'
                );
                
                setTimeout(() => {
                    new ExpiryHandler().removeNotification(notification);
                }, 3000);
                
                // Redirect to intended page after short delay
                setTimeout(() => {
                    if (info.intendedPage && info.intendedPage !== '/login') {
                        window.location.href = info.intendedPage;
                    }
                }, 2000);
                
            } catch (error) {
                console.error('Error handling post-login redirect:', error);
            }
        }
    }

    // Cleanup method
    destroy() {
        if (this.sessionCheckInterval) {
            clearInterval(this.sessionCheckInterval);
        }
    }
}

// Initialize expiry handler when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.expiryHandler = new ExpiryHandler();
    
    // Handle post-login redirect if needed
    ExpiryHandler.handlePostLoginRedirect();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExpiryHandler;
}