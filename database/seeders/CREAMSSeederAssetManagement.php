<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CREAMSSeederAssetManagement extends Seeder
{
    /**
     * CREAMS Asset Management Seeder
     * Seeds: Asset categories, types, locations, assets, maintenance
     */
    public function run(): void
    {
        $this->command->info('🔧 Seeding CREAMS Asset Management...');
        
        // Create asset categories first
        $this->command->info('   🏷️ Creating asset categories...');
        $this->seedAssetCategories();
        
        // Create asset types
        $this->command->info('   📂 Creating asset types...');
        $this->seedAssetTypes();
        
        // Create asset locations
        $this->command->info('   📍 Creating asset locations...');
        $this->seedAssetLocations();
        
        // Create assets (including real Gombak assets)
        $this->command->info('   🏭 Creating assets...');
        $this->seedAssets();
        
        // Create asset maintenance records
        $this->command->info('   🔧 Creating asset maintenance records...');
        $this->seedAssetMaintenance();
        
        $this->command->info('✅ Asset Management seeding completed');
    }

    private function seedAssetCategories(): void
    {
        $categories = [
            ['category_name' => 'Medical Equipment', 'category_description' => 'Therapeutic and medical devices'],
            ['category_name' => 'Educational Materials', 'category_description' => 'Learning and educational resources'],
            ['category_name' => 'Mobility Aids', 'category_description' => 'Wheelchairs, walkers, and mobility devices'],
            ['category_name' => 'Furniture', 'category_description' => 'Specialized furniture and seating'],
            ['category_name' => 'Sports Equipment', 'category_description' => 'Physical therapy and recreational equipment'],
            ['category_name' => 'Musical Instruments', 'category_description' => 'Music therapy and recreational instruments'],
            ['category_name' => 'Storage Solutions', 'category_description' => 'Storage cabinets and organizational equipment'],
            ['category_name' => 'Technology', 'category_description' => 'Educational technology and assistive devices']
        ];
        
        foreach ($categories as $category) {
            DB::table('asset_categories')->insertOrIgnore([
                'category_name' => $category['category_name'],
                'category_description' => $category['category_description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->line('      ✓ Created ' . count($categories) . ' asset categories');
    }
    
    private function seedAssetTypes(): void
    {
        $categories = DB::table('asset_categories')->get();
        $totalTypes = 0;
        
        $typesByCategory = [
            'Medical Equipment' => ['Physiotherapy Ball', 'Therapy Mat', 'Exercise Equipment', 'Sensory Tools'],
            'Educational Materials' => ['Learning Books', 'Educational Games', 'Teaching Aids', 'Flashcards'],
            'Mobility Aids' => ['Wheelchair', 'Walker', 'Mobility Scooter', 'Support Rails'],
            'Furniture' => ['Therapy Chair', 'Adjustable Table', 'Storage Bench', 'Classroom Desk'],
            'Sports Equipment' => ['Gym Ball', 'Exercise Mat', 'Fitness Equipment', 'Recreational Games'],
            'Musical Instruments' => ['Piano', 'Drum', 'Bell Set', 'Tambourine'],
            'Storage Solutions' => ['Filing Cabinet', 'Storage Shelf', 'Mobile Pedestal', 'Book Rack'],
            'Technology' => ['Computer', 'Tablet', 'Interactive Board', 'Assistive Device']
        ];
        
        foreach ($categories as $category) {
            if (isset($typesByCategory[$category->category_name])) {
                foreach ($typesByCategory[$category->category_name] as $typeName) {
                    DB::table('asset_types')->insertOrIgnore([
                        'name' => $typeName,
                        'type_description' => 'Equipment for rehabilitation and therapy programs',
                        'category_id' => $category->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $totalTypes++;
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalTypes} asset types");
    }
    
    private function seedAssetLocations(): void
    {
        $centres = DB::table('centres')->get();
        $totalLocations = 0;
        
        $locationTypes = ['Therapy Room', 'Classroom', 'Storage Room', 'Office', 'Common Area', 'Music Room'];
        
        foreach ($centres as $centre) {
            foreach ($locationTypes as $locationType) {
                for ($i = 1; $i <= rand(2, 3); $i++) {
                    DB::table('asset_locations')->insertOrIgnore([
                        'location_name' => $locationType . ' ' . $i,
                        'location_description' => $locationType . ' in ' . $centre->centre_name,
                        'centre_id' => $centre->centre_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $totalLocations++;
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalLocations} asset locations across centres");
    }
    
    private function seedAssets(): void
    {
        // First, seed real Gombak assets from IRLSeeder data
        $this->seedRealGombakAssets();
        
        // Then create additional assets for other centres
        $types = DB::table('asset_types')->get();
        $locations = DB::table('asset_locations')->get();
        $centres = DB::table('centres')->where('centre_id', '!=', '01')->get(); // Exclude Gombak
        $totalAssets = 0;
        
        foreach ($centres as $centre) {
            $centreLocations = $locations->where('centre_id', $centre->centre_id);
            $assetsPerCentre = rand(25, 40); // 25-40 assets per non-Gombak centre
            
            for ($i = 1; $i <= $assetsPerCentre; $i++) {
                $type = $types->random();
                $location = $centreLocations->random();
                $purchaseDate = now()->subDays(rand(30, 1095)); // 1 month to 3 years ago
                
                $assetId = 'AST' . $centre->centre_id . sprintf('%03d', $i);
                
                DB::table('assets')->insert([
                    'asset_tag' => $assetId,
                    'asset_name' => $type->name . ' #' . $i,
                    'asset_description' => 'Rehabilitation equipment for ' . $centre->centre_name,
                    'category_id' => $type->category_id,
                    'type_id' => $type->id,
                    'centre_id' => $centre->centre_id,
                    'location_id' => $location->id,
                    'purchase_date' => $purchaseDate->format('Y-m-d'),
                    'purchase_price' => rand(100, 3000),
                    'condition' => ['Excellent', 'Good', 'Fair'][array_rand(['Excellent', 'Good', 'Fair'])],
                    'warranty_expiry' => $purchaseDate->addYears(rand(1, 3))->format('Y-m-d'),
                    'manufacturer' => ['RehabSupply Sdn Bhd', 'TherapyEquip Malaysia', 'MediCare Tools'][array_rand(['RehabSupply Sdn Bhd', 'TherapyEquip Malaysia', 'MediCare Tools'])],
                    'status' => 'available',
                    'created_at' => $purchaseDate,
                    'updated_at' => $purchaseDate
                ]);
                
                $totalAssets++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalAssets} additional assets for other centres");
    }
    
    private function seedRealGombakAssets(): void
    {
        $this->command->info('   🏗️ Integrating real Gombak assets from IRLSeeder...');
        
        // Real asset data from IRLSeeder (simplified for asset management)
        $realAssetData = [
            ['name' => 'Peanut Ball 45cm x 90cm', 'type' => 'Gym Equipment', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-02-24'],
            ['name' => 'Touch Ball 75cm', 'type' => 'Gym Equipment', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-02-24'],
            ['name' => 'Gym Roller', 'type' => 'Gym Equipment', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-02-24'],
            ['name' => 'Bench Storage (Castors & Cushion)', 'type' => 'Storage', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-02-24'],
            ['name' => '12 Level Economy Adjustable Shelf', 'type' => 'Storage', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-02-24'],
            ['name' => 'Wooden Office Table (Dark Grey + Maple)', 'type' => 'Furniture', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-02-24'],
            ['name' => 'Plastic Chair Blue', 'type' => 'Furniture', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-08-25', 'qty' => 12],
            ['name' => 'Plastic Chair Green', 'type' => 'Furniture', 'supplier' => 'PPT EDU SUPPLIES', 'date' => '2023-08-25', 'qty' => 12],
            ['name' => 'Ring Bell', 'type' => 'Musical Instrument', 'supplier' => 'USL EDUCATIONAL SUPPLIES', 'date' => '2024-12-31', 'qty' => 2],
            ['name' => 'Wooden Tambourine 6"', 'type' => 'Musical Instrument', 'supplier' => 'USL EDUCATIONAL SUPPLIES', 'date' => '2024-12-31'],
            ['name' => 'Triangle 6"', 'type' => 'Musical Instrument', 'supplier' => 'USL EDUCATIONAL SUPPLIES', 'date' => '2024-12-31', 'qty' => 2],
            ['name' => 'Rectangular Table 2\'x4\' - Wood', 'type' => 'Furniture', 'supplier' => 'USL EDUCATIONAL SUPPLIES', 'date' => '2024-12-31', 'qty' => 2],
            ['name' => 'Magnetic White Board 3\'x6\'', 'type' => 'Educational Equipment', 'supplier' => 'USL EDUCATIONAL SUPPLIES', 'date' => '2024-12-31']
        ];
        
        $gombakLocations = DB::table('asset_locations')->where('centre_id', '01')->get();
        $categories = DB::table('asset_categories')->get();
        $types = DB::table('asset_types')->get();
        $assetsCreated = 0;
        
        // Category mapping for real assets
        $categoryMapping = [
            'Gym Equipment' => $categories->where('category_name', 'Sports Equipment')->first()->id ?? 5,
            'Storage' => $categories->where('category_name', 'Storage Solutions')->first()->id ?? 7,
            'Furniture' => $categories->where('category_name', 'Furniture')->first()->id ?? 4,
            'Musical Instrument' => $categories->where('category_name', 'Musical Instruments')->first()->id ?? 6,
            'Educational Equipment' => $categories->where('category_name', 'Educational Materials')->first()->id ?? 2
        ];
        
        foreach ($realAssetData as $asset) {
            $quantity = $asset['qty'] ?? 1;
            $location = $gombakLocations->random();
            
            for ($i = 1; $i <= $quantity; $i++) {
                $assetId = 'GMK' . sprintf('%03d', $assetsCreated + 1);
                
                DB::table('assets')->insert([
                    'asset_tag' => $assetId,
                    'asset_name' => $asset['name'] . ($quantity > 1 ? " #{$i}" : ''),
                    'asset_description' => 'Real Gombak asset - ' . $asset['name'],
                    'category_id' => $categoryMapping[$asset['type']] ?? 1,
                    'type_id' => $types->where('name', 'LIKE', '%' . explode(' ', $asset['name'])[0] . '%')->first()->id ?? 1,
                    'centre_id' => '01',
                    'location_id' => $location->id,
                    'purchase_date' => $asset['date'],
                    'purchase_price' => rand(100, 2000),
                    'condition' => 'Good',
                    'warranty_expiry' => date('Y-m-d', strtotime($asset['date'] . ' +2 years')),
                    'manufacturer' => $asset['supplier'],
                    'status' => 'available',
                    'created_at' => $asset['date'],
                    'updated_at' => now()
                ]);
                $assetsCreated++;
            }
        }
        
        $this->command->line("      ✓ Integrated {$assetsCreated} real Gombak assets");
    }
    
    private function seedAssetMaintenance(): void
    {
        $assets = DB::table('assets')->get();
        $staff = DB::table('users')->get();
        $totalMaintenanceRecords = 0;
        
        foreach ($assets as $asset) {
            $maintenanceCount = rand(1, 3); // 1-3 maintenance records per asset
            
            for ($i = 0; $i < $maintenanceCount; $i++) {
                $maintenanceDate = now()->subDays(rand(30, 365));
                $staffMember = $staff->random();
                
                $maintenanceTypes = ['routine', 'repair', 'inspection'];
                $maintenanceType = $maintenanceTypes[array_rand($maintenanceTypes)];
                
                $descriptions = [
                    'routine' => 'Regular maintenance and safety check',
                    'repair' => 'Repair and component replacement',
                    'inspection' => 'Safety inspection and functionality test'
                ];
                
                DB::table('asset_maintenance')->insert([
                    'asset_id' => $asset->id,
                    'maintenance_type' => $maintenanceType,
                    'scheduled_date' => $maintenanceDate->format('Y-m-d'),
                    'completed_date' => $maintenanceDate->format('Y-m-d'),
                    'status' => 'completed',
                    'priority' => 'normal',
                    'description' => $descriptions[$maintenanceType],
                    'cost' => $maintenanceType === 'repair' ? rand(50, 300) : rand(0, 50),
                    'performed_by' => $staffMember->name,
                    'created_at' => $maintenanceDate,
                    'updated_at' => $maintenanceDate
                ]);
                
                $totalMaintenanceRecords++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalMaintenanceRecords} maintenance records");
    }
}