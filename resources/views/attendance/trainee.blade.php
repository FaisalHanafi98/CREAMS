<!-- resources/views/attendance/trainee.blade.php -->
@extends('layouts.app')

@section('title', 'Trainee Attendance Details | CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-bottom: 20px;
    }
    
    .calendar-day {
        padding: 10px;
        border-radius: 5px;
        text-align: centre;
        background-color: #f8f9fa;
        position: relative;
    }
    
    .day-header {
        font-weight: bold;
        background-color: #343a40;
        color: white;
    }
    
    .current-month {
        font-weight: bold;
    }
    
    .day-number {
        font-size: 14px;
        font-weight: 600;
    }
    
    .day-status {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-top: 5px;
    }
    
    .status-present {
        background-color: #28a745;
    }
    
    .status-absent {
        background-color: #dc3545;
    }
    
    .status-late {
        background-color: #ffc107;
    }
    
    .status-excused {
        background-color: #17a2b8;
    }
    
    .status-none {
        background-color: #e9ecef;
    }
    
    .attendance-details {
        margin-top: 10px;
        font-size: 12px;
        color: #6c757d;
    }
    
    .day-tooltip {
        position: absolute;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
        display: none;
        z-index: 10;
    }
    
    .calendar-day:hover .day-tooltip {
        display: block;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }
    
    .timeline-item:before {
        content: "";
        position: absolute;
        left: 16px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #dee2e6;
    }
    
    .timeline-item:last-child:before {
        bottom: 50%;
    }
    
    .timeline-date {
        position: absolute;
        left: 0;
        top: 0;
        width: 32px;
        height: 32px;
        background-color: #fff;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        text-align: centre;
        line-height: 28px;
        z-index: 1;
    }
    
    .timeline-content {
        background-color: #fff;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-date.status-present {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }
    
    .timeline-date.status-absent {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
    }
    
    .timeline-date.status-late {
        background-color: #ffc107;
        color: black;
        border-color: #ffc107;
    }
    
    .timeline-date.status-excused {
        background-color: #17a2b8;
        color: white;
        border-color: #17a2b8;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}'s Attendance</h5>
                    <div class="card-options">
                        <a href="{{ route('attendance.report') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-centre">
                                <div class="avatar mr-3">
                                    <img src="{{ asset($trainee->trainee_avatar) }}" alt="{{ $trainee->trainee_first_name }}" class="rounded-circle" width="60">
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h4>
                                    <p class="mb-0 text-muted">{{ $trainee->trainee_condition }} • {{ $trainee->centre_name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('attendance.trainee', ['id' => $trainee->id]) }}" method="GET" class="d-flex justify-content-end">
                                <div class="input-group" style="max-width: 400px;">
                                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date', $startDate) }}">
                                    <div class="input-group-prepend input-group-append">
                                        <span class="input-group-text">to</span>
                                    </div>
                                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date', $endDate) }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attendance Summary -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-centre">
                                    <div class="display-4 text-success">{{ $attendanceRate['present'] }}</div>
                                    <h5 class="mt-2">Present Days</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-centre">
                                    <div class="display-4 text-danger">{{ $attendanceRate['absent'] }}</div>
                                    <h5 class="mt-2">Absent Days</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-centre">
                                    <div class="display-4 text-warning">{{ $attendanceRate['late'] }}</div>
                                    <h5 class="mt-2">Late Days</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-centre">
                                    <div class="display-4">{{ $attendanceRate['percentage'] }}%</div>
                                    <h5 class="mt-2">Attendance Rate</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Calendar -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Monthly Attendance Calendar</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-centre mb-3">
                                <h5 class="mb-0">{{ date('F Y', strtotime($calendarMonth)) }}</h5>
                                <div>
                                    <a href="{{ route('attendance.trainee', ['id' => $trainee->id, 'month' => date('Y-m', strtotime($calendarMonth . ' -1 month')), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-chevron-left"></i> Previous Month
                                    </a>
                                    <a href="{{ route('attendance.trainee', ['id' => $trainee->id, 'month' => date('Y-m', strtotime($calendarMonth . ' +1 month')), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-sm btn-outline-primary">
                                        Next Month <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="calendar-grid">
                                <!-- Days of week headers -->
                                <div class="calendar-day day-header">Sun</div>
                                <div class="calendar-day day-header">Mon</div>
                                <div class="calendar-day day-header">Tue</div>
                                <div class="calendar-day day-header">Wed</div>
                                <div class="calendar-day day-header">Thu</div>
                                <div class="calendar-day day-header">Fri</div>
                                <div class="calendar-day day-header">Sat</div>
                                
                                <!-- Calendar days -->
                                @foreach($calendarDays as $day)
                                    <div class="calendar-day {{ $day['current_month'] ? 'current-month' : '' }}" 
                                        style="{{ $day['is_today'] ? 'border: 2px solid #007bff;' : '' }}">
                                        <div class="day-number">{{ $day['day'] }}</div>
                                        
                                        @if(isset($day['status']))
                                            <div class="day-status status-{{ $day['status'] }}"></div>
                                            <div class="day-tooltip">
                                                <strong>{{ ucfirst($day['status']) }}</strong>
                                                @if(isset($day['remarks']) && !empty($day['remarks']))
                                                <br>{{ $day['remarks'] }}
                                                @endif
                                            </div>
                                        @else
                                            <div class="day-status status-none"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="d-flex justify-content-centre">
                                <div class="d-flex align-items-centre mr-4">
                                    <span class="day-status status-present mr-1"></span> Present
                                </div>
                                <div class="d-flex align-items-centre mr-4">
                                    <span class="day-status status-absent mr-1"></span> Absent
                                </div>
                                <div class="d-flex align-items-centre mr-4">
                                    <span class="day-status status-late mr-1"></span> Late
                                </div>
                                <div class="d-flex align-items-centre">
                                    <span class="day-status status-excused mr-1"></span> Excused
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Attendance Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @forelse($attendanceRecords as $record)
                                    <div class="timeline-item">
                                        <div class="timeline-date status-{{ $record->status }}">
                                            <i class="fas fa-{{ 
                                                $record->status == 'present' ? 'check' : 
                                                ($record->status == 'absent' ? 'times' : 
                                                ($record->status == 'late' ? 'clock' : 'notes-medical')) 
                                            }}"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1">{{ date('l, F j, Y', strtotime($record->date)) }}</h6>
                                                <span class="badge badge-{{ 
                                                    $record->status == 'present' ? 'success' : 
                                                    ($record->status == 'absent' ? 'danger' : 
                                                    ($record->status == 'late' ? 'warning' : 'info')) 
                                                }}">{{ ucfirst($record->status) }}</span>
                                            </div>
                                            
                                            @if($record->activity_id)
                                                <p class="mb-1"><strong>Activity:</strong> {{ $record->activity->name ?? 'Unknown Activity' }}</p>
                                            @endif
                                            
                                            @if($record->remarks)
                                                <p class="mb-0"><strong>Remarks:</strong> {{ $record->remarks }}</p>
                                            @endif
                                            
                                            <small class="text-muted">Marked by: {{ $record->markedBy->name ?? 'System' }}</small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-centre py-5">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <h5>No attendance records found for the selected date range.</h5>
                                        <p class="text-muted">Try selecting a different date range or check if attendance has been recorded for this trainee.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection