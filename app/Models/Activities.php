<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activities extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'date',
        'trainee_id',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function trainee()
    {
        return $this->belongsTo(Trainees::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
