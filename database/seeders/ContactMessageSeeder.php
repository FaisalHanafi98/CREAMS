<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📞 Seeding contact messages...');

        $faker = Faker::create();
        
        for ($i = 1; $i <= 15; $i++) {
            DB::table('contact_messages')->insert([
                'name' => $faker->name,
                'email' => $faker->email,
                'phone' => '+60' . $faker->numerify('##########'),
                'subject' => $faker->randomElement(['Inquiry about services', 'Enrollment question', 'General information', 'Feedback']),
                'message' => $faker->paragraph,
                'status' => $faker->randomElement(['new', 'read', 'replied']),
                'replied_by' => $faker->boolean(40) ? DB::table('users')->where('role', 'admin')->first()->id : null,
                'replied_at' => $faker->boolean(40) ? $faker->dateTimeBetween('-1 week', 'now') : null,
                'reply_message' => $faker->boolean(40) ? 'Thank you for your inquiry. We will contact you soon.' : null,
                'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now()
            ]);
        }

        $this->command->info('📞 Successfully seeded 15 contact messages');
    }
}
