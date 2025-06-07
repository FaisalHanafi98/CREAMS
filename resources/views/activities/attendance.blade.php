@extends('layouts.app')

@section('title', 'Mark Attendance - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user-check"></i> Mark Attendance
        </h1>
        <div class="page-actions">
            <a href="{{ route('activities.sessions', $session->activity_id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Sessions
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>{{ $session->activity->activity_name }} - {{ $session->class_name }}</h5>
            <div class="row mt-3">
                <div class="col-md-3">
                    <p class="mb-1"><strong>Teacher:</strong> {{ $session->teacher->name }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Day:</strong> {{ $session->day_of_week }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Time:</strong> {{ date('g:i A', strtotime($session->start_time)) }} - {{ date('g:i A', strtotime($session->end_time)) }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('activities.attendance.store', $session->id) }}" method="POST">
        @csrf
        <input type="hidden" name="attendance_date" value="{{ $date }}">
        
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Trainee Attendance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table attendance-table">
                        <thead>
                            <tr>
                                <th>Trainee ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->enrollments as $enrollment)
                                @php
                                    $trainee = $enrollment->trainee;
                                    $attendance = $attendanceRecords->get($trainee->id);
                                @endphp
                                <tr>
                                    <td>{{ $trainee->trainee_id }}</td>
                                    <td>{{ $trainee->name }}</td>
                                    <td>
                                        <input type="hidden" name="attendance[{{ $loop->index }}][trainee_id]" value="{{ $trainee->id }}">
                                        <input type="hidden" name="attendance[{{ $loop->index }}][status]" 
                                               id="attendance_{{ $trainee->id }}_status" 
                                               value="{{ $attendance->status ?? '' }}">
                                        
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm {{ ($attendance && $attendance->status == 'Present') ? 'btn-success' : 'btn-outline-secondary' }}"
                                                    onclick="markAttendance({{ $trainee->id }}, 'Present')">
                                                Present
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm {{ ($attendance && $attendance->status == 'Absent') ? 'btn-danger' : 'btn-outline-secondary' }}"
                                                    onclick="markAttendance({{ $trainee->id }}, 'Absent')">
                                                Absent
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm {{ ($attendance && $attendance->status == 'Excused') ? 'btn-warning' : 'btn-outline-secondary' }}"
                                                    onclick="markAttendance({{ $trainee->id }}, 'Excused')">
                                                Excused
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm {{ ($attendance && $attendance->status == 'Late') ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                                    onclick="markAttendance({{ $trainee->id }}, 'Late')">
                                                Late
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="attendance[{{ $loop->index }}][notes]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $attendance->notes ?? '' }}"
                                               placeholder="Optional notes">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No trainees enrolled in this session.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
                <a href="{{ route('activities.sessions', $session->activity_id) }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection