<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CREAMSSeederFoundationManagement extends Seeder
{
    /**
     * CREAMS Foundation Management Seeder
     * Seeds: Malaysian centres, real Gombak users, demo users
     * Preserves: Real data patterns and Malaysian context
     */
    public function run(): void
    {
        $this->command->info('🏛️ Seeding CREAMS Foundation Management...');
        
        // Seed Malaysian rehabilitation centres (preserves current centre logic)
        $this->seedMalaysianCentres();
        
        // Preserve real Gombak users if backup exists (preserves current data protection)
        if (file_exists(database_path('real_data_backup.json'))) {
            $this->seedRealGombakUsers();
        }
        
        // Seed demo users for other centres (preserves current demo patterns)
        $this->seedDemoMalaysianUsers();
        
        $this->command->info('✅ Foundation Management seeding completed');
    }

    /**
     * Seed Malaysian rehabilitation centres (preserves current centre data)
     */
    private function seedMalaysianCentres(): void
    {
        $this->command->info('   📍 Creating Malaysian rehabilitation centres...');
        
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
                'centre_description' => 'CREAMS Gombak Centre - Pusat rehabilitasi utama di IIUM yang menyediakan perkhidmatan pendidikan dan terapi keperluan khas yang komprehensif.',
                'centre_facilities' => json_encode([
                    'Pentadbiran Utama', 'Bilik Terapi', 'Bilik Integrasi Sensori', 'Makmal Komputer',
                    'Perpustakaan', 'Bilik Terapi Seni', 'Gimnasium', 'Bilik Terapi Muzik',
                    'Bilik Terapi Pertuturan', 'Bilik Terapi Pekerjaan', 'Pusat Penyelidikan'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'Kuantan',
                'centre_address' => 'Jalan Sultan Ahmad Shah, 25300 Kuantan, Pahang',
                'centre_phone' => '+609-513-4000',
                'centre_email' => 'kuantan@creams.edu.my',
                'centre_capacity' => '120',
                'centre_manager' => 'Dr. Siti Nurhaliza binti Ahmad',
                'centre_manager_contact' => '+609-513-4001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Kuantan Centre - Pusat rehabilitasi wilayah yang menyediakan perkhidmatan pendidikan dan terapi keperluan khas untuk kanak-kanak dan remaja.',
                'centre_facilities' => json_encode([
                    'Bilik Terapi', 'Bilik Aktiviti', 'Perpustakaan Kecil', 'Bilik Komputer',
                    'Bilik Terapi Pertuturan', 'Ruang Rekreasi', 'Bilik Konseling'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
            [
                'centre_id' => '03',
                'centre_name' => 'Johor Bahru',
                'centre_address' => 'Jalan Skudai, 81300 Johor Bahru, Johor',
                'centre_phone' => '+607-553-4000',
                'centre_email' => 'jb@creams.edu.my',
                'centre_capacity' => '100',
                'centre_manager' => 'Dr. Rajesh Kumar a/l Subramaniam',
                'centre_manager_contact' => '+607-553-4001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Johor Bahru Centre - Pusat rehabilitasi selatan yang menumpukan perkhidmatan terapi dan pendidikan khas untuk komuniti berbilang kaum.',
                'centre_facilities' => json_encode([
                    'Bilik Terapi', 'Ruang Seni', 'Bilik Muzik', 'Bilik Komputer',
                    'Perpustakaan', 'Bilik Konseling Keluarga'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
            [
                'centre_id' => '04',
                'centre_name' => 'Kota Kinabalu',
                'centre_address' => 'Jalan Lintas, 88300 Kota Kinabalu, Sabah',
                'centre_phone' => '+608-832-4000',
                'centre_email' => 'kk@creams.edu.my',
                'centre_capacity' => '90',
                'centre_manager' => 'Puan Jennifer Lim Siew Choo',
                'centre_manager_contact' => '+608-832-4001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Kota Kinabalu Centre - Pusat rehabilitasi Sabah yang menyediakan perkhidmatan kepada komuniti pelbagai etnik di Borneo.',
                'centre_facilities' => json_encode([
                    'Bilik Terapi', 'Bilik Aktiviti', 'Ruang Kebudayaan', 'Bilik Komputer'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
            [
                'centre_id' => '05',
                'centre_name' => 'Nilai',
                'centre_address' => 'Persiaran Olahraga, 71800 Nilai, Negeri Sembilan',
                'centre_phone' => '+606-798-4000',
                'centre_email' => 'nilai@creams.edu.my',
                'centre_capacity' => '80',
                'centre_manager' => 'Dr. Tan Wei Ming',
                'centre_manager_contact' => '+606-798-4001',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Nilai Centre - Pusat rehabilitasi Negeri Sembilan yang mengkhusus dalam terapi perkembangan awal kanak-kanak.',
                'centre_facilities' => json_encode([
                    'Bilik Terapi Awal Kanak-kanak', 'Taman Permainan Terapeutik', 'Bilik Keluarga'
                ]),
                'opening_time' => '08:00:00',
                'is_active' => true,
            ],
        ];

        foreach ($centres as $centre) {
            $centre['created_at'] = now();
            $centre['updated_at'] = now();
            DB::table('centres')->insertOrIgnore($centre);
        }
        
        $this->command->line('      ✓ Created 5 Malaysian rehabilitation centres');
    }

    /**
     * Restore real Gombak users from backup (preserves current real data)
     */
    private function seedRealGombakUsers(): void
    {
        $this->command->info('   👤 Restoring real Gombak centre staff...');
        
        $realData = json_decode(file_get_contents(database_path('real_data_backup.json')), true);
        
        if (isset($realData['users']) && !empty($realData['users'])) {
            foreach ($realData['users'] as $user) {
                // Convert array to proper format and preserve all fields
                $userData = (array) $user;
                DB::table('users')->insertOrIgnore($userData);
            }
            
            $this->command->line('      ✓ Restored ' . count($realData['users']) . ' real Gombak staff members');
        } else {
            $this->command->line('      ⚠ No real user data found in backup');
        }
    }

    /**
     * Seed demo Malaysian users (preserves current Malaysian naming patterns)
     */
    private function seedDemoMalaysianUsers(): void
    {
        $this->command->info('   👥 Creating demo Malaysian staff...');
        
        $faker = Faker::create('ms_MY');
        
        // Malaysian staff names with proper cultural context (preserves current patterns)
        $malayStaff = [
            ['name' => 'Dr. Ahmad Fauzi bin Abdul Rahman', 'role' => 'admin', 'position' => 'Pengarah Pusat'],
            ['name' => 'Puan Siti Zaleha binti Mohamed', 'role' => 'supervisor', 'position' => 'Penyelia Terapi'],
            ['name' => 'Encik Mohd Hafiz bin Ibrahim', 'role' => 'teacher', 'position' => 'Guru Pendidikan Khas'],
            ['name' => 'Dr. Nurliyana binti Hassan', 'role' => 'teacher', 'position' => 'Ahli Terapi Pertuturan'],
        ];
        
        $chineseStaff = [
            ['name' => 'Dr. Tan Wei Ming', 'role' => 'supervisor', 'position' => 'Pakar Terapi Pekerjaan'],
            ['name' => 'Puan Lim Hui Xin', 'role' => 'teacher', 'position' => 'Guru Seni Terapi'],
            ['name' => 'Encik Wong Jian Hao', 'role' => 'teacher', 'position' => 'Jurutera Bantuan'],
            ['name' => 'Dr. Lee Mei Ling', 'role' => 'teacher', 'position' => 'Pakar Psikologi'],
        ];
        
        $indianStaff = [
            ['name' => 'Dr. Raj Kumar a/l Suresh', 'role' => 'supervisor', 'position' => 'Pakar Fisioterapi'],
            ['name' => 'Puan Priya a/p Ganesh', 'role' => 'teacher', 'position' => 'Guru Muzik Terapi'],
            ['name' => 'Encik Vijay a/l Ravi', 'role' => 'ajk', 'position' => 'Pembantu Pentadbiran'],
            ['name' => 'Dr. Lakshmi a/p Krishnan', 'role' => 'teacher', 'position' => 'Pakar Pemakanan'],
        ];

        $allStaff = array_merge($malayStaff, $chineseStaff, $indianStaff);
        $centres = DB::table('centres')->where('centre_id', '!=', '01')->get();
        
        $totalCreated = 0;
        foreach ($centres as $centre) {
            // Create 4-6 staff per centre with Malaysian context
            $staffCount = rand(4, 6);
            $selectedStaff = collect($allStaff)->shuffle()->take($staffCount);
            
            foreach ($selectedStaff as $staff) {
                $email = strtolower(str_replace(['Dr. ', 'Puan ', 'Encik ', ' a/l ', ' a/p ', ' bin ', ' binti '], '', $staff['name']));
                $email = str_replace(' ', '.', $email) . '@creams.edu.my';
                
                try {
                    DB::table('users')->insertOrIgnore([
                        'iium_id' => 'DEMO' . $centre->centre_id . sprintf('%03d', rand(100, 999)),
                        'name' => $staff['name'],
                        'email' => $email,
                        'password' => Hash::make('password123'),
                        'phone' => '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'address' => $faker->address,
                        'date_of_birth' => $faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
                        'role' => $staff['role'],
                        'status' => 'active',
                        'centre_id' => $centre->centre_id,
                        'position' => $staff['position'],
                        'centre_location' => $centre->centre_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalCreated++;
                } catch (\Exception $e) {
                    // Skip duplicate entries
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalCreated} demo Malaysian staff members");
    }
}