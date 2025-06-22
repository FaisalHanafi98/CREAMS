@extends('layouts.app')

@section('title', $activity->activity_name . ' - CREAMS')

@section('content')
<div class="activity-detail-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $activity->activity_name }}</h1>
            <p class="activity-code">{{ $activity->activity_code }}</p>
        </div>
        <div class="page-actions">
            @if(in_array($role, ['admin', 'supervisor']))
                <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit Activity
                </a>
            @endif
            <a href="{{ route('activities.sessions', $activity->id) }}" class="btn btn-primary">
                <i class="fas fa-calendar"></i> Manage Sessions
            </a>
            <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Activity Details Card --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <h2>Activity Details</h2>
                    @if($activity->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </div>
                <div class="detail-card-body">
                    <div class="detail-section">
                        <h3>Description</h3>
                        <p>{{ $activity->description }}</p>
                    </div>

                    @if($activity->objectives)
                        <div class="detail-section">
                            <h3>Learning Objectives</h3>
                            <p>{{ $activity->objectives }}</p>
                        </div>
                    @endif

                    @if($activity->materials_needed)
                        <div class="detail-section">
                            <h3>Materials Needed</h3>
                            <p>{{ $activity->materials_needed }}</p>
                        </div>
                    @endif

                    <div class="detail-meta">
                        <div class="meta-item">
                            <i class="fas fa-folder"></i>
                            <span>Category: {{ $activity->category }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>Age Group: {{ $activity->age_group }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-layer-group"></i>
                            <span>Difficulty: {{ $activity->difficulty_level }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span>Created by: {{ $activity->creator->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Sessions --}}
            <div class="detail-card mt-4">
                <div class="detail-card-header">
                    <h2>Recent Sessions</h2>
                    <a href="{{ route('activities.sessions', $activity->id) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="detail-card-body">
                    @if($activity->sessions->count() > 0)
                        <div class="sessions-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Teacher</th>
                                        <th>Enrolled</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activity->sessions->take(5) as $session)
                                        <tr>
                                            <td>{{ $session->date->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                                            <td>{{ $session->teacher->name }}</td>
                                            <td>{{ $session->enrollments->count() }}/{{ $session->max_capacity }}</td>
                                            <td>
                                                <span class="badge badge-{{ $session->status == 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($session->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No sessions scheduled yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Statistics Card --}}
            <div class="stats-card">
                <div class="stats-card-header">
                    <h3>Statistics</h3>
                </div>
                <div class="stats-card-body">
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['totalSessions'] }}</div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['activeSessions'] }}</div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['totalEnrollments'] }}</div>
                        <div class="stat-label">Total Enrollments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['averageAttendance'] }}%</div>
                        <div class="stat-label">Avg. Attendance</div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions section for activities/show.blade.php --}}
            @if(in_array($role, ['admin', 'supervisor']))
            <div class="quick-actions-card mt-4">
                <div class="quick-actions-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-actions-body">
                    <a href="{{ route('activities.sessions', $activity->id) }}" class="action-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Schedule New Session</span>
                    </a>
                    <a href="{{ route('activities.edit', $activity->id) }}" class="action-item">
                        <i class="fas fa-edit"></i>
                        <span>Edit Activity Details</span>
                    </a>
                    @if($role === 'admin')
                        <form action="{{ route('activities.destroy', $activity->id) }}" 
                            method="POST" 
                            id="delete-form-{{ $activity->id }}"
                            onsubmit="return confirm('Are you sure you want to delete this activity? All associated sessions will also be deleted.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-item text-danger">
                                <i class="fas fa-trash-alt"></i>
                                <span>Delete Activity</span>
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

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection