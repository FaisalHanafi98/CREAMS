@extends('layouts.app')

@section('title', 'Activity Schedule - CREAMS')

@section('content')
<div class="modern-schedule-container p-4">
    <!-- Header Section -->
    <div class="schedule-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-4 fw-bold text-primary mb-1">
                    <i class="fas fa-calendar-check"></i> Activity Schedule
                </h1>
                <p class="text-muted mb-0">Manage and view all scheduled activities</p>
            </div>
            <div class="header-actions d-flex gap-2">
                <div class="view-toggle btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-view="calendar">
                        <i class="fas fa-calendar"></i> Calendar
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-view="list">
                        <i class="fas fa-list"></i> List
                    </button>
                </div>
                <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Activities
                </a>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Activity
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="modern-filter-card bg-white rounded-3 shadow-sm mb-4 border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0 text-dark">
                    <i class="fas fa-sliders-h text-primary me-2"></i>Smart Filters
                </h5>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                    <i class="fas fa-cog"></i> Advanced
                </button>
            </div>
            
            <form method="GET" action="{{ route('activities.schedule') }}" id="filterForm">
                <!-- Quick Filters Row -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="centre_filter" name="centre">
                                <option value="">All Centres</option>
                                @foreach($centres ?? [] as $centre)
                                    <option value="{{ $centre->centre_id ?? $centre['id'] }}" 
                                            {{ request('centre') == ($centre->centre_id ?? $centre['id']) ? 'selected' : '' }}>
                                        {{ $centre->centre_name ?? $centre['name'] ?? 'Unknown Centre' }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="centre_filter"><i class="fas fa-building me-2"></i>Centre</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="category_filter" name="category">
                                <option value="">All Categories</option>
                                <option value="Physical Therapy" {{ request('category') == 'Physical Therapy' ? 'selected' : '' }}>Physical Therapy</option>
                                <option value="Occupational Therapy" {{ request('category') == 'Occupational Therapy' ? 'selected' : '' }}>Occupational Therapy</option>
                                <option value="Speech Therapy" {{ request('category') == 'Speech Therapy' ? 'selected' : '' }}>Speech Therapy</option>
                                <option value="Behavioral Therapy" {{ request('category') == 'Behavioral Therapy' ? 'selected' : '' }}>Behavioral Therapy</option>
                                <option value="Mathematics" {{ request('category') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                <option value="Literacy" {{ request('category') == 'Literacy' ? 'selected' : '' }}>Literacy</option>
                            </select>
                            <label for="category_filter"><i class="fas fa-tags me-2"></i>Category</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="date_filter" name="date" value="{{ request('date') }}">
                            <label for="date_filter"><i class="fas fa-calendar me-2"></i>Date</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 h-100 align-items-end">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('activities.schedule') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Filters (Collapsible) -->
                <div class="collapse" id="advancedFilters">
                    <div class="row g-3 pt-3 border-top">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="day_filter" name="day">
                                    <option value="">All Days</option>
                                    <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                                <label for="day_filter">Day of Week</label>
                            </div>
                        </div>
                        @if(in_array($role ?? session('role'), ['admin', 'supervisor']))
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="teacher_filter" name="teacher">
                                    <option value="">All Teachers</option>
                                    @foreach($teachers ?? [] as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('teacher') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="teacher_filter">Teacher</label>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="participant_filter" name="participant" 
                                       placeholder="Enter Trainee ID" value="{{ request('participant') }}">
                                <label for="participant_filter">Trainee ID</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="show_all" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_all">
                                    Include past sessions
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="schedule-content">
        <!-- Calendar View -->
        <div id="calendar-view" class="view-container">
            @if(isset($sessions) && $sessions->count() > 0)
                <!-- Week Navigation -->
                <div class="week-navigation bg-white rounded-3 shadow-sm mb-4 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold">Week of {{ date('M d, Y', strtotime('monday this week')) }}</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="changeWeek(-1)">
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="changeWeek(0)">
                                Today
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="changeWeek(1)">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Weekly Calendar Grid -->
                <div class="weekly-calendar bg-white rounded-3 shadow-sm border-0">
                    <div class="calendar-header p-3 border-bottom">
                        <div class="row g-0">
                            <div class="col-1"></div>
                            @php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            @endphp
                            @foreach($days as $day)
                                <div class="col text-center">
                                    <div class="fw-semibold text-primary">{{ $day }}</div>
                                    <div class="text-muted small">{{ date('M j', strtotime($day . ' this week')) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="calendar-body p-3">
                        @php
                            $timeSlots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
                        @endphp
                        @foreach($timeSlots as $time)
                            <div class="time-row border-bottom py-2">
                                <div class="row g-0 align-items-center">
                                    <div class="col-1">
                                        <span class="text-muted fw-semibold small">{{ date('g:i A', strtotime($time)) }}</span>
                                    </div>
                                    @foreach($days as $day)
                                        <div class="col px-2">
                                            @php
                                                $dayDate = date('Y-m-d', strtotime($day . ' this week'));
                                                $daySession = $sessions->first(function($session) use ($dayDate, $time) {
                                                    if (!$session->scheduled_date || !$session->start_time) return false;
                                                    $sessionDate = \Carbon\Carbon::parse($session->scheduled_date)->format('Y-m-d');
                                                    $sessionTime = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                                                    return $sessionDate == $dayDate && $sessionTime == $time;
                                                });
                                            @endphp
                                            @if($daySession)
                                                <div class="session-card bg-primary text-white rounded-2 p-2 position-relative cursor-pointer" 
                                                     data-bs-toggle="modal" data-bs-target="#sessionModal" 
                                                     data-session="{{ json_encode($daySession) }}">
                                                    <div class="fw-semibold small mb-1">{{ \Str::limit($daySession->activity->activity_name ?? 'Unknown', 20) }}</div>
                                                    <div class="small opacity-75">
                                                        <i class="fas fa-user me-1"></i>{{ $daySession->teacher?->name ?? 'Unassigned' }}
                                                    </div>
                                                    <div class="small opacity-75">
                                                        <i class="fas fa-users me-1"></i>{{ $daySession->enrollments->count() ?? 0 }}/{{ $daySession->max_capacity ?? 'N/A' }}
                                                    </div>
                                                    <div class="position-absolute top-0 end-0 p-1">
                                                        <span class="badge bg-light text-dark rounded-pill px-2 small">
                                                            {{ ucfirst($daySession->status ?? 'scheduled') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="empty-slot rounded-2 p-2" style="min-height: 60px; background: #f8f9fa; border: 2px dashed #dee2e6;">
                                                    @if(in_array(session('role'), ['admin', 'supervisor']))
                                                        <div class="text-center text-muted small">
                                                            <i class="fas fa-plus"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="empty-state text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-calendar-times fa-4x text-primary opacity-25"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">No Sessions Scheduled</h3>
                    <p class="text-muted mb-4">There are no sessions matching your current filters.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('activities.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Schedule New Activity
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- List View (Hidden by default) -->
        <div id="list-view" class="view-container d-none">
            @if(isset($sessions) && $sessions->count() > 0)
                <div class="sessions-list">
                    @foreach($sessions as $session)
                        <div class="session-list-card bg-white rounded-3 shadow-sm mb-3 border-0 overflow-hidden hover-card">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <div class="date-badge bg-primary text-white rounded-3 p-3 text-center">
                                            <div class="fw-bold">{{ $session->scheduled_date ? \Carbon\Carbon::parse($session->scheduled_date)->format('d') : '--' }}</div>
                                            <div class="small">{{ $session->scheduled_date ? \Carbon\Carbon::parse($session->scheduled_date)->format('M Y') : 'Not Set' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-2 text-dark">
                                            <a href="{{ route('activities.show', $session->activity_id ?? 0) }}" class="text-decoration-none text-dark hover-primary">
                                                {{ $session->activity->activity_name ?? 'Unknown Activity' }}
                                            </a>
                                        </h5>
                                        <div class="session-meta d-flex flex-wrap gap-3 text-muted">
                                            <span><i class="fas fa-clock me-1"></i>{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : 'Not set' }}</span>
                                            <span><i class="fas fa-user me-1"></i>{{ $session->teacher?->name ?? 'Not assigned' }}</span>
                                            <span><i class="fas fa-building me-1"></i>{{ $session->activity->centre->centre_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-primary border border-primary me-2">
                                                {{ $session->activity->category ?? 'N/A' }}
                                            </span>
                                            <span class="badge bg-{{ $session->status == 'completed' ? 'success' : ($session->status == 'ongoing' ? 'warning' : 'info') }}">
                                                {{ ucfirst($session->status ?? 'scheduled') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="enrollment-info">
                                            <div class="fw-bold text-primary h4 mb-0">{{ $session->enrollments->count() ?? 0 }}</div>
                                            <div class="text-muted small">/ {{ $session->max_capacity ?? 'N/A' }} enrolled</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex flex-column gap-2">
                                            <a href="{{ route('activities.sessions', $session->activity_id ?? 0) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                                                <button class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-check"></i> Attend
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(method_exists($sessions, 'links'))
                    <div class="d-flex justify-content-center mt-4">
                        @include('components.custom-pagination', ['items' => $sessions])
                    </div>
                @endif
            @else
                <div class="empty-state text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-list-alt fa-4x text-primary opacity-25"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">No Sessions Found</h3>
                    <p class="text-muted mb-4">No sessions match your current filters or no sessions are scheduled.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('activities.home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Create Activity & Sessions
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Session Detail Modal -->
    <div class="modal fade" id="sessionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Session Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Content will be loaded dynamically -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-primary mb-2">Activity Information</h6>
                            <div class="mb-3">
                                <label class="text-muted small">Activity Name</label>
                                <div class="fw-semibold" id="modal-activity-name"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Category</label>
                                <div id="modal-category"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Centre</label>
                                <div id="modal-centre"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-primary mb-2">Session Information</h6>
                            <div class="mb-3">
                                <label class="text-muted small">Date & Time</label>
                                <div class="fw-semibold" id="modal-datetime"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Teacher</label>
                                <div id="modal-teacher"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Enrollment</label>
                                <div id="modal-enrollment"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="modal-view-btn" class="btn btn-primary">View Full Details</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .modern-schedule-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .schedule-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 2rem;
        color: white;
    }
    
    .modern-filter-card {
        border-radius: 15px !important;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .weekly-calendar {
        border-radius: 15px !important;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .time-row {
        border-bottom: 1px solid #f0f0f0 !important;
        min-height: 80px;
    }
    
    .time-row:last-child {
        border-bottom: none !important;
    }
    
    .session-card {
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 60px;
        border-left: 4px solid rgba(255, 255, 255, 0.3);
    }
    
    .session-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .empty-slot {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .empty-slot:hover {
        background: #e3f2fd !important;
        border-color: #2196f3 !important;
    }
    
    .session-list-card {
        border-radius: 12px !important;
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }
    
    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        border-color: #2196f3;
    }
    
    .date-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .hover-primary:hover {
        color: #2196f3 !important;
    }
    
    .view-toggle .btn {
        border-radius: 8px;
    }
    
    .view-toggle .btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }
    
    .week-navigation {
        border-radius: 12px !important;
        border: 1px solid #f0f0f0;
    }
    
    .modal-content {
        border-radius: 15px !important;
    }
    
    .form-floating {
        border-radius: 10px;
    }
    
    .form-floating .form-control,
    .form-floating .form-select {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }
    
    .form-floating .form-control:focus,
    .form-floating .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    
    .empty-state {
        background: white;
        border-radius: 15px;
        margin: 2rem 0;
        padding: 3rem 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    @media (max-width: 768px) {
        .weekly-calendar .calendar-header .col,
        .weekly-calendar .calendar-body .col {
            padding: 0.25rem !important;
        }
        
        .session-card {
            font-size: 0.75rem;
        }
        
        .time-row {
            min-height: 60px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View Toggle Functionality
        const viewToggleButtons = document.querySelectorAll('.view-toggle .btn');
        const calendarView = document.getElementById('calendar-view');
        const listView = document.getElementById('list-view');
        
        viewToggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Update button states
                viewToggleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Toggle views
                if (view === 'calendar') {
                    calendarView.classList.remove('d-none');
                    listView.classList.add('d-none');
                } else {
                    calendarView.classList.add('d-none');
                    listView.classList.remove('d-none');
                }
                
                // Save preference
                localStorage.setItem('schedule-view', view);
            });
        });
        
        // Restore saved view preference
        const savedView = localStorage.getItem('schedule-view') || 'calendar';
        document.querySelector(`[data-view="${savedView}"]`).click();
        
        // Session Modal Functionality
        const sessionModal = document.getElementById('sessionModal');
        if (sessionModal) {
            sessionModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const sessionData = JSON.parse(button.dataset.session || '{}');

                // Update modal content
                document.getElementById('modal-activity-name').textContent = sessionData.activity?.activity_name || 'N/A';
                // SECURITY: Escape category to prevent XSS
                document.getElementById('modal-category').innerHTML = `<span class="badge bg-primary">${escapeHtml(sessionData.activity?.category || 'N/A')}</span>`;
                document.getElementById('modal-centre').textContent = sessionData.activity?.centre?.centre_name || 'N/A';

                const date = sessionData.scheduled_date ? new Date(sessionData.scheduled_date).toLocaleDateString() : 'Not set';
                const time = sessionData.start_time ? new Date(`1970-01-01 ${sessionData.start_time}`).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Not set';
                document.getElementById('modal-datetime').textContent = `${date} at ${time}`;

                document.getElementById('modal-teacher').textContent = sessionData.teacher?.name || 'Not assigned';
                // SECURITY: Escape max_capacity to prevent XSS
                document.getElementById('modal-enrollment').innerHTML = `<span class="fw-bold">${sessionData.enrollments?.length || 0}</span> / ${escapeHtml(sessionData.max_capacity || 'N/A')} enrolled`;
                
                // Update view button link
                const viewBtn = document.getElementById('modal-view-btn');
                if (sessionData.activity_id) {
                    viewBtn.href = `/activities/${sessionData.activity_id}/sessions`;
                }
            });
        }
        
        // Auto-submit filters on change
        const filterForm = document.getElementById('filterForm');
        const filterSelects = filterForm.querySelectorAll('select, input[type="date"]');
        
        filterSelects.forEach(element => {
            element.addEventListener('change', function() {
                // Small delay to improve UX
                setTimeout(() => {
                    filterForm.submit();
                }, 300);
            });
        });
        
        // Search input with debounce
        const participantFilter = document.getElementById('participant_filter');
        if (participantFilter) {
            let debounceTimer;
            participantFilter.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        filterForm.submit();
                    }
                }, 500);
            });
        }
        
        // Smooth animations for cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe session cards for animation
        document.querySelectorAll('.session-list-card, .session-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
        
        // Add loading states
        const filterButtons = document.querySelectorAll('button[type="submit"]');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                this.disabled = true;
            });
        });
    });
    
    // Week Navigation Functions
    function changeWeek(direction) {
        const currentDate = new Date();
        let targetDate;
        
        if (direction === 0) {
            targetDate = new Date();
        } else {
            const currentWeekStart = new Date(currentDate.setDate(currentDate.getDate() - currentDate.getDay() + 1));
            targetDate = new Date(currentWeekStart.setDate(currentWeekStart.getDate() + (direction * 7)));
        }
        
        // Update URL with date parameter
        const url = new URL(window.location);
        url.searchParams.set('date', targetDate.toISOString().split('T')[0]);
        window.location.href = url.toString();
    }
    
    // Add tooltips to session cards
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endsection