@extends('layouts.app')

@section('title', 'Edit Trainee - ' . $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name . ' - CREAMS')

@section('styles')
<style>
    /* Enhanced styles for trainee edit form */
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
    
    .btn-danger {
        background: #dc3545;
        color: white;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }
    
    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }
    
    .btn-block {
        width: 100%;
        margin-bottom: 15px;
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
    
    #avatar-preview {
        border: 4px solid #e9ecef;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    #avatar-preview:hover {
        border-color: var(--primary-color);
        box-shadow: 0 6px 20px rgba(50, 189, 234, 0.3);
        transform: scale(1.05);
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
    
    .form-check {
        margin-bottom: 20px;
        padding-left: 0;
    }
    
    .form-check-input {
        width: 20px;
        height: 20px;
        margin-right: 12px;
        cursor: pointer;
    }
    
    .form-check-label {
        font-weight: 500;
        color: #495057;
        cursor: pointer;
        font-size: 14px;
        margin-bottom: 0;
    }
    
    hr {
        border: none;
        height: 2px;
        background: linear-gradient(to right, transparent, #e9ecef, transparent);
        margin: 35px 0;
    }
    
    h5 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 25px;
        font-size: 18px;
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
        display: inline-block;
    }
    
    /* Modal Styles */
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
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">Edit Trainee Profile</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('traineeshome') }}">Trainee</a>
                    <span class="separator">/</span>
                    <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</a>
                    <span class="separator">/</span>
                    <span class="current">Edit</span>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>
        </div>
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
                    <form action="{{ route('updatetraineeprofile', ['id' => $trainee->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('trainee_date_of_birth') is-invalid @enderror" 
                                                   id="trainee_date_of_birth" name="trainee_date_of_birth" 
                                                   value="{{ old('trainee_date_of_birth', $trainee->trainee_date_of_birth->format('Y-m-d')) }}" required>
                                            @error('trainee_date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_condition">Condition <span class="text-danger">*</span></label>
                                            <select class="form-control @error('trainee_condition') is-invalid @enderror" 
                                                    id="trainee_condition" name="trainee_condition" required>
                                                <option value="">Select Condition</option>
                                                @foreach($conditions as $condition)
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
                                        @foreach($centres as $centre)
                                            <option value="{{ $centre->centre_name }}" 
                                                {{ old('centre_name', $trainee->centre_name) == $centre->centre_name ? 'selected' : '' }}>
                                                {{ $centre->centre_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('centre_name')
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
                                                   value="{{ old('guardian_name', $traineeProfile->guardian_name ?? '') }}">
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
                                                <option value="Parent" {{ old('guardian_relationship', $traineeProfile->guardian_relationship ?? '') == 'Parent' ? 'selected' : '' }}>Parent</option>
                                                <option value="Sibling" {{ old('guardian_relationship', $traineeProfile->guardian_relationship ?? '') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                                                <option value="Grandparent" {{ old('guardian_relationship', $traineeProfile->guardian_relationship ?? '') == 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
                                                <option value="Aunt/Uncle" {{ old('guardian_relationship', $traineeProfile->guardian_relationship ?? '') == 'Aunt/Uncle' ? 'selected' : '' }}>Aunt/Uncle</option>
                                                <option value="Legal Guardian" {{ old('guardian_relationship', $traineeProfile->guardian_relationship ?? '') == 'Legal Guardian' ? 'selected' : '' }}>Legal Guardian</option>
                                                <option value="Other" {{ old('guardian_relationship', $traineeProfile->guardian_relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
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
                                                   value="{{ old('guardian_phone', $traineeProfile->guardian_phone ?? '') }}">
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
                                                   value="{{ old('guardian_email', $traineeProfile->guardian_email ?? '') }}">
                                            @error('guardian_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="guardian_address">Guardian's Address</label>
                                    <textarea class="form-control @error('guardian_address') is-invalid @enderror" 
                                          id="guardian_address" name="guardian_address" rows="3">{{ old('guardian_address', $traineeProfile->guardian_address ?? '') }}</textarea>
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
                                          id="medical_history" name="medical_history" rows="4">{{ old('medical_history', $traineeProfile->medical_history ?? '') }}</textarea>
                                    @error('medical_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="additional_notes">Additional Notes</label>
                                    <textarea class="form-control @error('additional_notes') is-invalid @enderror" 
                                          id="additional_notes" name="additional_notes" rows="4">{{ old('additional_notes', $traineeProfile->additional_notes ?? '') }}</textarea>
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
                                             src="{{ $trainee->getAvatarUrlAttribute() }}" alt="Profile Picture" 
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                        
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
                                                   value="{{ old('emergency_contact_name', $traineeProfile->emergency_contact_name ?? '') }}">
                                            @error('emergency_contact_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="emergency_contact_phone">Contact Phone</label>
                                            <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                                   id="emergency_contact_phone" name="emergency_contact_phone" 
                                                   value="{{ old('emergency_contact_phone', $traineeProfile->emergency_contact_phone ?? '') }}">
                                            @error('emergency_contact_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="emergency_contact_relationship">Relationship</label>
                                            <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                                                   id="emergency_contact_relationship" name="emergency_contact_relationship" 
                                                   value="{{ old('emergency_contact_relationship', $traineeProfile->emergency_contact_relationship ?? '') }}">
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
                                                   {{ old('photo_consent', $traineeProfile->photo_consent ?? 0) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="photo_consent">
                                                Permission to use photos/videos for promotional purposes
                                            </label>
                                        </div>
                                        
                                        <div class="form-group form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="services_consent" name="services_consent" value="1" 
                                                   {{ old('services_consent', $traineeProfile->services_consent ?? 0) ? 'checked' : '' }}>
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
                                        <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}" class="btn btn-secondary btn-block">
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
                <form action="{{ route('traineeprofile.destroy', ['id' => $trainee->id]) }}" method="POST" style="display: inline;">
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
    });
</script>
@endsection