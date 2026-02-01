<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GombakDataExtractor extends Seeder
{
    /**
     * Extract real Gombak data before migration changes
     */
    public function run(): void
    {
        $this->command->info('🔒 Extracting real Gombak centre data...');
        
        try {
            $realData = [
                'centres' => DB::table('centres')->where('centre_id', '01')->get()->toArray(),
                'users' => DB::table('staffs')->where('centre_id', '01')->get()->toArray(),
                'assets' => DB::table('assets')->where('centre_id', '01')->get()->toArray(),
                'staff_attendances' => DB::table('staff_attendances')
                    ->whereIn('user_id', function($query) {
                        $query->select('id')->from('users')->where('centre_id', '01');
                    })->get()->toArray(),
            ];
            
            // Store backup
            file_put_contents(
                database_path('real_data_backup.json'), 
                json_encode($realData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            
            $this->command->info('✅ Real data backed up successfully:');
            $this->command->line('   • Centres: ' . count($realData['centres']));
            $this->command->line('   • Users: ' . count($realData['users']));
            $this->command->line('   • Assets: ' . count($realData['assets']));
            $this->command->line('   • Staff Attendances: ' . count($realData['staff_attendances']));
            $this->command->line('   • Backup saved to: database/real_data_backup.json');
            
        } catch (\Exception $e) {
            $this->command->error('Failed to backup data: ' . $e->getMessage());
            throw $e;
        }
    }
}