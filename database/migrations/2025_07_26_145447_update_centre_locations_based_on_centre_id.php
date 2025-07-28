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
        // Update centre_location based on centre_id mapping
        DB::table('users')->where('centre_id', '01')->update(['centre_location' => 'Gombak']);
        DB::table('users')->where('centre_id', '02')->update(['centre_location' => 'Pagoh']);
        DB::table('users')->where('centre_id', '03')->update(['centre_location' => 'Kuantan']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset centre_location to NULL for the affected records
        DB::table('users')->whereIn('centre_id', ['01', '02', '03'])
                         ->update(['centre_location' => null]);
    }
};
