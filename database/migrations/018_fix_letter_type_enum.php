<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Fix letter_type enum values
     * Module: Letter Generation System (Letter Type Fix)
     * Priority: 018 - Critical (Fix letter generation compatibility)
     */
    public function up(): void
    {
        // Modify the letter_type enum to include additional values for flexibility
        DB::statement("ALTER TABLE letters MODIFY COLUMN letter_type ENUM(
            'recommendation',
            'completion_certificate', 
            'progress_report',
            'invitation',
            'official_letter',
            'assessment_report',
            'letter',
            'general',
            'certificate',
            'report',
            'correspondence',
            'notification',
            'custom'
        ) DEFAULT 'custom'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE letters MODIFY COLUMN letter_type ENUM(
            'recommendation',
            'completion_certificate',
            'progress_report', 
            'invitation',
            'official_letter',
            'assessment_report',
            'custom'
        ) DEFAULT 'custom'");
    }
};