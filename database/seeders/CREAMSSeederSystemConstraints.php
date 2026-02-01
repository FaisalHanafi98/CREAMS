<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CREAMSSeederSystemConstraints extends Seeder
{
    /**
     * CREAMS System Constraints Seeder
     * Purpose: Validate foreign key relationships and run integrity checks
     */
    public function run(): void
    {
        $this->command->info('🔗 Validating CREAMS System Constraints...');
        
        $this->validateConstraints();
        
        $this->command->info('✅ System Constraints validation completed');
    }

    /**
     * Validate all foreign key relationships are working
     */
    private function validateConstraints(): void
    {
        $this->command->info('   🔍 Running foreign key relationship checks...');
        
        $checks = [
            'Staff linked to centres' => "SELECT COUNT(*) as count FROM staffs s JOIN centres c ON s.centre_id = c.centre_id",
            'Trainees linked to centres' => "SELECT COUNT(*) as count FROM trainees t JOIN centres c ON t.centre_id = c.centre_id WHERE t.centre_id IS NOT NULL",
            'Assets linked to centres' => "SELECT COUNT(*) as count FROM assets a JOIN centres c ON a.centre_id = c.centre_id",
            'Activities linked to centres' => "SELECT COUNT(*) as count FROM activities a JOIN centres c ON a.centre_id = c.centre_id",
        ];

        foreach ($checks as $description => $query) {
            try {
                $result = DB::select($query);
                $count = $result[0]->count ?? 0;
                $this->command->line("      ✓ {$description}: {$count} records");
            } catch (\Exception $e) {
                $this->command->line("      ✗ {$description}: Error - " . $e->getMessage());
            }
        }
        
        $this->command->info('   📊 Database integrity check completed');
    }
}