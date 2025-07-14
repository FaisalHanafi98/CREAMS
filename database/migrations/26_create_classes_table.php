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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('course_id');
            $table->unsignedBigInteger('teacher_id');  // This will reference users table
            $table->string('center_id');  // Assuming this references centres.centre_id
            $table->json('schedule');
            $table->string('location');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
            
            // Add the foreign key constraint to reference the users table
            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->where('role', 'teacher')  // This syntax might not work in all MySQL versions
                ->onDelete('cascade');
            
            // Add the foreign key constraint for course_id
            $table->foreign('course_id')
                ->references('course_id')
                ->on('courses');

            // Add the foreign key constraint for center_id
            $table->foreign('center_id')
                ->references('centre_id')
                ->on('centres');
        });

        // Pivot table for trainees enrolled in classes
        Schema::create('class_trainee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('trainee_id')->constrained('trainees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_trainee');
        Schema::dropIfExists('classes');
    }
};