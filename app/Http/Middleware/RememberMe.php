<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;


class RememberMe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Debug at start of middleware
        $sessionId = session()->getId();
        $hasSessionId = session()->has('id');
        $sessionRole = session('role');
        
        Log::debug('RememberMe middleware starting', [
            'session_id' => $sessionId,
            'has_session_id' => $hasSessionId,
            'session_role' => $sessionRole,
            'url' => $request->fullUrl()
        ]);
        
        // If user is already logged in, no need to check remember token
        if (session('id') && session('role')) {
            Log::debug('User already logged in, skipping remember token check', [
                'user_id' => session('id'),
                'role' => session('role')
            ]);
            return $next($request);
        }
        
        // Check for remember token cookie
        $token = $request->cookie('remember_token');
        
        if ($token) {
            Log::info('Attempting to login with remember token', [
                'token_exists' => !empty($token),
                'token_length' => strlen($token)
            ]);
            
            // Try to find user with this remember token
            $user = Users::where('remember_token', $token)
                        ->where('status', 'active')
                        ->first();
            
            if ($user) {
                Log::info('User found with remember token', [
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'role' => $user->role
                ]);
                
                // Set session data for the remembered user
                session([
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'email' => $user->email,
                    'centre_id' => $user->centre_id,
                    'logged_in' => true,
                    'login_time' => now()->toDateTimeString(),
                    'login_method' => 'remember_token'
                ]);
                
                // Force session to be saved immediately
                session()->save();
                
                // Verify session was properly set
                $newSessionContainsId = session()->has('id');
                
                Log::info('User logged in using remember token', [
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'role' => $user->role,
                    'session_id' => session()->getId(),
                    'session_contains_id' => $newSessionContainsId
                ]);
                
                // Update last login
                $user-> user_last_accessed_at = now();
                $user->save();
            } else {
                Log::warning('Remember token not found or invalid', [
                    'token_partial' => substr($token, 0, 10) . '...'
                ]);
                
                // Clear invalid remember token cookie
                Cookie::queue(Cookie::forget('remember_token'));
            }
        }
        
        return $next($request);
    }
    
    /**
     * Get role name from user model (copy from MainController)
     * 
     * @param mixed $user
     * @return string
     */
    private function getRoleFromModel($user)
    {
        $className = get_class($user);
        
        if ($className === Admins::class) {
            return 'admin';
        } elseif ($className === Supervisors::class) {
            return 'supervisor';
        } elseif ($className === Teachers::class) {
            return 'teacher';
        } elseif ($className === AJKs::class) {
            return 'ajk';
        }
        
        // Default fallback (extract from class name)
        $baseName = strtolower(class_basename($className));
        return rtrim($baseName, 's');
    }
}