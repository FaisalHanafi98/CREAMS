<div class="widget-card">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-bell"></i> Notifications
        </h3>
        @php
            $notificationsCollection = collect($notifications);
            $unreadCount = $notificationsCollection->where('read', false)->count();
        @endphp
        <span class="badge badge-primary">{{ $unreadCount }}</span>
    </div>
    <div class="widget-body">
        @if(count($notifications) > 0)
            <div class="notification-list">
                @foreach(array_slice($notifications, 0, 5) as $notification)
                    <div class="notification-item {{ !($notification['read'] ?? true) ? 'unread' : '' }}">
                        <div class="notification-content">
                            <p>{{ $notification['message'] ?? 'No message' }}</p>
                            <small class="text-muted">{{ $notification['time'] ?? $notification['created_at'] ?? 'Just now' }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="#" class="btn btn-sm btn-block btn-light mt-2">View All Notifications</a>
        @else
            <p class="text-muted text-center">No new notifications</p>
        @endif
    </div>
</div>