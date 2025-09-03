<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\Attendance;
use App\Models\StaffAttendance;
use App\Helpers\MalaysiaHolidays;

class CREAMSRealisticAttendanceSeeder extends Seeder
{
    /**
     * Realistic Attendance Seeder for CREAMS
     * 
     * Business Logic:
     * - Trainee attendance: Per activity session (80% attendance rate)
     * - Staff attendance: Per working day (85% attendance rate)
     * - Each session has at least 3 trainee participants
     * - Realistic patterns: Lower attendance on Mondays, Fridays
     * - Account for Malaysian holidays and weekends
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding Realistic Attendance Data...');
        
        // Define seeding period (last 3 months to current date)
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now()->endOfDay();
        
        $this->command->info("   📅 Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        
        // Clear existing attendance data
        $this->clearExistingAttendance();
        
        // 1. Ensure activity enrollments (minimum 3 trainees per activity)
        $this->ensureActivityEnrollments();
        
        // 2. Generate trainee session-based attendance (80% rate)
        $this->generateTraineeAttendance($startDate, $endDate);
        
        // 3. Generate staff daily attendance (85% rate)
        $this->generateStaffAttendance($startDate, $endDate);
        
        $this->command->info('✅ Realistic attendance data seeded successfully');
    }
    
    /**
     * Clear existing attendance data to avoid duplicates
     */
    private function clearExistingAttendance(): void
    {
        $this->command->info('   🧹 Clearing existing attendance data...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('trainee_attendances')->truncate();
        DB::table('staff_attendances')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
    
    /**
     * Ensure each active activity has at least 3 enrolled trainees
     */
    private function ensureActivityEnrollments(): void
    {
        $this->command->info('   👥 Ensuring activity enrollments (min 3 trainees per activity)...');
        
        $activeActivities = Activity::where('is_active', true)->get();
        $trainees = Trainee::where('status', 'active')->get();
        
        if ($trainees->count() < 3) {
            $this->command->warn('   ⚠️  Warning: Less than 3 active trainees found. Creating additional trainees...');
            $this->createAdditionalTrainees(10); // Create 10 additional trainees
            $trainees = Trainee::where('status', 'active')->get();
        }
        
        foreach ($activeActivities as $activity) {
            $currentEnrollments = ActivityEnrollment::where('activity_id', $activity->id)
                ->where('enrollment_status', 'enrolled')
                ->count();
            
            if ($currentEnrollments < 3) {
                $needed = 3 - $currentEnrollments;
                $availableTrainees = $trainees->shuffle()->take($needed + 2); // Take a few extra for variety
                
                foreach ($availableTrainees->take($needed + rand(1, 3)) as $trainee) {
                    // Check if already enrolled
                    $exists = ActivityEnrollment::where('activity_id', $activity->id)
                        ->where('trainee_id', $trainee->id)
                        ->exists();
                    
                    if (!$exists) {
                        ActivityEnrollment::create([
                            'trainee_id' => $trainee->id,
                            'activity_id' => $activity->id,
                            'enrollment_status' => 'enrolled',
                            'enrollment_date' => Carbon::now()->subDays(rand(1, 30))
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Create additional trainees if needed
     */
    private function createAdditionalTrainees(int $count): void
    {
        $conditions = [
            'Physical Disabilities', 'Learning Support', 'Visual Impairment',
            'Autism Spectrum Support', 'Hearing Impairment', 'Speech Therapy'
        ];
        
        $centres = DB::table('centres')->pluck('centre_name')->toArray();
        
        for ($i = 1; $i <= $count; $i++) {
            Trainee::create([
                'trainee_first_name' => "Generated",
                'trainee_last_name' => "Trainee {$i}",
                'trainee_email' => "generated.trainee{$i}@test.com",
                'trainee_phone_number' => "019-" . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'trainee_date_of_birth' => Carbon::now()->subYears(rand(18, 35))->format('Y-m-d'),
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'trainee_condition' => $conditions[array_rand($conditions)],
                'centre_name' => $centres[array_rand($centres)],
                'status' => 'active',
                'trainee_address' => "Test Address {$i}, Test City",
                'guardian_name' => "Guardian {$i}",
                'guardian_phone' => "016-" . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            ]);
        }
    }
    
    /**
     * Generate trainee session-based attendance with 80% rate
     */
    private function generateTraineeAttendance(Carbon $startDate, Carbon $endDate): void
    {
        $this->command->info('   📝 Generating trainee session attendance (80% rate)...');
        
        $sessions = ActivitySession::whereBetween('session_date', [$startDate, $endDate])
            ->with(['activity.enrollments.trainee'])
            ->get();
        
        $attendanceRecords = 0;
        $progressBar = $this->command->getOutput()->createProgressBar($sessions->count());
        
        foreach ($sessions as $session) {
            // Skip weekends and holidays
            if ($this->isNonWorkingDay($session->session_date)) {
                $progressBar->advance();
                continue;
            }
            
            $enrolledTrainees = $session->activity->enrollments()
                ->where('enrollment_status', 'enrolled')
                ->with('trainee')
                ->get();
            
            foreach ($enrolledTrainees as $enrollment) {
                $attendanceRate = $this->calculateAttendanceRate($session->session_date);
                
                // Determine if trainee attends (weighted probability)
                if (rand(1, 100) <= $attendanceRate) {
                    $status = $this->determineAttendanceStatus($session->session_date);
                    
                    Attendance::create([
                        'trainee_id' => $enrollment->trainee_id,
                        'activity_id' => $session->activity_id,
                        'session_id' => $session->id,
                        'attendance_date' => $session->session_date,
                        'status' => $status,
                        'notes' => $this->generateAttendanceNotes($status),
                        'marked_by_user_id' => $session->activity->instructor_id ?? 1,
                        'marked_at' => Carbon::parse($session->session_date)->addHour()
                    ]);
                    
                    $attendanceRecords++;
                }
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->command->info("\n   ✅ Created {$attendanceRecords} trainee attendance records");
    }
    
    /**
     * Generate staff daily attendance with 85% rate
     */
    private function generateStaffAttendance(Carbon $startDate, Carbon $endDate): void
    {
        $this->command->info('   👨‍💼 Generating staff daily attendance (85% rate)...');
        
        $staff = User::whereIn('role', ['teacher', 'supervisor', 'ajk', 'admin'])
            ->where('status', 'active')
            ->get();
        
        $current = $startDate->copy();
        $attendanceRecords = 0;
        
        while ($current->lte($endDate)) {
            // Skip weekends and holidays
            if (!$this->isNonWorkingDay($current)) {
                foreach ($staff as $user) {
                    // 85% attendance rate with realistic patterns
                    $attendanceRate = $this->calculateStaffAttendanceRate($current);
                    
                    if (rand(1, 100) <= $attendanceRate) {
                        $status = $this->determineStaffAttendanceStatus($current);
                        $attendanceTime = $this->generateAttendanceTime($status);
                        
                        StaffAttendance::create([
                            'user_id' => $user->id,
                            'marked_by_user_id' => $user->id, // Self-marked
                            'marked_by_email' => $user->email,
                            'attendance_date' => $current->format('Y-m-d'),
                            'check_in_time' => $attendanceTime,
                            'centre_id' => $user->centre_id ?? '01',
                            'status' => $status,
                            'remarks' => $this->generateStaffAttendanceRemarks($status),
                            'approved' => true,
                            'approved_by' => 1, // Admin approval
                            'approved_at' => $current
                        ]);
                        
                        $attendanceRecords++;
                    }
                }
            }
            
            $current->addDay();
        }
        
        $this->command->info("   ✅ Created {$attendanceRecords} staff attendance records");
    }
    
    /**
     * Check if date is non-working day (weekends or Malaysian holidays)
     */
    private function isNonWorkingDay(string $date): bool
    {
        $carbon = Carbon::parse($date);
        
        // Check weekends
        if ($carbon->isWeekend()) {
            return true;
        }
        
        // Check Malaysian holidays if helper exists
        if (class_exists(MalaysiaHolidays::class)) {
            return MalaysiaHolidays::isNonWorkingDay($date);
        }
        
        return false;
    }
    
    /**
     * Calculate attendance rate based on day patterns
     * Lower on Mondays/Fridays, higher mid-week
     */
    private function calculateAttendanceRate(string $date): int
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        
        switch ($dayOfWeek) {
            case Carbon::MONDAY:
                return 75; // Lower on Monday
            case Carbon::FRIDAY:
                return 78; // Lower on Friday
            case Carbon::TUESDAY:
            case Carbon::WEDNESDAY:
            case Carbon::THURSDAY:
                return 85; // Higher mid-week
            default:
                return 80; // Default rate
        }
    }
    
    /**
     * Calculate staff attendance rate (generally higher than trainees)
     */
    private function calculateStaffAttendanceRate(Carbon $date): int
    {
        $dayOfWeek = $date->dayOfWeek;
        
        switch ($dayOfWeek) {
            case Carbon::MONDAY:
                return 82; // Slightly lower on Monday
            case Carbon::FRIDAY:
                return 80; // Lower on Friday
            case Carbon::TUESDAY:
            case Carbon::WEDNESDAY:
            case Carbon::THURSDAY:
                return 90; // Higher mid-week
            default:
                return 85; // Default rate
        }
    }
    
    /**
     * Determine attendance status with realistic distribution
     */
    private function determineAttendanceStatus(string $date): string
    {
        $rand = rand(1, 100);
        
        if ($rand <= 85) {
            return 'present';
        } elseif ($rand <= 92) {
            return 'excused';
        } else {
            return 'absent';
        }
    }
    
    /**
     * Determine staff attendance status
     */
    private function determineStaffAttendanceStatus(Carbon $date): string
    {
        $rand = rand(1, 100);
        
        if ($rand <= 88) {
            return 'present';
        } elseif ($rand <= 95) {
            return 'late';
        } else {
            return 'leave';
        }
    }
    
    /**
     * Generate realistic attendance time for staff
     * Centre opens at 9:00 AM, staff must check in by 8:45 AM to be on time
     */
    private function generateAttendanceTime(string $status): string
    {
        switch ($status) {
            case 'present':
                // On time arrival: 8:00-8:45 AM
                $hour = 8;
                $minute = rand(0, 45); // 8:00 to 8:45 AM
                break;
            case 'late':
                // Late arrival: 8:46-9:30 AM (after 8:45 AM deadline)
                $hour = 8;
                $minute = rand(46, 90); // 8:46 to 9:30 AM
                if ($minute >= 60) {
                    $hour = 9;
                    $minute = $minute - 60;
                }
                break;
            default:
                // Default time for leave
                $hour = 8;
                $minute = 30;
        }
        
        return sprintf('%02d:%02d:00', $hour, $minute);
    }
    
    /**
     * Generate attendance notes for trainees
     */
    private function generateAttendanceNotes(string $status): ?string
    {
        switch ($status) {
            case 'excused':
                $reasons = ['Medical leave', 'Family emergency', 'Official business', 'Prior arrangement'];
                return $reasons[array_rand($reasons)];
            case 'absent':
                $reasons = ['Unexcused absence', 'No notification', 'Family matters', 'Health issues'];
                return $reasons[array_rand($reasons)];
            default:
                return null;
        }
    }
    
    /**
     * Generate staff attendance remarks
     */
    private function generateStaffAttendanceRemarks(string $status): ?string
    {
        switch ($status) {
            case 'late':
                $reasons = ['Traffic congestion', 'Public transport delay', 'Personal matter'];
                return $reasons[array_rand($reasons)];
            case 'leave':
                $reasons = ['Medical appointment', 'Official training', 'Family emergency'];
                return $reasons[array_rand($reasons)];
            default:
                return null;
        }
    }
}