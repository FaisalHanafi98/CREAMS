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
use App\Models\Users;
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
            $user = Users::find($roleId);
            
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
            
            // Handle avatar - prefer database avatar, but fall back to session avatar if database is empty
            if (empty($userData['avatar']) && session()->has('avatar')) {
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
            return view('profile', [
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
                'request_data' => $request->all()
            ]);
            
            if (!$roleId || !$role) {
                Log::warning('Incomplete session data when updating profile');
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
            
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'about' => 'nullable|string|max:1000',
                'date_of_birth' => 'nullable|date|before:today',
                'education_level' => 'nullable|in:Diploma,Bachelor\'s,Master\'s,PhD',
                'education_specialization' => 'nullable|string|max:255',
                'teaching_specialization' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
            ]);
            
            // Find user
            $user = Users::findOrFail($roleId);
            
            // Check if email is unique (if changed)
            if ($user->email !== $validated['email']) {
                $emailExists = Users::where('email', $validated['email'])
                    ->where('id', '!=', $roleId)
                    ->exists();
                    
                if ($emailExists) {
                    return redirect()->back()
                        ->with('error', 'Email address is already in use by another account.')
                        ->withInput();
                }
            }
            
            // Update user data
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->address = $validated['address'];
            $user->about = $validated['about']; // Map about field to about column
            
            // Update new education fields
            $user->education_level = $validated['education_level'];
            $user->education_specialization = $validated['education_specialization'];
            $user->teaching_specialization = $validated['teaching_specialization'];
            $user->position = $validated['position'];
            
            if (!empty($validated['date_of_birth'])) {
                $user->date_of_birth = $validated['date_of_birth'];
            }
            
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
                'changes' => $user->getChanges()
            ]);
            
            if (!$saved) {
                Log::error('User save failed');
                return redirect()->back()
                    ->with('error', 'Failed to update profile. Please try again.')
                    ->withInput();
            }
            
            // Update session data
            session([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'about' => $user->about,
                'bio' => $user->about,  // Store both for compatibility
                'date_of_birth' => $user->date_of_birth,
                'education_level' => $user->education_level,
                'education_specialization' => $user->education_specialization,
                'teaching_specialization' => $user->teaching_specialization,
                'position' => $user->position,
            ]);
            
            Log::info('Profile updated successfully', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('success', 'Your profile has been updated successfully.');
            
        } catch (Exception $e) {
            Log::error('Exception during profile update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
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
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => [
                    'required',
                    'min:8',
                    'confirmed',
                    'different:current_password',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                ],
                'new_password_confirmation' => 'required'
            ], [
                'current_password.required' => 'Your current password is required.',
                'new_password.required' => 'The new password is required.',
                'new_password.min' => 'Your password must be at least 8 characters long.',
                'new_password.confirmed' => 'The password confirmation does not match.',
                'new_password.different' => 'Your new password cannot be the same as your current password.',
                'new_password.regex' => 'Your password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'new_password_confirmation.required' => 'Please confirm your new password.'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator);
            }
            
            // Get user model
            $user = null;
            $passwordUpdateSuccess = false;
            
            try {
                $user = Users::find($roleId);
                if ($user) {
                    // Check if current password is correct
                    if (!Hash::check($request->current_password, $user->password)) {
                        Log::warning('Incorrect current password during password change', [
                            'user_id' => $roleId,
                            'role' => $role
                        ]);
                        
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Your current password is incorrect.');
                    }
                    
                    // Update password
                    $user->password = Hash::make($request->new_password);
                    $saved = $user->save();
                    
                    if ($saved) {
                        $passwordUpdateSuccess = true;
                        Log::info('Password changed with Eloquent model', [
                            'user_id' => $roleId
                        ]);
                    }
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
                    $user = Users::find($roleId);
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
                $user = Users::find($roleId);
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
            
            return redirect()->back()->with('success', 'Your profile photo has been updated successfully.');
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
}