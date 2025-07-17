<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SEEDING ASSET TYPES ===" . PHP_EOL;

try {
    // Create AssetType model if it doesn't exist or use direct DB
    $assetTypes = [
        [
            'type_name' => 'Equipment',
            'type_description' => 'Medical and therapy equipment',
            'type_icon' => 'fas fa-tools',
            'type_color' => '#28a745',
            'is_active' => true
        ],
        [
            'type_name' => 'Furniture',
            'type_description' => 'Office and therapy furniture',
            'type_icon' => 'fas fa-couch',
            'type_color' => '#6f42c1',
            'is_active' => true
        ],
        [
            'type_name' => 'Computer',
            'type_description' => 'Computers and IT equipment',
            'type_icon' => 'fas fa-laptop',
            'type_color' => '#007bff',
            'is_active' => true
        ],
        [
            'type_name' => 'Vehicle',
            'type_description' => 'Transport vehicles',
            'type_icon' => 'fas fa-car',
            'type_color' => '#dc3545',
            'is_active' => true
        ],
        [
            'type_name' => 'Educational',
            'type_description' => 'Educational materials and tools',
            'type_icon' => 'fas fa-book',
            'type_color' => '#fd7e14',
            'is_active' => true
        ]
    ];

    foreach ($assetTypes as $type) {
        DB::table('asset_types')->updateOrInsert(
            ['type_name' => $type['type_name']],
            array_merge($type, [
                'created_at' => now(),
                'updated_at' => now()
            ])
        );
        echo "✅ Asset type '{$type['type_name']}' created/updated" . PHP_EOL;
    }

    echo PHP_EOL . "Asset types seeded successfully!" . PHP_EOL;

} catch (Exception $e) {
    echo "❌ Error seeding asset types: " . $e->getMessage() . PHP_EOL;
}

echo "=== END SEEDING ASSET TYPES ===" . PHP_EOL;