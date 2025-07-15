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
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'completed', 'dropped', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['activity_id', 'trainee_id']);
            $table->index(['activity_id']);
            $table->index(['trainee_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_enrollments');
    }
};