<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SessionManager
{
    /**
     * Enhanced login with better session handling
     */
    public static function login($user, $remember = false)
    {
        // Clear any existing session
        Session::flush();
        Session::regenerate();
        
        // Set core session data
        $sessionData = [
            'id' => $user->id,
            'iium_id' => $user->iium_id ?? null,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'centre_id' => $user->centre_id,
            'logged_in' => true,
            'login_time' => now()->toDateTimeString(),
            'last_activity' => now()->timestamp
        ];
        
        // Add enhanced user data
        $enhancedData = self::getEnhancedUserData($user);
        $sessionData = array_merge($sessionData, $enhancedData);
        
        // Store in session
        foreach ($sessionData as $key => $value) {
            Session::put($key, $value);
        }
        
        // Handle remember me
        if ($remember) {
            $token = Str::random(60);
            $user->update(['remember_token' => $token]);
            
            Cookie::queue('remember_token', $token, 60 * 24 * 30); // 30 days
            Cookie::queue('user_id', $user->id, 60 * 24 * 30);
        }
        
        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);
        
        // Log successful login
        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'role' => $user->role,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        Session::save();
        
        return true;
    }
    
    /**
     * Enhanced logout with proper cleanup
     */
    public static function logout()
    {
        $userId = session('id');
        
        // Clear remember token
        if ($userId) {
            User::where('id', $userId)->update(['remember_token' => null]);
        }
        
        // Clear cookies
        Cookie::queue(Cookie::forget('remember_token'));
        Cookie::queue(Cookie::forget('user_id'));
        
        // Log logout before clearing session
        Log::info('User logged out', [
            'user_id' => $userId,
            'ip' => request()->ip()
        ]);
        
        // Clear session
        Session::flush();
        Session::regenerate();
        
        return true;
    }
    
    /**
     * Check if user is authenticated
     */
    public static function check()
    {
        // Check session
        if (Session::has('id') && Session::get('logged_in') === true) {
            // Update last activity
            Session::put('last_activity', now()->timestamp);
            return true;
        }
        
        // Check remember token
        if (Cookie::get('remember_token') && Cookie::get('user_id')) {
            $user = User::where('id', Cookie::get('user_id'))
                ->where('remember_token', Cookie::get('remember_token'))
                ->where('status', 'active')
                ->first();
            
            if ($user) {
                self::login($user, true);
                Log::info('User auto-logged in via remember token', [
                    'user_id' => $user->id
                ]);
                return true;
            } else {
                // Clear invalid cookies
                Cookie::queue(Cookie::forget('remember_token'));
                Cookie::queue(Cookie::forget('user_id'));
            }
        }
        
        return false;
    }
    
    /**
     * Get current authenticated user
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }
        
        $userId = session('id');
        if (!$userId) {
            return null;
        }
        
        // Cache user data in session to avoid repeated DB queries
        $sessionKey = 'user_data_' . $userId;
        if (!Session::has($sessionKey)) {
            $user = User::find($userId);
            if ($user) {
                Session::put($sessionKey, $user->toArray());
            }
            return $user;
        }
        
        $userData = Session::get($sessionKey);
        return new User($userData);
    }
    
    /**
     * Get enhanced user data
     */
    private static function getEnhancedUserData($user)
    {
        $data = [
            'avatar' => $user->avatar ?? null,
            'phone' => $user->phone ?? null,
            'address' => $user->address ?? null,
            'position' => $user->position ?? null,
            'department' => $user->department ?? null,
            'permissions' => []
        ];
        
        // Add role-specific permissions
        switch ($user->role) {
            case 'admin':
                $data['permissions'] = [
                    'all', 'user_management', 'system_settings', 
                    'reports', 'centre_management', 'asset_management'
                ];
                break;
            case 'supervisor':
                $data['permissions'] = [
                    'view_all_centre', 'edit_centre', 'manage_staff', 
                    'manage_trainees', 'manage_activities', 'view_reports'
                ];
                break;
            case 'teacher':
                $data['permissions'] = [
                    'view_assigned', 'manage_activities', 'manage_attendance',
                    'view_trainees', 'create_reports'
                ];
                break;
            case 'ajk':
                $data['permissions'] = [
                    'view_only', 'assist_activities', 'basic_reports'
                ];
                break;
            default:
                $data['permissions'] = ['view_only'];
                break;
        }
        
        return $data;
    }
    
    /**
     * Refresh session data
     */
    public static function refresh()
    {
        $userId = session('id');
        if (!$userId) {
            return false;
        }
        
        $user = User::find($userId);
        if (!$user) {
            self::logout();
            return false;
        }
        
        // Check if user is still active
        if ($user->status !== 'active') {
            self::logout();
            return false;
        }
        
        // Update session with fresh data
        $sessionData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'centre_id' => $user->centre_id,
            'last_activity' => now()->timestamp
        ];
        
        $enhancedData = self::getEnhancedUserData($user);
        $sessionData = array_merge($sessionData, $enhancedData);
        
        foreach ($sessionData as $key => $value) {
            Session::put($key, $value);
        }
        
        // Clear cached user data to force refresh
        Session::forget('user_data_' . $userId);
        
        Session::save();
        
        return true;
    }
    
    /**
     * Check if user has specific permission
     */
    public static function hasPermission($permission)
    {
        if (!self::check()) {
            return false;
        }
        
        $permissions = session('permissions', []);
        
        // Admin has all permissions
        if (in_array('all', $permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        if (!self::check()) {
            return false;
        }
        
        $userRole = session('role');
        
        if (is_array($role)) {
            return in_array($userRole, $role);
        }
        
        return $userRole === $role;
    }
    
    /**
     * Get session timeout in minutes
     */
    public static function getTimeoutMinutes()
    {
        return config('session.lifetime', 120);
    }
    
    /**
     * Check if session is about to expire (within 5 minutes)
     */
    public static function isAboutToExpire()
    {
        if (!self::check()) {
            return false;
        }
        
        $lastActivity = session('last_activity', 0);
        $timeoutSeconds = self::getTimeoutMinutes() * 60;
        $warningThreshold = 5 * 60; // 5 minutes warning
        
        return (time() - $lastActivity) > ($timeoutSeconds - $warningThreshold);
    }
    
    /**
     * Extend session lifetime
     */
    public static function extend()
    {
        if (self::check()) {
            Session::put('last_activity', now()->timestamp);
            Session::save();
            return true;
        }
        
        return false;
    }
}