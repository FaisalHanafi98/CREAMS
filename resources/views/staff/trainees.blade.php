@extends('layouts.app')

@section('title')
{{ $staffMember->name }} - Assigned Trainee | CREAMS
@endsection

@section('styles')
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
        --border-color: #e0e0e0;
        --success-color: #2ed573;
        --warning-color: #ffa502;
        --danger-color: #ff4757;
    }

    .trainees-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .trainees-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .trainees-header {
        background: var(--primary-gradient);
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .trainee-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        border-left: 4px solid var(--success-color);
    }

    .trainee-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        background-color: #f8f9fa;
    }
    
    .trainee-item.clickable {
        cursor: pointer !important;
        transition: all 0.2s ease;
    }
    
    .trainee-item.clickable:hover {
        box-shadow: 0 8px 25px rgba(50, 189, 234, 0.15);
        border-left-color: var(--primary-color);
        border-left-width: 6px;
    }

    .trainee-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 1rem;
    }

    .trainee-name {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .trainee-id {
        background: var(--primary-color);
        color: white;
        padding: 0.2rem 0.6rem;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-enrolled { background: var(--success-color); color: white; }
    .status-active { background: var(--primary-color); color: white; }
    .status-pending { background: var(--warning-color); color: white; }
    .status-inactive { background: #6c757d; color: white; }

    .trainee-meta {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .no-trainees {
        text-align: center;
        color: #6c757d;
        padding: 3rem;
        background: var(--light-bg);
        border-radius: 10px;
        border: 2px dashed var(--border-color);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid var(--border-color);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .stat-label {
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staffs.home') }}">Staff Directory</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staffs.profile', $staffMember->encrypted_id) }}">{{ $staffMember->name }}</a></li>
            <li class="breadcrumb-item active">Assigned Trainee</li>
        </ol>
    </nav>

    @include('components.flash-messages')

    <!-- Header -->
    <div class="trainees-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-users me-3"></i>{{ $staffMember->name }}'s Assigned Trainee
                </h1>
                <p class="mb-0 opacity-75">Manage and monitor trainee progress and enrollment</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('staffs.profile', $staffMember->encrypted_id) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="trainees-card">
        <h3 class="mb-4">
            <i class="fas fa-chart-bar me-2 text-primary"></i>Trainee Statistics
        </h3>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $traineeStats['total_trainees'] ?? 0 }}</div>
                <div class="stat-label">Total Trainee</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $traineeStats['active_enrollments'] ?? 0 }}</div>
                <div class="stat-label">Active Enrollments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $traineeStats['total_enrollments'] ?? 0 }}</div>
                <div class="stat-label">Enrolled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $traineeStats['unique_activities'] ?? 0 }}</div>
                <div class="stat-label">Unique Activity</div>
            </div>
        </div>
    </div>

    <!-- Trainee List -->
    <div class="trainees-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>All Assigned Trainee
                @if(isset($pagination))
                    <small class="text-muted">({{ $pagination->total }} total, showing {{ count($trainees) }} per page)</small>
                @endif
            </h3>
        </div>

        @if(count($trainees) > 0)
            @foreach($trainees as $trainee)
                <div class="trainee-item clickable" onclick="window.location.href='{{ route('traineeprofile', $trainee->encrypted_id ?? '') }}'">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="trainee-avatar">
                                    {{ strtoupper(substr($trainee->name ?? 'T', 0, 2)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        @if(isset($trainee->trainee_id))
                                            <span class="trainee-id me-2">{{ $trainee->trainee_id }}</span>
                                        @endif
                                        <div class="trainee-name">
                                            <a href="{{ route('traineeprofile', $trainee->encrypted_id ?? '') }}" class="text-decoration-none text-dark">
                                                {{ $trainee->name ?? 'Unknown Trainee' }}
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="trainee-meta">
                                        @if(isset($trainee->enrolled_activities))
                                            <div class="mb-1">
                                                <i class="fas fa-tasks me-1"></i>
                                                <strong>Activities ({{ $trainee->activity_count ?? 1 }}):</strong> 
                                                <span class="text-primary">{{ $trainee->enrolled_activities }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if(isset($trainee->first_enrollment_date))
                                                    <i class="fas fa-calendar me-1"></i>First Enrolled: {{ date('M j, Y', strtotime($trainee->first_enrollment_date)) }}
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if(isset($trainee->age))
                                                    <i class="fas fa-user me-1"></i>Age: {{ $trainee->age }} years
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if(isset($trainee->trainee_condition))
                                            <div class="mt-1">
                                                <i class="fas fa-heartbeat me-1"></i>Condition: {{ $trainee->trainee_condition }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-end">
                            <span class="status-badge status-enrolled">
                                Enrolled
                            </span>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-external-link-alt me-1"></i>Click to view profile
                                </small>
                            </div>
                            
                            @if(isset($trainee->gender))
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $trainee->gender === 'male' ? 'mars' : 'venus' }} me-1"></i>
                                        {{ ucfirst($trainee->gender) }}
                                    </small>
                                </div>
                            @endif
                            
                            @if(isset($trainee->centre_name))
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>{{ $trainee->centre_name }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if(isset($trainee->trainee_phone_number) || isset($trainee->trainee_email))
                        <div class="mt-3 pt-3 border-top">
                            <div class="row">
                                @if(isset($trainee->trainee_phone_number))
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-phone me-1"></i>Phone: {{ $trainee->trainee_phone_number }}
                                        </small>
                                    </div>
                                @endif
                                @if(isset($trainee->trainee_email))
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i>Email: {{ $trainee->trainee_email }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Pagination -->
            @if(isset($pagination) && $pagination->lastPage > 1)
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            Showing {{ (($pagination->currentPage - 1) * $pagination->perPage) + 1 }} to 
                            {{ min($pagination->currentPage * $pagination->perPage, $pagination->total) }} 
                            of {{ $pagination->total }} trainees
                        </small>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @if($pagination->previousPageUrl)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $pagination->previousPageUrl }}">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            @endif
                            
                            @for($i = max(1, $pagination->currentPage - 2); $i <= min($pagination->lastPage, $pagination->currentPage + 2); $i++)
                                <li class="page-item {{ $i == $pagination->currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                                </li>
                            @endfor
                            
                            @if($pagination->nextPageUrl)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $pagination->nextPageUrl }}">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
        @else
            <div class="no-trainees">
                <i class="fas fa-user-graduate fa-3x mb-3 text-muted"></i>
                <h4>No Trainee Assigned</h4>
                <p class="mb-0">This staff member has no trainees assigned to their activities yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection