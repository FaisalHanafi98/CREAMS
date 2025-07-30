@extends('layouts.app')

@section('title', 'Attendance - ' . ($staffMember->name ?? 'Staff Member') . ' - CREAMS')

@section('styles')
<link href="{{ asset('css/dropdown-improvements.css') }}" rel="stylesheet">
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
        box-shadow: 0 5px 20px rgba(50, 189, 234, 0.3);
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
    .stat-card.early-leave .stat-number { color: var(--info-color); }
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

    .status-early-leave {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info-color);
        border: 1px solid var(--info-color);
    }

    .status-overtime {
        background: rgba(200, 80, 192, 0.1);
        color: var(--secondary-color);
        border: 1px solid var(--secondary-color);
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
        box-shadow: 0 4px 15px rgba(50, 189, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(50, 189, 234, 0.4);
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

    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
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
                    <h1><i class="fas fa-user-clock mr-3"></i>Staff Attendance Record</h1>
                    <p>{{ $staffMember->name ?? 'Staff Member' }} ({{ $staffMember->iium_id }}) - Work Hours & Attendance Tracking</p>
                </div>
                <div class="col-md-4 text-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('staffs.home') }}">Staff</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('staffs.profile', ['encrypted_id' => $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)]) }}">{{ $staffMember->name ?? 'Profile' }}</a>
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
                    Attendance Overview - {{ date('F Y') }}
                </h5>
                <span class="text-muted">Working Days: {{ $workingDays ?? 22 }}</span>
            </div>

            <div class="stats-grid">
                <div class="stat-card present">
                    <div class="stat-number">{{ $attendanceStats['present_days'] ?? 20 }}</div>
                    <div class="stat-label">Present Days</div>
                </div>
                <div class="stat-card late">
                    <div class="stat-number">{{ $attendanceStats['late_arrivals'] ?? 2 }}</div>
                    <div class="stat-label">Late Arrivals</div>
                </div>
                <div class="stat-card early-leave">
                    <div class="stat-number">{{ $attendanceStats['early_leaves'] ?? 1 }}</div>
                    <div class="stat-label">Early Leaves</div>
                </div>
                <div class="stat-card rate">
                    <div class="stat-number">{{ $attendanceStats['attendance_rate'] ?? 95 }}%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="attendance-card text-center">
                    <h6 class="text-muted mb-2">Total Hours This Month</h6>
                    <h3 class="text-primary">{{ $monthlyStats['total_hours'] ?? '168' }}h</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="attendance-card text-center">
                    <h6 class="text-muted mb-2">Average Daily Hours</h6>
                    <h3 class="text-success">{{ $monthlyStats['avg_daily'] ?? '8.2' }}h</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="attendance-card text-center">
                    <h6 class="text-muted mb-2">Overtime Hours</h6>
                    <h3 class="text-warning">{{ $monthlyStats['overtime'] ?? '12' }}h</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="attendance-card text-center">
                    <h6 class="text-muted mb-2">This Week</h6>
                    <h3 class="text-info">{{ $weeklyStats['hours'] ?? '40' }}h</h3>
                </div>
            </div>
        </div>

        <!-- Attendance History Table -->
        <div class="attendance-card">
            <div class="attendance-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    Recent Attendance History
                </h5>
                <div>
                    <select class="form-select" id="monthFilter" style="width: auto; display: inline-block; min-height: 42px; padding: 10px 12px;">
                        <option value="">This Month</option>
                        @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}">{{ \Carbon\Carbon::now()->subMonths($i)->format('F Y') }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="attendance-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Hours Worked</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Sample staff attendance data
                            $sampleStaffAttendance = [
                                ['date' => '2025-01-27', 'day' => 'Monday', 'check_in' => '08:00 AM', 'check_out' => '05:00 PM', 'hours' => '9.0', 'status' => 'present', 'remarks' => 'Regular day'],
                                ['date' => '2025-01-26', 'day' => 'Sunday', 'check_in' => '08:30 AM', 'check_out' => '05:15 PM', 'hours' => '8.75', 'status' => 'late', 'remarks' => 'Traffic jam'],
                                ['date' => '2025-01-25', 'day' => 'Saturday', 'check_in' => '07:45 AM', 'check_out' => '06:00 PM', 'hours' => '10.25', 'status' => 'overtime', 'remarks' => 'Project deadline'],
                                ['date' => '2025-01-24', 'day' => 'Friday', 'check_in' => '08:00 AM', 'check_out' => '04:30 PM', 'hours' => '8.5', 'status' => 'early-leave', 'remarks' => 'Medical appointment'],
                                ['date' => '2025-01-23', 'day' => 'Thursday', 'check_in' => '08:00 AM', 'check_out' => '05:00 PM', 'hours' => '9.0', 'status' => 'present', 'remarks' => 'Normal working day'],
                                ['date' => '2025-01-22', 'day' => 'Wednesday', 'check_in' => '08:15 AM', 'check_out' => '05:30 PM', 'hours' => '9.25', 'status' => 'late', 'remarks' => 'Meeting preparation'],
                                ['date' => '2025-01-21', 'day' => 'Tuesday', 'check_in' => '07:55 AM', 'check_out' => '05:00 PM', 'hours' => '9.08', 'status' => 'present', 'remarks' => 'Early arrival'],
                                ['date' => '2025-01-20', 'day' => 'Monday', 'check_in' => '08:00 AM', 'check_out' => '05:45 PM', 'hours' => '9.75', 'status' => 'overtime', 'remarks' => 'Extra training session'],
                            ];
                        @endphp

                        @foreach($sampleStaffAttendance as $record)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($record['date'])->format('M j, Y') }}</strong>
                            </td>
                            <td>{{ $record['day'] }}</td>
                            <td>{{ $record['check_in'] }}</td>
                            <td>{{ $record['check_out'] }}</td>
                            <td><strong>{{ $record['hours'] }}h</strong></td>
                            <td>
                                <span class="status-badge status-{{ $record['status'] }}">
                                    {{ ucfirst(str_replace('-', ' ', $record['status'])) }}
                                </span>
                            </td>
                            <td>{{ $record['remarks'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('staffs.profile', ['encrypted_id' => $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)]) }}" class="btn btn-primary">
                <i class="fas fa-user"></i>Back to Profile
            </a>
            
            <a href="{{ route('staffs.schedule', ['encrypted_id' => $staffMember->encrypted_id ?? \App\Helpers\EncryptionHelper::generateEncryptedId($staffMember->id)]) }}" class="btn btn-secondary">
                <i class="fas fa-calendar"></i>View Schedule
            </a>
            
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <button class="btn btn-secondary" onclick="exportAttendance()">
                <i class="fas fa-file-export"></i>Export Report
            </button>

            <button class="btn btn-secondary" onclick="markAttendance()">
                <i class="fas fa-clock"></i>Mark Attendance
            </button>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Month filter functionality
    const monthFilter = document.getElementById('monthFilter');
    if (monthFilter) {
        monthFilter.addEventListener('change', function() {
            // This would typically reload the page with the selected month
            console.log('Filter changed to:', this.value);
        });
    }
});

// Export attendance function
function exportAttendance() {
    alert('Staff attendance report export functionality would be implemented here.');
}

// Mark attendance function
function markAttendance() {
    alert('Quick attendance marking functionality would be implemented here.');
}

// Add hover effects to stat cards
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(-5px) scale(1)';
    });
});
</script>
@endsection