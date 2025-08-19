<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔔 Seeding notifications...');

        $faker = Faker::create();
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Create 2-3 notifications per user
            for ($i = 0; $i < $faker->numberBetween(2, 3); $i++) {
                DB::table('notifications')->insert([
                    'id' => $faker->uuid,
                    'type' => $faker->randomElement(['App\\Notifications\\AttendanceAlert', 'App\\Notifications\\ActivityUpdate']),
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => $faker->randomElement(['Attendance Alert', 'New Activity', 'Schedule Update']),
                        'message' => $faker->sentence,
                        'action_url' => '/dashboard'
                    ]),
                    'read_at' => $faker->boolean(60) ? $faker->dateTimeBetween('-1 week', 'now') : null,
                    'created_at' => $faker->dateTimeBetween('-2 weeks', 'now'),
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('🔔 Successfully seeded notifications for all users');
    }
}