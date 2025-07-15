<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Users;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a notification for a specific user
     */
    public static function createForUser($userId, $title, $message, $type = 'general', $priority = 'medium', $metadata = null, $centreId = null)
    {
        try {
            // Get user to determine user_type
            $user = Users::find($userId);
            if (!$user) {
                Log::error('User not found for notification', ['user_id' => $userId]);
                return null;
            }

            // Use existing table schema
            return Notification::create([
                'user_id' => $userId,
                'role' => $user->role, // Use role column
                'title' => $title,
                'message' => $message, // Use message column
                'type' => $type,
                'metadata' => $metadata, // Use metadata column
                'read' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'user_id' => $userId,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function createForUserLegacy($userId, $userType, $title, $content, $type = 'general', $data = null)
    {
        return self::createForUser($userId, $title, $content, $type, 'medium', $data);
    }

    /**
     * Create notifications for multiple users
     */
    public static function createForUsers(array $userIds, $title, $message, $type = 'general', $priority = 'medium', $metadata = null, $centreId = null)
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notification = self::createForUser(
                $userId, 
                $title, 
                $message, 
                $type,
                $priority,
                $metadata,
                $centreId
            );
            
            if ($notification) {
                $notifications[] = $notification;
            }
        }
        
        Log::info('Created bulk notifications', [
            'count' => count($notifications),
            'total_requested' => count($userIds)
        ]);
        
        return $notifications;
    }

    /**
     * Create notifications for all users of a specific role
     */
    public static function createForRole($role, $title, $content, $type = 'general', $data = null, $centreId = null)
    {
        try {
            $query = Users::where('role', $role)->where('status', 'active');
            
            if ($centreId) {
                $query->where('centre_id', $centreId);
            }
            
            $users = $query->get(['id', 'role']);
            
            $notifications = [];
            foreach ($users as $user) {
                $notification = self::createForUser(
                    $user->id, 
                    $user->role, 
                    $title, 
                    $content, 
                    $type, 
                    $data
                );
                
                if ($notification) {
                    $notifications[] = $notification;
                }
            }
            
            Log::info('Created notifications for role', [
                'role' => $role,
                'count' => count($notifications),
                'centre_id' => $centreId
            ]);
            
            return $notifications;
        } catch (\Exception $e) {
            Log::error('Failed to create notifications for role', [
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Staff-related notification triggers
     */
    public static function notifyStaffRegistered($staffId, $staffName, $role, $centreId = null)
    {
        // Notify admins and supervisors about new staff
        self::createForRole(
            'admin',
            'New Staff Member Registered',
            "A new {$role} named {$staffName} has been registered in the system.",
            'staff',
            ['staff_id' => $staffId, 'action' => 'registered'],
            $centreId
        );

        self::createForRole(
            'supervisor',
            'New Staff Member Registered',
            "A new {$role} named {$staffName} has been registered in the system.",
            'staff',
            ['staff_id' => $staffId, 'action' => 'registered'],
            $centreId
        );
    }

    public static function notifyStaffProfileUpdated($staffId, $staffName, $updatedBy)
    {
        // Notify supervisors about staff profile updates
        self::createForRole(
            'supervisor',
            'Staff Profile Updated',
            "Profile for {$staffName} has been updated by {$updatedBy}.",
            'staff',
            ['staff_id' => $staffId, 'action' => 'profile_updated']
        );
    }

    public static function notifyStaffAssignedToActivity($staffId, $staffName, $activityName, $activityId)
    {
        // Notify the staff member about activity assignment
        $staff = Users::find($staffId);
        if ($staff) {
            self::createForUser(
                $staffId,
                $staff->role,
                'Assigned to New Activity',
                "You have been assigned to the activity: {$activityName}",
                'activity',
                ['activity_id' => $activityId, 'action' => 'assigned']
            );
        }
    }

    /**
     * Trainee-related notification triggers
     */
    public static function notifyTraineeEnrolled($traineeId, $traineeName, $centreId = null)
    {
        // Notify teachers and supervisors about new trainee enrollment
        self::createForRole(
            'teacher',
            'New Trainee Enrolled',
            "A new trainee named {$traineeName} has been enrolled in the system.",
            'trainee',
            ['trainee_id' => $traineeId, 'action' => 'enrolled'],
            $centreId
        );

        self::createForRole(
            'supervisor',
            'New Trainee Enrolled',
            "A new trainee named {$traineeName} has been enrolled in the system.",
            'trainee',
            ['trainee_id' => $traineeId, 'action' => 'enrolled'],
            $centreId
        );
    }

    public static function notifyTraineeAttendanceMarked($traineeId, $traineeName, $sessionName, $status)
    {
        // Notify assigned teachers about attendance updates
        self::createForRole(
            'teacher',
            'Attendance Updated',
            "Attendance for {$traineeName} has been marked as {$status} for {$sessionName}.",
            'trainee',
            ['trainee_id' => $traineeId, 'action' => 'attendance_marked', 'status' => $status]
        );
    }

    /**
     * Activity-related notification triggers
     */
    public static function notifyActivityCreated($activityId, $activityName, $createdBy, $centreId = null)
    {
        // Notify relevant staff about new activity
        self::createForRole(
            'teacher',
            'New Activity Created',
            "A new activity '{$activityName}' has been created by {$createdBy}.",
            'activity',
            ['activity_id' => $activityId, 'action' => 'created'],
            $centreId
        );

        self::createForRole(
            'supervisor',
            'New Activity Created',
            "A new activity '{$activityName}' has been created by {$createdBy}.",
            'activity',
            ['activity_id' => $activityId, 'action' => 'created'],
            $centreId
        );
    }

    public static function notifyActivityScheduleChanged($activityId, $activityName, $participants = [])
    {
        // Notify all enrolled participants about schedule changes
        foreach ($participants as $participant) {
            self::createForUser(
                $participant['id'],
                $participant['type'], // 'teacher' or 'trainee'
                'Activity Schedule Changed',
                "The schedule for '{$activityName}' has been updated. Please check the new details.",
                'activity',
                ['activity_id' => $activityId, 'action' => 'schedule_changed']
            );
        }
    }

    public static function notifyActivityCancelled($activityId, $activityName, $participants = [])
    {
        // Notify all participants about activity cancellation
        foreach ($participants as $participant) {
            self::createForUser(
                $participant['id'],
                $participant['type'],
                'Activity Cancelled',
                "The activity '{$activityName}' has been cancelled. You will be notified if it is rescheduled.",
                'activity',
                ['activity_id' => $activityId, 'action' => 'cancelled']
            );
        }
    }

    /**
     * System-related notification triggers
     */
    public static function notifyLowAttendanceAlert($activityName, $attendanceRate, $centreId = null)
    {
        // Notify supervisors about low attendance
        self::createForRole(
            'supervisor',
            'Low Attendance Alert',
            "Activity '{$activityName}' has low attendance rate of {$attendanceRate}%. Please review and take action.",
            'system',
            ['activity_name' => $activityName, 'attendance_rate' => $attendanceRate],
            $centreId
        );
    }

    public static function notifyAssetLowStock($assetName, $currentStock, $minStock)
    {
        // Notify admins about low stock
        self::createForRole(
            'admin',
            'Asset Low Stock Alert',
            "Asset '{$assetName}' is running low. Current stock: {$currentStock}, Minimum: {$minStock}.",
            'system',
            ['asset_name' => $assetName, 'current_stock' => $currentStock, 'min_stock' => $minStock]
        );
    }

    public static function notifyUpcomingEvents($eventName, $eventDate, $centreId = null)
    {
        // Notify relevant users about upcoming events
        $roles = ['admin', 'supervisor', 'teacher'];
        
        foreach ($roles as $role) {
            self::createForRole(
                $role,
                'Upcoming Event Reminder',
                "Event '{$eventName}' is scheduled for {$eventDate}. Please prepare accordingly.",
                'system',
                ['event_name' => $eventName, 'event_date' => $eventDate],
                $centreId
            );
        }
    }

    /**
     * Get unread notifications count for a user
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for a user
     */
    public static function getRecentNotifications($userId, $limit = 5)
    {
        return Notification::where('user_id', $userId)
            ->with('centre')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Delete old read notifications (cleanup)
     */
    public static function cleanupOldNotifications($daysOld = 30)
    {
        return Notification::where('read', true)
            ->where('read_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}