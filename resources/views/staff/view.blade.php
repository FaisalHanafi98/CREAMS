@extends('layouts.app')

@section('title')
{{ $staffMember->name ?? 'Staff Member' }} - Staff Profile | CREAMS
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #25a6cf;
        --success-color: #1cc88a;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .profile-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        object-fit: cover;
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .info-item {
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--dark-color);
        min-width: 140px;
        display: flex;
        align-items: center;
    }

    .info-label i {
        margin-right: 8px;
        color: var(--primary-color);
        width: 16px;
    }

    .info-value {
        color: #555;
        flex: 1;
    }

    .role-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .role-admin { background: linear-gradient(45deg, #e74a3b, #c0392b); color: white; }
    .role-supervisor { background: linear-gradient(45deg, #f39c12, #e67e22); color: white; }
    .role-teacher { background: linear-gradient(45deg, #1cc88a, #17a673); color: white; }
    .role-ajk { background: linear-gradient(45deg, #3498db, #2980b9); color: white; }

    .action-btn {
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        margin: 0.25rem;
    }

    .btn-edit {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-edit:hover {
        background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(50, 189, 234, 0.4);
    }

    .btn-back {
        background: linear-gradient(45deg, #6c757d, #5a6268);
        color: white;
    }

    .btn-back:hover {
        background: linear-gradient(45deg, #5a6268, #495057);
        color: white;
        transform: translateY(-2px);
    }

    .section-title {
        color: var(--dark-color);
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid var(--primary-color);
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--secondary-color);
    }

    .stats-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid var(--border-color);
    }

    .stats-number {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .profile-avatar {
            width: 100px;
            height: 100px;
        }
        
        .info-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .info-label {
            min-width: auto;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teachershome') }}">Staff Directory</a></li>
            <li class="breadcrumb-item active">{{ $staffMember->name }}</li>
        </ol>
    </nav>

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    @if($staffMember->avatar)
                        <img src="{{ asset('storage/avatars/' . $staffMember->avatar) }}" alt="{{ $staffMember->name }}" class="profile-avatar">
                    @else
                        <div class="profile-avatar bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h1 class="mb-2">{{ $staffMember->name }}</h1>
                    <span class="role-badge role-{{ strtolower($staffMember->role) }}">{{ ucfirst($staffMember->role) }}</span>
                    <p class="mt-3 mb-0">
                        <i class="fas fa-envelope me-2"></i>{{ $staffMember->email }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-id-card me-2"></i>ID: {{ $staffMember->iium_id }}
                    </p>
                </div>
                <div class="col-md-3 text-center">
                    <a href="{{ route('staff.edit', $staffMember->id) }}" class="action-btn btn-edit">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('teachershome') }}" class="action-btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="profile-card">
                <h3 class="section-title">
                    <i class="fas fa-user me-2"></i>Personal Information
                </h3>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user"></i>Full Name
                    </div>
                    <div class="info-value">{{ $staffMember->name }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-envelope"></i>Email
                    </div>
                    <div class="info-value">{{ $staffMember->email }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-phone"></i>Phone
                    </div>
                    <div class="info-value">{{ $staffMember->phone ?? 'Not provided' }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-birthday-cake"></i>Date of Birth
                    </div>
                    <div class="info-value">
                        {{ $staffMember->date_of_birth ? \Carbon\Carbon::parse($staffMember->date_of_birth)->format('F j, Y') : 'Not provided' }}
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-map-marker-alt"></i>Address
                    </div>
                    <div class="info-value">{{ $staffMember->address ?? 'Not provided' }}</div>
                </div>
                
                @if($staffMember->bio)
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-info-circle"></i>Bio
                    </div>
                    <div class="info-value">{{ $staffMember->bio }}</div>
                </div>
                @endif
            </div>

            <!-- Professional Information -->
            <div class="profile-card">
                <h3 class="section-title">
                    <i class="fas fa-briefcase me-2"></i>Professional Information
                </h3>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-id-badge"></i>IIUM ID
                    </div>
                    <div class="info-value">{{ $staffMember->iium_id }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user-tag"></i>Role
                    </div>
                    <div class="info-value">
                        <span class="role-badge role-{{ strtolower($staffMember->role) }}">{{ ucfirst($staffMember->role) }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-building"></i>Centre
                    </div>
                    <div class="info-value">
                        @if($staffMember->centre_id && isset($centre))
                            {{ $centre->centre_name }}
                        @else
                            Not Assigned
                        @endif
                    </div>
                </div>
                
                @if($staffMember->user_activity_1)
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-star"></i>Primary Activity
                    </div>
                    <div class="info-value">{{ $staffMember->user_activity_1 }}</div>
                </div>
                @endif
                
                @if($staffMember->user_activity_2)
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-star-half-alt"></i>Secondary Activity
                    </div>
                    <div class="info-value">{{ $staffMember->user_activity_2 }}</div>
                </div>
                @endif
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-calendar-plus"></i>Join Date
                    </div>
                    <div class="info-value">
                        {{ \Carbon\Carbon::parse($staffMember->created_at)->format('F j, Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics & Quick Actions -->
        <div class="col-lg-4">
            <div class="profile-card">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar me-2"></i>Statistics
                </h3>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stats-card">
                            <div class="stats-number">{{ $stats['active_sessions'] ?? 0 }}</div>
                            <div class="stats-label">Active Activities</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card">
                            <div class="stats-number">{{ $stats['total_trainees'] ?? 0 }}</div>
                            <div class="stats-label">Total Trainees</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card">
                            <div class="stats-number">{{ $stats['attendance_rate'] ?? 0 }}%</div>
                            <div class="stats-label">Avg Attendance</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card">
                            <div class="stats-number">{{ $stats['years_service'] ?? 'N/A' }}</div>
                            <div class="stats-label">Service Period</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <h3 class="section-title">
                    <i class="fas fa-cog me-2"></i>Quick Actions
                </h3>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('staff.edit', $staffMember->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('staff.schedule', $staffMember->id) }}" class="btn btn-outline-success">
                        <i class="fas fa-calendar me-2"></i>View Schedule
                    </a>
                    <a href="{{ route('staff.activities', $staffMember->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-tasks me-2"></i>View Activities
                    </a>
                    @if($staffMember->role === 'teacher')
                    <a href="{{ route('staff.trainees', $staffMember->id) }}" class="btn btn-outline-warning">
                        <i class="fas fa-users me-2"></i>Assigned Trainees
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection