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
        Schema::table('assets', function (Blueprint $table) {
            // Add assigned_to_id column to existing assets table for backward compatibility
            $table->unsignedBigInteger('assigned_to_id')->nullable()->after('asset_note');
            
            // Add foreign key constraint
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
            
            // Add index for performance
            $table->index(['assigned_to_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_id']);
            $table->dropIndex(['assigned_to_id']);
            $table->dropColumn('assigned_to_id');
        });
    }
};