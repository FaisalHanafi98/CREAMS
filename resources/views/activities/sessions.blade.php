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
        @if ($errors->any())
            <div class="alert alert-danger w-100" role="alert">
                <strong>Session could not be saved:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            @if(session('role') === 'admin')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSessionModal">
                <i class="fas fa-plus"></i> Schedule New Session
            </button>
            @endif
            <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Activities
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
                            <tr class="session-row" data-status="{{ $session->session_date < now() ? 'past' : 'upcoming' }}">
                                <td>
                                    <div class="date-display">
                                        <span class="date-day">{{ $session->session_date->format('d') }}</span>
                                        <span class="date-month">{{ $session->session_date->format('M Y') }}</span>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                                <td>{{ $session->duration ?? $session->duration_minutes }} mins</td>
                                <td>{{ $session->teacher->name }}</td>
                                <td>{{ $session->location ?? $session->venue }}</td>
                                <td>
                                    <div class="enrollment-status">
                                        <span class="{{ $session->is_full ? 'text-danger' : 'text-success' }}">
                                            {{ $session->valid_participant_count }}/{{ $session->max_capacity ?? $session->max_participants }}
                                        </span>
                                        @if($session->is_full)
                                            <span class="badge badge-danger ml-1">Full</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        // Use model method for real-time status calculation
                                        $statusData = $session->getRealTimeStatus();
                                        $actualStatus = $statusData['status'];
                                        $statusClass = $statusData['class'];
                                        
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ strtoupper($actualStatus) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('activities.enrollments', [$activity->id, $session->id]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Manage Enrollments">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        @if($session->session_date >= now() && in_array($role, ['admin', 'supervisor', 'teacher']))
                                            <a href="{{ route('activities.attendance', [$activity->id, $session->id]) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Mark Attendance">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                        @endif
                                        @if($role === 'admin')
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
            <!-- Custom Pagination -->
            <div class="text-center mt-4">
                <div class="mb-2">
                    <small class="text-muted">
                        Page {{ $sessions->currentPage() }} of {{ $sessions->lastPage() }} • {{ $sessions->total() }} total sessions
                    </small>
                </div>
                
                @if($sessions->lastPage() > 1)
                <div class="d-inline-flex">
                    @php
                        $current = $sessions->currentPage();
                        $last = $sessions->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    
                    {{-- Previous --}}
                    @if(!$sessions->onFirstPage())
                        <a href="{{ $sessions->appends(request()->query())->previousPageUrl() }}" class="text-decoration-none mx-1" style="color: #667eea;">‹ Prev</a>
                    @endif
                    
                    {{-- First page --}}
                    @if($start > 1)
                        <a href="{{ $sessions->appends(request()->query())->url(1) }}" class="text-decoration-none mx-1 px-2 py-1 rounded {{ $current == 1 ? 'bg-primary text-white' : 'text-secondary' }}">1</a>
                        @if($start > 2)
                            <span class="mx-1 text-muted">…</span>
                        @endif
                    @endif
                    
                    {{-- Page range --}}
                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $current)
                            <span class="mx-1 px-2 py-1 rounded bg-primary text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $sessions->appends(request()->query())->url($page) }}" class="text-decoration-none mx-1 px-2 py-1 rounded text-secondary hover-bg-light">{{ $page }}</a>
                        @endif
                    @endfor
                    
                    {{-- Last page --}}
                    @if($end < $last)
                        @if($end < $last - 1)
                            <span class="mx-1 text-muted">…</span>
                        @endif
                        <a href="{{ $sessions->appends(request()->query())->url($last) }}" class="text-decoration-none mx-1 px-2 py-1 rounded text-secondary">{{ $last }}</a>
                    @endif
                    
                    {{-- Next --}}
                    @if($sessions->hasMorePages())
                        <a href="{{ $sessions->appends(request()->query())->nextPageUrl() }}" class="text-decoration-none mx-1" style="color: #667eea;">Next ›</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Create Session Modal - Enhanced Version --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content enhanced-modal">
            <form id="sessionModalForm" action="{{ route('activities.sessions.create', $activity->id) }}" method="POST">
                @csrf
                <div class="modal-header enhanced-header">
                    <div class="header-info">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-plus text-primary"></i>
                            Schedule New Session
                        </h5>
                        <p class="activity-subtitle">{{ $activity->activity_name }} ({{ $activity->activity_code }})</p>
                    </div>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                {{-- Validation Alert --}}
                <div id="modalValidationAlert" class="validation-alert" style="display: none;">
                    <div class="alert-content">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="alert-text">
                            <strong>Please fix the following issues:</strong>
                            <ul id="modalValidationList"></ul>
                        </div>
                    </div>
                </div>

                <div class="modal-body enhanced-body">
                    {{-- Required Fields Section --}}
                    <div class="form-section">
                        <div class="section-header">
                            <h6><i class="fas fa-star text-danger"></i> Essential Information</h6>
                            <small>All fields marked with <span class="text-danger">*</span> are mandatory</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group enhanced-field">
                                    <label for="modal_teacher_id" class="enhanced-label">
                                        <i class="fas fa-user-tie text-primary"></i>
                                        Assigned Teacher <span class="required-star">*</span>
                                    </label>
                                    <select class="form-control enhanced-select" 
                                            id="modal_teacher_id" 
                                            name="teacher_id" 
                                            required 
                                            data-validation="required">
                                        <option value="">Choose qualified teacher...</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" 
                                                    data-qualifications="{{ $teacher->qualifications ?? '' }}"
                                                    data-specialization="{{ $teacher->specialization ?? '' }}">
                                                {{ $teacher->name }}
                                                @if($teacher->qualifications)
                                                    - {{ $teacher->qualifications }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="field-error" id="modal_teacher_id_error"></div>
                                    <small class="field-hint">
                                        <i class="fas fa-info-circle text-info"></i>
                                        Select teacher with appropriate qualifications for this activity
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group enhanced-field">
                                    <label for="modal_centre_id" class="enhanced-label">
                                        <i class="fas fa-building text-primary"></i>
                                        Centre <span class="required-star">*</span>
                                    </label>
                                    <select class="form-control enhanced-select" 
                                            id="modal_centre_id" 
                                            name="centre_id" 
                                            required 
                                            data-validation="required">
                                        <option value="">Select centre...</option>
                                        @foreach($centres ?? [] as $centre)
                                            <option value="{{ $centre->centre_id }}" 
                                                    {{ $activity->centre_id == $centre->centre_id ? 'selected' : '' }}
                                                    data-location="{{ $centre->location }}"
                                                    data-hours="{{ $centre->operating_hours ?? '8:00 AM - 6:00 PM' }}">
                                                {{ $centre->centre_name }} - {{ $centre->location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="field-error" id="modal_centre_id_error"></div>
                                    <small class="field-hint">
                                        <i class="fas fa-info-circle text-info"></i>
                                        Default: {{ $activity->centre->centre_name ?? 'Activity centre' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Configuration --}}
                    <div class="form-section">
                        <div class="section-header">
                            <h6><i class="fas fa-clock text-warning"></i> Schedule Configuration</h6>
                            <small>Set the precise timing for this session</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group enhanced-field">
                                    <label for="modal_date" class="enhanced-label">
                                        <i class="fas fa-calendar text-primary"></i>
                                        Session Date <span class="required-star">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="modal_date" 
                                           name="date" 
                                           min="{{ date('Y-m-d') }}" 
                                           max="{{ date('Y-m-d', strtotime('+6 months')) }}"
                                           required 
                                           data-validation="required|date|future">
                                    <div class="field-error" id="modal_date_error"></div>
                                    <div id="modalDateConflict" class="conflict-warning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Date conflicts detected</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group enhanced-field">
                                    <label for="modal_start_time" class="enhanced-label">
                                        <i class="fas fa-clock text-primary"></i>
                                        Start Time <span class="required-star">*</span>
                                    </label>
                                    <input type="time" 
                                           class="form-control" 
                                           id="modal_start_time" 
                                           name="start_time" 
                                           required 
                                           data-validation="required|time|business_hours">
                                    <div class="field-error" id="modal_start_time_error"></div>
                                    <div id="modalTimeConflict" class="conflict-warning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Time slot conflicts</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group enhanced-field">
                                    <label for="modal_duration" class="enhanced-label">
                                        <i class="fas fa-hourglass-half text-primary"></i>
                                        Duration <span class="required-star">*</span>
                                    </label>
                                    <select class="form-control enhanced-select" 
                                            id="modal_duration" 
                                            name="duration" 
                                            required 
                                            data-validation="required">
                                        <option value="">Select duration...</option>
                                        <option value="15">15 minutes</option>
                                        <option value="20">20 minutes</option>
                                        <option value="30" selected>30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60">1 hour</option>
                                        <option value="90">1.5 hours</option>
                                        <option value="120">2 hours</option>
                                    </select>
                                    <div class="field-error" id="modal_duration_error"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Schedule Preview --}}
                        <div id="modalSchedulePreview" class="schedule-preview-box" style="display: none;">
                            <div class="preview-header">
                                <i class="fas fa-eye text-info"></i>
                                <strong>Schedule Preview</strong>
                            </div>
                            <div class="preview-content">
                                <div class="preview-item">
                                    <strong>Session:</strong> <span id="modalPreviewDateTime">-</span>
                                </div>
                                <div class="preview-item">
                                    <strong>Duration:</strong> <span id="modalPreviewDuration">-</span>
                                </div>
                                <div class="preview-item">
                                    <strong>End Time:</strong> <span id="modalPreviewEndTime">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Location & Capacity --}}
                    <div class="form-section">
                        <div class="section-header">
                            <h6><i class="fas fa-map-marker-alt text-success"></i> Location & Capacity</h6>
                            <small>Specify where the session will be held and participant limits</small>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group enhanced-field">
                                    <label for="modal_location" class="enhanced-label">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        Specific Location <span class="required-star">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="modal_location" 
                                           name="location" 
                                           placeholder="e.g., Therapy Room A, Main Hall, Computer Lab"
                                           required 
                                           data-validation="required|min:3">
                                    <div class="field-error" id="modal_location_error"></div>
                                    <small class="field-hint">
                                        <i class="fas fa-info-circle text-info"></i>
                                        Be specific to help participants find the session easily
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group enhanced-field">
                                    <label for="modal_max_capacity" class="enhanced-label">
                                        <i class="fas fa-users text-primary"></i>
                                        Max Capacity <span class="required-star">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="modal_max_capacity" 
                                               name="max_capacity" 
                                               min="3" 
                                               max="10" 
                                               value="10" 
                                               required 
                                               data-validation="required|numeric|min:3|max:10">
                                        <div class="input-group-append">
                                            <span class="input-group-text">people</span>
                                        </div>
                                    </div>
                                    <div class="field-error" id="modal_max_capacity_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_room_number" class="enhanced-label">
                                        <i class="fas fa-door-open text-secondary"></i>
                                        Room/Hall Number
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="modal_room_number" 
                                           name="room_number" 
                                           placeholder="e.g., A101, B205, TH-1">
                                    <small class="field-hint">
                                        <i class="fas fa-info-circle text-info"></i>
                                        Optional formal room designation
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_status" class="enhanced-label">
                                        <i class="fas fa-flag text-secondary"></i>
                                        Initial Status
                                    </label>
                                    <select class="form-control enhanced-select" id="modal_status" name="status">
                                        <option value="scheduled" selected>Scheduled</option>
                                        <option value="draft">Draft (not visible to trainees)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Additional Information --}}
                    <div class="form-section">
                        <div class="section-header">
                            <h6><i class="fas fa-sticky-note text-info"></i> Additional Information</h6>
                            <small>Optional notes and special instructions</small>
                        </div>

                        <div class="form-group">
                            <label for="modal_notes" class="enhanced-label">
                                <i class="fas fa-clipboard-list text-secondary"></i>
                                Session Notes & Instructions
                            </label>
                            <textarea class="form-control" 
                                      id="modal_notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Add any special instructions, materials needed, preparation requirements, or session objectives..."></textarea>
                            <small class="field-hint">
                                <i class="fas fa-info-circle text-info"></i>
                                Include setup requirements, special materials, or participant preparation notes
                            </small>
                        </div>
                    </div>

                    {{-- Validation Summary --}}
                    <div id="modalValidationSummary" class="validation-summary-box" style="display: none;">
                        <div class="summary-header">
                            <i class="fas fa-clipboard-check text-success"></i>
                            <strong>Validation Status</strong>
                        </div>
                        <div id="modalValidationItems" class="validation-items">
                            <!-- Validation items will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer enhanced-footer">
                    <div class="footer-info">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            All required fields must be completed before scheduling
                        </small>
                    </div>
                    <div class="footer-actions">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">
                            <i class="fas fa-calendar-plus"></i> Schedule Session
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
<link rel="stylesheet" href="{{ asset('css/form-validation-enhanced.css') }}">
<link rel="stylesheet" href="{{ asset('css/session-enhanced.css') }}">
<style>
/* Filter Tab Styles */
.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 0;
}

