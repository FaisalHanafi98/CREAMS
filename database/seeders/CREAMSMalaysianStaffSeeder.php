<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Models\Centres;

class CREAMSMalaysianStaffSeeder extends Seeder
{
    /**
     * Malaysian staff names database
     */
    private array $malayNames = [
        'male' => [
            'Ahmad Firdaus', 'Muhammad Aidil', 'Mohd Hafiz', 'Ahmad Zulkifli', 'Muhammad Danial',
            'Ahmad Rizwan', 'Mohd Faizal', 'Muhammad Syafiq', 'Ahmad Shahrul', 'Mohd Izwan',
            'Khairul Anuar', 'Muhammad Azim', 'Ahmad Nazri', 'Mohd Azlan', 'Muhammad Hakim',
            'Ahmad Azhar', 'Mohd Rashid', 'Muhammad Irfan', 'Syed Ahmad', 'Muhammad Aiman'
        ],
        'female' => [
            'Nur Aisyah', 'Siti Nurhaliza', 'Nurul Huda', 'Siti Zainab', 'Nur Syafiqah',
            'Faridah Salwa', 'Siti Aminah', 'Nurul Ain', 'Siti Mariam', 'Nur Hidayah',
            'Nurfatihah', 'Siti Khadijah', 'Nurul Iman', 'Siti Aishah', 'Nur Azlina',
            'Fauziah Rahman', 'Siti Hajar', 'Nurul Fatin', 'Siti Noraini', 'Nur Sabrina'
        ]
    ];

    private array $chineseNames = [
        'male' => [
            'Wong Wei Ming', 'Lim Jia Hao', 'Tan Kheng Seng', 'Lee Zhi Hao', 'Ng Wei Jie',
            'Chen Yong Ming', 'Ong Zheng Hao', 'Low Jun Wei', 'Goh Ming Sheng', 'Teh Wei Hong'
        ],
        'female' => [
            'Lim Hui Ying', 'Tan Mei Ling', 'Wong Xin Yi', 'Lee Jia Min', 'Ng Hui Shan',
            'Chen Li Ying', 'Ong Wan Ting', 'Low Pei Ying', 'Goh Ai Ling', 'Teh Yee Ling'
        ]
    ];

    private array $indianNames = [
        'male' => [
            'Rajesh Kumar', 'Suresh Chandran', 'Prakash Raman', 'Vijay Krishnan', 'Arjun Nair',
            'Sanjay Pillai', 'Ravi Menon', 'Deepak Sharma', 'Anil Reddy', 'Kiran Patel'
        ],
        'female' => [
            'Priya Devi', 'Kavitha Rao', 'Sushma Nair', 'Asha Menon', 'Rekha Sharma',
            'Sunita Pillai', 'Meera Krishnan', 'Radha Chandran', 'Lakshmi Raman', 'Geetha Kumar'
        ]
    ];

    private array $lastNames = [
        'Abdullah', 'Rahman', 'Ibrahim', 'Hassan', 'Ahmad', 'Mohamed', 'Ali', 'Omar',
        'Yusof', 'Ismail', 'Hamid', 'Mahmud', 'Salleh', 'Bakar', 'Mansor', 'Osman',
        'Razak', 'Hashim', 'Karim', 'Mohd Nor', 'Zainuddin', 'Sulaiman', 'Jamaluddin'
    ];

    /**
     * Professional qualifications for rehabilitation staff
     */
    private array $qualifications = [
        'admin' => [
            'PhD in Special Education', 'Master in Educational Administration', 'Master in Rehabilitation Science',
            'Bachelor in Special Education', 'Master in Educational Management'
        ],
        'supervisor' => [
            'Master in Occupational Therapy', 'Master in Speech Therapy', 'Master in Special Education',
            'Bachelor in Rehabilitation Science', 'Master in Educational Psychology'
        ],
        'teacher' => [
            'Bachelor in Occupational Therapy', 'Bachelor in Speech Therapy', 'Bachelor in Physiotherapy',
            'Bachelor in Special Education', 'Diploma in Rehabilitation Therapy', 'Bachelor in Psychology',
            'Diploma in Special Education'
        ],
        'ajk' => [
            'Bachelor in Administration', 'Diploma in Management', 'Bachelor in Communication',
            'Diploma in Accounting', 'Bachelor in Information Technology'
        ]
    ];

    /**
     * Specializations for therapy staff
     */
    private array $specializations = [
        'Speech and Language Therapy', 'Occupational Therapy', 'Pediatric Physiotherapy',
        'Behavioral Intervention', 'Social Skills Training', 'Academic Support',
        'Daily Living Skills', 'Art Therapy', 'Music Therapy', 'Sensory Integration',
        'Autism Spectrum Disorder', 'Cerebral Palsy', 'Down Syndrome',
        'Learning Disabilities', 'ADHD Management'
    ];

    public function run(): void
    {
        $this->command->info('🇲🇾 Creating realistic Malaysian CREAMS staff...');

        // Get centres
        $centres = Centres::all();
        
        if ($centres->isEmpty()) {
            $this->command->error('No centres found! Please run CREAMSCentresSeeder first.');
            return;
        }

        // Create staff for each centre with proper hierarchy
        foreach ($centres as $centre) {
            $this->createStaffForCentre($centre);
        }

        $this->showStaffSummary();
    }

    private function createStaffForCentre($centre): void
    {
        $this->command->info("\n🏢 Creating staff for {$centre->centre_name}...");

        $staffCounts = $this->getStaffCounts($centre->centre_id);
        
        foreach ($staffCounts as $role => $count) {
            for ($i = 0; $i < $count; $i++) {
                $staff = $this->createStaffMember($centre, $role, $i + 1);
                $this->command->line("   ✅ {$role}: {$staff['name']} ({$staff['email']})");
            }
        }
    }

    private function getStaffCounts(string $centreId): array
    {
        // Staff distribution based on centre importance
        return match($centreId) {
            '01' => [ // Gombak (Main centre)
                'admin' => 1,
                'supervisor' => 3,
                'teacher' => 12,
                'ajk' => 3
            ],
            '02' => [ // Kuantan (Specialized)
                'supervisor' => 2,
                'teacher' => 8,
                'ajk' => 2
            ],
            '03' => [ // Pagoh (Community-based)
                'supervisor' => 2,
                'teacher' => 8,
                'ajk' => 2
            ],
            default => [
                'supervisor' => 1,
                'teacher' => 4,
                'ajk' => 1
            ]
        };
    }

    private function createStaffMember($centre, string $role, int $index): array
    {
        // Generate Malaysian name based on ethnicity distribution
        $ethnicity = $this->getRandomEthnicity();
        $gender = rand(0, 1) ? 'male' : 'female';
        $name = $this->generateMalaysianName($ethnicity, $gender);
        
        // Generate IIUM ID
        $iiumId = $this->generateIiumId($centre->centre_id, $role, $index);
        
        // Generate email
        $email = $this->generateEmail($name, $centre->centre_id, $role, $index);
        
        // Get specialization
        $specialization = $this->getSpecialization($role);
        $qualification = $this->getQualification($role);

        $staff = Users::create([
            'iium_id' => $iiumId,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'centre_id' => $centre->centre_id,
            'status' => 'active',
            'phone' => $this->generatePhone(),
            'address' => $this->generateAddress($centre->centre_name),
            'position' => $this->getPositionTitle($role),
            'education_specialization' => $specialization,
            'teaching_specialization' => $qualification,
            'education_level' => $this->getEducationLevel($role),
            'about' => $this->generateAbout($role, $specialization),
            'date_of_birth' => $this->generateBirthDate($role),
            'created_at' => now()->subDays(rand(30, 365)),
        ]);

        return [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'specialization' => $specialization
        ];
    }

    private function getRandomEthnicity(): string
    {
        // Malaysian demographic distribution
        $random = rand(1, 100);
        if ($random <= 70) return 'malay';
        if ($random <= 85) return 'chinese';
        if ($random <= 95) return 'indian';
        return 'malay'; // Others
    }

    private function generateMalaysianName(string $ethnicity, string $gender): string
    {
        switch ($ethnicity) {
            case 'malay':
                $firstName = $this->malayNames[$gender][array_rand($this->malayNames[$gender])];
                $lastName = $this->lastNames[array_rand($this->lastNames)];
                $connector = $gender === 'male' ? 'bin' : 'binti';
                return "{$firstName} {$connector} {$lastName}";
                
            case 'chinese':
                return $this->chineseNames[$gender][array_rand($this->chineseNames[$gender])];
                
            case 'indian':
                return $this->indianNames[$gender][array_rand($this->indianNames[$gender])];
                
            default:
                return $this->malayNames[$gender][array_rand($this->malayNames[$gender])];
        }
    }

    private function generateIiumId(string $centreId, string $role, int $index): string
    {
        // IIUM ID format: RRCC#### (Role-Centre-Number)
        $roleCode = match($role) {
            'admin' => 'AD',
            'supervisor' => 'SV',
            'teacher' => 'TC',
            'ajk' => 'AJ',
            default => 'US'
        };
        
        $number = str_pad($index, 4, '0', STR_PAD_LEFT);
        return $roleCode . $centreId . $number;
    }

    private function generateEmail(string $name, string $centreId, string $role, int $index): string
    {
        // Clean name for email
        $cleanName = strtolower(str_replace([' bin ', ' binti ', ' '], '.', $name));
        $cleanName = preg_replace('/[^a-z.]/', '', $cleanName);
        
        $centreCode = match($centreId) {
            '01' => 'gombak',
            '02' => 'kuantan',
            '03' => 'pagoh',
            default => 'centre'
        };
        
        // Add unique suffix to prevent duplicates
        $baseEmail = "{$cleanName}.{$centreCode}";
        $email = "{$baseEmail}@iium.edu.my";
        
        // Check if email exists and add suffix if needed
        $counter = 1;
        while (Users::where('email', $email)->exists()) {
            $email = "{$baseEmail}{$counter}@iium.edu.my";
            $counter++;
        }
        
        return $email;
    }

    private function generatePhone(): string
    {
        // Malaysian mobile format
        $prefixes = ['010', '011', '012', '013', '014', '016', '017', '018', '019'];
        $prefix = $prefixes[array_rand($prefixes)];
        $number = rand(1000000, 9999999);
        return "{$prefix}-{$number}";
    }

    private function generateAddress(string $centreName): string
    {
        $streets = [
            'Jalan Melati', 'Jalan Mawar', 'Jalan Cempaka', 'Jalan Kenanga', 'Jalan Dahlia',
            'Jalan Orkid', 'Jalan Teratai', 'Jalan Seroja', 'Jalan Bunga Raya', 'Jalan Angsana'
        ];
        
        $areas = match($centreName) {
            'Gombak' => ['Taman Gombak Setia', 'Bandar Baru Selayang', 'Taman Gombak Permai'],
            'Kuantan' => ['Bandar Kuantan', 'Taman Teruntum', 'Bandar Indera Mahkota'],
            'Pagoh' => ['Bandar Pagoh', 'Taman Pagoh Jaya', 'Bandar Universiti Pagoh'],
            default => ['Taman Damai', 'Bandar Baru', 'Taman Harmoni']
        };

        $states = [
            'Gombak' => 'Selangor',
            'Kuantan' => 'Pahang',
            'Pagoh' => 'Johor'
        ];

        $street = $streets[array_rand($streets)];
        $area = $areas[array_rand($areas)];
        $state = $states[$centreName] ?? 'Selangor';
        $postcode = rand(10000, 99999);
        
        return "No. " . rand(1, 999) . ", {$street}, {$area}, {$postcode} {$centreName}, {$state}";
    }

