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
        Schema::table('activities', function (Blueprint $table) {
            // Enhanced activity information
            $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced'])->nullable()->after('description');
            $table->string('age_group')->nullable(); // e.g., '3-6 years', '7-10 years'
            $table->integer('max_participants')->default(10);
            $table->integer('duration_minutes')->nullable(); // Duration in minutes
            $table->json('materials_required')->nullable(); // Store as JSON array
            $table->decimal('rating_average', 2, 1)->nullable(); // Calculated average rating
            $table->integer('total_ratings')->default(0);
            $table->integer('total_sessions')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('featured_image')->nullable(); // Path to activity image
            
            // Schedule information
            $table->json('schedule_days')->nullable(); // Store scheduled days as JSON
            $table->time('default_start_time')->nullable();
            $table->time('default_end_time')->nullable();
            
            // Activity categorization enhancements
            $table->string('therapy_type')->nullable(); // More specific than category
            $table->json('target_skills')->nullable(); // Skills this activity targets
            $table->text('prerequisites')->nullable(); // Prerequisites for participation
            
            // Add indexes for better performance
            $table->index(['difficulty_level']);
            $table->index(['age_group']);
            $table->index(['is_active']);
            $table->index(['therapy_type']);
            $table->index(['rating_average']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'difficulty_level',
                'age_group',
                'max_participants',
                'duration_minutes',
                'materials_required',
                'rating_average',
                'total_ratings',
                'total_sessions',
                'is_active',
                'featured_image',
                'schedule_days',
                'default_start_time',
                'default_end_time',
                'therapy_type',
                'target_skills',
                'prerequisites'
            ]);
        });
    }
};