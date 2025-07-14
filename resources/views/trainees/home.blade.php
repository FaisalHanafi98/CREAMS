<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainees Home - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/traineehomestyle.css') }}">
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
                    <h1 class="page-title">Trainee Management</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <span class="current">Trainee Management</span>
                    </div>
                </div>
                
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Search...">
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
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Trainee Management</h1>
                <div>
                    <a href="{{ route('traineesregistrationpage') }}" class="btn btn-primary btn-sm shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i>Register New Trainee
                    </a>
                    <a href="#" class="btn btn-info btn-sm shadow-sm ml-2" data-toggle="modal" data-target="#filterModal">
                        <i class="fas fa-filter fa-sm text-white-50 mr-1"></i>Filter Trainees
                    </a>
                </div>
            </div>

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

            @if(isset($error))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $error }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Content Row - Statistics -->
            <div class="row">
                <!-- Total Trainees Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Trainees</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTrainees ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Centers Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Centers</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ isset($traineesByCenter) ? $traineesByCenter->count() : 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Condition Types Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Condition Types</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conditionTypes ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Trainees Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New Trainees (30 days)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $newTraineesCount ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Box -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Search Trainees</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('traineeshome') }}" method="GET" class="form-inline">
                        <div class="form-group mb-2 flex-grow-1">
                            <input type="text" name="search" class="form-control w-100" placeholder="Search by name or email..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mb-2 ml-2">Search</button>
                        @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                            <a href="{{ route('traineeshome') }}" class="btn btn-secondary mb-2 ml-2">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Active Filters</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            @if(request('search'))
                                <div class="badge badge-info m-1 p-2">
                                    Search: {{ request('search') }}
                                    <a href="{{ route('traineeshome', array_merge(request()->except('search'), [])) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            @endif
                            
                            @if(request('centre'))
                                <div class="badge badge-primary m-1 p-2">
                                    Center: {{ request('centre') }}
                                    <a href="{{ route('traineeshome', array_merge(request()->except('centre'), [])) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            @endif
                            
                            @if(request('condition'))
                                <div class="badge badge-success m-1 p-2">
                                    Condition: {{ request('condition') }}
                                    <a href="{{ route('traineeshome', array_merge(request()->except('condition'), [])) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            @endif
                            
                            <a href="{{ route('traineeshome') }}" class="btn btn-sm btn-outline-secondary ml-auto">
                                Clear All Filters
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Trainees by Center -->
            @if(isset($traineesByCenter) && $traineesByCenter->count() > 0)
                @foreach($traineesByCenter as $centreName => $centerTrainees)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">{{ $centreName ?? 'Unassigned' }} ({{ $centerTrainees->count() }})</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink-{{ Str::slug($centreName) }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink-{{ Str::slug($centreName) }}">
                                    <div class="dropdown-header">Center Actions:</div>
                                    <a class="dropdown-item" href="{{ route('traineeshome', ['centre' => $centreName]) }}">Filter by Center</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('traineesregistrationpage', ['centre' => $centreName]) }}">Add Trainee to Center</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($centerTrainees as $trainee)
                                    <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-header bg-light py-2 text-center">
                                                <div class="avatar-container mb-2">
                                                    <img src="{{ asset($trainee->trainee_avatar ?? 'images/default-avatar.jpg') }}" class="rounded-circle" width="80" height="80" alt="Trainee Avatar" style="object-fit: cover;">
                                                </div>
                                                <h5 class="card-title mb-0">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h5>
                                            </div>
                                            <div class="card-body pt-2">
                                                <p class="mb-1"><strong>Email:</strong> {{ $trainee->trainee_email }}</p>
                                                <p class="mb-1"><strong>Condition:</strong> <span class="badge badge-{{ $trainee->getConditionBadgeClassAttribute() ?? 'secondary' }}">{{ $trainee->trainee_condition }}</span></p>
                                                <p class="mb-1"><strong>Age:</strong> {{ $trainee->getAgeAttribute() ?? 'N/A' }} years</p>
                                                <p class="mb-1"><small class="text-muted">Registered: {{ $trainee->created_at ? $trainee->created_at->format('M d, Y') : 'Unknown' }}</small></p>
                                            </div>
                                            <div class="card-footer bg-transparent border-top-0 text-center">
                                                <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-user mr-1"></i>View Profile
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="text-center py-4">
                            <img src="{{ asset('images/empty-state.svg') }}" alt="No trainees found" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>No trainees found</h5>
                            <p class="text-muted">There are no trainees registered in the system yet, or none match your search criteria.</p>
                            <a href="{{ route('traineesregistrationpage') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus mr-1"></i>Register New Trainee
                            </a>
                        </div>
                    </div>
                </div>
            @endif
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

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('traineeshome') }}" method="GET">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">Filter Trainees</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Center Filter -->
                        <div class="form-group">
                            <label for="centre">Center</label>
                            <select name="centre" id="centre" class="form-control">
                                <option value="">All Centers</option>
                                @foreach($centres ?? [] as $centre)
                                    <option value="{{ $centre->centre_name }}" {{ request('centre') == $centre->centre_name ? 'selected' : '' }}>
                                        {{ $centre->centre_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Condition Filter -->
                        <div class="form-group">
                            <label for="condition">Condition</label>
                            <select name="condition" id="condition" class="form-control">
                                <option value="">All Conditions</option>
                                @foreach($conditions ?? [] as $condition)
                                    <option value="{{ $condition }}" {{ request('condition') == $condition ? 'selected' : '' }}>
                                        {{ $condition }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Search by name/email -->
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search by name or email..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                            <a href="{{ route('traineeshome') }}" class="btn btn-outline-secondary">Clear Filters</a>
                        @endif
                    </div>
                </form>
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
            
            // Hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Enable search on enter press
            $('#search').keypress(function(e) {
                if (e.which == 13) {
                    $(this).closest('form').submit();
                    return false;
                }
            });
        });
    </script>
</body>
</html>