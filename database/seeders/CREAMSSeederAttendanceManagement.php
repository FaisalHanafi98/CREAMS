<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CREAMSSeederAttendanceManagement extends Seeder
{
    /**
     * CREAMS Attendance Management Seeder
     * Seeds: Staff attendances, trainee attendances, session attendance, alerts
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding CREAMS Attendance Management...');
        
        $this->call([
            StaffAttendanceSeeder::class,
            TraineeAttendanceSeeder::class,
            SessionAttendanceSeeder::class,
            AttendanceAlertSeeder::class,
        ]);
        
        $this->command->info('✅ Attendance Management seeding completed');
    }
}