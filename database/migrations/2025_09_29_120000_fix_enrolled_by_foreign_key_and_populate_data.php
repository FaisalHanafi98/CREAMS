<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix enrolled_by field in activity_enrollments table
     * - Change from int to unsignedBigInteger
     * - Add proper foreign key constraint
     * - Populate existing NULL values with system admin
     */
    public function up(): void
    {
        // First, get the admin user ID for populating existing records
        $adminUser = DB::table('staffs')
            ->where('role', 'admin')
            ->where('status', 'active')
            ->first();

        if (!$adminUser) {
            // Create a system admin if none exists
            $adminUserId = DB::table('staffs')->insertGetId([
                'name' => 'System Administrator',
                'email' => 'admin@creams.system',
                'password' => bcrypt(uniqid('', true)),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '',
                'centre_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $adminUserId = $adminUser->id;
        }

        // Update the enrolled_by field structure and populate data
        Schema::table('activity_enrollments', function (Blueprint $table) {
            // Change enrolled_by from int to unsignedBigInteger
            $table->unsignedBigInteger('enrolled_by')->nullable()->change();
        });

        // Populate NULL enrolled_by values with admin user
        DB::table('activity_enrollments')
            ->whereNull('enrolled_by')
            ->update(['enrolled_by' => $adminUserId]);

        // Add foreign key constraint
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->foreign('enrolled_by')
                  ->references('id')
                  ->on('staffs')
                  ->onDelete('set null')
                  ->name('fk_activity_enrollments_enrolled_by');

            // Add index for better performance
            $table->index('enrolled_by', 'idx_activity_enrollments_enrolled_by');
        });

        // Add column comment on MySQL only (SQLite does not support MODIFY COLUMN syntax)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE activity_enrollments MODIFY COLUMN enrolled_by BIGINT UNSIGNED NULL COMMENT 'Foreign key to staffs table - tracks which staff member enrolled the trainee'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropForeign('fk_activity_enrollments_enrolled_by');
            $table->dropIndex('idx_activity_enrollments_enrolled_by');

            // Revert to int type
            $table->integer('enrolled_by')->nullable()->change();
        });
    }
};