@extends('layouts.app')

@section('title', 'Mark Attendance - CREAMS')

@section('content')
<div class="attendance-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Mark Attendance</h1>
            <p class="page-subtitle">
                {{ $session->activity->activity_name }} - 
                {{ $session->date->format('M d, Y') }} at 
                {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
            </p>
        </div>
        <a href="{{ route('activities.sessions', $session->activity_id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sessions
        </a>
    </div>

    @if($attendanceExists)
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Attendance has already been marked for today. You can update it below.
        </div>
    @endif

    <form action="{{ route('activities.attendance.store', [$session->activity_id, $session->id]) }}" method="POST">
        @csrf
        
        <div class="attendance-card">
            <div class="attendance-card-header">
                <h2>Student Attendance</h2>
                <div class="attendance-date">
                    <label for="attendance_date">Attendance Date:</label>
                    <input type="date" 
                           id="attendance_date" 
                           name="attendance_date" 
                           value="{{ date('Y-m-d') }}" 
                           max="{{ date('Y-m-d') }}" 
                           class="form-control form-control-sm" 
                           style="width: auto; display: inline-block;"
                           required>
                </div>
            </div>
            <div class="attendance-card-body">
                <div class="attendance-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Student Name</th>
                                <th width="400">Attendance Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->enrollments as $index => $enrollment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="student-info">
                                            <strong>{{ $enrollment->trainee->trainee_first_name }} {{ $enrollment->trainee->trainee_last_name }}</strong>
                                            <small class="text-muted d-block">ID: {{ $enrollment->trainee->trainee_ic_passport }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="attendance-options">
                                            <label class="attendance-option present">
                                                <input type="radio" 
                                                       name="attendance[{{ $enrollment->trainee_id }}]" 
                                                       value="present" 
                                                       checked>
                                                <span class="option-label">
                                                    <i class="fas fa-check"></i> Present
                                                </span>
                                            </label>
                                            <label class="attendance-option absent">
                                                <input type="radio" 
                                                       name="attendance[{{ $enrollment->trainee_id }}]" 
                                                       value="absent">
                                                <span class="option-label">
                                                    <i class="fas fa-times"></i> Absent
                                                </span>
                                            </label>
                                            <label class="attendance-option late">
                                                <input type="radio" 
                                                       name="attendance[{{ $enrollment->trainee_id }}]" 
                                                       value="late">
                                                <span class="option-label">
                                                    <i class="fas fa-clock"></i> Late
                                                </span>
                                            </label>
                                            <label class="attendance-option excused">
                                                <input type="radio" 
                                                       name="attendance[{{ $enrollment->trainee_id }}]" 
                                                       value="excused">
                                                <span class="option-label">
                                                    <i class="fas fa-file-medical"></i> Excused
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="notes[{{ $enrollment->trainee_id }}]" 
                                               class="form-control form-control-sm" 
                                               placeholder="Optional notes...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <p class="text-muted mb-0">No students enrolled in this session.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="attendance-card-footer">
                <button type="submit" class="btn btn-success btn-lg" {{ $session->enrollments->count() == 0 ? 'disabled' : '' }}>
                    <i class="fas fa-save"></i> Save Attendance
                </button>
                <a href="{{ route('activities.sessions', $session->activity_id) }}" class="btn btn-outline-secondary btn-lg">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection