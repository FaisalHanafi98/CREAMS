<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class StaffAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👨‍💼 Seeding staff attendance records...');

        $faker = Faker::create();
        $users = DB::table('users')->get();
        
        $totalRecords = 0;
        
        foreach ($users as $user) {
            // Generate 30 days of attendance records
            for ($i = 30; $i > 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                // Skip weekends
                if ($date->isWeekend()) continue;
                
                $status = $faker->randomElement(['present', 'late', 'absent'], [75, 15, 10]);
                
                DB::table('staff_attendances')->insert([
                    'user_id' => $user->id,
                    'centre_id' => $user->centre_id,
                    'attendance_date' => $date->format('Y-m-d'),
                    'check_in_time' => $status !== 'absent' ? $faker->time('H:i:s', '09:30:00') : null,
                    'check_out_time' => $status !== 'absent' ? $faker->time('H:i:s', '17:30:00') : null,
                    'status' => $status,
                    'total_hours' => $status !== 'absent' ? $faker->randomFloat(2, 7, 8.5) : 0,
                    'approved' => true,
                    'marked_by_user_id' => $user->id,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);
                
                $totalRecords++;
            }
        }

        $this->command->info("👨‍💼 Successfully seeded {$totalRecords} staff attendance records");
    }
}