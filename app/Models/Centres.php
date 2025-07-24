<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Centres extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'centre_id';

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'centre_id',
        'centre_name',
        'centre_address',
        'centre_phone',
        'centre_email',
        'centre_capacity',
        'centre_manager',
        'centre_manager_contact',
        'centre_status',
        'centre_description',
        'centre_facilities',
        'centre_image',
        'centre_latitude',
        'centre_longitude',
        'is_active'
    ];

    /**
     * Get the users associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(Users::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the trainees associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainees()
    {
        return $this->hasMany(Trainees::class, 'centre_name', 'centre_name');
    }

    /**
     * Get the courses associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Courses::class, 'location_id', 'centre_id');
    }

    /**
     * Get the assets associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the activities associated with this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'centre_id', 'centre_id');
    }

    /**
     * Scope a query to only include active centres.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'facilities' => 'array'
    ];

    /**
     * Get all centres as a key-value array for dropdown lists
     *
     * @param bool $activeOnly Whether to include only active centres
     * @return array
     */
    public static function getForDropdown($activeOnly = true)
    {
        $query = self::orderBy('centre_name');
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->pluck('centre_name', 'centre_id')->toArray();
    }

    /**
     * Get the default centre (Gombak)
     *
     * @return Centres|null
     */
    public static function getDefault()
    {
        return self::find('01');
    }
}