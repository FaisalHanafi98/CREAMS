<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// NOTE: Message table does NOT have a centre_id column.
// Centre filtering is handled via scopeForCentre() local scope
// and through the sender's centre relationship.

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'subject',
        'message_body',
        'priority',
        'status',
        'sent_at',
        'attachment_path'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    /**
     * Get the sender user
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the primary recipient user (for direct messages)
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    /**
     * Get all recipients (polymorphic relationship)
     */
    public function recipients()
    {
        return $this->hasMany(MessageRecipient::class, 'message_id');
    }
    
    /**
     * Get the centre this message belongs to
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }
    
    /**
     * Get the parent message (for replies)
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }
    
    /**
     * Get child messages (replies)
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }
    
    /**
     * Get the message category
     */
    public function category()
    {
        return $this->belongsTo(MessageCategory::class, 'message_category_id');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
    
    /**
     * Scope for non-deleted messages
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }
    
    /**
     * Scope for messages by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }
    
    /**
     * Scope for messages by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('message_priority', $priority);
    }
    
    /**
     * Scope for scheduled messages
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '>', now());
    }
    
    /**
     * Scope for delivered messages
     */
    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }
    
    /**
     * Scope for expired messages
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
    }
    
    /**
     * Scope for centre-specific messages
     */
    public function scopeForCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope for messages involving a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('recipient_id', $userId)
              ->orWhereHas('recipients', function($subQ) use ($userId) {
                  $subQ->where('recipient_type', 'App\\Models\\User')
                       ->where('recipient_id', $userId);
              });
        });
    }

    /**
     * Mark the message as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
    
    /**
     * Mark the message as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'delivered_at' => now()
        ]);
    }
    
    /**
     * Soft delete the message
     */
    public function softDelete()
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now()
        ]);
    }

    /**
     * Determine if the message is read
     */
    public function isRead()
    {
        return $this->is_read;
    }
    
    /**
     * Determine if the message is scheduled
     */
    public function isScheduled()
    {
        return $this->scheduled_at && $this->scheduled_at > now();
    }
    
    /**
     * Determine if the message is delivered
     */
    public function isDelivered()
    {
        return !is_null($this->delivered_at);
    }
    
    /**
     * Determine if the message is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at < now();
    }
    
    /**
     * Determine if the message is deleted
     */
    public function isDeleted()
    {
        return $this->is_deleted;
    }
    
    /**
     * Get the message priority label
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
        
        return $labels[$this->message_priority] ?? 'Normal';
    }
    
    /**
     * Get the message type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'direct' => 'Direct Message',
            'broadcast' => 'Broadcast',
            'announcement' => 'Announcement',
            'system' => 'System Message'
        ];
        
        return $labels[$this->message_type] ?? 'Direct Message';
    }

    /**
     * Get the sender name
     */
    public function getSenderNameAttribute()
    {
        return $this->sender ? $this->sender->name : 'System';
    }

    /**
     * Get the recipient name
     */
    public function getRecipientNameAttribute()
    {
        return $this->recipient ? $this->recipient->name : 'Unknown';
    }
    
    /**
     * Get formatted created date
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }
    
    /**
     * Get short preview of message body
     */
    public function getPreviewAttribute()
    {
        return Str::limit(strip_tags($this->message_body), 100);
    }
    
    /**
     * Get recipient count for broadcast messages
     */
    public function getRecipientCountAttribute()
    {
        return $this->recipients()->count();
    }
    
    /**
     * Get unread recipient count
     */
    public function getUnreadCountAttribute()
    {
        return $this->recipients()->unread()->count();
    }
    
    /**
     * Check if message has attachments
     */
    public function hasAttachments()
    {
        return !empty($this->attachments);
    }
    
    /**
     * Get attachment count
     */
    public function getAttachmentCountAttribute()
    {
        return is_array($this->attachments) ? count($this->attachments) : 0;
    }
    
    /**
     * Create a broadcast message to multiple recipients
     */
    public static function createBroadcast($senderId, $centreId, $subject, $body, $recipients, $options = [])
    {
        $message = static::create([
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
        foreach ($recipients as $recipient) {
            MessageRecipient::create([
                'message_id' => $message->id,
                'recipient_type' => $recipient['type'],
                'recipient_id' => $recipient['id'],
                'recipient_type_specific' => $recipient['specific_type'] ?? 'user'
            ]);
        }
        
        return $message;
    }
    
    /**
     * Create a reply to this message
     */
    public function createReply($senderId, $subject, $body, $options = [])
    {
        return static::create([
            'sender_id' => $senderId,
            'recipient_id' => $this->sender_id,
            'message_subject' => $subject,
            'message_body' => $body,
            'message_type' => 'direct',
            'message_priority' => $options['priority'] ?? $this->message_priority,
            'centre_id' => $this->centre_id,
            'parent_message_id' => $this->id,
            'message_category_id' => $this->message_category_id,
            'attachments' => $options['attachments'] ?? null,
            'metadata' => $options['metadata'] ?? null
        ]);
    }
    
    /**
     * Get conversation thread
     */
    public function getConversationThread()
    {
        $rootMessage = $this->parent_message_id ? $this->parent : $this;
        
        return static::where(function($q) use ($rootMessage) {
            $q->where('id', $rootMessage->id)
              ->orWhere('parent_message_id', $rootMessage->id);
        })->orderBy('created_at')->get();
    }
    
    /**
     * Send the message (mark as delivered and create notifications)
     */
    public function send()
    {
        $this->markAsDelivered();
        
        // Create notifications for recipients
        if ($this->message_type === 'broadcast') {
            foreach ($this->recipients as $recipient) {
                $this->createNotificationForRecipient($recipient);
            }
        } elseif ($this->recipient_id) {
            $this->createNotificationForUser($this->recipient_id);
        }
        
        return $this;
    }
    
    /**
     * Create notification for a specific recipient
     */
    protected function createNotificationForRecipient($recipient)
    {
        Notification::create([
            'notification_title' => 'New Message: ' . $this->message_subject,
            'notification_message' => 'You have received a new message from ' . $this->sender_name,
            'notification_type' => 'info',
            'notification_channel' => 'database',
            'user_id' => $recipient->recipient_id,
            'notifiable_type' => $recipient->recipient_type,
            'notifiable_id' => $recipient->recipient_id,
            'centre_id' => $this->centre_id,
            'priority' => $this->message_priority,
            'notification_data' => [
                'message_id' => $this->id,
                'sender_name' => $this->sender_name,
                'subject' => $this->message_subject
            ]
        ]);
    }
    
    /**
     * Create notification for a specific user
     */
    protected function createNotificationForUser($userId)
    {
        Notification::create([
            'notification_title' => 'New Message: ' . $this->message_subject,
            'notification_message' => 'You have received a new message from ' . $this->sender_name,
            'notification_type' => 'info',
            'notification_channel' => 'database',
            'user_id' => $userId,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'centre_id' => $this->centre_id,
            'priority' => $this->message_priority,
            'notification_data' => [
                'message_id' => $this->id,
                'sender_name' => $this->sender_name,
                'subject' => $this->message_subject
            ]
        ]);
    }
}