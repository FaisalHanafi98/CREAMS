<!-- resources/views/attendance/report.blade.php -->
@extends('layouts.app')

@section('title', 'Attendance Reports | CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Attendance Reports</h5>
                    <div class="card-options">
                        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Attendance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.report') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', $startDate) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', $endDate) }}">
                            </div>
                            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'supervisor')
                            <div class="col-md-3 mb-3">
                                <label for="centre_name">Centre</label>
                                <select class="form-control" id="centre_name" name="centre_name">
                                    <option value="">All Centres</option>
                                    @foreach($centres as $centre)
                                    <option value="{{ $centre->centre_name }}" {{ request('centre_name') == $centre->centre_name ? 'selected' : '' }}>{{ $centre->centre_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-2"></i>Generate Report
                                </button>
                                <button type="button" class="btn btn-success ml-2" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel mr-2"></i>Export
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Attendance Overview Chart -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Attendance Overview</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="attendanceOverviewChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trainee Attendance Statistics -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="attendanceStatsTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Trainee ID</th>
                                    <th>Name</th>
                                    <th>Centre</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Excused</th>
                                    <th>Attendance Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trainees as $trainee)
                                <tr>
                                    <td>{{ $trainee->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-centre">
                                            <div class="avatar mr-3">
                                                <img src="{{ asset($trainee->trainee_avatar) }}" alt="{{ $trainee->trainee_first_name }}" class="rounded-circle" width="40">
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $trainee->centre_name }}</td>
                                    <td>{{ $attendanceData[$trainee->id]['present'] ?? 0 }}</td>
                                    <td>{{ $attendanceData[$trainee->id]['absent'] ?? 0 }}</td>
                                    <td>{{ $attendanceData[$trainee->id]['late'] ?? 0 }}</td>
                                    <td>{{ $attendanceData[$trainee->id]['excused'] ?? 0 }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $attendanceRate = $attendanceData[$trainee->id]['percentage'] ?? 0;
                                                $bgClass = $attendanceRate >= 90 ? 'bg-success' :
                                                          ($attendanceRate >= 75 ? 'bg-primary' :
                                                          ($attendanceRate >= 60 ? 'bg-warning' : 'bg-danger'));
                                            @endphp
                                            <div class="progress-bar {{ $bgClass }}" role="progressbar" style="width: {{ $attendanceRate }}%;" aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100">{{ $attendanceRate }}%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('attendance.trainee', ['id' => $trainee->id, 'start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate)]) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
        // Create attendance overview chart
        var ctx = document.getElementById('attendanceOverviewChart').getContext('2d');
        var overviewChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Present', 'Absent', 'Late', 'Excused'],
                datasets: [{
                    label: 'Attendance Overview',
                    data: [
                        {{ $summaryStats['present_count'] ?? 0 }},
                        {{ $summaryStats['absent_count'] ?? 0 }},
                        {{ $summaryStats['late_count'] ?? 0 }},
                        {{ $summaryStats['excused_count'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',  // green for present
                        'rgba(220, 53, 69, 0.7)',  // red for absent
                        'rgba(255, 193, 7, 0.7)',  // yellow for late
                        'rgba(23, 162, 184, 0.7)'  // cyan for excused
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });

    // Function to export attendance data to Excel
    function exportToExcel() {
        // Get the table data
        const table = document.getElementById('attendanceStatsTable');
        const wb = XLSX.utils.table_to_book(table, { sheet: "Attendance Report" });
        
        // Save the Excel file
        const dateRange = document.getElementById('start_date').value + ' to ' + document.getElementById('end_date').value;
        XLSX.writeFile(wb, 'Attendance Report - ' + dateRange + '.xlsx');
    }
</script>
@endsection