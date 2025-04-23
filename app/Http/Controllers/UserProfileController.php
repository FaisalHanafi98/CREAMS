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
            
            // Get user from database to ensure we have the latest data
            $user = Users::find($roleId);
            
            if (!$user) {
                Log::error('User not found in database', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your account could not be found. Please log in again.');
            }
            
            // Create a user data array with standardized fields
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'iium_id' => $user->iium_id,
                'avatar' => $user->avatar,
                'phone' => $user->phone,
                'address' => $user->address,
                'bio' => $user->about, 
                'date_of_birth' => $user->date_of_birth,
                'centre_id' => $user->centre_id
            ];
            
            // Add debug log to see what data is being loaded
            Log::debug('User data loaded for profile page', [
                'userData' => [
                                'name' => $user->name,
                                'email' => $user->email,
                                'phone' => $user->phone,
                                'date_of_birth' => $user->date_of_birth,
                                'address' => $user->address,
                                'bio' => $user->about,
                              ]
            ]);
            
            // Update session with the latest user data
            session([
                'avatar' => $user->avatar,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'about' => $user->about,
                'date_of_birth' => $user->date_of_birth
            ]);
            
            // Return the profile view with user data
            return view('profile', [
                'user' => $userData,
                'role' => $role
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
                return redirect()->route('profile')
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Get user model
            $user = Users::where('id', $roleId)->first();
            
            if (!$user) {
                Log::error('User not found when updating profile', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your account could not be found. Please log in again.');
            }
            
            // Check if email changed and if it's unique
            if ($user->email !== $request->email) {
                $emailExists = Users::where('email', $request->email)
                    ->where('id', '!=', $roleId)
                    ->exists();
                    
                if ($emailExists) {
                    DB::rollBack();
                    return redirect()->route('profile')
                        ->with('error', 'Email address is already in use by another account.')
                        ->withInput();
                }
            }
            
            // Update user data
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->address = $request->address;
            
            // Update bio field (stored in about)
            $user->about = $request->bio;
            
            if ($request->has('date_of_birth') && $request->date_of_birth) {
                $user->date_of_birth = $request->date_of_birth;
            }
            
            $saved = $user->save();
            
            if (!$saved) {
                Log::error('Failed to save user profile', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->route('profile')
                    ->with('error', 'Failed to update profile. Please try again.')
                    ->withInput();
            }
            
            // Add debug log to verify what was saved
            Log::debug('User data saved during update', [
                'updatedFields' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'about' => $user->about,
                    'date_of_birth' => $user->date_of_birth
                ]
            ]);
            
            // Update session data
            session([
                'name' => $user->name, 
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'about' => $user->about,
                'date_of_birth' => $user->date_of_birth
            ]);
            
            DB::commit();
            
            Log::info('Profile updated successfully', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            return redirect()->route('profile')->with('success', 'Your profile has been updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Exception during profile update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('profile')
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
                return redirect()->route('profile')
                    ->withErrors($validator);
            }
            
            // Get user model
            $user = Users::where('id', $roleId)->first();
            
            if (!$user) {
                Log::error('User not found when changing password', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your account could not be found. Please log in again.');
            }
            
            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                Log::warning('Incorrect current password during password change', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->route('profile')
                    ->with('error', 'Your current password is incorrect.');
            }
            
            // Update password
            $user->password = Hash::make($request->new_password);
            $saved = $user->save();
            
            if (!$saved) {
                Log::error('Failed to save new password', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->route('profile')
                    ->with('error', 'Failed to update password. Please try again.');
            }
            
            DB::commit();
            
            Log::info('Password changed successfully', [
                'user_id' => $roleId,
                'role' => $role
            ]);
            
            return redirect()->route('profile')->with('success', 'Your password has been changed successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Exception during password change', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('profile')
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
                return redirect()->route('profile')
                    ->withErrors($validator);
            }
            
            // Get user model
            $user = Users::where('id', $roleId)->first();
            
            if (!$user) {
                Log::error('User not found when uploading avatar', [
                    'user_id' => $roleId,
                    'role' => $role
                ]);
                
                DB::rollBack();
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Your account could not be found. Please log in again.');
            }
            
            // Ensure the avatar directory exists
            $avatarsPath = storage_path('app/public/avatars');
            if (!file_exists($avatarsPath)) {
                if (!mkdir($avatarsPath, 0775, true)) {
                    Log::error('Failed to create avatars directory', ['path' => $avatarsPath]);
                    
                    DB::rollBack();
                    return redirect()->route('profile')
                        ->with('error', 'Server configuration error: Could not create storage directory');
                }
            }
            
            // Test write permissions
            if (!is_writable($avatarsPath)) {
                Log::error('Avatars directory is not writable', ['path' => $avatarsPath]);
                
                DB::rollBack();
                return redirect()->route('profile')
                    ->with('error', 'Server configuration error: Storage directory is not writable');
            }
            
            // Delete old avatar if exists
            $oldAvatarPath = null;
            if (isset($user->avatar) && $user->avatar) {
                $oldAvatarPath = 'public/avatars/' . $user->avatar;
            }
            
            if ($oldAvatarPath && Storage::exists($oldAvatarPath)) {
                Storage::delete($oldAvatarPath);
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
            $path = $request->avatar->storeAs('avatars', $avatarName, 'public');
            
            // Verify the file was actually saved
            if (!Storage::exists('public/avatars/' . $avatarName)) {
                Log::error('Avatar file was not saved properly', [
                    'expected_path' => 'public/avatars/' . $avatarName
                ]);
                
                DB::rollBack();
                return redirect()->route('profile')
                    ->with('error', 'Failed to save avatar file. Please try again.');
            }
            
            // Update avatar field in the database
            $user->avatar = $avatarName;
            $saved = $user->save();
            
            if (!$saved) {
                Log::error('Failed to save avatar reference in database', [
                    'user_id' => $roleId,
                    'role' => $role,
                    'avatar' => $avatarName
                ]);
                
                // Clean up the uploaded file
                Storage::delete('public/avatars/' . $avatarName);
                
                DB::rollBack();
                return redirect()->route('profile')
                    ->with('error', 'Failed to update avatar. Please try again.');
            }
            
            // Update session data
            session(['avatar' => $avatarName]);
            
            DB::commit();
            
            Log::info('Avatar uploaded successfully', [
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
            
            return redirect()->route('profile')
                ->with('error', 'An unexpected error occurred while uploading your profile photo: ' . $e->getMessage());
        }
    }
}