.filter-tab {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
}

.filter-tab:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
}

.filter-tab.active {
    background: #667eea;
    border-color: #667eea;
    color: #fff;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.sessions-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
}

.sessions-card-header h2 {
    margin: 0;
    font-size: 20px;
    color: #2c3e50;
}

/* Pagination Styles */
.hover-bg-light:hover {
    background-color: #f8f9fa !important;
    transition: background-color 0.2s ease;
}

/* Enhanced Modal Styles */
.enhanced-modal {
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.enhanced-header {
    background: linear-gradient(135deg, #32bdea, #c850c0);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 30px;
}

.header-info .modal-title {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.activity-subtitle {
    margin: 5px 0 0 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.enhanced-body {
    padding: 30px;
}

.form-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #32bdea;
}

.section-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e6ed;
}

.section-header h6 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.section-header h6 i {
    margin-right: 8px;
}

.enhanced-field {
    margin-bottom: 20px;
}

.enhanced-label {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.enhanced-label i {
    margin-right: 8px;
}

.required-star {
    color: #ff4757;
    margin-left: 4px;
    font-weight: 700;
}

.enhanced-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
}

.field-error {
    display: none;
    color: #ff4757;
    font-size: 13px;
    margin-top: 5px;
    font-weight: 500;
}

.field-error.show {
    display: block;
}

.field-hint {
    display: block;
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
}

.conflict-warning {
    background: #fffbf0;
    border: 1px solid #fed7aa;
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 8px;
    color: #92400e;
    font-size: 13px;
    font-weight: 500;
}

.conflict-warning i {
    margin-right: 6px;
    color: #ffa726;
}

.schedule-preview-box {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
}

.preview-header {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #3742fa;
    margin-bottom: 12px;
}

.preview-header i {
    margin-right: 8px;
}

.preview-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
}

.preview-item {
    font-size: 14px;
}

.validation-alert {
    background: #fff5f5;
    border: 1px solid #fed7d7;
    margin: 0 30px;
    padding: 15px;
    border-radius: 8px;
}

.alert-content {
    display: flex;
    align-items: flex-start;
}

.alert-content i {
    color: #ff4757;
    margin-right: 10px;
    margin-top: 2px;
}

.alert-text strong {
    color: #ff4757;
}

.validation-summary-box {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.summary-header {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #2ed573;
    margin-bottom: 15px;
}

.summary-header i {
    margin-right: 8px;
}

.enhanced-footer {
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .enhanced-footer {
        flex-direction: column;
        gap: 15px;
    }
    
    .footer-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
<script src="{{ asset('js/form-validation-enhanced.js') }}"></script>
<script>
// Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Session Filter Tabs
    const filterTabs = document.querySelectorAll('.filter-tab');
    const sessionRows = document.querySelectorAll('.session-row');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter sessions
            sessionRows.forEach(row => {
                const status = row.getAttribute('data-status');
                
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'upcoming' && status === 'upcoming') {
                    row.style.display = '';
                } else if (filter === 'past' && status === 'past') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update session count
            updateSessionCount();
        });
    });
    
    function updateSessionCount() {
        const visibleSessions = document.querySelectorAll('.session-row:not([style*="display: none"])').length;
        const activeFilter = document.querySelector('.filter-tab.active').textContent;
        
        // Could update a counter if exists
        console.log(`Showing ${visibleSessions} ${activeFilter.toLowerCase()}`);
    }

    // Enhanced Session Modal Management
    const sessionModal = document.getElementById('createSessionModal');
    const sessionForm = document.getElementById('sessionModalForm');
    
    if (sessionModal && sessionForm) {
        // Initialize modal form validator
        const modalValidator = new ModalSessionValidator({
            formId: 'sessionModalForm',
            activityId: {{ $activity->id }},
            activityName: '{{ $activity->activity_name }}',
            existingSessions: @json($sessions ?? [])
        });
        
        // Reset form when modal is shown
        $(sessionModal).on('shown.bs.modal', function() {
            modalValidator.resetForm();
        });
        
        // Clean up when modal is hidden
        $(sessionModal).on('hidden.bs.modal', function() {
            modalValidator.clearValidation();
        });
    }
});

