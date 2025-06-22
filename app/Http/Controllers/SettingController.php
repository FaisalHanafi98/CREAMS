<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Users;
use App\Models\UserSettings;
use App\Models\UserSession;
use Exception;

class SettingController extends Controller
{
    /**
     * Display enhanced settings interface with modern UI
     *
     * @return \Illuminate\View\View
     */
    public function enhancedIndex()
    {
        try {
            Log::info('Accessing enhanced settings interface', [
                'user_id' => session('id'),
                'role' => session('role')
            ]);
            
            $userId = session('id');
            $user = Users::find($userId);
            
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Please log in to access settings.');
            }
            
            // Get user preferences
            $preferences = [
                'theme' => 'light',
                'language' => 'en',
                'notifications' => [
                    'email' => true,
                    'browser' => true,
                    'mobile' => false
                ],
                'privacy' => [
                    'profile_visibility' => 'public',
                    'activity_sharing' => true
                ]
            ];
            
            Log::info('Enhanced settings interface loaded successfully', [
                'user_id' => $userId,
                'has_preferences' => !empty($preferences)
            ]);
            
            return view('settings.enhanced-settings', [
                'user' => $user,
                'preferences' => $preferences
            ]);
            
        } catch (Exception $e) {
            Log::error('Error loading enhanced settings interface', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'An error occurred while loading the settings page.');
        }
    }

    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to settings', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy settings data
        $settings = [
            'general' => [
                'site_name' => 'CREAMS',
                'site_description' => 'Community-based REhAbilitation Management System',
                'contact_email' => 'admin@creams.edu.my',
                'contact_phone' => '+60 3-6196 4000'
            ],
            'appearance' => [
                'primary_color' => '#32bdea',
                'secondary_color' => '#c850c0',
                'logo_path' => 'images/favicon.png'
            ],
            'security' => [
                'password_expiry_days' => 90,
                'session_timeout_minutes' => 30,
                'allow_registration' => true,
                'require_email_verification' => true
            ],
            'notifications' => [
                'email_notifications' => true,
                'system_notifications' => true,
                'sms_notifications' => false
            ]
        ];
        
        return view('settings.index', [
            'settings' => $settings
        ]);
    }
    
    /**
     * Update the specified settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to update settings', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'site_name' => 'required|string|max:100',
            'site_description' => 'nullable|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'password_expiry_days' => 'required|integer|min:0|max:365',
            'session_timeout_minutes' => 'required|integer|min:5|max:120',
            'allow_registration' => 'boolean',
            'require_email_verification' => 'boolean',
            'email_notifications' => 'boolean',
            'system_notifications' => 'boolean',
            'sms_notifications' => 'boolean'
        ]);
        
        // In a real implementation, save settings to database or config files
        
        Log::info('Settings updated', [
            'user_id' => session('id'),
            'settings_updated' => array_keys($request->except('_token'))
        ]);
        
        return redirect()->route('settings')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Display user settings page (individual user settings)
     */
    public function userSettings()
    {
        try {
            // Check authentication
            if (!session('id') || !session('role')) {
                return redirect()->route('login');
            }

            $user = Users::findOrFail(session('id'));
            $userSettings = $user->settings ?? UserSettings::createDefaults($user->id);
            $activeSessions = UserSession::getUserSessions($user->id);

            Log::info('User settings page accessed', [
                'user_id' => session('id'),
                'role' => session('role')
            ]);

            return view('settings.user', compact('user', 'userSettings', 'activeSessions'));

        } catch (\Exception $e) {
            Log::error('Error accessing user settings page', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to access settings page.');
        }
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Users::findOrFail(session('id'));

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:1000',
            ]);

            $user->update($validatedData);

            // Update user settings if bio is provided
            if (isset($validatedData['bio']) || isset($validatedData['position']) || isset($validatedData['phone'])) {
                $settings = $user->settings ?? UserSettings::createDefaults($user->id);
                $settings->update([
                    'bio' => $validatedData['bio'] ?? $settings->bio,
                    'position' => $validatedData['position'] ?? $settings->position,
                    'phone' => $validatedData['phone'] ?? $settings->phone,
                ]);
            }

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validatedData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user profile', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 422);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Users::findOrFail(session('id'));

            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
                'new_password_confirmation' => 'required'
            ]);

            // Verify current password
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validatedData['new_password'])
            ]);

            Log::info('User password updated', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user password', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update password'
            ], 422);
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Users::findOrFail(session('id'));
            $settings = $user->settings ?? UserSettings::createDefaults($user->id);

            $validatedData = $request->validate([
                'email_notifications' => 'boolean',
                'sms_notifications' => 'boolean',
                'push_notifications' => 'boolean',
                'reminder_emails' => 'boolean',
                'weekly_reports' => 'boolean',
                'activity_updates' => 'boolean',
                'trainee_progress' => 'boolean',
                'system_alerts' => 'boolean'
            ]);

            $settings->updateNotificationPreferences($validatedData);

            Log::info('User notification preferences updated', [
                'user_id' => $user->id,
                'preferences' => $validatedData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating notification preferences', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification preferences'
            ], 422);
        }
    }

    /**
     * Update application preferences
     */
    public function updatePreferences(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Users::findOrFail(session('id'));
            $settings = $user->settings ?? UserSettings::createDefaults($user->id);

            $validatedData = $request->validate([
                'theme' => 'in:light,dark,auto',
                'language' => 'string|max:5',
                'date_format' => 'string|max:20',
                'time_format' => 'in:12hr,24hr',
                'first_day_of_week' => 'in:sunday,monday',
                'default_view' => 'string|max:50',
                'items_per_page' => 'integer|min:10|max:100',
                'auto_logout' => 'integer|min:5|max:120'
            ]);

            $settings->update($validatedData);

            Log::info('User application preferences updated', [
                'user_id' => $user->id,
                'preferences' => $validatedData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating application preferences', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences'
            ], 422);
        }
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Users::findOrFail(session('id'));
            $settings = $user->settings ?? UserSettings::createDefaults($user->id);

            $validatedData = $request->validate([
                'two_factor_auth' => 'boolean',
                'session_timeout' => 'integer|min:5|max:120',
                'login_alerts' => 'boolean',
                'api_access' => 'boolean',
                'data_export' => 'boolean'
            ]);

            $settings->update($validatedData);

            Log::info('User security settings updated', [
                'user_id' => $user->id,
                'settings' => $validatedData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security settings updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating security settings', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update security settings'
            ], 422);
        }
    }

    /**
     * Revoke a user session
     */
    public function revokeSession(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $sessionId = $request->input('session_id');
            $userSession = UserSession::where('id', $sessionId)
                                    ->where('user_id', session('id'))
                                    ->firstOrFail();

            $userSession->revoke();

            Log::info('User session revoked', [
                'user_id' => session('id'),
                'revoked_session_id' => $sessionId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session revoked successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error revoking user session', [
                'user_id' => session('id'),
                'session_id' => $request->input('session_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke session'
            ], 422);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        try {
            if (!session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $user = Users::findOrFail(session('id'));
            
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('avatars', $filename, 'public');
                
                // Delete old avatar if exists
                if ($user->avatar && \Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                    \Storage::disk('public')->delete('avatars/' . $user->avatar);
                }
                
                $user->update(['avatar' => $filename]);

                Log::info('User avatar updated', [
                    'user_id' => $user->id,
                    'avatar_filename' => $filename
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Avatar updated successfully',
                    'avatar_url' => $user->avatar_url
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No avatar file provided'
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error uploading user avatar', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar'
            ], 422);
        }
    }

    /**
     * Update user privacy settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePrivacy(Request $request)
    {
        try {
            $userId = session('id');
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'profile_visibility' => 'nullable|in:public,private,friends',
                'activity_sharing' => 'nullable|boolean',
                'data_collection' => 'nullable|boolean',
                'marketing_emails' => 'nullable|boolean',
                'analytics_tracking' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update or create user privacy settings
            $settings = UserSettings::firstOrCreate(['user_id' => $userId]);
            $privacySettings = $settings->privacy_settings ?? [];
            
            $privacySettings = array_merge($privacySettings, array_filter([
                'profile_visibility' => $request->input('profile_visibility'),
                'activity_sharing' => $request->boolean('activity_sharing'),
                'data_collection' => $request->boolean('data_collection'),
                'marketing_emails' => $request->boolean('marketing_emails'),
                'analytics_tracking' => $request->boolean('analytics_tracking')
            ], function($value) { return $value !== null; }));

            $settings->privacy_settings = $privacySettings;
            $settings->save();

            Log::info('User privacy settings updated', [
                'user_id' => $userId,
                'updated_fields' => array_keys($request->all())
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Privacy settings updated successfully',
                'data' => $privacySettings
            ]);

        } catch (Exception $e) {
            Log::error('Error updating privacy settings', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update privacy settings'
            ], 500);
        }
    }
}