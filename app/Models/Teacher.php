<?php

namespace App\Models;

use App\Models\Scopes\CentreScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\AuthenticationTrait;
use Illuminate\Support\Facades\Log;

class Teacher extends Authenticatable
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

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
        'about',
        'last_accessed_at',
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
        Log::debug('Direct getRole method called on Teacher model');
        return 'teacher';
    }

    /**
     * Get the activities associated with this teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the trainees managed by this teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainees()
    {
        return $this->hasMany(Trainee::class);
    }

    /**
     * Get the assets managed by this teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
    
    /**
     * Get the centre this teacher belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }
    
    /**
     * Find a teacher by their email.
     *
     * @param string $email
     * @return Teacher|null
     */
    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }
    
    /**
     * Find a teacher by their IIUM ID.
     *
     * @param string $iiumId
     * @return Teacher|null
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