<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Fix Letter System Database Columns
     * Module: Letter Generation System (Fix)
     * Priority: 016 - Critical (Fix letter generation errors)
     */
    public function up(): void
    {
        // Fix letters table - add letter_reference column as alias for letter_reference_number
        Schema::table('letters', function (Blueprint $table) {
            // Add letter_reference column that the code expects
            $table->string('letter_reference')->nullable()->after('letter_reference_number');
            $table->index(['letter_reference']);
        });

        // Update existing records to copy letter_reference_number to letter_reference
        DB::statement('UPDATE letters SET letter_reference = letter_reference_number WHERE letter_reference IS NULL');

        // Fix letter_templates table - add missing columns
        Schema::table('letter_templates', function (Blueprint $table) {
            // Add the missing image and text columns
            $table->string('header_image_path')->nullable()->after('template_variables');
            $table->string('footer_image_path')->nullable()->after('header_image_path');
            $table->text('header_text')->nullable()->after('footer_image_path');
            $table->text('footer_text')->nullable()->after('header_text');
            $table->timestamp('last_used_at')->nullable()->after('footer_text');
            $table->integer('usage_count')->default(0)->after('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->dropColumn([
                'header_image_path',
                'footer_image_path', 
                'header_text',
                'footer_text',
                'last_used_at',
                'usage_count'
            ]);
        });

        Schema::table('letters', function (Blueprint $table) {
            $table->dropIndex(['letter_reference']);
            $table->dropColumn('letter_reference');
        });
    }
};