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
        DB::statement("ALTER TABLE categories MODIFY COLUMN category_type ENUM('rehabilitation', 'academic', 'creative_social', 'faith') DEFAULT 'rehabilitation'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE categories MODIFY COLUMN category_type ENUM('rehabilitation', 'academic', 'creative_social') DEFAULT 'rehabilitation'");
    }
};
