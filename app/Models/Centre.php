<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Centre extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'centre_id';

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'centre_id',
        'centre_name',
        'centre_address',
        'centre_phone',
        'centre_email',
        'centre_capacity',
        'centre_manager',
        'centre_manager_contact',
        'centre_status',
        'centre_description',
        'centre_facilities',
        'centre_image',
        'centre_latitude',
        'centre_longitude',
        'is_active',
        'attendance_policies'
    ];

    /**
     * Get the users associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the trainees associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainees()
    {
        return $this->hasMany(Trainee::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the courses associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'location_id', 'centre_id');
    }

    /**
     * Get the assets associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the activities associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'centre_id', 'centre_id');
    }

    /**
     * Scope a query to only include active centres.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'facilities' => 'array',
        'attendance_policies' => 'array'
    ];

    /**
     * Get all centres as a key-value array for dropdown lists
     *
     * @param bool $activeOnly Whether to include only active centres
     * @return array
     */
    public static function getForDropdown($activeOnly = true)
    {
        $query = self::orderBy('centre_name');
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->pluck('centre_name', 'centre_id')->toArray();
    }

    /**
     * Get the default centre (Gombak)
     *
     * @return Centre|null
     */
    public static function getDefault()
    {
        return self::find('01');
    }

    /**
     * Get centre name accessor
     */
    public function getNameAttribute()
    {
        return $this->centre_name;
    }

    /**
     * Get default attendance policies for centre
     *
     * @return array
     */
    public function getDefaultAttendancePolicies()
    {
        return [
            'office_hours' => [
                'start_time' => '08:00',
                'end_time' => '17:00',
                'late_threshold_minutes' => 15,
                'early_departure_threshold_minutes' => 30
            ],
            'work_days' => [
                'monday' => true,
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
                'friday' => true,
                'saturday' => false,
                'sunday' => false
            ],
            'attendance_rules' => [
                'require_check_in' => true,
                'require_check_out' => true,
                'allow_self_marking' => true,
                'auto_mark_absent_after_hours' => 2,
                'minimum_hours_per_day' => 8
            ],
            'leave_policies' => [
                'sick_leave_require_approval' => false,
                'emergency_leave_require_approval' => true,
                'authorized_leave_require_approval' => true,
                'advance_notice_hours' => 24
            ]
        ];
    }

    /**
     * Get attendance policies for this centre
     *
     * @return array
     */
    public function getAttendancePolicies()
    {
        $defaultPolicies = $this->getDefaultAttendancePolicies();
        $centrePolicies = $this->attendance_policies ?? [];
        
        return array_merge_recursive($defaultPolicies, $centrePolicies);
    }

    /**
     * Check if user is late based on centre policies
     *
     * @param string $checkInTime
     * @return bool
     */
    public function isLateCheckIn($checkInTime)
    {
        $policies = $this->getAttendancePolicies();
        $officeStartTime = $policies['office_hours']['start_time'];
        $lateThreshold = $policies['office_hours']['late_threshold_minutes'];
        
        $checkInCarbon = \Carbon\Carbon::parse($checkInTime);
        $expectedTime = \Carbon\Carbon::parse($checkInCarbon->format('Y-m-d') . ' ' . $officeStartTime);
        $lateThresholdTime = $expectedTime->addMinutes($lateThreshold);
        
        return $checkInCarbon->greaterThan($lateThresholdTime);
    }

    /**
     * Check if user is leaving early based on centre policies
     *
     * @param string $checkOutTime
     * @return bool
     */
    public function isEarlyCheckOut($checkOutTime)
    {
        $policies = $this->getAttendancePolicies();
        $officeEndTime = $policies['office_hours']['end_time'];
        $earlyThreshold = $policies['office_hours']['early_departure_threshold_minutes'];
        
        $checkOutCarbon = \Carbon\Carbon::parse($checkOutTime);
        $expectedTime = \Carbon\Carbon::parse($checkOutCarbon->format('Y-m-d') . ' ' . $officeEndTime);
        $earlyThresholdTime = $expectedTime->subMinutes($earlyThreshold);
        
        return $checkOutCarbon->lessThan($earlyThresholdTime);
    }

    /**
     * Check if attendance is allowed for specific day
     *
     * @param string $date
     * @return bool
     */
    public function isWorkDay($date)
    {
        $policies = $this->getAttendancePolicies();
        $dayOfWeek = \Carbon\Carbon::parse($date)->format('l'); // Full day name
        $dayKey = strtolower($dayOfWeek);
        
        return $policies['work_days'][$dayKey] ?? false;
    }

    /**
     * Get staff attendances for this centre
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staffAttendances()
    {
        return $this->hasMany(StaffAttendance::class, 'centre_id', 'centre_id');
    }
}