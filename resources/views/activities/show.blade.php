@extends('layouts.app')

@section('title', $activity->activity_name . ' - CREAMS')

<!-- CREATED_AT: JULY 7 - AUTO BY CLAUDE -->

@section('content')
<div class="activity-detail-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $activity->activity_name }}</h1>
            <p class="activity-code">Activity #{{ $activity->id }}</p>
        </div>
        <div class="page-actions">
            @if($role === 'admin' && $activity->is_active)
                <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit Activity
                </a>
            @endif
            <a href="{{ route('activities.sessions', $activity->id) }}" class="btn btn-primary">
                <i class="fas fa-calendar"></i> Manage Sessions
            </a>
            <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
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
                        <p>{{ $activity->activity_description ?? 'No description available.' }}</p>
                    </div>

                    @if($activity->activity_goals)
                        <div class="detail-section">
                            <h3>Learning Goals</h3>
                            @if(is_array($activity->activity_goals))
                                <ul>
                                    @foreach($activity->activity_goals as $goal)
                                        <li>{{ $goal }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>{{ $activity->activity_goals }}</p>
                            @endif
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
                            <span>Category: {{ $activity->category ?? 'Uncategorized' }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>Max Participants: {{ $activity->max_participants }} trainees</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Duration: {{ $activity->duration_weeks }} weeks ({{ $activity->sessions_per_week }} sessions/week)</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span>Instructor: {{ $activity->instructor->name ?? 'Not assigned' }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Location: {{ $activity->activity_location }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-building"></i>
                            <span>Centre: {{ $activity->centre->centre_name ?? 'Not specified' }}</span>
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
                                            <td>{{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('M d, Y') : 'Not scheduled' }}</td>
                                            <td>{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : 'Not scheduled' }}</td>
                                            <td>{{ $session->teacher?->name ?? 'Not assigned' }}</td>
                                            <td>{{ $activity->enrollments->count() }}/{{ $session->max_participants ?? 'N/A' }}</td>
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
                        <div class="stat-value">{{ $stats['totalSessions'] ?? 0 }}</div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['activeSessions'] ?? 0 }}</div>
                        <div class="stat-label">Active/Scheduled</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['totalEnrollments'] ?? 0 }}</div>
                        <div class="stat-label">Total Enrollments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['averageAttendance'] ?? 0 }}%</div>
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
                    @if($role === 'admin' || $role === 'supervisor')
                    <a href="{{ route('activities.sessions', $activity->id) }}" class="action-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Schedule New Session</span>
                    </a>
                    @endif
                    @if($role === 'admin' && $activity->is_active)
                    <a href="{{ route('activities.edit', $activity->id) }}" class="action-item">
                        <i class="fas fa-edit"></i>
                        <span>Edit Activity Details</span>
                    </a>
                    @endif
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