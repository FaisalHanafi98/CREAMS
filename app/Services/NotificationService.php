<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\MessageRecipient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 15;
    const LONG_CACHE_DURATION = 1440; // 24 hours
    
    /**
     * Get user notifications with caching
     */
    public function getUserNotifications($userId, $limit = 20, $unreadOnly = false)
    {
        $cacheKey = "user_notifications_{$userId}_{$limit}_" . ($unreadOnly ? 'unread' : 'all');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId, $limit, $unreadOnly) {
            $query = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc');
                
            if ($unreadOnly) {
                $query->where('is_read', false);
            }
            
            return $query->limit($limit)->get();
        });
    }
    
    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userId)
    {
        $cacheKey = "unread_count_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();
        });
    }
    
    /**
     * Create notification with delivery tracking
     */
    public function createNotification($data)
    {
        try {
            DB::beginTransaction();
            
            $notification = Notification::create([
                'notification_title' => $data['title'],
                'notification_message' => $data['message'],
                'notification_type' => $data['type'] ?? 'info',
                'notification_channel' => $data['channel'] ?? 'database',
                'user_id' => $data['user_id'],
                'notifiable_type' => $data['notifiable_type'] ?? 'App\\Models\\User',
                'notifiable_id' => $data['notifiable_id'] ?? $data['user_id'],
                'centre_id' => $data['centre_id'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'notification_data' => $data['data'] ?? null
            ]);
            
            // Mark as delivered if not scheduled
            if (!$notification->scheduled_at) {
                $notification->update(['delivered_at' => now()]);
            }
            
            // Clear user notification cache
            $this->clearUserNotificationCache($data['user_id']);
            
            DB::commit();
            
            Log::info('Notification created successfully', [
                'notification_id' => $notification->id,
                'user_id' => $data['user_id'],
                'type' => $data['type'] ?? 'info'
            ]);
            
            return $notification;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create notification', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Send broadcast message to multiple users
     */
    public function sendBroadcastMessage($senderId, $centreId, $subject, $body, $recipients, $options = [])
    {
        try {
            DB::beginTransaction();
            
            // Create the main message
            $message = EnhancedMessage::create([
                'sender_id' => $senderId,
                'message_subject' => $subject,
                'message_body' => $body,
                'message_type' => 'broadcast',
                'message_priority' => $options['priority'] ?? 'normal',
                'centre_id' => $centreId,
                'message_category_id' => $options['category_id'] ?? null,
                'scheduled_at' => $options['scheduled_at'] ?? null,
                'expires_at' => $options['expires_at'] ?? null,
                'attachments' => $options['attachments'] ?? null,
                'metadata' => $options['metadata'] ?? null
            ]);
            
            // Create recipient records
            $recipientData = [];
            $timestamp = now();
            
            foreach ($recipients as $recipient) {
                $recipientData[] = [
                    'message_id' => $message->id,
                    'recipient_type' => 'App\\Models\\User',
                    'recipient_id' => $recipient['id'],
                    'recipient_type_specific' => 'user',
                    'delivered_at' => $options['scheduled_at'] ? null : $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
            
            MessageRecipient::insert($recipientData);
            
            // Create notifications if not scheduled
            if (!isset($options['scheduled_at'])) {
                $this->createBulkNotifications(
                    array_column($recipients, 'id'),
                    [
                        'title' => 'New Message: ' . $subject,
                        'message' => 'You have received a new broadcast message',
                        'type' => 'info',
                        'centre_id' => $centreId,
                        'priority' => $options['priority'] ?? 'normal',
                        'data' => [
                            'message_id' => $message->id,
                            'sender_id' => $senderId,
                            'subject' => $subject
                        ]
                    ]
                );
            }
            
            // Mark message as delivered
            $message->markAsDelivered();
            
            DB::commit();
            
            Log::info('Broadcast message sent successfully', [
                'message_id' => $message->id,
                'sender_id' => $senderId,
                'recipient_count' => count($recipients)
            ]);
            
            return $message;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to send broadcast message', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId,
                'recipient_count' => count($recipients)
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Create bulk notifications
     */
    public function createBulkNotifications($userIds, $data)
    {
        try {
            $notifications = [];
            $timestamp = now();
            
            foreach ($userIds as $userId) {
                $notifications[] = [
                    'notification_title' => $data['title'],
                    'notification_message' => $data['message'],
                    'notification_type' => $data['type'] ?? 'info',
                    'notification_channel' => $data['channel'] ?? 'database',
                    'user_id' => $userId,
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $userId,
                    'centre_id' => $data['centre_id'] ?? null,
                    'priority' => $data['priority'] ?? 'normal',
                    'scheduled_at' => $data['scheduled_at'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'notification_data' => json_encode($data['data'] ?? null),
                    'delivered_at' => $data['scheduled_at'] ? null : $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
            
            // Batch insert for performance
            Notification::insert($notifications);
            
            // Clear cache for all affected users
            foreach ($userIds as $userId) {
                $this->clearUserNotificationCache($userId);
            }
            
            return count($notifications);
            
        } catch (\Exception $e) {
            Log::error('Failed to create bulk notifications', [
                'error' => $e->getMessage(),
                'user_count' => count($userIds)
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId, $userId)
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $userId)
                ->firstOrFail();
                
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            
            // Clear user notification cache
            $this->clearUserNotificationCache($userId);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Get notification statistics for a user
     */
    public function getNotificationStats($userId)
    {
        $cacheKey = "notification_stats_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            $total = Notification::where('user_id', $userId)->count();
            $unread = Notification::where('user_id', $userId)->where('is_read', false)->count();
            $today = Notification::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count();
                
            return [
                'total' => $total,
                'unread' => $unread,
                'read' => $total - $unread,
                'today' => $today,
                'read_percentage' => $total > 0 ? round((($total - $unread) / $total) * 100, 2) : 0
            ];
        });
    }
    
    /**
     * Clear user notification cache
     */
    public function clearUserNotificationCache($userId)
    {
        $patterns = [
            "user_notifications_{$userId}_20_all",
            "user_notifications_{$userId}_20_unread",
            "unread_count_{$userId}",
            "notification_stats_{$userId}"
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}