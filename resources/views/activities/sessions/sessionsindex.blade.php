@extends('layouts.app')

@section('title', $activity->activity_name . ' - Sessions')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $activity->activity_name }} - Sessions</h1>
                    <p class="text-muted mb-0">{{ $activity->activity_code }} | {{ $activity->category }}</p>
                </div>
                <div>
                    <a href="{{ route('activities.sessions.create', $activity->id) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Session
                    </a>
                    <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Activity
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions by Class -->
    @forelse($sessionsByClass as $className => $sessions)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $className }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Teacher</th>
                            <th>Location</th>
                            <th>Enrollments</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                        <tr>
                            <td>{{ $session->day_of_week }}</td>
                            <td>
                                {{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }} - 
                                {{ Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
                            </td>
                            <td>{{ $session->teacher->name ?? 'Not Assigned' }}</td>
                            <td>{{ $session->location ?? '-' }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $session->activeEnrollments->count() }} / {{ $session->max_trainees }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $session->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $session->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('activities.sessions.edit', [$activity->id, $session->id]) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('activities.attendance.mark', $session->id) }}" 
                                       class="btn btn-sm btn-outline-info" title="Attendance">
                                        <i class="fas fa-check-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="enrollTrainees({{ $session->id }})" title="Enroll">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <form action="{{ route('activities.sessions.destroy', [$activity->id, $session->id]) }}" 
                                          method="POST" style="display: inline-block;"
                                          onsubmit="return confirm('Are you sure you want to delete this session?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h4>No Sessions Scheduled</h4>
            <p class="text-muted">No sessions have been scheduled for this activity yet.</p>
            <a href="{{ route('activities.sessions.create', $activity->id) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Schedule First Session
            </a>
        </div>
    </div>
    @endforelse
</div>

<!-- Enrollment Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enroll Trainees</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="enrollForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="traineesList">
                        <p class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Loading eligible trainees...
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection