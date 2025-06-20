<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Users;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\AJKs;
use App\Models\Teachers;
use App\Models\Centres;
use App\Models\Notifications;
use App\Models\Trainees;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class MainController extends Controller
{
    /**
     * Display the login page
     *
     * @return \Illuminate\View\View
     */
    public function login()
    {
        Log::info('Login page accessed');
        return view("auth.login");
    }

    /**
     * Display the registration page
     *
     * @return \Illuminate\View\View
     */
    public function registration()
    {
        Log::info('Registration page accessed');

        try {
            // Check which column exists in the centres table
            $hasStatusColumn = Schema::hasColumn('centres', 'status');
            $hasCentreStatusColumn = Schema::hasColumn('centres', 'centre_status');

            Log::info('Centres table structure check', [
                'has_status_column' => $hasStatusColumn,
                'has_centre_status_column' => $hasCentreStatusColumn
            ]);

            // Get centers for dropdown based on available columns
            if ($hasStatusColumn) {
                Log::info('Querying centres using status column');
                $centers = Centres::where('status', 'active')->get();
            } elseif ($hasCentreStatusColumn) {
                Log::info('Querying centres using centre_status column');
                $centers = Centres::where('centre_status', 'active')->get();
            } else {
                // Fallback - get all centers without filtering
                Log::info('No status columns found, getting all centres');
                $centers = Centres::all();
            }

            Log::info('Centres retrieved successfully', [
                'count' => $centers->count()
            ]);
        } catch (\Exception $e) {
            // Log error and provide empty collection as fallback
            Log::error('Error retrieving centres for registration page', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $centers = collect();
        }

        return view("auth.register", [
            'centers' => $centers
        ]);
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function save(Request $request)
    {

         // Debug log to see what's being submitted
        Log::info('Registration form data:', [
            'all_data' => $request->all(),
            'centre_id' => $request->centre_id,
            'centre_location' => $request->centre_location
        ]);
        Log::info('Registration form submitted', ['data' => $request->except(['password', 'password_confirmation'])]);

        try {
            // Validate input with specific error messages
            Log::info('Beginning validation');
            $validator = Validator::make($request->all(), [
                'iium_id' => [
                    'required',
                    'string',
                    'size:8',
                    'regex:/^[A-Z]{4}\d{4}$/',
                    Rule::unique('users', 'iium_id'),
                ],
                'role' => 'required|in:admin,supervisor,teacher,ajk',
                'name' => 'required',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email'),
                ],
                'password' => [
                    'required',
                    'min:5',
                    'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{5,}$/',
                ],
                'password_confirmation' => 'required|same:password',
                'centre_id' => 'required|exists:centres,centre_id',
                'centre_location' => 'nullable|in:Gombak,Kuantan,Pagoh', // Add validation for the new field
            ], [
                'iium_id.required' => 'IIUM ID is required.',
                'iium_id.size' => 'IIUM ID must be exactly 8 characters.',
                'iium_id.regex' => 'IIUM ID must be 4 letters followed by 4 numbers (e.g., ABCD1234).',
                'iium_id.unique' => 'This IIUM ID is already registered in our system.',
                'role.required' => 'Please select a role.',
                'role.in' => 'The selected role is invalid.',
                'name.required' => 'Your full name is required.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 5 characters.',
                'password.regex' => 'Password must contain at least one letter and one number.',
                'password_confirmation.required' => 'Please confirm your password.',
                'password_confirmation.same' => 'Password confirmation does not match.',
                'centre_id.required' => 'Please select a centre location.',
                'centre_id.exists' => 'The selected centre is invalid.',
                'centre_location.in' => 'The selected centre location is invalid.', // Add error message for the new field
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                Log::warning('Validation failed with specific errors:', $errors);

                // Log specific error types for detailed debugging
                if (isset($errors['iium_id']) && strpos(implode('', $errors['iium_id']), 'unique') !== false) {
                    Log::warning('Duplicate IIUM ID detected during registration', ['iium_id' => $request->iium_id]);
                }
                if (isset($errors['email']) && strpos(implode('', $errors['email']), 'unique') !== false) {
                    Log::warning('Duplicate email detected during registration', ['email' => $request->email]);
                }

                return redirect()->back()->withErrors($validator)->withInput($request->except(['password', 'password_confirmation']));
            }

            // Get validated data
            $validatedData = $validator->validated();
            Log::info('Validation passed', ['role' => $validatedData['role'], 'iium_id' => $validatedData['iium_id']]);

            // Encrypt password
            $validatedData['password'] = Hash::make($validatedData['password']);

            DB::beginTransaction();
            try {
                // Create the user with proper logging
                $user = new Users();
                $user->iium_id = strtoupper($validatedData['iium_id']);
                $user->name = $validatedData['name'];
                $user->email = $validatedData['email'];
                $user->password = $validatedData['password'];
                $user->role = $validatedData['role'];
                $user->centre_id = $validatedData['centre_id'];

                // Set the centre_location if provided
                if (isset($validatedData['centre_location'])) {
                    $user->centre_location = $validatedData['centre_location'];
                    Log::info('Centre location set', ['centre_location' => $validatedData['centre_location']]);
                }

                $user->status = 'active';

                Log::info('Attempting to save user', [
                    'iium_id' => $user->iium_id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'centre_location' => $user->centre_location ?? 'null',
                    'model' => get_class($user)
                ]);

                $saved = $user->save();

                if (!$saved) {
                    Log::error('Failed to save user');
                    DB::rollBack();
                    return back()->with('fail', 'Something went wrong, try again later');
                }

                // Create welcome notification
                $notification = new Notifications();
                $notification->user_id = $user->id;
                $notification->user_type = Users::class;
                $notification->title = 'Welcome to CREAMS';
                $notification->content = 'Welcome to the Community-based REhAbilitation Management System. Your account has been created successfully.';
                $notification->type = 'success';
                $notification->save();

                DB::commit();
                Log::info('User successfully registered', [
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'role' => $user->role,
                    'centre_location' => $user->centre_location ?? 'null'
                ]);

                $successMessage = "New " . ucfirst($validatedData['role']) . " has been registered";
                return redirect()->route('auth.loginpage')->with('success', $successMessage);
            } catch (\PDOException $e) {
                DB::rollBack();
                Log::error('Database error during registration:', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);

                // More specific database error messages
                if ($e->getCode() == 23000) { // Integrity constraint violation
                    return back()->with('fail', 'This IIUM ID or email is already registered in our system.')->withInput($request->except(['password', 'password_confirmation']));
                }

                return back()->with('fail', 'A database error occurred. Please try again later.')->withInput($request->except(['password', 'password_confirmation']));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Exception while saving user', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->with('fail', 'An error occurred: ' . $e->getMessage())->withInput($request->except(['password', 'password_confirmation']));
            }
        } catch (\Exception $e) {
            Log::error('Error occurred in save method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('fail', 'An error occurred: ' . $e->getMessage())->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    /**
     * Handle login authentication
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function check(Request $request)
    {
        // Debug log at the beginning
        Log::info('Login attempt started', [
            'data' => $request->except(['password']),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            // Validate with specific error messages
            $validator = Validator::make($request->all(), [
                'identifier' => 'required',
                'password' => 'required|min:5'
            ], [
                'identifier.required' => 'Email or IIUM ID is required.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 5 characters.'
            ]);

            if ($validator->fails()) {
                Log::warning('Login validation failed with specific errors:', $validator->errors()->toArray());
                return redirect()->back()->withErrors($validator)->withInput($request->except('password'));
            }

            // Determine if the identifier is an email or IIUM ID
            $identifier = $request->identifier;
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

            Log::info('Searching for user by ' . ($isEmail ? 'email' : 'IIUM ID'), ['identifier' => $identifier]);

            // Find user based on the identifier type in the users table only
            $user = null;

            if ($isEmail) {
                $user = Users::where('email', $identifier)
                            ->where('status', 'active')
                            ->first();

                Log::debug('User search by email result', [
                    'email' => $identifier,
                    'found' => ($user ? 'Yes' : 'No')
                ]);
            } else {
                $iiumId = strtoupper($identifier);
                $user = Users::where('iium_id', $iiumId)
                            ->where('status', 'active')
                            ->first();

                Log::debug('User search by IIUM ID result', [
                    'iium_id' => $iiumId,
                    'found' => ($user ? 'Yes' : 'No')
                ]);
            }

            // If user not found, return appropriate error message
            if (!$user) {
                Log::warning('Login failed: User not found', [
                    'identifier' => $identifier,
                    'is_email' => $isEmail
                ]);

                return redirect()->route('auth.loginpage')
                    ->with('error', 'No account found with this ' . ($isEmail ? 'email address' : 'IIUM ID'));
            }

            Log::info('User found', [
                'id' => $user->id,
                'iium_id' => $user->iium_id,
                'role' => $user->role,
                'status' => $user->status
            ]);

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Login failed: Incorrect password', [
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'attempt_time' => now()->toDateTimeString()
                ]);

                return redirect()->route('auth.loginpage')
                    ->with('error', 'The password you entered is incorrect');
            }

            Log::info('Password verification successful', [
                'id' => $user->id,
                'iium_id' => $user->iium_id
            ]);

            // Update last accessed time using updateLastLogin method
            try {
                $user->updateLastLogin();
                Log::info('User last login time updated successfully');
            } catch (\Exception $e) {
                // If updateLastLogin fails, log it but continue with authentication
                Log::warning('Could not update last login time: ' . $e->getMessage());
            }

            // Set session data
            session([
                'id' => $user->id,
                'iium_id' => $user->iium_id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'centre_id' => $user->centre_id,
                'avatar' => $user->avatar ?? null,
                'user_avatar' => $user->avatar ?? null,
                'phone' => $user->phone ?? null,
                'address' => $user->address ?? null,
                'bio' => $user->bio ?? null,
                'date_of_birth' => $user->date_of_birth ?? null,
                'logged_in' => true,
                'login_time' => now()->toDateTimeString()
            ]);

            // Remember Me functionality
            if ($request->has('remember') && $request->remember == 'on') {
                // Create a remember token if it doesn't exist
                if (empty($user->remember_token)) {
                    $user->remember_token = Str::random(60);
                    $user->save();

                    Log::info('Remember token generated', [
                        'user_id' => $user->id,
                        'token_length' => strlen($user->remember_token)
                    ]);
                }

                // Set a cookie with the remember token
                Cookie::queue('remember_token', $user->remember_token, 43200); // 30 days
            }

            Log::info('User logged in successfully', [
                'id' => $user->id,
                'iium_id' => $user->iium_id,
                'role' => $user->role,
                'remember_me' => $request->has('remember'),
                'session_id' => session()->getId()
            ]);

            return redirect()->route($user->role . '.dashboard');

        } catch (\Exception $e) {
            // Log the exception with detailed information
            Log::error('Error occurred in login process', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'identifier' => $request->identifier ?? 'not provided'
            ]);

            // Return a generic error message to the user
            return redirect()->route('auth.loginpage')
                ->with('error', 'An error occurred during login. Please try again later.');
        }
    }

    /**
     * Find user by email in users table with role filtering
     *
     * @param string $email
     * @param string|null $role Role to filter by (optional)
     * @return mixed
     */
    private function findUserByEmail($email, $role = null)
    {
        Log::debug('Searching for user by email', ['email' => $email, 'role_filter' => $role]);

        try {
            // Start query
            $query = Users::where('email', $email)
                        ->where('status', 'active');

            // Add role filter if specified
            if (!is_null($role)) {
                $query->where('role', $role);
            }

            // Execute query
            $user = $query->first();

            if ($user) {
                Log::info('User found by email', [
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'role' => $user->role
                ]);
            } else {
                Log::info('No user found by email', ['email' => $email]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Error finding user by email', [
                'email' => $email,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Find user by IIUM ID in users table with role filtering
     *
     * @param string $iiumId
     * @param string|null $role Role to filter by (optional)
     * @return mixed
     */
    private function findUserByIiumId($iiumId, $role = null)
    {
        $iiumId = strtoupper($iiumId);
        Log::debug('Searching for user by IIUM ID', ['iium_id' => $iiumId, 'role_filter' => $role]);

        try {
            // Start query
            $query = Users::where('iium_id', $iiumId)
                        ->where('status', 'active');

            // Add role filter if specified
            if (!is_null($role)) {
                $query->where('role', $role);
            }

            // Execute query
            $user = $query->first();

            if ($user) {
                Log::info('User found by IIUM ID', [
                    'id' => $user->id,
                    'iium_id' => $user->iium_id,
                    'role' => $user->role
                ]);
            } else {
                Log::info('No user found by IIUM ID', ['iium_id' => $iiumId]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Error finding user by IIUM ID', [
                'iium_id' => $iiumId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Set user session data
     *
     * @param mixed $user
     * @return void
     */
    private function setUserSession($user)
    {
        // First clear any existing session data
        $oldSessionId = session()->getId();
        $hadPreviousSession = session()->has('id');

        Log::debug('Clearing previous session before setting new one', [
            'previous_session_id' => $oldSessionId,
            'had_user' => $hadPreviousSession,
            'previous_user_id' => session('id'),
            'previous_role' => session('role')
        ]);

        session()->flush();

        // Store user data in session with all relevant fields
        $sessionData = [
            'id' => $user->id,
            'iium_id' => $user->iium_id,
            'name' => $user->name,
            'role' => $user->role,
            'email' => $user->email,
            'centre_id' => $user->centre_id,
            'logged_in' => true,
            'login_time' => now()->toDateTimeString()
        ];

        session($sessionData);

        // Force session to be saved immediately
        session()->save();

        // Verify session was properly set
        $newSessionId = session()->getId();
        $sessionContainsId = session()->has('id');
        $sessionUserId = session('id');

        Log::info('User session set', [
            'id' => $user->id,
            'iium_id' => $user->iium_id,
            'role' => $user->role,
            'new_session_id' => $newSessionId,
            'session_contains_id' => $sessionContainsId,
            'session_user_id' => $sessionUserId,
            'session_changed' => ($oldSessionId !== $newSessionId)
        ]);

        // Double-check all session data is correct
        if ($sessionUserId != $user->id) {
            Log::warning('Session user ID mismatch after setting session', [
                'expected' => $user->id,
                'actual' => $sessionUserId
            ]);
        }
    }

    /**
     * Get role name from user model
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

    /**
     * Log out the user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $userId = session('id');
        $userRole = session('role');
        $sessionId = session()->getId();

        Log::info('User logout initiated', [
            'user_id' => $userId,
            'role' => $userRole,
            'session_id' => $sessionId,
            'ip' => $request->ip()
        ]);

        // If the user is logged in, find their model and clear the remember token
        if ($userId) {
            try {
                $user = Users::find($userId);

                if ($user) {
                    Log::debug('Clearing remember token for user', ['user_id' => $userId]);
                    $user->remember_token = null;
                    $user->save();
                } else {
                    Log::warning('User not found during logout', ['user_id' => $userId]);
                }
            } catch (\Exception $e) {
                Log::error('Error updating user during logout', [
                    'user_id' => $userId,
                    'message' => $e->getMessage()
                ]);
            }
        }

        // Clear cookies
        Cookie::queue(Cookie::forget('remember_token'));

        // Store log data before clearing session
        $logData = [
            'user_id' => $userId,
            'role' => $userRole,
            'session_id' => $sessionId,
            'logout_time' => now()->toDateTimeString()
        ];

        // Clear the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log after session is cleared
        Log::info('User logged out successfully', $logData);

        return redirect('/');
    }
    
    /**
     * Search for users and trainees by name
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        Log::info('Global search initiated', [
            'query' => $request->input('query'),
            'user_id' => session('id')
        ]);
        
        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        try {
            // Search for users (staff/teachers)
            $users = Users::where('name', 'like', "%{$query}%")
                ->where('status', 'active')
                ->where('role', '!=', 'admin') // Optional: exclude admins from results
                ->select('id', 'name', 'role', 'centre_id')
                ->with('centre:id,name,centre_id,centre_name')
                ->limit(10)
                ->get();
                
            // Search for trainees
            $trainees = Trainees::where(function($q) use ($query) {
                    $q->where('trainee_first_name', 'like', "%{$query}%")
                      ->orWhere('trainee_last_name', 'like', "%{$query}%");
                })
                ->select('id', 'trainee_first_name', 'trainee_last_name', 'centre_id')
                ->with('centre:id,name,centre_id,centre_name')
                ->limit(10)
                ->get()
                ->map(function($trainee) {
                    $trainee->role = 'trainee';
                    $trainee->name = $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name;
                    return $trainee;
                });
            
            // Combine and format results
            $results = $users->concat($trainees)->map(function($item) {
                $centreName = '';
                
                // Handle different centre relation formats
                if ($item->centre) {
                    $centreName = $item->centre->name ?? $item->centre->centre_name ?? $item->centre->centre_id ?? 'Unknown';
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'role' => ucfirst($item->role),
                    'location' => $centreName ?: 'Not assigned',
                    'url' => $item->role === 'trainee' 
                        ? route('traineeprofile', ['id' => $item->id])
                        : route(session('role') . '.user.view', ['id' => $item->id])
                ];
            });
            
            Log::info('Search results generated', [
                'query' => $query,
                'count' => $results->count()
            ]);
            
            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Error in search function', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'An error occurred while searching'
            ], 500);
        }
    }
}