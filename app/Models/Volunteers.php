<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteers extends Model
{
    use HasFactory;

    protected $table = 'volunteer_applications';

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
        
        // Admin fields
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'availability' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
    ];

    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Users::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByInterest($query, $interest)
    {
        return $query->where('interest', $interest);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst(strtolower($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst(strtolower($value));
    }

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

        $availability = is_string($this->availability) ? json_decode($this->availability, true) : $this->availability;

        $formatted = array_map(function($item) use ($availabilityMap) {
            return $availabilityMap[$item] ?? $item;
        }, $availability ?: []);

        return implode(', ', $formatted);
    }

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

    public function approve()
    {
        $this->status = 'approved';
        $this->reviewed_at = now();
        $this->reviewed_by = session('id');
        return $this->save();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->reviewed_at = now();
        $this->reviewed_by = session('id');
        return $this->save();
    }

    public function markAsContacted()
    {
        $this->status = 'contacted';
        $this->reviewed_at = now();
        $this->reviewed_by = session('id');
        return $this->save();
    }

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
