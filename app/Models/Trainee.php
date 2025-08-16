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
        'trainee_id',
        'trainee_first_name',
        'trainee_last_name',
        'trainee_email',
        'trainee_phone_number',
        'trainee_date_of_birth',
        'gender',
        'trainee_address',
        'avatar',
        'trainee_condition',
        'centre_name',
        'centre_id',
        'ic_number',
        'status',
        'course_id',
        // Guardian information
        'guardian_name',
        'guardian_phone',
        'guardian_email', 
        'guardian_relationship',
        'guardian_address',
        // Emergency contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        // Additional fields
        'medical_history',
        'additional_notes',
        // Consent fields
        'photo_consent',
        'services_consent',
        // Enhanced fields for data integrity
        'admission_date',
        'graduation_date',
        'medical_info',
        'assessment_data',
        'tags',
        'guardian_info',
        'emergency_contact',
        'last_updated_by',
        // Backwards compatibility aliases
        'name', // Maps to full name
        'email', // Maps to trainee_email
        'phone', // Maps to trainee_phone_number
        'date_of_birth', // Maps to trainee_date_of_birth
        'address', // Maps to trainee_address
        'condition', // Maps to trainee_condition
    ];
    
    /**
     * The attributes that should be guarded from mass assignment.
     * These fields require explicit authorization to modify.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'trainee_last_accessed_at', // System-managed field
        // 'trainee_attendance' field removed - doesn't exist in database
        'course_id',               // Requires enrollment process
        'status',                  // Status changes require approval
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trainee_date_of_birth' => 'date',
        'trainee_last_accessed_at' => 'datetime',
        // Enhanced casts for new fields
        'admission_date' => 'date',
        'graduation_date' => 'date',
        'medical_info' => 'array',
        'assessment_data' => 'array',
        'tags' => 'array',
        'guardian_info' => 'array',
        'emergency_contact' => 'array',
        'photo_consent' => 'boolean',
        'services_consent' => 'boolean',
        'date_of_birth' => 'date', // Compatibility
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
            // Check if path already starts with 'storage/'
            if (strpos($this->avatar, 'storage/') === 0) {
                // Path already includes storage/, just add asset()
                return asset($this->avatar);
            } elseif (strpos($this->avatar, 'trainee_avatars/') !== false) {
                // Path has trainee_avatars but no storage prefix
                return asset('storage/' . $this->avatar);
            } else {
                // Just filename, add full path
                return asset('storage/trainee_avatars/' . $this->avatar);
            }
        }
        
        // Return default avatar
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
     * Get the activities associated with the trainee through enrollments.
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_enrollments', 'trainee_id', 'activity_id')
                    ->withPivot(['enrollment_date', 'enrollment_status', 'enrollment_notes', 'progress_percentage', 'attendance_count', 'completion_date', 'completion_notes', 'enrolled_by'])
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
     * Get the session enrollments for the trainee.
     */
    public function sessionEnrollments()
    {
        return $this->hasMany(SessionEnrollment::class, 'trainee_id');
    }

    /**
     * Get the attendance records associated with the trainee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'trainee_id');
    }

    /**
     * Get the session attendance records for the trainee.
     */
    public function sessionAttendances()
    {
        return $this->hasMany(SessionAttendance::class, 'trainee_id');
    }

    /**
     * Get the course that the trainee is enrolled in.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the centre that the trainee belongs to.
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_name', 'centre_name');
    }

    /**
     * Get the classes that the trainee is enrolled in.
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_trainee', 'trainee_id', 'class_id')
                    ->withTimestamps();
    }

    /**
     * Get the trainee's competency progress records.
     */
    public function competencyProgress()
    {
        return $this->hasMany(TraineeCompetencyProgress::class);
    }

    /**
     * Get competency progress for a specific activity
     */
    public function competencyProgressForActivity($activityId)
    {
        return $this->competencyProgress()
                    ->whereHas('learningOutcome', function ($query) use ($activityId) {
                        $query->where('activity_id', $activityId);
                    });
    }

    /**
     * Get the trainee's education plans (IEPs)
     */
    public function educationPlans()
    {
        return $this->hasMany(TraineeEducationPlan::class);
    }

    /**
     * Get the trainee's active education plan
     */
    public function activeEducationPlan()
    {
        return $this->hasOne(TraineeEducationPlan::class)->where('status', 'Active');
    }

    /**
     * Get the trainee's progress reports
     */
    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class);
    }

    /**
     * Get progress reports accessible to parents/guardians
     */
    public function parentAccessibleReports()
    {
        return $this->hasMany(ProgressReport::class)
                    ->where('parent_accessible', true)
                    ->where('status', 'Shared');
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
     * Calculate academic progress percentage for a specific activity based on time and attendance
     * Your logic: Activity 7/31/2025 → 12/31/2025, 2 sessions/week = 40 total sessions, each attendance = 2.5%
     */
    public function calculateActivityProgress($activity)
    {
        // Use actual attendance data from attendances table
        $totalSessions = $this->attendances()
            ->where('activity_id', $activity->id)
            ->count();

        if ($totalSessions <= 0) {
            return 0; // No sessions recorded yet
        }

        // Get attended sessions (present + late)
        $attendedSessions = $this->attendances()
            ->where('activity_id', $activity->id)
            ->whereIn('status', ['present', 'late'])
            ->count();

        // Calculate progress percentage based on attendance rate
        $progress = ($attendedSessions / $totalSessions) * 100;
        
        return min(round($progress, 1), 100); // Cap at 100%
    }

    /**
     * Calculate average progress across all enrolled activities
     */
    public function calculateAverageProgress()
    {
        $enrolled_activities = $this->activities;
        
        if ($enrolled_activities->isEmpty()) {
            return 0;
        }

        $total_progress = 0;
        $activities_with_progress = 0;

        foreach ($enrolled_activities as $activity) {
            $progress = $this->calculateActivityProgress($activity);
            if ($progress > 0) {
                $total_progress += $progress;
                $activities_with_progress++;
            }
        }

        return $activities_with_progress > 0 ? round($total_progress / $activities_with_progress, 1) : 0;
    }

    /**
     * Calculate comprehensive attendance statistics for trainee
     */
    public function getAttendanceStatistics($activityId = null, $dateFrom = null, $dateTo = null)
    {
        $query = $this->attendances();
        
        if ($activityId) {
            $query->where('activity_id', $activityId);
        }
        
        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }
        
        $attendances = $query->get();
        
        $stats = [
            'total_sessions' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'attendance_rate' => 0,
            'meets_threshold' => false,
            'threshold_percentage' => 50
        ];
        
        if ($stats['total_sessions'] > 0) {
            $attended = $stats['present'] + $stats['late'];
            $stats['attendance_rate'] = round(($attended / $stats['total_sessions']) * 100, 2);
            $stats['meets_threshold'] = $stats['attendance_rate'] >= $stats['threshold_percentage'];
        }
        
        return $stats;
    }

    /**
     * Check if trainee meets 50% attendance threshold for specific activity
     */
    public function meetsAttendanceThreshold($activityId, $threshold = 50)
    {
        $stats = $this->getAttendanceStatistics($activityId);
        return $stats['attendance_rate'] >= $threshold;
    }

    /**
     * Get overall attendance average across all activities
     */
    public function getOverallAttendanceAverage()
    {
        $activities = $this->activities;
        
        if ($activities->isEmpty()) {
            return 0;
        }
        
        $totalRate = 0;
        $activityCount = 0;
        
        foreach ($activities as $activity) {
            $stats = $this->getAttendanceStatistics($activity->id);
            if ($stats['total_sessions'] > 0) {
                $totalRate += $stats['attendance_rate'];
                $activityCount++;
            }
        }
        
        return $activityCount > 0 ? round($totalRate / $activityCount, 2) : 0;
    }

    /**
     * Get activities where trainee doesn't meet attendance threshold
     */
    public function getActivitiesBelowThreshold($threshold = 50)
    {
        $activities = $this->activities;
        $belowThreshold = [];
        
        foreach ($activities as $activity) {
            $stats = $this->getAttendanceStatistics($activity->id);
            if ($stats['total_sessions'] > 0 && $stats['attendance_rate'] < $threshold) {
                $belowThreshold[] = [
                    'activity' => $activity,
                    'stats' => $stats
                ];
            }
        }
        
        return $belowThreshold;
    }

    // =============================================
    // ENHANCED RELATIONSHIPS
    // =============================================

    /**
     * Get the audit logs for the trainee
     */
    public function auditLogs()
    {
        return $this->hasMany(TraineeAuditLog::class);
    }

    /**
     * Get the documents for the trainee
     */
    public function documents()
    {
        return $this->hasMany(TraineeDocument::class);
    }

    /**
     * Get the progress records for the trainee
     */
    public function progress()
    {
        return $this->hasMany(TraineeProgress::class);
    }

    /**
     * Get the user who last updated this trainee
     */
    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // =============================================
    // ENHANCED ATTRIBUTES & ACCESSORS
    // =============================================

    /**
     * Get the trainee's name (compatibility accessor)
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Set the name attribute (compatibility mutator)
     */
    public function setNameAttribute($value)
    {
        // Split name into first and last name
        $nameParts = explode(' ', $value, 2);
        $this->trainee_first_name = $nameParts[0];
        $this->trainee_last_name = $nameParts[1] ?? '';
    }

    /**
     * Get email attribute (compatibility)
     */
    public function getEmailAttribute()
    {
        return $this->trainee_email;
    }

    /**
     * Set email attribute (compatibility)
     */
    public function setEmailAttribute($value)
    {
        $this->trainee_email = $value;
    }

    /**
     * Get phone attribute (compatibility)
     */
    public function getPhoneAttribute()
    {
        return $this->trainee_phone_number;
    }

    /**
     * Set phone attribute (compatibility)
     */
    public function setPhoneAttribute($value)
    {
        $this->trainee_phone_number = $value;
    }

    /**
     * Get date of birth attribute (compatibility)
     */
    public function getDateOfBirthAttribute()
    {
        return $this->trainee_date_of_birth;
    }

    /**
     * Set date of birth attribute (compatibility)
     */
    public function setDateOfBirthAttribute($value)
    {
        $this->trainee_date_of_birth = $value;
    }

    /**
     * Get address attribute (compatibility)
     */
    public function getAddressAttribute()
    {
        return $this->trainee_address;
    }

    /**
     * Set address attribute (compatibility)
     */
    public function setAddressAttribute($value)
    {
        $this->trainee_address = $value;
    }

    /**
     * Get condition attribute (compatibility)
     */
    public function getConditionAttribute()
    {
        return $this->trainee_condition;
    }

    /**
     * Set condition attribute (compatibility)
     */
    public function setConditionAttribute($value)
    {
        $this->trainee_condition = $value;
    }

    /**
     * Get the latest progress assessment
     */
    public function getLatestProgressAttribute()
    {
        return $this->progress()->latest('assessment_date')->first();
    }

    /**
     * Get average progress percentage
     */
    public function getAverageProgressAttribute()
    {
        return TraineeProgress::getAverageProgress($this->id);
    }

    /**
     * Check if trainee has expired documents
     */
    public function getHasExpiredDocumentsAttribute()
    {
        return $this->documents()->expired()->exists();
    }

    /**
     * Get documents expiring soon
     */
    public function getExpiringDocumentsAttribute()
    {
        return $this->documents()->expiringSoon()->get();
    }

    /**
     * Get guardian phone (from guardian_info or legacy field)
     */
    public function getGuardianPhoneAttribute()
    {
        if ($this->guardian_info && isset($this->guardian_info['guardian_phone'])) {
            return $this->guardian_info['guardian_phone'];
        }
        return $this->attributes['guardian_phone'] ?? null;
    }

    // =============================================
    // ENHANCED SCOPES
    // =============================================

    /**
     * Scope for trainees with upcoming birthdays
     */
    public function scopeUpcomingBirthdays($query, $days = 30)
    {
        return $query->whereRaw("
            DATE_ADD(
                DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(trainee_date_of_birth), '-', DAY(trainee_date_of_birth))),
                INTERVAL IF(
                    DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(trainee_date_of_birth), '-', DAY(trainee_date_of_birth))) < CURDATE(),
                    1,
                    0
                ) YEAR
            ) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ", [$days]);
    }

    /**
     * Scope for trainees by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('trainee_first_name', 'LIKE', "%{$search}%")
              ->orWhere('trainee_last_name', 'LIKE', "%{$search}%")
              ->orWhere('trainee_email', 'LIKE', "%{$search}%")
              ->orWhere('trainee_phone_number', 'LIKE', "%{$search}%")
              ->orWhere('guardian_name', 'LIKE', "%{$search}%")
              ->orWhere('guardian_phone', 'LIKE', "%{$search}%")
              ->orWhere('guardian_email', 'LIKE', "%{$search}%")
              ->orWhere('emergency_contact_name', 'LIKE', "%{$search}%")
              ->orWhere('emergency_contact_phone', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope for trainees needing progress assessment
     */
    public function scopeNeedsAssessment($query, $months = 3)
    {
        return $query->whereDoesntHave('progress', function($q) use ($months) {
            $q->where('assessment_date', '>', now()->subMonths($months));
        });
    }

    // =============================================
    // MODEL EVENTS & AUDIT TRAIL
    // =============================================

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate unique identifier and trainee_id on creation
        static::creating(function ($trainee) {
            // Generate trainee_id if not set
            if (!$trainee->trainee_id) {
                $year = date('Y');
                $centreId = $trainee->centre_id ?? '001';
                
                // Get the next sequence number for this year and centre
                $lastTrainee = static::where('trainee_id', 'LIKE', "TRN{$year}{$centreId}%")
                    ->orderBy('trainee_id', 'desc')
                    ->first();
                    
                if ($lastTrainee) {
                    $lastSequence = intval(substr($lastTrainee->trainee_id, -4));
                    $nextSequence = $lastSequence + 1;
                } else {
                    $nextSequence = 1;
                }
                
                $trainee->trainee_id = 'TRN' . $year . $centreId . sprintf('%04d', $nextSequence);
            }
            
            // Auto-set trainee_id if not provided
            if (!$trainee->trainee_id) {
                $trainee->trainee_id = $trainee->generateTraineeId();
            }
        });

        // Log updates
        static::updating(function ($trainee) {
            if ($trainee->isDirty()) {
                TraineeAuditLog::logAction(
                    $trainee->id,
                    'updated',
                    $trainee->getOriginal(),
                    $trainee->getDirty(),
                    'Trainee record updated'
                );
            }
        });

        // Log deletions
        static::deleting(function ($trainee) {
            TraineeAuditLog::logAction(
                $trainee->id,
                'deleted',
                $trainee->toArray(),
                null,
                'Trainee record deleted'
            );
        });
    }
}