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
            // First, create backup tables only if they don't already exist
            if (Schema::hasTable('activities') && !Schema::hasTable('activities_backup')) {
                Log::info('Creating activities backup table');
                DB::statement('CREATE TABLE IF NOT EXISTS activities_backup AS SELECT * FROM activities');
            }
            
            if (Schema::hasTable('trainee_activities') && !Schema::hasTable('trainee_activities_backup')) {
                Log::info('Creating trainee_activities backup table');
                DB::statement('CREATE TABLE IF NOT EXISTS trainee_activities_backup AS SELECT * FROM trainee_activities');
            }
            
            // Check if we need to create the activities table
            $needsNewActivitiesTable = false;
            
            if (Schema::hasTable('activities')) {
                // If we have the old schema, we'll need to recreate it
                // Check for missing columns that would indicate an old schema
                $needsNewActivitiesTable = !Schema::hasColumn('activities', 'activity_code') || 
                                          !Schema::hasColumn('activities', 'age_group') ||
                                          !Schema::hasColumn('activities', 'difficulty_level');
                
                if ($needsNewActivitiesTable) {
                    Log::info('Activities table exists but has old schema, will recreate');
                    
                    // Drop related tables that depend on activities table
                    if (Schema::hasTable('session_enrollments')) {
                        Schema::dropIfExists('session_enrollments');
                    }
                    
                    if (Schema::hasTable('activity_attendance')) {
                        Schema::dropIfExists('activity_attendance');
                    }
                    
                    if (Schema::hasTable('activity_sessions')) {
                        Schema::dropIfExists('activity_sessions');
                    }
                    
                    Schema::dropIfExists('activities');
                }
            } else {
                $needsNewActivitiesTable = true;
                Log::info('Activities table does not exist, will create');
            }
            
            // Create optimized activities table if needed
            if ($needsNewActivitiesTable) {
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
                
                // Create activity sessions table if needed
                if (!Schema::hasTable('activity_sessions')) {
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
                        $table->string('semester')->index();
                        $table->boolean('is_active')->default(true);
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        
                        $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                        $table->foreign('teacher_id')->references('id')->on('users');
                        $table->index(['activity_id', 'is_active']);
                        $table->index(['teacher_id', 'day_of_week']);
                    });
                }
                
                // Create session enrollments table if needed
                if (!Schema::hasTable('session_enrollments')) {
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
                    });
                }
                
                // Create activity attendance table if needed
                if (!Schema::hasTable('activity_attendance')) {
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
                }
                
                // Import data from backup tables if they exist
                $this->migrateData();
            }
            
        } catch (\Exception $e) {
            Log::error('Error during activity data consolidation: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function migrateData()
    {
        try {
            // Migrate data from rehabilitation_activities to activities
            if (Schema::hasTable('rehabilitation_activities')) {
                Log::info('Migrating data from rehabilitation_activities to activities');
                
                $rehabActivities = DB::table('rehabilitation_activities')->get();
                
                foreach ($rehabActivities as $rehabActivity) {
                    // Check if the activity already exists
                    $exists = DB::table('activities')
                        ->where('activity_name', $rehabActivity->name)
                        ->exists();
                        
                    if (!$exists) {
                        DB::table('activities')->insert([
                            'activity_name' => $rehabActivity->name,
                            'activity_code' => strtoupper(substr($rehabActivity->category, 0, 3)) . '-' . str_pad($rehabActivity->id, 3, '0', STR_PAD_LEFT),
                            'description' => $rehabActivity->full_description ?? $rehabActivity->short_description,
                            'category' => $rehabActivity->category,
                            'objectives' => $rehabActivity->progress_metrics ?? null,
                            'materials_needed' => null, // Map from rehabilitation_resources if needed
                            'age_group' => $rehabActivity->age_range ?? 'All Ages',
                            'difficulty_level' => ucfirst($rehabActivity->difficulty_level),
                            'is_active' => $rehabActivity->status === 'published',
                            'created_by' => $rehabActivity->created_by,
                            'created_at' => $rehabActivity->created_at,
                            'updated_at' => $rehabActivity->updated_at
                        ]);
                    }
                }
                
                Log::info('Successfully migrated rehabilitation activities');
            }
            
            // Migrate data from trainee_activities if exists
            if (Schema::hasTable('trainee_activities')) {
                Log::info('Migrating data from trainee_activities');
                
                $traineeActivities = DB::table('trainee_activities')->get();
                
                foreach ($traineeActivities as $traineeActivity) {
                    // Check if activity already exists
                    $existingActivity = DB::table('activities')
                        ->where('activity_name', $traineeActivity->activity_name ?? 'Migrated Activity ' . $traineeActivity->id)
                        ->first();
                    
                    if (!$existingActivity) {
                        DB::table('activities')->insert([
                            'activity_name' => $traineeActivity->activity_name ?? 'Migrated Activity ' . $traineeActivity->id,
                            'activity_code' => 'MIG-' . str_pad($traineeActivity->id, 3, '0', STR_PAD_LEFT),
                            'description' => $traineeActivity->description ?? 'Migrated from trainee activities',
                            'category' => $traineeActivity->category ?? 'General',
                            'objectives' => $traineeActivity->objectives ?? null,
                            'materials_needed' => null,
                            'age_group' => 'All Ages',
                            'difficulty_level' => 'Beginner',
                            'is_active' => true,
                            'created_by' => $traineeActivity->created_by ?? 1,
                            'created_at' => $traineeActivity->created_at ?? now(),
                            'updated_at' => $traineeActivity->updated_at ?? now()
                        ]);
                    }
                }
                
                Log::info('Successfully migrated trainee activities');
            }
            
            // Restore from backup if data exists
            if (Schema::hasTable('activities_backup') && DB::table('activities')->count() == 0) {
                Log::info('Restoring data from activities_backup');
                
                $backupActivities = DB::table('activities_backup')->get();
                
                foreach ($backupActivities as $activity) {
                    $data = (array) $activity;
                    // Remove any columns that don't exist in the new schema
                    unset($data['id']); // Let the database assign a new ID
                    
                    DB::table('activities')->insert($data);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error during data migration: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        // Restore from backups if they exist
        if (Schema::hasTable('activities_backup')) {
            if (Schema::hasTable('activities')) {
                Schema::dropIfExists('activities');
            }
            
            DB::statement('CREATE TABLE activities AS SELECT * FROM activities_backup');
            Log::warning('Restored activities from backup');
        }
        
        if (Schema::hasTable('trainee_activities_backup')) {
            if (Schema::hasTable('trainee_activities')) {
                Schema::dropIfExists('trainee_activities');
            }
            
            DB::statement('CREATE TABLE trainee_activities AS SELECT * FROM trainee_activities_backup');
            Log::warning('Restored trainee_activities from backup');
        }
    }
};