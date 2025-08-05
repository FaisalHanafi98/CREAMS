<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSAssetTypesSeeder extends Seeder
{
    /**
     * Run the database seeds for asset types
     */
    public function run(): void
    {
        $this->command->info('🏷️ Creating essential asset types...');

        try {
            DB::beginTransaction();

            $assetTypes = [
                [
                    'type_name' => 'Medical Equipment',
                    'type_description' => 'Medical devices and equipment for rehabilitation therapy',
                    'type_icon' => 'fas fa-heartbeat',
                    'type_color' => '#e74c3c',
                    'type_attributes' => json_encode([
                        'requires_calibration' => true,
                        'maintenance_frequency' => 'monthly',
                        'certification_required' => true
                    ]),
                    'is_active' => true,
                ],
                [
                    'type_name' => 'Therapeutic Equipment',
                    'type_description' => 'Physical therapy and occupational therapy equipment',
                    'type_icon' => 'fas fa-dumbbell',
                    'type_color' => '#3498db',
                    'type_attributes' => json_encode([
                        'weight_capacity' => true,
                        'safety_inspection' => 'quarterly',
                        'user_training_required' => true
                    ]),
                    'is_active' => true,
                ],
                [
                    'type_name' => 'Educational Technology',
                    'type_description' => 'Computers, tablets, and educational software for learning',
                    'type_icon' => 'fas fa-laptop',
                    'type_color' => '#9b59b6',
                    'type_attributes' => json_encode([
                        'software_updates' => 'automatic',
                        'security_scanning' => 'weekly',
                        'backup_required' => true
                    ]),
                    'is_active' => true,
                ],
                [
                    'type_name' => 'Communication Aids',
                    'type_description' => 'Assistive technology for communication and speech therapy',
                    'type_icon' => 'fas fa-comments',
                    'type_color' => '#f39c12',
                    'type_attributes' => json_encode([
                        'battery_life' => '8_hours',
                        'waterproof' => false,
                        'customization_options' => true
                    ]),
                    'is_active' => true,
                ],
                [
                    'type_name' => 'Mobility Aids',
                    'type_description' => 'Wheelchairs, walkers, and mobility support equipment',
                    'type_icon' => 'fas fa-wheelchair',
                    'type_color' => '#27ae60',
                    'type_attributes' => json_encode([
                        'weight_limit' => true,
                        'adjustable' => true,
                        'safety_check_frequency' => 'monthly'
                    ]),
                    'is_active' => true,
                ],
                [
                    'type_name' => 'Sensory Equipment',
                    'type_description' => 'Equipment for sensory integration and stimulation therapy',
                    'type_icon' => 'fas fa-eye',
                    'type_color' => '#e67e22',
                    'type_attributes' => json_encode([
                        'sensory_type' => ['visual', 'auditory', 'tactile'],
                        'intensity_adjustable' => true,
                        'age_appropriate' => true
                    ]),
                    'is_active' => true,
                ],
            ];

            foreach ($assetTypes as $assetType) {
                DB::table('asset_types')->insert(array_merge($assetType, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]));
            }

            DB::commit();

            $this->command->info('✅ Asset types seeded successfully!');
            $this->showAssetTypesStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to seed asset types: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Show asset types statistics
     */
    private function showAssetTypesStatistics(): void
    {
        $count = DB::table('asset_types')->count();
        $activeCount = DB::table('asset_types')->where('is_active', true)->count();
        
        $this->command->info("\n📊 ASSET TYPES STATISTICS:");
        $this->command->line("   🏷️ Total Asset Types: {$count}");
        $this->command->line("   ✅ Active Types: {$activeCount}");
        $this->command->line("   🎨 Categories: Medical, Therapeutic, Educational, Communication, Mobility, Sensory");
    }
}