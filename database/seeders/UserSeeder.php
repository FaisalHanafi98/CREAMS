<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with Malaysian staff members following exact DATABASE_ARCHITECTURE.txt specifications
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding Malaysian staff members...');

        $faker = Faker::create('ms_MY');
        
        // Define centres
        $centres = ['01', '02', '03', '04'];
        $centreNames = ['Gombak', 'Kuantan', 'Pagoh', 'Gambang'];
        
        // Malaysian name pools for realistic demographics
        $malayNames = [
            'male' => ['Ahmad', 'Mohammad', 'Abdul Rahman', 'Mohd Azlan', 'Farid', 'Haziq', 'Irfan', 'Kamal', 'Lutfi', 'Nazim'],
            'female' => ['Siti Aishah', 'Nur Fatimah', 'Amirah', 'Zarina', 'Farah', 'Khadijah', 'Mariam', 'Norazlina', 'Rosnah', 'Suraya']
        ];
        
        $chineseNames = [
            'male' => ['Wei Ming', 'Kai Xin', 'Jun Hao', 'Zhi Wei', 'Li Cheng', 'Boon Seng', 'Chee Keong', 'Hock Lim'],
            'female' => ['Li Ying', 'Mei Ling', 'Hui Shan', 'Xin Yi', 'Jia Min', 'Sock Choo', 'Pei Fen', 'Seok Lan']
        ];
        
        $indianNames = [
            'male' => ['Raj Kumar', 'Suresh', 'Anand', 'Krishnan', 'Ravi', 'Deepak', 'Ganesh', 'Murugan'],
            'female' => ['Priya', 'Kavitha', 'Indira', 'Shanti', 'Rohini', 'Meera', 'Kamala', 'Devi']
        ];

        // IIUM-style ID prefixes
        $prefixes = ['AVRK', 'BTNS', 'CLMD', 'DRPQ', 'EFST', 'GLMN', 'HJKL', 'MNPQ', 'RSTV', 'WXYZ'];

        $totalUsers = 0;

        foreach ($centres as $index => $centreId) {
            $centreName = $centreNames[$index];
            
            // Create admin for each centre (1 per centre)
            $adminData = $this->createUser('admin', $centreId, $centreName, $faker, $prefixes, $malayNames, $chineseNames, $indianNames);
            DB::table('users')->insert($adminData);
            $totalUsers++;

            // Create supervisors (2-3 per centre)
            $supervisorCount = $faker->numberBetween(2, 3);
            for ($s = 0; $s < $supervisorCount; $s++) {
                $supervisorData = $this->createUser('supervisor', $centreId, $centreName, $faker, $prefixes, $malayNames, $chineseNames, $indianNames);
                DB::table('users')->insert($supervisorData);
                $totalUsers++;
            }

            // Create teachers (15-20 per centre)
            $teacherCount = $faker->numberBetween(15, 20);
            for ($t = 0; $t < $teacherCount; $t++) {
                $teacherData = $this->createUser('teacher', $centreId, $centreName, $faker, $prefixes, $malayNames, $chineseNames, $indianNames);
                DB::table('users')->insert($teacherData);
                $totalUsers++;
            }

            // Create AJK members (8-10 per centre)
            $ajkCount = $faker->numberBetween(8, 10);
            for ($a = 0; $a < $ajkCount; $a++) {
                $ajkData = $this->createUser('ajk', $centreId, $centreName, $faker, $prefixes, $malayNames, $chineseNames, $indianNames);
                DB::table('users')->insert($ajkData);
                $totalUsers++;
            }

            $this->command->line("   ✅ Created staff for {$centreName} centre");
        }

        $this->command->info("👥 Successfully seeded {$totalUsers} staff members across all centres");
        
        // Show role distribution
        $roleStats = DB::table('users')->select('role', DB::raw('count(*) as count'))->groupBy('role')->get();
        foreach ($roleStats as $stat) {
            $this->command->line("   📊 {$stat->role}: {$stat->count} users");
        }
    }

    /**
     * Create a user with Malaysian-specific data
     */
    private function createUser($role, $centreId, $centreName, $faker, $prefixes, $malayNames, $chineseNames, $indianNames)
    {
        // Determine ethnicity with Malaysian demographics (60% Malay, 25% Chinese, 15% Indian)
        $ethnicityRand = rand(1, 100);
        if ($ethnicityRand <= 60) {
            $ethnicity = 'malay';
        } elseif ($ethnicityRand <= 85) {
            $ethnicity = 'chinese';
        } else {
            $ethnicity = 'indian';
        }
        
        $gender = $faker->randomElement(['male', 'female']);
        $name = $this->getNameByEthnicity($ethnicity, $gender, $malayNames, $chineseNames, $indianNames, $faker);
        
        // Generate unique email (avoiding duplicates)
        $emailPrefix = strtolower(str_replace([' ', 'binti ', 'bin '], ['.', '', ''], $name));
        $email = $emailPrefix . '.' . $role . rand(100, 999) . '@creams.edu.my';
        
        $positionMap = [
            'admin' => 'Centre Administrator',
            'supervisor' => 'Program Supervisor',
            'teacher' => $faker->randomElement(['Therapist', 'Special Education Teacher', 'Intervention Specialist']),
            'ajk' => 'Committee Member'
        ];

        return [
            'iium_id' => $faker->randomElement($prefixes) . sprintf('%04d', $faker->unique()->numberBetween(1000, 9999)),
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'phone' => $this->generateMalaysianPhone($faker),
            'address' => $this->generateMalaysianAddress($faker, $centreName),
            'education_level' => $this->getEducationLevel($role, $faker),
            'education_specialization' => $this->getEducationSpecialization($role, $faker),
            'teaching_specialization' => $this->getTeachingSpecialization($role, $faker),
            'date_of_birth' => $faker->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'role' => $role,
            'status' => 'active',
            'centre_id' => $centreId,
            'position' => $positionMap[$role],
            'about' => $this->getAboutText($role),
            'centre_location' => $centreName,
            'user_last_accessed_at' => $faker->dateTimeBetween('-30 days', 'now'),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    private function getNameByEthnicity($ethnicity, $gender, $malayNames, $chineseNames, $indianNames, $faker)
    {
        switch ($ethnicity) {
            case 'malay':
                $firstName = $faker->randomElement($malayNames[$gender]);
                $lastName = $faker->randomElement(['bin Abdullah', 'binti Siti', 'bin Ahmad', 'binti Fatimah', 'bin Ali', 'binti Khadijah']);
                return $firstName . ' ' . $lastName;
            case 'chinese':
                return $faker->randomElement($chineseNames[$gender]) . ' ' . $faker->randomElement(['Tan', 'Lee', 'Lim', 'Wong', 'Ng', 'Teh']);
            case 'indian':
                $lastName = $faker->randomElement(['Rajoo', 'Muthu', 'Krishnan', 'Devi', 'Nair', 'Pillai']);
                return $faker->randomElement($indianNames[$gender]) . ' ' . $lastName;
            default:
                return $faker->randomElement($malayNames[$gender]);
        }
    }

    private function generateMalaysianPhone($faker)
    {
        return '+60' . $faker->randomElement(['3', '9', '7', '4']) . '-' . $faker->numerify('###-####');
    }

    private function generateMalaysianAddress($faker, $centreName)
    {
        $streets = ['Jalan Makmur', 'Jalan Sejahtera', 'Jalan Harmoni', 'Jalan Perdana', 'Jalan Cemerlang'];
        $areas = ['Taman', 'Bandar', 'Kampung', 'Desa'];
        
        return $faker->randomElement($streets) . ', ' . 
               $faker->randomElement($areas) . ' ' . $faker->words(2, true) . ', ' .
               $faker->postcode() . ' ' . $centreName;
    }

    private function getEducationLevel($role, $faker)
    {
        $levels = [
            'admin' => ['PhD', 'Master\'s Degree', 'Bachelor\'s Degree'],
            'supervisor' => ['Master\'s Degree', 'Bachelor\'s Degree'],
            'teacher' => ['Bachelor\'s Degree', 'Master\'s Degree', 'Diploma'],
            'ajk' => ['Bachelor\'s Degree', 'Diploma', 'Certificate']
        ];
        
        return $faker->randomElement($levels[$role]);
    }

    private function getEducationSpecialization($role, $faker)
    {
        $specializations = [
            'admin' => 'Special Education Administration',
            'supervisor' => $faker->randomElement(['Special Education', 'Occupational Therapy', 'Speech Therapy', 'Psychology']),
            'teacher' => $faker->randomElement(['Special Education', 'Occupational Therapy', 'Speech-Language Pathology', 'Physical Therapy', 'Music Therapy', 'Art Therapy', 'Behavioral Therapy']),
            'ajk' => $faker->randomElement(['Business Administration', 'Social Work', 'Community Development'])
        ];
        
        return $specializations[$role];
    }

    private function getTeachingSpecialization($role, $faker)
    {
        if ($role === 'ajk') {
            return 'Administrative Support';
        }
        
        if ($role === 'admin') {
            return 'Special Needs Management';
        }
        
        return $faker->randomElement([
            'Autism Spectrum Disorders', 'Learning Disabilities', 'Intellectual Disabilities',
            'Physical Disabilities', 'Sensory Impairments', 'Behavioral Disorders'
        ]);
    }

    private function getAboutText($role)
    {
        $aboutTexts = [
            'admin' => 'Experienced administrator in special education and rehabilitation services.',
            'supervisor' => 'Supervisor overseeing rehabilitation programs and staff coordination.',
            'teacher' => 'Dedicated to providing quality therapeutic and educational services to individuals with special needs.',
            'ajk' => 'Supporting the centre\'s administrative functions and community outreach programs.'
        ];
        
        return $aboutTexts[$role];
    }
}