<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class RehabilitationActivities extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rehabilitation_activities';
    
    // Enable mass assignment protection
    protected $fillable = [
        'name', 'category', 'short_description', 'full_description',
        'difficulty_level', 'age_range', 'activity_type', 'duration',
        'max_participants', 'lower_adaptations', 'higher_adaptations',
        'progress_metrics', 'notes', 'status', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'duration' => 'integer',
        'max_participants' => 'integer',
    ];

    // Optimize default query to exclude soft deleted
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });
    }

    // Relationships with optimized queries
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by')
                    ->select(['id', 'name', 'email']);
    }

    public function updater()
    {
        return $this->belongsTo(Users::class, 'updated_by')
                    ->select(['id', 'name', 'email']);
    }

    public function objectives()
    {
        return $this->hasMany(RehabilitationObjectives::class, 'activity_id')
                    ->orderBy('order');
    }

    // Scopes for better query performance
    public function scopePublished(Builder $query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory(Builder $query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDifficulty(Builder $query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function scopeRecentFirst(Builder $query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeWithBasicRelations(Builder $query)
    {
        return $query->with(['creator:id,name']);
    }

    // Cache frequently accessed data
    public function getCategoryCountAttribute()
    {
        return Cache::remember(
            "category_count_{$this->category}",
            3600, // 1 hour
            fn() => static::where('category', $this->category)
                         ->where('status', 'published')
                         ->count()
        );
    }

    // Full-text search method
    public static function search($query)
    {
        return static::whereRaw(
            "MATCH(name, short_description, full_description) AGAINST(? IN BOOLEAN MODE)",
            ["+{$query}*"]
        );
    }

    // Bulk operations for better performance
    public static function bulkUpdateStatus($ids, $status)
    {
        return static::whereIn('id', $ids)->update([
            'status' => $status,
            'updated_at' => now()
        ]);
    }

    // Get popular categories with caching
    public static function getPopularCategories($limit = 6)
    {
        return Cache::remember('popular_categories', 3600, function() use ($limit) {
            return static::select('category', 
                                \DB::raw('COUNT(*) as count'))
                         ->where('status', 'published')
                         ->groupBy('category')
                         ->orderBy('count', 'desc')
                         ->limit($limit)
                         ->get();
        });
    }
}