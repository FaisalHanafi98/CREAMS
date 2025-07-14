<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'start_time',
        'end_time',
        'location',
        'description',
        'organizer',
        'contact_person',
        'contact_email',
        'contact_phone',
        'max_participants',
        'status',
        'centre_id'
    ];

    protected $casts = [
        'date' => 'date',
        'schedule' => 'array'
    ];

    /**
     * Get the centre that owns the event
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class);
    }

    /**
     * Get the participants for the event
     */
    public function participants()
    {
        return $this->belongsToMany(Users::class, 'event_participants')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    /**
     * Get the volunteers for the event
     */
    public function volunteers()
    {
        return $this->belongsToMany(Users::class, 'event_volunteers')
                    ->withPivot('role', 'status')
                    ->withTimestamps();
    }
}