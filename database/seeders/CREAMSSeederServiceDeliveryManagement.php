<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CREAMSSeederServiceDeliveryManagement extends Seeder
{
    /**
     * CREAMS Service Delivery Management Seeder
     * Seeds: Activity categories, activities, sessions, enrollments
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding CREAMS Service Delivery Management...');
        
        $this->call([
            ActivityCategorySeeder::class,
            ActivitySeeder::class,
            ActivitySessionSeeder::class,
            ActivityEnrollmentSeeder::class,
        ]);
        
        $this->command->info('✅ Service Delivery Management seeding completed');
    }
}