<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Volunteers;

class BasicVolunteersSeeder extends Seeder
{
    private array $volunteerData = [
        [
            'name' => 'Sarah Ahmad',
            'email' => 'sarah.ahmad@gmail.com',
            'phone' => '019-7654321',
            'address' => 'Taman Melawati, Kuala Lumpur',
            'birth_date' => '1995-05-15',
            'gender' => 'Female',
            'skills' => 'Art therapy, working with children with autism',
            'experience' => 'Volunteer at autism support center for 2 years',
            'availability' => 'Weekends, 9 AM - 5 PM',
            'emergency_contact_name' => 'Ahmad Ahmad',
            'emergency_contact_phone' => '019-1234567'
        ],
        [
            'name' => 'David Lim',
            'email' => 'david.lim@outlook.com',
            'phone' => '012-9876543',
            'address' => 'Bandar Utama, Selangor',
            'birth_date' => '1988-12-03',
            'gender' => 'Male',
            'skills' => 'Music therapy, guitar playing',
            'experience' => 'Music teacher with special needs students',
            'availability' => 'Weekdays, 2 PM - 6 PM',
            'emergency_contact_name' => 'Lisa Lim',
            'emergency_contact_phone' => '012-5678901'
        ],
        [
            'name' => 'Fatimah Zahra',
            'email' => 'fatimah.zahra@yahoo.com',
            'phone' => '013-4567890',
            'address' => 'Gombak, Selangor',
            'birth_date' => '1992-08-20',
            'gender' => 'Female',
            'skills' => 'Sign language, communication support',
            'experience' => 'Degree in Special Education',
            'availability' => 'Flexible schedule',
            'emergency_contact_name' => 'Zahra Fatimah',
            'emergency_contact_phone' => '013-2345678'
        ],
        [
            'name' => 'Raj Kumar',
            'email' => 'raj.kumar@gmail.com',
            'phone' => '017-8901234',
            'address' => 'Petaling Jaya, Selangor',
            'birth_date' => '1985-03-10',
            'gender' => 'Male',
            'skills' => 'Computer skills, adaptive technology',
            'experience' => 'IT professional, interested in helping with technology training',
            'availability' => 'Evenings and weekends',
            'emergency_contact_name' => 'Priya Kumar',
            'emergency_contact_phone' => '017-5678901'
        ],
        [
            'name' => 'Nurul Hidayah',
            'email' => 'nurul.hidayah@gmail.com',
            'phone' => '019-3456789',
            'address' => 'Shah Alam, Selangor',
            'birth_date' => '1990-11-25',
            'gender' => 'Female',
            'skills' => 'Cooking, life skills training',
            'experience' => 'Chef with interest in special needs support',
            'availability' => 'Weekends only',
            'emergency_contact_name' => 'Hidayah Nurul',
            'emergency_contact_phone' => '019-6789012'
        ]
    ];

    public function run(): void
    {
        $this->command->info('🤝 Creating volunteer applications...');

        $totalVolunteers = 0;

        foreach ($this->volunteerData as $volunteerInfo) {
            $volunteer = Volunteers::create([
                'volunteer_name' => $volunteerInfo['name'],
                'volunteer_email' => $volunteerInfo['email'],
                'volunteer_phone' => $volunteerInfo['phone'],
                'volunteer_address' => $volunteerInfo['address'],
                'volunteer_birth_date' => $volunteerInfo['birth_date'],
                'volunteer_gender' => $volunteerInfo['gender'],
                'volunteer_skills' => $volunteerInfo['skills'],
                'volunteer_experience' => $volunteerInfo['experience'],
                'volunteer_availability' => $volunteerInfo['availability'],
                'volunteer_status' => rand(0, 2) == 0 ? 'pending' : (rand(0, 1) == 0 ? 'active' : 'inactive'),
                'volunteer_start_date' => rand(0, 1) == 1 ? now()->subDays(rand(10, 100)) : null,
                'emergency_contact_name' => $volunteerInfo['emergency_contact_name'],
                'emergency_contact_phone' => $volunteerInfo['emergency_contact_phone']
            ]);

            $totalVolunteers++;
            $this->command->line("   ✅ {$volunteer->volunteer_name} ({$volunteer->volunteer_status})");
        }

        $this->command->info("🤝 Total volunteer applications created: $totalVolunteers");
        $this->command->info("   📋 Status distribution: pending, active, inactive");
    }
}