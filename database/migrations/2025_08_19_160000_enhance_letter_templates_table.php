<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add missing columns to letter_templates table
     */
    public function up(): void
    {
        Schema::table('letter_templates', function (Blueprint $table) {
            // Check if columns don't already exist before adding them
            if (!Schema::hasColumn('letter_templates', 'header_image_path')) {
                $table->string('header_image_path')->nullable()->after('template_content');
            }
            if (!Schema::hasColumn('letter_templates', 'footer_image_path')) {
                $table->string('footer_image_path')->nullable()->after('header_image_path');
            }
            if (!Schema::hasColumn('letter_templates', 'header_text')) {
                $table->text('header_text')->nullable()->after('footer_image_path');
            }
            if (!Schema::hasColumn('letter_templates', 'footer_text')) {
                $table->text('footer_text')->nullable()->after('header_text');
            }
            if (!Schema::hasColumn('letter_templates', 'centre_id')) {
                $table->string('centre_id', 10)->nullable()->after('is_system_template');
            }
            if (!Schema::hasColumn('letter_templates', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('centre_id');
            }
            if (!Schema::hasColumn('letter_templates', 'usage_count')) {
                $table->integer('usage_count')->default(0)->after('created_by');
            }
            if (!Schema::hasColumn('letter_templates', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('usage_count');
            }
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
                'centre_id',
                'created_by',
                'usage_count',
                'last_used_at'
            ]);
        });
    }
};