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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code')->unique();
            $table->string('activity_name');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('materials_needed')->nullable();
            $table->string('activity_type');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->enum('age_group', ['early_childhood', 'primary', 'secondary', 'adult', 'all'])->default('all');
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants')->default(10);
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['activity_code']);
            $table->index(['centre_id']);
            $table->index(['is_active']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};