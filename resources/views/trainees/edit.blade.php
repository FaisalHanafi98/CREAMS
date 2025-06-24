<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trainee - {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }} - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/trainee-edit.css') }}">
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
                    <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="User Avatar">
                @else
                    <img src="{{ asset('images/admin-avatar.jpg') }}" alt="User Avatar">
                @endif
            </div>
            <div class="admin-info">
                <div class="admin-name">{{ $user['name'] ?? 'User' }}</div>
                <div class="admin-role">{{ ucfirst($user['role'] ?? 'guest') }}</div>
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
            <li class="{{ request()->routeIs('traineeshome') || request()->routeIs('traineeprofile') || request()->routeIs('traineesregistrationpage') ? 'active' : '' }}">
                <a href="{{ route('traineeshome') }}">
                    <i class="fas fa-user-graduate"></i>
                    <span>Trainees</span>
                    <div class="tooltip-sidebar">Trainees</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('traineeactivity') ? 'active' : '' }}">
                <a href="{{ route('traineeactivity') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Activities</span>
                    <div class="tooltip-sidebar">Trainee Activities</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('teachershome') ? 'active' : '' }}">
                <a href="{{ route('teachershome') }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Staff</span>
                    <div class="tooltip-sidebar">Staff</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('schedulehomepage') ? 'active' : '' }}">
                <a href="{{ route('schedulehomepage') }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                    <div class="tooltip-sidebar">Schedule</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('assetmanagementpage') ? 'active' : '' }}">
                <a href="{{ route('assetmanagementpage') }}">
                    <i class="fas fa-box"></i>
                    <span>Assets</span>
                    <div class="tooltip-sidebar">Asset Management</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('aboutus') ? 'active' : '' }}">
                <a href="{{ route('aboutus') }}">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                    <div class="tooltip-sidebar">About Us</div>
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
        <div class="header">
            <div class="header-content">
                <div class="page-info">
                    <h1 class="page-title">Edit Trainee Profile</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <a href="{{ route('traineeshome') }}">Trainees</a>
                        <span class="separator">/</span>
                        <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</a>
                        <span class="separator">/</span>
                        <span class="current">Edit</span>
                    </div>
                </div>
                
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search" placeholder="Search...">
                    </div>
                    
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        @if(isset($notificationCount) && $notificationCount > 0)
                            <span class="notification-count">{{ $notificationCount }}</span>
                        @endif
                    </div>
                    
                    <div class="admin-dropdown">
                        <div class="admin-dropdown-toggle">
                            @if(isset($user['avatar']) && $user['avatar'])
                                <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="User">
                            @else
                                <img src="{{ asset('images/admin-avatar.jpg') }}" alt="User">
                            @endif
                            <span>{{ $user['name'] ?? 'User' }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="admin-dropdown-menu">
                            <a href="{{ route('profile') }}">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            @if(isset($user['role']) && $user['role'] == 'admin')
                                @if(Route::has('admin.settings'))
                                    <a href="{{ route('admin.settings') }}">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                @else
                                    <a href="#" onclick="alert('Settings feature coming soon')">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                @endif
                            @endif
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
            
            <div class="page-actions">
                <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Alert Messages -->
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
                    <form action="{{ route('traineeprofile.destroy', ['id' => $trainee->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Trainee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar toggle
            $('.toggle-btn').click(function() {
                $('.sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('expanded');
            });
            
            // Admin dropdown
            $('.admin-dropdown-toggle').click(function(e) {
                e.stopPropagation();
                $('.admin-dropdown-menu').toggleClass('show');
            });
            
            // Close dropdown when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('.admin-dropdown').length) {
                    $('.admin-dropdown-menu').removeClass('show');
                }
            });
            
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
</body>
</html>