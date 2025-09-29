@extends('layouts.app')

@section('title', 'Mark Session Attendance')

@section('content')
<div class="container-fluid">
    <!-- Session Header -->
    <div class="session-header mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="mb-2">
                                    <i class="fas fa-clipboard-check text-primary"></i> 
                                    Mark Attendance
                                </h2>
                                <h4 class="text-primary">{{ $session->activity->activity_name }}</h4>
                                <div class="session-details mt-2">
                                    <span class="badge badge-info me-2">
                                        <i class="fas fa-tag"></i> {{ $session->activity->category->category_name ?? 'Uncategorized' }}
                                    </span>
                                    <span class="badge badge-secondary me-2">
                                        <i class="fas fa-clock"></i> 
                                        {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                    </span>
                                    <span class="badge badge-success me-2">
                                        <i class="fas fa-map-marker-alt"></i> {{ $session->venue }}
                                        @if($session->room_number) - Room {{ $session->room_number }} @endif
                                    </span>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-user-tie"></i> {{ $session->teacher->name ?? 'Unassigned' }}
                                    </span>
                                </div>
                            </div>
                            <div class="session-actions">
                                <a href="{{ route('centre.attendance.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('centre.attendance.store-session', $session->id) }}" id="attendanceForm">
        @csrf
        
        <div class="row">
            <!-- Attendance Marking Panel -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users"></i> 
                            Enrolled Trainees ({{ $enrolledTrainees->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($enrolledTrainees->count() > 0)
                            <div class="attendance-controls mb-3">
                                <button type="button" class="btn btn-success btn-sm" onclick="markAllPresent()">
                                    <i class="fas fa-check-circle"></i> Mark All Present
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" onclick="markAllAbsent()">
                                    <i class="fas fa-times-circle"></i> Mark All Absent
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="toggleAllDetails()">
                                    <i class="fas fa-expand"></i> Toggle Details
                                </button>
                            </div>
                            
                            <div class="attendance-list">
                                @foreach($enrolledTrainees as $index => $enrollment)
                                    <div class="attendance-card mb-3" data-trainee="{{ $enrollment->trainee->id }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <div class="trainee-info">
                                                    <div class="d-flex align-items-center">
                                                        <div class="trainee-avatar me-2">
                                                            {{ substr($enrollment->trainee->trainee_name, 0, 2) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $enrollment->trainee->trainee_name }}</h6>
                                                            <small class="text-muted">ID: {{ $enrollment->trainee->id }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="attendance-status">
                                                    <select class="form-control form-control-sm attendance-select" 
                                                            name="attendance[{{ $index }}][status]" 
                                                            data-trainee="{{ $enrollment->trainee->id }}" required>
                                                        <option value="">Select Status</option>
                                                        <option value="present">Present</option>
                                                        <option value="absent">Absent</option>
                                                        <option value="late">Late</option>
                                                        <option value="excused">Excused</option>
                                                    </select>
                                                    <input type="hidden" name="attendance[{{ $index }}][trainee_id]" 
                                                           value="{{ $enrollment->trainee->id }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="participation-score" style="display: none;">
                                                    <label class="form-label">Participation (0-10)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="attendance[{{ $index }}][participation_score]" 
                                                           min="0" max="10" placeholder="Score">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-outline-info btn-sm details-toggle" 
                                                        onclick="toggleDetails({{ $enrollment->trainee->id }})">
                                                    <i class="fas fa-chevron-down"></i> Details
                                                </button>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="attendance-indicator">
                                                    <i class="fas fa-circle text-muted status-indicator"></i>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Detailed Section (Hidden by default) -->
                                        <div class="attendance-details" style="display: none;" data-trainee="{{ $enrollment->trainee->id }}">
                                            <hr class="my-2">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Session Notes</label>
                                                        <textarea class="form-control form-control-sm" 
                                                                name="attendance[{{ $index }}][notes]" 
                                                                rows="2" placeholder="Any notes about this trainee's participation..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="learning-progress">
                                                        <label class="form-label">Real-time Learning Progress</label>
                                                        @foreach($learningOutcomes->take(2) as $outcome)
                                                            <div class="learning-outcome-assessment mb-2" data-outcome-id="{{ $outcome->id }}" data-trainee-id="{{ $enrollment->trainee->id }}">
                                                                <div class="outcome-header d-flex justify-content-between align-items-center mb-1">
                                                                    <small class="fw-bold">{{ Str::limit($outcome->outcome_title, 20) }}</small>
                                                                    <span class="competency-badge badge bg-info">{{ $outcome->competency_level }}</span>
                                                                </div>
                                                                <div class="progress-assessment-row d-flex align-items-center gap-2">
                                                                    <select class="form-control form-control-sm progress-selector" 
                                                                            name="learning_progress[{{ $enrollment->trainee->id }}_{{ $outcome->id }}][progress_level]"
                                                                            onchange="updateCompetencyScore(this, {{ $enrollment->trainee->id }}, {{ $outcome->id }})">
                                                                        <option value="">Not Assessed</option>
                                                                        <option value="Not Started">Not Started</option>
                                                                        <option value="In Progress">In Progress</option>
                                                                        <option value="Achieved">Achieved</option>
                                                                        <option value="Mastered">Mastered</option>
                                                                    </select>
                                                                    <input type="number" class="form-control form-control-sm competency-score-input" 
                                                                           name="learning_progress[{{ $enrollment->trainee->id }}_{{ $outcome->id }}][competency_score]"
                                                                           min="0" max="10" placeholder="0-10" style="width: 60px; display: none;"
                                                                           onchange="updateProgressIndicator(this, {{ $enrollment->trainee->id }}, {{ $outcome->id }})">
                                                                    <div class="progress-indicator" style="width: 30px; height: 20px; display: none;">
                                                                        <div class="progress" style="height: 100%;">
                                                                            <div class="progress-bar bg-success" style="width: 0%" data-trainee="{{ $enrollment->trainee->id }}" data-outcome="{{ $outcome->id }}"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" 
                                                                       name="learning_progress[{{ $enrollment->trainee->id }}_{{ $outcome->id }}][trainee_id]" 
                                                                       value="{{ $enrollment->trainee->id }}">
                                                                <input type="hidden" 
                                                                       name="learning_progress[{{ $enrollment->trainee->id }}_{{ $outcome->id }}][outcome_id]" 
                                                                       value="{{ $outcome->id }}">
                                                            </div>
                                                        @endforeach
                                                        @if($learningOutcomes->count() > 2)
                                                            <button type="button" class="btn btn-link btn-sm p-0" onclick="showAllOutcomes({{ $enrollment->trainee->id }})">
                                                                <i class="fas fa-plus-circle me-1"></i>View All {{ $learningOutcomes->count() }} Outcomes
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-times text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No Enrolled Trainees</h4>
                                <p class="text-muted">No trainees are enrolled in this session.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Session Information & Actions -->
            <div class="col-lg-4">
                <!-- Session Summary -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> 
                            Session Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="session-stats">
                            <div class="stat-item mb-2">
                                <strong>Duration:</strong> 
                                {{ \Carbon\Carbon::parse($session->start_time)->diffInMinutes(\Carbon\Carbon::parse($session->end_time)) }} minutes
                            </div>
                            <div class="stat-item mb-2">
                                <strong>Enrolled:</strong> {{ $enrolledTrainees->count() }} trainees
                            </div>
                            <div class="stat-item mb-2">
                                <strong>Capacity:</strong> {{ $session->activity->max_participants }}
                            </div>
                            <div class="stat-item mb-2">
                                <strong>Difficulty:</strong> 
                                <span class="badge badge-secondary">{{ $session->activity->difficulty_level }}</span>
                            </div>
                            @if($session->activity->activity_description)
                                <div class="stat-item">
                                    <strong>Description:</strong> 
                                    <small class="text-muted">{{ Str::limit($session->activity->activity_description, 100) }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Learning Outcomes -->
                @if($learningOutcomes->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-graduation-cap"></i> 
                                Learning Outcomes
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($learningOutcomes as $outcome)
                                <div class="outcome-item mb-2">
                                    <h6 class="mb-1">{{ $outcome->outcome_title }}</h6>
                                    <small class="text-muted">{{ $outcome->outcome_description }}</small>
                                    <span class="badge badge-info badge-sm">{{ $outcome->competency_level }}</span>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- IEP Goals -->
                @if($iepGoals->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-target"></i> 
                                Related IEP Goals
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($iepGoals as $goal)
                                <div class="iep-goal-item mb-2">
                                    <h6 class="mb-1">{{ $goal->iep->trainee->trainee_name }}</h6>
                                    <small class="text-muted">{{ $goal->goal_description }}</small>
                                    <div class="mt-1">
                                        <small class="text-info">Target: {{ \Carbon\Carbon::parse($goal->target_completion_date)->format('M j, Y') }}</small>
                                    </div>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Session Notes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note"></i>
                            Session Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="session-notes-section">
                            <div class="mb-3">
                                <label for="session_notes" class="form-label">Notes for this session:</label>
                                <textarea class="form-control"
                                          name="session_notes"
                                          id="session_notes"
                                          rows="4"
                                          placeholder="Add notes about this session (activities, observations, issues, achievements, etc.)"
                                          maxlength="1000">{{ $session->session_notes ?? '' }}</textarea>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <span id="notesCounter">{{ strlen($session->session_notes ?? '') }}</span>/1000 characters
                                    </small>
                                </div>
                            </div>
                            <div class="notes-actions">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="saveSessionNotes()">
                                    <i class="fas fa-save"></i> Save Notes Only
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSessionNotes()">
                                    <i class="fas fa-eraser"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-save"></i> Save Attendance & Notes
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="previewAttendance()">
                                <i class="fas fa-eye"></i> Preview Before Save
                            </button>
                        </div>

                        <div class="attendance-summary mt-3" id="attendanceSummary" style="display: none;">
                            <h6>Attendance Summary:</h6>
                            <div class="summary-stats">
                                <small>Present: <span id="presentCount">0</span></small><br>
                                <small>Absent: <span id="absentCount">0</span></small><br>
                                <small>Late: <span id="lateCount">0</span></small><br>
                                <small>Excused: <span id="excusedCount">0</span></small>
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
.attendance-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.attendance-card.present {
    border-color: #28a745;
    background-color: #f8fff9;
}

.attendance-card.absent {
    border-color: #dc3545;
    background-color: #fff8f8;
}

.attendance-card.late {
    border-color: #ffc107;
    background-color: #fffbf0;
}

.attendance-card.excused {
    border-color: #6c757d;
    background-color: #f8f9fa;
}

.trainee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    text-transform: uppercase;
}

.status-indicator.present {
    color: #28a745 !important;
}

.status-indicator.absent {
    color: #dc3545 !important;
}

.status-indicator.late {
    color: #ffc107 !important;
}

.status-indicator.excused {
    color: #6c757d !important;
}

.attendance-controls {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 15px;
}

.session-details .badge {
    margin-right: 8px;
    margin-bottom: 4px;
}

.learning-progress select {
    width: 120px;
    font-size: 11px;
}

.outcome-item, .iep-goal-item {
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

/* Real-time Learning Progress Enhancements */
.learning-outcome-assessment {
    background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
    border: 1px solid #d4edda;
    border-radius: 6px;
    padding: 8px;
    transition: all 0.3s ease;
}

.learning-outcome-assessment:hover {
    border-color: #28a745;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
}

.outcome-header {
    margin-bottom: 6px;
}

.competency-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    transition: all 0.3s ease;
}

.progress-assessment-row {
    align-items: center;
}

.progress-selector {
    flex: 1;
    margin-right: 8px;
}

.competency-score-input {
    transition: all 0.3s ease;
    border-color: #28a745;
}

.competency-score-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.progress-indicator {
    transition: all 0.3s ease;
}

.progress-indicator .progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.progress-indicator .progress-bar {
    transition: width 0.5s ease-in-out;
    border-radius: 10px;
}

/* Enhanced Modal Styles */
.outcome-assessment-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-left: 4px solid #28a745;
    transition: transform 0.2s ease;
}

.outcome-assessment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.outcome-title h6 {
    color: #155724;
    margin-bottom: 0.25rem;
}

.assessment-controls {
    margin-top: 12px;
}

.assessment-controls .form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.assessment-controls .form-control {
    border-radius: 4px;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.assessment-controls .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Learning Progress Enhancements */
.learning-progress {
    background: rgba(40, 167, 69, 0.05);
    border-radius: 8px;
    padding: 12px;
    border: 1px solid rgba(40, 167, 69, 0.1);
}

.learning-progress .form-label {
    color: #155724;
    font-weight: 600;
    margin-bottom: 8px;
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .learning-outcome-assessment {
        padding: 6px;
        margin-bottom: 8px;
    }
    
    .outcome-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .competency-badge {
        margin-top: 4px;
    }
    
    .progress-assessment-row {
        flex-direction: column;
        gap: 6px;
    }
    
    .progress-selector {
        margin-right: 0;
        margin-bottom: 6px;
    }
    
    .competency-score-input {
        width: 100% !important;
    }
    
    .progress-indicator {
        width: 100% !important;
        height: 15px !important;
    }
    
    .assessment-controls {
        margin-top: 8px;
    }
    
    .assessment-controls .col-md-6 {
        margin-bottom: 8px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Attendance marking functionality
function markAllPresent() {
    $('.attendance-select').val('present').trigger('change');
}

function markAllAbsent() {
    $('.attendance-select').val('absent').trigger('change');
}

function toggleAllDetails() {
    $('.attendance-details').toggle();
    $('.details-toggle i').toggleClass('fa-chevron-down fa-chevron-up');
}

function toggleDetails(traineeId) {
    const details = $(`.attendance-details[data-trainee="${traineeId}"]`);
    const icon = $(`.details-toggle[onclick="toggleDetails(${traineeId})"] i`);
    
    details.toggle();
    icon.toggleClass('fa-chevron-down fa-chevron-up');
}

function previewAttendance() {
    updateAttendanceSummary();
    $('#attendanceSummary').show();
}

function updateAttendanceSummary() {
    const counts = {present: 0, absent: 0, late: 0, excused: 0};
    
    $('.attendance-select').each(function() {
        const status = $(this).val();
        if (status && counts.hasOwnProperty(status)) {
            counts[status]++;
        }
    });
    
    $('#presentCount').text(counts.present);
    $('#absentCount').text(counts.absent);
    $('#lateCount').text(counts.late);
    $('#excusedCount').text(counts.excused);
}

// Handle attendance status changes
$(document).on('change', '.attendance-select', function() {
    const status = $(this).val();
    const traineeId = $(this).data('trainee');
    const card = $(`.attendance-card[data-trainee="${traineeId}"]`);
    const indicator = card.find('.status-indicator');
    const participationScore = card.find('.participation-score');
    
    // Update card appearance
    card.removeClass('present absent late excused');
    indicator.removeClass('present absent late excused text-muted');
    
    if (status) {
        card.addClass(status);
        indicator.addClass(status);
        
        // Show participation score for present/late attendees
        if (status === 'present' || status === 'late') {
            participationScore.show();
        } else {
            participationScore.hide();
        }
    } else {
        indicator.addClass('text-muted');
        participationScore.hide();
    }
    
    // Auto-update summary
    updateAttendanceSummary();
});

// Form submission validation
$('#attendanceForm').on('submit', function(e) {
    const unmarkedAttendance = $('.attendance-select').filter(function() {
        return $(this).val() === '';
    });
    
    if (unmarkedAttendance.length > 0) {
        e.preventDefault();
        alert(`Please mark attendance for all ${unmarkedAttendance.length} trainees before submitting.`);
        unmarkedAttendance.first().focus();
        return false;
    }
    
    // Show loading state
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
});

// Real-time Learning Progress Functions
function updateCompetencyScore(selectElement, traineeId, outcomeId) {
    const selectedProgress = selectElement.value;
    const assessmentDiv = selectElement.closest('.learning-outcome-assessment');
    const scoreInput = assessmentDiv.querySelector('.competency-score-input');
    const progressIndicator = assessmentDiv.querySelector('.progress-indicator');
    
    // Show/hide competency score input based on progress level
    if (selectedProgress === 'In Progress' || selectedProgress === 'Achieved' || selectedProgress === 'Mastered') {
        scoreInput.style.display = 'block';
        progressIndicator.style.display = 'block';
        
        // Auto-fill default scores based on progress level
        if (selectedProgress === 'In Progress' && !scoreInput.value) {
            scoreInput.value = 5;
        } else if (selectedProgress === 'Achieved' && !scoreInput.value) {
            scoreInput.value = 7;
        } else if (selectedProgress === 'Mastered' && !scoreInput.value) {
            scoreInput.value = 9;
        }
        
        // Update progress indicator
        updateProgressIndicator(scoreInput, traineeId, outcomeId);
    } else {
        scoreInput.style.display = 'none';
        progressIndicator.style.display = 'none';
        scoreInput.value = '';
    }
    
    // Update competency badge color based on progress
    const competencyBadge = assessmentDiv.querySelector('.competency-badge');
    competencyBadge.className = 'competency-badge badge bg-' + getProgressBadgeClass(selectedProgress);
}

function updateProgressIndicator(scoreInput, traineeId, outcomeId) {
    const score = parseInt(scoreInput.value) || 0;
    const progressBar = document.querySelector(`[data-trainee="${traineeId}"][data-outcome="${outcomeId}"]`);
    
    if (progressBar) {
        const percentage = Math.min((score / 10) * 100, 100);
        progressBar.style.width = percentage + '%';
        
        // Update color based on score
        progressBar.className = 'progress-bar ' + getScoreProgressClass(score);
    }
}

function getProgressBadgeClass(progress) {
    const map = {
        'Not Started': 'secondary',
        'In Progress': 'warning',
        'Achieved': 'success',
        'Mastered': 'primary'
    };
    return map[progress] || 'info';
}

function getScoreProgressClass(score) {
    if (score >= 8) return 'bg-success';
    if (score >= 6) return 'bg-info';
    if (score >= 4) return 'bg-warning';
    return 'bg-danger';
}

function showAllOutcomes(traineeId) {
    // Create modal to show all learning outcomes for this trainee
    const modal = $(`
        <div class="modal fade" id="allOutcomesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-graduation-cap me-2"></i>All Learning Outcomes Assessment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading learning outcomes...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" onclick="saveAllProgressUpdates(${traineeId})">
                            <i class="fas fa-save me-1"></i>Save All Progress
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(modal);
    $('#allOutcomesModal').modal('show');
    
    // Load all outcomes via AJAX
    fetch(`/sessions/{{ $session->id }}/learning-outcomes/${traineeId}`)
        .then(response => response.json())
        .then(data => {
            let outcomesHtml = '<div class="all-outcomes-grid">';
            
            data.outcomes.forEach(outcome => {
                outcomesHtml += `
                    <div class="outcome-assessment-card mb-3 p-3 border rounded" data-outcome-id="${outcome.id}">
                        <div class="outcome-title mb-2">
                            <h6 class="fw-bold">${outcome.outcome_title}</h6>
                            <small class="text-muted">${outcome.outcome_description}</small>
                        </div>
                        <div class="assessment-controls row">
                            <div class="col-md-6">
                                <label class="form-label small">Progress Level</label>
                                <select class="form-control form-control-sm" 
                                        onchange="updateModalCompetencyScore(this, ${traineeId}, ${outcome.id})">
                                    <option value="">Not Assessed</option>
                                    <option value="Not Started">Not Started</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Achieved">Achieved</option>
                                    <option value="Mastered">Mastered</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Competency Score (0-10)</label>
                                <input type="number" class="form-control form-control-sm" 
                                       min="0" max="10" placeholder="Score">
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-secondary" style="width: 0%"></div>
                        </div>
                    </div>
                `;
            });
            
            outcomesHtml += '</div>';
            $('#allOutcomesModal .modal-body').html(outcomesHtml);
        })
        .catch(error => {
            $('#allOutcomesModal .modal-body').html(`
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <p>Error loading learning outcomes. Please try again.</p>
                </div>
            `);
        });
}

function updateModalCompetencyScore(selectElement, traineeId, outcomeId) {
    const selectedProgress = selectElement.value;
    const card = selectElement.closest('.outcome-assessment-card');
    const scoreInput = card.querySelector('input[type="number"]');
    const progressBar = card.querySelector('.progress-bar');
    
    // Auto-fill default scores and update progress
    if (selectedProgress === 'In Progress') {
        scoreInput.value = 5;
    } else if (selectedProgress === 'Achieved') {
        scoreInput.value = 7;
    } else if (selectedProgress === 'Mastered') {
        scoreInput.value = 9;
    } else {
        scoreInput.value = '';
    }
    
    // Update progress bar
    const score = parseInt(scoreInput.value) || 0;
    const percentage = Math.min((score / 10) * 100, 100);
    progressBar.style.width = percentage + '%';
    progressBar.className = 'progress-bar ' + getScoreProgressClass(score);
}

function saveAllProgressUpdates(traineeId) {
    // This would save all progress updates via AJAX
    alert('All progress updates would be saved here. Feature coming soon!');
    $('#allOutcomesModal').modal('hide');
}

// Session Notes Functions
function saveSessionNotes() {
    const notes = $('#session_notes').val();
    const sessionId = {{ $session->id }};

    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;

    fetch(`/sessions/${sessionId}/notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            session_notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Session notes saved successfully!', 'success');
        } else {
            showNotification(data.message || 'Error saving session notes', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Network error occurred', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function clearSessionNotes() {
    if (confirm('Are you sure you want to clear all session notes?')) {
        $('#session_notes').val('');
        updateNotesCounter();
    }
}

function updateNotesCounter() {
    const notes = $('#session_notes').val();
    $('#notesCounter').text(notes.length);
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      type === 'warning' ? 'alert-warning' : 'alert-info';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Initialize
$(document).ready(function() {
    updateAttendanceSummary();

    // Initialize any existing progress indicators
    $('.progress-selector').each(function() {
        if ($(this).val()) {
            const traineeId = $(this).closest('.learning-outcome-assessment').data('trainee-id');
            const outcomeId = $(this).closest('.learning-outcome-assessment').data('outcome-id');
            updateCompetencyScore(this, traineeId, outcomeId);
        }
    });

    // Initialize session notes counter
    updateNotesCounter();

    // Update notes counter on input
    $('#session_notes').on('input', updateNotesCounter);
});
</script>
@endpush
@endsection