<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration is now handled in the previous migration (2025_08_02_060134)
        // No action needed here as ENUM update is done when faith categories are added
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed here as ENUM revert is handled in the previous migration rollback
    }
};
