# CREAMS Messages & Notifications Module Summary

## Overview
The Messages and Notifications module provides comprehensive internal communication and alerting capabilities within the CREAMS system. It enables staff members to send messages to each other across roles and receive automated notifications about system events, activity updates, and important reminders with real-time delivery and read status tracking.

## Controllers

### 1. MessageController.php
**Purpose**: Complete internal messaging system with role-based communication and conversation management

**Key Methods**:
- `index()`: Message inbox and sent items with pagination and unread count
- `create()`: Message composition form with recipient selection by role
- `store()`: Send new message with notification creation and validation
- `show()`: Display message details with conversation history and read marking
- `reply()`: Reply to message with context preservation and authorization
- `markAsRead()`: Mark individual message as read with timestamp tracking
- `markAllAsRead()`: Mark all user messages as read in bulk operation
- `destroy()`: Delete message with authorization checking

**Key Features**:
- **Role-based Messaging**: Send messages between admin, supervisor, teacher, and AJK roles
- **Conversation Threading**: Track conversation history between users
- **Read Status Management**: Track read/unread status with timestamps
- **Authorization Control**: Ensure users can only access their own messages
- **Notification Integration**: Automatic notification creation for new messages
- **Bulk Operations**: Mark all messages as read or delete multiple messages
- **Pagination Support**: Efficient pagination for inbox and sent items
- **Comprehensive Logging**: Detailed logging for all message operations

### 2. NotificationController.php
**Purpose**: System notification management with real-time alerts and status tracking

**Key Methods**:
- `index()`: Notification listing with pagination and unread count
- `show()`: Display notification details with automatic read marking
- `markAsRead()`: Mark individual notification as read
- `markAllAsRead()`: Mark all notifications as read (supports AJAX)
- `destroy()`: Delete individual notification
- `clearRead()`: Delete all read notifications in bulk
- `getUnread()`: Get unread notifications for AJAX requests

**Key Features**:
- **Real-time Notifications**: AJAX-based notification delivery and updates
- **Type-based Categorization**: Different notification types with icons and colors
- **Priority System**: Priority levels (low, medium, high, urgent) with visual indicators
- **Bulk Operations**: Mark all as read or clear read notifications
- **AJAX Integration**: JSON API endpoints for frontend integration
- **Authorization Security**: Role-based access control for all operations
- **Pagination Support**: Efficient notification listing with pagination
- **Auto-read Marking**: Automatic read status when viewing notifications

## Models

### 1. Messages.php
**Purpose**: Core messaging model with polymorphic relationships and conversation management

**Key Properties**:
- `sender_id`: Sender user identifier
- `sender_type`: Sender role type (admin, supervisor, teacher, ajk)
- `recipient_id`: Recipient user identifier
- `recipient_type`: Recipient role type
- `subject`: Message subject line
- `content`: Message body content
- `read`: Read status (boolean)
- `read_at`: Timestamp when message was read

**Key Features**:
- **Polymorphic Relationships**: Support for different user role types
- **Read Status Tracking**: Comprehensive read/unread status management
- **Name Resolution**: Automatic sender and recipient name resolution
- **Conversation Support**: Track message threads between users
- **Timestamp Management**: Proper datetime casting and timezone handling

**Key Relationships**:
- `sender()`: Polymorphic relationship to sender user
- `recipient()`: Polymorphic relationship to recipient user

**Key Scopes & Methods**:
- `unread()`: Filter unread messages
- `markAsRead()`: Mark message as read with timestamp
- `isRead()`: Check if message is read
- `getSenderNameAttribute()`: Get sender name from role-specific model
- `getRecipientNameAttribute()`: Get recipient name from role-specific model

### 2. Notifications.php
**Purpose**: System notification model with type categorization and user targeting

