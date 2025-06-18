<div class="widget-card">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-bell"></i> Notifications
        </h3>
        <span class="badge badge-primary">{{ $notifications->where('read', false)->count() }}</span>
    </div>
    <div class="widget-body">
        @if($notifications->count() > 0)
            <div class="notification-list">
                @foreach($notifications->take(5) as $notification)
                    <div class="notification-item {{ !$notification['read'] ? 'unread' : '' }}">
                        <div class="notification-content">
                            <p>{{ $notification['message'] }}</p>
                            <small class="text-muted">{{ $notification['time'] }}</small>
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