<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteers extends Model
{
    use HasFactory;

    protected $table = 'volunteers';

    protected $fillable = [
        // Personal Information
        'volunteer_name',
        'volunteer_email',
        'volunteer_phone',
        'volunteer_address',
        'volunteer_birth_date',
        'volunteer_gender',
        'volunteer_skills',
        'volunteer_experience',
        'volunteer_availability',
        'volunteer_status',
        'volunteer_start_date',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'volunteer_availability' => 'array',
        'volunteer_birth_date' => 'date',
        'volunteer_start_date' => 'date',
    ];

    protected $hidden = [];

    // Relationships can be added later if needed


    public function scopePending($query)
    {
        return $query->where('volunteer_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('volunteer_status', 'active');
    }

    public function scopeRejected($query)
    {
        return $query->where('volunteer_status', 'inactive');
    }

    // Scope methods can be added later if needed

    public function setVolunteerNameAttribute($value)
    {
        $this->attributes['volunteer_name'] = ucwords(strtolower($value));
    }



    public function getFormattedAvailabilityAttribute()
    {
        if (!$this->volunteer_availability) {
            return 'Not specified';
        }

        return $this->volunteer_availability;
    }

    // Accessor methods can be added later if needed


    public function approve()
    {
        $this->volunteer_status = 'active';
        return $this->save();
    }

    public function reject()
    {
        $this->volunteer_status = 'inactive';
        return $this->save();
    }


    public function getStatusBadgeColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'active' => 'success',
            'inactive' => 'danger'
        ];

        return $colors[$this->volunteer_status] ?? 'secondary';
    }
}
