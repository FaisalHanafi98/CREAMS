<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_token',
        'device_type',
        'browser',
        'platform',
        'ip_address',
        'location',
        'last_activity',
        'is_current'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'is_current' => 'boolean'
    ];

    /**
     * Get the user that owns the session
     */
    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('last_activity', '>=', now()->subMinutes(30));
    }

    /**
     * Scope for current session
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope for expired sessions
     */
    public function scopeExpired($query, $timeoutMinutes = 30)
    {
        return $query->where('last_activity', '<', now()->subMinutes($timeoutMinutes));
    }

    /**
     * Create a new session record
     */
    public static function createSession($userId, $sessionToken, $deviceInfo = [])
    {
        // Mark all other sessions as not current
        static::where('user_id', $userId)->update(['is_current' => false]);

        return static::create([
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'device_type' => $deviceInfo['device_type'] ?? null,
            'browser' => $deviceInfo['browser'] ?? null,
            'platform' => $deviceInfo['platform'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? request()->ip(),
            'location' => $deviceInfo['location'] ?? null,
            'last_activity' => now(),
            'is_current' => true
        ]);
    }

    /**
     * Update session activity
     */
    public function updateActivity()
    {
        return $this->update(['last_activity' => now()]);
    }

    /**
     * Revoke session
     */
    public function revoke()
    {
        return $this->delete();
    }

    /**
     * Check if session is expired
     */
    public function isExpired($timeoutMinutes = 30)
    {
        return $this->last_activity < now()->subMinutes($timeoutMinutes);
    }

    /**
     * Get human readable last activity time
     */
    public function getLastActivityHumanAttribute()
    {
        return $this->last_activity->diffForHumans();
    }

    /**
     * Get formatted device info
     */
    public function getDeviceInfoAttribute()
    {
        $info = [];
        
        if ($this->browser) {
            $info[] = $this->browser;
        }
        
        if ($this->platform) {
            $info[] = $this->platform;
        }
        
        return implode(' on ', $info) ?: 'Unknown Device';
    }

    /**
     * Clean up expired sessions
     */
    public static function cleanupExpired($timeoutMinutes = 30)
    {
        return static::expired($timeoutMinutes)->delete();
    }

    /**
     * Get active sessions count for user
     */
    public static function getActiveSessionsCount($userId)
    {
        return static::where('user_id', $userId)->active()->count();
    }

    /**
     * Get all sessions for user with formatting
     */
    public static function getUserSessions($userId)
    {
        return static::where('user_id', $userId)
                    ->orderBy('last_activity', 'desc')
                    ->get()
                    ->map(function ($session) {
                        return [
                            'id' => $session->id,
                            'device_info' => $session->device_info,
                            'location' => $session->location,
                            'last_activity' => $session->last_activity_human,
                            'is_current' => $session->is_current,
                            'is_active' => !$session->isExpired()
                        ];
                    });
    }
}