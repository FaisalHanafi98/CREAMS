<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\LetterTemplate;
use App\Models\Letter;
use Exception;

class UserProfileController extends Controller
{
    /**
     * Show the user profile page
     * 
     * @return \Illuminate\View\View
     */
    public function showProfile()
    {
        try {
            // Get user data from session
            $roleId = session('id');
            $role = session('role');
            
            Log::info('Profile page accessed', [
                'user_id' => $roleId,
                'role' => $role,
                'session_id' => session()->getId()
            ]);
            
            if (!$roleId || !$role) {
                Log::warning('Incomplete session data when accessing profile', [
                    'session_data' => session()->all()
                ]);
                
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
            
            // Fetch user data from database
            $user = User::find($roleId);
            
            if (!$user) {
                Log::warning('User not found in database', ['user_id' => $roleId]);
                return redirect()->route('auth.loginpage')
                    ->with('error', 'User not found. Please log in again.');
            }
            
            $userData = $user->toArray();
            Log::info('User found in database', [
                'user_id' => $roleId,
                'phone' => $userData['phone'] ?? 'NULL',
                'address' => $userData['address'] ?? 'NULL',
                'about' => $userData['about'] ?? 'NULL',
                'date_of_birth' => $userData['date_of_birth'] ?? 'NULL'
            ]);
            
            
            // Add role information
            $userData['role'] = $role;
            
            // Handle avatar - prefer database avatar over session avatar for consistency
            // If both exist, database takes precedence; if database is empty, use session
            if (!empty($userData['avatar'])) {
                // Database has avatar, update session to match
                session(['avatar' => $userData['avatar'], 'user_avatar' => $userData['avatar']]);
            } elseif (session()->has('avatar')) {
                // No avatar in database but session has one, use session
                $userData['avatar'] = session('avatar');
            }
            
            // Handle bio/about field - use 'about' from database as 'bio' for the form
            $userData['bio'] = $userData['about'] ?? '';
            
            // Format date of birth for HTML date input
            if (!empty($userData['date_of_birth'])) {
                try {
                    // Handle both Carbon instances and string dates
                    if ($userData['date_of_birth'] instanceof \Carbon\Carbon) {
                        $userData['date_of_birth'] = $userData['date_of_birth']->format('Y-m-d');
                    } else {
                        $userData['date_of_birth'] = date('Y-m-d', strtotime($userData['date_of_birth']));
                    }
                } catch (Exception $e) {
                    Log::warning('Error formatting date of birth', [
                        'date' => $userData['date_of_birth'],
                        'error' => $e->getMessage()
                    ]);
                    $userData['date_of_birth'] = '';
                }
            }
            
            
            // Load letter data for admin users
            $activeTemplate = null;
            $recentLetters = collect();
            
            if ($role === 'admin') {
                try {
                    // Load active letter template
                    $activeTemplate = \App\Models\LetterTemplate::getActive();
                    
                    // Load recent letters (last 10)
                    $recentLetters = \App\Models\Letter::with('generator')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                        
                    Log::info('Letter data loaded for admin', [
                        'active_template' => $activeTemplate ? $activeTemplate->id : null,
                        'recent_letters_count' => $recentLetters->count()
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to load letter data', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            // Return the profile view with user data
            return view('profile.home', [
                'user' => $userData,
                'role' => $role,
                'debug' => config('app.debug'),
                'activeTemplate' => $activeTemplate,
                'recentLetters' => $recentLetters
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying profile page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'There was a problem accessing your profile. Please try again later.');
        }
    }
    
    /**
     * Update user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            // Get user data from session
            $roleId = session('id');
            $role = session('role');
            
            Log::info('Profile update attempted', [
                'user_id' => $roleId,
                'role' => $role,
                'request_data' => $request->all(),
                'has_address' => $request->has('address'),
                'has_about' => $request->has('about'), 
                'has_date_of_birth' => $request->has('date_of_birth'),
                'address_value' => $request->input('address'),
                'about_value' => $request->input('about'),
                'date_of_birth_value' => $request->input('date_of_birth')
            ]);
            
            if (!$roleId || !$role) {
                Log::warning('Incomplete session data when updating profile');
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
            
            // Build validation rules based on what fields are being updated
            $validationRules = [
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'about' => 'nullable|string|max:1000',
                'date_of_birth' => 'nullable|date|before:today',
                'education_level' => 'nullable|in:Diploma,Bachelor\'s,Master\'s,PhD',
                'education_specialization' => 'nullable|string|max:255',
                'teaching_specialization' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
            ];
            
            // Only require name and email if they're being updated
            if ($request->has('name')) {
                $validationRules['name'] = 'required|string|max:255';
            }
            if ($request->has('email')) {
                $validationRules['email'] = 'required|email|max:255';
            }
            
            $validated = $request->validate($validationRules);
            
            Log::info('Validation successful', [
                'validated_data' => $validated,
                'contains_address' => array_key_exists('address', $validated),
                'contains_about' => array_key_exists('about', $validated),
                'contains_date_of_birth' => array_key_exists('date_of_birth', $validated)
            ]);
            
            // Find user
            $user = User::findOrFail($roleId);
            
            // Check if email is unique (if changed)
            if (isset($validated['email']) && $user->email !== $validated['email']) {
                $emailExists = User::where('email', $validated['email'])
                    ->where('id', '!=', $roleId)
                    ->exists();
                    
                if ($emailExists) {
                    return redirect()->back()
                        ->with('error', 'Email address is already in use by another account.')
                        ->withInput();
                }
            }
            
            // Only update fields that were provided in the request
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (array_key_exists('phone', $validated)) {
                $user->phone = $validated['phone'];
            }
            if (array_key_exists('address', $validated)) {
                $user->address = $validated['address'];
            }
            if (array_key_exists('about', $validated)) {
                $user->about = $validated['about'];
            }
            if (array_key_exists('education_level', $validated)) {
                $user->education_level = $validated['education_level'];
            }
            if (array_key_exists('education_specialization', $validated)) {
                $user->education_specialization = $validated['education_specialization'];
            }
            if (array_key_exists('teaching_specialization', $validated)) {
                $user->teaching_specialization = $validated['teaching_specialization'];
            }
            if (array_key_exists('position', $validated)) {
                $user->position = $validated['position'];
            }
            if (array_key_exists('date_of_birth', $validated)) {
                $user->date_of_birth = $validated['date_of_birth'];
            }
            
            // Alternative approach - try mass assignment for problematic fields
            Log::info('User attributes before assignment', [
                'address_before' => $user->address,
                'about_before' => $user->about,
                'date_of_birth_before' => $user->date_of_birth
            ]);
            
            // Force update using mass assignment as backup
            $updateData = [];
            foreach (['address', 'about', 'date_of_birth'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updateData[$field] = $validated[$field];
                }
            }
            
            if (!empty($updateData)) {
                Log::info('Attempting mass assignment for critical fields', $updateData);
                $user->fill($updateData);
            }
            
            Log::info('User attributes after assignment', [
                'address_after' => $user->address,
                'about_after' => $user->about,
                'date_of_birth_after' => $user->date_of_birth,
                'is_dirty' => $user->isDirty(),
                'dirty_fields' => $user->getDirty()
            ]);
            
            Log::info('About to save user', [
                'user_id' => $roleId,
                'changes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'about' => $user->about,
                    'date_of_birth' => $user->date_of_birth,
                    'education_level' => $user->education_level,
                    'education_specialization' => $user->education_specialization,
                    'teaching_specialization' => $user->teaching_specialization,
                    'position' => $user->position,
                ]
            ]);
            
            // Save user
            $saved = $user->save();
            
            Log::info('User save result', [
                'user_id' => $roleId,
                'saved' => $saved,
                'changes' => $user->getChanges(),
                'final_address' => $user->address,
                'final_about' => $user->about,
                'final_date_of_birth' => $user->date_of_birth
            ]);
            
            // Verify by reloading from database
            $reloadedUser = User::find($roleId);
            Log::info('Database verification after save', [
                'db_address' => $reloadedUser->address,
                'db_about' => $reloadedUser->about,
                'db_date_of_birth' => $reloadedUser->date_of_birth
            ]);
            
            if (!$saved) {
                Log::error('User save failed');
                return redirect()->back()
                    ->with('error', 'Failed to update profile. Please try again.')
                    ->withInput();
            }
            
            // Update session data only for changed fields
            $sessionUpdates = [];
            if (isset($validated['name'])) {
                $sessionUpdates['name'] = $user->name;
            }
            if (isset($validated['email'])) {
                $sessionUpdates['email'] = $user->email;
            }
            if (array_key_exists('phone', $validated)) {
                $sessionUpdates['phone'] = $user->phone;
            }
            if (array_key_exists('address', $validated)) {
                $sessionUpdates['address'] = $user->address;
            }
            if (array_key_exists('about', $validated)) {
                $sessionUpdates['about'] = $user->about;
                $sessionUpdates['bio'] = $user->about; // Store both for compatibility
            }
            if (array_key_exists('education_level', $validated)) {
                $sessionUpdates['education_level'] = $user->education_level;
            }
            if (array_key_exists('education_specialization', $validated)) {
                $sessionUpdates['education_specialization'] = $user->education_specialization;
            }
            if (array_key_exists('teaching_specialization', $validated)) {
                $sessionUpdates['teaching_specialization'] = $user->teaching_specialization;
            }
            if (array_key_exists('position', $validated)) {
                $sessionUpdates['position'] = $user->position;
            }
            if (array_key_exists('date_of_birth', $validated)) {
                $sessionUpdates['date_of_birth'] = $user->date_of_birth;
            }
            
            Log::info('Session updates prepared', [
                'session_updates' => $sessionUpdates,
                'user_id' => $roleId
            ]);
            
            if (!empty($sessionUpdates)) {
                session($sessionUpdates);
                Log::info('Session updated successfully', [
                    'updated_fields' => array_keys($sessionUpdates),
                    'user_id' => $roleId
                ]);
            }
            
            Log::info('Profile updated successfully', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            // Check if this is an AJAX request and respond accordingly
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your profile has been updated successfully.',
                    'data' => [
                        'address' => $user->address,
                        'about' => $user->about,
                        'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', 'Your profile has been updated successfully.');
            
        } catch (Exception $e) {
            Log::error('Exception during profile update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again later.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'An unexpected error occurred. Please try again later.')
                ->withInput();
        }
    }
    
    /**
     * Change user password
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Get user data from session
            $roleId = session('id');
            $role = session('role');
            
            Log::info('Password change attempted', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            if (!$roleId || !$role) {
                Log::warning('Incomplete session data when changing password', [
                    'session_data' => session()->all()
                ]);
                
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
            
            // First validate only required fields
            $basicValidator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required',
                'new_password_confirmation' => 'required'
            ], [
                'current_password.required' => 'Your current password is required.',
                'new_password.required' => 'The new password is required.',
                'new_password_confirmation.required' => 'Please confirm your new password.'
            ]);
            
            if ($basicValidator->fails()) {
                return redirect()->back()
                    ->withErrors($basicValidator);
            }
            
            // Get user and verify current password first
            $user = User::find($roleId);
            if (!$user) {
                Log::warning('User not found during password change', ['user_id' => $roleId]);
                return redirect()->back()
                    ->with('error', 'User not found. Please log in again.');
            }
            
            // Check if current password is correct FIRST
            if (!Hash::check($request->current_password, $user->password)) {
                Log::warning('Incorrect current password during password change', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Your current password is incorrect.');
            }
            
            // Only after current password is verified, validate new password format
            $passwordValidator = Validator::make($request->all(), [
                'new_password' => [
                    'min:8',
                    'confirmed',
                    'different:current_password',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                ]
            ], [
                'new_password.min' => 'Your password must be at least 8 characters long.',
                'new_password.confirmed' => 'The password confirmation does not match.',
                'new_password.different' => 'Your new password cannot be the same as your current password.',
                'new_password.regex' => 'Your password must include at least one uppercase letter, one lowercase letter, one number, and one special character.'
            ]);
            
            if ($passwordValidator->fails()) {
                return redirect()->back()
                    ->withErrors($passwordValidator);
            }
            
            // Update password (user is already verified)
            $passwordUpdateSuccess = false;
            
            try {
                // Update password
                $user->password = Hash::make($request->new_password);
                $saved = $user->save();
                
                if ($saved) {
                    $passwordUpdateSuccess = true;
                    Log::info('Password changed with Eloquent model', [
                        'user_id' => $roleId
                    ]);
                }
            } catch (Exception $e) {
                Log::warning('Error changing password with Eloquent', [
                    'error' => $e->getMessage(),
                    'user_id' => $roleId
                ]);
            }
            
            // If Eloquent update failed, try direct DB update
            if (!$passwordUpdateSuccess) {
                try {
                    // First verify current password
                    $currentUser = DB::table('users')
                        ->where('id', $roleId)
                        ->first();
                        
                    if ($currentUser && !Hash::check($request->current_password, $currentUser->password)) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Your current password is incorrect.');
                    }
                    
                    // Update with direct query
                    $updated = DB::table('users')
                        ->where('id', $roleId)
                        ->update([
                            'password' => Hash::make($request->new_password)
                        ]);
                        
                    if ($updated) {
                        $passwordUpdateSuccess = true;
                        Log::info('Password changed with direct DB query', [
                            'user_id' => $roleId
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Error changing password with direct query', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            if (!$passwordUpdateSuccess) {
                Log::error('Failed to change password through any method', [
                    'user_id' => $roleId
                ]);
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Failed to update password. Please try again.');
            }
            
            DB::commit();
            
            Log::info('Password changed successfully', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('success', 'Your password has been changed successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Exception during password change', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }
    
    /**
     * Upload user avatar
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadAvatar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Get user data from session
            $roleId = session('id');
            $role = session('role');
            
            Log::info('Avatar upload attempted', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            if (!$roleId || !$role) {
                Log::warning('Incomplete session data when uploading avatar', [
                    'session_data' => session()->all()
                ]);
                
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            ], [
                'avatar.required' => 'Please select an image to upload.',
                'avatar.image' => 'The uploaded file must be an image.',
                'avatar.mimes' => 'Allowed image formats are: JPEG, PNG, JPG, GIF.',
                'avatar.max' => 'The image size must not exceed 2MB.'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator);
            }
            
            // Ensure the avatar directory exists
            $avatarsPath = storage_path('app/public/avatars');
            if (!file_exists($avatarsPath)) {
                if (!mkdir($avatarsPath, 0775, true)) {
                    Log::error('Failed to create avatars directory', ['path' => $avatarsPath]);
                    
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Server configuration error: Could not create storage directory');
                }
            }
            
            // Test write permissions
            if (!is_writable($avatarsPath)) {
                Log::error('Avatars directory is not writable', ['path' => $avatarsPath]);
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Server configuration error: Storage directory is not writable');
            }
            
            // Delete old avatar if exists
            $oldAvatarPath = null;
            
            // Try to get current avatar from session or user model
            $currentAvatar = session('avatar') ?? session('avatar');
            
            if (!$currentAvatar) {
                try {
                    $user = User::find($roleId);
                    if ($user) {
                        $currentAvatar = $user->avatar ?? $user->avatar;
                    }
                } catch (Exception $e) {
                    Log::warning('Error getting current avatar from model', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            if ($currentAvatar) {
                $oldAvatarPath = 'public/avatars/' . $currentAvatar;
                
                if (Storage::exists($oldAvatarPath)) {
                    try {
                        Storage::delete($oldAvatarPath);
                        Log::info('Deleted old avatar file', [
                            'path' => $oldAvatarPath
                        ]);
                    } catch (Exception $e) {
                        Log::warning('Failed to delete old avatar', [
                            'path' => $oldAvatarPath,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with upload even if delete fails
                    }
                }
            }
            
            // Generate a unique avatar filename
            $avatarName = $role . '_' . $roleId . '_' . Str::random(10) . '.' . $request->avatar->extension();
            
            // Log file details for debugging
            Log::info('Avatar file details', [
                'original_name' => $request->avatar->getClientOriginalName(),
                'size' => $request->avatar->getSize(),
                'mime' => $request->avatar->getMimeType(),
                'new_name' => $avatarName
            ]);
            
            // Store the file
            try {
                $path = $request->avatar->storeAs('avatars', $avatarName, 'public');
                
                // Verify the file was actually saved
                if (!Storage::exists('public/avatars/' . $avatarName)) {
                    Log::error('Avatar file was not saved properly', [
                        'expected_path' => 'public/avatars/' . $avatarName
                    ]);
                    
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Failed to save avatar file. Please try again.');
                }
                
                Log::info('Avatar file stored successfully', [
                    'path' => $path
                ]);
            } catch (Exception $e) {
                Log::error('Failed to store avatar file', [
                    'error' => $e->getMessage()
                ]);
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Failed to save avatar file: ' . $e->getMessage());
            }
            
            // Update avatar field in database
            $avatarUpdateSuccess = false;
            
            try {
                // Try with Eloquent first
                $user = User::find($roleId);
                if ($user) {
                    $user->avatar = $avatarName;
                    $saved = $user->save();
                    
                    if ($saved) {
                        $avatarUpdateSuccess = true;
                        Log::info('Avatar reference updated in database with Eloquent', [
                            'user_id' => $roleId,
                            'avatar' => $avatarName,
                            'user_changes' => $user->getChanges()
                        ]);
                    } else {
                        Log::warning('Eloquent save returned false for avatar update', [
                            'user_id' => $roleId,
                            'avatar' => $avatarName
                        ]);
                    }
                } else {
                    Log::warning('User not found for avatar update', ['user_id' => $roleId]);
                }
            } catch (Exception $e) {
                Log::warning('Error updating avatar reference with Eloquent', [
                    'error' => $e->getMessage(),
                    'user_id' => $roleId
                ]);
            }
            
            // If Eloquent update failed, try direct DB update
            if (!$avatarUpdateSuccess) {
                try {
                    $updated = DB::table('users')
                        ->where('id', $roleId)
                        ->update(['avatar' => $avatarName]);
                        
                    if ($updated) {
                        $avatarUpdateSuccess = true;
                        Log::info('Avatar reference updated in database with direct query', [
                            'user_id' => $roleId,
                            'avatar' => $avatarName,
                            'rows_affected' => $updated
                        ]);
                    } else {
                        Log::warning('Direct DB update affected 0 rows for avatar', [
                            'user_id' => $roleId,
                            'avatar' => $avatarName
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Error updating avatar reference with direct query', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            if (!$avatarUpdateSuccess) {
                Log::error('Failed to update avatar reference in database', [
                    'user_id' => $roleId,
                    'avatar' => $avatarName,
                    'path' => $path
                ]);
                
                // Clean up the uploaded file if database update failed
                if (Storage::exists('public/avatars/' . $avatarName)) {
                    Storage::delete('public/avatars/' . $avatarName);
                }
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Failed to save avatar to database. Please try again.');
            }
            
            // Update session data regardless of database update success
            session([
                'avatar' => $avatarName,
                'user_avatar' => $avatarName
            ]);
            
            DB::commit();
            
            Log::info('Avatar upload completed successfully', [
                'user_id' => $roleId,
                'role' => $role,
                'avatar' => $avatarName,
                'path' => $path
            ]);
            
            return redirect()->route('profile')->with('success', 'Your profile photo has been updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Exception during avatar upload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while uploading your profile photo: ' . $e->getMessage());
        }
    }
    
    /**
     * Save a letter template
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveTemplate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_name' => 'required|string|max:255',
                'template_description' => 'nullable|string|max:1000',
                'header_image_path' => 'nullable|string',
                'footer_image_path' => 'nullable|string',
                'header_text' => 'nullable|string',
                'footer_text' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $roleId = session('id');
            $centreId = session('centre_id');

            if (!$roleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Allow multiple templates to coexist - don't deactivate previous ones
            // Users can choose which template to use for each letter

            // Handle base64 image conversion to files
            $headerImagePath = null;
            $footerImagePath = null;

            // Process header image if it's base64
            if ($request->header_image_path && str_starts_with($request->header_image_path, 'data:image/')) {
                $headerImagePath = $this->saveBase64Image($request->header_image_path, 'header');
                Log::info('Header image saved from base64', ['path' => $headerImagePath]);
            }

            // Process footer image if it's base64
            if ($request->footer_image_path && str_starts_with($request->footer_image_path, 'data:image/')) {
                $footerImagePath = $this->saveBase64Image($request->footer_image_path, 'footer');
                Log::info('Footer image saved from base64', ['path' => $footerImagePath]);
            }

            $template = new LetterTemplate();
            $template->template_name = $request->template_name;
            $template->template_description = $request->template_description ?? '';
            $template->template_content = '<div class="main-content">[CONTENT]</div>'; // Default template content
            $template->template_type = 'letter'; // Add required template_type field
            $template->header_image_path = $headerImagePath;
            $template->footer_image_path = $footerImagePath;
            $template->header_text = $request->header_text ?? '';
            $template->footer_text = $request->footer_text ?? '';
            $template->centre_id = $centreId ?? '001';
            $template->created_by = $roleId;
            $template->is_active = true;
            $template->usage_count = 0;
            // Simplified template variables for better performance
            $template->template_variables = [
                'header_image' => $headerImagePath,
                'footer_image' => $footerImagePath,
            ];
            $template->save();

            Log::info('Template saved successfully', [
                'template_id' => $template->id,
                'user_id' => $roleId,
                'template_name' => $template->template_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'template_id' => $template->id,
                'template_name' => $template->template_name,
                'template' => $template
            ]);

        } catch (Exception $e) {
            Log::error('Error saving template', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load templates for current centre
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadTemplates(Request $request)
    {
        try {
            $centreId = session('centre_id');
            
            $templates = LetterTemplate::where('centre_id', $centreId)
                ->where('is_active', true)
                ->orderBy('last_used_at', 'desc')
                ->orderBy('usage_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->with('creator:id,name')
                ->get();

            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);

        } catch (Exception $e) {
            Log::error('Error loading templates', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load a specific template
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadTemplate(Request $request)
    {
        try {
            $templateId = $request->input('template_id');
            $centreId = session('centre_id');

            if (!$templateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template ID is required'
                ], 400);
            }

            $template = LetterTemplate::where('id', $templateId)
                ->where('centre_id', $centreId)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Update usage count and last used timestamp
            $template->increment('usage_count');
            $template->update(['last_used_at' => now()]);

            return response()->json([
                'success' => true,
                'template' => $template
            ]);

        } catch (Exception $e) {
            Log::error('Error loading template', [
                'error' => $e->getMessage(),
                'template_id' => $request->input('template_id'),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get letter archive for current centre
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLetterArchive(Request $request)
    {
        try {
            $centreId = session('centre_id');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search');

            $query = Letter::where('centre_id', $centreId)
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->search($search);
            }

            $letters = $query->with(['createdBy:id,name', 'template:id,template_name'])
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'letters' => $letters
            ]);

        } catch (Exception $e) {
            Log::error('Error loading letter archive', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load letter archive: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save base64 image data as a file
     * 
     * @param string $base64Data
     * @param string $type (header|footer)
     * @return string|null
     */
    private function saveBase64Image($base64Data, $type)
    {
        try {
            // Extract the image data from base64 string
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode($matches[2]);
                
                // Validate image type
                $allowedTypes = ['jpeg', 'jpg', 'png', 'gif'];
                if (!in_array(strtolower($imageType), $allowedTypes)) {
                    Log::warning('Invalid image type for template', ['type' => $imageType]);
                    return null;
                }
                
                // Compress image for better performance
                $compressedImageData = $this->compressImage($imageData, $imageType);
                
                // Generate unique filename
                $filename = $type . '_' . time() . '_' . uniqid() . '.jpeg'; // Always save as JPEG for consistency
                
                // Ensure template_images directory exists
                Storage::makeDirectory('public/template_images');
                
                // Save the compressed image
                $saved = Storage::put('public/template_images/' . $filename, $compressedImageData);
                
                if ($saved) {
                    $savedSize = Storage::size('public/template_images/' . $filename);
                    Log::info('Template image saved and compressed', [
                        'type' => $type,
                        'filename' => $filename,
                        'original_size' => strlen($imageData),
                        'compressed_size' => $savedSize,
                        'compression_ratio' => round((1 - $savedSize / strlen($imageData)) * 100, 1) . '%'
                    ]);
                    
                    return 'template_images/' . $filename;
                } else {
                    Log::error('Failed to save base64 image', ['type' => $type]);
                    return null;
                }
            } else {
                Log::warning('Invalid base64 image format', ['type' => $type]);
                return null;
            }
        } catch (Exception $e) {
            Log::error('Error saving base64 image', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Compress image data for better performance
     */
    private function compressImage($imageData, $imageType)
    {
        try {
            // Create image resource from string
            $image = imagecreatefromstring($imageData);
            
            if (!$image) {
                Log::warning('Failed to create image resource for compression');
                return $imageData; // Return original if compression fails
            }
            
            // Get original dimensions
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);
            
            // Calculate new dimensions (max width: 800px for performance)
            $maxWidth = 800;
            $maxHeight = 600;
            
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                // Image is already small enough, just compress quality
                ob_start();
                imagejpeg($image, null, 75); // 75% quality for good balance
                $compressedData = ob_get_contents();
                ob_end_clean();
                imagedestroy($image);
                return $compressedData;
            }
            
            // Calculate scaling ratio
            $widthRatio = $maxWidth / $originalWidth;
            $heightRatio = $maxHeight / $originalHeight;
            $ratio = min($widthRatio, $heightRatio);
            
            $newWidth = intval($originalWidth * $ratio);
            $newHeight = intval($originalHeight * $ratio);
            
            // Create new image with calculated dimensions
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG/GIF
            if ($imageType === 'png' || $imageType === 'gif') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize the image
            imagecopyresampled(
                $resizedImage, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // Output compressed image
            ob_start();
            imagejpeg($resizedImage, null, 75); // 75% quality
            $compressedData = ob_get_contents();
            ob_end_clean();
            
            // Clean up
            imagedestroy($image);
            imagedestroy($resizedImage);
            
            return $compressedData;
            
        } catch (Exception $e) {
            Log::error('Image compression failed', ['error' => $e->getMessage()]);
            return $imageData; // Return original on error
        }
    }
}