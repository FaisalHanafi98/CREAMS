@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Mark Attendance</h4>
                    <p class="mb-0 text-muted">
                        {{ $session->activity->activity_name }} - {{ $session->class_name }}
                    </p>
                </div>
                <div class="card-body">
                    <form action="{{ route('activities.attendance.store', $session->id) }}" 
                          method="POST" id="attendanceForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="date">Attendance Date</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="{{ $date }}" required>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Trainees</h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-success quick-mark" 
                                        data-status="Present">
                                    <i class="fas fa-check"></i> Mark All Present
                                </button>
                                <button type="button" class="btn btn-sm btn-danger quick-mark" 
                                        data-status="Absent">
                                    <i class="fas fa-times"></i> Mark All Absent
                                </button>
                            </div>
                        </div>
                        
                        <div class="attendance-grid">
                            @forelse($session->activeEnrollments as $enrollment)
                            <div class="attendance-item">
                                <div class="trainee-info">
                                    <h6>{{ $enrollment->trainee->name }}</h6>
                                    <small class="text-muted">ID: {{ $enrollment->trainee->trainee_id }}</small>
                                </div>
                                <div class="attendance-controls">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input attendance-status" 
                                               type="radio" 
                                               name="attendance[{{ $enrollment->trainee_id }}]" 
                                               id="present_{{ $enrollment->trainee_id }}" 
                                               value="Present"
                                               {{ ($attendance[$enrollment->trainee_id] ?? '') == 'Present' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="present_{{ $enrollment->trainee_id }}">
                                            Present
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input attendance-status" 
                                               type="radio" 
                                               name="attendance[{{ $enrollment->trainee_id }}]" 
                                               id="absent_{{ $enrollment->trainee_id }}" 
                                               value="Absent"
                                               {{ ($attendance[$enrollment->trainee_id] ?? '') == 'Absent' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="absent_{{ $enrollment->trainee_id }}">
                                            Absent
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input attendance-status" 
                                               type="radio" 
                                               name="attendance[{{ $enrollment->trainee_id }}]" 
                                               id="late_{{ $enrollment->trainee_id }}" 
                                               value="Late"
                                               {{ ($attendance[$enrollment->trainee_id] ?? '') == 'Late' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="late_{{ $enrollment->trainee_id }}">
                                            Late
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input attendance-status" 
                                               type="radio" 
                                               name="attendance[{{ $enrollment->trainee_id }}]" 
                                               id="excused_{{ $enrollment->trainee_id }}" 
                                               value="Excused"
                                               {{ ($attendance[$enrollment->trainee_id] ?? '') == 'Excused' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="excused_{{ $enrollment->trainee_id }}">
                                            Excused
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" 
                                                   class="form-control form-control-sm participation-score" 
                                                   name="participation[{{ $enrollment->trainee_id }}]" 
                                                   placeholder="Score (0-10)" 
                                                   min="0" max="10"
                                                   {{ in_array($attendance[$enrollment->trainee_id] ?? '', ['Present', 'Late']) ? '' : 'disabled' }}>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   name="notes[{{ $enrollment->trainee_id }}]" 
                                                   placeholder="Notes (optional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="text-center text-muted">No trainees enrolled in this session.</p>
                            @endforelse
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Attendance
                            </button>
                            <a href="{{ route('activities.sessions', $session->activity_id) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Session Info</h5>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Day</dt>
                        <dd>{{ $session->day_of_week }}</dd>
                        
                        <dt>Time</dt>
                        <dd>
                            {{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }} - 
                            {{ Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
                        </dd>
                        
                        <dt>Teacher</dt>
                        <dd>{{ $session->teacher->name }}</dd>
                        
                        <dt>Location</dt>
                        <dd>{{ $session->location ?? 'Not specified' }}</dd>
                        
                        <dt>Total Enrolled</dt>
                        <dd>{{ $session->activeEnrollments->count() }} trainees</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection