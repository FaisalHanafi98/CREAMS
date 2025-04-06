<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainees extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trainees';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'trainee_first_name',
        'trainee_last_name',
        'trainee_email',
        'trainee_phone_number',
        'trainee_date_of_birth',
        'trainee_last_accessed_at',
        'trainee_avatar',
        'trainee_attendance',
        'trainee_condition',
        'centre_name',
        // Original fields for compatibility
        'name',
        'dob',
        'gender',
        'disability',
        'class',
        'centre_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trainee_date_of_birth' => 'date',
        'trainee_last_accessed_at' => 'datetime',
        'dob' => 'date',
    ];

    /**
     * The model's attributes that are dates.
     *
     * @var array
     */
    protected $dates = [
        'trainee_date_of_birth',
    ];

    /**
     * Get the centre that the trainee belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centres()
    {
        return $this->belongsTo(Centres::class, 'centre_name', 'centre_name');
    }

    /**
     * Alternative relationship for centre with different field name.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id');
    }

    /**
     * Get the user associated with this trainee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * Get the activities associated with this trainee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activities::class, 'trainee_id');
    }

    /**
     * Get the courses associated with this trainee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Courses::class, 'participant_id');
    }

    /**
     * Get the trainee's full name.
     *
     * @return string
     */
    public function getTraineeNameAttribute()
    {
        return $this->trainee_first_name . ' ' . $this->trainee_last_name;
    }
}