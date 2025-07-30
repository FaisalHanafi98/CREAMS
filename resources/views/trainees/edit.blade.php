@extends('layouts.app')

@section('title', 'Edit Trainee - ' . $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name . ' - CREAMS')

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

    .edit-mode-toggle {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: 1px solid #f1f3f4;
    }

    .edit-mode-toggle h6 {
        margin: 0 0 15px 0;
        color: var(--dark-color);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .edit-mode-toggle .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 0;
    }

    .edit-mode-toggle .form-check-input {
        width: 50px;
        height: 25px;
        border-radius: 25px;
        background: #e9ecef;
        border: none;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .edit-mode-toggle .form-check-input:checked {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .edit-mode-toggle .form-check-input::before {
        content: '';
        position: absolute;
        width: 21px;
        height: 21px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .edit-mode-toggle .form-check-input:checked::before {
        transform: translateX(25px);
    }

    .edit-mode-toggle .form-check-label {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 16px;
        margin-bottom: 0;
        cursor: pointer;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        background: white;
        transition: all 0.3s ease;
        border: 1px solid #f1f3f4;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 20px 25px;
        border: none;
    }

    .card-header h6 {
        margin: 0;
        font-weight: 700;
        color: white !important;
        font-size: 18px;
    }

    .card-body {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 10px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 18px;
        transition: all 0.3s ease;
        font-size: 16px;
        background: #fafbfc;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
        background: white;
        outline: none;
    }

    .form-control:hover {
        border-color: var(--primary-color);
        background: white;
    }

    .form-control:disabled {
        background: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }

    select.form-control {
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

    select.form-control:focus {
        color: #333 !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23c850c0' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    select.form-control:disabled {
        cursor: not-allowed;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
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

    .btn-danger {
        background: var(--danger-color);
        color: white;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
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

    .btn-block {
        width: 100%;
        margin-bottom: 15px;
    }

    .text-danger {
        color: var(--danger-color) !important;
        font-weight: 600;
    }

    .is-invalid {
        border-color: var(--danger-color) !important;
        background: #fff5f5;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .invalid-feedback {
        color: var(--danger-color);
        font-size: 14px;
        margin-top: 8px;
        font-weight: 500;
        display: block;
    }

    .custom-file {
        margin-bottom: 15px;
    }

    .custom-file-input:focus ~ .custom-file-label {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
    }

    .custom-file-label {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 18px;
        cursor: pointer;
        background: #fafbfc;
        transition: all 0.3s ease;
        font-size: 16px;
    }

    .custom-file-label:hover {
        border-color: var(--primary-color);
        background: white;
    }

    .custom-file-label::after {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 8px;
        border: none;
        padding: 8px 15px;
        font-weight: 600;
    }

    #avatar-preview {
        border: 4px solid #e9ecef;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    #avatar-preview:hover {
        border-color: var(--primary-color);
        box-shadow: 0 6px 20px rgba(200, 80, 192, 0.3);
        transform: scale(1.05);
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

    hr {
        border: none;
        height: 2px;
        background: linear-gradient(to right, transparent, var(--border-color), transparent);
        margin: 35px 0;
    }

    h5 {
        color: var(--dark-color);
        font-weight: 700;
        margin-bottom: 25px;
        font-size: 18px;
        border-bottom: 3px solid var(--primary-color);
        padding-bottom: 10px;
        display: inline-block;
    }

    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
        padding: 20px 25px;
    }

    .modal-title {
        font-weight: 600;
        color: white;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
        text-shadow: none;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #e9ecef;
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

        .card-body {
            padding: 20px;
        }

        .btn {
            padding: 12px 25px;
            font-size: 14px;
        }

        .form-control {
            padding: 12px 15px;
            font-size: 14px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Edit Header -->
    <div class="trainee-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-user-edit mr-3"></i>Edit Trainee Information</h1>
                    <p>Update trainee details and personal information for {{ $trainee->trainee_first_name ?? 'Trainee' }} {{ $trainee->trainee_last_name ?? '' }}</p>
                </div>
                <div class="col-md-4 text-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.home') }}">Trainee</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.show', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}">{{ $trainee->trainee_first_name ?? 'Trainee' }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Edit Mode Toggle -->
        <div class="edit-mode-toggle">
            <h6>
                <i class="fas fa-edit"></i>Edit Mode Control
            </h6>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="editModeToggle" checked>
                <label class="form-check-label" for="editModeToggle">
                    Enable editing for all fields
                </label>
            </div>
            <small class="text-muted">Toggle off to view data in read-only mode, toggle on to make changes</small>
        </div>
    
    <!-- Alert Message -->
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> Please check the form for errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Edit Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('updatetraineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trainee_first_name">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('trainee_first_name') is-invalid @enderror" 
                                           id="trainee_first_name" name="trainee_first_name" 
                                           value="{{ old('trainee_first_name', $trainee->trainee_first_name) }}" required>
                                    @error('trainee_first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trainee_last_name">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('trainee_last_name') is-invalid @enderror" 
                                           id="trainee_last_name" name="trainee_last_name" 
                                           value="{{ old('trainee_last_name', $trainee->trainee_last_name) }}" required>
                                    @error('trainee_last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trainee_email">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('trainee_email') is-invalid @enderror" 
                                           id="trainee_email" name="trainee_email" 
                                           value="{{ old('trainee_email', $trainee->trainee_email) }}" required>
                                    @error('trainee_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trainee_phone_number">Phone Number</label>
                                    <input type="text" class="form-control @error('trainee_phone_number') is-invalid @enderror" 
                                           id="trainee_phone_number" name="trainee_phone_number" 
                                           value="{{ old('trainee_phone_number', $trainee->trainee_phone_number) }}">
                                    @error('trainee_phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trainee_date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('trainee_date_of_birth') is-invalid @enderror" 
                                           id="trainee_date_of_birth" name="trainee_date_of_birth" 
                                           value="{{ old('trainee_date_of_birth', $trainee->trainee_date_of_birth ? $trainee->trainee_date_of_birth->format('Y-m-d') : '') }}" required>
                                    @error('trainee_date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select class="form-control @error('gender') is-invalid @enderror" 
                                            id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $trainee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $trainee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trainee_condition">Condition <span class="text-danger">*</span></label>
                                    <select class="form-control @error('trainee_condition') is-invalid @enderror" 
                                            id="trainee_condition" name="trainee_condition" required>
                                        <option value="">Select Condition</option>
                                        @foreach(config('trainee.conditions') as $condition)
                                            <option value="{{ $condition }}" 
                                                {{ old('trainee_condition', $trainee->trainee_condition) == $condition ? 'selected' : '' }}>
                                                {{ $condition }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('trainee_condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="centre_name">Centre <span class="text-danger">*</span></label>
                            <select class="form-control @error('centre_name') is-invalid @enderror" id="centre_name" name="centre_name" required>
                                <option value="">Select Centre</option>
                                @if(isset($centres))
                                    @foreach($centres as $centre)
                                        <option value="{{ $centre->centre_name }}" 
                                            {{ old('centre_name', $trainee->centre_name) == $centre->centre_name ? 'selected' : '' }}>
                                            {{ $centre->centre_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('centre_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="trainee_address">Address</label>
                            <textarea class="form-control @error('trainee_address') is-invalid @enderror" 
                                      id="trainee_address" name="trainee_address" rows="3"
                                      placeholder="Enter complete address including postal code">{{ old('trainee_address', $trainee->trainee_address ?? '') }}</textarea>
                            @error('trainee_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Guardian Information -->
                        <hr class="my-4">
                        <h5 class="mb-3">Guardian Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="guardian_name">Guardian's Name</label>
                                    <input type="text" class="form-control @error('guardian_name') is-invalid @enderror" 
                                           id="guardian_name" name="guardian_name" 
                                           value="{{ old('guardian_name', $trainee->guardian_name ?? '') }}">
                                    @error('guardian_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="guardian_relationship">Relationship</label>
                                    <select class="form-control @error('guardian_relationship') is-invalid @enderror" 
                                            id="guardian_relationship" name="guardian_relationship">
                                        <option value="">Select Relationship</option>
                                        @foreach(config('trainee.guardian_relationships') as $relationship)
                                            <option value="{{ $relationship }}" 
                                                {{ old('guardian_relationship', $trainee->guardian_relationship ?? '') == $relationship ? 'selected' : '' }}>
                                                {{ $relationship }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guardian_relationship')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="guardian_phone">Guardian's Phone</label>
                                    <input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" 
                                           id="guardian_phone" name="guardian_phone" 
                                           value="{{ old('guardian_phone', $trainee->guardian_phone ?? '') }}">
                                    @error('guardian_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="guardian_email">Guardian's Email</label>
                                    <input type="email" class="form-control @error('guardian_email') is-invalid @enderror" 
                                           id="guardian_email" name="guardian_email" 
                                           value="{{ old('guardian_email', $trainee->guardian_email ?? '') }}">
                                    @error('guardian_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="guardian_address">Guardian's Address</label>
                            <textarea class="form-control @error('guardian_address') is-invalid @enderror" 
                                  id="guardian_address" name="guardian_address" rows="3">{{ old('guardian_address', $trainee->guardian_address ?? '') }}</textarea>
                            @error('guardian_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Additional Information -->
                        <hr class="my-4">
                        <h5 class="mb-3">Additional Information</h5>
                        <div class="form-group">
                            <label for="medical_history">Medical History</label>
                            <textarea class="form-control @error('medical_history') is-invalid @enderror" 
                                  id="medical_history" name="medical_history" rows="4">{{ old('medical_history', $trainee->medical_history ?? '') }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="additional_notes">Additional Notes</label>
                            <textarea class="form-control @error('additional_notes') is-invalid @enderror" 
                                  id="additional_notes" name="additional_notes" rows="4">{{ old('additional_notes', $trainee->additional_notes ?? '') }}</textarea>
                            <small class="form-text text-muted">Add any additional notes or information about the trainee.</small>
                            @error('additional_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Profile Picture -->
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                            </div>
                            <div class="card-body text-center">
                                <img id="avatar-preview" class="img-fluid rounded-circle mb-3" 
                                     src="{{ $trainee->avatar_url }}" alt="Profile Picture" 
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('trainee_avatar') is-invalid @enderror" 
                                               id="trainee_avatar" name="trainee_avatar" accept="image/*">
                                        <label class="custom-file-label" for="trainee_avatar">Choose new image</label>
                                    </div>
                                    <small class="form-text text-muted">Maximum file size: 2MB. Accepted formats: JPEG, PNG, JPG, GIF.</small>
                                    @error('trainee_avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Emergency Contact Information -->
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Emergency Contact</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="emergency_contact_name">Contact Name</label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                           id="emergency_contact_name" name="emergency_contact_name" 
                                           value="{{ old('emergency_contact_name', $trainee->emergency_contact_name ?? '') }}">
                                    @error('emergency_contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="emergency_contact_phone">Contact Phone</label>
                                    <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                           id="emergency_contact_phone" name="emergency_contact_phone" 
                                           value="{{ old('emergency_contact_phone', $trainee->emergency_contact_phone ?? '') }}">
                                    @error('emergency_contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="emergency_contact_relationship">Relationship</label>
                                    <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                                           id="emergency_contact_relationship" name="emergency_contact_relationship" 
                                           value="{{ old('emergency_contact_relationship', $trainee->emergency_contact_relationship ?? '') }}">
                                    @error('emergency_contact_relationship')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Consent Information -->
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Consent</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" 
                                           id="photo_consent" name="photo_consent" value="1" 
                                           {{ old('photo_consent', $trainee->photo_consent ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="photo_consent">
                                        Permission to use photos/videos for promotional purposes
                                    </label>
                                </div>
                                
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" 
                                           id="services_consent" name="services_consent" value="1" 
                                           {{ old('services_consent', $trainee->services_consent ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="services_consent">
                                        Consent for rehabilitation services
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                            </div>
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Save Changes
                                </button>
                                <a href="{{ route('traineeprofile', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)]) }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                                <hr>
                                <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteTraineeModal">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete Trainee
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Trainee Modal -->
<div class="modal fade" id="deleteTraineeModal" tabindex="-1" role="dialog" aria-labelledby="deleteTraineeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTraineeModalLabel">Delete Trainee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 
                    Warning: This action cannot be undone. Are you sure you want to delete this trainee?
                </p>
                <p>
                    This will permanently remove <strong>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</strong> 
                    and all related data from the system.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('traineeprofile.destroy', ['encrypted_id' => \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)]) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Trainee</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Preview uploaded avatar
        $('#trainee_avatar').change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatar-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
                
                // Update custom file label with file name
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            }
        });
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Handle edit mode toggle
        $('#editModeToggle').change(function() {
            var isChecked = $(this).is(':checked');
            var formElements = $('.form-control, .form-check-input:not(#editModeToggle), select, textarea');
            
            if (isChecked) {
                // Enable editing mode
                formElements.prop('readonly', false).prop('disabled', false);
                $('.btn[type="submit"]').prop('disabled', false);
                $('.form-check-input:not(#editModeToggle)').prop('disabled', false);
            } else {
                // Disable editing mode (read-only)
                formElements.prop('readonly', true);
                $('select').prop('disabled', true);
                $('.btn[type="submit"]').prop('disabled', true);
                $('.form-check-input:not(#editModeToggle)').prop('disabled', true);
            }
        });
        
        // Initialize edit mode state on page load
        $('#editModeToggle').trigger('change');
    });
</script>
@endsection