@extends('layouts.app')

@section('title', 'Attendance Management')

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

    .attendance-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .stats-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-present { color: var(--success-color); }
    .stats-absent { color: var(--danger-color); }
    .stats-late { color: var(--warning-color); }
    .stats-total { color: var(--info-color); }

    .attendance-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        border: none;
        overflow: hidden;
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.3s ease;
    }

    .user-item:last-child {
        border-bottom: none;
    }

    .user-item:hover {
        background-color: #f8f9fa;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--border-color);
        margin-right: 1rem;
    }

    .user-info h6 {
        margin: 0;
        font-weight: 600;
        color: var(--dark-color);
    }

    .user-info small {
        color: #6c757d;
    }

    .attendance-status {
        margin-left: auto;
        text-align: right;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-present { background: var(--success-color); color: white; }
    .status-absent { background: var(--danger-color); color: white; }
    .status-late { background: var(--warning-color); color: white; }
    .status-leave { background: var(--info-color); color: white; }
    .status-none { background: #6c757d; color: white; }

    .attendance-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .attendance-table .table {
        margin: 0;
    }

    .attendance-table .table thead th {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
    }

    .attendance-table .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: var(--border-color);
    }

    .btn-mark-attendance {
        background: linear-gradient(45deg, var(--success-color), #20bf6b);
        border: none;
        color: white;
        border-radius: 25px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-mark-attendance:hover {
        background: linear-gradient(45deg, #20bf6b, var(--success-color));
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(46, 213, 115, 0.4);
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .time-display {
        background: linear-gradient(135deg, var(--dark-color), #34495e);
        color: white;
        padding: 1rem;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 1rem;
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
        .user-item {
            flex-direction: column;
            text-align: center;
        }
        
        .user-avatar {
            margin: 0 0 1rem 0;
        }
        
        .attendance-status {
            margin: 1rem 0 0 0;
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
            <li class="breadcrumb-item active">Attendance Management</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="attendance-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-clock mr-2"></i>Attendance Management
                    </h1>
                    <p class="mb-0">Monitor and manage daily attendance for staff and trainees across your centre</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="time-display">
                        <div id="currentDate" class="h6 mb-1"></div>
                        <div id="currentTime" class="h4 mb-0"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number stats-present">{{ $stats['today_present'] ?? 0 }}</div>
                <div class="stats-label">Present Today</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number stats-absent">{{ $stats['today_absent'] ?? 0 }}</div>
                <div class="stats-label">Absent Today</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number stats-late">{{ $stats['today_late'] ?? 0 }}</div>
                <div class="stats-label">Late Today</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number stats-total">{{ $stats['month_total'] ?? 0 }}</div>
                <div class="stats-label">This Month</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    @if(in_array(session('role'), ['admin', 'supervisor']))
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h5 class="mb-3 mb-md-0">
                    <i class="fas fa-filter me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="col-md-8">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="showTodayAttendance()">
                        <i class="fas fa-calendar-day me-2"></i>Today's Records
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="showWeeklyReport()">
                        <i class="fas fa-calendar-week me-2"></i>Weekly Report
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="exportAttendance()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Staff List with Quick Attendance -->
        <div class="col-lg-6">
            <div class="attendance-card">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Staff Members
                        <span class="badge bg-primary ms-2">{{ count($users) }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($users as $user)
                    <div class="user-item">
                        <div class="d-flex align-items-center flex-grow-1">
                            @if($user->avatar)
                                <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}" class="user-avatar">
                            @else
                                <div class="user-avatar bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                            @endif
                            <div class="user-info">
                                <h6>{{ $user->name }}</h6>
                                <small>{{ ucfirst($user->role) }} • {{ $user->email }}</small>
                            </div>
                        </div>
                        <div class="attendance-status">
                            @php
                                $todayAttendance = $user->staffAttendances->first();
                                $hasCheckedIn = $user->staffAttendances->where('attendance_type', 'check_in')->count() > 0;
                                $hasCheckedOut = $user->staffAttendances->where('attendance_type', 'check_out')->count() > 0;
                            @endphp
                            
                            @if($todayAttendance)
                                <span class="status-badge status-{{ $todayAttendance->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $todayAttendance->status)) }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($todayAttendance->attendance_time)->format('H:i') }}
                                    </small>
                                </div>
                            @else
                                <span class="status-badge status-none">Not Marked</span>
                            @endif
                            
                            @if(session('role') === 'admin' || 
                                (session('role') === 'supervisor' && $user->centre_id === session('centre_id')) ||
                                $user->id === session('id'))
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-mark-attendance" 
                                        onclick="openAttendanceModal({{ $user->id }}, '{{ $user->name }}')">
                                    <i class="fas fa-clock me-1"></i>Mark
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No staff members found</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Today's Attendance Records -->
        <div class="col-lg-6">
            <div class="attendance-table">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2"></i>Today's Attendance Records
                        <span class="badge bg-success ms-2">{{ $todayAttendance ? count($todayAttendance) : 0 }}</span>
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Marked By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayAttendance ?? [] as $attendance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($attendance->user->avatar)
                                            <img src="{{ asset('storage/avatars/' . $attendance->user->avatar) }}" 
                                                 alt="{{ $attendance->user->name }}" 
                                                 class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 30px; height: 30px;">
                                                <i class="fas fa-user text-muted" style="font-size: 12px;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold" style="font-size: 0.9rem;">{{ $attendance->user->name }}</div>
                                            <small class="text-muted">{{ ucfirst($attendance->user->role) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $attendance->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $attendance->attendance_type === 'check_in' ? 'success' : 'warning' }}">
                                        {{ $attendance->attendance_type === 'check_in' ? 'Check In' : 'Check Out' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($attendance->attendance_time)->format('H:i:s') }}</strong>
                                </td>
                                <td>
                                    @if($attendance->isSelfMarked())
                                        <span class="text-success">
                                            <i class="fas fa-user-check me-1"></i>Self
                                        </span>
                                    @else
                                        <span class="text-info">
                                            <i class="fas fa-user-tie me-1"></i>{{ $attendance->markedBy->name ?? 'System' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No attendance records for today</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                    <i class="fas fa-clock me-2"></i>Mark Attendance
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
                    <i class="fas fa-check me-2"></i>Mark Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Report Modal -->
<div class="modal fade" id="weeklyReportModal" tabindex="-1" aria-labelledby="weeklyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="weeklyReportModalLabel">
                    <i class="fas fa-calendar-week mr-2"></i>Weekly Report
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-calendar-week fa-3x text-info mb-3"></i>
                <h4>Weekly Reporting Feature</h4>
                <p class="lead">Weekly reporting feature will be available in future updates.</p>
                <p>This feature will include:</p>
                <ul class="text-left">
                    <li>Comprehensive weekly attendance summaries</li>
                    <li>Staff and trainee attendance analytics</li>
                    <li>Centre-specific performance metrics</li>
                    <li>Downloadable PDF reports</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-dismiss="modal">Got it!</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1" aria-labelledby="exportDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportDataModalLabel">
                    <i class="fas fa-download mr-2"></i>Export Data
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-download fa-3x text-success mb-3"></i>
                <h4>Data Export Functionality</h4>
                <p class="lead">Data export functionality will be implemented in upcoming releases.</p>
                <p>This feature will support:</p>
                <ul class="text-left">
                    <li>Excel (.xlsx) and CSV format exports</li>
                    <li>Customizable date range selection</li>
                    <li>Filtered exports by centre, role, or individual</li>
                    <li>Automated scheduled reports via email</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Understood!</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const attendanceForm = document.getElementById('attendanceForm');
    const submitBtn = document.getElementById('submitAttendance');
    
    // Update time display
    function updateDateTime() {
        const now = new Date();
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-MY', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-MY', {
            hour12: false
        });
        
        // Update modal time if open
        const modalDateElement = document.getElementById('attendanceDate');
        const modalTimeElement = document.getElementById('attendanceTime');
        if (modalDateElement && modalTimeElement) {
            modalDateElement.textContent = now.toLocaleDateString('en-MY', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            modalTimeElement.textContent = now.toLocaleTimeString('en-MY', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }
    
    // Update time every second
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Global function for opening attendance modal
    window.openAttendanceModal = function(userId, userName) {
        document.getElementById('attendanceUserId').value = userId;
        document.getElementById('markAttendanceModalLabel').innerHTML = 
            `<i class="fas fa-clock me-2"></i>Mark Attendance - ${userName}`;
        
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
            .catch(error => console.error('Error checking status:', error));
        
        $('#markAttendanceModal').modal('show');
    };
    
    // Submit attendance
    submitBtn.addEventListener('click', function() {
        const formData = new FormData(attendanceForm);
        
        if (!formData.get('status') || !formData.get('attendance_type')) {
            showAlert('Please fill in all required fields.', 'danger', true);
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Marking...';
        
        fetch('/centres/attendance/mark', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success', true);
                setTimeout(() => {
                    $('#markAttendanceModal').modal('hide');
                    location.reload();
                }, 1500);
            } else {
                showAlert(data.message || 'Failed to mark attendance.', 'danger', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger', true);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Mark Attendance';
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
    
    // Global functions for filter buttons
    window.showTodayAttendance = function() {
        // This could be expanded to filter the current view
        location.reload();
    };
    
    window.showWeeklyReport = function() {
        $('#weeklyReportModal').modal('show');
    };
    
    window.exportAttendance = function() {
        $('#exportDataModal').modal('show');
    };
    
    window.refreshData = function() {
        location.reload();
    };
});
</script>
@endpush