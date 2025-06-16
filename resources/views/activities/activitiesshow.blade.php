@extends('layouts.app')

@section('title', $activity->activity_name)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Activity Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $activity->activity_name }}</h4>
                    <span class="badge {{ $activity->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $activity->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Activity Code:</strong> {{ $activity->activity_code }}</p>
                            <p><strong>Category:</strong> {{ $activity->category }}</p>
                            <p><strong>Age Group:</strong> {{ $activity->age_group }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Difficulty Level:</strong> {{ $activity->difficulty_level }}</p>
                            <p><strong>Created By:</strong> {{ $activity->creator->name ?? 'System' }}</p>
                            <p><strong>Created On:</strong> {{ $activity->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Description</h5>
                        <p>{{ $activity->description }}</p>
                    </div>
                    
                    @if($activity->objectives)
                    <div class="mb-3">
                        <h5>Learning Objectives</h5>
                        <ul>
                            @foreach(explode("\n", $activity->objectives) as $objective)
                                @if(trim($objective))
                                <li>{{ trim($objective) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if($activity->materials_needed)
                    <div class="mb-3">
                        <h5>Materials Needed</h5>
                        <p>{{ $activity->materials_needed }}</p>
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Activity
                        </a>
                        <a href="{{ route('activities.sessions', $activity->id) }}" class="btn btn-info">
                            <i class="fas fa-calendar"></i> Manage Sessions
                        </a>
                        @endif
                        <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Activities
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Sessions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Active Sessions</h5>
                </div>
                <div class="card-body">
                    @if($activity->activeSessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Teacher</th>
                                    <th>Schedule</th>
                                    <th>Time</th>
                                    <th>Enrollments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activity->activeSessions as $session)
                                <tr>
                                    <td>{{ $session->class_name }}</td>
                                    <td>{{ $session->teacher->name ?? 'Not Assigned' }}</td>
                                    <td>{{ $session->day_of_week }}</td>
                                    <td>{{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }} - 
                                        {{ Carbon\Carbon::parse($session->end_time)->format('g:i A') }}</td>
                                    <td>
                                        {{ $session->activeEnrollments->count() }} / {{ $session->max_trainees }}
                                    </td>
                                    <td>
                                        @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                                        <a href="{{ route('activities.attendance.mark', $session->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-check-square"></i> Attendance
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center">No active sessions scheduled for this activity.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Statistics Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <i class="fas fa-calendar-alt text-primary"></i>
                        <span>Total Sessions: {{ $stats['total_sessions'] }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Active Sessions: {{ $stats['active_sessions'] }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users text-info"></i>
                        <span>Total Enrollments: {{ $stats['total_enrollments'] }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-chalkboard-teacher text-warning"></i>
                        <span>Teachers: {{ $stats['teachers'] }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('activities.sessions.create', $activity->id) }}" 
                       class="btn btn-block btn-primary mb-2">
                        <i class="fas fa-plus"></i> Add New Session
                    </a>
                    <a href="{{ route('activities.edit', $activity->id) }}" 
                       class="btn btn-block btn-secondary mb-2">
                        <i class="fas fa-edit"></i> Edit Activity
                    </a>
                    @if(!$activity->hasActiveEnrollments())
                    <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to deactivate this activity?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-block btn-danger">
                            <i class="fas fa-times-circle"></i> Deactivate Activity
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection