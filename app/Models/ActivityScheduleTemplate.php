<?php

namespace App\Models;

use App\Models\Scopes\CentreScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityScheduleTemplate extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

    protected $table = 'activity_schedule_templates';

    protected $fillable = [
        'template_name',
        'description',
        'sessions_per_week',
        'duration_weeks',
        'session_length_minutes',
        'days_of_week',
        'time_slots',
        'template_type',
        'is_active',
        'created_by',
        'centre_id'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'time_slots' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the centre this template belongs to
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id', 'centre_id');
    }

    /**
     * Get template applications
     */
    public function applications()
    {
        return $this->hasMany(ActivityTemplateApplication::class, 'template_id');
    }

    /**
     * Calculate total sessions for this template
     */
    public function getTotalSessionsAttribute()
    {
        return $this->sessions_per_week * $this->duration_weeks;
    }

    /**
     * Get template duration in days
     */
    public function getDurationDaysAttribute()
    {
        return $this->duration_weeks * 7;
    }

    /**
     * Generate sessions for an activity using this template
     */
    public function generateSessions($activity, $startDate, $customizations = [])
    {
        $startDate = Carbon::parse($startDate);
        $endDate = $startDate->copy()->addWeeks($this->duration_weeks);
        $sessions = [];
        
        $daysOfWeek = $customizations['days_of_week'] ?? $this->days_of_week;
        $timeSlots = $customizations['time_slots'] ?? $this->time_slots;
        $sessionLength = $customizations['session_length_minutes'] ?? $this->session_length_minutes;
        
        // Convert day names to Carbon day numbers
        $dayMapping = [
            'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4,
            'Friday' => 5, 'Saturday' => 6, 'Sunday' => 0
        ];
        
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            $dayName = $current->format('l');
            
            if (in_array($dayName, $daysOfWeek)) {
                foreach ($timeSlots as $timeSlot) {
                    $sessionStart = Carbon::parse($current->format('Y-m-d') . ' ' . $timeSlot['start']);
                    $sessionEnd = $sessionStart->copy()->addMinutes($sessionLength);
                    
                    $sessionCode = 'S-' . $activity->id . '-' . $current->format('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
                    
                    $sessionName = $activity->activity_name . ' - ' . $current->format('M j, Y');
                    
                    $sessions[] = [
                        'activity_id' => $activity->id,
                        'session_code' => $sessionCode,
                        'session_name' => $sessionName,
                        'session_date' => $current->format('Y-m-d'),
                        'scheduled_date' => $current->format('Y-m-d'),
                        'start_time' => $sessionStart->format('H:i:s'),
                        'session_start_time' => $sessionStart->format('H:i:s'),
                        'end_time' => $sessionEnd->format('H:i:s'),
                        'session_end_time' => $sessionEnd->format('H:i:s'),
                        'venue' => $activity->activity_location ?? 'TBD',
                        'room_number' => $customizations['room_number'] ?? null,
                        'max_participants' => $activity->max_participants ?? 20,
                        'teacher_id' => $customizations['teacher_id'] ?? $activity->created_by ?? null,
                        'instructor_id' => $customizations['instructor_id'] ?? $activity->created_by ?? null,
                        'status' => 'scheduled',
                        'color_code' => $activity->color_code ?? '#3498db',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
            
            $current->addDay();
        }
        
        return $sessions;
    }

    /**
     * Get predefined template types
     */
    public static function getTemplateTypes()
    {
        return [
            'weekly' => 'Weekly Pattern (1-2x per week)',
            'intensive' => 'Intensive (3-5x per week)',
            'flexible' => 'Flexible Schedule',
            'custom' => 'Custom Pattern'
        ];
    }

    /**
     * Get common day combinations
     */
    public static function getCommonDayCombinations()
    {
        return [
            'mwf' => ['Monday', 'Wednesday', 'Friday'],
            'tth' => ['Tuesday', 'Thursday'],
            'daily' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'weekend' => ['Saturday', 'Sunday'],
            'custom' => []
        ];
    }
}
