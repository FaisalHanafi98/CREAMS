<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add missing columns to letters table
     */
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Check if columns don't already exist before adding them
            if (!Schema::hasColumn('letters', 'letter_reference')) {
                $table->string('letter_reference')->unique()->after('id');
            }
            if (!Schema::hasColumn('letters', 'letter_reference_number')) {
                $table->string('letter_reference_number')->nullable()->after('letter_reference');
            }
            if (!Schema::hasColumn('letters', 'letter_name')) {
                $table->string('letter_name')->nullable()->after('letter_reference_number');
            }
            if (!Schema::hasColumn('letters', 'letter_date')) {
                $table->date('letter_date')->after('letter_name');
            }
            if (!Schema::hasColumn('letters', 'letter_description')) {
                $table->text('letter_description')->nullable()->after('letter_title');
            }
            if (!Schema::hasColumn('letters', 'letter_subject')) {
                $table->string('letter_subject')->nullable()->after('letter_description');
            }
            if (!Schema::hasColumn('letters', 'recipient_address')) {
                $table->text('recipient_address')->nullable()->after('recipient_email');
            }
            if (!Schema::hasColumn('letters', 'recipient_type')) {
                $table->string('recipient_type')->default('external')->after('recipient_address');
            }
            if (!Schema::hasColumn('letters', 'recipient_organization')) {
                $table->string('recipient_organization')->nullable()->after('recipient_type');
            }
            if (!Schema::hasColumn('letters', 'purpose')) {
                $table->string('purpose')->nullable()->after('recipient_organization');
            }
            if (!Schema::hasColumn('letters', 'priority_level')) {
                $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('purpose');
            }
            if (!Schema::hasColumn('letters', 'letter_status')) {
                $table->string('letter_status')->default('draft')->after('priority_level');
            }
            if (!Schema::hasColumn('letters', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('letter_status');
            }
            if (!Schema::hasColumn('letters', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }
            if (!Schema::hasColumn('letters', 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            }
            if (!Schema::hasColumn('letters', 'sent_date')) {
                $table->date('sent_date')->nullable()->after('archived_by');
            }
            if (!Schema::hasColumn('letters', 'is_sent')) {
                $table->boolean('is_sent')->default(false)->after('sent_at');
            }
            if (!Schema::hasColumn('letters', 'delivery_method')) {
                $table->string('delivery_method')->nullable()->after('is_sent');
            }
            if (!Schema::hasColumn('letters', 'delivery_notes')) {
                $table->text('delivery_notes')->nullable()->after('delivery_method');
            }
            if (!Schema::hasColumn('letters', 'letter_file_path')) {
                $table->string('letter_file_path')->nullable()->after('delivery_notes');
            }
            if (!Schema::hasColumn('letters', 'letter_data')) {
                $table->json('letter_data')->nullable()->after('letter_file_path');
            }
            if (!Schema::hasColumn('letters', 'generation_metadata')) {
                $table->json('generation_metadata')->nullable()->after('letter_data');
            }
            if (!Schema::hasColumn('letters', 'generated_file_type')) {
                $table->string('generated_file_type')->default('pdf')->after('generation_metadata');
            }
            if (!Schema::hasColumn('letters', 'file_size_bytes')) {
                $table->unsignedInteger('file_size_bytes')->nullable()->after('generated_file_type');
            }
            if (!Schema::hasColumn('letters', 'pdf_filename')) {
                $table->string('pdf_filename')->nullable()->after('file_size_bytes');
            }
            if (!Schema::hasColumn('letters', 'pdf_file_size')) {
                $table->unsignedInteger('pdf_file_size')->nullable()->after('pdf_path');
            }
            // Only add created_by if it doesn't exist
            if (!Schema::hasColumn('letters', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('centre_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn([
                'letter_reference',
                'letter_reference_number',
                'letter_name',
                'letter_date',
                'letter_description', 
                'letter_subject',
                'recipient_address',
                'recipient_type',
                'recipient_organization',
                'purpose',
                'priority_level',
                'letter_status',
                'is_archived',
                'archived_at',
                'archived_by',
                'sent_date',
                'is_sent',
                'delivery_method',
                'delivery_notes',
                'letter_file_path',
                'letter_data',
                'generation_metadata',
                'generated_file_type',
                'file_size_bytes',
                'pdf_filename',
                'pdf_file_size'
            ]);
        });
    }
};