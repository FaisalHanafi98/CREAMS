<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Trainee Profile Page</title>
    <meta name="author" content="CREAMS" />
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900&display=swap" rel="stylesheet">
    
    <!-- CSS Dependencies -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css'>
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/traineeprofilestyle.css') }}"> 
</head>
<body>
    <div class="ScriptTop">
        <div class="rt-container">
            <div class="col-rt-4" id="float-right">
                <!-- Ad Space (Optional) -->
            </div>
        </div>
    </div>

    <a class="back-btn" href="/dashboard">
        <i class="fas fa-arrow-left"></i>
    </a>
    
    <section>
        <div class="rt-container">
            <div class="col-rt-12">
                <div class="Scriptcontent">
                    <div class="trainee-profile py-4">
                        <div class="container">
                            <div class="row">
                                <header class="ScriptHeader">
                                    <div class="rt-container">
                                        <div class="col-rt-12">
                                            <div class="rt-heading">
                                                <h1>Trainee Profile</h1>
                                            </div>
                                        </div>
                                    </div>
                                </header>

                                <!-- Profile Navigation Tabs -->
                                <div class="col-lg-12">
                                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">
                                                <i class="fas fa-user"></i> Personal Info
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab" aria-controls="attendance" aria-selected="false">
                                                <i class="fas fa-calendar-check"></i> Attendance
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="activities-tab" data-toggle="tab" href="#activities" role="tab" aria-controls="activities" aria-selected="false">
                                                <i class="fas fa-tasks"></i> Activities
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="profileTabsContent">
                                        <!-- Personal Info Tab -->
                                        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="card shadow-sm">
                                                        <div class="card-header bg-transparent text-centre">
                                                            <img class="profile_img" src="{{ asset('images/trainees/default-profile.jpg') }}" alt="trainee profile picture">
                                                            <h3>{{ $trainee->name }}</h3>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="mb-0"><strong class="pr-1">Trainee ID:</strong>{{ $trainee->id }}</p>
                                                            <p class="mb-0"><strong class="pr-1">Class:</strong>{{ $trainee->class }}</p>
                                                            <p class="mb-0"><strong class="pr-1">Section:</strong>{{ $trainee->section }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-8">
                                                    <div class="card shadow-sm">
                                                        <div class="card-header bg-transparent border-0">
                                                            <h3 class="mb-0"><i class="far fa-clone pr-1"></i>General Information</h3>
                                                        </div>
                                                        <div class="card-body pt-0">
                                                            <table class="table table-bordered">
                                                                <tr>
                                                                    <th width="30%">Full Name</th>
                                                                    <td width="2%">:</td>
                                                                    <td>{{ $trainee->full_name }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th width="30%">Academic Year</th>
                                                                    <td width="2%">:</td>
                                                                    <td>{{ $trainee->academic_year }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th width="30%">Gender</th>
                                                                    <td width="2%">:</td>
                                                                    <td>{{ $trainee->gender }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th width="30%">Date of Birth</th>
                                                                    <td width="2%">:</td>
                                                                    <td>{{ $trainee->date_of_birth }}</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attendance Tab -->
                                        <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                                            <div class="card shadow-sm">
                                                <div class="card-header bg-transparent border-0">
                                                    <h3 class="mb-0"><i class="fas fa-calendar-check pr-1"></i>Attendance Overview</h3>
                                                </div>
                                                <div class="card-body">
                                                    @php
                                                        $currentMonth = date('Y-m');
                                                        $startDate = date('Y-m-01');
                                                        $endDate = date('Y-m-t');
                                                        
                                                        $attendanceStats = App\Models\Attendances::where('trainee_id', $trainee->id)
                                                            ->whereBetween('date', [$startDate, $endDate])
                                                            ->get();
                                                        
                                                        $presentCount = $attendanceStats->where('status', 'present')->count();
                                                        $lateCount = $attendanceStats->where('status', 'late')->count();
                                                        $totalDays = $attendanceStats->count();
                                                        
                                                        $attendanceRate = $totalDays > 0 ? 
                                                            round((($presentCount + ($lateCount * 0.5)) / $totalDays) * 100) : 0;
                                                            
                                                        $bgClass = $attendanceRate >= 90 ? 'bg-success' :
                                                                  ($attendanceRate >= 75 ? 'bg-primary' :
                                                                  ($attendanceRate >= 60 ? 'bg-warning' : 'bg-danger'));
                                                    @endphp
                                                    
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar {{ $bgClass }}" role="progressbar" 
                                                             style="width: {{ $attendanceRate }}%;" 
                                                             aria-valuenow="{{ $attendanceRate }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            {{ $attendanceRate }}%
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="attendance-stats mt-3">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <small>
                                                                    <span class="text-success">Present: {{ $presentCount }}</span> | 
                                                                    <span class="text-warning">Late: {{ $lateCount }}</span> | 
                                                                    <span class="text-danger">Absent: {{ $attendanceStats->where('status', 'absent')->count() }}</span>
                                                                </small>
                                                            </div>
                                                            <div class="col-md-6 text-right">
                                                                <a href="{{ route('attendance.trainee', ['id' => $trainee->id]) }}" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-calendar-alt"></i> View Full Attendance
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="attendance-history mt-4">
                                                        <h5>Recent Attendance Records</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date</th>
                                                                        <th>Status</th>
                                                                        <th>Activity</th>
                                                                        <th>Remarks</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php
                                                                        $recentAttendance = App\Models\Attendances::where('trainee_id', $trainee->id)
                                                                            ->orderBy('date', 'desc')
                                                                            ->limit(10)
                                                                            ->get();
                                                                    @endphp
                                                                    
                                                                    @forelse($recentAttendance as $record)
                                                                        <tr class="
                                                                            @if($record->status == 'present') table-success 
                                                                            @elseif($record->status == 'absent') table-danger 
                                                                            @elseif($record->status == 'late') table-warning 
                                                                            @endif">
                                                                            <td>{{ date('M d, Y', strtotime($record->date)) }}</td>
                                                                            <td>
                                                                                <span class="badge 
                                                                                    @if($record->status == 'present') badge-success 
                                                                                    @elseif($record->status == 'absent') badge-danger 
                                                                                    @elseif($record->status == 'late') badge-warning 
                                                                                    @endif">
                                                                                    {{ ucfirst($record->status) }}
                                                                                </span>
                                                                            </td>
                                                                            <td>{{ optional($record->activity)->name ?? 'N/A' }}</td>
                                                                            <td>{{ $record->remarks ?? 'No remarks' }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="4" class="text-centre">No attendance records found.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Activities Tab -->
                                        <div class="tab-pane fade" id="activities" role="tabpanel" aria-labelledby="activities-tab">
                                            <div class="card shadow-sm">
                                                <div class="card-header bg-transparent border-0">
                                                    <h3 class="mb-0"><i class="fas fa-tasks pr-1"></i>Trainee Activities</h3>
                                                </div>
                                                <div class="card-body">
                                                    <!-- Add activities content here -->
                                                    <p>No activities recorded yet.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Tab switching functionality
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // You can add any tab switching logic here if needed
                console.log('Switched to: ' + $(e.target).attr('href'));
            });
        });
    </script>
</body>
</html>