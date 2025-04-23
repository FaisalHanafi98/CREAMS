<!-- resources/views/settings/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>System Settings - CREAMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
  <style>
    .settings-card {
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      margin-bottom: 25px;
      transition: all 0.3s ease;
    }
    
    .settings-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .settings-card .card-header {
      background: linear-gradient(135deg, #32bdea, #c850c0);
      color: white;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
      padding: 15px 20px;
    }
    
    .settings-card .card-header h5 {
      margin: 0;
      font-weight: 600;
    }
    
    .settings-icon {
      margin-right: 10px;
      background: rgba(255, 255, 255, 0.2);
      width: 32px;
      height: 32px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }
    
    .setting-group {
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }
    
    .setting-group:last-child {
      border-bottom: none;
    }
    
    .setting-group label {
      font-weight: 500;
      color: #333;
    }
    
    .color-picker {
      height: 38px;
      padding: 0;
      border: 1px solid #ced4da;
      border-radius: .25rem;
    }
    
    .submit-settings {
      background: linear-gradient(135deg, #32bdea, #c850c0);
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .submit-settings:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }
    
    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 34px;
    }
    
    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    
    input:checked + .slider {
      background: linear-gradient(135deg, #32bdea, #c850c0);
    }
    
    input:checked + .slider:before {
      transform: translateX(26px);
    }
  </style>
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
        @if(session('avatar'))
          <img src="{{ asset('storage/avatars/' . session('avatar')) }}" alt="User Avatar">
        @else
          <img src="{{ asset('images/admin-avatar.jpg') }}" alt="User Avatar">
        @endif
      </div>
      <div class="admin-info">
        <div class="admin-name">{{ Auth::user()->name }}</div>
        <div class="admin-role">{{ ucfirst(session('role')) }}</div>
      </div>
    </div>
    
    <ul class="nav-menu">
      <li>
        <a href="{{ route('dashboard') }}">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="{{ route('profile') }}">
          <i class="fas fa-user-circle"></i>
          <span>My Profile</span>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.users') }}">
          <i class="fas fa-users"></i>
          <span>Staffs</span>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.centres') }}">
          <i class="fas fa-building"></i>
          <span>Centres</span>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.assets') }}">
          <i class="fas fa-boxes"></i>
          <span>Assets</span>
        </a>
      </li>
      <li class="active">
        <a href="{{ route('admin.settings') }}">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
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
          <h1 class="page-title">System Settings</h1>
          <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <span class="current">Settings</span>
          </div>
        </div>
        
        <div class="header-actions">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="search" placeholder="Search...">
          </div>
          
          <div class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-count">3</span>
          </div>
          
          <div class="admin-dropdown">
            <div class="admin-dropdown-toggle">
              @if(session('avatar'))
                <img src="{{ asset('storage/avatars/' . session('avatar')) }}" alt="User">
              @else
                <img src="{{ asset('images/admin-avatar.jpg') }}" alt="User">
              @endif
              <span>{{ Auth::user()->name }}</span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="admin-dropdown-menu">
              <a href="{{ route('profile') }}">
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
    </div>
    
    <!-- Content -->
    <div class="content-section">
      <!-- Settings Form -->
      <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="row">
          <!-- General Settings -->
          <div class="col-lg-6">
            <div class="card settings-card">
              <div class="card-header d-flex align-items-center">
                <div class="settings-icon">
                  <i class="fas fa-globe"></i>
                </div>
                <h5 class="card-title">General Settings</h5>
              </div>
              <div class="card-body">
                <div class="setting-group">
                  <label for="site_name">Site Name</label>
                  <input type="text" class="form-control" id="site_name" name="site_name" value="{{ $settings['general']['site_name'] }}">
                </div>
                <div class="setting-group">
                  <label for="site_description">Site Description</label>
                  <textarea class="form-control" id="site_description" name="site_description" rows="3">{{ $settings['general']['site_description'] }}</textarea>
                </div>
                <div class="setting-group">
                  <label for="contact_email">Contact Email</label>
                  <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ $settings['general']['contact_email'] }}">
                </div>
                <div class="setting-group">
                  <label for="contact_phone">Contact Phone</label>
                  <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ $settings['general']['contact_phone'] }}">
                </div>
              </div>
            </div>
          </div>
          
          <!-- Appearance Settings -->
          <div class="col-lg-6">
            <div class="card settings-card">
              <div class="card-header d-flex align-items-center">
                <div class="settings-icon">
                  <i class="fas fa-paint-brush"></i>
                </div>
                <h5 class="card-title">Appearance Settings</h5>
              </div>
              <div class="card-body">
                <div class="setting-group">
                  <label for="primary_color">Primary Color</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">#</span>
                    </div>
                    <input type="text" class="form-control" id="primary_color" name="primary_color" value="{{ str_replace('#', '', $settings['appearance']['primary_color']) }}">
                    <div class="input-group-append">
                      <input type="color" class="color-picker" id="primary_color_picker" value="{{ $settings['appearance']['primary_color'] }}">
                    </div>
                  </div>
                </div>
                <div class="setting-group">
                  <label for="secondary_color">Secondary Color</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">#</span>
                    </div>
                    <input type="text" class="form-control" id="secondary_color" name="secondary_color" value="{{ str_replace('#', '', $settings['appearance']['secondary_color']) }}">
                    <div class="input-group-append">
                      <input type="color" class="color-picker" id="secondary_color_picker" value="{{ $settings['appearance']['secondary_color'] }}">
                    </div>
                  </div>
                </div>
                <div class="setting-group">
                  <label>Logo</label>
                  <div class="d-flex align-items-center">
                    <img src="{{ asset($settings['appearance']['logo_path']) }}" alt="Logo" class="img-thumbnail mr-3" style="width: 60px; height: 60px;">
                    <button type="button" class="btn btn-outline-primary">Change Logo</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Security Settings -->
          <div class="col-lg-6">
            <div class="card settings-card">
              <div class="card-header d-flex align-items-center">
                <div class="settings-icon">
                  <i class="fas fa-shield-alt"></i>
                </div>
                <h5 class="card-title">Security Settings</h5>
              </div>
              <div class="card-body">
                <div class="setting-group">
                  <label for="password_expiry_days">Password Expiry (Days)</label>
                  <input type="number" class="form-control" id="password_expiry_days" name="password_expiry_days" min="0" max="365" value="{{ $settings['security']['password_expiry_days'] }}">
                </div>
                <div class="setting-group">
                  <label for="session_timeout_minutes">Session Timeout (Minutes)</label>
                  <input type="number" class="form-control" id="session_timeout_minutes" name="session_timeout_minutes" min="5" max="120" value="{{ $settings['security']['session_timeout_minutes'] }}">
                </div>
                <div class="setting-group">
                  <div class="d-flex justify-content-between align-items-center">
                    <label for="allow_registration">Allow Registration</label>
                    <label class="switch">
                      <input type="checkbox" id="allow_registration" name="allow_registration" {{ $settings['security']['allow_registration'] ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
                <div class="setting-group">
                  <div class="d-flex justify-content-between align-items-center">
                    <label for="require_email_verification">Require Email Verification</label>
                    <label class="switch">
                      <input type="checkbox" id="require_email_verification" name="require_email_verification" {{ $settings['security']['require_email_verification'] ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notification Settings -->
          <div class="col-lg-6">
            <div class="card settings-card">
              <div class="card-header d-flex align-items-center">
                <div class="settings-icon">
                  <i class="fas fa-bell"></i>
                </div>
                <h5 class="card-title">Notification Settings</h5>
              </div>
              <div class="card-body">
                <div class="setting-group">
                  <div class="d-flex justify-content-between align-items-center">
                    <label for="email_notifications">Email Notifications</label>
                    <label class="switch">
                      <input type="checkbox" id="email_notifications" name="email_notifications" {{ $settings['notifications']['email_notifications'] ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
                <div class="setting-group">
                  <div class="d-flex justify-content-between align-items-center">
                    <label for="system_notifications">System Notifications</label>
                    <label class="switch">
                      <input type="checkbox" id="system_notifications" name="system_notifications" {{ $settings['notifications']['system_notifications'] ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
                <div class="setting-group">
                  <div class="d-flex justify-content-between align-items-center">
                    <label for="sms_notifications">SMS Notifications</label>
                    <label class="switch">
                      <input type="checkbox" id="sms_notifications" name="sms_notifications" {{ $settings['notifications']['sms_notifications'] ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center mt-4 mb-5">
          <button type="submit" class="submit-settings">
            <i class="fas fa-save mr-2"></i> Save Settings
          </button>
        </div>
      </form>
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
      
      // Color picker sync
      $('#primary_color_picker').on('input', function() {
        $('#primary_color').val($(this).val().replace('#', ''));
      });
      
      $('#secondary_color_picker').on('input', function() {
        $('#secondary_color').val($(this).val().replace('#', ''));
      });
      
      $('#primary_color').on('input', function() {
        $('#primary_color_picker').val('#' + $(this).val());
      });
      
      $('#secondary_color').on('input', function() {
        $('#secondary_color_picker').val('#' + $(this).val());
      });
    });
  </script>
</body>
</html>