**Key Properties**:
- `user_id`: Target user identifier
- `user_type`: Target user role type
- `type`: Notification type (message, activity, trainee, asset, system)
- `title`: Notification title
- `content`: Notification content
- `data`: Additional JSON data
- `read`: Read status (boolean)
- `read_at`: Timestamp when notification was read

**Key Features**:
- **Type System**: Comprehensive notification type categorization
- **JSON Data Storage**: Flexible additional data storage
- **Icon and Color Mapping**: Automatic icon and color assignment by type
- **Polymorphic User Relations**: Support for different user role types
- **Read Status Management**: Track read/unread status with timestamps

**Key Relationships**:
- `user()`: Polymorphic relationship to target user

**Key Scopes & Methods**:
- `unread()`: Filter unread notifications
- `markAsRead()`: Mark notification as read
- `isRead()`: Check if notification is read
- `getUserNameAttribute()`: Get user name from role-specific model
- `getIconAttribute()`: Get notification icon based on type
- `getColorAttribute()`: Get notification color based on type

### 3. Notification.php (Enhanced)
**Purpose**: Advanced notification model with comprehensive type system and priority management

**Key Properties**:
- `user_id`: Target user identifier
- `role`: User role
- `title`: Notification title
- `content`: Notification content
- `type`: Detailed notification type (activity_scheduled, trainee_enrolled, etc.)
- `data`: JSON data with action URLs and additional information
- `read`: Read status
- `read_at`: Read timestamp

**Notification Types**:
- **Activity Types**: `activity_scheduled`, `activity_cancelled`, `activity_rescheduled`, `low_enrollment`, `session_reminder`, `attendance_missing`
- **Trainee Types**: `trainee_enrolled`, `trainee_withdrawn`, `trainee_profile_updated`, `progress_report_due`, `birthday_reminder`
- **Staff Types**: `new_staff_member`, `profile_update_request`, `leave_request`
- **System Types**: `asset_assigned`, `schedule_conflict`, `system_announcement`

**Priority Levels**:
- `PRIORITY_LOW`: Low priority notifications
- `PRIORITY_MEDIUM`: Medium priority notifications
- `PRIORITY_HIGH`: High priority notifications
- `PRIORITY_URGENT`: Urgent notifications requiring immediate attention

**Key Features**:
- **Comprehensive Type System**: 20+ predefined notification types
- **Priority Management**: Four-level priority system with visual indicators
- **Icon Mapping**: Detailed icon mapping for each notification type
- **Action URLs**: Support for clickable notifications with action links
- **Time Formatting**: Human-readable time ago formatting
- **Badge System**: Priority badge classes for UI display

## Database Schema

### messages table
```sql
- id (auto-increment primary key)
- sender_id (unsigned big integer) - Sender user ID
- recipient_id (unsigned big integer) - Recipient user ID
- message_subject (string) - Message subject
- message_body (text) - Message content
- is_read (boolean, default: false) - Read status
- read_at (timestamp, nullable) - Read timestamp
- attachments (json, nullable) - File attachments
- message_priority (enum: low, normal, high, default: normal) - Message priority
- timestamps
```

**Indexes**:
- `sender_id` (for sender-based queries)
- `recipient_id` (for recipient-based queries)
- `is_read` (for read status filtering)
- `message_priority` (for priority-based filtering)

### notifications table
```sql
- id (auto-increment primary key)
- notification_title (string) - Notification title
- notification_message (text) - Notification content
- notification_type (enum: info, warning, success, error, default: info) - Type
- user_id (unsigned big integer) - Target user ID
- user_type (string, default: user) - Target user type
- is_read (boolean, default: false) - Read status
- read_at (timestamp, nullable) - Read timestamp
- notification_data (json, nullable) - Additional data
- timestamps
```

**Indexes**:
- `user_id` (for user-based queries)
- `user_type` (for user type filtering)
- `is_read` (for read status filtering)
- `notification_type` (for type-based queries)

## Views

### 1. messages/index.blade.php
**Purpose**: Main messaging interface with inbox and sent items
**Features**:
- **Dual-tab Interface**: Separate tabs for inbox and sent messages
- **Message Listing**: Paginated message list with sender/recipient information
- **Read Status Indicators**: Visual indicators for read/unread messages
- **Unread Counter**: Real-time unread message counter
- **Quick Actions**: Reply, mark as read, delete actions
- **Search and Filter**: Search messages by subject or sender
- **Responsive Design**: Mobile-friendly responsive layout

### 2. messages/show.blade.php
**Purpose**: Detailed message view with conversation history
**Features**:
- **Message Details**: Complete message content display
- **Conversation Thread**: Full conversation history between users
- **Read Status Management**: Automatic read marking when viewing
- **Reply Interface**: Quick reply functionality
- **Action Buttons**: Mark as read, delete, reply actions
- **Sender Information**: Complete sender details and role

### 3. messages/create.blade.php (inferred)
**Purpose**: Message composition interface
**Features**:
- **Recipient Selection**: Role-based recipient selection
- **Rich Text Editor**: Advanced text editor for message content
- **Subject Line**: Message subject input
- **Priority Selection**: Message priority assignment
- **Draft Saving**: Save messages as drafts
- **Attachment Support**: File attachment capabilities

### 4. notifications/index.blade.php
**Purpose**: Notification management interface
**Features**:
- **Notification Listing**: Paginated notification display
- **Type-based Icons**: Visual icons for different notification types
- **Priority Indicators**: Priority-based color coding
- **Bulk Actions**: Mark all as read, clear read notifications
- **Filter Options**: Filter by type, priority, read status
- **Time Display**: Human-readable time stamps
- **Action URLs**: Clickable notifications with action links

### 5. notifications/show.blade.php
**Purpose**: Detailed notification view
**Features**:
- **Full Content Display**: Complete notification content
- **Action Links**: Clickable action buttons
- **Related Data**: Display related information from JSON data
- **Mark as Read**: Automatic read marking
- **Navigation**: Return to notification list

## Routes

### Web Routes (in routes/web.php)
```php
// Messages Management
Route::middleware(['auth'])->prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('/create', [MessageController::class, 'create'])->name('create');
    Route::post('/', [MessageController::class, 'store'])->name('store');
    Route::get('/{id}', [MessageController::class, 'show'])->name('show');
    Route::get('/{id}/reply', [MessageController::class, 'reply'])->name('reply');
    Route::patch('/{id}/read', [MessageController::class, 'markAsRead'])->name('markAsRead');
    Route::patch('/mark-all-read', [MessageController::class, 'markAllAsRead'])->name('markAllAsRead');
    Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
});

// Notifications Management
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/clear-read', [NotificationController::class, 'clearRead'])->name('clearRead');
    Route::get('/api/unread', [NotificationController::class, 'getUnread'])->name('getUnread');
});

// Role-based Notification Access
Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
});

Route::group(['middleware' => ['role:supervisor']], function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('supervisor.notifications');
});

Route::group(['middleware' => ['role:teacher']], function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('teacher.notifications');
});

Route::group(['middleware' => ['role:ajk']], function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('ajk.notifications');
});
```

## Key Features

### 1. Comprehensive Messaging System
- **Role-based Communication**: Messages between all staff roles (admin, supervisor, teacher, AJK)
- **Conversation Threading**: Track complete conversation history between users
- **Read Status Tracking**: Real-time read/unread status with timestamps
- **Message Priority**: Low, normal, high priority levels for important communications
- **Attachment Support**: File attachment capabilities for document sharing

### 2. Advanced Notification System
- **Real-time Delivery**: Instant notification delivery with AJAX updates
- **Type Categorization**: 20+ notification types for different system events
- **Priority Management**: Four-level priority system (low, medium, high, urgent)
- **Action Integration**: Clickable notifications with action URLs
- **Auto-generation**: Automatic notification creation for system events

