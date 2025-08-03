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
        Schema::create('activity_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('prerequisite_activity_id');
            $table->decimal('minimum_completion_percentage', 5, 2)->default(80.00);
            $table->enum('required_competency_level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
            $table->boolean('is_required')->default(true);
            $table->text('description')->nullable(); // Why this prerequisite is needed
            $table->timestamps();

            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('prerequisite_activity_id')->references('id')->on('activities')->onDelete('cascade');
            
            // Indexes
            $table->index('activity_id');
            $table->index('prerequisite_activity_id');
            $table->index(['activity_id', 'is_required']);
            
            // Unique constraint to prevent duplicate prerequisites
            $table->unique(['activity_id', 'prerequisite_activity_id'], 'ap_activity_prereq_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_prerequisites');
    }
};
