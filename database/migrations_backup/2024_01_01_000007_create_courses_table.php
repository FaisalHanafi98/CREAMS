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
            $table->string('course_code')->unique();
            $table->string('course_name');
            $table->text('description')->nullable();
            $table->string('level')->nullable();
            $table->integer('duration_weeks')->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('centre_id')->nullable();
            $table->timestamps();
            
            $table->index(['course_code']);
            $table->index(['is_active']);
            $table->index(['centre_id']);
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