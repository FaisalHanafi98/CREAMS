@extends('layouts.app')

@section('title', $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name . ' - Profile')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle mr-2"></i>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}
        </h1>
        <div>
            <a href="{{ route('traineeshome') }}" class="btn btn-secondary btn-sm shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i>Back to Trainees
            </a>
            <div class="btn-group">
                <a href="{{ route('traineeprofile.edit', ['id' => $trainee->id]) }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-edit fa-sm text-white-50 mr-1"></i>Edit Profile
                </a>
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addActivityModal">
                        <i class="fas fa-plus-circle fa-sm mr-1"></i> Add Activity
                    </a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#recordAttendanceModal">
                        <i class="fas fa-calendar-check fa-sm mr-1"></i> Record Attendance
                    </a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#updateProgressModal">
                        <i class="fas fa-chart-line fa-sm mr-1"></i> Update Progress
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('traineeprofile.download', ['id' => $trainee->id]) }}">
                        <i class="fas fa-file-pdf fa-sm mr-1"></i> Download Profile
                    </a>
                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#deleteTraineeModal">
                        <i class="fas fa-trash-alt fa-sm mr-1"></i> Delete Trainee
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Content Row - Statistics -->
    <div class="row">
        <!-- Age Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Age</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $age }} years</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-birthday-cake fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Enrolled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $enrollmentDuration }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Activities Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Activities</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalActivities }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Rate Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Attendance Rate</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $attendanceRate }}%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar 
                                            @if($attendanceRate >= 90) bg-success 
                                            @elseif($attendanceRate >= 75) bg-primary 
                                            @elseif($attendanceRate >= 60) bg-warning 
                                            @else bg-danger 
                                            @endif" 
                                            role="progressbar" style="width: {{ $attendanceRate }}%" 
                                            aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="row">
        <!-- Left Column - Basic Information -->
        <div class="col-xl-4">
            <!-- Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img class="img-profile rounded-circle" src="{{ $trainee->getAvatarUrlAttribute() }}" width="150" height="150">
                        <h4 class="mt-3">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h4>
                        <span class="badge badge-{{ $trainee->getConditionBadgeClassAttribute() }} font-weight-bold px-3 py-2">
                            {{ $trainee->trainee_condition }}
                        </span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="35%"><i class="fas fa-envelope mr-1"></i> Email</th>
                                    <td>{{ $trainee->trainee_email }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone mr-1"></i> Phone</th>
                                    <td>{{ $trainee->trainee_phone_number ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar mr-1"></i> Date of Birth</th>
                                    <td>{{ $trainee->trainee_date_of_birth->format('M d, Y') }} ({{ $age }} years)</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-building mr-1"></i> Centre</th>
                                    <td>{{ $trainee->centre_name }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-clock mr-1"></i> Enrolled</th>
                                    <td>{{ $trainee->created_at->format('M d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Guardian Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Guardian Information</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="35%"><i class="fas fa-user mr-1"></i> Name</th>
                                    <td>{{ $guardian['name'] }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-users mr-1"></i> Relationship</th>
                                    <td>{{ $guardian['relationship'] }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone mr-1"></i> Phone</th>
                                    <td>{{ $guardian['phone'] }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-envelope mr-1"></i> Email</th>
                                    <td>{{ $guardian['email'] }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-home mr-1"></i> Address</th>
                                    <td>{{ $guardian['address'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Tabs for detailed data -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab" aria-controls="attendance" aria-selected="true">
                                <i class="fas fa-calendar-check mr-1"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="activities-tab" data-toggle="tab" href="#activities" role="tab" aria-controls="activities" aria-selected="false">
                                <i class="fas fa-tasks mr-1"></i> Activities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="progress-tab" data-toggle="tab" href="#progress" role="tab" aria-controls="progress" aria-selected="false">
                                <i class="fas fa-chart-line mr-1"></i> Progress
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <!-- Attendance Tab -->
                        <div class="tab-pane fade show active" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Monthly Attendance Summary</h5>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($attendanceDays['present'] / 20) * 100 }}%;" aria-valuenow="{{ $attendanceDays['present'] }}" aria-valuemin="0" aria-valuemax="20">
                                            {{ $attendanceDays['present'] }} Present
                                        </div>
                                    </div>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($attendanceDays['late'] / 20) * 100 }}%;" aria-valuenow="{{ $attendanceDays['late'] }}" aria-valuemin="0" aria-valuemax="20">
                                            {{ $attendanceDays['late'] }} Late
                                        </div>
                                    </div>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($attendanceDays['absent'] / 20) * 100 }}%;" aria-valuenow="{{ $attendanceDays['absent'] }}" aria-valuemin="0" aria-valuemax="20">
                                            {{ $attendanceDays['absent'] }} Absent
                                        </div>
                                    </div>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($attendanceDays['excused'] / 20) * 100 }}%;" aria-valuenow="{{ $attendanceDays['excused'] }}" aria-valuemin="0" aria-valuemax="20">
                                            {{ $attendanceDays['excused'] }} Excused
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Actions</h5>
                                    <div class="d-flex flex-column">
                                        <button class="btn btn-primary mb-2" data-toggle="modal" data-target="#recordAttendanceModal">
                                            <i class="fas fa-plus-circle mr-1"></i> Record Attendance
                                        </button>
                                        <button class="btn btn-info mb-2" data-toggle="modal" data-target="#viewAttendanceCalendarModal">
                                            <i class="fas fa-calendar-alt mr-1"></i> View Calendar
                                        </button>
                                        <button class="btn btn-secondary" onclick="window.location.href='#'">
                                            <i class="fas fa-file-excel mr-1"></i> Export Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <h5 class="mb-3">Recent Attendance</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attendanceHistory as $record)
                                            <tr class="
                                                @if($record['status'] == 'present') table-success 
                                                @elseif($record['status'] == 'late') table-warning 
                                                @elseif($record['status'] == 'absent') table-danger 
                                                @else table-info
                                                @endif">
                                                <td>{{ date('M d, Y', strtotime($record['date'])) }}</td>
                                                <td>{{ $record['day_name'] }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($record['status'] == 'present') badge-success 
                                                        @elseif($record['status'] == 'late') badge-warning 
                                                        @elseif($record['status'] == 'absent') badge-danger 
                                                        @else badge-info
                                                        @endif">
                                                        {{ ucfirst($record['status']) }}
                                                    </span>
                                                </td>
                                                <td>{{ $record['remarks'] ?? 'No remarks' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Activities Tab -->
                        <div class="tab-pane fade" id="activities" role="tabpanel" aria-labelledby="activities-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Activities ({{ $totalActivities }})</h5>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addActivityModal">
                                    <i class="fas fa-plus-circle mr-1"></i> Add Activity
                                </button>
                            </div>
                            
                            @if($trainee->activities->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($trainee->activities as $activity)
                                                <tr>
                                                    <td>{{ $activity->activity_date->format('M d, Y') }}</td>
                                                    <td>{{ $activity->activity_name }}</td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $activity->activity_type }}</span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info view-activity" data-activity-id="{{ $activity->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <img src="{{ asset('images/no-data.svg') }}" alt="No activities" class="img-fluid mb-3" style="max-width: 200px;">
                                    <h5>No activities recorded yet</h5>
                                    <p class="text-muted">Start by adding the first activity using the button above.</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Progress Tab -->
                        <div class="tab-pane fade" id="progress" role="tabpanel" aria-labelledby="progress-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Progress Updates</h5>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#updateProgressModal">
                                    <i class="fas fa-chart-line mr-1"></i> Update Progress
                                </button>
                            </div>
                            
                            @php
                                $progressActivities = $trainee->activities->where('activity_type', 'Progress');
                            @endphp
                            
                            @if($progressActivities->count() > 0)
                                <div class="accordion" id="progressAccordion">
                                    @foreach($progressActivities as $index => $progress)
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center" id="heading{{ $progress->id }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $progress->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $progress->id }}">
                                                        {{ $progress->activity_date->format('M d, Y') }} - {{ $progress->activity_name }}
                                                    </button>
                                                </h2>
                                                <span class="badge badge-info">{{ $progress->activity_goals }}</span>
                                            </div>
                                            <div id="collapse{{ $progress->id }}" class="collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $progress->id }}" data-parent="#progressAccordion">
                                                <div class="card-body">
                                                    <p>{{ $progress->activity_description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <img src="{{ asset('images/no-data.svg') }}" alt="No progress updates" class="img-fluid mb-3" style="max-width: 200px;">
                                    <h5>No progress updates recorded</h5>
                                    <p class="text-muted">Start tracking progress by using the update button above.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- Add Activity Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1" role="dialog" aria-labelledby="addActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('traineeprofile.addActivity', ['id' => $trainee->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addActivityModalLabel">Add New Activity</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_description">Activity Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="activity_description" name="activity_description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_goals">Goals</label>
                                <textarea class="form-control" id="activity_goals" name="activity_goals" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_outcomes">Outcomes</label>
                                <textarea class="form-control" id="activity_outcomes" name="activity_outcomes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Attendance Modal -->
<div class="modal fade" id="recordAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="recordAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('traineeprofile.recordAttendance', ['id' => $trainee->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="recordAttendanceModalLabel">Record Attendance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="attendance_date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="attendance_status">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="attendance_status" name="attendance_status" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="attendance_remarks">Remarks</label>
                        <textarea class="form-control" id="attendance_remarks" name="attendance_remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="updateProgressModal" tabindex="-1" role="dialog" aria-labelledby="updateProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('traineeprofile.updateProgress', ['id' => $trainee->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProgressModalLabel">Update Progress</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="progress_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="progress_date" name="progress_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="progress_type">Progress Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="progress_type" name="progress_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Physical Development">Physical Development</option>
                                    <option value="Speech Development">Speech Development</option>
                                    <option value="Cognitive Development">Cognitive Development</option>
                                    <option value="Social Skills">Social Skills</option>
                                    <option value="Behavioral">Behavioral</option>
                                    <option value="Educational">Educational</option>
                                    <option value="Overall Assessment">Overall Assessment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="progress_notes">Progress Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="progress_notes" name="progress_notes" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="progress_rating">Rating <span class="text-danger">*</span></label>
                        <div class="rating-input">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="progress_rating" id="rating1" value="1" required>
                                <label class="form-check-label" for="rating1">1 - Poor</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="progress_rating" id="rating2" value="2">
                                <label class="form-check-label" for="rating2">2 - Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="progress_rating" id="rating3" value="3">
                                <label class="form-check-label" for="rating3">3 - Average</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="progress_rating" id="rating4" value="4">
                                <label class="form-check-label" for="rating4">4 - Good</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="progress_rating" id="rating5" value="5">
                                <label class="form-check-label" for="rating5">5 - Excellent</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Activity Modal -->
<div class="modal fade" id="viewActivityModal" tabindex="-1" role="dialog" aria-labelledby="viewActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewActivityModalLabel">Activity Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="activityDetails">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading activity details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Trainee Modal -->
<div class="modal fade" id="deleteTraineeModal" tabindex="-1" role="dialog" aria-labelledby="deleteTraineeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTraineeModalLabel">Delete Trainee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 
                    Warning: This action cannot be undone. Are you sure you want to delete this trainee?
                </p>
                <p>
                    This will permanently remove <strong>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</strong> 
                    and all related data from the system.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('traineeprofile.destroy', ['id' => $trainee->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Trainee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Attendance Calendar Modal -->
<div class="modal fade" id="viewAttendanceCalendarModal" tabindex="-1" role="dialog" aria-labelledby="viewAttendanceCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAttendanceCalendarModalLabel">Attendance Calendar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="attendanceCalendar" class="text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Calendar view will be implemented in a future update.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // View activity details
        $('.view-activity').click(function() {
            var activityId = $(this).data('activity-id');
            $('#viewActivityModal').modal('show');
            
            // Reset modal content
            $('#activityDetails').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div><p class="mt-2">Loading activity details...</p></div>');
            
            // Fetch activity details
            $.ajax({
                url: '/traineeactivity/details/' + activityId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var activity = response.data;
                        var html = '<div class="card">';
                        html += '<div class="card-header"><h5>' + activity.activity_name + '</h5></div>';
                        html += '<div class="card-body">';
                        html += '<div class="row mb-3">';
                        html += '<div class="col-md-6"><strong>Date:</strong> ' + new Date(activity.activity_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + '</div>';
                        html += '<div class="col-md-6"><strong>Type:</strong> ' + activity.activity_type + '</div>';
                        html += '</div>';
                        
                        html += '<div class="mb-3">';
                        html += '<strong>Description:</strong>';
                        html += '<p>' + activity.activity_description + '</p>';
                        html += '</div>';
                        
                        if (activity.activity_goals) {
                            html += '<div class="mb-3">';
                            html += '<strong>Goals:</strong>';
                            html += '<p>' + activity.activity_goals + '</p>';
                            html += '</div>';
                        }
                        
                        if (activity.activity_outcomes) {
                            html += '<div class="mb-3">';
                            html += '<strong>Outcomes:</strong>';
                            html += '<p>' + activity.activity_outcomes + '</p>';
                            html += '</div>';
                        }
                        
                        html += '</div>'; // card-body
                        html += '</div>'; // card
                        
                        $('#activityDetails').html(html);
                    } else {
                        $('#activityDetails').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#activityDetails').html('<div class="alert alert-danger">Error loading activity details. Please try again.</div>');
                }
            });
        });
    });
</script>
@endsection

@section('styles')
<style>
    .img-profile {
        object-fit: cover;
        border: 4px solid #f8f9fc;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .badge {
        font-size: 85%;
    }
    
    .badge-cerebral-palsy {
        background-color: #e74a3b;
        color: white;
    }
    
    .badge-autism {
        background-color: #4e73df;
        color: white;
    }
    
    .badge-down-syndrome {
        background-color: #1cc88a;
        color: white;
    }
    
    .badge-hearing {
        background-color: #36b9cc;
        color: white;
    }
    
    .badge-visual {
        background-color: #f6c23e;
        color: white;
    }
    
    .badge-intellectual {
        background-color: #fd7e14;
        color: white;
    }
    
    .badge-physical {
        background-color: #6f42c1;
        color: white;
    }
    
    .badge-speech {
        background-color: #20c9a6;
        color: white;
    }
    
    .badge-learning {
        background-color: #858796;
        color: white;
    }
    
    .badge-default {
        background-color: #5a5c69;
        color: white;
    }
    
    .table th, .table td {
        vertical-align: middle;
    }
    
    .progress {
        background-color: #eaecf4;
    }
    
    .nav-tabs .nav-link {
        color: #6e707e;
    }
    
    .nav-tabs .nav-link.active {
        color: #4e73df;
        font-weight: 600;
    }
    
    .rating-input {
        margin-top: 0.5rem;
    }
</style>
@endsectionactivity_name">Activity Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="activity_name" name="activity_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_type">Activity Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="activity_type" name="activity_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Physical Therapy">Physical Therapy</option>
                                    <option value="Speech Therapy">Speech Therapy</option>
                                    <option value="Occupational Therapy">Occupational Therapy</option>
                                    <option value="Educational">Educational</option>
                                    <option value="Social Skills">Social Skills</option>
                                    <option value="Assessment">Assessment</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_date">Activity Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="activity_date" name="activity_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="activity_description">Activity Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="activity_description" name="activity_description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_goals">Goals</label>
                                <textarea class="form-control" id="activity_goals" name="activity_goals" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_outcomes">Outcomes</label>
                                <textarea class="form-control" id="activity_outcomes" name="activity_outcomes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>