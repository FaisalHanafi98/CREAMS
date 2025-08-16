<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Letter Generation System Module
     * Module: Letter Generation System
     * Priority: 009 - Medium (Document Management)
     */
    public function up(): void
    {
        // Letter templates for various document types
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->text('template_description')->nullable();
            $table->enum('template_type', [
                'recommendation',
                'completion_certificate',
                'progress_report',
                'invitation',
                'official_letter',
                'assessment_report',
                'custom'
            ])->default('custom');
            $table->longText('template_content'); // HTML/Blade template content
            $table->json('template_variables')->nullable(); // Available placeholders
            $table->string('template_header_image')->nullable();
            $table->string('template_footer_text')->nullable();
            $table->json('template_styling')->nullable(); // CSS styles
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('centre_id')->nullable(); // Centre-specific templates
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['template_type']);
            $table->index(['is_active']);
            $table->index(['centre_id']);
        });

        // Generated letters archive
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_reference_number')->unique(); // LTR/YYYY/MM/XXXXX
            $table->string('letter_name')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('letter_title');
            $table->text('letter_description')->nullable();
            $table->enum('letter_type', [
                'recommendation',
                'completion_certificate',
                'progress_report',
                'invitation',
                'official_letter',
                'assessment_report',
                'custom'
            ])->default('custom');
            $table->longText('letter_content'); // Final generated content
            $table->json('letter_data')->nullable(); // Data used for generation
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_address')->nullable();
            $table->enum('letter_status', ['draft', 'generated', 'sent', 'archived'])->default('generated');
            $table->string('pdf_filename')->nullable(); // Generated PDF file name
            $table->string('pdf_path')->nullable(); // Path to PDF file
            $table->integer('pdf_file_size')->nullable(); // File size in bytes
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('centre_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['letter_reference_number']);
            $table->index(['letter_type']);
            $table->index(['letter_status']);
            $table->index(['centre_id']);
            $table->index(['created_by']);
            $table->index(['generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_templates');
    }
};