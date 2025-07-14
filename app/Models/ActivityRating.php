<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'user_id',
        'trainee_id',
        'rating',
        'review',
        'rating_type'
    ];

    protected $casts = [
        'rating' => 'decimal:1'
    ];

    /**
     * Get the activity that was rated
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the user who made the rating
     */
    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * Get the trainee for whom the rating was made (if applicable)
     */
    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    /**
     * Scope for getting ratings by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('rating_type', $type);
    }

    /**
     * Scope for getting ratings for specific activity
     */
    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    /**
     * Calculate average rating for an activity
     */
    public static function calculateAverageRating($activityId)
    {
        return static::where('activity_id', $activityId)->avg('rating');
    }

    /**
     * Get rating statistics for an activity
     */
    public static function getRatingStats($activityId)
    {
        $ratings = static::where('activity_id', $activityId);
        
        return [
            'average' => round($ratings->avg('rating'), 1),
            'total' => $ratings->count(),
            'breakdown' => $ratings->selectRaw('rating, COUNT(*) as count')
                                  ->groupBy('rating')
                                  ->orderBy('rating', 'desc')
                                  ->get()
                                  ->pluck('count', 'rating')
                                  ->toArray()
        ];
    }
}