<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code', 20)->unique();
            $table->string('activity_name');
            $table->text('description');
            
            // Category and type
            $table->enum('category', [
                'Physical Therapy',
                'Occupational Therapy',
                'Speech Therapy',
                'Behavioral Therapy',
                'Sensory Integration',
                'Mathematics',
                'Literacy',
                'Science',
                'Computer Skills',
                'Art & Creativity',
                'Music Therapy',
                'Social Skills',
                'Life Skills',
                'Vocational Training'
            ]);
            $table->enum('activity_type', ['Individual', 'Group', 'Both'])->default('Both');
            
            // Details
            $table->text('objectives')->nullable();
            $table->text('materials_needed')->nullable();
            $table->json('skills_developed')->nullable();
            
            // Requirements
            $table->string('age_group')->nullable(); // e.g., "5-10 years"
            $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants')->default(10);
            $table->integer('duration_minutes')->default(60);
            
            // Location and resources
            $table->string('location_type')->nullable(); // e.g., "Therapy Room", "Outdoor"
            $table->boolean('requires_equipment')->default(false);
            $table->json('equipment_list')->nullable();
            
            // Status and tracking
            $table->boolean('is_active')->default(true);
            $table->integer('times_conducted')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            
            // Relationships
            $table->unsignedBigInteger('created_by');
            $table->string('centre_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['category', 'is_active']);
            $table->index(['age_group', 'difficulty_level']);
            $table->index('centre_id');
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};