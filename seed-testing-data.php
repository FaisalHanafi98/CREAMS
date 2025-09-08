<?php

/**
 * CREAMS Testing Guide Data Seeder Script
 * 
 * This script seeds only the data needed for the CREAMS Form Testing Guide
 * without running the full database seeding process.
 * 
 * Usage: php seed-testing-data.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Database\Seeders\TestingGuideDataSeeder;

// Initialize Laravel application
$app = new Application(realpath(__DIR__));
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "================================================================================\n";
echo "           CREAMS TESTING GUIDE DATA SEEDER                                      \n";
echo "================================================================================\n";
echo "\n";

echo "🚀 Starting Testing Guide Data Seeding...\n\n";

try {
    // Create and run the seeder
    $seeder = new TestingGuideDataSeeder();
    $seeder->setCommand(new class {
        public function info($message) { echo "ℹ️  {$message}\n"; }
        public function line($message) { echo "   {$message}\n"; }
        public function warn($message) { echo "⚠️  {$message}\n"; }
        public function error($message) { echo "❌ {$message}\n"; }
    });
    
    $seeder->run();
    
    echo "\n";
    echo "================================================================================\n";
    echo "✅ TESTING GUIDE DATA SEEDING COMPLETED SUCCESSFULLY!\n";
    echo "================================================================================\n";
    echo "\n";
    
    echo "🔐 TEST CREDENTIALS AVAILABLE:\n";
    echo "   👑 Admin: lakshmi.krishnan@iium.edu.my / Admin@2024!\n";
    echo "   👨‍🏫 Teacher: ahmad.hassan@iium.edu.my / Teacher@2024 (IIUM ID: 1928471)\n";
    echo "   👨‍💼 Supervisor: supervisor.gombak@iium.edu.my / Supervise@2024\n";
    echo "   👥 AJK: fatimah.abdullah@iium.edu.my / AJK@2024\n";
    echo "\n";
    
    echo "📋 TEST DATA AVAILABLE:\n";
    echo "   🏢 Gombak Centre (ID: 01) - Main testing centre\n";
    echo "   👶 Existing trainee for edit testing\n";
    echo "   🤝 Existing volunteer for duplicate testing\n";
    echo "   📧 Previous contact messages for testing\n";
    echo "   🏭 Asset categories, types, and locations\n";
    echo "\n";
    
    echo "🎯 Ready for Testing!\n";
    echo "   All credentials from the CREAMS Form Testing Guide are now available.\n";
    echo "   Run the testing guide scenarios using these seeded accounts.\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n";
    echo "❌ ERROR: Testing data seeding failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}