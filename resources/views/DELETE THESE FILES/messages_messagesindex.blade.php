@extends('layouts.dashboard')

@section('title', 'Messages')

@section('content')
<div class="content-section">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Messages</h5>
            <div class="card-options">
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#composeModal">
                    <i class="fas fa-paper-plane"></i> Compose New Message
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="messages-container">
                @if(count($messages) > 0)
                    <div class="messages-list">
                        @foreach($messages as $message)
                            <div class="message-item {{ !$message['read'] ? 'unread' : '' }}">
                                <div class="message-checkbox">
                                    <input type="checkbox" class="message-select" data-id="{{ $message['id'] }}">
                                </div>
                                <div class="message-sender">
                                    <strong>{{ $message['sender_name'] }}</strong>
                                    <span class="sender-role">{{ $message['sender_role'] }}</span>
                                </div>
                                <div class="message-content">
                                    <a href="{{ route('messages.conversation', $message['id']) }}" class="message-subject">
                                        {{ $message['subject'] }}
                                    </a>
                                    <p class="message-preview">{{ Str::limit($message['message'], 100) }}</p>
                                </div>
                                <div class="message-date">
                                    {{ \Carbon\Carbon::parse($message['date'])->format('M d, Y') }}
                                </div>
                                <div class="message-actions">
                                    <button class="btn-icon" title="Mark as {{ $message['read'] ? 'unread' : 'read' }}">
                                        <i class="fas fa-{{ $message['read'] ? 'envelope' : 'envelope-open' }}"></i>
                                    </button>
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
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <h4>No Messages</h4>
                        <p>You don't have any messages yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div class="modal fade" id="composeModal" tabindex="-1" role="dialog" aria-labelledby="composeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="composeModalLabel">Compose New Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('messages.send') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient_id">Recipient</label>
                        <select class="form-control" id="recipient_id" name="recipient_id" required>
                            <option value="" disabled selected>Select recipient</option>
                            <option value="1">Ahmad Razif (Admin)</option>
                            <option value="2">Dr. Nurul Hafizah (Teacher)</option>
                            <option value="3">Mr. Ismail Rahman (Teacher)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .messages-container {
        min-height: 400px;
    }
    
    .messages-list {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .message-item {
        display: flex;
        align-items: centre;
        padding: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        background-color: var(--light-color);
        transition: all 0.3s ease;
    }
    
    .message-item:last-child {
        border-bottom: none;
    }
    
    .message-item:hover {
        background-color: var(--light-bg);
    }
    
    .message-item.unread {
        background-color: rgba(50, 189, 234, 0.05);
        font-weight: 500;
    }
    
    .message-checkbox {
        width: 30px;
    }
    
    .message-sender {
        width: 180px;
        padding-right: 15px;
    }
    
    .sender-role {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.5);
        display: block;
    }
    
    .message-content {
        flex: 1;
        padding-right: 15px;
    }
    
    .message-subject {
        font-weight: 500;
        color: var(--text-color);
        text-decoration: none;
        display: block;
        margin-bottom: 5px;
    }
    
    .message-preview {
        font-size: 13px;
        color: rgba(0, 0, 0, 0.6);
        margin: 0;
    }
    
    .message-date {
        width: 100px;
        font-size: 12px;
        color: rgba(0, 0, 0, 0.5);
        text-align: right;
    }
    
    .message-actions {
        width: 80px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
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
    
    @media (max-width: 767px) {
        .message-item {
            flex-wrap: wrap;
        }
        
        .message-sender {
            width: 50%;
            order: 2;
        }
        
        .message-date {
            width: 50%;
            order: 3;
        }
        
        .message-content {
            width: 100%;
            order: 4;
            padding-top: 10px;
        }
        
        .message-checkbox {
            order: 1;
        }
        
        .message-actions {
            width: 100%;
            order: 5;
            justify-content: flex-start;
            padding-top: 10px;
        }
    }
</style>
@endsection