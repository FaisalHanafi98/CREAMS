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
        Schema::table('session_enrollments', function (Blueprint $table) {
            // Add transfer tracking fields
            $table->unsignedBigInteger('transferred_from_session')->nullable()->after('enrollment_notes')
                ->comment('ID of session from which this enrollment was transferred');
            
            // Add cancellation tracking fields
            $table->text('cancellation_reason')->nullable()->after('transferred_from_session')
                ->comment('Reason for enrollment cancellation');
            
            // Add foreign key constraint for transferred_from_session
            $table->foreign('transferred_from_session')->references('id')->on('activity_sessions')->onDelete('set null');
            
            // Add index for performance
            $table->index('transferred_from_session', 'idx_enrollment_transfer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_enrollments', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['transferred_from_session']);
            
            // Drop index
            $table->dropIndex('idx_enrollment_transfer');
            
            // Drop columns
            $table->dropColumn([
                'transferred_from_session',
                'cancellation_reason'
            ]);
        });
    }
};