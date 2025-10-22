{{--
    Modern Toast Notification System
    Usage: @include('components.toast-notifications')

    This component shows toast notifications in the top-right corner
    Automatically dismisses after 5 seconds
    Supports: success, error, warning, info
--}}

{{-- Toast Container --}}
<div id="toast-container" class="toast-notification-container"></div>

<script>
/**
 * Toast Notification System
 * Shows notifications in top-right corner with auto-dismiss
 */
window.ToastNotification = {
    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} type - Type of notification (success, error, warning, info)
     * @param {number} duration - Duration in milliseconds (default: 5000)
     */
    show: function(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;

        // Get icon based on type
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${icons[type] || icons.info}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${titles[type] || titles.info}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="ToastNotification.dismiss(this)">
                <i class="fas fa-times"></i>
            </button>
            <div class="toast-progress"></div>
        `;

        // Add to container
        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Start progress bar animation
        const progressBar = toast.querySelector('.toast-progress');
        progressBar.style.animation = `toast-progress ${duration}ms linear`;

        // Auto dismiss
        setTimeout(() => {
            this.dismiss(toast);
        }, duration);
    },

    /**
     * Dismiss a toast notification
     * @param {Element} element - Toast element or close button
     */
    dismiss: function(element) {
        const toast = element.classList && element.classList.contains('toast-notification')
            ? element
            : element.closest('.toast-notification');

        if (toast) {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 300);
        }
    },

    /**
     * Show success notification
     */
    success: function(message, duration) {
        this.show(message, 'success', duration);
    },

    /**
     * Show error notification
     */
    error: function(message, duration) {
        this.show(message, 'error', duration);
    },

    /**
     * Show warning notification
     */
    warning: function(message, duration) {
        this.show(message, 'warning', duration);
    },

    /**
     * Show info notification
     */
    info: function(message, duration) {
        this.show(message, 'info', duration);
    }
};

// Show session flash messages as toasts on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        ToastNotification.success('{{ session('success') }}', 10000);
    @endif

    @if(session('error'))
        ToastNotification.error('{{ session('error') }}');
    @endif

    @if(session('fail'))
        ToastNotification.error('{{ session('fail') }}');
    @endif

    @if(session('warning'))
        ToastNotification.warning('{{ session('warning') }}');
    @endif

    @if(session('info'))
        ToastNotification.info('{{ session('info') }}');
    @endif

    @if($errors->any())
        @if($errors->count() === 1)
            ToastNotification.error('{{ $errors->first() }}', 6000);
        @else
            const errors = @json($errors->all());
            const errorMessage = errors.join('<br>');
            ToastNotification.error(errorMessage, 8000);
        @endif
    @endif
});
</script>

<style>
/* Toast Container */
.toast-notification-container {
    position: fixed;
    top: 80px; /* Below topbar */
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    pointer-events: none;
}

/* Toast Notification */
.toast-notification {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    min-width: 320px;
    max-width: 400px;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    pointer-events: all;
    position: relative;
    overflow: hidden;
}

.toast-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-notification.hide {
    opacity: 0;
    transform: translateX(400px);
}

/* Toast Icon */
.toast-icon {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 14px;
}

.toast-success .toast-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.toast-error .toast-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.toast-warning .toast-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.toast-info .toast-icon {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

/* Toast Content */
.toast-content {
    flex: 1;
    min-width: 0;
}

.toast-title {
    font-weight: 600;
    font-size: 13px;
    color: #2c3e50;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.toast-message {
    font-size: 14px;
    color: #495057;
    line-height: 1.4;
    word-wrap: break-word;
}

/* Toast Close Button */
.toast-close {
    flex-shrink: 0;
    background: none;
    border: none;
    color: rgba(0, 0, 0, 0.3);
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 12px;
}

.toast-close:hover {
    background: rgba(0, 0, 0, 0.05);
    color: rgba(0, 0, 0, 0.6);
}

/* Toast Progress Bar */
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 100%;
    transform-origin: left;
}

.toast-success .toast-progress {
    background: linear-gradient(90deg, #28a745, #20c997);
}

.toast-error .toast-progress {
    background: linear-gradient(90deg, #dc3545, #e63946);
}

.toast-warning .toast-progress {
    background: linear-gradient(90deg, #ffc107, #ffb300);
}

.toast-info .toast-progress {
    background: linear-gradient(90deg, #17a2b8, #00acc1);
}

@keyframes toast-progress {
    from {
        transform: scaleX(1);
    }
    to {
        transform: scaleX(0);
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .toast-notification-container {
        top: 70px;
        right: 10px;
        left: 10px;
        max-width: none;
    }

    .toast-notification {
        min-width: auto;
        max-width: none;
        width: 100%;
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    .toast-notification {
        background: #2c3e50;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .toast-title {
        color: #ecf0f1;
    }

    .toast-message {
        color: #bdc3c7;
    }

    .toast-close {
        color: rgba(255, 255, 255, 0.5);
    }

    .toast-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.8);
    }
}
</style>