### 3. User Experience Features
- **Unread Counters**: Real-time unread message and notification counters
- **Bulk Operations**: Mark all as read, delete multiple items
- **Search and Filter**: Advanced search and filtering capabilities
- **Mobile Responsive**: Mobile-optimized interface design
- **Visual Indicators**: Icons, colors, and badges for quick identification

### 4. Integration Capabilities
- **Activity Integration**: Notifications for activity scheduling, enrollment, attendance
- **Trainee Integration**: Notifications for trainee enrollment, profile updates, birthdays
- **Asset Integration**: Notifications for asset assignments and maintenance
- **System Integration**: System announcements and administrative alerts

## Security Features

### 1. Authentication & Authorization
- **Session Validation**: Comprehensive session checking for all operations
- **Role-based Access**: Users can only access their own messages and notifications
- **Permission Validation**: Verify user permissions for each operation
- **Cross-role Security**: Prevent unauthorized access to other users' data

### 2. Data Protection
- **Input Validation**: Comprehensive validation for all message and notification data
- **XSS Prevention**: Proper output escaping and content sanitization
- **SQL Injection Prevention**: Parameterized queries and ORM usage
- **Data Integrity**: Ensure data consistency across operations

### 3. Audit & Logging
- **Operation Logging**: Log all message and notification operations
- **Access Logging**: Track all access attempts and authorization failures
- **Error Logging**: Comprehensive error logging for troubleshooting
- **Activity Tracking**: Track user activity for audit purposes

## API Integration

### 1. AJAX Endpoints
```javascript
// Get unread notifications
GET /notifications/api/unread
Response: {
    success: true,
    count: 5,
    notifications: [
        {
            id: 1,
            title: "New Message",
            message: "You have received a new message",
            icon: "fas fa-envelope",
            priority_color: "text-info",
            time_ago: "2 minutes ago",
            action_url: "/messages/1"
        }
    ]
}

// Mark all notifications as read
PATCH /notifications/mark-all-read
Response: {
    success: true,
    message: "5 notifications marked as read",
    count: 5
}
```

### 2. Real-time Updates
```javascript
// Auto-refresh notifications every 30 seconds
setInterval(function() {
    fetch('/notifications/api/unread')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
            updateNotificationDropdown(data.notifications);
        });
}, 30000);
```

## Statistical Calculations

### 1. Message Statistics
```php
// Get message statistics for user
$stats = [
    'inbox_count' => Messages::where('recipient_id', $userId)
        ->where('recipient_type', $role)->count(),
    'sent_count' => Messages::where('sender_id', $userId)
        ->where('sender_type', $role)->count(),
    'unread_count' => Messages::where('recipient_id', $userId)
        ->where('recipient_type', $role)
        ->where('read', false)->count(),
    'today_received' => Messages::where('recipient_id', $userId)
        ->where('recipient_type', $role)
        ->whereDate('created_at', today())->count()
];
```

### 2. Notification Statistics
```php
// Get notification statistics
$notificationStats = [
    'total' => Notification::where('user_id', $userId)->count(),
    'unread' => Notification::where('user_id', $userId)
        ->where('read', false)->count(),
    'today' => Notification::where('user_id', $userId)
        ->whereDate('created_at', today())->count(),
    'by_type' => Notification::where('user_id', $userId)
        ->select('type', DB::raw('count(*) as count'))
        ->groupBy('type')->get()
];
```

## Notification Type System

### 1. Activity Notifications
```php
// Activity-related notification types
const TYPE_ACTIVITY_SCHEDULED = 'activity_scheduled';
const TYPE_ACTIVITY_CANCELLED = 'activity_cancelled';
const TYPE_ACTIVITY_RESCHEDULED = 'activity_rescheduled';
const TYPE_LOW_ENROLLMENT = 'low_enrollment';
const TYPE_SESSION_REMINDER = 'session_reminder';
const TYPE_ATTENDANCE_MISSING = 'attendance_missing';
```

