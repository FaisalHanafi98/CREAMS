@extends('layouts.app')

@section('title', 'Create New Activity - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #c850c0;
        --success-color: #2ed573;
        --danger-color: #ff4757;
        --warning-color: #ffa502;
        --info-color: #1e90ff;
        --dark-color: #1a2a3a;
        --light-color: #f8f9fa;
        --border-color: #e9ecef;
        --transition-speed: 0.3s;
    }

    .activity-creation-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .creation-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(50, 189, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .creation-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(50px, -50px);
    }

    .creation-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .creation-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .tabs-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .nav-tabs {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        margin: 0;
        padding: 0;
    }

    .nav-tabs .nav-link {
        background: transparent;
        border: none;
        color: rgba(255,255,255,0.7);
        padding: 1.5rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all var(--transition-speed) ease;
        position: relative;
        border-radius: 0;
    }

    .nav-tabs .nav-link:hover {
        color: white;
        background: rgba(255,255,255,0.1);
    }

    .nav-tabs .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.2);
        box-shadow: inset 0 -3px 0 white;
    }

    .tab-content {
        padding: 2.5rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        color: var(--dark-color);
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-color);
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background: var(--secondary-color);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }

    .form-label .required {
        color: var(--danger-color);
        margin-left: 0.25rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid var(--border-color);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all var(--transition-speed) ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
    }

    .auto-generated {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-style: dashed;
        position: relative;
    }

    .auto-generated::after {
        content: 'Auto-generated';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        color: #6c757d;
        background: white;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
    }

    .participant-selection {
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 1.5rem;
        background: #f8f9fa;
    }

    .participant-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
    }

    .participant-item:hover {
        background: #f0f8ff;
        border-color: var(--primary-color);
    }

    .participant-item.selected {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .conflict-warning {
        background: linear-gradient(45deg, #fff3cd, #ffeaa7);
        border: 2px solid var(--warning-color);
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        display: none;
    }

    .conflict-warning.show {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        padding: 2rem;
        background: #f8f9fa;
        border-top: 1px solid var(--border-color);
    }

    .btn-modern {
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all var(--transition-speed) ease;
        border: none;
    }

    .btn-primary-modern {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-primary-modern:hover {
        background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(50, 189, 234, 0.4);
    }

    .btn-secondary-modern {
        background: linear-gradient(45deg, #6c757d, #495057);
        color: white;
    }

    .btn-secondary-modern:hover {
        background: linear-gradient(45deg, #495057, #343a40);
        transform: translateY(-2px);
    }

    .alert-modern {
        border-radius: 10px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .alert-danger-modern {
        background: linear-gradient(45deg, #fff5f5, #fed7d7);
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
    }

    .invalid-feedback {
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .creation-header h1 {
            font-size: 2rem;
        }
        
        .nav-tabs .nav-link {
            padding: 1rem;
            font-size: 1rem;
        }
        
        .tab-content {
            padding: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="activity-creation-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="creation-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-plus-circle me-3"></i>Create New Activity</h1>
                    <p>Design and configure a new rehabilitation activity for your centre</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('activities.home') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Back to Activities
                    </a>
                </div>
            </div>
        </div>

        <!-- Admin Access Check -->
        @if(session('role') !== 'admin')
            <div class="alert alert-danger-modern">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Access Restricted</h5>
                <p class="mb-0">Only administrators can create new activities. Please contact your system administrator.</p>
            </div>
            <div class="text-center">
                <a href="{{ route('activities.home') }}" class="btn btn-secondary-modern">
                    <i class="fas fa-arrow-left me-2"></i>Return to Activities
                </a>
            </div>
        @else

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-danger-modern">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Please Fix the Following Issues:</h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabbed Form -->
        <div class="tabs-container">
            <ul class="nav nav-tabs" id="activityTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Basic Information
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab">
                        <i class="fas fa-map-marker-alt me-2"></i>Location & Centre
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="instructor-tab" data-bs-toggle="tab" data-bs-target="#instructor" type="button" role="tab">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Instructor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Participants
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                        <i class="fas fa-calendar-alt me-2"></i>Schedule
                    </button>
                </li>
            </ul>

            <form action="{{ route('activities.store') }}" method="POST" id="activityForm">
                @csrf
                
                <div class="tab-content" id="activityTabContent">
                    <!-- Basic Information Tab -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="form-section">
                            <h3 class="section-title">Activity Details</h3>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="activity_name" class="form-label">
                                            Activity Name<span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('activity_name') is-invalid @enderror" 
                                               id="activity_name" name="activity_name" value="{{ old('activity_name') }}" 
                                               placeholder="e.g., Basic Motor Skills Development" required>
                                        @error('activity_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="activity_id" class="form-label">
                                            Activity ID<span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control auto-generated @error('activity_id') is-invalid @enderror" 
                                               id="activity_id" name="activity_id" value="{{ old('activity_id') }}" 
                                               placeholder="SC0123" readonly>
                                        @error('activity_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category" class="form-label">
                                            Category<span class="required">*</span>
                                        </label>
                                        <select class="form-select @error('category') is-invalid @enderror" 
                                                id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Science" {{ old('category') == 'Science' ? 'selected' : '' }}>Science (SC)</option>
                                            <option value="Physical" {{ old('category') == 'Physical' ? 'selected' : '' }}>Physical Therapy (PT)</option>
                                            <option value="Occupational" {{ old('category') == 'Occupational' ? 'selected' : '' }}>Occupational Therapy (OT)</option>
                                            <option value="Speech" {{ old('category') == 'Speech' ? 'selected' : '' }}>Speech Therapy (ST)</option>
                                            <option value="Cognitive" {{ old('category') == 'Cognitive' ? 'selected' : '' }}>Cognitive Training (CT)</option>
                                            <option value="Social" {{ old('category') == 'Social' ? 'selected' : '' }}>Social Skills (SS)</option>
                                            <option value="Arts" {{ old('category') == 'Arts' ? 'selected' : '' }}>Arts & Crafts (AC)</option>
                                            <option value="Music" {{ old('category') == 'Music' ? 'selected' : '' }}>Music Therapy (MT)</option>
                                        </select>
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="difficulty_level" class="form-label">
                                            Difficulty Level<span class="required">*</span>
                                        </label>
                                        <select class="form-select @error('difficulty_level') is-invalid @enderror" 
                                                id="difficulty_level" name="difficulty_level" required>
                                            <option value="">Select Difficulty</option>
                                            <option value="Beginner" {{ old('difficulty_level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="Intermediate" {{ old('difficulty_level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="Advanced" {{ old('difficulty_level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                        </select>
                                        @error('difficulty_level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">
                                    Activity Description<span class="required">*</span>
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Provide a detailed description of the activity, its objectives, and expected outcomes..." 
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location & Centre Tab -->
                    <div class="tab-pane fade" id="location" role="tabpanel">
                        <div class="form-section">
                            <h3 class="section-title">Location & Centre Assignment</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_id" class="form-label">
                                            Centre<span class="required">*</span>
                                        </label>
                                        <select class="form-select @error('centre_id') is-invalid @enderror" 
                                                id="centre_id" name="centre_id" required>
                                            <option value="">Select Centre</option>
                                            @foreach($centres ?? [] as $centre)
                                                <option value="{{ $centre->centre_id }}" {{ old('centre_id') == $centre->centre_id ? 'selected' : '' }}>
                                                    {{ $centre->centre_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('centre_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            The centre determines which staff and trainees are available for this activity.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location" class="form-label">
                                            Specific Location<span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                               id="location" name="location" value="{{ old('location') }}" 
                                               placeholder="e.g., Therapy Room A, Main Hall, Outdoor Garden" required>
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructor Tab -->
                    <div class="tab-pane fade" id="instructor" role="tabpanel">
                        <div class="form-section">
                            <h3 class="section-title">Activity Instructor</h3>
                            
                            <div class="form-group">
                                <label for="instructor_id" class="form-label">
                                    Select Instructor<span class="required">*</span>
                                </label>
                                <select class="form-select @error('instructor_id') is-invalid @enderror" 
                                        id="instructor_id" name="instructor_id" required>
                                    <option value="">First select a centre to see available instructors</option>
                                </select>
                                @error('instructor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Only staff members from the selected centre are shown.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Participants Tab -->
                    <div class="tab-pane fade" id="participants" role="tabpanel">
                        <div class="form-section">
                            <h3 class="section-title">Activity Participants</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_participants" class="form-label">
                                            Maximum Participants<span class="required">*</span>
                                        </label>
                                        <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                                               id="max_participants" name="max_participants" value="{{ old('max_participants', 10) }}" 
                                               min="1" max="50" required>
                                        @error('max_participants')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="min_participants" class="form-label">
                                            Minimum Participants<span class="required">*</span>
                                        </label>
                                        <input type="number" class="form-control @error('min_participants') is-invalid @enderror" 
                                               id="min_participants" name="min_participants" value="{{ old('min_participants', 1) }}" 
                                               min="1" required>
                                        @error('min_participants')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="participant-selection">
                                <h5><i class="fas fa-users me-2"></i>Select Participants</h5>
                                <p class="text-muted">Choose trainees from the selected centre to participate in this activity.</p>
                                <div id="participantsList">
                                    <p class="text-center text-muted">Please select a centre first to see available trainees.</p>
                                </div>
                                <input type="hidden" name="participants" id="selectedParticipants">
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Tab -->
                    <div class="tab-pane fade" id="schedule" role="tabpanel">
                        <div class="form-section">
                            <h3 class="section-title">Activity Schedule</h3>
                            
                            <div class="conflict-warning" id="conflictWarning">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Scheduling Conflicts Detected!</h5>
                                <div id="conflictDetails"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sessions_per_week" class="form-label">
                                            Sessions Per Week<span class="required">*</span>
                                        </label>
                                        <select class="form-select @error('sessions_per_week') is-invalid @enderror" 
                                                id="sessions_per_week" name="sessions_per_week" required>
                                            <option value="">Select Frequency</option>
                                            <option value="1" {{ old('sessions_per_week') == '1' ? 'selected' : '' }}>Once a week</option>
                                            <option value="2" {{ old('sessions_per_week') == '2' ? 'selected' : '' }}>Twice a week</option>
                                            <option value="3" {{ old('sessions_per_week') == '3' ? 'selected' : '' }}>Three times a week</option>
                                            <option value="4" {{ old('sessions_per_week') == '4' ? 'selected' : '' }}>Four times a week</option>
                                            <option value="5" {{ old('sessions_per_week') == '5' ? 'selected' : '' }}>Five times a week</option>
                                        </select>
                                        @error('sessions_per_week')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration_hours" class="form-label">
                                            Duration Per Session (Hours)<span class="required">*</span>
                                        </label>
                                        <select class="form-select @error('duration_hours') is-invalid @enderror" 
                                                id="duration_hours" name="duration_hours" required>
                                            <option value="">Select Duration</option>
                                            <option value="0.5" {{ old('duration_hours') == '0.5' ? 'selected' : '' }}>30 minutes</option>
                                            <option value="1" {{ old('duration_hours') == '1' ? 'selected' : '' }}>1 hour</option>
                                            <option value="1.5" {{ old('duration_hours') == '1.5' ? 'selected' : '' }}>1.5 hours</option>
                                            <option value="2" {{ old('duration_hours') == '2' ? 'selected' : '' }}>2 hours</option>
                                            <option value="2.5" {{ old('duration_hours') == '2.5' ? 'selected' : '' }}>2.5 hours</option>
                                            <option value="3" {{ old('duration_hours') == '3' ? 'selected' : '' }}>3 hours</option>
                                        </select>
                                        @error('duration_hours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date" class="form-label">
                                            Start Date<span class="required">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" name="start_date" value="{{ old('start_date') }}" 
                                               min="{{ date('Y-m-d') }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_time" class="form-label">
                                            Start Time<span class="required">*</span>
                                        </label>
                                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                               id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                        @error('start_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="schedule_days" class="form-label">
                                    Days of Week<span class="required">*</span>
                                </label>
                                <div class="row">
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="day_{{ strtolower($day) }}" name="schedule_days[]" 
                                                   value="{{ $day }}" {{ in_array($day, old('schedule_days', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                                {{ $day }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @error('schedule_days')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="navigation-buttons">
                    <button type="button" class="btn btn-secondary-modern" id="prevBtn" onclick="changeTab(-1)" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>Previous
                    </button>
                    <div>
                        <button type="button" class="btn btn-primary-modern" id="nextBtn" onclick="changeTab(1)">
                            Next<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn btn-primary-modern" id="submitBtn" style="display: none;">
                            <i class="fas fa-check me-2"></i>Create Activity
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentTab = 0;
    const tabs = ['basic', 'location', 'instructor', 'participants', 'schedule'];
    const selectedParticipants = new Set();

    // Initialize category-based ID generation
    const categorySelect = document.getElementById('category');
    const activityIdInput = document.getElementById('activity_id');
    
    categorySelect.addEventListener('change', function() {
        generateActivityId();
    });

    function generateActivityId() {
        const category = categorySelect.value;
        if (!category) return;
        
        const categoryMap = {
            'Science': 'SC',
            'Physical': 'PT',
            'Occupational': 'OT',
            'Speech': 'ST',
            'Cognitive': 'CT',
            'Social': 'SS',
            'Arts': 'AC',
            'Music': 'MT'
        };
        
        const prefix = categoryMap[category] || 'GN';
        const number = String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
        activityIdInput.value = `${prefix}${number}`;
    }

    // Centre-based instructor loading
    const centreSelect = document.getElementById('centre_id');
    const instructorSelect = document.getElementById('instructor_id');
    
    centreSelect.addEventListener('change', function() {
        loadInstructors();
        loadParticipants();
    });

    function loadInstructors() {
        const centreId = centreSelect.value;
        if (!centreId) {
            instructorSelect.innerHTML = '<option value="">First select a centre</option>';
            return;
        }

        // Simulate API call - replace with actual endpoint
        fetch(`/api/centres/${centreId}/instructors`)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Select Instructor</option>';
                data.forEach(instructor => {
                    options += `<option value="${instructor.id}">${instructor.name} - ${instructor.role}</option>`;
                });
                instructorSelect.innerHTML = options;
            })
            .catch(error => {
                console.error('Error loading instructors:', error);
                instructorSelect.innerHTML = '<option value="">Error loading instructors</option>';
            });
    }

    function loadParticipants() {
        const centreId = centreSelect.value;
        const participantsList = document.getElementById('participantsList');
        
        if (!centreId) {
            participantsList.innerHTML = '<p class="text-center text-muted">Please select a centre first.</p>';
            return;
        }

        // Simulate API call - replace with actual endpoint
        fetch(`/api/centres/${centreId}/trainees`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                data.forEach(trainee => {
                    html += `
                        <div class="participant-item" data-id="${trainee.id}" onclick="toggleParticipant(${trainee.id}, '${trainee.name}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${trainee.name}</strong>
                                    <br><small class="text-muted">${trainee.condition || 'No condition specified'}</small>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="participant_${trainee.id}">
                                </div>
                            </div>
                        </div>
                    `;
                });
                participantsList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading participants:', error);
                participantsList.innerHTML = '<p class="text-center text-danger">Error loading participants</p>';
            });
    }

    // Participant selection
    window.toggleParticipant = function(id, name) {
        const checkbox = document.getElementById(`participant_${id}`);
        const item = checkbox.closest('.participant-item');
        
        if (selectedParticipants.has(id)) {
            selectedParticipants.delete(id);
            checkbox.checked = false;
            item.classList.remove('selected');
        } else {
            const maxParticipants = parseInt(document.getElementById('max_participants').value) || 10;
            if (selectedParticipants.size >= maxParticipants) {
                alert(`Maximum ${maxParticipants} participants allowed.`);
                return;
            }
            
            selectedParticipants.add(id);
            checkbox.checked = true;
            item.classList.add('selected');
        }
        
        updateSelectedParticipants();
        checkScheduleConflicts();
    };

    function updateSelectedParticipants() {
        document.getElementById('selectedParticipants').value = Array.from(selectedParticipants).join(',');
    }

    // Schedule conflict checking
    function checkScheduleConflicts() {
        const days = Array.from(document.querySelectorAll('input[name="schedule_days[]"]:checked')).map(cb => cb.value);
        const startTime = document.getElementById('start_time').value;
        const duration = document.getElementById('duration_hours').value;
        
        if (selectedParticipants.size === 0 || days.length === 0 || !startTime || !duration) return;
        
        // Simulate conflict checking - replace with actual API call
        const hasConflicts = Math.random() > 0.7; // 30% chance of conflicts for demo
        
        if (hasConflicts) {
            showConflictWarning();
        } else {
            hideConflictWarning();
        }
    }

    function showConflictWarning() {
        const warning = document.getElementById('conflictWarning');
        document.getElementById('conflictDetails').innerHTML = `
            <ul>
                <li>Participant "John Doe" has another activity on Monday at 10:00 AM</li>
                <li>Instructor "Dr. Smith" is scheduled for another session on Wednesday at 2:00 PM</li>
            </ul>
        `;
        warning.classList.add('show');
    }

    function hideConflictWarning() {
        document.getElementById('conflictWarning').classList.remove('show');
    }

    // Tab navigation
    window.changeTab = function(direction) {
        if (direction === 1 && !validateCurrentTab()) return;
        
        currentTab += direction;
        showTab();
    };

    function showTab() {
        // Hide all tabs
        document.querySelectorAll('.tab-pane').forEach(tab => {
            tab.classList.remove('show', 'active');
        });
        
        // Show current tab
        document.getElementById(tabs[currentTab]).classList.add('show', 'active');
        
        // Update nav tabs
        document.querySelectorAll('.nav-link').forEach((link, index) => {
            link.classList.toggle('active', index === currentTab);
        });
        
        // Update buttons
        document.getElementById('prevBtn').style.display = currentTab === 0 ? 'none' : 'inline-block';
        document.getElementById('nextBtn').style.display = currentTab === tabs.length - 1 ? 'none' : 'inline-block';
        document.getElementById('submitBtn').style.display = currentTab === tabs.length - 1 ? 'inline-block' : 'none';
    }

    function validateCurrentTab() {
        const currentTabElement = document.getElementById(tabs[currentTab]);
        const requiredFields = currentTabElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value || (field.type === 'checkbox' && field.name === 'schedule_days[]' && 
                !currentTabElement.querySelector('input[name="schedule_days[]"]:checked'))) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }

    // Add event listeners for schedule conflict checking
    ['schedule_days[]', 'start_time', 'duration_hours'].forEach(name => {
        document.querySelectorAll(`[name="${name}"]`).forEach(input => {
            input.addEventListener('change', checkScheduleConflicts);
        });
    });

    // Form submission
    document.getElementById('activityForm').addEventListener('submit', function(e) {
        if (!validateCurrentTab()) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush