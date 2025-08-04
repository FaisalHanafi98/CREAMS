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
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Add cancellation tracking fields (status already exists)
            $table->text('cancellation_reason')->nullable()->after('status')
                ->comment('Reason for session cancellation');
            
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancellation_reason')
                ->comment('ID of user who cancelled the session');
            
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by')
                ->comment('Timestamp when session was cancelled');
            
            // Add modification tracking fields
            $table->unsignedBigInteger('last_modified_by')->nullable()->after('cancelled_at')
                ->comment('ID of user who last modified the session');
            
            $table->text('modification_notes')->nullable()->after('last_modified_by')
                ->comment('Notes about session modifications');
            
            // Add foreign key constraints
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('last_modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['cancelled_by']);
            $table->dropForeign(['last_modified_by']);
            
            // Drop columns
            $table->dropColumn([
                'cancellation_reason',
                'cancelled_by',
                'cancelled_at',
                'last_modified_by',
                'modification_notes'
            ]);
        });
    }
};
