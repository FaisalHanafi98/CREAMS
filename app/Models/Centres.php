<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

class Centres extends Model
{
    use HasFactory;

    protected $primaryKey = 'centre_id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'centre_id',
        'centre_name',
        'centre_status',
        'status',
    ];

    /**
     * Boot function to add global scopes
     */
    protected static function boot()
    {
        parent::boot();
        
        // Add a global scope to handle either status column
        static::addGlobalScope('active', function (Builder $builder) {
            // Determine which status column to use
            $hasStatusColumn = Schema::hasColumn('centres', 'status');
            $hasCentreStatusColumn = Schema::hasColumn('centres', 'centre_status');
            
            if ($hasStatusColumn) {
                $builder->where('status', 'active');
            } elseif ($hasCentreStatusColumn) {
                $builder->where('centre_status', 'active');
            }
            
            // If neither column exists, don't apply any filtering
        });
        
        // Log when a center is created
        static::created(function ($model) {
            Log::info('Centre created', [
                'centre_id' => $model->centre_id,
                'centre_name' => $model->centre_name
            ]);
        });
        
        // Log when a center is updated
        static::updated(function ($model) {
            Log::info('Centre updated', [
                'centre_id' => $model->centre_id,
                'centre_name' => $model->centre_name
            ]);
        });
    }

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
     * Get the courses/activities conducted at this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Courses::class, 'location_id', 'centre_id');
    }

    /**
     * Get the assets belonging to this centre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Assets::class, 'centre_name', 'centre_name');
    }
    
    /**
     * Get all active centres
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        try {
            $hasStatusColumn = Schema::hasColumn('centres', 'status');
            $hasCentreStatusColumn = Schema::hasColumn('centres', 'centre_status');
            
            Log::info('Getting active centres', [
                'has_status_column' => $hasStatusColumn,
                'has_centre_status_column' => $hasCentreStatusColumn
            ]);
            
            if ($hasStatusColumn) {
                return self::where('status', 'active')->get();
            } elseif ($hasCentreStatusColumn) {
                return self::where('centre_status', 'active')->get();
            }
            
            // Fallback to all centres if no status column exists
            return self::all();
        } catch (\Exception $e) {
            Log::error('Error getting active centres', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return collect();
        }
    }
}