@extends('layouts.app')

@section('title', 'Register New Trainee - CREAMS')

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

    .trainee-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        position: relative;
        overflow: hidden;
    }

    .trainee-header::before {
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

    .trainee-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .trainee-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
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
        background: var(--light-bg);
        min-height: 100vh;
        padding: 20px;
    }

    .trainee-form .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        background: white;
        transition: all 0.3s ease;
        border: 1px solid #f1f3f4;
    }

    .trainee-form .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .trainee-form .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 20px 25px;
        border: none;
    }

    .trainee-form .card-header h5 {
        margin: 0;
        font-weight: 700;
        color: white !important;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .trainee-form .card-body {
        padding: 30px;
    }

    .trainee-form .form-group label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 10px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .trainee-form .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 18px;
        transition: all 0.3s ease;
        font-size: 16px;
        background: #fafbfc;
    }

    .trainee-form .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
        background: white;
        outline: none;
    }

    .trainee-form .form-control:hover {
        border-color: var(--primary-color);
        background: white;
    }

    .trainee-form select.form-control {
        cursor: pointer;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23c850c0' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 45px !important;
        color: #333 !important;
        font-weight: 500;
        background-color: #fafbfc !important;
        height: 52px !important;
        line-height: 1.4 !important;
        font-size: 16px !important;
    }

    .trainee-form select.form-control:focus {
        color: #333 !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23c850c0' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    .trainee-form textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .trainee-form .text-danger {
        color: var(--danger-color) !important;
        font-weight: 600;
    }

    .trainee-form .is-invalid {
        border-color: var(--danger-color) !important;
        background: #fff5f5;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .trainee-form .invalid-feedback {
        color: var(--danger-color);
        font-size: 14px;
        margin-top: 8px;
        font-weight: 500;
        display: block;
    }

    .trainee-form .form-actions {
        padding: 30px 0;
        border-top: 2px solid #f1f3f4;
        margin-top: 30px;
        text-align: center;
    }

    .btn {
        border-radius: 10px;
        padding: 15px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        font-size: 16px;
        text-transform: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(200, 80, 192, 0.4);
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
        color: white;
    }

    .btn-light {
        background: rgba(255,255,255,0.9);
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        color: var(--dark-color);
        transition: all 0.3s ease;
    }

    .btn-light:hover {
        background: white;
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: var(--dark-color);
    }

    .alert {
        border: none;
        border-radius: 15px;
        padding: 20px 25px;
        margin-bottom: 25px;
        font-weight: 500;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-left: 4px solid var(--success-color);
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border-left: 4px solid var(--danger-color);
    }

    .form-check {
        margin-bottom: 20px;
        padding-left: 0;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .form-check-input {
        width: 22px;
        height: 22px;
        cursor: pointer;
        margin-top: 2px;
        flex-shrink: 0;
        border: 2px solid var(--primary-color);
        background: white;
    }

    .form-check-input:checked {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-color: var(--primary-color);
    }

    .form-check-label {
        font-weight: 500;
        color: var(--dark-color);
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 0;
        line-height: 1.5;
    }

    .img-thumbnail {
        border: 3px solid #e9ecef;
        border-radius: 10px;
        padding: 5px;
        transition: all 0.3s ease;
    }

    .img-thumbnail:hover {
        border-color: var(--primary-color);
        transform: scale(1.05);
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding: 15px;
        }

        .trainee-header {
            text-align: center;
            padding: 1.5rem;
        }

        .trainee-header h1 {
            font-size: 2rem;
        }

        .trainee-form .card-body {
            padding: 20px;
        }

        .btn {
            padding: 12px 25px;
            font-size: 14px;
        }

        .trainee-form .form-control {
            padding: 12px 15px;
            font-size: 14px;
        }

        .trainee-form .form-actions {
            text-align: center;
        }

        .trainee-form .form-actions .btn {
            width: 100%;
            margin-bottom: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Registration Header -->
    <div class="trainee-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-user-plus mr-3"></i>Register New Trainee</h1>
                    <p>Add a new trainee to the CREAMS rehabilitation program with comprehensive information collection</p>
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
                    'centres' => $centres ?? [],
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
