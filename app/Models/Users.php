<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Users extends Authenticatable
{
    use Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */// Add 'centre_location' to the $fillable array in your Users model
    protected $fillable = [
        'iium_id',
        'name', 
        'email', 
        'password',
        'status',
        'role',
        'phone',
        'address',
        'position',
        'centre_id',
        'centre_location',
        'user_avatar',
        'user_activity_1',
        'user_activity_2',
        'user_last_accessed_at',
        'about',
        'review',
        'date_of_birth',
        'avatar',
        'bio'
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
        'user_last_accessed_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the avatar URL for the user.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        } elseif ($this->user_avatar) {
            return asset('storage/avatars/' . $this->user_avatar);
        }
        
        // Return default avatar if none is set
        return asset('images/default-avatar.png');
    }

    /**
     * Get role
     * 
     * @return string
     */
    public function getRole()
    {
        Log::debug('getRole method called on User model');
        return $this->role;
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return $this->role === $roleName;
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a supervisor.
     *
     * @return bool
     */
    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    /**
     * Check if the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if the user is an AJK.
     *
     * @return bool
     */
    public function isAJK()
    {
        return $this->role === 'ajk';
    }
    
    /**
     * Get the activities associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activities::class);
    }

    /**
     * Get the trainees managed by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainees()
    {
        return $this->hasMany(Trainees::class);
    }

    /**
     * Get the assets managed by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Assets::class);
    }
    
    /**
     * Get the centre this user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id');
    }
    
    /**
     * Get the centres managed by this admin/supervisor.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function centres()
    {
        if ($this->isAdmin()) {
            return $this->hasMany(Centres::class, 'admin_id');
        } elseif ($this->isSupervisor()) {
            return $this->hasMany(Centres::class, 'supervisor_id');
        }
        
        return $this->hasMany(Centres::class, 'user_id');
    }
    
    /**
     * Get the classes taught by this teacher.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classes()
    {
        if ($this->isTeacher()) {
            return $this->hasMany(Classes::class, 'teacher_id');
        }
        
        return null;
    }
    
    /**
     * Get the courses associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Courses::class, 'teacher_id');
    }
    
    /**
     * Get the events organized by this user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        if ($this->isAJK()) {
            return $this->hasMany(Events::class, 'organizer_id');
        }
        
        return null;
    }
    
    /**
     * Get the user's notifications.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notifications::class, 'user_id');
    }
    
    /**
     * Find a user by their email.
     *
     * @param string $email
     * @return Users|null
     */
    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }
    
    /**
     * Find a user by their IIUM ID.
     *
     * @param string $iiumId
     * @return Users|null
     */
    public static function findByIiumId($iiumId)
    {
        return self::where('iium_id', strtoupper($iiumId))->first();
    }
    
    /**
     * Find active users by role.
     * 
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByRole($role)
    {
        return self::where('role', $role)
            ->where('status', 'active')
            ->get();
    }
    
    /**
     * Update last login time.
     * 
     * @return void
     */
    public function updateLastLogin()
    {
        $this->user_last_accessed_at = now();
        $this->save();
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
        
        // Log when a user is created
        static::created(function ($model) {
            Log::info('User created', [
                'id' => $model->id,
                'iium_id' => $model->iium_id,
                'role' => $model->role
            ]);
        });
        
        // Log when a user is updated
        static::updated(function ($model) {
            Log::info('User updated', [
                'id' => $model->id,
                'iium_id' => $model->iium_id,
                'role' => $model->role
            ]);
        });
    }
}