<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainee Management - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
    <style>
        /* Custom styles for trainee management */
        .badge-condition {
            font-size: 85%;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 15px;
        }
        
        .badge-cerebral-palsy {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-autism {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-down-syndrome {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-hearing {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-visual {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .badge-intellectual {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .trainee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .trainee-filter-box {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .trainee-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .trainee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .trainee-card-header {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        
        .trainee-card-avatar {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .trainee-card-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .trainee-card-info h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .trainee-card-body {
            padding: 15px;
        }
        
        .trainee-card-detail {
            margin-bottom: 10px;
            display: flex;
        }
        
        .trainee-card-detail-label {
            width: 140px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .trainee-card-detail-value {
            flex: 1;
        }
        
        .trainee-card-footer {
            padding: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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
            <li class="{{ request()->routeIs('traineesmanagement') ? 'active' : '' }}">
                <a href="{{ route('traineesmanagement') }}">
                    <i class="fas fa-user-graduate"></i>
                    <span>Trainees</span>
                    <div class="tooltip-sidebar">Trainees</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('traineesregistrationpage') ? '' : '' }}">
                <a href="{{ route('traineesregistrationpage') }}">
                    <i class="fas fa-user-plus"></i>
                    <span>Register Trainee</span>
                    <div class="tooltip-sidebar">Register Trainee</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('teachershome') ? '' : '' }}">
                <a href="{{ route('teachershome') }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Staff</span>
                    <div class="tooltip-sidebar">Staff</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('schedulehomepage') ? '' : '' }}">
                <a href="{{ route('schedulehomepage') }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                    <div class="tooltip-sidebar">Schedule</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('assetmanagementpage') ? '' : '' }}">
                <a href="{{ route('assetmanagementpage') }}">
                    <i class="fas fa-box"></i>
                    <span>Asset Management</span>
                    <div class="tooltip-sidebar">Asset Management</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('aboutus') ? '' : '' }}">
                <a href="{{ route('aboutus') }}">
                    <i class="fas fa-info-circle"></i>
                    <span>About Us</span>
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
                    <h1 class="page-title">Trainees</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <span class="current">Trainees</span>
                    </div>
                </div>
                
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Search trainees...">
                    </div>
                    
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        @if(isset($user['notifications']) && count($user['notifications']) > 0)
                            <span class="notification-count">{{ count($user['notifications']) }}</span>
                        @elseif(isset($notificationCount) && $notificationCount > 0)
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
                <a href="{{ route('traineesregistrationpage') }}" class="action-btn primary">
                    <i class="fas fa-plus"></i> Add New Trainee
                </a>
                <button class="action-btn" data-toggle="modal" data-target="#filterModal">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button class="action-btn" id="viewSwitchBtn">
                    <i class="fas fa-th-large"></i> <span id="viewSwitchText">Card View</span>
                </button>
                <button class="action-btn" id="exportBtn">
                    <i class="fas fa-file-export"></i> Export
                </button>
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
            
            <!-- Stats Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-body">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-details">
                                <div class="stats-value">{{ count($trainees) }}</div>
                                <div class="stats-label">Total Trainees</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stats-details">
                                <div class="stats-value">{{ count($trainees->unique('centre_name')) }}</div>
                                <div class="stats-label">Centres</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="stats-details">
                                <div class="stats-value">{{ count($trainees->unique('trainee_condition')) }}</div>
                                <div class="stats-label">Condition Types</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stats-details">
                                <div class="stats-value">
                                    {{ $trainees->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count() }}
                                </div>
                                <div class="stats-label">New in 30 Days</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table View (Default) -->
            <div class="card" id="tableView">
                <div class="card-header">
                    <h5 class="card-title">Trainee List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="traineesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Centre</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trainees as $trainee)
                                    <tr>
                                        <td>{{ $trainee->id }}</td>
                                        <td>
                                            <img src="{{ asset($trainee->trainee_avatar ?? 'images/default-avatar.jpg') }}" 
                                                 alt="{{ $trainee->trainee_first_name }}" 
                                                 class="trainee-avatar">
                                        </td>
                                        <td>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</td>
                                        <td>
                                            @if($trainee->trainee_date_of_birth)
                                                {{ \Carbon\Carbon::parse($trainee->trainee_date_of_birth)->age }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $trainee->trainee_email }}</td>
                                        <td>{{ $trainee->trainee_phone_number }}</td>
                                        <td>{{ $trainee->centre_name }}</td>
                                        <td>
                                            @php
                                                $conditionClass = '';
                                                if (strpos(strtolower($trainee->trainee_condition), 'cerebral') !== false) {
                                                    $conditionClass = 'badge-cerebral-palsy';
                                                } elseif (strpos(strtolower($trainee->trainee_condition), 'autism') !== false) {
                                                    $conditionClass = 'badge-autism';
                                                } elseif (strpos(strtolower($trainee->trainee_condition), 'down') !== false) {
                                                    $conditionClass = 'badge-down-syndrome';
                                                } elseif (strpos(strtolower($trainee->trainee_condition), 'hear') !== false) {
                                                    $conditionClass = 'badge-hearing';
                                                } elseif (strpos(strtolower($trainee->trainee_condition), 'visual') !== false) {
                                                    $conditionClass = 'badge-visual';
                                                } elseif (strpos(strtolower($trainee->trainee_condition), 'intellectual') !== false) {
                                                    $conditionClass = 'badge-intellectual';
                                                }
                                            @endphp
                                            <span class="badge badge-condition {{ $conditionClass }}">
                                                {{ $trainee->trainee_condition }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('traineeprofile', ['id' => $trainee->id]) }}">
                                                        <i class="fas fa-eye text-primary"></i> View
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('updatetraineeprofile', ['id' => $trainee->id]) }}">
                                                        <i class="fas fa-edit text-info"></i> Edit
                                                    </a>
                                                    <a class="dropdown-item delete-trainee" href="#" data-id="{{ $trainee->id }}">
                                                        <i class="fas fa-trash-alt text-danger"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Card View (Hidden by default) -->
            <div class="row mt-4" id="cardView" style="display: none;">
                @foreach($trainees as $trainee)
                    <div class="col-md-6 col-lg-4 trainee-card-container">
                        <div class="trainee-card">
                            <div class="trainee-card-header">
                                <div class="trainee-card-avatar">
                                    <img src="{{ asset($trainee->trainee_avatar ?? 'images/default-avatar.jpg') }}" 
                                         alt="{{ $trainee->trainee_first_name }}">
                                </div>
                                <div class="trainee-card-info">
                                    <h4>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h4>
                                    <span class="badge badge-condition 
                                        @php
                                            $conditionClass = '';
                                            if (strpos(strtolower($trainee->trainee_condition), 'cerebral') !== false) {
                                                echo 'badge-cerebral-palsy';
                                            } elseif (strpos(strtolower($trainee->trainee_condition), 'autism') !== false) {
                                                echo 'badge-autism';
                                            } elseif (strpos(strtolower($trainee->trainee_condition), 'down') !== false) {
                                                echo 'badge-down-syndrome';
                                            } elseif (strpos(strtolower($trainee->trainee_condition), 'hear') !== false) {
                                                echo 'badge-hearing';
                                            } elseif (strpos(strtolower($trainee->trainee_condition), 'visual') !== false) {
                                                echo 'badge-visual';
                                            } elseif (strpos(strtolower($trainee->trainee_condition), 'intellectual') !== false) {
                                                echo 'badge-intellectual';
                                            }
                                        @endphp
                                    ">
                                        {{ $trainee->trainee_condition }}
                                    </span>
                                </div>
                            </div>
                            <div class="trainee-card-body">
                                <div class="trainee-card-detail">
                                    <div class="trainee-card-detail-label">ID:</div>
                                    <div class="trainee-card-detail-value">{{ $trainee->id }}</div>
                                </div>
                                <div class="trainee-card-detail">
                                    <div class="trainee-card-detail-label">Email:</div>
                                    <div class="trainee-card-detail-value">{{ $trainee->trainee_email }}</div>
                                </div>
                                <div class="trainee-card-detail">
                                    <div class="trainee-card-detail-label">Phone:</div>
                                    <div class="trainee-card-detail-value">{{ $trainee->trainee_phone_number }}</div>
                                </div>
                                <div class="trainee-card-detail">
                                    <div class="trainee-card-detail-label">Age:</div>
                                    <div class="trainee-card-detail-value">
                                        @if($trainee->trainee_date_of_birth)
                                            {{ \Carbon\Carbon::parse($trainee->trainee_date_of_birth)->age }} years
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="trainee-card-detail">
                                    <div class="trainee-card-detail-label">Centre:</div>
                                    <div class="trainee-card-detail-value">{{ $trainee->centre_name }}</div>
                                </div>
                            </div>
                            <div class="trainee-card-footer">
                                <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('updatetraineeprofile', ['id' => $trainee->id]) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-trainee" data-id="{{ $trainee->id }}">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
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
    
    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Trainees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="form-group">
                            <label for="filterCentre">Centre</label>
                            <select class="form-control" id="filterCentre">
                                <option value="">All Centres</option>
                                @php
                                    $centres = $trainees->pluck('centre_name')->unique()->sort();
                                @endphp
                                @foreach($centres as $centre)
                                    <option value="{{ $centre }}">{{ $centre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filterCondition">Condition</label>
                            <select class="form-control" id="filterCondition">
                                <option value="">All Conditions</option>
                                @php
                                    $conditions = $trainees->pluck('trainee_condition')->unique()->sort();
                                @endphp
                                @foreach($conditions as $condition)
                                    <option value="{{ $condition }}">{{ $condition }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filterAgeRange">Age Range</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" id="filterAgeMin" placeholder="Min Age">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" id="filterAgeMax" placeholder="Max Age">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="applyFilters">Apply Filters</button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">Reset</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this trainee? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#traineesTable').DataTable({
                responsive: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'desc']], // Sort by ID descending by default
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search trainees...",
                    lengthMenu: "Show _MENU_ trainees per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ trainees",
                    infoEmpty: "Showing 0 to 0 of 0 trainees",
                    infoFiltered: "(filtered from _MAX_ total trainees)"
                }
            });
            
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
            
            // Global search functionality
            $('#globalSearch').on('keyup', function() {
                table.search(this.value).draw();
            });
            
            // Toggle view (Table/Card)
            $('#viewSwitchBtn').click(function() {
                if ($('#tableView').is(':visible')) {
                    $('#tableView').hide();
                    $('#cardView').show();
                    $('#viewSwitchText').text('Table View');
                    $(this).find('i').removeClass('fa-th-large').addClass('fa-list');
                } else {
                    $('#tableView').show();
                    $('#cardView').hide();
                    $('#viewSwitchText').text('Card View');
                    $(this).find('i').removeClass('fa-list').addClass('fa-th-large');
                }
            });
            
            // Filter functionality
            $('#applyFilters').click(function() {
                var centre = $('#filterCentre').val();
                var condition = $('#filterCondition').val();
                var ageMin = $('#filterAgeMin').val();
                var ageMax = $('#filterAgeMax').val();
                
                // Reset all filters first
                table.columns().search('').draw();
                
                // Apply Centre filter
                if (centre) {
                    table.column(6).search(centre).draw();
                }
                
                // Apply Condition filter
                if (condition) {
                    table.column(7).search(condition).draw();
                }
                
                // Apply Age filter (custom filtering function)
                if (ageMin || ageMax) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            var age = parseInt(data[3]) || 0;
                            var min = parseInt(ageMin) || 0;
                            var max = parseInt(ageMax) || 100;
                            
                            if ((isNaN(min) && isNaN(max)) ||
                                (isNaN(min) && age <= max) ||
                                (min <= age && isNaN(max)) ||
                                (min <= age && age <= max)) {
                                return true;
                            }
                            return false;
                        }
                    );
                    table.draw();
                    
                    // Clean up custom filter after use
                    $.fn.dataTable.ext.search.pop();
                }
                
                // Also filter the card view
                filterCardView(centre, condition, ageMin, ageMax);
                
                $('#filterModal').modal('hide');
            });
            
            // Reset filters
            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                table.search('').columns().search('').draw();
                $('.trainee-card-container').show();
                $('#filterModal').modal('hide');
            });
            
            // Filter Card View
            function filterCardView(centre, condition, ageMin, ageMax) {
                $('.trainee-card-container').each(function() {
                    var $this = $(this);
                    var cardCentre = $this.find('.trainee-card-detail:contains("Centre:")').find('.trainee-card-detail-value').text();
                    var cardCondition = $this.find('.badge-condition').text().trim();
                    var ageText = $this.find('.trainee-card-detail:contains("Age:")').find('.trainee-card-detail-value').text();
                    var cardAge = parseInt(ageText) || 0;
                    
                    var centreMatch = !centre || cardCentre.includes(centre);
                    var conditionMatch = !condition || cardCondition.includes(condition);
                    var ageMatch = (!ageMin && !ageMax) || 
                                  ((!ageMin || cardAge >= parseInt(ageMin)) && 
                                   (!ageMax || cardAge <= parseInt(ageMax)));
                    
                    if (centreMatch && conditionMatch && ageMatch) {
                        $this.show();
                    } else {
                        $this.hide();
                    }
                });
            }
            
            // Delete trainee functionality
            $('.delete-trainee').click(function(e) {
                e.preventDefault();
                var traineeId = $(this).data('id');
                $('#deleteForm').attr('action', '/trainees/delete/' + traineeId);
                $('#deleteModal').modal('show');
            });
            
            // Export functionality
            $('#exportBtn').click(function() {
                Swal.fire({
                    title: 'Export Trainees',
                    text: 'Choose export format:',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Excel',
                    cancelButtonText: 'CSV',
                    showCloseButton: true,
                    showDenyButton: true,
                    denyButtonText: 'PDF'
                }).then((result) => {
                    if (result.isConfirmed) {
                        exportToExcel();
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        exportToCSV();
                    } else if (result.isDenied) {
                        Swal.fire('PDF Export', 'This feature is coming soon!', 'info');
                    }
                });
            });
            
            // Export to Excel
            function exportToExcel() {
                var wb = XLSX.utils.table_to_book(document.getElementById('traineesTable'), {
                    sheet: "Trainees"
                });
                var wbout = XLSX.write(wb, {
                    bookType: 'xlsx',
                    type: 'binary'
                });
                
                function s2ab(s) {
                    var buf = new ArrayBuffer(s.length);
                    var view = new Uint8Array(buf);
                    for (var i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                    return buf;
                }
                
                saveAs(new Blob([s2ab(wbout)], {type:"application/octet-stream"}), "trainees_" + new Date().toISOString().slice(0,10) + ".xlsx");
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Trainees exported to Excel successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
            
            // Export to CSV
            function exportToCSV() {
                var wb = XLSX.utils.table_to_book(document.getElementById('traineesTable'), {
                    sheet: "Trainees"
                });
                var wbout = XLSX.utils.sheet_to_csv(wb.Sheets["Trainees"]);
                
                var blob = new Blob([wbout], {type: 'text/csv;charset=utf-8'});
                saveAs(blob, "trainees_" + new Date().toISOString().slice(0,10) + ".csv");
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Trainees exported to CSV successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
            
            // Auto-hide alert messages after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>