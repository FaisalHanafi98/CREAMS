<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CREAMSSeederClientManagement extends Seeder
{
    /**
     * CREAMS Client Management Seeder
     * Seeds: Malaysian trainees, volunteers, contact messages
     * Preserves: Malaysian cultural context and naming patterns
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding CREAMS Client Management...');
        
        $this->seedMalaysianTrainees();
        $this->seedMalaysianVolunteers();
        $this->seedContactMessages();
        
        $this->command->info('✅ Client Management seeding completed');
    }

    /**
     * Seed Malaysian trainees with proper cultural context
     */
    private function seedMalaysianTrainees(): void
    {
        $this->command->info('   🧒 Creating Malaysian trainees...');
        
        $faker = Faker::create('ms_MY');
        
        // Malaysian disability conditions with proper bilingual terminology and codes
        $conditions = [
            'Autism Spectrum Disorder (Gangguan Spektrum Autisme)' => 'AU',
            'Down Syndrome (Sindrom Down)' => 'DS',
            'Attention Deficit Hyperactivity Disorder (ADHD)' => 'AD',
            'Cerebral Palsy (Palsi Serebrum)' => 'CP',
            'Learning Disability (Ketidakupayaan Pembelajaran)' => 'LD',
            'Intellectual Disability (Ketidakupayaan Intelektual)' => 'ID',
            'Speech and Language Delay (Kelewatan Pertuturan dan Bahasa)' => 'SL',
            'Developmental Delay (Kelewatan Perkembangan)' => 'DD',
            'Hearing Impairment (Orang Kurang Upaya Pendengaran)' => 'HI',
            'Visual Impairment (Orang Kurang Upaya Penglihatan)' => 'VI',
            'Physical Disability (Ketidakupayaan Fizikal)' => 'PD',
        ];

        // Malaysian names by ethnicity with proper cultural patterns
        $malayNames = [
            'male_first' => ['Muhammad', 'Ahmad', 'Mohd', 'Abdul', 'Ismail', 'Yusof', 'Ibrahim', 'Hassan', 'Omar', 'Farid'],
            'female_first' => ['Nur', 'Siti', 'Nor', 'Noor', 'Fatimah', 'Zainab', 'Aishah', 'Farah', 'Zulaikha', 'Aminah'],
            'surnames' => ['Abdullah', 'Ahmad', 'Mohamed', 'Ibrahim', 'Ismail', 'Hassan', 'Rahman', 'Othman', 'Ali', 'Omar']
        ];
        
        $chineseNames = [
            'male_first' => ['Wei Ming', 'Jian Hao', 'Jun Feng', 'Chen Wei', 'Yong Tao', 'Kun Xiang', 'Li Wei', 'Ming Hao'],
            'female_first' => ['Mei Li', 'Hui Xin', 'Yan Ying', 'Qi Jing', 'Yue Fang', 'Lin Yu', 'Xiao Mei', 'Li Hua'],
            'surnames' => ['Tan', 'Lim', 'Lee', 'Wong', 'Ng', 'Cheong', 'Yap', 'Ong', 'Chin', 'Ho', 'Teh', 'Goh']
        ];
        
        $indianNames = [
            'male_first' => ['Raj Kumar', 'Suresh', 'Ravi', 'Vijay', 'Ganesh', 'Arun', 'Prakash', 'Kiran', 'Mohan'],
            'female_first' => ['Priya', 'Lakshmi', 'Devi', 'Shanti', 'Meena', 'Kavitha', 'Anjali', 'Rekha', 'Sita'],
            'surnames' => ['Pillai', 'Naidu', 'Gopal', 'Nair', 'Singh', 'Sharma', 'Krishnan', 'Raman', 'Devi']
        ];

        $centres = DB::table('centres')->get();
        $totalCreated = 0;
        $globalCounter = 1; // Global counter for unique IDs
        
        foreach ($centres as $centre) {
            $traineeCount = rand(25, 45); // Realistic numbers per centre
            
            for ($i = 0; $i < $traineeCount; $i++) {
                // Randomly select ethnicity (60% Malay, 25% Chinese, 15% Indian)
                $ethnicity = $this->getRandomEthnicity();
                $gender = rand(0, 1) ? 'Male' : 'Female';
                
                switch ($ethnicity) {
                    case 'malay':
                        $firstName = $malayNames[$gender === 'Male' ? 'male_first' : 'female_first'][array_rand($malayNames[$gender === 'Male' ? 'male_first' : 'female_first'])];
                        $lastName = $malayNames['surnames'][array_rand($malayNames['surnames'])];
                        $prefix = $gender === 'Male' ? 'bin' : 'binti';
                        $fullLastName = $prefix . ' ' . $lastName;
                        break;
                    case 'chinese':
                        $firstName = $chineseNames[$gender === 'Male' ? 'male_first' : 'female_first'][array_rand($chineseNames[$gender === 'Male' ? 'male_first' : 'female_first'])];
                        $fullLastName = $chineseNames['surnames'][array_rand($chineseNames['surnames'])];
                        break;
                    case 'indian':
                        $firstName = $indianNames[$gender === 'Male' ? 'male_first' : 'female_first'][array_rand($indianNames[$gender === 'Male' ? 'male_first' : 'female_first'])];
                        $lastName = $indianNames['surnames'][array_rand($indianNames['surnames'])];
                        $prefix = $gender === 'Male' ? 'a/l' : 'a/p';
                        $fullLastName = $prefix . ' ' . $lastName;
                        break;
                }

                $birthDate = $faker->dateTimeBetween('-25 years', '-3 years');
                $icNumber = $this->generateMalaysianIC($birthDate);
                
                // Select condition and generate disability-based ID
                $selectedCondition = array_rand($conditions);
                $disabilityCode = $conditions[$selectedCondition];
                $traineeId = $disabilityCode . sprintf('%04d', $globalCounter++);
                
                try {
                    DB::table('trainees')->insertOrIgnore([
                        'trainee_id' => $traineeId,
                        'trainee_first_name' => $firstName,
                        'trainee_last_name' => $fullLastName,
                        'trainee_email' => strtolower(str_replace(' ', '.', $firstName . '.' . str_replace(['bin ', 'binti ', 'a/l ', 'a/p '], '', $fullLastName))) . rand(1, 999) . '@' . collect(['gmail.com', 'outlook.com', 'yahoo.com', 'hotmail.com', 'live.com'])->random(),
                        'ic_number' => $icNumber,
                        'trainee_date_of_birth' => $birthDate->format('Y-m-d'),
                        'gender' => $gender,
                        'trainee_phone_number' => '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'trainee_address' => $this->generateMalaysianAddress($centre->centre_name),
                        'trainee_condition' => $selectedCondition,
                        'centre_id' => $centre->centre_id,
                        'centre_name' => $centre->centre_name,
                        'status' => 'active',
                        'guardian_name' => $this->generateGuardianName($ethnicity, $gender),
                        'guardian_phone' => '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'guardian_email' => $faker->email,
                        'guardian_relationship' => rand(0, 1) ? 'Mother' : 'Father',
                        'guardian_address' => $this->generateMalaysianAddress($centre->centre_name),
                        'emergency_contact_name' => $this->generateGuardianName($ethnicity, rand(0, 1) ? 'Male' : 'Female'),
                        'emergency_contact_phone' => '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'emergency_contact_relationship' => collect(['Grandfather', 'Grandmother', 'Uncle', 'Aunt', 'Brother', 'Sister'])->random(),
                        'photo_consent' => 1, // Mandatory consent
                        'services_consent' => 1, // Mandatory consent
                        'data_consent' => 1, // Third mandatory consent
                        'registration_date' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalCreated++;
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalCreated} Malaysian trainees");
    }

    /**
     * Seed Malaysian volunteers
     */
    private function seedMalaysianVolunteers(): void
    {
        $this->command->info('   🤝 Creating Malaysian volunteers...');
        
        $centres = DB::table('centres')->get();
        $totalCreated = 0;
        
        foreach ($centres as $centre) {
            $volunteerCount = rand(8, 15);
            
            for ($i = 0; $i < $volunteerCount; $i++) {
                $ethnicity = $this->getRandomEthnicity();
                $gender = rand(0, 1) ? 'Male' : 'Female';
                $name = $this->generateMalaysianName($ethnicity, $gender, 'adult');
                
                try {
                    DB::table('volunteers')->insertOrIgnore([
                        'volunteer_id' => 'V' . $centre->centre_id . sprintf('%04d', $i + 1),
                        'name' => $name,
                        'email' => strtolower(str_replace(' ', '.', str_replace(['Dr. ', 'Puan ', 'Encik ', 'bin ', 'binti ', 'a/l ', 'a/p '], '', $name))) . '@volunteer.creams.edu.my',
                        'phone' => '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'address' => $this->generateMalaysianAddress($centre->centre_name),
                        'date_of_birth' => now()->subYears(rand(18, 65))->format('Y-m-d'),
                        'gender' => $gender,
                        'occupation' => collect(['Guru', 'Doktor', 'Jurutera', 'Akauntan', 'Peguam', 'Jururawat', 'Pensyarah', 'Peniaga'])->random(),
                        'skills' => collect(['Seni dan Kraf', 'Muzik', 'Sukan', 'Memasak', 'Komputer', 'Bahasa', 'Matematik', 'Terapi'])->random(),
                        'centre_id' => $centre->centre_id,
                        'status' => collect(['active', 'pending'])->random(),
                        'motivation' => 'Ingin membantu kanak-kanak berkeperluan khas dan menyumbang kepada masyarakat.',
                        'registration_date' => now()->subDays(rand(1, 365))->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalCreated++;
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalCreated} Malaysian volunteers");
    }

    /**
     * Seed contact messages
     */
    private function seedContactMessages(): void
    {
        $this->command->info('   📧 Creating contact messages...');
        
        $centres = DB::table('centres')->get();
        $totalCreated = 0;
        
        for ($i = 0; $i < 50; $i++) {
            $ethnicity = $this->getRandomEthnicity();
            $gender = rand(0, 1) ? 'Male' : 'Female';
            $name = $this->generateMalaysianName($ethnicity, $gender, 'adult');
            
            try {
                DB::table('contact_messages')->insertOrIgnore([
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '.', str_replace(['Dr. ', 'Puan ', 'Encik ', 'bin ', 'binti ', 'a/l ', 'a/p '], '', $name))) . '@email.com',
                    'phone' => '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'subject' => collect([
                        'Pertanyaan mengenai perkhidmatan terapi',
                        'Ingin mendaftarkan anak',
                        'Maklumat program pendidikan khas',
                        'Peluang sukarelawan',
                        'Lawatan ke pusat'
                    ])->random(),
                    'message' => 'Assalamualaikum. Saya berminat untuk mengetahui lebih lanjut mengenai perkhidmatan yang disediakan di pusat ini. Terima kasih.',
                    'inquiry_type' => collect(['general', 'services', 'volunteer', 'other'])->random(),
                    'status' => collect(['new', 'read', 'replied'])->random(),
                    'centre_id' => $centres->random()->centre_id,
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now()->subDays(rand(1, 90)),
                ]);
                $totalCreated++;
            } catch (\Exception $e) {
                // Skip duplicates
            }
        }
        
        $this->command->line("      ✓ Created {$totalCreated} contact messages");
    }

    // Helper methods for Malaysian context
    private function getRandomEthnicity(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 60) return 'malay';
        if ($rand <= 85) return 'chinese';
        return 'indian';
    }

    private function generateMalaysianIC($birthDate): string
    {
        $year = date('y', $birthDate->getTimestamp());
        $month = date('m', $birthDate->getTimestamp());
        $day = date('d', $birthDate->getTimestamp());
        
        // Malaysian state codes (simplified)
        $stateCodes = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
        $stateCode = $stateCodes[array_rand($stateCodes)];
        
        return $year . $month . $day . '-' . $stateCode . '-' . sprintf('%04d', rand(1000, 9999));
    }

    private function generateMalaysianAddress($centreName): string
    {
        $addresses = [
            'Gombak' => ['Jalan Gombak', 'Taman Gombak', 'Batu Caves', 'Rawang'],
            'Kuantan' => ['Jalan Teluk Sisek', 'Taman Tas', 'Bandar Indera Mahkota', 'Semambu'],
            'Johor Bahru' => ['Taman Daya', 'Skudai', 'Tampoi', 'Tebrau'],
            'Kota Kinabalu' => ['Jalan Lintas', 'Penampang', 'Inanam', 'Menggatal'],
            'Nilai' => ['Bandar Baru Nilai', 'Putra Nilai', 'Taman Semarak', 'KLIA'],
        ];
        
        $areas = $addresses[$centreName] ?? ['Taman Malaysia', 'Bandar Baru'];
        $area = $areas[array_rand($areas)];
        $number = rand(1, 999);
        
        return "No. {$number}, {$area}";
    }

    private function generateMalaysianName($ethnicity, $gender, $ageGroup = 'adult'): string
    {
        switch ($ethnicity) {
            case 'malay':
                $first = collect(['Ahmad', 'Siti', 'Mohammad', 'Nor', 'Abdul', 'Fatimah'])->random();
                $last = collect(['Rahman', 'Hassan', 'Ahmad', 'Ibrahim', 'Mohamed'])->random();
                $prefix = $gender === 'Male' ? 'bin' : 'binti';
                return ($ageGroup === 'adult' ? ($gender === 'Male' ? 'Encik ' : 'Puan ') : '') . $first . ' ' . $prefix . ' ' . $last;
            case 'chinese':
                $first = collect(['Wei Ming', 'Hui Xin', 'Jian Hao', 'Mei Li'])->random();
                $last = collect(['Tan', 'Lim', 'Lee', 'Wong'])->random();
                return ($ageGroup === 'adult' ? ($gender === 'Male' ? 'Encik ' : 'Puan ') : '') . $last . ' ' . $first;
            case 'indian':
                $first = collect(['Raj Kumar', 'Priya', 'Suresh', 'Lakshmi'])->random();
                $last = collect(['Pillai', 'Singh', 'Nair', 'Krishnan'])->random();
                $prefix = $gender === 'Male' ? 'a/l' : 'a/p';
                return ($ageGroup === 'adult' ? ($gender === 'Male' ? 'Encik ' : 'Puan ') : '') . $first . ' ' . $prefix . ' ' . $last;
        }
        return 'Name';
    }

    private function generateGuardianName($ethnicity, $gender): string
    {
        return $this->generateMalaysianName($ethnicity, $gender, 'adult');
    }
}