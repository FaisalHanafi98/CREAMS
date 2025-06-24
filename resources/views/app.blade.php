<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CREAMS - Community Rehabilitation Management System')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')

    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            font-family: 'Roboto', sans-serif;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* Navbar Styles */
        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            height: 40px;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        .user-info {
            color: white;
            margin-right: 10px;
        }

        .dropdown-toggle {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
        }

        .dropdown-item {
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--primary-gradient);
            color: white;
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 70px);
            padding: 20px 0;
        }

        /* Footer */
        .footer {
            background: #343a40;
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .footer-links {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Loading Spinner */
        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            display: none;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-nav {
                margin-top: 20px;
            }

            .user-menu {
                margin-top: 15px;
                width: 100%;
                justify-content: center;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo">
                CREAMS
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    @if(session('id') && session('role'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                               href="{{ route('dashboard') }}">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        
                        @if(in_array(session('role'), ['admin', 'supervisor']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" 
                                   href="{{ route('users.index') }}">
                                    <i class="fas fa-users"></i> Users
                                </a>
                            </li>
                        @endif
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}" 
                               href="{{ route('activities.index') }}">
                                <i class="fas fa-tasks"></i> Activities
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('trainees.*') ? 'active' : '' }}" 
                               href="{{ route('trainees.index') }}">
                                <i class="fas fa-child"></i> Trainees
                            </a>
                        </li>
                    @endif
                </ul>
                
                <!-- User Menu -->
                @if(session('id') && session('role'))
                    <div class="user-menu">
                        <span class="user-info">{{ session('name') }}</span>
                        <div class="dropdown">
                            <button class="dropdown-toggle" type="button" data-toggle="dropdown">
                                <img src="{{ asset('images/default-avatar.png') }}" alt="Avatar" class="user-avatar">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('logout') }}" method="POST" class="mb-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> {{ session('info') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h5>CREAMS</h5>
                    <p class="mb-0">Community-based Rehabilitation Management System</p>
                </div>
                <div class="footer-links">
                    <a href="#">About</a>
                    <a href="#">Contact</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center">
                <small>&copy; {{ date('Y') }} CREAMS. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // CSRF Token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Show loading spinner for AJAX requests
        $(document).ajaxStart(function() {
            $('.loading-spinner').show();
        }).ajaxStop(function() {
            $('.loading-spinner').hide();
        });

        // Confirm delete actions
        $(document).on('submit', 'form[data-confirm]', function(e) {
            if (!confirm($(this).data('confirm'))) {
                e.preventDefault();
            }
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize popovers
        $('[data-toggle="popover"]').popover();
    </script>

    @yield('scripts')
</body>
</html>