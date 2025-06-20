<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        // First check if the referenced table exists
        if (!Schema::hasTable('activity_enrollments')) {
            Log::warning('The activity_enrollments table does not exist. Creating it first.');
            
            // Create the activity_enrollments table if it doesn't exist
            Schema::create('activity_enrollments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('trainee_id');
                $table->date('enrollment_date');
                $table->enum('status', ['active', 'completed', 'withdrawn'])->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('activity_id')->references('id')->on('activities');
                $table->foreign('trainee_id')->references('id')->on('trainees');
                
                $table->index(['trainee_id', 'status']);
                $table->index(['activity_id', 'status']);
            });
            
            Log::info('Created activity_enrollments table');
        }
        
        // Now create the trainee_progress table
        if (!Schema::hasTable('trainee_progress')) {
            Schema::create('trainee_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_enrollment_id'); // Changed from foreignId
                $table->unsignedBigInteger('assessed_by'); // Changed from foreignId
                $table->date('assessment_date');
                $table->enum('assessment_type', ['initial', 'weekly', 'monthly', 'quarterly', 'final']);
                $table->json('skills_assessment')->nullable(); // JSON array of skill scores
                $table->json('goals_progress')->nullable(); // Progress on individual goals
                $table->text('achievements')->nullable();
                $table->text('challenges')->nullable();
                $table->text('recommendations')->nullable();
                $table->decimal('overall_progress_score', 5, 2)->nullable(); // 0-100 scale
                $table->boolean('goals_modified')->default(false);
                $table->json('new_goals')->nullable(); // If goals were updated
                $table->timestamps();
                
                // Add foreign keys after defining columns
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
            
            Log::info('Created trainee_progress table');
        }
    }

    public function down()
    {
        Schema::dropIfExists('trainee_progress');
        // Uncomment if you want to also drop the activity_enrollments table if it was created by this migration
        // Schema::dropIfExists('activity_enrollments');
    }
};