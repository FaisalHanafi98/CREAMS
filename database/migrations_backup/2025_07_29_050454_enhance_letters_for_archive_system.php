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
        Schema::table('letters', function (Blueprint $table) {
            // Add centre_id for proper data segregation
            $table->string('centre_id')->nullable()->after('created_by');
            
            // Add archive status
            $table->boolean('is_archived')->default(false)->after('letter_status');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            
            // Add letter generation metadata
            $table->text('generation_metadata')->nullable()->after('letter_data'); // JSON field for extra data
            $table->string('generated_file_type')->default('pdf')->after('generation_metadata'); // pdf, html, etc.
            $table->integer('file_size_bytes')->nullable()->after('generated_file_type');
            
            // Add audit fields
            $table->string('recipient_organization')->nullable()->after('recipient_type');
            $table->text('purpose')->nullable()->after('recipient_organization'); // Purpose of the letter
            $table->string('priority_level')->default('normal')->after('purpose'); // urgent, normal, low
            
            // Add tracking fields
            $table->boolean('is_sent')->default(false)->after('sent_date');
            $table->string('delivery_method')->nullable()->after('is_sent'); // email, post, hand_delivery
            $table->text('delivery_notes')->nullable()->after('delivery_method');
            
            // Foreign key constraints
            $table->foreign('archived_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn([
                'centre_id',
                'is_archived',
                'archived_at', 
                'archived_by',
                'generation_metadata',
                'generated_file_type',
                'file_size_bytes',
                'recipient_organization',
                'purpose',
                'priority_level',
                'is_sent',
                'delivery_method',
                'delivery_notes'
            ]);
        });
    }
};
