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
        Schema::table('users', function (Blueprint $table) {
            // Add expertise and qualification fields for staff-trainee matching
            $table->integer('experience_years')->nullable()->after('teaching_specialization');
            $table->text('certifications')->nullable()->after('experience_years');
            $table->json('disability_expertise')->nullable()->after('certifications');
            $table->json('age_group_preference')->nullable()->after('disability_expertise');
            $table->text('additional_skills')->nullable()->after('age_group_preference');
            $table->decimal('qualification_score', 5, 2)->default(0)->after('additional_skills');
            
            // Add indexes for better query performance
            $table->index(['education_specialization', 'teaching_specialization'], 'specialization_index');
            $table->index(['role', 'centre_id'], 'role_centre_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('specialization_index');
            $table->dropIndex('role_centre_index');
            $table->dropColumn([
                'experience_years',
                'certifications',
                'disability_expertise',
                'age_group_preference',
                'additional_skills',
                'qualification_score'
            ]);
        });
    }
};
