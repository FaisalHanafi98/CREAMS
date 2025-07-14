
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
public function up()
{
Schema::create('contact_messages', function (Blueprint $table) {
$table->id();
        // Contact Information
        $table->string('name');
        $table->string('email');    
        $table->string('phone')->nullable();
        $table->string('organization')->nullable();
        
        // Message Details
        $table->enum('reason', [
            'services',
            'support',
            'volunteer',
            'partnership',
            'general',
            'other',
            'admission',
            'complaint',
            'feedback'
        ]);
        $table->string('subject')->nullable();
        $table->text('message');
        $table->enum('urgency', ['low', 'medium', 'high', 'urgent'])->default('medium');
        $table->enum('preferred_contact_method', ['email', 'phone', 'both'])->default('email');
        
        // System fields
        $table->enum('status', ['new', 'read', 'in_progress', 'resolved', 'closed'])->default('new');
        $table->ipAddress('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->string('referrer')->nullable();
        $table->timestamp('submitted_at')->nullable();
        
        // Admin fields
        $table->unsignedBigInteger('assigned_to')->nullable();
        $table->text('admin_notes')->nullable();
        $table->timestamp('response_sent_at')->nullable();
        $table->timestamp('resolved_at')->nullable();
        
        $table->timestamps();
        
        // Foreign key constraints
        $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        
        // Indexes
        $table->index(['status', 'created_at']);
        $table->index(['urgency', 'status']);
        $table->index(['reason', 'status']);
        $table->index('submitted_at');
    });
}

public function down()
{
    Schema::dropIfExists('contact_messages');
}
};