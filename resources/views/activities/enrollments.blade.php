@extends('layouts.app')

@section('title', 'Manage Enrollments - ' . $session->activity->activity_name)

@section('content')
<div class="enrollments-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Enrollments</h1>
            <p class="page-subtitle">{{ $session->activity->activity_name }} - {{ $session->scheduled_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('activities.sessions', $session->activity_id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Sessions
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Current Enrollments --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Enrolled Trainee ({{ $session->enrollments->count() }}/{{ $session->max_participants ?? $session->max_capacity ?? 'Unlimited' }})
                    </h3>
                </div>
                <div class="card-body">
                    @if($session->enrollments->count() > 0)
                        <div class="enrolled-list">
                            @foreach($session->enrollments as $enrollment)
                                <div class="enrollment-item d-flex justify-content-between align-items-center mb-3">
                                    <div class="trainee-info">
                                        <div class="trainee-name font-weight-bold">
                                            {{ $enrollment->trainee->trainee_first_name }} {{ $enrollment->trainee->trainee_last_name }}
                                        </div>
                                        <div class="trainee-meta text-muted small">
                                            <i class="fas fa-id-card"></i> ID: {{ $enrollment->trainee->id }}
                                            @if($enrollment->trainee->trainee_condition)
                                                | <i class="fas fa-medical"></i> {{ $enrollment->trainee->trainee_condition }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="enrollment-actions">
                                        <span class="badge badge-{{ $enrollment->status == 'confirmed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($enrollment->status ?? 'enrolled') }}
                                        </span>
                                        @if(in_array(session('role'), ['admin', 'supervisor']))
                                            <form method="POST" action="{{ route('activities.enrollments.remove', [$session->activity_id, $session->id, $enrollment->trainee->id]) }}" 
                                                  class="d-inline ml-2" onsubmit="return confirm('Are you sure you want to remove this trainee from the session?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from session">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No trainees enrolled yet</h5>
                            <p class="text-muted">Add trainees from the eligible list on the right.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Eligible Trainee --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus"></i> Eligible Trainee ({{ $eligibleTrainees->count() }})
                    </h3>
                </div>
                <div class="card-body">
                    @if($eligibleTrainees->count() > 0)
                        <div class="eligible-list">
                            @foreach($eligibleTrainees as $trainee)
                                <div class="eligible-item d-flex justify-content-between align-items-center mb-3">
                                    <div class="trainee-info">
                                        <div class="trainee-name font-weight-bold">
                                            {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}
                                        </div>
                                        <div class="trainee-meta text-muted small">
                                            <i class="fas fa-id-card"></i> ID: {{ $trainee->id }}
                                            @if($trainee->trainee_condition)
                                                | <i class="fas fa-medical"></i> {{ $trainee->trainee_condition }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="enrollment-actions">
                                        @if(!$session->is_full)
                                            <form method="POST" action="{{ route('activities.enrollments.add', [$session->activity_id, $session->id]) }}" 
                                                  class="d-inline">
                                                @csrf
                                                <input type="hidden" name="trainee_id" value="{{ $trainee->id }}">
                                                <button type="submit" class="btn btn-sm btn-success" title="Add to session">
                                                    <i class="fas fa-plus"></i> Enroll
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge badge-secondary">Session Full</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success">All trainees enrolled</h5>
                            <p class="text-muted">No more eligible trainees available for this session.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Session Info --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Session Information
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <strong>Activity:</strong> {{ $session->activity->activity_name }}
                    </div>
                    <div class="info-item">
                        <strong>Date:</strong> {{ $session->scheduled_date->format('l, F d, Y') }}
                    </div>
                    <div class="info-item">
                        <strong>Time:</strong> {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <strong>Location:</strong> {{ $session->location ?? $session->venue ?? 'Not specified' }}
                    </div>
                    <div class="info-item">
                        <strong>Teacher:</strong> {{ $session->teacher->name ?? 'Not assigned' }}
                    </div>
                    <div class="info-item">
                        <strong>Status:</strong> 
                        <span class="badge badge-{{ $session->status == 'scheduled' ? 'primary' : ($session->status == 'active' ? 'success' : 'secondary') }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
<style>
.enrollments-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.enrollment-item, .eligible-item {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.enrollment-item:hover, .eligible-item:hover {
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.trainee-name {
    color: #2c3e50;
    font-size: 16px;
}

.trainee-meta {
    margin-top: 4px;
}

.info-item {
    margin-bottom: 10px;
}

.empty-state {
    padding: 40px 20px;
}
</style>
@endsection