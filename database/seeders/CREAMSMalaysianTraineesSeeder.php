<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainees;
use App\Models\Centres;
use Carbon\Carbon;

class CREAMSMalaysianTraineesSeeder extends Seeder
{
    /**
     * Malaysian children names by ethnicity and gender
     */
    private array $malayNames = [
        'male' => [
            'Ahmad Danial', 'Muhammad Aariz', 'Ahmad Haziq', 'Muhammad Rayyan', 'Ahmad Zikri',
            'Muhammad Harith', 'Ahmad Adriel', 'Muhammad Ayden', 'Ahmad Izzat', 'Muhammad Hakim',
            'Aiman Hakeem', 'Aryan Danish', 'Aqil Harith', 'Ashraf Zafran', 'Azlan Mikail',
            'Danish Zafir', 'Darwish Iman', 'Emir Zafran', 'Faris Zaydan', 'Harith Zafir',
            'Haziq Aryan', 'Iman Rayyan', 'Irfan Zikri', 'Luqman Hakim', 'Mikail Zafran'
        ],
        'female' => [
            'Nur Aisyah', 'Siti Aishah', 'Nur Aliyah', 'Siti Zara', 'Nur Hana',
            'Qaisara Medina', 'Siti Sofiya', 'Nur Batrisyia', 'Aleeya Zara', 'Siti Iman',
            'Ameera Sofea', 'Arissa Zara', 'Ayra Medina', 'Azalea Hana', 'Darla Sofea',
            'Elysia Zara', 'Hana Sofiya', 'Iris Batrisyia', 'Layla Iman', 'Mika Aleeya',
            'Nayla Sofea', 'Qhaleesya Zara', 'Rania Medina', 'Sofia Iman', 'Zara Aleeya'
        ]
    ];

    private array $chineseNames = [
        'male' => [
            'Lim Jun Hao', 'Tan Wei Jie', 'Wong Zhi Heng', 'Lee Jun Wei', 'Ng Wei Xuan',
            'Chen Kai Yang', 'Ong Jun Ming', 'Low Wei Hao', 'Goh Zhi Wei', 'Teh Jun Jie',
            'Chong Wei Ming', 'Yap Zhi Xuan', 'Koh Jun Yang', 'Sim Wei Jie', 'Ooi Zhi Hao'
        ],
        'female' => [
            'Lim Jia Ying', 'Tan Hui Min', 'Wong Xin Yee', 'Lee Zi Ying', 'Ng Hui Xuan',
            'Chen Li Xin', 'Ong Mei Ling', 'Low Jia Min', 'Goh Hui Ying', 'Teh Zi Xuan',
            'Chong Jia Hui', 'Yap Mei Ying', 'Koh Li Min', 'Sim Hui Ling', 'Ooi Zi Ying'
        ]
    ];

    private array $indianNames = [
        'male' => [
            'Arjun Kumar', 'Kiran Raj', 'Arun Krishnan', 'Vikram Nair', 'Ravi Menon',
            'Suresh Pillai', 'Deepak Sharma', 'Anil Reddy', 'Rajesh Patel', 'Anand Rao'
        ],
        'female' => [
            'Priya Devi', 'Kavya Nair', 'Asha Menon', 'Divya Sharma', 'Meera Pillai',
            'Sita Krishnan', 'Radha Rao', 'Geetha Kumar', 'Lakshmi Reddy', 'Sunita Patel'
        ]
    ];

    /**
     * Common rehabilitation conditions in Malaysia
     */
    private array $conditions = [
        'Autism Spectrum Disorder' => [
            'prevalence' => 35,
            'age_range' => [3, 16],
            'severity' => ['Mild', 'Moderate', 'Severe'],
            'characteristics' => 'Communication difficulties, social interaction challenges, repetitive behaviors'
        ],
        'Cerebral Palsy' => [
            'prevalence' => 20,
            'age_range' => [2, 18],
            'severity' => ['Mild', 'Moderate', 'Severe'],
            'characteristics' => 'Motor function impairment, coordination difficulties, mobility challenges'
        ],
        'Down Syndrome' => [
            'prevalence' => 15,
            'age_range' => [2, 18],
            'severity' => ['Mild', 'Moderate'],
            'characteristics' => 'Intellectual disability, delayed development, distinctive physical features'
        ],
        'Intellectual Disability' => [
            'prevalence' => 10,
            'age_range' => [4, 18],
            'severity' => ['Mild', 'Moderate', 'Severe'],
            'characteristics' => 'Cognitive impairment, adaptive behavior challenges, learning difficulties'
        ],
        'ADHD' => [
            'prevalence' => 8,
            'age_range' => [5, 16],
            'severity' => ['Mild', 'Moderate', 'Severe'],
            'characteristics' => 'Attention deficits, hyperactivity, impulsivity, concentration difficulties'
        ],
        'Learning Disabilities' => [
            'prevalence' => 7,
            'age_range' => [6, 18],
            'severity' => ['Mild', 'Moderate'],
            'characteristics' => 'Specific learning challenges, reading/writing difficulties, processing issues'
        ],
        'Speech and Language Disorders' => [
            'prevalence' => 5,
            'age_range' => [3, 12],
            'severity' => ['Mild', 'Moderate', 'Severe'],
            'characteristics' => 'Communication impairment, language delay, articulation difficulties'
        ]
    ];

    /**
     * Malaysian hospitals and clinics for medical history
     */
    private array $malaysianHospitals = [
        'Hospital Kuala Lumpur', 'Hospital Selayang', 'Hospital Sungai Buloh',
        'Hospital Sultanah Bahiyah, Alor Setar', 'Hospital Tengku Ampuan Afzan, Kuantan',
        'Hospital Sultan Ismail, Johor Bahru', 'Hospital Sultanah Aminah, Johor Bahru',
        'Klinik Kesihatan Gombak', 'Klinik Kesihatan Kuantan', 'Klinik Kesihatan Pagoh',
        'Pusat Perubatan UKM', 'Hospital Pakar Kanak-Kanak UKM'
    ];

    public function run(): void
    {
        $this->command->info('🧒 Creating realistic Malaysian trainee data...');

        $centres = Centres::all();
        
        if ($centres->isEmpty()) {
            $this->command->error('No centres found! Please run CREAMSCentresSeeder first.');
            return;
        }

        $totalTrainees = 0;

        foreach ($centres as $centre) {
            $count = $this->getTraineeCountForCentre($centre->centre_id);
            $this->command->info("\n🏢 Creating {$count} trainees for {$centre->centre_name}...");
            
            for ($i = 0; $i < $count; $i++) {
                $trainee = $this->createTrainee($centre);
                $totalTrainees++;
                
                if ($i % 10 === 0 && $i > 0) {
                    $this->command->line("   📝 Created {$i} trainees...");
                }
            }
            
            $this->command->info("   ✅ {$count} trainees created for {$centre->centre_name}");
        }

        $this->showTraineeSummary($totalTrainees);
    }

    private function getTraineeCountForCentre(string $centreId): int
    {
        return match($centreId) {
            '01' => 35, // Gombak (main centre)
            '02' => 25, // Kuantan (specialized)
            '03' => 20, // Pagoh (community-based)
            default => 15
        };
    }

    private function createTrainee($centre): Trainees
    {
        // Generate Malaysian name
        $ethnicity = $this->getRandomEthnicity();
        $gender = rand(0, 1) ? 'male' : 'female';
        $names = $this->generateTraineeName($ethnicity, $gender);
        
        // Generate condition based on prevalence
        $condition = $this->selectCondition();
        $conditionInfo = $this->conditions[$condition];
        
        // Generate age appropriate for condition
        $age = rand($conditionInfo['age_range'][0], $conditionInfo['age_range'][1]);
        $dateOfBirth = Carbon::now()->subYears($age)->subDays(rand(0, 365));
        
        // Generate guardian information
        $guardian = $this->generateGuardianInfo($names['ethnicity'], $names['last_name']);
        
        // Generate medical information
        $medical = $this->generateMedicalInfo($condition, $conditionInfo);

        return Trainees::create([
            'trainee_first_name' => $names['first_name'],
            'trainee_last_name' => $names['last_name'],
            'trainee_email' => $this->generateTraineeEmail($names['first_name'], $names['last_name']),
            'trainee_phone_number' => $this->generateMalaysianPhone(),
            'trainee_date_of_birth' => $dateOfBirth->format('Y-m-d'),
            'centre_name' => $centre->centre_name,
            'trainee_condition' => $condition,
            'trainee_attendance' => rand(75, 95), // Attendance percentage
            
            // Guardian information
            'guardian_name' => $guardian['name'],
            'guardian_relationship' => $guardian['relationship'],
            'guardian_phone' => $this->generateMalaysianPhone(),
            'guardian_email' => $guardian['email'],
            'guardian_address' => $this->generateMalaysianAddress($centre->centre_name),
            
            // Medical information
            'medical_history' => $medical['history'],
            'additional_notes' => $medical['notes'],
            
            // Emergency contact
            'emergency_contact_name' => $this->generateEmergencyContact($guardian['name']),
            'emergency_contact_phone' => $this->generateMalaysianPhone(),
            'emergency_contact_relationship' => $this->getEmergencyRelationship(),
            
            // System fields
            'created_at' => Carbon::now()->subDays(rand(30, 730)), // Created 1 month to 2 years ago
            'updated_at' => Carbon::now()->subDays(rand(1, 30)),
        ]);
    }

    private function getRandomEthnicity(): string
    {
        $random = rand(1, 100);
        if ($random <= 70) return 'malay';
        if ($random <= 85) return 'chinese';
        if ($random <= 95) return 'indian';
        return 'malay';
    }

    private function generateTraineeName(string $ethnicity, string $gender): array
    {
        switch ($ethnicity) {
            case 'malay':
                $firstName = $this->malayNames[$gender][array_rand($this->malayNames[$gender])];
                $lastName = $this->getRandomMalayLastName();
                break;
            case 'chinese':
                $fullName = $this->chineseNames[$gender][array_rand($this->chineseNames[$gender])];
                $nameParts = explode(' ', $fullName);
                $firstName = implode(' ', array_slice($nameParts, 1));
                $lastName = $nameParts[0];
                break;
            case 'indian':
                $fullName = $this->indianNames[$gender][array_rand($this->indianNames[$gender])];
                $nameParts = explode(' ', $fullName);
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? $nameParts[1] : 'Kumar';
                break;
            default:
                $firstName = $this->malayNames[$gender][array_rand($this->malayNames[$gender])];
                $lastName = $this->getRandomMalayLastName();
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'ethnicity' => $ethnicity
        ];
    }

    private function getRandomMalayLastName(): string
    {
        $lastNames = [
            'Abdullah', 'Rahman', 'Ibrahim', 'Hassan', 'Ahmad', 'Mohamed', 'Ali', 'Omar',
            'Yusof', 'Ismail', 'Hamid', 'Mahmud', 'Salleh', 'Bakar', 'Mansor', 'Osman'
        ];
        return $lastNames[array_rand($lastNames)];
    }

    private function selectCondition(): string
    {
        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($this->conditions as $condition => $info) {
            $cumulative += $info['prevalence'];
            if ($random <= $cumulative) {
                return $condition;
            }
        }
        
        return 'Autism Spectrum Disorder'; // Default
    }

    private function generateTraineeEmail(string $firstName, string $lastName): string
    {
        $cleanFirst = strtolower(preg_replace('/[^a-z]/', '', $firstName));
        $cleanLast = strtolower(preg_replace('/[^a-z]/', '', $lastName));
        $year = Carbon::now()->year;
        
        $baseEmail = "{$cleanFirst}.{$cleanLast}.{$year}";
        $email = "{$baseEmail}@trainee.creams.edu.my";
        
        // Check if email exists and add suffix if needed
        $counter = 1;
        while (Trainees::where('trainee_email', $email)->exists()) {
            $email = "{$baseEmail}{$counter}@trainee.creams.edu.my";
            $counter++;
        }
        
        return $email;
    }

    private function generateMalaysianPhone(): string
    {
        $prefixes = ['010', '011', '012', '013', '014', '016', '017', '018', '019'];
        $prefix = $prefixes[array_rand($prefixes)];
        $number = rand(1000000, 9999999);
        return "{$prefix}-{$number}";
    }

    private function generateGuardianInfo(string $ethnicity, string $childLastName): array
    {
        $relationships = ['Ibu / Mother', 'Bapa / Father', 'Ibu Angkat / Foster Mother', 'Bapa Angkat / Foster Father', 'Nenek / Grandmother', 'Datuk / Grandfather'];
        $relationship = $relationships[array_rand($relationships)];
        
        // Generate guardian name based on relationship
        $isMother = str_contains($relationship, 'Ibu') || str_contains($relationship, 'Nenek');
        $gender = $isMother ? 'female' : 'male';
        
        $guardianName = $this->generateAdultName($ethnicity, $gender, $childLastName);
        $email = $this->generateGuardianEmail($guardianName);
        
        return [
            'name' => $guardianName,
            'relationship' => $relationship,
            'email' => $email
        ];
    }

    private function generateAdultName(string $ethnicity, string $gender, string $familyName): string
    {
        $adultNames = [
            'malay' => [
                'male' => ['Ahmad', 'Muhammad', 'Mohd', 'Abdul', 'Syed'],
                'female' => ['Siti', 'Nur', 'Faridah', 'Noraini', 'Zainab']
            ],
            'chinese' => [
                'male' => ['Lim', 'Tan', 'Wong', 'Lee', 'Ng'],
                'female' => ['Lim', 'Tan', 'Wong', 'Lee', 'Ng']
            ],
            'indian' => [
                'male' => ['Rajesh', 'Suresh', 'Prakash', 'Vijay', 'Ravi'],
                'female' => ['Priya', 'Kavitha', 'Sushma', 'Asha', 'Rekha']
            ]
        ];

        if ($ethnicity === 'malay') {
            $firstName = $adultNames[$ethnicity][$gender][array_rand($adultNames[$ethnicity][$gender])];
            $connector = $gender === 'male' ? 'bin' : 'binti';
            return "{$firstName} {$connector} {$familyName}";
        } else {
            $firstName = $adultNames[$ethnicity][$gender][array_rand($adultNames[$ethnicity][$gender])];
            return "{$firstName} {$familyName}";
        }
    }

    private function generateGuardianEmail(string $name): string
    {
        $cleanName = strtolower(str_replace([' bin ', ' binti ', ' '], '.', $name));
        $cleanName = preg_replace('/[^a-z.]/', '', $cleanName);
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $domain = $domains[array_rand($domains)];
        
        return "{$cleanName}@{$domain}";
    }

    private function generateMedicalInfo(string $condition, array $conditionInfo): array
    {
        $hospital = $this->malaysianHospitals[array_rand($this->malaysianHospitals)];
        $severity = $conditionInfo['severity'][array_rand($conditionInfo['severity'])];
        $diagnosisAge = rand(1, 5);
        
        $history = "Didiagnosis dengan {$condition} ({$severity}) pada umur {$diagnosisAge} tahun di {$hospital}. ";
        $history .= "Characteristics: {$conditionInfo['characteristics']}. ";
        $history .= $this->getConditionSpecificHistory($condition);
        
        $notes = $this->generateProgressNotes($condition, $severity);
        
        return [
            'history' => $history,
            'notes' => $notes
        ];
    }

    private function getConditionSpecificHistory(string $condition): string
    {
        $specificHistory = [
            'Autism Spectrum Disorder' => 'Memerlukan terapi pertuturan dan intervensi tingkah laku. Responsive terhadap routine yang konsisten.',
            'Cerebral Palsy' => 'Memerlukan fisioterapi dan terapi okupasi. Menggunakan alat bantu mobiliti mengikut keperluan.',
            'Down Syndrome' => 'Memerlukan sokongan akademik tambahan dan terapi pertuturan. Personality yang mesra dan suka bersosialisasi.',
            'ADHD' => 'Memerlukan persekitaran pembelajaran yang berstruktur. Responsive terhadap strategi pengurusan tingkah laku.',
            'Learning Disabilities' => 'Memerlukan kaedah pembelajaran yang disesuaikan. Menunjukkan kekuatan dalam bidang tertentu.',
            'Intellectual Disability' => 'Memerlukan sokongan dalam aktiviti harian. Berkembang dengan baik dalam persekitaran yang menyokong.',
            'Speech and Language Disorders' => 'Memerlukan terapi pertuturan intensif. Menunjukkan kemajuan dalam komunikasi.'
        ];
        
        return $specificHistory[$condition] ?? 'Memerlukan sokongan dan intervensi yang berterusan.';
    }

    private function generateProgressNotes(string $condition, string $severity): string
    {
        $progressTemplates = [
            'Mild' => 'Menunjukkan kemajuan yang baik dalam program terapi. Mampu mengikuti arahan mudah dan berinteraksi dengan rakan sebaya. Target: meningkatkan kemahiran komunikasi dan kemandirian.',
            'Moderate' => 'Memerlukan sokongan tambahan dalam aktiviti harian. Menunjukkan kemajuan perlahan tetapi konsisten. Target: meningkatkan kemahiran asas dan kemahiran sosial.',
            'Severe' => 'Memerlukan sokongan intensif dan pengawasan berterusan. Fokus kepada kemahiran asas dan komunikasi fungsional. Target: meningkatkan kualiti hidup dan kemahiran asas.'
        ];
        
        return $progressTemplates[$severity] ?? $progressTemplates['Moderate'];
    }

    private function generateMalaysianAddress(string $centreName): string
    {
        $streets = [
            'Jalan Melati', 'Jalan Mawar', 'Jalan Cempaka', 'Jalan Kenanga', 'Jalan Dahlia',
            'Jalan Orkid', 'Jalan Teratai', 'Jalan Seroja', 'Jalan Bunga Raya', 'Jalan Angsana'
        ];
        
        $areas = [
            'Gombak' => ['Taman Gombak Setia', 'Bandar Baru Selayang', 'Taman Gombak Permai', 'Batu Caves'],
            'Kuantan' => ['Bandar Kuantan', 'Taman Teruntum', 'Bandar Indera Mahkota', 'Taman Tas'],
            'Pagoh' => ['Bandar Pagoh', 'Taman Pagoh Jaya', 'Bandar Universiti Pagoh', 'Taman Ledang']
        ];

        $states = [
            'Gombak' => 'Selangor',
            'Kuantan' => 'Pahang',
            'Pagoh' => 'Johor'
        ];

        $street = $streets[array_rand($streets)];
        $area = $areas[$centreName][array_rand($areas[$centreName])];
        $state = $states[$centreName];
        $postcode = $this->getPostcode($centreName);
        
        return "No. " . rand(1, 999) . ", {$street}, {$area}, {$postcode} {$centreName}, {$state}";
    }

    private function getPostcode(string $centreName): string
    {
        return match($centreName) {
            'Gombak' => (string)rand(53100, 53299),
            'Kuantan' => (string)rand(25000, 25999),
            'Pagoh' => (string)rand(84600, 84699),
            default => (string)rand(10000, 99999)
        };
    }

    private function generateEmergencyContact(string $guardianName): string
    {
        // Usually another family member
        $titles = ['Puan', 'Encik', 'Datin', 'Dato'];
        $names = ['Azman', 'Salmah', 'Rashid', 'Noraini', 'Farid', 'Zainab'];
        
        $title = $titles[array_rand($titles)];
        $name = $names[array_rand($names)];
        
        return "{$title} {$name}";
    }

    private function getEmergencyRelationship(): string
    {
        $relationships = [
            'Pak Cik / Uncle', 'Mak Cik / Aunt', 'Nenek / Grandmother', 
            'Datuk / Grandfather', 'Abang / Elder Brother', 'Kakak / Elder Sister'
        ];
        
        return $relationships[array_rand($relationships)];
    }

    private function showTraineeSummary(int $totalTrainees): void
    {
        $this->command->info("\n📊 Malaysian Trainee Summary:");
        
        // Summary by centre
        $centreStats = Trainees::selectRaw('centre_name, COUNT(*) as count')
            ->groupBy('centre_name')
            ->get();
            
        foreach ($centreStats as $stat) {
            $this->command->info("🏢 {$stat->centre_name}: {$stat->count} trainees");
        }
        
        // Summary by condition
        $this->command->info("\n🏥 Condition Distribution:");
        $conditionStats = Trainees::selectRaw('trainee_condition, COUNT(*) as count')
            ->groupBy('trainee_condition')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($conditionStats as $stat) {
            $this->command->line("   📋 {$stat->trainee_condition}: {$stat->count} trainees");
        }
        
        // Age distribution
        $this->command->info("\n👶 Age Distribution:");
        $ages = Trainees::selectRaw('
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 3 AND 6 THEN "3-6 years"
                WHEN TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 7 AND 12 THEN "7-12 years"
                WHEN TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 13 AND 18 THEN "13-18 years"
                ELSE "Other"
            END as age_group,
            COUNT(*) as count
        ')
        ->groupBy('age_group')
        ->get();
        
        foreach ($ages as $age) {
            $this->command->line("   🎂 {$age->age_group}: {$age->count} trainees");
        }

        $this->command->info("\n🎯 Total: {$totalTrainees} Malaysian trainees created!");
        $this->command->info("✅ All trainees have authentic Malaysian names, conditions, and family information!");
        $this->command->info("🇲🇾 Data includes proper Malaysian context: addresses, phone numbers, and cultural elements!");
    }
}