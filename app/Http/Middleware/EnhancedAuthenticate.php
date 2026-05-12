<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\SessionManager;
use Illuminate\Support\Facades\Log;

class EnhancedAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Check authentication
        if (!SessionManager::check()) {
            $this->logUnauthorizedAccess($request);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Please login to continue',
                    'redirect' => route('auth.loginpage')
                ], 401);
            }
            
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please login to continue')
                ->with('intended_url', $request->fullUrl());
        }
        
        // Check session timeout (configurable, default 30 minutes)
        $timeoutMinutes = config('session.lifetime', 120);
        $lastActivity = session('last_activity', 0);
        
        if (time() - $lastActivity > ($timeoutMinutes * 60)) {
            $this->logSessionTimeout($request);
            SessionManager::logout();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Session Expired',
                    'message' => 'Your session has expired. Please login again',
                    'redirect' => route('auth.loginpage')
                ], 401);
            }
            
            return redirect()->route('auth.loginpage')
                ->with('error', 'Session expired. Please login again')
                ->with('intended_url', $request->fullUrl());
        }
        
        // Refresh session activity
        session(['last_activity' => time()]);
        
        // Check if user still exists and is active
        if (session('id')) {
            $user = \App\Models\User::find(session('id'));
            if (!$user || $user->status !== 'active') {
                $this->logInactiveUser($request, session('id'));
                SessionManager::logout();
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Account Inactive',
                        'message' => 'Your account is no longer active',
                        'redirect' => route('auth.loginpage')
                    ], 401);
                }
                
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Account not found or inactive');
            }
            
            // Refresh session data if needed (every 10 minutes)
            $lastRefresh = session('last_refresh', 0);
            if (time() - $lastRefresh > 600) { // 10 minutes
                SessionManager::refresh();
                session(['last_refresh' => time()]);
            }
        }
        
        // Add user data to request for easy access
        $request->merge([
            'auth_user_id' => session('id'),
            'auth_user_role' => session('role'),
            'auth_user_centre' => session('centre_id')
        ]);
        
        return $next($request);
    }
    
    /**
     * Log unauthorized access attempt
     */
    private function logUnauthorizedAccess($request)
    {
        Log::warning('Unauthorized access attempt', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer')
        ]);
    }
    
    /**
     * Log session timeout
     */
    private function logSessionTimeout($request)
    {
        Log::info('Session timeout', [
            'user_id' => session('id'),
            'url' => $request->fullUrl(),
            'last_activity' => session('last_activity'),
            'ip' => $request->ip()
        ]);
    }
    
    /**
     * Log inactive user access attempt
     */
    private function logInactiveUser($request, $userId)
    {
        Log::warning('Inactive user access attempt', [
            'user_id' => $userId,
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);
    }
}