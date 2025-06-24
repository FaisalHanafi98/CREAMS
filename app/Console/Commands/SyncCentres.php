<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCentres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centres:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize centre_id and centre_location in users table with the correct values from centres table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting centre synchronization...');
        Log::info('Centre sync command started');
        
        try {
            // First, get a mapping of centre data
            $centres = DB::table('centres')->get(['centre_id', 'centre_name']);
            
            if ($centres->isEmpty()) {
                $this->error('No centres found in the database.');
                return 1;
            }
            
            // Create a lookup table for quick reference
            $centreMap = [];
            foreach ($centres as $centre) {
                $centreMap[$centre->centre_id] = $centre->centre_name;
            }
            
            $this->info('Found ' . count($centreMap) . ' centres');
            
            // Get all users
            $users = DB::table('users')->get(['id', 'centre_id', 'centre_location']);
            
            $this->info('Processing ' . count($users) . ' users...');
            
            $updatedCount = 0;
            $invalidCount = 0;
            
            $progressBar = $this->output->createProgressBar(count($users));
            $progressBar->start();
            
            foreach ($users as $user) {
                $updateData = [];
                $needsUpdate = false;
                
                // Check if centre_id exists in centres table
                if (empty($user->centre_id) || !isset($centreMap[$user->centre_id])) {
                    // Invalid centre_id, set to default "01" (Gombak)
                    $updateData['centre_id'] = '01';
                    $updateData['centre_location'] = 'Gombak';
                    $needsUpdate = true;
                    $invalidCount++;
                } else {
                    // Valid centre_id, ensure centre_location is correct
                    $correctLocation = $centreMap[$user->centre_id];
                    
                    if ($user->centre_location !== $correctLocation) {
                        $updateData['centre_location'] = $correctLocation;
                        $needsUpdate = true;
                    }
                }
                
                // Update user if needed
                if ($needsUpdate) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update($updateData);
                    
                    $updatedCount++;
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            
            $this->info('Centre synchronization completed:');
            $this->info('- Total users processed: ' . count($users));
            $this->info('- Users with invalid centre_id: ' . $invalidCount);
            $this->info('- Total users updated: ' . $updatedCount);
            
            Log::info('Centre sync command completed', [
                'total_users' => count($users),
                'invalid_centre_ids' => $invalidCount,
                'updated_users' => $updatedCount
            ]);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            
            Log::error('Error during centre sync command', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}