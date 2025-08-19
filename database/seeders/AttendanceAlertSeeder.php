<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AttendanceAlertSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚨 Seeding attendance alerts...');

        $faker = Faker::create();
        
        // Create some staff alerts
        $staffWithPoorAttendance = DB::table('staff_attendances')
            ->select('user_id')
            ->where('status', 'absent')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 3')
            ->limit(5)
            ->get();
            
        foreach ($staffWithPoorAttendance as $staff) {
            DB::table('attendance_alerts')->insert([
                'alert_type' => 'staff',
                'user_id' => $staff->user_id,
                'alert_message' => 'Staff member has exceeded acceptable absence threshold',
                'severity' => 'medium',
                'is_read' => $faker->boolean(30),
                'is_resolved' => $faker->boolean(20),
                'created_at' => $faker->dateTimeBetween('-1 week', 'now'),
                'updated_at' => now()
            ]);
        }
        
        // Create some trainee alerts
        $traineesWithPoorAttendance = DB::table('trainee_attendances')
            ->select('trainee_id')
            ->where('status', 'absent')
            ->groupBy('trainee_id')
            ->havingRaw('COUNT(*) > 5')
            ->limit(8)
            ->get();
            
        foreach ($traineesWithPoorAttendance as $trainee) {
            DB::table('attendance_alerts')->insert([
                'alert_type' => 'trainee',
                'trainee_id' => $trainee->trainee_id,
                'alert_message' => 'Trainee attendance rate below 70% threshold',
                'severity' => 'high',
                'is_read' => $faker->boolean(50),
                'is_resolved' => $faker->boolean(30),
                'created_at' => $faker->dateTimeBetween('-2 weeks', 'now'),
                'updated_at' => now()
            ]);
        }

        $this->command->info("🚨 Successfully seeded attendance alerts");
    }
}