### 2. Trainee Notifications
```php
// Trainee-related notification types
const TYPE_TRAINEE_ENROLLED = 'trainee_enrolled';
const TYPE_TRAINEE_WITHDRAWN = 'trainee_withdrawn';
const TYPE_TRAINEE_PROFILE_UPDATED = 'trainee_profile_updated';
const TYPE_PROGRESS_REPORT_DUE = 'progress_report_due';
const TYPE_BIRTHDAY_REMINDER = 'birthday_reminder';
```

### 3. System Notifications
```php
// System-related notification types
const TYPE_SYSTEM_ANNOUNCEMENT = 'system_announcement';
const TYPE_SCHEDULE_CONFLICT = 'schedule_conflict';
const TYPE_ASSET_ASSIGNED = 'asset_assigned';
const TYPE_NEW_STAFF_MEMBER = 'new_staff_member';
```

## Performance Optimization

### 1. Database Optimization
```php
// Optimized queries with proper indexing
$notifications = Notification::where('user_id', $userId)
    ->where('role', $role)
    ->orderBy('created_at', 'desc')
    ->paginate(15);

// Efficient unread count query
$unreadCount = Notification::where('user_id', $userId)
    ->where('role', $role)
    ->where('read', false)
    ->count();
```

### 2. Caching Strategy
- **Unread Counters**: Cache unread message and notification counts
- **User Data**: Cache user role and center information
- **Notification Types**: Cache notification type configurations
- **Query Results**: Cache frequently accessed notification data

### 3. Frontend Optimization
- **AJAX Updates**: Real-time updates without page refresh
- **Lazy Loading**: Load notifications on demand
- **Pagination**: Efficient pagination for large notification lists
- **Debounced Requests**: Debounce AJAX requests to reduce server load

## Recent Updates

### 1. Enhanced Notification System
- **Comprehensive Type System**: Added 20+ specific notification types
- **Priority Management**: Implemented four-level priority system
- **Icon Mapping**: Detailed icon mapping for each notification type
- **Action URLs**: Support for clickable notifications with actions

### 2. Improved Security
- **Authorization Enhancement**: Strengthened authorization checking
- **Input Validation**: Enhanced validation for all inputs
- **Audit Logging**: Comprehensive logging for all operations
- **Error Handling**: Improved error handling and user feedback

### 3. AJAX Integration
- **Real-time Updates**: AJAX endpoints for real-time notification updates
- **Bulk Operations**: AJAX support for bulk operations
- **Mobile Optimization**: Enhanced mobile interface for messaging
- **Performance Improvements**: Optimized queries and caching

## Future Enhancements

### 1. Advanced Features
- **Push Notifications**: Browser push notifications for real-time alerts
- **Email Integration**: Email notifications for important messages
- **SMS Integration**: SMS notifications for urgent alerts
- **File Attachments**: Enhanced file attachment capabilities

### 2. Communication Enhancement
- **Group Messaging**: Support for group conversations
- **Message Templates**: Predefined message templates
- **Auto-replies**: Automatic reply functionality
- **Message Scheduling**: Schedule messages for future delivery

### 3. Analytics & Reporting
- **Communication Analytics**: Message and notification analytics
- **Response Time Tracking**: Track message response times
- **Usage Statistics**: User engagement with messaging system
- **Performance Metrics**: System performance monitoring

### 4. Integration Expansion
- **Calendar Integration**: Integration with calendar systems
- **External APIs**: Integration with external messaging services
- **Webhook Support**: Webhook support for external integrations
- **Third-party Notifications**: Integration with third-party notification services

This module provides comprehensive internal communication capabilities essential for rehabilitation centre operations, ensuring efficient staff communication, timely system alerts, and effective information dissemination while maintaining strict security and privacy standards.