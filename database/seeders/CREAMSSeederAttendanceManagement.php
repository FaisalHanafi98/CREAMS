<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSSeederAttendanceManagement extends Seeder
{
    /**
     * CREAMS Attendance Management Seeder
     * Seeds: Staff attendances, trainee attendances, session attendance, alerts
     * Purpose: Session-based attendance for disabled children who may attend morning but miss evening
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding CREAMS Attendance Management...');
        
        // Create staff attendances
        $this->command->info('   👨‍💼 Creating staff attendances...');
        $this->seedStaffAttendances();
        
        // Create session attendance (core functionality for disabled children)
        $this->command->info('   📝 Creating session attendance...');
        $this->seedSessionAttendance();
        
        // Create attendance alerts
        $this->command->info('   🚨 Creating attendance alerts...');
        $this->seedAttendanceAlerts();
        
        $this->command->info('✅ Attendance Management seeding completed');
    }
    
    private function seedStaffAttendances(): void
    {
        $staff = DB::table('users')->get();
        $totalAttendances = 0;
        
        // Generate attendance for the last 3 months (June, July, August)
        $startDate = Carbon::create(2025, 6, 1);
        $endDate = Carbon::now();
        
        foreach ($staff as $staffMember) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip weekends for staff attendance
                if (!$currentDate->isWeekend()) {
                    // 95% attendance rate for staff
                    if (rand(1, 100) <= 95) {
                        $clockIn = $currentDate->copy()->setTime(8, rand(0, 30), 0);
                        $clockOut = $currentDate->copy()->setTime(17, rand(0, 30), 0);
                        
                        DB::table('staff_attendances')->insert([
                            'user_id' => $staffMember->id,
                            'centre_id' => $staffMember->centre_id,
                            'attendance_date' => $currentDate->format('Y-m-d'),
                            'check_in_time' => $clockIn->format('H:i:s'),
                            'status' => 'present',
                            'approved' => true,
                            'remarks' => null,
                            'created_at' => $currentDate,
                            'updated_at' => $currentDate
                        ]);
                        
                        $totalAttendances++;
                    } else {
                        // Absent or late
                        $status = rand(1, 100) <= 70 ? 'absent' : 'late';
                        $clockIn = $status === 'late' ? $currentDate->copy()->setTime(9, rand(15, 45), 0) : null;
                        $clockOut = $status === 'late' ? $currentDate->copy()->setTime(17, rand(0, 30), 0) : null;
                        
                        DB::table('staff_attendances')->insert([
                            'user_id' => $staffMember->id,
                            'centre_id' => $staffMember->centre_id,
                            'attendance_date' => $currentDate->format('Y-m-d'),
                            'check_in_time' => $clockIn ? $clockIn->format('H:i:s') : null,
                            'status' => $status,
                            'leave_type' => $status === 'absent' ? 'medical' : null,
                            'approved' => $status !== 'absent',
                            'remarks' => $status === 'absent' ? 'Medical leave' : 'Traffic jam',
                            'created_at' => $currentDate,
                            'updated_at' => $currentDate
                        ]);
                        
                        $totalAttendances++;
                    }
                }
                
                $currentDate->addDay();
            }
        }
        
        $this->command->line("      ✓ Created {$totalAttendances} staff attendance records");
    }
    
    private function seedSessionAttendance(): void
    {
        $sessions = DB::table('activity_sessions')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->join('activity_enrollments', 'activities.id', '=', 'activity_enrollments.activity_id')
            ->select('activity_sessions.*', 'activity_enrollments.trainee_id', 'activities.centre_id')
            ->get();
            
        $totalAttendances = 0;
        
        foreach ($sessions as $session) {
            $sessionDate = Carbon::parse($session->session_date);
            
            // Only create attendance for past and current sessions
            if ($sessionDate->lte(now())) {
                // Attendance patterns for disabled children
                $attendanceRate = $this->getTraineeAttendanceRate($session->trainee_id);
                
                if (rand(1, 100) <= $attendanceRate) {
                    $status = 'present';
                    $remarks = null;
                    
                    // Some trainees may arrive late or be excused due to medical needs
                    if (rand(1, 100) <= 15) {
                        $status = rand(1, 100) <= 50 ? 'late' : 'excused';
                        $remarks = $status === 'late' ? 'Medical appointment delay' : 'Fatigue - excused early';
                    }
                } else {
                    $status = 'absent';
                    $reasons = ['Medical appointment', 'Illness', 'Family emergency', 'Transportation issue', 'Therapy session'];
                    $remarks = $reasons[array_rand($reasons)];
                }
                
                DB::table('session_attendance')->insert([
                    'session_id' => $session->id,
                    'trainee_id' => $session->trainee_id,
                    'attendance_status' => $status,
                    'check_in_time' => $status !== 'absent' ? Carbon::parse($session->start_time)->addMinutes(rand(-5, 15))->format('H:i:s') : null,
                    'check_out_time' => $status === 'present' ? Carbon::parse($session->end_time)->addMinutes(rand(-10, 5))->format('H:i:s') : null,
                    'notes' => $remarks,
                    'marked_by' => $session->instructor_id,
                    'created_at' => $sessionDate,
                    'updated_at' => $sessionDate
                ]);
                
                $totalAttendances++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalAttendances} session attendance records");
    }
    
    private function getTraineeAttendanceRate($traineeId): int
    {
        $trainee = DB::table('trainees')->where('id', $traineeId)->first();
        
        if (!$trainee) return 75;
        
        $condition = strtolower($trainee->trainee_condition);
        
        // Different attendance patterns based on disability type
        if (strpos($condition, 'autism') !== false) {
            return 70; // May have routine disruptions
        } elseif (strpos($condition, 'physical') !== false || strpos($condition, 'palsy') !== false) {
            return 65; // Medical appointments and therapy sessions
        } elseif (strpos($condition, 'hearing') !== false) {
            return 85; // Generally good attendance
        } elseif (strpos($condition, 'visual') !== false) {
            return 80; // Good attendance with occasional transport issues
        } elseif (strpos($condition, 'learning') !== false || strpos($condition, 'adhd') !== false) {
            return 75; // Moderate attendance
        }
        
        return 75; // Default attendance rate
    }
    
    private function seedAttendanceAlerts(): void
    {
        // Find trainees with low attendance in the last month
        $lowAttendanceTrainees = DB::table('session_attendance')
            ->select('trainee_id', DB::raw('COUNT(*) as total_sessions'), 
                    DB::raw('SUM(CASE WHEN attendance_status = "present" THEN 1 ELSE 0 END) as attended_sessions'))
            ->where('created_at', '>=', now()->subMonth())
            ->groupBy('trainee_id')
            ->having('attended_sessions', '<', DB::raw('total_sessions * 0.6')) // Less than 60% attendance
            ->get();
            
        $totalAlerts = 0;
        
        foreach ($lowAttendanceTrainees as $traineeData) {
            $trainee = DB::table('trainees')->where('id', $traineeData->trainee_id)->first();
            if ($trainee) {
                $attendanceRate = round(($traineeData->attended_sessions / $traineeData->total_sessions) * 100);
                
                $traineeName = $trainee->trainee_first_name . ' ' . ($trainee->trainee_last_name ?? '');
                
                DB::table('attendance_alerts')->insert([
                    'alert_type' => 'trainee',
                    'trainee_id' => $trainee->id,
                    'alert_message' => "Low attendance: {$traineeName} has {$attendanceRate}% attendance rate. Attended {$traineeData->attended_sessions} out of {$traineeData->total_sessions} sessions in the last month",
                    'severity' => $attendanceRate < 40 ? 'high' : 'medium',
                    'is_read' => false,
                    'is_resolved' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $totalAlerts++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalAlerts} attendance alerts for low attendance cases");
    }
}