<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('Starting rehabilitation_activities table creation');
            
            // Create rehabilitation_activities table
            Schema::create('rehabilitation_activities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category');
                $table->string('short_description');
                $table->text('full_description');
                $table->enum('difficulty_level', ['easy', 'medium', 'hard']);
                $table->string('age_range');
                $table->enum('activity_type', ['individual', 'group', 'both']);
                $table->integer('duration');
                $table->integer('max_participants')->nullable();
                $table->text('lower_adaptations')->nullable();
                $table->text('higher_adaptations')->nullable();
                $table->text('progress_metrics');
                $table->text('notes')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by');
                $table->timestamps();
                $table->softDeletes();
                
                // Foreign keys
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            });
            
            Log::info('Starting rehabilitation_objectives table creation');
            
            // Create rehabilitation_objectives table
            Schema::create('rehabilitation_objectives', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->text('description');
                $table->integer('order')->default(1);
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('activity_id')->references('id')->on('rehabilitation_activities')->onDelete('cascade');
            });
            
            Log::info('Starting rehabilitation_resources table creation');
            
            // Create rehabilitation_resources table
            Schema::create('rehabilitation_resources', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('name');
                $table->string('type')->nullable();
                $table->boolean('optional')->default(false);
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('activity_id')->references('id')->on('rehabilitation_activities')->onDelete('cascade');
            });
            
            Log::info('Starting rehabilitation_steps table creation');
            
            // Create rehabilitation_steps table
            Schema::create('rehabilitation_steps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('title');
                $table->text('description');
                $table->integer('order')->default(1);
                $table->integer('duration')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('activity_id')->references('id')->on('rehabilitation_activities')->onDelete('cascade');
            });
            
            Log::info('Starting rehabilitation_milestones table creation');
            
            // Create rehabilitation_milestones table
            Schema::create('rehabilitation_milestones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->text('description');
                $table->integer('order')->default(1);
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('activity_id')->references('id')->on('rehabilitation_activities')->onDelete('cascade');
            });
            
            Log::info('Starting rehabilitation_schedules table creation');
            
            // Create rehabilitation_schedules table
            Schema::create('rehabilitation_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('teacher_id');
                $table->string('centre_id');
                $table->dateTime('start_time');
                $table->dateTime('end_time');
                $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
                $table->integer('max_participants');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('activity_id')->references('id')->on('rehabilitation_activities')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
            
            Log::info('Starting rehabilitation_participants table creation');
            
            // Create rehabilitation_participants table
            Schema::create('rehabilitation_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_id');
                $table->unsignedBigInteger('trainee_id');
                $table->enum('attendance_status', ['present', 'absent', 'excused', 'late'])->nullable();
                $table->integer('progress_rating')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('marked_by')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('schedule_id')->references('id')->on('rehabilitation_schedules')->onDelete('cascade');
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->foreign('marked_by')->references('id')->on('users')->onDelete('cascade');
                
                // Unique constraint to prevent duplicate entries
                $table->unique(['schedule_id', 'trainee_id']);
            });
            
            Log::info('Starting activities table modification');
            
            // Add rehab_activity_id column to activities table if it doesn't exist
            if (!Schema::hasColumn('activities', 'rehab_activity_id')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->unsignedBigInteger('rehab_activity_id')->nullable();
                    $table->foreign('rehab_activity_id')->references('id')->on('rehabilitation_activities')->onDelete('set null');
                });
            }
            
            Log::info('Rehabilitation module tables created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating rehabilitation module tables: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Remove foreign key constraint from activities table
            if (Schema::hasColumn('activities', 'rehab_activity_id')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->dropForeign(['rehab_activity_id']);
                    $table->dropColumn('rehab_activity_id');
                });
            }
            
            // Drop tables in reverse order to avoid foreign key constraints
            Schema::dropIfExists('rehabilitation_participants');
            Schema::dropIfExists('rehabilitation_schedules');
            Schema::dropIfExists('rehabilitation_milestones');
            Schema::dropIfExists('rehabilitation_steps');
            Schema::dropIfExists('rehabilitation_resources');
            Schema::dropIfExists('rehabilitation_objectives');
            Schema::dropIfExists('rehabilitation_activities');
            
            Log::info('Rehabilitation module tables dropped successfully');
        } catch (\Exception $e) {
            Log::error('Error dropping rehabilitation module tables: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
};