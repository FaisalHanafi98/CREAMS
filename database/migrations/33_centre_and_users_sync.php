<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations to synchronize centre_id and centre_location in users table
     * with the correct values from centres table.
     * 
     * @return void
     */
    public function up(): void
    {
        try {
            Log::info('Starting centre synchronization migration');
            
            // First, get a mapping of centre data
            $centres = DB::table('centres')->get(['centre_id', 'centre_name']);
            
            // Create a lookup table for quick reference
            $centreMap = [];
            foreach ($centres as $centre) {
                $centreMap[$centre->centre_id] = $centre->centre_name;
            }
            
            Log::info('Centres lookup map created', [
                'centres' => $centreMap
            ]);
            
            // Update users with invalid centre_id or centre_location
            $updatedCount = 0;
            $invalidCount = 0;
            
            // Get all users
            $users = DB::table('users')->get(['id', 'centre_id', 'centre_location']);
            
            foreach ($users as $user) {
                $updateData = [];
                $needsUpdate = false;
                
                // Check if centre_id exists in centres table
                if (!isset($centreMap[$user->centre_id])) {
                    // Invalid centre_id, set to default "01" (Gombak)
                    $updateData['centre_id'] = '01';
                    $updateData['centre_location'] = 'Gombak';
                    $needsUpdate = true;
                    $invalidCount++;
                    
                    Log::info('User has invalid centre_id', [
                        'user_id' => $user->id,
                        'invalid_centre_id' => $user->centre_id,
                        'set_to' => '01'
                    ]);
                } else {
                    // Valid centre_id, ensure centre_location is correct
                    $correctLocation = $centreMap[$user->centre_id];
                    
                    if ($user->centre_location !== $correctLocation) {
                        $updateData['centre_location'] = $correctLocation;
                        $needsUpdate = true;
                        
                        Log::info('User has mismatched centre_location', [
                            'user_id' => $user->id,
                            'centre_id' => $user->centre_id,
                            'incorrect_location' => $user->centre_location,
                            'correct_location' => $correctLocation
                        ]);
                    }
                }
                
                // Update user if needed
                if ($needsUpdate) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update($updateData);
                    
                    $updatedCount++;
                }
            }
            
            Log::info('Centre synchronization completed', [
                'total_users' => count($users),
                'invalid_centre_ids' => $invalidCount,
                'updated_users' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error during centre synchronization migration', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to ensure migration fails properly
        }
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down(): void
    {
        // This migration cannot be reversed safely since we don't have the original values
        Log::info('Centre synchronization migration rollback - no action taken to preserve data integrity');
    }
};