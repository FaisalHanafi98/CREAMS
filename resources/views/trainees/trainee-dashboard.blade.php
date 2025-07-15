<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Base Styles -->
    <style>
        :root {
          --primary-color: #32bdea;
          --secondary-color: #c850c0;
          --primary-gradient: linear-gradient(-135deg, var(--primary-color), var(--secondary-color));
          --secondary-gradient: linear-gradient(-135deg, var(--secondary-color), var(--primary-color));
          --dark-color: #1a2a3a;
          --light-color: #ffffff;
          --text-color: #444444;
          --light-bg: #f8f9fa;
          --sidebar-width: 260px;
          --sidebar-collapsed-width: 70px;
          --header-height: 60px;
          --transition-speed: 0.3s;
        }
        
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: 'Poppins', sans-serif;
        }
        
        body {
          background-color: var(--light-bg);
          color: var(--text-color);
          overflow-x: hidden;
        }
        
        /* Sidebar styles */
        .sidebar {
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          width: var(--sidebar-width);
          background: var(--dark-color);
          color: var(--light-color);
          transition: all var(--transition-speed) ease;
          z-index: 1000;
          box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.collapsed {
          width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
          display: flex;
          align-items: center;
          padding: 20px;
          height: var(--header-height);
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header .toggle-btn {
          width: 30px;
          height: 30px;
          display: flex;
          justify-content: center;
          align-items: center;
          background: rgba(255, 255, 255, 0.1);
          border-radius: 50%;
          margin-left: auto;
          cursor: pointer;
          transition: all var(--transition-speed) ease;
        }
        
        .sidebar.collapsed .toggle-btn {
          transform: rotate(180deg);
        }
        
        .logo {
          display: flex;
          align-items: center;
        }
        
        .logo img {
          width: 40px;
          height: 40px;
          border-radius: 10px;
          object-fit: cover;
        }
        
        .logo-text {
          margin-left: 15px;
          font-weight: 600;
          font-size: 20px;
          white-space: nowrap;
          transition: opacity var(--transition-speed) ease;
        }
        
        .sidebar.collapsed .logo-text {
          opacity: 0;
          pointer-events: none;
        }
        
        .admin-profile {
          padding: 20px;
          display: flex;
          align-items: center;
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-avatar {
          width: 50px;
          height: 50px;
          border-radius: 15px;
          overflow: hidden;
          background: var(--primary-gradient);
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .admin-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
        
        .admin-info {
          margin-left: 15px;
          transition: opacity var(--transition-speed) ease;
        }
        
        .sidebar.collapsed .admin-info {
          opacity: 0;
          pointer-events: none;
        }
        
        .admin-name {
          font-weight: 600;
          font-size: 16px;
          margin-bottom: 3px;
        }
        
        .admin-role {
          font-size: 12px;
          color: rgba(255, 255, 255, 0.7);
          padding: 3px 10px;
          background: rgba(255, 255, 255, 0.1);
          border-radius: 20px;
          display: inline-block;
        }
        
        .nav-menu {
          padding: 20px 0;
          list-style: none;
        }
        
        .nav-menu li {
          position: relative;
        }
        
        .nav-menu li a {
          display: flex;
          align-items: center;
          padding: 12px 20px;
          color: var(--light-color);
          text-decoration: none;
          font-size: 14px;
          transition: all var(--transition-speed) ease;
        }
        
        .nav-menu li a:hover {
          background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-menu li.active a {
          background: var(--primary-gradient);
        }
        
        .nav-menu li.active a::before {
          content: '';
          position: absolute;
          right: 0;
          top: 0;
          height: 100%;
          width: 4px;
          background: var(--secondary-color);
        }
        
        .nav-menu li a i {
          min-width: 30px;
          font-size: 18px;
          display: flex;
          justify-content: center;
        }
        
        .nav-menu li a span {
          margin-left: 15px;
          transition: opacity var(--transition-speed) ease;
        }
        
        .sidebar.collapsed .nav-menu li a span {
          opacity: 0;
          pointer-events: none;
        }
        
        .tooltip-sidebar {
          position: absolute;
          left: 100%;
          top: 50%;
          transform: translateY(-50%);
          background: var(--dark-color);
          color: var(--light-color);
          padding: 5px 10px;
          border-radius: 5px;
          font-size: 12px;
          white-space: nowrap;
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
          opacity: 0;
          pointer-events: none;
          transition: all var(--transition-speed) ease;
        }
        
        .sidebar.collapsed .nav-menu li:hover .tooltip-sidebar {
          opacity: 1;
          left: calc(var(--sidebar-collapsed-width) + 10px);
        }
        
        .logout-container {
          margin-top: auto;
          padding: 20px;
        }
        
        .logout-btn {
          width: 100%;
          padding: 10px 20px;
          display: flex;
          align-items: center;
          background: rgba(255, 255, 255, 0.1);
          border: none;
          color: var(--light-color);
          cursor: pointer;
          transition: all var(--transition-speed) ease;
          text-align: left;
          border-radius: 8px;
        }
        
        .logout-btn:hover {
          background: rgba(255, 60, 60, 0.15);
          color: #ff6b6b;
        }
        
        .logout-btn i {
          min-width: 30px;
          font-size: 18px;
          display: flex;
          justify-content: center;
        }
        
        .logout-btn span {
          margin-left: 15px;
          transition: opacity var(--transition-speed) ease;
        }
        
        .sidebar.collapsed .logout-btn span {
          opacity: 0;
          pointer-events: none;
        }
        
        /* Main content styles */
        .main-content {
          margin-left: var(--sidebar-width);
          padding: 20px;
          transition: margin var(--transition-speed) ease;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
        }
        
        .main-content.expanded {
          margin-left: var(--sidebar-collapsed-width);
        }
        
        .header {
          margin-bottom: 30px;
        }
        
        .header-content {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
        }
        
        .page-info {
          flex: 1;
        }
        
        .page-title {
          font-size: 28px;
          font-weight: 700;
          background: var(--primary-gradient);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          margin-bottom: 5px;
        }
        
        .breadcrumb {
          display: flex;
          align-items: center;
          font-size: 14px;
          color: rgba(0, 0, 0, 0.5);
          background: none;
          padding: 0;
          margin: 0;
        }
        
        .breadcrumb a {
          color: var(--primary-color);
          text-decoration: none;
          transition: all var(--transition-speed) ease;
        }
        
        .breadcrumb a:hover {
          color: var(--secondary-color);
        }
        
        .breadcrumb .separator {
          margin: 0 10px;
        }
        
        .breadcrumb .current {
          color: rgba(0, 0, 0, 0.5);
        }
        
        .header-actions {
          display: flex;
          align-items: center;
          gap: 20px;
        }
        
        .search-box {
          position: relative;
        }
        
        .search-box input {
          width: 250px;
          height: 40px;
          border-radius: 20px;
          border: 1px solid rgba(0, 0, 0, 0.1);
          padding: 0 15px 0 40px;
          font-size: 14px;
          transition: all var(--transition-speed) ease;
        }
        
        .search-box input:focus {
          width: 300px;
          border-color: var(--primary-color);
          box-shadow: 0 0 15px rgba(50, 189, 234, 0.1);
          outline: none;
        }
        
        .search-box i {
          position: absolute;
          left: 15px;
          top: 50%;
          transform: translateY(-50%);
          color: rgba(0, 0, 0, 0.3);
        }
        
        .notification-bell {
          position: relative;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: var(--light-color);
          border: 1px solid rgba(0, 0, 0, 0.1);
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: all var(--transition-speed) ease;
        }
        
        .notification-bell:hover {
          background: var(--primary-gradient);
          color: var(--light-color);
          border-color: transparent;
        }
        
        .notification-count {
          position: absolute;
          top: -5px;
          right: -5px;
          width: 20px;
          height: 20px;
          border-radius: 50%;
          background: #ff4757;
          color: var(--light-color);
          font-size: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 600;
        }
        
        .admin-dropdown {
          position: relative;
        }
        
        .admin-dropdown-toggle {
          display: flex;
          align-items: center;
          cursor: pointer;
          padding: 5px 10px;
          border-radius: 30px;
          background: var(--light-color);
          border: 1px solid rgba(0, 0, 0, 0.1);
          transition: all var(--transition-speed) ease;
        }
        
        .admin-dropdown-toggle:hover {
          background: var(--light-bg);
        }
        
        .admin-dropdown-toggle img {
          width: 30px;
          height: 30px;
          border-radius: 50%;
          object-fit: cover;
          margin-right: 10px;
        }
        
        .admin-dropdown-toggle span {
          font-size: 14px;
          margin-right: 10px;
        }
        
        .admin-dropdown-menu {
          position: absolute;
          top: calc(100% + 10px);
          right: 0;
          background: var(--light-color);
          border-radius: 10px;
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
          min-width: 200px;
          z-index: 100;
          opacity: 0;
          pointer-events: none;
          transform: translateY(10px);
          transition: all var(--transition-speed) ease;
        }
        
        .admin-dropdown-menu.show {
          opacity: 1;
          pointer-events: all;
          transform: translateY(0);
        }
        
        .admin-dropdown-menu a,
        .admin-dropdown-menu button {
          display: flex;
          align-items: center;
          padding: 12px 20px;
          color: var(--text-color);
          text-decoration: none;
          font-size: 14px;
          transition: all var(--transition-speed) ease;
          border: none;
          background: transparent;
          width: 100%;
          text-align: left;
          cursor: pointer;
        }
        
        .admin-dropdown-menu a:hover,
        .admin-dropdown-menu button:hover {
          background: var(--light-bg);
          color: var(--primary-color);
        }
        
        .admin-dropdown-menu a i,
        .admin-dropdown-menu button i {
          margin-right: 10px;
          font-size: 16px;
        }
        
        .admin-dropdown-menu a:first-child {
          border-top-left-radius: 10px;
          border-top-right-radius: 10px;
        }
        
        .admin-dropdown-menu a:last-child,
        .admin-dropdown-menu form:last-child button {
          border-bottom-left-radius: 10px;
          border-bottom-right-radius: 10px;
        }
        
        .page-actions {
          display: flex;
          align-items: center;
          gap: 15px;
        }
        
        .action-btn {
          padding: 8px 20px;
          border-radius: 8px;
          border: 1px solid rgba(0, 0, 0, 0.1);
          background: var(--light-color);
          display: flex;
          align-items: center;
          cursor: pointer;
          transition: all var(--transition-speed) ease;
          font-size: 14px;
          text-decoration: none;
          color: var(--text-color);
        }
        
        .action-btn:hover {
          background: var(--light-bg);
          border-color: rgba(0, 0, 0, 0.2);
          color: var(--primary-color);
        }
        
        .action-btn.primary {
          background: var(--primary-gradient);
          color: var(--light-color);
          border-color: transparent;
        }
        
        .action-btn.primary:hover {
          box-shadow: 0 5px 15px rgba(50, 189, 234, 0.2);
          transform: translateY(-2px);
        }
        
        .action-btn i {
          margin-right: 8px;
        }
        
        /* Footer styles */
        .dashboard-footer {
          margin-top: auto;
          padding: 20px 0 0;
          border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .footer-content {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 15px 0;
        }
        
        .footer-logo {
          display: flex;
          align-items: center;
        }
        
        .footer-logo img {
          width: 30px;
          height: 30px;
          margin-right: 10px;
        }
        
        .footer-logo span {
          font-weight: 600;
        }
        
        .footer-text {
          font-size: 14px;
          color: rgba(0, 0, 0, 0.5);
        }
        
        .footer-links {
          display: flex;
          gap: 20px;
        }
        
        .footer-link {
          font-size: 14px;
          color: var(--primary-color);
          text-decoration: none;
          transition: all var(--transition-speed) ease;
        }
        
        .footer-link:hover {
          color: var(--secondary-color);
        }
        
        /* Content section */
        .content-section {
          flex: 1;
        }
        
        /* Alert styles */
        .alert {
          border: none;
          border-radius: 10px;
          padding: 15px 20px;
          margin-bottom: 20px;
          display: flex;
          align-items: center;
        }
        
        .alert i {
          margin-right: 10px;
          font-size: 20px;
        }
        
        .alert-success {
          background-color: rgba(40, 167, 69, 0.1);
          color: #28a745;
        }
        
        .alert-danger {
          background-color: rgba(220, 53, 69, 0.1);
          color: #dc3545;
        }
        
        .alert-warning {
          background-color: rgba(255, 193, 7, 0.1);
          color: #ffc107;
        }
        
        .alert-info {
          background-color: rgba(23, 162, 184, 0.1);
          color: #17a2b8;
        }
        
        /* Animations */
        @keyframes fadeIn {
          from {
            opacity: 0;
          }
          to {
            opacity: 1;
          }
        }
        
        @keyframes slideInUp {
          from {
            transform: translateY(20px);
            opacity: 0;
          }
          to {
            transform: translateY(0);
            opacity: 1;
          }
        }
        
        .fade-in {
          animation: fadeIn 0.5s ease-in-out;
        }
        
        .slide-in-up {
          animation: slideInUp 0.5s ease-in-out;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
          .search-box input {
            width: 180px;
          }
          
          .search-box input:focus {
            width: 220px;
          }
        }
        
        @media (max-width: 768px) {
          .sidebar {
            width: var(--sidebar-collapsed-width);
          }
          
          .sidebar .logo-text,
          .sidebar .admin-info,
          .sidebar .nav-menu li a span,
          .sidebar .logout-btn span {
            opacity: 0;
            pointer-events: none;
          }
          
          .main-content {
            margin-left: var(--sidebar-collapsed-width);
          }
          
          .header-content {
            flex-direction: column;
            align-items: flex-start;
          }
          
          .header-actions {
            width: 100%;
            margin-top: 20px;
          }
          
          .search-box {
            flex: 1;
          }
          
          .search-box input {
            width: 100%;
          }
          
          .search-box input:focus {
            width: 100%;
          }
          
          .footer-content {
            flex-direction: column;
            gap: 15px;
            text-align: center;
          }
          
          .footer-logo {
            justify-content: center;
          }
          
          .footer-links {
            justify-content: center;
          }
        }
        
        @media (max-width: 576px) {
          .page-title {
            font-size: 24px;
          }
          
          .header-actions {
            gap: 10px;
          }
          
          .notification-bell,
          .admin-dropdown-toggle {
            width: 36px;
            height: 36px;
          }
          
          .admin-dropdown-toggle span,
          .admin-dropdown-toggle i {
            display: none;
          }
          
          .page-actions {
            flex-wrap: wrap;
          }
          
          .action-btn {
            flex: 1;
            justify-content: center;
          }
        }
    </style>
    
    <!-- Additional CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
    
    <!-- Page-specific CSS -->
    @yield('styles')
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
            @if(Route::has('traineeactivity'))
            <li class="{{ request()->routeIs('traineeactivity') ? 'active' : '' }}">
                <a href="{{ route('traineeactivity') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Activities</span>
                    <div class="tooltip-sidebar">Trainee Activities</div>
                </a>
            </li>
            @else
            <li class="{{ request()->routeIs('activities.*') ? 'active' : '' }}">
                <a href="{{ route('activities.index') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Activities</span>
                    <div class="tooltip-sidebar">Activities</div>
                </a>
            </li>
            @endif
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
                    <h1 class="page-title">@yield('page-title')</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <span class="current">@yield('breadcrumb')</span>
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
            
            @yield('page-actions')
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
            
            <!-- Main Content -->
            @yield('content')
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
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
    
    <!-- Page-specific Scripts -->
    @yield('scripts')
</body>
</html>