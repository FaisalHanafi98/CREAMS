@extends('layouts.app')

@section('title', 'Reply to Message - CREAMS')

@section('styles')
<style>
    .reply-container {
        background: var(--light-color);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .reply-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: var(--light-bg);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .reply-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--light-color);
        background: linear-gradient(135deg, var(--primary-color), #00d2ff);
    }
    
    .reply-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0;
    }
    
    .reply-content {
        padding: 20px;
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    
    .original-message {
        margin-top: 30px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background-color: var(--light-bg);
        padding: 15px;
    }
    
    .original-message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .original-message-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .original-message-meta {
        font-size: 12px;
        color: #888;
    }
    
    .original-message-content {
        font-size: 14px;
        line-height: 1.6;
        color: var(--text-color);
        padding: 10px;
        background-color: var(--light-color);
        border-radius: 8px;
    }
    
    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .form-actions .btn {
            width: 100%;
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
                <h1 class="dashboard-title">Reply to Message</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('messages.index') }}">Messages</a>
                    <span class="separator">/</span>
                    <span class="current">Reply</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="reply-container">
        <div class="reply-header">
            <div class="reply-icon">
                <i class="fas fa-reply"></i>
            </div>
            <h2 class="reply-title">Reply to {{ $recipient_name }}</h2>
        </div>
        
        <div class="reply-content">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $recipient_id }}">
                <input type="hidden" name="recipient_type" value="{{ $recipient_type }}">
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="{{ $subject }}" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Message</label>
                    <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('messages.show', $message->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="original-message">
        <div class="original-message-header">
            <div class="original-message-title">
                <i class="fas fa-envelope mr-2"></i> Original Message
            </div>
            <div class="original-message-meta">
                From {{ $message->sender_name }} • {{ $message->created_at->format('F d, Y - h:i A') }}
            </div>
        </div>
        <div class="original-message-content">
            {{ $message->content }}
        </div>
    </div>
</div>
@endsection