<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'type',
        'title',
        'content',
        'data',
        'read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        $this->read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Determine if the notification is read.
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * Get the user's name.
     *
     * @return string
     */
    public function getUserNameAttribute()
    {
        switch ($this->user_type) {
            case 'admin':
                $user = Admins::find($this->user_id);
                break;
            case 'supervisor':
                $user = Supervisors::find($this->user_id);
                break;
            case 'teacher':
                $user = Teachers::find($this->user_id);
                break;
            case 'ajk':
                $user = AJKs::find($this->user_id);
                break;
            default:
                return 'Unknown User';
        }

        return $user ? $user->name : 'Unknown User';
    }

    /**
     * Get the notification icon.
     *
     * @return string
     */
    public function getIconAttribute()
    {
        switch ($this->type) {
            case 'message':
                return 'fas fa-envelope';
            case 'activity':
                return 'fas fa-calendar-alt';
            case 'trainee':
                return 'fas fa-user-graduate';
            case 'asset':
                return 'fas fa-boxes';
            case 'system':
                return 'fas fa-cog';
            default:
                return 'fas fa-bell';
        }
    }

    /**
     * Get the notification color.
     *
     * @return string
     */
    public function getColorAttribute()
    {
        switch ($this->type) {
            case 'message':
                return 'primary'; // Blue
            case 'activity':
                return 'success'; // Green
            case 'trainee':
                return 'info'; // Light blue
            case 'asset':
                return 'warning'; // Yellow
            case 'system':
                return 'danger'; // Red
            default:
                return 'secondary'; // Gray
        }
    }
}