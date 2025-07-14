@extends('layouts.app')

@section('title', 'User Registration - CREAMS')

@section('styles')
<style>
    /* Registration form styles */
    .registration-page {
        padding: 20px 0;
    }
    
    .register-container {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .registration-form-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 30px;
        text-align: center;
    }
    
    .registration-form-header h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
    }
    
    .registration-form-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
    }
    
    .form-content {
        padding: 40px;
    }
    
    .tab-nav {
        display: flex;
        margin-bottom: 40px;
        border-bottom: 2px solid #f0f0f0;
        overflow-x: auto;
    }
    
    .tab-btn {
        flex: 1;
        padding: 15px 20px;
        border: none;
        background: none;
        color: #666;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        white-space: nowrap;
        min-width: 160px;
    }
    
    .tab-btn.active {
        color: var(--primary-color);
    }
    
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 3px 3px 0 0;
    }
    
    .tab-btn:hover:not(.active) {
        color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
    }
    
    .form-sections-container {
        position: relative;
        min-height: 400px;
    }
    
    .form-section {
        display: none;
        animation: fadeInUp 0.3s ease;
    }
    
    .form-section.active {
        display: block;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .form-grid-full {
        grid-column: 1 / -1;
    }
    
    .form-group {
        position: relative;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: #fff;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
        outline: none;
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    
    .password-field {
        position: relative;
    }
    
    .toggle-password {
        position: absolute;
        right: 12px;
        top: 38px;
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
    }
    
    .form-help {
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }
    
    .form-error {
        margin-top: 5px;
        font-size: 12px;
        color: #dc3545;
    }
    
    .role-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .role-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        position: relative;
    }
    
    .role-option:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(50, 189, 234, 0.1);
    }
    
    .role-option.selected {
        border-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
    }
    
    .role-radio {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    .role-icon {
        font-size: 28px;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .role-name {
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    
    .form-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
        gap: 15px;
    }
    
    .btn-prev, .btn-next, .btn-submit {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-prev {
        background: #6c757d;
        color: white;
    }
    
    .btn-prev:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }
    
    .btn-next, .btn-submit {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }
    
    .btn-next:hover, .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(50, 189, 234, 0.3);
    }
    
    .review-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .review-item {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .review-item:last-child {
        border-bottom: none;
    }
    
    .review-label {
        font-weight: 500;
        color: #333;
        width: 150px;
        flex-shrink: 0;
    }
    
    .review-value {
        color: #666;
        flex-grow: 1;
    }
    
    .terms-container {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .form-check {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    
    .form-check-input {
        margin-top: 3px;
    }
    
    .form-check-label {
        font-size: 14px;
        line-height: 1.5;
    }
    
    .form-check-label a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .form-check-label a:hover {
        text-decoration: underline;
    }
    
    .login-link {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
        color: #666;
    }
    
    .login-link a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }
    
    .login-link a:hover {
        text-decoration: underline;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .form-content {
            padding: 20px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .role-options {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .tab-nav {
            flex-direction: column;
        }
        
        .tab-btn {
            min-width: auto;
            text-align: center;
        }
        
        .review-item {
            flex-direction: column;
            gap: 5px;
        }
        
        .review-label {
            width: auto;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid registration-page">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">User Registration</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('teachershome') }}">Staff Management</a>
                    <span class="separator">/</span>
                    <span class="current">Registration</span>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('teachershome') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Staff Directory
                </a>
            </div>
        </div>
    </div>

    <div class="register-container">
        <div class="registration-form-header">
            <h2>New User Registration</h2>
            <p>Create a new staff account for the CREAMS system</p>
        </div>
        
        <div class="form-content">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @if (session('fail'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> {{ session('fail') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
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
            
            <div class="tab-nav">
                <button class="tab-btn active" id="tab-1">Account Details</button>
                <button class="tab-btn" id="tab-2">Profile Information</button>
                <button class="tab-btn" id="tab-3">Review & Submit</button>
            </div>
            
            <form action="{{ route('auth.save') }}" method="POST" id="registration-form" onsubmit="console.log('Form submitting with center_id:', document.getElementById('center_id').value); return true;">
                @csrf
                
                <div class="form-sections-container">
                    <!-- Section 1: Account Details -->
                    <div class="form-section active" id="section-1">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="email">Email Address*</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="iium_id">IIUM ID*</label>
                                <input type="text" class="form-control @error('iium_id') is-invalid @enderror" id="iium_id" name="iium_id" value="{{ old('iium_id') }}" required maxlength="8">
                                <div class="form-help">Format: 4 letters followed by 4 digits (e.g., ABCD1234)</div>
                                @error('iium_id')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group password-field">
                                <label for="password">Password*</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                                <div class="form-help">Must be at least 5 characters with at least one letter and one number</div>
                                @error('password')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group password-field">
                                <label for="password_confirmation">Confirm Password*</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required>
                                <button type="button" class="toggle-password" id="togglePasswordConfirmation">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <div></div> <!-- Empty div for spacing -->
                            <button type="button" class="btn-next" id="to-section-2">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section 2: Profile Information -->
                    <div class="form-section" id="section-2">
                        <div class="form-grid">
                            <div class="form-group form-grid-full">
                                <label for="name">Full Name*</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group form-grid-full">
                                <label>Select User Role*</label>
                                <div class="role-options">
                                    <label class="role-option {{ old('role') === 'admin' ? 'selected' : '' }}" id="role-admin">
                                        <input type="radio" name="role" value="admin" class="role-radio" {{ old('role') === 'admin' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-user-cog"></i>
                                        </div>
                                        <div class="role-name">Admin</div>
                                    </label>
                                    
                                    <label class="role-option {{ old('role') === 'supervisor' ? 'selected' : '' }}" id="role-supervisor">
                                        <input type="radio" name="role" value="supervisor" class="role-radio" {{ old('role') === 'supervisor' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="role-name">Supervisor</div>
                                    </label>
                                    
                                    <label class="role-option {{ old('role') === 'teacher' ? 'selected' : '' }}" id="role-teacher">
                                        <input type="radio" name="role" value="teacher" class="role-radio" {{ old('role') === 'teacher' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div class="role-name">Teacher</div>
                                    </label>
                                    
                                    <label class="role-option {{ old('role') === 'ajk' ? 'selected' : '' }}" id="role-ajk">
                                        <input type="radio" name="role" value="ajk" class="role-radio" {{ old('role') === 'ajk' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-users-cog"></i>
                                        </div>
                                        <div class="role-name">AJK</div>
                                    </label>
                                </div>
                                @error('role')
                                    <div class="form-error mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Centre Location dropdown -->
                        <div class="form-group form-grid-full">
                            <label for="center_location">Centre Location*</label>
                            <select class="form-control @error('center_location') is-invalid @enderror" id="center_location" name="center_location" required>
                                <option value="">-- Select a Centre --</option>
                                <option value="Gombak" {{ old('center_location') === 'Gombak' ? 'selected' : '' }}>Gombak</option>
                                <option value="Kuantan" {{ old('center_location') === 'Kuantan' ? 'selected' : '' }}>Kuantan</option>
                                <option value="Pagoh" {{ old('center_location') === 'Pagoh' ? 'selected' : '' }}>Pagoh</option>
                            </select>
                            <div class="form-help">Select the campus centre location</div>
                            @error('center_location')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Hidden Center ID field that gets populated based on selection -->
                        <input type="hidden" id="center_id" name="center_id" value="{{ old('center_id') }}">
                        @error('center_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        
                        <div class="form-buttons">
                            <button type="button" class="btn-prev" id="to-section-1-from-2">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn-next" id="to-section-3">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section 3: Review & Submit -->
                    <div class="form-section" id="section-3">
                        <h3 class="mb-4">Review Information</h3>
                        
                        <div class="review-section">
                            <div class="review-item">
                                <div class="review-label">Email Address:</div>
                                <div class="review-value" id="review-email"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">IIUM ID:</div>
                                <div class="review-value" id="review-iium-id"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">Full Name:</div>
                                <div class="review-value" id="review-name"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">Selected Role:</div>
                                <div class="review-value" id="review-role"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">Centre:</div>
                                <div class="review-value" id="review-center">Not selected</div>
                            </div>
                        </div>
                        
                        <div class="terms-container">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I confirm that the information provided is accurate and that this user account is being created for official CREAMS system access.
                                </label>
                                @error('terms')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="button" class="btn-prev" id="to-section-2-from-3">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="submit" class="btn-submit" id="submit-button">
                                <i class="fas fa-user-plus"></i> Create User Account
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Tab navigation
    $('.tab-btn').click(function() {
        const tabId = $(this).attr('id').split('-')[1];
        switchToSection(tabId);
    });
    
    // Form navigation buttons
    $('#to-section-2').click(function() {
        if (validateSection1()) {
            switchToSection('2');
        }
    });
    
    $('#to-section-3').click(function() {
        if (validateSection2()) {
            updateReviewSection();
            switchToSection('3');
        }
    });
    
    $('#to-section-1-from-2').click(function() {
        switchToSection('1');
    });
    
    $('#to-section-2-from-3').click(function() {
        switchToSection('2');
    });
    
    // Password toggle functionality
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const type = passwordField.attr('type');
        const icon = $(this).find('i');
        
        if (type === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    
    $('#togglePasswordConfirmation').click(function() {
        const passwordField = $('#password_confirmation');
        const type = passwordField.attr('type');
        const icon = $(this).find('i');
        
        if (type === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    
    // Role selection
    $('.role-option').click(function() {
        $('.role-option').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('.role-radio').prop('checked', true);
    });
    
    // Centre location mapping
    const centerMapping = {
        'Gombak': '01',
        'Kuantan': '02',
        'Pagoh': '03'
    };
    
    $('#center_location').change(function() {
        const location = $(this).val();
        const centerId = centerMapping[location] || '';
        $('#center_id').val(centerId);
    });
    
    // Functions
    function switchToSection(sectionNumber) {
        // Update tabs
        $('.tab-btn').removeClass('active');
        $('#tab-' + sectionNumber).addClass('active');
        
        // Update sections
        $('.form-section').removeClass('active');
        $('#section-' + sectionNumber).addClass('active');
    }
    
    function validateSection1() {
        let isValid = true;
        const email = $('#email').val();
        const iiumId = $('#iium_id').val();
        const password = $('#password').val();
        const passwordConfirm = $('#password_confirmation').val();
        
        // Basic validation
        if (!email || !iiumId || !password || !passwordConfirm) {
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Password match validation
        if (password !== passwordConfirm) {
            alert('Passwords do not match.');
            return false;
        }
        
        // IIUM ID format validation
        const iiumIdPattern = /^[A-Za-z]{4}[0-9]{4}$/;
        if (!iiumIdPattern.test(iiumId)) {
            alert('IIUM ID must be 4 letters followed by 4 digits.');
            return false;
        }
        
        return isValid;
    }
    
    function validateSection2() {
        const name = $('#name').val();
        const role = $('input[name="role"]:checked').val();
        const centerLocation = $('#center_location').val();
        
        if (!name || !role || !centerLocation) {
            alert('Please fill in all required fields.');
            return false;
        }
        
        return true;
    }
    
    function updateReviewSection() {
        $('#review-email').text($('#email').val());
        $('#review-iium-id').text($('#iium_id').val());
        $('#review-name').text($('#name').val());
        $('#review-role').text($('input[name="role"]:checked').val().charAt(0).toUpperCase() + $('input[name="role"]:checked').val().slice(1));
        $('#review-center').text($('#center_location').val() || 'Not selected');
    }
    
    // Set initial center_id if center_location is already selected
    if ($('#center_location').val()) {
        const location = $('#center_location').val();
        const centerId = centerMapping[location] || '';
        $('#center_id').val(centerId);
    }
});
</script>
@endsection