    private function getPositionTitle(string $role): string
    {
        return match($role) {
            'admin' => 'Centre Director',
            'supervisor' => 'Clinical Supervisor',
            'teacher' => 'Therapist',
            'ajk' => 'Committee Member',
            default => 'Staff Member'
        };
    }

    private function getSpecialization(string $role): string
    {
        if ($role === 'admin') {
            return 'Overall Management and Administration';
        }
        
        return $this->specializations[array_rand($this->specializations)];
    }

    private function getQualification(string $role): string
    {
        $qualList = $this->qualifications[$role] ?? $this->qualifications['teacher'];
        return $qualList[array_rand($qualList)];
    }

    private function getEducationLevel(string $role): string
    {
        $levels = match($role) {
            'admin' => ["Master's", 'PhD'],
            'supervisor' => ["Bachelor's", "Master's"],
            'teacher' => ['Diploma', "Bachelor's", "Master's"],
            'ajk' => ['Diploma', "Bachelor's"],
            default => ["Bachelor's"]
        };
        
        return $levels[array_rand($levels)];
    }

    private function generateAbout(string $role, string $specialization): string
    {
        $templates = [
            'admin' => "Over 15 years of experience in rehabilitation centre management. Committed to providing the best services for children with special needs. Experienced in special education administration and policy development.",
            
            'supervisor' => "Experienced supervisor specializing in {$specialization}. Oversees daily operations and ensures quality therapeutic services. Dedicated to maintaining high standards of therapeutic services and staff development.",
            
            'teacher' => "Qualified therapist specializing in {$specialization}. Experienced working with children with autism, cerebral palsy, and other special needs. Passionate about helping children reach their full potential through evidence-based therapeutic interventions.",
            
            'ajk' => "Committee member who assists in administrative management and community programs. Committed to supporting the centre's mission in providing quality rehabilitation services."
        ];

        return $templates[$role] ?? $templates['teacher'];
    }

    private function generateBirthDate(string $role): string
    {
        $ageRanges = [
            'admin' => [45, 60],     // Senior management
            'supervisor' => [35, 50], // Mid-level management
            'teacher' => [25, 45],    // Professional staff
            'ajk' => [30, 55]         // Committee members
        ];
        
        $range = $ageRanges[$role];
        $age = rand($range[0], $range[1]);
        
        return now()->subYears($age)->subDays(rand(0, 365))->format('Y-m-d');
    }

    private function showStaffSummary(): void
    {
        $this->command->info("\n📊 CREAMS Staff Summary:");
        
        $summary = Users::selectRaw('role, centre_id, COUNT(*) as count')
            ->groupBy('role', 'centre_id')
            ->orderBy('centre_id')
            ->orderBy('role')
            ->get();

        $centres = Centres::pluck('centre_name', 'centre_id');
        
        foreach ($centres as $centreId => $centreName) {
            $this->command->info("\n🏢 {$centreName} (ID: {$centreId}):");
            
            $centreStaff = $summary->where('centre_id', $centreId);
            $total = $centreStaff->sum('count');
            
            foreach (['admin', 'supervisor', 'teacher', 'ajk'] as $role) {
                $count = $centreStaff->where('role', $role)->first()->count ?? 0;
                if ($count > 0) {
                    $this->command->line("   📋 " . ucfirst($role) . ": {$count} staff");
                }
            }
            
            $this->command->line("   👥 Total: {$total} staff");
        }

        $grandTotal = Users::count();
        $this->command->info("\n🎯 Grand Total: {$grandTotal} CREAMS staff created across all centres!");
        $this->command->info("✅ All staff have Malaysian names, proper IIUM IDs, and realistic qualifications!");
    }
}