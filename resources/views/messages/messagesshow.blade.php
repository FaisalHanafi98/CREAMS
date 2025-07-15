@extends('layouts.dashboard')

@section('title', 'View Conversation')

@section('content')
<div class="content-section">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Conversation with {{ $conversation['with'] }}</h5>
            <div class="card-options">
                <a href="{{ route('messages') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
                <button class="btn btn-sm btn-primary ml-2" data-toggle="modal" data-target="#replyModal">
                    <i class="fas fa-reply"></i> Reply
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="conversation-container">
                <div class="conversation-header">
                    <div class="conversation-subject">
                        <h4>Weekly Progress Report</h4>
                    </div>
                    <div class="conversation-participants">
                        <span class="participant">
                            <i class="fas fa-user-circle"></i> {{ $conversation['with'] }} ({{ $conversation['with_role'] }})
                        </span>
                        <span class="participant">
                            <i class="fas fa-user-circle"></i> You
                        </span>
                    </div>
                </div>
                
                <div class="conversation-messages">
                    @foreach($conversation['messages'] as $message)
                        <div class="conversation-message {{ $message['is_mine'] ? 'my-message' : '' }}">
                            <div class="message-sender">
                                <div class="sender-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="sender-details">
                                    <span class="sender-name">{{ $message['sender_name'] }}</span>
                                    <span class="message-time">{{ \Carbon\Carbon::parse($message['date'])->format('M d, Y - h:i A') }}</span>
                                </div>
                            </div>
                            <div class="message-body">
                                <p>{{ $message['content'] }}</p>
                            </div>
                            <div class="message-actions">
                                <button class="btn-icon" title="Forward">
                                    <i class="fas fa-share"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">Reply to {{ $conversation['with'] }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('messages.send') }}" method="POST">
                @csrf
                <input type="hidden" name="recipient_id" value="2">
                <input type="hidden" name="subject" value="RE: Weekly Progress Report">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .conversation-container {
        min-height: 400px;
    }
    
    .conversation-header {
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .conversation-subject h4 {
        margin-bottom: 10px;
    }
    
    .conversation-participants {
        display: flex;
        gap: 15px;
    }
    
    .participant {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.7);
    }
    
    .conversation-messages {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .conversation-message {
        background-color: var(--light-bg);
        border-radius: 10px;
        padding: 15px;
    }
    
    .conversation-message.my-message {
        background-color: rgba(50, 189, 234, 0.1);
        margin-left: 40px;
    }
    
    .message-sender {
        display: flex;
        align-items: centre;
        margin-bottom: 10px;
    }
    
    .sender-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: centre;
        justify-content: centre;
        font-size: 20px;
        margin-right: 10px;
    }
    
    .my-message .sender-avatar {
        background-color: var(--secondary-color);
    }
    
    .sender-details {
        display: flex;
        flex-direction: column;
    }
    
    .sender-name {
        font-weight: 500;
    }
    
    .message-time {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.5);
    }
    
    .message-body {
        padding: 0 10px;
    }
    
    .message-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
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
</style>
@endsection