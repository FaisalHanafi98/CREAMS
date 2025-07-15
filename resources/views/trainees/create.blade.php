@extends('layouts.app')

@section('title', 'Register New Trainee - CREAMS')

@section('styles')
<style>
    .registration-header {
        background: linear-gradient(135deg, #007bff, #6c757d);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 10px;
    }
    
    .registration-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 300;
    }
    
    .registration-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: rgba(255,255,255,0.7);
    }
    
    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .breadcrumb-item.active {
        color: white;
    }
    
    .container-fluid {
        background: #f0f2f5;
        min-height: 100vh;
        padding: 20px;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        background: white;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 12px 12px 0 0 !important;
        padding: 18px 25px;
        border: none;
    }
    
    .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: white !important;
        font-size: 16px;
    }
    
    .card-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 14px;
        display: block;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 18px;
        transition: all 0.3s ease;
        font-size: 14px;
        background: #f8f9fa;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(50, 189, 234, 0.15);
        background: white;
        outline: none;
    }
    
    .form-control:hover {
        border-color: #c3d4e6;
        background: white;
    }
    
    select.form-control {
        cursor: pointer;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 45px !important;
        color: #333 !important;
        font-weight: 500;
        background-color: #fff !important;
        height: 50px !important;
        line-height: 1.4 !important;
        font-size: 14px !important;
    }
    
    select.form-control:focus {
        color: #333 !important;
        background-color: #fff !important;
    }
    
    select.form-control option {
        padding: 10px 15px !important;
        color: #333 !important;
        background: #fff !important;
        font-size: 14px !important;
        font-weight: normal !important;
    }
    
    /* Specific fixes for Chrome/Safari dropdown visibility */
    select.form-control:invalid {
        color: #999;
    }
    
    select.form-control:valid {
        color: #333 !important;
    }
    
    /* Specific fixes for problematic dropdowns */
    #trainee_condition,
    #guardian_relationship,
    #centre_name {
        color: #333 !important;
        background-color: #fff !important;
        font-size: 14px !important;
        padding: 15px 45px 15px 18px !important;
        height: 50px !important;
        line-height: 1.4 !important;
        border: 2px solid #e9ecef !important;
        text-indent: 0px !important;
        text-overflow: clip !important;
        white-space: nowrap !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    #trainee_condition:focus,
    #guardian_relationship:focus,
    #centre_name:focus {
        color: #333 !important;
        background-color: #fff !important;
        border-color: var(--primary-color) !important;
    }
    
    /* Force text visibility in all select options */
    select option {
        color: #333 !important;
        background-color: #fff !important;
        font-size: 14px !important;
        padding: 8px 12px !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* Override any Bootstrap or other CSS that might hide text */
    .form-control.is-invalid,
    .form-control.is-valid,
    .was-validated .form-control:invalid,
    .was-validated .form-control:valid {
        color: #333 !important;
        background-color: #fff !important;
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    .btn {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
        text-transform: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(50, 189, 234, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(50, 189, 234, 0.4);
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }
    
    .text-danger {
        color: #dc3545 !important;
        font-weight: 500;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
        background: #fff5f5;
    }
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 13px;
        margin-top: 8px;
        font-weight: 500;
        display: block;
    }
    
    .custom-file {
        margin-bottom: 15px;
    }
    
    .custom-file-input:focus ~ .custom-file-label {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(50, 189, 234, 0.15);
    }
    
    .custom-file-label {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 18px;
        cursor: pointer;
        background: #f8f9fa;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .custom-file-label:hover {
        border-color: var(--primary-color);
        background: white;
    }
    
    .custom-file-label::after {
        background: var(--primary-color);
        color: white;
        border-radius: 8px;
        border: none;
        padding: 8px 15px;
        font-weight: 600;
    }
    
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 30px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-radius: 10px 10px 0 0;
        background: #f8f9fa;
        color: #6c757d;
        padding: 15px 20px;
        margin-right: 5px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        background: #e9ecef;
        color: var(--primary-color);
    }
    
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
    }
    
    .progress {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
        margin-bottom: 30px;
    }
    
    .progress-bar {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 10px;
        transition: width 0.6s ease;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        background: white;
        color: #495057;
        text-decoration: none;
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .action-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(50, 189, 234, 0.3);
    }
    
    .action-btn i {
        margin-right: 8px;
    }
    
    .alert {
        border: none;
        border-radius: 10px;
        padding: 18px 25px;
        margin-bottom: 25px;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
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
    
    #avatar-preview img {
        border: 4px solid #e9ecef;
        transition: all 0.3s ease;
        border-radius: 10px;
    }
    
    #avatar-preview img:hover {
        border-color: var(--primary-color);
        transform: scale(1.05);
    }
    
    .next-tab, .prev-tab {
        min-width: 150px;
    }
    
    /* Checkbox styling */
    .form-check {
        margin-bottom: 20px;
        padding-left: 0;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .form-check-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
        margin-top: 2px;
        flex-shrink: 0;
    }
    
    .form-check-label {
        font-weight: 500;
        color: #495057;
        cursor: pointer;
        font-size: 14px;
        margin-bottom: 0;
        line-height: 1.5;
    }
    
    .form-check-input.is-invalid {
        border-color: #dc3545;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 15px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn {
            padding: 10px 20px;
        }
        
        .form-control {
            padding: 12px 15px;
        }
        
        .nav-tabs .nav-link {
            padding: 12px 15px;
            font-size: 13px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Registration Header -->
    <div class="registration-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-user-plus mr-3"></i>Register New Trainee</h1>
                    <p>Add a new trainee to the CREAMS rehabilitation program</p>
                </div>
                <div class="col-md-4 text-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.index') }}">Trainees</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Register</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                @include('trainees._form', [
                    'action' => $action ?? route('trainees.store'),
                    'isEdit' => $isEdit ?? false,
                    'centres' => $centres,
                    'trainee' => $trainee ?? null
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation enhancements
    const form = document.querySelector('.trainee-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Avatar preview
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must not exceed 2MB');
                    this.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.querySelector('.avatar-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'avatar-preview mt-2';
                        avatarInput.parentNode.appendChild(preview);
                    }
                    preview.innerHTML = `
                        <img src="${e.target.result}" 
                             alt="Avatar Preview" 
                             class="img-thumbnail" 
                             style="max-width: 100px; max-height: 100px;">
                        <small class="text-muted d-block">Preview</small>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form submission loading state
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Registering...';
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);
});
</script>
@endsection
