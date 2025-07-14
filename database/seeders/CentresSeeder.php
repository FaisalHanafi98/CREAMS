<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Centres;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class CentresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding centres...');
        
        try {
            // Safe deletion of existing centres
            $this->command->info('Deleting existing centres...');
            
            // Temporarily disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Delete existing centres
            DB::table('centres')->delete();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            // Actual campus data with proper status field
            $centres = [
                [
                    'centre_id' => '01',
                    'centre_name' => 'Gombak',
                    'centre_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'centre_id' => '02',
                    'centre_name' => 'Kuantan',
                    'centre_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'centre_id' => '03',
                    'centre_name' => 'Gambang',
                    'centre_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'centre_id' => '04',
                    'centre_name' => 'Pagoh',
                    'centre_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            // Insert all centres at once
            DB::table('centres')->insert($centres);
            
            foreach ($centres as $centre) {
                $this->command->info("Created centre: {$centre['centre_name']}");
            }
            
            // Run the centre sync command to update any existing users
            $this->command->info('Running centre sync command to update user records...');
            Artisan::call('centres:sync');
            $syncOutput = Artisan::output();
            $this->command->info($syncOutput);
            
            $this->command->info('Centres seeding completed successfully!');
            
        } catch (\Exception $e) {
            // Make sure to re-enable foreign key checks even if there's an error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->command->error('Error seeding centres: ' . $e->getMessage());
            Log::error('Error seeding centres', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}