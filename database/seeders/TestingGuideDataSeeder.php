<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestingGuideDataSeeder extends Seeder
{
    /**
     * Seed all the data referenced in the CREAMS Form Testing Guide
     * This ensures all test credentials and data are available for testing
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding Testing Guide Data...');

        // Ensure centres exist first
        $this->seedRequiredCentres();

        // Seed authentication users
        $this->seedTestingUsers();

        // Seed asset categories and types
        $this->seedAssetData();

        // Seed existing trainees for testing
        $this->seedExistingTrainees();

        // Seed existing volunteers
        $this->seedExistingVolunteers();

        // Seed existing contact messages
        $this->seedExistingContactMessages();

        // Seed realistic activities for comprehensive testing
        $this->seedRealisticActivities();

        // Seed activity sessions for time conflict testing
        $this->seedActivitySessions();

        // Seed additional trainees for activity testing
        $this->seedAdditionalTrainees();

        $this->command->info('✅ Testing Guide Data seeding completed with enhanced activity testing data');
    }

    /**
     * Seed required centres
     */
    private function seedRequiredCentres(): void
    {
        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'centre_address' => 'Jalan Gombak, 53100 Gombak, Selangor',
                'centre_phone' => '+603-6196-4000',
                'centre_email' => 'gombak@iium.edu.my',
                'centre_capacity' => '150',
                'centre_manager' => 'Prof. Dr. Mohd Roslan bin Mohd Nor',
                'centre_manager_contact' => '+603-6196-4001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Gombak Centre - Main rehabilitation centre at IIUM',
                'centre_facilities' => json_encode(['Therapy Rooms', 'Assessment Rooms', 'Training Facilities']),
                'opening_time' => '08:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($centres as $centre) {
            DB::table('centres')->insertOrIgnore($centre);
        }

        $this->command->line('   📍 Centres seeded');
    }

    /**
     * Seed staff referenced in testing guide
     */
    private function seedTestingUsers(): void
    {
        $this->command->info('   👥 Seeding testing guide staff...');

        $users = [
            // Admin user from testing guide
            [
                'iium_id' => null,
                'name' => 'Dr. Lakshmi a/p Krishnan',
                'email' => 'lakshmi.krishnan@iium.edu.my',
                'password' => Hash::make('Admin@2024!'),
                'phone' => '+60123456789',
                'address' => 'Gombak Campus, IIUM',
                'education_level' => 'PhD',
                'education_specialization' => 'Speech Therapy',
                'teaching_specialization' => 'Special Needs Education',
                'date_of_birth' => '1980-05-15',
                'role' => 'admin',
                'status' => 'active',
                'centre_id' => '01',
                'position' => 'Director of Special Needs Services',
                'about' => 'Director of CREAMS with expertise in speech therapy and autism intervention',
                'centre_location' => 'Gombak',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Teacher with IIUM ID from testing guide
            [
                'iium_id' => '1928471',
                'name' => 'Ustaz Ahmad bin Hassan',
                'email' => 'ahmad.hassan@iium.edu.my',
                'password' => Hash::make('Teacher@2024'),
                'phone' => '+60198765432',
                'address' => 'Taman Gombak Setia, Gombak',
                'education_level' => 'Masters',
                'education_specialization' => 'Special Education',
                'teaching_specialization' => 'Learning Disabilities',
                'date_of_birth' => '1985-08-12',
                'role' => 'teacher',
                'status' => 'active',
                'centre_id' => '01',
                'position' => 'Special Education Teacher',
                'about' => 'Dedicated teacher specializing in learning disabilities support',
                'centre_location' => 'Gombak',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Supervisor from testing guide
            [
                'iium_id' => '1675432',
                'name' => 'Dr. Aminah binti Mohd Said',
                'email' => 'supervisor.gombak@iium.edu.my',
                'password' => Hash::make('Supervise@2024'),
                'phone' => '+60131234567',
                'address' => 'Wangsa Maju, Kuala Lumpur',
                'education_level' => 'PhD',
                'education_specialization' => 'Psychology',
                'teaching_specialization' => 'Behavioral Therapy',
                'date_of_birth' => '1978-12-03',
                'role' => 'supervisor',
                'status' => 'active',
                'centre_id' => '01',
                'position' => 'Program Supervisor',
                'about' => 'Program supervisor with expertise in behavioral interventions',
                'centre_location' => 'Gombak',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Additional AJK for comprehensive testing
            [
                'iium_id' => '1543298',
                'name' => 'Siti Fatimah binti Abdullah',
                'email' => 'fatimah.abdullah@iium.edu.my',
                'password' => Hash::make('AJK@2024'),
                'phone' => '+60167891234',
                'address' => 'Bandar Baru Bangi, Selangor',
                'education_level' => 'Degree',
                'education_specialization' => 'Social Work',
                'teaching_specialization' => 'Family Support Services',
                'date_of_birth' => '1990-06-20',
                'role' => 'ajk',
                'status' => 'active',
                'centre_id' => '01',
                'position' => 'Community Liaison Officer',
                'about' => 'AJK member specializing in family support and community outreach',
                'centre_location' => 'Gombak',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $userCount = 0;
        foreach ($users as $user) {
            try {
                DB::table('staffs')->insertOrIgnore($user);
                $userCount++;
            } catch (\Exception $e) {
                // Skip if already exists
            }
        }

        $this->command->line("   ✓ Seeded {$userCount} testing guide staff");
    }

    /**
     * Seed asset categories and types for testing
     */
    private function seedAssetData(): void
    {
        $this->command->info('   📦 Seeding asset categories and types...');

        // Asset categories
        $categories = [
            ['id' => 1, 'category_name' => 'Medical Equipment', 'category_description' => 'Therapy and medical equipment', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'category_name' => 'Furniture', 'category_description' => 'Office and therapy furniture', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'category_name' => 'Electronics', 'category_description' => 'Computers and electronic devices', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($categories as $category) {
            DB::table('asset_categories')->insertOrIgnore($category);
        }

        // Asset types
        $types = [
            ['id' => 1, 'name' => 'Therapy Equipment', 'type_description' => 'Physical and occupational therapy equipment', 'category_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Office Furniture', 'type_description' => 'Desks, chairs, and office furniture', 'category_id' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Computer Equipment', 'type_description' => 'Computers, tablets, and peripherals', 'category_id' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($types as $type) {
            DB::table('asset_parents')->insertOrIgnore($type);
        }

        // Asset locations
        $locations = [
            ['id' => 1, 'location_name' => 'Therapy Room', 'location_description' => 'Main therapy and treatment room', 'centre_id' => '01', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'location_name' => 'Office Area', 'location_description' => 'Administrative office space', 'centre_id' => '01', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'location_name' => 'Computer Lab', 'location_description' => 'Computer training laboratory', 'centre_id' => '01', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($locations as $location) {
            DB::table('asset_locations')->insertOrIgnore($location);
        }

        $this->command->line('   ✓ Asset master data seeded');
    }

    /**
     * Seed existing trainees for testing edit scenarios
     */
    private function seedExistingTrainees(): void
    {
        $this->command->info('   👶 Seeding existing trainees for testing...');

        $trainees = [
            [
                'trainee_id' => 'AU0001',
                'trainee_first_name' => 'Muhammad Irfan',
                'trainee_last_name' => 'bin Zainal',
                'trainee_email' => 'existing.trainee@gmail.com',
                'ic_number' => '100815-03-1234',
                'trainee_date_of_birth' => '2010-08-15',
                'gender' => 'Male',
                'trainee_phone_number' => '+60123456789',
                'trainee_address' => 'Taman Gombak, Kuala Lumpur',
                'trainee_condition' => 'Autism Spectrum Disorder',
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'status' => 'active',
                'guardian_name' => 'Zainab binti Ahmad',
                'guardian_phone' => '+60198765432',
                'guardian_email' => 'zainab.ahmad@yahoo.com',
                'guardian_relationship' => 'Mother',
                'guardian_address' => 'Taman Gombak, Kuala Lumpur',
                'emergency_contact_name' => 'Ahmad bin Zainal',
                'emergency_contact_phone' => '+60131234567',
                'emergency_contact_relationship' => 'Father',
                'photo_consent' => true,
                'services_consent' => true,
                'data_consent' => true,
                'registration_date' => '2024-01-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $traineeCount = 0;
        foreach ($trainees as $trainee) {
            try {
                DB::table('trainees')->insertOrIgnore($trainee);
                $traineeCount++;
            } catch (\Exception $e) {
                // Skip if already exists
            }
        }

        $this->command->line("   ✓ Seeded {$traineeCount} existing trainees");
    }

    /**
     * Seed existing volunteers for testing
     */
    private function seedExistingVolunteers(): void
    {
        $this->command->info('   🙋 Seeding existing volunteers for testing...');

        $volunteers = [
            [
                'volunteer_id' => 'VL0001',
                'name' => 'Existing Volunteer',
                'email' => 'existing.volunteer@gmail.com',
                'phone' => '+60167891234',
                'address' => 'Kuala Lumpur',
                'date_of_birth' => '1995-05-20',
                'gender' => 'Female',
                'occupation' => 'Student',
                'skills' => 'Teaching, Counseling',
                'availability' => 'Weekends',
                'centre_id' => '01',
                'status' => 'active',
                'motivation' => 'Want to help special needs children',
                'registration_date' => '2024-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $volunteerCount = 0;
        foreach ($volunteers as $volunteer) {
            try {
                DB::table('volunteers')->insertOrIgnore($volunteer);
                $volunteerCount++;
            } catch (\Exception $e) {
                // Skip if already exists
            }
        }

        $this->command->line("   ✓ Seeded {$volunteerCount} existing volunteers");
    }

    /**
     * Seed existing contact messages for testing
     */
    private function seedExistingContactMessages(): void
    {
        $this->command->info('   📧 Seeding existing contact messages...');

        $messages = [
            [
                'name' => 'Previous Inquirer',
                'email' => 'previous.inquirer@gmail.com',
                'phone' => '+60123456789',
                'subject' => 'Previous Inquiry',
                'message' => 'This is a previous inquiry for testing purposes.',
                'inquiry_type' => 'general',
                'status' => 'replied',
                'centre_id' => '01',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(3),
            ],
        ];

        $messageCount = 0;
        foreach ($messages as $message) {
            try {
                DB::table('contact_messages')->insertOrIgnore($message);
                $messageCount++;
            } catch (\Exception $e) {
                // Skip if already exists
            }
        }

        $this->command->line("   ✓ Seeded {$messageCount} existing contact messages");
    }

    /**
     * Seed realistic activities for testing comprehensive scenarios
     */
    private function seedRealisticActivities(): void
    {
        $this->command->info('   🏃 Seeding realistic activities for testing...');

        $activities = [
            [
                'id' => 1,
                'activity_name' => 'Program Terapi Pertuturan untuk Kanak-kanak Autisme - Sesi Pagi',
                'activity_description' => 'Program terapi pertuturan yang direka khas untuk kanak-kanak dengan spektrum autisme. Fokus kepada peningkatan kemahiran komunikasi verbal, interaksi sosial, dan perkembangan bahasa menggunakan pendekatan terapeutik berasaskan bukti.',
                'category' => 'Speech Therapy',
                'centre_id' => '01',
                'duration_weeks' => 12,
                'sessions_per_week' => 2,
                'session_duration_minutes' => 60,
                'max_participants' => 4,
                'learning_outcomes' => json_encode([
                    'Meningkatkan kemahiran komunikasi verbal dan bukan verbal',
                    'Membangunkan kemahiran interaksi sosial asas',
                    'Mengembangkan pemahaman bahasa reseptif dan ekspresif',
                    'Memperkukuh keyakinan diri dalam komunikasi harian'
                ]),
                'activity_location' => 'Bilik Terapi 1',
                'instructor_id' => 1, // Dr. Lakshmi
                'is_active' => true,
                'times_conducted' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'activity_name' => 'Program Terapi Pertuturan untuk Kanak-kanak Autisme - Sesi Petang',
                'activity_description' => 'Program terapi pertuturan sesi petang dengan pendekatan yang sama tetapi direka untuk kanak-kanak yang lebih sesuai dengan waktu petang. Menggunakan kaedah terapi play-based dan structured learning.',
                'category' => 'Speech Therapy',
                'centre_id' => '01',
                'duration_weeks' => 12,
                'sessions_per_week' => 2,
                'session_duration_minutes' => 60,
                'max_participants' => 4,
                'learning_outcomes' => json_encode([
                    'Meningkatkan kemahiran komunikasi dalam persekitaran yang tenang',
                    'Pembangunan vocabulary dan syntax',
                    'Latihan artikulasi dan pronunciation',
                    'Kemahiran pragmatik dalam komunikasi sosial'
                ]),
                'activity_location' => 'Bilik Terapi 2',
                'instructor_id' => 2, // Ustaz Ahmad
                'is_active' => true,
                'times_conducted' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'activity_name' => 'Program Terapi Okupasi untuk Down Syndrome',
                'activity_description' => 'Program terapi okupasi komprehensif untuk kanak-kanak dengan Down Syndrome, memfokuskan kepada kemahiran motor halus, kemandirian harian, dan kemahiran kognitif praktikal.',
                'category' => 'Learning Support',
                'centre_id' => '01',
                'duration_weeks' => 16,
                'sessions_per_week' => 2,
                'session_duration_minutes' => 90,
                'max_participants' => 6,
                'learning_outcomes' => json_encode([
                    'Membangunkan kemahiran motor halus dan koordinasi',
                    'Meningkatkan kemandirian dalam aktiviti harian',
                    'Memperkukuh kemahiran kognitif dan pemecahan masalah',
                    'Latihan kemahiran sosial dalam konteks aktiviti'
                ]),
                'activity_location' => 'Bilik Terapi 3',
                'instructor_id' => 3, // Dr. Aminah
                'is_active' => true,
                'times_conducted' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        try {
            foreach ($activities as $activity) {
                DB::table('activities')->insertOrIgnore($activity);
            }
            $this->command->line('   ✓ Realistic activities seeded');
        } catch (\Exception $e) {
            $this->command->line('   ⚠️ Activities table may not exist - skipping');
        }
    }

    /**
     * Seed activity occurrences for testing time conflicts
     */
    private function seedActivitySessions(): void
    {
        $this->command->info('   📅 Seeding activity occurrences for conflict testing...');

        $sessions = [
            [
                'id' => 1,
                'activity_id' => 1,
                'session_name' => 'Sesi Pengenalan dan Assessment',
                'session_description' => 'Sesi pertama program - pengenalan kepada terapis dan assessment awal kemahiran komunikasi',
                'session_date' => '2025-01-06',
                'start_time' => '09:30:00',
                'end_time' => '10:30:00',
                'venue' => 'Bilik Terapi 1',
                'room_number' => 'T1',
                'max_participants' => 4,
                'attendance_marked' => false,
                'instructor_id' => 1,
                'supervisor_id' => 3,
                'session_status' => 'scheduled',
                'priority' => 'normal',
                'session_objectives' => 'Melakukan assessment awal kemahiran komunikasi dan menetapkan objektif individual',
                'session_materials' => json_encode([
                    'Visual communication cards',
                    'Assessment checklist',
                    'Recording device',
                    'Sensory toys'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'activity_id' => 2,
                'session_name' => 'Sesi Petang - Assessment dan Pengenalan',
                'session_description' => 'Sesi assessment untuk program terapi pertuturan sesi petang',
                'session_date' => '2025-01-06',
                'start_time' => '14:00:00',
                'end_time' => '15:00:00',
                'venue' => 'Bilik Terapi 2',
                'room_number' => 'T2',
                'max_participants' => 4,
                'attendance_marked' => false,
                'instructor_id' => 2,
                'supervisor_id' => 3,
                'session_status' => 'scheduled',
                'priority' => 'normal',
                'session_objectives' => 'Assessment kemahiran komunikasi untuk sesi petang',
                'session_materials' => json_encode([
                    'Assessment tools',
                    'Communication boards',
                    'Sensory materials'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        try {
            foreach ($sessions as $session) {
                DB::table('activity_occurrences')->insertOrIgnore($session);
            }
            $this->command->line('   ✓ Activity occurrences seeded');
        } catch (\Exception $e) {
            $this->command->line('   ⚠️ Activity occurrences table may not exist - skipping');
        }
    }

    /**
     * Seed additional realistic trainees for activity testing
     */
    private function seedAdditionalTrainees(): void
    {
        $this->command->info('   👶 Seeding additional trainees for activity testing...');

        $trainees = [
            [
                'trainee_id' => 'AU0002',
                'trainee_first_name' => 'Ahmad Danial',
                'trainee_last_name' => 'bin Mohd Azmi',
                'trainee_email' => 'danial.family@gmail.com',
                'ic_number' => '160812-03-2567',
                'trainee_phone_number' => '+60123456789',
                'trainee_date_of_birth' => '2016-08-12',
                'gender' => 'Male',
                'trainee_address' => 'No. 25, Jalan Gombak Setia 3/7, Taman Gombak Setia, 53100 Kuala Lumpur',
                'trainee_condition' => 'Autism Spectrum Disorder (Moderate) - Delayed speech development, repetitive behaviors, sensory sensitivities',
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'status' => 'active',
                'guardian_name' => 'Siti Rahmah binti Abdullah',
                'guardian_phone' => '+60123456789',
                'guardian_email' => 'rahmah.abdullah@yahoo.com',
                'guardian_relationship' => 'Mother',
                'guardian_address' => 'No. 25, Jalan Gombak Setia 3/7, Taman Gombak Setia, 53100 Kuala Lumpur',
                'emergency_contact_name' => 'Mohd Azmi bin Hassan',
                'emergency_contact_phone' => '+60198765432',
                'emergency_contact_relationship' => 'Father',
                'photo_consent' => true,
                'services_consent' => true,
                'data_consent' => true,
                'registration_date' => '2024-11-15',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'trainee_id' => 'AU0003',
                'trainee_first_name' => 'Nur Aisyah',
                'trainee_last_name' => 'binti Hassan',
                'trainee_email' => 'aisyah.hassan@hotmail.com',
                'ic_number' => '180305-08-1234',
                'trainee_phone_number' => '+60198765432',
                'trainee_date_of_birth' => '2018-03-05',
                'gender' => 'Female',
                'trainee_address' => 'No. 12, Jalan Wangsa Maju 2/15, Taman Wangsa Maju, 53300 Kuala Lumpur',
                'trainee_condition' => 'Autism Spectrum Disorder (Mild) - High functioning autism, selective mutism in social situations',
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'status' => 'active',
                'guardian_name' => 'Fatimah binti Ismail',
                'guardian_phone' => '+60198765432',
                'guardian_email' => 'fatimah.ismail@gmail.com',
                'guardian_relationship' => 'Mother',
                'guardian_address' => 'No. 12, Jalan Wangsa Maju 2/15, Taman Wangsa Maju, 53300 Kuala Lumpur',
                'emergency_contact_name' => 'Hassan bin Ahmad',
                'emergency_contact_phone' => '+60131234567',
                'emergency_contact_relationship' => 'Father',
                'photo_consent' => true,
                'services_consent' => true,
                'data_consent' => true,
                'registration_date' => '2024-10-20',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'trainee_id' => 'DS0002',
                'trainee_first_name' => 'Muhammad Haziq',
                'trainee_last_name' => 'bin Abdul Rahman',
                'trainee_email' => 'haziq.family@gmail.com',
                'ic_number' => '140610-11-3456',
                'trainee_phone_number' => '+60131234567',
                'trainee_date_of_birth' => '2014-06-10',
                'gender' => 'Male',
                'trainee_address' => 'No. 8, Jalan Bandar Tun Razak 1/3, Bandar Tun Razak, 56000 Cheras, Kuala Lumpur',
                'trainee_condition' => 'Down Syndrome - Intellectual disability, delayed motor development, heart condition (managed)',
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'status' => 'active',
                'guardian_name' => 'Noraini binti Sulaiman',
                'guardian_phone' => '+60131234567',
                'guardian_email' => 'noraini.sulaiman@yahoo.com',
                'guardian_relationship' => 'Mother',
                'guardian_address' => 'No. 8, Jalan Bandar Tun Razak 1/3, Bandar Tun Razak, 56000 Cheras, Kuala Lumpur',
                'emergency_contact_name' => 'Abdul Rahman bin Mohammad',
                'emergency_contact_phone' => '+60167891234',
                'emergency_contact_relationship' => 'Father',
                'photo_consent' => true,
                'services_consent' => true,
                'data_consent' => true,
                'registration_date' => '2024-09-10',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        $traineeCount = 0;
        foreach ($trainees as $trainee) {
            try {
                DB::table('trainees')->insertOrIgnore($trainee);
                $traineeCount++;
            } catch (\Exception $e) {
                // Skip if already exists
            }
        }

        $this->command->line("   ✓ Seeded {$traineeCount} additional trainees for activity testing");
    }
}
