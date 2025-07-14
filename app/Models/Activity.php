<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'activity_name',
        'description',
        'category_id',
        'activity_type',
        'objectives',
        'materials_needed',
        'skills_developed',
        'age_group',
        'difficulty_level',
        'min_participants',
        'max_participants',
        'duration_minutes',
        'location_type',
        'requires_equipment',
        'equipment_list',
    ];
    
    /**
     * The attributes that should be guarded from mass assignment.
     * These fields require explicit authorization to modify.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'activity_code',       // System-generated unique code
        'category',           // Deprecated field, use category_id
        'is_active',          // Status requires admin approval
        'times_conducted',    // System-calculated field
        'average_rating',     // System-calculated field
        'created_by',         // Set automatically during creation
        'centre_id',          // Set based on user's centre assignment
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'skills_developed' => 'array',
        'equipment_list' => 'array',
        'is_active' => 'boolean',
        'requires_equipment' => 'boolean'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id', 'centre_id');
    }

    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    public function upcomingSessions()
    {
        return $this->sessions()
            ->where('scheduled_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date');
    }

    public function completedSessions()
    {
        return $this->sessions()->where('status', 'completed');
    }
    
    /**
     * Securely set activity as active/inactive (admin/supervisor only).
     *
     * @param bool $isActive
     * @param int $userId
     * @return bool
     */
    public function updateActiveStatus($isActive, $userId)
    {
        $this->is_active = $isActive;
        $this->updated_by = $userId;
        return $this->save();
    }
    
    /**
     * Assign activity to centre during creation.
     *
     * @param string $centreId
     * @param int $creatorId
     * @return bool
     */
    public function assignToCentre($centreId, $creatorId)
    {
        $this->centre_id = $centreId;
        $this->created_by = $creatorId;
        return $this->save();
    }
    
    /**
     * Generate unique activity code.
     *
     * @param string $centreId
     * @return string
     */
    public static function generateActivityCode($centreId)
    {
        $prefix = 'ACT-' . $centreId . '-';
        $lastActivity = static::where('activity_code', 'like', $prefix . '%')
            ->orderBy('activity_code', 'desc')
            ->first();
            
        if ($lastActivity) {
            $lastNumber = (int) substr($lastActivity->activity_code, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // New relationships for scheduling and enrollment
    public function schedules()
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function activeSchedules()
    {
        return $this->schedules()->where('status', 'active');
    }

    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class);
    }

    public function activeEnrollments()
    {
        return $this->enrollments()->whereIn('status', ['enrolled', 'active']);
    }

    public function trainees()
    {
        return $this->belongsToMany(Trainees::class, 'activity_enrollments', 'activity_id', 'trainee_id')
                    ->withPivot(['enrollment_date', 'status', 'notes'])
                    ->withTimestamps();
    }

    public function teacher()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForAgeGroup($query, $age)
    {
        return $query->where('age_group', 'LIKE', "%{$age}%");
    }

    // Accessors
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }
        return $minutes . 'm';
    }

    public function getParticipantRangeAttribute()
    {
        if ($this->min_participants == $this->max_participants) {
            return $this->min_participants . ' participants';
        }
        return $this->min_participants . '-' . $this->max_participants . ' participants';
    }
}