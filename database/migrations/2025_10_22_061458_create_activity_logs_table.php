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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('centre_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Who performed the action
            $table->string('user_name')->nullable(); // Cache username for faster display
            $table->string('action_type'); // created, updated, deleted
            $table->string('model_type'); // User, Trainee, Activity, etc.
            $table->string('model_id')->nullable(); // ID of the affected record
            $table->string('title'); // Human-readable description
            $table->text('description')->nullable(); // Detailed description
            $table->json('metadata')->nullable(); // Store additional data (old/new values, etc.)
            $table->string('icon')->default('circle'); // FontAwesome icon
            $table->string('status')->default('info'); // success, warning, danger, info
            $table->timestamps();

            // Indexes
            $table->index('centre_id');
            $table->index('user_id');
            $table->index('model_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
