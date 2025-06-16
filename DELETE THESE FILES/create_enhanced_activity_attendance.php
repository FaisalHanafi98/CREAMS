<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_name');
            $table->string('activity_code', 20)->unique();
            $table->text('description');
            $table->string('category', 100)->index();
            $table->text('objectives')->nullable();
            $table->text('materials_needed')->nullable();
            $table->string('age_group', 50)->index();
            $table->string('difficulty_level', 50);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['category', 'is_active']);
            $table->index(['age_group', 'difficulty_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};