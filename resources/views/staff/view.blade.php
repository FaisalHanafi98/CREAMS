@extends('layouts.app')

@section('title')
{{ $staffMember->name ?? 'Staff Member' }} - Staff Profile | CREAMS
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #c850c0;
        --success-color: #2ed573;
        --danger-color: #ff4757;
        --warning-color: #ffa502;
        --info-color: #1e90ff;
        --dark-color: #1a2a3a;
        --light-color: #f8f9fa;
        --border-color: #e9ecef;
        --transition-speed: 0.3s;
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

    .role-admin { background: linear-gradient(45deg, #dc3545, #c82333); color: white; }
    .role-supervisor { background: linear-gradient(45deg, #fd7e14, #e55a00); color: white; }
    .role-teacher { background: linear-gradient(45deg, #32bdea, #0fa3cc); color: white; }
    .role-ajk { background: linear-gradient(45deg, #c850c0, #a843a0); color: white; }

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
            <li class="breadcrumb-item"><a href="{{ route('staffs.home') }}">Staff Directory</a></li>
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
                        <img src="{{ asset('storage/avatars/' . $staffMember->avatar) }}?v={{ time() }}" 
                             alt="{{ $staffMember->name }}" 
                             class="profile-avatar"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-avatar bg-light align-items-center justify-content-center" style="display: none;">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
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
                    <a href="{{ route('staffs.edit', ['encrypted_id' => $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)]) }}" class="action-btn btn-edit">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('staffs.home') }}" class="action-btn btn-back">
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
                
                @if($staffMember->about)
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-info-circle"></i>About
                    </div>
                    <div class="info-value">{{ $staffMember->about }}</div>
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
                            <div class="stats-label">Active Activity</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card">
                            <div class="stats-number">{{ $stats['total_trainees'] ?? 0 }}</div>
                            <div class="stats-label">Total Trainee</div>
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
                
                @php
                    $currentUserRole = session('role');
                    $currentUserId = session('id');
                    $canMarkAttendance = false;
                @endphp
                
                <div class="d-grid gap-2">
                    <a href="{{ route('staffs.edit', ['encrypted_id' => $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('staffs.schedule', $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)) }}" class="btn btn-outline-success">
                        <i class="fas fa-calendar-alt me-2"></i>View Schedule
                    </a>
                    <a href="{{ route('staffs.attendance', $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)) }}" class="btn btn-outline-info">
                        <i class="fas fa-user-clock me-2"></i>View Attendance
                    </a>
                    @if($currentUserRole === 'admin')
                    <a href="{{ route('staffs.activities', $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)) }}" class="btn btn-outline-info">
                        <i class="fas fa-tasks me-2"></i>View Activity
                    </a>
                    @endif
                    @if($staffMember->role === 'teacher')
                    <a href="{{ route('staffs.trainees', $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)) }}" class="btn btn-outline-warning">
                        <i class="fas fa-users me-2"></i>Assigned Trainee
                    </a>
                    @endif
                    
                    @php
                        // Users can mark their own attendance
                        if ($staffMember->id == $currentUserId) {
                            $canMarkAttendance = true;
                        }
                        // Admin can mark for anyone
                        elseif ($currentUserRole === 'admin') {
                            $canMarkAttendance = true;
                        }
                        // Supervisor can mark for staff in their centre
                        elseif ($currentUserRole === 'supervisor' && $staffMember->centre_id === session('centre_id') && in_array($staffMember->role, ['teacher', 'ajk'])) {
                            $canMarkAttendance = true;
                        }
                    @endphp
                    
                    @if($canMarkAttendance)
                    <button type="button" class="btn btn-outline-info" id="markAttendanceBtn" data-user-id="{{ $staffMember->id }}" data-user-name="{{ $staffMember->name }}">
                        <i class="fas fa-clock me-2"></i>Mark Attendance
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-labelledby="markAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="markAttendanceModalLabel">
                    <i class="fas fa-clock mr-2"></i>Mark Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="attendanceStatusAlert" class="alert" style="display: none;"></div>
                
                <form id="attendanceForm">
                    <input type="hidden" id="attendanceUserId" name="user_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="attendanceStatus" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="attendanceStatus" name="status" required>
                                <option value="">Select Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="sick_leave">Sick Leave</option>
                                <option value="emergency_leave">Emergency Leave</option>
                                <option value="authorized_leave">Authorized Leave</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="attendanceType" class="form-label fw-bold">Type</label>
                            <select class="form-select" id="attendanceType" name="attendance_type" required>
                                <option value="">Select Type</option>
                                <option value="check_in">Check In</option>
                                <option value="check_out">Check Out</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="attendanceRemarks" class="form-label fw-bold">Remarks (Optional)</label>
                        <textarea class="form-control" id="attendanceRemarks" name="remarks" rows="3" placeholder="Add any additional notes..."></textarea>
                    </div>
                    
                    <div class="border-top pt-3">
                        <h6 class="fw-bold mb-2">Attendance Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Date:</small>
                                <span class="fw-bold" id="attendanceDate"></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Time:</small>
                                <span class="fw-bold" id="attendanceTime"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitAttendance">
                    <i class="fas fa-check mr-2"></i>Mark Attendance
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    const markAttendanceBtn = document.getElementById('markAttendanceBtn');
    const attendanceForm = document.getElementById('attendanceForm');
    const submitBtn = document.getElementById('submitAttendance');
    
    if (markAttendanceBtn) {
        // Update time display every second
        function updateDateTime() {
            const now = new Date();
            document.getElementById('attendanceDate').textContent = now.toLocaleDateString('en-MY', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('attendanceTime').textContent = now.toLocaleTimeString('en-MY', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        
        // Mark Attendance Button Click
        markAttendanceBtn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            // Set user data
            document.getElementById('attendanceUserId').value = userId;
            document.getElementById('markAttendanceModalLabel').innerHTML = 
                `<i class="fas fa-clock mr-2"></i>Mark Attendance - ${userName}`;
            
            // Update date/time
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            // Reset form
            attendanceForm.reset();
            document.getElementById('attendanceUserId').value = userId;
            showAlert('', '', false);
            
            // Check today's status
            fetch(`/centres/attendance/status/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let statusHtml = '<div class="alert alert-info"><h6>Today\'s Status</h6>';
                        
                        if (data.has_checked_in) {
                            statusHtml += '<span class="badge bg-success me-2">Checked In</span>';
                        }
                        if (data.has_checked_out) {
                            statusHtml += '<span class="badge bg-warning">Checked Out</span>';
                        }
                        if (!data.has_checked_in && !data.has_checked_out) {
                            statusHtml += '<span class="badge bg-secondary">No attendance marked</span>';
                        }
                        
                        statusHtml += '</div>';
                        showAlert(statusHtml, 'info', true);
                        
                        // Pre-select appropriate type
                        if (!data.has_checked_in) {
                            document.getElementById('attendanceType').value = 'check_in';
                        } else if (!data.has_checked_out) {
                            document.getElementById('attendanceType').value = 'check_out';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                });
            
            $('#markAttendanceModal').modal('show');
        });
        
        // Submit Attendance
        submitBtn.addEventListener('click', function() {
            const formData = new FormData(attendanceForm);
            
            // Validate required fields
            if (!formData.get('status') || !formData.get('attendance_type')) {
                showAlert('Please fill in all required fields.', 'danger', true);
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Marking...';
            
            // Submit attendance
            fetch('/centres/attendance/mark', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                    // Don't set Content-Type for FormData - let browser set it with boundary
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showAlert(data.message, 'success', true);
                    setTimeout(() => {
                        $('#markAttendanceModal').modal('hide');
                        location.reload(); // Refresh to show updated status
                    }, 1500);
                } else {
                    showAlert(data.message || 'Failed to mark attendance.', 'danger', true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert(`An error occurred: ${error.message}`, 'danger', true);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Mark Attendance';
            });
        });
        
        function showAlert(message, type, show) {
            const alertDiv = document.getElementById('attendanceStatusAlert');
            if (show && message) {
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = message;
                alertDiv.style.display = 'block';
            } else {
                alertDiv.style.display = 'none';
            }
        }
    }
});
</script>
@endpush