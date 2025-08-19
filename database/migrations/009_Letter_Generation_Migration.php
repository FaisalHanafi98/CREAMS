<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Letter Generation Module.
     * Creates: letter_templates, letters tables
     */
    public function up(): void
    {
        // 1. LETTER_TEMPLATES TABLE
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->text('template_description')->nullable();
            $table->enum('template_type', ['trainee', 'staff', 'general', 'certificate'])->default('general');
            $table->longText('template_content');
            $table->json('template_variables')->nullable();
            $table->string('created_by_role', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_template')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('template_type');
            $table->index('is_active');
            $table->index('is_system_template');
        });

        // 2. LETTERS TABLE
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_title');
            $table->integer('template_id')->nullable();
            $table->enum('letter_type', ['trainee', 'staff', 'general', 'certificate'])->default('general');
            $table->longText('letter_content');
            $table->integer('recipient_trainee_id')->nullable();
            $table->integer('recipient_user_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->integer('generated_by');
            $table->string('centre_id', 10)->nullable();
            $table->enum('status', ['draft', 'generated', 'sent', 'delivered'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('template_id');
            $table->index('letter_type');
            $table->index('recipient_trainee_id');
            $table->index('recipient_user_id');
            $table->index('generated_by');
            $table->index('centre_id');
            $table->index('status');
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