<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Seed the test database with comprehensive test data.
     *
     * This seeder creates a complete test environment with:
     * - 4 centres across Malaysia
     * - 20 users (5 admins, 4 supervisors, 8 teachers, 3 AJKs)
     * - 50 trainees with various disabilities
     * - 30 activities with sessions and enrollments
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Starting test data seeding...');

        $this->call([
            Test\CentreTestSeeder::class,      // 4 centres
            Test\UserTestSeeder::class,         // 20 users (all 6 roles)
            Test\TraineeTestSeeder::class,      // 50 trainees
            Test\ActivityTestSeeder::class,     // 30 activities
        ]);

        $this->command->info('Test data seeding completed successfully!');
        $this->command->info('Summary:');
        $this->command->info('- Centres: ' . \DB::table('centres')->count());
        $this->command->info('- Users: ' . \DB::table('staffs')->count());
        $this->command->info('- Trainees: ' . \DB::table('trainees')->count());
        $this->command->info('- Activities: ' . \DB::table('activities')->count());
        $this->command->info('- Sessions: ' . \DB::table('activity_occurrences')->count());
        $this->command->info('- Enrollments: ' . \DB::table('activity_enrollments')->count());
    }
}
