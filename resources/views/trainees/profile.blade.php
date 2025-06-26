@extends('layouts.app')

@section('title')
{{ $trainee->full_name ?? 'Trainee Profile' }} - CREAMS
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #8B5CF6;
        --secondary-color: #A78BFA;
        --accent-color: #7C3AED;
        --success-color: #10B981;
        --warning-color: #F59E0B;
        --danger-color: #EF4444;
        --info-color: #3B82F6;
        --dark-color: #1F2937;
        --light-bg: #F9FAFB;
        --border-color: #E5E7EB;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
        background-color: var(--light-bg);
        font-family: 'Poppins', sans-serif;
    }

    .profile-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .profile-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(50px, -50px);
    }

    .profile-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        transform: translate(-50px, 50px);
    }

    .profile-header-content {
        position: relative;
        z-index: 2;
    }

    .trainee-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.3);
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        margin-right: 2rem;
    }

    .avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        margin-right: 2rem;
        border: 4px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .trainee-info h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .info-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .info-card:hover {
        box-shadow: var(--card-hover-shadow);
        transform: translateY(-2px);
    }

    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .card-header h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark-color);
        margin: 0;
        margin-left: 0.75rem;
    }

    .card-header i {
        font-size: 1.5rem;
        color: var(--primary-color);
        width: 24px;
        text-align: center;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-weight: 600;
        color: #6B7280;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 1rem;
        color: var(--dark-color);
        font-weight: 500;
    }

    .condition-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .badge-primary { background: rgba(139, 92, 246, 0.1); color: var(--primary-color); }
    .badge-info { background: rgba(59, 130, 246, 0.1); color: var(--info-color); }
    .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
    .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
    .badge-secondary { background: rgba(107, 114, 128, 0.1); color: #6B7280; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-hover-shadow);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label {
        color: #6B7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.875rem;
    }

    .activity-item {
        background: var(--light-bg);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: white;
        box-shadow: var(--card-shadow);
    }

    .activity-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .activity-meta {
        color: #6B7280;
        font-size: 0.875rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .attendance-chart {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .attendance-stat {
        text-align: center;
        padding: 1rem;
        border-radius: 8px;
        background: var(--light-bg);
    }

    .attendance-stat.present { background: rgba(16, 185, 129, 0.1); }
    .attendance-stat.late { background: rgba(245, 158, 11, 0.1); }
    .attendance-stat.absent { background: rgba(239, 68, 68, 0.1); }
    .attendance-stat.excused { background: rgba(107, 114, 128, 0.1); }

    .attendance-number {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .attendance-number.present { color: var(--success-color); }
    .attendance-number.late { color: var(--warning-color); }
    .attendance-number.absent { color: var(--danger-color); }
    .attendance-number.excused { color: #6B7280; }

    .attendance-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
        color: #6B7280;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        color: white;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-outline {
        background: transparent;
        border: 2px solid var(--primary-color);
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        color: var(--primary-color);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1.5rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-item.active {
        color: #6B7280;
    }

    .no-data {
        text-align: center;
        padding: 3rem;
        color: #6B7280;
    }

    .no-data i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem;
        }

        .profile-header-content {
            flex-direction: column;
            text-align: center;
        }

        .trainee-avatar,
        .avatar-placeholder {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .trainee-info h1 {
            font-size: 2rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="profile-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('traineeshome') }}">Trainees</a></li>
            <li class="breadcrumb-item active">{{ $trainee->full_name ?? 'Profile' }}</li>
        </ol>
    </nav>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-content d-flex align-items-center">
            @if($trainee->avatar)
                <img src="{{ $trainee->avatar_url }}" alt="{{ $trainee->full_name }}" class="trainee-avatar">
            @else
                <div class="avatar-placeholder">
                    {{ strtoupper(substr($trainee->trainee_first_name ?? 'T', 0, 1) . substr($trainee->trainee_last_name ?? 'R', 0, 1)) }}
                </div>
            @endif
            
            <div class="trainee-info flex-grow-1">
                <h1>{{ $trainee->full_name ?? 'Unknown Trainee' }}</h1>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="condition-badge badge-{{ $trainee->condition_badge_class ?? 'secondary' }}">
                        {{ $trainee->trainee_condition ?? 'No condition specified' }}
                    </span>
                    @if($age)
                        <span class="text-white opacity-75">
                            <i class="fas fa-birthday-cake me-2"></i>{{ $age }} years old
                        </span>
                    @endif
                </div>
                <div class="d-flex gap-4 text-white opacity-90">
                    @if($trainee->trainee_email)
                        <span><i class="fas fa-envelope me-2"></i>{{ $trainee->trainee_email }}</span>
                    @endif
                    @if($trainee->trainee_phone_number)
                        <span><i class="fas fa-phone me-2"></i>{{ $trainee->trainee_phone_number }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $totalActivities ?? 0 }}</div>
            <div class="stat-label">Total Activities</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $attendanceRate ?? 0 }}%</div>
            <div class="stat-label">Attendance Rate</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $recentActivities ?? 0 }}</div>
            <div class="stat-label">Recent Activities</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $enrollmentDuration ?? 'N/A' }}</div>
            <div class="stat-label">Enrolled Since</div>
        </div>
    </div>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8">
            <!-- Personal Info Card -->
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    <h3>Personal Information</h3>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $trainee->full_name ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            {{ $trainee->trainee_date_of_birth ? $trainee->trainee_date_of_birth->format('F j, Y') : 'Not provided' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $trainee->trainee_email ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">{{ $trainee->trainee_phone_number ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Centre</div>
                        <div class="info-value">{{ $trainee->centre_name ?? 'Not assigned' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Medical Condition</div>
                        <div class="info-value">
                            <span class="condition-badge badge-{{ $trainee->condition_badge_class ?? 'secondary' }}">
                                {{ $trainee->trainee_condition ?? 'Not specified' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guardian Information -->
            @if(isset($guardian))
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-users"></i>
                    <h3>Guardian Information</h3>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Guardian Name</div>
                        <div class="info-value">{{ $guardian['name'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Relationship</div>
                        <div class="info-value">{{ $guardian['relationship'] ?? 'Not specified' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">{{ $guardian['phone'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $guardian['email'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $guardian['address'] ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Current Activities -->
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-tasks"></i>
                    <h3>Current Activities</h3>
                </div>
                
                @if($trainee->activities && $trainee->activities->count() > 0)
                    @foreach($trainee->activities->take(5) as $activity)
                        <div class="activity-item">
                            <div class="activity-title">{{ $activity->activity_name ?? 'Unnamed Activity' }}</div>
                            <div class="activity-meta">
                                @if(isset($activity->pivot->enrollment_date))
                                    <span><i class="fas fa-calendar me-1"></i>Enrolled: {{ date('M j, Y', strtotime($activity->pivot->enrollment_date)) }}</span>
                                @endif
                                @if(isset($activity->pivot->status))
                                    <span><i class="fas fa-info-circle me-1"></i>Status: {{ ucfirst($activity->pivot->status) }}</span>
                                @endif
                                @if(isset($activity->category))
                                    <span><i class="fas fa-tag me-1"></i>{{ $activity->category }}</span>
                                @endif
                                @if(isset($activity->pivot->notes) && $activity->pivot->notes)
                                    <span><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($activity->pivot->notes, 50) }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No activities enrolled yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Attendance Card -->
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Attendance Overview</h3>
                </div>
                
                @if(isset($attendanceDays))
                    <div class="attendance-chart">
                        <div class="attendance-stat present">
                            <div class="attendance-number present">{{ $attendanceDays['present'] ?? 0 }}</div>
                            <div class="attendance-label">Present</div>
                        </div>
                        <div class="attendance-stat late">
                            <div class="attendance-number late">{{ $attendanceDays['late'] ?? 0 }}</div>
                            <div class="attendance-label">Late</div>
                        </div>
                        <div class="attendance-stat absent">
                            <div class="attendance-number absent">{{ $attendanceDays['absent'] ?? 0 }}</div>
                            <div class="attendance-label">Absent</div>
                        </div>
                        <div class="attendance-stat excused">
                            <div class="attendance-number excused">{{ $attendanceDays['excused'] ?? 0 }}</div>
                            <div class="attendance-label">Excused</div>
                        </div>
                    </div>
                @else
                    <div class="no-data">
                        <i class="fas fa-calendar-check"></i>
                        <p>No attendance data available</p>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i>
                    <h3>Quick Actions</h3>
                </div>
                
                <div class="action-buttons">
                    <a href="{{ route('traineeprofile.edit', $trainee->id) }}" class="btn-primary">
                        <i class="fas fa-edit"></i>Edit Profile
                    </a>
                    <a href="#" class="btn-outline">
                        <i class="fas fa-plus"></i>Add Activity
                    </a>
                    <a href="#" class="btn-outline">
                        <i class="fas fa-file-download"></i>Export PDF
                    </a>
                </div>
            </div>

            <!-- Recent Progress -->
            @if(isset($attendanceHistory) && count($attendanceHistory) > 0)
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-history"></i>
                    <h3>Recent Attendance</h3>
                </div>
                
                @foreach(array_slice($attendanceHistory, 0, 5) as $record)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="activity-title">{{ $record['day_name'] }}</div>
                                <div class="activity-meta">{{ date('F j, Y', strtotime($record['date'])) }}</div>
                            </div>
                            <span class="condition-badge badge-{{ $record['status'] == 'present' ? 'success' : ($record['status'] == 'late' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($record['status']) }}
                            </span>
                        </div>
                        @if(isset($record['remarks']))
                            <div class="mt-2 text-muted small">{{ $record['remarks'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
    
    // Add smooth hover animations
    $('.info-card, .stat-card').hover(
        function() { $(this).addClass('shadow-lg'); },
        function() { $(this).removeClass('shadow-lg'); }
    );
});
</script>
@endsection