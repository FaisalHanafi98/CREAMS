@extends('layouts.app')

@section('title', 'Attendance Dashboard - ' . ($selectedCentre->centre_name ?? 'CREAMS'))

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #c850c0;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
        --shadow: 0 5px 20px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
    }

    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .attendance-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }

    .attendance-header::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .centre-selector {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .centre-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .centre-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: 2px solid var(--border-color);
        border-radius: 50px;
        background: white;
        color: var(--dark-color);
        text-decoration: none;
        transition: var(--transition);
        font-weight: 500;
        min-width: 120px;
        justify-content: center;
    }

    .centre-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: scale(1.05);
    }

    .centre-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(50, 189, 234, 0.3);
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .centre-btn.active:hover {
        color: white;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        text-align: center;
        border-left: 5px solid var(--primary-color);
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 60px;
        height: 60px;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        opacity: 0.1;
        border-radius: 50%;
        transform: translate(20px, -20px);
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .stats-card.success { border-left-color: var(--success-color); }
    .stats-card.warning { border-left-color: var(--warning-color); }
    .stats-card.danger { border-left-color: var(--danger-color); }
    .stats-card.info { border-left-color: var(--info-color); }

    .stats-number {
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .attendance-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .attendance-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid var(--info-color);
        transition: var(--transition);
        position: relative;
    }

    .attendance-card:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .attendance-card.present { border-left-color: var(--success-color); }
    .attendance-card.absent { border-left-color: var(--danger-color); }
    .attendance-card.late { border-left-color: var(--warning-color); }
    .attendance-card.excused { border-left-color: var(--info-color); }
    
    /* Clickable card styles */
    .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .card-link:hover {
        text-decoration: none;
        color: inherit;
    }
    
    .attendance-card.clickable {
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .attendance-card.clickable:hover {
        background: #e9ecef;
        transform: translateX(5px) scale(1.02);
        box-shadow: 0 8px 25px rgba(50, 189, 234, 0.2);
    }
    
    .card-overlay {
        position: absolute;
        top: 0;
        right: 0;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0 12px 0 12px;
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0;
        transform: translateY(-100%);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .attendance-card.clickable:hover .card-overlay {
        opacity: 1;
        transform: translateY(0);
    }

    .person-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .person-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .person-details h4 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--dark-color);
    }

    .person-details p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .attendance-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .attendance-status.present {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .attendance-status.absent {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }

    .attendance-status.late {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .attendance-status.excused {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info-color);
    }

    .attendance-status.not-marked {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .refresh-btn {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 500;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .refresh-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(50, 189, 234, 0.4);
    }

    .tabs {
        display: flex;
        background: white;
        border-radius: 15px;
        padding: 0.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
    }

    .tab-btn {
        flex: 1;
        padding: 1rem 2rem;
        border: none;
        background: transparent;
        border-radius: 10px;
        font-weight: 500;
        transition: var(--transition);
        color: var(--dark-color);
    }

    .tab-btn.active {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
        transform: scale(1.02);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    @media (max-width: 768px) {
        .centre-nav {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .stats-row {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
        
        .attendance-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(!in_array(session('role'), ['admin']))
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-triangle"></i> Access Denied</h4>
            <p>Only administrators can access the attendance dashboard. Please contact your supervisor.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Return to Dashboard</a>
        </div>
    @else
    <!-- Header -->
    <div class="attendance-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-calendar-check me-3"></i>
                    Attendance Dashboard
                    <span class="badge badge-danger ml-2">Admin Only</span>
                </h1>
                <p class="mb-0 opacity-90">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    {{ $selectedCentre->centre_name ?? 'All Centres' }} - {{ Carbon\Carbon::now()->format('l, F j, Y') }}
                    <br><small><i class="fas fa-info-circle"></i> Click on any card to mark attendance automatically</small>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button class="refresh-btn" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Centre Selector -->
    <div class="centre-selector">
        <div>
            <h5 class="mb-0">
                <i class="fas fa-building me-2"></i>
                Select Centre
            </h5>
            <small class="text-muted">Choose a centre to view attendance</small>
        </div>
        <div class="centre-nav">
            @foreach($centres as $centre)
                <a href="{{ route('centres.attendance.index', ['centre' => $centre->centre_id]) }}" 
                   class="centre-btn {{ $centre->centre_id == $selectedCentreId ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $centre->centre_name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-row">
        <!-- Staff Stats -->
        <div class="stats-card success">
            <div class="stats-number">{{ $stats['today_present'] ?? 0 }}</div>
            <div class="stats-label">Staff Present</div>
        </div>
        <div class="stats-card danger">
            <div class="stats-number">{{ $stats['today_absent'] ?? 0 }}</div>
            <div class="stats-label">Staff Absent</div>
        </div>
        <div class="stats-card warning">
            <div class="stats-number">{{ $stats['today_late'] ?? 0 }}</div>
            <div class="stats-label">Staff Late</div>
        </div>
        <!-- Trainee Stats -->
        <div class="stats-card success">
            <div class="stats-number">{{ $traineeStats['today_present'] ?? 0 }}</div>
            <div class="stats-label">Trainees Present</div>
        </div>
        <div class="stats-card danger">
            <div class="stats-number">{{ $traineeStats['today_absent'] ?? 0 }}</div>
            <div class="stats-label">Trainees Absent</div>
        </div>
        <div class="stats-card info">
            <div class="stats-number">{{ $traineeStats['total_trainees'] ?? 0 }}</div>
            <div class="stats-label">Total Trainees</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('staff')">
            <i class="fas fa-users me-2"></i>
            Staff Attendance
        </button>
        <button class="tab-btn" onclick="switchTab('trainees')">
            <i class="fas fa-graduation-cap me-2"></i>
            Trainee Attendance
        </button>
    </div>

    <!-- Staff Attendance Tab -->
    <div id="staff-tab" class="tab-content active">
        <div class="attendance-section">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    Staff Attendance Today
                </div>
            </div>
            
            <div class="attendance-grid">
                @forelse($staff as $member)
                    @php
                        $attendance = $member->staffAttendances->first();
                        $status = $attendance ? $attendance->status : 'not-marked';
                        $statusLabel = $attendance ? ucfirst($attendance->status) : 'Not Marked';
                        $hasCheckedIn = $member->staffAttendances->first();
                    @endphp
                    <div class="attendance-card {{ $status }} clickable" data-user-id="{{ $member->id }}" data-user-name="{{ $member->name }}" onclick="markStaffAttendance({{ $member->id }}, '{{ $member->name }}', '{{ $status }}', {{ $hasCheckedIn ? 'false' : 'true' }})">
                        <div class="person-info">
                            <div class="person-avatar">
                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                            </div>
                            <div class="person-details">
                                <h4>{{ $member->first_name }} {{ $member->last_name }}</h4>
                                <p>{{ ucfirst($member->role) }} • {{ $member->email }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="attendance-status {{ $status }}">
                                <i class="fas fa-circle"></i>
                                {{ $statusLabel }}
                            </span>
                            @if($attendance)
                                @php
                                    // Show who actually marked it
                                    if ($attendance->marked_by_email) {
                                        // Someone else marked it (admin or supervisor)
                                        if ($attendance->marked_by_email === $member->email) {
                                            $markedBy = 'Self';
                                        } else {
                                            $markedBy = $attendance->marked_by_email;
                                        }
                                    } else {
                                        $markedBy = 'Self';
                                    }
                                @endphp
                                <small class="text-muted">
                                    {{ $attendance->check_in_time?->format('g:i A') ?? '—' }}
                                    <br><small>by {{ $markedBy }}</small>
                                </small>
                            @endif
                        </div>
                        <div class="card-overlay">
                            <i class="fas fa-clock"></i>
                            <span>
                                @if($hasCheckedIn)
                                    Change Status
                                @else
                                    Click to Check In
                                @endif
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No staff found for this centre</h5>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Trainee Attendance Tab -->
    <div id="trainees-tab" class="tab-content">
        <div class="attendance-section">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    Trainee Attendance Today
                </div>
            </div>
            
            <div class="attendance-grid">
                @forelse($trainees as $trainee)
                    @php
                        $attendance = $trainee->traineeAttendances->first();
                        $status = $attendance ? $attendance->status : 'not-marked';
                        $statusLabel = $attendance ? ucfirst($attendance->status) : 'Not Marked';
                    @endphp
                    <div class="attendance-card {{ $status }} clickable" data-trainee-id="{{ $trainee->id }}" data-trainee-name="{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}" onclick="markTraineeAttendance({{ $trainee->id }}, '{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}', '{{ $status }}')">
                        <div class="person-info">
                            <div class="person-avatar">
                                {{ strtoupper(substr($trainee->trainee_first_name, 0, 1) . substr($trainee->trainee_last_name, 0, 1)) }}
                            </div>
                            <div class="person-details">
                                <h4>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h4>
                                <p>{{ $trainee->trainee_condition }} • ID: {{ $trainee->unique_identifier ?? $trainee->id }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="attendance-status {{ $status }}">
                                <i class="fas fa-circle"></i>
                                {{ $statusLabel }}
                            </span>
                            @if($attendance)
                                @php
                                    // Show who actually marked the trainee attendance
                                    if ($attendance->markedBy) {
                                        $markedByEmail = $attendance->markedBy->email;
                                    } else {
                                        $markedByEmail = 'System';
                                    }
                                    $timeMarked = $attendance->marked_at ? $attendance->marked_at->format('g:i A') : 'N/A';
                                @endphp
                                <small class="text-muted">
                                    {{ $timeMarked }}
                                    <br><small>by {{ $markedByEmail }}</small>
                                </small>
                            @endif
                        </div>
                        <div class="card-overlay">
                            <i class="fas fa-clock"></i>
                            <span>Click to Mark Attendance</span>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No trainees found for this centre</h5>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Attendance Marking Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm">
                    <div class="mb-3">
                        <label for="personName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="personName" readonly>
                        <input type="hidden" id="personId">
                        <input type="hidden" id="personType">
                    </div>
                    <div class="mb-3">
                        <label for="attendanceStatus" class="form-label">Status</label>
                        <select class="form-control" id="attendanceStatus" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>
                    <div class="mb-3" id="attendanceTypeDiv">
                        <label for="attendanceType" class="form-label">Type</label>
                        <select class="form-control" id="attendanceType" required>
                            <option value="check_in">Check In</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="remarks" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAttendance()">Mark Attendance</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh every 10 minutes with conflict detection
let lastRefreshTime = Date.now();
let refreshInterval;

function setupAutoRefresh() {
    refreshInterval = setInterval(function() {
        // Check if user was just marking attendance (within last 30 seconds)
        if (Date.now() - lastRefreshTime > 30000) {
            location.reload();
        }
    }, 600000); // 10 minutes
}

// Initialize auto refresh on page load
document.addEventListener('DOMContentLoaded', function() {
    setupAutoRefresh();
});

// Reset refresh timer when user interacts
function resetRefreshTimer() {
    lastRefreshTime = Date.now();
}

function markStaffAttendance(userId, userName, currentStatus, canCheckIn) {
    resetRefreshTimer();
    
    document.getElementById('personId').value = userId;
    document.getElementById('personName').value = userName;
    document.getElementById('personType').value = 'staff';
    
    // Only check-in is allowed
    const attendanceTypeSelect = document.getElementById('attendanceType');
    attendanceTypeSelect.value = 'check_in';
    
    if (canCheckIn) {
        document.getElementById('attendanceStatus').value = 'present';
    }
    
    // Show attendance type selection for staff
    document.getElementById('attendanceTypeDiv').style.display = 'block';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
}

function markTraineeAttendance(traineeId, traineeName, currentStatus) {
    resetRefreshTimer();
    
    document.getElementById('personId').value = traineeId;
    document.getElementById('personName').value = traineeName;
    document.getElementById('personType').value = 'trainee';
    
    // Hide attendance type for trainees (always session-based)
    document.getElementById('attendanceTypeDiv').style.display = 'none';
    
    // Set default status
    if (currentStatus === 'not-marked') {
        document.getElementById('attendanceStatus').value = 'present';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
}

function submitAttendance() {
    const personId = document.getElementById('personId').value;
    const personType = document.getElementById('personType').value;
    const status = document.getElementById('attendanceStatus').value;
    const attendanceType = document.getElementById('attendanceType').value;
    const remarks = document.getElementById('remarks').value;
    
    // Show loading state
    const submitBtn = document.querySelector('#attendanceModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';
    submitBtn.disabled = true;
    
    const data = {
        user_id: personId,
        status: status,
        remarks: remarks,
        _token: '{{ csrf_token() }}'
    };
    
    if (personType === 'staff') {
        data.attendance_type = attendanceType;
        console.log('Staff attendance data being sent:', data);
        
        fetch('/centres/attendance/mark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Staff attendance response:', data);
            if (data.success) {
                console.log('Staff attendance success response received');
                
                // Show success message first
                showNotification('success', data.message);
                
                // Try to close modal - use multiple methods as fallbacks
                try {
                    const modalElement = document.getElementById('attendanceModal');
                    if (modalElement) {
                        // Method 1: Try Bootstrap 5
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Method 2: Try creating new Bootstrap instance
                            const newModal = new bootstrap.Modal(modalElement);
                            newModal.hide();
                        }
                    }
                } catch (e) {
                    console.log('Modal close attempt failed, will reload page:', e);
                }
                
                // Refresh page after short delay
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', data.message || 'Failed to mark staff attendance');
            }
        })
        .catch(error => {
            console.error('Staff attendance error:', error);
            showNotification('error', 'Network error or server issue. Please check your connection and try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    } else {
        // Handle trainee attendance marking
        const traineeData = {
            trainee_id: personId,
            status: status,
            remarks: remarks,
            _token: '{{ csrf_token() }}'
        };
        
        console.log('Trainee attendance data being sent:', traineeData);
        
        fetch('/centres/attendance/mark-trainee', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(traineeData)
        })
        .then(response => {
            console.log('Raw trainee attendance response:', response);
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Parsed trainee attendance response:', data);
            if (data && data.success) {
                console.log('Trainee attendance success response received');
                
                // Show success message first
                showNotification('success', data.message || 'Trainee attendance marked successfully!');
                
                // Try to close modal - use multiple methods as fallbacks
                try {
                    const modalElement = document.getElementById('attendanceModal');
                    if (modalElement) {
                        // Method 1: Try Bootstrap 5
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Method 2: Try creating new Bootstrap instance
                            const newModal = new bootstrap.Modal(modalElement);
                            newModal.hide();
                        }
                    }
                } catch (e) {
                    console.log('Modal close attempt failed, will reload page:', e);
                }
                
                // Refresh page after short delay
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('Response indicates failure:', data);
                showNotification('error', data.message || 'Failed to mark trainee attendance');
            }
        })
        .catch(error => {
            console.error('Trainee attendance error details:', error);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            
            // More specific error messages based on error type
            if (error.name === 'SyntaxError') {
                showNotification('error', 'Server response format error. The attendance may have been marked - please refresh the page.');
            } else if (error.message.includes('HTTP error')) {
                showNotification('error', `Server error (${error.message}). The attendance may have been marked - please refresh the page.`);
            } else {
                showNotification('error', 'Connection error occurred. The attendance may have been marked - please refresh the page to check.');
            }
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
}

function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Fix modal close functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add manual close button handler
    const closeBtn = document.querySelector('#attendanceModal .btn-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Close button clicked');
            const modal = document.getElementById('attendanceModal');
            if (modal) {
                // Try Bootstrap hide first
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    // Fallback to jQuery
                    $(modal).modal('hide');
                }
            }
        });
    }
    
    // Also handle clicking outside the modal
    const modal = document.getElementById('attendanceModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                console.log('Backdrop clicked');
                const bsModal = bootstrap.Modal.getInstance(this);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    $(this).modal('hide');
                }
            }
        });
    }
});

// Debug function to test session data
window.debugAttendanceSystem = function() {
    console.log('=== DEBUGGING ATTENDANCE SYSTEM ===');
    
    fetch('/debug-attendance', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Debug response:', data);
        alert('Debug info logged to console. Check browser console (F12) for details.');
    })
    .catch(error => {
        console.error('Debug request failed:', error);
        alert('Debug request failed: ' + error.message);
    });
};
</script>

<!-- Debug Button (only visible in development) -->
@if(config('app.debug', false))
<div style="position: fixed; bottom: 20px; left: 20px; z-index: 9999;">
    <button onclick="debugAttendanceSystem()" class="btn btn-warning btn-sm">
        🐛 Debug Attendance
    </button>
</div>
@endif

@endsection