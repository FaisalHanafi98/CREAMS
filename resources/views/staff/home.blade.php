@extends('layouts.app')

@section('title', 'Staff Directory - CREAMS')

@section('styles')
<link href="{{ asset('css/dropdown-improvements.css') }}" rel="stylesheet">
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #c850c0;
        --success-color: #2ed573;
        --warning-color: #ffa502;
        --danger-color: #ff4757;
        --dark-color: #1a2a3a;
        --info-color: #1e90ff;
        --light-bg: #f8f9fc;
        --border-color: #e9ecef;
    }

    .staff-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(50, 189, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .staff-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(50px, -50px);
    }

    .staff-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .staff-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border-left: 5px solid var(--primary-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        opacity: 0.1;
        border-radius: 50%;
        transform: translate(25px, -25px);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 24px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .filter-section h5 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-section .form-control, .filter-section .form-select {
        height: 45px;
        font-size: 14px;
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .filter-section .form-control:focus, .filter-section .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 2rem;
    }

    .staff-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f1f3f4;
    }

    .staff-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .staff-card-header {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(41, 128, 185, 0.1));
        padding: 20px;
        text-align: center;
        position: relative;
    }

    .staff-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        margin: 0 auto 15px;
        display: block;
    }

    .avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        margin: 0 auto 15px;
    }

    .staff-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin: 0 0 5px 0;
    }

    .staff-id {
        font-size: 0.9rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
        background: rgba(255,255,255,0.8);
        padding: 3px 8px;
        border-radius: 10px;
        display: inline-block;
    }

    .staff-card-body {
        padding: 20px;
    }

    .staff-info {
        margin-bottom: 15px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .info-label {
        color: #6c757d;
        font-weight: 500;
    }

    .info-value {
        color: var(--dark-color);
        font-weight: 600;
    }

    .role-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 15px;
        text-align: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin: 2px;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #e0a800);
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin: 2px;
        color: white;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #e0a800, #cc9500);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #218838);
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin: 2px;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #218838, #1e7e34);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        color: white;
    }

    .no-staff {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .no-staff i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .no-staff h4 {
        color: var(--dark-color);
        margin-bottom: 15px;
    }

    .no-staff p {
        color: #6c757d;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Error and Success Messages -->
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="staff-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>
                    <i class="fas fa-users me-3"></i>Staff Directory
                </h1>
                <p>Comprehensive staff profiles and management system</p>
            </div>
            <div class="col-md-4 text-end">
                @if(in_array($currentUserRole, ['admin', 'supervisor']))
                <a href="{{ route('staffs.register') }}" class="btn btn-light me-2">
                    <i class="fas fa-plus me-2"></i>Add Staff
                </a>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
            <div class="stat-label">Total Staff</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-value">{{ $stats['teachers_count'] ?? 0 }}</div>
            <div class="stat-label">Teachers</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-value">{{ $stats['supervisors_count'] ?? 0 }}</div>
            <div class="stat-label">Supervisors</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-value">{{ $stats['admins_count'] ?? 0 }}</div>
            <div class="stat-label">Administrators</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h5><i class="fas fa-filter"></i>Filter Staff</h5>
        <form method="GET" action="{{ route('staffs.home') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="role" class="form-label fw-bold">Role</label>
                        <select name="role" id="role" class="form-select">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="education_level" class="form-label fw-bold">Education Level</label>
                        <select name="education_level" id="education_level" class="form-select">
                            <option value="">All Levels</option>
                            @foreach($educationLevels as $level)
                                <option value="{{ $level }}" {{ request('education_level') == $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="centre" class="form-label fw-bold">Centre</label>
                        <select name="centre" id="centre" class="form-select">
                            <option value="">All Centres</option>
                            @foreach($centres as $centre)
                                <option value="{{ $centre->centre_id }}" {{ request('centre') == $centre->centre_id ? 'selected' : '' }}>
                                    {{ $centre->centre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search" class="form-label fw-bold">Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search by name, email..." value="{{ request('search') }}">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('staffs.home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear All
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Staff Grid -->
    @if($users->count() > 0)
        <div class="staff-grid">
            @foreach($users as $user)
                <div class="staff-card">
                    <div class="staff-card-header">
                        @if($user->avatar)
                            <img src="{{ asset('storage/avatars/' . $user->avatar) }}?v={{ time() }}" 
                                 alt="{{ $user->user_name }}" 
                                 class="staff-avatar"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="staff-avatar bg-light d-flex align-items-center justify-content-center" style="display: none;">
                                <i class="fas fa-user fa-2x text-muted"></i>
                            </div>
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($user->user_name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <div class="staff-name">{{ $user->user_name }}</div>
                        @if($user->iium_id)
                            <div class="staff-id">{{ $user->iium_id }}</div>
                        @endif
                    </div>
                    
                    <div class="staff-card-body">
                        <div class="role-badge">{{ ucfirst($user->role) }}</div>
                        
                        <div class="staff-info">
                            @if($user->position)
                                <div class="info-row">
                                    <span class="info-label">Position:</span>
                                    <span class="info-value">{{ $user->position }}</span>
                                </div>
                            @endif
                            
                            @if($user->education_level)
                                <div class="info-row">
                                    <span class="info-label">Education:</span>
                                    <span class="info-value">{{ $user->education_level }}</span>
                                </div>
                            @endif
                            
                            @if($user->centre && $user->centre->centre_name)
                                <div class="info-row">
                                    <span class="info-label">Centre:</span>
                                    <span class="info-value">{{ $user->centre->centre_name }}</span>
                                </div>
                            @endif
                            
                            @if($user->email)
                                <div class="info-row">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value">{{ Str::limit($user->email, 25) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-center">
                            <a href="{{ route('staffs.profile', ['encrypted_id' => $user->encrypted_id]) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            @if(in_array($currentUserRole, ['admin', 'supervisor']) || session('id') == $user->id)
                                <a href="{{ route('staffs.edit', ['encrypted_id' => $user->encrypted_id]) }}" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-staff">
            <i class="fas fa-users"></i>
            <h4>No Staff Members Found</h4>
            <p>Try adjusting your search criteria or filters to find staff members.</p>
            @if(in_array($currentUserRole, ['admin', 'supervisor']))
                <a href="{{ route('staffs.register') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Add First Staff Member
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Auto-submit form on filter change
    $(document).ready(function() {
        $('#role, #education_level, #centre').change(function() {
            $(this).closest('form').submit();
        });
        
        // Search with delay
        let searchTimeout;
        $('#search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                $(this).closest('form').submit();
            }, 500);
        });
    });
</script>
@endsection