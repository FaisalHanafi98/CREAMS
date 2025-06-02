<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Volunteers extends Model
{
use HasFactory;
/**
 * The table associated with the model.
 *
 * @var string
 */
protected $table = 'volunteer_applications';

/**
 * The attributes that are mass assignable.
 *
 * @var array<int, string>
 */
protected $fillable = [
    // Personal Information
    'name',
    'first_name',
    'last_name',
    'email',
    'phone',
    'address',
    'city',
    'postcode',
    
    // Volunteer Preferences
    'interest',
    'other_interest',
    'skills',
    'availability',
    'commitment',
    
    // Additional Information
    'motivation',
    'experience',
    'referral',
    
    // System fields
    'status',
    'centre_id',
    'ip_address',
    'user_agent',
    'submitted_at',
    'admin_notes',
    'reviewed_by',
    'reviewed_at',
];

/**
 * The attributes that should be cast.
 *
 * @var array<string, string>
 */
protected $casts = [
    'availability' => 'array',
    'submitted_at' => 'datetime',
    'reviewed_at' => 'datetime',
];

/**
 * The attributes that should be hidden for serialization.
 *
 * @var array<int, string>
 */
protected $hidden = [
    'ip_address',
    'user_agent',
];

/**
 * Get the centre that this volunteer application is associated with.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function centre()
{
    return $this->belongsTo(Centres::class, 'centre_id');
}

/**
 * Get the user who reviewed this application.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function reviewer()
{
    return $this->belongsTo(Users::class, 'reviewed_by');
}

/**
 * Scope a query to only include pending applications.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopePending($query)
{
    return $query->where('status', 'pending');
}

/**
 * Scope a query to only include approved applications.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeApproved($query)
{
    return $query->where('status', 'approved');
}

/**
 * Scope a query to only include rejected applications.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeRejected($query)
{
    return $query->where('status', 'rejected');
}

/**
 * Scope a query to filter by interest area.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @param  string  $interest
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeByInterest($query, $interest)
{
    return $query->where('interest', $interest);
}

/**
 * Set the name attribute with proper capitalization.
 *
 * @param  string  $value
 * @return void
 */
public function setNameAttribute($value)
{
    $this->attributes['name'] = ucwords(strtolower($value));
}

/**
 * Set the first name attribute with proper capitalization.
 *
 * @param  string  $value
 * @return void
 */
public function setFirstNameAttribute($value)
{
    $this->attributes['first_name'] = ucfirst(strtolower($value));
}

/**
 * Set the last name attribute with proper capitalization.
 *
 * @param  string  $value
 * @return void
 */
public function setLastNameAttribute($value)
{
    $this->attributes['last_name'] = ucfirst(strtolower($value));
}

/**
 * Get the formatted availability attribute.
 *
 * @return string
 */
public function getFormattedAvailabilityAttribute()
{
    if (!$this->availability) {
        return 'Not specified';
    }

    $availabilityMap = [
        'weekday' => 'Weekdays (9am-5pm)',
        'evening' => 'Evenings (5pm-9pm)',
        'weekend' => 'Weekends'
    ];

    $formatted = array_map(function($item) use ($availabilityMap) {
        return $availabilityMap[$item] ?? $item;
    }, $this->availability);

    return implode(', ', $formatted);
}

/**
 * Get the formatted interest attribute.
 *
 * @return string
 */
public function getFormattedInterestAttribute()
{
    $interestMap = [
        'direct-support' => 'Direct Support',
        'skills-sharing' => 'Skills Sharing',
        'event-support' => 'Event Support',
        'creative-arts' => 'Creative Arts',
        'administrative' => 'Administrative Support',
        'advocacy' => 'Advocacy & Outreach',
        'other' => 'Other'
    ];

    $interest = $interestMap[$this->interest] ?? $this->interest;
    
    if ($this->interest === 'other' && $this->other_interest) {
        $interest .= ' (' . $this->other_interest . ')';
    }

    return $interest;
}

/**
 * Get the formatted commitment attribute.
 *
 * @return string
 */
public function getFormattedCommitmentAttribute()
{
    $commitmentMap = [
        '1-3' => '1-3 hours per week',
        '4-6' => '4-6 hours per week',
        '7-10' => '7-10 hours per week',
        'flexible' => 'Flexible/As needed'
    ];

    return $commitmentMap[$this->commitment] ?? $this->commitment;
}

/**
 * Approve this volunteer application.
 *
 * @return bool
 */
public function approve()
{
    $this->status = 'approved';
    $this->reviewed_at = now();
    $this->reviewed_by = session('id');
    return $this->save();
}

/**
 * Reject this volunteer application.
 *
 * @return bool
 */
public function reject()
{
    $this->status = 'rejected';
    $this->reviewed_at = now();
    $this->reviewed_by = session('id');
    return $this->save();
}

/**
 * Mark this application as contacted.
 *
 * @return bool
 */
public function markAsContacted()
{
    $this->status = 'contacted';
    $this->reviewed_at = now();
    $this->reviewed_by = session('id');
    return $this->save();
}

/**
 * Get the status badge color.
 *
 * @return string
 */
public function getStatusBadgeColorAttribute()
{
    $colors = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'contacted' => 'info'
    ];

    return $colors[$this->status] ?? 'secondary';
}
}