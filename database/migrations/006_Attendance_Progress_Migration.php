<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Attendance & Progress Module.
     * Creates: staff_attendances, attendance_alerts tables
     */
    public function up(): void
    {
        // 1. STAFF_ATTENDANCES TABLE
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('centre_id', 10)->nullable();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave'])->default('absent');
            $table->string('leave_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('approved')->default(false);
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->integer('marked_by_user_id')->nullable();
            $table->decimal('total_hours', 4, 2)->default(0.00);
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('centre_id');
            $table->index('attendance_date');
            $table->index('status');
            $table->index('approved');
        });

        // 2. ATTENDANCE_ALERTS TABLE
        Schema::create('attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('alert_type', ['staff', 'trainee']);
            $table->integer('user_id')->nullable();
            $table->integer('trainee_id')->nullable();
            $table->string('alert_message');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->integer('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('alert_type');
            $table->index('user_id');
            $table->index('trainee_id');
            $table->index('severity');
            $table->index('is_read');
            $table->index('is_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_alerts');
        Schema::dropIfExists('staff_attendances');
    }
};