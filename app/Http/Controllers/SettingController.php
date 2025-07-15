<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
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
}