// Modal Session Validator Class
class ModalSessionValidator {
    constructor(options) {
        this.options = options;
        this.form = document.getElementById(options.formId);
        this.fields = this.getFormFields();
        this.validationRules = this.getValidationRules();
        
        this.setupEventListeners();
    }
    
    getFormFields() {
        return {
            teacher_id: document.getElementById('modal_teacher_id'),
            centre_id: document.getElementById('modal_centre_id'),
            date: document.getElementById('modal_date'),
            start_time: document.getElementById('modal_start_time'),
            duration: document.getElementById('modal_duration'),
            location: document.getElementById('modal_location'),
            max_capacity: document.getElementById('modal_max_capacity')
        };
    }
    
    getValidationRules() {
        return {
            teacher_id: { required: true, message: 'Please select a qualified teacher' },
            centre_id: { required: true, message: 'Please select a centre' },
            date: { required: true, date: true, future: true, message: 'Please select a valid future date' },
            start_time: { required: true, time: true, message: 'Please select a valid start time' },
            duration: { required: true, message: 'Please select session duration' },
            location: { required: true, minLength: 3, message: 'Please specify the session location' },
            max_capacity: { required: true, numeric: true, min: 1, max: 50, message: 'Capacity must be between 1 and 50' }
        };
    }
    
