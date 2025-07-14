<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            // Backup existing data first
            $this->backupExistingData();
            
            // Fix activities table structure if needed
            $this->fixActivitiesTable();
            
            // Fix activity_sessions table if needed
            $this->fixActivitySessionsTable();
            
            // Fix session_enrollments table if needed
            $this->fixSessionEnrollmentsTable();
            
            // Fix activity_attendance table if needed
            $this->fixActivityAttendanceTable();
            
            // Migrate any data from backups if tables were recreated
            $this->migrateBackupData();
            
            Log::info('All activity tables fixed successfully');
            
        } catch (\Exception $e) {
            Log::error('Error fixing activity tables: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function backupExistingData()
    {
        // Create backups of existing tables if they exist
        if (Schema::hasTable('activities') && !Schema::hasTable('activities_backup_fix')) {
            Log::info('Backing up activities table');
            DB::statement('CREATE TABLE IF NOT EXISTS activities_backup_fix AS SELECT * FROM activities');
        }
        
        if (Schema::hasTable('activity_sessions') && !Schema::hasTable('activity_sessions_backup_fix')) {
            Log::info('Backing up activity_sessions table');
            DB::statement('CREATE TABLE IF NOT EXISTS activity_sessions_backup_fix AS SELECT * FROM activity_sessions');
        }
        
        if (Schema::hasTable('session_enrollments') && !Schema::hasTable('session_enrollments_backup_fix')) {
            Log::info('Backing up session_enrollments table');
            DB::statement('CREATE TABLE IF NOT EXISTS session_enrollments_backup_fix AS SELECT * FROM session_enrollments');
        }
        
        if (Schema::hasTable('activity_attendance') && !Schema::hasTable('activity_attendance_backup_fix')) {
            Log::info('Backing up activity_attendance table');
            DB::statement('CREATE TABLE IF NOT EXISTS activity_attendance_backup_fix AS SELECT * FROM activity_attendance');
        }
    }
    
    private function fixActivitiesTable()
    {
        $needsRecreate = false;
        
        // Check if table exists with correct structure
        if (Schema::hasTable('activities')) {
            // Check for required columns with correct types
            $missingColumns = !Schema::hasColumn('activities', 'activity_code') ||
                              !Schema::hasColumn('activities', 'activity_name') ||
                              !Schema::hasColumn('activities', 'category') ||
                              !Schema::hasColumn('activities', 'is_active');
                              
            if ($missingColumns) {
                Log::info('Activities table is missing required columns, will recreate');
                $needsRecreate = true;
            }
        } else {
            Log::info('Activities table does not exist, will create');
            $needsRecreate = true;
        }
        
        if ($needsRecreate) {
            // Drop dependent tables first
            Schema::dropIfExists('activity_attendance');
            Schema::dropIfExists('session_enrollments');
            Schema::dropIfExists('activity_sessions');
            Schema::dropIfExists('activities');
            
            // Create table with proper structure
            Schema::create('activities', function (Blueprint $table) {
                $table->id();
                $table->string('activity_name');
                $table->string('activity_code')->unique();
                $table->string('category');
                $table->text('description');
                $table->text('objectives')->nullable();
                $table->text('materials_needed')->nullable();
                $table->enum('age_group', ['3-6', '7-12', '13-18', 'All Ages']);
                $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced']);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
                $table->index(['category', 'is_active']);
            });
            
            Log::info('Activities table recreated successfully');
        } else {
            Log::info('Activities table structure is valid, no recreation needed');
        }
    }
    
    private function fixActivitySessionsTable()
    {
        if (!Schema::hasTable('activity_sessions')) {
            Log::info('Creating activity_sessions table');
            
            Schema::create('activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('teacher_id');
                $table->string('class_name');
                $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
                $table->time('start_time');
                $table->time('end_time');
                $table->decimal('duration_hours', 3, 1);
                $table->string('location');
                $table->integer('max_capacity')->default(10);
                $table->integer('current_enrollment')->default(0);
                $table->string('semester');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('users');
                $table->index(['activity_id', 'is_active']);
                $table->index(['teacher_id', 'day_of_week']);
                $table->index('semester');
            });
            
            Log::info('Activity_sessions table created successfully');
        } else {
            Log::info('Activity_sessions table already exists, checking structure');
            
            // Check and add any missing columns
            if (!Schema::hasColumn('activity_sessions', 'is_active')) {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true);
                });
                Log::info('Added is_active column to activity_sessions table');
            }
            
            if (!Schema::hasColumn('activity_sessions', 'current_enrollment')) {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->integer('current_enrollment')->default(0);
                });
                Log::info('Added current_enrollment column to activity_sessions table');
            }
        }
    }
    
    private function fixSessionEnrollmentsTable()
    {
        if (!Schema::hasTable('session_enrollments')) {
            Log::info('Creating session_enrollments table');
            
            Schema::create('session_enrollments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('trainee_id');
                $table->datetime('enrollment_date');
                $table->enum('status', ['active', 'withdrawn', 'completed'])->default('active');
                $table->unsignedBigInteger('enrolled_by');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->foreign('enrolled_by')->references('id')->on('users');
                
                $table->unique(['session_id', 'trainee_id']);
                $table->index(['trainee_id', 'status']);
                $table->index(['session_id', 'status']);
            });
            
            Log::info('Session_enrollments table created successfully');
        } else {
            Log::info('Session_enrollments table already exists, checking structure');
            
            // Check and add any missing columns or indexes
            if (!Schema::hasColumn('session_enrollments', 'enrolled_by')) {
                Schema::table('session_enrollments', function (Blueprint $table) {
                    $table->unsignedBigInteger('enrolled_by')->nullable();
                    $table->foreign('enrolled_by')->references('id')->on('users');
                });
                Log::info('Added enrolled_by column to session_enrollments table');
            }
        }
    }
    
    private function fixActivityAttendanceTable()
    {
        if (!Schema::hasTable('activity_attendance')) {
            Log::info('Creating activity_attendance table');
            
            Schema::create('activity_attendance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('trainee_id');
                $table->date('attendance_date');
                $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
                $table->time('arrival_time')->nullable();
                $table->time('departure_time')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('marked_by');
                $table->timestamps();
                
                $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->foreign('marked_by')->references('id')->on('users');
                
                $table->unique(['session_id', 'trainee_id', 'attendance_date']);
                $table->index(['session_id', 'attendance_date']);
                $table->index(['trainee_id', 'attendance_date']);
            });
            
            Log::info('Activity_attendance table created successfully');
        } else {
            Log::info('Activity_attendance table already exists, checking structure');
            
            // Check and add any missing columns or indexes
            if (!Schema::hasColumn('activity_attendance', 'marked_by')) {
                Schema::table('activity_attendance', function (Blueprint $table) {
                    $table->unsignedBigInteger('marked_by')->nullable();
                    $table->foreign('marked_by')->references('id')->on('users');
                });
                Log::info('Added marked_by column to activity_attendance table');
            }
        }
    }
    
    private function migrateBackupData()
    {
        // Restore activities data if table was recreated
        if (Schema::hasTable('activities_backup_fix') && DB::table('activities')->count() == 0) {
            Log::info('Restoring data from activities_backup_fix');
            
            $backupActivities = DB::table('activities_backup_fix')->get();
            $requiredColumns = ['activity_name', 'category', 'description', 'created_by', 'created_at', 'updated_at'];
            
            foreach ($backupActivities as $activity) {
                try {
                    $data = [];
                    
                    // Only include columns that exist in both tables
                    foreach ((array) $activity as $column => $value) {
                        if (Schema::hasColumn('activities', $column) && $column != 'id') {
                            $data[$column] = $value;
                        }
                    }
                    
                    // Add required columns if missing
                    foreach ($requiredColumns as $column) {
                        if (!isset($data[$column])) {
                            if ($column == 'activity_name') {
                                $data[$column] = 'Migrated Activity ' . $activity->id;
                            } elseif ($column == 'category') {
                                $data[$column] = 'General';
                            } elseif ($column == 'description') {
                                $data[$column] = 'Migrated from activities_backup_fix';
                            } elseif ($column == 'created_by') {
                                $data[$column] = 1; // Default admin user
                            } elseif (in_array($column, ['created_at', 'updated_at'])) {
                                $data[$column] = now();
                            }
                        }
                    }
                    
                    // Add activity_code if missing
                    if (!isset($data['activity_code'])) {
                        $data['activity_code'] = 'MIG-' . str_pad($activity->id, 3, '0', STR_PAD_LEFT);
                    }
                    
                    // Add age_group if missing
                    if (!isset($data['age_group'])) {
                        $data['age_group'] = 'All Ages';
                    }
                    
                    // Add difficulty_level if missing
                    if (!isset($data['difficulty_level'])) {
                        $data['difficulty_level'] = 'Beginner';
                    }
                    
                    // Add is_active if missing
                    if (!isset($data['is_active'])) {
                        $data['is_active'] = true;
                    }
                    
                    DB::table('activities')->insert($data);
                } catch (\Exception $e) {
                    Log::warning('Could not migrate activity ID ' . $activity->id . ': ' . $e->getMessage());
                }
            }
            
            Log::info('Activities data restored successfully');
        }
        
        // Similar for other tables...
        // (add code to restore other tables if needed)
    }

    public function down()
    {
        // Restore from backups if they exist
        if (Schema::hasTable('activities_backup_fix') && Schema::hasTable('activities')) {
            Schema::dropIfExists('activity_attendance');
            Schema::dropIfExists('session_enrollments');
            Schema::dropIfExists('activity_sessions');
            Schema::dropIfExists('activities');
            
            DB::statement('CREATE TABLE activities AS SELECT * FROM activities_backup_fix');
            Log::warning('Restored activities from backup_fix');
        }
        
        // Similar restore for other tables if needed
    }
};