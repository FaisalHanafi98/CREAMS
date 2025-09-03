@extends('layouts.app')

@section('title', 'Create New Activity - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities-enhanced.css') }}">
<link rel="stylesheet" href="{{ asset('css/form-validation-enhanced.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="activity-creation-enhanced">
    <!-- Standardized Flash Messages -->
    @include('components.flash-messages')
    <!-- Enhanced Header -->
    <div class="creation-header-enhanced">
        <div class="header-backdrop"></div>
        <div class="header-content">
            <div class="header-main">
                <div class="breadcrumb-enhanced">
                    <a href="{{ route('activities.home') }}" class="breadcrumb-link">
                        <i class="fas fa-tasks"></i> Activities
                    </a>
                    <i class="fas fa-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Create New Activity</span>
                </div>
                <h1 class="page-title">
                    <span class="title-icon">✨</span>
                    Create New Activity
                </h1>
                <p class="page-subtitle">Design and configure a new rehabilitation or educational program with comprehensive validation</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('activities.home') }}" class="btn-enhanced btn-ghost">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Activities</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-container">
        <div class="progress-steps">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Basic Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Details</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Schedule</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Resources</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-number">5</div>
                <div class="step-label">Review</div>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: 20%"></div>
        </div>
    </div>

    <!-- Main Form Container -->
    <div class="form-container-enhanced">
        <form id="activityForm" method="POST" action="{{ route('activities.store') }}" novalidate>
            @csrf
            
            <!-- Step 1: Basic Information -->
            <div class="form-step active" data-step="1">
                <div class="step-header">
                    <h2 class="step-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </h2>
                    <p class="step-description">Essential details about your activity program</p>
                </div>

                <div class="form-grid">
                    <!-- Activity Name -->
                    <div class="form-group full-width">
                        <label for="activity_name" class="form-label">
                            <i class="fas fa-tag"></i>
                            Activity Name
                            <span class="required">*</span>
                            <span class="field-help" data-tooltip="Choose a clear, descriptive name for your activity">?</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   id="activity_name" 
                                   name="activity_name" 
                                   class="form-control-enhanced @error('activity_name') is-invalid @enderror" 
                                   placeholder="e.g., Speech Therapy - Basic Communication"
                                   value="{{ old('activity_name') }}"
                                   maxlength="100"
                                   required>
                            <div class="input-feedback">
                                <div class="character-count">
                                    <span class="current">{{ strlen(old('activity_name', '')) }}</span>/<span class="max">100</span>
                                </div>
                            </div>
                        </div>
                        <div class="validation-message">
                            @error('activity_name')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="field-suggestions" style="display: none;">
                            <!-- Dynamic suggestions based on category -->
                        </div>
                    </div>

                    <!-- Activity ID is auto-generated by database -->

                    <!-- Centre Assignment -->
                    <div class="form-group half-width">
                        <label for="centre_id" class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Centre Location
                            <span class="required">*</span>
                            <span class="field-help" data-tooltip="Select the centre where this activity will be conducted">?</span>
                        </label>
                        <div class="input-wrapper">
                            <select id="centre_id" name="centre_id" class="form-select-enhanced @error('centre_id') is-invalid @enderror" required>
                                <option value="">Select a centre...</option>
                                @foreach(\App\Models\Centre::where('centre_status', 'active')->get() as $centre)
                                    <option value="{{ $centre->centre_id }}" 
                                            {{ old('centre_id') == $centre->centre_id ? 'selected' : '' }}
                                            data-location="{{ $centre->centre_location }}"
                                            data-capacity="{{ $centre->centre_capacity ?? 'N/A' }}">
                                        {{ $centre->centre_name }} 
                                        @if($centre->centre_location)
                                            - {{ $centre->centre_location }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="select-indicator">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="validation-message">
                            @error('centre_id')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="centre-info" style="display: none;">
                            <div class="info-card">
                                <div class="info-item">
                                    <span class="info-label">Location:</span>
                                    <span class="info-value" id="centre-location">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Capacity:</span>
                                    <span class="info-value" id="centre-capacity">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Selection -->
                    <div class="form-group half-width">
                        <label for="category_id" class="form-label">
                            <i class="fas fa-folder"></i>
                            Activity Category
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <select id="category_id" name="category_id" class="form-select-enhanced @error('category_id') is-invalid @enderror" required>
                                <option value="">Select a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="select-indicator">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="validation-message">
                            @error('category_id')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Activity Type is now handled by Category selection -->

                    <!-- Activity Description -->
                    <div class="form-group full-width">
                        <label for="activity_description" class="form-label">
                            <i class="fas fa-align-left"></i>
                            Description
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <textarea id="activity_description" 
                                      name="activity_description" 
                                      class="form-control-enhanced" 
                                      rows="4" 
                                      placeholder="Provide a detailed description of the activity, its objectives, and expected outcomes..."
                                      maxlength="500"
                                      required></textarea>
                            <div class="input-feedback">
                                <div class="character-count">
                                    <span class="current">0</span>/<span class="max">500</span>
                                </div>
                            </div>
                        </div>
                        <div class="validation-message"></div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Activity Details -->
            <div class="form-step" data-step="2">
                <div class="step-header">
                    <h2 class="step-title">
                        <i class="fas fa-cogs"></i>
                        Activity Details
                    </h2>
                    <p class="step-description">Configure specific parameters and requirements</p>
                </div>

                <div class="form-grid">
                    <!-- Difficulty Level -->
                    <div class="form-group third-width">
                        <label for="difficulty_level" class="form-label">
                            <i class="fas fa-signal"></i>
                            Difficulty Level
                            <span class="required">*</span>
                        </label>
                        <div class="radio-group-enhanced">
                            <label class="radio-option">
                                <input type="radio" name="difficulty_level" value="beginner" required>
                                <span class="radio-mark"></span>
                                <div class="radio-content">
                                    <span class="radio-title">Beginner</span>
                                    <span class="radio-description">Basic level activities</span>
                                </div>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="difficulty_level" value="intermediate" required>
                                <span class="radio-mark"></span>
                                <div class="radio-content">
                                    <span class="radio-title">Intermediate</span>
                                    <span class="radio-description">Moderate complexity</span>
                                </div>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="difficulty_level" value="advanced" required>
                                <span class="radio-mark"></span>
                                <div class="radio-content">
                                    <span class="radio-title">Advanced</span>
                                    <span class="radio-description">High complexity</span>
                                </div>
                            </label>
                        </div>
                        <div class="validation-message"></div>
                    </div>

                    <!-- Age Group -->
                    <div class="form-group third-width">
                        <label for="age_group" class="form-label">
                            <i class="fas fa-child"></i>
                            Target Age Group
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <select id="age_group" name="age_group" class="form-select-enhanced" required>
                                <option value="">Select age group...</option>
                                <option value="children">Children (4-8 years)</option>
                                <option value="adolescents">Adolescents (9-16 years)</option>
                                <option value="adults">Adults (17+ years)</option>
                                <option value="all_ages">All Ages</option>
                            </select>
                            <div class="select-indicator">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="validation-message"></div>
                    </div>

                    <!-- Duration -->
                    <div class="form-group third-width">
                        <label for="session_duration" class="form-label">
                            <i class="fas fa-clock"></i>
                            Session Duration (minutes)
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="number" 
                                   id="session_duration" 
                                   name="session_duration" 
                                   class="form-control-enhanced" 
                                   min="15" 
                                   max="300" 
                                   step="15"
                                   placeholder="60"
                                   required>
                            <div class="input-addon">min</div>
                        </div>
                        <div class="validation-message"></div>
                        <div class="input-help">Recommended: 30-90 minutes</div>
                    </div>

                    <!-- Participant Capacity -->
                    <div class="form-group half-width">
                        <label class="form-label">
                            <i class="fas fa-users"></i>
                            Participant Capacity
                            <span class="required">*</span>
                        </label>
                        <div class="capacity-controls">
                            <div class="capacity-input">
                                <label for="min_participants" class="capacity-label">Minimum</label>
                                <input type="number" 
                                       id="min_participants" 
                                       name="min_participants" 
                                       class="form-control-enhanced" 
                                       min="1" 
                                       max="50"
                                       value="1"
                                       required>
                            </div>
                            <div class="capacity-separator">to</div>
                            <div class="capacity-input">
                                <label for="max_participants" class="capacity-label">Maximum</label>
                                <input type="number" 
                                       id="max_participants" 
                                       name="max_participants" 
                                       class="form-control-enhanced" 
                                       min="1" 
                                       max="50"
                                       value="10"
                                       required>
                            </div>
                        </div>
                        <div class="validation-message"></div>
                        <div class="capacity-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Maximum must be greater than minimum</span>
                        </div>
                    </div>

                    <!-- Activity Location -->
                    <div class="form-group half-width">
                        <label for="activity_location" class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Activity Location
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   id="activity_location" 
                                   name="activity_location" 
                                   class="form-control-enhanced" 
                                   placeholder="e.g., Main Hall, Therapy Room A, Outdoor Area..."
                                   maxlength="255"
                                   required>
                            <div class="input-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                        <div class="validation-message"></div>
                        <div class="input-help">Specify the primary location where this activity will take place</div>
                    </div>

                    <!-- Prerequisites -->
                    <div class="form-group half-width">
                        <label for="prerequisites" class="form-label">
                            <i class="fas fa-list-check"></i>
                            Prerequisites
                            <span class="optional">Optional</span>
                        </label>
                        <div class="input-wrapper">
                            <textarea id="prerequisites" 
                                      name="prerequisites" 
                                      class="form-control-enhanced" 
                                      rows="3" 
                                      placeholder="Any requirements or skills needed before joining this activity..."
                                      maxlength="300"></textarea>
                            <div class="input-feedback">
                                <div class="character-count">
                                    <span class="current">0</span>/<span class="max">300</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Schedule Configuration -->
            <div class="form-step" data-step="3">
                <div class="step-header">
                    <h2 class="step-title">
                        <i class="fas fa-calendar-alt"></i>
                        Schedule Configuration
                    </h2>
                    <p class="step-description">Set up timing and scheduling parameters</p>
                    
                    <!-- Critical Validation Alert -->
                    <div class="critical-alert" id="scheduleAlert" style="display: none;">
                        <div class="alert-content">
                            <i class="fas fa-exclamation-triangle alert-icon"></i>
                            <div class="alert-text">
                                <strong>Schedule Conflict Detected!</strong>
                                <p>Please review the conflicts below and adjust your schedule accordingly.</p>
                            </div>
                        </div>
                        <div class="alert-details" id="conflictDetails">
                            <!-- Dynamic conflict details -->
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <!-- Activity Period -->
                    <div class="form-group full-width">
                        <label for="activity_period" class="form-label">
                            <i class="fas fa-calendar-week"></i>
                            Activity Period
                            <span class="required">*</span>
                        </label>
                        <div class="period-selection">
                            <div class="period-option">
                                <input type="radio" id="period_single" name="activity_period_type" value="single" required>
                                <label for="period_single" class="period-label">
                                    <span class="period-icon">📅</span>
                                    <span class="period-title">Single Session</span>
                                    <span class="period-description">One-time activity</span>
                                </label>
                            </div>
                            <div class="period-option">
                                <input type="radio" id="period_recurring" name="activity_period_type" value="recurring" required>
                                <label for="period_recurring" class="period-label">
                                    <span class="period-icon">🔄</span>
                                    <span class="period-title">Recurring Sessions</span>
                                    <span class="period-description">Multiple sessions</span>
                                </label>
                            </div>
                            <div class="period-option">
                                <input type="radio" id="period_course" name="activity_period_type" value="course" required>
                                <label for="period_course" class="period-label">
                                    <span class="period-icon">📚</span>
                                    <span class="period-title">Course Program</span>
                                    <span class="period-description">Structured curriculum</span>
                                </label>
                            </div>
                        </div>
                        <div class="validation-message"></div>
                    </div>

                    <!-- Date Range -->
                    <div class="form-group date-range-group">
                        <div class="date-input-group">
                            <label for="start_date" class="form-label">
                                <i class="fas fa-calendar-day"></i>
                                Start Date
                                <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="date" 
                                       id="start_date" 
                                       name="start_date" 
                                       class="form-control-enhanced" 
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                <div class="date-helper">
                                    <button type="button" class="btn-date-today">Today</button>
                                    <button type="button" class="btn-date-tomorrow">Tomorrow</button>
                                </div>
                            </div>
                            <div class="validation-message"></div>
                        </div>

                        <div class="date-input-group" id="endDateGroup" style="display: none;">
                            <label for="end_date" class="form-label">
                                <i class="fas fa-calendar-day"></i>
                                End Date
                                <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="date" 
                                       id="end_date" 
                                       name="end_date" 
                                       class="form-control-enhanced">
                                <div class="date-difference" id="dateDifference"></div>
                            </div>
                            <div class="validation-message"></div>
                        </div>
                    </div>

                    <!-- Time Selection -->
                    <div class="form-group time-group">
                        <div class="time-input-group">
                            <label for="activity_start_time" class="form-label">
                                <i class="fas fa-clock"></i>
                                Start Time
                                <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="time" 
                                       id="activity_start_time" 
                                       name="activity_start_time" 
                                       class="form-control-enhanced" 
                                       required>
                                <div class="time-helper">
                                    <button type="button" class="btn-time-preset" data-time="09:00">9 AM</button>
                                    <button type="button" class="btn-time-preset" data-time="14:00">2 PM</button>
                                    <button type="button" class="btn-time-preset" data-time="16:00">4 PM</button>
                                </div>
                            </div>
                            <div class="validation-message"></div>
                        </div>

                        <div class="time-input-group">
                            <label for="activity_end_time" class="form-label">
                                <i class="fas fa-clock"></i>
                                End Time
                                <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="time" 
                                       id="activity_end_time" 
                                       name="activity_end_time" 
                                       class="form-control-enhanced" 
                                       required>
                                <div class="time-duration" id="timeDuration"></div>
                            </div>
                            <div class="validation-message"></div>
                        </div>
                    </div>

                    <!-- Location is now set per session, not per activity -->

                    <!-- Recurring Options (shown only for recurring/course) -->
                    <div class="form-group full-width recurring-options" style="display: none;">
                        <label class="form-label">
                            <i class="fas fa-repeat"></i>
                            Recurrence Pattern
                            <span class="required">*</span>
                        </label>
                        <div class="recurrence-controls">
                            <div class="recurrence-frequency">
                                <label for="sessions_per_week">Sessions per week:</label>
                                <select id="sessions_per_week" name="sessions_per_week" class="form-select-enhanced">
                                    <option value="1">Once a week</option>
                                    <option value="2">Twice a week</option>
                                    <option value="3">Three times a week</option>
                                    <option value="5">Five times a week (weekdays)</option>
                                </select>
                            </div>
                            <div class="recurrence-days">
                                <label>Select days:</label>
                                <div class="day-selector">
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="monday">
                                        <span class="day-mark">Mon</span>
                                    </label>
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="tuesday">
                                        <span class="day-mark">Tue</span>
                                    </label>
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="wednesday">
                                        <span class="day-mark">Wed</span>
                                    </label>
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="thursday">
                                        <span class="day-mark">Thu</span>
                                    </label>
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="friday">
                                        <span class="day-mark">Fri</span>
                                    </label>
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="saturday">
                                        <span class="day-mark">Sat</span>
                                    </label>
                                    <label class="day-option">
                                        <input type="checkbox" name="recurring_days[]" value="sunday">
                                        <span class="day-mark">Sun</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Resources & Requirements -->
            <div class="form-step" data-step="4">
                <div class="step-header">
                    <h2 class="step-title">
                        <i class="fas fa-toolbox"></i>
                        Resources & Requirements
                    </h2>
                    <p class="step-description">Specify instructor qualifications and required resources</p>
                </div>

                <div class="form-grid">
                    <!-- Instructor Assignment -->
                    <div class="form-group full-width">
                        <label for="instructor_id" class="form-label">
                            <i class="fas fa-user-tie"></i>
                            Assigned Instructor
                            <span class="required">*</span>
                            <span class="field-help" data-tooltip="Select a qualified instructor for this activity">?</span>
                        </label>
                        <div class="instructor-selection">
                            <div class="input-wrapper">
                                <select id="instructor_id" name="instructor_id" class="form-select-enhanced" required>
                                    <option value="">Select an instructor...</option>
                                    @foreach(\App\Models\User::where('role', 'teacher')->where('status', 'active')->get() as $instructor)
                                        <option value="{{ $instructor->id }}" 
                                                data-qualifications="{{ $instructor->qualifications ?? '' }}"
                                                data-specializations="{{ $instructor->specializations ?? '' }}"
                                                data-centre="{{ $instructor->centre_id }}">
                                            {{ $instructor->name }} 
                                            @if($instructor->specializations)
                                                - {{ $instructor->specializations }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="select-indicator">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="instructor-qualification-check" id="qualificationCheck" style="display: none;">
                                <!-- Dynamic qualification validation -->
                            </div>
                        </div>
                        <div class="validation-message"></div>
                        <div class="instructor-info" style="display: none;">
                            <div class="info-card">
                                <div class="instructor-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Qualifications:</span>
                                        <span class="detail-value" id="instructor-qualifications">-</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Specializations:</span>
                                        <span class="detail-value" id="instructor-specializations">-</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Centre:</span>
                                        <span class="detail-value" id="instructor-centre">-</span>
                                    </div>
                                </div>
                                <div class="availability-check" id="instructorAvailability">
                                    <!-- Dynamic availability check -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Required Qualifications -->
                    <div class="form-group full-width">
                        <label for="required_qualifications" class="form-label">
                            <i class="fas fa-certificate"></i>
                            Required Qualifications
                            <span class="field-help" data-tooltip="Specify minimum qualifications needed to conduct this activity">?</span>
                        </label>
                        <div class="qualification-builder">
                            <div class="qualification-presets">
                                <button type="button" class="preset-btn" data-qual="Speech Therapy Certification">Speech Therapy</button>
                                <button type="button" class="preset-btn" data-qual="Occupational Therapy License">Occupational Therapy</button>
                                <button type="button" class="preset-btn" data-qual="Physical Therapy License">Physical Therapy</button>
                                <button type="button" class="preset-btn" data-qual="Special Education Certification">Special Education</button>
                                <button type="button" class="preset-btn" data-qual="Behavioral Analysis Certification">Behavioral Analysis</button>
                            </div>
                            <div class="input-wrapper">
                                <textarea id="required_qualifications" 
                                          name="required_qualifications" 
                                          class="form-control-enhanced" 
                                          rows="3" 
                                          placeholder="List required certifications, licenses, or qualifications..."
                                          maxlength="400"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Required Resources -->
                    <div class="form-group full-width">
                        <label for="required_resources" class="form-label">
                            <i class="fas fa-boxes"></i>
                            Required Resources & Equipment
                        </label>
                        <div class="resource-builder">
                            <div class="resource-categories">
                                <div class="resource-category">
                                    <h5>Common Equipment</h5>
                                    <div class="resource-checkboxes">
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Tables and chairs">
                                            <span>Tables and chairs</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Whiteboard">
                                            <span>Whiteboard</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Projector">
                                            <span>Projector</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Audio system">
                                            <span>Audio system</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="resource-category">
                                    <h5>Therapy Equipment</h5>
                                    <div class="resource-checkboxes">
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Exercise mats">
                                            <span>Exercise mats</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Therapy balls">
                                            <span>Therapy balls</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Communication boards">
                                            <span>Communication boards</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Sensory tools">
                                            <span>Sensory tools</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="resource-category">
                                    <h5>Safety Equipment</h5>
                                    <div class="resource-checkboxes">
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="First aid kit">
                                            <span>First aid kit</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Safety mats">
                                            <span>Safety mats</span>
                                        </label>
                                        <label class="resource-checkbox">
                                            <input type="checkbox" name="resources[]" value="Emergency contact list">
                                            <span>Emergency contact list</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-resources">
                                <label for="custom_resources">Additional Resources:</label>
                                <textarea id="custom_resources" 
                                          name="custom_resources" 
                                          class="form-control-enhanced" 
                                          rows="3" 
                                          placeholder="List any additional resources, materials, or special requirements..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Review & Submit -->
            <div class="form-step" data-step="5">
                <div class="step-header">
                    <h2 class="step-title">
                        <i class="fas fa-clipboard-check"></i>
                        Review & Submit
                    </h2>
                    <p class="step-description">Review all information before creating the activity</p>
                </div>

                <div class="review-container">
                    <div class="review-sections">
                        <!-- Basic Information Review -->
                        <div class="review-section">
                            <h3 class="review-title">
                                <i class="fas fa-info-circle"></i>
                                Basic Information
                                <button type="button" class="btn-edit-section" data-step="1">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </h3>
                            <div class="review-content" id="reviewBasicInfo">
                                <!-- Dynamic content populated by JS -->
                            </div>
                        </div>

                        <!-- Details Review -->
                        <div class="review-section">
                            <h3 class="review-title">
                                <i class="fas fa-cogs"></i>
                                Activity Details
                                <button type="button" class="btn-edit-section" data-step="2">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </h3>
                            <div class="review-content" id="reviewDetails">
                                <!-- Dynamic content populated by JS -->
                            </div>
                        </div>

                        <!-- Schedule Review -->
                        <div class="review-section">
                            <h3 class="review-title">
                                <i class="fas fa-calendar-alt"></i>
                                Schedule Configuration
                                <button type="button" class="btn-edit-section" data-step="3">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </h3>
                            <div class="review-content" id="reviewSchedule">
                                <!-- Dynamic content populated by JS -->
                            </div>
                        </div>

                        <!-- Resources Review -->
                        <div class="review-section">
                            <h3 class="review-title">
                                <i class="fas fa-toolbox"></i>
                                Resources & Requirements
                                <button type="button" class="btn-edit-section" data-step="4">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </h3>
                            <div class="review-content" id="reviewResources">
                                <!-- Dynamic content populated by JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Final Validation Status -->
                    <div class="validation-summary" id="validationSummary">
                        <div class="validation-header">
                            <h4>Validation Status</h4>
                            <div class="validation-progress">
                                <div class="progress-circle" id="validationProgress">
                                    <span class="progress-value">0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="validation-items" id="validationItems">
                            <!-- Dynamic validation items -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Navigation -->
            <div class="form-navigation">
                <button type="button" id="prevStep" class="btn-enhanced btn-secondary" disabled>
                    <i class="fas fa-arrow-left"></i>
                    Previous
                </button>
                
                <div class="navigation-info">
                    <span class="step-indicator">Step <span id="currentStepNumber">1</span> of 5</span>
                </div>
                
                <button type="button" id="nextStep" class="btn-enhanced btn-primary">
                    Next
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="submit" id="submitForm" class="btn-enhanced btn-success" style="display: none;">
                    <i class="fas fa-check-circle"></i>
                    Create Activity
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modals -->
<!-- Conflict Resolution Modal -->
<div class="modal fade" id="conflictModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    Schedule Conflicts Detected
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="conflictModalContent">
                <!-- Dynamic conflict resolution options -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="ignoreConflicts">Continue Anyway</button>
                <button type="button" class="btn btn-primary" id="resolveConflicts">Auto-Resolve</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <p class="mt-3">Creating your activity...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities-enhanced.js') }}"></script>
<script src="{{ asset('js/form-validation-enhanced.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the enhanced activity creation form
    const formValidator = new EnhancedFormValidator({
        form: '#activityForm',
        steps: 5,
        realTimeValidation: true,
        conflictChecking: true,
        autoSave: true
    });
    
    formValidator.init();
    
    console.log('Enhanced Activity Creation Form initialized');
});
</script>
@endsection