<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centre;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class IRLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds real-world data for Gombak Centre (Centre 01) exclusively
     */
    public function run(): void
    {
        $this->command->info('🏥 Seeding IRL (In Real Life) data for Gombak Centre...');

        $faker = Faker::create('ms_MY'); // Malaysian locale

        // Get Gombak centre
        $gombakCentre = Centre::where('centre_id', '01')->first();
        if (!$gombakCentre) {
            $this->command->error('Gombak Centre (01) not found!');
            return;
        }

        $this->command->info('📍 Target Centre: ' . $gombakCentre->centre_name . ' (ID: ' . $gombakCentre->centre_id . ')');

        // Clear existing Gombak-specific data to avoid conflicts
        $this->clearExistingGombakData($gombakCentre->centre_id);

        // Seed data in logical order
        $this->seedAssets($gombakCentre, $faker);
        $this->seedTrainees($gombakCentre, $faker);
        $this->seedStaff($gombakCentre, $faker);
        $this->seedActivities($gombakCentre, $faker);
        $this->seedSchedules($gombakCentre, $faker);

        $this->command->info('✅ IRL seeding completed successfully for Gombak Centre!');
    }

    /**
     * Clear existing Gombak-specific data to avoid conflicts
     */
    private function clearExistingGombakData($centreId)
    {
        $this->command->info('🧹 Clearing existing Gombak data to prevent conflicts...');

        // Delete in reverse dependency order
        $activityIds = Activity::where('centre_id', $centreId)->pluck('id');
        DB::table('activity_sessions')->whereIn('activity_id', $activityIds)->delete();

        Activity::where('centre_id', $centreId)->delete();
        Asset::where('centre_id', $centreId)->delete();
        Trainee::where('centre_id', $centreId)->delete();

        // Remove all users for this centre to prevent duplicates
        User::where('centre_id', $centreId)->delete();

        $this->command->info('✅ Existing Gombak data cleared');
    }

    /**
     * Seed asset inventory data
     */
    private function seedAssets($centre, $faker)
    {
        $this->command->info('📦 Seeding asset inventory...');

        // Get asset type IDs for mapping
        $assetParentMapping = [
            'Gym Equipment' => 1,        // Medical Equipment (closest match for therapy equipment)
            'Storage' => 4,              // Furniture
            'Furniture' => 4,            // Furniture
            'Musical Instrument' => 2,   // Educational Materials
            'Educational Equipment' => 2 // Educational Materials
        ];

        // Get asset category IDs for mapping
        $assetCategoryMapping = [
            'Gym Equipment' => 1,        // Therapy Equipment
            'Storage' => 5,              // Classroom Furniture
            'Furniture' => 5,            // Classroom Furniture
            'Musical Instrument' => 2,   // Learning Materials
            'Educational Equipment' => 2 // Learning Materials
        ];

        $assetData = [
            // PPT EDU SUPPLIES GYM EQUIPMENT (24/02/2023)
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/PB4590-1/1',
                'asset_name' => 'Peanut Ball 45cm x 90cm',
                'asset_parent' => 'Gym Equipment',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/TB75-1/1',
                'asset_name' => 'Touch Ball 75cm',
                'asset_parent' => 'Gym Equipment',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/GR-1/1',
                'asset_name' => 'Gym Roller',
                'asset_parent' => 'Gym Equipment',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/BSCCN-1/1',
                'asset_name' => 'Bench Storage (Castors & Cushion Not)',
                'asset_parent' => 'Storage',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/12LEAS-1/1',
                'asset_name' => '12 Level Economy Adjustable Shelf',
                'asset_parent' => 'Storage',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/6LEMPSS-1/1',
                'asset_name' => '6 Level Economy Multi-Purpose Storage Shelf',
                'asset_parent' => 'Storage',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/3LESS-1/1',
                'asset_name' => '3 Level Economy Storage Shelf',
                'asset_parent' => 'Storage',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/WOTDGM-1/1',
                'asset_name' => 'Wooden Office Table (Dark Grey + Maple)',
                'asset_parent' => 'Furniture',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/MP3DDGM-1/1',
                'asset_name' => 'Mobile Pedestal 3 Drawer (Dark Grey + Maple)',
                'asset_parent' => 'Storage',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/JBFP-1/3',
                'asset_name' => 'Jumping Ball (Free Pump)',
                'asset_parent' => 'Gym Equipment',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 3
            ],
            [
                'asset_tag' => 'PDCARE/2025/G-PPT/24HH-1/5',
                'asset_name' => '24" Hula Hoop',
                'asset_parent' => 'Gym Equipment',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-02-24',
                'quantity' => 5
            ],

            // PPT EDU SUPPLIES FURNITURE (25/08/2023)
            [
                'asset_tag' => 'PDCARE/2025/F-PPT/PC49.55B-1/12',
                'asset_name' => 'Plastic Chair Blue (49.5cm x 51cm x 86.5cm)',
                'asset_parent' => 'Furniture',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-08-25',
                'quantity' => 12,
                'color' => 'Blue'
            ],
            [
                'asset_tag' => 'PDCARE/2025/F-PPT/PC49.55G-1/12',
                'asset_name' => 'Plastic Chair Green (49.5cm x 51cm x 86.5cm)',
                'asset_parent' => 'Furniture',
                'supplier' => 'PPT EDU SUPPLIES',
                'purchase_date' => '2023-08-25',
                'quantity' => 12,
                'color' => 'Green'
            ],

            // USL EDUCATIONAL SUPPLIES (31/12/2024)
            [
                'asset_tag' => 'PDCARE/2025/F-USL/RB-1/2',
                'asset_name' => 'Ring Bell',
                'asset_parent' => 'Musical Instrument',
                'supplier' => 'USL EDUCATIONAL SUPPLIES (M) SDN. BHD.',
                'purchase_date' => '2024-12-31',
                'quantity' => 2
            ],
            [
                'asset_tag' => 'PDCARE/2025/F-USL/WT6-1/1',
                'asset_name' => 'Wooden Tambourine 6"',
                'asset_parent' => 'Musical Instrument',
                'supplier' => 'USL EDUCATIONAL SUPPLIES (M) SDN. BHD.',
                'purchase_date' => '2024-12-31',
                'quantity' => 1
            ],
            [
                'asset_tag' => 'PDCARE/2025/F-USL/T6-1/2',
                'asset_name' => 'Triangle 6"',
                'asset_parent' => 'Musical Instrument',
                'supplier' => 'USL EDUCATIONAL SUPPLIES (M) SDN. BHD.',
                'purchase_date' => '2024-12-31',
                'quantity' => 2
            ],
            [
                'asset_tag' => 'PDCARE/2025/F-USL/RT24W-1/2',
                'asset_name' => 'Rectangular Table 2\'x4\' - Wood',
                'asset_parent' => 'Furniture',
                'supplier' => 'USL EDUCATIONAL SUPPLIES (M) SDN. BHD.',
                'purchase_date' => '2024-12-31',
                'quantity' => 2
            ],
            [
                'asset_tag' => 'PDCARE/2025/F-USL/PCC28B-1/8',
                'asset_name' => 'Premium Children Chair 28cm - Brown',
                'asset_parent' => 'Furniture',
                'supplier' => 'USL EDUCATIONAL SUPPLIES (M) SDN. BHD.',
                'purchase_date' => '2024-12-31',
                'quantity' => 8,
                'color' => 'Brown'
            ],
            [
                'asset_tag' => 'PDCARE/2025/F-USL/MWB36-1/1',
                'asset_name' => 'Magnetic White Board 3\'x6\'',
                'asset_parent' => 'Educational Equipment',
                'supplier' => 'USL EDUCATIONAL SUPPLIES (M) SDN. BHD.',
                'purchase_date' => '2024-12-31',
                'quantity' => 1
            ]
        ];

        $assetCount = 0;
        foreach ($assetData as $data) {
            for ($i = 1; $i <= $data['quantity']; $i++) {
                $cost = rand(50, 2000); // Randomized reasonable cost
                Asset::create([
                    'asset_tag' => $data['quantity'] > 1 ? $data['asset_tag'] . '-' . $i : $data['asset_tag'],
                    'asset_name' => $data['asset_name'],
                    'asset_description' => $data['asset_name'] . ' - ' . $data['asset_parent'],
                    'type_id' => $assetParentMapping[$data['asset_parent']] ?? 2, // Default to Educational Materials
                    'category_id' => $assetCategoryMapping[$data['asset_parent']] ?? 2, // Default to Learning Materials
                    'centre_id' => $centre->centre_id,
                    'serial_number' => 'SN' . strtoupper($faker->bothify('##??####')),
                    'manufacturer' => $data['supplier'],
                    'purchase_date' => Carbon::parse($data['purchase_date']),
                    'warranty_expiry' => Carbon::parse($data['purchase_date'])->addYears(2),
                    'purchase_price' => $cost,
                    'condition' => $faker->randomElement(['Excellent', 'Good', 'Fair']),
                    'status' => 'available',
                    'notes' => 'Real inventory item from ' . $data['supplier']
                ]);
                $assetCount++;
            }
        }

        $this->command->info("📦 Created {$assetCount} assets");
    }

    /**
     * Seed trainee data based on real records
     */
    private function seedTrainees($centre, $faker)
    {
        $this->command->info('👨‍🎓 Seeding trainee records...');

        $realTrainees = [
            [
                'name' => 'Aariz Hakimi bin Muhamad Nasrul',
                'ic' => '170504-10-2057',
                'oku_card' => 'LD100223000036',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 8
            ],
            [
                'name' => 'Azzalien Nuzman Bin Amir Najib',
                'ic' => '210513-10-0361',
                'oku_card' => 'LD100222000501',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 4
            ],
            [
                'name' => 'Muhammad Zaim Aariz Bin Mohd Zambuzi Affandi',
                'ic' => '201231-10-1329',
                'oku_card' => 'LD100224000242',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 5
            ],
            [
                'name' => 'Muhammad Hazim Zafiran bin Mohd Shafiruzzee',
                'ic' => '200629-10-0073',
                'oku_card' => 'LD100224000086',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 5
            ],
            [
                'name' => 'Putera Nur Afeezah Bin Azaman',
                'ic' => '210606-02-0379',
                'oku_card' => 'LD002240009915',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 4
            ],
            [
                'name' => 'Nur Adriana Damia binti Abdul Azri',
                'ic' => '100302-14-1000',
                'oku_card' => 'LD100312503130',
                'condition' => 'Kurang Upaya Pelbagai',
                'age' => 15
            ],
            [
                'name' => 'Afkanzhar binti Hasan',
                'ic' => '220707-05-0366',
                'oku_card' => 'LD050623000007',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 3
            ],
            [
                'name' => 'Nurul Rufiq binti Kadzrul Anwar',
                'ic' => '110278-01-0422',
                'oku_card' => 'PH100212002863',
                'condition' => 'Kurang Upaya Fizikal',
                'age' => 14
            ],
            [
                'name' => 'Muhammad Irfan bin Mohd Fadly',
                'ic' => '161217-14-0125',
                'oku_card' => 'LD140031000698',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 9
            ],
            [
                'name' => 'Nur Dlila Aleenya binti Ahmad Faisal',
                'ic' => '120529-14-0414',
                'oku_card' => 'LD140020000485',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 12
            ],
            [
                'name' => 'Ariff Adli bin Idham',
                'ic' => '060923-10-1809',
                'oku_card' => 'LD100122004466',
                'condition' => 'Kurang Upaya Pembelajaran',
                'age' => 18
            ],
            [
                'name' => 'Nurin Jazlina binti Abdul Wahab',
                'ic' => '021026-14-0240',
                'oku_card' => 'MD100318000019',
                'condition' => 'Kurang Upaya Pelbagai',
                'age' => 22
            ],
            [
                'name' => 'Iman Nur Syahirah binti Megan Halim',
                'ic' => '011025-14-1226',
                'oku_card' => 'DE140020000030',
                'condition' => 'Kurang Upaya Pendengaran',
                'age' => 23
            ],
            [
                'name' => 'Siti Nur Zulaikha binti Razali',
                'ic' => '000428-10-1452',
                'oku_card' => 'DE100815000002',
                'condition' => 'Kurang Upaya Pendengaran',
                'age' => 25
            ]
        ];

        $traineeCount = 0;
        foreach ($realTrainees as $data) {
            $nameParts = explode(' ', $data['name']);
            $firstName = $nameParts[0];
            $lastName = implode(' ', array_slice($nameParts, 1));

            $dob = Carbon::now()->subYears($data['age'])->subMonths(rand(1, 11))->subDays(rand(1, 28));

            Trainee::create([
                'trainee_id' => 'GMK' . sprintf('%04d', $traineeCount + 1),
                'trainee_first_name' => $firstName,
                'trainee_last_name' => $lastName,
                'trainee_email' => strtolower(str_replace(' ', '.', $firstName)) . '.' . ($traineeCount + 1) . '@ppdk-gombak.edu.my',
                'ic_number' => $data['ic'],
                'trainee_date_of_birth' => $dob,
                'gender' => $this->determineGender($data['name']),
                'trainee_condition' => $data['condition'],
                'centre_id' => $centre->centre_id,
                'centre_name' => $centre->centre_name,
                'status' => 'Active',
                'trainee_phone_number' => '01' . $faker->randomNumber(8, true),
                'trainee_address' => $faker->address,
                'guardian_name' => $faker->name,
                'guardian_phone' => '01' . $faker->randomNumber(8, true),
                'guardian_email' => $faker->email,
                'guardian_relationship' => $faker->randomElement(['Ibu', 'Bapa', 'Penjaga', 'Datuk', 'Nenek']),
                'emergency_contact_name' => $faker->name,
                'emergency_contact_phone' => '01' . $faker->randomNumber(8, true),
                'emergency_contact_relationship' => $faker->randomElement(['Saudara', 'Jiran', 'Pakcik', 'Makcik']),
                'medical_history' => 'OKU Card: ' . $data['oku_card'],
                'additional_notes' => 'Real trainee record from PPDK UIAM Gombak'
            ]);

            $traineeCount++;
        }

        $this->command->info("👨‍🎓 Created {$traineeCount} trainees");
    }

    /**
     * Create staff based on attendance records and organization chart
     */
    private function seedStaff($centre, $faker)
    {
        $this->command->info('👥 Seeding staff members...');

        // Function to generate IIUM staff ID (ABCD1234 format)
        $generateStaffId = function () {
            $letters = '';
            for ($i = 0; $i < 4; $i++) {
                $letters .= chr(rand(65, 90)); // A-Z
            }
            $numbers = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            return $letters . $numbers;
        };

        // Gombak addresses
        $gombakAddresses = [
            'Jalan 7/27A, Taman Gombak Setia, 53100 Kuala Lumpur',
            'Jalan Gombak 3/1, Taman Gombak Jaya, 53000 Kuala Lumpur',
            'Jalan Keranji 5, Taman Keranji, 68100 Batu Caves, Selangor',
            'Jalan Wangsa 8/2, Wangsa Maju, 53300 Kuala Lumpur',
            'Jalan AU5, Taman Keramat AU, 54200 Kuala Lumpur',
            'Jalan Selayang Baru 1A, Batu Caves, 68100 Selangor',
            'Jalan Rawang-Selayang, Rawang, 48000 Selangor',
            'Jalan Batu Caves 3/7, Gombak, 68100 Selangor'
        ];

        // Education levels and specializations for special needs education
        $educationLevels = ['Bachelor\'s Degree', 'Master\'s Degree', 'Diploma'];
        $educationSpecializations = [
            'Special Education',
            'Early Childhood Education',
            'Psychology',
            'Rehabilitation Sciences',
            'Islamic Studies',
            'Educational Psychology'
        ];
        $teachingSpecializations = [
            'Learning Disabilities',
            'Autism Spectrum Disorders',
            'Physical Disabilities',
            'Speech and Language Development',
            'Behavioral Interventions',
            'Life Skills Training'
        ];

        $staffMembers = [
            // Director (Admin) - Real person
            [
                'name' => 'Dr. Ruzita binti Abd. Rahim',
                'position' => 'Centre Director',
                'role' => 'admin',
                'birth_year' => 1972,
                'about' => 'Experienced director with extensive background in special needs education and rehabilitation services. Leading PPDK UIAM Gombak with dedication to inclusive education.',
                'education_level' => 'PhD',
                'education_specialization' => 'Special Education',
                'teaching_specialization' => 'Learning Disabilities'
            ],

            // Existing real staff from attendance records (Teachers)
            [
                'name' => 'Nabilah binti Ahmad',
                'position' => 'Special Education Teacher',
                'role' => 'teacher',
                'birth_year' => 1992,
                'about' => 'Dedicated special education teacher specializing in early intervention programs for children with learning disabilities.',
                'education_level' => 'Bachelor\'s Degree',
                'education_specialization' => 'Special Education',
                'teaching_specialization' => 'Learning Disabilities'
            ],
            [
                'name' => 'Najwa binti Ibrahim',
                'position' => 'Therapy Assistant',
                'role' => 'teacher',
                'birth_year' => 1990,
                'about' => 'Experienced therapy assistant focusing on speech and language development for children with communication disorders.',
                'education_level' => 'Diploma',
                'education_specialization' => 'Rehabilitation Sciences',
                'teaching_specialization' => 'Speech and Language Development'
            ],
            [
                'name' => 'Iman binti Yusof',
                'position' => 'Learning Support Teacher',
                'role' => 'teacher',
                'birth_year' => 1994,
                'about' => 'Passionate teacher specializing in behavioral interventions and life skills training for children with autism.',
                'education_level' => 'Bachelor\'s Degree',
                'education_specialization' => 'Psychology',
                'teaching_specialization' => 'Autism Spectrum Disorders'
            ],

            // Real Supervisors from organization
            [
                'name' => 'Nor Aisyah binti Muhamad Asri',
                'position' => 'Program Supervisor',
                'role' => 'supervisor',
                'birth_year' => 1982,
                'about' => 'Program supervisor with expertise in curriculum development and program management for special needs education.',
                'education_level' => 'Master\'s Degree',
                'education_specialization' => 'Educational Psychology',
                'teaching_specialization' => 'Behavioral Interventions'
            ],
            [
                'name' => 'Puan Azlin binti Nordin',
                'position' => 'Clinical Supervisor',
                'role' => 'supervisor',
                'birth_year' => 1978,
                'about' => 'Clinical supervisor overseeing therapy programs and ensuring quality care for trainees with various disabilities.',
                'education_level' => 'Master\'s Degree',
                'education_specialization' => 'Rehabilitation Sciences',
                'teaching_specialization' => 'Physical Disabilities'
            ],

            // Real AJK Committee Members from organization chart
            [
                'name' => 'Encik Ahmad Zaki bin Mohamed',
                'position' => 'Committee Member - Parent Liaison',
                'role' => 'ajk',
                'birth_year' => 1985,
                'about' => 'Active committee member facilitating communication between parents and centre staff, coordinating family support programs.',
                'education_level' => 'Bachelor\'s Degree',
                'education_specialization' => 'Psychology',
                'teaching_specialization' => 'Life Skills Training'
            ],
            [
                'name' => 'Puan Siti Hajar binti Kassim',
                'position' => 'Committee Member - Community Outreach',
                'role' => 'ajk',
                'birth_year' => 1980,
                'about' => 'Committee member responsible for community engagement and raising awareness about disability rights and inclusion.',
                'education_level' => 'Bachelor\'s Degree',
                'education_specialization' => 'Islamic Studies',
                'teaching_specialization' => 'Behavioral Interventions'
            ],
            [
                'name' => 'Dr. Faridah binti Zainal Abidin',
                'position' => 'Committee Member - Academic Affairs',
                'role' => 'ajk',
                'birth_year' => 1975,
                'about' => 'Senior committee member with PhD in Special Education, overseeing academic programs and research initiatives.',
                'education_level' => 'PhD',
                'education_specialization' => 'Special Education',
                'teaching_specialization' => 'Learning Disabilities'
            ],
            [
                'name' => 'Ustaz Muhammad Firdaus bin Hassan',
                'position' => 'Committee Member - Islamic Programs',
                'role' => 'ajk',
                'birth_year' => 1983,
                'about' => 'Committee member specializing in Islamic education programs and spiritual therapy for special needs children.',
                'education_level' => 'Master\'s Degree',
                'education_specialization' => 'Islamic Studies',
                'teaching_specialization' => 'Behavioral Interventions'
            ],

            // Additional real teachers and support staff
            [
                'name' => 'Cikgu Nurul Hidayah binti Mohd Yusof',
                'position' => 'Special Education Teacher',
                'role' => 'teacher',
                'birth_year' => 1988,
                'about' => 'Special education teacher focusing on inclusive education methods and assistive technology integration.',
                'education_level' => 'Bachelor\'s Degree',
                'education_specialization' => 'Special Education',
                'teaching_specialization' => 'Physical Disabilities'
            ],
            [
                'name' => 'Encik Mohd Azlan bin Ibrahim',
                'position' => 'Therapy Assistant',
                'role' => 'teacher',
                'birth_year' => 1986,
                'about' => 'Therapy assistant specializing in motor skills development and physical therapy support for children with disabilities.',
                'education_level' => 'Diploma',
                'education_specialization' => 'Rehabilitation Sciences',
                'teaching_specialization' => 'Physical Disabilities'
            ],
            [
                'name' => 'Puan Rohani binti Abdul Rahman',
                'position' => 'Speech Therapist',
                'role' => 'teacher',
                'birth_year' => 1984,
                'about' => 'Qualified speech therapist working with children with communication disorders and speech delays.',
                'education_level' => 'Bachelor\'s Degree',
                'education_specialization' => 'Rehabilitation Sciences',
                'teaching_specialization' => 'Speech and Language Development'
            ]
        ];

        $staffCount = 0;
        foreach ($staffMembers as $data) {
            $staffId = $generateStaffId();
            $address = $gombakAddresses[array_rand($gombakAddresses)];
            $dob = Carbon::create($data['birth_year'], rand(1, 12), rand(1, 28));

            // Generate proper email by removing titles and handling Malaysian names correctly
            $cleanName = str_replace(['Dr. ', 'Puan ', 'Encik ', 'Cikgu ', 'Ustaz '], '', $data['name']);
            $cleanName = str_replace([' bin ', ' binti ', ' '], ['.', '.', '.'], $cleanName);
            $email = strtolower($cleanName) . '@iium.edu.my';

            User::create([
                'iium_id' => $staffId,
                'name' => $data['name'],
                'email' => $email,
                'password' => bcrypt('password123'),
                'role' => $data['role'],
                'position' => $data['position'],
                'centre_id' => $centre->centre_id,
                'centre_location' => $centre->centre_name,
                'status' => 'active',
                'phone' => '03-' . $faker->numberBetween(10000000, 99999999),
                'address' => $address,
                'education_level' => $data['education_level'],
                'education_specialization' => $data['education_specialization'],
                'teaching_specialization' => $data['teaching_specialization'],
                'date_of_birth' => $dob,
                'about' => $data['about'],
                'user_last_accessed_at' => Carbon::now()->subDays(rand(0, 30)),
                'created_at' => Carbon::create(2024, rand(6, 8), rand(1, 28)),
                'updated_at' => now()
            ]);
            $staffCount++;
        }

        $this->command->info("👥 Created {$staffCount} staff members from organization chart");
    }

    /**
     * Create activities based on schedule data
     */
    private function seedActivities($centre, $faker)
    {
        $this->command->info('📚 Seeding Islamic therapy and educational activities...');

        // Category mapping
        $categoryMapping = [
            'Religious Therapy' => 11,    // Islamic Education
            'Physical Therapy' => 3,      // Physical Therapy
            'Academic Skills' => 5,       // Academic Skills
            'Cognitive Skills' => 5,      // Academic Skills
            'Arts Therapy' => 6,          // Creative Arts
            'Social Skills' => 7,         // Social Skills Training
        ];

        $activities = [
            // Islamic Therapy Activities
            [
                'name' => 'Terapi Al-Quran',
                'description' => 'Quranic therapy sessions for spiritual and cognitive development',
                'category' => 'Religious Therapy',
                'duration' => 30,
                'max_participants' => 8
            ],
            [
                'name' => 'Terapi Solat',
                'description' => 'Prayer therapy to develop spiritual awareness and motor coordination',
                'category' => 'Religious Therapy',
                'duration' => 30,
                'max_participants' => 12
            ],
            [
                'name' => 'Terapi Dhikir',
                'description' => 'Remembrance of Allah therapy for mental peace and focus',
                'category' => 'Religious Therapy',
                'duration' => 30,
                'max_participants' => 12
            ],

            // Motor Skills Activities
            [
                'name' => 'Motor Halus',
                'description' => 'Fine motor skills development through hand and finger exercises',
                'category' => 'Physical Therapy',
                'duration' => 30,
                'max_participants' => 6
            ],
            [
                'name' => 'Motor Kasar',
                'description' => 'Gross motor skills development through whole body movement',
                'category' => 'Physical Therapy',
                'duration' => 30,
                'max_participants' => 8
            ],

            // Educational Activities
            [
                'name' => 'Pre-Akademik Asas 3M',
                'description' => 'Basic academic skills: Membaca, Menulis, Mengira (Reading, Writing, Arithmetic)',
                'category' => 'Academic Skills',
                'duration' => 30,
                'max_participants' => 6
            ],
            [
                'name' => 'Penulisan Jawi',
                'description' => 'Arabic script writing skills development',
                'category' => 'Academic Skills',
                'duration' => 30,
                'max_participants' => 8
            ],
            [
                'name' => 'Mengenal Warna',
                'description' => 'Color recognition and identification skills',
                'category' => 'Cognitive Skills',
                'duration' => 30,
                'max_participants' => 8
            ],

            // Arts and Music
            [
                'name' => 'Terapi Muzik',
                'description' => 'Music therapy using traditional and modern instruments',
                'category' => 'Arts Therapy',
                'duration' => 30,
                'max_participants' => 12
            ],
            [
                'name' => 'Kesenian Seni',
                'description' => 'Art and craft activities for creativity and fine motor development',
                'category' => 'Arts Therapy',
                'duration' => 30,
                'max_participants' => 8
            ],
            [
                'name' => 'Kesenian Islam',
                'description' => 'Islamic art and calligraphy for cultural and spiritual development',
                'category' => 'Arts Therapy',
                'duration' => 30,
                'max_participants' => 6
            ],

            // Social Activities
            [
                'name' => 'Circle Time',
                'description' => 'Group discussion and social interaction activities',
                'category' => 'Social Skills',
                'duration' => 30,
                'max_participants' => 12
            ]
        ];

        $teachers = User::where('centre_id', $centre->centre_id)->where('role', 'teacher')->get();
        $activityCount = 0;

        foreach ($activities as $data) {
            $teacher = $teachers->random();

            Activity::create([
                'activity_name' => $data['name'],
                'activity_description' => $data['description'],
                'activity_location' => $faker->randomElement(['Therapy Room A', 'Therapy Room B', 'Main Hall', 'Music Room', 'Art Studio']),
                'max_participants' => $data['max_participants'],
                'session_duration_minutes' => $data['duration'],
                'instructor_id' => $teacher->id,
                'centre_id' => $centre->centre_id,
                'category_id' => $categoryMapping[$data['category']] ?? 11, // Default to Islamic Education
                'learning_outcomes' => 'Develop ' . strtolower($data['category']) . ' through structured therapeutic activities',
                'is_active' => true,
                'duration_weeks' => 12,
                'sessions_per_week' => 2
            ]);

            $activityCount++;
        }

        $this->command->info("📚 Created {$activityCount} activities");
    }

    /**
     * Create schedule sessions based on daily timetable
     */
    private function seedSchedules($centre, $faker)
    {
        $this->command->info('📅 Seeding daily schedules...');

        $activities = Activity::where('centre_id', $centre->centre_id)->get();
        $sessionCount = 0;

        // Create sessions for the next 4 weeks (Monday-Thursday schedule)
        $startDate = Carbon::now()->startOfWeek(); // Start from this Monday

        for ($week = 0; $week < 4; $week++) {
            for ($day = 0; $day < 4; $day++) { // Monday to Thursday
                $sessionDate = $startDate->copy()->addWeeks($week)->addDays($day);

                // Morning sessions based on real schedule (adjusted for business rules)
                $timeSlots = [
                    ['09:30', '10:00'], // Terapi Al-Quran / Religious (shifted to comply with rules)
                    ['10:00', '10:30'], // Terapi Solat
                    ['10:30', '11:00'], // Motor skills / Arts
                    ['11:00', '11:30'], // Academic / Circle Time
                ];

                foreach ($timeSlots as $index => $slot) {
                    if ($activities->count() > $index) {
                        $activity = $activities->skip($index)->first();

                        ActivitySession::create([
                            'activity_id' => $activity->id,
                            'session_name' => $activity->activity_name . ' - ' . $sessionDate->format('l'),
                            'session_description' => 'Daily ' . $activity->activity_name . ' session',
                            'session_date' => $sessionDate,
                            'start_time' => $slot[0] . ':00',
                            'end_time' => $slot[1] . ':00',
                            'location' => $activity->activity_location,
                            'instructor_id' => $activity->instructor_id,
                            'max_participants' => $activity->max_participants,
                            'session_notes' => 'Real-world schedule based on PPDK daily timetable'
                        ]);

                        $sessionCount++;
                    }
                }
            }
        }

        $this->command->info("📅 Created {$sessionCount} scheduled sessions");
    }

    /**
     * Determine gender from Malaysian name
     */
    private function determineGender($name)
    {
        $malePrefixes = ['bin', 'Bin'];
        $femalePrefixes = ['binti', 'Binti'];

        foreach ($malePrefixes as $prefix) {
            if (strpos($name, $prefix) !== false) {
                return 'Male';
            }
        }

        foreach ($femalePrefixes as $prefix) {
            if (strpos($name, $prefix) !== false) {
                return 'Female';
            }
        }

        // Default fallback based on first name
        $maleNames = ['Muhammad', 'Mohd', 'Ahmad', 'Aariz', 'Ariff', 'Irfan'];
        $firstName = explode(' ', $name)[0];

        return in_array($firstName, $maleNames) ? 'Male' : 'Female';
    }
}
