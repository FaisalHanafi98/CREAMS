<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Seed users with proper role hierarchy and Malaysian email domains
     * Role hierarchy (AJK and Supervisor are same level):
     * - Admin: 5 users (1 per centre) 
     * - Supervisor: 10 users (2 per centre)
     * - Teacher: 30 users (6 per centre)
     * - AJK: 20 users (4 per centre) - Same level as Supervisor
     * Total: 65 users
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding users with proper role hierarchy...');

        // Malaysian email domains
        $emailDomains = [
            'gmail.com', 'hotmail.com', 'yahoo.com.my', 'yahoo.com',
            'outlook.com', 'live.com.my', 'tm.net.my'
        ];

        // Education levels and specializations
        $educationLevels = [
            'Bachelor\'s Degree', 'Master\'s Degree', 'PhD', 'Diploma', 'Certificate'
        ];
        
        $educationSpecializations = [
            'Special Education', 'Psychology', 'Early Childhood Education', 'Rehabilitation Sciences',
            'Speech Therapy', 'Occupational Therapy', 'Physical Therapy', 'Social Work',
            'Educational Psychology', 'Counseling', 'Islamic Studies'
        ];
        
        $teachingSpecializations = [
            'Autism Spectrum Disorders', 'Learning Disabilities', 'Physical Disabilities',
            'Intellectual Disabilities', 'Speech and Language Development', 'Behavioral Interventions',
            'Inclusive Education', 'Assistive Technology', 'Life Skills Training'
        ];
        
        $positions = [
            'admin' => ['Centre Director', 'Assistant Director', 'Administrative Manager'],
            'supervisor' => ['Program Supervisor', 'Clinical Supervisor', 'Academic Supervisor'],
            'teacher' => ['Special Education Teacher', 'Therapy Assistant', 'Learning Support Teacher'],
            'ajk' => ['Committee Member', 'Parent Liaison Officer', 'Community Outreach Coordinator']
        ];

        // Malaysian names (expanded for 60+ users)
        $malaysianNames = [
            // Malay names
            ['Ahmad', 'Salleh'], ['Siti', 'Aminah'], ['Muhammad', 'Ali'], ['Fatimah', 'Ahmad'],
            ['Mohd', 'Hassan'], ['Nurul', 'Ain'], ['Abdul', 'Rahman'], ['Zainab', 'Ibrahim'],
            ['Farid', 'Ismail'], ['Mariam', 'Yusof'], ['Azman', 'Omar'], ['Rohani', 'Bakar'],
            ['Rosli', 'Mohd'], ['Khadijah', 'Daud'], ['Hakim', 'Razak'], ['Aminah', 'Jamal'],
            ['Ismail', 'Abdullah'], ['Norhayati', 'Ismail'], ['Zulkifli', 'Yusof'], ['Rosnah', 'Hassan'],
            ['Badrul', 'Hisham'], ['Zaleha', 'Mohd'], ['Hashim', 'Omar'], ['Zaharah', 'Ahmad'],
            ['Rashid', 'Rahman'], ['Norma', 'Salleh'], ['Razak', 'Ibrahim'], ['Faridah', 'Ali'],
            
            // Chinese names
            ['Lim', 'Wei Ming'], ['Tan', 'Siew Lan'], ['Wong', 'Kah Wai'], ['Lee', 'Mei Lin'],
            ['Ong', 'Beng Soon'], ['Ng', 'Pei Ling'], ['Teo', 'Kian Huat'], ['Yap', 'Ai Ling'],
            ['Chua', 'Jin Wei'], ['Khoo', 'Su Lin'], ['Low', 'Chee Keong'], ['Goh', 'Li Yen'],
            ['Chen', 'Ming Hui'], ['Liu', 'Xin Yi'], ['Zhang', 'Wei Jie'], ['Wang', 'Li Na'],
            ['Lau', 'Teck Seng'], ['Chong', 'Mei Fong'], ['Tee', 'Jun Hao'], ['Yeo', 'Shu Ling'],
            
            // Indian names
            ['Kumar', 'Raman'], ['Devi', 'Lakshmi'], ['Raj', 'Shankar'], ['Priya', 'Nair'],
            ['Suresh', 'Krishnan'], ['Meera', 'Patel'], ['Anand', 'Sharma'], ['Kavitha', 'Rao'],
            ['Deepak', 'Singh'], ['Shanti', 'Das'], ['Ravi', 'Kumar'], ['Kamala', 'Devi'],
            ['Vijay', 'Naidu'], ['Uma', 'Devi'], ['Sanjay', 'Pillai'], ['Geetha', 'Nair'],
            ['Mohan', 'Das'], ['Shanti', 'Kumar'], ['Arjun', 'Singh'], ['Radha', 'Sharma']
        ];

        // Centre IDs (now includes Gombak as 01)
        $centreIds = ['01', '02', '03', '04', '05'];
        $userIndex = 0;
        
        // Function to generate IIUM staff ID (ABCD1234 format)
        $generateStaffId = function() {
            $letters = '';
            for($i = 0; $i < 4; $i++) {
                $letters .= chr(rand(65, 90)); // A-Z
            }
            $numbers = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            return $letters . $numbers;
        };

        // Admin users (1 per centre)
        $this->command->info('   Creating Admin users (1 per centre)...');
        foreach ($centreIds as $index => $centreId) {
            $userData = $this->generateCompleteUserData($userIndex, 'admin', $centreId, $malaysianNames, $emailDomains, $educationLevels, $educationSpecializations, $teachingSpecializations, $positions, $generateStaffId);
            DB::table('users')->insert($userData);
            $userIndex++;
        }

        // Supervisor users (2 per centre)
        $this->command->info('   Creating Supervisor users (2 per centre)...');
        foreach ($centreIds as $centreId) {
            for ($i = 0; $i < 2; $i++) {
                $userData = $this->generateCompleteUserData($userIndex, 'supervisor', $centreId, $malaysianNames, $emailDomains, $educationLevels, $educationSpecializations, $teachingSpecializations, $positions, $generateStaffId);
                DB::table('users')->insert($userData);
                $userIndex++;
            }
        }

        // Teacher users (6 per centre)
        $this->command->info('   Creating Teacher users (6 per centre)...');
        foreach ($centreIds as $centreId) {
            for ($i = 0; $i < 6; $i++) {
                $userData = $this->generateCompleteUserData($userIndex, 'teacher', $centreId, $malaysianNames, $emailDomains, $educationLevels, $educationSpecializations, $teachingSpecializations, $positions, $generateStaffId);
                DB::table('users')->insert($userData);
                $userIndex++;
            }
        }

        // AJK users (4 per centre)
        $this->command->info('   Creating AJK users (4 per centre)...');
        foreach ($centreIds as $centreId) {
            for ($i = 0; $i < 4; $i++) {
                $userData = $this->generateCompleteUserData($userIndex, 'ajk', $centreId, $malaysianNames, $emailDomains, $educationLevels, $educationSpecializations, $teachingSpecializations, $positions, $generateStaffId);
                DB::table('users')->insert($userData);
                $userIndex++;
            }
        }

        $this->command->info("👥 Successfully seeded $userIndex users with proper role hierarchy:");
        $this->command->line("   • Admin: 5 users (1 per centre)");
        $this->command->line("   • Supervisor: 10 users (2 per centre) - Same level as AJK");
        $this->command->line("   • Teacher: 30 users (6 per centre)");
        $this->command->line("   • AJK: 20 users (4 per centre) - Same level as Supervisor");
        $this->command->line("   • All users have comprehensive data: Malaysian names, IIUM staff IDs (ABCD1234), education, emergency contacts, positions, and biographical information");
    }

    /**
     * Generate Malaysian address
     */
    private function generateMalaysianAddress(): string
    {
        $addresses = [
            'Jalan Raja Muda, 50300 Kuala Lumpur',
            'Taman Tun Dr. Ismail, 60000 Kuala Lumpur',
            'Bandar Baru Salak Tinggi, 43900 Sepang, Selangor',
            'Taman Universiti, 81300 Skudai, Johor',
            'Bandar Indera Mahkota, 25200 Kuantan, Pahang',
            'Section 7, 40000 Shah Alam, Selangor',
            'Taman Melawati, 53100 Kuala Lumpur',
            'Bandar Tasik Selatan, 57000 Kuala Lumpur',
            'Taman Johor Jaya, 81100 Johor Bahru, Johor',
            'Bandar Baru Bangi, 43650 Bangi, Selangor'
        ];
        
        return $addresses[array_rand($addresses)];
    }

    /**
     * Get centre location by centre_id
     */
    private function getCentreLocation($centreId): string
    {
        $locations = [
            '01' => 'Gombak, Selangor',
            '02' => 'Kuantan, Pahang', 
            '03' => 'Shah Alam, Selangor',
            '04' => 'Pagoh, Johor',
            '05' => 'Gambang, Pahang'
        ];
        
        return $locations[$centreId] ?? 'Malaysia';
    }

    /**
     * Generate complete user data with all fields
     */
    private function generateCompleteUserData($userIndex, $role, $centreId, $malaysianNames, $emailDomains, $educationLevels, $educationSpecializations, $teachingSpecializations, $positions, $generateStaffId)
    {
        $name = $malaysianNames[$userIndex % count($malaysianNames)];
        $email = strtolower($name[0] . '.' . str_replace(' ', '', $name[1])) . '@' . $emailDomains[array_rand($emailDomains)];
        $staffId = $generateStaffId();
        $gender = rand(0, 1) ? 'Male' : 'Female';
        
        // Age ranges by role
        $ageRanges = [
            'admin' => [1975, 1985], // 38-48 years
            'supervisor' => [1980, 1990], // 33-43 years  
            'teacher' => [1985, 1995], // 28-38 years
            'ajk' => [1990, 2000] // 23-33 years
        ];
        
        $birthYear = rand($ageRanges[$role][0], $ageRanges[$role][1]);
        
        $aboutTexts = [
            'admin' => 'Experienced administrator dedicated to special needs education and rehabilitation services.',
            'supervisor' => 'Skilled supervisor with expertise in program management and clinical oversight.',
            'teacher' => 'Dedicated special education teacher committed to helping children with disabilities reach their potential.',
            'ajk' => 'Active committee member supporting community engagement and rehabilitation programs.'
        ];

        return [
            'id' => $userIndex + 1,
            'staff_id' => $staffId,
            'iium_id' => $staffId,
            'name' => $name[0] . ' ' . $name[1],
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'centre_id' => $centreId,
            'phone' => '+60' . rand(10000000, 99999999),
            'address' => $this->generateMalaysianAddress(),
            'education_level' => $educationLevels[array_rand($educationLevels)],
            'education_specialization' => $educationSpecializations[array_rand($educationSpecializations)],
            'teaching_specialization' => $teachingSpecializations[array_rand($teachingSpecializations)],
            'date_of_birth' => Carbon::create($birthYear, rand(1, 12), rand(1, 28)),
            'position' => $positions[$role][array_rand($positions[$role])],
            'about' => $aboutTexts[$role] . ' Gender: ' . $gender . '. Emergency contact: ' . $name[1] . ' (' . (rand(0, 1) ? 'Spouse' : 'Parent') . ') - +60' . rand(10000000, 99999999),
            'centre_location' => $this->getCentreLocation($centreId),
            'status' => 'active',
            'user_last_accessed_at' => Carbon::now()->subDays(rand(0, 30)),
            'created_at' => Carbon::create(2024, rand(6, 8), rand(1, 28)),
            'updated_at' => now()
        ];
    }
}