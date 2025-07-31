<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionAttendance extends Model
{
    use HasFactory;

    protected $table = 'session_attendance';

    protected $fillable = [
        'session_id',
        'trainee_id',
        'attended',
        'recorded_by',
        'recorded_at',
        'notes'
    ];

    protected $casts = [
        'attended' => 'boolean',
        'recorded_at' => 'datetime'
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(ActivitySession::class, 'session_id');
    }

    public function trainee()
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
