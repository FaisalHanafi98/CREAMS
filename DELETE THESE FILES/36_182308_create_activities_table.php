<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_name');
            $table->string('activity_code')->unique();
            $table->text('description');
            $table->string('category');
            $table->text('objectives')->nullable();
            $table->text('materials_needed')->nullable();
            $table->enum('age_group', ['3-6', '7-12', '13-18', 'All Ages']);
            $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced']);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['category', 'is_active']);
            $table->index('activity_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};