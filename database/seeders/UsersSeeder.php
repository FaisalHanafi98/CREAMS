<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Centres;

class UsersSeeder extends Seeder
{
    /**
     * Store used emails to prevent duplicates
     * 
     * @var array
     */
    private $usedEmails = [];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have the centers
        $this->createCentres();
        
        // Create specific role-based users for each center
        $this->command->info('Creating role-based users for each centre...');
        $this->createUsersForCentre('04', 'Pagoh');
        $this->createUsersForCentre('02', 'Kuantan');
        $this->createUsersForCentre('03', 'Gambang');
        $this->createUsersForCentre('01', 'Gombak');
        
        // Store all existing emails to prevent duplicates
        $this->usedEmails = DB::table('users')->pluck('email')->toArray();
        
        // Now create additional diverse users across centers
        $this->createDiverseUsers();
        
        // Verify final distribution
        $this->verifyDistribution();
        
        $this->command->info('All users created successfully!');
    }
    
    /**
     * Create the required centers if they don't exist
     */
    private function createCentres(): void
    {
        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'centre_status' => 'active',
                'status' => 'active'
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'Kuantan',
                'centre_status' => 'active',
                'status' => 'active'
            ],
            [
                'centre_id' => '03',
                'centre_name' => 'Gambang',
                'centre_status' => 'active',
                'status' => 'active'
            ],
            [
                'centre_id' => '04',
                'centre_name' => 'Pagoh',
                'centre_status' => 'active',
                'status' => 'active'
            ]
        ];
        
        foreach ($centres as $centre) {
            Centres::updateOrCreate(
                ['centre_id' => $centre['centre_id']],
                $centre
            );
        }
        
        $this->command->info('Centers created: Gombak, Kuantan, Gambang, and Pagoh');
    }
    
    /**
     * Create users for a specific center with different roles
     * 
     * @param string $centreId The centre ID
     * @param string $centreName The centre name
     */
    private function createUsersForCentre(string $centreId, string $centreName): void
    {
        // Create 1 admin
        $this->createAdminForCentre($centreId, $centreName);
        
        // Create 2 supervisors
        $this->createSupervisorsForCentre($centreId, $centreName, 2);
        
        // Create 3 AJKs
        $this->createAJKsForCentre($centreId, $centreName, 3);
        
        // Create 5 teachers
        $this->createTeachersForCentre($centreId, $centreName, 5);
    }
    
    /**
     * Create an admin for a center
     */
    private function createAdminForCentre(string $centreId, string $centreName): void
    {
        $malayNames = [
            'Ahmad Razali bin Abdullah',
            'Mohd Hafiz bin Ibrahim',
        ];
        
        $name = $malayNames[array_rand($malayNames)];
        $nameParts = explode(' ', $name);
        
        // Generate IIUM ID: 4 numbers followed by 4 alphabets
        $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
        $iiumId = $numericPart . $alphabetPart;
        
        $email = strtolower(str_replace(' ', '.', $nameParts[0] . '.' . $nameParts[1])) . '.' . $centreId . '@iium.edu.my';
        
        Users::updateOrCreate(
            ['email' => $email],
            [
                'iium_id' => $iiumId,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'centre_id' => $centreId,
                'status' => 'active'
            ]
        );
        
        $this->command->info("Created Admin for $centreName: $name ($email)");
    }
    
    /**
     * Create supervisors for a center
     */
    private function createSupervisorsForCentre(string $centreId, string $centreName, int $count): void
    {
        $malayNames = [
            'Noraziah binti Hassan',
            'Zulkifli bin Othman',
            'Nurul Huda binti Mohd Yusof',
            'Ismail bin Abdullah',
            'Fatimah binti Zainuddin',
            'Kamaruddin bin Ahmad',
        ];
        
        shuffle($malayNames);
        
        for ($i = 0; $i < $count; $i++) {
            $name = $malayNames[$i];
            $nameParts = explode(' ', $name);
            
            // Generate IIUM ID: 4 numbers followed by 4 alphabets
            $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
            $iiumId = $numericPart . $alphabetPart;
            
            $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.' . $centreId . '.' . ($i + 1) . '@iium.edu.my';
            
            Users::updateOrCreate(
                ['email' => $email],
                [
                    'iium_id' => $iiumId,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role' => 'supervisor',
                    'centre_id' => $centreId,
                    'status' => 'active'
                ]
            );
            
            $this->command->info("Created Supervisor for $centreName: $name ($email)");
        }
    }
    
    /**
     * Create AJKs for a center
     */
    private function createAJKsForCentre(string $centreId, string $centreName, int $count): void
    {
        $malayNames = [
            'Amirah binti Hamid',
            'Mohd Faizal bin Razak',
            'Siti Aishah binti Mahmud',
            'Azman bin Mohd Noor',
            'Rosli bin Ismail',
            'Noraini binti Abdul Rahman',
            'Zainab binti Mohamad',
        ];
        
        shuffle($malayNames);
        
        for ($i = 0; $i < $count; $i++) {
            $name = $malayNames[$i];
            $nameParts = explode(' ', $name);
            
            // Generate IIUM ID: 4 numbers followed by 4 alphabets
            $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
            $iiumId = $numericPart . $alphabetPart;
            
            $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.ajk.' . $centreId . '.' . ($i + 1) . '@iium.edu.my';
            
            Users::updateOrCreate(
                ['email' => $email],
                [
                    'iium_id' => $iiumId,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role' => 'ajk',
                    'centre_id' => $centreId,
                    'status' => 'active'
                ]
            );
            
            $this->command->info("Created AJK for $centreName: $name ($email)");
        }
    }
    
    /**
     * Create teachers for a center
     */
    private function createTeachersForCentre(string $centreId, string $centreName, int $count): void
    {
        $malayNames = [
            'Nurul Ain binti Muhammad',
            'Mohd Rizal bin Hamzah',
            'Azlina binti Mohd Yusof',
            'Ahmad Firdaus bin Omar',
            'Siti Noor binti Abdul Aziz',
            'Zainal bin Abidin',
            'Faridah binti Ismail',
            'Mohd Hafiz bin Ibrahim',
            'Rosnah binti Abdul Hamid',
            'Khairul Anuar bin Bakar',
        ];
        
        shuffle($malayNames);
        
        for ($i = 0; $i < $count; $i++) {
            $name = $malayNames[$i];
            $nameParts = explode(' ', $name);
            
            // Generate IIUM ID: 4 numbers followed by 4 alphabets
            $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
            $iiumId = $numericPart . $alphabetPart;
            
            $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.teacher.' . $centreId . '.' . ($i + 1) . '@iium.edu.my';
            
            Users::updateOrCreate(
                ['email' => $email],
                [
                    'iium_id' => $iiumId,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role' => 'teacher',
                    'centre_id' => $centreId,
                    'status' => 'active'
                ]
            );
            
            $this->command->info("Created Teacher for $centreName: $name ($email)");
        }
    }
    
    /**
     * Create additional diverse users across centres
     */
    private function createDiverseUsers(): void
    {
        $this->command->info("\nCreating additional diverse users across centres...");
        
        // Get all active centres
        $centres = Centres::where('centre_status', 'active')->get(['centre_id', 'centre_name']);
        
        // Define number of additional users to create
        $totalUsersToCreate = 60; // Adjust as needed
        
        // Distribution percentages across centers
        $distribution = [
            '01' => 28, // Gombak - 28%
            '02' => 26, // Kuantan - 26% 
            '03' => 24, // Gambang - 24%
            '04' => 22, // Pagoh - 22%
        ];
        
        // Calculate exact numbers
        $userCounts = [];
        $remainingUsers = $totalUsersToCreate;
        
        foreach ($distribution as $centreId => $percentage) {
            if (array_search($centreId, $centres->pluck('centre_id')->toArray()) === false) {
                // Skip if centre doesn't exist
                continue;
            }
            
            $count = floor($totalUsersToCreate * ($percentage / 100));
            $userCounts[$centreId] = $count;
            $remainingUsers -= $count;
        }
        
        // Distribute any remaining users to the first centre
        if ($remainingUsers > 0) {
            $firstCentreId = $centres->first()->centre_id;
            $userCounts[$firstCentreId] += $remainingUsers;
        }
        
        // Create the users for each centre
        foreach ($userCounts as $centreId => $count) {
            $centreName = $centres->where('centre_id', $centreId)->first()->centre_name;
            
            $this->command->info("Creating {$count} additional diverse users for {$centreName} (ID: {$centreId})");
            
            for ($i = 0; $i < $count; $i++) {
                // Generate a malay name
                $firstName = $this->getRandomMalayName();
                $lastName = $this->getRandomMalayName();
                $name = $firstName . ' bin/binti ' . $lastName;
                
                // Generate IIUM ID: 4 numbers followed by 4 alphabets
                $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
                $iiumId = $numericPart . $alphabetPart;
                
                // Generate a guaranteed unique email by including timestamp and random string
                $uniqueSuffix = time() . rand(1000, 9999);
                $email = strtolower(str_replace(' ', '.', $firstName)) . '.' . 
                         strtolower($lastName) . '.' . 
                         $centreId . '.' . 
                         $uniqueSuffix . '@iium.edu.my';
                
                Users::create([
                    'iium_id' => $iiumId,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role' => $this->getRandomRole(),
                    'centre_id' => $centreId,
                    'status' => 'active'
                ]);
            }
        }
    }
    
    /**
     * Get a random Malay name
     * 
     * @return string
     */
    private function getRandomMalayName(): string
    {
        $malayNames = [
            'Ahmad', 'Muhammad', 'Ali', 'Hassan', 'Ibrahim', 'Ismail', 'Osman',
            'Yusof', 'Abdullah', 'Othman', 'Razak', 'Hamid', 'Aziz', 'Bakar',
            'Karim', 'Rahman', 'Salleh', 'Zain', 'Nasir', 'Malik', 'Anwar', 'Hasan',
            'Nurul', 'Siti', 'Fatimah', 'Aisha', 'Noraini', 'Zainab', 'Mariam',
            'Hafsah', 'Aminah', 'Faridah', 'Azlina', 'Noor', 'Rosnah', 'Halimah',
            'Ramlah', 'Saadiah', 'Zarina', 'Sharifah', 'Khadijah', 'Maimunah'
        ];
        
        return $malayNames[array_rand($malayNames)];
    }
    
    /**
     * Get a random user role with weighted distribution
     * 
     * @return string
     */
    private function getRandomRole(): string
    {
        $roles = ['student', 'lecturer', 'admin', 'staff'];
        // Weighted distribution - more students than others
        $weights = [70, 15, 5, 10]; // 70% students, 15% lecturers, 5% admins, 10% staff
        
        $rand = mt_rand(1, array_sum($weights));
        $sum = 0;
        
        foreach ($weights as $index => $weight) {
            $sum += $weight;
            if ($rand <= $sum) {
                return $roles[$index];
            }
        }
        
        return 'student';
    }
    
    /**
     * Verify the distribution of users across centres
     */
    private function verifyDistribution(): void
    {
        $this->command->info("\nFinal user distribution across centres:");
        
        $stats = DB::table('users')
            ->select('centre_id', 'centre_location', DB::raw('count(*) as count'))
            ->groupBy('centre_id', 'centre_location')
            ->orderBy('centre_id')
            ->get();
            
        foreach ($stats as $stat) {
            $this->command->info("- {$stat->centre_location} (ID: {$stat->centre_id}): {$stat->count} users");
        }
        
        $this->command->info("\nRole distribution:");
        $roleStats = DB::table('users')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($roleStats as $stat) {
            $this->command->info("- {$stat->role}: {$stat->count} users");
        }
        
        $this->command->info("\nTotal users: " . DB::table('users')->count());
    }
}