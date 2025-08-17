<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Fix activity_outcomes field type
     * Module: Activity System (Learning Outcomes Fix)
     * Priority: 014 - Critical (Fix database error)
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Change activity_outcomes from text to json to properly store learning outcomes
            $table->json('activity_outcomes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Revert back to text field
            $table->text('activity_outcomes')->nullable()->change();
        });
    }
};