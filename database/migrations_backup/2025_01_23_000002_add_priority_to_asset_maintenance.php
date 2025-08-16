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
        if (Schema::hasTable('asset_maintenance') && !Schema::hasColumn('asset_maintenance', 'priority')) {
            Schema::table('asset_maintenance', function (Blueprint $table) {
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('type');
                $table->index('priority');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('asset_maintenance') && Schema::hasColumn('asset_maintenance', 'priority')) {
            Schema::table('asset_maintenance', function (Blueprint $table) {
                $table->dropIndex(['priority']);
                $table->dropColumn('priority');
            });
        }
    }
};