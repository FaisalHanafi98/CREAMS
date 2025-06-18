@extends('layouts.app')

@section('title', 'Notifications - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/notificationstyle.css') }}">
<style>
    .notification-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .notification-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .notification-actions {
        display: flex;
        gap: 10px;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #e9ecef;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        background-color: #e9ecef;
    }
    
    .notification-filter {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .filter-label {
        font-weight: 500;
        margin-right: 15px;
    }
    
    .filter-options {
        display: flex;
        gap: 15px;
    }
    
    .filter-option {
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .filter-option:hover {
        background-color: #f8f9fa;
    }
    
    .filter-option.active {
        background-color: var(--primary-color);
        color: white;
    }
    
    .notification-list {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .notification-item {
        display: flex;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .notification-item.unread {
        background-color: rgba(50, 189, 234, 0.05);
    }
    
    .notification-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }
    
    .notification-icon.primary {
        background: linear-gradient(135deg, var(--primary-color), #00d2ff);
    }
    
    .notification-icon.success {
        background: linear-gradient(135deg, #2ed573, #00e676);
    }
    
    .notification-icon.info {
        background: linear-gradient(135deg, #00b0ff, #0091ea);
    }
    
    .notification-icon.warning {
        background: linear-gradient(135deg, #ffa502, #ffca28);
    }
    
    .notification-icon.danger {
        background: linear-gradient(135deg, #ff4757, #ff5252);
    }
    
    .notification-content {
        flex-grow: 1;
        margin-right: 15px;
    }
    
    .notification-title-text {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .notification-message {
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .notification-time {
        color: #888;
        font-size: 12px;
    }
    
    .notification-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
    }
    
    .notification-actions-item {
        display: flex;
        gap: 5px;
    }
    
    .btn-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #666;
        transition: all 0.3s ease;
    }
    
    .btn-icon:hover {
        background-color: #e9ecef;
    }
    
    .text-danger {
        color: #ff4757 !important;
    }
    
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }
    
    .empty-icon {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 20px;
    }
    
    .pagination-container {
        padding: 15px 20px;
        display: flex;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">Notifications</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <span class="current">Notifications</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="notification-container">
        <div class="notification-header">
            <h2 class="notification-title">
                <i class="fas fa-bell mr-2"></i> Notifications
                @if($unreadCount > 0)
                    <span class="badge badge-primary">{{ $unreadCount }}</span>
                @endif
            </h2>
            <div class="notification-actions">
                <button class="action-btn" id="markAllReadBtn">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
                <button class="action-btn" id="clearReadBtn">
                    <i class="fas fa-trash"></i> Clear Read Notifications
                </button>
            </div>
        </div>
        
        <div class="notification-filter">
            <div class="filter-label">Filter:</div>
            <div class="filter-options">
                <div class="filter-option active" data-filter="all">All</div>
                <div class="filter-option" data-filter="unread">Unread</div>
                <div class="filter-option" data-filter="message">Messages</div>
                <div class="filter-option" data-filter="activity">Activities</div>
                <div class="filter-option" data-filter="system">System</div>
            </div>
        </div>
        
        @if($notifications->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3>No notifications</h3>
                <p>You don't have any notifications yet. When you receive notifications, they will appear here.</p>
            </div>
        @else
            <div class="notification-list">
                @foreach($notifications as $notification)
                    <div class="notification-item {{ $notification->read ? '' : 'unread' }}" data-id="{{ $notification->id }}" data-type="{{ $notification->type }}">
                        <div class="notification-icon {{ $notification->color }}">
                            <i class="{{ $notification->icon }}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title-text">{{ $notification->title }}</div>
                            <div class="notification-message">{{ $notification->content }}</div>
                            <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="notification-meta">
                            @if(!$notification->read)
                                <span class="badge badge-info">New</span>
                            @endif
                            <div class="notification-actions-item">
                                <a href="{{ route('notifications.show', $notification->id) }}" class="btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$notification->read)
                                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-icon" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon text-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="pagination-container">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Filter notifications
        $('.filter-option').click(function() {
            $('.filter-option').removeClass('active');
            $(this).addClass('active');
            
            const filter = $(this).data('filter');
            
            if (filter === 'all') {
                $('.notification-item').show();
            } else if (filter === 'unread') {
                $('.notification-item').hide();
                $('.notification-item.unread').show();
            } else {
                $('.notification-item').hide();
                $('.notification-item[data-type="' + filter + '"]').show();
            }
        });
        
        // Mark all as read
        $('#markAllReadBtn').click(function() {
            if (confirm('Are you sure you want to mark all notifications as read?')) {
                $.ajax({
                    url: '{{ route("notifications.mark-all-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(error) {
                        console.error('Error marking notifications as read:', error);
                        alert('An error occurred while marking notifications as read.');
                    }
                });
            }
        });
        
        // Clear read notifications
        $('#clearReadBtn').click(function() {
            if (confirm('Are you sure you want to clear all read notifications? This action cannot be undone.')) {
                $.ajax({
                    url: '{{ route("notifications.clear-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(error) {
                        console.error('Error clearing read notifications:', error);
                        alert('An error occurred while clearing read notifications.');
                    }
                });
            }
        });
        
        // Delete confirmation
        $('.delete-form').submit(function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this notification? This action cannot be undone.')) {
                this.submit();
            }
        });
        
        // Notification item click to view
        $('.notification-item').click(function(e) {
            // Don't navigate if clicking on an action button
            if ($(e.target).closest('.notification-actions-item').length === 0) {
                const notificationId = $(this).data('id');
                window.location.href = '{{ url("notifications") }}/' + notificationId;
            }
        });
    });
</script>
@endsection