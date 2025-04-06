<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Add detailed debug logging
        Log::info('Authenticate middleware check', [
            'session_id' => session()->getId(),
            'has_session_id' => session()->has('id'),
            'session_id_value' => session('id'),
            'has_role' => session()->has('role'),
            'role_value' => session('role'),
            'url' => $request->fullUrl(),
            'all_session' => session()->all()
        ]);
        
        // Simple check for session ID
        if (!session()->has('id') || !session('id') || !session()->has('role') || !session('role')) {
            Log::warning('Unauthenticated access attempt', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('auth.loginpage')->with('error', 'Please log in to access this page');
        }
        
        // User is authenticated, proceed
        return $next($request);
    }
}