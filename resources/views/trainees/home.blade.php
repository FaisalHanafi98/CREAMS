@extends('layouts.app')

@section('title', 'Trainee Management - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
        --info-color: #17a2b8;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .trainee-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        position: relative;
        overflow: hidden;
    }

    .trainee-header::before {
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

    .trainee-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .trainee-header p {
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
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
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

    .filter-section .form-control {
        height: 45px;
        font-size: 14px;
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .filter-section .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
    }

    .trainee-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-bottom: 2rem;
    }

    @media (max-width: 1400px) {
        .trainee-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .trainee-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .trainee-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f1f3f4;
    }

    .trainee-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .trainee-card-header {
        background: linear-gradient(135deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
        padding: 20px;
        text-align: center;
        position: relative;
    }

    .trainee-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
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
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
        font-size: 2rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        margin: 0 auto 15px;
        text-transform: uppercase;
    }

    .trainee-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin: 0 0 5px 0;
    }

    .trainee-id {
        font-size: 0.9rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
        background: rgba(255,255,255,0.8);
        padding: 3px 8px;
        border-radius: 10px;
        display: inline-block;
    }

    .trainee-card-body {
        padding: 20px;
    }

    .trainee-info {
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

    .condition-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 15px;
        text-align: center;
        width: 100%;
    }

    .progress-section {
        margin-bottom: 15px;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        font-size: 0.85rem;
    }

    .progress {
        height: 8px;
        border-radius: 10px;
        background: #f1f3f4;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .attendance-section {
        border-top: 1px solid #f1f3f4;
        padding-top: 15px;
        margin-bottom: 15px;
    }

    .attendance-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        font-size: 0.85rem;
    }

    .attendance-percentage {
        font-weight: 600;
    }

    .trainee-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 8px;
        margin-top: 15px;
    }

    .btn-action {
        padding: 6px 8px;
        border-radius: 10px;
        border: none;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
    }

    .btn-view {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-edit {
        background: var(--warning-color);
        color: white;
    }

    .btn-profile {
        background: var(--success-color);
        color: white;
    }

    .btn-schedule {
        background: var(--info-color);
        color: white;
    }

    .btn-attendance {
        background: var(--dark-color);
        color: white;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        color: white;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(200, 80, 192, 0.4);
    }

    .btn-light {
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        color: var(--dark-color);
        transition: all 0.3s ease;
    }

    .btn-light:hover {
        background: white;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        color: var(--dark-color);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .empty-state .empty-icon {
        font-size: 4rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .trainee-header {
            text-align: center;
            padding: 1.5rem;
        }

        .trainee-header h1 {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .trainee-grid {
            grid-template-columns: 1fr;
        }

        .filter-section {
            padding: 20px;
        }
    }

    /* Enhanced Pagination Styling */
    .pagination-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-top: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .pagination-info {
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
        margin-right: 20px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .pagination-info .info-highlight {
        font-weight: 700;
        color: var(--primary-color);
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0;
    }

    .page-item {
        margin: 0;
    }

    .page-link {
        border: none;
        background: transparent;
        color: #6c757d;
        padding: 10px 15px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 45px;
        height: 45px;
        text-decoration: none;
    }

    .page-link:hover {
        background: rgba(200, 80, 192, 0.1);
        color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(200, 80, 192, 0.2);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
        transform: translateY(-2px);
    }

    .page-item.disabled .page-link {
        color: #cbd5e0;
        background: #f8f9fa;
        cursor: not-allowed;
    }

    .page-item.disabled .page-link:hover {
        background: #f8f9fa;
        color: #cbd5e0;
        transform: none;
        box-shadow: none;
    }

    /* Navigation buttons styling */
    .page-item:first-child .page-link,
    .page-item:last-child .page-link {
        background: rgba(200, 80, 192, 0.05);
        color: var(--primary-color);
        font-weight: 700;
    }

    .page-item:first-child .page-link:hover,
    .page-item:last-child .page-link:hover {
        background: rgba(200, 80, 192, 0.15);
        color: var(--primary-color);
    }

    /* Responsive pagination */
    @media (max-width: 768px) {
        .pagination-wrapper {
            flex-direction: column;
            gap: 15px;
        }

        .pagination-info {
            margin-right: 0;
            text-align: center;
        }

        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-link {
            padding: 8px 12px;
            font-size: 13px;
            min-width: 40px;
            height: 40px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Persistent success/error banners (in addition to toast) --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>{{ session('success') }}</strong>
        &mdash; Use the search box below to find the new record.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Header -->
    <div class="trainee-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>
                    <i class="fas fa-user-graduate me-3"></i>Trainee Management
                </h1>
                <p>Comprehensive trainee profiles and progress tracking system</p>
            </div>
            <div class="col-md-4 text-end">
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('trainees.create') }}" class="btn btn-light me-2">
                    <i class="fas fa-plus me-2"></i>Add Trainee
                </a>
                @endif
                {{-- 
                <a href="{{ route('trainees.reports') }}" class="btn btn-light me-2">
                    <i class="fas fa-chart-bar me-2"></i>Reports
                </a>
                --}}
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
            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Trainee</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
            <div class="stat-label">Active Trainee</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-value">{{ $stats['enrolled'] ?? 0 }}</div>
            <div class="stat-label">Enrolled in Activity</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value">{{ $stats['avg_progress'] ?? 0 }}%</div>
            <div class="stat-label">Average Progress</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle text-warning"></i>
            </div>
            <div class="stat-value">{{ $stats['below_threshold'] ?? 0 }}</div>
            <div class="stat-label">Below 50% Attendance</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <h5><i class="fas fa-filter me-2"></i>Filter Trainee</h5>
        <form method="GET" action="{{ route('trainees.index') }}" class="row align-items-end">
            <div class="col-md-3 mb-3">
                <label for="search" class="form-label">Search Trainee</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Search by name, ID..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="condition" class="form-label">Condition</label>
                <select class="form-control" id="condition" name="condition">
                    <option value="">All Conditions</option>
                    @if(isset($conditions))
                        @foreach($conditions as $condition)
                            <option value="{{ $condition }}" {{ request('condition') == $condition ? 'selected' : '' }}>
                                {{ $condition }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="centre" class="form-label">Centre</label>
                <select class="form-control" id="centre" name="centre">
                    <option value="">All Centre</option>
                    @if(isset($centres))
                        @foreach($centres as $centre)
                            <option value="{{ $centre->centre_name }}" {{ request('centre') == $centre->centre_name ? 'selected' : '' }}>
                                {{ $centre->centre_name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Trainee Grid -->
    @if(isset($trainees) && count($trainees) > 0)
        <div class="trainee-grid">
            @foreach($trainees as $trainee)
                <div class="trainee-card">
                    <div class="trainee-card-header">
                        @if($trainee->avatar && file_exists(public_path('storage/' . str_replace('storage/', '', $trainee->avatar))))
                            <img src="{{ asset($trainee->avatar) }}?v={{ time() }}" 
                                 alt="{{ $trainee->name ?? ($trainee->trainee_first_name . ' ' . $trainee->trainee_last_name) }}" 
                                 class="trainee-avatar"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="avatar-placeholder" style="display: none;">
                                {{ strtoupper(substr($trainee->trainee_first_name ?? 'T', 0, 1)) }}
                            </div>
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($trainee->trainee_first_name ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        
                        <h6 class="trainee-name">{{ $trainee->name ?? ($trainee->trainee_first_name . ' ' . $trainee->trainee_last_name) }}</h6>
                        <div class="trainee-id">ID: {{ $trainee->trainee_id ?? 'N/A' }}</div>
                    </div>
                    <div class="trainee-card-body">
                        <div class="condition-badge">
                            {{ $trainee->condition ?? $trainee->trainee_condition ?? 'N/A' }}
                        </div>
                        
                        <div class="trainee-info">
                            <div class="info-row">
                                <span class="info-label">Age:</span>
                                <span class="info-value">
                                    @if(isset($trainee->trainee_date_of_birth))
                                        {{ \Carbon\Carbon::parse($trainee->trainee_date_of_birth)->age }} years
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Gender:</span>
                                <span class="info-value">{{ $trainee->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Centre:</span>
                                <span class="info-value">{{ $trainee->centre_name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Overall Progress Section -->
                        <div class="attendance-section mt-3">
                            <div class="attendance-label">
                                <span>Overall Progress</span>
                                <span class="attendance-percentage {{ isset($trainee->meets_attendance_threshold) && $trainee->meets_attendance_threshold ? 'text-success' : 'text-warning' }}">
                                    {{ isset($trainee->session_progress) ? round($trainee->session_progress, 1) : 0 }}%
                                </span>
                            </div>
                            <div class="progress mt-1">
                                <div class="progress-bar {{ isset($trainee->meets_attendance_threshold) && $trainee->meets_attendance_threshold ? 'bg-success' : 'bg-warning' }}" 
                                     style="width: {{ isset($trainee->session_progress) ? round($trainee->session_progress) : 0 }}%"></div>
                            </div>
                            @if(isset($trainee->meets_attendance_threshold) && !$trainee->meets_attendance_threshold)
                                <small class="text-warning mt-1 d-block">
                                    <i class="fas fa-exclamation-triangle"></i> Below 50% progress
                                </small>
                            @elseif(isset($trainee->meets_attendance_threshold) && $trainee->meets_attendance_threshold)
                                <small class="text-success mt-1 d-block">
                                    <i class="fas fa-check-circle"></i> Good overall progress
                                </small>
                            @endif
                        </div>

                        <div class="trainee-actions">
                            <a href="{{ route('trainees.show', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn-action btn-view" title="View Complete Profile">
                                <i class="fas fa-user"></i>Profile
                            </a>
                            <a href="{{ route('trainees.schedule', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn-action btn-schedule" title="View Schedule">
                                <i class="fas fa-calendar"></i>Schedule
                            </a>
                            <a href="{{ route('trainees.edit', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn-action btn-edit" title="Edit Trainee Information">
                                <i class="fas fa-edit"></i>Edit
                            </a>
                            <a href="{{ route('trainees.attendance', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn-action btn-attendance" title="View Attendance">
                                <i class="fas fa-clipboard-check"></i>Attendance
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <!-- Enhanced Pagination -->
        @if(method_exists($trainees, 'hasPages') && $trainees->hasPages())
        <div class="pagination-container">
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing <span class="info-highlight">{{ $trainees->firstItem() }}</span> to 
                    <span class="info-highlight">{{ $trainees->lastItem() }}</span> of 
                    <span class="info-highlight">{{ $trainees->total() }}</span> trainees
                </div>
                
                <nav aria-label="Trainee pagination">
                    <ul class="pagination">
                        {{-- Previous Page Link --}}
                        @if ($trainees->onFirstPage())
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link">
                                    <i class="fas fa-chevron-left me-1"></i>Previous
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $trainees->previousPageUrl() }}" rel="prev">
                                    <i class="fas fa-chevron-left me-1"></i>Previous
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $start = max($trainees->currentPage() - 2, 1);
                            $end = min($start + 4, $trainees->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $trainees->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $trainees->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $i }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $trainees->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        @if($end < $trainees->lastPage())
                            @if($end < $trainees->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $trainees->url($trainees->lastPage()) }}">{{ $trainees->lastPage() }}</a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($trainees->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $trainees->nextPageUrl() }}" rel="next">
                                    Next<i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link">
                                    Next<i class="fas fa-chevron-right ms-1"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>No Trainee Found</h4>
            <p>No trainees match your current filters or none have been registered yet.</p>
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <a href="{{ route('trainees.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Register First Trainee
            </a>
            @endif
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
// Handle page loading
document.addEventListener('DOMContentLoaded', function() {

    // Auto-submit filter form on select change
    const conditionSelect = document.getElementById('condition');
    const centreSelect = document.getElementById('centre');
    
    if (conditionSelect) {
        conditionSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (centreSelect) {
        centreSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>
@endsection