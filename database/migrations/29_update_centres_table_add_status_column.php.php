<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('Starting centres table modification migration');
            
            if (Schema::hasTable('centres')) {
                Log::info('centres table exists, proceeding with modification');
                
                // Check if centre_status column exists but status doesn't
                $hasStatusColumn = Schema::hasColumn('centres', 'status');
                $hasCentreStatusColumn = Schema::hasColumn('centres', 'centre_status');
                
                Log::info('Column check results', [
                    'has_status_column' => $hasStatusColumn,
                    'has_centre_status_column' => $hasCentreStatusColumn
                ]);
                
                Schema::table('centres', function (Blueprint $table) use ($hasStatusColumn, $hasCentreStatusColumn) {
                    // If status column doesn't exist, add it
                    if (!$hasStatusColumn) {
                        Log::info('Adding status column to centres table');
                        
                        // If centre_status exists, copy values from centre_status
                        if ($hasCentreStatusColumn) {
                            Log::info('Will copy values from centre_status to status');
                            // First add the new column
                            $table->string('status')->nullable();
                        } else {
                            // Just add the column with default value
                            Log::info('Adding status column with default active value');
                            $table->string('status')->default('active');
                        }
                    } else {
                        Log::info('status column already exists in centres table');
                    }
                });
                
                // If both centre_status and status columns exist, copy data
                if ($hasCentreStatusColumn && Schema::hasColumn('centres', 'status')) {
                    Log::info('Copying data from centre_status to status column');
                    
                    // Use raw DB query to copy data
                    DB::statement('UPDATE centres SET status = centre_status');
                    
                    // After confirming data copy, you could optionally drop the old column
                    // Uncomment if you want to drop the old column
                    // Schema::table('centres', function (Blueprint $table) {
                    //     $table->dropColumn('centre_status');
                    // });
                }
            } else {
                Log::warning('centres table does not exist, migration skipped');
            }
            
            Log::info('centres table modification migration completed successfully');
        } catch (\Exception $e) {
            Log::error('Error during centres table modification migration', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to ensure migration fails properly
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Log::info('Rolling back centres table modification');
            
            if (Schema::hasTable('centres') && Schema::hasColumn('centres', 'status')) {
                Log::info('Removing status column from centres table');
                
                Schema::table('centres', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
                
                Log::info('Status column removed successfully');
            } else {
                Log::info('No status column found to remove');
            }
        } catch (\Exception $e) {
            Log::error('Error during centres table rollback migration', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
};