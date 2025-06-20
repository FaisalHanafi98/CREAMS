```php
@extends('layouts.app')

@section('title', 'User Profile - CREAMS')

@section('styles')
<style>
    /* Profile styles */
    .profile-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        overflow: hidden;
    }
    
    .profile-header {
        padding: 25px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: #fff;
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        position: relative;
        background: #fff;
        margin-right: 30px;
    }
    
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        opacity: 0;
    }
    
    .profile-avatar:hover .avatar-overlay {
        opacity: 1;
    }
    
    .avatar-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .profile-info {
        flex-grow: 1;
    }
    
    .profile-info h2 {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .profile-role {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 15px;
        display: inline-block;
        padding: 4px 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
    }
    
    .profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        font-size: 14px;
    }
    
    .meta-item i {
        margin-right: 8px;
        opacity: 0.8;
    }
    
    .edit-profile-btn {
        position: absolute;
        top: 25px;
        right: 25px;
        padding: 8px 20px;
        border-radius: 50px;
        background: rgba(255,255,255,0.2);
        color: #fff;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .edit-profile-btn:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .edit-profile-btn i {
        margin-right: 8px;
    }
    
    .edit-profile-btn.active {
        background: rgba(255,0,0,0.2);
    }
    
    .profile-tabs {
        padding: 25px;
    }
    
    .profile-tabs .nav-pills {
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }
    
    .profile-tabs .nav-link {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        color: #555;
    }
    
    .profile-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: #fff;
    }
    
    .profile-tabs .nav-link i {
        margin-right: 8px;
    }
    
    .tab-content {
        padding-top: 25px;
    }
    
    .form-actions {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .password-strength {
        margin-top: 20px;
    }
    
    .password-strength .progress {
        height: 10px;
        margin-top: 8px;
        margin-bottom: 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">My Profile</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <span class="current">My Profile</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Avatar upload error alert -->
    <div id="avatar-error" class="alert alert-danger mb-4" style="display: none;">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="avatar-error-text"></span>
        <button type="button" class="close" onclick="$('#avatar-error').hide()">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <!-- Profile Container -->
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar" id="avatar-container">
                @if(isset($user['avatar']) && $user['avatar'])
                    <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="{{ $user['name'] ?? 'User' }}" id="avatar-preview" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
                @else
                    <img src="{{ asset('images/default-avatar.svg') }}" alt="{{ $user['name'] ?? 'User' }}" id="avatar-preview">
                @endif
                <div class="avatar-overlay" id="avatar-upload-btn" title="Change Profile Photo">
                    <i class="fas fa-camera"></i>
                </div>
                <div id="avatar-loading" class="avatar-loading" style="display: none;">
                    <div class="spinner-border text-light" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="profile-info">
                <h2>{{ $user['name'] ?? 'User' }}</h2>
                @php
                    $roleDisplayNames = [
                        'admin' => 'Administration',
                        'supervisor' => 'Supervisor', 
                        'teacher' => 'Teacher',
                        'ajk' => 'AJK'
                    ];
                    $roleDisplay = $roleDisplayNames[$role] ?? ucfirst($role);
                @endphp
                <div class="profile-role">{{ $roleDisplay }}</div>
                <div class="profile-meta">
                    <div class="meta-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $user['email'] ?? 'No email' }}</span>
                    </div>
                    @if(isset($user['phone']) && $user['phone'])
                    <div class="meta-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ $user['phone'] }}</span>
                    </div>
                    @endif
                    @if(isset($user['iium_id']) && $user['iium_id'])
                    <div class="meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>{{ $user['iium_id'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <button class="edit-profile-btn" id="edit-profile-toggle">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
        </div>
        
        <!-- Hidden file input for avatar upload -->
        <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" style="display: none;">
            @csrf
            <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
        </form>
        
        <!-- Debug hidden fields -->
        <input type="hidden" id="debug_phone" value="{{ $user['phone'] ?? '' }}">
        <input type="hidden" id="debug_address" value="{{ $user['address'] ?? '' }}">
        <input type="hidden" id="debug_bio" value="{{ $user['bio'] ?? '' }}">
        <input type="hidden" id="debug_dob" value="{{ $user['date_of_birth'] ?? '' }}">
        
        <!-- Profile Tabs -->
        <div class="profile-tabs">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="pills-edit-tab" data-toggle="pill" href="#pills-edit" role="tab" aria-controls="pills-edit" aria-selected="true">
                        <i class="fas fa-user-edit"></i> Personal Information
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="pills-password-tab" data-toggle="pill" href="#pills-password" role="tab" aria-controls="pills-password" aria-selected="false">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="pills-preferences-tab" data-toggle="pill" href="#pills-preferences" role="tab" aria-controls="pills-preferences" aria-selected="false">
                        <i class="fas fa-cog"></i> Preferences
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <!-- Edit Profile Tab -->
                <div class="tab-pane fade show active" id="pills-edit" role="tabpanel" aria-labelledby="pills-edit-tab">
                    <form id="profile-form" action="{{ route('profile.update') }}" method="POST" onsubmit="console.log('Form data being submitted:', new FormData(this)); return true;">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $user['name'] ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $user['email'] ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control editable-field" id="phone" name="phone" value="{{ $user['phone'] ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control editable-field" id="date_of_birth" name="date_of_birth" value="{{ $user['date_of_birth'] ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control editable-field" id="address" name="address" rows="3" readonly>{{ $user['address'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea class="form-control editable-field" id="bio" name="bio" rows="5" readonly>{{ $user['bio'] ?? '' }}</textarea>
                        </div>
                        <div class="form-actions" id="profile-actions" style="display: none;">
                            <button type="button" class="btn btn-outline-secondary" id="cancel-edit">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="pills-password" role="tabpanel" aria-labelledby="pills-password-tab">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <small class="form-text text-muted">Enter your current password to verify your identity.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="form-text text-muted">Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, one number, and one special character.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                </div>
                            </div>
                        </div>
                        <div class="password-strength mt-3 mb-4" id="password-strength-meter">
                            <label>Password Strength:</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted" id="password-strength-text">Enter a new password</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
                
                <!-- Preferences Tab (Placeholder for future development) -->
                <div class="tab-pane fade" id="pills-preferences" role="tabpanel" aria-labelledby="pills-preferences-tab">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Preferences management is coming soon. This feature will allow you to customize your CREAMS experience.
                    </div>
                    
                    <h4 class="mt-4 mb-3">Future Preference Options</h4>
                    
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Notification Settings</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Control which notifications you receive and how they are delivered.</p>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emailNotifications" disabled>
                                    <label class="custom-control-label" for="emailNotifications">Email Notifications</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="systemNotifications" disabled>
                                    <label class="custom-control-label" for="systemNotifications">System Notifications</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Display Preferences</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Customize your dashboard and interface preferences.</p>
                            <div class="form-group">
                                <label for="theme">Theme</label>
                                <select class="form-control" id="theme" disabled>
                                    <option>Light</option>
                                    <option>Dark</option>
                                    <option>System Default</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
<script>
    $(document).ready(function() {
        // Fix for Topbar Avatar and Search Bar
        // This ensures the topbar components work correctly
        $(document).on('click', '#userProfileToggle', function(e) {
            e.stopPropagation();
            $('#userDropdown').toggleClass('show');
            
            // Close notification menu if open
            const notificationMenu = document.getElementById('notificationMenu');
            if (notificationMenu) notificationMenu.classList.remove('show');
        });
        
        // Fix dropdown placement
        $('#userDropdown').css('position', 'absolute');
        $('#userDropdown').css('z-index', '9999');
        
        // Stop propagation on dropdown
        $(document).on('click', '#userDropdown', function(e) {
            e.stopPropagation();
        });
        
        // Close dropdowns when clicking outside
        $(document).on('click', function() {
            $('#userDropdown').removeClass('show');
            $('#notificationMenu').removeClass('show');
        });
        
        // Fix search bar functionality
        $('#globalSearch').on('keypress', function(e) {
            if (e.which == 13) {
                $(this).closest('form').submit();
                return false;
            }
        });
        
        // Fix for the "Recently accessed" continuously loading
        // This stops the initialization of recent items if it's already done
        if (window.recentItemsInitialized !== true) {
            // Only initialize once
            window.recentItemsInitialized = true;
            
            // Load recent items once on page load
            if (typeof loadRecentItems === 'function') {
                loadRecentItems();
            }
        }
        
        // CRITICAL FIX: Force set form field values from hidden fields to solve the null value problem
        $('#phone').val($('#debug_phone').val());
        $('#address').val($('#debug_address').val());
        $('#bio').val($('#debug_bio').val());
        $('#date_of_birth').val($('#debug_dob').val());
        
        // Avatar upload functionality
        $('#avatar-upload-btn').click(function(e) {
            e.preventDefault();
            $('#avatarInput').click();
        });
        
        // File size validation before upload
        $('#avatarInput').change(function() {
            const file = this.files[0];
            
            if (!file) {
                return;
            }
            
            // Check file size (2MB max)
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            if (file.size > maxSize) {
                // Show error message
                $('#avatar-error-text').text('File size exceeds 2MB limit. Please choose a smaller image.');
                $('#avatar-error').show();
                // Clear file input
                $(this).val('');
                return;
            }
            
            // Check file type
            const acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!acceptedTypes.includes(file.type)) {
                $('#avatar-error-text').text('Please select a valid image file (JPEG, PNG, JPG, or GIF).');
                $('#avatar-error').show();
                $(this).val('');
                return;
            }
            
            // Hide any error message
            $('#avatar-error').hide();
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
            
            // Show loading indicator
            $('#avatar-loading').show();
            
            // Submit form
            $('#avatarForm').submit();
        });
        
        // Edit profile toggle button
        $('#edit-profile-toggle').click(function() {
            const isEditing = $(this).hasClass('active');
            
            if (isEditing) {
                // Disable editing
                $(this).removeClass('active');
                $(this).html('<i class="fas fa-edit"></i> Edit Profile');
                
                // Make specific fields readonly individually
                $('#phone').prop('readonly', true);
                $('#date_of_birth').prop('readonly', true);
                $('#address').prop('readonly', true);
                $('#bio').prop('readonly', true);
                
                $('#profile-actions').hide();
                
                // Update visual styles
                $('.editable-field').css('background-color', '#f8f9fa');
            } else {
                // Enable editing
                $(this).addClass('active');
                $(this).html('<i class="fas fa-times"></i> Cancel');
                
                // Make specific fields editable individually
                $('#phone').prop('readonly', false);
                $('#date_of_birth').prop('readonly', false);
                $('#address').prop('readonly', false);
                $('#bio').prop('readonly', false);
                
                $('#profile-actions').show();
                
                // Update visual styles
                $('.editable-field').css('background-color', '#fff');
                
                // Focus on the first editable field
                $('#phone').focus();
            }
        });
        
        // Cancel edit button
        $('#cancel-edit').click(function() {
            // Reset the form with original values
            $('#profile-form')[0].reset();
            
            // Make sure to reset fields with data from hidden fields
            $('#phone').val($('#debug_phone').val());
            $('#address').val($('#debug_address').val());
            $('#bio').val($('#debug_bio').val());
            $('#date_of_birth').val($('#debug_dob').val());
            
            // Disable editing
            $('#edit-profile-toggle').removeClass('active');
            $('#edit-profile-toggle').html('<i class="fas fa-edit"></i> Edit Profile');
            
            // Make fields readonly
            $('#phone').prop('readonly', true);
            $('#date_of_birth').prop('readonly', true);
            $('#address').prop('readonly', true);
            $('#bio').prop('readonly', true);
            
            $('#profile-actions').hide();
            
            // Update visual styles
            $('.editable-field').css('background-color', '#f8f9fa');
        });
        
        // Password strength meter
        $('#new_password').on('input', function() {
            const password = $(this).val();
            
            if (password.length === 0) {
                updateStrengthMeter(0, 'Enter a new password');
                return;
            }
            
            // Use zxcvbn to evaluate password strength
            const result = zxcvbn(password);
            const score = result.score;
            
            // Update UI based on score (0-4)
            let message = '';
            let progressClass = '';
            
            switch(score) {
                case 0:
                    message = 'Very weak password';
                    progressClass = 'bg-danger';
                    break;
                case 1:
                    message = 'Weak password';
                    progressClass = 'bg-danger';
                    break;
                case 2:
                    message = 'Fair password';
                    progressClass = 'bg-warning';
                    break;
                case 3:
                    message = 'Good password';
                    progressClass = 'bg-info';
                    break;
                case 4:
                    message = 'Strong password';
                    progressClass = 'bg-success';
                    break;
            }
            
            // If there are specific suggestions, add them to the message
            if (result.feedback.suggestions.length > 0) {
                message += ': ' + result.feedback.suggestions[0];
            }
            
            // Update the progress bar
            updateStrengthMeter((score + 1) * 20, message, progressClass);
        });
        
        function updateStrengthMeter(percentage, message, cssClass) {
            const progressBar = $('#password-strength-meter .progress-bar');
            const messageEl = $('#password-strength-text');
            
            progressBar.css('width', percentage + '%');
            progressBar.attr('aria-valuenow', percentage);
            
            // Remove all color classes and add the appropriate one
            progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
            if (cssClass) {
                progressBar.addClass(cssClass);
            }
            
            // Update message
            messageEl.text(message);
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert-dismissible').alert('close');
        }, 5000);
        
        // Helper function to check if field is editable and apply styling
        function checkEditableFields() {
            $('.editable-field').each(function() {
                if (!$(this).prop('readonly')) {
                    $(this).css('background-color', '#fff');
                    $(this).css('border-color', '#32bdea');
                } else {
                    $(this).css('background-color', '#f8f9fa');
                    $(this).css('border-color', '#ced4da');
                }
            });
        }
        
        // Run initially and on any change
        checkEditableFields();
        $('.editable-field').on('change focus blur', checkEditableFields);
    });
</script>
@endsection
```