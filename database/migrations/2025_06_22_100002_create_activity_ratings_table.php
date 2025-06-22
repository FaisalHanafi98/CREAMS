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
        Schema::create('activity_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who rated
            $table->foreignId('trainee_id')->nullable()->constrained()->onDelete('cascade'); // For whom (if applicable)
            $table->decimal('rating', 2, 1)->check('rating >= 1 AND rating <= 5');
            $table->text('review')->nullable();
            $table->enum('rating_type', ['teacher', 'guardian', 'trainee'])->default('teacher');
            $table->timestamps();
            
            // Ensure one rating per user per activity
            $table->unique(['activity_id', 'user_id', 'trainee_id']);
            $table->index(['activity_id', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_ratings');
    }
};