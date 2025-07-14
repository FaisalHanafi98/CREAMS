<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations to standardize avatar fields
     * 
     * @return void
     */
    public function up(): void
    {
        try {
            Log::info('Starting avatar standardization migration');
            
            // Process Users table
            if (Schema::hasTable('users')) {
                $this->processTable('users');
            }
            
            // Process other user tables
            $userTables = ['admins', 'supervisors', 'teachers', 'ajks', 'trainees'];
            foreach ($userTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->processTable($table);
                }
            }
            
            Log::info('Avatar standardization migration completed successfully');
        } catch (\Exception $e) {
            Log::error('Error during avatar standardization migration', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to ensure migration fails properly
        }
    }

    /**
     * Process individual table to standardize avatar field
     * 
     * @param string $table Table name
     * @return void
     */
    private function processTable($table)
    {
        DB::beginTransaction();
        
        try {
            Log::info("Processing table: $table");
            
            // Check for various avatar field names that might exist
            $hasAvatar = Schema::hasColumn($table, 'avatar');
            $hasUserAvatar = Schema::hasColumn($table, 'user_avatar');
            $hasTraineeAvatar = ($table === 'trainees' && Schema::hasColumn($table, 'trainee_avatar'));
            
            Log::info("Table $table column status", [
                'has_avatar' => $hasAvatar,
                'has_user_avatar' => $hasUserAvatar,
                'has_trainee_avatar' => $hasTraineeAvatar
            ]);
            
            // Step 1: Ensure 'avatar' field exists
            if (!$hasAvatar) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('avatar')->nullable();
                });
                $hasAvatar = true;
                Log::info("Added 'avatar' column to $table");
            }
            
            // Step 2: Migrate data from other avatar fields to 'avatar'
            $migrationsMade = false;
            
            if ($hasUserAvatar) {
                // Count records that need migration
                $recordsToMigrate = DB::table($table)
                    ->whereNull('avatar')
                    ->whereNotNull('user_avatar')
                    ->count();
                
                if ($recordsToMigrate > 0) {
                    // Copy data from user_avatar to avatar where avatar is null
                    DB::statement("UPDATE $table SET avatar = user_avatar WHERE avatar IS NULL AND user_avatar IS NOT NULL");
                    Log::info("Migrated $recordsToMigrate records from 'user_avatar' to 'avatar' in $table");
                    $migrationsMade = true;
                }
            }
            
            if ($hasTraineeAvatar) {
                // Count records that need migration
                $recordsToMigrate = DB::table($table)
                    ->whereNull('avatar')
                    ->whereNotNull('trainee_avatar')
                    ->count();
                
                if ($recordsToMigrate > 0) {
                    // Copy data from trainee_avatar to avatar where avatar is null
                    DB::statement("UPDATE $table SET avatar = trainee_avatar WHERE avatar IS NULL AND trainee_avatar IS NOT NULL");
                    Log::info("Migrated $recordsToMigrate records from 'trainee_avatar' to 'avatar' in $table");
                    $migrationsMade = true;
                }
            }
            
            // Step 3: Verify data migration was successful
            if ($migrationsMade) {
                // Count records that should have been migrated but weren't
                $failedMigrations = 0;
                
                if ($hasUserAvatar) {
                    $failedMigrations += DB::table($table)
                        ->whereNull('avatar')
                        ->whereNotNull('user_avatar')
                        ->count();
                }
                
                if ($hasTraineeAvatar) {
                    $failedMigrations += DB::table($table)
                        ->whereNull('avatar')
                        ->whereNotNull('trainee_avatar')
                        ->count();
                }
                
                if ($failedMigrations > 0) {
                    throw new \Exception("Data migration validation failed: $failedMigrations records were not properly migrated to 'avatar' column");
                }
                
                Log::info("Data migration validation successful for table $table");
            }
            
            // Step 4: Delete the old avatar columns
            if ($hasUserAvatar) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('user_avatar');
                });
                Log::info("Dropped 'user_avatar' column from $table");
            }
            
            if ($hasTraineeAvatar && $table === 'trainees') {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('trainee_avatar');
                });
                Log::info("Dropped 'trainee_avatar' column from $table");
            }
            
            DB::commit();
            Log::info("Completed processing table: $table");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing table $table", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down(): void
    {
        // In the down migration, we'll add back the user_avatar and trainee_avatar columns
        // but we can't restore the deleted data
        
        try {
            Log::info('Starting avatar standardization rollback');
            
            // Process Users table
            if (Schema::hasTable('users')) {
                $this->rollbackTable('users');
            }
            
            // Process other user tables
            $userTables = ['admins', 'supervisors', 'teachers', 'ajks', 'trainees'];
            foreach ($userTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->rollbackTable($table, $table === 'trainees');
                }
            }
            
            Log::info('Avatar standardization rollback completed');
        } catch (\Exception $e) {
            Log::error('Error during avatar standardization rollback', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Rollback changes for a table
     * 
     * @param string $table Table name
     * @param bool $isTraineeTable Whether this is the trainees table
     * @return void
     */
    private function rollbackTable($table, $isTraineeTable = false)
    {
        DB::beginTransaction();
        
        try {
            Log::info("Rolling back table: $table");
            
            // Add back user_avatar column
            if (!Schema::hasColumn($table, 'user_avatar')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('user_avatar')->nullable();
                });
                
                // Copy data from avatar to user_avatar if avatar exists
                if (Schema::hasColumn($table, 'avatar')) {
                    DB::statement("UPDATE $table SET user_avatar = avatar WHERE avatar IS NOT NULL");
                }
                
                Log::info("Added back 'user_avatar' column to $table");
            }
            
            // For trainees table, add back trainee_avatar column
            if ($isTraineeTable && !Schema::hasColumn($table, 'trainee_avatar')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('trainee_avatar')->nullable();
                });
                
                // Copy data from avatar to trainee_avatar if avatar exists
                if (Schema::hasColumn($table, 'avatar')) {
                    DB::statement("UPDATE $table SET trainee_avatar = avatar WHERE avatar IS NOT NULL");
                }
                
                Log::info("Added back 'trainee_avatar' column to $table");
            }
            
            DB::commit();
            Log::info("Completed rolling back table: $table");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error rolling back table $table", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
};