// ===========================
// CREAMS Global Notification Service
// ===========================

class NotificationService {
    constructor() {
        this.container = null;
        this.defaultDuration = 5000;
        this.maxNotifications = 5;
        this.init();
    }

    init() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            this.container.style.cssText = `
                position: fixed;
                top: 90px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
                pointer-events: none;
            `;
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('notification-container');
        }

        // Add CSS animations if not already present
        if (!document.getElementById('notification-styles')) {
            this.addStyles();
        }
    }

    addStyles() {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification-toast {
                pointer-events: auto;
                margin-bottom: 12px;
                border: none;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                backdrop-filter: blur(10px);
                animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                min-width: 320px;
                max-width: 400px;
            }

            .notification-toast.removing {
                animation: slideOutRight 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            }

            .notification-content {
                display: flex;
                align-items: flex-start;
                padding: 16px 20px;
                gap: 12px;
            }

            .notification-icon {
                font-size: 18px;
                margin-top: 2px;
                flex-shrink: 0;
            }

            .notification-body {
                flex: 1;
                min-width: 0;
            }

            .notification-title {
                font-weight: 600;
                margin: 0 0 4px 0;
                font-size: 14px;
                line-height: 1.3;
            }

            .notification-message {
                margin: 0;
                font-size: 13px;
                line-height: 1.4;
                opacity: 0.9;
            }

            .notification-close {
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
                opacity: 0.6;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: opacity 0.2s;
            }

            .notification-close:hover {
                opacity: 1;
            }

            .notification-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 0 0 12px 12px;
                animation: progress linear;
            }

            /* Type-specific styling */
            .notification-success {
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
            }

            .notification-error {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
            }

            .notification-warning {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
            }

            .notification-info {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
            }

            .notification-default {
                background: linear-gradient(135deg, #6b7280, #4b5563);
                color: white;
            }

            /* Animations */
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                }
            }

            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%) scale(0.9);
                }
            }

            @keyframes progress {
                from { width: 100%; }
                to { width: 0%; }
            }

            /* Responsive design */
            @media (max-width: 768px) {
                #notification-container {
                    top: 70px;
                    right: 15px;
                    left: 15px;
                    max-width: none;
                }

                .notification-toast {
                    min-width: auto;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(style);
    }

    show(message, type = 'info', options = {}) {
        const config = {
            title: options.title || this.getDefaultTitle(type),
            duration: options.duration || this.defaultDuration,
            persistent: options.persistent || false,
            actions: options.actions || null,
            ...options
        };

        // Limit number of notifications
        this.limitNotifications();

        // Create notification element
        const notification = this.createNotification(message, type, config);
        
        // Add to container
        this.container.appendChild(notification);

        // Auto remove if not persistent
        if (!config.persistent && config.duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, config.duration);
        }

        return notification;
    }

    createNotification(message, type, config) {
        const notification = document.createElement('div');
        notification.className = `notification-toast notification-${type} alert`;

        const iconMap = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
            default: 'fas fa-bell'
        };

        const icon = iconMap[type] || iconMap.default;

        notification.innerHTML = `
            <div class="notification-content">
                <i class="notification-icon ${icon}"></i>
                <div class="notification-body">
                    ${config.title ? `<div class="notification-title">${config.title}</div>` : ''}
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close" onclick="this.closest('.notification-toast').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${!config.persistent && config.duration > 0 ? 
                `<div class="notification-progress" style="animation-duration: ${config.duration}ms;"></div>` : ''
            }
        `;

        // Add click handler for close button
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.remove(notification));

        return notification;
    }

    remove(notification) {
        if (!notification || !notification.parentNode) return;

        notification.classList.add('removing');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    limitNotifications() {
        const notifications = this.container.querySelectorAll('.notification-toast');
        if (notifications.length >= this.maxNotifications) {
            // Remove oldest notification
            this.remove(notifications[0]);
        }
    }

    getDefaultTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error', 
            warning: 'Warning',
            info: 'Information',
            default: 'Notification'
        };
        return titles[type] || titles.default;
    }

    // Convenience methods
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    info(message, options = {}) {
        return this.show(message, 'info', options);
    }

    // Clear all notifications
    clear() {
        const notifications = this.container.querySelectorAll('.notification-toast');
        notifications.forEach(notification => this.remove(notification));
    }

    // Handle Laravel flash messages on page load
    handleFlashMessages() {
        // Check for Laravel session flash messages in meta tags or data attributes
        const flashData = document.querySelector('meta[name="flash-messages"]');
        if (flashData) {
            try {
                const messages = JSON.parse(flashData.content);
                Object.entries(messages).forEach(([type, message]) => {
                    this.show(message, type);
                });
            } catch (e) {
                console.warn('Failed to parse flash messages:', e);
            }
        }
    }
}

// Initialize global notification service
const NotificationSystem = new NotificationService();

// Global convenience functions for backward compatibility
function showNotification(message, type = 'info', options = {}) {
    return NotificationSystem.show(message, type, options);
}

function showSuccess(message, options = {}) {
    return NotificationSystem.success(message, options);
}

function showError(message, options = {}) {
    return NotificationSystem.error(message, options);
}

function showWarning(message, options = {}) {
    return NotificationSystem.warning(message, options);
}

function showInfo(message, options = {}) {
    return NotificationSystem.info(message, options);
}

// Handle flash messages on page load
document.addEventListener('DOMContentLoaded', function() {
    NotificationSystem.handleFlashMessages();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationService;
}

// Make available globally
window.NotificationSystem = NotificationSystem;
window.showNotification = showNotification;
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;