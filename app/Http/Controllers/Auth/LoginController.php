<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Handle a login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        // Try to authenticate user with each user type
        $userTypes = [
            ['model' => Admins::class, 'guard' => 'admin', 'role' => 'admin'],
            ['model' => Supervisors::class, 'guard' => 'supervisor', 'role' => 'supervisor'],
            ['model' => Teachers::class, 'guard' => 'teacher', 'role' => 'teacher'],
            ['model' => AJKs::class, 'guard' => 'ajk', 'role' => 'ajk']
        ];
        
        foreach ($userTypes as $userType) {
            $user = $userType['model']::where('email', $credentials['email'])->first();
            
            if ($user) {
                // We found a user, now check password
                if (Auth::guard($userType['guard'])->attempt([
                    'email' => $credentials['email'],
                    'password' => $credentials['password']
                ])) {
                    // Authentication successful
                    $request->session()->regenerate();
                    
                    // Store complete user data in session
                    session([
                        'id' => $user->id,
                        'iium_id' => $user->iium_id ?? null,
                        'name' => $user->name,
                        'role' => $userType['role'],
                        'email' => $user->email,
                        'centre_id' => $user->centre_id ?? null,
                        'avatar' => $user->avatar ?? null,
                        'user_avatar' => $user->avatar ?? null,
                        'phone' => $user->phone ?? null,
                        'address' => $user->address ?? null,
                        'bio' => $user->bio ?? $user->about ?? null,
                        'date_of_birth' => $user->date_of_birth ?? null,
                        'logged_in' => true,
                        'login_time' => now()->toDateTimeString()
                    ]);
                    
                    Log::info('User login successful', [
                        'email' => $credentials['email'],
                        'user_type' => $userType['role']
                    ]);
                    
                    return redirect()->route($userType['role'] . '.dashboard');
                }
                
                // If we found the user but password failed, no need to check other models
                Log::warning('Login failed: Invalid password', [
                    'email' => $credentials['email']
                ]);
                
                return back()->withErrors([
                    'password' => 'The provided password is incorrect.'
                ])->withInput($request->except('password'));
            }
        }
        
        // If we got here, no user was found with that email
        Log::warning('Login failed: User not found', [
            'email' => $credentials['email']
        ]);
        
        return back()->withErrors([
            'email' => 'No account found with that email address.'
        ])->withInput($request->except('password'));
    }
    
    /**
     * Log the user out
     */
    public function logout(Request $request)
    {
        // Determine which guard to use based on the user's role
        $role = session('role') ?: 'web';
        Auth::guard($role)->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}