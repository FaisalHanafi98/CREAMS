<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Attendance;
use App\Models\User;
use App\Models\StaffAttendance;
use App\Models\ActivityEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CREAMSRealisticAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds with REALISTIC attendance patterns based on EXISTING enrollments
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating REALISTIC attendance data based on existing enrollments...');

        try {
            DB::beginTransaction();

            // Method 1: Use EXISTING ActivityEnrollments to determine who should have session attendance
            $this->generateAttendanceFromExistingEnrollments();
            
            // Method 2: Generate staff attendance for EXISTING staff only
            $this->generateRealisticStaffAttendance();

            DB::commit();

            $this->command->info('✅ Realistic attendance data created successfully!');
            $this->showAttendanceStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Realistic attendance seeding failed: ' . $e->getMessage());
            $this->command->error('❌ Failed to create attendance data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate attendance ONLY for trainees who are ACTUALLY enrolled in activities
     */
    private function generateAttendanceFromExistingEnrollments()
    {
        $this->command->info('👥 Creating attendance for ENROLLED trainees only...');

        // Get EXISTING activity enrollments
        $activityEnrollments = ActivityEnrollment::with(['trainee', 'activity.sessions'])
            ->where('enrollment_status', 'enrolled')
            ->get();

        $this->command->info("📊 Processing {$activityEnrollments->count()} ACTUAL enrollments");

        foreach ($activityEnrollments as $activityEnrollment) {
            $trainee = $activityEnrollment->trainee;
            $activity = $activityEnrollment->activity;
            
            if (!$trainee || !$activity) {
                continue; // Skip if relationships are broken
            }

            // Get attendance personality for this trainee
            $attendanceProfile = $this->getAttendanceProfile($trainee);
            
            // Process each session of this activity
            foreach ($activity->sessions as $session) {
                // Only process past sessions
                if ($session->session_date > Carbon::now()) {
                    continue;
                }

                // Create session enrollment (this trainee IS enrolled in this activity)
                $sessionEnrollment = SessionEnrollment::firstOrCreate([
                    'session_id' => $session->id,
                    'trainee_id' => $trainee->id,
                ], [
                    'enrollment_date' => $activityEnrollment->enrollment_date ?? $session->session_date->subDays(rand(1, 7)),
                    'enrollment_status' => 'enrolled',
                    'enrolled_by' => $activityEnrollment->enrolled_by ?? 1,
                ]);

                // Generate realistic attendance
                $attendanceStatus = $this->generateAttendanceStatus($attendanceProfile);
                
                // Update session enrollment with attendance
                $sessionEnrollment->update([
                    'attendance_status' => $attendanceStatus,
                    'participation_score' => $this->generateParticipationScore($attendanceStatus),
                    'progress_notes' => $this->generateProgressNotes($attendanceStatus, $trainee),
                    'checked_in_at' => $attendanceStatus === 'present' ? 
                        $session->session_date->copy()->addHours(rand(8, 10))->addMinutes(rand(0, 59)) : null,
                ]);

                // Create attendance record using the ACTUAL assigned teacher
                $assignedTeacher = $session->teacher_id ?? 
                    User::where('centre_id', $activity->centre_id)
                        ->where('role', 'teacher')
                        ->first()->id ?? 1;

                Attendance::updateOrCreate([
                    'trainee_id' => $trainee->id,
                    'date' => $session->session_date,
                ], [
                    'status' => $attendanceStatus,
                    'marked_by' => $assignedTeacher,
                    'remarks' => $this->generateProgressNotes($attendanceStatus, $trainee),
                    'activity_id' => $activity->id,
                ]);
            }
        }
    }

    /**
     * Generate staff attendance for EXISTING staff members in their assigned centres
     */
    private function generateRealisticStaffAttendance()
    {
        $this->command->info('👨‍⚕️ Creating realistic staff attendance...');

        $staff = User::whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])
            ->whereNotNull('centre_id')
            ->get();

        foreach ($staff as $staffMember) {
            // Generate attendance for last 30 days (not 60 to be more realistic)
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now();

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                // Skip weekends for most staff
                if ($date->isWeekend() && !in_array($staffMember->role, ['admin'])) {
                    continue;
                }

                // Generate realistic attendance status
                $status = $this->generateStaffAttendanceStatus($staffMember);
                
                if ($status !== 'absent') {
                    // Use correct structure that matches actual database schema
                    StaffAttendance::updateOrCreate([
                        'user_id' => $staffMember->id,
                        'attendance_date' => $date,
                        'attendance_type' => 'check_in',
                    ], [
                        'marked_by_user_id' => $staffMember->id,
                        'marked_by_email' => $staffMember->email,
                        'attendance_time' => sprintf('%02d:%02d:00', rand(7, 9), rand(0, 59)),
                        'centre_id' => $staffMember->centre_id,
                        'status' => $status,
                        'remarks' => $this->generateStaffAttendanceNotes($status),
                    ]);
                }
            }
        }
    }

    /**
     * Get realistic attendance profile based on trainee characteristics
     */
    private function getAttendanceProfile($trainee)
    {
        // More conservative attendance profiles
        $profiles = [
            'excellent' => ['present' => 90, 'late' => 5, 'absent' => 2, 'excused' => 3],
            'good' => ['present' => 75, 'late' => 15, 'absent' => 5, 'excused' => 5],
            'average' => ['present' => 65, 'late' => 15, 'absent' => 15, 'excused' => 5],
            'struggling' => ['present' => 45, 'late' => 15, 'absent' => 30, 'excused' => 10],
        ];

        // Assign based on condition (more realistic distribution)
        if (str_contains(strtolower($trainee->trainee_condition ?? ''), 'autism')) {
            return rand(1, 10) <= 6 ? $profiles['average'] : $profiles['good'];
        } elseif (str_contains(strtolower($trainee->trainee_condition ?? ''), 'down syndrome')) {
            return rand(1, 10) <= 7 ? $profiles['good'] : $profiles['excellent'];
        } else {
            // Random but realistic distribution
            $rand = rand(1, 10);
            if ($rand <= 2) return $profiles['excellent'];
            if ($rand <= 5) return $profiles['good'];
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
     * Generate participation score
     */
    private function generateParticipationScore($status)
    {
        switch ($status) {
            case 'present': return rand(7, 10);
            case 'late': return rand(5, 8);
            case 'excused': return rand(6, 9);
            case 'absent': return 0;
            default: return rand(6, 8);
        }
    }

    /**
     * Generate realistic progress notes
     */
    private function generateProgressNotes($status, $trainee)
    {
        $notes = [
            'present' => [
                'Actively participated in session',
                'Good engagement and focus',
                'Completed assigned tasks',
                'Positive behavior displayed',
                'Worked well with peers',
                'Made good progress',
                'Followed instructions well',
            ],
            'late' => [
                'Late arrival but participated',
                'Caught up quickly after arrival',
                'Late due to transport',
                'Missed warm-up activities',
            ],
            'absent' => [
                'Absent due to illness',
                'Family matter',
                'Medical appointment',
                'Transport issues',
            ],
            'excused' => [
                'Medical appointment (excused)',
                'Family commitment (approved)',
                'School event',
                'Therapy session',
            ]
        ];

        $statusNotes = $notes[$status] ?? $notes['present'];
        return $statusNotes[array_rand($statusNotes)];
    }

    /**
     * Generate staff attendance status based on role
     */
    private function generateStaffAttendanceStatus($staff)
    {
        $patterns = [
            'admin' => ['present' => 92, 'late' => 5, 'absent' => 3],
            'supervisor' => ['present' => 88, 'late' => 7, 'absent' => 5],
            'teacher' => ['present' => 85, 'late' => 10, 'absent' => 5],
            'ajk' => ['present' => 80, 'late' => 12, 'absent' => 8],
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
     * Generate staff notes
     */
    private function generateStaffAttendanceNotes($status)
    {
        $notes = [
            'present' => ['Regular attendance', 'On time', 'Ready for work'],
            'late' => ['Traffic delay', 'Personal matter', 'Transport issue'],
            'absent' => ['Sick leave', 'Personal leave', 'Medical appointment'],
        ];

        $statusNotes = $notes[$status] ?? ['Present'];
        return $statusNotes[array_rand($statusNotes)];
    }

    /**
     * Show realistic statistics
     */
    private function showAttendanceStatistics()
    {
        $this->command->info("\n📊 REALISTIC ATTENDANCE STATISTICS:");
        
        // Count actual records created
        $sessionEnrollments = DB::table('session_enrollments')->whereNotNull('attendance_status')->count();
        $attendanceRecords = DB::table('attendances')->count();
        $staffAttendance = DB::table('staff_attendances')->count();
        
        // Get actual enrollment-based data
        $actualEnrollments = DB::table('activity_enrollments')->where('enrollment_status', 'enrolled')->count();
        
        // Calculate realistic rates
        $traineeAttendanceRate = 0;
        if ($sessionEnrollments > 0) {
            $presentCount = DB::table('session_enrollments')
                ->whereIn('attendance_status', ['present', 'late'])
                ->count();
            $traineeAttendanceRate = round(($presentCount / $sessionEnrollments) * 100, 1);
        }
        
        $staffAttendanceRate = 0;
        if ($staffAttendance > 0) {
            $staffPresentCount = DB::table('staff_attendances')
                ->whereIn('status', ['present', 'late'])
                ->count();
            $staffAttendanceRate = round(($staffPresentCount / $staffAttendance) * 100, 1);
        }

        $this->command->line("   📋 Based on ACTUAL Enrollments: {$actualEnrollments}");
        $this->command->line("   👥 Session Attendance Records: {$sessionEnrollments}");
        $this->command->line("   📝 Legacy Attendance Records: {$attendanceRecords}");
        $this->command->line("   👨‍⚕️ Staff Attendance Records: {$staffAttendance}");
        $this->command->line("   📈 Trainee Attendance Rate: {$traineeAttendanceRate}%");
        $this->command->line("   📈 Staff Attendance Rate: {$staffAttendanceRate}%");
        
        // Show trainees below threshold
        $belowThreshold = DB::table('trainees as t')
            ->join('session_enrollments as se', 't.id', '=', 'se.trainee_id')
            ->select('t.id', DB::raw('
                ROUND(
                    (COUNT(CASE WHEN se.attendance_status IN ("present", "late") THEN 1 END) * 100.0) / 
                    COUNT(CASE WHEN se.attendance_status IS NOT NULL THEN 1 END), 
                    1
                ) as attendance_rate
            '))
            ->groupBy('t.id')
            ->havingRaw('attendance_rate < 50')
            ->count();
            
        $this->command->line("   ⚠️  Trainees Below 50% Threshold: {$belowThreshold}");
        $this->command->info("   ✅ All attendance based on REAL enrollment relationships!");
    }
}