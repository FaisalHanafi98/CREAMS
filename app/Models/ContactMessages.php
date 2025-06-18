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
    // Contact Information
    'name',
    'email',    
    'phone',
    'organization',
    
    // Message Details
    'reason',
    'subject',
    'message',
    'urgency',
    'preferred_contact_method',
    
    // System fields
    'status',
    'ip_address',
    'user_agent',
    'referrer',
    'submitted_at',
    
    // Admin fields
    'assigned_to',
    'admin_notes',
    'response_sent_at',
    'resolved_at',
];

/**
 * The attributes that should be cast.
 *
 * @var array<string, string>
 */
protected $casts = [
    'submitted_at' => 'datetime',
    'response_sent_at' => 'datetime',
    'resolved_at' => 'datetime',
];

/**
 * The attributes that should be hidden for serialization.
 *
 * @var array<int, string>
 */
protected $hidden = [
    'ip_address',
    'user_agent',
    'referrer',
];

/**
 * Relationship with the user assigned to handle this message
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function assignedUser()
{
    return $this->belongsTo(Users::class, 'assigned_to');
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
 * Scope a query to only include urgent messages.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeUrgent($query)
{
    return $query->where('urgency', 'urgent');
}

/**
 * Scope a query to only include resolved messages.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeResolved($query)
{
    return $query->where('status', 'resolved');
}

/**
 * Scope a query to filter by reason.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @param  string  $reason
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeByReason($query, $reason)
{
    return $query->where('reason', $reason);
}

/**
 * Scope a query to filter by urgency.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @param  string  $urgency
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeByUrgency($query, $urgency)
{
    return $query->where('urgency', $urgency);
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
 * Get the formatted reason attribute.
 *
 * @return string
 */
public function getFormattedReasonAttribute()
{
    $reasonMap = [
        'services' => 'Rehabilitation Services',
        'support' => 'Support & Assistance',
        'volunteer' => 'Volunteer Inquiry',
        'partnership' => 'Partnership Opportunity',
        'general' => 'General Inquiry',
        'admission' => 'Admission Inquiry',
        'complaint' => 'Complaint',
        'feedback' => 'Feedback',
        'other' => 'Other'
    ];

    return $reasonMap[$this->reason] ?? ucfirst($this->reason);
}

/**
 * Get the formatted urgency attribute.
 *
 * @return string
 */
public function getFormattedUrgencyAttribute()
{
    return ucfirst($this->urgency);
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
        'in_progress' => 'warning',
        'resolved' => 'success',
        'closed' => 'secondary'
    ];

    return $colors[$this->status] ?? 'secondary';
}

/**
 * Get the urgency badge color.
 *
 * @return string
 */
public function getUrgencyBadgeColorAttribute()
{
    $colors = [
        'low' => 'secondary',
        'medium' => 'info',
        'high' => 'warning',
        'urgent' => 'danger'
    ];

    return $colors[$this->urgency] ?? 'secondary';
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
 * Check if message is urgent.
 *
 * @return bool
 */
public function isUrgent()
{
    return $this->urgency === 'urgent';
}

/**
 * Check if message is overdue for response.
 *
 * @return bool
 */
public function isOverdue()
{
    $hours = $this->isUrgent() ? 24 : 72; // 24 hours for urgent, 72 for others
    return $this->created_at->diffInHours(now()) > $hours && !in_array($this->status, ['resolved', 'closed']);
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
 * Mark as in progress.
 *
 * @return bool
 */
public function markAsInProgress()
{
    $this->status = 'in_progress';
    return $this->save();
}

/**
 * Mark as resolved.
 *
 * @return bool
 */
public function markAsResolved()
{
    $this->status = 'resolved';
    $this->resolved_at = now();
    return $this->save();
}

/**
 * Mark response as sent.
 *
 * @return bool
 */
public function markResponseSent()
{
    $this->response_sent_at = now();
    return $this->save();
}

/**
 * Assign to user.
 *
 * @param int $userId
 * @return bool
 */
public function assignTo($userId)
{
    $this->assigned_to = $userId;
    if ($this->status === 'new') {
        $this->status = 'read';
    }
    return $this->save();
}
}