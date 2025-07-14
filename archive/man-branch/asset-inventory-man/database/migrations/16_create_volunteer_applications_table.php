
<?php
// File: 16_3_create_volunteer_applications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
public function up()
{
Schema::create('volunteer_applications', function (Blueprint $table) {
$table->id();
        // Personal Information
        $table->string('name');
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('phone');
        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('postcode', 10)->nullable();
        
        // Volunteer Preferences
        $table->enum('interest', [
            'direct-support',
            'skills-sharing', 
            'event-support',
            'creative-arts',
            'administrative',
            'advocacy',
            'other'
        ]);
        $table->string('other_interest')->nullable();
        $table->text('skills')->nullable();
        $table->json('availability');
        $table->enum('commitment', ['1-3', '4-6', '7-10', 'flexible']);
        
        // Additional Information
        $table->text('motivation');
        $table->text('experience')->nullable();
        $table->enum('referral', [
            'website',
            'social-media',
            'friend',
            'event',
            'other'
        ])->nullable();
        
        // System fields
        $table->enum('status', ['pending', 'approved', 'rejected', 'contacted'])->default('pending');
        $table->string('centre_id', 10)->nullable(); // Changed to match your centres table
        $table->ipAddress('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('submitted_at')->nullable();
        
        // Admin fields
        $table->text('admin_notes')->nullable();
        $table->unsignedBigInteger('reviewed_by')->nullable();
        $table->timestamp('reviewed_at')->nullable();
        
        $table->timestamps();
        
        // Foreign key constraints - FIXED
        $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        
        // Indexes
        $table->index(['status', 'created_at']);
        $table->index(['interest', 'status']);
        $table->index('submitted_at');
    });
}

public function down()
{
    Schema::dropIfExists('volunteer_applications');
}
};