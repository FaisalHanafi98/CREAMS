<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ucfirst(session('role')) . ' Dashboard - CREAMS')</title>

    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --success-color: #2ed573;
            --danger-color: #ff4757;
            --warning-color: #ffa502;
            --info-color: #1e90ff;
            --dark-color: #1a2a3a;
            --light-color: #f8f9fa;
            --border-color: #e9ecef;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            overflow-x: hidden;
        }

        /* Topbar styles */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
            display: flex;
            align-items: center;
            padding: 0 15px 0 0;
        }

        .sidebar-toggle {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .topbar-logo {
            width: 190px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .topbar-logo a {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: 1px;
            text-decoration: none;
            width: 100%;
            height: 100%;
        }

        .topbar-logo i {
            margin-right: 10px;
        }

        .topbar-title {
            margin-left: 20px;
            font-size: 18px;
            font-weight: 600;
            background-image: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            padding-bottom: 3px;
        }

        .topbar-spacer {
            flex-grow: 1;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Search box */
        .search-container {
            position: relative;
            margin-right: 15px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: var(--light-color);
            border-radius: 20px;
            height: 38px;
            width: 200px;
            overflow: hidden;
        }

        .search-box input {
            flex-grow: 1;
            border: none;
            background: transparent;
            padding: 0 15px;
            height: 100%;
            outline: none;
        }

        .btn-search {
            border: none;
            background: transparent;
            padding: 0 15px;
            height: 100%;
            color: #777;
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 5px;
            z-index: 1000;
            display: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            transition: all var(--transition-speed) ease;
            border-bottom: 1px solid var(--border-color);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background-color: rgba(50, 189, 234, 0.05);
            text-decoration: none;
        }

        .search-result-icon {
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
        }

        .search-result-content {
            flex-grow: 1;
        }

        .search-result-name {
            font-weight: 500;
            margin-bottom: 2px;
        }

        .search-result-meta {
            font-size: 12px;
            color: #777;
        }

        .search-no-results {
            padding: 15px;
            text-align: center;
            color: #777;
        }

        /* Mobile search toggle */
        .search-mobile-toggle {
            display: none;
            width: 38px;
            height: 38px;
            background: var(--light-color);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            color: #777;
            cursor: pointer;
        }

        .mobile-search {
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            background: white;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 999;
        }

        .mobile-search.show {
            display: block;
        }

        /* Notifications dropdown */
        .notifications-dropdown {
            position: relative;
        }

        .icon-button {
            width: 38px;
            height: 38px;
            background: var(--light-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .notification-menu.show {
            display: block;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .notification-icon.primary {
            background: var(--primary-color);
        }

        .notification-icon.success {
            background: var(--success-color);
        }

        .notification-icon.warning {
            background: var(--warning-color);
        }

        .notification-icon.danger {
            background: var(--danger-color);
        }

        .smallest {
            font-size: 10px;
        }

        /* User profile */
        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            background: var(--light-color);
            border-radius: 20px;
            padding: 6px 6px 6px 12px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 10px;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            margin-right: 8px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .user-role {
            font-size: 12px;
            color: #888;
            margin-top: -2px;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 250px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .user-dropdown.show {
            display: block;
        }

        .dropdown-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
        }

        .user-details {
            display: flex;
            align-items: center;
        }

        .dropdown-item {
            padding: 12px 15px;
            color: #333;
            transition: all var(--transition-speed) ease;
        }

        .dropdown-item:hover {
            background-color: rgba(50, 189, 234, 0.05);
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 0;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            height: calc(100% - 60px);
            width: 250px;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 998;
            overflow-y: auto;
            transition: width var(--transition-speed) ease;
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #555;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            position: relative;
        }

        .sidebar-link:hover {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.05);
            text-decoration: none;
        }

        .sidebar-link.active {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.1);
            font-weight: 500;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }

        .sidebar-icon {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            font-size: 16px;
        }

        .sidebar-text {
            flex-grow: 1;
            font-size: 14px;
        }

        .sidebar-toggle-submenu {
            margin-left: auto;
            font-size: 12px;
            transition: transform var(--transition-speed) ease;
        }

        .sidebar-divider {
            height: 1px;
            background: var(--border-color);
            margin: 15px 0;
        }

        .sidebar-title {
            padding: 0 20px;
            margin: 15px 0 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 0.5px;
        }

        .sidebar-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-speed) ease;
        }

        .submenu-open .sidebar-submenu {
            max-height: 200px;
        }

        .submenu-open .sidebar-toggle-submenu {
            transform: rotate(90deg);
        }

        .sidebar-submenu-link {
            display: block;
            padding: 10px 20px 10px 50px;
            color: #555;
            text-decoration: none;
            font-size: 13px;
            transition: all var(--transition-speed) ease;
        }

        .sidebar-submenu-link:hover {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.05);
            text-decoration: none;
        }

        .sidebar-submenu-link.active {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.1);
            font-weight: 500;
        }

        .feature-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            background-color: var(--warning-color);
            color: white;
            margin-left: auto;
        }

        /* Collapsed sidebar styles */
        body.sidebar-collapsed .sidebar {
            width: 60px;
        }

        body.sidebar-collapsed .sidebar-text,
        body.sidebar-collapsed .sidebar-title,
        body.sidebar-collapsed .sidebar-toggle-submenu,
        body.sidebar-collapsed .feature-badge {
            display: none;
        }

        body.sidebar-collapsed .sidebar-link {
            justify-content: center;
            padding: 15px;
        }

        body.sidebar-collapsed .sidebar-icon {
            margin-right: 0;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 60px;
        }

        body.sidebar-collapsed .sidebar-item:hover .sidebar-submenu {
            display: none;
        }

        /* Main content styles */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 20px;
            min-height: calc(100vh - 60px);
            transition: margin-left var(--transition-speed) ease;
        }

        /* Dashboard header */
        .dashboard-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .dashboard-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-color);
        }

        .dashboard-subtitle {
            color: #6c757d;
            font-size: 14px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 13px;
            padding: 0;
            margin: 0;
            background: none;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb .separator {
            margin: 0 8px;
            color: #6c757d;
        }

        .breadcrumb .current {
            color: #6c757d;
        }

        /* Date display */
        .date-display {
            display: inline-flex;
            align-items: center;
            background: var(--light-color);
            border-radius: 20px;
            padding: 8px 15px;
            color: #6c757d;
            font-size: 14px;
        }

        .date-display i {
            margin-right: 8px;
        }

        /* Recent access card */
        .recent-access-card {
            border-radius: 10px;
            overflow: hidden;
            transition: all var(--transition-speed) ease;
        }

        .recent-access-header {
            background: var(--light-color);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .recent-access-title {
            margin: 0;
            font-weight: 600;
        }

        .section-action {
            color: #6c757d;
            text-decoration: none;
            font-size: 13px;
            transition: all var(--transition-speed) ease;
        }

        .section-action:hover {
            color: var(--primary-color);
            text-decoration: none;
        }

        .recent-items {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .recent-item {
            border-bottom: 1px solid var(--border-color);
            transition: all var(--transition-speed) ease;
        }

        .recent-link {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: #555;
            text-decoration: none;
        }

        .recent-link:hover {
            background-color: rgba(50, 189, 234, 0.05);
            color: #555;
            text-decoration: none;
        }

        .recent-icon {
            width: 40px;
            height: 40px;
            background: var(--light-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            margin-right: 15px;
        }

        .recent-content {
            flex-grow: 1;
        }

        .recent-name {
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .recent-meta {
            color: #6c757d;
            font-size: 12px;
        }

        .recent-time {
            color: #6c757d;
            font-size: 12px;
            margin-left: 15px;
        }

        /* Stats cards */
        .stats-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            transition: all var(--transition-speed) ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Rehab categories */
        .rehab-category {
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--light-color);
            transition: all var(--transition-speed) ease;
        }

        .rehab-category:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .rehab-category-autism {
            background: linear-gradient(to right, rgba(79, 172, 254, 0.15), rgba(0, 242, 254, 0.15));
            border-left: 3px solid #4facfe;
        }

        .rehab-category-hearing {
            background: linear-gradient(to right, rgba(255, 154, 158, 0.15), rgba(250, 208, 196, 0.15));
            border-left: 3px solid #ff9a9e;
        }

        .rehab-category-visual {
            background: linear-gradient(to right, rgba(51, 204, 255, 0.15), rgba(0, 196, 154, 0.15));
            border-left: 3px solid #33ccff;
        }

        .rehab-category-physical {
            background: linear-gradient(to right, rgba(246, 211, 101, 0.15), rgba(253, 160, 133, 0.15));
            border-left: 3px solid #f6d365;
        }

        .rehab-category-learning {
            background: linear-gradient(to right, rgba(200, 80, 192, 0.15), rgba(65, 88, 208, 0.15));
            border-left: 3px solid #c850c0;
        }

        .rehab-category-speech {
            background: linear-gradient(to right, rgba(161, 140, 209, 0.15), rgba(251, 194, 235, 0.15));
            border-left: 3px solid #a18cd1;
        }

        .rehab-category-title {
            font-weight: 500;
            font-size: 14px;
        }

        .rehab-category-count {
            font-weight: 600;
            font-size: 18px;
            color: #555;
        }

        /* Media queries */
        @media (max-width: 991px) {
            body {
                overflow-x: hidden;
            }

            body:not(.sidebar-collapsed) .sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-collapsed .sidebar {
                transform: translateX(0);
                width: 250px;
            }

            body.sidebar-collapsed .sidebar-text,
            body.sidebar-collapsed .sidebar-title,
            body.sidebar-collapsed .sidebar-toggle-submenu,
            body.sidebar-collapsed .feature-badge {
                display: block;
            }

            body.sidebar-collapsed .sidebar-link {
                justify-content: flex-start;
                padding: 12px 20px;
            }

            body.sidebar-collapsed .sidebar-icon {
                margin-right: 10px;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }

            .topbar-title {
                max-width: 200px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .search-container.d-none.d-md-block {
                display: none !important;
            }

            .search-mobile-toggle {
                display: flex;
            }
        }

        @media (max-width: 767px) {
            .user-info.d-none.d-md-flex {
                display: none !important;
            }

            .user-profile {
                padding: 6px;
            }

            .user-avatar {
                margin-right: 0;
            }

            .notification-menu,
            .user-dropdown {
                width: 280px;
            }

            .topbar-title {
                font-size: 16px;
                max-width: 150px;
            }

            .categories-container {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </div>

        <div class="topbar-logo">
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-clinic-medical"></i>
                <span>CREAMS</span>
            </a>
        </div>

        <div class="topbar-title">
            <!-- Dynamic title based on current page -->
            @if (Route::currentRouteName() == 'traineeshome' || strpos(Route::currentRouteName(), 'trainee') !== false)
                Trainees Management
            @elseif(Route::currentRouteName() == 'rehabilitation.categories' ||
                    strpos(Route::currentRouteName(), 'rehabilitation') !== false)
                Rehabilitation Categories
            @else
                {{ ucfirst(session('role')) }} Dashboard
            @endif
        </div>

        <div class="topbar-spacer"></div>

        <div class="search-container d-none d-md-block">
            <form id="searchForm" action="{{ route('search') }}" method="GET">
                <div class="search-box">
                    <input type="text" name="query" id="globalSearch" placeholder="Search..." class="form-control">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <div id="searchResults" class="search-results-dropdown"></div>
        </div>

        <div class="topbar-right">
            <div class="search-mobile-toggle d-md-none">
                <i class="fas fa-search"></i>
            </div>

            <div class="mobile-search">
                <form action="{{ route('search') }}" method="GET" class="mb-0">
                    <div class="input-group">
                        <input type="text" name="query" class="form-control" placeholder="Search...">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="notifications-dropdown" id="notificationToggle">
                <div class="icon-button">
                    <i class="fas fa-bell"></i>
                    @if (isset($unreadNotifications) && $unreadNotifications > 0)
                        <span class="notification-count">{{ $unreadNotifications }}</span>
                    @endif
                </div>
                <div class="notification-menu" id="notificationMenu">
                    <!-- Notifications will be loaded via JavaScript -->
                </div>
            </div>

            <div class="user-profile" id="userProfileToggle">
                <div class="user-avatar">
                    <img src="{{ session('user_avatar') ?? asset('images/default-avatar.png') }}" alt="User Avatar">
                </div>
                <div class="user-info d-none d-md-flex">
                    <div class="user-name">{{ session('name') ?? 'User' }}</div>
                    <div class="user-role">{{ ucfirst(session('role') ?? 'User') }}</div>
                </div>
                <div class="dropdown-menu user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <div class="user-details d-flex align-items-center">
                            <div class="user-avatar mr-3">
                                <img src="{{ session('user_avatar') ?? asset('images/default-avatar.png') }}"
                                    alt="User Avatar">
                            </div>
                            <div class="user-info">
                                <div class="user-name">{{ session('name') ?? 'User' }}</div>
                                <div class="user-role">{{ ucfirst(session('role') ?? 'User') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile') }}" class="dropdown-item">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                    <a href="{{ route('notifications.index') }}" class="dropdown-item">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                    <a href="{{ route(session('role') . '.settings') }}" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="fas fa-home"></i></span>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('profile') }}"
                    class="sidebar-link {{ Route::currentRouteName() == 'profile' ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="fas fa-user-circle"></i></span>
                    <span class="sidebar-text">My Profile</span>
                </a>
            </li>

            <li class="sidebar-divider"></li>

            <li class="sidebar-title">Management</li>

            @if (in_array(session('role'), ['admin', 'supervisor']))
                <li
                    class="sidebar-item {{ strpos(Route::currentRouteName(), '.users') !== false ? 'submenu-open' : '' }}">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon"><i class="fas fa-users"></i></span>
                        <span class="sidebar-text">Staffs</span>
                        <i class="fas fa-chevron-right sidebar-toggle-submenu"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route(session('role') . '.users') }}"
                                class="sidebar-submenu-link {{ Route::currentRouteName() == session('role') . '.users' ? 'active' : '' }}">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('auth.registerpage') }}"
                                class="sidebar-submenu-link {{ Route::currentRouteName() == 'auth.registerpage' ? 'active' : '' }}">
                                Registration
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <li
                class="sidebar-item {{ strpos(Route::currentRouteName(), 'trainee') !== false ? 'submenu-open' : '' }}">
                <a href="#" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-user-graduate"></i></span>
                    <span class="sidebar-text">Trainees</span>
                    <i class="fas fa-chevron-right sidebar-toggle-submenu"></i>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('traineeshome') }}"
                            class="sidebar-submenu-link {{ Route::currentRouteName() == 'traineeshome' ? 'active' : '' }}">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('traineesregistrationpage') }}"
                            class="sidebar-submenu-link {{ Route::currentRouteName() == 'traineesregistrationpage' ? 'active' : '' }}">
                            Registration
                        </a>
                    </li>
                </ul>
            </li>

            <li
                class="sidebar-item {{ strpos(Route::currentRouteName(), 'activities') !== false || strpos(Route::currentRouteName(), 'rehabilitation') !== false ? 'submenu-open' : '' }}">
                <a href="#" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-heartbeat"></i></span>
                    <span class="sidebar-text">Activities</span>
                    <i class="fas fa-chevron-right sidebar-toggle-submenu"></i>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('rehabilitation.categories') }}"
                            class="sidebar-submenu-link {{ Route::currentRouteName() == 'rehabilitation.categories' ? 'active' : '' }}">
                            Categories
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('activities.index') }}"
                            class="sidebar-submenu-link {{ Route::currentRouteName() == 'activities.index' ? 'active' : '' }}">
                            Schedule
                        </a>
                    </li>
                </ul>
            </li>

            <li
                class="sidebar-item {{ strpos(Route::currentRouteName(), '.centres') !== false || strpos(Route::currentRouteName(), '.assets') !== false ? 'submenu-open' : '' }}">
                <a href="#" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-building"></i></span>
                    <span class="sidebar-text">Centres</span>
                    <i class="fas fa-chevron-right sidebar-toggle-submenu"></i>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route(session('role') . '.centres') }}"
                            class="sidebar-submenu-link {{ Route::currentRouteName() == session('role') . '.centres' ? 'active' : '' }}">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route(session('role') . '.asset-types.index') }}"
                            class="sidebar-submenu-link {{ Route::currentRouteName() == session('role') . '.assets' ? 'active' : '' }}">
                            Assets
                        </a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-divider"></li>

            <li class="sidebar-title">Reports & Settings</li>

            <li class="sidebar-item">
                <a href="{{ route(session('role') . '.reports') }}"
                    class="sidebar-link {{ Route::currentRouteName() == session('role') . '.reports' ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="fas fa-chart-bar"></i></span>
                    <span class="sidebar-text">Reports</span>
                    <span class="feature-badge">Development</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="{{ route(session('role') . '.settings') }}"
                    class="sidebar-link {{ Route::currentRouteName() == session('role') . '.settings' ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="fas fa-cog"></i></span>
                    <span class="sidebar-text">Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-collapsed');

                    // Store preference in localStorage
                    localStorage.setItem('sidebar-collapsed', document.body.classList.contains(
                        'sidebar-collapsed'));
                });
            }

            // Check if sidebar was previously collapsed
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

            // Handle sidebar submenu toggles
            const submenuLinks = document.querySelectorAll('.sidebar-link');
            submenuLinks.forEach(link => {
                if (link.nextElementSibling && link.nextElementSibling.classList.contains(
                        'sidebar-submenu')) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const parent = this.parentElement;

                        // Toggle submenu-open class
                        parent.classList.toggle('submenu-open');

                        // Close other submenus
                        const siblings = Array.from(parent.parentElement.children).filter(el =>
                            el !== parent);
                        siblings.forEach(sibling => {
                            sibling.classList.remove('submenu-open');
                        });
                    });
                }
            });

            // Handle notifications dropdown
            const notificationToggle = document.getElementById('notificationToggle');
            const notificationMenu = document.getElementById('notificationMenu');

            if (notificationToggle && notificationMenu) {
                notificationToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationMenu.classList.toggle('show');

                    // Close user dropdown if open
                    const userDropdown = document.getElementById('userDropdown');
                    if (userDropdown) {
                        userDropdown.classList.remove('show');
                    }

                    // Load notifications if menu is showing
                    if (notificationMenu.classList.contains('show')) {
                        loadNotifications();
                    }
                });
            }

            // Handle user profile dropdown
            const userProfileToggle = document.getElementById('userProfileToggle');
            const userDropdown = document.getElementById('userDropdown');

            if (userProfileToggle && userDropdown) {
                userProfileToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');

                    // Close notification menu if open
                    if (notificationMenu) {
                        notificationMenu.classList.remove('show');
                    }
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                if (notificationMenu) {
                    notificationMenu.classList.remove('show');
                }

                if (userDropdown) {
                    userDropdown.classList.remove('show');
                }

                // Also close mobile search if open
                const mobileSearch = document.querySelector('.mobile-search');
                if (mobileSearch) {
                    mobileSearch.classList.remove('show');
                }
            });

            // Prevent dropdown close when clicking inside
            [notificationMenu, userDropdown].forEach(el => {
                if (el) {
                    el.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }
            });

            // Mobile search toggle
            const searchMobileToggle = document.querySelector('.search-mobile-toggle');
            const mobileSearch = document.querySelector('.mobile-search');

            if (searchMobileToggle && mobileSearch) {
                searchMobileToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileSearch.classList.toggle('show');
                });

                mobileSearch.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // Search functionality
            const globalSearch = document.getElementById('globalSearch');
            const searchResults = document.getElementById('searchResults');

            if (globalSearch && searchResults) {
                let searchTimer;

                globalSearch.addEventListener('input', function() {
                    const query = this.value;
                    clearTimeout(searchTimer);

                    // Don't search for very short queries
                    if (query.length < 2) {
                        searchResults.innerHTML = '';
                        searchResults.style.display = 'none';
                        return;
                    }

                    // Set a small delay to avoid too many requests
                    searchTimer = setTimeout(function() {
                        // This would normally be an AJAX request to the server
                        // Mock response for demo
                        const mockResults = [{
                                name: 'John Doe',
                                role: 'Trainee',
                                location: 'Main Centre',
                                url: '#trainee-1'
                            },
                            {
                                name: 'Training Session: Social Skills',
                                role: 'Activity',
                                location: 'Main Centre',
                                url: '#activity-1'
                            },
                            {
                                name: 'Communication Tools',
                                role: 'Asset',
                                location: 'East Wing',
                                url: '#asset-1'
                            }
                        ];

                        searchResults.innerHTML = '';

                        if (mockResults.length === 0) {
                            searchResults.innerHTML =
                                '<div class="search-no-results"><p>No results found</p></div>';
                            searchResults.style.display = 'block';
                            return;
                        }

                        mockResults.forEach(function(item) {
                            // Create an icon based on role
                            let icon = 'file';
                            if (item.role.toLowerCase() === 'trainee') {
                                icon = 'user-graduate';
                            } else if (item.role.toLowerCase() === 'activity') {
                                icon = 'calendar-alt';
                            } else if (item.role.toLowerCase() === 'asset') {
                                icon = 'boxes';
                            }

                            const resultItem = document.createElement('a');
                            resultItem.href = item.url;
                            resultItem.className = 'search-result-item';
                            resultItem.innerHTML = `
                                <div class="search-result-icon">
                                    <i class="fas fa-${icon}"></i>
                                </div>
                                <div class="search-result-content">
                                    <div class="search-result-name">${item.name}</div>
                                    <div class="search-result-meta">
                                        <span class="search-result-role">${item.role}</span> ·
                                        <span class="search-result-location">${item.location}</span>
                                    </div>
                                </div>
                            `;

                            searchResults.appendChild(resultItem);
                        });

                        searchResults.style.display = 'block';
                    }, 300);
                });

                // Close search results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.search-container')) {
                        searchResults.style.display = 'none';
                    }
                });

                // Prevent search form submission
                const searchForm = document.getElementById('searchForm');
                if (searchForm) {
                    searchForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        // Implement full search page redirect if needed
                    });
                }
            }

            // Function to load notifications
            function loadNotifications() {
                if (!notificationMenu) return;

                // This would normally be an AJAX request to the server
                // Mock notifications for demo
                const mockNotifications = [{
                        id: 1,
                        title: 'New Trainee Registered',
                        content: 'A new trainee has been registered in the system.',
                        icon: 'fas fa-user-plus',
                        color: 'primary',
                        time: '5 minutes ago',
                        url: '#notification-1'
                    },
                    {
                        id: 2,
                        title: 'Activity Scheduled',
                        content: 'A new activity has been scheduled for tomorrow.',
                        icon: 'fas fa-calendar-alt',
                        color: 'success',
                        time: '1 hour ago',
                        url: '#notification-2'
                    },
                    {
                        id: 3,
                        title: 'System Update',
                        content: 'The system will be undergoing maintenance tonight.',
                        icon: 'fas fa-cog',
                        color: 'warning',
                        time: '3 hours ago',
                        url: '#notification-3'
                    }
                ];

                // Build notification HTML
                let notificationsHtml = `
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0">Notifications</h6>
                            <a href="#" class="text-muted small" id="mark-all-read">
                                Mark all as read
                            </a>
                        </div>
                    </div>
                `;

                if (mockNotifications.length > 0) {
                    mockNotifications.forEach(notification => {
                        notificationsHtml += `
                            <a href="${notification.url}" class="d-flex p-3 border-bottom">
                                <div class="mr-3">
                                    <div class="notification-icon ${notification.color}">
                                        <i class="${notification.icon}"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-weight-bold">${notification.title}</div>
                                    <div class="small text-muted">${notification.content}</div>
                                    <div class="smallest text-muted mt-1">${notification.time}</div>
                                </div>
                            </a>
                        `;
                    });
                } else {
                    notificationsHtml += `
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-3"></i>
                            <p>No new notifications</p>
                        </div>
                    `;
                }

                notificationsHtml += `
                    <div class="p-2 text-center border-top">
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-light w-100">
                            View All Notifications
                        </a>
                    </div>
                `;

                notificationMenu.innerHTML = notificationsHtml;

                // Set up mark all read button
                const markAllReadBtn = document.getElementById('mark-all-read');
                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const notificationCount = document.querySelector('.notification-count');
                        if (notificationCount) {
                            notificationCount.style.display = 'none';
                        }

                        notificationMenu.classList.remove('show');
                    });
                }
            }

            // Fix avatar images
            const avatarImages = document.querySelectorAll(
                '.user-avatar img, .profile-img, .rounded-circle[src*="profile"]');
            avatarImages.forEach(function(img) {
                // Check if src is empty, null, or undefined
                if (!img.getAttribute('src') || img.getAttribute('src') === '') {
                    img.src = '/images/default-avatar.png';
                }

                // Add error handler for loading failures
                img.addEventListener('error', function() {
                    this.src = '/images/default-avatar.png';
                });
            });
        });
    </script>

    @yield('scripts')
</body>

</html>
