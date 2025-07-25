@extends('layouts.app')

@section('title', 'Notifications - CREAMS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Page Header --}}
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="page-title">
                            <i class="fas fa-bell"></i> Notifications
                        </h1>
                        <p class="page-subtitle">Manage your notifications and alerts</p>
                    </div>
                    <div class="col-auto">
                        @if($notifications->where('read', false)->count() > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-check-double"></i> Mark All as Read
                            </button>
                        </form>
                        @endif
                        
                        <form action="{{ route('notifications.clear-read') }}" method="POST" class="d-inline ml-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary" 
                                    onclick="return confirm('Are you sure you want to clear all read notifications?')">
                                <i class="fas fa-trash"></i> Clear Read
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Notifications Filter --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="filter-tabs">
                                <button class="btn btn-sm filter-btn active" data-filter="all">
                                    All ({{ $notifications->count() }})
                                </button>
                                <button class="btn btn-sm filter-btn" data-filter="unread">
                                    Unread ({{ $notifications->where('read', false)->count() }})
                                </button>
                                <button class="btn btn-sm filter-btn" data-filter="read">
                                    Read ({{ $notifications->where('read', true)->count() }})
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="notification-stats">
                                <small class="text-muted">
                                    Total: {{ $notifications->count() }} notifications
                                    @if($notifications->count() > 0)
                                        | Latest: {{ $notifications->first()->created_at->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifications List --}}
            <div class="notifications-container">
                @if($notifications->count() > 0)
                    @foreach($notifications as $notification)
                    <div class="notification-item {{ $notification->read ? 'read' : 'unread' }}" 
                         data-notification-id="{{ $notification->id }}"
                         data-filter-status="{{ $notification->read ? 'read' : 'unread' }}">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="notification-icon {{ $notification->read ? 'read' : 'unread' }}">
                                            @switch($notification->type)
                                                @case('success')
                                                    <i class="fas fa-check-circle text-success"></i>
                                                    @break
                                                @case('warning')
                                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                                    @break
                                                @case('error')
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                    @break
                                                @case('info')
                                                    <i class="fas fa-info-circle text-info"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-bell text-primary"></i>
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="notification-content">
                                            <h6 class="notification-title mb-1">
                                                {{ $notification->title }}
                                                @if(!$notification->read)
                                                    <span class="badge badge-primary badge-sm ml-2">New</span>
                                                @endif
                                            </h6>
                                            <p class="notification-message mb-2">
                                                {{ $notification->content }}
                                            </p>
                                            <div class="notification-meta">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> 
                                                    {{ $notification->created_at->format('M j, Y \a\t g:i A') }}
                                                    ({{ $notification->created_at->diffForHumans() }})
                                                    
                                                    @if($notification->user_type)
                                                        | <i class="fas fa-tag"></i> 
                                                        {{ str_replace('App\\Models\\', '', $notification->user_type) }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="notification-actions">
                                            @if(!$notification->read)
                                                <form action="{{ route('notifications.mark-read', $notification->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                            title="Mark as Read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('notifications.destroy', $notification->id) }}" 
                                                  method="POST" class="d-inline ml-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        title="Delete Notification"
                                                        onclick="return confirm('Are you sure you want to delete this notification?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Pagination --}}
                    @if($notifications instanceof \Illuminate\Pagination\AbstractPaginator)
                        <div class="d-flex justify-content-center">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                @else
                    {{-- Empty State --}}
                    <div class="empty-state text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-bell-slash fa-5x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No Notifications</h4>
                        <p class="text-muted mb-4">You don't have any notifications at the moment.</p>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.notification-item {
    transition: all 0.3s ease;
}

.notification-item.unread .card {
    border-left: 4px solid var(--primary-color, #007bff);
    background-color: #f8f9ff;
}

.notification-item.read .card {
    border-left: 4px solid #e9ecef;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.notification-icon.unread {
    background-color: rgba(0, 123, 255, 0.1);
}

.notification-icon.read {
    background-color: rgba(108, 117, 125, 0.1);
}

.notification-title {
    color: #333;
    font-weight: 600;
}

.notification-message {
    color: #666;
    line-height: 1.5;
}

.notification-meta {
    border-top: 1px solid #f0f0f0;
    padding-top: 8px;
    margin-top: 8px;
}

.filter-tabs .filter-btn {
    border: 1px solid #dee2e6;
    margin-right: 5px;
    transition: all 0.3s ease;
}

.filter-tabs .filter-btn.active {
    background-color: var(--primary-color, #007bff);
    color: white;
    border-color: var(--primary-color, #007bff);
}

.filter-tabs .filter-btn:not(.active):hover {
    background-color: #f8f9fa;
}

.page-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.page-subtitle {
    color: #6c757d;
    margin-bottom: 0;
}

.notification-actions .btn {
    transition: all 0.2s ease;
}

.empty-state-icon {
    opacity: 0.3;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const notificationItems = document.querySelectorAll('.notification-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            notificationItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'block';
                } else {
                    const status = item.dataset.filterStatus;
                    if (status === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
    
    // Auto-hide success/error messages after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            const closeBtn = alert.querySelector('.close');
            if (closeBtn) {
                closeBtn.click();
            }
        });
    }, 5000);
});
</script>
@endsection