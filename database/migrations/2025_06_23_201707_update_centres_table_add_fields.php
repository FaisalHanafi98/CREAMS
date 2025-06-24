<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            // Add missing fields
            $table->string('address')->nullable()->after('centre_name');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postcode', 10)->nullable()->after('state');
            $table->string('phone', 20)->nullable()->after('postcode');
            $table->string('email')->nullable()->after('phone');
            $table->integer('capacity')->default(50)->after('email');
            $table->text('description')->nullable()->after('capacity');
            $table->json('facilities')->nullable()->after('description');
            $table->time('opening_time')->default('08:00')->after('facilities');
            $table->time('closing_time')->default('17:00')->after('opening_time');
            
            // Change centre_status to boolean
            $table->boolean('is_active')->default(true)->after('closing_time');
        });
        
        // Migrate existing centre_status to is_active
        DB::statement("UPDATE centres SET is_active = CASE WHEN centre_status = 'active' THEN 1 ELSE 0 END");
        
        // Drop old column
        Schema::table('centres', function (Blueprint $table) {
            $table->dropColumn('centre_status');
        });
    }

    public function down(): void
    {
        Schema::table('centres', function (Blueprint $table) {
            $table->string('centre_status')->after('centre_name');
            $table->dropColumn([
                'address', 'city', 'state', 'postcode', 'phone', 'email',
                'capacity', 'description', 'facilities', 'opening_time', 
                'closing_time', 'is_active'
            ]);
        });
    }
};