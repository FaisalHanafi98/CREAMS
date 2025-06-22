@extends('layouts.app')

@section('title', 'Message Details - CREAMS')

@section('styles')
<style>
    .message-container {
        background: var(--light-color);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .message-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: var(--light-bg);
    }
    
    .message-subject {
        font-size: 22px;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .message-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .message-participants {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .message-participant {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .participant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
    }
    
    .participant-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .participant-info {
        display: flex;
        flex-direction: column;
    }
    
    .participant-name {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 14px;
    }
    
    .participant-role {
        font-size: 12px;
        color: #888;
    }
    
    .message-details {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
    }
    
    .message-time {
        font-size: 13px;
        color: #888;
    }
    
    .message-status {
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 20px;
    }
    
    .message-status.read {
        background: rgba(46, 213, 115, 0.15);
        color: var(--success-color);
    }
    
    .message-status.unread {
        background: rgba(255, 165, 2, 0.15);
        color: var(--warning-color);
    }
    
    .message-content {
        padding: 20px;
    }
    
    .message-body {
        font-size: 16px;
        line-height: 1.6;
        color: var(--text-color);
        margin-bottom: 20px;
        white-space: pre-line; /* Preserve line breaks */
    }
    
    .message-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background-color: var(--light-bg);
        border-top: 1px solid var(--border-color);
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .conversation-container {
        margin-top: 30px;
    }
    
    .conversation-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .conversation-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .conversation-timeline {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .conversation-item {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 15px;
        border-radius: 10px;
        background-color: var(--light-bg);
    }
    
    .conversation-item.highlight {
        border-left: 4px solid var(--primary-color);
        background-color: rgba(50, 189, 234, 0.05);
    }
    
    .conversation-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .conversation-sender {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .sender-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        overflow: hidden;
    }
    
    .sender-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .sender-name {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 14px;
    }
    
    .sender-role {
        font-size: 12px;
        color: #888;
    }
    
    .conversation-time {
        font-size: 12px;
        color: #888;
    }
    
    .conversation-content {
        font-size: 14px;
        line-height: 1.6;
        color: var(--text-color);
        white-space: pre-line; /* Preserve line breaks */
        padding-left: 40px; /* Align with sender name */
    }
    
    @media (max-width: 768px) {
        .message-meta {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .message-details {
            align-items: flex-start;
            width: 100%;
        }
        
        .message-actions {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .action-buttons {
            width: 100%;
            flex-wrap: wrap;
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
                <h1 class="dashboard-title">Message Details</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('messages.index') }}">Messages</a>
                    <span class="separator">/</span>
                    <span class="current">View</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="message-container">
        <div class="message-header">
            <div class="message-subject">{{ $message->subject }}</div>
            <div class="message-meta">
                <div class="message-participants">
                    <div class="message-participant">
                        <div class="participant-avatar">
                            @php
                                $senderAvatar = 'images/default-avatar.png';
                                if ($message->sender_type === 'admin') {
                                    $user = \App\Models\Admins::find($message->sender_id);
                                } elseif ($message->sender_type === 'supervisor') {
                                    $user = \App\Models\Supervisors::find($message->sender_id);
                                } elseif ($message->sender_type === 'teacher') {
                                    $user = \App\Models\Teachers::find($message->sender_id);
                                } elseif ($message->sender_type === 'ajk') {
                                    $user = \App\Models\AJKs::find($message->sender_id);
                                }
                                
                                if ($user && $user->avatar) {
                                    $senderAvatar = 'storage/avatars/' . $user->avatar;
                                }
                            @endphp
                            <img src="{{ asset($senderAvatar) }}" alt="Sender" class="avatar-img">
                        </div>
                        <div class="participant-info">
                            <div class="participant-name">{{ $message->sender_name }}</div>
                            <div class="participant-role">{{ ucfirst($message->sender_type) }} (Sender)</div>
                        </div>
                    </div>
                    <div class="message-participant">
                        <div class="participant-avatar">
                            @php
                                $recipientAvatar = 'images/default-avatar.png';
                                if ($message->recipient_type === 'admin') {
                                    $user = \App\Models\Admins::find($message->recipient_id);
                                } elseif ($message->recipient_type === 'supervisor') {
                                    $user = \App\Models\Supervisors::find($message->recipient_id);
                                } elseif ($message->recipient_type === 'teacher') {
                                    $user = \App\Models\Teachers::find($message->recipient_id);
                                } elseif ($message->recipient_type === 'ajk') {
                                    $user = \App\Models\AJKs::find($message->recipient_id);
                                }
                                
                                if ($user && $user->avatar) {
                                    $recipientAvatar = 'storage/avatars/' . $user->avatar;
                                } 
                            @endphp
                            <img src="{{ asset($recipientAvatar) }}" alt="Recipient" class="avatar-img">
                        </div>
                        <div class="participant-info">
                            <div class="participant-name">{{ $message->recipient_name }}</div>
                            <div class="participant-role">{{ ucfirst($message->recipient_type) }} (Recipient)</div>
                        </div>
                    </div>
                </div>
                <div class="message-details">
                    <div class="message-time">{{ $message->created_at->format('F d, Y - h:i A') }}</div>
                    <div class="message-status {{ $message->read ? 'read' : 'unread' }}">
                        @if($message->read)
                            <i class="fas fa-check-double"></i> Read {{ $message->read_at ? '(' . $message->read_at->diffForHumans() . ')' : '' }}
                        @else
                            <i class="fas fa-check"></i> Delivered
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="message-content">
            <div class="message-body">{{ $message->content }}</div>
        </div>
        <div class="message-actions">
            <div class="action-buttons">
                <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
                <a href="{{ route('messages.reply', $message->id) }}" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Reply
                </a>
            </div>
            <form action="{{ route('messages.destroy', $message->id) }}" method="POST" class="delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </form>
        </div>
    </div>
    
    @if($conversation->count() > 1)
        <div class="conversation-container">
            <div class="conversation-header">
                <h2 class="conversation-title">Conversation History</h2>
            </div>
            <div class="conversation-timeline">
                @foreach($conversation as $conversationMessage)
                    <div class="conversation-item {{ $conversationMessage->id == $message->id ? 'highlight' : '' }}">
                        <div class="conversation-meta">
                            <div class="conversation-sender">
                                <div class="sender-avatar">
                                    @php
                                        $conversationAvatar = 'images/default-avatar.png';
                                        if ($conversationMessage->sender_type === 'admin') {
                                            $user = \App\Models\Admins::find($conversationMessage->sender_id);
                                        } elseif ($conversationMessage->sender_type === 'supervisor') {
                                            $user = \App\Models\Supervisors::find($conversationMessage->sender_id);
                                        } elseif ($conversationMessage->sender_type === 'teacher') {
                                            $user = \App\Models\Teachers::find($conversationMessage->sender_id);
                                        } elseif ($conversationMessage->sender_type === 'ajk') {
                                            $user = \App\Models\AJKs::find($conversationMessage->sender_id);
                                        }
                                        
                                        if ($user && $user->avatar) {
                                            $conversationAvatar = 'storage/avatars/' . $user->avatar;
                                        } 
                                    @endphp
                                    <img src="{{ asset($conversationAvatar) }}" alt="{{ $conversationMessage->sender_name }}" class="avatar-img">
                                </div>
                                <div>
                                    <span class="sender-name">{{ $conversationMessage->sender_name }}</span>
                                </div>
                            </div>
                            <div class="conversation-time">
                                {{ $conversationMessage->created_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                        <div class="conversation-content">
                            {{ $conversationMessage->content }}
                        </div>
                    </div>
                @endforeach
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
            
            if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
</script>
@endsection