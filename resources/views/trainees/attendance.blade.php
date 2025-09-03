@extends('layouts.app')

@section('title', 'Attendance - ' . ($trainee->trainee_first_name ?? 'Trainee') . ' ' . ($trainee->trainee_last_name ?? '') . ' - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    body {
        background-color: var(--light-bg);
    }

    .attendance-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        position: relative;
        overflow: hidden;
    }

    .attendance-header::before {
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

    .attendance-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .attendance-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .attendance-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        border: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }

    .attendance-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .attendance-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }

    .attendance-card-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-icon {
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1;
    }

    .stat-label {
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }

    .stat-card.present .stat-number { color: var(--success-color); }
    .stat-card.late .stat-number { color: var(--warning-color); }
    .stat-card.absent .stat-number { color: var(--danger-color); }
    .stat-card.rate .stat-number { color: var(--primary-color); }

    .attendance-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: var(--dark-color);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
        padding: 15px;
    }

    .table tbody td {
        padding: 15px;
        border-color: #f1f3f4;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f8f9fc;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-present {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
        border: 1px solid var(--success-color);
    }

    .status-late {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
        border: 1px solid var(--warning-color);
    }

    .status-absent {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
        border: 1px solid var(--danger-color);
    }

    .status-excused {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        border: 1px solid #6c757d;
    }

    .filter-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
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

    .btn {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
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

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }

    .attendance-chart {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Attendance Header -->
    <div class="attendance-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-clipboard-check mr-3"></i>Attendance Record</h1>
                    <p>{{ $trainee->trainee_first_name ?? 'Trainee' }} {{ $trainee->trainee_last_name ?? '' }} - Attendance History & Statistics</p>
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
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.show', $trainee->id) }}">{{ $trainee->trainee_first_name ?? 'Profile' }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Attendance Statistics -->
        <div class="attendance-card">
            <div class="attendance-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    Attendance Overview
                </h5>
                <div class="text-muted">
                    <div>Current Month: {{ date('F Y') }}</div>
                    <div style="margin-top: 5px;">Today: {{ date('l, F j, Y') }}</div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card present">
                    <div class="stat-number">{{ $attendanceStats['present'] }}</div>
                    <div class="stat-label">Present Days</div>
                </div>
                <div class="stat-card late">
                    <div class="stat-number">{{ $attendanceStats['late'] }}</div>
                    <div class="stat-label">Late Arrivals</div>
                </div>
                <div class="stat-card absent">
                    <div class="stat-number">{{ $attendanceStats['absent'] }}</div>
                    <div class="stat-label">Absent Days</div>
                </div>
                <div class="stat-card rate">
                    <div class="stat-number">{{ $attendanceStats['rate'] }}%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Attendance Records</h6>
            <form method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <select class="form-control" id="month" name="month">
                        <option value="">All Months</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select class="form-control" id="year" name="year">
                        <option value="">All Years</option>
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>Excused</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance History Table -->
        <div class="attendance-card">
            <div class="attendance-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    Attendance History
                </h5>
            </div>

            <div class="attendance-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Activity</th>
                            <th>Check In</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceHistory as $record)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($record->date)->format('M j, Y') }}</strong>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($record->date)->format('l') }}</td>
                            <td>{{ $record->activity ?? 'General Attendance' }}</td>
                            <td>{{ $record->marked_at ? \Carbon\Carbon::parse($record->marked_at)->format('h:i A') : '-' }}</td>
                            <td>
                                <span class="status-badge status-{{ $record->status }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td>{{ $record->remarks ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                No attendance records found for this trainee.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Attendance Chart -->
        <div class="attendance-chart">
            <div class="attendance-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    Monthly Attendance Trend
                </h5>
            </div>
            
            <div class="chart-container">
                <canvas id="attendanceChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('trainees.show', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn btn-primary">
                <i class="fas fa-user"></i>Back to Profile
            </a>
            
            <a href="{{ route('trainees.schedule', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn btn-secondary">
                <i class="fas fa-calendar"></i>View Schedule
            </a>
            
            @if(in_array(session('role'), ['admin']))
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                <i class="fas fa-check-circle"></i>Mark Today's Attendance (Admin Only)
            </button>
            @endif
            
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <button class="btn btn-secondary" onclick="exportAttendance()">
                <i class="fas fa-file-export"></i>Export Report
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-labelledby="markAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markAttendanceModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Mark Attendance for {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm">
                    @csrf
                    <input type="hidden" name="trainee_id" value="{{ $trainee->id }}">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="attendance_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="attendance_date" name="attendance_date" 
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="attendance_status" class="form-label">Attendance Status</label>
                            <select class="form-control" id="attendance_status" name="attendance_status" required>
                                <option value="">Select Status</option>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3" id="timeFields" style="display: none;">
                        <div class="col-md-12">
                            <label for="check_in_time" class="form-label">Check-in Time</label>
                            <input type="time" class="form-control" id="check_in_time" name="check_in_time">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="attendance_remarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="attendance_remarks" name="attendance_remarks" 
                                  rows="3" placeholder="Add any relevant notes about the attendance..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="activity_type" class="form-label">Activity/Session Type (Optional)</label>
                        <select class="form-control" id="activity_type" name="activity_type">
                            <option value="">General Attendance</option>
                            <option value="Physical Therapy">Physical Therapy</option>
                            <option value="Group Session">Group Session</option>
                            <option value="Individual Session">Individual Session</option>
                            <option value="Assessment">Assessment</option>
                            <option value="Group Activity">Group Activity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="markAttendance()">
                    <i class="fas fa-save"></i>Mark Attendance
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize attendance chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                @foreach($monthlyData as $month)
                '{{ $month["month"] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Attendance Rate (%)',
                data: [
                    @foreach($monthlyData as $month)
                    {{ $month["rate"] }},
                    @endforeach
                ],
                borderColor: '#c850c0',
                backgroundColor: 'rgba(200, 80, 192, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 6,
                    hoverRadius: 8
                }
            }
        }
    });

    // Auto-submit filter form on change
    const filterSelects = document.querySelectorAll('#month, #year, #status');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});

// Export attendance function
function exportAttendance() {
    // This would typically make an AJAX call to generate and download a report
    alert('Attendance report export functionality would be implemented here.');
}

// Add some interactivity to the stats cards
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(-5px) scale(1)';
    });
});

// Handle attendance status change to show/hide time fields
document.getElementById('attendance_status').addEventListener('change', function() {
    const timeFields = document.getElementById('timeFields');
    const currentTime = new Date().toTimeString().slice(0, 5);
    
    if (this.value === 'present' || this.value === 'late') {
        timeFields.style.display = 'block';
        if (this.value === 'present') {
            document.getElementById('check_in_time').value = currentTime;
        } else if (this.value === 'late') {
            // For late, set a slightly later time
            const lateTime = new Date();
            lateTime.setMinutes(lateTime.getMinutes() + 15);
            document.getElementById('check_in_time').value = lateTime.toTimeString().slice(0, 5);
        }
    } else {
        timeFields.style.display = 'none';
        document.getElementById('check_in_time').value = '';
    }
});

// Mark attendance function
function markAttendance() {
    const form = document.getElementById('attendanceForm');
    const formData = new FormData(form);
    const submitButton = document.querySelector('[onclick="markAttendance()"]');
    
    // Disable submit button to prevent double-click
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Marking...';
    
    // Convert FormData to regular object for JSON
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    fetch('{{ route("trainees.attendance.mark", ["encrypted_id" => \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Attendance marked successfully!');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('markAttendanceModal'));
            modal.hide();
            
            // Refresh page to show updated data
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to mark attendance'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while marking attendance. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save"></i>Mark Attendance';
    });
}
</script>
@endsection