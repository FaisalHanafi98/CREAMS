@extends('layouts.app')

@section('title', 'Notification Details - CREAMS')

@section('styles')
<style>
    .notification-detail-container {
        background: var(--light-color);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .notification-header {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: var(--light-bg);
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
    
    .notification-title-section {
        flex: 1;
    }
    
    .notification-title-text {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark-color);
    }
    
    .notification-time {
        font-size: 14px;
        color: #888;
    }
    
    .notification-content {
        padding: 20px;
    }
    
    .notification-message {
        font-size: 16px;
        line-height: 1.6;
        color: var(--text-color);
        margin-bottom: 20px;
    }
    
    .notification-meta {
        padding: 15px 20px;
        background-color: var(--light-bg);
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .notification-actions {
        display: flex;
        gap: 10px;
    }
    
    .related-content {
        margin-top: 20px;
    }
    
    .related-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .related-notification-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .related-notification-item {
        display: flex;
        padding: 15px;
        border-radius: 10px;
        background-color: var(--light-bg);
        transition: all var(--transition-speed) ease;
    }
    
    .related-notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .related-notification-item .notification-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    @media (max-width: 768px) {
        .notification-meta {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .notification-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .notification-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">Notification Details</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('notifications.index') }}">Notifications</a>
                    <span class="separator">/</span>
                    <span class="current">View</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="notification-detail-container">
        <div class="notification-header">
            <div class="notification-icon {{ $notification->color }}">
                <i class="{{ $notification->icon }}"></i>
            </div>
            <div class="notification-title-section">
                <div class="notification-title-text">{{ $notification->title }}</div>
                <div class="notification-time">{{ $notification->created_at->format('F d, Y - h:i A') }} ({{ $notification->created_at->diffForHumans() }})</div>
            </div>
        </div>
        
        <div class="notification-content">
            <div class="notification-message">
                {{ $notification->content }}
            </div>
            
            @if(isset($notification->data) && !empty($notification->data))
                <div class="additional-data">
                    <h3 class="mb-3">Additional Information</h3>
                    <div class="card">
                        <div class="card-body">
                            <pre class="mb-0">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="notification-meta">
            <div class="notification-status">
                @if($notification->read)
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Read on {{ $notification->read_at ? $notification->read_at->format('F d, Y - h:i A') : 'unknown date' }}</span>
                @else
                    <span class="badge badge-info"><i class="fas fa-envelope mr-1"></i> Unread</span>
                @endif
            </div>
            <div class="notification-actions">
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                @if(!$notification->read)
                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Mark as Read
                        </button>
                    </form>
                @endif
                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    @if($notification->type === 'message')
        <div class="notification-detail-container related-content">
            <div class="notification-content">
                <h3 class="related-title">Related Message</h3>
                <p>This notification was generated from a message. You can view the full message details or conversation history.</p>
                <div class="text-center mt-4">
                    <a href="{{ route('messages.index') }}" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> Go to Messages
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Delete confirmation
        $('.delete-form').submit(function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this notification? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
</script>
@endsection