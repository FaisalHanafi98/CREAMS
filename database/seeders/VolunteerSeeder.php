<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class VolunteerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🤝 Seeding volunteer applications...');

        $faker = Faker::create();
        
        for ($i = 1; $i <= 20; $i++) {
            DB::table('volunteers')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'phone' => '+60' . $faker->numerify('##########'),
                'address' => $faker->address,
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-18 years'),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'occupation' => $faker->jobTitle,
                'skills' => $faker->randomElement(['Teaching', 'Healthcare', 'Technology', 'Arts & Crafts', 'Sports']),
                'availability' => $faker->randomElement(['Weekends', 'Weekdays', 'Evenings', 'Flexible']),
                'motivation' => 'I want to help children with special needs and contribute to the community.',
                'status' => $faker->randomElement(['applied', 'reviewed', 'approved', 'active']),
                'reviewed_by' => $faker->boolean(70) ? DB::table('users')->where('role', 'admin')->first()->id : null,
                'reviewed_at' => $faker->boolean(70) ? $faker->dateTimeBetween('-1 month', 'now') : null,
                'review_notes' => $faker->boolean(50) ? 'Good candidate for volunteer position' : null,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now()
            ]);
        }

        $this->command->info('🤝 Successfully seeded 20 volunteer applications');
    }
}