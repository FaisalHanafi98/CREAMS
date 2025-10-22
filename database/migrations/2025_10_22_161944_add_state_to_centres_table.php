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
        Schema::table('centres', function (Blueprint $table) {
            $table->string('state')->nullable()->after('centre_name');
        });

        // Update existing centres with their states
        DB::table('centres')->where('centre_id', '01')->update(['state' => 'Selangor']); // Gombak
        DB::table('centres')->where('centre_id', '02')->update(['state' => 'Pahang']); // Kuantan
        DB::table('centres')->where('centre_id', '03')->update(['state' => 'Johor']); // Pagoh
        DB::table('centres')->where('centre_id', '04')->update(['state' => 'Pahang']); // Gambang
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
