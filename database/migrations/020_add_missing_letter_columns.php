<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing columns to letters table
     * Module: Letter Generation System (Missing Columns Fix)
     * Priority: 020 - Critical (Fix letter generation error)
     */
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Add missing recipient columns
            $table->unsignedBigInteger('recipient_id')->nullable()->after('recipient_name');
            $table->enum('recipient_type', ['trainee', 'external', 'staff', 'organization'])->default('external')->after('recipient_address');
            $table->string('recipient_organization')->nullable()->after('recipient_type');
            
            // Add missing letter properties
            $table->string('purpose')->nullable()->after('recipient_organization');
            $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('purpose');
            
            // Add missing archiving columns
            $table->boolean('is_archived')->default(false)->after('letter_status');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            
            // Add missing sending columns
            $table->date('sent_date')->nullable()->after('sent_at');
            $table->boolean('is_sent')->default(false)->after('sent_date');
            $table->enum('delivery_method', ['email', 'post', 'hand_delivery', 'digital'])->default('email')->after('is_sent');
            
            // Add missing file columns
            $table->string('letter_file_path')->nullable()->after('delivery_notes');
            $table->json('generation_metadata')->nullable()->after('letter_data');
            $table->string('generated_file_type')->nullable()->after('generation_metadata');
            $table->integer('file_size_bytes')->nullable()->after('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_id',
                'recipient_type', 
                'recipient_organization',
                'purpose',
                'priority_level',
                'is_archived',
                'archived_at',
                'archived_by',
                'sent_date',
                'is_sent',
                'delivery_method',
                'letter_file_path',
                'generation_metadata',
                'generated_file_type',
                'file_size_bytes'
            ]);
        });
    }
};