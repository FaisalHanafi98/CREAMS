{{-- activities/teacher-schedule.blade.php --}}
@extends('layouts.app')

@section('title', 'My Schedule - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i> My Teaching Schedule
        </h1>
    </div>

    @if($groupedSessions->count() > 0)
        <div class="row">
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                @if($groupedSessions->has($day))
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card day-schedule">
                            <div class="card-header day-header">
                                <h5 class="m-0">{{ $day }}</h5>
                                <span class="badge badge-primary">{{ $groupedSessions[$day]->count() }} session(s)</span>
                            </div>
                            <div class="card-body">
                                @foreach($groupedSessions[$day] as $session)
                                    <div class="session-card teacher-session">
                                        <div class="session-time">
                                            <i class="fas fa-clock"></i>
                                            {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                                        </div>
                                        <h6 class="session-activity">{{ $session->activity->activity_name }}</h6>
                                        <div class="session-details">
                                            <div class="detail-item">
                                                <i class="fas fa-users"></i>
                                                <span>{{ $session->class_name }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>{{ $session->location }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-user-friends"></i>
                                                <span>{{ $session->current_enrollment }}/{{ $session->max_capacity }} trainees</span>
                                            </div>
                                        </div>
                                        <div class="session-actions">
                                            <a href="{{ route('teacher.attendance.mark', $session->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-check"></i> Mark Attendance
                                            </a>
                                            <a href="{{ route('activities.show', $session->activity_id) }}" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5>No Sessions Scheduled</h5>
                <p class="text-muted">You don't have any sessions assigned to you yet. Please contact your supervisor for session assignments.</p>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection