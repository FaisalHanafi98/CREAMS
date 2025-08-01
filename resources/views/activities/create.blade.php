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

    .participant-item.suitable-participant {
        border-left: 4px solid var(--success-color);
        background: linear-gradient(45deg, #f8fff8, #e6ffe6);
    }

    .participant-item.suitable-participant:hover {
        background: #e8f5e8;
        border-color: var(--success-color);
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
                    <button class="nav-link active" id="basic-tab" type="button" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Basic Information
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="location-tab" type="button" role="tab">
                        <i class="fas fa-map-marker-alt me-2"></i>Location & Centre
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="instructor-tab" type="button" role="tab">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Instructor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="participants-tab" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Participants
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedule-tab" type="button" role="tab">
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
                                        <input type="text" class="form-control @error('activity_id') is-invalid @enderror" 
                                               id="activity_id" name="activity_id" value="{{ old('activity_id') }}" 
                                               placeholder="SC0123" maxlength="6">
                                        <small class="form-text text-muted">
                                            Format: 2 letters (auto-generated from category) + 4 digits (customizable). Example: SC1234
                                        </small>
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
                                <p class="text-muted">
                                    Choose trainees from the selected centre to participate in this activity.
                                    <br><small class="text-info">
                                        <i class="fas fa-info-circle"></i> 
                                        When you select an activity category, trainees are filtered to show those whose conditions are most suitable for that type of activity.
                                    </small>
                                </p>
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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="activity_period" class="form-label">
                                            Activity Period<span class="required">*</span>
                                        </label>
                                        <select class="form-select @error('activity_period') is-invalid @enderror" 
                                                id="activity_period" name="activity_period" required>
                                            <option value="">Select Period</option>
                                            <option value="1" {{ old('activity_period') == '1' ? 'selected' : '' }}>1 Month</option>
                                            <option value="2" {{ old('activity_period') == '2' ? 'selected' : '' }}>2 Months</option>
                                            <option value="3" {{ old('activity_period') == '3' ? 'selected' : '' }}>3 Months</option>
                                            <option value="6" {{ old('activity_period') == '6' ? 'selected' : '' }}>6 Months</option>
                                            <option value="9" {{ old('activity_period') == '9' ? 'selected' : '' }}>9 Months</option>
                                            <option value="12" {{ old('activity_period') == '12' ? 'selected' : '' }}>12 Months (1 Year)</option>
                                            <option value="18" {{ old('activity_period') == '18' ? 'selected' : '' }}>18 Months</option>
                                            <option value="24" {{ old('activity_period') == '24' ? 'selected' : '' }}>24 Months (2 Years)</option>
                                        </select>
                                        @error('activity_period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Duration helps calculate progress tracking milestones and completion targets.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date" class="form-label">
                                            Estimated End Date
                                        </label>
                                        <input type="date" class="form-control" 
                                               id="end_date" name="end_date" readonly>
                                        <small class="form-text text-muted">
                                            Automatically calculated based on start date and selected period.
                                        </small>
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
                    <button type="button" class="btn btn-secondary-modern" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>Previous
                    </button>
                    <div>
                        <button type="button" class="btn btn-primary-modern" id="nextBtn">
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Activity creation form initializing');
    
    let currentTab = 0;
    const tabs = ['basic', 'location', 'instructor', 'participants', 'schedule'];
    const selectedParticipants = new Set();
    
    console.log('Initial state - currentTab:', currentTab, 'tabs array:', tabs);
    
    // Helper function to get proper role titles
    function getRoleTitle(role) {
        const roleTitles = {
            'admin': 'Administrator',
            'supervisor': 'Supervisor', 
            'teacher': 'Teacher',
            'ajk': 'AJK'
        };
        return roleTitles[role] || 'Staff';
    }

    // Initialize category-based ID generation
    const categorySelect = document.getElementById('category');
    const activityIdInput = document.getElementById('activity_id');
    
    categorySelect.addEventListener('change', function() {
        generateActivityId();
        // Reload participants with disability-appropriate filtering
        if (centreSelect && centreSelect.value) {
            loadParticipants();
        }
    });

    function generateActivityId() {
        const category = categorySelect.value;
        if (!category) {
            activityIdInput.value = '';
            return;
        }
        
        const categoryMap = {
            'Physical Therapy': 'PT',
            'Occupational Therapy': 'OT',
            'Speech Therapy': 'ST',
            'Behavioral Therapy': 'BT',
            'Sensory Integration': 'SI',
            'Mathematics': 'MA',
            'Literacy': 'LT',
            'Science': 'SC',
            'Computer Skills': 'CS',
            'Art & Creativity': 'AC',
            'Music Therapy': 'MT',
            'Social Skills': 'SS',
            'Life Skills': 'LS',
            'Vocational Training': 'VT'
        };
        
        const prefix = categoryMap[category] || 'GN';
        const currentValue = activityIdInput.value;
        
        // If there's already a value, preserve the last 4 digits if they exist
        let customNumber = '';
        if (currentValue.length >= 3) {
            // Extract existing custom number (last 4 digits)
            customNumber = currentValue.slice(2);
        }
        
        // Set the new prefix + preserved/empty custom number
        activityIdInput.value = prefix + customNumber;
        
        // Focus on the number part for easy editing
        activityIdInput.focus();
        activityIdInput.setSelectionRange(2, activityIdInput.value.length);
    }
    
    // Add input validation for activity ID format
    activityIdInput.addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase();
        
        // If user tries to modify the first 2 characters, restore the prefix
        const category = categorySelect.value;
        if (category && value.length >= 2) {
            const categoryMap = {
                'Physical Therapy': 'PT',
                'Occupational Therapy': 'OT',
                'Speech Therapy': 'ST',
                'Behavioral Therapy': 'BT',
                'Sensory Integration': 'SI',
                'Mathematics': 'MA',
                'Literacy': 'LT',
                'Science': 'SC',
                'Computer Skills': 'CS',
                'Art & Creativity': 'AC',
                'Music Therapy': 'MT',
                'Social Skills': 'SS',
                'Life Skills': 'LS',
                'Vocational Training': 'VT'
            };
            
            const correctPrefix = categoryMap[category] || 'GN';
            const currentPrefix = value.slice(0, 2);
            
            if (currentPrefix !== correctPrefix) {
                // Restore correct prefix
                const numberPart = value.slice(2).replace(/[^0-9]/g, ''); // Only allow numbers
                e.target.value = correctPrefix + numberPart;
                return;
            }
        }
        
        // Ensure only numbers after the prefix
        if (value.length > 2) {
            const prefix = value.slice(0, 2);
            const numberPart = value.slice(2).replace(/[^0-9]/g, ''); // Only allow numbers
            e.target.value = prefix + numberPart.slice(0, 4); // Max 4 digits
        }
    });

    // Centre-based instructor loading
    const centreSelect = document.getElementById('centre_id');
    const instructorSelect = document.getElementById('instructor_id');
    
    console.log('Form elements found - centreSelect:', !!centreSelect, 'instructorSelect:', !!instructorSelect);
    
    // Debug: Check if centres are loaded in the dropdown
    if (centreSelect) {
        console.log('Centre options available:', centreSelect.options.length);
        for (let i = 0; i < centreSelect.options.length; i++) {
            console.log('Centre option:', i, centreSelect.options[i].value, centreSelect.options[i].text);
        }
        
        centreSelect.addEventListener('change', function() {
            console.log('Centre dropdown changed to:', this.value, 'Text:', this.options[this.selectedIndex].text);
            try {
                loadInstructors();
                loadParticipants();
            } catch (error) {
                console.error('Error in centre change handler:', error);
            }
        });
    } else {
        console.error('Centre select element not found!');
    }

    function loadInstructors() {
        const centreId = centreSelect.value;
        console.log('loadInstructors called with centreId:', centreId);
        
        if (!centreId) {
            console.log('No centre selected, showing default option');
            instructorSelect.innerHTML = '<option value="">First select a centre</option>';
            return;
        }

        console.log('Fetching instructors from:', `/api/centres/${centreId}/instructors`);
        
        // Use existing route for getting centre instructors
        fetch(`/api/centres/${centreId}/instructors`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                console.log('Instructor API response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Instructor data received:', data);
                let options = '<option value="">Select Instructor</option>';
                if (data && Array.isArray(data)) {
                    console.log('Processing', data.length, 'instructors');
                    data.forEach(instructor => {
                        const roleTitle = getRoleTitle(instructor.role);
                        options += `<option value="${instructor.id}">${instructor.name} - ${roleTitle}</option>`;
                    });
                } else {
                    console.warn('Instructor data is not an array:', typeof data);
                }
                instructorSelect.innerHTML = options;
                console.log('Instructor dropdown updated with', instructorSelect.options.length, 'options');
            })
            .catch(error => {
                console.error('Error loading instructors:', error);
                instructorSelect.innerHTML = '<option value="">Error loading instructors</option>';
                return Promise.resolve();
            });
    }

    function loadParticipants() {
        const centreId = centreSelect.value;
        const categoryId = categorySelect ? categorySelect.value : null;
        const participantsList = document.getElementById('participantsList');
        
        console.log('loadParticipants called with centreId:', centreId, 'categoryId:', categoryId);
        console.log('participantsList element:', participantsList);
        
        if (!centreId) {
            participantsList.innerHTML = '<p class="text-center text-muted">Please select a centre first.</p>';
            return;
        }
        
        // Use filtered API if category is selected for better matching
        const apiUrl = categoryId ? 
            `/api/centres/${centreId}/trainees/filtered/${categoryId}` : 
            `/api/centres/${centreId}/trainees`;
            
        console.log('Fetching trainees from:', apiUrl);

        // Use filtered route for disability-appropriate matching
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                console.log('Trainees API response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Trainees data received:', data);
                let html = '';
                if (data && Array.isArray(data)) {
                    console.log('Processing', data.length, 'trainees');
                    data.forEach(trainee => {
                        const suitabilityIndicator = categoryId ? 
                            '<small class="text-success"><i class="fas fa-check-circle"></i> Well-suited for this activity</small>' : 
                            '';
                        
                        // Properly escape name for JavaScript
                        const escapedName = trainee.name.replace(/'/g, "\\'").replace(/"/g, '\\"');
                        
                        html += `
                            <div class="participant-item ${categoryId ? 'suitable-participant' : ''}" data-id="${trainee.id}" data-name="${escapedName}" onclick="toggleParticipant(${trainee.id}, '${escapedName}')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${trainee.name}</strong>
                                        <br><small class="text-muted">${trainee.condition || 'No condition specified'}</small>
                                        ${suitabilityIndicator}
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="participant_${trainee.id}">
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    console.warn('Trainees data is not an array:', typeof data);
                }
                if (html === '') {
                    if (categoryId) {
                        html = '<p class="text-center text-muted">No trainees with suitable conditions found for this activity category.<br><small>Try selecting "All Categories" to see all available trainees.</small></p>';
                    } else {
                        html = '<p class="text-center text-muted">No trainees found for this centre.</p>';
                    }
                }
                console.log('Setting participants HTML, length:', html.length);
                participantsList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading participants:', error);
                // Don't block the UI, just show a message
                participantsList.innerHTML = '<p class="text-center text-muted">Unable to load participants. You can still create the activity.</p>';
                // Don't let this error break other functionality
                return Promise.resolve();
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
        
        // Dispatch custom event to trigger conflict checking
        document.dispatchEvent(new CustomEvent('participantsChanged', { 
            detail: { participants: Array.from(selectedParticipants) } 
        }));
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
        const instructorId = document.getElementById('instructor_id').value;
        const location = document.getElementById('location').value;
        const startDate = document.getElementById('start_date').value;
        
        // Only check if we have the minimum required data
        if (days.length === 0 || !startTime || !duration || !instructorId || !startDate) {
            hideConflictWarning();
            return;
        }
        
        // Prepare participants array
        const participants = Array.from(selectedParticipants);
        
        // Make API call to check for real conflicts
        const requestData = {
            schedule_days: days,
            start_time: startTime,
            duration_hours: parseFloat(duration),
            instructor_id: instructorId,
            location: location,
            start_date: startDate,
            participants: participants,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        
        fetch('/api/activities/check-conflicts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': requestData._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.hasConflicts) {
                showConflictWarning(data.conflicts);
            } else {
                hideConflictWarning();
            }
        })
        .catch(error => {
            console.error('Error checking conflicts:', error);
            hideConflictWarning(); // Hide warning on error to prevent confusion
        });
    }

    function showConflictWarning(conflicts = []) {
        const warning = document.getElementById('conflictWarning');
        
        if (conflicts.length > 0) {
            let conflictHTML = '<ul>';
            conflicts.forEach(conflict => {
                conflictHTML += `<li><i class="fas fa-exclamation-triangle text-warning me-2"></i>${conflict.message}</li>`;
            });
            conflictHTML += '</ul>';
            
            // Group conflicts by type for better display
            const groupedConflicts = {};
            conflicts.forEach(conflict => {
                if (!groupedConflicts[conflict.type]) {
                    groupedConflicts[conflict.type] = [];
                }
                groupedConflicts[conflict.type].push(conflict);
            });
            
            let summaryHTML = '<div class="conflict-summary mb-2">';
            if (groupedConflicts.instructor) {
                summaryHTML += `<span class="badge badge-warning me-2">Instructor: ${groupedConflicts.instructor.length}</span>`;
            }
            if (groupedConflicts.location) {
                summaryHTML += `<span class="badge badge-danger me-2">Location: ${groupedConflicts.location.length}</span>`;
            }
            if (groupedConflicts.participant) {
                summaryHTML += `<span class="badge badge-info me-2">Participants: ${groupedConflicts.participant.length}</span>`;
            }
            summaryHTML += '</div>';
            
            document.getElementById('conflictDetails').innerHTML = summaryHTML + conflictHTML;
        } else {
            // Fallback for old dummy conflicts
            document.getElementById('conflictDetails').innerHTML = `
                <ul>
                    <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Schedule conflicts detected. Please review your selections.</li>
                </ul>
            `;
        }
        
        warning.classList.add('show');
    }

    function hideConflictWarning() {
        document.getElementById('conflictWarning').classList.remove('show');
    }

    // Tab navigation
    window.changeTab = function(direction) {
        console.log('changeTab called: direction=' + direction + ', current=' + currentTab + ' (' + tabs[currentTab] + ')');
        
        // Validate for visual feedback but don't block navigation
        try {
            validateCurrentTab();
            console.log('Validation completed successfully');
        } catch (error) {
            console.error('Error in validateCurrentTab:', error);
        }
        
        currentTab += direction;
        
        // Ensure currentTab stays within bounds
        if (currentTab < 0) currentTab = 0;
        if (currentTab >= tabs.length) currentTab = tabs.length - 1;
        
        console.log('Moving to tab:', currentTab, '(' + tabs[currentTab] + ')');
        
        try {
            showTab();
            console.log('showTab completed successfully');
        } catch (error) {
            console.error('Error in showTab:', error);
        }
    };

    function showTab() {
        console.log('showTab called - currentTab:', currentTab, 'tab name:', tabs[currentTab]);
        
        // Hide all tabs
        document.querySelectorAll('.tab-pane').forEach(tab => {
            tab.classList.remove('show', 'active');
        });
        
        // Show current tab
        const targetTab = document.getElementById(tabs[currentTab]);
        if (targetTab) {
            targetTab.classList.add('show', 'active');
            console.log('Successfully activated tab:', tabs[currentTab]);
        } else {
            console.error('Could not find tab element:', tabs[currentTab]);
        }
        
        // Update nav tabs
        document.querySelectorAll('.nav-link').forEach((link, index) => {
            link.classList.toggle('active', index === currentTab);
        });
        
        // Update buttons
        document.getElementById('prevBtn').style.display = currentTab === 0 ? 'none' : 'inline-block';
        document.getElementById('nextBtn').style.display = currentTab === tabs.length - 1 ? 'none' : 'inline-block';
        
        // Show submit button only on last tab and validate entire form
        const submitBtn = document.getElementById('submitBtn');
        if (currentTab === tabs.length - 1) {
            submitBtn.style.display = 'inline-block';
            validateEntireForm();
        } else {
            submitBtn.style.display = 'none';
        }
        
    }

    function validateCurrentTab() {
        console.log('validateCurrentTab called for tab:', currentTab, tabs[currentTab]);
        
        const currentTabElement = document.getElementById(tabs[currentTab]);
        let isValid = true;
        
        // Only validate required fields in the current tab, but don't block navigation
        // Just highlight fields that need attention
        if (currentTab === 0) { // Basic Information
            const requiredFields = ['activity_name', 'category', 'difficulty_level', 'description'];
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field && !field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
        } else if (currentTab === 1) { // Location & Centre
            const requiredFields = ['centre_id', 'location'];
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field && !field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
        } else if (currentTab === 4) { // Schedule - only validate on final submission
            const scheduleDaysChecked = currentTabElement.querySelector('input[name="schedule_days[]"]:checked');
            if (!scheduleDaysChecked) {
                isValid = false;
            }
        }
        
        // For navigation, always allow moving between tabs
        // Only validate fully on form submission
        return true; // Allow tab navigation regardless of validation
    }

    function validateEntireForm() {
        const submitBtn = document.getElementById('submitBtn');
        let isFormValid = true;
        
        // Required fields across all tabs (activity_id is auto-generated)
        const requiredFields = [
            'activity_name', 'category', 'difficulty_level', 'description',                 // Basic
            'centre_id', 'location',                                                        // Location
            'instructor_id',                                                                // Instructor
            'max_participants', 'min_participants',                                        // Participants
            'sessions_per_week', 'duration_hours', 'start_date', 'start_time', 'activity_period'  // Schedule
        ];
        
        // Check all required fields
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field) {
                isFormValid = false;
                console.log('Field element not found:', fieldName);
            } else {
                const fieldValue = field.value || '';
                if (fieldValue.trim() === '') {
                    isFormValid = false;
                    console.log('Missing value for field:', fieldName, 'Current value:', fieldValue);
                } else {
                    console.log('Field OK:', fieldName, '=', fieldValue);
                }
            }
        });
        
        // Check that activity_id has been generated (has category prefix)
        const activityIdField = document.getElementById('activity_id');
        if (!activityIdField || !activityIdField.value || activityIdField.value.length < 2) {
            isFormValid = false;
            console.log('Activity ID not generated - select a category first');
        }
        
        // Check that at least one schedule day is selected
        const scheduleDaysChecked = document.querySelector('input[name="schedule_days[]"]:checked');
        if (!scheduleDaysChecked) {
            isFormValid = false;
            console.log('No schedule days selected');
        }
        
        // Enable/disable submit button
        if (isFormValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary-modern');
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Create Activity';
            console.log('✅ Form validation passed - all fields complete');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-primary-modern');
            submitBtn.classList.add('btn-secondary');
            submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Complete Required Fields';
            console.log('❌ Form validation failed - check console above for missing fields');
        }
        
        console.log('Form validation result:', isFormValid);
        return isFormValid;
    }

    // Add event listeners for schedule conflict checking
    ['schedule_days[]', 'start_time', 'duration_hours', 'instructor_id', 'location', 'start_date', 'activity_period'].forEach(name => {
        document.querySelectorAll(`[name="${name}"]`).forEach(input => {
            input.addEventListener('change', checkScheduleConflicts);
        });
    });

    // Add event listeners for form validation (activity_id is auto-generated)
    const validationFields = [
        'activity_name', 'category', 'difficulty_level', 'description',
        'centre_id', 'location', 'instructor_id', 'max_participants', 'min_participants',
        'sessions_per_week', 'duration_hours', 'start_date', 'start_time', 'activity_period'
    ];
    
    validationFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                if (currentTab === tabs.length - 1) {
                    validateEntireForm();
                }
            });
            field.addEventListener('input', function() {
                if (currentTab === tabs.length - 1) {
                    validateEntireForm();
                }
            });
        }
    });
    
    // Add validation for schedule days checkboxes
    document.querySelectorAll('input[name="schedule_days[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (currentTab === tabs.length - 1) {
                validateEntireForm();
            }
        });
    });

    // Auto-calculate end date based on start date and period
    function calculateEndDate() {
        const startDateField = document.getElementById('start_date');
        const periodField = document.getElementById('activity_period');
        const endDateField = document.getElementById('end_date');
        
        if (startDateField.value && periodField.value) {
            const startDate = new Date(startDateField.value);
            const periodMonths = parseInt(periodField.value);
            
            // Add months to start date
            const endDate = new Date(startDate);
            endDate.setMonth(endDate.getMonth() + periodMonths);
            
            // Format date as YYYY-MM-DD
            const formattedEndDate = endDate.toISOString().split('T')[0];
            endDateField.value = formattedEndDate;
        }
    }
    
    // Add event listeners for end date calculation
    const startDateField = document.getElementById('start_date');
    const periodField = document.getElementById('activity_period');
    
    if (startDateField) {
        startDateField.addEventListener('change', calculateEndDate);
    }
    if (periodField) {
        periodField.addEventListener('change', calculateEndDate);
    }

    // Add event listeners to buttons as backup to onclick handlers
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    
    console.log('Button elements found - nextBtn:', !!nextBtn, 'prevBtn:', !!prevBtn);
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Next button clicked, current tab:', currentTab);
            try {
                changeTab(1);
            } catch (error) {
                console.error('Error in changeTab:', error);
            }
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            changeTab(-1);
        });
    }
    
    // Add click handlers to tab navigation buttons
    const navLinks = document.querySelectorAll('.nav-link');
    console.log('Found', navLinks.length, 'nav links');
    
    navLinks.forEach((tabButton, index) => {
        tabButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Tab link clicked, switching to tab:', index);
            currentTab = index;
            showTab();
        });
    });
    
    // Test if the global function is accessible
    console.log('Global changeTab function:', typeof window.changeTab);
    
    // Add a test function that can be called from console
    window.testTabNavigation = function() {
        console.log('Testing tab navigation...');
        changeTab(1);
    };
    
    // Debug functions
    window.debugCurrentState = function() {
        console.log('=== DEBUG CURRENT STATE ===');
        console.log('currentTab:', currentTab);
        console.log('tabs array:', tabs);
        console.log('Current tab name:', tabs[currentTab]);
        console.log('Next button exists:', !!document.getElementById('nextBtn'));
        console.log('Current tab element exists:', !!document.getElementById(tabs[currentTab]));
        console.log('All tab elements:');
        tabs.forEach((tabName, index) => {
            const element = document.getElementById(tabName);
            console.log(`  ${index}: ${tabName} - exists: ${!!element}`);
        });
    };
    
    window.forceNextTab = function() {
        console.log('Forcing next tab...');
        currentTab++;
        if (currentTab >= tabs.length) currentTab = tabs.length - 1;
        showTab();
    };
    
    // Emergency bypass function
    window.skipToTab = function(tabIndex) {
        console.log('Skipping to tab:', tabIndex);
        currentTab = tabIndex;
        showTab();
    };
    
    // Simple navigation without validation
    window.simpleNext = function() {
        console.log('Simple next - current tab:', currentTab);
        currentTab++;
        if (currentTab >= tabs.length) currentTab = tabs.length - 1;
        showTab();
        console.log('Simple next - new tab:', currentTab);
    };
    
    // Test API directly
    window.testInstructorAPI = function(centreId) {
        console.log('Testing instructor API for centre:', centreId);
        fetch(`/centres/${centreId}/instructors`)
            .then(response => {
                console.log('API Response:', response.status, response.statusText);
                return response.text(); // Get as text first to see raw response
            })
            .then(text => {
                console.log('Raw API Response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed JSON:', data);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                }
            })
            .catch(error => {
                console.error('API Error:', error);
            });
    };
    
    // Test with first available centre
    window.testWithFirstCentre = function() {
        const centreSelect = document.getElementById('centre_id');
        if (centreSelect && centreSelect.options.length > 1) {
            const firstCentreId = centreSelect.options[1].value;
            console.log('Testing with first centre:', firstCentreId);
            testInstructorAPI(firstCentreId);
        } else {
            console.log('No centres available to test with');
        }
    };

    // Form submission - comprehensive validation only on final submit
    document.getElementById('activityForm').addEventListener('submit', function(e) {
        let hasErrors = false;
        const errors = [];
        
        // Validate all required fields across all tabs
        const requiredFields = {
            'activity_name': 'Activity Name',
            'category': 'Category', 
            'difficulty_level': 'Difficulty Level',
            'description': 'Description',
            'centre_id': 'Centre',
            'location': 'Location',
            'instructor_id': 'Instructor',
            'max_participants': 'Maximum Participants',
            'min_participants': 'Minimum Participants',
            'sessions_per_week': 'Sessions per Week',
            'duration_hours': 'Duration',
            'start_date': 'Start Date',
            'start_time': 'Start Time'
        };
        
        Object.keys(requiredFields).forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                field.classList.add('is-invalid');
                errors.push(requiredFields[fieldName]);
                hasErrors = true;
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });
        
        // Check schedule days
        const scheduleDaysChecked = document.querySelector('input[name="schedule_days[]"]:checked');
        if (!scheduleDaysChecked) {
            errors.push('At least one day must be selected for the schedule');
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.preventDefault();
            alert('Please fill in the following required fields:\n- ' + errors.join('\n- '));
        }
    });
});
</script>
@endsection