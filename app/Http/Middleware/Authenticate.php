<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

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
        // Minimal auth check logging — no PII or full session dumps (PDPA)
        Log::debug('Authenticate middleware', [
            'has_session' => session()->has('id'),
            'role' => session('role'),
            'url' => $request->path(),
        ]);
        
        // Simple check for session ID
        if (!session()->has('id') || !session('id') || !session()->has('role') || !session('role')) {
            Log::warning('Unauthenticated access attempt', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'session_id' => session()->getId()
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Please log in to access this resource'
                ], 401);
            }

            return redirect()->route('auth.loginpage')->with('error', 'Please log in to access this page');
        }
        
        // Verify the user exists in the User table
        try {
            $userId = session('id');
            $user = User::find($userId);
            
            if (!$user) {
                Log::warning('Session contains user ID that does not exist in database', [
                    'session_user_id' => $userId,
                    'url' => $request->fullUrl()
                ]);
                
                // Clear invalid session data
                session()->forget(['id', 'role']);
                return redirect()->route('auth.loginpage')->with('error', 'Your session has expired. Please log in again.');
            }
            
            // Ensure role in session matches user's actual role
            if (session('role') !== $user->role) {
                Log::warning('Role mismatch between session and database', [
                    'session_role' => session('role'),
                    'db_role' => $user->role,
                    'user_id' => $userId
                ]);
                
                // Update session with correct role
                session(['role' => $user->role]);
            }
        } catch (\Exception $e) {
            Log::error('Error verifying user in Authenticate middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // User is authenticated, proceed
        return $next($request);
    }
}