<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing letter_date column
     * Module: Letter Generation System (Date Column Fix)
     * Priority: 019 - Critical (Fix letter generation error)
     */
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Add the missing letter_date column
            $table->date('letter_date')->nullable()->after('letter_name');
            
            // Also add some other commonly used columns that might be missing
            $table->string('letter_subject')->nullable()->after('letter_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn(['letter_date', 'letter_subject']);
        });
    }
};