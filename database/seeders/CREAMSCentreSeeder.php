<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Centre;
use Illuminate\Support\Facades\DB;

class CREAMSCentreSeeder extends Seeder
{
    /**
     * Seed the centres table with Malaysian rehabilitation centers
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding Malaysian rehabilitation centres...');

        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'centre_address' => 'Jalan Gombak, 53100 Kuala Lumpur, Selangor',
                'centre_phone' => '+603-6196-4000',
                'centre_email' => 'gombak@creams.edu.my',
                'centre_capacity' => '150',
                'centre_manager' => 'Dr. Ahmad bin Ali',
                'centre_manager_contact' => '+603-6196-4001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Gombak Centre - Main rehabilitation centre providing comprehensive special needs education and therapy services for children and adolescents.',
                'centre_facilities' => json_encode([
                    'Therapy Rooms', 'Sensory Integration Room', 'Computer Lab', 
                    'Library', 'Art Therapy Room', 'Gymnasium', 'Music Therapy Room',
                    'Speech Therapy Room', 'Occupational Therapy Room'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => 1
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'Kuantan',
                'centre_address' => 'Jalan Sultan Ahmad Shah, 25300 Kuantan, Pahang',
                'centre_phone' => '+609-570-7000',
                'centre_email' => 'kuantan@creams.edu.my',
                'centre_capacity' => '120',
                'centre_manager' => 'Dr. Siti Fatimah binti Mohd Salleh',
                'centre_manager_contact' => '+609-570-7001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Kuantan Centre - Specialized centre focusing on autism spectrum disorders and developmental disabilities with emphasis on early intervention.',
                'centre_facilities' => json_encode([
                    'Speech Therapy Room', 'Occupational Therapy Room', 'Sensory Garden', 
                    'Learning Center', 'Music Therapy Room', 'Behavioral Intervention Room',
                    'Parent Training Room'
                ]),
                'opening_time' => '08:30:00',
                'is_active' => 1
            ],
            [
                'centre_id' => '03',
                'centre_name' => 'Pagoh',
                'centre_address' => 'Km 1, Jalan Panchor, 84600 Pagoh, Johor',
                'centre_phone' => '+607-434-1000',
                'centre_email' => 'pagoh@creams.edu.my',
                'centre_capacity' => '100',
                'centre_manager' => 'Dr. Mohd Hazlan bin Ibrahim',
                'centre_manager_contact' => '+607-434-1001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Pagoh Centre - Community-based rehabilitation centre serving southern Peninsular Malaysia with focus on inclusive education and vocational training.',
                'centre_facilities' => json_encode([
                    'Vocational Training Workshop', 'Life Skills Training Room', 
                    'Computer Training Lab', 'Therapy Rooms', 'Community Integration Center',
                    'Physical Therapy Room'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => 1
            ],
            [
                'centre_id' => '04',
                'centre_name' => 'Gambang',
                'centre_address' => 'Jalan ILP, 26300 Gambang, Pahang',
                'centre_phone' => '+609-424-2000',
                'centre_email' => 'gambang@creams.edu.my',
                'centre_capacity' => '80',
                'centre_manager' => 'Dr. Nurul Ain binti Kamal',
                'centre_manager_contact' => '+609-424-2001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Gambang Centre - Rural rehabilitation centre providing accessible services to underserved communities with mobile outreach programs.',
                'centre_facilities' => json_encode([
                    'Mobile Therapy Unit', 'Community Outreach Center', 
                    'Basic Therapy Rooms', 'Learning Support Center',
                    'Family Counseling Room'
                ]),
                'opening_time' => '08:30:00',
                'is_active' => 1
            ]
        ];

        foreach ($centres as $centreData) {
            Centre::create($centreData);
            $this->command->line("   ✅ Created centre: {$centreData['centre_name']}");
        }

        $this->command->info("🏢 Successfully seeded " . count($centres) . " rehabilitation centres");
    }
}