<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit User Profile - CREAMS Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" />
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
  <link rel="stylesheet" href="{{ asset('css/usermanagement.css') }}">
  <link rel="stylesheet" href="{{ asset('css/userprofile.css') }}">
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
        <img src="{{ asset('images/admin-avatar.jpg') }}" alt="Admin Avatar">
      </div>
      <div class="admin-info">
        <div class="admin-name">{{ $name }}</div>
        <div class="admin-role">Administrator</div>
      </div>
    </div>
    
    <ul class="nav-menu">
      <li>
        <a href="{{ route('admin.dashboard') }}">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
          <div class="tooltip-sidebar">Dashboard</div>
        </a>
      </li>
      <li class="active">
        <a href="{{ route('admin.users') }}">
          <i class="fas fa-users"></i>
          <span>Staff Management</span>
          <div class="tooltip-sidebar">Staff Management</div>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.trainees') }}">
          <i class="fas fa-user-graduate"></i>
          <span>Tainee Management</span>
          <div class="tooltip-sidebar">Tainee Management</div>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.activities') }}">
          <i class="fas fa-calendar-alt"></i>
          <span>Activities</span>
          <div class="tooltip-sidebar">Activities</div>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.centres') }}">
          <i class="fas fa-building"></i>
          <span>Centres</span>
          <div class="tooltip-sidebar">Centres</div>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.assets') }}">
          <i class="fas fa-boxes"></i>
          <span>Assets</span>
          <div class="tooltip-sidebar">Assets</div>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.reports') }}">
          <i class="fas fa-chart-bar"></i>
          <span>Reports</span>
          <div class="tooltip-sidebar">Reports</div>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.settings') }}">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
          <div class="tooltip-sidebar">Settings</div>
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
          <h1 class="page-title">Edit User Profile</h1>
          <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <a href="{{ route('admin.users') }}">Staff Management</a>
            <span class="separator">/</span>
            <a href="{{ route('admin.user.view', 2) }}">Dr. Nurul Hafizah</a>
            <span class="separator">/</span>
            <span class="current">Edit</span>
          </div>
        </div>
        
        <div class="header-actions">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearch" placeholder="Search users...">
          </div>
          
          <div class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-count">3</span>
          </div>
          
          <div class="admin-dropdown">
            <div class="admin-dropdown-toggle">
              <img src="{{ asset('images/admin-avatar.jpg') }}" alt="Admin">
              <span>{{ $name }}</span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="admin-dropdown-menu">
              <a href="{{ route('admin.profile') }}">
                <i class="fas fa-user"></i> My Profile
              </a>
              <a href="{{ route('admin.settings') }}">
                <i class="fas fa-cog"></i> Settings
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
      
      <div class="page-actions">
        <a href="{{ route('admin.users') }}" class="action-btn">
          <i class="fas fa-arrow-left"></i> Back to Users
        </a>
        <div class="action-group">
          <a href="{{ route('admin.user.view', 2) }}" class="action-btn">
            <i class="fas fa-eye"></i> View Profile
          </a>
          <a href="{{ url()->current() }}" class="action-btn active">
            <i class="fas fa-edit"></i> Edit Profile
          </a>
        </div>
      </div>
    </div>
    
    <!-- Content -->
    <div class="content-section">
      <div class="profile-container">
        <form class="edit-profile-form" action="{{ route('admin.user.update', 2) }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="edit-avatar-container">
            <div class="edit-avatar">
              <img src="{{ asset('images/team/program-head.jpg') }}" alt="Dr. Nurul Hafizah">
            </div>
            <div class="avatar-buttons">
              <label for="avatar-upload" class="avatar-btn">
                <i class="fas fa-upload"></i> Upload Photo
              </label>
              <input type="file" id="avatar-upload" name="avatar" style="display: none;">
              <button type="button" class="avatar-btn" id="remove-avatar">
                <i class="fas fa-trash-alt"></i> Remove
              </button>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="iium_id">IIUM ID</label>
                <input type="text" class="form-control" id="iium_id" name="iium_id" value="EFGH5678" required>
                <small class="form-text text-muted">Format: 4 letters followed by 4 numbers (e.g., ABCD1234)</small>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" id="role" name="role" required>
                  <option value="supervisor">Supervisor</option>
                  <option value="teacher" selected>Teacher</option>
                  <option value="ajk">AJK</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="Nurul Hafizah" required>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="binti Abdullah" required>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="nurulh@iium.edu.my" required>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="+60 12-345-6789" required>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="position">Position/Title</label>
                <input type="text" class="form-control" id="position" name="position" value="Rehabilitation Services & Education">
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="centre_id">Centre</label>
                <select class="form-control" id="centre_id" name="centre_id" required>
                  <option value="1">IIUM Gombak</option>
                  <option value="2">IIUM Kuantan</option>
                  <option value="3">IIUM Pagoh</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="1985-04-12">
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="gender">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                  <option value="male">Male</option>
                  <option value="female" selected>Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3">123 Jalan Gombak, 53100 Kuala Lumpur</textarea>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="joined_date">Joined Date</label>
                <input type="date" class="form-control" id="joined_date" name="joined_date" value="2022-01-15">
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="active" selected>Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="leave">On Leave</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label>Expertise & Skills</label>
            <div class="checkbox-group">
              <div class="checkbox-wrapper">
                <input type="checkbox" id="skill_special_education" name="skills[]" value="special_education" checked>
                <label for="skill_special_education">Special Education</label>
              </div>
              <div class="checkbox-wrapper">
                <input type="checkbox" id="skill_speech_therapy" name="skills[]" value="speech_therapy" checked>
                <label for="skill_speech_therapy">Speech Therapy</label>
              </div>
              <div class="checkbox-wrapper">
                <input type="checkbox" id="skill_occupational_therapy" name="skills[]" value="occupational_therapy" checked>
                <label for="skill_occupational_therapy">Occupational Therapy</label>
              </div>
            </div>
            <div class="checkbox-group">
              <div class="checkbox-wrapper">
                <input type="checkbox" id="skill_child_psychology" name="skills[]" value="child_psychology" checked>
                <label for="skill_child_psychology">Child Psychology</label>
              </div>
              <div class="checkbox-wrapper">
                <input type="checkbox" id="skill_curriculum" name="skills[]" value="curriculum" checked>
                <label for="skill_curriculum">Curriculum Development</label>
              </div>
              <div class="checkbox-wrapper">
                <input type="checkbox" id="skill_sensory" name="skills[]" value="sensory" checked>
                <label for="skill_sensory">Sensory Integration</label>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="about">About</label>
            <textarea class="form-control" id="about" name="about" rows="4">Dr. Nurul Hafizah is a specialized educator with over 10 years of experience in special education and rehabilitation. She holds a PhD in Special Education from Universiti Kebangsaan Malaysia and has conducted extensive research on speech therapy for children with special needs.</textarea>
          </div>
          
          <h4 class="section-title">Emergency Contact</h4>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="emergency_name">Name</label>
                <input type="text" class="form-control" id="emergency_name" name="emergency_name" value="Ahmad Rahman">
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="emergency_relationship">Relationship</label>
                <input type="text" class="form-control" id="emergency_relationship" name="emergency_relationship" value="Spouse">
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="emergency_phone">Phone</label>
                <input type="text" class="form-control" id="emergency_phone" name="emergency_phone" value="+60 13-987-6543">
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="emergency_email">Email</label>
                <input type="email" class="form-control" id="emergency_email" name="emergency_email" value="ahmad.r@gmail.com">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
            <small class="form-text text-muted">Password must be at least 8 characters and include letters and numbers</small>
          </div>
          
          <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Leave blank to keep current password">
          </div>
          
          <div class="action-buttons">
            <a href="{{ route('admin.user.view', 2) }}" class="submit-btn cancel">Cancel</a>
            <button type="submit" class="submit-btn save">Save Changes</button>
          </div>
        </form>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
  
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
      
      // Remove avatar
      $('#remove-avatar').click(function() {
        Swal.fire({
          title: 'Remove Avatar',
          text: "Are you sure you want to remove the profile photo?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
          if (result.isConfirmed) {
            // Logic to remove avatar
            $('.edit-avatar img').attr('src', '{{ asset('images/default-avatar.png') }}');
            
            Swal.fire(
              'Removed!',
              'The profile photo has been removed.',
              'success'
            );
          }
        });
      });
      
      // Avatar upload preview
      $('#avatar-upload').change(function() {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $('.edit-avatar img').attr('src', e.target.result);
          }
          reader.readAsDataURL(file);
        }
      });
      
      // Form submission
      $('.edit-profile-form').submit(function(e) {
        e.preventDefault();
        
        // Validation logic can be added here
        
        // Simulate form submission
        Swal.fire({
          title: 'Saving Changes',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Simulate API request delay
        setTimeout(function() {
          Swal.fire({
            title: 'Success!',
            text: 'Profile information has been updated successfully.',
            icon: 'success',
            confirmButtonColor: '#32bdea'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = '{{ route('admin.user.view', 2) }}';
            }
          });
        }, 1500);
      });
    });
  </script>
</body>
</html>