<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'alert_type',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime'
    ];

    // Relationships
    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeAssignedTo($query, $staffId)
    {
        return $query->where('assigned_to', $staffId);
    }

    public function scopeByType($query, $alertType)
    {
        return $query->where('alert_type', $alertType);
    }

    // Accessors
    public function getAlertTypeDisplayAttribute()
    {
        $types = [
            'consecutive_absences' => 'Consecutive Absences',
            'frequent_tardiness' => 'Frequent Tardiness',
            'pattern_concern' => 'Attendance Pattern Concern',
            'medical_concern' => 'Medical/Health Concern'
        ];

        return $types[$this->alert_type] ?? 'Unknown';
    }

    public function getPriorityDisplayAttribute()
    {
        return ucfirst($this->priority);
    }

    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }

    public function getDaysOverdueAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning', 
            'high' => 'danger',
            'urgent' => 'dark'
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    // Helper methods
    public function resolve($actionTaken = null, $resolvedBy = null)
    {
        $this->update([
            'status' => 'resolved',
            'action_taken' => $actionTaken,
            'resolved_at' => now()
        ]);
    }

    public function dismiss($dismissedBy = null)
    {
        $this->update([
            'status' => 'dismissed',
            'resolved_at' => now()
        ]);
    }

    public function assignTo($staffId, $notes = null)
    {
        $this->update([
            'assigned_to' => $staffId,
            'alert_description' => $notes ? $this->alert_description . "\n\nAssignment Notes: " . $notes : $this->alert_description
        ]);
    }

    public function escalate($newPriority = 'urgent')
    {
        if ($this->priority !== 'urgent') {
            $this->update([
                'priority' => $newPriority,
                'alert_description' => $this->alert_description . "\n\nEscalated on " . now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    // Static helper methods
    public static function createConsecutiveAbsenceAlert($traineeId, $activityId, $absenceCount, $createdBy)
    {
        $priority = $absenceCount >= 5 ? 'urgent' : ($absenceCount >= 3 ? 'high' : 'medium');
        
        return static::create([
            'trainee_id' => $traineeId,
            'activity_id' => $activityId,
            'alert_type' => 'consecutive_absences',
            'absence_count' => $absenceCount,
            'alert_description' => "Trainee has been absent for {$absenceCount} consecutive sessions. Immediate follow-up required.",
            'priority' => $priority,
            'status' => 'active',
            'created_by' => $createdBy
        ]);
    }

    public static function createTardinessAlert($traineeId, $activityId, $tardyCount, $createdBy)
    {
        $priority = $tardyCount >= 5 ? 'high' : 'medium';
        
        return static::create([
            'trainee_id' => $traineeId,
            'activity_id' => $activityId,
            'alert_type' => 'frequent_tardiness',
            'absence_count' => $tardyCount,
            'alert_description' => "Trainee has been late for {$tardyCount} sessions this month. Pattern monitoring required.",
            'priority' => $priority,
            'status' => 'active',
            'created_by' => $createdBy
        ]);
    }

    public static function createPatternConcernAlert($traineeId, $activityId, $description, $createdBy, $priority = 'medium')
    {
        return static::create([
            'trainee_id' => $traineeId,
            'activity_id' => $activityId,
            'alert_type' => 'pattern_concern',
            'alert_description' => $description,
            'priority' => $priority,
            'status' => 'active',
            'created_by' => $createdBy
        ]);
    }

    public static function checkAndCreateAlerts($traineeId, $activityId, $createdBy)
    {
        // Check for consecutive absences
        $recentAbsences = Attendance::where('trainee_id', $traineeId)
            ->where('activity_id', $activityId)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->where('status', 'absent')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $consecutiveAbsences = 0;
        $lastAttendanceDate = null;

        foreach ($recentAbsences as $absence) {
            if ($absence->status === 'absent') {
                $consecutiveAbsences++;
            } else {
                $lastAttendanceDate = $absence->date;
                break;
            }
        }

        // Create alert if 3 or more consecutive absences
        if ($consecutiveAbsences >= 3) {
            $existingAlert = static::where('trainee_id', $traineeId)
                ->where('activity_id', $activityId)
                ->where('alert_type', 'consecutive_absences')
                ->where('status', 'active')
                ->first();

            if (!$existingAlert) {
                static::createConsecutiveAbsenceAlert($traineeId, $activityId, $consecutiveAbsences, $createdBy);
            } elseif ($existingAlert->absence_count < $consecutiveAbsences) {
                $existingAlert->update([
                    'absence_count' => $consecutiveAbsences,
                    'priority' => $consecutiveAbsences >= 5 ? 'urgent' : 'high'
                ]);
            }
        }

        // Check for frequent tardiness (separate logic)
        $recentTardiness = Attendance::where('trainee_id', $traineeId)
            ->where('activity_id', $activityId)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->where('status', 'late')
            ->count();

        if ($recentTardiness >= 3) {
            $existingTardinessAlert = static::where('trainee_id', $traineeId)
                ->where('activity_id', $activityId)
                ->where('alert_type', 'frequent_tardiness')
                ->where('status', 'active')
                ->first();

            if (!$existingTardinessAlert) {
                static::createTardinessAlert($traineeId, $activityId, $recentTardiness, $createdBy);
            }
        }
    }
}