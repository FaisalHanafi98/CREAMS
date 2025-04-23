<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profilestyle.css') }}">
    
    <style>
        .content-section {
            padding: 20px;
        }

        .header {
            background-color: #ffffff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .page-info {
            display: flex;
            flex-direction: column;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            background-image: linear-gradient(to right, #32bdea, #c850c0);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: none;
        }

        .breadcrumb a {
            color: #32bdea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #c850c0;
        }

        .separator {
            margin: 0 8px;
            color: #ccc;
        }

        .current {
            color: #888;
        }

        .profile-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .profile-avatar {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 20px;
            border: 4px solid #f8f9fa;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .avatar-overlay i {
            color: #ffffff;
            font-size: 24px;
        }

        .profile-avatar:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 5px;
            color: #333;
        }

        .profile-role {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 14px;
        }

        .meta-item i {
            margin-right: 8px;
            color: #32bdea;
        }

        .edit-profile-btn {
            padding: 8px 16px;
            background-color: #32bdea;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .edit-profile-btn:hover {
            background-color: #28a7d0;
        }

        .edit-profile-btn.active {
            background-color: #dc3545;
        }

        .edit-profile-btn.active:hover {
            background-color: #c82333;
        }

        .profile-tabs {
            margin-top: 20px;
        }

        .nav-pills .nav-link {
            color: #6c757d;
            background-color: #f8f9fa;
            margin-right: 10px;
            border-radius: 5px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link i {
            color: #32bdea;
        }

        .nav-pills .nav-link.active {
            color: #ffffff;
            background-color: #32bdea;
        }

        .nav-pills .nav-link.active i {
            color: #ffffff;
        }

        .tab-content {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #32bdea;
            box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
        }

        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-primary {
            background-color: #32bdea;
            border-color: #32bdea;
        }

        .btn-primary:hover {
            background-color: #28a7d0;
            border-color: #28a7d0;
        }

        .password-strength {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            margin: 8px 0;
        }

        .admin-dropdown {
            position: relative;
        }

        .admin-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .admin-dropdown-toggle:hover {
            background-color: #f8f9fa;
        }

        .admin-dropdown-toggle img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .admin-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .admin-dropdown-menu.show {
            display: block;
        }

        .admin-dropdown-menu a,
        .admin-dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            color: #555;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .admin-dropdown-menu a:hover,
        .admin-dropdown-menu button:hover {
            background-color: #f8f9fa;
            color: #32bdea;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-avatar {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .profile-meta {
                justify-content: center;
            }

            .edit-profile-btn {
                margin-top: 20px;
            }

            .nav-pills .nav-link {
                padding: 8px 10px;
                font-size: 14px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="{{ asset('images/favicon.png') }}" alt="CREAMS Logo">
                <span class="logo-text">CREAMS</span>
            </div>
            <div class="toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </div>
        </div>
        
        <div class="admin-profile">
            <div class="admin-avatar">
                @if(isset($user['avatar']) && $user['avatar'])
                    <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="{{ $user['name'] ?? 'User' }}">
                @else
                    <img src="{{ asset('images/admin-avatar.jpg') }}" alt="{{ $user['name'] ?? 'User' }}">
                @endif
            </div>
            <div class="admin-info">
                <div class="admin-name">{{ $user['name'] ?? 'User' }}</div>
                <div class="admin-role">{{ ucfirst($role) }}</div>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                    <div class="tooltip-sidebar">Dashboard</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('traineeshome') ? 'active' : '' }}">
                <a href="{{ route('traineeshome') }}">
                    <i class="fas fa-user-graduate"></i>
                    <span>Trainees</span>
                    <div class="tooltip-sidebar">Trainee Management</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('teachershome') ? 'active' : '' }}">
                <a href="{{ route('teachershome') }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Teachers</span>
                    <div class="tooltip-sidebar">Teacher Management</div>
                </a>
            </li>
            
            <!-- Display role-specific menu items -->
            @if($role == 'admin')
                <li class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <a href="{{ route('admin.users') }}">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                        <div class="tooltip-sidebar">User Management</div>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.centres') ? 'active' : '' }}">
                    <a href="{{ route('admin.centres') }}">
                        <i class="fas fa-building"></i>
                        <span>Centres</span>
                        <div class="tooltip-sidebar">Centre Management</div>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings') }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                        <div class="tooltip-sidebar">System Settings</div>
                    </a>
                </li>
            @endif
            
            <!-- Common menu items for all users -->
            <li class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                <a href="{{ route('profile') }}">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                    <div class="tooltip-sidebar">My Profile</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('messages') ? 'active' : '' }}">
                <a href="{{ route('messages') }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <div class="tooltip-sidebar">Messages</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('notifications') ? 'active' : '' }}">
                <a href="{{ route('notifications') }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <div class="tooltip-sidebar">Notifications</div>
                </a>
            </li>
        </ul>
        
        <form method="POST" action="{{ route('logout') }}" class="logout-container">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="content-section">
            <div class="header">
                <div class="header-content">
                    <div class="page-info">
                        <h1 class="page-title">My Profile</h1>
                        <div class="breadcrumb">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                            <span class="separator">/</span>
                            <span class="current">My Profile</span>
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <div class="admin-dropdown">
                            <div class="admin-dropdown-toggle">
                                @if(isset($user['avatar']) && $user['avatar'])
                                    <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="{{ $user['name'] ?? 'User' }}">
                                @else
                                    <img src="{{ asset('images/admin-avatar.jpg') }}" alt="{{ $user['name'] ?? 'User' }}">
                                @endif
                                <span>{{ $user['name'] ?? 'User' }}</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="admin-dropdown-menu">
                                <a href="{{ route('profile') }}">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Container -->
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar" id="avatar-container">
                        @if(isset($user['avatar']) && $user['avatar'])
                            <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="{{ $user['name'] ?? 'User' }}" id="avatar-preview">
                        @else
                            <img src="{{ asset('images/admin-avatar.jpg') }}" alt="{{ $user['name'] ?? 'User' }}" id="avatar-preview">
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
                        <div class="profile-role">{{ ucfirst($role) }}</div>
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
                
                <!-- Avatar upload error alert -->
                <div id="avatar-error" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="avatar-error-text"></span>
                    <button type="button" class="close" onclick="$('#avatar-error').hide()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <!-- Alerts -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                            <form id="profile-form" action="{{ route('profile.update') }}" method="POST">
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
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $user['phone'] ?? '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_of_birth">Date of Birth</label>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ isset($user['date_of_birth']) ? date('Y-m-d', strtotime($user['date_of_birth'])) : '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" readonly>{{ $user['address'] ?? '' }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="5" readonly>{{ $user['bio'] ?? '' }}</textarea>
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
        
        <!-- Footer -->
        <div class="dashboard-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="{{ asset('images/favicon.png') }}" alt="CREAMS Logo">
                    <span>CREAMS</span>
                </div>
                <div class="footer-text">
                    Community-based REhAbilitation Management System &copy; {{ date('Y') }} IIUM
                </div>
                <div class="footer-links">
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                    <a href="#" class="footer-link">Help Centre</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    
    <script>
        $(document).ready(function() {
            // Sidebar toggle
            $('.toggle-btn').click(function() {
                $('.sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('expanded');
            });
            
            // Admin dropdown
            $('.admin-dropdown-toggle').click(function() {
                $('.admin-dropdown-menu').toggleClass('show');
            });
            
            // Close dropdown when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('.admin-dropdown').length) {
                    $('.admin-dropdown-menu').removeClass('show');
                }
            });
            
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
                    $('#profile-form input, #profile-form textarea').prop('readonly', true);
                    $('#profile-actions').hide();
                } else {
                    // Enable editing
                    $(this).addClass('active');
                    $(this).html('<i class="fas fa-times"></i> Cancel');
                    $('#profile-form input, #profile-form textarea').not('#iium_id').prop('readonly', false);
                    $('#profile-actions').show();
                }
            });
            
            // Cancel edit button
            $('#cancel-edit').click(function() {
                // Reset the form
                $('#profile-form')[0].reset();
                
                // Disable editing
                $('#edit-profile-toggle').removeClass('active');
                $('#edit-profile-toggle').html('<i class="fas fa-edit"></i> Edit Profile');
                $('#profile-form input, #profile-form textarea').prop('readonly', true);
                $('#profile-actions').hide();
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
        });
    </script>
</body>
</html>