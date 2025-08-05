@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Wizard Header -->
    <div class="wizard-header mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1"><i class="fas fa-magic text-primary"></i> Activity Creation Wizard</h2>
                                <p class="text-muted mb-0">Create comprehensive educational activities with integrated learning outcomes, schedules, and IEP goals</p>
                            </div>
                            <div>
                                <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Activities
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="wizard-steps mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="step-progress">
                            <div class="step-item active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Basic Information</div>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-item" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Learning Outcomes</div>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-item" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Schedule Configuration</div>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-item" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-label">Prerequisites</div>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-item" data-step="5">
                                <div class="step-number">5</div>
                                <div class="step-label">IEP Integration</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Form -->
    <form id="activityWizardForm" method="POST" action="{{ route('activities.wizard.store') }}">
        @csrf
        
        <!-- Step Navigation -->
        <div class="wizard-navigation mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <button type="button" id="prevStep" class="btn btn-outline-secondary" style="display: none;">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <div class="step-counter">
                            <span class="current-step">1</span> of <span class="total-steps">5</span>
                        </div>
                        <button type="button" id="nextStep" class="btn btn-primary">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Basic Information -->
        <div class="wizard-step active" data-step="1">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-info-circle"></i> Basic Activity Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="activity_name" class="form-label required">Activity Name</label>
                                        <input type="text" class="form-control" id="activity_name" name="activity_name" 
                                               placeholder="Enter descriptive activity name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="activity_description" class="form-label required">Activity Description</label>
                                        <textarea class="form-control" id="activity_description" name="activity_description" 
                                                rows="4" placeholder="Detailed description of the activity, its purpose, and benefits" required></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="category_id" class="form-label required">Category</label>
                                                <select class="form-control" id="category_id" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" data-type="{{ $category->category_type }}">
                                                            {{ $category->category_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="centre_id" class="form-label required">Centre</label>
                                                <select class="form-control" id="centre_id" name="centre_id" required>
                                                    <option value="">Select Centre</option>
                                                    @foreach($centres as $centre)
                                                        <option value="{{ $centre->centre_id }}" 
                                                                {{ session('centre_id') == $centre->centre_id ? 'selected' : '' }}>
                                                            {{ $centre->centre_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="activity_type" class="form-label required">Activity Type</label>
                                                <select class="form-control" id="activity_type" name="activity_type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Individual">Individual</option>
                                                    <option value="Group">Group</option>
                                                    <option value="Both">Both</option>
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="difficulty_level" class="form-label required">Difficulty Level</label>
                                                <select class="form-control" id="difficulty_level" name="difficulty_level" required>
                                                    <option value="">Select Level</option>
                                                    <option value="Beginner">Beginner</option>
                                                    <option value="Intermediate">Intermediate</option>
                                                    <option value="Advanced">Advanced</option>
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="age_group" class="form-label required">Age Group</label>
                                                <input type="text" class="form-control" id="age_group" name="age_group" 
                                                       placeholder="e.g., 6-12 years" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="min_participants" class="form-label required">Min Participants</label>
                                                <input type="number" class="form-control" id="min_participants" name="min_participants" 
                                                       min="1" max="50" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="max_participants" class="form-label required">Max Participants</label>
                                                <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                                       min="1" max="50" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="duration_minutes" class="form-label required">Duration (Minutes)</label>
                                                <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                                                       min="15" max="480" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="category-preview">
                                        <h5>Category Information</h5>
                                        <div id="categoryPreview" class="alert alert-info" style="display: none;">
                                            <div class="category-icon mb-2"></div>
                                            <div class="category-name font-weight-bold"></div>
                                            <div class="category-description"></div>
                                            <div class="category-type badge mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Learning Outcomes -->
        <div class="wizard-step" data-step="2" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-graduation-cap"></i> Learning Outcomes & Competencies</h4>
                        </div>
                        <div class="card-body">
                            <div class="learning-outcomes-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="mb-0">Define what trainees will achieve through this activity</p>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="addOutcome">
                                        <i class="fas fa-plus"></i> Add Learning Outcome
                                    </button>
                                </div>
                                
                                <div id="learningOutcomes">
                                    <!-- Learning outcomes will be added dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Schedule Configuration -->
        <div class="wizard-step" data-step="3" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning text-white">
                            <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Schedule Configuration</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Schedule Type</label>
                                        <div class="btn-group-toggle d-flex" data-toggle="buttons">
                                            <label class="btn btn-outline-primary active flex-fill">
                                                <input type="radio" name="schedule_type" value="template" checked> Use Template
                                            </label>
                                            <label class="btn btn-outline-primary flex-fill">
                                                <input type="radio" name="schedule_type" value="custom"> Custom Schedule
                                            </label>
                                        </div>
                                    </div>

                                    <div id="templateSchedule">
                                        <div class="form-group mb-3">
                                            <label for="template_id" class="form-label">Schedule Template</label>
                                            <select class="form-control" id="template_id" name="template_id">
                                                <option value="">Select Template</option>
                                                @foreach($scheduleTemplates as $template)
                                                    <option value="{{ $template->id }}">
                                                        {{ $template->template_name }} ({{ $template->sessions_per_week }}x/week for {{ $template->duration_weeks }} weeks)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div id="customSchedule" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="sessions_per_week" class="form-label">Sessions per Week</label>
                                                    <input type="number" class="form-control" id="sessions_per_week" 
                                                           name="sessions_per_week" min="1" max="7">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="session_length_minutes" class="form-label">Session Length (Minutes)</label>
                                                    <input type="number" class="form-control" id="session_length_minutes" 
                                                           name="session_length_minutes" min="15" max="480">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Days of Week</label>
                                            <div class="days-of-week">
                                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" name="days_of_week[]" 
                                                               value="{{ $day }}" id="day_{{ strtolower($day) }}">
                                                        <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                                            {{ $day }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="session_time" class="form-label">Session Time</label>
                                            <input type="time" class="form-control" id="session_time" name="session_time">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="start_date" class="form-label required">Start Date</label>
                                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="end_date" class="form-label required">End Date</label>
                                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                <label for="venue" class="form-label required">Venue</label>
                                                <input type="text" class="form-control" id="venue" name="venue" 
                                                       placeholder="Activity venue/location" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="room_number" class="form-label">Room Number</label>
                                                <input type="text" class="form-control" id="room_number" name="room_number" 
                                                       placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div id="templatePreview" class="alert alert-info" style="display: none;">
                                        <h5>Template Preview</h5>
                                        <div class="template-details"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Prerequisites -->
        <div class="wizard-step" data-step="4" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0"><i class="fas fa-link"></i> Prerequisites & Dependencies</h4>
                        </div>
                        <div class="card-body">
                            <div class="prerequisites-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="mb-0">Define activities that trainees must complete before joining this activity</p>
                                    <button type="button" class="btn btn-outline-info btn-sm" id="addPrerequisite">
                                        <i class="fas fa-plus"></i> Add Prerequisite
                                    </button>
                                </div>
                                
                                <div id="prerequisites">
                                    <!-- Prerequisites will be added dynamically -->
                                </div>
                                
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle"></i> Prerequisites are optional but help ensure trainees are properly prepared for this activity.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: IEP Integration -->
        <div class="wizard-step" data-step="5" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-purple text-white">
                            <h4 class="mb-0"><i class="fas fa-user-graduate"></i> Individual Education Plan Integration</h4>
                        </div>
                        <div class="card-body">
                            <div class="iep-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <p class="mb-1">Map this activity to Individual Education Plan goals</p>
                                        <small class="text-muted">This helps track progress toward individual trainee goals</small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="getSuggestions">
                                            <i class="fas fa-lightbulb"></i> Get Suggestions
                                        </button>
                                        <button type="button" class="btn btn-outline-purple btn-sm" id="addIepGoal">
                                            <i class="fas fa-plus"></i> Add IEP Goal
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="iepGoals">
                                    <!-- IEP goals will be added dynamically -->
                                </div>
                                
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle"></i> IEP goal mapping is optional but highly recommended for comprehensive progress tracking.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Step: Review & Submit -->
        <div class="wizard-step" data-step="6" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0"><i class="fas fa-check-circle"></i> Review & Create Activity</h4>
                        </div>
                        <div class="card-body">
                            <div id="reviewSummary">
                                <!-- Review summary will be populated by JavaScript -->
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-magic"></i> Create Activity
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
/* Wizard Styles */
.step-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 20px;
}

.step-item {
    text-align: center;
    position: relative;
    flex: 0 0 auto;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.step-item.active .step-number {
    background-color: #007bff;
    color: white;
}

.step-item.completed .step-number {
    background-color: #28a745;
    color: white;
}

.step-label {
    font-size: 12px;
    color: #6c757d;
    white-space: nowrap;
}

.step-item.active .step-label {
    color: #007bff;
    font-weight: bold;
}

.step-connector {
    flex: 1;
    height: 2px;
    background-color: #e9ecef;
    margin: 0 15px;
    position: relative;
    top: -20px;
}

.step-item.completed + .step-connector {
    background-color: #28a745;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
}

.btn-outline-purple:hover {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}

/* Learning Outcome Cards */
.learning-outcome-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
}

/* Prerequisite Cards */
.prerequisite-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
}

/* IEP Goal Cards */
.iep-goal-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
}

.wizard-navigation {
    position: sticky;
    top: 20px;
    z-index: 100;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/activity-wizard.js') }}"></script>
<script>
// Pass server data to JavaScript
window.availableActivities = @json($availableActivities);
window.activeIepPlans = @json($activeIepPlans);
window.categories = @json($categories);
window.scheduleTemplates = @json($scheduleTemplates);
</script>
@endpush
@endsection