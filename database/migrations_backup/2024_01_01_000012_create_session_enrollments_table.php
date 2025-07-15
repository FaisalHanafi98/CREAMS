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
        Schema::create('session_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->enum('status', ['enrolled', 'attended', 'absent', 'cancelled'])->default('enrolled');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['session_id', 'trainee_id']);
            $table->index(['session_id']);
            $table->index(['trainee_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_enrollments');
    }
};