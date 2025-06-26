<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Trainees extends Model
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
     * Get the condition badge CSS class for displaying medical conditions.
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
            'ADHD' => 'success',
            'Learning Disabilities' => 'secondary',
            'Intellectual Disability' => 'danger',
            'Speech and Language Disorders' => 'light',
            'Hearing Impairment' => 'secondary',
            'Visual Impairment' => 'secondary',
            'Physical Disability' => 'dark',
            'Multiple Disabilities' => 'danger',
            'Others' => 'secondary'
        ];
        
        // Return the mapped badge class or default to secondary
        return $conditionMap[$this->trainee_condition] ?? 'secondary';
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
     * Get the profile associated with the trainee.
     */
    public function profile()
    {
        return $this->hasOne(TraineeProfile::class, 'trainee_id');
    }

    /**
     * Get the activities associated with the trainee through enrollments.
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_enrollments', 'trainee_id', 'activity_id')
                    ->withPivot(['enrollment_date', 'status', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Get the activity enrollments for the trainee.
     */
    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class, 'trainee_id');
    }

    /**
     * Get the attendance records associated with the trainee.
     */
    public function attendances()
    {
        return $this->hasMany(ActivityAttendance::class, 'trainee_id');
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
}