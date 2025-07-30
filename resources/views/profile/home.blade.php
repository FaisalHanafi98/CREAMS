@extends('layouts.app')

@section('title', 'User Profile - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
        --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }
    
    /* Modern Profile Container */
    .profile-wrapper {
        background: var(--light-bg);
        min-height: 100vh;
        padding: 30px 0;
    }
    
    .profile-container {
        background: #fff;
        border-radius: 25px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid var(--border-color);
        max-width: 1200px;
        margin: 0 auto;
    }
    
    /* Profile Header */
    .profile-header {
        background: var(--gradient-primary);
        padding: 40px;
        color: white;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }
    
    .profile-user-info {
        display: flex;
        align-items: center;
        position: relative;
        z-index: 2;
    }
    
    .profile-avatar {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        position: relative;
        background: #fff;
        margin-right: 30px;
        border: 5px solid rgba(255,255,255,0.3);
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
        height: 50px;
        background: rgba(0,0,0,0.7);
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
    
    .profile-details h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .profile-role {
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.95rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 15px;
        backdrop-filter: blur(10px);
    }
    
    .profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        opacity: 0.95;
    }
    
    .meta-item i {
        margin-right: 8px;
        width: 16px;
    }
    
    .edit-profile-btn {
        position: relative;
        z-index: 2;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .edit-profile-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .edit-profile-btn.editing {
        background: rgba(255,0,0,0.2);
        border-color: rgba(255,0,0,0.3);
    }
    
    /* Horizontal Tabs */
    .profile-tabs {
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        padding: 0;
    }
    
    .nav-tabs {
        border: none;
        justify-content: center;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        margin: 0;
        padding: 20px 0 0 0;
    }
    
    .nav-tabs .nav-item {
        margin: 0 5px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-radius: 25px 25px 0 0;
        padding: 15px 25px;
        font-weight: 600;
        color: #6c757d;
        background: #fff;
        margin-bottom: -1px;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 150px;
        justify-content: center;
    }
    
    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .nav-tabs .nav-link.active {
        background: #fff;
        color: var(--primary-color);
        border-bottom: 3px solid var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .nav-tabs .nav-link i {
        font-size: 1.1rem;
    }
    
    /* Tab Content */
    .tab-content {
        background: #fff;
        padding: 40px;
        min-height: 600px;
    }
    
    .tab-pane {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Form Styling */
    .form-section {
        margin-bottom: 40px;
    }
    
    .form-section h4 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control {
        border-radius: 12px;
        border: 2px solid var(--border-color);
        padding: 12px 16px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .form-control[readonly] {
        background-color: var(--light-bg);
        color: #6c757d;
        cursor: not-allowed;
    }
    
    .form-control:not([readonly]):focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
        transform: translateY(-1px);
    }
    
    .editable-field:not([readonly]) {
        border-color: var(--secondary-color);
        background-color: #fff;
    }
    
    /* Select field specific styling */
    select.form-control {
        height: 50px;
        line-height: 1.5;
        font-size: 1rem;
        padding: 12px 16px;
    }
    
    select.form-control option {
        padding: 8px 12px;
        font-size: 1rem;
        line-height: 1.4;
        height: auto;
        min-height: 32px;
    }
    
    /* Action Buttons */
    .form-actions {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }
    
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: var(--gradient-primary);
        border: none;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(200, 80, 192, 0.4);
    }
    
    /* Letter Generation Styling */
    .letter-preview {
        border: 2px solid var(--border-color);
        border-radius: 15px;
        padding: 30px;
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .letter-header, .letter-footer {
        text-align: center;
        padding: 20px;
        border: 2px dashed var(--border-color);
        border-radius: 10px;
        margin-bottom: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .letter-header:hover, .letter-footer:hover {
        border-color: var(--primary-color);
        background: var(--light-bg);
    }
    
    .letter-header img, .letter-footer img {
        max-width: 100%;
        height: auto;
        max-height: 150px;
    }
    
    .upload-placeholder {
        color: #6c757d;
        font-style: italic;
    }
    
    /* Password Strength */
    .password-strength {
        margin-top: 15px;
    }
    
    .password-strength .progress {
        height: 8px;
        border-radius: 4px;
        margin: 10px 0;
    }
    
    /* Status Badges */
    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }
    
    .status-inactive {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            padding: 30px 20px;
        }
        
        .profile-user-info {
            flex-direction: column;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            margin-right: 0;
            margin-bottom: 20px;
            width: 100px;
            height: 100px;
        }
        
        .profile-details h2 {
            font-size: 1.8rem;
        }
        
        .profile-meta {
            justify-content: center;
            text-align: center;
        }
        
        .nav-tabs {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .nav-tabs .nav-link {
            min-width: auto;
            margin: 5px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        
        .nav-tabs .nav-link span {
            display: none;
        }
        
        .nav-tabs .nav-link i {
            margin-right: 0;
        }
        
        .tab-content {
            padding: 20px;
        }
        
        .form-section h4 {
            font-size: 1.1rem;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .form-actions .btn {
            width: 100%;
        }
        
        .edit-profile-btn {
            width: 100%;
            justify-content: center;
            margin-top: 15px;
        }
    }
    
    @media (max-width: 480px) {
        .profile-container {
            margin: 10px;
            border-radius: 15px;
        }
        
        .tab-content {
            padding: 15px;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .letter-preview {
            padding: 15px;
            font-size: 0.9rem;
        }
    }

    .upload-area {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .upload-area:hover {
        border-color: #007bff;
        background: #e7f3ff;
    }

    .preview-area {
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .preview-area i {
        font-size: 2rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .preview-area p {
        color: #6c757d;
        margin: 0;
        font-size: 0.9rem;
    }

    .preview-area img {
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="profile-wrapper">
    <div class="container-fluid">
        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
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
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        @endif
        
        <!-- Profile Container -->
        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-user-info">
                    <div class="profile-avatar" id="avatar-container">
                        @php
                            $userModel = \App\Models\User::find(session('id'));
                            $userModel = $userModel ? $userModel->fresh() : null; // Always get fresh data from database
                            $avatarUrl = $userModel ? $userModel->avatar_url : asset('images/default-avatar.svg');
                        @endphp
                        <img src="{{ $avatarUrl }}" alt="{{ $user['name'] ?? 'User' }}" id="avatar-preview" onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}'; console.warn('Avatar not found, using default');">
                        <div class="avatar-overlay" id="avatar-upload-btn" title="Change Profile Photo">
                            <i class="fas fa-camera text-white"></i>
                        </div>
                    </div>
                    <div class="profile-details">
                        <h2>{{ $user['name'] ?? 'User' }}</h2>
                        @php
                            $roleDisplayNames = [
                                'admin' => 'Administrator',
                                'supervisor' => 'Centre Supervisor', 
                                'teacher' => 'Special Education Teacher',
                                'ajk' => 'Activity Coordinator'
                            ];
                            $roleDisplay = $roleDisplayNames[$role] ?? ucfirst($role);
                        @endphp
                        <div class="profile-role">{{ $roleDisplay }}</div>
                        <div class="profile-meta">
                            <div class="meta-item">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $user['email'] ?? 'No email' }}</span>
                            </div>
                            @if(isset($user['iium_id']) && $user['iium_id'])
                            <div class="meta-item">
                                <i class="fas fa-id-card"></i>
                                <span>{{ $user['iium_id'] }}</span>
                            </div>
                            @endif
                            @if(isset($user['centre_location']) && $user['centre_location'])
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $user['centre_location'] }}</span>
                            </div>
                            @endif
                            <div class="meta-item">
                                <i class="fas fa-circle"></i>
                                <span class="status-badge status-{{ $user['status'] ?? 'active' }}">
                                    {{ ucfirst($user['status'] ?? 'active') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="edit-profile-btn" id="edit-profile-toggle">
                    <i class="fas fa-edit"></i>
                    <span>Edit Profile</span>
                </button>
            </div>

            <!-- Hidden file input for avatar upload -->
            <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                @csrf
                <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
            </form>
            
            <!-- Horizontal Navigation Tabs -->
            <div class="profile-tabs">
                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab">
                            <i class="fas fa-user"></i>
                            <span>Personal Info</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="professional-tab" data-toggle="tab" href="#professional" role="tab">
                            <i class="fas fa-briefcase"></i>
                            <span>Professional</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="system-tab" data-toggle="tab" href="#system" role="tab">
                            <i class="fas fa-cogs"></i>
                            <span>System Info</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab">
                            <i class="fas fa-shield-alt"></i>
                            <span>Security</span>
                        </a>
                    </li>
                    @if(session('role') === 'admin')
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="letters-tab" data-toggle="tab" href="#letters" role="tab">
                            <i class="fas fa-file-alt"></i>
                            <span>Letter Generator</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content" id="profileTabContent">
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <form id="personal-form" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="form-section">
                            <h4><i class="fas fa-user-circle"></i> Basic Information</h4>
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
                                <textarea class="form-control editable-field" id="address" name="address" rows="3" readonly>{{ trim($user['address'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="about">Bio / About Me</label>
                                <textarea class="form-control editable-field" id="about" name="about" rows="4" readonly>{{ trim($user['about'] ?? '') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions" id="personal-actions" style="display: none;">
                            <button type="button" class="btn btn-outline-secondary" id="cancel-personal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
                
                <!-- Professional Information Tab -->
                <div class="tab-pane fade" id="professional" role="tabpanel">
                    <form id="professional-form" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="form-section">
                            <h4><i class="fas fa-graduation-cap"></i> Education Background</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="education_level">Education Level</label>
                                        <select class="form-control editable-field" id="education_level" name="education_level" disabled>
                                            <option value="">Select Education Level</option>
                                            <option value="Diploma" {{ ($user['education_level'] ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                            <option value="Bachelor's" {{ ($user['education_level'] ?? '') == "Bachelor's" ? 'selected' : '' }}>Bachelor's Degree</option>
                                            <option value="Master's" {{ ($user['education_level'] ?? '') == "Master's" ? 'selected' : '' }}>Master's Degree</option>
                                            <option value="PhD" {{ ($user['education_level'] ?? '') == 'PhD' ? 'selected' : '' }}>PhD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="education_specialization">Education Specialization</label>
                                        <input type="text" class="form-control editable-field" id="education_specialization" name="education_specialization" value="{{ $user['education_specialization'] ?? '' }}" readonly placeholder="e.g., Computer Science, Psychology">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Professional Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position">Position/Title</label>
                                        <input type="text" class="form-control editable-field" id="position" name="position" value="{{ $user['position'] ?? '' }}" readonly placeholder="e.g., Senior Teacher, Coordinator">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="teaching_specialization">Teaching Specialization</label>
                                        <input type="text" class="form-control editable-field" id="teaching_specialization" name="teaching_specialization" value="{{ $user['teaching_specialization'] ?? '' }}" readonly placeholder="e.g., Special Education, Counseling">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions" id="professional-actions" style="display: none;">
                            <button type="button" class="btn btn-outline-secondary" id="cancel-professional">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
                
                <!-- System Information Tab -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <div class="form-section">
                        <h4><i class="fas fa-server"></i> System Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="iium_id">IIUM ID</label>
                                    <input type="text" class="form-control" id="iium_id" value="{{ $user['iium_id'] ?? 'Not Assigned' }}" readonly>
                                    <small class="form-text text-muted">Your IIUM identification number</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">System Role</label>
                                    <input type="text" class="form-control" id="role" value="{{ $roleDisplay }}" readonly>
                                    <small class="form-text text-muted">Your role in the system</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Account Status</label>
                                    <input type="text" class="form-control" id="status" value="{{ ucfirst($user['status'] ?? 'active') }}" readonly>
                                    <small class="form-text text-muted">Current account status</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="centre_location">Assigned Centre</label>
                                    <input type="text" class="form-control" id="centre_location" value="{{ $user['centre_location'] ?? 'Not Assigned' }}" readonly>
                                    <small class="form-text text-muted">Your current work location</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="centre_id">Centre ID</label>
                                    <input type="text" class="form-control" id="centre_id" value="{{ $user['centre_id'] ?? 'Not Assigned' }}" readonly>
                                    <small class="form-text text-muted">Internal centre identifier</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_last_accessed_at">Last Access</label>
                                    <input type="text" class="form-control" id="user_last_accessed_at" value="{{ isset($user['user_last_accessed_at']) ? \Carbon\Carbon::parse($user['user_last_accessed_at'])->format('M d, Y - h:i A') : 'Never' }}" readonly>
                                    <small class="form-text text-muted">Last system access time</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        System information is managed by administrators and cannot be modified by users.
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <div class="form-section">
                        <h4><i class="fas fa-key"></i> Change Password</h4>
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
                                        <small class="form-text text-muted">Password must be at least 8 characters and include uppercase, lowercase, number, and special character.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="new_password_confirmation">Confirm New Password</label>
                                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                    </div>
                                </div>
                            </div>
                            <div class="password-strength" id="password-strength-meter">
                                <label>Password Strength:</label>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small class="text-muted" id="password-strength-text">Enter a new password</small>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                @if(session('role') === 'admin')
                <!-- Letter Generator Tab -->
                <div class="tab-pane fade" id="letters" role="tabpanel">
                    <!-- Template Management Section -->
                    <div class="form-section">
                        <h4><i class="fas fa-layer-group"></i> Letter Template Management</h4>
                        
                        <!-- Current Template Status -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle"></i> Current Active Template</h6>
                            <p class="mb-0" id="current-template-info">
                                @php
                                    $activeTemplate = \App\Models\LetterTemplate::where('is_active', true)->latest()->first();
                                @endphp
                                @if($activeTemplate)
                                    <strong>{{ $activeTemplate->template_name }}</strong>
                                    @if($activeTemplate->template_description)
                                        - {{ $activeTemplate->template_description }}
                                    @endif
                                    <br><small class="text-muted">Created: {{ $activeTemplate->created_at->format('d M Y H:i') }}</small>
                                @else
                                    <em>No active template found. Default template will be used.</em>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Template Name -->
                        <div class="form-group mb-4">
                            <label for="template_name">Template Name</label>
                            <input type="text" class="form-control" id="template_name" name="template_name" placeholder="e.g., Official Recommendation Letter">
                            <small class="form-text text-muted">Give your template a descriptive name</small>
                        </div>

                        <!-- Template Description -->
                        <div class="form-group mb-4">
                            <label for="template_description">Template Description</label>
                            <input type="text" class="form-control" id="template_description" name="template_description" placeholder="Brief description of template purpose">
                        </div>

                        <!-- Letter Header Image -->
                        <div class="form-group mb-4">
                            <label>Letter Header (Image)</label>
                            <input type="file" id="header-input" name="header_image" accept="image/*" style="display: none;">
                            <div class="upload-area" onclick="document.getElementById('header-input').click();">
                                <div id="header-preview" class="preview-area">
                                    @if($activeTemplate && $activeTemplate->header_image_url)
                                        <img src="{{ $activeTemplate->header_image_url }}" alt="Header Image" style="max-width: 20%; height: auto; max-height: 80px; border: 2px solid #ddd; border-radius: 8px;">
                                    @else
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload header image</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Header Text Option -->
                        <div class="form-group mb-4">
                            <label for="header_text">Header Text (Optional)</label>
                            <textarea class="form-control" id="header_text" name="header_text" rows="2" placeholder="Optional text to overlay on header image or use as header if no image"></textarea>
                            <small class="form-text text-muted">This text will appear over the header image or replace it if no image is uploaded</small>
                        </div>

                        <!-- Letter Footer Image -->
                        <div class="form-group mb-4">
                            <label>Letter Footer (Image)</label>
                            <input type="file" id="footer-input" name="footer_image" accept="image/*" style="display: none;">
                            <div class="upload-area" onclick="document.getElementById('footer-input').click();">
                                <div id="footer-preview" class="preview-area">
                                    @if($activeTemplate && $activeTemplate->footer_image_url)
                                        <img src="{{ $activeTemplate->footer_image_url }}" alt="Footer Image" style="max-width: 20%; height: auto; max-height: 60px; border: 2px solid #ddd; border-radius: 8px;">
                                    @else
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload footer image</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Footer Text Option -->
                        <div class="form-group mb-4">
                            <label for="footer_text">Footer Text (Optional)</label>
                            <textarea class="form-control" id="footer_text" name="footer_text" rows="2" placeholder="Optional text to overlay on footer image or use as footer if no image"></textarea>
                            <small class="form-text text-muted">This text will appear over the footer image or replace it if no image is uploaded</small>
                        </div>

                        <div class="template-actions mb-4">
                            <button type="button" class="btn btn-outline-primary" id="save-template-btn">
                                <i class="fas fa-save"></i> Save Template
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="view-templates-archive-btn">
                                <i class="fas fa-folder-open"></i> View Templates & Letter Archive
                            </button>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-file-alt"></i> Letter Generator</h4>
                        <form id="letter-form" action="{{ route('profile.letter.generate') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="immediate_download" value="1">
                            
                            <!-- Letter Reference and Date -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="letter_ref">Letter Reference ID</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="letter_ref" name="reference_number" value="" required readonly>
                                            <button type="button" class="btn btn-outline-secondary" id="generate-ref-btn" title="Generate New Reference">
                                                <i class="fas fa-refresh"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Unique reference number for this letter</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="letter_date">Letter Date</label>
                                        <input type="date" class="form-control" id="letter_date" name="letter_date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Letter Name -->
                            <div class="form-group mb-4">
                                <label for="letter_name">Letter Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="letter_name" name="letter_name" placeholder="e.g., Recommendation Letter for John Doe" required>
                                <small class="form-text text-muted">This name will be used as the filename when downloading the PDF</small>
                            </div>
                            
                            <!-- Recipient Information -->
                            <div class="form-group">
                                <label for="recipient_name">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" placeholder="Enter recipient's full name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="recipient_address">Recipient Address</label>
                                <textarea class="form-control" id="recipient_address" name="recipient_address" rows="3" placeholder="Enter complete address including postal code" required></textarea>
                            </div>
                            
                            <!-- Letter Subject and Content -->
                            <div class="form-group">
                                <label for="letter_subject">Letter Subject</label>
                                <input type="text" class="form-control" id="letter_subject" name="subject" placeholder="Enter letter subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="letter_content">Letter Content</label>
                                <textarea class="form-control" id="letter_content" name="content" rows="8" placeholder="Enter the main content of your letter..." required></textarea>
                                <small class="form-text text-muted">You can use basic formatting. The content will be automatically formatted in the final letter.</small>
                            </div>
                            
                            <!-- Letter Preview -->
                            <div class="letter-preview" id="letter-preview" style="display: none;">
                                <h5><i class="fas fa-eye"></i> Letter Preview</h5>
                                <div id="preview-content">
                                    <!-- Preview will be generated here -->
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline-info" id="preview-letter">Preview Letter</button>
                                <button type="submit" class="btn btn-primary">Generate & Download Letter</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
<script>
$(document).ready(function() {
    let editMode = false;
    
    // Initially hide avatar upload overlay
    $('.avatar-overlay').hide();
    
    // Store original values for all forms
    const originalValues = {
        personal: {
            phone: @json($user['phone'] ?? ''),
            date_of_birth: @json($user['date_of_birth'] ?? ''),
            address: @json($user['address'] ?? ''),
            about: @json($user['about'] ?? '')
        },
        professional: {
            education_level: @json($user['education_level'] ?? ''),
            education_specialization: @json($user['education_specialization'] ?? ''),
            position: @json($user['position'] ?? ''),
            teaching_specialization: @json($user['teaching_specialization'] ?? '')
        }
    };
    
    // Edit Profile Toggle
    $('#edit-profile-toggle').click(function() {
        editMode = !editMode;
        
        if (editMode) {
            $(this).addClass('editing');
            $(this).html('<i class="fas fa-times"></i><span>Cancel Edit</span>');
            enableEditing();
        } else {
            $(this).removeClass('editing');
            $(this).html('<i class="fas fa-edit"></i><span>Edit Profile</span>');
            disableEditing();
            resetAllForms();
        }
    });
    
    function enableEditing() {
        // Enable personal fields
        $('#phone, #date_of_birth, #address, #about').prop('readonly', false);
        $('#personal-actions').show();
        
        // Enable professional fields
        $('#education_level').prop('disabled', false);
        $('#education_specialization, #position, #teaching_specialization').prop('readonly', false);
        $('#professional-actions').show();
        
        // Enable avatar upload
        $('.avatar-overlay').show();
        
        // Update styling
        $('.editable-field').removeClass('readonly-field');
        updateFieldStyling();
    }
    
    function disableEditing() {
        // Disable personal fields
        $('#phone, #date_of_birth, #address, #about').prop('readonly', true);
        $('#personal-actions').hide();
        
        // Disable professional fields
        $('#education_level').prop('disabled', true);
        $('#education_specialization, #position, #teaching_specialization').prop('readonly', true);
        $('#professional-actions').hide();
        
        // Disable avatar upload
        $('.avatar-overlay').hide();
        
        // Update styling
        $('.editable-field').addClass('readonly-field');
        updateFieldStyling();
    }
    
    function resetAllForms() {
        // Reset personal form
        $('#phone').val(originalValues.personal.phone);
        $('#date_of_birth').val(originalValues.personal.date_of_birth);
        $('#address').val(originalValues.personal.address);
        $('#about').val(originalValues.personal.about);
        
        // Reset professional form
        $('#education_level').val(originalValues.professional.education_level);
        $('#education_specialization').val(originalValues.professional.education_specialization);
        $('#position').val(originalValues.professional.position);
        $('#teaching_specialization').val(originalValues.professional.teaching_specialization);
    }
    
    function updateFieldStyling() {
        $('.editable-field').each(function() {
            const isReadonly = $(this).prop('readonly') || $(this).prop('disabled');
            if (isReadonly) {
                $(this).css({
                    'background-color': 'var(--light-bg)',
                    'border-color': 'var(--border-color)',
                    'color': '#6c757d'
                });
            } else {
                $(this).css({
                    'background-color': '#fff',
                    'border-color': 'var(--secondary-color)',
                    'color': 'var(--dark-color)'
                });
            }
        });
    }
    
    // Cancel buttons for individual forms
    $('#cancel-personal').click(function() {
        $('#phone').val(originalValues.personal.phone);
        $('#date_of_birth').val(originalValues.personal.date_of_birth);
        $('#address').val(originalValues.personal.address);
        $('#about').val(originalValues.personal.about);
    });
    
    $('#cancel-professional').click(function() {
        $('#education_level').val(originalValues.professional.education_level);
        $('#education_specialization').val(originalValues.professional.education_specialization);
        $('#position').val(originalValues.professional.position);
        $('#teaching_specialization').val(originalValues.professional.teaching_specialization);
    });
    
    // Avatar upload functionality
    $('#avatar-upload-btn').click(function(e) {
        e.preventDefault();
        if (!editMode) {
            alert('Please enable edit mode to change your profile photo.');
            return;
        }
        $('#avatarInput').click();
    });
    
    $('#avatarInput').change(function() {
        const file = this.files[0];
        
        if (!file) return;
        
        // File validation
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            showErrorAlert('File size exceeds 2MB limit. Please choose a smaller image.');
            $(this).val('');
            return;
        }
        
        const acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!acceptedTypes.includes(file.type)) {
            showErrorAlert('Please select a valid image file (JPEG, PNG, JPG, or GIF).');
            $(this).val('');
            return;
        }
        
        // Show loading state
        showLoadingOverlay('Uploading profile photo...');
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#avatar-preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
        
        // Submit form with AJAX to handle response properly
        const formData = new FormData($('#avatarForm')[0]);
        
        $.ajax({
            url: $('#avatarForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoadingOverlay();
                if (response.success || $(response).find('.alert-success').length > 0) {
                    showSuccessAlert('Profile photo updated successfully!');
                    // Force refresh the avatar with a new timestamp
                    const newSrc = $('#avatar-preview').attr('src').split('?')[0] + '?v=' + new Date().getTime();
                    $('#avatar-preview').attr('src', newSrc);
                } else {
                    showErrorAlert('Failed to update profile photo. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                hideLoadingOverlay();
                console.error('Avatar upload error:', error);
                showErrorAlert('An error occurred while uploading the photo. Please try again.');
            }
        });
    });
    
    // Form submission handlers with loading states and validation
    $('#personal-form').submit(function(e) {
        if (!validatePersonalForm()) {
            e.preventDefault();
            return false;
        }
        showLoadingOverlay('Updating personal information...');
    });
    
    $('#professional-form').submit(function(e) {
        if (!validateProfessionalForm()) {
            e.preventDefault();
            return false;
        }
        showLoadingOverlay('Updating professional information...');
    });
    
    // Form validation functions
    function validatePersonalForm() {
        let isValid = true;
        
        // Clear previous error styles
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate phone number (if provided)
        const phone = $('#phone').val().trim();
        if (phone && !/^\+?[\d\s\-\(\)]{10,}$/.test(phone)) {
            showFieldError('#phone', 'Please enter a valid phone number');
            isValid = false;
        }
        
        // Validate date of birth (if provided)
        const dob = $('#date_of_birth').val();
        if (dob) {
            const dobDate = new Date(dob);
            const today = new Date();
            const age = today.getFullYear() - dobDate.getFullYear();
            
            if (age < 16 || age > 100) {
                showFieldError('#date_of_birth', 'Please enter a valid date of birth (age should be between 16-100)');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function validateProfessionalForm() {
        let isValid = true;
        
        // Clear previous error styles
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // No specific validation needed for professional form currently
        // All fields are optional
        
        return isValid;
    }
    
    function showFieldError(fieldSelector, message) {
        const field = $(fieldSelector);
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback d-block">${message}</div>`);
    }
    
    // Utility functions for better UX
    function showLoadingOverlay(message) {
        if ($('#loading-overlay').length === 0) {
            $('body').append(`
                <div id="loading-overlay" style="
                    position: fixed; 
                    top: 0; 
                    left: 0; 
                    width: 100%; 
                    height: 100%; 
                    background: rgba(0,0,0,0.7); 
                    z-index: 9999; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center;
                    color: white;
                    font-size: 1.2rem;
                ">
                    <div style="text-align: center;">
                        <div class="spinner-border text-light mb-3" role="status"></div>
                        <div>${message}</div>
                    </div>
                </div>
            `);
        }
    }
    
    function hideLoadingOverlay() {
        $('#loading-overlay').remove();
    }
    
    function showErrorAlert(message) {
        // Remove existing error alerts
        $('.alert-danger').remove();
        
        // Add new error alert
        $('.profile-container').before(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert-danger').alert('close');
        }, 5000);
    }
    
    // Hide loading overlay when page loads (in case of redirects)
    $(window).on('load', function() {
        hideLoadingOverlay();
    });
    
    // Password strength meter
    $('#new_password').on('input', function() {
        const password = $(this).val();
        
        if (password.length === 0) {
            updateStrengthMeter(0, 'Enter a new password');
            return;
        }
        
        const result = zxcvbn(password);
        const score = result.score;
        
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
        
        if (result.feedback.suggestions.length > 0) {
            message += ': ' + result.feedback.suggestions[0];
        }
        
        updateStrengthMeter((score + 1) * 20, message, progressClass);
    });
    
    function updateStrengthMeter(percentage, message, cssClass) {
        const progressBar = $('#password-strength-meter .progress-bar');
        const messageEl = $('#password-strength-text');
        
        progressBar.css('width', percentage + '%');
        progressBar.attr('aria-valuenow', percentage);
        
        progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
        if (cssClass) {
            progressBar.addClass(cssClass);
        }
        
        messageEl.text(message);
    }
    
    // Letter Generator Functions
    $('#header-input').change(function() {
        handleImageUpload(this, '#header-preview');
    });
    
    $('#footer-input').change(function() {
        handleImageUpload(this, '#footer-preview');
    });
    
    function handleImageUpload(input, previewSelector) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const maxHeight = previewSelector.includes('header') ? '80px' : '60px';
                $(previewSelector).html(`<img src="${e.target.result}" alt="Preview" style="max-width: 20%; height: auto; max-height: ${maxHeight}; border: 2px solid #ddd; border-radius: 8px;">`);
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Letter Reference ID Generator
    $('#letter_ref').click(function() {
        if (confirm('Generate a new reference ID?')) {
            const newRef = 'CREAMS-' + new Date().getFullYear() + '-' + String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
            $(this).val(newRef);
        }
    });
    
    // Letter Preview
    $('#preview-letter').click(function() {
        generateLetterPreview();
    });
    
    // Generate new reference number
    $('#generate-ref-btn').click(function() {
        generateNewReference();
    });
    
    // Initialize with a reference number on page load
    generateNewReference();
    
    function generateNewReference() {
        const btn = $('#generate-ref-btn');
        const input = $('#letter_ref');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ route('profile.letter.newReference') }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    input.val(response.reference);
                } else {
                    // Fallback to client-side generation
                    const fallbackRef = 'LTR/' + new Date().getFullYear() + '/' + 
                                       String(new Date().getMonth() + 1).padStart(2, '0') + '/' +
                                       String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
                    input.val(fallbackRef);
                }
            },
            error: function() {
                // Fallback to client-side generation
                const fallbackRef = 'LTR/' + new Date().getFullYear() + '/' + 
                                   String(new Date().getMonth() + 1).padStart(2, '0') + '/' +
                                   String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
                input.val(fallbackRef);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-refresh"></i>');
            }
        });
    }
    
    function generateLetterPreview() {
        const formData = {
            letter_name: $('#letter_name').val(),
            subject: $('#letter_subject').val(),
            content: $('#letter_content').val(),
            letter_date: $('#letter_date').val(), 
            recipient_name: $('#recipient_name').val(),
            recipient_address: $('#recipient_address').val()
        };
        
        // Validate required fields
        if (!formData.letter_name || !formData.subject || !formData.content) {
            alert('Please fill in the letter name, subject and content fields to generate a preview.');
            return;
        }
        
        const previewBtn = $('#preview-letter');
        previewBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating Preview...');
        
        $.ajax({
            url: '{{ route('profile.letter.preview') }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#preview-content').html(response.html);
                    $('#letter-preview').show();
                    
                    // Scroll to preview
                    $('html, body').animate({
                        scrollTop: $('#letter-preview').offset().top - 100
                    }, 500);
                } else {
                    alert('Failed to generate preview: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to generate preview.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                previewBtn.prop('disabled', false).html('<i class="fas fa-eye"></i> Preview Letter');
            }
        });
    }
    
    // Initialize styling
    updateFieldStyling();
    disableEditing();
    
    // Check for selected template from letters archive page
    checkForSelectedTemplate();
    
    // Template Management JavaScript
    $('#save-template-btn').click(function() {
        const templateData = {
            template_name: $('#template_name').val(),
            template_description: $('#template_description').val(),
            header_text: $('#header_text').val(),
            footer_text: $('#footer_text').val(),
            header_image_path: $('#header-preview img').length > 0 ? $('#header-preview img').attr('src') : null,
            footer_image_path: $('#footer-preview img').length > 0 ? $('#footer-preview img').attr('src') : null
        };
        
        if (!templateData.template_name) {
            showErrorAlert('Please enter a template name.');
            return;
        }
        
        // Show confirmation with template preview
        const confirmMsg = `Save template "${templateData.template_name}"?\n\nThis will save the current header/footer configuration for future use.`;
        if (confirm(confirmMsg)) {
            saveTemplate(templateData);
        }
    });
    
    $('#view-templates-archive-btn').click(function() {
        // Open templates and archive in same page instead of popup
        window.location.href = '{{ route("letters.index") }}';
    });
    
    // Letter Archive Management 
    function showLetterArchive() {
        // Open letter archive in same page instead of popup
        window.location.href = '{{ route("letters.index") }}';
    }
    
    // Save template via AJAX
    function saveTemplate(templateData) {
        $.ajax({
            url: '{{ route("profile.templates.save") }}',
            method: 'POST',
            data: {
                ...templateData,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#save-template-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    showSuccessAlert('Template saved successfully! The page will refresh to show the current template.');
                    $('#template_name').val('');
                    $('#template_description').val('');
                    // Refresh page after short delay to show updated template
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showErrorAlert('Failed to save template: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to save template.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showErrorAlert(errorMessage);
            },
            complete: function() {
                $('#save-template-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Template');
            }
        });
    }
    
    // Load templates and show modal
    function loadTemplatesModal() {
        $.ajax({
            url: '{{ route("profile.templates.load") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showTemplatesModal(response.templates);
                } else {
                    showErrorAlert('Failed to load templates: ' + response.message);
                }
            },
            error: function(xhr) {
                showErrorAlert('Failed to load templates.');
            }
        });
    }
    
    // Show templates modal
    function showTemplatesModal(templates) {
        let templatesHtml = '';
        
        if (templates.length === 0) {
            templatesHtml = '<p class="text-muted">No templates found. Save your first template to get started!</p>';
        } else {
            templatesHtml = '<div class="row">';
            templates.forEach(function(template) {
                templatesHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">${template.template_name}</h6>
                                <p class="card-text text-muted small">${template.template_description || 'No description'}</p>
                                <p class="small text-info">
                                    Created by: ${template.creator ? template.creator.name : 'Unknown'}<br>
                                    Used: ${template.usage_count} times
                                </p>
                                <button class="btn btn-primary btn-sm" onclick="loadSingleTemplate(${template.id})">
                                    <i class="fas fa-download"></i> Load Template
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            templatesHtml += '</div>';
        }
        
        const modalHtml = `
            <div class="modal fade" id="templatesModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-folder-open"></i> Letter Templates</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${templatesHtml}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal
        $('#templatesModal').remove();
        
        // Add new modal
        $('body').append(modalHtml);
        
        // Show modal
        $('#templatesModal').modal('show');
    }
    
    // Load single template
    function loadSingleTemplate(templateId) {
        $.ajax({
            url: '{{ route("profile.templates.load-single") }}',
            method: 'POST',
            data: {
                template_id: templateId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    const template = response.template;
                    
                    // Fill in header and footer text
                    $('#header_text').val(template.header_text || '');
                    $('#footer_text').val(template.footer_text || '');
                    
                    // Handle header image
                    if (template.header_image_path) {
                        $('#header-preview').html(`<img src="${template.header_image_path}" alt="Header Image" style="max-width: 20%; height: auto; max-height: 80px; border: 2px solid #ddd; border-radius: 8px;">`);
                    }
                    
                    // Handle footer image
                    if (template.footer_image_path) {
                        $('#footer-preview').html(`<img src="${template.footer_image_path}" alt="Footer Image" style="max-width: 20%; height: auto; max-height: 60px; border: 2px solid #ddd; border-radius: 8px;">`);
                    }
                    
                    // Close modal
                    $('#templatesModal').modal('hide');
                    
                    showSuccessAlert(`Template "${template.template_name}" loaded successfully!`);
                } else {
                    showErrorAlert('Failed to load template: ' + response.message);
                }
            },
            error: function(xhr) {
                showErrorAlert('Failed to load template.');
            }
        });
    }
    
    // Check for selected template from localStorage (from letters archive page)
    function checkForSelectedTemplate() {
        const selectedTemplate = localStorage.getItem('selectedTemplate');
        if (selectedTemplate) {
            try {
                const templateData = JSON.parse(selectedTemplate);
                
                // Populate template fields
                $('#template_name').val(templateData.name || '');
                $('#template_description').val(templateData.description || '');
                $('#header_text').val(templateData.headerText || '');
                $('#footer_text').val(templateData.footerText || '');
                
                // Handle header image
                if (templateData.headerImage) {
                    $('#header-preview').html(`<img src="${templateData.headerImage}" alt="Header Image" style="max-width: 20%; height: auto; max-height: 80px; border: 2px solid #ddd; border-radius: 8px;">`);
                }
                
                // Handle footer image
                if (templateData.footerImage) {
                    $('#footer-preview').html(`<img src="${templateData.footerImage}" alt="Footer Image" style="max-width: 20%; height: auto; max-height: 60px; border: 2px solid #ddd; border-radius: 8px;">`);
                }
                
                // Clear localStorage
                localStorage.removeItem('selectedTemplate');
                
                // Show success message
                showSuccessAlert(`Template "${templateData.name}" loaded successfully! You can now modify the content and generate a new letter.`);
                
                // Scroll to letters section
                if (window.location.hash === '#letters-tab') {
                    $('html, body').animate({
                        scrollTop: $('#letters-tab').offset().top - 100
                    }, 500);
                }
                
            } catch (e) {
                console.error('Error parsing selected template:', e);
                localStorage.removeItem('selectedTemplate');
            }
        }
    }
    
    // Load letter archive
    function loadLetterArchive() {
        $.ajax({
            url: '{{ route("profile.letters.archive") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showLetterArchiveModal(response.letters);
                } else {
                    showErrorAlert('Failed to load letter archive: ' + response.message);
                }
            },
            error: function(xhr) {
                showErrorAlert('Failed to load letter archive.');
            }
        });
    }
    
    // Show letter archive modal
    function showLetterArchiveModal(letters) {
        let archiveHtml = '';
        
        if (letters.data && letters.data.length === 0) {
            archiveHtml = '<p class="text-muted">No letters found in archive.</p>';
        } else {
            archiveHtml = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Recipient</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            (letters.data || letters).forEach(function(letter) {
                const letterData = letter.letter_data || {};
                archiveHtml += `
                    <tr>
                        <td><code>${letter.letter_reference}</code></td>
                        <td>${new Date(letter.letter_date).toLocaleDateString()}</td>
                        <td>${letter.letter_subject}</td>
                        <td>${letterData.recipient_name || 'Unknown'}</td>
                        <td>
                            <a href="${letter.pdf_url || '#'}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            archiveHtml += '</tbody></table></div>';
        }
        
        const modalHtml = `
            <div class="modal fade" id="archiveModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-archive"></i> Letter Archive</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${archiveHtml}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal
        $('#archiveModal').remove();
        
        // Add new modal
        $('body').append(modalHtml);
        
        // Show modal
        $('#archiveModal').modal('show');
    }
    
    // Helper function to show info alerts
    function showInfoAlert(message) {
        const alertHtml = `
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the form
        $('.profile-form').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert-info').alert('close');
        }, 5000);
    }

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
});
</script>
@endsection