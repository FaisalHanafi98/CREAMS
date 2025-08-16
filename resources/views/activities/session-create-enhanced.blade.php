@extends('layouts.app')

@section('title', 'Schedule Session - ' . $activity->activity_name)

@section('content')
<div class="enhanced-session-container">
    <div class="session-header">
        <div class="header-content">
            <div class="breadcrumb-nav">
                <a href="{{ route('activities.index') }}" class="breadcrumb-link">Activities</a>
                <i class="fas fa-chevron-right breadcrumb-separator"></i>
                <a href="{{ route('activities.show', $activity->id) }}" class="breadcrumb-link">{{ $activity->activity_name }}</a>
                <i class="fas fa-chevron-right breadcrumb-separator"></i>
                <span class="breadcrumb-current">Schedule Session</span>
            </div>
            <h1 class="page-title">Schedule New Session</h1>
            <p class="page-subtitle">{{ $activity->activity_name }} ({{ $activity->activity_code }})</p>
        </div>
    </div>

    {{-- Enhanced Session Creation Form --}}
    <div class="enhanced-form-container">
        <form id="sessionForm" action="{{ route('activities.sessions.create', $activity->id) }}" method="POST">
            @csrf
            <input type="hidden" name="activity_id" value="{{ $activity->id }}">

            {{-- Form Progress Indicator --}}
            <div class="form-progress">
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Schedule</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Location</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 25%"></div>
                </div>
            </div>

            {{-- Form Validation Summary --}}
            <div id="validationSummary" class="validation-summary" style="display: none;">
                <div class="validation-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Please fix the following issues:</span>
                </div>
                <ul id="validationList" class="validation-list"></ul>
            </div>

            {{-- Step 1: Basic Information --}}
            <div class="form-step active" data-step="1">
                <div class="step-header">
                    <h3>Basic Session Information</h3>
                    <p>Configure the essential details for this session</p>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="teacher_id" class="required-label">
                            <i class="fas fa-user-tie"></i> Assigned Teacher
                            <span class="required-asterisk">*</span>
                        </label>
                        <select class="form-control enhanced-select" id="teacher_id" name="teacher_id" required data-validation="required">
                            <option value="">Select a qualified teacher...</option>
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
                        <div class="field-validation-error" id="teacher_id_error"></div>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Choose a teacher with appropriate qualifications for {{ $activity->activity_name }}
                        </small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="max_capacity" class="required-label">
                            <i class="fas fa-users"></i> Maximum Capacity
                            <span class="required-asterisk">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="max_capacity" 
                                   name="max_capacity" 
                                   min="1" 
                                   max="50" 
                                   value="20" 
                                   required 
                                   data-validation="required|numeric|min:1|max:50">
                            <div class="input-group-append">
                                <span class="input-group-text">people</span>
                            </div>
                        </div>
                        <div class="field-validation-error" id="max_capacity_error"></div>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Recommended: {{ $activity->recommended_capacity ?? '15-25' }} participants
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="session_type">Session Type</label>
                        <select class="form-control" id="session_type" name="session_type">
                            <option value="regular" selected>Regular Session</option>
                            <option value="assessment">Assessment Session</option>
                            <option value="group">Group Session</option>
                            <option value="individual">Individual Session</option>
                            <option value="makeup">Make-up Session</option>
                        </select>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Session type affects enrollment and scheduling rules
                        </small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="status">Initial Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="scheduled" selected>Scheduled</option>
                            <option value="draft">Draft (not visible to trainees)</option>
                        </select>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Draft sessions can be edited before publishing
                        </small>
                    </div>
                </div>
            </div>

            {{-- Step 2: Schedule Configuration --}}
            <div class="form-step" data-step="2">
                <div class="step-header">
                    <h3>Schedule Configuration</h3>
                    <p>Set the date, time, and duration for this session</p>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="session_date" class="required-label">
                            <i class="fas fa-calendar"></i> Session Date
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="session_date" 
                               name="session_date" 
                               min="{{ date('Y-m-d') }}" 
                               max="{{ date('Y-m-d', strtotime('+6 months')) }}"
                               required 
                               data-validation="required|date|future">
                        <div class="field-validation-error" id="session_date_error"></div>
                        <div id="dateConflictWarning" class="conflict-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Potential scheduling conflicts detected</span>
                        </div>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Sessions can be scheduled up to 6 months in advance
                        </small>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="start_time" class="required-label">
                            <i class="fas fa-clock"></i> Start Time
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="time" 
                               class="form-control" 
                               id="start_time" 
                               name="start_time" 
                               required 
                               data-validation="required|time">
                        <div class="field-validation-error" id="start_time_error"></div>
                        <div id="timeConflictWarning" class="conflict-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Time slot conflict with existing session</span>
                        </div>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="duration" class="required-label">
                            <i class="fas fa-hourglass-half"></i> Duration
                            <span class="required-asterisk">*</span>
                        </label>
                        <select class="form-control" id="duration" name="duration" required data-validation="required">
                            <option value="">Select duration...</option>
                            <option value="15">15 minutes</option>
                            <option value="20">20 minutes</option>
                            <option value="30" selected>30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="90">1.5 hours</option>
                            <option value="120">2 hours</option>
                        </select>
                        <div class="field-validation-error" id="duration_error"></div>
                    </div>
                </div>

                <div class="schedule-preview" id="schedulePreview" style="display: none;">
                    <div class="preview-header">
                        <i class="fas fa-eye"></i>
                        <span>Schedule Preview</span>
                    </div>
                    <div class="preview-content">
                        <div class="preview-item">
                            <strong>Session:</strong> <span id="previewDateTime"></span>
                        </div>
                        <div class="preview-item">
                            <strong>Duration:</strong> <span id="previewDuration"></span>
                        </div>
                        <div class="preview-item">
                            <strong>End Time:</strong> <span id="previewEndTime"></span>
                        </div>
                    </div>
                </div>

                {{-- Centre Operating Hours Check --}}
                <div class="form-group">
                    <div class="operating-hours-check" id="operatingHoursCheck">
                        <div class="check-item">
                            <i class="fas fa-building"></i>
                            <span>Centre Operating Hours: </span>
                            <span class="hours-display">{{ $activity->centre->operating_hours ?? '8:00 AM - 6:00 PM' }}</span>
                        </div>
                        <div id="operatingHoursWarning" class="warning-message" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Session scheduled outside normal operating hours
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Location & Requirements --}}
            <div class="form-step" data-step="3">
                <div class="step-header">
                    <h3>Location & Requirements</h3>
                    <p>Specify where the session will take place and any special requirements</p>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="centre_id" class="required-label">
                            <i class="fas fa-building"></i> Centre
                            <span class="required-asterisk">*</span>
                        </label>
                        <select class="form-control" id="centre_id" name="centre_id" required data-validation="required">
                            <option value="">Select centre...</option>
                            @foreach($centres as $centre)
                                <option value="{{ $centre->id }}" 
                                        {{ $activity->centre_id == $centre->id ? 'selected' : '' }}
                                        data-location="{{ $centre->location }}"
                                        data-facilities="{{ $centre->facilities ?? '' }}">
                                    {{ $centre->centre_name }} - {{ $centre->location }}
                                </option>
                            @endforeach
                        </select>
                        <div class="field-validation-error" id="centre_id_error"></div>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Default: {{ $activity->centre->centre_name ?? 'Activity centre' }}
                        </small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="location" class="required-label">
                            <i class="fas fa-map-marker-alt"></i> Specific Location
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="location" 
                               name="location" 
                               placeholder="e.g., Therapy Room A, Main Hall, Room 101" 
                               required 
                               data-validation="required|min:3">
                        <div class="field-validation-error" id="location_error"></div>
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Be specific to help participants find the session
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="room_number">Room/Hall Number</label>
                        <input type="text" 
                               class="form-control" 
                               id="room_number" 
                               name="room_number" 
                               placeholder="e.g., A101, B205, TH-1">
                        <small class="field-help">
                            <i class="fas fa-info-circle"></i>
                            Optional: Formal room designation
                        </small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="accessibility_requirements">Accessibility Requirements</label>
                        <select class="form-control" id="accessibility_requirements" name="accessibility_requirements">
                            <option value="">No special requirements</option>
                            <option value="wheelchair_accessible">Wheelchair Accessible</option>
                            <option value="elevator_access">Elevator Access Required</option>
                            <option value="ground_floor_only">Ground Floor Only</option>
                            <option value="hearing_loop">Hearing Loop Available</option>
                            <option value="visual_aids">Visual Aid Support</option>
                        </select>
                    </div>
                </div>

                {{-- Equipment & Materials --}}
                <div class="form-group">
                    <label for="required_equipment">Required Equipment & Materials</label>
                    <div class="equipment-selector">
                        <div class="equipment-grid">
                            <div class="equipment-item">
                                <input type="checkbox" id="eq_projector" name="equipment[]" value="projector">
                                <label for="eq_projector">
                                    <i class="fas fa-video"></i>
                                    Projector
                                </label>
                            </div>
                            <div class="equipment-item">
                                <input type="checkbox" id="eq_whiteboard" name="equipment[]" value="whiteboard">
                                <label for="eq_whiteboard">
                                    <i class="fas fa-chalkboard"></i>
                                    Whiteboard
                                </label>
                            </div>
                            <div class="equipment-item">
                                <input type="checkbox" id="eq_computer" name="equipment[]" value="computer">
                                <label for="eq_computer">
                                    <i class="fas fa-desktop"></i>
                                    Computer
                                </label>
                            </div>
                            <div class="equipment-item">
                                <input type="checkbox" id="eq_materials" name="equipment[]" value="therapy_materials">
                                <label for="eq_materials">
                                    <i class="fas fa-tools"></i>
                                    Therapy Materials
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_notes">Special Instructions & Notes</label>
                    <textarea class="form-control" 
                              id="special_notes" 
                              name="special_notes" 
                              rows="4"
                              placeholder="Add any special instructions, preparation requirements, or notes for this session..."></textarea>
                    <small class="field-help">
                        <i class="fas fa-info-circle"></i>
                        Include setup requirements, special materials, or participant preparation notes
                    </small>
                </div>
            </div>

            {{-- Step 4: Review & Confirmation --}}
            <div class="form-step" data-step="4">
                <div class="step-header">
                    <h3>Review & Confirm</h3>
                    <p>Please review all session details before scheduling</p>
                </div>

                <div class="review-container">
                    <div class="review-section">
                        <h4><i class="fas fa-info-circle"></i> Session Overview</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <label>Activity:</label>
                                <span>{{ $activity->activity_name }}</span>
                            </div>
                            <div class="review-item">
                                <label>Teacher:</label>
                                <span id="reviewTeacher">-</span>
                            </div>
                            <div class="review-item">
                                <label>Date & Time:</label>
                                <span id="reviewDateTime">-</span>
                            </div>
                            <div class="review-item">
                                <label>Duration:</label>
                                <span id="reviewDuration">-</span>
                            </div>
                            <div class="review-item">
                                <label>Location:</label>
                                <span id="reviewLocation">-</span>
                            </div>
                            <div class="review-item">
                                <label>Capacity:</label>
                                <span id="reviewCapacity">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-exclamation-triangle"></i> Validation Status</h4>
                        <div id="finalValidation" class="validation-checklist">
                            <!-- Validation items will be populated by JavaScript -->
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-clock"></i> Schedule Conflicts</h4>
                        <div id="conflictCheck" class="conflict-check">
                            <div class="checking-message">
                                <i class="fas fa-spinner fa-spin"></i>
                                Checking for scheduling conflicts...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="form-navigation">
                <button type="button" id="prevBtn" class="btn btn-outline-secondary" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                
                <div class="nav-spacer"></div>
                
                <button type="button" id="nextBtn" class="btn btn-primary">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                    <i class="fas fa-calendar-plus"></i> Schedule Session
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/form-validation-enhanced.css') }}">
<link rel="stylesheet" href="{{ asset('css/session-enhanced.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/form-validation-enhanced.js') }}"></script>
<script src="{{ asset('js/session-enhanced.js') }}"></script>
<script>
// Initialize enhanced session form
document.addEventListener('DOMContentLoaded', function() {
    const sessionForm = new EnhancedSessionForm({
        activityId: {{ $activity->id }},
        activityName: '{{ $activity->activity_name }}',
        centreId: {{ $activity->centre_id ?? 'null' }},
        existingSessions: @json($existingSessions ?? []),
        teacherSchedules: @json($teacherSchedules ?? [])
    });
});
</script>
@endsection