<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class HandleSessionExpiration
{
    /**
     * Handle session expiration based on remember me status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in
        if (session('logged_in')) {
            $rememberMe = session('remember_me', false);
            $sessionId = session()->getId();
            
            Log::debug('Session expiration middleware', [
                'session_id' => $sessionId,
                'remember_me' => $rememberMe,
                'user_id' => session('id')
            ]);
            
            // If NOT remember me, set session to expire on browser close
            if (!$rememberMe) {
                // Dynamically set session cookie to expire when browser closes
                $cookieName = config('session.cookie');
                $sessionCookie = $request->cookie($cookieName);
                
                if ($sessionCookie) {
                    // Re-queue the session cookie with expire_on_close behavior
                    Cookie::queue(
                        $cookieName,
                        $sessionCookie,
                        0, // 0 means expire when browser closes
                        config('session.path'),
                        config('session.domain'),
                        config('session.secure'),
                        config('session.http_only'),
                        false,
                        config('session.same_site')
                    );
                    
                    Log::debug('Session cookie set to expire on browser close', [
                        'user_id' => session('id'),
                        'session_id' => $sessionId
                    ]);
                }
            } else {
                Log::debug('Remember me active - session will persist', [
                    'user_id' => session('id'),
                    'session_id' => $sessionId
                ]);
            }
        }

        return $next($request);
    }
}