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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->onDelete('cascade');
            $table->string('course_id')->nullable();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'excused', 'late'])->default('absent');
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Foreign key to link attendance to courses
            $table->foreign('course_id')
                ->references('course_id')
                ->on('courses')
                ->onDelete('set null');
            
            // Unique constraint to prevent duplicate attendance records
            $table->unique(['trainee_id', 'course_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};