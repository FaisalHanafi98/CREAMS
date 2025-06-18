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
        // Fix existing users with wrong roles and wrong gender markers
        $this->fixExistingUsers();
        
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
        $maleNames = [
            'Ahmad Razali',
            'Mohd Hafiz',
            'Muhammad Rizwan',
            'Khairul Anuar',
        ];
        
        $lastNames = [
            'Abdullah',
            'Ibrahim',
            'Ismail',
            'Othman',
            'Bakar'
        ];
        
        $firstName = $maleNames[array_rand($maleNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $name = $firstName . ' bin ' . $lastName;
        $nameParts = explode(' ', $firstName);
        
        // Generate IIUM ID: 4 numbers followed by 4 alphabets
        $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
        $iiumId = $numericPart . $alphabetPart;
        
        $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.' . $centreId . '@iium.edu.my';
        
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
        $femaleNames = [
            'Noraziah',
            'Nurul Huda',
            'Fatimah',
            'Siti Aisyah',
            'Nur Syafiqah',
        ];
        
        $maleNames = [
            'Zulkifli',
            'Ismail',
            'Kamaruddin',
            'Ahmad Firdaus',
            'Muhammad Zikri',
        ];
        
        $lastNames = [
            'Hassan',
            'Othman',
            'Mohd Yusof',
            'Abdullah',
            'Zainuddin',
            'Ahmad',
        ];
        
        for ($i = 0; $i < $count; $i++) {
            // Alternate between male and female names
            if ($i % 2 == 0) {
                $firstName = $maleNames[array_rand($maleNames)];
                $gender = 'bin';
            } else {
                $firstName = $femaleNames[array_rand($femaleNames)];
                $gender = 'binti';
            }
            
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $firstName . ' ' . $gender . ' ' . $lastName;
            
            // Generate IIUM ID: 4 numbers followed by 4 alphabets
            $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
            $iiumId = $numericPart . $alphabetPart;
            
            $email = strtolower(str_replace(' ', '.', $firstName)) . '.' . $centreId . '.' . ($i + 1) . '@iium.edu.my';
            
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
        $femaleNames = [
            'Amirah',
            'Siti Aishah',
            'Noraini',
            'Zainab',
            'Nurul Ain',
            'Azlina',
        ];
        
        $maleNames = [
            'Mohd Faizal',
            'Azman',
            'Rosli',
            'Ahmad Firdaus',
            'Zainal',
            'Anuar',
        ];
        
        $lastNames = [
            'Hamid',
            'Razak',
            'Mahmud',
            'Mohd Noor',
            'Ismail',
            'Abdul Rahman',
            'Mohamad',
        ];
        
        for ($i = 0; $i < $count; $i++) {
            // Alternate between male and female names with more females (2:1 ratio)
            if ($i % 3 == 0 || $i % 3 == 1) {
                $firstName = $femaleNames[array_rand($femaleNames)];
                $gender = 'binti';
            } else {
                $firstName = $maleNames[array_rand($maleNames)];
                $gender = 'bin';
            }
            
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $firstName . ' ' . $gender . ' ' . $lastName;
            
            // Generate IIUM ID: 4 numbers followed by 4 alphabets
            $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
            $iiumId = $numericPart . $alphabetPart;
            
            $email = strtolower(str_replace(' ', '.', $firstName)) . '.ajk.' . $centreId . '.' . ($i + 1) . '@iium.edu.my';
            
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
        $femaleNames = [
            'Nurul Ain',
            'Azlina',
            'Siti Noor',
            'Faridah',
            'Rosnah',
            'Halimah',
            'Nur Hidayah',
        ];
        
        $maleNames = [
            'Mohd Rizal',
            'Ahmad Firdaus',
            'Zainal',
            'Mohd Hafiz',
            'Khairul Anuar',
            'Muhammad Imran',
            'Azizi',
        ];
        
        $lastNames = [
            'Muhammad',
            'Hamzah',
            'Mohd Yusof',
            'Omar',
            'Abdul Aziz',
            'Abidin',
            'Ismail',
            'Ibrahim',
            'Abdul Hamid',
            'Bakar',
        ];
        
        for ($i = 0; $i < $count; $i++) {
            // Balanced gender distribution
            if ($i % 2 == 0) {
                $firstName = $maleNames[array_rand($maleNames)];
                $gender = 'bin';
            } else {
                $firstName = $femaleNames[array_rand($femaleNames)];
                $gender = 'binti';
            }
            
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $firstName . ' ' . $gender . ' ' . $lastName;
            
            // Generate IIUM ID: 4 numbers followed by 4 alphabets
            $numericPart = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $alphabetPart = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
            $iiumId = $numericPart . $alphabetPart;
            
            $email = strtolower(str_replace(' ', '.', $firstName)) . '.teacher.' . $centreId . '.' . ($i + 1) . '@iium.edu.my';
            
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
        
        // Define role distribution
        $roleDistribution = [
            'admin' => 5,      // 5%
            'supervisor' => 10, // 10%
            'ajk' => 25,       // 25%
            'teacher' => 60     // 60% (remainder)
        ];
        
        // Create the users for each centre
        foreach ($userCounts as $centreId => $count) {
            $centreName = $centres->where('centre_id', $centreId)->first()->centre_name;
            
            $this->command->info("Creating {$count} additional diverse users for {$centreName} (ID: {$centreId})");
            
            // Calculate role counts for this centre
            $adminCount = max(1, round($count * ($roleDistribution['admin'] / 100)));
            $supervisorCount = max(1, round($count * ($roleDistribution['supervisor'] / 100)));
            $ajkCount = max(1, round($count * ($roleDistribution['ajk'] / 100)));
            
            // Ensure we don't exceed total count
            $teacherCount = $count - ($adminCount + $supervisorCount + $ajkCount);
            
            // Combined count array
            $roleCounts = [
                'admin' => $adminCount,
                'supervisor' => $supervisorCount,
                'ajk' => $ajkCount,
                'teacher' => $teacherCount
            ];
            
            $this->command->info("  Role breakdown: Admin: $adminCount, Supervisor: $supervisorCount, AJK: $ajkCount, Teacher: $teacherCount");
            
            // Create users for each role
            foreach ($roleCounts as $role => $roleCount) {
                for ($i = 0; $i < $roleCount; $i++) {
                    // Determine gender - approx 50/50 split
                    $isMale = (rand(0, 1) == 1);
                    
                    // Get first name based on gender
                    $firstName = $isMale ? $this->getRandomMalayMaleName() : $this->getRandomMalayFemaleName();
                    $lastName = $this->getRandomMalayLastName();
                    $gender = $isMale ? 'bin' : 'binti';
                    $name = $firstName . ' ' . $gender . ' ' . $lastName;
                    
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
                        'role' => $role,
                        'centre_id' => $centreId,
                        'status' => 'active'
                    ]);
                }
            }
        }
    }
    
    /**
     * Get a random Malay male name
     * 
     * @return string
     */
    private function getRandomMalayMaleName(): string
    {
        $malayMaleNames = [
            'Ahmad', 'Muhammad', 'Ali', 'Hassan', 'Ibrahim', 'Ismail', 'Osman',
            'Yusof', 'Abdullah', 'Othman', 'Razak', 'Hamid', 'Aziz', 'Bakar',
            'Karim', 'Rahman', 'Salleh', 'Zain', 'Nasir', 'Malik', 'Anwar', 'Hasan',
            'Mohd Rizal', 'Azman', 'Rosli', 'Zainal', 'Khairul', 'Imran', 'Azizi',
            'Firdaus', 'Hafiz', 'Zikri', 'Amirul', 'Syafiq', 'Izzat', 'Faizal'
        ];
        
        return $malayMaleNames[array_rand($malayMaleNames)];
    }
    
    /**
     * Get a random Malay female name
     * 
     * @return string
     */
    private function getRandomMalayFemaleName(): string
    {
        $malayFemaleNames = [
            'Nurul', 'Siti', 'Fatimah', 'Aisha', 'Noraini', 'Zainab', 'Mariam',
            'Hafsah', 'Aminah', 'Faridah', 'Azlina', 'Noor', 'Rosnah', 'Halimah',
            'Ramlah', 'Saadiah', 'Zarina', 'Sharifah', 'Khadijah', 'Maimunah',
            'Nur Syafiqah', 'Aisyah', 'Hidayah', 'Suhaila', 'Nadia', 'Amirah',
            'Hani', 'Farah', 'Najwa', 'Syahirah', 'Adibah', 'Afiqah', 'Aliya'
        ];
        
        return $malayFemaleNames[array_rand($malayFemaleNames)];
    }
    
    /**
     * Get a random Malay last name
     * 
     * @return string
     */
    private function getRandomMalayLastName(): string
    {
        $malayLastNames = [
            'Ahmad', 'Muhammad', 'Ibrahim', 'Ismail', 'Abdullah', 'Othman', 
            'Abdul Rahman', 'Abdul Aziz', 'Mohd Yusof', 'Abdul Hamid', 'Bakar',
            'Hassan', 'Razak', 'Omar', 'Mohamad', 'Mahmud', 'Abdul Rahim',
            'Hashim', 'Jamaluddin', 'Sulaiman', 'Hamzah', 'Mohd Noor', 'Yusoff',
            'Idris', 'Yaakob', 'Mustafa', 'Abu Bakar', 'Zainal Abidin'
        ];
        
        return $malayLastNames[array_rand($malayLastNames)];
    }
    
    /**
     * Fix existing users with wrong roles and bin/binti issues
     */
    private function fixExistingUsers(): void
    {
        $this->command->info('Starting to fix existing users...');
        
        // 1. Fix incorrect roles
        $wrongRolesCount = DB::table('users')
            ->whereNotIn('role', ['admin', 'supervisor', 'ajk', 'teacher'])
            ->count();
        
        if ($wrongRolesCount > 0) {
            $this->command->info("Found $wrongRolesCount users with incorrect roles to fix.");
            
            // Get the list of users with incorrect roles
            $usersToFix = DB::table('users')
                ->whereNotIn('role', ['admin', 'supervisor', 'ajk', 'teacher'])
                ->get();
            
            // Get counts for proper distribution
            $totalCount = $usersToFix->count();
            $adminCount = max(1, round($totalCount * 0.05)); // 5%
            $supervisorCount = max(1, round($totalCount * 0.10)); // 10%
            $ajkCount = max(1, round($totalCount * 0.25)); // 25%
            $teacherCount = $totalCount - ($adminCount + $supervisorCount + $ajkCount); // remainder
            
            $this->command->info("  Role distribution plan: Admin: $adminCount, Supervisor: $supervisorCount, AJK: $ajkCount, Teacher: $teacherCount");
            
            // Update roles according to the distribution
            $counter = 0;
            foreach ($usersToFix as $user) {
                $newRole = 'teacher'; // Default to teacher
                
                if ($counter < $adminCount) {
                    $newRole = 'admin';
                } elseif ($counter < ($adminCount + $supervisorCount)) {
                    $newRole = 'supervisor';
                } elseif ($counter < ($adminCount + $supervisorCount + $ajkCount)) {
                    $newRole = 'ajk';
                }
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['role' => $newRole]);
                
                $counter++;
            }
            
            $this->command->info("Fixed roles for $wrongRolesCount users.");
        } else {
            $this->command->info("No users with incorrect roles found.");
        }
        
        // 2. Fix bin/binti issues
        $invalidGenderCount = DB::table('users')
            ->where('name', 'like', '% bin/binti %')
            ->count();
        
        if ($invalidGenderCount > 0) {
            $this->command->info("Found $invalidGenderCount users with 'bin/binti' in their names that need to be fixed.");
            
            // Get the list of users with bin/binti issues
            $usersWithGenderIssues = DB::table('users')
                ->where('name', 'like', '% bin/binti %')
                ->get();
            
            foreach ($usersWithGenderIssues as $user) {
                // Get the name parts
                $nameParts = explode(' bin/binti ', $user->name);
                
                if (count($nameParts) < 2) {
                    continue; // Skip if we can't parse the name properly
                }
                
                $firstName = $nameParts[0];
                $lastName = $nameParts[1];
                
                // Determine gender based on first name
                // This is an approximation - we check if it contains common female name patterns
                $isMale = true; // Default to male
                $femalePrefixes = ['nurul', 'siti', 'nur', 'aisha', 'fatimah', 'noraini', 'zainab', 'mariam', 'faridah', 'azlina'];
                
                foreach ($femalePrefixes as $prefix) {
                    if (stripos($firstName, $prefix) !== false) {
                        $isMale = false;
                        break;
                    }
                }
                
                // Build the corrected name
                $gender = $isMale ? 'bin' : 'binti';
                $correctedName = $firstName . ' ' . $gender . ' ' . $lastName;
                
                // Update the name
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['name' => $correctedName]);
                
                $this->command->info("  Fixed name: {$user->name} → {$correctedName}");
            }
            
            $this->command->info("Fixed bin/binti issues for $invalidGenderCount users.");
        } else {
            $this->command->info("No users with bin/binti issues found.");
        }
        
        $this->command->info('User fixes completed!');
    }
    
    /**
     * Verify the distribution of users across centres
     */
    private function verifyDistribution(): void
    {
        $this->command->info("\nFinal user distribution across centres:");
        
        $stats = DB::table('users')
            ->select('centre_id', DB::raw('count(*) as count'))
            ->groupBy('centre_id')
            ->orderBy('centre_id')
            ->get();
            
        foreach ($stats as $stat) {
            $centreName = Centres::where('centre_id', $stat->centre_id)->first()->centre_name ?? 'Unknown';
            $this->command->info("- {$centreName} (ID: {$stat->centre_id}): {$stat->count} users");
        }
        
        $this->command->info("\nRole distribution:");
        $roleStats = DB::table('users')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($roleStats as $stat) {
            $percentage = round(($stat->count / DB::table('users')->count()) * 100, 2);
            $this->command->info("- {$stat->role}: {$stat->count} users ({$percentage}%)");
        }
        
        $this->command->info("\nGender distribution:");
        $maleCount = DB::table('users')->where('name', 'like', '% bin %')->count();
        $femaleCount = DB::table('users')->where('name', 'like', '% binti %')->count();
        $totalCount = $maleCount + $femaleCount;
        
        $malePercentage = $totalCount > 0 ? round(($maleCount / $totalCount) * 100, 2) : 0;
        $femalePercentage = $totalCount > 0 ? round(($femaleCount / $totalCount) * 100, 2) : 0;
        
        $this->command->info("- Male (bin): {$maleCount} users ({$malePercentage}%)");
        $this->command->info("- Female (binti): {$femaleCount} users ({$femalePercentage}%)");
        
        $this->command->info("\nTotal users: " . DB::table('users')->count());
    }
}