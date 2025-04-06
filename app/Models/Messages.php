<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'sender_type',
        'recipient_id',
        'recipient_type',
        'subject',
        'content',
        'read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the sender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sender()
    {
        return $this->morphTo('sender', 'sender_type', 'sender_id');
    }

    /**
     * Get the recipient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recipient()
    {
        return $this->morphTo('recipient', 'recipient_type', 'recipient_id');
    }

    /**
     * Scope a query to only include unread messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Mark the message as read.
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
     * Determine if the message is read.
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * Get the sender name.
     *
     * @return string
     */
    public function getSenderNameAttribute()
    {
        switch ($this->sender_type) {
            case 'admin':
                $user = Admins::find($this->sender_id);
                break;
            case 'supervisor':
                $user = Supervisors::find($this->sender_id);
                break;
            case 'teacher':
                $user = Teachers::find($this->sender_id);
                break;
            case 'ajk':
                $user = AJKs::find($this->sender_id);
                break;
            default:
                return 'Unknown User';
        }

        return $user ? $user->name : 'Unknown User';
    }

    /**
     * Get the recipient name.
     *
     * @return string
     */
    public function getRecipientNameAttribute()
    {
        switch ($this->recipient_type) {
            case 'admin':
                $user = Admins::find($this->recipient_id);
                break;
            case 'supervisor':
                $user = Supervisors::find($this->recipient_id);
                break;
            case 'teacher':
                $user = Teachers::find($this->recipient_id);
                break;
            case 'ajk':
                $user = AJKs::find($this->recipient_id);
                break;
            default:
                return 'Unknown User';
        }

        return $user ? $user->name : 'Unknown User';
    }
}