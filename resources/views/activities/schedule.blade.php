@extends('layouts.app')

@section('title', 'Schedule Sessions - CREAMS')

<!-- CREATED_AT: JULY 7 - AUTO BY CLAUDE -->

@section('content')
<div class="schedule-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i> Schedule Sessions
        </h1>
        <div class="page-actions">
            <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Activities List
            </a>
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Activity
                </a>
            @endif
        </div>
    </div>

    {{-- Schedule Filters --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Schedule Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('activities.schedule') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="centre_filter">Centre</label>
                            <select class="form-control" id="centre_filter" name="centre">
                                <option value="">All Centres</option>
                                @foreach($centres ?? [] as $centre)
                                    <option value="{{ $centre->centre_id ?? $centre['id'] }}" 
                                            {{ request('centre') == ($centre->centre_id ?? $centre['id']) ? 'selected' : '' }}>
                                        {{ $centre->centre_name ?? $centre['name'] ?? 'Unknown Centre' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="day_filter">Day</label>
                            <select class="form-control" id="day_filter" name="day">
                                <option value="">All Days</option>
                                <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Monday</option>
                                <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Friday</option>
                                <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="category_filter">Category</label>
                            <select class="form-control" id="category_filter" name="category">
                                <option value="">All Categories</option>
                                <option value="Physical Therapy" {{ request('category') == 'Physical Therapy' ? 'selected' : '' }}>Physical Therapy</option>
                                <option value="Occupational Therapy" {{ request('category') == 'Occupational Therapy' ? 'selected' : '' }}>Occupational Therapy</option>
                                <option value="Speech Therapy" {{ request('category') == 'Speech Therapy' ? 'selected' : '' }}>Speech Therapy</option>
                                <option value="Behavioral Therapy" {{ request('category') == 'Behavioral Therapy' ? 'selected' : '' }}>Behavioral Therapy</option>
                                <option value="Mathematics" {{ request('category') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                <option value="Literacy" {{ request('category') == 'Literacy' ? 'selected' : '' }}>Literacy</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="participant_filter">Participant ID</label>
                            <input type="text" class="form-control" id="participant_filter" name="participant" 
                                   placeholder="Enter Trainee ID" value="{{ request('participant') }}">
                        </div>
                    </div>
                    @if(in_array($role ?? session('role'), ['admin', 'supervisor']))
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="teacher_filter">Teacher</label>
                            <select class="form-control" id="teacher_filter" name="teacher">
                                <option value="">All Teachers</option>
                                @foreach($teachers ?? [] as $teacher)
                                    <option value="{{ $teacher->id }}" {{ request('teacher') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="show_all" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_all">
                                Show all sessions (past and future)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('activities.schedule') }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Session List --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Scheduled Sessions</h6>
        </div>
        <div class="card-body">
            @if(isset($sessions) && $sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="sessionsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Activity</th>
                                <th>Category</th>
                                <th>Teacher</th>
                                <th>Centre</th>
                                <th>Enrolled</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr>
                                    <td>{{ $session->scheduled_date ? \Carbon\Carbon::parse($session->scheduled_date)->format('M d, Y') : 'Not scheduled' }}</td>
                                    <td>{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : 'Not set' }}</td>
                                    <td>
                                        <a href="{{ route('activities.show', $session->activity_id ?? 0) }}">
                                            {{ $session->activity->activity_name ?? 'Unknown Activity' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $session->activity->category ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $session->teacher?->name ?? 'Not assigned' }}</td>
                                    <td>{{ $session->activity->centre->centre_name ?? 'N/A' }}</td>
                                    <td>{{ $session->enrollments->count() ?? 0 }}/{{ $session->max_capacity ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $session->status == 'completed' ? 'success' : ($session->status == 'ongoing' ? 'warning' : 'info') }}">
                                            {{ ucfirst($session->status ?? 'scheduled') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('activities.sessions', $session->activity_id ?? 0) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                                            <a href="#" class="btn btn-sm btn-outline-info" title="Mark Attendance">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($sessions, 'links'))
                    <div class="d-flex justify-content-center">
                        {{ $sessions->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-calendar-times fa-3x text-muted"></i>
                    </div>
                    <h5>No Sessions Found</h5>
                    <p class="text-muted">No sessions match the current filters or no sessions are scheduled.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('activities.home') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Activities & Sessions
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#sessionsTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "responsive": true
        });
    });
</script>
@endsection