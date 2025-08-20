<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ContactMessages extends Model
{
use HasFactory;
/**
 * The table associated with the model.
 *
 * @var string
 */
protected $table = 'contact_messages';

/**
 * The attributes that are mass assignable.
 *
 * @var array<int, string>
 */
protected $fillable = [
    // Contact Information (actual database columns)
    'name',
    'email',    
    'phone',
    'subject',
    'message',
    'status',
    
    // Admin fields (actual database columns)
    'replied_by',
    'replied_at',
    'reply_message',
];

/**
 * The attributes that should be cast.
 *
 * @var array<string, string>
 */
protected $casts = [
    'replied_at' => 'datetime',
];

/**
 * The attributes that should be hidden for serialization.
 *
 * @var array<int, string>
 */
protected $hidden = [
    // No fields to hide for this table
];

/**
 * Relationship with the user assigned to handle this message
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function repliedByUser()
{
    return $this->belongsTo(User::class, 'replied_by');
}

/**
 * Scope a query to only include new messages.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeNew($query)
{
    return $query->where('status', 'new');
}

/**
 * Scope a query to only include closed messages.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeClosed($query)
{
    return $query->where('status', 'closed');
}

/**
 * Scope a query to only include replied messages.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeReplied($query)
{
    return $query->where('status', 'replied');
}

/**
 * Scope a query to filter by status.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @param  string  $status
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeByStatus($query, $status)
{
    return $query->where('status', $status);
}

/**
 * Set the name attribute with proper capitalization.
 *
 * @param  string  $value
 * @return void
 */
public function setNameAttribute($value)
{
    $this->attributes['name'] = ucwords(strtolower(trim($value)));
}

/**
 * Set the email attribute in lowercase.
 *
 * @param  string  $value
 * @return void
 */
public function setEmailAttribute($value)
{
    $this->attributes['email'] = strtolower(trim($value));
}

/**
 * Get the status badge color.
 *
 * @return string
 */
public function getStatusBadgeColorAttribute()
{
    $colors = [
        'new' => 'primary',
        'read' => 'info',
        'replied' => 'success',
        'closed' => 'secondary'
    ];

    return $colors[$this->status] ?? 'secondary';
}

/**
 * Get time since submission.
 *
 * @return string
 */
public function getTimeSinceSubmissionAttribute()
{
    return $this->created_at->diffForHumans();
}

/**
 * Check if message has been replied to.
 *
 * @return bool
 */
public function isReplied()
{
    return $this->status === 'replied';
}

/**
 * Check if message is overdue for response.
 *
 * @return bool
 */
public function isOverdue()
{
    $hours = 72; // 72 hours for response
    return $this->created_at->diffInHours(now()) > $hours && !in_array($this->status, ['replied', 'closed']);
}

/**
 * Mark as read.
 *
 * @return bool
 */
public function markAsRead()
{
    if ($this->status === 'new') {
        $this->status = 'read';
        return $this->save();
    }
    return true;
}

/**
 * Mark as replied.
 *
 * @param int $userId
 * @param string $replyMessage
 * @return bool
 */
public function markAsReplied($userId, $replyMessage)
{
    $this->status = 'replied';
    $this->replied_by = $userId;
    $this->replied_at = now();
    $this->reply_message = $replyMessage;
    return $this->save();
}

/**
 * Mark as closed.
 *
 * @return bool
 */
public function markAsClosed()
{
    $this->status = 'closed';
    return $this->save();
}
}