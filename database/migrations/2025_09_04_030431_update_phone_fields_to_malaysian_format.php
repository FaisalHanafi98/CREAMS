<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Rules\MalaysianPhoneRule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Staffs table phone field
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('phone', 20)->change();
        });

        // Update Trainees table phone fields  
        Schema::table('trainees', function (Blueprint $table) {
            $table->string('trainee_phone_number', 20)->change();
            $table->string('guardian_phone', 20)->nullable()->change();
            $table->string('emergency_contact_phone', 20)->nullable()->change();
        });

        // Normalize existing phone data
        $this->normalizeExistingPhoneData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert field lengths (optional - depends on original schema)
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('phone', 255)->change();
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->string('trainee_phone_number', 255)->change();
            $table->string('guardian_phone', 255)->nullable()->change();
            $table->string('emergency_contact_phone', 255)->nullable()->change();
        });
    }

    /**
     * Normalize existing phone data to Malaysian format
     */
    private function normalizeExistingPhoneData()
    {
        // Update Staffs table
        $users = DB::table('staffs')->whereNotNull('phone')->get();
        foreach ($users as $user) {
            if (!empty($user->phone)) {
                $normalized = MalaysianPhoneRule::normalize($user->phone);
                if ($normalized !== $user->phone) {
                    DB::table('staffs')
                        ->where('id', $user->id)
                        ->update(['phone' => $normalized]);
                }
            }
        }

        // Update Trainees table
        $trainees = DB::table('trainees')->get();
        foreach ($trainees as $trainee) {
            $updates = [];

            // Normalize trainee phone
            if (!empty($trainee->trainee_phone_number)) {
                $normalized = MalaysianPhoneRule::normalize($trainee->trainee_phone_number);
                if ($normalized !== $trainee->trainee_phone_number) {
                    $updates['trainee_phone_number'] = $normalized;
                }
            }

            // Normalize guardian phone
            if (!empty($trainee->guardian_phone)) {
                $normalized = MalaysianPhoneRule::normalize($trainee->guardian_phone);
                if ($normalized !== $trainee->guardian_phone) {
                    $updates['guardian_phone'] = $normalized;
                }
            }

            // Normalize emergency contact phone
            if (!empty($trainee->emergency_contact_phone)) {
                $normalized = MalaysianPhoneRule::normalize($trainee->emergency_contact_phone);
                if ($normalized !== $trainee->emergency_contact_phone) {
                    $updates['emergency_contact_phone'] = $normalized;
                }
            }

            // Apply updates if any
            if (!empty($updates)) {
                DB::table('trainees')
                    ->where('id', $trainee->id)
                    ->update($updates);
            }
        }

        echo "Phone number normalization completed for all existing records.\n";
    }
};
