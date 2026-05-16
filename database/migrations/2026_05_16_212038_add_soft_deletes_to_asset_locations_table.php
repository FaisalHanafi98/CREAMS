<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the missing deleted_at column to asset_locations.
     *
     * The original migration (000005) included softDeletes() but the production
     * database was created before that line was present. The AssetLocation model
     * uses SoftDeletes, so queries append "AND deleted_at IS NULL", causing a
     * SQLSTATE[42S22] column-not-found error on any asset with a location_id set.
     *
     * This migration adds the column safely on databases that are missing it.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('asset_locations', 'deleted_at')) {
            Schema::table('asset_locations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('asset_locations', 'deleted_at')) {
            Schema::table('asset_locations', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
