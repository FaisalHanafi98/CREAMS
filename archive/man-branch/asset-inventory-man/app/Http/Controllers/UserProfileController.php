<?php

namespace App\Http\Controllers;

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
            $userData = [];
            $userFound = false;
            
            try {
                $user = Users::find($roleId);
                if ($user) {
                    $userData = $user->toArray();
                    $userFound = true;
                    Log::info('User found in database', ['user_id' => $roleId]);
                }
            } catch (Exception $e) {
                Log::warning('Error retrieving user with Eloquent', [
                    'error' => $e->getMessage(),
                    'user_id' => $roleId
                ]);
            }
            
            // If user not found with Eloquent, try direct query
            if (!$userFound) {
                try {
                    $userDirect = DB::table('users')->where('id', $roleId)->first();
                    if ($userDirect) {
                        $userData = (array)$userDirect;
                        $userFound = true;
                        Log::info('User found with direct query', ['user_id' => $roleId]);
                    }
                } catch (Exception $e) {
                    Log::warning('Error retrieving user with direct query', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            // If still not found, build a basic array from session data
            if (!$userFound) {
                Log::warning('User not found in database, using session data only', [
                    'user_id' => $roleId
                ]);
                
                $userData = [
                    'id' => $roleId,
                    'role' => $role,
                    'name' => session('name'),
                    'email' => session('email'),
                    'iium_id' => session('iium_id')
                ];
            }
            
            // Add role information
            $userData['role'] = $role;
            
            // Handle bio/about field inconsistency
            if (isset($userData['about']) && !empty($userData['about'])) {
                $userData['bio'] = $userData['about'];
            } else if (isset($userData['bio']) && !empty($userData['bio'])) {
                $userData['about'] = $userData['bio'];
            }
            
            // =================================================================
            // CRITICAL FIX: Ensure session data is used as fallback
            // =================================================================
            $sessionFields = [
                'phone', 
                'address', 
                'bio', 
                'about', 
                'date_of_birth', 
                'avatar', 
                
            ];
            
            $sessionUsed = false;
            
            foreach ($sessionFields as $field) {
                // If user data is empty but session has data, use session data
                if (
                    (!isset($userData[$field]) || empty($userData[$field]) || $userData[$field] === null) && 
                    session()->has($field) && 
                    !empty(session($field))
                ) {
                    $oldValue = isset($userData[$field]) ? var_export($userData[$field], true) : 'null';
                    $userData[$field] = session($field);
                    
                    Log::info("Using session data for {$field}", [
                        'from' => $oldValue,
                        'to' => $userData[$field]
                    ]);
                    
                    $sessionUsed = true;
                }
            }
            
            if ($sessionUsed) {
                Log::info('Session data was used as fallback', [
                    'user_id' => $roleId,
                    'fields_using_session' => array_filter($sessionFields, function($field) use ($userData) {
                        return isset($userData[$field]) && $userData[$field] === session($field);
                    })
                ]);
            }
            
            // =================================================================
            // ADDITIONAL FIX: Ensure we have data in all required fields
            // =================================================================
            $requiredFields = [
                'name', 'email', 'phone', 'address', 'bio', 'about', 
                'date_of_birth', 'avatar'
            ];
            
            foreach ($requiredFields as $field) {
                if (!isset($userData[$field])) {
                    $userData[$field] = '';
                }
                
                // Ensure field is not null
                if ($userData[$field] === null) {
                    $userData[$field] = '';
                }
                
                // Handle special case for empty strings that might be coming from the database
                if ($userData[$field] === '' || $userData[$field] === '0000-00-00' || $userData[$field] === '0000-00-00 00:00:00') {
                    $userData[$field] = '';
                }
            }
            
            // Handle date formatting if date exists
            if (!empty($userData['date_of_birth']) && $userData['date_of_birth'] != '') {
                try {
                    $userData['date_of_birth'] = date('Y-m-d', strtotime($userData['date_of_birth']));
                } catch (Exception $e) {
                    Log::warning('Error formatting date of birth', [
                        'date' => $userData['date_of_birth'],
                        'error' => $e->getMessage()
                    ]);
                    $userData['date_of_birth'] = '';
                }
            }
            
            // Handle avatar field inconsistency
            if (empty($userData['avatar']) && !empty($userData['avatar'])) {
                $userData['avatar'] = $userData['avatar'];
            } else if (empty($userData['avatar']) && !empty($userData['avatar'])) {
                $userData['avatar'] = $userData['avatar'];
            }
            
            // Update session with consolidated data for next page load
            $fieldsToUpdate = [];
            
            foreach ($sessionFields as $field) {
                if (!empty($userData[$field]) && (empty(session($field)) || session($field) !== $userData[$field])) {
                    session([$field => $userData[$field]]);
                    $fieldsToUpdate[] = $field;
                }
            }
            
            if (!empty($fieldsToUpdate)) {
                Log::info('Updated session data for future use', [
                    'fields_updated' => $fieldsToUpdate
                ]);
            }
            
            // Log final prepared data for debugging
            Log::debug('Final profile data prepared for view', [
                'user_data_keys' => array_keys($userData),
                'phone' => $userData['phone'],
                'address' => $userData['address'],
                'bio' => $userData['bio'],
                'date_of_birth' => $userData['date_of_birth'],
                'from_session' => $sessionUsed
            ]);
            
            // Return the profile view with user data
            return view('profile', [
                'user' => $userData,
                'role' => $role,
                'debug' => config('app.debug')
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
        DB::beginTransaction();
        
        try {
            // Get user data from session
            $roleId = session('id');
            $role = session('role');
            
            Log::info('Profile update attempted', [
                'user_id' => $roleId,
                'role' => $role,
                'data' => $request->except(['password', 'new_password', 'password_confirmation'])
            ]);
            
            if (!$roleId || !$role) {
                Log::warning('Incomplete session data when updating profile', [
                    'session_data' => session()->all()
                ]);
                
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
            
            // Validate input with custom error messages
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'bio' => 'nullable|string|max:1000',
                'date_of_birth' => 'nullable|date|before:today',
            ], [
                'name.required' => 'Your name is required.',
                'email.required' => 'Your email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'phone.max' => 'Phone number must not exceed 20 characters.',
                'date_of_birth.before' => 'Date of birth must be in the past.',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Get user model
            $user = null;
            $updateSuccess = false;
            
            try {
                $user = Users::find($roleId);
                if ($user) {
                    // Check if email changed and if it's unique
                    if ($user->email !== $request->email) {
                        $emailExists = Users::where('email', $request->email)
                            ->where('id', '!=', $roleId)
                            ->exists();
                            
                        if ($emailExists) {
                            DB::rollBack();
                            return redirect()->back()
                                ->with('error', 'Email address is already in use by another account.')
                                ->withInput();
                        }
                    }
                    
                    // Update user data with model
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->phone = $request->phone;
                    $user->address = $request->address;
                    $user->bio = $request->bio ?? $user->bio;
                    $user->about = $request->bio ?? $user->about; // Update both fields for compatibility
                    
                    if ($request->has('date_of_birth') && $request->date_of_birth) {
                        $user->date_of_birth = $request->date_of_birth;
                    }
                    
                    $saved = $user->save();
                    
                    if ($saved) {
                        $updateSuccess = true;
                        Log::info('Profile updated with Eloquent model', [
                            'user_id' => $roleId
                        ]);
                    }
                }
            } catch (Exception $e) {
                Log::warning('Error updating user profile with Eloquent', [
                    'error' => $e->getMessage(),
                    'user_id' => $roleId
                ]);
            }
            
            // If Eloquent update failed, try direct DB update
            if (!$updateSuccess) {
                try {
                    // Set update data
                    $updateData = [
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'bio' => $request->bio,
                        'about' => $request->bio // Update both fields for compatibility
                    ];
                    
                    if ($request->has('date_of_birth') && $request->date_of_birth) {
                        $updateData['date_of_birth'] = $request->date_of_birth;
                    }
                    
                    // Check if email changed and if it's unique
                    $existingEmail = DB::table('users')
                        ->where('id', '!=', $roleId)
                        ->where('email', $request->email)
                        ->exists();
                        
                    if ($existingEmail) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Email address is already in use by another account.')
                            ->withInput();
                    }
                    
                    // Update with direct query
                    $updated = DB::table('users')
                        ->where('id', $roleId)
                        ->update($updateData);
                        
                    if ($updated) {
                        $updateSuccess = true;
                        Log::info('Profile updated with direct DB query', [
                            'user_id' => $roleId
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Error updating user profile with direct query', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            if (!$updateSuccess) {
                Log::error('Failed to update profile through any method', [
                    'user_id' => $roleId
                ]);
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Failed to update profile. Please try again.')
                    ->withInput();
            }
            
            // Update session data
            session([
                'name' => $request->name, 
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'bio' => $request->bio,
                'about' => $request->bio,
                'date_of_birth' => $request->date_of_birth
            ]);
            
            DB::commit();
            
            Log::info('Profile updated successfully', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('success', 'Your profile has been updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            
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
                    // Update both fields for compatibility
                    if (isset($user->avatar)) {
                        $user->avatar = $avatarName;
                    }
                    
                    $saved = $user->save();
                    
                    if ($saved) {
                        $avatarUpdateSuccess = true;
                        Log::info('Avatar reference updated in database with Eloquent', [
                            'user_id' => $roleId,
                            'avatar' => $avatarName
                        ]);
                    }
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
                    // Update both fields for compatibility
                    $updateData = [];
                    
                    // Check which fields exist in the users table
                    $hasAvatarField = DB::getSchemaBuilder()->hasColumn('users', 'avatar');
    
                    
                    if ($hasAvatarField) {
                        $updateData['avatar'] = $avatarName;
                    }
                    
                    
                    
                    if (!empty($updateData)) {
                        $updated = DB::table('users')
                            ->where('id', $roleId)
                            ->update($updateData);
                            
                        if ($updated) {
                            $avatarUpdateSuccess = true;
                            Log::info('Avatar reference updated in database with direct query', [
                                'user_id' => $roleId,
                                'avatar' => $avatarName,
                                'fields_updated' => array_keys($updateData)
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error updating avatar reference with direct query', [
                        'error' => $e->getMessage(),
                        'user_id' => $roleId
                    ]);
                }
            }
            
            if (!$avatarUpdateSuccess) {
                Log::warning('Failed to update avatar reference in database, but file was saved', [
                    'user_id' => $roleId,
                    'avatar' => $avatarName,
                    'path' => $path
                ]);
                
                // Continue anyway since the file was successfully uploaded
                // Just log a warning instead of failing the operation
            }
            
            // Update session data regardless of database update success
            session([
                'avatar' => $avatarName,
                
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