<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - CREAMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --supervisor-color: #4facfe;
            --teacher-color: #00c49a;
            --ajk-color: #f9a825;
            --success-color: #2ed573;
            --danger-color: #ff4757;
            --warning-color: #ffa502;
            --dark-color: #1a2a3a;
            --light-color: #ffffff;
            --text-color: #444444;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --header-height: 70px;
            --footer-height: 60px;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
        }
        
        .content-section {
            padding: 20px;
        }
        
        .header {
            background-color: var(--light-color);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: centre;
            padding: 15px 20px;
            min-height: var(--header-height);
            border-bottom: 1px solid var(--border-color);
        }
        
        .page-info {
            display: flex;
            flex-direction: column;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            background-image: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: centre;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: none;
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }
        
        .breadcrumb a:hover {
            color: var(--secondary-color);
        }
        
        .separator {
            margin: 0 8px;
            color: #ccc;
        }
        
        .current {
            color: #888;
        }
        
        .header-actions {
            display: flex;
            align-items: centre;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            display: flex;
            align-items: centre;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 8px;
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background: rgba(50, 189, 234, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .action-btn.primary {
            background: var(--primary-color);
            color: var(--light-color);
            border-color: var(--primary-color);
        }
        
        .action-btn.primary:hover {
            background: var(--primary-color);
            opacity: 0.9;
        }
        
        .messages-container {
            background: var(--light-color);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .messages-tabs {
            display: flex;
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0 20px;
        }
        
        .tab-btn {
            padding: 15px 20px;
            font-size: 15px;
            font-weight: 600;
            color: #888;
            border: none;
            background: none;
            cursor: pointer;
            position: relative;
            transition: color 0.3s ease;
            display: flex;
            align-items: centre;
            gap: 8px;
        }
        
        .tab-btn:hover {
            color: var(--primary-color);
        }
        
        .tab-btn.active {
            color: var(--primary-color);
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        
        .badge {
            display: inline-flex;
            align-items: centre;
            justify-content: centre;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            color: var(--light-color);
            background: var(--primary-color);
        }
        
        .tab-content {
            position: relative;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .message-list {
            display: flex;
            flex-direction: column;
        }
        
        .message-item {
            display: flex;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .message-item:hover {
            background: var(--light-bg);
        }
        
        .message-item.unread {
            background: rgba(50, 189, 234, 0.05);
        }
        
        .message-item.unread .message-subject {
            font-weight: 700;
        }
        
        .message-sender, .message-recipient {
            width: 200px;
            display: flex;
            align-items: centre;
            gap: 10px;
        }
        
        .user-avatar.small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .user-avatar.small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .sender-info, .recipient-info {
            display: flex;
            flex-direction: column;
        }
        
        .sender-name, .recipient-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 14px;
        }
        
        .sender-role, .recipient-role {
            font-size: 12px;
            color: #888;
        }
        
        .message-preview {
            flex: 1;
            padding: 0 20px;
        }
        
        .message-subject {
            font-size: 15px;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .message-excerpt {
            font-size: 13px;
            color: #777;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
            min-width: 120px;
        }
        
        .message-time {
            font-size: 12px;
            color: #888;
        }
        
        .message-status {
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: centre;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.read {
            background: rgba(46, 213, 115, 0.15);
            color: var(--success-color);
        }
        
        .status-badge.unread {
            background: rgba(255, 165, 2, 0.15);
            color: var(--warning-color);
        }
        
        .message-actions {
            display: flex;
            gap: 5px;
        }
        
        .btn-icon {
            display: flex;
            align-items: centre;
            justify-content: centre;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: var(--light-bg);
            color: #777;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-icon:hover {
            background: rgba(50, 189, 234, 0.15);
            color: var(--primary-color);
        }
        
        .btn-icon.text-danger:hover {
            background: rgba(255, 71, 87, 0.15);
            color: var(--danger-color);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: centre;
            justify-content: centre;
            padding: 50px 20px;
            text-align: centre;
        }
        
        .empty-icon {
            font-size: 60px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 14px;
            color: #888;
            max-width: 400px;
        }
        
        .pagination-container {
            padding: 20px;
            display: flex;
            justify-content: centre;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .message-sender, .message-recipient {
                width: 150px;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .message-item {
                flex-direction: column;
                gap: 10px;
            }
            
            .message-sender, .message-recipient {
                width: 100%;
            }
            
            .message-preview {
                padding: 0;
            }
            
            .message-meta {
                flex-direction: row;
                justify-content: space-between;
                align-items: centre;
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-btn {
                width: 100%;
                justify-content: centre;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (Include your sidebar here) -->
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="content-section">
            <div class="header">
                <div class="header-content">
                    <div class="page-info">
                        <h1 class="page-title">Messages</h1>
                        <div class="breadcrumb">
                            <a href="{{ route(session('role') . '.dashboard') }}">Dashboard</a>
                            <span class="separator">/</span>
                            <span class="current">Messages</span>
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <div class="action-buttons">
                            <a href="{{ route('messages.create') }}" class="action-btn primary">
                                <i class="fas fa-plus"></i> Compose New Message
                            </a>
                            <button class="action-btn" id="refreshMessages">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="action-btn" id="markAllRead">
                                <i class="fas fa-check-double"></i> Mark All Read
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="messages-container">
                <div class="messages-tabs">
                    <button class="tab-btn active" data-tab="inbox">
                        <i class="fas fa-inbox"></i> Inbox
                        @if($unreadCount > 0)
                            <span class="badge badge-primary">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <button class="tab-btn" data-tab="sent">
                        <i class="fas fa-paper-plane"></i> Sent
                    </button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="inbox-tab">
                        @if($inbox->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h3>Your inbox is empty</h3>
                                <p>You don't have any messages yet. When you receive messages, they will appear here.</p>
                            </div>
                        @else
                            <div class="message-list">
                                @foreach($inbox as $message)
                                    <div class="message-item {{ $message->read ? '' : 'unread' }}" data-id="{{ $message->id }}">
                                        <div class="message-sender">
                                            <div class="user-avatar small">
                                                @php
                                                    $avatar = 'images/default-avatar.png';
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
                                                        $avatar = 'storage/avatars/' . $user->avatar;
                                                    }
                                                @endphp
                                                <img src="{{ asset($avatar) }}" alt="Sender">
                                            </div>
                                            <div class="sender-info">
                                                <div class="sender-name">{{ $message->sender_name }}</div>
                                                <div class="sender-role">{{ ucfirst($message->sender_type) }}</div>
                                            </div>
                                        </div>
                                        <div class="message-preview">
                                            <div class="message-subject">{{ $message->subject }}</div>
                                            <div class="message-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($message->content), 100) }}</div>
                                        </div>
                                        <div class="message-meta">
                                            <div class="message-time">{{ $message->created_at->diffForHumans() }}</div>
                                            <div class="message-actions">
                                                <a href="{{ route('messages.show', $message->id) }}" class="btn-icon" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('messages.reply', $message->id) }}" class="btn-icon" title="Reply">
                                                    <i class="fas fa-reply"></i>
                                                </a>
                                                @if(!$message->read)
                                                    <form action="{{ route('messages.mark-read', $message->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn-icon" title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('messages.destroy', $message->id) }}" method="POST" class="d-inline delete-form">
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
                                {{ $inbox->appends(['sent_page' => $sent->currentPage()])->links() }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="tab-pane" id="sent-tab">
                        @if($sent->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <h3>No sent messages</h3>
                                <p>You haven't sent any messages yet. When you send messages, they will appear here.</p>
                            </div>
                        @else
                            <div class="message-list">
                                @foreach($sent as $message)
                                    <div class="message-item" data-id="{{ $message->id }}">
                                        <div class="message-recipient">
                                            <div class="user-avatar small">
                                                @php
                                                    $avatar = 'images/default-avatar.png';
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
                                                        $avatar = 'storage/avatars/' . $user->avatar;
                                                    }
                                                @endphp
                                                <img src="{{ asset($avatar) }}" alt="Recipient">
                                            </div>
                                            <div class="recipient-info">
                                                <div class="recipient-name">{{ $message->recipient_name }}</div>
                                                <div class="recipient-role">{{ ucfirst($message->recipient_type) }}</div>
                                            </div>
                                        </div>
                                        <div class="message-preview">
                                            <div class="message-subject">{{ $message->subject }}</div>
                                            <div class="message-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($message->content), 100) }}</div>
                                        </div>
                                        <div class="message-meta">
                                            <div class="message-time">{{ $message->created_at->diffForHumans() }}</div>
                                            <div class="message-status">
                                                @if($message->read)
                                                    <span class="status-badge read" title="Read {{ $message->read_at ? 'on ' . $message->read_at->format('M d, Y g:i A') : '' }}">
                                                        <i class="fas fa-check-double"></i> Read
                                                    </span>
                                                @else
                                                    <span class="status-badge unread">
                                                        <i class="fas fa-check"></i> Sent
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="message-actions">
                                                <a href="{{ route('messages.show', $message->id) }}" class="btn-icon" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('messages.destroy', $message->id) }}" method="POST" class="d-inline delete-form">
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
                                {{ $sent->appends(['inbox_page' => $inbox->currentPage()])->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.tab-btn').click(function() {
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                const tabId = $(this).data('tab') + '-tab';
                $('.tab-pane').removeClass('active');
                $('#' + tabId).addClass('active');
            });
            
            // Message item click to view
            $('.message-item').click(function(e) {
                // Don't navigate if clicking on an action button
                if ($(e.target).closest('.message-actions').length === 0) {
                    const messageId = $(this).data('id');
                    window.location.href = '{{ url("messages") }}/' + messageId;
                }
            });
            
            // Refresh messages
            $('#refreshMessages').click(function() {
                location.reload();
            });
            
            // Mark all as read
            $('#markAllRead').click(function() {
                if (confirm('Are you sure you want to mark all messages as read?')) {
                    $.ajax({
                        url: '{{ route("messages.mark-all-read") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(error) {
                            console.error('Error marking messages as read:', error);
                            alert('An error occurred while marking messages as read.');
                        }
                    });
                }
            });
            
            // Delete confirmation
            $('.delete-form').submit(function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>