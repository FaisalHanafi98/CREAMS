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
            $table->string('class_id')->unique();
            $table->string('class_name');
            $table->text('class_description')->nullable();
            $table->string('class_level');
            $table->integer('max_students')->default(20);
            $table->integer('current_students')->default(0);
            $table->time('class_start_time');
            $table->time('class_end_time');
            $table->json('class_days')->nullable();
            $table->string('class_room');
            $table->string('centre_id');
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->enum('class_status', ['active', 'inactive', 'completed'])->default('active');
            $table->date('class_start_date');
            $table->date('class_end_date')->nullable();
            $table->timestamps();
            
            $table->index(['class_id']);
            $table->index(['centre_id']);
            $table->index(['instructor_id']);
            $table->index(['course_id']);
            $table->index(['class_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};