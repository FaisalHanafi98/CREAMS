@extends('layouts.dashboard')

@section('title', 'Notifications')

@section('content')
<div class="content-section">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Notifications</h5>
            <div class="card-options">
                <form action="{{ route('notifications.mark-read') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="notification_ids[]" value="all">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="notifications-container">
                @if(count($notifications) > 0)
                    <div class="notifications-list">
                        @foreach($notifications as $notification)
                            <div class="notification-item {{ !$notification['read'] ? 'unread' : '' }}">
                                <div class="notification-icon notification-{{ $notification['type'] }}">
                                    @if($notification['type'] == 'info')
                                        <i class="fas fa-info-circle"></i>
                                    @elseif($notification['type'] == 'success')
                                        <i class="fas fa-check-circle"></i>
                                    @elseif($notification['type'] == 'warning')
                                        <i class="fas fa-exclamation-triangle"></i>
                                    @elseif($notification['type'] == 'danger')
                                        <i class="fas fa-times-circle"></i>
                                    @endif
                                </div>
                                <div class="notification-content">
                                    <h5 class="notification-title">{{ $notification['title'] }}</h5>
                                    <p class="notification-message">{{ $notification['message'] }}</p>
                                    <span class="notification-time">
                                        {{ \Carbon\Carbon::parse($notification['date'])->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <form action="{{ route('notifications.mark-read') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="notification_ids[]" value="{{ $notification['id'] }}">
                                        <button type="submit" class="btn-icon" title="Mark as {{ $notification['read'] ? 'unread' : 'read' }}">
                                            <i class="fas fa-{{ $notification['read'] ? 'envelope' : 'envelope-open' }}"></i>
                                        </button>
                                    </form>
                                    <button class="btn-icon" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h4>No Notifications</h4>
                        <p>You don't have any notifications at this time.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .notifications-container {
        min-height: 400px;
    }
    
    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .notification-item {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        border-radius: 10px;
        background-color: var(--light-color);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .notification-item:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    
    .notification-item.unread {
        border-left: 3px solid var(--primary-color);
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: centre;
        justify-content: centre;
        margin-right: 15px;
        flex-shrink: 0;
        font-size: 18px;
    }
    
    .notification-info {
        background-color: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
    }
    
    .notification-success {
        background-color: rgba(28, 200, 138, 0.1);
        color: #1cc88a;
    }
    
    .notification-warning {
        background-color: rgba(246, 194, 62, 0.1);
        color: #f6c23e;
    }
    
    .notification-danger {
        background-color: rgba(231, 74, 59, 0.1);
        color: #e74a3b;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .notification-message {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.7);
        margin-bottom: 5px;
    }
    
    .notification-time {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.5);
    }
    
    .notification-actions {
        display: flex;
        gap: 10px;
        margin-left: 15px;
    }
    
    .btn-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: transparent;
        border: none;
        display: flex;
        align-items: centre;
        justify-content: centre;
        color: var(--text-color);
        transition: all 0.3s ease;
    }
    
    .btn-icon:hover {
        background: rgba(0, 0, 0, 0.1);
        color: var(--primary-color);
    }
    
    .empty-state {
        text-align: centre;
        padding: 60px 20px;
    }
    
    .empty-state-icon {
        font-size: 50px;
        color: rgba(0, 0, 0, 0.2);
        margin-bottom: 15px;
    }
    
    .empty-state h4 {
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: rgba(0, 0, 0, 0.5);
    }
</style>
@endsection