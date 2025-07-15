<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Users;
use App\Models\AuditLog;

class UserController extends Controller
{
    // Define role hierarchy
    private $roleHierarchy = [
        'admin' => 4,
        'supervisor' => 3,
        'ajk' => 2,
        'teacher' => 1
    ];

    /**
     * Check if the current user has permission to manage a target user of a specific role
     *
     * @param string $targetRole Role of the user being accessed/modified
     * @return bool
     */
    private function canManageRole($targetRole)
    {
        $userRole = session('role');
        
        // Get hierarchy levels
        $userLevel = $this->roleHierarchy[$userRole] ?? 0;
        $targetLevel = $this->roleHierarchy[$targetRole] ?? 0;
        
        // User can only manage roles with lower hierarchy level than their own
        return $userLevel > $targetLevel;
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        $userRole = session('role');
        $userId = session('id');
        
        Log::info('User accessed staff list', [
            'user_id' => $userId,
            'role' => $userRole
        ]);
        
        // Get users based on role hierarchy
        $admins = [];
        $supervisors = [];
        $ajks = [];
        $teachers = [];
        
        // Admins can see all users
        if ($userRole === 'admin') {
            $admins = Users::where('role', 'admin')->get();
            $supervisors = Users::where('role', 'supervisor')->get();
            $ajks = Users::where('role', 'ajk')->get();
            $teachers = Users::where('role', 'teacher')->get();
        } 
        // Supervisors can see AJKs and Teachers
        else if ($userRole === 'supervisor') {
            $ajks = Users::where('role', 'ajk')->get();
            $teachers = Users::where('role', 'teacher')->get();
        } 
        // AJKs can see Teachers
        else if ($userRole === 'ajk') {
            $teachers = Users::where('role', 'teacher')->get();
        } 
        // Teachers can't manage other staff
        else {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        return view('users.index', [
            'admins' => $admins,
            'supervisors' => $supervisors,
            'ajks' => $ajks,
            'teachers' => $teachers,
            'userRole' => $userRole
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        $userRole = session('role');
        
        // Get available roles based on hierarchy
        $availableRoles = [];
        
        if ($userRole === 'admin') {
            $availableRoles = ['admin', 'supervisor', 'ajk', 'teacher'];
        } else if ($userRole === 'supervisor') {
            $availableRoles = ['ajk', 'teacher'];
        } else if ($userRole === 'ajk') {
            $availableRoles = ['teacher'];
        } else {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to create users');
        }
        
        return view('users.create', [
            'availableRoles' => $availableRoles,
            'userRole' => $userRole
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        $userRole = session('role');
        $userId = session('id');
        
        // Check if user has permission to create this role
        if (!$this->canManageRole($request->role)) {
            Log::warning('Unauthorized attempt to create user', [
                'user_id' => $userId,
                'user_role' => $userRole,
                'target_role' => $request->role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to create users with this role');
        }
        
        // Validate input with specific error messages
        $validator = Validator::make($request->all(), [
            'iium_id' => [
                'required',
                'string',
                'size:8',
                'regex:/^[A-Z]{4}\d{4}$/',
                Rule::unique('users', 'iium_id'),
            ],
            'role' => [
                'required',
                Rule::in(array_filter(['admin', 'supervisor', 'ajk', 'teacher'], function($role) use ($userRole) {
                    return $this->canManageRole($role);
                }))
            ],
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
            'centre_id' => 'required|exists:centres,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->except(['password', 'password_confirmation']));
        }
        
        // Get validated data
        $validatedData = $validator->validated();
        
        // Encrypt password
        $validatedData['password'] = Hash::make($validatedData['password']);
        
        DB::beginTransaction();
        try {
            // Create user directly in Users table
            $user = new Users();
            $user->iium_id = strtoupper($validatedData['iium_id']);
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = $validatedData['password'];
            $user->role = $validatedData['role']; // Set the role
            $user->centre_id = $validatedData['centre_id'];
            $user->status = 'active';
            
            $saved = $user->save();
            
            if (!$saved) {
                DB::rollBack();
                return back()->with('fail', 'Something went wrong, try again later');
            }
            
            // Log the action
            $this->logUserAction('create', $user->id, $validatedData['role'], [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'iium_id' => $validatedData['iium_id'],
                'role' => $validatedData['role'],
                'centre_id' => $validatedData['centre_id']
            ]);
            
            DB::commit();
            
            $successMessage = "New " . ucfirst($validatedData['role']) . " has been registered";
            return redirect()->route('users.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('fail', 'An error occurred: ' . $e->getMessage())->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    /**
     * Display the specified user.
     *
     * @param  string  $role
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($role, $id)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Check if user has permission to view this role
        if (!$this->canManageRole($role)) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to view users with this role');
        }
        
        // Get user from the users table filtered by role
        $user = Users::where('role', $role)
                ->where('id', $id)
                ->firstOrFail();
        
        // Get user audit log history if the table exists
        $history = [];
        if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
            $history = AuditLog::where('table', 'users')
                     ->where('record_id', $id)
                     ->orderBy('created_at', 'desc')
                     ->get();
        }
        
        return view('users.show', [
            'user' => $user,
            'role' => $role,
            'history' => $history,
            'canEdit' => $this->canManageRole($role)
        ]);
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  string  $role
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($role, $id)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Check if user has permission to edit this role
        if (!$this->canManageRole($role)) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to edit users with this role');
        }
        
        // Get user from users table filtered by role
        $user = Users::where('role', $role)
               ->where('id', $id)
               ->firstOrFail();
        
        return view('users.edit', [
            'user' => $user,
            'role' => $role
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $role
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $role, $id)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Check if user has permission to update this role
        if (!$this->canManageRole($role)) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to update users with this role');
        }
        
        // Get user from users table filtered by role
        $user = Users::where('role', $role)
               ->where('id', $id)
               ->firstOrFail();
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'iium_id' => [
                'required',
                'string',
                'size:8',
                'regex:/^[A-Z]{4}\d{4}$/',
                Rule::unique('users', 'iium_id')->ignore($id),
            ],
            'name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'centre_id' => 'required|exists:centres,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'bio' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Get validated data
        $validatedData = $validator->validated();
        
        // Save original user data for audit log
        $originalData = [
            'iium_id' => $user->iium_id,
            'name' => $user->name,
            'email' => $user->email,
            'centre_id' => $user->centre_id,
            'phone' => $user->phone ?? null,
            'address' => $user->address ?? null,
            'bio' => $user->bio ?? null,
        ];
        
        // Update user
        $user->iium_id = strtoupper($validatedData['iium_id']);
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->centre_id = $validatedData['centre_id'];
        
        // Update optional fields if provided
        if (isset($validatedData['phone'])) {
            $user->phone = $validatedData['phone'];
        }
        
        if (isset($validatedData['address'])) {
            $user->address = $validatedData['address'];
        }
        
        if (isset($validatedData['bio'])) {
            $user->bio = $validatedData['bio'];
        }
        
        $saved = $user->save();
        
        if (!$saved) {
            return back()->with('fail', 'Something went wrong, try again later');
        }
        
        // Log the changes
        $newData = [
            'iium_id' => $user->iium_id,
            'name' => $user->name,
            'email' => $user->email,
            'centre_id' => $user->centre_id,
            'phone' => $user->phone ?? null,
            'address' => $user->address ?? null,
            'bio' => $user->bio ?? null,
        ];
        
        $changes = array_diff_assoc($newData, $originalData);
        
        if (!empty($changes)) {
            $this->logUserAction('update', $id, $role, $changes, $originalData);
        }
        
        return redirect()->route('users.show', ['role' => $role, 'id' => $id])
            ->with('success', ucfirst($role) . ' updated successfully');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  string  $role
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($role, $id)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Check if user has permission to delete this role
        if (!$this->canManageRole($role)) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to delete users with this role');
        }
        
        // Get user from users table filtered by role
        $user = Users::where('role', $role)
               ->where('id', $id)
               ->firstOrFail();
        
        // Save user data for audit log before deletion
        $userData = [
            'iium_id' => $user->iium_id,
            'name' => $user->name,
            'email' => $user->email,
            'centre_id' => $user->centre_id
        ];
        
        // Delete user
        $user->delete();
        
        // Log the deletion
        $this->logUserAction('delete', $id, $role, $userData);
        
        return redirect()->route('users.index')
            ->with('success', ucfirst($role) . ' deleted successfully');
    }
    
    /**
     * Reset user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $role
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request, $role, $id)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Check if user has permission to reset password for this role
        if (!$this->canManageRole($role)) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to reset password for users with this role');
        }
        
        // Validate input
        $request->validate([
            'password' => [
                'required',
                'min:5',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{5,}$/',
                'confirmed'
            ]
        ]);
        
        // Get user from users table filtered by role
        $user = Users::where('role', $role)
               ->where('id', $id)
               ->firstOrFail();
        
        // Update password
        $user->password = Hash::make($request->password);
        $saved = $user->save();
        
        if (!$saved) {
            return back()->with('fail', 'Something went wrong, try again later');
        }
        
        // Log the password reset
        $this->logUserAction('password_reset', $id, $role, [
            'password' => 'Password was reset'
        ]);
        
        return redirect()->route('users.show', ['role' => $role, 'id' => $id])
            ->with('success', 'Password reset successfully');
    }
    
    /**
     * Change user status (active/inactive).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $role
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus(Request $request, $role, $id)
    {
        // Check if user is authenticated
        if (!session('id') || !session('role')) {
            return redirect()->route('auth.loginpage')
                ->with('error', 'Please log in to access this page');
        }
        
        // Check if user has permission to change status for this role
        if (!$this->canManageRole($role)) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to change status for users with this role');
        }
        
        // Validate input
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);
        
        // Get user from users table filtered by role
        $user = Users::where('role', $role)
               ->where('id', $id)
               ->firstOrFail();
        
        // Save original status for audit log
        $originalStatus = $user->status;
        
        // Update status
        $user->status = $request->status;
        $saved = $user->save();
        
        if (!$saved) {
            return back()->with('fail', 'Something went wrong, try again later');
        }
        
        // Log the status change
        $this->logUserAction('status_change', $id, $role, [
            'status' => $request->status
        ], [
            'status' => $originalStatus
        ]);
        
        return redirect()->route('users.show', ['role' => $role, 'id' => $id])
            ->with('success', 'User status updated successfully');
    }
    
    /**
     * Log user actions for auditing purposes
     *
     * @param string $action Type of action (create, update, delete, etc.)
     * @param int $recordId ID of the affected record
     * @param string $role User role (admin, supervisor, etc.)
     * @param array $newData New data for the record
     * @param array $oldData Old data for the record (for updates)
     * @return void
     */
    private function logUserAction($action, $recordId, $role, $newData, $oldData = [])
    {
        $userId = session('id');
        $userRole = session('role');
        
        if (!$userId || !$userRole) {
            Log::warning('Attempted to log user action without authenticated user', [
                'action' => $action,
                'record_id' => $recordId,
                'role' => $role
            ]);
            return;
        }
        
        try {
            // Check if audit_logs table exists
            if (!DB::getSchemaBuilder()->hasTable('audit_logs')) {
                Log::warning('Audit logs table does not exist, skipping logging', [
                    'action' => $action,
                    'record_id' => $recordId,
                    'role' => $role
                ]);
                return;
            }
            
            $auditLog = new AuditLog();
            $auditLog->user_id = $userId;
            $auditLog->user_role = $userRole;
            $auditLog->action = $action;
            $auditLog->table = 'users'; // Always use 'users' table, not role-specific tables
            $auditLog->record_id = $recordId;
            $auditLog->old_values = !empty($oldData) ? json_encode($oldData) : null;
            $auditLog->new_values = !empty($newData) ? json_encode($newData) : null;
            $auditLog->ip_address = request()->ip();
            $auditLog->user_agent = request()->userAgent();
            $auditLog->save();
            
            Log::info('User action logged successfully', [
                'action' => $action,
                'user_id' => $userId,
                'record_id' => $recordId,
                'table' => 'users'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log user action', [
                'error' => $e->getMessage(),
                'action' => $action,
                'user_id' => $userId,
                'record_id' => $recordId
            ]);
        }
    }
}