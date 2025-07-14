<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'title',
        'content',
        'type',
        'data',
        'read',
        'read_at'
    ];

    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Notification types constants
    const TYPE_ACTIVITY_SCHEDULED = 'activity_scheduled';
    const TYPE_ACTIVITY_CANCELLED = 'activity_cancelled';
    const TYPE_ACTIVITY_RESCHEDULED = 'activity_rescheduled';
    const TYPE_LOW_ENROLLMENT = 'low_enrollment';
    const TYPE_SESSION_REMINDER = 'session_reminder';
    const TYPE_ATTENDANCE_MISSING = 'attendance_missing';
    const TYPE_TRAINEE_ENROLLED = 'trainee_enrolled';
    const TYPE_TRAINEE_WITHDRAWN = 'trainee_withdrawn';
    const TYPE_TRAINEE_PROFILE_UPDATED = 'trainee_profile_updated';
    const TYPE_PROGRESS_REPORT_DUE = 'progress_report_due';
    const TYPE_BIRTHDAY_REMINDER = 'birthday_reminder';
    const TYPE_NEW_STAFF_MEMBER = 'new_staff_member';
    const TYPE_ASSET_ASSIGNED = 'asset_assigned';
    const TYPE_SCHEDULE_CONFLICT = 'schedule_conflict';
    const TYPE_SYSTEM_ANNOUNCEMENT = 'system_announcement';
    const TYPE_ACTIVITY_APPROVAL_PENDING = 'activity_approval_pending';
    const TYPE_PROFILE_UPDATE_REQUEST = 'profile_update_request';
    const TYPE_LEAVE_REQUEST = 'leave_request';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    /**
     * Get the centre that the notification belongs to.
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centres::class, 'centre_id', 'centre_id');
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope to get notifications by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get recent notifications.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->read) {
            return true; // Already read
        }

        return $this->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return !$this->read;
    }

    /**
     * Get notification message attribute (backward compatibility).
     */
    public function getMessageAttribute(): string
    {
        return $this->content ?? '';
    }

    /**
     * Get notification action URL from data.
     */
    public function getActionUrlAttribute(): ?string
    {
        return $this->data['action_url'] ?? null;
    }

    /**
     * Get priority color for UI.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'text-success',
            self::PRIORITY_MEDIUM => 'text-info',
            self::PRIORITY_HIGH => 'text-warning',
            self::PRIORITY_URGENT => 'text-danger',
            default => 'text-info'
        };
    }

    /**
     * Get type icon for UI with comprehensive mapping.
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            // Activity related
            self::TYPE_ACTIVITY_SCHEDULED => 'fas fa-calendar-plus',
            self::TYPE_ACTIVITY_CANCELLED => 'fas fa-calendar-times',
            self::TYPE_ACTIVITY_RESCHEDULED => 'fas fa-calendar-alt',
            self::TYPE_LOW_ENROLLMENT => 'fas fa-exclamation-triangle',
            self::TYPE_SESSION_REMINDER => 'fas fa-clock',
            self::TYPE_ATTENDANCE_MISSING => 'fas fa-user-clock',
            self::TYPE_ACTIVITY_APPROVAL_PENDING => 'fas fa-hourglass-half',
            
            // Trainee related
            self::TYPE_TRAINEE_ENROLLED => 'fas fa-user-plus',
            self::TYPE_TRAINEE_WITHDRAWN => 'fas fa-user-minus',
            self::TYPE_TRAINEE_PROFILE_UPDATED => 'fas fa-user-edit',
            self::TYPE_PROGRESS_REPORT_DUE => 'fas fa-file-alt',
            self::TYPE_BIRTHDAY_REMINDER => 'fas fa-birthday-cake',
            
            // Staff related
            self::TYPE_NEW_STAFF_MEMBER => 'fas fa-users',
            self::TYPE_PROFILE_UPDATE_REQUEST => 'fas fa-edit',
            self::TYPE_LEAVE_REQUEST => 'fas fa-calendar-minus',
            
            // System related
            self::TYPE_ASSET_ASSIGNED => 'fas fa-box',
            self::TYPE_SCHEDULE_CONFLICT => 'fas fa-exclamation-circle',
            self::TYPE_SYSTEM_ANNOUNCEMENT => 'fas fa-bullhorn',
            
            // Legacy types for backward compatibility
            'activity' => 'fas fa-calendar-alt',
            'staff' => 'fas fa-users',
            'trainee' => 'fas fa-user-graduate',
            'system' => 'fas fa-cog',
            'general' => 'fas fa-info-circle',
            
            default => 'fas fa-bell'
        };
    }

    /**
     * Get time ago formatted string.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get priority badge class for UI.
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'badge-success',
            self::PRIORITY_MEDIUM => 'badge-info',
            self::PRIORITY_HIGH => 'badge-warning',
            self::PRIORITY_URGENT => 'badge-danger',
            default => 'badge-info'
        };
    }
}
