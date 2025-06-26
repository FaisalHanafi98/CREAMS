@extends('layouts.app')

@section('title')
{{ $staffMember->name }} - Activities | CREAMS
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

    .activities-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .activities-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .activities-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .activity-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .activity-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .activity-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .activity-code {
        background: var(--primary-color);
        color: white;
        padding: 0.2rem 0.6rem;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .category-badge {
        background: var(--success-color);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .enrollment-badge {
        background: var(--warning-color);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .activity-meta {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .no-activities {
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
            <li class="breadcrumb-item active">Activities</li>
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
    <div class="activities-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-tasks me-3"></i>{{ $staffMember->name }}'s Activities
                </h1>
                <p class="mb-0 opacity-75">Manage and view all assigned rehabilitation activities</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('staff.view', $staffMember->id) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="activities-card">
        <h3 class="mb-4">
            <i class="fas fa-chart-bar me-2 text-primary"></i>Activity Statistics
        </h3>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ count($activities) }}</div>
                <div class="stat-label">Total Activities</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ collect($activities)->where('is_active', true)->count() }}
                </div>
                <div class="stat-label">Active Activities</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    @if(isset($activities[0]->enrollment_count))
                        {{ collect($activities)->sum('enrollment_count') }}
                    @else
                        0
                    @endif
                </div>
                <div class="stat-label">Total Enrollments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ collect($activities)->avg('duration_minutes') ? round(collect($activities)->avg('duration_minutes')) : 0 }}m
                </div>
                <div class="stat-label">Avg Duration</div>
            </div>
        </div>
    </div>

    <!-- Activities List -->
    <div class="activities-card">
        <h3 class="mb-4">
            <i class="fas fa-list me-2 text-primary"></i>All Activities
        </h3>

        @if(count($activities) > 0)
            @foreach($activities as $activity)
                <div class="activity-item">
                    <div class="row align-items-start">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <span class="activity-code me-2">{{ $activity->activity_code ?? 'N/A' }}</span>
                                <div class="activity-title">{{ $activity->activity_name }}</div>
                            </div>
                            
                            @if($activity->description)
                                <p class="text-muted mb-2">{{ Str::limit($activity->description, 120) }}</p>
                            @endif
                            
                            <div class="activity-meta">
                                <i class="fas fa-clock me-1"></i>Duration: {{ $activity->duration_minutes ?? 60 }} minutes
                                @if($activity->age_group)
                                    | <i class="fas fa-users me-1"></i>Age Group: {{ $activity->age_group }}
                                @endif
                                @if($activity->difficulty_level)
                                    | <i class="fas fa-signal me-1"></i>Level: {{ ucfirst($activity->difficulty_level) }}
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-end">
                            <div class="mb-2">
                                @if($activity->category)
                                    <span class="category-badge">{{ $activity->category }}</span>
                                @endif
                            </div>
                            
                            @if(isset($activity->enrollment_count))
                                <div class="mb-2">
                                    <span class="enrollment-badge">
                                        {{ $activity->enrollment_count }} Enrolled
                                    </span>
                                </div>
                            @endif
                            
                            <div>
                                @if($activity->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                                
                                @if($activity->requires_equipment)
                                    <span class="badge bg-warning">Equipment Required</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($activity->objectives)
                        <div class="mt-3 pt-3 border-top">
                            <strong class="text-primary">Objectives:</strong>
                            <p class="mb-0 mt-1 text-muted">{{ $activity->objectives }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="no-activities">
                <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
                <h4>No Activities Assigned</h4>
                <p class="mb-0">This staff member has not been assigned any activities yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection