<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TraineeSeeder extends Seeder
{
    /**
     * Seed the trainees table with Malaysian trainee data following DATABASE_ARCHITECTURE.txt specifications
     */
    public function run(): void
    {
        $this->command->info('🧒 Seeding Malaysian trainees...');
        
        $faker = Faker::create('en_MY');
        
        // Get available centres
        $centres = DB::table('centres')->select('centre_id', 'centre_name')->get();
        
        if ($centres->isEmpty()) {
            $this->command->error('No centres found! Run CentreSeeder first.');
            return;
        }
        
        // Malaysian names by ethnicity
        $malayNames = [
            'male' => ['Ahmad', 'Muhammad', 'Mohd', 'Ismail', 'Ibrahim', 'Yusof', 'Abdul', 'Kamal', 'Rizal', 'Hafiz'],
            'female' => ['Nur', 'Siti', 'Nor', 'Noor', 'Fatimah', 'Zainab', 'Aishah', 'Farah', 'Zulaikha', 'Khairiah'],
            'surnames' => ['Abdullah', 'Ahmad', 'Mohamed', 'Ibrahim', 'Ismail', 'Hassan', 'Rahman', 'Othman', 'Ali', 'Omar']
        ];
        
        $chineseNames = [
            'male' => ['Wei Ming', 'Jian Hao', 'Chen Wei', 'Jun Feng', 'Yong Tao', 'Kun Xiang'],
            'female' => ['Mei Li', 'Hui Xiu', 'Yan Ying', 'Qi Jing', 'Yue Fang', 'Lin Yu'],
            'surnames' => ['Tan', 'Lim', 'Lee', 'Wong', 'Ng', 'Cheong', 'Yap', 'Ong', 'Chin', 'Ho']
        ];
        
        $indianNames = [
            'male' => ['Raj Kumar', 'Suresh', 'Ravi', 'Vijay', 'Ganesh', 'Arun', 'Prakash'],
            'female' => ['Priya', 'Lakshmi', 'Devi', 'Shanti', 'Meena', 'Kavitha', 'Anjali'],
            'surnames' => ['Pillai', 'Naidu', 'Gopal', 'Nair', 'Singh', 'Sharma', 'Krishnan']
        ];
        
        // Special needs conditions with realistic prevalence
        $conditions = [
            'Autism Spectrum Disorder',
            'Down Syndrome',
            'Cerebral Palsy',
            'Intellectual Disability',
            'ADHD',
            'Learning Disabilities',
            'Speech and Language Disorders',
            'Hearing Impairment',
            'Visual Impairment',
            'Physical Disability',
            'Multiple Disabilities'
        ];
        
        // Malaysian locations for addresses
        $malaysianStates = ['Selangor', 'Kuala Lumpur', 'Pahang', 'Johor', 'Perak'];
        
        // Create 50 trainees with realistic data
        for ($i = 1; $i <= 50; $i++) {
            // Determine ethnicity with Malaysian demographics
            $ethnicityRand = rand(1, 100);
            if ($ethnicityRand <= 60) {
                $ethnicity = 'malay';
            } elseif ($ethnicityRand <= 85) {
                $ethnicity = 'chinese';
            } else {
                $ethnicity = 'indian';
            }
            
            $gender = $faker->randomElement(['Male', 'Female']);
            $genderKey = strtolower($gender);
            
            // Generate names based on ethnicity
            switch ($ethnicity) {
                case 'malay':
                    $firstName = $faker->randomElement($malayNames[$genderKey]);
                    $lastName = ($gender === 'Male' ? 'bin ' : 'binti ') . $faker->randomElement($malayNames['surnames']);
                    break;
                case 'chinese':
                    $firstName = $faker->randomElement($chineseNames[$genderKey]);
                    $lastName = $faker->randomElement($chineseNames['surnames']);
                    break;
                case 'indian':
                    $firstName = $faker->randomElement($indianNames[$genderKey]);
                    $lastName = $faker->randomElement($indianNames['surnames']);
                    break;
            }
            
            // Generate realistic Malaysian IC number
            $birthYear = rand(2005, 2018); // Children aged 7-19
            $birthMonth = sprintf('%02d', rand(1, 12));
            $birthDay = sprintf('%02d', rand(1, 28));
            $birthDate = "{$birthYear}-{$birthMonth}-{$birthDay}";
            
            $icYear = substr($birthYear, -2);
            $placeOfBirth = sprintf('%02d', rand(1, 16));
            $sequence = sprintf('%03d', rand(1, 999));
            $genderCode = ($gender === 'Male') ? rand(1, 4) * 2 - 1 : rand(1, 5) * 2 - 2;
            $icNumber = "{$icYear}{$birthMonth}{$birthDay}-{$placeOfBirth}-{$sequence}{$genderCode}";
            
            // Select random centre
            $selectedCentre = $centres->random();
            
            // Generate trainee ID
            $centreCode = strtoupper(substr($selectedCentre->centre_name, 0, 3));
            $traineeId = "{$centreCode}-" . sprintf('%04d', $i);
            
            // Generate contact details
            $phonePrefix = $faker->randomElement(['012', '013', '014', '016', '017', '018', '019']);
            $phoneNumber = $phonePrefix . '-' . $faker->numerify('#######');
            
            $cleanFirstName = strtolower(str_replace(' ', '', $firstName));
            $cleanLastName = strtolower(str_replace(['bin ', 'binti '], '', $lastName));
            $email = "{$cleanFirstName}.{$cleanLastName}" . rand(1, 999) . "@gmail.com";
            
            // Generate address
            $state = $faker->randomElement($malaysianStates);
            $streetNum = rand(1, 999);
            $streetNames = ['Merdeka', 'Bunga Raya', 'Melati', 'Mawar', 'Kenanga', 'Harmoni', 'Damai'];
            $streetTypes = ['Jalan', 'Lorong', 'Persiaran', 'Lebuh'];
            $address = "No. {$streetNum}, {$faker->randomElement($streetTypes)} {$faker->randomElement($streetNames)}, Taman {$faker->randomElement($streetNames)}, {$faker->postcode} {$selectedCentre->centre_name}, {$state}";
            
            // Guardian information
            $guardianGender = $faker->randomElement(['Father', 'Mother']);
            $guardianNames = ($ethnicity === 'malay') ? $malayNames : (($ethnicity === 'chinese') ? $chineseNames : $indianNames);
            $guardianFirstName = $faker->randomElement($guardianNames[($guardianGender === 'Father') ? 'male' : 'female']);
            $guardianLastName = ($ethnicity === 'malay') ? $faker->randomElement($malayNames['surnames']) : $lastName;
            $guardianName = $guardianFirstName . ' ' . $guardianLastName;
            $guardianPhone = $faker->randomElement(['012', '013', '019']) . '-' . $faker->numerify('#######');
            $guardianEmail = strtolower(str_replace(' ', '.', $guardianFirstName)) . rand(100, 999) . "@gmail.com";
            
            $traineeData = [
                'trainee_id' => $traineeId,
                'trainee_first_name' => $firstName,
                'trainee_last_name' => $lastName,
                'trainee_email' => $email,
                'ic_number' => $icNumber,
                'trainee_date_of_birth' => $birthDate,
                'gender' => $gender,
                'trainee_phone_number' => $phoneNumber,
                'trainee_address' => $address,
                'trainee_condition' => $faker->randomElement($conditions),
                'centre_id' => $selectedCentre->centre_id,
                'centre_name' => $selectedCentre->centre_name,
                'status' => 'active',
                
                // Guardian information
                'guardian_name' => $guardianName,
                'guardian_relationship' => $guardianGender,
                'guardian_phone' => $guardianPhone,
                'guardian_email' => $guardianEmail,
                'guardian_address' => $address,
                
                // Emergency contact
                'emergency_contact_name' => $faker->boolean(70) ? $faker->name : null,
                'emergency_contact_phone' => $faker->boolean(70) ? $phonePrefix . '-' . $faker->numerify('#######') : null,
                'emergency_contact_relationship' => $faker->boolean(70) ? $faker->randomElement(['Uncle', 'Aunt', 'Grandfather', 'Grandmother']) : null,
                
                // Consent and additional info
                'photo_consent' => $faker->boolean(80),
                'services_consent' => $faker->boolean(90),
                'medical_history' => $faker->boolean(80) ? 
                    "Diagnosed with condition at age " . rand(1, 5) . ". Regular follow-ups at Hospital " . $faker->randomElement(['Kuala Lumpur', 'Selayang', 'Putrajaya']) . "." : null,
                'additional_notes' => $faker->boolean(60) ? 
                    "Responds well to " . $faker->randomElement(['structured routine', 'music therapy', 'visual aids', 'positive reinforcement']) . "." : null,
                
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 month', 'now')
            ];
            
            DB::table('trainees')->insert($traineeData);
        }
        
        $this->command->info('🧒 Successfully seeded 50 trainees with realistic Malaysian data');
        
        // Show distribution by centre
        $centreStats = DB::table('trainees')
            ->select('centre_name', DB::raw('count(*) as count'))
            ->groupBy('centre_name')
            ->get();
            
        foreach ($centreStats as $stat) {
            $this->command->line("   📊 {$stat->centre_name}: {$stat->count} trainees");
        }
    }
}