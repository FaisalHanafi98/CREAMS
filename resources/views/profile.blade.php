@extends('layouts.app')

@section('title', 'My Profile - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .profile-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(50px, -50px);
    }

    .profile-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .profile-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .container-fluid {
        background: var(--light-bg);
        min-height: 100vh;
        padding: 20px;
    }

    .profile-form .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        background: white;
        transition: all 0.3s ease;
        border: 1px solid #f1f3f4;
    }

    .profile-form .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .profile-form .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 20px 25px;
        border: none;
    }

    .profile-form .card-header h5 {
        margin: 0;
        font-weight: 700;
        color: white !important;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-form .card-body {
        padding: 30px;
    }

    .profile-form .form-group label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 10px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-form .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 18px;
        transition: all 0.3s ease;
        font-size: 16px;
        background: #fafbfc;
    }

    .profile-form .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
        background: white;
        outline: none;
    }

    .profile-form .form-control:disabled {
        background: #f1f3f4;
        color: #6c757d;
        border-color: #e9ecef;
        cursor: not-allowed;
    }

    .profile-form .form-control:hover:not(:disabled) {
        border-color: var(--primary-color);
        background: white;
    }

    .profile-form select.form-control {
        cursor: pointer;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23c850c0' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 45px !important;
    }

    .profile-form textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .btn {
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(200, 80, 192, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .edit-mode-controls {
        display: none;
    }

    .edit-mode .edit-mode-controls {
        display: block;
    }

    .edit-mode .view-mode-controls {
        display: none;
    }

    .password-strength {
        height: 5px;
        border-radius: 3px;
        margin-top: 8px;
        background: #e9ecef;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .strength-weak .password-strength-bar { background: var(--danger-color); width: 25%; }
    .strength-medium .password-strength-bar { background: var(--warning-color); width: 50%; }
    .strength-good .password-strength-bar { background: #17a2b8; width: 75%; }
    .strength-strong .password-strength-bar { background: var(--success-color); width: 100%; }

    .strength-text {
        font-size: 12px;
        margin-top: 5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .file-upload-area {
        border: 2px dashed #ced4da;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        background: #fafbfc;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(200, 80, 192, 0.05);
    }

    .avatar-section {
        text-align: center;
        margin-bottom: 30px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(200, 80, 192, 0.3);
        margin: 0 auto 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        position: relative;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
        color: white;
        height: 100%;
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }

    @media (max-width: 768px) {
        .profile-header {
            text-align: center;
            padding: 1.5rem;
        }

        .profile-header h1 {
            font-size: 2rem;
        }

        .profile-form .card-body {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>
                    <i class="fas fa-user-circle me-3"></i>My Profile
                </h1>
                <p>Manage your personal information and account settings</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Message -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Form -->
    <div class="profile-form" x-data="profileManager()">
        <!-- Personal Information Card -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user me-2"></i>Personal Information</h5>
            </div>
            <div class="card-body">
                <!-- Avatar Section -->
                <div class="avatar-section">
                    <div class="profile-avatar">
                        @if(session('avatar'))
                            <img src="{{ asset('storage/avatars/' . session('avatar')) }}" alt="Profile Picture">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr(session('name', 'U'), 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h4>{{ session('name', 'User') }}</h4>
                    <span class="badge" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 8px 15px; border-radius: 20px;">
                        {{ ucfirst(session('role', 'user')) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" :class="editing ? 'edit-mode' : ''">
                    @csrf
                    @method('PUT')

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="view-mode-controls">
                            <button type="button" class="btn btn-primary" @click="editing = true">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>
                        <div class="edit-mode-controls">
                            <button type="button" class="btn btn-secondary me-2" @click="editing = false">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" class="form-control" name="name" 
                                       value="{{ session('name', '') }}" 
                                       disabled required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="{{ session('email', '') }}" 
                                       disabled required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" class="form-control" name="phone" 
                                       value="{{ session('phone', '') }}" 
                                       disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>IIUM ID</label>
                                <input type="text" class="form-control" 
                                       value="{{ session('iium_id', '') }}" disabled>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Bio</label>
                                <textarea class="form-control" name="bio" rows="3" 
                                          disabled placeholder="Tell us about yourself...">{{ session('bio', '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group edit-mode-controls">
                        <label>Profile Picture</label>
                        <div class="file-upload-area" onclick="document.getElementById('avatar').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--primary-color);"></i>
                            <p class="mb-0"><strong>Click to upload or drag and drop</strong></p>
                            <small class="text-muted">PNG, JPG up to 2MB</small>
                            <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                        </div>
                    </div>

                    <div class="edit-mode-controls">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary" @click="editing = false">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Card -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt me-2"></i>Security Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}" x-data="passwordManager()">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" class="form-control" name="current_password" 
                                       x-model="currentPassword" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" class="form-control" name="password" 
                                       x-model="newPassword" @input="checkStrength()" required>
                                <div class="password-strength" :class="strengthClass">
                                    <div class="password-strength-bar"></div>
                                </div>
                                <div class="strength-text" :class="strengthTextClass" x-text="strengthText"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" class="form-control" name="password_confirmation" 
                                       x-model="confirmPassword" required>
                                <small x-show="newPassword && confirmPassword && newPassword !== confirmPassword" 
                                       class="text-danger">Passwords do not match</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password Requirements</label>
                                <ul class="list-unstyled small mt-2">
                                    <li><i class="fas fa-check text-success me-2"></i>At least 8 characters</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Include uppercase letter</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Include lowercase letter</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Include number</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Include special character</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success" :disabled="!isValidPassword()">
                        <i class="fas fa-key me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Last Login</label>
                            <input type="text" class="form-control" value="{{ session('login_time', 'Never') }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" value="{{ ucfirst(session('role', 'user')) }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Centre ID</label>
                            <input type="text" class="form-control" value="{{ session('centre_id', 'N/A') }}" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function profileManager() {
    return {
        editing: false,
        init() {
            this.$watch('editing', (value) => {
                this.toggleFormFields(value);
            });
        },
        toggleFormFields(enabled) {
            const form = this.$el;
            const inputs = form.querySelectorAll('input[name]:not([name="avatar"]), textarea[name]');
            
            inputs.forEach(input => {
                if (input.name !== 'iium_id') { // Keep IIUM ID always disabled
                    input.disabled = !enabled;
                }
            });
        }
    }
}

function passwordManager() {
    return {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
        strength: 0,
        strengthClass: 'strength-weak',
        strengthText: 'Weak',
        strengthTextClass: 'text-danger',

        checkStrength() {
            const password = this.newPassword;
            let score = 0;

            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            this.strength = score;

            if (score < 2) {
                this.strengthClass = 'strength-weak';
                this.strengthText = 'Weak';
                this.strengthTextClass = 'text-danger';
            } else if (score < 3) {
                this.strengthClass = 'strength-medium';
                this.strengthText = 'Medium';
                this.strengthTextClass = 'text-warning';
            } else if (score < 4) {
                this.strengthClass = 'strength-good';
                this.strengthText = 'Good';
                this.strengthTextClass = 'text-info';
            } else {
                this.strengthClass = 'strength-strong';
                this.strengthText = 'Strong';
                this.strengthTextClass = 'text-success';
            }
        },

        isValidPassword() {
            return this.currentPassword && 
                   this.newPassword && 
                   this.confirmPassword && 
                   this.newPassword === this.confirmPassword && 
                   this.strength >= 3;
        }
    }
}

// File upload handling
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('avatar');

    if (fileUploadArea && fileInput) {
        // Drag and drop handlers
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileUploadArea.classList.add('dragover');
        }

        function unhighlight(e) {
            fileUploadArea.classList.remove('dragover');
        }

        fileUploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        }
    }
});
</script>
@endpush