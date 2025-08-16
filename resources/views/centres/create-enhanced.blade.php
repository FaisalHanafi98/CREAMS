@extends('layouts.app')

@section('title', 'Create New Centre - CREAMS')

@section('content')
<div class="enhanced-centre-form-container">
    {{-- Enhanced Header Section --}}
    <div class="form-header-section">
        <div class="header-content">
            <div class="header-info">
                <div class="breadcrumb-nav">
                    <a href="{{ route('centres.index') }}" class="breadcrumb-link">Centre Management</a>
                    <i class="fas fa-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Create New Centre</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-building gradient-icon"></i>
                    Create New Centre
                </h1>
                <p class="page-subtitle">Set up a new rehabilitation centre with comprehensive details and configuration</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('centres.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Centres
                </a>
            </div>
        </div>
    </div>

    {{-- Enhanced Alert Section --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show enhanced-alert" role="alert">
            <div class="alert-content">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Success!</strong>
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show enhanced-alert" role="alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Error!</strong>
                    {{ session('error') }}
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show enhanced-alert" role="alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Validation Errors!</strong>
                    Please check the form for errors:
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Enhanced Multi-Step Form --}}
    <div class="enhanced-form-wrapper">
        <form id="centreForm" action="{{ route('centres.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Form Progress Indicator --}}
            <div class="form-progress">
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Location</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Contact</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Operations</div>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-number">5</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 20%"></div>
                </div>
            </div>

            {{-- Validation Summary --}}
            <div id="validationSummary" class="validation-summary" style="display: none;">
                <div class="validation-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Please fix the following issues:</span>
                </div>
                <ul id="validationList" class="validation-list"></ul>
            </div>

            {{-- Step 1: Basic Information --}}
            <div class="form-step active" data-step="1">
                <div class="step-header">
                    <h3><i class="fas fa-info-circle"></i> Basic Centre Information</h3>
                    <p>Provide essential details about the centre</p>
                </div>

                <div class="form-grid">
                    <div class="form-section">
                        <div class="section-header">
                            <h4>Identification</h4>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="centre_id" class="required-label">
                                    <i class="fas fa-fingerprint"></i>
                                    Centre ID
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('centre_id') is-invalid @enderror" 
                                       id="centre_id" 
                                       name="centre_id" 
                                       value="{{ old('centre_id') }}" 
                                       placeholder="e.g., CTR001, REHAB-KL-01"
                                       maxlength="20" 
                                       required
                                       data-validation="required|unique|alphanumeric">
                                <div class="field-validation-error" id="centre_id_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-info-circle"></i>
                                    Unique identifier for the centre (max 20 characters, alphanumeric)
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centre_name" class="required-label">
                                    <i class="fas fa-building"></i>
                                    Centre Name
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('centre_name') is-invalid @enderror" 
                                       id="centre_name" 
                                       name="centre_name" 
                                       value="{{ old('centre_name') }}" 
                                       placeholder="Enter centre name"
                                       maxlength="100"
                                       required
                                       data-validation="required|min:3|max:100">
                                <div class="field-validation-error" id="centre_name_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-info-circle"></i>
                                    Official name of the rehabilitation centre
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="centre_description" class="enhanced-label">
                                <i class="fas fa-file-alt"></i>
                                Centre Description
                            </label>
                            <textarea class="form-control @error('centre_description') is-invalid @enderror" 
                                      id="centre_description" 
                                      name="centre_description" 
                                      rows="4"
                                      maxlength="500"
                                      data-validation="max:500"
                                      placeholder="Brief description of the centre, its mission, and specializations...">{{ old('centre_description') }}</textarea>
                            <div class="field-validation-error" id="centre_description_error"></div>
                            <div class="character-counter">
                                <span id="descriptionCounter">0</span>/500 characters
                            </div>
                            <small class="field-help">
                                <i class="fas fa-lightbulb"></i>
                                Include the centre's mission, specializations, and unique features
                            </small>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <h4>Centre Image</h4>
                        </div>

                        <div class="image-upload-section">
                            <div class="image-upload-area" id="imageUploadArea">
                                <div class="upload-placeholder">
                                    <i class="fas fa-camera"></i>
                                    <h5>Upload Centre Image</h5>
                                    <p>Click to select or drag and drop an image</p>
                                    <small>Supported formats: JPG, PNG, WebP (Max 5MB)</small>
                                </div>
                                <input type="file" 
                                       id="centre_image" 
                                       name="centre_image" 
                                       accept="image/*"
                                       class="image-input"
                                       data-validation="image|max_size:5MB">
                                <div class="image-preview" id="imagePreview" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="field-validation-error" id="centre_image_error"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Location Information --}}
            <div class="form-step" data-step="2">
                <div class="step-header">
                    <h3><i class="fas fa-map-marker-alt"></i> Location & Address</h3>
                    <p>Specify the centre's complete address and location details</p>
                </div>

                <div class="form-grid">
                    <div class="form-section">
                        <div class="section-header">
                            <h4>Address Information</h4>
                        </div>

                        <div class="form-group">
                            <label for="centre_address" class="required-label">
                                <i class="fas fa-home"></i>
                                Complete Address
                                <span class="required-asterisk">*</span>
                            </label>
                            <textarea class="form-control @error('centre_address') is-invalid @enderror" 
                                      id="centre_address" 
                                      name="centre_address" 
                                      rows="3"
                                      maxlength="250"
                                      required
                                      data-validation="required|min:10|max:250"
                                      placeholder="Enter complete street address, including building number, street name, area...">{{ old('centre_address') }}</textarea>
                            <div class="field-validation-error" id="centre_address_error"></div>
                            <div class="character-counter">
                                <span id="addressCounter">0</span>/250 characters
                            </div>
                            <small class="field-help">
                                <i class="fas fa-info-circle"></i>
                                Include building number, street name, area, and any landmark information
                            </small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="centre_city" class="required-label">
                                    <i class="fas fa-city"></i>
                                    City
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('centre_city') is-invalid @enderror" 
                                       id="centre_city" 
                                       name="centre_city" 
                                       value="{{ old('centre_city') }}" 
                                       placeholder="e.g., Kuala Lumpur"
                                       required
                                       data-validation="required|min:2">
                                <div class="field-validation-error" id="centre_city_error"></div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="centre_state" class="required-label">
                                    <i class="fas fa-flag"></i>
                                    State
                                    <span class="required-asterisk">*</span>
                                </label>
                                <select class="form-control enhanced-select @error('centre_state') is-invalid @enderror" 
                                        id="centre_state" 
                                        name="centre_state" 
                                        required
                                        data-validation="required">
                                    <option value="">Select State</option>
                                    <option value="Johor" {{ old('centre_state') == 'Johor' ? 'selected' : '' }}>Johor</option>
                                    <option value="Kedah" {{ old('centre_state') == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                                    <option value="Kelantan" {{ old('centre_state') == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                                    <option value="Kuala Lumpur" {{ old('centre_state') == 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
                                    <option value="Labuan" {{ old('centre_state') == 'Labuan' ? 'selected' : '' }}>Labuan</option>
                                    <option value="Melaka" {{ old('centre_state') == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                                    <option value="Negeri Sembilan" {{ old('centre_state') == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                                    <option value="Pahang" {{ old('centre_state') == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                                    <option value="Penang" {{ old('centre_state') == 'Penang' ? 'selected' : '' }}>Penang</option>
                                    <option value="Perak" {{ old('centre_state') == 'Perak' ? 'selected' : '' }}>Perak</option>
                                    <option value="Perlis" {{ old('centre_state') == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                                    <option value="Putrajaya" {{ old('centre_state') == 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
                                    <option value="Sabah" {{ old('centre_state') == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                                    <option value="Sarawak" {{ old('centre_state') == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                                    <option value="Selangor" {{ old('centre_state') == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                                    <option value="Terengganu" {{ old('centre_state') == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                                </select>
                                <div class="field-validation-error" id="centre_state_error"></div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="centre_postcode" class="required-label">
                                    <i class="fas fa-mail-bulk"></i>
                                    Postcode
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('centre_postcode') is-invalid @enderror" 
                                       id="centre_postcode" 
                                       name="centre_postcode" 
                                       value="{{ old('centre_postcode') }}" 
                                       placeholder="e.g., 50400"
                                       maxlength="5"
                                       required
                                       data-validation="required|numeric|length:5">
                                <div class="field-validation-error" id="centre_postcode_error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <h4>Geographic Details</h4>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="centre_region" class="enhanced-label">
                                    <i class="fas fa-globe-asia"></i>
                                    Region/Zone
                                </label>
                                <select class="form-control enhanced-select" id="centre_region" name="centre_region">
                                    <option value="">Select Region</option>
                                    <option value="Central" {{ old('centre_region') == 'Central' ? 'selected' : '' }}>Central</option>
                                    <option value="Northern" {{ old('centre_region') == 'Northern' ? 'selected' : '' }}>Northern</option>
                                    <option value="Southern" {{ old('centre_region') == 'Southern' ? 'selected' : '' }}>Southern</option>
                                    <option value="Eastern" {{ old('centre_region') == 'Eastern' ? 'selected' : '' }}>Eastern</option>
                                    <option value="East Malaysia" {{ old('centre_region') == 'East Malaysia' ? 'selected' : '' }}>East Malaysia</option>
                                </select>
                                <small class="field-help">
                                    <i class="fas fa-info-circle"></i>
                                    Administrative region for reporting and coordination
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centre_coordinates" class="enhanced-label">
                                    <i class="fas fa-map-pin"></i>
                                    GPS Coordinates (Optional)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="centre_coordinates" 
                                       name="centre_coordinates" 
                                       value="{{ old('centre_coordinates') }}" 
                                       placeholder="e.g., 3.139, 101.687"
                                       data-validation="coordinates">
                                <div class="field-validation-error" id="centre_coordinates_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-satellite"></i>
                                    Format: latitude, longitude (for mapping and navigation)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Contact Information --}}
            <div class="form-step" data-step="3">
                <div class="step-header">
                    <h3><i class="fas fa-address-book"></i> Contact & Management</h3>
                    <p>Configure contact details and management information</p>
                </div>

                <div class="form-grid">
                    <div class="form-section">
                        <div class="section-header">
                            <h4>Primary Contact Information</h4>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="centre_phone" class="enhanced-label">
                                    <i class="fas fa-phone"></i>
                                    Primary Phone Number
                                </label>
                                <input type="tel" 
                                       class="form-control @error('centre_phone') is-invalid @enderror" 
                                       id="centre_phone" 
                                       name="centre_phone" 
                                       value="{{ old('centre_phone') }}" 
                                       placeholder="+60123456789"
                                       data-validation="phone|malaysia">
                                <div class="field-validation-error" id="centre_phone_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-info-circle"></i>
                                    Include country code (+60 for Malaysia)
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centre_fax" class="enhanced-label">
                                    <i class="fas fa-fax"></i>
                                    Fax Number
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="centre_fax" 
                                       name="centre_fax" 
                                       value="{{ old('centre_fax') }}" 
                                       placeholder="+60323456789"
                                       data-validation="phone">
                                <small class="field-help">
                                    <i class="fas fa-info-circle"></i>
                                    Optional fax number for official communications
                                </small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="centre_email" class="enhanced-label">
                                    <i class="fas fa-envelope"></i>
                                    Official Email Address
                                </label>
                                <input type="email" 
                                       class="form-control @error('centre_email') is-invalid @enderror" 
                                       id="centre_email" 
                                       name="centre_email" 
                                       value="{{ old('centre_email') }}" 
                                       placeholder="centre@example.com"
                                       data-validation="email">
                                <div class="field-validation-error" id="centre_email_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-shield-alt"></i>
                                    Official email for communications and notifications
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centre_website" class="enhanced-label">
                                    <i class="fas fa-globe"></i>
                                    Website URL
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="centre_website" 
                                       name="centre_website" 
                                       value="{{ old('centre_website') }}" 
                                       placeholder="https://centre-website.com"
                                       data-validation="url">
                                <div class="field-validation-error" id="centre_website_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-external-link-alt"></i>
                                    Optional website URL for public information
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <h4>Management Details</h4>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="centre_manager" class="enhanced-label">
                                    <i class="fas fa-user-tie"></i>
                                    Centre Manager
                                </label>
                                <input type="text" 
                                       class="form-control @error('centre_manager') is-invalid @enderror" 
                                       id="centre_manager" 
                                       name="centre_manager" 
                                       value="{{ old('centre_manager') }}" 
                                       placeholder="Full name of centre manager"
                                       data-validation="name">
                                <div class="field-validation-error" id="centre_manager_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-id-badge"></i>
                                    Full name of the person responsible for centre operations
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centre_manager_contact" class="enhanced-label">
                                    <i class="fas fa-mobile-alt"></i>
                                    Manager Contact
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="centre_manager_contact" 
                                       name="centre_manager_contact" 
                                       value="{{ old('centre_manager_contact') }}" 
                                       placeholder="+60123456789"
                                       data-validation="phone">
                                <div class="field-validation-error" id="centre_manager_contact_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-phone-volume"></i>
                                    Direct contact number for the centre manager
                                </small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="centre_supervisor" class="enhanced-label">
                                    <i class="fas fa-user-check"></i>
                                    Clinical Supervisor
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="centre_supervisor" 
                                       name="centre_supervisor" 
                                       value="{{ old('centre_supervisor') }}" 
                                       placeholder="Name of clinical supervisor">
                                <small class="field-help">
                                    <i class="fas fa-stethoscope"></i>
                                    Licensed professional overseeing clinical operations
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centre_license_number" class="enhanced-label">
                                    <i class="fas fa-certificate"></i>
                                    License/Registration Number
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="centre_license_number" 
                                       name="centre_license_number" 
                                       value="{{ old('centre_license_number') }}" 
                                       placeholder="Official license or registration number">
                                <small class="field-help">
                                    <i class="fas fa-seal-check"></i>
                                    Government-issued license or registration number
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Operational Details --}}
            <div class="form-step" data-step="4">
                <div class="step-header">
                    <h3><i class="fas fa-cogs"></i> Operational Configuration</h3>
                    <p>Set up operational parameters, capacity, and facility details</p>
                </div>

                <div class="form-grid">
                    <div class="form-section">
                        <div class="section-header">
                            <h4>Capacity & Status</h4>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="centre_capacity" class="required-label">
                                    <i class="fas fa-users"></i>
                                    Maximum Capacity
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('centre_capacity') is-invalid @enderror" 
                                       id="centre_capacity" 
                                       name="centre_capacity" 
                                       value="{{ old('centre_capacity') }}" 
                                       placeholder="e.g., 100"
                                       min="1"
                                       max="1000"
                                       required
                                       data-validation="required|numeric|min:1|max:1000">
                                <div class="field-validation-error" id="centre_capacity_error"></div>
                                <small class="field-help">
                                    <i class="fas fa-calculator"></i>
                                    Maximum number of trainees that can be accommodated
                                </small>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="centre_status" class="required-label">
                                    <i class="fas fa-toggle-on"></i>
                                    Operational Status
                                    <span class="required-asterisk">*</span>
                                </label>
                                <select class="form-control enhanced-select @error('centre_status') is-invalid @enderror" 
                                        id="centre_status" 
                                        name="centre_status" 
                                        required
                                        data-validation="required">
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('centre_status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('centre_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="maintenance" {{ old('centre_status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                    <option value="planning" {{ old('centre_status') == 'planning' ? 'selected' : '' }}>Planning Phase</option>
                                </select>
                                <div class="field-validation-error" id="centre_status_error"></div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="centre_type" class="enhanced-label">
                                    <i class="fas fa-building-columns"></i>
                                    Centre Type
                                </label>
                                <select class="form-control enhanced-select" id="centre_type" name="centre_type">
                                    <option value="">Select Type</option>
                                    <option value="residential" {{ old('centre_type') == 'residential' ? 'selected' : '' }}>Residential</option>
                                    <option value="day_care" {{ old('centre_type') == 'day_care' ? 'selected' : '' }}>Day Care</option>
                                    <option value="outpatient" {{ old('centre_type') == 'outpatient' ? 'selected' : '' }}>Outpatient</option>
                                    <option value="community" {{ old('centre_type') == 'community' ? 'selected' : '' }}>Community</option>
                                    <option value="specialized" {{ old('centre_type') == 'specialized' ? 'selected' : '' }}>Specialized</option>
                                </select>
                                <small class="field-help">
                                    <i class="fas fa-home-heart"></i>
                                    Classification based on service delivery model
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <h4>Operating Hours</h4>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="opening_time" class="enhanced-label">
                                    <i class="fas fa-sun"></i>
                                    Opening Time
                                </label>
                                <input type="time" 
                                       class="form-control" 
                                       id="opening_time" 
                                       name="opening_time" 
                                       value="{{ old('opening_time', '08:00') }}">
                                <small class="field-help">
                                    <i class="fas fa-clock"></i>
                                    Daily opening time for regular operations
                                </small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="closing_time" class="enhanced-label">
                                    <i class="fas fa-moon"></i>
                                    Closing Time
                                </label>
                                <input type="time" 
                                       class="form-control" 
                                       id="closing_time" 
                                       name="closing_time" 
                                       value="{{ old('closing_time', '17:00') }}">
                                <small class="field-help">
                                    <i class="fas fa-clock"></i>
                                    Daily closing time for regular operations
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="operating_days" class="enhanced-label">
                                <i class="fas fa-calendar-week"></i>
                                Operating Days
                            </label>
                            <div class="days-selector">
                                <div class="day-checkbox">
                                    <input type="checkbox" id="monday" name="operating_days[]" value="monday" {{ in_array('monday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="monday">Monday</label>
                                </div>
                                <div class="day-checkbox">
                                    <input type="checkbox" id="tuesday" name="operating_days[]" value="tuesday" {{ in_array('tuesday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="tuesday">Tuesday</label>
                                </div>
                                <div class="day-checkbox">
                                    <input type="checkbox" id="wednesday" name="operating_days[]" value="wednesday" {{ in_array('wednesday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="wednesday">Wednesday</label>
                                </div>
                                <div class="day-checkbox">
                                    <input type="checkbox" id="thursday" name="operating_days[]" value="thursday" {{ in_array('thursday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="thursday">Thursday</label>
                                </div>
                                <div class="day-checkbox">
                                    <input type="checkbox" id="friday" name="operating_days[]" value="friday" {{ in_array('friday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="friday">Friday</label>
                                </div>
                                <div class="day-checkbox">
                                    <input type="checkbox" id="saturday" name="operating_days[]" value="saturday" {{ in_array('saturday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="saturday">Saturday</label>
                                </div>
                                <div class="day-checkbox">
                                    <input type="checkbox" id="sunday" name="operating_days[]" value="sunday" {{ in_array('sunday', old('operating_days', [])) ? 'checked' : '' }}>
                                    <label for="sunday">Sunday</label>
                                </div>
                            </div>
                            <small class="field-help">
                                <i class="fas fa-calendar-check"></i>
                                Select the days when the centre operates
                            </small>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <h4>Facilities & Services</h4>
                        </div>

                        <div class="form-group">
                            <label for="centre_facilities" class="enhanced-label">
                                <i class="fas fa-tools"></i>
                                Available Facilities
                            </label>
                            <textarea class="form-control @error('centre_facilities') is-invalid @enderror" 
                                      id="centre_facilities" 
                                      name="centre_facilities" 
                                      rows="4"
                                      maxlength="1000"
                                      data-validation="max:1000"
                                      placeholder="List available facilities, equipment, and amenities...">{{ old('centre_facilities') }}</textarea>
                            <div class="field-validation-error" id="centre_facilities_error"></div>
                            <div class="character-counter">
                                <span id="facilitiesCounter">0</span>/1000 characters
                            </div>
                            <small class="field-help">
                                <i class="fas fa-list-ul"></i>
                                Include therapy rooms, equipment, accessibility features, etc.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="specialized_services" class="enhanced-label">
                                <i class="fas fa-heart"></i>
                                Specialized Services
                            </label>
                            <div class="services-grid">
                                <div class="service-checkbox">
                                    <input type="checkbox" id="physical_therapy" name="specialized_services[]" value="physical_therapy">
                                    <label for="physical_therapy">
                                        <i class="fas fa-walking"></i>
                                        Physical Therapy
                                    </label>
                                </div>
                                <div class="service-checkbox">
                                    <input type="checkbox" id="occupational_therapy" name="specialized_services[]" value="occupational_therapy">
                                    <label for="occupational_therapy">
                                        <i class="fas fa-hands-helping"></i>
                                        Occupational Therapy
                                    </label>
                                </div>
                                <div class="service-checkbox">
                                    <input type="checkbox" id="speech_therapy" name="specialized_services[]" value="speech_therapy">
                                    <label for="speech_therapy">
                                        <i class="fas fa-comments"></i>
                                        Speech Therapy
                                    </label>
                                </div>
                                <div class="service-checkbox">
                                    <input type="checkbox" id="counseling" name="specialized_services[]" value="counseling">
                                    <label for="counseling">
                                        <i class="fas fa-user-friends"></i>
                                        Counseling Services
                                    </label>
                                </div>
                                <div class="service-checkbox">
                                    <input type="checkbox" id="vocational_training" name="specialized_services[]" value="vocational_training">
                                    <label for="vocational_training">
                                        <i class="fas fa-tools"></i>
                                        Vocational Training
                                    </label>
                                </div>
                                <div class="service-checkbox">
                                    <input type="checkbox" id="medical_services" name="specialized_services[]" value="medical_services">
                                    <label for="medical_services">
                                        <i class="fas fa-user-md"></i>
                                        Medical Services
                                    </label>
                                </div>
                            </div>
                            <small class="field-help">
                                <i class="fas fa-star-of-life"></i>
                                Select all specialized services offered at this centre
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 5: Review & Confirmation --}}
            <div class="form-step" data-step="5">
                <div class="step-header">
                    <h3><i class="fas fa-clipboard-check"></i> Review & Confirm</h3>
                    <p>Please review all information before creating the centre</p>
                </div>

                <div class="review-container">
                    <div class="review-section">
                        <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <label>Centre ID:</label>
                                <span id="review-centre-id">-</span>
                            </div>
                            <div class="review-item">
                                <label>Centre Name:</label>
                                <span id="review-centre-name">-</span>
                            </div>
                            <div class="review-item">
                                <label>Description:</label>
                                <span id="review-description">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-map-marker-alt"></i> Location Details</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <label>Address:</label>
                                <span id="review-address">-</span>
                            </div>
                            <div class="review-item">
                                <label>City, State:</label>
                                <span id="review-city-state">-</span>
                            </div>
                            <div class="review-item">
                                <label>Postcode:</label>
                                <span id="review-postcode">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-address-book"></i> Contact Information</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <label>Phone:</label>
                                <span id="review-phone">-</span>
                            </div>
                            <div class="review-item">
                                <label>Email:</label>
                                <span id="review-email">-</span>
                            </div>
                            <div class="review-item">
                                <label>Manager:</label>
                                <span id="review-manager">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-cogs"></i> Operational Details</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <label>Capacity:</label>
                                <span id="review-capacity">-</span>
                            </div>
                            <div class="review-item">
                                <label>Status:</label>
                                <span id="review-status">-</span>
                            </div>
                            <div class="review-item">
                                <label>Operating Hours:</label>
                                <span id="review-hours">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Final Validation Check --}}
                    <div class="final-validation">
                        <h4><i class="fas fa-shield-check"></i> Validation Status</h4>
                        <div id="finalValidationItems" class="validation-items">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Navigation --}}
            <div class="form-navigation">
                <button type="button" id="prevBtn" class="btn btn-outline-secondary" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                
                <div class="nav-spacer"></div>
                
                <button type="button" id="nextBtn" class="btn btn-primary">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                    <i class="fas fa-plus-circle"></i> Create Centre
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/form-validation-enhanced.css') }}">
<link rel="stylesheet" href="{{ asset('css/centre-form-enhanced.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/form-validation-enhanced.js') }}"></script>
<script src="{{ asset('js/centre-form-enhanced.js') }}"></script>
<script>
// Initialize enhanced centre form
document.addEventListener('DOMContentLoaded', function() {
    const centreForm = new EnhancedCentreForm({
        formId: 'centreForm',
        totalSteps: 5,
        autoSave: true,
        realTimeValidation: true
    });
    
    // Make it globally accessible
    window.centreForm = centreForm;
});
</script>
@endsection