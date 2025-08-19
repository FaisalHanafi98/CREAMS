<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CREAMSUserSeeder extends Seeder
{
    /**
     * Seed the users table with Malaysian staff members
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding Malaysian staff members...');

        $faker = Faker::create('ms_MY');
        
        // Define centres
        $centres = ['01', '02', '03', '04'];
        $centreNames = ['Gombak', 'Kuantan', 'Pagoh', 'Gambang'];
        
        // Malaysian name pools
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

        // Random prefix generator for user IDs
        $prefixes = ['AVRK', 'BTNS', 'CLMD', 'DRPQ', 'EFST', 'GLMN', 'HJKL', 'MNPQ', 'RSTV', 'WXYZ'];

        $createdUsers = [];
        $totalUsers = 0;

        foreach ($centres as $index => $centreId) {
            $centreName = $centreNames[$index];
            
            // Create admin for each centre (1 per centre)
            $adminPrefix = $faker->randomElement($prefixes);
            $adminGender = $faker->randomElement(['male', 'female']);
            $adminEthnicity = $faker->randomElement(['malay', 'chinese', 'indian']);
            
            $adminName = $this->getNameByEthnicity($adminEthnicity, $adminGender, $malayNames, $chineseNames, $indianNames);
            
            $admin = User::create([
                'iium_id' => $adminPrefix . sprintf('%04d', $faker->unique()->numberBetween(1000, 9999)),
                'name' => $adminName,
                'email' => strtolower(str_replace([' ', 'binti ', 'bin '], ['.', '', ''], $adminName)) . '.admin' . rand(100, 999) . '@creams.edu.my',
                'password' => Hash::make('password123'),
                'phone' => $this->generateMalaysianPhone($faker),
                'address' => $this->generateMalaysianAddress($faker, $centreName),
                'education_level' => $faker->randomElement(['PhD', 'Master\'s Degree', 'Bachelor\'s Degree']),
                'education_specialization' => 'Special Education Administration',
                'teaching_specialization' => 'Special Needs Management',
                'date_of_birth' => $faker->dateTimeBetween('-55 years', '-30 years')->format('Y-m-d'),
                'role' => 'admin',
                'status' => 'active',
                'centre_id' => $centreId,
                'position' => 'Centre Administrator',
                'about' => 'Experienced administrator in special education and rehabilitation services.',
                'centre_location' => $centreName,
                'user_last_accessed_at' => now()
            ]);
            $createdUsers[] = $admin;
            $totalUsers++;

            // Create supervisors (2-3 per centre)
            $supervisorCount = $faker->numberBetween(2, 3);
            for ($s = 0; $s < $supervisorCount; $s++) {
                $supervisorPrefix = $faker->randomElement($prefixes);
                $supervisorGender = $faker->randomElement(['male', 'female']);
                $supervisorEthnicity = $faker->randomElement(['malay', 'chinese', 'indian']);
                
                $supervisorName = $this->getNameByEthnicity($supervisorEthnicity, $supervisorGender, $malayNames, $chineseNames, $indianNames);
                
                $supervisor = User::create([
                    'iium_id' => $supervisorPrefix . sprintf('%04d', $faker->unique()->numberBetween(1000, 9999)),
                    'name' => $supervisorName,
                    'email' => strtolower(str_replace([' ', 'binti ', 'bin '], ['.', '', ''], $supervisorName)) . '.supervisor' . rand(100, 999) . '@creams.edu.my',
                    'password' => Hash::make('password123'),
                    'phone' => $this->generateMalaysianPhone($faker),
                    'address' => $this->generateMalaysianAddress($faker, $centreName),
                    'education_level' => $faker->randomElement(['Master\'s Degree', 'Bachelor\'s Degree']),
                    'education_specialization' => $faker->randomElement(['Special Education', 'Occupational Therapy', 'Speech Therapy', 'Psychology']),
                    'teaching_specialization' => $faker->randomElement(['Learning Disabilities', 'Autism Spectrum Disorders', 'Physical Disabilities']),
                    'date_of_birth' => $faker->dateTimeBetween('-50 years', '-28 years')->format('Y-m-d'),
                    'role' => 'supervisor',
                    'status' => 'active',
                    'centre_id' => $centreId,
                    'position' => 'Program Supervisor',
                    'about' => 'Supervisor overseeing rehabilitation programs and staff coordination.',
                    'centre_location' => $centreName,
                    'user_last_accessed_at' => $faker->dateTimeBetween('-30 days', 'now')
                ]);
                $createdUsers[] = $supervisor;
                $totalUsers++;
            }

            // Create teachers (15-20 per centre)
            $teacherCount = $faker->numberBetween(15, 20);
            for ($t = 0; $t < $teacherCount; $t++) {
                $teacherPrefix = $faker->randomElement($prefixes);
                $teacherGender = $faker->randomElement(['male', 'female']);
                $teacherEthnicity = $faker->randomElement(['malay', 'chinese', 'indian']);
                
                $teacherName = $this->getNameByEthnicity($teacherEthnicity, $teacherGender, $malayNames, $chineseNames, $indianNames);
                
                $teacher = User::create([
                    'iium_id' => $teacherPrefix . sprintf('%04d', $faker->unique()->numberBetween(1000, 9999)),
                    'name' => $teacherName,
                    'email' => strtolower(str_replace([' ', 'binti ', 'bin '], ['.', '', ''], $teacherName)) . '.teacher' . rand(100, 999) . '@creams.edu.my',
                    'password' => Hash::make('password123'),
                    'phone' => $this->generateMalaysianPhone($faker),
                    'address' => $this->generateMalaysianAddress($faker, $centreName),
                    'education_level' => $faker->randomElement(['Bachelor\'s Degree', 'Master\'s Degree', 'Diploma']),
                    'education_specialization' => $faker->randomElement([
                        'Special Education', 'Occupational Therapy', 'Speech-Language Pathology', 
                        'Physical Therapy', 'Music Therapy', 'Art Therapy', 'Behavioral Therapy'
                    ]),
                    'teaching_specialization' => $faker->randomElement([
                        'Autism Spectrum Disorders', 'Learning Disabilities', 'Intellectual Disabilities',
                        'Physical Disabilities', 'Sensory Impairments', 'Behavioral Disorders'
                    ]),
                    'date_of_birth' => $faker->dateTimeBetween('-45 years', '-25 years')->format('Y-m-d'),
                    'role' => 'teacher',
                    'status' => 'active',
                    'centre_id' => $centreId,
                    'position' => $faker->randomElement(['Therapist', 'Special Education Teacher', 'Intervention Specialist']),
                    'about' => 'Dedicated to providing quality therapeutic and educational services to individuals with special needs.',
                    'centre_location' => $centreName,
                    'user_last_accessed_at' => $faker->dateTimeBetween('-7 days', 'now')
                ]);
                $createdUsers[] = $teacher;
                $totalUsers++;
            }

            // Create AJK members (8-10 per centre)
            $ajkCount = $faker->numberBetween(8, 10);
            for ($a = 0; $a < $ajkCount; $a++) {
                $ajkPrefix = $faker->randomElement($prefixes);
                $ajkGender = $faker->randomElement(['male', 'female']);
                $ajkEthnicity = $faker->randomElement(['malay', 'chinese', 'indian']);
                
                $ajkName = $this->getNameByEthnicity($ajkEthnicity, $ajkGender, $malayNames, $chineseNames, $indianNames);
                
                $ajk = User::create([
                    'iium_id' => $ajkPrefix . sprintf('%04d', $faker->unique()->numberBetween(1000, 9999)),
                    'name' => $ajkName,
                    'email' => strtolower(str_replace([' ', 'binti ', 'bin '], ['.', '', ''], $ajkName)) . '.ajk' . rand(100, 999) . '@creams.edu.my',
                    'password' => Hash::make('password123'),
                    'phone' => $this->generateMalaysianPhone($faker),
                    'address' => $this->generateMalaysianAddress($faker, $centreName),
                    'education_level' => $faker->randomElement(['Bachelor\'s Degree', 'Diploma', 'Certificate']),
                    'education_specialization' => $faker->randomElement(['Business Administration', 'Social Work', 'Community Development']),
                    'teaching_specialization' => 'Administrative Support',
                    'date_of_birth' => $faker->dateTimeBetween('-40 years', '-22 years')->format('Y-m-d'),
                    'role' => 'ajk',
                    'status' => 'active',
                    'centre_id' => $centreId,
                    'position' => 'Committee Member',
                    'about' => 'Supporting the centre\'s administrative functions and community outreach programs.',
                    'centre_location' => $centreName,
                    'user_last_accessed_at' => $faker->dateTimeBetween('-14 days', 'now')
                ]);
                $createdUsers[] = $ajk;
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
     * Get name by ethnicity and gender
     */
    private function getNameByEthnicity($ethnicity, $gender, $malayNames, $chineseNames, $indianNames)
    {
        $faker = Faker::create('ms_MY');
        
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

    /**
     * Generate Malaysian phone number
     */
    private function generateMalaysianPhone($faker)
    {
        return '+60' . $faker->randomElement(['3', '9', '7', '4']) . '-' . $faker->numerify('###-####');
    }

    /**
     * Generate Malaysian address
     */
    private function generateMalaysianAddress($faker, $centreName)
    {
        $streets = ['Jalan Makmur', 'Jalan Sejahtera', 'Jalan Harmoni', 'Jalan Perdana', 'Jalan Cemerlang'];
        $areas = ['Taman', 'Bandar', 'Kampung', 'Desa'];
        
        return $faker->randomElement($streets) . ', ' . 
               $faker->randomElement($areas) . ' ' . $faker->words(2, true) . ', ' .
               $faker->postcode() . ' ' . $centreName;
    }
}