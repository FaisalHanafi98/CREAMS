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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_id')->unique();
            $table->string('course_name');
            $table->text('course_description');
            $table->string('course_level');
            $table->integer('course_duration_months');
            $table->json('course_objectives');
            $table->json('course_modules');
            $table->string('course_certificate');
            $table->enum('course_status', ['active', 'inactive', 'draft'])->default('active');
            $table->decimal('course_fee', 10, 2)->nullable();
            $table->integer('max_trainees')->default(20);
            $table->string('course_image')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['course_id']);
            $table->index(['course_status']);
            $table->index(['course_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};