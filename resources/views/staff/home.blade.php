@extends('layouts.app')

@section('title', 'Staff Directory - CREAMS')

@section('styles')
<style>
    .staff-avatar {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 3px solid #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    
    .staff-avatar:hover {
        transform: scale(1.05);
    }
    
    .avatar-placeholder {
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-size: 2.5rem;
        font-weight: bold;
        transition: transform 0.2s ease;
    }
    
    .avatar-placeholder:hover {
        transform: scale(1.05);
    }
    
    .staff-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .staff-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .staff-card .card-body {
        padding: 1.5rem;
    }
    
    .staff-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .role-badge {
        font-size: 0.85rem;
        padding: 0.375rem 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Staff Directory</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Staff Directory</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Error and Success Message -->
    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ $stats['total_users'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Total Staff</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle h3">
                                <i class="fas fa-users"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ $stats['teachers_count'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Teacher</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-circle h3">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ $stats['supervisors_count'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Supervisor</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning rounded-circle h3">
                                <i class="fas fa-user-tie"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ $stats['admins_count'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Administrators</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-danger rounded-circle h3">
                                <i class="fas fa-user-shield"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Filter Staff</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('staffs.home') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select name="role" id="role" class="form-control">
                                        <option value="">All Role</option>
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
                                    <label for="education_level">Education Level</label>
                                    <select name="education_level" id="education_level" class="form-control">
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
                                    <label for="centre">Centre</label>
                                    <select name="centre" id="centre" class="form-control">
                                        <option value="">All Centre</option>
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
                                    <label for="search">Search</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           placeholder="Search by name, email..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('staffs.home') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Staff Members ({{ $users->count() }})</h4>
                    @if(in_array($currentUserRole, ['admin', 'supervisor']))
                        <div class="card-title-right">
                            <a href="{{ route('staffs.register') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Add New Staff
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <div class="row">
                            @foreach($users as $user)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 staff-card">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                @if($user->avatar)
                                                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                                                         alt="{{ $user->user_name }}" 
                                                         class="rounded-circle staff-avatar mx-auto d-block"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="avatar-placeholder rounded-circle bg-primary text-white mx-auto" style="display: none;">
                                                        {{ strtoupper(substr($user->user_name, 0, 1)) }}
                                                    </div>
                                                @else
                                                    <div class="avatar-placeholder rounded-circle bg-primary text-white mx-auto">
                                                        {{ strtoupper(substr($user->user_name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <h5 class="card-title mb-1 staff-name">{{ $user->user_name }}</h5>
                                            <p class="text-muted mb-2">
                                                <span class="badge badge-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'supervisor' ? 'warning' : ($user->role == 'teacher' ? 'success' : 'info')) }} role-badge">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </p>
                                            
                                            @if($user->position)
                                                <p class="text-muted small mb-2">{{ $user->position }}</p>
                                            @endif
                                            
                                            @if($user->education_level)
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-graduation-cap"></i> {{ $user->education_level }}
                                                </p>
                                            @endif
                                            
                                            @if($user->centre && $user->centre->centre_name)
                                                <p class="text-muted small mb-3">
                                                    <i class="fas fa-building"></i> {{ $user->centre->centre_name }}
                                                </p>
                                            @endif
                                            
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('staffs.profile', ['encrypted_id' => $user->encrypted_id]) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if(in_array($currentUserRole, ['admin', 'supervisor']) || session('id') == $user->id)
                                                    <a href="{{ route('staffs.edit', ['encrypted_id' => $user->encrypted_id]) }}" 
                                                       class="btn btn-outline-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No staff members found</h5>
                            <p class="text-muted">Try adjusting your search criteria or add new staff members.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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