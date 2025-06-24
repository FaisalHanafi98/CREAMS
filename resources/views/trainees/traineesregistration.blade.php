<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Trainee - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/traineeregistrationstyle.css') }}">
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
                    <h1 class="page-title">Register New Trainee</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <a href="{{ route('traineeshome') }}">Trainees</a>
                        <span class="separator">/</span>
                        <span class="current">Register New Trainee</span>
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
                                <a href="{{ route('admin.settings') }}">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
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
                <a href="{{ route('traineeshome') }}" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back to Trainees
                </a>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i> Please check the form for errors
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

            <!-- Registration Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trainee Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('traineesregistrationstore') }}" method="POST" enctype="multipart/form-data" id="traineeRegistrationForm">
                        @csrf
                        
                        <!-- Form progress indicator -->
                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        
                        <!-- Tabs for form sections -->
                        <ul class="nav nav-tabs mb-4" id="formTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                    <i class="fas fa-user mr-1"></i>Basic Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="guardian-tab" data-toggle="tab" href="#guardian" role="tab" aria-controls="guardian" aria-selected="false">
                                    <i class="fas fa-user-shield mr-1"></i>Guardian Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="additional-tab" data-toggle="tab" href="#additional" role="tab" aria-controls="additional" aria-selected="false">
                                    <i class="fas fa-clipboard-list mr-1"></i>Additional Information
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Tab content -->
                        <div class="tab-content" id="formTabContent">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_first_name">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('trainee_first_name') is-invalid @enderror" id="trainee_first_name" name="trainee_first_name" value="{{ old('trainee_first_name') }}" required>
                                            @error('trainee_first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_last_name">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('trainee_last_name') is-invalid @enderror" id="trainee_last_name" name="trainee_last_name" value="{{ old('trainee_last_name') }}" required>
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
                                            <input type="email" class="form-control @error('trainee_email') is-invalid @enderror" id="trainee_email" name="trainee_email" value="{{ old('trainee_email') }}" required>
                                            <small id="emailHelp" class="form-text text-muted">This will be used for communication purposes.</small>
                                            @error('trainee_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div id="emailFeedback" class="mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_phone_number">Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('trainee_phone_number') is-invalid @enderror" id="trainee_phone_number" name="trainee_phone_number" value="{{ old('trainee_phone_number') }}" required>
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
                                            <input type="date" class="form-control @error('trainee_date_of_birth') is-invalid @enderror" id="trainee_date_of_birth" name="trainee_date_of_birth" value="{{ old('trainee_date_of_birth') }}" max="{{ date('Y-m-d') }}" required>
                                            <small id="ageCalculation" class="form-text text-muted"></small>
                                            @error('trainee_date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_condition">Condition <span class="text-danger">*</span></label>
                                            <select class="form-control @error('trainee_condition') is-invalid @enderror" id="trainee_condition" name="trainee_condition" required>
                                                <option value="">Select Condition</option>
                                                @foreach($conditions ?? [] as $condition)
                                                    <option value="{{ $condition }}" {{ old('trainee_condition') == $condition ? 'selected' : '' }}>
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
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="centre_name">Centre <span class="text-danger">*</span></label>
                                            <select class="form-control @error('centre_name') is-invalid @enderror" id="centre_name" name="centre_name" required>
                                                <option value="">Select Centre</option>
                                                @foreach($centres as $centre)
                                                    <option value="{{ $centre->centre_name }}" {{ (old('centre_name') == $centre->centre_name || (isset($selectedCentre) && $selectedCentre && $selectedCentre->centre_name == $centre->centre_name)) ? 'selected' : '' }}>
                                                        {{ $centre->centre_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('centre_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="trainee_avatar">Profile Picture</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input @error('trainee_avatar') is-invalid @enderror" id="trainee_avatar" name="trainee_avatar" accept="image/*">
                                                <label class="custom-file-label" for="trainee_avatar">Choose file</label>
                                            </div>
                                            <small class="form-text text-muted">Maximum file size: 2MB. Accepted formats: JPEG, PNG, JPG, GIF.</small>
                                            @error('trainee_avatar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div id="avatar-preview" class="mt-2 text-center d-none">
                                                <img src="" class="img-thumbnail" style="max-height: 150px;" alt="Avatar Preview">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-primary next-tab" data-next="guardian-tab">
                                        Next: Guardian Information <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Guardian Information Tab -->
                            <div class="tab-pane fade" id="guardian" role="tabpanel" aria-labelledby="guardian-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="guardian_name">Guardian's Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('guardian_name') is-invalid @enderror" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required>
                                            @error('guardian_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="guardian_relationship">Relationship <span class="text-danger">*</span></label>
                                            <select class="form-control @error('guardian_relationship') is-invalid @enderror" id="guardian_relationship" name="guardian_relationship" required>
                                                <option value="">Select Relationship</option>
                                                <option value="Parent" {{ old('guardian_relationship') == 'Parent' ? 'selected' : '' }}>Parent</option>
                                                <option value="Sibling" {{ old('guardian_relationship') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                                                <option value="Grandparent" {{ old('guardian_relationship') == 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
                                                <option value="Aunt/Uncle" {{ old('guardian_relationship') == 'Aunt/Uncle' ? 'selected' : '' }}>Aunt/Uncle</option>
                                                <option value="Legal Guardian" {{ old('guardian_relationship') == 'Legal Guardian' ? 'selected' : '' }}>Legal Guardian</option>
                                                <option value="Other" {{ old('guardian_relationship') == 'Other' ? 'selected' : '' }}>Other</option>
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
                                            <label for="guardian_phone">Guardian's Phone <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}" required>
                                            @error('guardian_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="guardian_email">Guardian's Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('guardian_email') is-invalid @enderror" id="guardian_email" name="guardian_email" value="{{ old('guardian_email') }}" required>
                                            @error('guardian_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-secondary prev-tab" data-prev="basic-tab">
                                        <i class="fas fa-arrow-left mr-1"></i> Previous: Basic Information
                                    </button>
                                    <button type="button" class="btn btn-primary next-tab" data-next="additional-tab">
                                        Next: Additional Information <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Additional Information Tab -->
                            <div class="tab-pane fade" id="additional" role="tabpanel" aria-labelledby="additional-tab">
                                <div class="form-group">
                                    <label for="additional_notes">Additional Notes</label>
                                    <textarea class="form-control @error('additional_notes') is-invalid @enderror" id="additional_notes" name="additional_notes" rows="5">{{ old('additional_notes') }}</textarea>
                                    <small class="form-text text-muted">Please provide any additional information that may be relevant to the trainee's care and development.</small>
                                    @error('additional_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="consent" name="consent" required>
                                    <label class="form-check-label" for="consent">
                                        I confirm that all information provided is accurate and I consent to the collection and processing of this data for the purpose of providing services to the trainee.
                                    </label>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-secondary prev-tab" data-prev="guardian-tab">
                                        <i class="fas fa-arrow-left mr-1"></i> Previous: Guardian Information
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <i class="fas fa-user-plus mr-1"></i> Register Trainee
                                    </button>
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
            
            // File input change event for image preview
            $('#trainee_avatar').change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#avatar-preview').removeClass('d-none');
                        $('#avatar-preview img').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                    
                    // Update custom file label with file name
                    var fileName = $(this).val().split('\\').pop();
                    $(this).next('.custom-file-label').html(fileName);
                } else {
                    $('#avatar-preview').addClass('d-none');
                    $(this).next('.custom-file-label').html('Choose file');
                }
            });
            
            // Age calculation on date of birth change
            $('#trainee_date_of_birth').change(function() {
                var dob = new Date($(this).val());
                var today = new Date();
                var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
                
                if (!isNaN(age) && age >= 0) {
                    $('#ageCalculation').text('Age: ' + age + ' years');
                } else {
                    $('#ageCalculation').text('');
                }
            });
            
            // Trigger date of birth change if already has value
            if ($('#trainee_date_of_birth').val()) {
                $('#trainee_date_of_birth').trigger('change');
            }
            
            // Email validation via AJAX
            var emailTimer;
            $('#trainee_email').on('input', function() {
                clearTimeout(emailTimer);
                var email = $(this).val();
                
                if (email && validateEmail(email)) {
                    emailTimer = setTimeout(function() {
                        $('#emailFeedback').html('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="sr-only">Loading...</span></div> Checking email availability...');
                        
                        $.ajax({
                            url: '{{ route("validateEmail") }}',
                            type: 'POST',
                            data: {
                                email: email,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.valid) {
                                    $('#emailFeedback').html('<div class="text-success"><i class="fas fa-check-circle"></i> ' + response.message + '</div>');
                                } else {
                                    $('#emailFeedback').html('<div class="text-danger"><i class="fas fa-times-circle"></i> ' + response.message + '</div>');
                                }
                            },
                            error: function() {
                                $('#emailFeedback').html('<div class="text-danger"><i class="fas fa-exclamation-circle"></i> Error checking email. Please try again.</div>');
                            }
                        });
                    }, 500);
                } else {
                    $('#emailFeedback').empty();
                }
            });
            
            // Tab navigation
            $('.next-tab').click(function() {
                var nextTab = $(this).data('next');
                $('#' + nextTab).tab('show');
                updateProgressBar();
            });
            
            $('.prev-tab').click(function() {
                var prevTab = $(this).data('prev');
                $('#' + prevTab).tab('show');
                updateProgressBar();
            });
            
            $('#formTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                updateProgressBar();
            });
            
            // Progress bar update
            function updateProgressBar() {
                var currentTab = $('#formTabs .nav-link.active').attr('id');
                var progress = 0;
                
                switch(currentTab) {
                    case 'basic-tab':
                        progress = 33;
                        break;
                    case 'guardian-tab':
                        progress = 66;
                        break;
                    case 'additional-tab':
                        progress = 100;
                        break;
                }
                
                $('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).text(progress + '%');
            }
            
            // Form validation before submission
            $('#traineeRegistrationForm').submit(function(e) {
                var requiredFields = $(this).find('[required]');
                var valid = true;
                
                requiredFields.each(function() {
                    if (!$(this).val()) {
                        valid = false;
                        $(this).addClass('is-invalid');
                        
                        // Show the tab containing the first invalid field
                        var tabId = $(this).closest('.tab-pane').attr('id');
                        $('#formTabs a[href="#' + tabId + '"]').tab('show');
                        
                        // Stop the loop after finding the first error
                        return false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                } else if (!$('#consent').is(':checked')) {
                    e.preventDefault();
                    alert('Please confirm your consent by checking the box.');
                    $('#consent').addClass('is-invalid');
                }
            });
            
            // Email validation helper function
            function validateEmail(email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }
            
            // Initialize progress bar
            updateProgressBar();
            
            // Hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>
</html>