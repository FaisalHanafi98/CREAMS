@extends('layouts.app')

@section('title', $activity->activity_name . ' - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tasks"></i> Activity Details
        </h1>
        <div class="page-actions">
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('activities.sessions', $activity->id) }}" class="btn btn-primary">
                    <i class="fas fa-calendar"></i> Manage Sessions
                </a>
            @endif
            <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="activity-header">
        <h2>{{ $activity->activity_name }}</h2>
        <p class="mb-3">{{ $activity->description }}</p>
        <div class="activity-meta">
            <div class="activity-meta-item">
                <i class="fas fa-tag"></i>
                <span>{{ $activity->activity_code }}</span>
            </div>
            <div class="activity-meta-item">
                <i class="fas fa-folder"></i>
                <span>{{ $activity->category }}</span>
            </div>
            <div class="activity-meta-item">
                <i class="fas fa-users"></i>
                <span>{{ $activity->age_group }}</span>
            </div>
            <div class="activity-meta-item">
                <i class="fas fa-layer-group"></i>
                <span>{{ $activity->difficulty_level }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Activity Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">Activity Information</h5>
                </div>
                <div class="card-body">
                    @if($activity->objectives)
                        <h6 class="font-weight-bold">Learning Objectives:</h6>
                        <div class="mb-3">
                            {!! nl2br(e($activity->objectives)) !!}
                        </div>
                    @endif

                    @if($activity->materials_needed)
                        <h6 class="font-weight-bold">Materials Needed:</h6>
                        <div class="mb-3">
                            {!! nl2br(e($activity->materials_needed)) !!}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Sessions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">Active Sessions</h5>
                </div>
                <div class="card-body">
                    @forelse($activity->activeSessions as $session)
                        <div class="session-card">
                            <div class="session-header">
                                <h6 class="session-title">{{ $session->class_name }}</h6>
                                <span class="badge badge-info">{{ $session->day_of_week }}</span>
                            </div>
                            <div class="session-info">
                                <div class="session-info-item">
                                    <i class="fas fa-user-tie"></i>
                                    {{ $session->teacher->name }}
                                </div>
                                <div class="session-info-item">
                                    <i class="fas fa-clock"></i>
                                    {{ date('g:i A', strtotime($session->start_time)) }} - 
                                    {{ date('g:i A', strtotime($session->end_time)) }}
                                </div>
                                <div class="session-info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $session->location }}
                                </div>
                                <div class="session-info-item">
                                    <i class="fas fa-users"></i>
                                    {{ $session->current_enrollment }}/{{ $session->max_capacity }} enrolled
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No active sessions scheduled for this activity.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Sessions:</span>
                            <strong>{{ $stats['total_sessions'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Active Sessions:</span>
                            <strong>{{ $stats['active_sessions'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Enrollment:</span>
                            <strong>{{ $stats['total_enrollment'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Average Attendance:</span>
                            <strong>{{ $stats['average_attendance'] }}%</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">Activity Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Created by:</strong><br>
                        {{ $activity->creator->name }}
                    </p>
                    <p class="mb-2">
                        <strong>Created on:</strong><br>
                        {{ $activity->created_at->format('M d, Y') }}
                    </p>
                    <p class="mb-2">
                        <strong>Last updated:</strong><br>
                        {{ $activity->updated_at->format('M d, Y') }}
                    </p>
                    <p class="mb-0">
                        <strong>Status:</strong><br>
                        @if($activity->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection