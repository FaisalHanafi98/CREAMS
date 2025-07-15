<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Models\Centres;

class QuickUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a centre first
        $centre = Centres::updateOrCreate(
            ['centre_id' => '01'],
            [
                'centre_id' => '01',
                'centre_name' => 'Main Centre',
                'address' => 'Test Address',
                'city' => 'Kuala Lumpur',
                'state' => 'WP',
                'postcode' => '50000',
                'phone' => '03-1234567',
                'email' => 'centre@test.com',
                'capacity' => 100,
                'description' => 'Test Centre',
                'facilities' => '["Test"]',
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'is_active' => 1,
                'status' => 'active'
            ]
        );

        // Create admin user
        Users::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'iium_id' => 'ADMIN001',
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'centre_id' => '01',
                'status' => 'active'
            ]
        );

        // Create teacher user
        Users::updateOrCreate(
            ['email' => 'teacher@test.com'],
            [
                'iium_id' => 'TEACH001',
                'name' => 'Teacher User',
                'email' => 'teacher@test.com',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'centre_id' => '01',
                'status' => 'active'
            ]
        );

        $this->command->info('Quick users created:');
        $this->command->info('Admin: admin@test.com / password');
        $this->command->info('Teacher: teacher@test.com / password');
    }
}