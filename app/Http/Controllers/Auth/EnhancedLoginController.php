<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EnhancedLoginController extends Controller
{
    /**
     * Show enhanced login form
     */
    public function showLogin()
    {
        return view('auth.enhanced-login');
    }

    /**
     * Enhanced login with better UX and security
     */
    public function login(Request $request)
    {
        // Rate limiting
        $key = 'login.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                'lockout_time' => $seconds
            ], 429);
        }

        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        try {
            // Find user by email or IIUM ID
            $user = Users::where('email', $request->identifier)
                ->orWhere('iium_id', strtoupper($request->identifier))
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                RateLimiter::hit($key, 300); // 5 minutes lockout
                
                // Log failed attempt
                Log::warning('Failed login attempt', [
                    'identifier' => $request->identifier,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Please check your email/IIUM ID and password.'
                ], 401);
            }

            // Check if user is active
            if (isset($user->status) && $user->status === 'inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.'
                ], 403);
            }

            // Clear rate limiting on successful login
            RateLimiter::clear($key);

            // Set session data
            Session::regenerate();
            Session::put([
                'id' => $user->id,
                'iium_id' => $user->iium_id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'centre_id' => $user->centre_id,
                'logged_in' => true,
                'login_time' => now()->toDateTimeString()
            ]);

            // Handle remember me
            if ($request->remember) {
                $token = bin2hex(random_bytes(32));
                $user->update(['remember_token' => $token]);
                cookie()->queue('remember_token', $token, 60 * 24 * 30); // 30 days
            }

            // Update last login
            $user->update(['last_login' => now()]);

            // Log successful login
            Log::info('Successful login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => $request->ip()
            ]);

            // Return JSON response for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'redirect_url' => $this->getRedirectUrl($user->role),
                'user' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'centre' => $user->centre->centre_name ?? 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'identifier' => $request->identifier,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ], 500);
        }
    }

    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl($role)
    {
        $redirects = [
            'admin' => route('admin.dashboard'),
            'supervisor' => route('supervisor.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'ajk' => route('ajk.dashboard')
        ];

        return $redirects[$role] ?? route('dashboard');
    }
}