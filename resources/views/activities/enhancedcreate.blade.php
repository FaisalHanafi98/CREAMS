@extends('layouts.app')

@section('title', 'Create New Activity - CREAMS')

@section('styles')
<style>
    /* Enhanced Activity Creation Form Styles */
    .activity-creation-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .creation-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 30px;
        border-radius: 12px 12px 0 0;
        text-align: center;
        margin-bottom: 0;
    }
    
    .creation-header h1 {
        font-size: 2.2rem;
        font-weight: 600;
        margin: 0 0 10px 0;
    }
    
    .creation-header p {
        margin: 0;
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .form-card {
        background: white;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .step-navigation {
        background: #f8f9fa;
        padding: 20px 30px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .step-tabs {
        display: flex;
        gap: 0;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .step-tab {
        flex: 1;
        position: relative;
    }
    
    .step-tab button {
        width: 100%;
        padding: 15px 20px;
        border: none;
        background: transparent;
        color: #6c757d;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .step-tab.active button {
        color: #007bff;
        background: rgba(0, 123, 255, 0.1);
    }
    
    .step-tab.completed button {
        color: #28a745;
    }
    
    .step-tab button::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: #007bff;
        transition: width 0.3s ease;
    }
    
    .step-tab.active button::after {
        width: 100%;
    }
    
    .step-tab.completed button::after {
        width: 100%;
        background: #28a745;
    }
    
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        margin-bottom: 5px;
    }
    
    .step-number {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }
    
    .step-tab.active .step-number {
        background: #007bff;
        color: white;
    }
    
    .step-tab.completed .step-number {
        background: #28a745;
        color: white;
    }
    
    .form-content {
        padding: 40px;
    }
    
    .step-section {
        display: none;
        animation: fadeInUp 0.4s ease;
    }
    
    .step-section.active {
        display: block;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .section-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: #007bff;
    }
    
    .form-grid {
        display: grid;
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .form-grid-2 {
        grid-template-columns: 1fr 1fr;
    }
    
    .form-grid-3 {
        grid-template-columns: 1fr 1fr 1fr;
    }
    
    .form-grid-4 {
        grid-template-columns: 2fr 1fr 1fr;
    }
    
    .form-field {
        position: relative;
    }
    
    .form-field label {
        display: block;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .required-indicator {
        color: #dc3545;
        margin-left: 4px;
    }
    
    .form-field input,
    .form-field select,
    .form-field textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
        line-height: 1.5;
    }
    
    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        outline: none;
    }
    
    .form-field.is-invalid input,
    .form-field.is-invalid select,
    .form-field.is-invalid textarea {
        border-color: #dc3545;
    }
    
    .field-help {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }
    
    .field-error {
        font-size: 12px;
        color: #dc3545;
        margin-top: 4px;
    }
    
    .conflict-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 6px;
        padding: 12px;
        margin-top: 15px;
        display: none;
    }
    
    .conflict-warning.show {
        display: block;
    }
    
    .conflict-warning .icon {
        color: #856404;
        margin-right: 8px;
    }
    
    .activity-type-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .type-option {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .type-option:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }
    
    .type-option.selected {
        border-color: #007bff;
        background: rgba(0, 123, 255, 0.05);
    }
    
    .type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    .type-icon {
        font-size: 2rem;
        color: #007bff;
        margin-bottom: 10px;
    }
    
    .type-name {
        font-weight: 600;
        color: #343a40;
        font-size: 14px;
    }
    
    .session-builder {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .session-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }
    
    .session-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .session-code {
        font-family: 'Courier New', monospace;
        background: #007bff;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .remove-session {
        color: #dc3545;
        cursor: pointer;
        font-size: 1.2rem;
    }
    
    .add-session-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .add-session-btn:hover {
        background: #0056b3;
        transform: translateY(-1px);
    }
    
    .form-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    .nav-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .nav-btn-primary {
        background: #007bff;
        color: white;
    }
    
    .nav-btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .nav-btn-success {
        background: #28a745;
        color: white;
    }
    
    .nav-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .preview-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .preview-item {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .preview-item:last-child {
        border-bottom: none;
    }
    
    .preview-label {
        font-weight: 600;
        color: #343a40;
        width: 200px;
        flex-shrink: 0;
    }
    
    .preview-value {
        color: #6c757d;
        flex-grow: 1;
    }
    
    @media (max-width: 768px) {
        .form-grid-2,
        .form-grid-3,
        .form-grid-4 {
            grid-template-columns: 1fr;
        }
        
        .step-tabs {
            flex-direction: column;
        }
        
        .form-content {
            padding: 20px;
        }
        
        .activity-type-selector {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="activity-creation-container">
    <!-- Header -->
    <div class="creation-header">
        <h1><i class="fas fa-plus-circle"></i> Create New Activity</h1>
        <p>Design engaging activities for trainees with comprehensive session management</p>
    </div>
    
    <!-- Form Card -->
    <div class="form-card">
        <!-- Step Navigation -->
        <div class="step-navigation">
            <ul class="step-tabs">
                <li class="step-tab active" data-step="1">
                    <button type="button">
                        <div class="step-indicator">
                            <span class="step-number">1</span>
                            <span>Basic Information</span>
                        </div>
                    </button>
                </li>
                <li class="step-tab" data-step="2">
                    <button type="button">
                        <div class="step-indicator">
                            <span class="step-number">2</span>
                            <span>Schedule & Venue</span>
                        </div>
                    </button>
                </li>
                <li class="step-tab" data-step="3">
                    <button type="button">
                        <div class="step-indicator">
                            <span class="step-number">3</span>
                            <span>Goals & Resources</span>
                        </div>
                    </button>
                </li>
                <li class="step-tab" data-step="4">
                    <button type="button">
                        <div class="step-indicator">
                            <span class="step-number">4</span>
                            <span>Review & Create</span>
                        </div>
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Form Content -->
        <div class="form-content">
            <form id="activity-creation-form" action="{{ route('activities.store') }}" method="POST">
                @csrf
                
                <!-- Step 1: Basic Information -->
                <div class="step-section active" data-step="1">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </h2>
                    
                    <div class="form-grid form-grid-4">
                        <div class="form-field">
                            <label for="activity_name">
                                Activity Name
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="text" 
                                   id="activity_name" 
                                   name="activity_name" 
                                   value="{{ old('activity_name') }}" 
                                   placeholder="e.g., Basic Motor Skills Development"
                                   required>
                            <div class="field-help">Enter a descriptive name for the activity</div>
                            @error('activity_name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="activity_code">
                                Activity Code
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="text" 
                                   id="activity_code" 
                                   name="activity_code" 
                                   value="{{ old('activity_code') }}" 
                                   placeholder="e.g., PHY-001"
                                   required>
                            <div class="field-help">Unique identifier for this activity</div>
                            @error('activity_code')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                @if(isset($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('category_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="activity_description">
                            Description
                            <span class="required-indicator">*</span>
                        </label>
                        <textarea id="activity_description" 
                                  name="activity_description" 
                                  rows="4" 
                                  placeholder="Provide a detailed description of the activity, its purpose, and what participants will learn..."
                                  required>{{ old('activity_description') }}</textarea>
                        <div class="field-help">Describe the activity in detail to help staff and trainees understand its purpose</div>
                        @error('activity_description')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-field">
                        <label>
                            Activity Type
                            <span class="required-indicator">*</span>
                        </label>
                        <div class="activity-type-selector">
                            <label class="type-option">
                                <input type="radio" name="activity_type" value="Individual" {{ old('activity_type') == 'Individual' ? 'checked' : '' }}>
                                <div class="type-icon"><i class="fas fa-user"></i></div>
                                <div class="type-name">Individual</div>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="activity_type" value="Group" {{ old('activity_type') == 'Group' ? 'checked' : '' }}>
                                <div class="type-icon"><i class="fas fa-users"></i></div>
                                <div class="type-name">Group</div>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="activity_type" value="Education" {{ old('activity_type') == 'Education' ? 'checked' : '' }}>
                                <div class="type-icon"><i class="fas fa-graduation-cap"></i></div>
                                <div class="type-name">Education</div>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="activity_type" value="Therapy" {{ old('activity_type') == 'Therapy' ? 'checked' : '' }}>
                                <div class="type-icon"><i class="fas fa-heartbeat"></i></div>
                                <div class="type-name">Therapy</div>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="activity_type" value="Training" {{ old('activity_type') == 'Training' ? 'checked' : '' }}>
                                <div class="type-icon"><i class="fas fa-dumbbell"></i></div>
                                <div class="type-name">Training</div>
                            </label>
                        </div>
                        @error('activity_type')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Step 2: Schedule & Venue -->
                <div class="step-section" data-step="2">
                    <h2 class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Schedule & Venue
                    </h2>
                    
                    <div class="form-grid form-grid-3">
                        <div class="form-field">
                            <label for="activity_date">
                                Date
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="date" 
                                   id="activity_date" 
                                   name="activity_date" 
                                   value="{{ old('activity_date') }}" 
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('activity_date')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="start_time">
                                Start Time
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="time" 
                                   id="start_time" 
                                   name="start_time" 
                                   value="{{ old('start_time') }}" 
                                   required>
                            @error('start_time')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="end_time">
                                End Time
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="time" 
                                   id="end_time" 
                                   name="end_time" 
                                   value="{{ old('end_time') }}" 
                                   required>
                            @error('end_time')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-grid form-grid-2">
                        <div class="form-field">
                            <label for="venue">
                                Venue
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="text" 
                                   id="venue" 
                                   name="venue" 
                                   value="{{ old('venue') }}" 
                                   placeholder="e.g., Kelas 5 Harapan, Therapy Room 1"
                                   required>
                            <div class="field-help">Specify the room or location where the activity will take place</div>
                            @error('venue')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="max_participants">
                                Max Participants
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="number" 
                                   id="max_participants" 
                                   name="max_participants" 
                                   value="{{ old('max_participants', 20) }}" 
                                   min="1" 
                                   max="100"
                                   required>
                            <div class="field-help">Maximum number of trainees that can participate</div>
                            @error('max_participants')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-grid form-grid-2">
                        <div class="form-field">
                            <label for="teacher_id">
                                Assigned Teacher
                                <span class="required-indicator">*</span>
                            </label>
                            <select id="teacher_id" name="teacher_id" required>
                                <option value="">Select Teacher</option>
                                @if(isset($teachers))
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" 
                                                {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('teacher_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="supervisor_id">Supervisor</label>
                            <select id="supervisor_id" name="supervisor_id">
                                <option value="">Select Supervisor (Optional)</option>
                                @if(isset($supervisors))
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}" 
                                                {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('supervisor_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Conflict Warning -->
                    <div class="conflict-warning" id="conflict-warning">
                        <i class="fas fa-exclamation-triangle icon"></i>
                        <span id="conflict-message">Checking for scheduling conflicts...</span>
                    </div>
                </div>
                
                <!-- Step 3: Goals & Resources -->
                <div class="step-section" data-step="3">
                    <h2 class="section-title">
                        <i class="fas fa-bullseye"></i>
                        Goals & Resources
                    </h2>
                    
                    <div class="form-grid form-grid-2">
                        <div class="form-field">
                            <label for="activity_goals">Activity Goals</label>
                            <textarea id="activity_goals" 
                                      name="activity_goals" 
                                      rows="5" 
                                      placeholder="What are the specific goals and learning objectives of this activity?">{{ old('activity_goals') }}</textarea>
                            <div class="field-help">Define what participants should achieve</div>
                            @error('activity_goals')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="expected_outcomes">Expected Outcomes</label>
                            <textarea id="expected_outcomes" 
                                      name="expected_outcomes" 
                                      rows="5" 
                                      placeholder="What outcomes and improvements are expected from this activity?">{{ old('expected_outcomes') }}</textarea>
                            <div class="field-help">Describe the expected results and benefits</div>
                            @error('expected_outcomes')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="required_resources">Required Resources & Materials</label>
                        <textarea id="required_resources" 
                                  name="required_resources" 
                                  rows="4" 
                                  placeholder="List all materials, equipment, and resources needed for this activity...">{{ old('required_resources') }}</textarea>
                        <div class="field-help">Include equipment, materials, technology, and any special requirements</div>
                        @error('required_resources')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-field">
                        <label for="special_instructions">Special Instructions</label>
                        <textarea id="special_instructions" 
                                  name="special_instructions" 
                                  rows="3" 
                                  placeholder="Any special instructions, safety considerations, or accessibility requirements...">{{ old('special_instructions') }}</textarea>
                        <div class="field-help">Include safety notes, accessibility requirements, or special considerations</div>
                        @error('special_instructions')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Step 4: Review & Create -->
                <div class="step-section" data-step="4">
                    <h2 class="section-title">
                        <i class="fas fa-check-circle"></i>
                        Review & Create Activity
                    </h2>
                    
                    <div class="preview-section">
                        <h3 style="margin-bottom: 20px; color: #343a40;">Activity Summary</h3>
                        
                        <div class="preview-item">
                            <div class="preview-label">Activity Name:</div>
                            <div class="preview-value" id="preview-name">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Activity Code:</div>
                            <div class="preview-value" id="preview-code">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Type:</div>
                            <div class="preview-value" id="preview-type">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Category:</div>
                            <div class="preview-value" id="preview-category">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Date & Time:</div>
                            <div class="preview-value" id="preview-datetime">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Venue:</div>
                            <div class="preview-value" id="preview-venue">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Max Participants:</div>
                            <div class="preview-value" id="preview-participants">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Teacher:</div>
                            <div class="preview-value" id="preview-teacher">-</div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Supervisor:</div>
                            <div class="preview-value" id="preview-supervisor">-</div>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label>
                            <input type="checkbox" id="confirm-create" required style="margin-right: 8px;">
                            I confirm that all information is accurate and the activity is ready to be created
                        </label>
                        @error('confirm')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="form-navigation">
                    <button type="button" class="nav-btn nav-btn-secondary" id="prev-btn" style="display: none;">
                        <i class="fas fa-arrow-left"></i>
                        Previous
                    </button>
                    
                    <div style="flex: 1;"></div>
                    
                    <button type="button" class="nav-btn nav-btn-primary" id="next-btn">
                        Next
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    
                    <button type="submit" class="nav-btn nav-btn-success" id="submit-btn" style="display: none;">
                        <i class="fas fa-save"></i>
                        Create Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Back to Activity Link -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Activity
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Initialize form
    updateStepDisplay();
    
    // Step navigation
    $('.step-tab button').click(function() {
        const targetStep = parseInt($(this).closest('.step-tab').data('step'));
        if (canNavigateToStep(targetStep)) {
            goToStep(targetStep);
        }
    });
    
    // Next button
    $('#next-btn').click(function() {
        if (validateCurrentStep()) {
            goToStep(currentStep + 1);
        }
    });
    
    // Previous button
    $('#prev-btn').click(function() {
        goToStep(currentStep - 1);
    });
    
    // Activity type selection
    $('.type-option').click(function() {
        $('.type-option').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input').prop('checked', true);
    });
    
    // Time validation
    $('#start_time, #end_time').change(function() {
        validateTimeAndCheckConflicts();
    });
    
    $('#activity_date, #venue, #teacher_id, #supervisor_id').change(function() {
        checkSchedulingConflicts();
    });
    
    // Form submission
    $('#activity-creation-form').submit(function(e) {
        if (!validateAllSteps()) {
            e.preventDefault();
            alert('Please complete all required fields before submitting.');
        }
    });
    
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        
        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            $(`.step-tab[data-step="${i}"]`).addClass('completed');
        }
        
        currentStep = step;
        updateStepDisplay();
        
        if (step === totalSteps) {
            updatePreview();
        }
    }
    
    function updateStepDisplay() {
        // Update active step
        $('.step-tab').removeClass('active');
        $(`.step-tab[data-step="${currentStep}"]`).addClass('active');
        
        // Update active section
        $('.step-section').removeClass('active');
        $(`.step-section[data-step="${currentStep}"]`).addClass('active');
        
        // Update navigation buttons
        $('#prev-btn').toggle(currentStep > 1);
        $('#next-btn').toggle(currentStep < totalSteps);
        $('#submit-btn').toggle(currentStep === totalSteps);
    }
    
    function canNavigateToStep(step) {
        // Can always go back
        if (step <= currentStep) return true;
        
        // Can only go forward if current step is valid
        return validateCurrentStep();
    }
    
    function validateCurrentStep() {
        let isValid = true;
        const currentSection = $(`.step-section[data-step="${currentStep}"]`);
        
        // Check required fields in current step
        currentSection.find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).focus();
                return false;
            }
        });
        
        // Additional validation for specific steps
        if (currentStep === 1) {
            if (!$('input[name="activity_type"]:checked').val()) {
                isValid = false;
                alert('Please select an activity type.');
            }
        }
        
        if (currentStep === 2) {
            isValid = validateTimeAndCheckConflicts() && isValid;
        }
        
        return isValid;
    }
    
    function validateAllSteps() {
        for (let i = 1; i <= totalSteps; i++) {
            const previousStep = currentStep;
            currentStep = i;
            if (!validateCurrentStep()) {
                currentStep = previousStep;
                goToStep(i);
                return false;
            }
        }
        currentStep = totalSteps;
        return true;
    }
    
    function validateTimeAndCheckConflicts() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (startTime && endTime && endTime <= startTime) {
            alert('End time must be after start time.');
            $('#end_time').val('');
            return false;
        }
        
        checkSchedulingConflicts();
        return true;
    }
    
    function checkSchedulingConflicts() {
        const date = $('#activity_date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        const venue = $('#venue').val();
        const teacherId = $('#teacher_id').val();
        const supervisorId = $('#supervisor_id').val();
        
        if (date && startTime && endTime && (venue || teacherId || supervisorId)) {
            // Simulate conflict checking (in real implementation, this would be an AJAX call)
            $('#conflict-warning').addClass('show');
            $('#conflict-message').html('<i class="fas fa-spinner fa-spin"></i> Checking for conflicts...');
            
            setTimeout(function() {
                // Simulate conflict check result
                const hasConflict = Math.random() < 0.3; // 30% chance of conflict for demo
                
                if (hasConflict) {
                    $('#conflict-warning').addClass('show').css('background', '#f8d7da').css('border-color', '#f5c6cb');
                    $('#conflict-message').html('<i class="fas fa-exclamation-triangle"></i> Scheduling conflict detected. Please choose a different time or venue.');
                } else {
                    $('#conflict-warning').addClass('show').css('background', '#d1edda').css('border-color', '#c3e6cb');
                    $('#conflict-message').html('<i class="fas fa-check-circle"></i> No conflicts detected. Schedule is available.');
                    
                    setTimeout(function() {
                        $('#conflict-warning').removeClass('show');
                    }, 2000);
                }
            }, 1000);
        } else {
            $('#conflict-warning').removeClass('show');
        }
    }
    
    function updatePreview() {
        $('#preview-name').text($('#activity_name').val() || '-');
        $('#preview-code').text($('#activity_code').val() || '-');
        $('#preview-type').text($('input[name="activity_type"]:checked').val() || '-');
        $('#preview-category').text($('#category_id option:selected').text() || '-');
        
        const date = $('#activity_date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        const datetime = date && startTime && endTime ? `${date} ${startTime} - ${endTime}` : '-';
        $('#preview-datetime').text(datetime);
        
        $('#preview-venue').text($('#venue').val() || '-');
        $('#preview-participants').text($('#max_participants').val() || '-');
        $('#preview-teacher').text($('#teacher_id option:selected').text() || '-');
        $('#preview-supervisor').text($('#supervisor_id option:selected').text() || 'Not assigned');
    }
    
    // Initialize activity type selection
    $('input[name="activity_type"]:checked').closest('.type-option').addClass('selected');
});
</script>
@endsection