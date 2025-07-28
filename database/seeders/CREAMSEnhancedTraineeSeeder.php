<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\Centre;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class CREAMSEnhancedTraineeSeeder extends Seeder
{
    /**
     * Enhanced Malaysian Trainee Seeder with realistic, unique profiles
     * Removes lazy patterns like "Ahmad1Rahman" and creates authentic diversity
     */
    public function run()
    {
        $this->command->info('🇲🇾 Starting Enhanced Malaysian Trainee Seeder with unique profiles...');
        
        try {
            // Safely delete existing trainees
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('trainees')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $faker = Faker::create('en_MY');
            
            // Get available centres
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
            
            // Realistic Malaysian trainee profiles with authentic backgrounds
            $traineeProfiles = [
                // Malay trainees with authentic backgrounds
                ['Ahmad Haziq', 'Abdullah', 'male', 'malay', 8, 'Autism Spectrum Disorder', 'Excellent at pattern recognition, loves building blocks'],
                ['Nur Aisyah', 'Mohamed', 'female', 'malay', 6, 'Down Syndrome', 'Very social child, enjoys music and dancing'],
                ['Muhammad Ariff', 'Hassan', 'male', 'malay', 10, 'Cerebral Palsy', 'Uses wheelchair, excellent at computer skills'],
                ['Siti Safiyyah', 'Ibrahim', 'female', 'malay', 7, 'Intellectual Disability', 'Loves art and drawing, very creative'],
                ['Mohd Hakim', 'Rahman', 'male', 'malay', 12, 'ADHD', 'High energy, excels in sports activities'],
                ['Fatimah Zahra', 'Ahmad', 'female', 'malay', 5, 'Speech and Language Disorders', 'Uses sign language, very expressive'],
                ['Zulkarnain', 'Yusof', 'male', 'malay', 14, 'Learning Disabilities', 'Strong in mathematics, struggles with reading'],
                ['Khairiah Amani', 'Othman', 'female', 'malay', 9, 'Hearing Impairment', 'Wears hearing aids, talented in visual arts'],
                ['Amir Danial', 'Kassim', 'male', 'malay', 11, 'Visual Impairment', 'Uses braille, exceptional memory skills'],
                ['Zainab Husna', 'Ali', 'female', 'malay', 13, 'Physical Disability', 'Amputee, passionate about swimming'],
                
                // Chinese trainees with cultural authenticity
                ['Chen Wei Ming', 'Tan', 'male', 'chinese', 11, 'Autism Spectrum Disorder', 'Gifted in mathematics, loves puzzles'],
                ['Lim Hui Xiu', 'Lim', 'female', 'chinese', 8, 'Cerebral Palsy', 'Uses communication device, enjoys storytelling'],
                ['Wong Jun Feng', 'Wong', 'male', 'chinese', 13, 'Intellectual Disability', 'Great at gardening, loves nature'],
                ['Lee Mei Li', 'Lee', 'female', 'chinese', 6, 'Down Syndrome', 'Cheerful personality, good at following routines'],
                ['Ng Yong Tao', 'Ng', 'male', 'chinese', 15, 'ADHD', 'Athletic, enjoys martial arts training'],
                ['Cheong Yan Ying', 'Cheong', 'female', 'chinese', 7, 'Visual Impairment', 'Musical talent, plays piano'],
                ['Yap Kun Xiang', 'Yap', 'male', 'chinese', 10, 'Learning Disabilities', 'Mechanical aptitude, builds model planes'],
                ['Ong Li Hua', 'Ong', 'female', 'chinese', 12, 'Speech and Language Disorders', 'Communicates through art'],
                ['Chin Jia Hao', 'Chin', 'male', 'chinese', 9, 'Hearing Impairment', 'Sign language interpreter for peers'],
                ['Ho Xin Yi', 'Ho', 'female', 'chinese', 14, 'Multiple Disabilities', 'Determined spirit, loves reading'],
                
                // Indian trainees with diverse backgrounds
                ['Raj Kumar', 'Pillai', 'male', 'indian', 12, 'Autism Spectrum Disorder', 'Computer programming prodigy'],
                ['Priya Lakshmi', 'Naidu', 'female', 'indian', 9, 'Cerebral Palsy', 'Excellent verbal skills, enjoys debates'],
                ['Suresh Ravi', 'Gopal', 'male', 'indian', 14, 'Physical Disability', 'Wheelchair basketball player'],
                ['Kavitha Devi', 'Nair', 'female', 'indian', 8, 'Speech and Language Disorders', 'Uses tablet to communicate'],
                ['Vijay Ganesh', 'Singh', 'male', 'indian', 11, 'Intellectual Disability', 'Kind helper, good with younger children'],
                ['Meena Shanti', 'Sharma', 'female', 'indian', 6, 'Multiple Disabilities', 'Responds well to music therapy'],
                ['Arun Prakash', 'Krishnan', 'male', 'indian', 13, 'Learning Disabilities', 'Artistic talent, creates beautiful drawings'],
                ['Deepa Anjali', 'Menon', 'female', 'indian', 10, 'ADHD', 'High achiever when focused, loves challenges'],
                ['Karthik Dev', 'Raman', 'male', 'indian', 7, 'Hearing Impairment', 'Quick learner, adapts well to new situations'],
                ['Sita Priya', 'Iyer', 'female', 'indian', 15, 'Visual Impairment', 'Academic achiever, wants to be a teacher'],
                
                // Additional diverse profiles
                ['Aishah Batrisyia', 'Hamid', 'female', 'malay', 16, 'Learning Disabilities', 'Excellent baker, loves cooking classes'],
                ['Darren Kai Jun', 'Lim', 'male', 'chinese', 5, 'Autism Spectrum Disorder', 'Exceptional memory for facts and dates'],
                ['Kavya Sree', 'Reddy', 'female', 'indian', 17, 'Physical Disability', 'Advocate for accessibility, student leader'],
                ['Hafiz Izzuddin', 'Omar', 'male', 'malay', 4, 'Down Syndrome', 'Youngest in program, loves sensory play'],
                ['Jasmine Yu Ting', 'Teo', 'female', 'chinese', 18, 'Cerebral Palsy', 'Graduating soon, plans for college'],
            ];
            
            // Generate additional random profiles to reach 50+ total
            $additionalNames = [
                'malay' => [
                    ['Syafiq Hakimi', 'Zakaria', 'male'], ['Nurul Izzati', 'Sulaiman', 'female'],
                    ['Fikri Hazwan', 'Rosli', 'male'], ['Aina Sofea', 'Bakar', 'female'],
                    ['Irfan Hakim', 'Razak', 'male'], ['Alya Damia', 'Nasir', 'female'],
                    ['Azim Danesh', 'Karim', 'male'], ['Sofia Batrisyia', 'Azman', 'female'],
                ],
                'chinese' => [
                    ['Marcus Jia Wei', 'Chong', 'male'], ['Chloe Shi Min', 'Goh', 'female'],
                    ['Ethan Yu Xuan', 'Koh', 'male'], ['Grace Xin Yi', 'Sim', 'female'],
                    ['Bryan Jun Hao', 'Teh', 'male'], ['Ashley Zi Xuan', 'Low', 'female'],
                ],
                'indian' => [
                    ['Arjun Mohan', 'Balasubramaniam', 'male'], ['Divya Priya', 'Rajendran', 'female'],
                    ['Kiran Kumar', 'Selvakumar', 'male'], ['Nisha Devi', 'Ramasamy', 'female'],
                ],
            ];
            
            // Create predefined realistic profiles
            foreach ($traineeProfiles as $index => $profile) {
                [$fullName, $surname, $gender, $ethnicity, $age, $condition, $notes] = $profile;
                $this->createTraineeRecord($faker, $centres, $index + 1, $fullName, $surname, $gender, $ethnicity, $age, $condition, $notes);
            }
            
            // Add additional diverse trainees
            $totalCreated = count($traineeProfiles);
            foreach ($additionalNames as $ethnicity => $names) {
                foreach ($names as $nameData) {
                    if ($totalCreated >= 60) break 2; // Limit to 60 total
                    
                    [$fullName, $surname, $gender] = $nameData;
                    $age = rand(4, 17);
                    $conditions = ['Autism Spectrum Disorder', 'ADHD', 'Learning Disabilities', 'Speech and Language Disorders'];
                    $condition = $faker->randomElement($conditions);
                    $notes = $this->generatePersonalizedNotes($fullName, $condition, $age);
                    
                    $this->createTraineeRecord($faker, $centres, $totalCreated + 1, $fullName, $surname, $gender, $ethnicity, $age, $condition, $notes);
                    $totalCreated++;
                }
            }
            
            $this->command->info("🎉 Enhanced Malaysian Trainee Seeder completed successfully!");
            $this->command->info("📊 Created {$totalCreated} unique trainees with authentic Malaysian diversity");
            
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->command->error('❌ Error in Enhanced Trainee Seeder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function createTraineeRecord($faker, $centres, $i, $fullName, $surname, $gender, $ethnicity, $age, $condition, $notes)
    {
        $nameParts = explode(' ', $fullName);
        $firstName = $nameParts[0];
        $middleName = count($nameParts) > 2 ? $nameParts[1] : '';
        $lastName = $surname;
        
        // Create unique trainee ID
        $traineeId = 'TR' . str_pad($i, 4, '0', STR_PAD_LEFT);
        
        $birthDate = Carbon::now()->subYears($age)->subDays(rand(0, 364));
        $selectedCentre = $centres->random();
        
        // Malaysian locations by state
        $locations = [
            'Selangor' => ['Gombak', 'Petaling Jaya', 'Shah Alam', 'Ampang', 'Klang'],
            'Kuala Lumpur' => ['KLCC', 'Cheras', 'Bangsar', 'Mont Kiara', 'Wangsa Maju'],
            'Johor' => ['Johor Bahru', 'Batu Pahat', 'Muar', 'Kluang'],
            'Perak' => ['Ipoh', 'Taiping', 'Teluk Intan', 'Kampar'],
            'Pahang' => ['Kuantan', 'Temerloh', 'Bentong', 'Raub'],
        ];
        
        $state = $faker->randomElement(array_keys($locations));
        $city = $faker->randomElement($locations[$state]);
        
        // Generate realistic contact info
        $phonePrefix = $faker->randomElement(['011', '012', '013', '014', '015', '016', '017', '018', '019']);
        $phone = $phonePrefix . '-' . $faker->numerify('#######');
        
        $address = $faker->numerify('##') . ', ' . $faker->randomElement(['Jalan', 'Lorong', 'Taman']) . ' ' . 
                  $faker->randomElement(['Merdeka', 'Harmoni', 'Sejahtera', 'Indah', 'Jaya', 'Utama']) . 
                  ', ' . $city . ', ' . $faker->numerify('#####') . ' ' . $state;
        
        // Guardian information with cultural appropriateness
        $guardianRelation = $faker->randomElement(['Father', 'Mother', 'Grandmother', 'Grandfather', 'Aunt', 'Uncle']);
        $guardianName = $this->generateGuardianName($ethnicity, $guardianRelation, $firstName, $lastName);
        $guardianPhone = $phonePrefix . '-' . $faker->numerify('#######');
        
        // Clean guardian name for email
        $cleanName = preg_replace('/[^a-zA-Z\s]/', '', $guardianName);
        $guardianEmail = strtolower(str_replace(' ', '.', $cleanName)) . '@' . $faker->randomElement(['gmail.com', 'yahoo.com', 'hotmail.com']);
        
        // Generate Malaysian IC number based on birth date and gender
        $icPrefix = $birthDate->format('ymd');
        $stateCodes = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16'];
        $stateCode = $faker->randomElement($stateCodes);
        $serialNumber = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $genderDigit = ($gender === 'male') ? rand(0, 4) * 2 + 1 : rand(0, 4) * 2; // Odd for male, even for female
        $icNumber = $icPrefix . '-' . $stateCode . '-' . $serialNumber . $genderDigit;

        $traineeData = [
            'trainee_id' => $traineeId,
            'trainee_first_name' => $firstName,
            'trainee_last_name' => $lastName,
            'ic_number' => $icNumber,
            'gender' => ucfirst($gender), // Fixed: use 'gender' not 'trainee_gender'
            'trainee_date_of_birth' => $birthDate,
            'trainee_phone_number' => $phone,
            'trainee_email' => strtolower($firstName . '.' . $lastName) . '@student.com',
            'trainee_address' => $address,
            'trainee_condition' => $condition,
            'centre_id' => $selectedCentre->centre_id,
            'centre_name' => $selectedCentre->centre_name,
            
            // Guardian details
            'guardian_name' => $guardianName,
            'guardian_relationship' => $guardianRelation,
            'guardian_phone' => $guardianPhone,
            'guardian_email' => $guardianEmail,
            'guardian_address' => $address,
            
            // Emergency contact
            'emergency_contact_name' => $faker->boolean(80) ? $faker->name : null,
            'emergency_contact_phone' => $faker->boolean(80) ? $phonePrefix . '-' . $faker->numerify('#######') : null,
            'emergency_contact_relationship' => $faker->boolean(80) ? 
                $faker->randomElement(['Uncle', 'Aunt', 'Family Friend', 'Neighbour']) : null,
            
            // Medical and notes
            'medical_history' => "Diagnosed with {$condition} at age " . rand(2, $age-1) . 
                ". Regular follow-ups at " . $faker->randomElement(['Hospital Kuala Lumpur', 'Hospital Selayang', 'Hospital Putrajaya']) . ".",
            'additional_notes' => $notes,
            
            'created_at' => $faker->dateTimeBetween('-8 months', '-1 month'),
            'updated_at' => $faker->dateTimeBetween('-1 month', 'now'),
            'unique_identifier' => 'TRN' . date('Y') . str_pad($i, 7, '0', STR_PAD_LEFT)
        ];
        
        $trainee = Trainee::create($traineeData);
        $this->command->info("✅ Created: {$fullName} (Age {$age}) - {$condition} at {$selectedCentre->centre_name}");
    }
    
    private function generateGuardianName($ethnicity, $relation, $firstName, $lastName)
    {
        switch ($ethnicity) {
            case 'malay':
                $title = match($relation) {
                    'Father' => 'Encik',
                    'Mother' => 'Puan',
                    'Grandmother' => 'Nenek',
                    'Grandfather' => 'Datuk',
                    default => ($relation === 'Aunt' ? 'Cik' : 'Encik')
                };
                return $title . ' ' . fake()->randomElement(['Ahmad', 'Ibrahim', 'Hassan', 'Omar', 'Yusof']) . ' ' . $lastName;
                
            case 'chinese':
                return match($relation) {
                    'Father' => 'Mr. ' . fake()->randomElement(['Tan', 'Lim', 'Wong', 'Lee']) . ' ' . fake()->randomElement(['Wei Ming', 'Jun Hao', 'Chen Wei']),
                    'Mother' => 'Mrs. ' . fake()->randomElement(['Tan', 'Lim', 'Wong', 'Lee']) . ' ' . fake()->randomElement(['Mei Li', 'Hui Xiu', 'Li Hua']),
                    default => fake()->randomElement(['Mr.', 'Mrs.']) . ' ' . $lastName . ' ' . fake()->randomElement(['Wei', 'Jun', 'Ming', 'Li'])
                };
                
            case 'indian':
                return match($relation) {
                    'Father' => 'Mr. ' . fake()->randomElement(['Raman', 'Kumar', 'Raj', 'Suresh']) . ' ' . $lastName,
                    'Mother' => 'Mrs. ' . fake()->randomElement(['Priya', 'Devi', 'Lakshmi', 'Kavitha']) . ' ' . $lastName,
                    default => fake()->randomElement(['Mr.', 'Mrs.']) . ' ' . fake()->randomElement(['Kumar', 'Raj', 'Devi']) . ' ' . $lastName
                };
                
            default:
                return fake()->name;
        }
    }
    
    private function generatePersonalizedNotes($name, $condition, $age)
    {
        $notes = [
            'Autism Spectrum Disorder' => [
                'Excellent attention to detail', 'Prefers structured routines', 'Strong visual learner',
                'Enjoys repetitive activities', 'Good with technology', 'Responds well to visual schedules'
            ],
            'ADHD' => [
                'High energy level', 'Benefits from movement breaks', 'Creative problem solver',
                'Needs frequent positive reinforcement', 'Excels in hands-on activities'
            ],
            'Learning Disabilities' => [
                'Strong verbal skills', 'Benefits from multi-sensory learning', 'Artistic abilities',
                'Good social skills', 'Needs extra time for tasks'
            ],
            'Speech and Language Disorders' => [
                'Uses alternative communication methods', 'Very expressive non-verbally',
                'Good listener', 'Responds well to visual cues', 'Making progress with speech therapy'
            ]
        ];
        
        $baseNotes = $notes[$condition] ?? ['Making good progress in all areas'];
        $selectedNote = fake()->randomElement($baseNotes);
        
        return $selectedNote . ($age >= 10 ? '. Shows independence in daily activities.' : '. Requires assistance with daily tasks.');
    }
}