<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add foreign key constraints after base tables are created.
     */
    public function up(): void
    {
        // Add foreign key constraint for users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });
    }
};