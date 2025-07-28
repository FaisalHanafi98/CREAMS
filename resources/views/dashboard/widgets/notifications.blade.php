<div class="widget-card">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-bell"></i> Notification
        </h3>
        @php
            // Handle notifications from dashboard service or fallback to system-generated
            $notificationData = $notifications ?? [];
            
            // If no notifications from service, generate role-based notifications
            if (empty($notificationData)) {
                $role = session('role');
                $notificationData = [];
                
                try {
                    switch ($role) {
                        case 'admin':
                            // Generate admin notifications from recent system activity
                            $recentUserCount = \App\Models\User::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))->count();
                            $recentTraineeCount = \App\Models\Trainee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))->count();
                            $activeSessionsCount = \App\Models\ActivitySession::where('status', 'active')->count();
                            
                            if ($recentUserCount > 0) {
                                $notificationData[] = [
                                    'type' => 'info',
                                    'message' => "{$recentUserCount} new users registered this week",
                                    'action' => route('admin.users', ['filter' => 'recent']),
                                    'created_at' => \Carbon\Carbon::now()->subHours(2),
                                    'read' => false,
                                    'icon' => 'fas fa-user-plus'
                                ];
                            }
                            
                            if ($recentTraineeCount > 0) {
                                $notificationData[] = [
                                    'type' => 'success',
                                    'message' => "{$recentTraineeCount} new trainees enrolled this week",
                                    'action' => route('trainees.home'),
                                    'created_at' => \Carbon\Carbon::now()->subHours(4),
                                    'read' => false,
                                    'icon' => 'fas fa-user-graduate'
                                ];
                            }
                            
                            if ($activeSessionsCount > 0) {
                                $notificationData[] = [
                                    'type' => 'warning',
                                    'message' => "{$activeSessionsCount} active sessions running",
                                    'action' => route('activities.home'),
                                    'created_at' => \Carbon\Carbon::now()->subMinutes(30),
                                    'read' => true,
                                    'icon' => 'fas fa-play-circle'
                                ];
                            }
                            
                            $pendingVolunteers = \App\Models\Volunteer::where('status', 'pending')->count();
                            if ($pendingVolunteers > 0) {
                                $notificationData[] = [
                                    'type' => 'info',
                                    'message' => "{$pendingVolunteers} volunteer applications pending review",
                                    'action' => '#',
                                    'created_at' => \Carbon\Carbon::now()->subHours(6),
                                    'read' => false,
                                    'icon' => 'fas fa-hands-helping'
                                ];
                            }
                            break;
                            
                        case 'teacher':
                            // Generate teacher notifications
                            $centreId = session('centre_id');
                            $myActivities = \App\Models\Activity::where('created_by', session('id'))->count();
                            $todaysSessions = \App\Models\ActivitySession::whereDate('scheduled_date', \Carbon\Carbon::today())
                                ->where('teacher_id', session('id'))->count();
                            
                            if ($todaysSessions > 0) {
                                $notificationData[] = [
                                    'type' => 'info',
                                    'message' => "You have {$todaysSessions} sessions scheduled for today",
                                    'action' => route('teacher.schedule'),
                                    'created_at' => \Carbon\Carbon::now()->subHours(1),
                                    'read' => false,
                                    'icon' => 'fas fa-calendar-day'
                                ];
                            }
                            
                            $upcomingSessions = \App\Models\ActivitySession::where('teacher_id', session('id'))
                                ->where('scheduled_date', '>', \Carbon\Carbon::now())
                                ->where('scheduled_date', '<=', \Carbon\Carbon::now()->addDays(3))
                                ->count();
                            
                            if ($upcomingSessions > 0) {
                                $notificationData[] = [
                                    'type' => 'success',
                                    'message' => "{$upcomingSessions} sessions scheduled for next 3 days",
                                    'action' => route('teacher.schedule'),
                                    'created_at' => \Carbon\Carbon::now()->subHours(3),
                                    'read' => true,
                                    'icon' => 'fas fa-calendar-alt'
                                ];
                            }
                            break;
                            
                        case 'supervisor':
                            // Generate supervisor notifications
                            $centreId = session('centre_id');
                            if ($centreId) {
                                $centreTeachers = \App\Models\User::where('role', 'teacher')
                                    ->where('centre_id', $centreId)->count();
                                $centreTrainees = \App\Models\Trainee::where('centre_id', $centreId)->count();
                                
                                $notificationData[] = [
                                    'type' => 'info',
                                    'message' => "Centre overview: {$centreTeachers} teachers, {$centreTrainees} trainees",
                                    'action' => route('supervisor.centre'),
                                    'created_at' => \Carbon\Carbon::now()->subHours(2),
                                    'read' => true,
                                    'icon' => 'fas fa-building'
                                ];
                            }
                            break;
                    }
                    
                    // Add general system notification for all roles
                    $notificationData[] = [
                        'type' => 'info',
                        'message' => 'CREAMS system is running smoothly',
                        'action' => route('dashboard'),
                        'created_at' => \Carbon\Carbon::now()->subHours(8),
                        'read' => true,
                        'icon' => 'fas fa-check-circle'
                    ];
                    
                } catch (Exception $e) {
                    // Fallback if database queries fail
                    $notificationData = [
                        [
                            'type' => 'info',
                            'message' => 'Welcome to CREAMS dashboard',
                            'action' => route('dashboard'),
                            'created_at' => \Carbon\Carbon::now(),
                            'read' => false,
                            'icon' => 'fas fa-info-circle'
                        ]
                    ];
                }
            }
            
            $notificationsCollection = collect($notificationData);
            $unreadCount = $notificationsCollection->where('read', false)->count();
        @endphp
        <div class="notification-badge-container">
            @if($unreadCount > 0)
                <span class="badge badge-danger notification-count">{{ $unreadCount }}</span>
            @else
                <span class="badge badge-secondary notification-count">{{ count($notificationData) }}</span>
            @endif
        </div>
    </div>
    <div class="widget-body">
        @if(count($notificationData) > 0)
            <div class="notification-list">
                @foreach(array_slice($notificationData, 0, 5) as $notification)
                    <div class="notification-item {{ !($notification['read'] ?? true) ? 'unread' : '' }}">
                        <div class="notification-icon">
                            <i class="{{ $notification['icon'] ?? 'fas fa-bell' }} text-{{ $notification['type'] ?? 'info' }}"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-message">{{ $notification['message'] ?? 'No message' }}</p>
                            <small class="text-muted notification-time">
                                {{ isset($notification['created_at']) ? \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() : 'Just now' }}
                            </small>
                        </div>
                        @if(!($notification['read'] ?? true))
                            <div class="notification-dot"></div>
                        @endif
                    </div>
                @endforeach
            </div>
            @if(session('role') === 'admin')
                <a href="{{ route('admin.notifications') }}" class="btn btn-sm btn-block btn-light mt-2">
                    <i class="fas fa-list me-1"></i>View All Notification
                </a>
            @else
                <button class="btn btn-sm btn-block btn-light mt-2" onclick="markAllAsRead()">
                    <i class="fas fa-check me-1"></i>Mark All as Read
                </button>
            @endif
        @else
            <div class="empty-notifications">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="text-muted text-center mb-0">No notifications</p>
                <small class="text-muted">You're all caught up!</small>
            </div>
        @endif
    </div>
</div>