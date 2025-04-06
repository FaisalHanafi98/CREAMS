<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\AuthenticationTrait;
use Illuminate\Support\Facades\Log;

class Supervisors extends Authenticatable
{
    use Notifiable, HasFactory, AuthenticationTrait;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iium_id',
        'name', 
        'email', 
        'password',
        'phone',
        'address',
        'position',
        'centre_id',
        'avatar',
        'bio',
        'user_last_accessed_at',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Direct role getter method as fallback if trait is not working
     * 
     * @return string
     */
    public function getRole()
    {
        Log::debug('Direct getRole method called on Supervisors model');
        return 'supervisor';
    }

    /**
     * Get the activities associated with this supervisor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activities::class);
    }

    /**
     * Get the trainees managed by this supervisor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainees()
    {
        return $this->hasMany(Trainees::class);
    }

    /**
     * Get the assets managed by this supervisor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Assets::class);
    }
    
    /**
     * Get the centre this supervisor belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id');
    }
    
    /**
     * Get the centres managed by this supervisor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function centres()
    {
        return $this->hasMany(Centres::class, 'supervisor_id');
    }
    
    /**
     * Find a supervisor by their email.
     *
     * @param string $email
     * @return Supervisors|null
     */
    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }
    
    /**
     * Find a supervisor by their IIUM ID.
     *
     * @param string $iiumId
     * @return Supervisors|null
     */
    public static function findByIiumId($iiumId)
    {
        return self::where('iium_id', strtoupper($iiumId))->first();
    }
    
    /**
     * Boot function to handle model events.
     * 
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // Convert IIUM ID to uppercase before saving
        static::saving(function ($model) {
            if (isset($model->iium_id)) {
                $model->iium_id = strtoupper($model->iium_id);
            }
        });
    }
}