    setupEventListeners() {
        // Real-time validation for all fields
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            if (field) {
                field.addEventListener('blur', () => this.validateField(fieldName));
                field.addEventListener('input', () => this.debounceValidation(fieldName));
                field.addEventListener('change', () => this.handleFieldChange(fieldName));
            }
        });
        
        // Schedule preview updates
        ['date', 'start_time', 'duration'].forEach(fieldName => {
            const field = this.fields[fieldName];
            if (field) {
                field.addEventListener('change', () => this.updateSchedulePreview());
            }
        });
        
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    validateField(fieldName) {
        const field = this.fields[fieldName];
        const rules = this.validationRules[fieldName];
        
        if (!field || !rules) return true;
        
        const value = field.value.trim();
        const errors = [];
        
        // Required validation
        if (rules.required && !value) {
            errors.push(rules.message || `${fieldName} is required`);
        }
        
        // Type-specific validations
        if (value) {
            if (rules.date && !this.isValidDate(value)) {
                errors.push('Please enter a valid date');
            }
            
            if (rules.future && !this.isFutureDate(value)) {
                errors.push('Date must be in the future');
            }
            
            if (rules.time && !this.isValidTime(value)) {
                errors.push('Please enter a valid time');
            }
            
            if (rules.numeric && !this.isNumeric(value)) {
                errors.push('Please enter a valid number');
            }
            
            if (rules.min && parseFloat(value) < rules.min) {
                errors.push(`Minimum value is ${rules.min}`);
            }
            
            if (rules.max && parseFloat(value) > rules.max) {
                errors.push(`Maximum value is ${rules.max}`);
            }
            
            if (rules.minLength && value.length < rules.minLength) {
                errors.push(`Minimum length is ${rules.minLength} characters`);
            }
        }
        
        this.updateFieldDisplay(fieldName, errors);
        return errors.length === 0;
    }
    
    updateFieldDisplay(fieldName, errors) {
        const field = this.fields[fieldName];
        const errorElement = document.getElementById(`modal_${fieldName}_error`);
        
        if (errors.length > 0) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            
            if (errorElement) {
                errorElement.textContent = errors[0];
                errorElement.classList.add('show');
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.classList.remove('show');
            }
        }
    }
    
    debounceValidation(fieldName) {
        clearTimeout(this.validationTimers?.[fieldName]);
        
        if (!this.validationTimers) this.validationTimers = {};
        
        this.validationTimers[fieldName] = setTimeout(() => {
            this.validateField(fieldName);
        }, 500);
    }
    
    handleFieldChange(fieldName) {
        this.validateField(fieldName);
        
        // Specific handlers
        if (['date', 'start_time', 'duration'].includes(fieldName)) {
            this.checkScheduleConflicts();
        }
    }
    
    updateSchedulePreview() {
        const date = this.fields.date?.value;
        const time = this.fields.start_time?.value;
        const duration = this.fields.duration?.value;
        
        const preview = document.getElementById('modalSchedulePreview');
        if (!preview || !date || !time || !duration) return;
        
        try {
            const sessionDate = new Date(`${date}T${time}`);
            const endTime = new Date(sessionDate.getTime() + (parseInt(duration) * 60000));
            
            const dateTimeDisplay = sessionDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) + ' at ' + sessionDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            document.getElementById('modalPreviewDateTime').textContent = dateTimeDisplay;
            document.getElementById('modalPreviewDuration').textContent = `${duration} minutes`;
            document.getElementById('modalPreviewEndTime').textContent = endTime.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            preview.style.display = 'block';
        } catch (e) {
            preview.style.display = 'none';
        }
    }
    
    checkScheduleConflicts() {
        // Check for conflicts and show warnings
        const dateConflict = document.getElementById('modalDateConflict');
        const timeConflict = document.getElementById('modalTimeConflict');
        
        // Implementation would check against existing sessions
        // For now, hide conflicts
        if (dateConflict) dateConflict.style.display = 'none';
        if (timeConflict) timeConflict.style.display = 'none';
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateAllFields()) {
            this.showValidationAlert();
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('modalSubmitBtn');
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';
        submitBtn.disabled = true;
        
        // Submit form
        this.form.submit();
    }
    
    validateAllFields() {
        let isValid = true;
        Object.keys(this.fields).forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                isValid = false;
            }
        });
        return isValid;
    }
    
    showValidationAlert() {
        const errors = this.getValidationErrors();
        const alert = document.getElementById('modalValidationAlert');
        const list = document.getElementById('modalValidationList');
        
        if (errors.length === 0) {
            alert.style.display = 'none';
            return;
        }
        
        list.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            list.appendChild(li);
        });
        
        alert.style.display = 'block';
        alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    getValidationErrors() {
        const errors = [];
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            const rules = this.validationRules[fieldName];
            
            if (rules?.required && (!field?.value || field.value.trim() === '')) {
                errors.push(rules.message || `${fieldName} is required`);
            }
        });
        return errors;
    }
    
    resetForm() {
        this.form.reset();
        this.clearValidation();
        document.getElementById('modalSchedulePreview').style.display = 'none';
    }
    
    clearValidation() {
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            const errorElement = document.getElementById(`modal_${fieldName}_error`);
            
            if (field) {
                field.classList.remove('is-valid', 'is-invalid');
            }
            
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.classList.remove('show');
            }
        });
        
        document.getElementById('modalValidationAlert').style.display = 'none';
    }
    
    // Utility methods
    isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }
    
    isFutureDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date >= today;
    }
    
    isValidTime(timeString) {
        const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return timeRegex.test(timeString);
    }
    
    isNumeric(value) {
        return !isNaN(value) && isFinite(value);
    }
}
</script>
@endsection