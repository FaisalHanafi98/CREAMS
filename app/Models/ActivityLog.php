<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'centre_id',
        'user_id',
        'user_name',
        'user_role',
        'action_type',
        'model_type',
        'model_id',
        'title',
        'description',
        'metadata',
        'icon',
        'status'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by centre
     */
    public function scopeForCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope to get recent activities
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Static helper to log an activity
     */
    public static function log($data)
    {
        return self::create([
            'centre_id' => $data['centre_id'] ?? session('centre_id'),
            'user_id' => $data['user_id'] ?? session('id'),
            'user_name' => $data['user_name'] ?? session('name'),
            'user_role' => $data['user_role'] ?? session('role'),
            'action_type' => $data['action_type'], // created, updated, deleted
            'model_type' => $data['model_type'],
            'model_id' => $data['model_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'icon' => $data['icon'] ?? 'circle',
            'status' => $data['status'] ?? 'info'
        ]);
    }
}
