@extends('layouts.app')

@section('title', 'Sessions - ' . $activity->activity_name)

@section('content')
<div class="sessions-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Session Management</h1>
            <p class="page-subtitle">{{ $activity->activity_name }} ({{ $activity->activity_code }})</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSessionModal">
                <i class="fas fa-plus"></i> Schedule New Session
            </button>
            <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Activity
            </a>
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="sessions-card">
        <div class="sessions-card-header">
            <h2>Scheduled Sessions</h2>
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All Sessions</button>
                <button class="filter-tab" data-filter="upcoming">Upcoming</button>
                <button class="filter-tab" data-filter="past">Past</button>
            </div>
        </div>
        <div class="sessions-card-body">
            <div class="table-responsive">
                <table class="table sessions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Teacher</th>
                            <th>Location</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr class="session-row" data-status="{{ $session->date < now() ? 'past' : 'upcoming' }}">
                                <td>
                                    <div class="date-display">
                                        <span class="date-day">{{ $session->date->format('d') }}</span>
                                        <span class="date-month">{{ $session->date->format('M Y') }}</span>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                                <td>{{ $session->duration }} mins</td>
                                <td>{{ $session->teacher->name }}</td>
                                <td>{{ $session->location }}</td>
                                <td>
                                    <div class="enrollment-status">
                                        <span class="{{ $session->is_full ? 'text-danger' : 'text-success' }}">
                                            {{ $session->enrollment_count }}/{{ $session->max_capacity }}
                                        </span>
                                        @if($session->is_full)
                                            <span class="badge badge-danger ml-1">Full</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $session->status == 'active' ? 'success' : ($session->status == 'cancelled' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('activities.enrollments', [$activity->id, $session->id]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Manage Enrollments">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        @if($session->date >= now() && in_array($role, ['admin', 'supervisor', 'teacher']))
                                            <a href="{{ route('activities.attendance', [$activity->id, $session->id]) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Mark Attendance">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                        @endif
                                        @if(in_array($role, ['admin', 'supervisor']))
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelSession({{ $session->id }})"
                                                    title="Cancel Session">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No sessions scheduled yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="sessions-card-footer">
            {{ $sessions->links() }}
        </div>
    </div>
</div>

{{-- Create Session Modal --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('activities.sessions.create', $activity->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Schedule New Session</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="teacher_id">Teacher <span class="required">*</span></label>
                                <select class="form-control" id="teacher_id" name="teacher_id" required>
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Date <span class="required">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date" 
                                       name="date" 
                                       min="{{ date('Y-m-d') }}" 
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_time">Start Time <span class="required">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="start_time" 
                                       name="start_time" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="duration">Duration <span class="required">*</span></label>
                                <select class="form-control" id="duration" name="duration" required>
                                    <option value="15">15 minutes</option>
                                    <option value="20">20 minutes</option>
                                    <option value="30" selected>30 minutes</option>
                                    <option value="45">45 minutes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="max_capacity">Max Capacity <span class="required">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="max_capacity" 
                                       name="max_capacity" 
                                       min="1" 
                                       max="50" 
                                       value="20" 
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="location">Location <span class="required">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="location" 
                               name="location" 
                               placeholder="e.g., Room 101, Therapy Hall" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Schedule Session
                    </button>
                </div>
            </form>
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