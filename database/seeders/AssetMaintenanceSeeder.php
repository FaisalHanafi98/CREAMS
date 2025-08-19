<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AssetMaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔧 Seeding asset maintenance records...');

        $faker = Faker::create();
        $assets = DB::table('assets')->get();
        $totalMaintenanceRecords = 0;
        
        foreach ($assets as $asset) {
            // Create 1-2 maintenance records per asset
            $maintenanceCount = $faker->numberBetween(1, 2);
            
            for ($i = 0; $i < $maintenanceCount; $i++) {
                $scheduledDate = $faker->dateTimeBetween('-6 months', '+3 months');
                $isCompleted = $scheduledDate < now();
                
                // Create maintenance record
                $maintenanceId = DB::table('asset_maintenance')->insertGetId([
                    'asset_id' => $asset->id,
                    'maintenance_type' => $faker->randomElement(['routine', 'repair', 'calibration', 'inspection']),
                    'scheduled_date' => $scheduledDate->format('Y-m-d'),
                    'completed_date' => $isCompleted ? $scheduledDate->format('Y-m-d') : null,
                    'status' => $isCompleted ? 'completed' : 'scheduled',
                    'description' => 'Regular maintenance and inspection',
                    'cost' => $isCompleted ? $faker->randomFloat(2, 50, 300) : null,
                    'performed_by' => $isCompleted ? 'Maintenance Team' : null,
                    'notes' => $isCompleted ? 'Maintenance completed successfully' : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Create history record if completed
                if ($isCompleted) {
                    DB::table('asset_maintenance_history')->insert([
                        'asset_id' => $asset->id,
                        'maintenance_id' => $maintenanceId,
                        'maintenance_date' => $scheduledDate->format('Y-m-d'),
                        'maintenance_type' => 'routine',
                        'description' => 'Completed routine maintenance',
                        'cost' => $faker->randomFloat(2, 50, 300),
                        'performed_by' => 'Maintenance Team',
                        'notes' => 'All systems functioning properly',
                        'created_at' => $scheduledDate,
                        'updated_at' => $scheduledDate
                    ]);
                }
                
                $totalMaintenanceRecords++;
            }
            
            // Create movement record
            DB::table('asset_movements')->insert([
                'asset_id' => $asset->id,
                'from_location_id' => null,
                'to_location_id' => $asset->current_location_id,
                'moved_by_user_id' => DB::table('users')->where('role', 'admin')->first()->id,
                'movement_date' => $faker->dateTimeBetween('-1 year', 'now'),
                'reason' => 'Initial placement',
                'notes' => 'Asset assigned to location upon receipt',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info("🔧 Successfully seeded {$totalMaintenanceRecords} maintenance records");
    }
}