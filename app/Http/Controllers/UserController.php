<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to users index', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Get users data
        $admins = Admins::all();
        $supervisors = Supervisors::all();
        $teachers = Teachers::all();
        $ajks = AJKs::all();
        
        return view('users.index', [
            'admins' => $admins,
            'supervisors' => $supervisors,
            'teachers' => $teachers,
            'ajks' => $ajks
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to create user', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to store user', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input with specific error messages
        $validator = Validator::make($request->all(), [
            'iium_id' => [
                'required',
                'string',
                'size:8',
                'regex:/^[A-Z]{4}\d{4}$/',
                Rule::unique('admins', 'iium_id'),
                Rule::unique('supervisors', 'iium_id'),
                Rule::unique('teachers', 'iium_id'),
                Rule::unique('ajks', 'iium_id'),
            ],
            'role' => 'required|in:admin,supervisor,teacher,ajk',
            'name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email'),
                Rule::unique('supervisors', 'email'),
                Rule::unique('teachers', 'email'),
                Rule::unique('ajks', 'email'),
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
            // Create user based on role
            $user = null;
            switch($validatedData['role']) {
                case 'admin':
                    $user = new Admins();
                    break;
                case 'supervisor':
                    $user = new Supervisors();
                    break;
                case 'teacher':
                    $user = new Teachers();
                    break;
                case 'ajk':
                    $user = new AJKs();
                    break;
                default:
                    throw new \Exception('Invalid role specified.');
            }
            
            // Assign values
            $user->iium_id = strtoupper($validatedData['iium_id']);
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = $validatedData['password'];
            $user->centre_id = $validatedData['centre_id'];
            $user->status = 'active';
            
            $saved = $user->save();
            
            if (!$saved) {
                DB::rollBack();
                return back()->with('fail', 'Something went wrong, try again later');
            }
            
            DB::commit();
            
            $successMessage = "New " . ucfirst($validatedData['role']) . " has been registered";
            return redirect()->route('users.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
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
        // Check role access
        $userRole = session('role');
        if ($userRole !== 'admin') {
            Log::warning('Unauthorized access attempt to view user', [
                'user_id' => session('id'),
                'role' => $userRole,
                'target_role' => $role,
                'target_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Get user based on role
        $user = null;
        switch($role) {
            case 'admin':
                $user = Admins::findOrFail($id);
                break;
            case 'supervisor':
                $user = Supervisors::findOrFail($id);
                break;
            case 'teacher':
                $user = Teachers::findOrFail($id);
                break;
            case 'ajk':
                $user = AJKs::findOrFail($id);
                break;
            default:
                return redirect()->route('users.index')
                    ->with('error', 'Invalid role specified');
        }
        
        return view('users.show', [
            'user' => $user,
            'role' => $role
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
        // Check role access
        $userRole = session('role');
        if ($userRole !== 'admin') {
            Log::warning('Unauthorized access attempt to edit user', [
                'user_id' => session('id'),
                'role' => $userRole,
                'target_role' => $role,
                'target_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Get user based on role
        $user = null;
        switch($role) {
            case 'admin':
                $user = Admins::findOrFail($id);
                break;
            case 'supervisor':
                $user = Supervisors::findOrFail($id);
                break;
            case 'teacher':
                $user = Teachers::findOrFail($id);
                break;
            case 'ajk':
                $user = AJKs::findOrFail($id);
                break;
            default:
                return redirect()->route('users.index')
                    ->with('error', 'Invalid role specified');
        }
        
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
        // Check role access
        $userRole = session('role');
        if ($userRole !== 'admin') {
            Log::warning('Unauthorized access attempt to update user', [
                'user_id' => session('id'),
                'role' => $userRole,
                'target_role' => $role,
                'target_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Get user based on role
        $user = null;
        switch($role) {
            case 'admin':
                $user = Admins::findOrFail($id);
                break;
            case 'supervisor':
                $user = Supervisors::findOrFail($id);
                break;
            case 'teacher':
                $user = Teachers::findOrFail($id);
                break;
            case 'ajk':
                $user = AJKs::findOrFail($id);
                break;
            default:
                return redirect()->route('users.index')
                    ->with('error', 'Invalid role specified');
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'iium_id' => [
                'required',
                'string',
                'size:8',
                'regex:/^[A-Z]{4}\d{4}$/',
                Rule::unique('admins', 'iium_id')->ignore($id),
                Rule::unique('supervisors', 'iium_id')->ignore($id),
                Rule::unique('teachers', 'iium_id')->ignore($id),
                Rule::unique('ajks', 'iium_id')->ignore($id),
            ],
            'name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($id),
                Rule::unique('supervisors', 'email')->ignore($id),
                Rule::unique('teachers', 'email')->ignore($id),
                Rule::unique('ajks', 'email')->ignore($id),
            ],
            'centre_id' => 'required|exists:centres,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Get validated data
        $validatedData = $validator->validated();
        
        // Update user
        $user->iium_id = strtoupper($validatedData['iium_id']);
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->centre_id = $validatedData['centre_id'];
        
        $saved = $user->save();
        
        if (!$saved) {
            return back()->with('fail', 'Something went wrong, try again later');
        }
        
        return redirect()->route('users.index')
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
        // Check role access
        $userRole = session('role');
        if ($userRole !== 'admin') {
            Log::warning('Unauthorized access attempt to delete user', [
                'user_id' => session('id'),
                'role' => $userRole,
                'target_role' => $role,
                'target_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Get user based on role
        $user = null;
        switch($role) {
            case 'admin':
                $user = Admins::findOrFail($id);
                break;
            case 'supervisor':
                $user = Supervisors::findOrFail($id);
                break;
            case 'teacher':
                $user = Teachers::findOrFail($id);
                break;
            case 'ajk':
                $user = AJKs::findOrFail($id);
                break;
            default:
                return redirect()->route('users.index')
                    ->with('error', 'Invalid role specified');
        }
        
        $user->delete();
        
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
        // Check role access
        $userRole = session('role');
        if ($userRole !== 'admin') {
            Log::warning('Unauthorized access attempt to reset user password', [
                'user_id' => session('id'),
                'role' => $userRole,
                'target_role' => $role,
                'target_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
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
        
        // Get user based on role
        $user = null;
        switch($role) {
            case 'admin':
                $user = Admins::findOrFail($id);
                break;
            case 'supervisor':
                $user = Supervisors::findOrFail($id);
                break;
            case 'teacher':
                $user = Teachers::findOrFail($id);
                break;
            case 'ajk':
                $user = AJKs::findOrFail($id);
                break;
            default:
                return redirect()->route('users.index')
                    ->with('error', 'Invalid role specified');
        }
        
        // Update password
        $user->password = Hash::make($request->password);
        $saved = $user->save();
        
        if (!$saved) {
            return back()->with('fail', 'Something went wrong, try again later');
        }
        
        return redirect()->route('users.edit', ['role' => $role, 'id' => $id])
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
        // Check role access
        $userRole = session('role');
        if ($userRole !== 'admin') {
            Log::warning('Unauthorized access attempt to change user status', [
                'user_id' => session('id'),
                'role' => $userRole,
                'target_role' => $role,
                'target_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);
        
        // Get user based on role
        $user = null;
        switch($role) {
            case 'admin':
                $user = Admins::findOrFail($id);
                break;
            case 'supervisor':
                $user = Supervisors::findOrFail($id);
                break;
            case 'teacher':
                $user = Teachers::findOrFail($id);
                break;
            case 'ajk':
                $user = AJKs::findOrFail($id);
                break;
            default:
                return redirect()->route('users.index')
                    ->with('error', 'Invalid role specified');
        }
        
        // Update status
        $user->status = $request->status;
        $saved = $user->save();
        
        if (!$saved) {
            return back()->with('fail', 'Something went wrong, try again later');
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User status updated successfully');
    }
}