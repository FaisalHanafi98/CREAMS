@extends('layouts.app')

@section('title', 'My Profile - CREAMS')

@push('styles')
<style>
    /* Custom animations and transitions */
    .tab-content {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-input-animated {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        font-size: 14px;
        background: #fafafa;
    }

    .form-input-animated:focus {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-color: var(--primary-color);
        background: white;
        outline: none;
    }

    .form-input-animated:disabled {
        background: #f5f5f5;
        color: #6c757d;
        cursor: not-allowed;
    }

    /* Password strength indicator */
    .password-strength {
        height: 4px;
        transition: all 0.3s ease;
        border-radius: 2px;
        margin-top: 8px;
    }

    .strength-weak { background-color: #ef4444; width: 33%; }
    .strength-medium { background-color: #f59e0b; width: 66%; }
    .strength-strong { background-color: #10b981; width: 100%; }

    /* Letter preview card hover effect */
    .letter-card {
        transition: all 0.2s ease;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        border: 1px solid #e9ecef;
    }

    .letter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border-color: var(--primary-color);
    }

    /* Loading spinner */
    .spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Profile specific styles with CREAMS theming */
    .profile-page {
        background: var(--light-color);
        min-height: calc(100vh - 60px);
    }

    .profile-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(50, 189, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.1;
    }

    .profile-header .container-fluid {
        position: relative;
        z-index: 1;
    }

    /* Tab Navigation */
    .tab-navigation {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .nav-tabs {
        border: none;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
    }

    .nav-tabs .nav-item {
        flex: 1;
        min-width: 200px;
    }

    .nav-tabs .nav-link {
        border: none;
        padding: 20px 15px;
        text-align: center;
        color: #6c757d;
        background: transparent;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 14px;
        border-radius: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        border-bottom: 3px solid transparent;
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
        border-bottom-color: rgba(50, 189, 234, 0.3);
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background: rgba(50, 189, 234, 0.1);
        border-bottom-color: var(--primary-color);
        font-weight: 600;
    }

    /* Content Sections */
    .content-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        padding: 30px;
        margin-bottom: 0;
        transition: all 0.3s ease;
    }

    .content-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    /* Form styling */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group-modern {
        margin-bottom: 20px;
    }

    .form-label-modern {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark-color);
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Button styling */
    .btn-modern {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(50, 189, 234, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-modern:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d, #495057);
    }

    .btn-secondary:hover {
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
    }

    /* Avatar styling */
    .profile-avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 30px;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid rgba(50, 189, 234, 0.2);
        overflow: hidden;
        position: relative;
        background: rgba(50, 189, 234, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary-color);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-upload-btn {
        position: absolute;
        bottom: -5px;
        right: -5px;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        border: 3px solid white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .avatar-upload-btn:hover {
        background: var(--secondary-color);
        transform: scale(1.1);
    }

    .avatar-upload-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Alert styling */
    .alert-modern {
        border: none;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    /* File upload area */
    .file-upload-area {
        border: 2px dashed #e9ecef;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background: #fafafa;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
    }

    .file-upload-area.dragover {
        border-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.1);
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .profile-avatar-section {
            flex-direction: column;
            text-align: center;
        }
        
        .nav-tabs {
            flex-direction: column;
        }
        
        .nav-tabs .nav-item {
            min-width: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-page" x-data="profilePage()">
    <div class="container-fluid">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="profile-avatar">
                                @if(!empty($user['avatar']) && file_exists(public_path('storage/avatars/' . $user['avatar'])))
                                    <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="Profile Avatar">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 600;">{{ $user['name'] ?? 'User' }}</h1>
                                <div style="font-size: 1.2rem; opacity: 0.9; background: rgba(255, 255, 255, 0.2); padding: 8px 20px; border-radius: 25px; display: inline-block;">
                                    {{ ucfirst($role ?? 'User') }} • Member since {{ \Carbon\Carbon::parse($user['created_at'] ?? now())->format('M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <div style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; font-size: 12px; display: inline-block;">
                            <i class="fas fa-user-cog"></i> Last updated: {{ \Carbon\Carbon::parse($user['updated_at'] ?? now())->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" @click="activeTab = 'profile'" 
                       :class="{'active': activeTab === 'profile'}" 
                       href="#" role="tab">
                        <i class="fas fa-user"></i>
                        Profile Information
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" @click="activeTab = 'password'" 
                       :class="{'active': activeTab === 'password'}" 
                       href="#" role="tab">
                        <i class="fas fa-lock"></i>
                        Change Password
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" @click="activeTab = 'settings'" 
                       :class="{'active': activeTab === 'settings'}" 
                       href="#" role="tab">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" @click="activeTab = 'letters'" 
                       :class="{'active': activeTab === 'letters'}" 
                       href="#" role="tab">
                        <i class="fas fa-envelope"></i>
                        Letter Generator
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="content-section">
            <!-- Profile Information Tab -->
            <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" class="tab-content">
                @if(session('success'))
                    <div class="alert-modern alert-success">
                        <i class="fas fa-check-circle"></i>{{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-modern alert-danger">
                        <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-modern alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            Please correct the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form @submit.prevent="saveProfile">
                    <!-- Profile Header with Avatar -->
                    <div class="profile-avatar-section">
                        <div class="profile-avatar">
                            @if(!empty($user['avatar']) && file_exists(public_path('storage/avatars/' . $user['avatar'])))
                                <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="{{ $user['name'] ?? 'User' }}">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                            <button type="button" :disabled="!isEditing" class="avatar-upload-btn"
                                    onclick="document.getElementById('avatarUpload').click()">
                                <i class="fas fa-camera"></i>
                            </button>
                            <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                        </div>
                        <div>
                            <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--dark-color); margin-bottom: 5px;">
                                {{ $user['name'] ?? 'User' }}
                            </h2>
                            <p style="color: #6c757d; margin: 0;">
                                {{ ucfirst($role ?? 'User') }} • {{ $user['email'] ?? 'No email provided' }}
                            </p>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="form-grid">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Full Name</label>
                            <input type="text" x-model="profileData.name" :disabled="!isEditing" 
                                   class="form-input-animated" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Email Address</label>
                            <input type="email" x-model="profileData.email" :disabled="!isEditing" 
                                   class="form-input-animated" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Phone Number</label>
                            <input type="tel" x-model="profileData.phone" :disabled="!isEditing" 
                                   class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Role</label>
                            <input type="text" value="{{ ucfirst($role ?? 'User') }}" disabled 
                                   class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Department</label>
                            <input type="text" x-model="profileData.department" :disabled="!isEditing" 
                                   class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Location</label>
                            <input type="text" x-model="profileData.location" :disabled="!isEditing" 
                                   class="form-input-animated">
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Bio / About</label>
                        <textarea x-model="profileData.bio" :disabled="!isEditing" rows="4" 
                                  class="form-input-animated" 
                                  placeholder="Tell us about yourself..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-3 pt-4" style="border-top: 1px solid #e9ecef;">
                        <button x-show="!isEditing" @click="isEditing = true" type="button" 
                                class="btn-modern">
                            <i class="fas fa-edit"></i>Edit Profile
                        </button>

                        <template x-if="isEditing">
                            <div class="d-flex gap-3">
                                <button @click="cancelEdit" type="button" class="btn-modern btn-secondary">
                                    <i class="fas fa-times"></i>Cancel
                                </button>
                                <button type="submit" class="btn-modern">
                                    <i class="fas fa-save"></i>Save Changes
                                </button>
                            </div>
                        </template>
                    </div>
                </form>
            </div>

            <!-- Change Password Tab -->
            <div x-show="activeTab === 'password'" x-transition:enter="transition ease-out duration-200" class="tab-content">
                <form @submit.prevent="changePassword" style="max-width: 500px; margin: 0 auto;">
                    <h3 style="text-align: center; margin-bottom: 30px; color: var(--dark-color);">
                        <i class="fas fa-shield-alt mr-2"></i>Change Your Password
                    </h3>
                    
                    <div class="form-group-modern">
                        <label class="form-label-modern">Current Password</label>
                        <input type="password" x-model="passwordData.current" required class="form-input-animated">
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">New Password</label>
                        <input type="password" x-model="passwordData.new" @input="checkPasswordStrength" 
                               required class="form-input-animated">
                        <div style="margin-top: 8px;">
                            <div style="background: #e9ecef; border-radius: 4px; height: 4px; overflow: hidden;">
                                <div class="password-strength" :class="passwordStrengthClass"></div>
                            </div>
                            <p style="font-size: 12px; color: #6c757d; margin-top: 4px;" x-text="passwordStrengthText"></p>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Confirm New Password</label>
                        <input type="password" x-model="passwordData.confirm" required class="form-input-animated">
                        <p x-show="passwordData.new && passwordData.confirm && passwordData.new !== passwordData.confirm"
                           style="color: #dc3545; font-size: 12px; margin-top: 4px;">Passwords do not match</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="!passwordValid" class="btn-modern w-100">
                            <i class="fas fa-key"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Settings Tab -->
            <div x-show="activeTab === 'settings'" x-transition:enter="transition ease-out duration-200" class="tab-content">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-cog fa-4x text-muted spinner"></i>
                    </div>
                    <h3 style="color: var(--dark-color); margin-bottom: 10px;">Settings Module Under Development</h3>
                    <p style="color: #6c757d;">Settings module is currently under development (KIV).</p>
                    <p style="color: #6c757d; font-size: 14px;">We're working hard to bring you new features!</p>
                </div>
            </div>

            <!-- Letter Generator Tab -->
            <div x-show="activeTab === 'letters'" x-transition:enter="transition ease-out duration-200" class="tab-content">
                <!-- Letter Form -->
                <form @submit.prevent="generateLetter">
                    <h3 style="color: var(--dark-color); margin-bottom: 30px; text-align: center;">
                        <i class="fas fa-envelope-open-text mr-2"></i>Create New Letter
                    </h3>

                    <div class="form-grid">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Letter ID</label>
                            <input type="text" x-model="letterData.id" disabled class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Date</label>
                            <input type="date" x-model="letterData.date" required class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">To (Recipient Name)</label>
                            <input type="text" x-model="letterData.recipientName" required class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Recipient Email</label>
                            <input type="email" x-model="letterData.recipientEmail" required class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Sender Email</label>
                            <input type="email" value="{{ $user['email'] ?? '' }}" disabled class="form-input-animated">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Letter Type</label>
                            <select x-model="letterData.type" class="form-input-animated">
                                <option value="formal">Formal Letter</option>
                                <option value="invitation">Invitation</option>
                                <option value="announcement">Announcement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Letter Content</label>
                        <textarea x-model="letterData.content" rows="6" required class="form-input-animated"></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Upload Header Image</label>
                            <div class="file-upload-area">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-3" style="color: var(--primary-color);"></i>
                                <p style="margin: 0; color: #6c757d;">
                                    <strong>Click to upload</strong> or drag and drop<br>
                                    <small>PNG, JPG, GIF up to 10MB</small>
                                </p>
                                <input type="file" accept="image/*" style="display: none;">
                            </div>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Upload Footer Image</label>
                            <div class="file-upload-area">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-3" style="color: var(--primary-color);"></i>
                                <p style="margin: 0; color: #6c757d;">
                                    <strong>Click to upload</strong> or drag and drop<br>
                                    <small>PNG, JPG, GIF up to 10MB</small>
                                </p>
                                <input type="file" accept="image/*" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-4">
                        <button type="submit" class="btn-modern">
                            <i class="fas fa-magic"></i>Generate Letter
                        </button>
                    </div>
                </form>

                <!-- Recent Letters Section -->
                <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #e9ecef;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 style="color: var(--dark-color); margin: 0;">
                            <i class="fas fa-history mr-2"></i>Recent Letters
                        </h3>
                        <a href="#" class="text-decoration-none" style="color: var(--primary-color);">
                            View All →
                        </a>
                    </div>

                    <!-- Sample recent letters -->
                    <div class="letter-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2" style="gap: 15px;">
                                    <span style="font-weight: 600; color: var(--dark-color);">#LTR-2025-001</span>
                                    <span style="color: #6c757d; font-size: 13px;">Jul 20, 2025</span>
                                </div>
                                <h5 style="color: var(--dark-color); margin-bottom: 5px;">To: John Doe</h5>
                                <p style="color: #6c757d; margin: 0; font-size: 14px;">
                                    Dear Mr. Doe, I am writing to inform you about the upcoming changes to our policy regarding...
                                </p>
                            </div>
                            <div style="margin-left: 15px;">
                                <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                    Sent
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function profilePage() {
    return {
        activeTab: 'profile',
        isEditing: false,
        profileData: {
            name: '{{ $user["name"] ?? "" }}',
            email: '{{ $user["email"] ?? "" }}',
            phone: '{{ $user["phone"] ?? "" }}',
            department: '{{ $user["department"] ?? "" }}',
            location: '{{ $user["location"] ?? "" }}',
            bio: '{{ $user["bio"] ?? $user["about"] ?? "" }}'
        },
        originalData: {},
        passwordData: {
            current: '',
            new: '',
            confirm: ''
        },
        letterData: {
            id: 'LTR-' + new Date().getFullYear() + '-' + Math.floor(Math.random() * 1000).toString().padStart(3, '0'),
            date: new Date().toISOString().split('T')[0],
            recipientName: '',
            recipientEmail: '',
            content: '',
            type: 'formal'
        },
        passwordStrength: 0,
        passwordStrengthClass: '',
        passwordStrengthText: '',

        init() {
            this.originalData = {...this.profileData};
        },

        cancelEdit() {
            this.profileData = {...this.originalData};
            this.isEditing = false;
        },

        saveProfile() {
            // Create form data for submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("profile.update") }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add form data
            Object.keys(this.profileData).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = this.profileData[key] || '';
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        },

        checkPasswordStrength() {
            const password = this.passwordData.new;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;

            this.passwordStrength = strength;

            if (strength <= 2) {
                this.passwordStrengthClass = 'strength-weak';
                this.passwordStrengthText = 'Weak password';
            } else if (strength <= 4) {
                this.passwordStrengthClass = 'strength-medium';
                this.passwordStrengthText = 'Medium strength';
            } else {
                this.passwordStrengthClass = 'strength-strong';
                this.passwordStrengthText = 'Strong password';
            }
        },

        get passwordValid() {
            return this.passwordData.current &&
                   this.passwordData.new &&
                   this.passwordData.confirm &&
                   this.passwordData.new === this.passwordData.confirm &&
                   this.passwordStrength >= 3;
        },

        changePassword() {
            if (this.passwordValid) {
                // Create form for password change
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("profile.password") }}';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // Add password data
                ['current', 'new', 'confirm'].forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field === 'current' ? 'current_password' : 
                                 field === 'new' ? 'new_password' : 'new_password_confirmation';
                    input.value = this.passwordData[field];
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        },

        generateLetter() {
            // Create form for letter generation
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("profile.letters.generate") }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add letter data
            Object.keys(this.letterData).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = this.letterData[key] || '';
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Handle file upload areas
document.addEventListener('DOMContentLoaded', function() {
    const uploadAreas = document.querySelectorAll('.file-upload-area');
    uploadAreas.forEach(area => {
        area.addEventListener('click', function() {
            const input = this.querySelector('input[type="file"]');
            if (input) input.click();
        });
        
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        area.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });
        
        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const input = this.querySelector('input[type="file"]');
            if (input && e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
            }
        });
    });
});
</script>
@endpush