<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class EnhancedLoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (SessionManager::check()) {
            return redirect()->route($this->getRedirectRoute(session('role')));
        }
        
        return view('auth.enhanced-login');
    }
    
    /**
     * Handle a login request with enhanced session management
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ], [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('password'));
        }

        try {
            // Find user across all user models
            $user = $this->findUserByEmail($request->email);
            
            if (!$user) {
                Log::warning('Login attempt with non-existent email', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);
                
                return back()->with('error', 'No account found with that email address')
                    ->withInput($request->except('password'));
            }
            
            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Login attempt with incorrect password', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'ip' => $request->ip()
                ]);
                
                return back()->with('error', 'The password you entered is incorrect')
                    ->withInput($request->except('password'));
            }
            
            // Check if user is active
            if ($user->status !== 'active') {
                Log::warning('Login attempt with inactive account', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'status' => $user->status,
                    'ip' => $request->ip()
                ]);
                
                return back()->with('error', 'Your account is currently inactive. Please contact administrator')
                    ->withInput($request->except('password'));
            }
            
            // Login user with enhanced session management
            SessionManager::login($user, $request->has('remember'));
            
            // Get intended URL or default redirect
            $intendedUrl = session('intended_url', null);
            if ($intendedUrl) {
                session()->forget('intended_url');
                return redirect($intendedUrl)->with('success', 'Welcome back, ' . $user->name);
            }
            
            // Redirect based on role
            $redirectRoute = $this->getRedirectRoute($user->role);
            return redirect()->route($redirectRoute)
                ->with('success', 'Welcome back, ' . $user->name);
                
        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            
            return back()->with('error', 'Login failed. Please try again or contact support')
                ->withInput($request->except('password'));
        }
    }

    /**
     * Handle logout with enhanced session cleanup
     */
    public function logout(Request $request)
    {
        $userName = session('name', 'User');
        
        SessionManager::logout();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => route('login')
            ]);
        }
        
        return redirect()->route('login')
            ->with('success', 'Goodbye ' . $userName . '. You have been logged out successfully');
    }
    
    /**
     * Check authentication status (AJAX endpoint)
     */
    public function checkAuth(Request $request)
    {
        $isAuthenticated = SessionManager::check();
        $isAboutToExpire = SessionManager::isAboutToExpire();
        
        return response()->json([
            'authenticated' => $isAuthenticated,
            'about_to_expire' => $isAboutToExpire,
            'user' => $isAuthenticated ? [
                'id' => session('id'),
                'name' => session('name'),
                'role' => session('role'),
                'email' => session('email')
            ] : null,
            'session_timeout' => SessionManager::getTimeoutMinutes()
        ]);
    }
    
    /**
     * Extend session (AJAX endpoint)
     */
    public function extendSession(Request $request)
    {
        if (SessionManager::extend()) {
            return response()->json([
                'success' => true,
                'message' => 'Session extended successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unable to extend session'
        ], 401);
    }
    
    /**
     * Refresh session data
     */
    public function refreshSession(Request $request)
    {
        if (SessionManager::refresh()) {
            return response()->json([
                'success' => true,
                'message' => 'Session refreshed successfully',
                'user' => [
                    'id' => session('id'),
                    'name' => session('name'),
                    'role' => session('role'),
                    'email' => session('email')
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unable to refresh session'
        ], 401);
    }

    /**
     * Find user by email across all user models
     */
    private function findUserByEmail($email)
    {
        // Define user models with their roles
        $userModels = [
            ['model' => User::class, 'role' => null], // Universal users table
        ];
        
        foreach ($userModels as $userModel) {
            $user = $userModel['model']::where('email', $email)->first();
            if ($user) {
                // If role is not set in the user record, use the model-specific role
                if (!isset($user->role) && $userModel['role']) {
                    $user->role = $userModel['role'];
                }
                return $user;
            }
        }
        
        return null;
    }

    /**
     * Get redirect route based on role
     */
    private function getRedirectRoute($role)
    {
        $routes = [
            'admin' => 'dashboard',
            'supervisor' => 'dashboard', 
            'teacher' => 'dashboard',
            'ajk' => 'dashboard'
        ];
        
        return $routes[$role] ?? 'dashboard';
    }
}