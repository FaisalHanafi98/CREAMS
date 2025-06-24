<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Centres;

class CREAMSUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have the two centers
        $this->createCentres();
        
        // Create users for each center
        $this->createUsersForCentre('PGOH001', 'Pagoh');
        $this->createUsersForCentre('KNTN001', 'Kuantan');
        
        $this->command->info('CREAMS users created successfully!');
    }
    
    /**
     * Create the required centers if they don't exist
     */
    private function createCentres(): void
    {
        $centres = [
            [
                'centre_id' => 'PGOH001',
                'centre_name' => 'PPDK Pagoh',
                'centre_status' => 'active',
                'status' => 'active'
            ],
            [
                'centre_id' => 'KNTN001',
                'centre_name' => 'PPDK Kuantan',
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
        
        $this->command->info('Centers created: PPDK Pagoh and PPDK Kuantan');
    }
    
    /**
     * Create users for a specific center with different roles
     * 
     * @param string $centreId The centre ID
     * @param string $centreName The centre name (Pagoh or Kuantan)
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
        $prefix = strtoupper(substr($centreName, 0, 2));
        
        $malayNames = [
            'Ahmad Razali bin Abdullah',
            'Mohd Hafiz bin Ibrahim',
        ];
        
        $name = $malayNames[array_rand($malayNames)];
        $nameParts = explode(' ', $name);
        $firstNameInitial = substr($nameParts[0], 0, 1);
        $secondNameInitial = substr($nameParts[1], 0, 1);
        
        $iiumId = $prefix . 'AD' . strtoupper($firstNameInitial . $secondNameInitial);
        
        // Add random numbers to make it 8 characters
        $iiumId = str_pad($iiumId, 8, rand(0, 9));
        
        $email = strtolower(str_replace(' ', '.', $nameParts[0] . '.' . $nameParts[1])) . '@iium.edu.my';
        
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
        $prefix = strtoupper(substr($centreName, 0, 2));
        
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
            $firstNameInitial = substr($nameParts[0], 0, 1);
            $secondNameInitial = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : 'X';
            
            $iiumId = $prefix . 'SV' . strtoupper($firstNameInitial . $secondNameInitial);
            
            // Add random numbers to make it 8 characters
            $iiumId = str_pad($iiumId, 8, rand(0, 9));
            
            $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.' . ($i + 1) . '@iium.edu.my';
            
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
        $prefix = strtoupper(substr($centreName, 0, 2));
        
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
            $firstNameInitial = substr($nameParts[0], 0, 1);
            $secondNameInitial = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : 'X';
            
            $iiumId = $prefix . 'AJ' . strtoupper($firstNameInitial . $secondNameInitial);
            
            // Add random numbers to make it 8 characters
            $iiumId = str_pad($iiumId, 8, rand(0, 9));
            
            $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.ajk' . ($i + 1) . '@iium.edu.my';
            
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
        $prefix = strtoupper(substr($centreName, 0, 2));
        
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
            $firstNameInitial = substr($nameParts[0], 0, 1);
            $secondNameInitial = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : 'X';
            
            $iiumId = $prefix . 'TC' . strtoupper($firstNameInitial . $secondNameInitial);
            
            // Add random numbers to make it 8 characters
            $iiumId = str_pad($iiumId, 8, rand(0, 9));
            
            $email = strtolower(str_replace(' ', '.', $nameParts[0])) . '.teacher' . ($i + 1) . '@iium.edu.my';
            
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
}