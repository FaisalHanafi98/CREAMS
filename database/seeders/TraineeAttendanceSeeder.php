<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TraineeAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📋 Seeding trainee attendance records...');

        $faker = Faker::create();
        
        $enrollments = DB::table('activity_enrollments')
            ->join('activity_sessions', 'activity_enrollments.activity_id', '=', 'activity_sessions.activity_id')
            ->where('activity_sessions.session_status', 'completed')
            ->select('activity_enrollments.trainee_id', 'activity_enrollments.activity_id', 'activity_sessions.id as session_id', 'activity_sessions.session_date')
            ->get();
        
        $totalRecords = 0;
        
        foreach ($enrollments as $enrollment) {
            $status = $faker->randomElement(['present', 'present', 'present', 'late', 'absent'], [75, 15, 5, 3, 2]);
            
            DB::table('trainee_attendances')->insert([
                'trainee_id' => $enrollment->trainee_id,
                'activity_id' => $enrollment->activity_id,
                'session_id' => $enrollment->session_id,
                'attendance_date' => $enrollment->session_date,
                'status' => $status,
                'notes' => $status === 'absent' ? $faker->randomElement(['Illness', 'Family emergency', 'Transportation issue']) : null,
                'marked_by_user_id' => DB::table('users')->where('role', 'teacher')->inRandomOrder()->first()->id,
                'marked_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $totalRecords++;
        }

        $this->command->info("📋 Successfully seeded {$totalRecords} trainee attendance records");
    }
}