<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionAttendanceSeeder extends Seeder
{
    /**
     * Seed session attendance with high participation rates requiring double staff
     * Target: 80%+ attendance rate with sessions requiring additional supervision
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding session attendance (high participation requiring double staff)...');

        // Get enrolled trainees and active sessions
        $enrollments = DB::table('activity_enrollments')
            ->join('trainees', 'activity_enrollments.trainee_id', '=', 'trainees.id')
            ->join('activity_sessions', 'activity_enrollments.activity_id', '=', 'activity_sessions.activity_id')
            ->where('activity_enrollments.enrollment_status', 'enrolled')
            ->select(
                'activity_sessions.id as session_id',
                'trainees.id as trainee_id',
                'activity_sessions.session_date',
                'activity_sessions.session_status',
                'trainees.centre_id'
            )
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->error('No enrollments found! Run ActivityEnrollmentSeeder first.');
            return;
        }

        $totalAttendance = 0;
        $presentCount = 0;

        foreach ($enrollments as $enrollment) {
            // High attendance rate (85-95%) to show need for double staff
            $attendanceRate = rand(85, 95) / 100;
            
            if (rand(1, 100) <= ($attendanceRate * 100)) {
                // Get appropriate staff member from the same centre
                $staffMember = $this->getStaffMember($enrollment->centre_id);
                
                if (!$staffMember) continue;
                
                $attendanceStatus = $this->getAttendanceStatus();
                $checkInTime = $this->generateCheckInTime($enrollment->session_status);
                $participationScore = $this->generateParticipationScore($attendanceStatus);
                
                $attendanceData = [
                    'session_id' => $enrollment->session_id,
                    'trainee_id' => $enrollment->trainee_id,
                    'marked_by_staff_id' => $staffMember->id,
                    'attendance_status' => $attendanceStatus,
                    'check_in_time' => $checkInTime,
                    'notes' => $this->generateAttendanceNotes($attendanceStatus, $participationScore),
                    'participation_score' => $participationScore,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                // Check if attendance record already exists (prevent duplicates)
                $exists = DB::table('session_attendance')
                    ->where('session_id', $enrollment->session_id)
                    ->where('trainee_id', $enrollment->trainee_id)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('session_attendance')->insert($attendanceData);
                    $totalAttendance++;
                    
                    if ($attendanceStatus === 'present') {
                        $presentCount++;
                    }
                }
            }
        }

        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;
        
        $this->command->info("📋 Successfully seeded {$totalAttendance} attendance records");
        $this->command->line("   • Present: {$presentCount} ({$attendanceRate}% attendance rate)");
        $this->command->line("   • High participation rates requiring additional staff supervision");
        
        // Show attendance statistics by status
        $stats = DB::table('session_attendance')
            ->select('attendance_status', DB::raw('count(*) as count'))
            ->groupBy('attendance_status')
            ->get();
            
        foreach ($stats as $stat) {
            $this->command->line("   📊 {$stat->attendance_status}: {$stat->count} records");
        }
    }
    
    private function getStaffMember($centreId)
    {
        return DB::table('users')
            ->where('centre_id', $centreId)
            ->whereIn('role', ['teacher', 'supervisor'])
            ->inRandomOrder()
            ->first();
    }
    
    private function getAttendanceStatus(): string
    {
        // High present rate (90%), some late (8%), few absent (2%)
        $rand = rand(1, 100);
        
        if ($rand <= 90) {
            return 'present';
        } elseif ($rand <= 98) {
            return 'late';
        } else {
            return 'absent';
        }
    }
    
    private function generateCheckInTime($sessionStatus): ?string
    {
        if ($sessionStatus === 'completed') {
            // Generate past check-in times
            $hour = rand(9, 16);
            $minute = rand(0, 59);
            return sprintf('%02d:%02d:00', $hour, $minute);
        }
        
        return null; // For future sessions
    }
    
    private function generateParticipationScore($attendanceStatus): ?float
    {
        if ($attendanceStatus === 'present') {
            return rand(70, 100) / 10; // 7.0 to 10.0 for present
        } elseif ($attendanceStatus === 'late') {
            return rand(50, 80) / 10; // 5.0 to 8.0 for late
        }
        
        return null; // No score for absent
    }
    
    private function generateAttendanceNotes($status, $score): ?string
    {
        $notes = [
            'present' => [
                'Excellent participation and engagement',
                'Active in all group activities',
                'Showed good progress today',
                'Helpful to other trainees',
                'Completed all assigned tasks'
            ],
            'late' => [
                'Arrived late but caught up quickly',
                'Late due to transportation issues',
                'Made good effort despite late arrival'
            ],
            'absent' => [
                'Absent due to medical appointment',
                'Family emergency',
                'Illness reported by guardian'
            ]
        ];
        
        return $notes[$status][array_rand($notes[$status])] ?? null;
    }
}
