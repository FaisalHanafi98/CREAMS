<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💬 Seeding internal messages...');

        $faker = Faker::create();
        $users = DB::table('users')->get();
        
        for ($i = 1; $i <= 25; $i++) {
            $sender = $users->random();
            
            DB::table('messages')->insert([
                'subject' => $faker->randomElement(['Meeting reminder', 'Schedule update', 'Policy announcement', 'Training notification']),
                'content' => $faker->paragraph,
                'sender_id' => $sender->id,
                'sender_type' => 'user',
                'message_type' => $faker->randomElement(['general', 'announcement', 'reminder']),
                'priority' => $faker->randomElement(['normal', 'high']),
                'is_draft' => false,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                'updated_at' => now()
            ]);
        }

        $this->command->info('💬 Successfully seeded 25 internal messages');
    }
}