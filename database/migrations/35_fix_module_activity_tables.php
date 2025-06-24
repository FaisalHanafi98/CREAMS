<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Backup existing tables if they exist
            $this->backupExistingTables();
            
            // Step 1: Fix activities table to include status column
            $this->fixActivitiesTable();
            
            // Step 2: Ensure activity_enrollments table exists before trainee_progress
            $this->fixActivityEnrollmentsTable();
            
            // Step 3: Fix trainee_progress table
            $this->fixTraineeProgressTable();
            
            // Step 4: Add any missing indexes
            $this->addMissingIndexes();
            
            // Step 5: Restore data from backups if needed
            $this->restoreData();
            
            Log::info('Activity module tables have been fixed successfully');
            
        } catch (\Exception $e) {
            Log::error('Error fixing activity module tables: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Backup existing tables before making changes.
     */
    private function backupExistingTables(): void
    {
        // Backup activities table
        if (Schema::hasTable('activities') && !Schema::hasTable('activities_backup_fix_june')) {
            DB::statement('CREATE TABLE activities_backup_fix_june AS SELECT * FROM activities');
            Log::info('Created backup of activities table: activities_backup_fix_june');
        }

        // Backup activity_enrollments table
        if (Schema::hasTable('activity_enrollments') && !Schema::hasTable('activity_enrollments_backup_fix_june')) {
            DB::statement('CREATE TABLE activity_enrollments_backup_fix_june AS SELECT * FROM activity_enrollments');
            Log::info('Created backup of activity_enrollments table: activity_enrollments_backup_fix_june');
        }

        // Backup trainee_progress table
        if (Schema::hasTable('trainee_progress') && !Schema::hasTable('trainee_progress_backup_fix_june')) {
            DB::statement('CREATE TABLE trainee_progress_backup_fix_june AS SELECT * FROM trainee_progress');
            Log::info('Created backup of trainee_progress table: trainee_progress_backup_fix_june');
        }
    }

    /**
     * Fix the activities table structure.
     */
    private function fixActivitiesTable(): void
    {
        if (Schema::hasTable('activities')) {
            // Check if status column exists
            if (!Schema::hasColumn('activities', 'status')) {
                // Add status column
                Schema::table('activities', function (Blueprint $table) {
                    $table->enum('status', ['draft', 'published', 'archived'])->default('published')->after('is_active');
                });
                Log::info('Added status column to activities table');
                
                // Copy is_active data to status column for consistency
                DB::statement("
                    UPDATE activities 
                    SET status = CASE 
                        WHEN is_active = 1 THEN 'published' 
                        ELSE 'archived' 
                    END
                ");
                Log::info('Migrated is_active values to status column');
            }
        } else {
            // Create activities table from scratch with proper structure
            Schema::create('activities', function (Blueprint $table) {
                $table->id();
                $table->string('activity_name');
                $table->string('activity_code', 20)->unique();
                $table->text('description');
                $table->string('category', 100)->index();
                $table->text('objectives')->nullable();
                $table->text('materials_needed')->nullable();
                $table->string('age_group', 50)->index();
                $table->string('difficulty_level', 50);
                $table->boolean('is_active')->default(true)->index();
                $table->enum('status', ['draft', 'published', 'archived'])->default('published')->index();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                
                // Indexes for performance
                $table->index(['category', 'is_active']);
                $table->index(['age_group', 'difficulty_level']);
                $table->index(['status', 'created_at']);
            });
            Log::info('Created activities table with proper structure');
        }
    }

    /**
     * Create or fix the activity_enrollments table.
     */
    private function fixActivityEnrollmentsTable(): void
    {
        // Drop existing trainee_progress table if it exists to avoid foreign key issues
        if (Schema::hasTable('trainee_progress')) {
            Schema::dropIfExists('trainee_progress');
            Log::info('Dropped trainee_progress table temporarily to resolve foreign key issues');
        }
        
        // Create or recreate activity_enrollments table
        if (Schema::hasTable('activity_enrollments')) {
            Schema::dropIfExists('activity_enrollments');
            Log::info('Dropped activity_enrollments table to recreate with proper structure');
        }
        
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'completed', 'withdrawn'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            
            $table->index(['trainee_id', 'status']);
            $table->index(['activity_id', 'status']);
            $table->unique(['activity_id', 'trainee_id'], 'unique_activity_enrollment');
        });
        
        Log::info('Created activity_enrollments table with proper structure');
    }

    /**
     * Create or fix the trainee_progress table.
     */
    private function fixTraineeProgressTable(): void
    {
        // Create trainee_progress table
        Schema::create('trainee_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_enrollment_id');
            $table->unsignedBigInteger('assessed_by');
            $table->date('assessment_date');
            $table->enum('assessment_type', ['initial', 'weekly', 'monthly', 'quarterly', 'final']);
            $table->json('skills_assessment')->nullable();
            $table->json('goals_progress')->nullable();
            $table->text('achievements')->nullable();
            $table->text('challenges')->nullable();
            $table->text('recommendations')->nullable();
            $table->decimal('overall_progress_score', 5, 2)->nullable();
            $table->boolean('goals_modified')->default(false);
            $table->json('new_goals')->nullable();
            $table->timestamps();
            
            // Add foreign keys
            $table->foreign('activity_enrollment_id')
                  ->references('id')
                  ->on('activity_enrollments')
                  ->onDelete('cascade');
                  
            $table->foreign('assessed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
            
            // Indexes
            $table->index(['activity_enrollment_id', 'assessment_date']);
            $table->index(['assessment_type', 'assessment_date']);
        });
        
        Log::info('Created trainee_progress table with proper structure');
    }

    /**
     * Add any missing indexes to tables.
     */
    private function addMissingIndexes(): void
    {
        // Add indexes to activities table
        if (Schema::hasTable('activities')) {
            // Only add if the index doesn't already exist
            if (!$this->indexExists('activities', 'idx_activities_category_status') && 
                Schema::hasColumn('activities', 'category') && 
                Schema::hasColumn('activities', 'status')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->index(['category', 'status'], 'idx_activities_category_status');
                });
                Log::info('Added index idx_activities_category_status to activities table');
            }
            
            if (!$this->indexExists('activities', 'idx_activities_creator_status') && 
                Schema::hasColumn('activities', 'created_by') && 
                Schema::hasColumn('activities', 'status')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->index(['created_by', 'status'], 'idx_activities_creator_status');
                });
                Log::info('Added index idx_activities_creator_status to activities table');
            }
        }
    }

    /**
     * Restore data from backups if available.
     */
    private function restoreData(): void
    {
        // Restore activities data
        if (Schema::hasTable('activities_backup_fix_june') && DB::table('activities')->count() == 0) {
            Log::info('Restoring data from activities_backup_fix_june');
            
            $backupActivities = DB::table('activities_backup_fix_june')->get();
            $tableColumns = $this->getTableColumns('activities');
            
            foreach ($backupActivities as $activity) {
                try {
                    $data = [];
                    
                    // Only include columns that exist in both tables
                    foreach ((array) $activity as $column => $value) {
                        if (in_array($column, $tableColumns) && $column != 'id') {
                            $data[$column] = $value;
                        }
                    }
                    
                    // Add required values for any missing columns
                    if (!isset($data['activity_code']) && in_array('activity_code', $tableColumns)) {
                        $data['activity_code'] = 'MIG-' . str_pad($activity->id, 3, '0', STR_PAD_LEFT);
                    }
                    
                    if (!isset($data['status']) && in_array('status', $tableColumns)) {
                        $data['status'] = $activity->is_active ?? true ? 'published' : 'archived';
                    }
                    
                    DB::table('activities')->insert($data);
                } catch (\Exception $e) {
                    Log::warning('Could not migrate activity ID ' . $activity->id . ': ' . $e->getMessage());
                }
            }
            
            Log::info('Activities data restored successfully');
        }
        
        // Restore activity_enrollments data
        if (Schema::hasTable('activity_enrollments_backup_fix_june') && DB::table('activity_enrollments')->count() == 0) {
            Log::info('Restoring data from activity_enrollments_backup_fix_june');
            
            $backupEnrollments = DB::table('activity_enrollments_backup_fix_june')->get();
            $tableColumns = $this->getTableColumns('activity_enrollments');
            
            foreach ($backupEnrollments as $enrollment) {
                try {
                    $data = [];
                    
                    // Only include columns that exist in both tables
                    foreach ((array) $enrollment as $column => $value) {
                        if (in_array($column, $tableColumns) && $column != 'id') {
                            $data[$column] = $value;
                        }
                    }
                    
                    DB::table('activity_enrollments')->insert($data);
                } catch (\Exception $e) {
                    Log::warning('Could not migrate enrollment ID ' . $enrollment->id . ': ' . $e->getMessage());
                }
            }
            
            Log::info('Activity enrollments data restored successfully');
        }
        
        // Restore trainee_progress data
        if (Schema::hasTable('trainee_progress_backup_fix_june') && DB::table('trainee_progress')->count() == 0) {
            Log::info('Restoring data from trainee_progress_backup_fix_june');
            
            $backupProgress = DB::table('trainee_progress_backup_fix_june')->get();
            $tableColumns = $this->getTableColumns('trainee_progress');
            
            foreach ($backupProgress as $progress) {
                try {
                    $data = [];
                    
                    // Only include columns that exist in both tables
                    foreach ((array) $progress as $column => $value) {
                        if (in_array($column, $tableColumns) && $column != 'id') {
                            $data[$column] = $value;
                        }
                    }
                    
                    // Check if the enrollment still exists
                    if (isset($data['activity_enrollment_id'])) {
                        $enrollmentExists = DB::table('activity_enrollments')
                            ->where('id', $data['activity_enrollment_id'])
                            ->exists();
                            
                        if ($enrollmentExists) {
                            DB::table('trainee_progress')->insert($data);
                        } else {
                            Log::warning('Skipped restoring progress record with non-existent enrollment ID: ' . $data['activity_enrollment_id']);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not migrate progress ID ' . $progress->id . ': ' . $e->getMessage());
                }
            }
            
            Log::info('Trainee progress data restored successfully');
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return !empty($indexes);
    }
    
    /**
     * Get all column names for a table.
     */
    private function getTableColumns(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore from backups if available
        if (Schema::hasTable('activities_backup_fix_june')) {
            Schema::dropIfExists('trainee_progress');
            Schema::dropIfExists('activity_enrollments');
            Schema::dropIfExists('activities');
            
            DB::statement('CREATE TABLE activities AS SELECT * FROM activities_backup_fix_june');
            Log::info('Restored activities from backup_fix_june');
        }
        
        if (Schema::hasTable('activity_enrollments_backup_fix_june')) {
            DB::statement('CREATE TABLE activity_enrollments AS SELECT * FROM activity_enrollments_backup_fix_june');
            Log::info('Restored activity_enrollments from backup_fix_june');
        }
        
        if (Schema::hasTable('trainee_progress_backup_fix_june')) {
            DB::statement('CREATE TABLE trainee_progress AS SELECT * FROM trainee_progress_backup_fix_june');
            Log::info('Restored trainee_progress from backup_fix_june');
        }
    }
};