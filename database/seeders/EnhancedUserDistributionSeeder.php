<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Centre;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnhancedUserDistributionSeeder extends Seeder
{
    private $roleHierarchy = [
        'admin' => 1,      // 1 per centre
        'supervisor' => 2, // 2 per centre  
        'teacher' => 5,    // 5 per centre
        'ajk' => 2         // 2 per centre
    ];

    private $maleNames = [
        'Ahmad Firdaus', 'Muhammad Danial', 'Ahmad Azhar', 'Muhammad Aiman', 
        'Muhammad Azim', 'Mohd Hafiz', 'Ahmad Syafiq', 'Muhammad Fikri',
        'Ahmad Zulkifli', 'Muhammad Hakim', 'Ahmad Rashid', 'Muhammad Irfan',
        'Ahmad Fahmi', 'Muhammad Nazri', 'Ahmad Ridhwan', 'Muhammad Shafiq'
    ];

    private $femaleNames = [
        'Siti Aminah', 'Nur Aisyah', 'Faridah Salwa', 'Siti Nurhaliza',
        'Nurfatihah', 'Nur Azlina', 'Fauziah Rahman', 'Siti Noraini',
        'Nur Syazana', 'Faridah Hanim', 'Siti Zaleha', 'Nur Fadhilah',
        'Fauziah Hanim', 'Siti Maryam', 'Nur Shahira', 'Faridah Wahida'
    ];

    private $lastNames = [
        'bin Abdullah', 'bin Rahman', 'bin Hassan', 'bin Mohd Nor',
        'bin Ahmad', 'bin Sulaiman', 'bin Mohamed', 'bin Karim',
        'binti Rahman', 'binti Abdullah', 'binti Hassan', 'binti Ahmad',
        'binti Sulaiman', 'binti Mohamed', 'binti Mahmud', 'binti Ali'
    ];

    private $nonMalayNames = [
        'Lee Zhi Hao', 'Chen Li Ying', 'Tan Wei Ming', 'Lim Shu Fen',
        'Wong Kar Wai', 'Ng Mei Lin', 'Kiran Patel', 'Priya Sharma',
        'Prakash Raman', 'Deepa Kumar', 'Raj Singh', 'Meera Nair'
    ];

    public function run()
    {
        DB::beginTransaction();
        
        try {
            Log::info('Starting Enhanced User Distribution Seeding...');
            
            // First, reduce Gombak users
            $this->reduceGombakUsers();
            
            // Add users to Kuantan and Pagoh
            $this->addUsersToKuantanAndPagoh();
            
            DB::commit();
            
            Log::info('Enhanced User Distribution Seeding completed successfully!');
            
            $this->command->info("✅ Successfully enhanced user distribution:");
            $this->command->info("   📍 Reduced Gombak users to manageable numbers");
            $this->command->info("   📍 Added 10 users each to Kuantan and Pagoh");
            $this->command->info("   👑 Proper role hierarchy maintained");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during user distribution seeding: ' . $e->getMessage());
            throw $e;
        }
    }

    private function reduceGombakUsers()
    {
        Log::info('Reducing Gombak users to manageable numbers...');
        
        // Keep only essential users in Gombak (centre_id = '01')
        // Keep 1 admin, 1 supervisor, 3 teachers, 1 ajk = 6 total users
        
        $gombakUsers = User::where('centre_id', '01')->get();
        $usersToKeep = collect();
        
        // Keep 1 admin (the existing main admin)
        $admin = $gombakUsers->where('role', 'admin')->first();
        if ($admin) {
            $usersToKeep->push($admin);
        }
        
        // Keep 1 supervisor
        $supervisor = $gombakUsers->where('role', 'supervisor')->first();
        if ($supervisor) {
            $usersToKeep->push($supervisor);
        }
        
        // Keep 3 teachers
        $teachers = $gombakUsers->where('role', 'teacher')->take(3);
        $usersToKeep = $usersToKeep->merge($teachers);
        
        // Keep 1 ajk
        $ajk = $gombakUsers->where('role', 'ajk')->first();
        if ($ajk) {
            $usersToKeep->push($ajk);
        }
        
        // Delete the rest
        $userIdsToKeep = $usersToKeep->pluck('id')->toArray();
        $usersToDelete = $gombakUsers->whereNotIn('id', $userIdsToKeep);
        
        foreach ($usersToDelete as $user) {
            // Clean up related data first
            DB::table('activities')->where('created_by', $user->id)->delete();
            DB::table('activities')->where('instructor_id', $user->id)->delete();
            DB::table('activity_sessions')->where('teacher_id', $user->id)->delete();
            DB::table('activity_sessions')->where('instructor_id', $user->id)->delete();
            
            $user->delete();
        }
        
        Log::info("Reduced Gombak users from {$gombakUsers->count()} to {$usersToKeep->count()}");
    }

    private function addUsersToKuantanAndPagoh()
    {
        $centres = [
            ['centre_id' => '02', 'name' => 'Kuantan'],
            ['centre_id' => '03', 'name' => 'Pagoh']
        ];

        foreach ($centres as $centre) {
            Log::info("Adding users to {$centre['name']} centre...");
            
            $existingUsers = User::where('centre_id', $centre['centre_id'])->count();
            $usersToAdd = max(0, 10 - $existingUsers); // Add up to 10 total users per centre
            
            if ($usersToAdd <= 0) {
                Log::info("{$centre['name']} already has sufficient users");
                continue;
            }
            
            // Calculate how many of each role to add
            $rolesToAdd = $this->calculateRolesToAdd($centre['centre_id'], $usersToAdd);
            
            foreach ($rolesToAdd as $role => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $this->createUser($role, $centre['centre_id'], $centre['name']);
                }
            }
            
            $newTotal = User::where('centre_id', $centre['centre_id'])->count();
            Log::info("Added {$usersToAdd} users to {$centre['name']}. Total users: {$newTotal}");
        }
    }

    private function calculateRolesToAdd($centreId, $maxUsersToAdd)
    {
        $existingRoleCounts = [];
        foreach (array_keys($this->roleHierarchy) as $role) {
            $existingRoleCounts[$role] = User::where('centre_id', $centreId)
                ->where('role', $role)
                ->count();
        }

        $rolesToAdd = [];
        $usersAdded = 0;

        // Add roles in hierarchy order (admin first, then supervisor, etc.)
        foreach ($this->roleHierarchy as $role => $targetCount) {
            $needed = max(0, $targetCount - $existingRoleCounts[$role]);
            $canAdd = min($needed, $maxUsersToAdd - $usersAdded);
            
            if ($canAdd > 0) {
                $rolesToAdd[$role] = $canAdd;
                $usersAdded += $canAdd;
            }
            
            if ($usersAdded >= $maxUsersToAdd) {
                break;
            }
        }

        // If we still have capacity, add more teachers and ajk
        if ($usersAdded < $maxUsersToAdd) {
            $remaining = $maxUsersToAdd - $usersAdded;
            
            // Distribute remaining slots: 70% teachers, 30% ajk
            $additionalTeachers = intval($remaining * 0.7);
            $additionalAjk = $remaining - $additionalTeachers;
            
            $rolesToAdd['teacher'] = ($rolesToAdd['teacher'] ?? 0) + $additionalTeachers;
            $rolesToAdd['ajk'] = ($rolesToAdd['ajk'] ?? 0) + $additionalAjk;
        }

        return $rolesToAdd;
    }

    private function createUser($role, $centreId, $centreName)
    {
        $name = $this->generateName();
        $email = $this->generateEmail($name, $centreId);
        
        // Ensure unique email
        $counter = 1;
        $originalEmail = $email;
        while (User::where('email', $email)->exists()) {
            $email = str_replace('@', $counter . '@', $originalEmail);
            $counter++;
        }

        $user = User::create([
            'iium_id' => $this->generateIiumId($centreId),
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'centre_id' => $centreId,
            'status' => 'active',
            'phone' => $this->generatePhone(),
            'position' => $this->getPositionByRole($role),
            'user_last_accessed_at' => $this->generateRandomDate(),
            'created_at' => $this->generateRandomCreationDate(),
            'updated_at' => now()
        ]);

        Log::info("Created {$role} user: {$name} ({$email}) in {$centreName}");
        return $user;
    }

    private function generateName()
    {
        $useNonMalay = rand(1, 10) <= 2; // 20% chance for non-Malay names
        
        if ($useNonMalay) {
            return $this->nonMalayNames[array_rand($this->nonMalayNames)];
        }
        
        $isMale = rand(0, 1);
        
        if ($isMale) {
            $firstName = $this->maleNames[array_rand($this->maleNames)];
            $lastName = collect($this->lastNames)->filter(function($name) {
                return str_contains($name, 'bin');
            })->random();
        } else {
            $firstName = $this->femaleNames[array_rand($this->femaleNames)];
            $lastName = collect($this->lastNames)->filter(function($name) {
                return str_contains($name, 'binti');
            })->random();
        }
        
        return $firstName . ' ' . $lastName;
    }

    private function generateEmail($name, $centreId)
    {
        // Clean name for email
        $cleanName = strtolower(str_replace(['bin ', 'binti ', ' '], '.', $name));
        $cleanName = preg_replace('/[^a-z.]/', '', $cleanName);
        
        // Add centre suffix
        $centreSuffix = [
            '01' => 'gombak',
            '02' => 'kuantan', 
            '03' => 'pagoh'
        ][$centreId] ?? 'creams';
        
        return $cleanName . '.' . $centreSuffix . '@iium.edu.my';
    }

    private function generatePhone()
    {
        return '+60' . rand(10, 19) . '-' . rand(100, 999) . '-' . rand(1000, 9999);
    }

    private function getPositionByRole($role)
    {
        $positions = [
            'admin' => ['Centre Administrator', 'Administrative Manager', 'Centre Director'],
            'supervisor' => ['Programme Supervisor', 'Academic Supervisor', 'Clinical Supervisor'],
            'teacher' => ['Special Education Teacher', 'Rehabilitation Therapist', 'Learning Support Teacher', 'Activity Coordinator'],
            'ajk' => ['Administrative Assistant', 'Programme Assistant', 'Support Coordinator']
        ];
        
        return $positions[$role][array_rand($positions[$role])];
    }

    private function generateRandomDate()
    {
        return now()->subDays(rand(1, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
    }

    private function generateRandomCreationDate()
    {
        return now()->subDays(rand(30, 365))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
    }

    private function generateIiumId($centreId)
    {
        // Generate unique IIUM ID with centre prefix
        $centrePrefix = [
            '01' => 'GB',  // Gombak
            '02' => 'KT',  // Kuantan  
            '03' => 'PG'   // Pagoh
        ][$centreId] ?? 'XX';
        
        do {
            $iiumId = $centrePrefix . date('y') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('iium_id', $iiumId)->exists());
        
        return $iiumId;
    }
}