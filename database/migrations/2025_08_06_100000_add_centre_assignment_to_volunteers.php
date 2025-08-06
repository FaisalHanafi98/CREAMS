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
        Schema::table('volunteers', function (Blueprint $table) {
            $table->unsignedBigInteger('centre_id')->nullable()->after('volunteer_status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('centre_id');
            $table->text('admin_notes')->nullable()->after('approved_by');
            $table->timestamp('status_updated_at')->nullable()->after('admin_notes');
            
            // Add indexes for better performance
            $table->index(['centre_id']);
            $table->index(['approved_by']);
            $table->index(['volunteer_status', 'centre_id']);
            
            // Add foreign key constraints
            $table->foreign('centre_id')->references('id')->on('centres')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['centre_id']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['volunteer_status', 'centre_id']);
            $table->dropColumn(['centre_id', 'approved_by', 'admin_notes', 'status_updated_at']);
        });
    }
};