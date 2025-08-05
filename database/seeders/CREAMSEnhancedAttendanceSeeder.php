<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Attendance;
use App\Models\User;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CREAMSEnhancedAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds with realistic attendance patterns
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating comprehensive attendance data...');

        try {
            DB::beginTransaction();

            // Get all trainees and sessions
            $trainees = Trainee::all();
            $sessions = ActivitySession::with('activity')->get();
            $staff = User::whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])->get();

            $this->command->info("📊 Processing {$trainees->count()} trainees across {$sessions->count()} sessions");

            // Generate realistic attendance patterns for last 3 months
            $this->generateTraineeAttendance($trainees, $sessions);
            $this->generateStaffAttendance($staff, $sessions);

            DB::commit();

            $this->command->info('✅ Enhanced attendance data created successfully!');
            $this->showAttendanceStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enhanced attendance seeding failed: ' . $e->getMessage());
            $this->command->error('❌ Failed to create attendance data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate realistic trainee attendance patterns
     */
    private function generateTraineeAttendance($trainees, $sessions)
    {
        $this->command->info('👥 Creating trainee attendance patterns...');

        foreach ($trainees as $trainee) {
            // Create different attendance personalities
            $attendanceProfile = $this->getAttendanceProfile($trainee);
            
            foreach ($sessions as $session) {
                // Only create attendance for sessions in trainee's centre
                if ($session->activity->centre_id !== $trainee->centre_id) {
                    continue;
                }

                // Generate attendance for past sessions only
                if ($session->session_date > Carbon::now()) {
                    continue;
                }

                // Create session enrollment first
                $enrollment = SessionEnrollment::firstOrCreate([
                    'session_id' => $session->id,
                    'trainee_id' => $trainee->id,
                ], [
                    'enrollment_date' => $session->session_date->subDays(rand(1, 7)),
                    'enrollment_status' => 'enrolled', // Use 'enrolled' instead of 'confirmed'
                    'enrolled_by' => User::where('role', 'admin')->first()->id ?? 1,
                ]);

                // Generate attendance based on profile
                $attendanceStatus = $this->generateAttendanceStatus($attendanceProfile);
                
                // Update enrollment with attendance
                $enrollment->update([
                    'attendance_status' => $attendanceStatus,
                    'participation_score' => $this->generateParticipationScore($attendanceStatus),
                    'progress_notes' => $this->generateProgressNotes($attendanceStatus, $trainee),
                    'checked_in_at' => $attendanceStatus === 'present' ? 
                        $session->session_date->copy()->addHours(rand(8, 10))->addMinutes(rand(0, 59)) : null,
                ]);

                // Also create attendance record for backward compatibility
                Attendance::updateOrCreate([
                    'trainee_id' => $trainee->id,
                    'date' => $session->session_date,
                ], [
                    'status' => $attendanceStatus,
                    'marked_by' => User::where('role', 'teacher')->inRandomOrder()->first()->id ?? 1,
                    'remarks' => $this->generateProgressNotes($attendanceStatus, $trainee),
                    'activity_id' => $session->activity_id,
                ]);
            }
        }
    }

    /**
     * Generate realistic staff attendance patterns
     */
    private function generateStaffAttendance($staff, $sessions)
    {
        $this->command->info('👨‍⚕️ Creating staff attendance patterns...');

        foreach ($staff as $staffMember) {
            // Generate daily attendance for last 60 days
            $startDate = Carbon::now()->subDays(60);
            $endDate = Carbon::now();

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                // Skip weekends for most staff
                if ($date->isWeekend() && $staffMember->role !== 'admin') {
                    continue;
                }

                // Generate attendance status
                $status = $this->generateStaffAttendanceStatus($staffMember);
                
                if ($status !== 'absent') {
                    $checkInTime = $date->copy()->addHours(rand(7, 9))->addMinutes(rand(0, 59));
                    $checkOutTime = $checkInTime->copy()->addHours(rand(8, 10));
                    
                    StaffAttendance::create([
                        'user_id' => $staffMember->id,
                        'attendance_date' => $date,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => rand(1, 10) > 2 ? $checkOutTime : null, // 80% check out
                        'status' => $status,
                        'marked_by' => $staffMember->id,
                        'centre_id' => $staffMember->centre_id,
                        'notes' => $this->generateStaffAttendanceNotes($status),
                        'is_self_marked' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Get attendance profile for trainee
     */
    private function getAttendanceProfile($trainee)
    {
        // Create different attendance personalities based on trainee characteristics
        $profiles = [
            'excellent' => ['present' => 95, 'late' => 3, 'absent' => 1, 'excused' => 1],
            'good' => ['present' => 80, 'late' => 10, 'absent' => 5, 'excused' => 5],
            'average' => ['present' => 70, 'late' => 15, 'absent' => 10, 'excused' => 5],
            'struggling' => ['present' => 45, 'late' => 15, 'absent' => 25, 'excused' => 15],
        ];

        // Assign profile based on trainee condition and random factor
        if (str_contains(strtolower($trainee->trainee_condition), 'autism')) {
            return rand(1, 10) <= 7 ? $profiles['average'] : $profiles['good'];
        } elseif (str_contains(strtolower($trainee->trainee_condition), 'down syndrome')) {
            return rand(1, 10) <= 6 ? $profiles['good'] : $profiles['excellent'];
        } elseif (str_contains(strtolower($trainee->trainee_condition), 'cerebral palsy')) {
            return rand(1, 10) <= 5 ? $profiles['average'] : $profiles['struggling'];
        } else {
            // Random distribution for other conditions
            $rand = rand(1, 10);
            if ($rand <= 3) return $profiles['excellent'];
            if ($rand <= 6) return $profiles['good'];
            if ($rand <= 8) return $profiles['average'];
            return $profiles['struggling'];
        }
    }

    /**
     * Generate attendance status based on profile
     */
    private function generateAttendanceStatus($profile)
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($profile as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'present'; // fallback
    }

    /**
     * Generate participation score based on attendance
     */
    private function generateParticipationScore($status)
    {
        switch ($status) {
            case 'present':
                return rand(7, 10);
            case 'late':
                return rand(5, 8);
            case 'excused':
                return rand(6, 9);
            case 'absent':
                return 0;
            default:
                return rand(6, 8);
        }
    }

    /**
     * Generate realistic progress notes
     */
    private function generateProgressNotes($status, $trainee)
    {
        $notes = [
            'present' => [
                'Actively participated in all activities',
                'Showed good engagement and focus',
                'Completed all assigned tasks',
                'Demonstrated positive behavior',
                'Worked well with peers',
                'Made noticeable progress today',
                'Followed instructions well',
                'Showed enthusiasm for learning',
            ],
            'late' => [
                'Arrived late but participated well',
                'Late arrival but caught up quickly',
                'Missed warm-up but engaged in main activity',
                'Late due to transport issues',
            ],
            'absent' => [
                'Absent due to illness',
                'Family emergency',
                'Medical appointment',
                'Transportation issues',
            ],
            'excused' => [
                'Medical appointment - excused absence',
                'Family commitment - pre-approved',
                'Therapy session conflict',
                'School event participation',
            ]
        ];

        $statusNotes = $notes[$status] ?? $notes['present'];
        return $statusNotes[array_rand($statusNotes)];
    }

    /**
     * Generate staff attendance status
     */
    private function generateStaffAttendanceStatus($staff)
    {
        // Different attendance patterns by role
        $patterns = [
            'admin' => ['present' => 95, 'late' => 3, 'absent' => 2],
            'supervisor' => ['present' => 90, 'late' => 5, 'absent' => 5],
            'teacher' => ['present' => 85, 'late' => 8, 'absent' => 7],
            'ajk' => ['present' => 80, 'late' => 10, 'absent' => 10],
        ];

        $pattern = $patterns[$staff->role] ?? $patterns['teacher'];
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($pattern as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'present';
    }

    /**
     * Generate staff attendance notes
     */
    private function generateStaffAttendanceNotes($status)
    {
        $notes = [
            'present' => ['On time', 'Regular check-in', 'Ready for work'],
            'late' => ['Traffic delay', 'Personal matter', 'Transportation issue'],
            'absent' => ['Sick leave', 'Emergency leave', 'Medical appointment'],
        ];

        $statusNotes = $notes[$status] ?? ['Present'];
        return $statusNotes[array_rand($statusNotes)];
    }

    /**
     * Show attendance statistics summary
     */
    private function showAttendanceStatistics()
    {
        $this->command->info("\n📊 ATTENDANCE STATISTICS:");
        
        $traineeAttendance = DB::table('attendances')->count();
        $staffAttendance = DB::table('staff_attendances')->count();
        $sessionEnrollments = DB::table('session_enrollments')->count();
        
        // Average attendance rates
        $avgTraineeRate = DB::table('session_enrollments')
            ->whereIn('attendance_status', ['present', 'late'])
            ->count() * 100 / max(DB::table('session_enrollments')->whereNotNull('attendance_status')->count(), 1);
            
        $avgStaffRate = DB::table('staff_attendances')
            ->whereIn('status', ['present', 'late'])
            ->count() * 100 / max(DB::table('staff_attendances')->count(), 1);

        $this->command->line("   👥 Trainee Attendance Records: {$traineeAttendance}");
        $this->command->line("   👨‍⚕️ Staff Attendance Records: {$staffAttendance}");
        $this->command->line("   📋 Session Enrollments: {$sessionEnrollments}");
        $this->command->line("   📈 Average Trainee Attendance: " . round($avgTraineeRate, 1) . "%");
        $this->command->line("   📈 Average Staff Attendance: " . round($avgStaffRate, 1) . "%");
        
        // Trainees below 50% threshold
        $belowThreshold = DB::table('trainees as t')
            ->leftJoin('session_enrollments as se', 't.id', '=', 'se.trainee_id')
            ->select('t.id', DB::raw('
                ROUND(
                    (COUNT(CASE WHEN se.attendance_status IN ("present", "late") THEN 1 END) * 100.0) / 
                    NULLIF(COUNT(CASE WHEN se.attendance_status IS NOT NULL THEN 1 END), 0), 
                    1
                ) as attendance_rate
            '))
            ->groupBy('t.id')
            ->havingRaw('attendance_rate < 50')
            ->count();
            
        $this->command->line("   ⚠️  Trainees Below 50% Threshold: {$belowThreshold}");
    }
}