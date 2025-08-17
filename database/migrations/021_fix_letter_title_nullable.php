<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Make letter_title nullable or set default value
     * Module: Letter Generation System (Letter Title Fix)
     * Priority: 021 - Critical (Fix letter generation error)
     */
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Make letter_title nullable since it's not being provided in the form
            $table->string('letter_title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Revert to NOT NULL
            $table->string('letter_title')->nullable(false)->change();
        });
    }
};