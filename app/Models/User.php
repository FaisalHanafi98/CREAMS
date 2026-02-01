<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesPhoneNumbers;
use App\Rules\MalaysianPhoneRule;

class User extends Authenticatable
{
    use Notifiable, HasFactory, HandlesPhoneNumbers;

    protected $table = 'staffs'; // Migrated from users to staffs table

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iium_id',
        'name', 
        'email',
        'phone',
        'address',
        'position',
        'education_level',
        'education_specialization',
        'teaching_specialization',
        'avatar',
        'about',
        'date_of_birth',
    ];
    
    /**
     * The attributes that should be guarded from mass assignment.
     * These fields require explicit authorization to modify.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'password',             // Only allow password changes through specific methods
        'status',              // Status changes require admin approval
        'role',                // Role changes require admin approval
        'centre_id',           // Centre assignment requires admin approval
        'centre_location',     // Auto-derived from centre_id
        'last_accessed_at', // System-managed field
        'encrypted_id',        // System-generated field
        'email_verified_at',   // System-managed field
        'remember_token',      // System-managed field
        'created_at',
        'updated_at',
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
        'last_accessed_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    /**
     * Phone number mutator - normalize before saving
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = MalaysianPhoneRule::normalize($value);
    }

    /**
     * Phone number accessor - format for display
     */
    public function getPhoneFormattedAttribute()
    {
        return MalaysianPhoneRule::format($this->phone);
    }

    /**
     * Get the avatar URL for the user.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path('storage/avatars/' . $this->avatar))) {
            return asset('storage/avatars/' . $this->avatar) . '?v=' . filemtime(public_path('storage/avatars/' . $this->avatar));
        }
        
        // Return default avatar if none is set
        return asset('images/default-avatar.svg');
    }

    /**
     * Get suitable trainees for this staff member based on expertise and category
     */
    public function getSuitableTrainees($categoryId = null)
    {
        $query = Trainee::query();
        
        // Filter by centre
        if ($this->centre_id) {
            $query->where('centre_id', $this->centre_id);
        }
        
        // Match based on staff specialization and trainee condition
        if ($this->teaching_specialization) {
            $specializations = explode(',', $this->teaching_specialization);
            $specializations = array_map('trim', $specializations);
            
            $query->where(function($q) use ($specializations) {
                foreach ($specializations as $specialization) {
                    $q->orWhere('trainee_condition', 'like', '%' . $specialization . '%');
                }
            });
        }
        
        // Filter by category if specified
        if ($categoryId) {
            $query->whereHas('activities', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        
        return $query->get();
    }

    /**
     * Check if staff member is qualified for specific activity category
     */
    public function isQualifiedForCategory($categoryId)
    {
        $category = \App\Models\Category::find($categoryId);
        
        if (!$category) {
            return false;
        }
        
        // Map category types to required qualifications
        $qualificationMap = [
            'rehabilitation' => ['physical therapy', 'occupational therapy', 'physiotherapy', 'rehabilitation'],
            'academic' => ['education', 'teaching', 'academic', 'special education'],
            'creative_social' => ['arts', 'social work', 'psychology', 'creative', 'music', 'art'],
            'faith' => ['religious studies', 'islam', 'theology', 'faith', 'spiritual']
        ];
        
        $requiredQuals = $qualificationMap[$category->category_type] ?? [];
        
        // Check education specialization
        if ($this->education_specialization) {
            $eduSpecs = explode(',', strtolower($this->education_specialization));
            $eduSpecs = array_map('trim', $eduSpecs);
            
            foreach ($requiredQuals as $qual) {
                foreach ($eduSpecs as $spec) {
                    if (strpos($spec, $qual) !== false) {
                        return true;
                    }
                }
            }
        }
        
        // Check teaching specialization
        if ($this->teaching_specialization) {
            $teachSpecs = explode(',', strtolower($this->teaching_specialization));
            $teachSpecs = array_map('trim', $teachSpecs);
            
            foreach ($requiredQuals as $qual) {
                foreach ($teachSpecs as $spec) {
                    if (strpos($spec, $qual) !== false) {
                        return true;
                    }
                }
            }
        }
        
        // Admin and supervisors are qualified for all categories
        if (in_array($this->role, ['admin', 'supervisor'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Get recommended activities for this staff member based on expertise
     */
    public function getRecommendedActivities()
    {
        $query = \App\Models\Activity::where('centre_id', $this->centre_id);
        
        // Filter by staff qualifications
        if ($this->teaching_specialization || $this->education_specialization) {
            $query->whereHas('category', function($q) {
                $categories = \App\Models\Category::all();
                $qualifiedCategoryIds = [];
                
                foreach ($categories as $category) {
                    if ($this->isQualifiedForCategory($category->id)) {
                        $qualifiedCategoryIds[] = $category->id;
                    }
                }
                
                if (!empty($qualifiedCategoryIds)) {
                    $q->whereIn('id', $qualifiedCategoryIds);
                }
            });
        }
        
        return $query->where('is_active', true)->get();
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
     * Securely update user status (admin only).
     *
     * @param string $status
     * @param int $adminId
     * @return bool
     */
    public function updateStatus($status, $adminId)
    {
        $this->status = $status;
        $this->updated_by = $adminId;
        return $this->save();
    }
    
    /**
     * Securely update user role (admin only).
     *
     * @param string $role
     * @param int $adminId
     * @return bool
     */
    public function updateRole($role, $adminId)
    {
        $validRoles = ['admin', 'supervisor', 'teacher', 'ajk'];
        
        if (!in_array($role, $validRoles)) {
            return false;
        }
        
        $this->role = $role;
        $this->updated_by = $adminId;
        return $this->save();
    }
    
    /**
     * Securely assign user to centre (admin only).
     *
     * @param string $centreId
     * @param int $adminId
     * @return bool
     */
    public function assignToCentre($centreId, $adminId)
    {
        $this->centre_id = $centreId;
        $this->updated_by = $adminId;
        return $this->save();
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
     * Get formatted education information.
     *
     * @return string
     */
    public function getEducationInfoAttribute()
    {
        $parts = [];
        
        if ($this->education_level) {
            $parts[] = $this->education_level;
        }
        
        if ($this->education_specialization) {
            $parts[] = 'in ' . $this->education_specialization;
        }
        
        return !empty($parts) ? implode(' ', $parts) : 'Not specified';
    }

    /**
     * Get teaching specialization or education specialization as fallback.
     *
     * @return string
     */
    public function getSpecializationAttribute()
    {
        return $this->teaching_specialization ?? $this->education_specialization ?? 'Not specified';
    }

    /**
     * Check if user can teach (has teaching specialization).
     *
     * @return bool
     */
    public function canTeach()
    {
        return !empty($this->teaching_specialization) && in_array($this->role, ['teacher', 'supervisor']);
    }
    
    /**
     * Get the activities associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the trainees managed by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainees()
    {
        return $this->hasMany(Trainee::class);
    }

    /**
     * Get the assets managed by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get the staff attendance records for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staffAttendances()
    {
        return $this->hasMany(StaffAttendance::class, 'user_id');
    }

    /**
     * Get the attendance records marked by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function markedAttendances()
    {
        return $this->hasMany(StaffAttendance::class, 'marked_by_user_id');
    }
    
    /**
     * Get the centre this user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }
    
    /**
     * Get the centres managed by this admin/supervisor.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function centres()
    {
        if ($this->isAdmin()) {
            return $this->hasMany(Centre::class, 'admin_id');
        } elseif ($this->isSupervisor()) {
            return $this->hasMany(Centre::class, 'supervisor_id');
        }
        
        return $this->hasMany(Centre::class, 'user_id');
    }
    
    /**
     * Get the classes taught by this teacher.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classes()
    {
        if ($this->isTeacher()) {
            return $this->hasMany(ClassModel::class, 'teacher_id');
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
        return $this->hasMany(Course::class, 'teacher_id');
    }
    
    /**
     * Get the events organized by this user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        if ($this->isAJK()) {
            return $this->hasMany(Event::class, 'organizer_id');
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
        return $this->hasMany(Notification::class, 'user_id');
    }
    
    /**
     * Find a user by their email.
     *
     * @param string $email
     * @return User|null
     */
    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }
    
    /**
     * Find a user by their IIUM ID.
     *
     * @param string $iiumId
     * @return User|null
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
        $this->last_accessed_at = now();
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