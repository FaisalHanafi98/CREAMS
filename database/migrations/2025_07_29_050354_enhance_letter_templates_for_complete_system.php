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
        Schema::table('letter_templates', function (Blueprint $table) {
            // Add fields for header and footer images
            $table->string('header_image_path')->nullable()->after('template_content');
            $table->string('footer_image_path')->nullable()->after('header_image_path');
            
            // Add optional text overlays for header/footer
            $table->text('header_text')->nullable()->after('footer_image_path');
            $table->text('footer_text')->nullable()->after('header_text');
            
            // Add template description for better management
            $table->text('template_description')->nullable()->after('template_name');
            
            // Add centre_id for template scoping
            $table->string('centre_id')->nullable()->after('created_by');
            
            // Add usage count for analytics
            $table->integer('usage_count')->default(0)->after('is_active');
            
            // Add last used timestamp
            $table->timestamp('last_used_at')->nullable()->after('usage_count');
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
                'template_description',
                'centre_id',
                'usage_count',
                'last_used_at'
            ]);
        });
    }
};
