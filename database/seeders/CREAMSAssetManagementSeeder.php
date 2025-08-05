<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Centre;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSAssetManagementSeeder extends Seeder
{
    /**
     * Run the database seeds for comprehensive asset management
     */
    public function run(): void
    {
        $this->command->info('<� Creating comprehensive CREAMS asset management system...');

        try {
            DB::beginTransaction();

            // Get centres and users for realistic associations
            $centres = DB::table('centres')->get();
            $users = DB::table('users')->get();
            $categories = DB::table('asset_categories')->get();

            if ($centres->isEmpty() || $users->isEmpty() || $categories->isEmpty()) {
                $this->command->error('L Missing required data: centres, users, or asset_categories');
                return;
            }

            // Create realistic assets
            $this->createAssets($centres, $users, $categories);
            
            // Create maintenance records
            $this->createMaintenanceRecords();
            
            // Create asset movements
            $this->createAssetMovements($users);

            DB::commit();

            $this->command->info(' Asset management system seeded successfully!');
            $this->showAssetStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('L Failed to seed asset management: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create realistic assets for each centre
     */
    private function createAssets($centres, $users, $categories): void
    {
        $this->command->info('=� Creating realistic assets for all centres...');

        $assetsData = [
            // Medical Equipment
            'MED' => [
                ['name' => 'Digital Blood Pressure Monitor', 'brand' => 'Omron', 'model' => 'HEM-7121', 'price' => 280.00],
                ['name' => 'Pulse Oximeter', 'brand' => 'Philips', 'model' => 'PO-300', 'price' => 150.00],
                ['name' => 'Digital Thermometer', 'brand' => 'Braun', 'model' => 'ThermoScan', 'price' => 65.00],
                ['name' => 'Stethoscope', 'brand' => 'Littmann', 'model' => 'Classic III', 'price' => 195.00],
                ['name' => 'Wheelchair', 'brand' => 'Drive Medical', 'model' => 'Blue Streak', 'price' => 180.00],
            ],
            
            // Rehabilitation Equipment
            'REH' => [
                ['name' => 'Exercise Mat', 'brand' => 'TheraBand', 'model' => 'Pro Mat', 'price' => 45.00],
                ['name' => 'Therapy Ball', 'brand' => 'Gymnic', 'model' => '65cm', 'price' => 35.00],
                ['name' => 'Resistance Bands Set', 'brand' => 'TheraBand', 'model' => 'Progressive', 'price' => 25.00],
                ['name' => 'Standing Frame', 'brand' => 'Rifton', 'model' => 'Pacer', 'price' => 2500.00],
                ['name' => 'Parallel Bars', 'brand' => 'Clinton', 'model' => 'Folding', 'price' => 850.00],
            ],

            // Educational Technology
            'EDU' => [
                ['name' => 'Interactive Whiteboard', 'brand' => 'SMART', 'model' => 'Board MX275', 'price' => 1800.00],
                ['name' => 'Tablet for Learning', 'brand' => 'Samsung', 'model' => 'Galaxy Tab A', 'price' => 350.00],
                ['name' => 'Educational Software License', 'brand' => 'Proloquo2Go', 'model' => 'Communication', 'price' => 250.00],
                ['name' => 'Learning Game Console', 'brand' => 'LeapFrog', 'model' => 'LeapTV', 'price' => 125.00],
            ],

            // Computer Equipment
            'COM' => [
                ['name' => 'Desktop Computer', 'brand' => 'Dell', 'model' => 'OptiPlex 7090', 'price' => 1200.00],
                ['name' => 'Laptop', 'brand' => 'Lenovo', 'model' => 'ThinkPad E15', 'price' => 950.00],
                ['name' => 'Printer', 'brand' => 'HP', 'model' => 'LaserJet Pro M404n', 'price' => 220.00],
                ['name' => 'Network Router', 'brand' => 'TP-Link', 'model' => 'Archer AX73', 'price' => 180.00],
            ],

            // Furniture
            'FUR' => [
                ['name' => 'Adjustable Desk', 'brand' => 'IKEA', 'model' => 'Bekant', 'price' => 120.00],
                ['name' => 'Ergonomic Chair', 'brand' => 'Herman Miller', 'model' => 'Sayl', 'price' => 295.00],
                ['name' => 'Filing Cabinet', 'brand' => 'Steelcase', 'model' => '4-Drawer', 'price' => 180.00],
                ['name' => 'Therapy Table', 'brand' => 'Clinton', 'model' => 'Alpha Series', 'price' => 450.00],
            ],

            // Audio Visual
            'AV' => [
                ['name' => 'LED Projector', 'brand' => 'Epson', 'model' => 'PowerLite 2265U', 'price' => 850.00],
                ['name' => 'Sound System', 'brand' => 'Bose', 'model' => 'SoundTouch 30', 'price' => 500.00],
                ['name' => 'Video Camera', 'brand' => 'Canon', 'model' => 'VIXIA HF R800', 'price' => 280.00],
                ['name' => 'Microphone Set', 'brand' => 'Shure', 'model' => 'SM58', 'price' => 150.00],
            ],

            // Safety Equipment
            'SAF' => [
                ['name' => 'Fire Extinguisher', 'brand' => 'Amerex', 'model' => 'B402', 'price' => 45.00],
                ['name' => 'First Aid Kit', 'brand' => 'Johnson & Johnson', 'model' => 'All Purpose', 'price' => 35.00],
                ['name' => 'Security Camera', 'brand' => 'Hikvision', 'model' => 'DS-2CD2043G0-I', 'price' => 120.00],
                ['name' => 'Motion Sensor Light', 'brand' => 'Philips', 'model' => 'Hue Motion', 'price' => 80.00],
            ]
        ];

        $assetId = 1;
        foreach ($centres as $centre) {
            $this->command->info("   <� Creating assets for {$centre->centre_name}...");
            
            foreach ($categories as $category) {
                if (isset($assetsData[$category->code])) {
                    $categoryAssets = $assetsData[$category->code];
                    
                    // Create 2-4 assets per category per centre
                    $assetCount = rand(2, min(4, count($categoryAssets)));
                    $selectedAssets = collect($categoryAssets)->random($assetCount);
                    
                    foreach ($selectedAssets as $assetData) {
                        $purchaseDate = Carbon::now()->subDays(rand(30, 1095)); // 1 month to 3 years ago
                        $warrantyExpiry = $purchaseDate->copy()->addMonths(rand(12, 36));
                        $currentValue = $this->calculateCurrentValue($assetData['price'], $purchaseDate, $category->depreciation_rate);
                        
                        DB::table('assets')->insert([
                            'id' => $assetId,
                            'asset_code' => $this->generateAssetCode($category->code, $centre->centre_id, $assetId),
                            'name' => $assetData['name'],
                            'description' => "Professional grade {$assetData['name']} for rehabilitation centre use",
                            'category_id' => $category->id,
                            'centre_id' => $centre->centre_id,
                            'brand' => $assetData['brand'],
                            'model' => $assetData['model'],
                            'serial_number' => $this->generateSerialNumber($assetData['brand']),
                            'purchase_price' => $assetData['price'],
                            'purchase_date' => $purchaseDate,
                            'warranty_months' => rand(12, 36),
                            'warranty_expiry' => $warrantyExpiry,
                            'condition' => $this->getRandomCondition($purchaseDate),
                            'status' => $this->getRandomStatus(),
                            'location' => $this->getRandomLocation($centre->centre_name),
                            'assigned_to' => rand(1, 10) <= 7 ? $users->random()->id : null, // 70% assigned
                            'assigned_date' => rand(1, 10) <= 7 ? $purchaseDate->copy()->addDays(rand(1, 30)) : null,
                            'depreciation_rate' => $category->depreciation_rate,
                            'current_value' => $currentValue,
                            'specifications' => json_encode($this->generateSpecifications($assetData)),
                            'images' => json_encode([]),
                            'notes' => $this->generateNotes($assetData['name']),
                            'created_by' => $users->where('role', 'admin')->first()->id ?? 1,
                            'created_at' => $purchaseDate,
                            'updated_at' => $purchaseDate,
                        ]);
                        
                        $assetId++;
                    }
                }
            }
        }
    }

    /**
     * Create realistic maintenance records
     */
    private function createMaintenanceRecords(): void
    {
        $this->command->info('🔧 Creating maintenance records...');
        
        $assets = DB::table('assets')->get();
        $users = DB::table('users')->get();
        
        foreach ($assets as $asset) {
            // Create 0-3 maintenance records per asset
            $maintenanceCount = rand(0, 3);
            
            for ($i = 0; $i < $maintenanceCount; $i++) {
                $scheduledDate = Carbon::parse($asset->purchase_date)->addMonths(rand(3, 18));
                $completedDate = rand(1, 10) <= 8 ? $scheduledDate->copy()->addDays(rand(0, 7)) : null;
                $status = $completedDate ? 'completed' : ['scheduled', 'in_progress'][rand(0, 1)];
                
                DB::table('asset_maintenance')->insert([
                    'asset_id' => $asset->id,
                    'type' => ['preventive', 'corrective', 'inspection'][rand(0, 2)],
                    'scheduled_date' => $scheduledDate,
                    'completed_date' => $completedDate,
                    'status' => $status,
                    'performed_by' => $this->getMaintenancePerformer(),
                    'cost' => rand(25, 200),
                    'description' => $this->getMaintenanceDescription(),
                    'notes' => $this->getMaintenanceNotes(),
                    'created_by' => $users->random()->id,
                    'created_at' => $scheduledDate->copy()->subDays(rand(1, 14)),
                    'updated_at' => $completedDate ?? $scheduledDate->copy()->subDays(rand(1, 14)),
                ]);
            }
        }
    }

    /**
     * Create asset movement history
     */
    private function createAssetMovements($users): void
    {
        $this->command->info('=� Creating asset movement history...');
        
        $assets = DB::table('assets')->get();
        
        foreach ($assets as $asset) {
            // Create 1-3 movement records per asset
            $movementCount = rand(1, 3);
            
            for ($i = 0; $i < $movementCount; $i++) {
                $movementDate = Carbon::parse($asset->purchase_date)->addDays(rand(1, 200));
                $type = ['assignment', 'return', 'transfer', 'maintenance'][rand(0, 3)];
                
                DB::table('asset_movements')->insert([
                    'asset_id' => $asset->id,
                    'type' => $type,
                    'from_user' => $i > 0 ? $users->random()->id : null,
                    'to_user' => $asset->assigned_to,
                    'from_location' => 'Storage Room',
                    'to_location' => $asset->location,
                    'reason' => $this->getMovementReason($type),
                    'performed_by' => $users->where('role', 'admin')->first()->id ?? 1,
                    'movement_date' => $movementDate,
                    'created_at' => $movementDate,
                    'updated_at' => $movementDate,
                ]);
            }
        }
    }

    /**
     * Generate unique asset code
     */
    private function generateAssetCode($categoryCode, $centreId, $assetId): string
    {
        $year = date('y');
        return sprintf("%s-%02d-%s-%04d", $categoryCode, $centreId, $year, $assetId);
    }

    /**
     * Generate realistic serial number
     */
    private function generateSerialNumber($brand): string
    {
        $prefix = strtoupper(substr($brand, 0, 2));
        $numbers = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . $numbers;
    }

    /**
     * Calculate current value with depreciation
     */
    private function calculateCurrentValue($purchasePrice, $purchaseDate, $depreciationRate): float
    {
        $monthsOld = Carbon::parse($purchaseDate)->diffInMonths(Carbon::now());
        $yearlyDepreciation = $depreciationRate / 100;
        $monthlyDepreciation = $yearlyDepreciation / 12;
        
        $depreciatedValue = $purchasePrice * (1 - ($monthlyDepreciation * $monthsOld));
        return max($depreciatedValue, $purchasePrice * 0.1); // Minimum 10% of original value
    }

    /**
     * Get random condition based on age
     */
    private function getRandomCondition($purchaseDate): string
    {
        $monthsOld = Carbon::parse($purchaseDate)->diffInMonths(Carbon::now());
        
        if ($monthsOld < 6) return 'new';
        if ($monthsOld < 18) return ['new', 'good'][rand(0, 1)];
        if ($monthsOld < 36) return ['good', 'fair'][rand(0, 1)];
        return ['fair', 'poor'][rand(0, 1)];
    }

    /**
     * Get random status
     */
    private function getRandomStatus(): string
    {
        $statuses = ['available', 'in_use', 'maintenance'];
        $weights = [30, 60, 10]; // 30% available, 60% in use, 10% maintenance
        
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($rand <= $cumulative) {
                return $status;
            }
        }
        
        return 'available';
    }

    /**
     * Get random location within centre
     */
    private function getRandomLocation($centreName): string
    {
        $locations = [
            'Therapy Room 1', 'Therapy Room 2', 'Computer Lab', 'Activity Hall',
            'Office', 'Storage Room', 'Reception', 'Meeting Room', 'Training Room'
        ];
        
        return $locations[rand(0, count($locations) - 1)];
    }

    /**
     * Generate specifications
     */
    private function generateSpecifications($assetData): array
    {
        return [
            'brand' => $assetData['brand'],
            'model' => $assetData['model'],
            'color' => ['Black', 'White', 'Silver', 'Blue'][rand(0, 3)],
            'weight' => rand(1, 50) . ' kg',
            'dimensions' => rand(20, 100) . 'x' . rand(20, 100) . 'x' . rand(10, 50) . ' cm'
        ];
    }

    /**
     * Generate asset notes
     */
    private function generateNotes($assetName): string
    {
        $notes = [
            "High-quality {$assetName} suitable for rehabilitation use",
            "Regular maintenance required every 6 months",
            "Approved for use with special needs individuals",
            "Staff training completed for proper usage",
            "Important safety guidelines attached"
        ];
        
        return $notes[rand(0, count($notes) - 1)];
    }

    /**
     * Get maintenance description
     */
    private function getMaintenanceDescription(): string
    {
        $descriptions = [
            'Regular preventive maintenance check',
            'Cleaning and calibration service',
            'Software update and system check',
            'Battery replacement and testing',
            'Safety inspection and compliance check',
            'Performance optimization and tuning'
        ];
        
        return $descriptions[rand(0, count($descriptions) - 1)];
    }

    /**
     * Get maintenance performer
     */
    private function getMaintenancePerformer(): string
    {
        $performers = [
            'TechCare Solutions Sdn Bhd',
            'MedEquip Services Malaysia',
            'Internal IT Department',
            'Authorized Service Center',
            'Equipment Specialist'
        ];
        
        return $performers[rand(0, count($performers) - 1)];
    }

    /**
     * Get maintenance notes
     */
    private function getMaintenanceNotes(): string
    {
        $notes = [
            'Maintenance completed successfully, equipment functioning normally',
            'Minor adjustments made, performance improved',
            'All safety checks passed, ready for use',
            'Preventive measures applied, next service due in 6 months',
            'Issue resolved, equipment restored to full functionality'
        ];
        
        return $notes[rand(0, count($notes) - 1)];
    }

    /**
     * Get movement reason
     */
    private function getMovementReason($type): string
    {
        $reasons = [
            'assignment' => 'Equipment assigned to staff member',
            'return' => 'Equipment returned after use',
            'transfer' => 'Moved to different location as needed',
            'maintenance' => 'Sent for scheduled maintenance'
        ];
        
        return $reasons[$type] ?? 'General asset movement';
    }

    /**
     * Show asset statistics
     */
    private function showAssetStatistics(): void
    {
        $this->command->info("\n=� ASSET MANAGEMENT STATISTICS:");
        
        $totalAssets = DB::table('assets')->count();
        $totalValue = DB::table('assets')->sum('current_value');
        $assetsByStatus = DB::table('assets')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
            
        $maintenanceRecords = DB::table('asset_maintenance')->count();
        $movementRecords = DB::table('asset_movements')->count();
        
        $this->command->line("   =� Total Assets: {$totalAssets}");
        $this->command->line("   =� Total Current Value: RM " . number_format($totalValue, 2));
        
        foreach ($assetsByStatus as $status) {
            $this->command->line("   =� {$status->status}: {$status->count} assets");
        }
        
        $this->command->line("   =' Maintenance Records: {$maintenanceRecords}");
        $this->command->line("   =� Movement Records: {$movementRecords}");
        
        $this->command->info("    Complete asset lifecycle tracking implemented!");
    }
}