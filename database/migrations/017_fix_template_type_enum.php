<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Fix template_type enum values
     * Module: Letter Generation System (Template Type Fix)
     * Priority: 017 - Critical (Fix template saving error)
     */
    public function up(): void
    {
        // Modify the template_type enum to include 'letter' and other missing values
        DB::statement("ALTER TABLE letter_templates MODIFY COLUMN template_type ENUM(
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
            'custom'
        ) DEFAULT 'custom'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE letter_templates MODIFY COLUMN template_type ENUM(
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