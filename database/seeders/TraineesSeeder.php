<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainees;
use App\Models\Centres;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class TraineesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Instead of truncating, we'll delete records more safely with respect to foreign keys
        $this->command->info('Deleting existing trainees...');
        
        try {
            // Temporarily disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Delete existing trainees
            DB::table('trainees')->delete();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $faker = Faker::create('en_MY'); // Malaysian faker
            
            // First check what centres actually exist in the database
            $centres = Centres::pluck('centre_name')->toArray();
            
            if (empty($centres)) {
                $this->command->error('No centres found in the database. Please add centres first.');
                $this->command->info('Creating a default centre for testing...');
                
                // Create a default centre if none exists
                $defaultCentre = new Centres();
                $defaultCentre->centre_id = 'DEFAULT';
                $defaultCentre->centre_name = 'Default Centre';
                $defaultCentre->centre_status = 'active';
                $defaultCentre->save();
                
                $centres = ['Default Centre'];
            }
            
            $this->command->info('Found these centres: ' . implode(', ', $centres));
            
            // Malay first names (male)
            $malayMaleNames = [
                'Ahmad', 'Muhammad', 'Mohd', 'Ismail', 'Ibrahim', 'Yusof', 'Aziz', 'Nasir', 
                'Kamal', 'Rizal', 'Hafiz', 'Amir', 'Azman', 'Farid', 'Zulkifli', 'Nazri',
                'Faisal', 'Zainal', 'Khairul', 'Anuar', 'Azlan', 'Hakim', 'Rahim', 'Saiful'
            ];
            
            // Malay first names (female)
            $malayFemaleNames = [
                'Nurul', 'Siti', 'Nor', 'Noor', 'Fatimah', 'Zainab', 'Aishah', 'Aminah',
                'Farah', 'Zulaikha', 'Khairiah', 'Rahmah', 'Safiah', 'Hasnah', 'Azizah',
                'Halimah', 'Mariam', 'Rohani', 'Salmah', 'Khadijah', 'Sakinah', 'Amalina'
            ];
            
            // Malay last names / surnames / bin/binti father's names
            $malayLastNames = [
                'bin Abdullah', 'bin Ahmad', 'bin Mohamed', 'bin Ibrahim', 'bin Ismail',
                'bin Hassan', 'bin Yusof', 'binti Abdullah', 'binti Ahmad', 'binti Mohamed',
                'bin Rahman', 'bin Othman', 'bin Ali', 'bin Omar', 'bin Kassim',
                'Abdul Rahman', 'Abdul Hamid', 'Abdul Aziz', 'Abdul Kadir', 'Abdul Rahim'
            ];
            
            // Chinese first names (male)
            $chineseMaleNames = [
                'Wei', 'Jian', 'Ming', 'Hao', 'Yong', 'Jun', 'Chen', 'Feng',
                'Cheng', 'Tao', 'Kun', 'Xiang', 'Jie', 'Wen', 'Xiong'
            ];
            
            // Chinese first names (female)
            $chineseFemaleNames = [
                'Mei', 'Li', 'Hui', 'Xiu', 'Yan', 'Ying', 'Qi', 'Jing',
                'Yue', 'Fang', 'Lin', 'Zhen', 'Hong', 'Fen', 'Yu'
            ];
            
            // Chinese last names
            $chineseLastNames = [
                'Tan', 'Lim', 'Lee', 'Wong', 'Ng', 'Cheong', 'Yap', 'Ong',
                'Chin', 'Ho', 'Chong', 'Goh', 'Teo', 'Chan', 'Teh'
            ];
            
            // Indian first names (male)
            $indianMaleNames = [
                'Raj', 'Kumar', 'Suresh', 'Ravi', 'Vijay', 'Ganesh', 'Arun', 'Prakash',
                'Siva', 'Mohan', 'Ramesh', 'Arjun', 'Shan', 'Nathan', 'Raja'
            ];
            
            // Indian first names (female)
            $indianFemaleNames = [
                'Priya', 'Lakshmi', 'Devi', 'Shanti', 'Meena', 'Rani', 'Kavitha', 'Anjali',
                'Sunitha', 'Seetha', 'Deepa', 'Uma', 'Vani', 'Saranya', 'Gayathri'
            ];
            
            // Indian last names
            $indianLastNames = [
                'Pillai', 'Naidu', 'Gopal', 'Raju', 'Nair', 'Patel', 'Singh',
                'Sharma', 'Samy', 'Muthu', 'Chandra', 'Lingam', 'Krishnan', 'Rao', 'Sivan'
            ];
            
            // List of possible conditions
            $conditions = [
                'Autism Spectrum Disorder',
                'Down Syndrome',
                'Cerebral Palsy',
                'Hearing Impairment',
                'Visual Impairment',
                'Intellectual Disability',
                'Physical Disability',
                'Speech and Language Disorder',
                'Learning Disability',
                'Multiple Disabilities'
            ];

            // Create 20 trainees
            for ($i = 1; $i <= 20; $i++) {
                // Determine ethnicity based on desired ratio (70% Malay)
                $ethnicity = $faker->randomElement(['malay', 'malay', 'malay', 'malay', 'malay', 'malay', 'malay', 'chinese', 'chinese', 'indian']);
                
                $gender = $faker->randomElement(['male', 'female']);
                
                // Set appropriate names based on ethnicity and gender
                if ($ethnicity === 'malay') {
                    if ($gender === 'male') {
                        $firstName = $faker->randomElement($malayMaleNames);
                    } else {
                        $firstName = $faker->randomElement($malayFemaleNames);
                    }
                    $lastName = $faker->randomElement($malayLastNames);
                } elseif ($ethnicity === 'chinese') {
                    if ($gender === 'male') {
                        $firstName = $faker->randomElement($chineseMaleNames);
                    } else {
                        $firstName = $faker->randomElement($chineseFemaleNames);
                    }
                    $lastName = $faker->randomElement($chineseLastNames);
                } else { // indian
                    if ($gender === 'male') {
                        $firstName = $faker->randomElement($indianMaleNames);
                    } else {
                        $firstName = $faker->randomElement($indianFemaleNames);
                    }
                    $lastName = $faker->randomElement($indianLastNames);
                }
                
                // Generate a birthdate for a child (between 4 and 17 years old)
                $birthdate = $faker->dateTimeBetween('-17 years', '-4 years')->format('Y-m-d');
                
                // Select a random centre from the available centres
                $centreName = $faker->randomElement($centres);
                
                // Create the trainee record
                $trainee = new Trainees();
                $trainee->trainee_first_name = $firstName;
                $trainee->trainee_last_name = $lastName;
                
                // Email format depends on ethnicity
                if ($ethnicity === 'malay') {
                    // Malay typically use first name in email
                    $trainee->trainee_email = strtolower($firstName) . mt_rand(1000, 9999) . '@gmail.com';
                } else {
                    // Chinese and Indian often use first name + last name
                    $trainee->trainee_email = strtolower($firstName) . '.' . strtolower($lastName) . '@gmail.com';
                }
                
                // Generate Malaysian mobile number format
                $trainee->trainee_phone_number = '01' . $faker->numberBetween(0, 9) . '-' . $faker->numberBetween(1000000, 9999999);
                
                $trainee->trainee_date_of_birth = $birthdate;
                $trainee->trainee_avatar = 'images/default-avatar.jpg'; // Default avatar
                $trainee->trainee_attendance = $faker->numberBetween(0, 30);
                $trainee->trainee_condition = $faker->randomElement($conditions);
                $trainee->centre_name = $centreName;
                
                // Guardian information (typically parents)
                // For Malay, use bin/binti for relationship
                if ($ethnicity === 'malay') {
                    $honorific = $gender === 'male' ? 'bin' : 'binti';
                    // Extract father's name from last name if it has bin/binti
                    if (strpos($lastName, 'bin') !== false || strpos($lastName, 'binti') !== false) {
                        $fatherName = str_replace(['bin ', 'binti '], '', $lastName);
                        $guardianFirstName = $faker->randomElement($malayMaleNames); // Father's name
                        $guardianLastName = $fatherName;
                    } else {
                        $guardianFirstName = $faker->randomElement($malayMaleNames);
                        $guardianLastName = $lastName;
                    }
                    
                    // Typically, parent would be referenced as father/mother of child
                    $guardianRelationship = $faker->randomElement(['Bapa', 'Ibu', 'Penjaga']);
                } elseif ($ethnicity === 'chinese') {
                    $guardianFirstName = $gender === 'male' ? $faker->randomElement($chineseMaleNames) : $faker->randomElement($chineseFemaleNames);
                    $guardianLastName = $lastName; // Same family name
                    $guardianRelationship = $faker->randomElement(['Father', 'Mother', 'Guardian']);
                } else { // indian
                    $guardianFirstName = $gender === 'male' ? $faker->randomElement($indianMaleNames) : $faker->randomElement($indianFemaleNames);
                    $guardianLastName = $lastName;
                    $guardianRelationship = $faker->randomElement(['Father', 'Mother', 'Guardian']);
                }
                
                $trainee->guardian_name = $guardianFirstName . ' ' . $guardianLastName;
                $trainee->guardian_relationship = $guardianRelationship;
                
                // Generate Malaysian mobile number format for guardian
                $trainee->guardian_phone = '01' . $faker->numberBetween(0, 9) . '-' . $faker->numberBetween(1000000, 9999999);
                
                // Guardian email
                if ($ethnicity === 'malay') {
                    $trainee->guardian_email = strtolower($guardianFirstName) . mt_rand(1000, 9999) . '@gmail.com';
                } else {
                    $trainee->guardian_email = strtolower($guardianFirstName) . '.' . strtolower($guardianLastName) . '@gmail.com';
                }
                
                // Malaysian address structure
                $addressNum = $faker->buildingNumber();
                $streetTypes = ['Jalan', 'Lorong', 'Persiaran', 'Lebuh'];
                $streetNames = [
                    'Merdeka', 'Bunga Raya', 'Melati', 'Mawar', 'Kenanga', 'Cempaka',
                    'Sentosa', 'Harmoni', 'Damai', 'Setiawangsa', 'Perdana', 'Saujana',
                    'Wawasan', 'Setia', 'Indah', 'Bistari', 'Bahagia', 'Sejahtera'
                ];
                
                $taman = [
                    'Taman', 'Bandar', 'Kampung', 'Desa'
                ];
                
                $districts = [
                    'Gombak', 'Petaling Jaya', 'Shah Alam', 'Ampang', 'Klang',
                    'Kuantan', 'Temerloh', 'Bentong', 'Indera Mahkota', 'Gambang',
                    'Batu Pahat', 'Pagoh', 'Muar', 'Kluang', 'Segamat'
                ];
                
                $states = [
                    'Selangor', 'Kuala Lumpur', 'Pahang', 'Johor', 'Perak', 'Pulau Pinang'
                ];
                
                // Construct a Malaysian-style address
                $tamanName = $faker->randomElement($taman) . ' ' . $faker->randomElement($streetNames);
                $jalan = $faker->randomElement($streetTypes) . ' ' . $faker->randomElement($streetNames);
                $district = $faker->randomElement($districts);
                $state = $faker->randomElement($states);
                $postcode = $faker->numberBetween(10000, 99999);
                
                $address = "No. $addressNum, $jalan,\n$tamanName,\n$postcode $district,\n$state, Malaysia";
                
                $trainee->guardian_address = $address;
                
                // Medical history with local context
                $medicalHistories = [
                    'Diagnosed with ' . $trainee->trainee_condition . ' at age ' . $faker->numberBetween(1, 5) . ' at Hospital ' . $faker->randomElement(['Kuala Lumpur', 'Selayang', 'Putrajaya', 'Sungai Buloh', 'Sultan Ahmad Shah', 'Tengku Ampuan Afzan']),
                    'Regular check-ups at Klinik Kesihatan ' . $faker->randomElement(['Gombak', 'Kuantan', 'Pagoh', 'Ampang', 'Selayang', 'Petaling Jaya']),
                    'Previously attended therapy sessions at ' . $faker->randomElement(['Hospital Rehabilitasi Cheras', 'Pusat Pemulihan PERKESO', 'National Autism Society of Malaysia (NASOM)', 'Malaysian Association for the Blind']),
                    'No significant medical complications apart from the primary condition',
                    'Allergic to ' . $faker->randomElement(['seafood', 'peanuts', 'eggs', 'milk', 'certain medications']),
                    'Required hospitalization for ' . $faker->randomElement(['respiratory issues', 'seizures', 'surgery', 'infection']) . ' in ' . $faker->numberBetween(2018, 2022)
                ];
                
                // Randomly select 1-3 items from medical histories and combine them
                $selectedHistories = $faker->randomElements($medicalHistories, $faker->numberBetween(1, 3));
                $trainee->medical_history = implode(". ", $selectedHistories);
                
                // Additional notes with local context
                $additionalNotes = [
                    'Requires assistance with ' . $faker->randomElement(['mobility', 'communication', 'self-care', 'social interaction']),
                    'Prefers structured ' . $faker->randomElement(['routine', 'environment', 'learning sessions']),
                    'Responds well to ' . $faker->randomElement(['music therapy', 'art therapy', 'physical activities', 'one-on-one attention']),
                    'Shows interest in ' . $faker->randomElement(['drawing', 'music', 'computers', 'sports', 'reading']),
                    'Previously attended ' . $faker->randomElement(['PPKI program', 'special education class', 'home-schooling']),
                    'Parent is a member of ' . $faker->randomElement(['Persatuan Ibu Bapa Kanak-kanak Istimewa', 'Parent support group', 'Malaysian Rare Disorders Society']),
                    'Sibling also attends ' . $faker->randomElement(['this center', 'another rehabilitation center', 'special education class'])
                ];
                
                // Randomly select 0-2 items from additional notes
                if ($faker->boolean(70)) { // 70% chance to have additional notes
                    $selectedNotes = $faker->randomElements($additionalNotes, $faker->numberBetween(1, 2));
                    $trainee->additional_notes = implode(". ", $selectedNotes);
                }
                
                // Emergency contact (typically another family member)
                if ($faker->boolean(70)) { // 70% chance to have emergency contact
                    if ($ethnicity === 'malay') {
                        // For Malay, could be other relatives with different surnames
                        $emergencyRelationship = $faker->randomElement(['Pak Cik', 'Mak Cik', 'Nenek', 'Datuk', 'Abang', 'Kakak']);
                        if ($faker->boolean(50)) { // 50% chance to be same last name
                            $emergencyName = $faker->randomElement(array_merge($malayMaleNames, $malayFemaleNames)) . ' ' . $guardianLastName;
                        } else {
                            $emergencyName = $faker->randomElement(array_merge($malayMaleNames, $malayFemaleNames)) . ' ' . $faker->randomElement($malayLastNames);
                        }
                    } elseif ($ethnicity === 'chinese') {
                        $emergencyRelationship = $faker->randomElement(['Uncle', 'Aunt', 'Grandfather', 'Grandmother', 'Elder Sibling']);
                        // For Chinese, could be other relatives with different surnames
                        if ($faker->boolean(50)) { // 50% chance to be same last name
                            $emergencyName = $faker->randomElement(array_merge($chineseMaleNames, $chineseFemaleNames)) . ' ' . $lastName;
                        } else {
                            $emergencyName = $faker->randomElement(array_merge($chineseMaleNames, $chineseFemaleNames)) . ' ' . $faker->randomElement($chineseLastNames);
                        }
                    } else { // indian
                        $emergencyRelationship = $faker->randomElement(['Uncle', 'Aunt', 'Grandfather', 'Grandmother', 'Elder Brother', 'Elder Sister']);
                        // For Indian, could be other relatives with different surnames
                        if ($faker->boolean(50)) { // 50% chance to be same last name
                            $emergencyName = $faker->randomElement(array_merge($indianMaleNames, $indianFemaleNames)) . ' ' . $lastName;
                        } else {
                            $emergencyName = $faker->randomElement(array_merge($indianMaleNames, $indianFemaleNames)) . ' ' . $faker->randomElement($indianLastNames);
                        }
                    }
                    
                    $trainee->emergency_contact_name = $emergencyName;
                    $trainee->emergency_contact_phone = '01' . $faker->numberBetween(0, 9) . '-' . $faker->numberBetween(1000000, 9999999);
                    $trainee->emergency_contact_relationship = $emergencyRelationship;
                }
                
                // IMPORTANT: Set created_at with proper format and with some variation in dates
                // This is critical for the "New Trainees (30 days)" counter to work
                $createdDate = null;
                
                // Ensure some trainees are created within the last 30 days (for the counter to work)
                if ($i <= 10) { // First half of trainees
                    // Created between 31-180 days ago (older trainees)
                    $createdDate = $faker->dateTimeBetween('-180 days', '-31 days')->format('Y-m-d H:i:s');
                } else { // Second half of trainees
                    // Created within the last 30 days (newer trainees)
                    $createdDate = $faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s');
                }
                
                // Set the timestamps explicitly
                $trainee->created_at = $createdDate;
                $trainee->updated_at = $faker->dateTimeBetween($createdDate, 'now')->format('Y-m-d H:i:s');
                
                // Save the trainee
                $trainee->save();
                
                $this->command->info("Created trainee: {$firstName} {$lastName} at {$centreName} (Created: {$createdDate})");
            }
            
            $this->command->info('Trainees seeding completed successfully!');
            
        } catch (\Exception $e) {
            // Make sure to re-enable foreign key checks even if there's an error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->command->error('Error seeding trainees: ' . $e->getMessage());
            throw $e;
        }
    }
}