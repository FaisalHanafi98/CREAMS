<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enhance activities table with Malaysian curriculum structure
        Schema::table('activities', function (Blueprint $table) {
            // Malaysian Subject Categories
            $table->enum('subject_category', [
                'bahasa_malaysia',
                'english_language', 
                'arabic_language',
                'mathematics',
                'science',
                'life_skills',
                'physical_therapy',
                'occupational_therapy',
                'speech_therapy',
                'social_skills'
            ])->nullable()->after('category');
            
            // Curriculum Level (Malaysian Foundation Standards)
            $table->enum('curriculum_level', [
                'pre_foundation',    // Pre-school level
                'foundation',        // Primary 1-3 equivalent
                'basic',            // Primary 4-6 equivalent  
                'adaptive'          // Special needs adaptation
            ])->default('foundation')->after('subject_category');
            
            // Time Management
            $table->integer('standard_duration_minutes')->default(45)->after('curriculum_level');
            $table->integer('minimum_duration_minutes')->default(15)->after('standard_duration_minutes');
            $table->integer('maximum_duration_minutes')->default(90)->after('minimum_duration_minutes');
            
            // Malaysian Curriculum Alignment
            $table->json('learning_outcomes')->nullable()->after('objectives'); // Malaysian curriculum LOs
            $table->json('assessment_criteria')->nullable()->after('learning_outcomes');
            $table->boolean('requires_special_accommodation')->default(false)->after('assessment_criteria');
        });

        // Create disability accommodation templates
        Schema::create('disability_accommodations', function (Blueprint $table) {
            $table->id();
            $table->enum('disability_type', [
                'Autism Spectrum Disorder',
                'Down Syndrome', 
                'Cerebral Palsy',
                'Hearing Impairment',
                'Visual Impairment',
                'Intellectual Disability',
                'Physical Disability',
                'Speech and Language Disorder',
                'Learning Disability',
                'Multiple Disabilities'
            ]);
            $table->enum('subject_category', [
                'bahasa_malaysia',
                'english_language',
                'arabic_language', 
                'mathematics',
                'science',
                'life_skills',
                'therapy'
            ]);
            $table->integer('recommended_duration_minutes');
            $table->integer('break_frequency_minutes')->default(0);
            $table->json('teaching_strategies'); // ['visual_aids', 'repetition', 'hands_on']
            $table->json('assessment_modifications'); // ['extended_time', 'alternative_format']
            $table->text('special_notes')->nullable();
            $table->timestamps();
            
            $table->unique(['disability_type', 'subject_category']);
        });

        // Create individual trainee subject adaptations
        Schema::create('trainee_subject_adaptations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->onDelete('cascade');
            $table->enum('subject_category', [
                'bahasa_malaysia',
                'english_language',
                'arabic_language',
                'mathematics', 
                'science',
                'life_skills',
                'therapy'
            ]);
            $table->integer('adapted_duration_minutes');
            $table->integer('break_frequency_minutes')->default(0);
            $table->json('accommodations')->nullable(); // Individual accommodations
            $table->text('teacher_notes')->nullable();
            $table->boolean('requires_aide')->default(false);
            $table->timestamps();
            
            $table->unique(['trainee_id', 'subject_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_subject_adaptations');
        Schema::dropIfExists('disability_accommodations');
        
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'subject_category',
                'curriculum_level', 
                'standard_duration_minutes',
                'minimum_duration_minutes',
                'maximum_duration_minutes',
                'learning_outcomes',
                'assessment_criteria',
                'requires_special_accommodation'
            ]);
        });
    }
};