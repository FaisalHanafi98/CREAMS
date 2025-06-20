<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RehabilitationObjectives extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rehabilitation_objectives';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_id',
        'description',
        'order'
    ];

    /**
     * Get the activity that owns this objective
     */
    public function activity()
    {
        return $this->belongsTo(RehabilitationActivities::class, 'activity_id');
    }
}