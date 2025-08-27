<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $table = 'volunteers';

    protected $fillable = [
        // Personal Information (actual database columns)
        'volunteer_id',
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'occupation',
        'skills',
        'availability',
        'motivation',
        'status',
        // Admin fields (actual database columns)
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected $hidden = [];

    /**
     * Relationships
     */
    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function centre()
    {
        return $this->hasOneThrough(
            Centre::class,          // Final model we want to reach
            User::class,            // Intermediate model (admin user)
            'id',                   // Foreign key on users table
            'centre_id',            // Foreign key on centres table  
            'reviewed_by',          // Local key on volunteers table
            'centre_id'             // Local key on users table
        );
    }

    /**
     * Get the centre name through the admin who approved this volunteer
     */
    public function getCentreNameAttribute()
    {
        return $this->centre ? $this->centre->centre_name : 'Unassigned';
    }

    /**
     * Get the centre ID through the admin who approved this volunteer
     */
    public function getCentreIdAttribute()
    {
        return $this->reviewedByUser ? $this->reviewedByUser->centre_id : null;
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'applied');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Scope methods can be added later if needed

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function getFormattedAvailabilityAttribute()
    {
        if (!$this->availability) {
            return 'Not specified';
        }

        return $this->availability;
    }

    // Accessor methods can be added later if needed


    public function approve($adminUserId, $notes = null)
    {
        $this->status = 'approved';
        $this->reviewed_by = $adminUserId;
        $this->review_notes = $notes;
        $this->reviewed_at = now();
        return $this->save();
    }

    public function reject($adminUserId, $notes = null)
    {
        $this->status = 'rejected';
        $this->reviewed_by = $adminUserId;
        $this->review_notes = $notes;
        $this->reviewed_at = now();
        return $this->save();
    }

    public function activate($adminUserId, $notes = null)
    {
        $this->status = 'active';
        $this->reviewed_by = $adminUserId;
        $this->review_notes = $notes;
        $this->reviewed_at = now();
        return $this->save();
    }


    public function getStatusBadgeColorAttribute()
    {
        $colors = [
            'applied' => 'info',
            'reviewed' => 'warning', 
            'approved' => 'success',
            'rejected' => 'danger',
            'active' => 'success',
            'inactive' => 'secondary'
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}
