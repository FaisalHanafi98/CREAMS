<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  One or more allowed roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // Debug logging at entry point
        $sessionId = session()->getId();
        
        Log::info('Role middleware check', [
            'required_roles' => $roles,
            'user_role' => session('role'),
            'session_id' => $sessionId,
            'has_session_id' => session()->has('id'),
            'url' => $request->fullUrl()
        ]);
        
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            Log::warning('Unauthenticated access attempt in Role middleware', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'session_id' => $sessionId,
                'has_session_id' => session()->has('id'),
                'session_role' => session('role')
            ]);
            
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Get user role from session
        $userRole = session('role');
        
        // If no roles specified, or role matches one of the allowed roles
        if (empty($roles) || in_array($userRole, $roles)) {
            Log::debug('Role middleware passed', [
                'user_id' => session('id'),
                'role' => $userRole,
                'required_roles' => $roles
            ]);
            return $next($request);
        }
        
        // Log unauthorized attempt
        Log::warning('Unauthorized role access attempt', [
            'required_roles' => $roles,
            'user_role' => $userRole,
            'user_id' => session('id'),
            'url' => $request->fullUrl()
        ]);
        
        // Redirect to appropriate dashboard based on user's role
        return redirect()->route($userRole . '.dashboard')
            ->with('warning', 'You do not have permission to access that page');
    }
}