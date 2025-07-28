<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\Centre;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class CREAMSTraineeSeeder extends Seeder
{
    /**
     * Run the database seeds with realistic Malaysian data and correct field mappings.
     * This seeder has been refactored to fix all field mismatches identified in the Trainee Module audit.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🇲🇾 Starting Malaysian Trainee Seeder with corrected field mappings...');
        
        try {
            // Safely delete existing trainees with foreign key consideration
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('trainees')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $faker = Faker::create('en_MY'); // Malaysian locale faker
            
            // Get available centres with their IDs
            $centres = Centre::select('centre_id', 'centre_name')->get();
            
            if ($centres->isEmpty()) {
                $this->command->error('No centres found! Creating default centre...');
                
                $defaultCentre = Centre::create([
                    'centre_id' => '99',
                    'centre_name' => 'Default Centre',
                    'centre_status' => 'active'
                ]);
                
                $centres = collect([$defaultCentre]);
            }
            
            $this->command->info('Found centres: ' . $centres->pluck('centre_name')->implode(', '));
            
            // Realistic Malaysian names by ethnicity
            $malayNames = [
                'male' => [
                    'Ahmad', 'Muhammad', 'Mohd', 'Ismail', 'Ibrahim', 'Yusof', 'Abdul',
                    'Kamal', 'Rizal', 'Hafiz', 'Amir', 'Azman', 'Farid', 'Zulkifli',
                    'Faisal', 'Zainal', 'Khairul', 'Anuar', 'Azlan', 'Hakim', 'Rahim'
                ],
                'female' => [
                    'Nur', 'Siti', 'Nor', 'Noor', 'Fatimah', 'Zainab', 'Aishah',
                    'Farah', 'Zulaikha', 'Khairiah', 'Rahmah', 'Safiah', 'Azizah',
                    'Halimah', 'Mariam', 'Rohani', 'Salmah', 'Khadijah', 'Sakinah'
                ],
                'surnames' => [
                    'Abdullah', 'Ahmad', 'Mohamed', 'Ibrahim', 'Ismail', 'Hassan',
                    'Rahman', 'Othman', 'Ali', 'Omar', 'Kassim', 'Hamid', 'Aziz'
                ]
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
                'Autism Spectrum Disorder',    // Most common
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
            
            // Malaysian states and cities for addresses
            $malaysianLocations = [
                'Selangor' => ['Gombak', 'Petaling Jaya', 'Shah Alam', 'Ampang', 'Klang', 'Subang Jaya'],
                'Kuala Lumpur' => ['KLCC', 'Cheras', 'Bangsar', 'Mont Kiara', 'Wangsa Maju'],
                'Pahang' => ['Kuantan', 'Temerloh', 'Bentong', 'Raub', 'Pekan'],
                'Johor' => ['Johor Bahru', 'Batu Pahat', 'Muar', 'Kluang', 'Segamat'],
                'Perak' => ['Ipoh', 'Taiping', 'Teluk Intan', 'Kampar', 'Parit Buntar']
            ];
            
            // Create 50 trainees with realistic data
            for ($i = 1; $i <= 50; $i++) {
                // Determine ethnicity with Malaysian demographics (60% Malay, 25% Chinese, 15% Indian)
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
                
                // Generate realistic Malaysian IC number (YYMMDD-PB-###G)
                $birthYear = rand(2005, 2018); // Children aged 7-19
                $birthMonth = sprintf('%02d', rand(1, 12));
                $birthDay = sprintf('%02d', rand(1, 28));
                $birthDate = "{$birthYear}-{$birthMonth}-{$birthDay}";
                
                $icYear = substr($birthYear, -2);
                $placeOfBirth = sprintf('%02d', rand(1, 16)); // Malaysian state codes
                $sequence = sprintf('%03d', rand(1, 999));
                $genderCode = ($gender === 'Male') ? rand(1, 4) * 2 - 1 : rand(1, 5) * 2 - 2; // Odd for male, even for female
                $icNumber = "{$icYear}{$birthMonth}{$birthDay}-{$placeOfBirth}-{$sequence}{$genderCode}";
                
                // Select random centre
                $selectedCentre = $centres->random();
                
                // Generate trainee ID based on centre
                $centreCode = strtoupper(substr($selectedCentre->centre_name, 0, 3));
                $traineeId = "{$centreCode}-" . sprintf('%04d', $i);
                
                // Generate Malaysian phone number
                $phonePrefix = $faker->randomElement(['012', '013', '014', '016', '017', '018', '019']);
                $phoneNumber = $phonePrefix . '-' . $faker->numerify('#######');
                
                // Generate email
                $cleanFirstName = strtolower(str_replace(' ', '', $firstName));
                $cleanLastName = strtolower(str_replace(['bin ', 'binti '], '', $lastName));
                $email = "{$cleanFirstName}.{$cleanLastName}" . rand(1, 999) . "@gmail.com";
                
                // Generate Malaysian address
                $state = $faker->randomElement(array_keys($malaysianLocations));
                $city = $faker->randomElement($malaysianLocations[$state]);
                $streetNum = rand(1, 999);
                $streetNames = ['Merdeka', 'Bunga Raya', 'Melati', 'Mawar', 'Kenanga', 'Harmoni', 'Damai'];
                $streetTypes = ['Jalan', 'Lorong', 'Persiaran', 'Lebuh'];
                $address = "No. {$streetNum}, {$faker->randomElement($streetTypes)} {$faker->randomElement($streetNames)}, Taman {$faker->randomElement($streetNames)}, {$faker->postcode} {$city}, {$state}";
                
                // Guardian information
                $guardianGender = $faker->randomElement(['Father', 'Mother']);
                $guardianNames = ($ethnicity === 'malay') ? $malayNames : (($ethnicity === 'chinese') ? $chineseNames : $indianNames);
                $guardianFirstName = $faker->randomElement($guardianNames[($guardianGender === 'Father') ? 'male' : 'female']);
                $guardianLastName = ($ethnicity === 'malay') ? $faker->randomElement($malayNames['surnames']) : $lastName;
                $guardianName = $guardianFirstName . ' ' . $guardianLastName;
                $guardianPhone = $faker->randomElement(['012', '013', '019']) . '-' . $faker->numerify('#######');
                $guardianEmail = strtolower(str_replace(' ', '.', $guardianFirstName)) . rand(100, 999) . "@gmail.com";
                
                // Create trainee with correct field mappings
                $traineeData = [
                    // FIXED: Using correct database field names
                    'trainee_id' => $traineeId,                                    // FIXED: Added required field
                    'trainee_first_name' => $firstName,
                    'trainee_last_name' => $lastName,
                    'trainee_email' => $email,
                    'ic_number' => $icNumber,                                      // FIXED: Added required field
                    'trainee_date_of_birth' => $birthDate,
                    'gender' => $gender,
                    'trainee_phone_number' => $phoneNumber,
                    'trainee_address' => $address,                                 // FIXED: Using correct field name
                    'avatar' => null,                                              // FIXED: Correct field name
                    'trainee_condition' => $faker->randomElement($conditions),
                    'centre_name' => $selectedCentre->centre_name,
                    'centre_id' => $selectedCentre->centre_id,                     // FIXED: Added missing field
                    'course_id' => null,                                          // Optional - can be assigned later
                    'status' => 'active',
                    'photo_consent' => $faker->boolean(80),                        // FIXED: Added required field
                    'services_consent' => $faker->boolean(90),                     // FIXED: Added required field
                    
                    // Guardian information
                    'guardian_name' => $guardianName,
                    'guardian_relationship' => $guardianGender,
                    'guardian_phone' => $guardianPhone,
                    'guardian_email' => $guardianEmail,
                    'guardian_address' => $address, // Same as trainee for simplicity
                    
                    // Emergency contact (optional)
                    'emergency_contact_name' => $faker->boolean(70) ? $faker->name : null,
                    'emergency_contact_phone' => $faker->boolean(70) ? $phonePrefix . '-' . $faker->numerify('#######') : null,
                    'emergency_contact_relationship' => $faker->boolean(70) ? $faker->randomElement(['Uncle', 'Aunt', 'Grandfather', 'Grandmother']) : null,
                    
                    // Medical and additional information
                    'medical_history' => $faker->boolean(80) ? 
                        "Diagnosed with condition at age " . rand(1, 5) . ". Regular follow-ups at Hospital " . $faker->randomElement(['Kuala Lumpur', 'Selayang', 'Putrajaya']) . "." : null,
                    'additional_notes' => $faker->boolean(60) ? 
                        "Responds well to " . $faker->randomElement(['structured routine', 'music therapy', 'visual aids', 'positive reinforcement']) . "." : null,
                    
                    // Timestamps with variation
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => $faker->dateTimeBetween('-1 month', 'now')
                ];
                
                // FIXED: Remove non-existent fields that were causing errors
                // - trainee_attendance (doesn't exist in database)
                // - trainee_last_accessed_at (doesn't exist in database) 
                // - registered_by (doesn't exist in database)
                
                $trainee = Trainee::create($traineeData);
                
                $this->command->info("✅ Created: {$firstName} {$lastName} (ID: {$traineeId}) at {$selectedCentre->centre_name}");
            }
            
            $this->command->info('🎉 Malaysian Trainee Seeder completed successfully!');
            $this->command->info('📊 Created 50 trainees with realistic Malaysian data and correct field mappings');
            
        } catch (\Exception $e) {
            // Ensure foreign key checks are re-enabled even on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->command->error('❌ Error in Malaysian Trainee Seeder: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}