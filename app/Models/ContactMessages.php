<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessages extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',    
        'phone',
        'reason',
        'message',
        'status',
        'ip_address',
        'user_agent'
    ];

    // Relationship with the user assigned to handle this message
    public function assignedUser()
    {
        return $this->belongsTo(Users::class, 'assigned_to');
    }
}