<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Trainee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainee_first_name',
        'trainee_last_name',
        'trainee_email',
        'trainee_phone_number',
        'trainee_date_of_birth',
        'trainee_last_accessed_at',
        'centre_name',
        'avatar',                 // Standard avatar field
        'trainee_attendance',
        'trainee_condition',
        'course_id',
        // Enhanced registration fields
        'medical_condition',
        'medical_history',
        'doctor_name',
        'doctor_contact',
        'special_requirements',
        'guardian_name',
        'guardian_relationship',
        'guardian_email',
        'guardian_phone',
        'guardian_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'preferred_activities',
        'additional_notes',
        'referral_source',
        'data_consent',
        'registration_completed_at',
        'registration_status',
        'photo_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trainee_date_of_birth' => 'date',
        'trainee_last_accessed_at' => 'datetime',
        'trainee_attendance' => 'integer',
        'preferred_activities' => 'array',
        'data_consent' => 'boolean',
        'registration_completed_at' => 'datetime',
    ];

    /**
     * Get the trainee's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->trainee_first_name} {$this->trainee_last_name}";
    }

    /**
     * Get the trainee's age.
     *
     * @return int|null
     */
    public function getAgeAttribute()
    {
        if ($this->trainee_date_of_birth) {
            return Carbon::parse($this->trainee_date_of_birth)->age;
        }
        
        return null;
    }

    /**
     * Get the avatar URL for the trainee.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        // Return default avatar based on gender if available
        if (isset($this->trainee_gender) && $this->trainee_gender == 'female') {
            return asset('images/default-female-avatar.png');
        }
        
        return asset('images/default-avatar.png');
    }

    /**
     * Get the badge class for the trainee's condition.
     *
     * @return string
     */
    public function getConditionBadgeClassAttribute()
    {
        // Map conditions to Bootstrap badge classes
        $conditionMap = [
            'Autism Spectrum Disorder' => 'info',
            'Down Syndrome' => 'primary',
            'Cerebral Palsy' => 'warning',
            'Hearing Impairment' => 'secondary',
            'Visual Impairment' => 'secondary',
            'Intellectual Disability' => 'danger',
            'Physical Disability' => 'dark',
            'Speech and Language Disorder' => 'light',
            'Learning Disability' => 'success',
            'Multiple Disabilities' => 'danger',
            'Others' => 'secondary'
        ];
        
        // Return the mapped badge class or default to secondary
        return $conditionMap[$this->trainee_condition] ?? 'secondary';
    }

    /**
     * Get the profile associated with the trainee.
     */
    public function profile()
    {
        return $this->hasOne(TraineeProfile::class, 'trainee_id');
    }

    /**
     * Get the activities associated with the trainee.
     */
    public function activities()
    {
        return $this->hasMany(TraineeActivities::class, 'trainee_id');
    }

    /**
     * Get the attendance records associated with the trainee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'trainee_id');
    }

    /**
     * Get the course that the trainee is enrolled in.
     */
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    /**
     * Get the centre that the trainee belongs to.
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_name', 'centre_name');
    }

    /**
     * Get the classes that the trainee is enrolled in.
     */
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_trainee', 'trainee_id', 'class_id')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active trainees.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include trainees of a specific centre.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $centreName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCentre($query, $centreName)
    {
        return $query->where('centre_name', $centreName);
    }

    /**
     * Scope a query to only include trainees of a specific course.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $courseId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope a query to only include trainees with a specific condition.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('trainee_condition', $condition);
    }

    /**
     * Scope a query to filter by registration status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRegistrationStatus($query, $status)
    {
        return $query->where('registration_status', $status);
    }

    /**
     * Get the photo URL for the trainee.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/trainee_photos/' . $this->photo_path);
        }
        
        return $this->avatar_url; // Fall back to avatar
    }

    /**
     * Get the guardian's full contact information.
     *
     * @return array
     */
    public function getGuardianContactAttribute()
    {
        return [
            'name' => $this->guardian_name,
            'relationship' => $this->guardian_relationship,
            'email' => $this->guardian_email,
            'phone' => $this->guardian_phone,
            'address' => $this->guardian_address,
        ];
    }

    /**
     * Get the emergency contact information.
     *
     * @return array
     */
    public function getEmergencyContactAttribute()
    {
        return [
            'name' => $this->emergency_contact_name,
            'phone' => $this->emergency_contact_phone,
            'relationship' => $this->emergency_contact_relationship,
        ];
    }

    /**
     * Check if registration is complete.
     *
     * @return bool
     */
    public function isRegistrationComplete()
    {
        return $this->registration_status === 'approved' && 
               $this->registration_completed_at !== null;
    }

    /**
     * Mark registration as complete.
     *
     * @return bool
     */
    public function completeRegistration()
    {
        return $this->update([
            'registration_status' => 'approved',
            'registration_completed_at' => now()
        ]);
    }

    /**
     * Get preferred activities as formatted list.
     *
     * @return array
     */
    public function getFormattedPreferredActivitiesAttribute()
    {
        if (!$this->preferred_activities) {
            return [];
        }

        $activityMap = [
            'speech_therapy' => 'Speech Therapy',
            'occupational_therapy' => 'Occupational Therapy',
            'physical_therapy' => 'Physical Therapy',
            'behavioral_therapy' => 'Behavioral Therapy',
            'sensory_integration' => 'Sensory Integration',
            'communication_skills' => 'Communication Skills',
        ];

        return array_map(function($activity) use ($activityMap) {
            return $activityMap[$activity] ?? $activity;
        }, $this->preferred_activities);
    }
}