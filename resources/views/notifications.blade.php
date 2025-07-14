<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Component - CREAMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --supervisor-color: #4facfe;
            --teacher-color: #00c49a;
            --ajk-color: #f9a825;
            --success-color: #2ed573;
            --danger-color: #ff4757;
            --warning-color: #ffa502;
            --dark-color: #1a2a3a;
            --light-color: #ffffff;
            --text-color: #444444;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            padding: 20px;
            display: flex;
            justify-content: centre;
            align-items: flex-start;
            min-height: 100vh;
        }
        
        .header-example {
            width: 100%;
            max-width: 800px;
            background-color: var(--light-color);
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: centre;
            justify-content: flex-end;
        }
        
        .instructions {
            max-width: 800px;
            margin: 0 auto 30px;
            background-color: var(--light-color);
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        
        .instructions h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .instructions p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .instructions code {
            background: var(--light-bg);
            padding: 3px 5px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .notification-component {
            position: relative;
        }
        
        .notification-bell {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: centre;
            justify-content: centre;
            border-radius: 50%;
            background: var(--light-bg);
            cursor: pointer;
            transition: all var(--transition-speed) ease;
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
            background: var(--danger-color);
            color: var(--light-color);
            font-size: 11px;
            display: flex;
            align-items: centre;
            justify-content: centre;
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
            background: var(--light-color);
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            margin-top: 10px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all var(--transition-speed) ease;
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
            background: var(--light-color);
            transform: rotate(45deg);
            border-top-left-radius: 4px;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: centre;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .notification-header h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
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
            align-items: centre;
            justify-content: centre;
            background: var(--light-bg);
            border: none;
            color: #888;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .notification-action-btn:hover {
            background: rgba(50, 189, 234, 0.15);
            color: var(--primary-color);
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
            align-items: centre;
            gap: 10px;
        }
        
        .spinner {
            width: 30px;
            height: 30px;
            border: 3px solid rgba(50, 189, 234, 0.3);
            border-top-color: var(--primary-color);
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
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background var(--transition-speed) ease;
        }
        
        .notification-item:hover {
            background: var(--light-bg);
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: centre;
            justify-content: centre;
            margin-right: 15px;
            font-size: 16px;
            color: var(--light-color);
        }
        
        .notification-icon.primary {
            background: linear-gradient(135deg, var(--primary-color), #00d2ff);
        }
        
        .notification-icon.success {
            background: linear-gradient(135deg, var(--success-color), #00e676);
        }
        
        .notification-icon.info {
            background: linear-gradient(135deg, #00b0ff, #0091ea);
        }
        
        .notification-icon.warning {
            background: linear-gradient(135deg, var(--warning-color), #ffca28);
        }
        
        .notification-icon.danger {
            background: linear-gradient(135deg, var(--danger-color), #ff5252);
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
            color: var(--dark-color);
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
            align-items: centre;
            gap: 10px;
            text-align: centre;
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
            border-top: 1px solid var(--border-color);
            text-align: centre;
        }
        
        .view-all-link {
            color: var(--primary-color);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: centre;
            gap: 5px;
            transition: color var(--transition-speed) ease;
        }
        
        .view-all-link:hover {
            color: var(--secondary-color);
        }
        
        /* Demo styles */
        .notification-example {
            width: 100%;
            max-width: 800px;
            background-color: var(--light-color);
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-top: 30px;
        }

        .notification-example h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .notification-demo {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .notification-preview {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 15px;
        }
        
        .notification-preview h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .code-block {
            background: var(--dark-color);
            color: var(--light-color);
            padding: 15px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            font-size: 13px;
            margin-top: 15px;
        }
        
        /* Display for demo purposes */
        .show-dropdown {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
    </style>
</head>
<body>
    <div class="instructions">
        <h2>Notification Component for CREAMS</h2>
        <p>This is a standalone notification component for the CREAMS application. It provides a notification bell icon with a dropdown that displays user notifications. The component can be integrated into any header or navigation bar.</p>
        <p>To use this component, copy the HTML, CSS, and JavaScript code into your application. Make sure to include jQuery and Font Awesome for the icons.</p>
        <p>The component makes AJAX calls to the <code>notifications.unread</code> route to fetch unread notifications, and to <code>notifications.mark-all-read</code> to mark all notifications as read.</p>
    </div>
    
    <div class="header-example">
        <!-- Notification Component Start -->
        <div class="notification-component">
            <div class="notification-bell" id="notificationToggle">
                <i class="fas fa-bell"></i>
                <span class="notification-count" id="notificationCount">3</span>
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
                        <!-- Sample notifications (will be populated via JavaScript) -->
                        <div class="notification-item" data-id="1" data-url="/notifications/1">
                            <div class="notification-icon primary">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">New Message</div>
                                <div class="notification-message">You have received a new message from Dr. Nurul Hafizah</div>
                                <div class="notification-time">5 minutes ago</div>
                            </div>
                        </div>
                        
                        <div class="notification-item" data-id="2" data-url="/notifications/2">
                            <div class="notification-icon success">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Activity Reminder</div>
                                <div class="notification-message">Speech Therapy session starts in 30 minutes</div>
                                <div class="notification-time">30 minutes ago</div>
                            </div>
                        </div>
                        
                        <div class="notification-item" data-id="3" data-url="/notifications/3">
                            <div class="notification-icon warning">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">New Tainee Assignment</div>
                                <div class="notification-message">A new trainee has been assigned to your class</div>
                                <div class="notification-time">1 hour ago</div>
                            </div>
                        </div>
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
        <!-- Notification Component End -->
    </div>
    
    <div class="notification-example">
        <h2>How to Use This Component</h2>
        
        <div class="notification-demo">
            <div class="notification-preview">
                <h3>HTML Code</h3>
                <div class="code-block">
                    <pre>&lt;!-- Notification Component --&gt;
&lt;div class="notification-component"&gt;
    &lt;div class="notification-bell" id="notificationToggle"&gt;
        &lt;i class="fas fa-bell"&gt;&lt;/i&gt;
        &lt;span class="notification-count" id="notificationCount"&gt;0&lt;/span&gt;
    &lt;/div&gt;
    
    &lt;div class="notification-dropdown" id="notificationDropdown"&gt;
        &lt;div class="notification-header"&gt;
            &lt;h3&gt;Notifications&lt;/h3&gt;
            &lt;div class="notification-actions"&gt;
                &lt;button class="notification-action-btn refresh-btn" id="refreshNotifications" title="Refresh"&gt;
                    &lt;i class="fas fa-sync-alt"&gt;&lt;/i&gt;
                &lt;/button&gt;
                &lt;button class="notification-action-btn mark-all-btn" id="markAllReadBtn" title="Mark All as Read"&gt;
                    &lt;i class="fas fa-check-double"&gt;&lt;/i&gt;
                &lt;/button&gt;
            &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div class="notification-body"&gt;
            &lt;div class="loading-indicator"&gt;
                &lt;div class="spinner"&gt;&lt;/div&gt;
                &lt;span&gt;Loading notifications...&lt;/span&gt;
            &lt;/div&gt;
            
            &lt;div class="notification-list" id="notificationList"&gt;
                &lt;!-- Notifications will be populated here via JavaScript --&gt;
            &lt;/div&gt;
            
            &lt;div class="empty-notifications hidden" id="emptyNotifications"&gt;
                &lt;div class="empty-icon"&gt;
                    &lt;i class="fas fa-bell-slash"&gt;&lt;/i&gt;
                &lt;/div&gt;
                &lt;p&gt;No new notifications&lt;/p&gt;
            &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div class="notification-footer"&gt;
            &lt;a href="{{ route('notifications.index') }}" class="view-all-link"&gt;
                View All Notifications
                &lt;i class="fas fa-arrow-right"&gt;&lt;/i&gt;
            &lt;/a&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/div&gt;</pre>
                </div>
            </div>
            
            <div class="notification-preview">
                <h3>JavaScript Code</h3>
                <div class="code-block">
                    <pre>$(document).ready(function() {
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
                                &lt;div class="notification-item" data-id="${notification.id}" data-url="${notification.url}"&gt;
                                    &lt;div class="notification-icon ${notification.color}"&gt;
                                        &lt;i class="${notification.icon}"&gt;&lt;/i&gt;
                                    &lt;/div&gt;
                                    &lt;div class="notification-content"&gt;
                                        &lt;div class="notification-title"&gt;${notification.title}&lt;/div&gt;
                                        &lt;div class="notification-message"&gt;${notification.content}&lt;/div&gt;
                                        &lt;div class="notification-time"&gt;${notification.time}&lt;/div&gt;
                                    &lt;/div&gt;
                                &lt;/div&gt;
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
                    $('#notificationList').html('&lt;div class="error-message"&gt;Failed to load notifications&lt;/div&gt;');
                }
            },
            error: function(error) {
                $('.loading-indicator').hide();
                console.error('Error loading notifications:', error);
                $('#notificationList').html('&lt;div class="error-message"&gt;Failed to load notifications&lt;/div&gt;');
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
});</pre>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // For demo purposes, toggle dropdown on click
            $('#notificationToggle').click(function(e) {
                e.stopPropagation();
                $('#notificationDropdown').toggleClass('show');
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
            
            // Demo - Refresh button animation
            $('#refreshNotifications').click(function() {
                const $this = $(this);
                $this.find('i').addClass('fa-spin');
                
                setTimeout(function() {
                    $this.find('i').removeClass('fa-spin');
                }, 1000);
            });
            
            // Demo - Mark all as read button
            $('#markAllReadBtn').click(function() {
                $('#notificationCount').text('0');
                $('.notification-item').remove();
                $('#emptyNotifications').removeClass('hidden');
            });
        });
    </script>
</body>
</html>