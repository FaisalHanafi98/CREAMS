<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;

class UserProfileController extends Controller
{
    /**
     * Show the user profile page
     * 
     * @return \Illuminate\View\View
     */
    public function showProfile()
    {
        // Get user data from session
        $role = session('role');
        $userId = session('id');
        
        Log::info('Profile page accessed', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        // Get user model based on role
        $user = $this->getUserModelByRole($role, $userId);
        
        if (!$user) {
            Log::warning('User not found when accessing profile page', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('error', 'User profile not found.');
        }
        
        // Return the profile view with user data
        return view('profile', [
            'user' => $user,
            'role' => $role
        ]);
    }
    
    /**
     * Update user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        // Get user data from session
        $role = session('role');
        $userId = session('id');
        
        Log::info('Profile update attempted', [
            'user_id' => $userId,
            'role' => $role,
            'data' => $request->except(['password', 'new_password', 'password_confirmation'])
        ]);
        
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'bio' => 'nullable|string|max:1000',
        ]);
        
        // Get user model based on role
        $user = $this->getUserModelByRole($role, $userId);
        
        if (!$user) {
            Log::warning('User not found when updating profile', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('error', 'User profile not found.');
        }
        
        // Check if email changed and if it's unique
        if ($user->email !== $request->email) {
            $emailExists = $this->checkEmailExists($request->email, $role, $userId);
            if ($emailExists) {
                return redirect()->back()->with('error', 'Email address is already in use by another account.');
            }
        }
        
        // Update user data
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->bio = $request->bio;
        
        $user->save();
        
        // Update session data
        session(['name' => $user->name, 'email' => $user->email]);
        
        Log::info('Profile updated successfully', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Change user password
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        // Get user data from session
        $role = session('role');
        $userId = session('id');
        
        Log::info('Password change attempted', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        // Validate input
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required'
        ]);
        
        // Get user model based on role
        $user = $this->getUserModelByRole($role, $userId);
        
        if (!$user) {
            Log::warning('User not found when changing password', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('error', 'User profile not found.');
        }
        
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            Log::warning('Incorrect current password during password change', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }
        
        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        Log::info('Password changed successfully', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        return redirect()->back()->with('success', 'Password changed successfully.');
    }
    
    /**
     * Upload user avatar
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadAvatar(Request $request)
    {
        // Get user data from session
        $role = session('role');
        $userId = session('id');
        
        Log::info('Avatar upload attempted', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        // Validate input with smaller file size limit
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024', // 1MB max
        ]);
        
        // Get user model based on role
        $user = $this->getUserModelByRole($role, $userId);
        
        if (!$user) {
            Log::warning('User not found when uploading avatar', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->back()->with('error', 'User profile not found.');
        }
        
        try {
            // Ensure the avatar directory exists
            $avatarsPath = storage_path('app/public/avatars');
            if (!file_exists($avatarsPath)) {
                if (!mkdir($avatarsPath, 0775, true)) {
                    Log::error('Failed to create avatars directory', ['path' => $avatarsPath]);
                    return redirect()->back()->with('error', 'Server configuration error: Could not create storage directory');
                }
            }
            
            // Test write permissions
            if (!is_writable($avatarsPath)) {
                Log::error('Avatars directory is not writable', ['path' => $avatarsPath]);
                return redirect()->back()->with('error', 'Server configuration error: Storage directory is not writable');
            }
            
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            
            // Store new avatar with a more unique name
            $avatarName = $role . '_' . $userId . '_' . time() . '.' . $request->avatar->extension();
            
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
            if (!Storage::disk('public')->exists('avatars/' . $avatarName)) {
                Log::error('Avatar file was not saved properly', [
                    'expected_path' => 'avatars/' . $avatarName
                ]);
                return redirect()->back()->with('error', 'Failed to save avatar file. Please try again.');
            }
            
            // Update user avatar in database
            $user->avatar = $avatarName;
            $user->save();
            
            Log::info('Avatar uploaded successfully', [
                'user_id' => $userId,
                'role' => $role,
                'avatar' => $avatarName,
                'path' => $path
            ]);
            
            return redirect()->back()->with('success', 'Avatar uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('Error uploading avatar', [
                'user_id' => $userId,
                'role' => $role,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while uploading your avatar: ' . $e->getMessage());
        }
    }
    
    /**
     * Get user model based on role and ID
     * 
     * @param string $role
     * @param int $userId
     * @return mixed
     */
    private function getUserModelByRole($role, $userId)
    {
        switch ($role) {
            case 'admin':
                return Admins::find($userId);
            case 'supervisor':
                return Supervisors::find($userId);
            case 'teacher':
                return Teachers::find($userId);
            case 'ajk':
                return AJKs::find($userId);
            default:
                return null;
        }
    }
    
    /**
     * Check if email exists in any user table except for the current user
     * 
     * @param string $email
     * @param string $role
     * @param int $userId
     * @return bool
     */
    private function checkEmailExists($email, $role, $userId)
    {
        // Check in Admins table
        if ($role !== 'admin' && Admins::where('email', $email)->exists()) {
            return true;
        }
        
        // Check in Supervisors table
        if ($role !== 'supervisor' && Supervisors::where('email', $email)->exists()) {
            return true;
        }
        
        // Check in Teachers table
        if ($role !== 'teacher' && Teachers::where('email', $email)->exists()) {
            return true;
        }
        
        // Check in AJKs table
        if ($role !== 'ajk' && AJKs::where('email', $email)->exists()) {
            return true;
        }
        
        // Check in the user's own role table to exclude their own email
        switch ($role) {
            case 'admin':
                return Admins::where('email', $email)->where('id', '!=', $userId)->exists();
            case 'supervisor':
                return Supervisors::where('email', $email)->where('id', '!=', $userId)->exists();
            case 'teacher':
                return Teachers::where('email', $email)->where('id', '!=', $userId)->exists();
            case 'ajk':
                return AJKs::where('email', $email)->where('id', '!=', $userId)->exists();
            default:
                return false;
        }
    }
    
    /**
     * Send a message to another user
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request)
    {
        // This is a placeholder for messaging functionality that you might want to implement
        // Logic for sending a message to another user would go here
        
        return redirect()->back()->with('success', 'Message sent successfully.');
    }
    
    /**
     * Get user messages
     * 
     * @return \Illuminate\View\View
     */
    public function getMessages()
    {
        // This is a placeholder for messaging functionality that you might want to implement
        // Logic for retrieving messages would go here
        
        return view('messages.index', [
            'messages' => []
        ]);
    }
    
    /**
     * View a conversation with another user
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewConversation($id)
    {
        // This is a placeholder for messaging functionality that you might want to implement
        // Logic for viewing a specific conversation would go here
        
        return view('messages.conversation', [
            'conversation' => null
        ]);
    }
    
    /**
     * Mark a message as read
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markMessageRead($id)
    {
        // This is a placeholder for messaging functionality that you might want to implement
        // Logic for marking a message as read would go here
        
        return redirect()->back();
    }
    
    /**
     * Get notifications as JSON
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationsJson()
    {
        // This is a placeholder for notification functionality that you might want to implement
        // Logic for retrieving notifications as JSON would go here
        
        return response()->json([
            'notifications' => []
        ]);
    }
    
    /**
     * List all centres
     * 
     * @return \Illuminate\View\View
     */
    public function listCentres()
    {
        // This is a placeholder for centre listing functionality that you might want to implement
        // Logic for listing all centres would go here
        
        return view('centres.index', [
            'centres' => []
        ]);
    }
    
    /**
     * View a specific centre
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewCentre($id)
    {
        // This is a placeholder for centre viewing functionality that you might want to implement
        // Logic for viewing a specific centre would go here
        
        return view('centres.view', [
            'centre' => null
        ]);
    }
}