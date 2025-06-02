<!-- Notification Component -->
<div class="notification-component">
    <div class="notification-bell" id="notificationToggle">
        <i class="fas fa-bell"></i>
        <span class="notification-count" id="notificationCount">0</span>
    </div>
    
    <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
            <h3>Notifications</h3>
            <div class="notification-actions">
                <button class="notification-action-btn refresh-btn" id="refreshNotifications" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="notification-action-btn mark-all-btn" id="markAllReadBtn" title="Mark All as Read">
                    <i class="fas fa-check-double"></i>
                </button>
            </div>
        </div>
        
        <div class="notification-body">
            <div class="loading-indicator">
                <div class="spinner"></div>
                <span>Loading notifications...</span>
            </div>
            
            <div class="notification-list" id="notificationList">
                <!-- Notifications will be populated here via JavaScript -->
            </div>
            
            <div class="empty-notifications hidden" id="emptyNotifications">
                <div class="empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <p>No new notifications</p>
            </div>
        </div>
        
        <div class="notification-footer">
            <a href="{{ route('notifications.index') }}" class="view-all-link">
                View All Notifications
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Include CSS -->
<style>
    .notification-component {
        position: relative;
    }
    
    .notification-bell {
        position: relative;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--light-bg, #f8f9fa);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .notification-bell:hover {
        background: rgba(50, 189, 234, 0.1);
    }
    
    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--danger-color, #ff4757);
        color: #ffffff;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .notification-count:empty {
        display: none;
    }
    
    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: -10px;
        width: 350px;
        max-width: calc(100vw - 40px);
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        margin-top: 10px;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    
    .notification-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .notification-dropdown::before {
        content: '';
        position: absolute;
        top: -8px;
        right: 20px;
        width: 16px;
        height: 16px;
        background: #ffffff;
        transform: rotate(45deg);
        border-top-left-radius: 4px;
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color, #e9ecef);
    }
    
    .notification-header h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        color: var(--dark-color, #1a2a3a);
    }
    
    .notification-actions {
        display: flex;
        gap: 10px;
    }
    
    .notification-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--light-bg, #f8f9fa);
        border: none;
        color: #888;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .notification-action-btn:hover {
        background: rgba(50, 189, 234, 0.15);
        color: var(--primary-color, #32bdea);
    }
    
    .notification-body {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .notification-body::-webkit-scrollbar {
        width: 5px;
    }
    
    .notification-body::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }
    
    .notification-body::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 10px;
    }
    
    .loading-indicator {
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .spinner {
        width: 30px;
        height: 30px;
        border: 3px solid rgba(50, 189, 234, 0.3);
        border-top-color: var(--primary-color, #32bdea);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
    
    .loading-indicator span {
        font-size: 14px;
        color: #888;
    }
    
    .notification-list {
        display: flex;
        flex-direction: column;
    }
    
    .notification-item {
        display: flex;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color, #e9ecef);
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .notification-item:hover {
        background: var(--light-bg, #f8f9fa);
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 16px;
        color: #ffffff;
    }
    
    .notification-icon.primary {
        background: linear-gradient(135deg, var(--primary-color, #32bdea), #00d2ff);
    }
    
    .notification-icon.success {
        background: linear-gradient(135deg, var(--success-color, #2ed573), #00e676);
    }
    
    .notification-icon.info {
        background: linear-gradient(135deg, #00b0ff, #0091ea);
    }
    
    .notification-icon.warning {
        background: linear-gradient(135deg, var(--warning-color, #ffa502), #ffca28);
    }
    
    .notification-icon.danger {
        background: linear-gradient(135deg, var(--danger-color, #ff4757), #ff5252);
    }
    
    .notification-icon.secondary {
        background: linear-gradient(135deg, #9e9e9e, #757575);
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--dark-color, #1a2a3a);
        margin-bottom: 5px;
    }
    
    .notification-message {
        font-size: 13px;
        color: #777;
        margin-bottom: 5px;
    }
    
    .notification-time {
        font-size: 12px;
        color: #888;
    }
    
    .empty-notifications {
        padding: 30px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        text-align: center;
    }
    
    .empty-notifications .empty-icon {
        font-size: 40px;
        color: #e0e0e0;
    }
    
    .empty-notifications p {
        font-size: 14px;
        color: #888;
    }
    
    .hidden {
        display: none;
    }
    
    .notification-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color, #e9ecef);
        text-align: center;
    }
    
    .view-all-link {
        color: var(--primary-color, #32bdea);
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.3s ease;
    }
    
    .view-all-link:hover {
        color: var(--secondary-color, #c850c0);
    }
</style>

<!-- Include JavaScript -->
<script>
$(document).ready(function() {
    // Toggle notification dropdown
    $('#notificationToggle').click(function(e) {
        e.stopPropagation();
        $('#notificationDropdown').toggleClass('show');
        
        // Load notifications when opening the dropdown
        if ($('#notificationDropdown').hasClass('show')) {
            loadNotifications();
        }
    });
    
    // Close dropdown when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.notification-component').length) {
            $('#notificationDropdown').removeClass('show');
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    $('#notificationDropdown').click(function(e) {
        e.stopPropagation();
    });
    
    // Load notifications function
    function loadNotifications() {
        $('.loading-indicator').show();
        $('#notificationList').empty();
        $('#emptyNotifications').addClass('hidden');
        
        $.ajax({
            url: '{{ route("notifications.unread") }}',
            type: 'GET',
            success: function(response) {
                $('.loading-indicator').hide();
                
                if (response.success) {
                    // Update notification count
                    $('#notificationCount').text(response.count);
                    
                    if (response.count > 0) {
                        // Populate notifications
                        $.each(response.notifications, function(index, notification) {
                            const notificationItem = `
                                <div class="notification-item" data-id="${notification.id}" data-url="${notification.url}">
                                    <div class="notification-icon ${notification.color}">
                                        <i class="${notification.icon}"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title">${notification.title}</div>
                                        <div class="notification-message">${notification.content}</div>
                                        <div class="notification-time">${notification.time}</div>
                                    </div>
                                </div>
                            `;
                            
                            $('#notificationList').append(notificationItem);
                        });
                        
                        // Add click event to notification items
                        $('.notification-item').click(function() {
                            const url = $(this).data('url');
                            window.location.href = url;
                        });
                    } else {
                        // Show empty state
                        $('#emptyNotifications').removeClass('hidden');
                    }
                } else {
                    console.error('Error loading notifications:', response.message);
                    $('#notificationList').html('<div class="error-message">Failed to load notifications</div>');
                }
            },
            error: function(error) {
                $('.loading-indicator').hide();
                console.error('Error loading notifications:', error);
                $('#notificationList').html('<div class="error-message">Failed to load notifications</div>');
            }
        });
    }
    
    // Refresh notifications
    $('#refreshNotifications').click(function() {
        loadNotifications();
    });
    
    // Mark all as read
    $('#markAllReadBtn').click(function() {
        $.ajax({
            url: '{{ route("notifications.mark-all-read") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                loadNotifications();
            },
            error: function(error) {
                console.error('Error marking notifications as read:', error);
            }
        });
    });
    
    // Initial load of notification count
    function loadInitialCount() {
        $.ajax({
            url: '{{ route("notifications.unread") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#notificationCount').text(response.count);
                }
            }
        });
    }
    
    // Load initial notification count
    loadInitialCount();
    
    // Refresh notification count periodically
    setInterval(loadInitialCount, 60000); // Refresh every minute
});
</script>