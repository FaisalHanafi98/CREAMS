@extends('layouts.app')

@section('title')
{{ $staffMember->name }} - Assigned Trainees | CREAMS
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
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
    }

    .trainee-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            <li class="breadcrumb-item"><a href="{{ route('teachershome') }}">Staff Directory</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.view', $staffMember->id) }}">{{ $staffMember->name }}</a></li>
            <li class="breadcrumb-item active">Assigned Trainees</li>
        </ol>
    </nav>

    <!-- Success/Error Messages -->
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

    <!-- Header -->
    <div class="trainees-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-users me-3"></i>{{ $staffMember->name }}'s Assigned Trainees
                </h1>
                <p class="mb-0 opacity-75">Manage and monitor trainee progress and enrollment</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('staff.view', $staffMember->id) }}" class="btn btn-light">
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
                <div class="stat-number">{{ count($trainees) }}</div>
                <div class="stat-label">Total Trainees</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ collect($trainees)->where('enrollment_status', 'active')->count() }}
                </div>
                <div class="stat-label">Active Enrollments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ collect($trainees)->where('enrollment_status', 'enrolled')->count() }}
                </div>
                <div class="stat-label">Enrolled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ collect($trainees)->unique('activity_name')->count() }}
                </div>
                <div class="stat-label">Unique Activities</div>
            </div>
        </div>
    </div>

    <!-- Trainees List -->
    <div class="trainees-card">
        <h3 class="mb-4">
            <i class="fas fa-list me-2 text-primary"></i>All Assigned Trainees
        </h3>

        @if(count($trainees) > 0)
            @foreach($trainees as $trainee)
                <div class="trainee-item">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="trainee-avatar">
                                    {{ strtoupper(substr($trainee->name ?? 'T', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        @if(isset($trainee->ic_number))
                                            <span class="trainee-id me-2">{{ $trainee->ic_number }}</span>
                                        @endif
                                        <div class="trainee-name">{{ $trainee->name ?? 'Unknown Trainee' }}</div>
                                    </div>
                                    
                                    <div class="trainee-meta">
                                        @if(isset($trainee->activity_name))
                                            <i class="fas fa-tasks me-1"></i>Activity: {{ $trainee->activity_name }}
                                        @endif
                                        @if(isset($trainee->enrollment_date))
                                            | <i class="fas fa-calendar me-1"></i>Enrolled: {{ date('M j, Y', strtotime($trainee->enrollment_date)) }}
                                        @endif
                                        @if(isset($trainee->age))
                                            | <i class="fas fa-user me-1"></i>Age: {{ $trainee->age }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-end">
                            @if(isset($trainee->enrollment_status))
                                <span class="status-badge status-{{ $trainee->enrollment_status }}">
                                    {{ ucfirst($trainee->enrollment_status) }}
                                </span>
                            @else
                                <span class="status-badge status-enrolled">
                                    Centre Trainee
                                </span>
                            @endif
                            
                            @if(isset($trainee->gender))
                                <div class="mt-2">
                                    <small class="text-muted">{{ ucfirst($trainee->gender) }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if(isset($trainee->phone) || isset($trainee->emergency_contact))
                        <div class="mt-3 pt-3 border-top">
                            <div class="row">
                                @if(isset($trainee->phone))
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-phone me-1"></i>Phone: {{ $trainee->phone }}
                                        </small>
                                    </div>
                                @endif
                                @if(isset($trainee->emergency_contact))
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Emergency: {{ $trainee->emergency_contact }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="no-trainees">
                <i class="fas fa-user-graduate fa-3x mb-3 text-muted"></i>
                <h4>No Trainees Assigned</h4>
                <p class="mb-0">This staff member has no trainees assigned to their activities yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection