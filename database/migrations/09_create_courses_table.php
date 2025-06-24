<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            Schema::create('courses', function (Blueprint $table) {
                $table->string('course_id')->primary();
                $table->string('course_type');
                $table->string('course_day');
                $table->string('start_time');
                $table->string('end_time');
                $table->string('location_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('location_id')
                    ->references('centre_id')
                    ->on('centres')
                    ->onDelete('set null');
                
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
