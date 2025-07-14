<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'position',
        'phone',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'reminder_emails',
        'weekly_reports',
        'activity_updates',
        'trainee_progress',
        'system_alerts',
        'theme',
        'language',
        'date_format',
        'time_format',
        'first_day_of_week',
        'default_view',
        'items_per_page',
        'auto_logout',
        'two_factor_auth',
        'session_timeout',
        'login_alerts',
        'api_access',
        'data_export',
        'profile_visibility',
        'show_activity_status'
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'reminder_emails' => 'boolean',
        'weekly_reports' => 'boolean',
        'activity_updates' => 'boolean',
        'trainee_progress' => 'boolean',
        'system_alerts' => 'boolean',
        'two_factor_auth' => 'boolean',
        'login_alerts' => 'boolean',
        'api_access' => 'boolean',
        'data_export' => 'boolean',
        'show_activity_status' => 'boolean',
        'items_per_page' => 'integer',
        'auto_logout' => 'integer',
        'session_timeout' => 'integer'
    ];

    /**
     * Get the user that owns the settings
     */
    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * Get default settings for a new user
     */
    public static function getDefaults()
    {
        return [
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'reminder_emails' => true,
            'weekly_reports' => true,
            'activity_updates' => true,
            'trainee_progress' => true,
            'system_alerts' => true,
            'theme' => 'light',
            'language' => 'en',
            'date_format' => 'DD/MM/YYYY',
            'time_format' => '24hr',
            'first_day_of_week' => 'monday',
            'default_view' => 'dashboard',
            'items_per_page' => 25,
            'auto_logout' => 30,
            'two_factor_auth' => false,
            'session_timeout' => 30,
            'login_alerts' => true,
            'api_access' => false,
            'data_export' => true,
            'profile_visibility' => 'staff_only',
            'show_activity_status' => true
        ];
    }

    /**
     * Create default settings for a user
     */
    public static function createDefaults($userId)
    {
        return static::create(array_merge(['user_id' => $userId], static::getDefaults()));
    }

    /**
     * Get notification preferences
     */
    public function getNotificationPreferences()
    {
        return [
            'email_notifications' => $this->email_notifications,
            'sms_notifications' => $this->sms_notifications,
            'push_notifications' => $this->push_notifications,
            'reminder_emails' => $this->reminder_emails,
            'weekly_reports' => $this->weekly_reports,
            'activity_updates' => $this->activity_updates,
            'trainee_progress' => $this->trainee_progress,
            'system_alerts' => $this->system_alerts
        ];
    }

    /**
     * Get application preferences
     */
    public function getApplicationPreferences()
    {
        return [
            'theme' => $this->theme,
            'language' => $this->language,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'first_day_of_week' => $this->first_day_of_week,
            'default_view' => $this->default_view,
            'items_per_page' => $this->items_per_page,
            'auto_logout' => $this->auto_logout
        ];
    }

    /**
     * Get security settings
     */
    public function getSecuritySettings()
    {
        return [
            'two_factor_auth' => $this->two_factor_auth,
            'session_timeout' => $this->session_timeout,
            'login_alerts' => $this->login_alerts,
            'api_access' => $this->api_access,
            'data_export' => $this->data_export
        ];
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(array $preferences)
    {
        $allowedKeys = [
            'email_notifications', 'sms_notifications', 'push_notifications',
            'reminder_emails', 'weekly_reports', 'activity_updates',
            'trainee_progress', 'system_alerts'
        ];

        $filteredPreferences = array_intersect_key($preferences, array_flip($allowedKeys));
        return $this->update($filteredPreferences);
    }
}