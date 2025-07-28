@extends('layouts.app')

@section('title', 'Enhanced Attendance Management | CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
<style>
    .attendance-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .attendance-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .session-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .session-card:hover {
        border-left-color: #28a745;
        background: #f0fff4;
    }
    
    .attendance-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-present { background: #d4edda; color: #155724; }
    .status-absent { background: #f8d7da; color: #721c24; }
    .status-late { background: #fff3cd; color: #856404; }
    .status-excused { background: #d1ecf1; color: #0c5460; }
    .status-unmarked { background: #e9ecef; color: #6c757d; }
    
    .birthday-alert {
        background: linear-gradient(45deg, #ff6b6b, #feca57);
        color: white;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }
    
    .quick-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        backdrop-filter: blur(10px);
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .trainee-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .btn-mark-attendance {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-mark-attendance:hover {
        background: linear-gradient(45deg, #218838, #1ca085);
        transform: translateY(-1px);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check text-primary mr-2"></i>
                Enhanced Attendance Management
            </h1>
            <p class="text-muted">Manage attendance for activity sessions with real-time tracking</p>
        </div>
    </div>
    
    <!-- Quick Stats Section -->
    @if(isset($stats))
    <div class="quick-stats">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h2 class="mb-0">{{ $stats['total_sessions'] ?? 0 }}</h2>
                    <small>Total Sessions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h2 class="mb-0">{{ $stats['completed_sessions'] ?? 0 }}</h2>
                    <small>Completed Sessions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h2 class="mb-0">{{ $stats['attendance_rate'] ?? 0 }}%</h2>
                    <small>Attendance Rate</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h2 class="mb-0">{{ $stats['total_enrollments'] ?? 0 }}</h2>
                    <small>Total Enrollments</small>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Filters Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('attendance.index') }}" class="row align-items-end">
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate ?? now()->toDateString() }}">
            </div>
            
            @if(isset($centres) && $centres->count() > 0)
            <div class="col-md-3">
                <label for="centre_id" class="form-label">Centre</label>
                <select name="centre_id" id="centre_id" class="form-control">
                    <option value="">All Centres</option>
                    @foreach($centres as $centre)
                        <option value="{{ $centre->id }}" {{ ($selectedCentreId ?? '') == $centre->id ? 'selected' : '' }}>
                            {{ $centre->centre_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            @if(isset($activities) && $activities->count() > 0)
            <div class="col-md-3">
                <label for="activity_id" class="form-label">Activity</label>
                <select name="activity_id" id="activity_id" class="form-control">
                    <option value="">All Activities</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}" {{ ($selectedActivityId ?? '') == $activity->id ? 'selected' : '' }}>
                            {{ $activity->activity_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary ml-2">
                    <i class="fas fa-times mr-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
    
    <!-- Upcoming Birthdays Alert -->
    @if(isset($upcomingBirthdays) && $upcomingBirthdays->count() > 0)
    <div class="birthday-alert">
        <i class="fas fa-birthday-cake mr-2"></i>
        <strong>Upcoming Birthdays:</strong>
        @foreach($upcomingBirthdays as $birthday)
            <span class="badge badge-light ml-1">{{ $birthday->trainee_first_name }} {{ $birthday->trainee_last_name }}</span>
        @endforeach
    </div>
    @endif
    
    <!-- Sessions Grid -->
    <div class="row">
        @forelse($sessions ?? [] as $session)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card attendance-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-dumbbell text-primary mr-2"></i>
                        {{ $session->activity->activity_name ?? 'Unknown Activity' }}
                    </h6>
                    @if($session->attendance_marked ?? false)
                        <span class="badge badge-success">
                            <i class="fas fa-check"></i> Marked
                        </span>
                    @else
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i> Pending
                        </span>
                    @endif
                </div>
                
                <div class="card-body">
                    <!-- Session Info -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-sm">
                            <span><i class="fas fa-clock text-muted mr-1"></i>{{ $session->start_time ?? 'N/A' }} - {{ $session->end_time ?? 'N/A' }}</span>
                            <span><i class="fas fa-user text-muted mr-1"></i>{{ $session->teacher->name ?? 'No Teacher' }}</span>
                        </div>
                        @if(isset($session->activity->centre))
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $session->activity->centre->centre_name }}
                        </small>
                        @endif
                    </div>
                    
                    <!-- Enrolled Trainees -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Enrolled Trainees ({{ $session->sessionEnrollments->count() ?? 0 }})</h6>
                        @if(isset($session->sessionEnrollments) && $session->sessionEnrollments->count() > 0)
                            @foreach($session->sessionEnrollments->take(5) as $enrollment)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center">
                                        @if($enrollment->trainee ?? false)
                                            <img src="{{ asset($enrollment->trainee->trainee_avatar ?? 'images/default-avatar.png') }}" 
                                                 alt="{{ $enrollment->trainee->trainee_first_name }}" 
                                                 class="trainee-avatar mr-2">
                                            <small>{{ $enrollment->trainee->trainee_first_name }} {{ $enrollment->trainee->trainee_last_name }}</small>
                                        @else
                                            <small class="text-muted">Unknown Trainee</small>
                                        @endif
                                    </div>
                                    @if($enrollment->attendance_status ?? false)
                                        <span class="attendance-status status-{{ $enrollment->attendance_status }}">
                                            {{ ucfirst($enrollment->attendance_status) }}
                                        </span>
                                    @else
                                        <span class="attendance-status status-unmarked">Not Marked</span>
                                    @endif
                                </div>
                            @endforeach
                            
                            @if($session->sessionEnrollments->count() > 5)
                                <small class="text-muted">... and {{ $session->sessionEnrollments->count() - 5 }} more</small>
                            @endif
                        @else
                            <small class="text-muted">No trainees enrolled</small>
                        @endif
                    </div>
                </div>
                
                <div class="card-footer">
                    @if($session->sessionEnrollments->count() > 0)
                        @if(!($session->attendance_marked ?? false))
                            <button type="button" class="btn btn-mark-attendance btn-block" 
                                    onclick="markAttendance({{ $session->id }})">
                                <i class="fas fa-clipboard-check mr-2"></i>Mark Attendance
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-success btn-block" 
                                    onclick="viewAttendance({{ $session->id }})">
                                <i class="fas fa-eye mr-2"></i>View Attendance
                            </button>
                        @endif
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-block" disabled>
                            <i class="fas fa-users mr-2"></i>No Enrollments
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>No Sessions Found</h4>
                <p>No activity sessions found for the selected date and filters.</p>
                <p class="text-sm">Try selecting a different date or clearing the filters.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="attendanceModalBody">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function markAttendance(sessionId) {
    // Show modal with loading state
    $('#attendanceModal').modal('show');
    $('#attendanceModalBody').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading attendance form...</p>
        </div>
    `);
    
    // Load attendance form via AJAX with proper error handling
    $.ajax({
        url: `/enhanced-attendance/session/${sessionId}/form`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.html) {
                $('#attendanceModalBody').html(response.html);
            } else {
                throw new Error(response.error || 'Failed to load attendance form');
            }
        },
        error: function(xhr) {
            console.error('Failed to load attendance form:', xhr);
            
            let errorMessage = 'Failed to load attendance form. Please try again.';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            } else if (xhr.status === 404) {
                errorMessage = 'Session not found or you do not have permission to access it.';
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to mark attendance for this session.';
            } else if (xhr.status === 422) {
                errorMessage = 'Cannot mark attendance for this session.';
            } else if (xhr.status >= 500) {
                errorMessage = 'Server error occurred. Please contact the administrator.';
            }
            
            $('#attendanceModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> ${errorMessage}
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="markAttendance(${sessionId})">
                        <i class="fas fa-redo me-1"></i>Try Again
                    </button>
                </div>
            `);
        }
    });
}

function viewAttendance(sessionId) {
    // Redirect to view attendance page
    window.location.href = `/activities/sessions/${sessionId}/attendance/view`;
}

// Auto-refresh stats every 5 minutes
setInterval(function() {
    $.get('/enhanced-attendance/stats/today', function(data) {
        if (data.success && data.stats) {
            updateStats(data.stats);
        }
    });
}, 300000);

function updateStats(stats) {
    // Update stats in real-time
    const statCards = document.querySelectorAll('.stat-card h2');
    if (statCards.length >= 4) {
        statCards[0].textContent = stats.total_sessions || 0;
        statCards[1].textContent = stats.completed_sessions || 0;
        statCards[2].textContent = (stats.attendance_rate || 0) + '%';
        statCards[3].textContent = stats.total_enrollments || 0;
    }
}

// Quick filters
$(document).ready(function() {
    $('#centre_id').on('change', function() {
        // Auto-submit form when centre changes
        $(this).closest('form').submit();
    });
    
    $('#activity_id').on('change', function() {
        // Auto-submit form when activity changes
        $(this).closest('form').submit();
    });
    
    $('#date').on('change', function() {
        // Auto-submit form when date changes
        $(this).closest('form').submit();
    });
});
</script>

<!-- SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection