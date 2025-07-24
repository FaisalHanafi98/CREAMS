<!-- resources/views/attendance/index.blade.php -->
@extends('layouts.app')

@section('title', 'Attendance Management | CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Attendance Management</h5>
                    <div class="card-options">
                        <a href="{{ route('attendance.report') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-chart-bar mr-2"></i>Attendance Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="date">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}">
                            </div>
                            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'supervisor')
                            <div class="col-md-3 mb-3">
                                <label for="center">Centre</label>
                                <select class="form-control" id="center" name="center_id">
                                    <option value="">All Centres</option>
                                    @foreach($centers as $center)
                                    <option value="{{ $center->center_id }}" {{ request('center_id') == $center->center_id ? 'selected' : '' }}>{{ $center->center_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="activity">Activity</label>
                                <select class="form-control" id="activity" name="activity_id">
                                    <option value="">All Activities</option>
                                    @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}" {{ request('activity_id') == $activity->id ? 'selected' : '' }}>{{ $activity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">
                        <input type="hidden" name="activity_id" value="{{ request('activity_id') }}">
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Trainee ID</th>
                                        <th>Name</th>
                                        <th>Condition</th>
                                        <th>Centre</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trainees as $trainee)
                                    <tr>
                                        <td>{{ $trainee->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar mr-3">
                                                    <img src="{{ asset($trainee->trainee_avatar) }}" alt="{{ $trainee->trainee_first_name }}" class="rounded-circle" width="40">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $trainee->trainee_condition }}</td>
                                        <td>{{ $trainee->center_name }}</td>
                                        <td>
                                            <input type="hidden" name="attendance[{{ $trainee->id }}][trainee_id]" value="{{ $trainee->id }}">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="attendance[{{ $trainee->id }}][status]" id="present-{{ $trainee->id }}" value="present" {{ isset($attendanceRecords[$trainee->id]) && $attendanceRecords[$trainee->id]->status == 'present' ? 'checked' : '' }}>
                                                <label class="form-check-label text-success" for="present-{{ $trainee->id }}">Present</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="attendance[{{ $trainee->id }}][status]" id="absent-{{ $trainee->id }}" value="absent" {{ isset($attendanceRecords[$trainee->id]) && $attendanceRecords[$trainee->id]->status == 'absent' ? 'checked' : '' }}>
                                                <label class="form-check-label text-danger" for="absent-{{ $trainee->id }}">Absent</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="attendance[{{ $trainee->id }}][status]" id="late-{{ $trainee->id }}" value="late" {{ isset($attendanceRecords[$trainee->id]) && $attendanceRecords[$trainee->id]->status == 'late' ? 'checked' : '' }}>
                                                <label class="form-check-label text-warning" for="late-{{ $trainee->id }}">Late</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="attendance[{{ $trainee->id }}][status]" id="excused-{{ $trainee->id }}" value="excused" {{ isset($attendanceRecords[$trainee->id]) && $attendanceRecords[$trainee->id]->status == 'excused' ? 'checked' : '' }}>
                                                <label class="form-check-label text-info" for="excused-{{ $trainee->id }}">Excused</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="attendance[{{ $trainee->id }}][remarks]" placeholder="Optional remarks" value="{{ isset($attendanceRecords[$trainee->id]) ? $attendanceRecords[$trainee->id]->remarks : '' }}">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No trainees found matching the selected criteria.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary Card -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Attendance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="stats-details">
                                    <div class="stats-value">{{ $stats['present_count'] ?? 0 }}</div>
                                    <div class="stats-label">Present</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon" style="background: linear-gradient(-135deg, #ff4757, #ff6b81);">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <div class="stats-details">
                                    <div class="stats-value">{{ $stats['absent_count'] ?? 0 }}</div>
                                    <div class="stats-label">Absent</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon" style="background: linear-gradient(-135deg, #ffa502, #ff7f50);">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stats-details">
                                    <div class="stats-value">{{ $stats['late_count'] ?? 0 }}</div>
                                    <div class="stats-label">Late</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon" style="background: linear-gradient(-135deg, #2e86de, #54a0ff);">
                                    <i class="fas fa-notes-medical"></i>
                                </div>
                                <div class="stats-details">
                                    <div class="stats-value">{{ $stats['excused_count'] ?? 0 }}</div>
                                    <div class="stats-label">Excused</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Date change automatic submit
        $('#date, #center, #activity').change(function() {
            $(this).closest('form').submit();
        });
        
        // Row highlighting based on attendance status
        $('input[type="radio"]').change(function() {
            const row = $(this).closest('tr');
            row.removeClass('table-success table-danger table-warning table-info');
            
            if ($(this).val() === 'present') {
                row.addClass('table-success');
            } else if ($(this).val() === 'absent') {
                row.addClass('table-danger');
            } else if ($(this).val() === 'late') {
                row.addClass('table-warning');
            } else if ($(this).val() === 'excused') {
                row.addClass('table-info');
            }
        });
        
        // Initialize row colors based on selected status
        $('input[type="radio"]:checked').each(function() {
            const row = $(this).closest('tr');
            if ($(this).val() === 'present') {
                row.addClass('table-success');
            } else if ($(this).val() === 'absent') {
                row.addClass('table-danger');
            } else if ($(this).val() === 'late') {
                row.addClass('table-warning');
            } else if ($(this).val() === 'excused') {
                row.addClass('table-info');
            }
        });
    });
</script>
@endsection