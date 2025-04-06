<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>My Profile - CREAMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
  <link rel="stylesheet" href="{{ asset('css/profilestyle.css') }}">
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
        @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
          <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
        @else
          <img src="{{ asset('images/admin-avatar.jpg') }}" alt="{{ $user->name }}">
        @endif
      </div>
      <div class="admin-info">
        <div class="admin-name">{{ $user->name }}</div>
        <div class="admin-role">{{ ucfirst($role) }}</div>
      </div>
    </div>
    
    <ul class="nav-menu">
      <li>
        <a href="{{ route('dashboard') }}">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
          <div class="tooltip-sidebar">Dashboard</div>
        </a>
      </li>

      <!-- Add appropriate menu items based on role -->
      @if($role == 'admin')
        <li>
          <a href="{{ route('users.index') }}">
            <i class="fas fa-users"></i>
            <span>Staff Management</span>
            <div class="tooltip-sidebar">Staff Management</div>
          </a>
        </li>
        <li>
          <a href="{{ route('trainees.index') }}">
            <i class="fas fa-user-graduate"></i>
            <span>Tainee Management</span>
            <div class="tooltip-sidebar">Tainee Management</div>
          </a>
        </li>
        <li>
          <a href="{{ route('activities.index') }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Activities</span>
            <div class="tooltip-sidebar">Activities</div>
          </a>
        </li>
        <li>
          <a href="{{ route('admin/centres.index') }}">
            <i class="fas fa-building"></i>
            <span>Centres</span>
            <div class="tooltip-sidebar">Centres</div>
          </a>
        </li>
        <li>
          <a href="{{ route('admin/assets.index') }}">
            <i class="fas fa-boxes"></i>
            <span>Assets</span>
            <div class="tooltip-sidebar">Assets</div>
          </a>
        </li>
        <li>
          <a href="{{ route('reports.index') }}">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
            <div class="tooltip-sidebar">Reports</div>
          </a>
        </li>
        <li>
          <a href="{{ route('settings') }}">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
            <div class="tooltip-sidebar">Settings</div>
          </a>
        </li>
      @elseif($role == 'supervisor')
        <li>
          <a href="{{ route('teachers.index') }}">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Teacher Management</span>
            <div class="tooltip-sidebar">Teacher Management</div>
          </a>
        </li>
        <li>
          <a href="{{ route('trainees.index') }}">
            <i class="fas fa-user-graduate"></i>
            <span>Tainee Management</span>
            <div class="tooltip-sidebar">Tainee Management</div>
          </a>
        </li>
        <li>
          <a href="{{ route('activities.index') }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Activities</span>
            <div class="tooltip-sidebar">Activities</div>
          </a>
        </li>
        <li>
          <a href="{{ route('supervisor.reports') }}">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
            <div class="tooltip-sidebar">Reports</div>
          </a>
        </li>
      @elseif($role == 'teacher')
        <li>
          <a href="{{ route('trainees.index') }}">
            <i class="fas fa-user-graduate"></i>
            <span>Tainees</span>
            <div class="tooltip-sidebar">Tainees</div>
          </a>
        </li>
        <li>
          <a href="{{ route('classes.index') }}">
            <i class="fas fa-book"></i>
            <span>Classes</span>
            <div class="tooltip-sidebar">Classes</div>
          </a>
        </li>
        <li>
          <a href="{{ route('schedule') }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Schedule</span>
            <div class="tooltip-sidebar">Schedule</div>
          </a>
        </li>
        <li>
          <a href="{{ route('activities.index') }}">
            <i class="fas fa-clipboard-list"></i>
            <span>Activities</span>
            <div class="tooltip-sidebar">Activities</div>
          </a>
        </li>
      @elseif($role == 'ajk')
        <li>
          <a href="{{ route('events.index') }}">
            <i class="fas fa-calendar-day"></i>
            <span>Events</span>
            <div class="tooltip-sidebar">Events</div>
          </a>
        </li>
        <li>
          <a href="{{ route('volunteers.index') }}">
            <i class="fas fa-hands-helping"></i>
            <span>Volunteers</span>
            <div class="tooltip-sidebar">Volunteers</div>
          </a>
        </li>
        <li>
          <a href="{{ route('activities.index') }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Activities</span>
            <div class="tooltip-sidebar">Activities</div>
          </a>
        </li>
      @endif

      <li class="active">
        <a href="{{ route('profile') }}">
          <i class="fas fa-user-circle"></i>
          <span>My Profile</span>
          <div class="tooltip-sidebar">My Profile</div>
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
          <h1 class="page-title">My Profile</h1>
          <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <span class="current">My Profile</span>
          </div>
        </div>
        
        <div class="header-actions">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search...">
          </div>
          
          <div class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-count">3</span>
          </div>
          
          <div class="admin-dropdown">
            <div class="admin-dropdown-toggle">
              @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
                <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
              @else
                <img src="{{ asset('images/admin-avatar.jpg') }}" alt="{{ $user->name }}">
              @endif
              <span>{{ $user->name }}</span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="admin-dropdown-menu">
              <a href="{{ route('profile') }}">
                <i class="fas fa-user"></i> My Profile
              </a>
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
    </div>
    
    <!-- Content -->
    <div class="content-section">
      <!-- Profile Container -->
      <div class="profile-container">
        <div class="profile-header">
          <div class="profile-avatar" id="avatar-container">
            @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
              <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}" id="avatar-preview">
            @else
              <img src="{{ asset('images/admin-avatar.jpg') }}" alt="{{ $user->name }}" id="avatar-preview">
            @endif
            <div class="avatar-overlay" id="avatar-upload-btn">Change Photo (Max 1MB)</div>
            <div id="avatar-loading" class="avatar-loading" style="display: none;">Uploading...</div>
          </div>
          <div class="profile-info">
            <h2>{{ $user->name }}</h2>
            <div class="profile-role">{{ ucfirst($role) }}</div>
            <div class="profile-meta">
              <div class="meta-item">
                <i class="fas fa-envelope"></i>
                <span>{{ $user->email }}</span>
              </div>
              @if($user->phone)
              <div class="meta-item">
                <i class="fas fa-phone"></i>
                <span>{{ $user->phone }}</span>
              </div>
              @endif
              @if(isset($user->iium_id))
              <div class="meta-item">
                <i class="fas fa-id-card"></i>
                <span>{{ $user->iium_id }}</span>
              </div>
              @endif
            </div>
          </div>
          
          <button class="edit-profile-btn" id="edit-profile-toggle">
            <i class="fas fa-edit"></i> Edit Profile
          </button>
        </div>
        
        <!-- Hidden file input for avatar upload -->
        <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" style="display: none;">
          @csrf
          <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
        </form>
        
        <div id="avatar-error" class="alert alert-danger" style="display: none;">
          <i class="fas fa-exclamation-circle mr-2"></i>
          <span id="avatar-error-text"></span>
          <button type="button" class="close" onclick="$('#avatar-error').hide()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <!-- Alerts -->
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
        
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
        
        <!-- Profile Tabs -->
        <div class="profile-tabs">
          <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" id="pills-edit-tab" data-toggle="pill" href="#pills-edit" role="tab" aria-controls="pills-edit" aria-selected="true">
                <i class="fas fa-user-edit"></i> Personal Information
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="pills-password-tab" data-toggle="pill" href="#pills-password" role="tab" aria-controls="pills-password" aria-selected="false">
                <i class="fas fa-key"></i> Change Password
              </a>
            </li>
          </ul>
          <div class="tab-content" id="pills-tabContent">
            <!-- Edit Profile Tab -->
            <div class="tab-pane fade show active" id="pills-edit" role="tabpanel" aria-labelledby="pills-edit-tab">
              <form id="profile-form" action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="name">Full Name</label>
                      <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" readonly>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="phone">Phone Number</label>
                      <input type="text" class="form-control" id="phone" name="phone" value="{{ $user->phone ?? '' }}" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="iium_id">IIUM ID</label>
                      <input type="text" class="form-control" id="iium_id" value="{{ $user->iium_id ?? '' }}" readonly>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="address">Address</label>
                  <textarea class="form-control" id="address" name="address" rows="3" readonly>{{ $user->address ?? '' }}</textarea>
                </div>
                <div class="form-group">
                  <label for="bio">Bio</label>
                  <textarea class="form-control" id="bio" name="bio" rows="5" readonly>{{ $user->bio ?? '' }}</textarea>
                </div>
                <div class="form-actions" id="profile-actions" style="display: none;">
                  <button type="button" class="btn btn-outline-secondary" id="cancel-edit">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
            
            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="pills-password" role="tabpanel" aria-labelledby="pills-password-tab">
              <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="current_password">Current Password</label>
                  <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="new_password">New Password</label>
                      <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="new_password_confirmation">Confirm New Password</label>
                      <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                  </div>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
              </form>
            </div>
          </div>
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
      $('.admin-dropdown-toggle').click(function() {
        $('.admin-dropdown-menu').toggleClass('show');
      });
      
      // Close dropdown when clicking outside
      $(document).click(function(event) {
        if (!$(event.target).closest('.admin-dropdown').length) {
          $('.admin-dropdown-menu').removeClass('show');
        }
      });
      
      // Avatar upload functionality
      $('#avatar-upload-btn').click(function(e) {
        e.preventDefault();
        $('#avatarInput').click();
      });
      
      // File size validation before upload
      $('#avatarInput').change(function() {
        const file = this.files[0];
        
        if (!file) {
          return;
        }
        
        // Check file size (1MB max)
        const maxSize = 1 * 1024 * 1024; // 1MB in bytes
        if (file.size > maxSize) {
          // Show error message
          $('#avatar-error-text').text('File size exceeds 1MB limit. Please choose a smaller image.');
          $('#avatar-error').show();
          // Clear file input
          $(this).val('');
          return;
        }
        
        // Check file type
        const acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!acceptedTypes.includes(file.type)) {
          $('#avatar-error-text').text('Please select a valid image file (JPEG, PNG, JPG, or GIF).');
          $('#avatar-error').show();
          $(this).val('');
          return;
        }
        
        // Hide any error message
        $('#avatar-error').hide();
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
          $('#avatar-preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
        
        // Show loading indicator
        $('#avatar-loading').show();
        
        // Submit form directly
        submitAvatarForm();
      });
      
      // Function to handle direct avatar form submission
      function submitAvatarForm() {
        $('#avatarForm').submit();
      }
      
      // Edit profile toggle button
      $('#edit-profile-toggle').click(function() {
        const isEditing = $(this).hasClass('active');
        
        if (isEditing) {
          // Disable editing
          $(this).removeClass('active');
          $(this).html('<i class="fas fa-edit"></i> Edit Profile');
          $('#profile-form input, #profile-form textarea').prop('readonly', true);
          $('#profile-actions').hide();
        } else {
          // Enable editing
          $(this).addClass('active');
          $(this).html('<i class="fas fa-times"></i> Cancel');
          $('#profile-form input, #profile-form textarea').not('#iium_id').prop('readonly', false);
          $('#profile-actions').show();
        }
      });
      
      // Cancel edit button
      $('#cancel-edit').click(function() {
        // Reset the form
        $('#profile-form')[0].reset();
        
        // Disable editing
        $('#edit-profile-toggle').removeClass('active');
        $('#edit-profile-toggle').html('<i class="fas fa-edit"></i> Edit Profile');
        $('#profile-form input, #profile-form textarea').prop('readonly', true);
        $('#profile-actions').hide();
      });
      
      // Auto-hide alerts after 5 seconds
      setTimeout(function() {
        $('.alert-dismissible').alert('close');
      }, 5000);
    });
  </script>
</body>
</html>