@extends('layouts.app')

@section('title', 'Trainee Profile - ' . ($trainee->trainee_first_name ?? 'Trainee') . ' ' . ($trainee->trainee_last_name ?? '') . ' - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
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

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: rgba(255,255,255,0.7);
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .container-fluid {
        background: var(--light-bg);
        min-height: 100vh;
        padding: 20px;
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        border: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }

    .profile-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .profile-card-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }

    .profile-card-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-card-header .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-size: 16px;
    }

    .profile-avatar-section {
        text-align: center;
        margin-bottom: 30px;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 8px 25px rgba(200, 80, 192, 0.3);
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .profile-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 35px rgba(200, 80, 192, 0.4);
    }

    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 5px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .profile-id {
        font-size: 1.1rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
        background: #f8f9fc;
        padding: 5px 15px;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 15px;
    }

    .profile-status {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border: 2px solid var(--success-color);
    }

    .status-inactive {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border: 2px solid var(--danger-color);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-item {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark-color);
        word-wrap: break-word;
    }

    .info-value.empty {
        color: #6c757d;
        font-style: italic;
    }

    .btn {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
        text-transform: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(200, 80, 192, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-warning {
        background: var(--warning-color);
        color: #212529;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    .btn-warning:hover {
        background: #e0a800;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 193, 7, 0.4);
        color: #212529;
        text-decoration: none;
    }

    .btn-info {
        background: var(--secondary-color);
        color: white;
        box-shadow: 0 4px 15px rgba(50, 189, 234, 0.3);
    }

    .btn-info:hover {
        background: #2a9fd7;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(50, 189, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-light {
        background: rgba(255,255,255,0.9);
        border: 2px solid #e9ecef;
        color: var(--dark-color);
    }

    .btn-light:hover {
        background: white;
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: var(--dark-color);
        text-decoration: none;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #f1f3f4;
    }

    .timeline-section {
        margin-top: 30px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        height: 100%;
        width: 2px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .timeline-item {
        position: relative;
        margin-bottom: 25px;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -37px;
        top: 20px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: 3px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .timeline-date {
        font-size: 12px;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .timeline-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--dark-color);
        margin: 5px 0;
    }

    .timeline-content {
        color: #6c757d;
        font-size: 14px;
        line-height: 1.5;
    }

    .progress-section {
        margin-top: 20px;
    }

    .progress-item {
        margin-bottom: 20px;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: var(--dark-color);
    }

    .progress {
        height: 12px;
        border-radius: 10px;
        background: #f1f3f4;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .progress-bar {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 10px;
        transition: width 0.6s ease;
        position: relative;
        overflow: hidden;
    }

    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 33%, rgba(255,255,255,0.2) 33%, rgba(255,255,255,0.2) 66%, transparent 66%);
        background-size: 30px 30px;
        animation: progress-animation 2s linear infinite;
    }

    @keyframes progress-animation {
        0% { transform: translateX(-30px); }
        100% { transform: translateX(30px); }
    }

    .condition-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
        box-shadow: 0 3px 10px rgba(200, 80, 192, 0.3);
    }

    .print-section {
        text-align: center;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #f1f3f4;
    }

    @media print {
        .btn, .action-buttons, .print-section {
            display: none !important;
        }
        
        .trainee-header {
            background: var(--primary-color) !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
        }
        
        .profile-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding: 15px;
        }

        .trainee-header {
            text-align: center;
            padding: 1.5rem;
        }

        .trainee-header h1 {
            font-size: 2rem;
        }

        .profile-card {
            padding: 20px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .btn {
            justify-content: center;
            width: 100%;
            margin-bottom: 10px;
        }

        .profile-name {
            font-size: 1.5rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
        }
    }

    /* Tab Navigation Styles */
    .profile-tabs {
        margin-bottom: 30px;
    }

    .tab-nav {
        display: flex;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .tab-nav-item {
        flex: 1;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        background: transparent;
        font-weight: 600;
        color: #6c757d;
        position: relative;
    }

    .tab-nav-item:hover {
        background: rgba(200, 80, 192, 0.1);
        color: var(--primary-color);
    }

    .tab-nav-item.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .tab-nav-item i {
        display: block;
        margin-bottom: 8px;
        font-size: 20px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Profile Header -->
    <div class="trainee-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-user-circle mr-3"></i>Trainee Profile</h1>
                    <p>Comprehensive profile information and progress tracking</p>
                </div>
                <div class="col-md-4 text-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.index') }}">Trainee</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $trainee->trainee_first_name ?? 'Profile' }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Overview -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #f1f3f4;">
                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 5px;">{{ $totalActivities ?? 0 }}</div>
                <div class="stat-label" style="color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;">Total Activities</div>
            </div>
            <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #f1f3f4;">
                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--success-color); margin-bottom: 5px;">{{ $attendanceRate ?? 0 }}%</div>
                <div class="stat-label" style="color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;">Attendance Rate</div>
            </div>
            <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #f1f3f4;">
                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 5px;">{{ $recentActivities ?? 0 }}</div>
                <div class="stat-label" style="color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;">This Week</div>
            </div>
            <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #f1f3f4;">
                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--warning-color); margin-bottom: 5px;">{{ $enrollmentDuration ?? 0 }}mo</div>
                <div class="stat-label" style="color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem;">Enrolled Since</div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="profile-tabs">
            <div class="tab-nav">
                <button class="tab-nav-item active" onclick="showTab('personal-info')">
                    <i class="fas fa-user"></i>
                    Personal Information
                </button>
                <button class="tab-nav-item" onclick="showTab('program-info')">
                    <i class="fas fa-graduation-cap"></i>
                    Program Information
                </button>
            </div>

            <!-- Tab Content -->
            <div id="personal-info" class="tab-content active">
                <div class="row">
                    <!-- Main Profile Information -->
                    <div class="col-lg-8">
                <!-- Basic Information Card -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="card-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5>Personal Information</h5>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">
                                {{ $trainee->trainee_first_name ?? 'N/A' }} {{ $trainee->trainee_last_name ?? '' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email Address</div>
                            <div class="info-value {{ empty($trainee->trainee_email) ? 'empty' : '' }}">
                                {{ $trainee->trainee_email ?? 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value {{ empty($trainee->trainee_phone_number) ? 'empty' : '' }}">
                                {{ $trainee->trainee_phone_number ?? 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">
                                @if($trainee->trainee_date_of_birth)
                                    {{ $trainee->trainee_date_of_birth->format('F j, Y') }}
                                    <small class="text-muted">({{ $trainee->trainee_date_of_birth->age }} years old)</small>
                                @else
                                    <span class="empty">Not provided</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Gender</div>
                            <div class="info-value {{ empty($trainee->gender) ? 'empty' : '' }}">
                                {{ $trainee->gender ?? 'Not specified' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Home Address</div>
                            <div class="info-value {{ empty($trainee->address) ? 'empty' : '' }}">
                                {{ $trainee->address ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Information Card -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="card-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h5>Medical & Centre Information</h5>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Medical Condition</div>
                            <div class="info-value">
                                @if($trainee->trainee_condition)
                                    <span class="condition-badge">{{ $trainee->trainee_condition }}</span>
                                @else
                                    <span class="empty">Not specified</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Assigned Centre</div>
                            <div class="info-value {{ empty($trainee->centre_name) ? 'empty' : '' }}">
                                {{ $trainee->centre_name ?? 'Not assigned' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Registration Date</div>
                            <div class="info-value">
                                {{ $trainee->created_at ? $trainee->created_at->format('F j, Y') : 'Unknown' }}
                                @if($trainee->created_at)
                                    <small class="text-muted">({{ $trainee->created_at->diffForHumans() }})</small>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="profile-status {{ $trainee->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($trainee->status ?? 'active') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($trainee->medical_history)
                    <div style="margin-top: 20px;">
                        <div class="info-label">Medical History & Notes</div>
                        <div class="info-value" style="background: #f8f9fc; padding: 15px; border-radius: 8px; margin-top: 10px; line-height: 1.6;">
                            {{ $trainee->medical_history }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Guardian Information Card -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Guardian & Emergency Contact</h5>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Guardian Name</div>
                            <div class="info-value {{ empty($trainee->guardian_name) ? 'empty' : '' }}">
                                {{ $trainee->guardian_name ?? 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Guardian Relationship</div>
                            <div class="info-value {{ empty($trainee->guardian_relationship) ? 'empty' : '' }}">
                                {{ $trainee->guardian_relationship ?? 'Not specified' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Guardian Phone</div>
                            <div class="info-value {{ empty($trainee->guardian_phone) ? 'empty' : '' }}">
                                {{ $trainee->guardian_phone ?? 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Guardian Email</div>
                            <div class="info-value {{ empty($trainee->guardian_email) ? 'empty' : '' }}">
                                {{ $trainee->guardian_email ?? 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Emergency Contact</div>
                            <div class="info-value {{ empty($trainee->emergency_contact_name) ? 'empty' : '' }}">
                                {{ $trainee->emergency_contact_name ?? 'Not provided' }}
                                @if($trainee->emergency_contact_phone)
                                    <br><small class="text-muted">{{ $trainee->emergency_contact_phone }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Emergency Relationship</div>
                            <div class="info-value {{ empty($trainee->emergency_contact_relationship) ? 'empty' : '' }}">
                                {{ $trainee->emergency_contact_relationship ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>

                    @if($trainee->guardian_address)
                    <div style="margin-top: 20px;">
                        <div class="info-label">Guardian Address</div>
                        <div class="info-value" style="background: #f8f9fc; padding: 15px; border-radius: 8px; margin-top: 10px;">
                            {{ $trainee->guardian_address }}
                        </div>
                    </div>
                    @endif
                </div>

                @if($trainee->additional_notes)
                <!-- Additional Information Card -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="card-icon">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        <h5>Additional Information</h5>
                    </div>

                    <div class="info-value" style="background: #f8f9fc; padding: 20px; border-radius: 10px; line-height: 1.6; border-left: 4px solid var(--primary-color);">
                        {{ $trainee->additional_notes }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Profile Avatar & Basic Info -->
                <div class="profile-card">
                    <div class="profile-avatar-section">
                        <img src="{{ $trainee->getAvatarUrlAttribute() }}" 
                             alt="{{ $trainee->trainee_first_name ?? 'Trainee' }}" 
                             class="profile-avatar"
                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                        
                        <div class="profile-name">
                            {{ $trainee->trainee_first_name ?? 'Unknown' }} {{ $trainee->trainee_last_name ?? '' }}
                        </div>
                        
                        @if($trainee->trainee_id)
                        <div class="profile-id">
                            ID: {{ $trainee->trainee_id }}
                        </div>
                        @endif
                        
                        <div>
                            <span class="profile-status {{ $trainee->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($trainee->status ?? 'active') }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('trainees.edit', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i>Edit Profile
                        </a>
                        @endif
                        
                        <a href="{{ route('trainees.schedule', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn btn-info">
                            <i class="fas fa-calendar"></i>Schedule
                        </a>
                        
                        <a href="{{ route('trainees.attendance', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn btn-warning">
                            <i class="fas fa-clipboard-check"></i>Attendance
                        </a>
                        
                        <button onclick="alert('This feature will be implemented in the future')" class="btn btn-light">
                            <i class="fas fa-print"></i>Print Profile
                        </button>
                        
                        <a href="{{ route('trainees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>Back to List
                        </a>
                    </div>
                </div>





            </div>
        </div>
        </div>
        <!-- End Personal Info Tab -->

        <!-- Program Information Tab -->
        <div id="program-info" class="tab-content">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Attendance Overview -->
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <div class="card-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h5>Attendance Overview</h5>
                        </div>

                        <div class="progress-section">
                            <div class="progress-label">
                                <span>Attendance Rate</span>
                                <span>{{ $attendanceRate ?? 0 }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $attendanceRate ?? 0 }}%"></div>
                            </div>
                        </div>

                        @if($totalActivities > 0)
                        <div class="progress-section">
                            <div class="progress-label">
                                <span>Activities Enrolled</span>
                                <span>{{ $totalActivities }}</span>
                            </div>
                            <div class="info-value" style="margin-top: 10px; font-size: 14px; color: #6c757d;">
                                Currently enrolled in {{ $totalActivities }} {{ $totalActivities == 1 ? 'activity' : 'activities' }}
                            </div>
                        </div>
                        @else
                        <div class="progress-section">
                            <div style="text-align: center; padding: 20px; color: #6c757d;">
                                <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p style="margin: 0; font-size: 14px;">No activities enrolled yet</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Current Activities -->
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <div class="card-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h5>Current Activities</h5>
                        </div>

                        @if(isset($currentActivities) && $currentActivities->count() > 0)
                            @foreach($currentActivities as $activity)
                                <div style="background: #f8f9fc; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--primary-color);">
                                    <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 5px;">{{ $activity->activity_name }}</div>
                                    <div style="color: #6c757d; font-size: 0.85rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                                        <span><i class="fas fa-calendar me-1"></i>Enrolled: {{ $activity->enrollment_date ? date('M j, Y', strtotime($activity->enrollment_date)) : 'N/A' }}</span>
                                        <span><i class="fas fa-info-circle me-1"></i>Status: {{ ucfirst($activity->enrollment_status ?? 'active') }}</span>
                                        <span><i class="fas fa-tag me-1"></i>{{ ucfirst($activity->category ?? 'General') }}</span>
                                        @if($activity->activity_description)
                                            <span><i class="fas fa-sticky-note me-1"></i>{{ \Illuminate\Support\Str::limit($activity->activity_description, 50) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div style="text-align: center; padding: 30px; color: #6c757d;">
                                <i class="fas fa-clipboard-list" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p>No activities enrolled yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Recent Attendance -->
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <div class="card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h5>Recent Attendance</h5>
                        </div>

                        @if(isset($recentAttendance) && $recentAttendance->count() > 0)
                            @foreach($recentAttendance->take(5) as $record)
                                <div style="background: #f8f9fc; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--primary-color);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div>
                                            <div style="font-weight: 600; color: var(--dark-color);">{{ $record->activity_name }}</div>
                                            <div style="color: #6c757d; font-size: 0.85rem;">{{ $record->date ? date('F j, Y', strtotime($record->date)) : 'N/A' }}</div>
                                        </div>
                                        <span class="badge" style="background: {{ $record->status == 'present' ? 'var(--success-color)' : ($record->status == 'late' ? 'var(--warning-color)' : 'var(--danger-color)') }}; color: white; padding: 4px 8px; border-radius: 10px; font-size: 0.7rem;">
                                            {{ ucfirst($record->status ?? 'unknown') }}
                                        </span>
                                    </div>
                                    @if($record->remarks)
                                        <div style="color: #6c757d; font-size: 0.8rem;">{{ $record->remarks }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div style="text-align: center; padding: 30px; color: #6c757d;">
                                <i class="fas fa-calendar-check" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p style="margin: 0; font-size: 14px;">No attendance records yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- End Program Info Tab -->

        </div>
        <!-- End Profile Tabs -->
    </div>
</div>
@endsection

@section('scripts')
<script>
// Tab switching function
function showTab(tabId) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab nav items
    const tabNavItems = document.querySelectorAll('.tab-nav-item');
    tabNavItems.forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to clicked tab nav item
    const clickedNavItem = event.target.closest('.tab-nav-item');
    if (clickedNavItem) {
        clickedNavItem.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars on load
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });

    // Add print functionality
    window.printProfile = function() {
        window.print();
    };

    // Avatar error handling
    const avatar = document.querySelector('.profile-avatar');
    if (avatar) {
        avatar.addEventListener('error', function() {
            this.src = '{{ asset("images/default-avatar.png") }}';
        });
    }
});

// Calculate profile completion percentage
function calculateProfileCompletion(trainee) {
    const fields = [
        '{{ $trainee->trainee_first_name }}',
        '{{ $trainee->trainee_last_name }}',
        '{{ $trainee->trainee_email }}',
        '{{ $trainee->trainee_phone_number }}',
        '{{ $trainee->trainee_date_of_birth }}',
        '{{ $trainee->gender }}',
        '{{ $trainee->trainee_condition }}',
        '{{ $trainee->centre_name }}',
        '{{ $trainee->guardian_name }}',
        '{{ $trainee->guardian_email }}',
        '{{ $trainee->guardian_relationship }}'
    ];
    
    let filledFields = 0;
    fields.forEach(field => {
        if (field && field.trim() !== '') {
            filledFields++;
        }
    });
    
    return Math.round((filledFields / fields.length) * 100);
}
</script>

@php
function calculateProfileCompletion($trainee) {
    $fields = [
        $trainee->trainee_first_name,
        $trainee->trainee_last_name,
        $trainee->trainee_email,
        $trainee->trainee_phone_number,
        $trainee->trainee_date_of_birth,
        $trainee->gender,
        $trainee->trainee_condition,
        $trainee->centre_name,
        $trainee->guardian_name,
        $trainee->guardian_email,
        $trainee->guardian_relationship
    ];
    
    $filledFields = 0;
    foreach ($fields as $field) {
        if (!empty($field)) {
            $filledFields++;
        }
    }
    
    return round(($filledFields / count($fields)) * 100);
}
@endphp
@endsection