@extends('layouts.app')

@section('title', 'Settings')

@section('styles')
<style>
    .settings-container {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .settings-sidebar {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        height: fit-content;
        position: sticky;
        top: 1rem;
    }
    
    .settings-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .settings-nav-item {
        margin-bottom: 0.5rem;
    }
    
    .settings-nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: #6c757d;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-weight: 500;
        position: relative;
    }
    
    .settings-nav-link:hover {
        background: rgba(50, 189, 234, 0.05);
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .settings-nav-link.active {
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
        border-left: 3px solid var(--primary-color);
    }
    
    .settings-nav-link i {
        width: 20px;
        text-align: center;
    }
    
    .chevron-icon {
        margin-left: auto;
        font-size: 0.75rem;
        transition: transform 0.2s ease;
    }
    
    .settings-nav-link.active .chevron-icon {
        transform: rotate(90deg);
    }
    
    .settings-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .settings-section {
        display: none;
        padding: 2rem;
    }
    
    .settings-section.active {
        display: block;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    
    .section-subtitle {
        color: #6c757d;
        margin-bottom: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
    }
    
    .form-text {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .avatar-section {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .avatar-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 600;
        overflow: hidden;
    }
    
    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        justify-content: center;
        font-size: 0.9rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary {
        background: #f8fafc;
        color: #6c757d;
        border: 1px solid #e2e8f0;
    }
    
    .btn-secondary:hover {
        background: #e2e8f0;
        color: #374151;
        text-decoration: none;
    }
    
    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }
    
    .btn-danger:hover {
        background: #fecaca;
        color: #b91c1c;
        text-decoration: none;
    }
    
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #ccc;
        transition: 0.3s;
        border-radius: 24px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background: var(--primary-color);
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    .toggle-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .toggle-group:last-child {
        border-bottom: none;
    }
    
    .toggle-info {
        flex: 1;
    }
    
    .toggle-title {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
    }
    
    .toggle-description {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .theme-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    .theme-option {
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    
    .theme-option:hover {
        border-color: var(--primary-color);
    }
    
    .theme-option.selected {
        border-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
    }
    
    .theme-icon {
        width: 40px;
        height: 40px;
        margin: 0 auto 0.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .theme-icon.light {
        background: #fef3c7;
        color: #d97706;
    }
    
    .theme-icon.dark {
        background: #374151;
        color: #f9fafb;
    }
    
    .theme-icon.auto {
        background: #e5e7eb;
        color: #6b7280;
    }
    
    .password-requirements {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .password-requirements h4 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1d4ed8;
        margin-bottom: 0.5rem;
    }
    
    .requirements-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .requirements-list li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #1e40af;
        margin-bottom: 0.25rem;
    }
    
    .requirements-list li.valid {
        color: #059669;
    }
    
    .requirements-list li.valid i {
        color: #059669;
    }
    
    .password-strength {
        margin-top: 0.5rem;
    }
    
    .strength-bar {
        width: 100%;
        height: 4px;
        background: #f1f5f9;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }
    
    .strength-fill {
        height: 100%;
        transition: width 0.3s ease;
        border-radius: 2px;
    }
    
    .strength-fill.weak {
        width: 25%;
        background: #dc2626;
    }
    
    .strength-fill.fair {
        width: 50%;
        background: #d97706;
    }
    
    .strength-fill.good {
        width: 75%;
        background: #059669;
    }
    
    .strength-fill.strong {
        width: 100%;
        background: #10b981;
    }
    
    .strength-text {
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .strength-text.weak {
        color: #dc2626;
    }
    
    .strength-text.fair {
        color: #d97706;
    }
    
    .strength-text.good,
    .strength-text.strong {
        color: #059669;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    
    .alert-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }
    
    .save-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .loading-spinner {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #e2e8f0;
        border-top: 3px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .sessions-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .session-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .session-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .session-icon {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .session-details h4 {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .session-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .session-status {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .session-status.current {
        background: #dcfce7;
        color: #166534;
    }
    
    .session-status.other {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .session-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .settings-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .settings-sidebar {
            position: static;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .theme-options {
            grid-template-columns: 1fr;
        }
        
        .avatar-section {
            flex-direction: column;
            text-align: center;
        }
        
        .save-section {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">Settings</h1>
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="separator">></span>
        <span class="current">Settings</span>
    </div>
    <p style="color: #6c757d; margin-top: 0.5rem;">Manage your account settings and preferences</p>
</div>

<div class="settings-container">
    <!-- Sidebar Navigation -->
    <div class="settings-sidebar">
        <ul class="settings-nav">
            <li class="settings-nav-item">
                <a href="#" class="settings-nav-link active" data-section="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                    <i class="fas fa-chevron-right chevron-icon"></i>
                </a>
            </li>
            <li class="settings-nav-item">
                <a href="#" class="settings-nav-link" data-section="password">
                    <i class="fas fa-lock"></i>
                    <span>Password</span>
                    <i class="fas fa-chevron-right chevron-icon"></i>
                </a>
            </li>
            <li class="settings-nav-item">
                <a href="#" class="settings-nav-link" data-section="notifications">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <i class="fas fa-chevron-right chevron-icon"></i>
                </a>
            </li>
            <li class="settings-nav-item">
                <a href="#" class="settings-nav-link" data-section="preferences">
                    <i class="fas fa-palette"></i>
                    <span>Preferences</span>
                    <i class="fas fa-chevron-right chevron-icon"></i>
                </a>
            </li>
            <li class="settings-nav-item">
                <a href="#" class="settings-nav-link" data-section="security">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                    <i class="fas fa-chevron-right chevron-icon"></i>
                </a>
            </li>
            <li class="settings-nav-item">
                <a href="#" class="settings-nav-link" data-section="privacy">
                    <i class="fas fa-user-shield"></i>
                    <span>Data & Privacy</span>
                    <i class="fas fa-chevron-right chevron-icon"></i>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Settings Content -->
    <div class="settings-content">
        <!-- Profile Section -->
        <div class="settings-section active" id="profile">
            <h2 class="section-title">Profile Settings</h2>
            <p class="section-subtitle">Manage your personal information and avatar</p>
            
            <div id="profileAlert"></div>
            
            <form id="profileForm">
                @csrf
                <div class="avatar-section">
                    <div class="avatar-preview" id="avatarPreview">
                        @php
                            $avatarPath = session('user_avatar') ?? session('avatar');
                            if ($avatarPath) {
                                $avatarUrl = asset('storage/avatars/' . $avatarPath);
                                echo '<img src="' . $avatarUrl . '" alt="Avatar" onerror="this.style.display=\'none\'; this.parentNode.textContent=\'' . strtoupper(substr(session('name', 'U'), 0, 2)) . '\';">';
                            } else {
                                echo strtoupper(substr(session('name', 'User'), 0, 2));
                            }
                        @endphp
                    </div>
                    <div class="avatar-actions">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('avatarInput').click()">
                            <i class="fas fa-camera"></i>
                            Change Photo
                        </button>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                        <p class="form-text">JPG, PNG or GIF. Max size 2MB</p>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ session('name', '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="{{ session('email', '') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" value="{{ session('phone', '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position" value="{{ ucfirst(session('role', '')) }}" readonly>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea class="form-control" name="bio" rows="4" placeholder="Tell us about yourself...">{{ session('bio', '') }}</textarea>
                    <div class="form-text">Brief description for your profile</div>
                </div>
                
                <div class="save-section">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Password Section -->
        <div class="settings-section" id="password">
            <h2 class="section-title">Change Password</h2>
            <p class="section-subtitle">Update your password to keep your account secure</p>
            
            <div id="passwordAlert"></div>
            
            <form id="passwordForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" id="newPassword" required>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                    <div class="form-text" id="passwordMatch"></div>
                </div>
                
                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <ul class="requirements-list">
                        <li id="req-length">
                            <i class="fas fa-times"></i>
                            At least 8 characters
                        </li>
                        <li id="req-case">
                            <i class="fas fa-times"></i>
                            Mix of uppercase and lowercase letters
                        </li>
                        <li id="req-number">
                            <i class="fas fa-times"></i>
                            At least one number
                        </li>
                        <li id="req-special">
                            <i class="fas fa-times"></i>
                            At least one special character
                        </li>
                    </ul>
                </div>
                
                <div class="save-section">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Notifications Section -->
        <div class="settings-section" id="notifications">
            <h2 class="section-title">Notification Preferences</h2>
            <p class="section-subtitle">Choose how you want to be notified</p>
            
            <div id="notificationAlert"></div>
            
            <form id="notificationForm">
                @csrf
                <h3>Communication Channels</h3>
                <div style="margin-bottom: 2rem;">
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Email Notifications</div>
                            <div class="toggle-description">Receive notifications via email</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_notifications" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">SMS Notifications</div>
                            <div class="toggle-description">Receive notifications via SMS</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_notifications">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Push Notifications</div>
                            <div class="toggle-description">Receive browser push notifications</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="push_notifications" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <h3>Notification Types</h3>
                <div style="margin-bottom: 2rem;">
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Session Reminders</div>
                            <div class="toggle-description">Get reminded before upcoming sessions</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="session_reminders" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Weekly Progress Reports</div>
                            <div class="toggle-description">Receive weekly summary of activities</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="weekly_reports" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Activity Updates</div>
                            <div class="toggle-description">Get notified about new activities</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="activity_updates" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Trainee Progress Alerts</div>
                            <div class="toggle-description">Important updates about trainee progress</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="trainee_progress" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">System Maintenance Alerts</div>
                            <div class="toggle-description">Get notified about system updates</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="system_alerts" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="save-section">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Preferences Section -->
        <div class="settings-section" id="preferences">
            <h2 class="section-title">Application Preferences</h2>
            <p class="section-subtitle">Customize your experience</p>
            
            <div id="preferencesAlert"></div>
            
            <form id="preferencesForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Theme</label>
                    <div class="theme-options">
                        <div class="theme-option selected" data-theme="light">
                            <div class="theme-icon light">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>Light</div>
                        </div>
                        <div class="theme-option" data-theme="dark">
                            <div class="theme-icon dark">
                                <i class="fas fa-moon"></i>
                            </div>
                            <div>Dark</div>
                        </div>
                        <div class="theme-option" data-theme="auto">
                            <div class="theme-icon auto">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <div>System</div>
                        </div>
                    </div>
                    <input type="hidden" name="theme" value="light">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select class="form-control" name="language">
                            <option value="en">English</option>
                            <option value="ms">Bahasa Melayu</option>
                            <option value="zh">中文</option>
                            <option value="ta">தமிழ்</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Format</label>
                        <select class="form-control" name="date_format">
                            <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                            <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                            <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Time Format</label>
                        <select class="form-control" name="time_format">
                            <option value="12hr">12 Hour (AM/PM)</option>
                            <option value="24hr">24 Hour</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Items Per Page</label>
                        <select class="form-control" name="items_per_page">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                
                <div class="save-section">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Security Section -->
        <div class="settings-section" id="security">
            <h2 class="section-title">Security Settings</h2>
            <p class="section-subtitle">Manage your account security</p>
            
            <div id="securityAlert"></div>
            
            <form id="securityForm">
                @csrf
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Two-Factor Authentication</strong><br>
                        Add an extra layer of security to your account by enabling two-factor authentication.
                    </div>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Enable Two-Factor Authentication</div>
                            <div class="toggle-description">Require a verification code from your phone</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="two_factor_auth">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Email Login Alerts</div>
                            <div class="toggle-description">Get notified when someone logs into your account</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="login_alerts" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <div class="toggle-title">Allow Data Export</div>
                            <div class="toggle-description">Enable downloading your data</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="data_export" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Auto-logout after inactivity (minutes)</label>
                    <select class="form-control" name="session_timeout">
                        <option value="5">5 minutes</option>
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="120">2 hours</option>
                    </select>
                </div>
                
                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Active Sessions</h3>
                <div class="sessions-list">
                    <div class="session-item">
                        <div class="session-info">
                            <div class="session-icon">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <div class="session-details">
                                <h4>Chrome on Windows</h4>
                                <div class="session-meta">Current session • Kuala Lumpur, MY</div>
                            </div>
                        </div>
                        <div class="session-actions">
                            <span class="session-status current">Active now</span>
                        </div>
                    </div>
                    
                    <div class="session-item">
                        <div class="session-info">
                            <div class="session-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="session-details">
                                <h4>Safari on iPhone</h4>
                                <div class="session-meta">Last active 2 hours ago • Selangor, MY</div>
                            </div>
                        </div>
                        <div class="session-actions">
                            <span class="session-status other">Other device</span>
                            <button type="button" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Revoke</button>
                        </div>
                    </div>
                </div>
                
                <div class="save-section">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Privacy Section -->
        <div class="settings-section" id="privacy">
            <h2 class="section-title">Data & Privacy</h2>
            <p class="section-subtitle">Control your data and privacy settings</p>
            
            <div id="privacyAlert"></div>
            
            <h3>Data Management</h3>
            <div style="margin-bottom: 2rem;">
                <div class="session-item">
                    <div class="session-info">
                        <div class="session-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="session-details">
                            <h4>Export Your Data</h4>
                            <div class="session-meta">Download a copy of your data in CSV format</div>
                        </div>
                    </div>
                    <div class="session-actions">
                        <button type="button" class="btn btn-primary">Export Data</button>
                    </div>
                </div>
                
                <div class="session-item">
                    <div class="session-info">
                        <div class="session-icon" style="background: #dc2626;">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="session-details">
                            <h4>Delete Account</h4>
                            <div class="session-meta">Permanently delete your account and all data</div>
                        </div>
                    </div>
                    <div class="session-actions">
                        <button type="button" class="btn btn-danger">Delete Account</button>
                    </div>
                </div>
            </div>
            
            <h3>Privacy Settings</h3>
            <form id="privacyForm">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Profile Visibility</label>
                        <select class="form-control" name="profile_visibility">
                            <option value="everyone">Everyone</option>
                            <option value="staff" selected>Staff Only</option>
                            <option value="admin">Admin Only</option>
                        </select>
                        <div class="form-text">Control who can see your profile</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Activity Status</label>
                        <div style="margin-top: 0.75rem;">
                            <label class="toggle-switch">
                                <input type="checkbox" name="show_online_status" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="form-text">Show when you're online</div>
                    </div>
                </div>
                
                <div class="alert" style="background: #eff6ff; color: #1e40af; border-color: #bfdbfe;">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Privacy Policy</strong><br>
                        Your data is protected according to our privacy policy. We do not share your personal information with third parties without your consent.
                        <a href="#" style="color: #1d4ed8; text-decoration: underline;">Read Full Privacy Policy →</a>
                    </div>
                </div>
                
                <div class="save-section">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <div>Saving changes...</div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Initialize settings
    document.addEventListener('DOMContentLoaded', function() {
        setupNavigation();
        setupPasswordValidation();
        setupThemeSelection();
        setupFormSubmission();
    });
    
    // Setup navigation
    function setupNavigation() {
        const navLinks = document.querySelectorAll('.settings-nav-link');
        const sections = document.querySelectorAll('.settings-section');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const sectionId = this.getAttribute('data-section');
                
                // Update nav active state
                navLinks.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                // Update section active state
                sections.forEach(section => section.classList.remove('active'));
                document.getElementById(sectionId).classList.add('active');
            });
        });
    }
    
    // Setup password validation
    function setupPasswordValidation() {
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                validatePassword(this.value);
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                checkPasswordMatch();
            });
        }
    }
    
    // Validate password strength
    function validatePassword(password) {
        const strengthSection = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        if (!password) {
            strengthSection.style.display = 'none';
            return;
        }
        
        strengthSection.style.display = 'block';
        
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            case: /[a-z]/.test(password) && /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[^a-zA-Z0-9]/.test(password)
        };
        
        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            const icon = element.querySelector('i');
            
            if (requirements[req]) {
                element.classList.add('valid');
                icon.className = 'fas fa-check';
                score++;
            } else {
                element.classList.remove('valid');
                icon.className = 'fas fa-times';
            }
        });
        
        // Update strength indicator
        const strengthLevels = ['weak', 'fair', 'good', 'strong'];
        const level = strengthLevels[Math.min(score - 1, 3)] || 'weak';
        
        strengthFill.className = `strength-fill ${level}`;
        strengthText.className = `strength-text ${level}`;
        strengthText.textContent = `Password strength: ${level.charAt(0).toUpperCase() + level.slice(1)}`;
    }
    
    // Check password match
    function checkPasswordMatch() {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const matchElement = document.getElementById('passwordMatch');
        
        if (!confirmPassword) {
            matchElement.textContent = '';
            return;
        }
        
        if (newPassword === confirmPassword) {
            matchElement.textContent = 'Passwords match';
            matchElement.style.color = '#059669';
        } else {
            matchElement.textContent = 'Passwords do not match';
            matchElement.style.color = '#dc2626';
        }
    }
    
    // Setup theme selection
    function setupThemeSelection() {
        const themeOptions = document.querySelectorAll('.theme-option');
        const themeInput = document.querySelector('input[name="theme"]');
        
        themeOptions.forEach(option => {
            option.addEventListener('click', function() {
                themeOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                themeInput.value = this.getAttribute('data-theme');
            });
        });
    }
    
    // Preview avatar
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Setup form submission
    function setupFormSubmission() {
        const forms = ['profileForm', 'passwordForm', 'notificationForm', 'preferencesForm', 'securityForm', 'privacyForm'];
        
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleFormSubmission(formId);
                });
            }
        });
    }
    
    // Handle form submission
    async function handleFormSubmission(formId) {
        const form = document.getElementById(formId);
        const formData = new FormData(form);
        const alertContainer = document.getElementById(formId.replace('Form', 'Alert'));
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Show loading
        loadingOverlay.style.display = 'flex';
        
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Show success message
            alertContainer.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Settings saved successfully!
                </div>
            `;
            
            // Scroll to top of section
            alertContainer.scrollIntoView({ behavior: 'smooth' });
            
            // Hide alert after 3 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 3000);
            
        } catch (error) {
            // Show error message
            alertContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Failed to save settings. Please try again.
                </div>
            `;
        } finally {
            // Hide loading
            loadingOverlay.style.display = 'none';
        }
    }
</script>
@endsection