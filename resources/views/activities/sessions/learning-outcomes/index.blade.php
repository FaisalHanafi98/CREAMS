@extends('layouts.app')

@section('title', 'Session Learning Outcomes Management')

@section('content')
<div class="container-fluid">
    <!-- Session Header -->
    <div class="session-learning-header mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start">
                            <div class="session-icon me-3">
                                <i class="fas fa-graduation-cap text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h2 class="mb-1">Session Learning Outcomes</h2>
                                <h4 class="text-primary mb-2">{{ $session->activity->activity_name }}</h4>
                                <div class="session-details">
                                    <span class="badge bg-info me-2">
                                        <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
                                    </span>
                                    <span class="badge bg-secondary me-2">
                                        <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                    </span>
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $session->venue }}
                                        @if($session->room_number) - Room {{ $session->room_number }} @endif
                                    </span>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-user-tie me-1"></i>{{ $session->teacher->name ?? 'Unassigned' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="session-stats">
                            <div class="stat-item mb-2">
                                <span class="stat-value">{{ $outcomeStats['total_outcomes'] }}</span>
                                <span class="stat-label">Assigned Outcomes</span>
                            </div>
                            <div class="stat-item mb-2">
                                <span class="stat-value">{{ $outcomeStats['total_trainees'] }}</span>
                                <span class="stat-label">Enrolled Trainees</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value">{{ number_format($outcomeStats['completion_rate'], 1) }}%</span>
                                <span class="stat-label">Assessment Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Learning Outcomes Assignment Panel -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list-check me-2"></i>Learning Outcomes Assignment
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="editOutcomeAssignment()">
                            <i class="fas fa-edit me-1"></i>Edit Assignment
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($sessionOutcomes) > 0)
                        <div class="assigned-outcomes">
                            @foreach($availableOutcomes->whereIn('id', $sessionOutcomes) as $outcome)
                                <div class="outcome-card mb-3" data-outcome-id="{{ $outcome->id }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="outcome-info">
                                                <h6 class="outcome-title mb-1">{{ $outcome->outcome_title }}</h6>
                                                <p class="outcome-description text-muted small mb-2">{{ $outcome->outcome_description }}</p>
                                                <div class="outcome-meta">
                                                    <span class="badge bg-info">{{ $outcome->competency_level }}</span>
                                                    <span class="badge bg-secondary">{{ $outcome->assessment_criteria }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="outcome-progress">
                                                @php
                                                    $progressData = $enrolledTrainees->flatMap(function($trainee) use ($outcome) {
                                                        return $trainee->session_progress[$outcome->id] ?? [];
                                                    });
                                                    $totalAssessed = $enrolledTrainees->filter(function($trainee) use ($outcome) {
                                                        return isset($trainee->session_progress[$outcome->id]);
                                                    })->count();
                                                    $completionRate = $enrolledTrainees->count() > 0 ? ($totalAssessed / $enrolledTrainees->count()) * 100 : 0;
                                                @endphp
                                                <div class="progress-stats text-center">
                                                    <div class="progress-circle mb-2" data-progress="{{ $completionRate }}">
                                                        <span>{{ number_format($completionRate) }}%</span>
                                                    </div>
                                                    <small class="text-muted">{{ $totalAssessed }}/{{ $enrolledTrainees->count() }} assessed</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-outcomes-assigned text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Learning Outcomes Assigned</h5>
                            <p class="text-muted">Assign learning outcomes to this session to start tracking trainee progress.</p>
                            <button class="btn btn-primary" onclick="editOutcomeAssignment()">
                                <i class="fas fa-plus me-1"></i>Assign Learning Outcomes
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Trainee Progress Tracking -->
            @if(count($sessionOutcomes) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Trainee Progress Tracking
                            </h5>
                            <button class="btn btn-light btn-sm" onclick="bulkUpdateProgress()">
                                <i class="fas fa-edit me-1"></i>Bulk Update
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="trainee-progress-list">
                            @foreach($enrolledTrainees as $trainee)
                                <div class="trainee-progress-card mb-3" data-trainee-id="{{ $trainee->id }}">
                                    <div class="trainee-header d-flex justify-content-between align-items-center mb-3">
                                        <div class="trainee-info">
                                            <h6 class="mb-0">{{ $trainee->trainee_name }}</h6>
                                            <small class="text-muted">ID: {{ $trainee->id }}</small>
                                        </div>
                                        <div class="trainee-stats">
                                            @php
                                                $assessedCount = count($trainee->session_progress ?? []);
                                                $totalCount = count($sessionOutcomes);
                                                $overallProgress = $totalCount > 0 ? ($assessedCount / $totalCount) * 100 : 0;
                                            @endphp
                                            <span class="badge bg-info">{{ $assessedCount }}/{{ $totalCount }} assessed</span>
                                            <span class="badge bg-{{ $overallProgress >= 80 ? 'success' : ($overallProgress >= 50 ? 'warning' : 'secondary') }}">
                                                {{ number_format($overallProgress) }}% complete
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="outcomes-progress-grid">
                                        @foreach($availableOutcomes->whereIn('id', $sessionOutcomes) as $outcome)
                                            @php
                                                $progress = $trainee->session_progress[$outcome->id] ?? null;
                                                $currentLevel = $progress['current_level'] ?? 'Not Started';
                                                $competencyScore = $progress['competency_score'] ?? 0;
                                            @endphp
                                            <div class="outcome-progress-item" data-outcome-id="{{ $outcome->id }}" data-trainee-id="{{ $trainee->id }}">
                                                <div class="outcome-mini-card">
                                                    <div class="outcome-mini-header">
                                                        <small class="fw-bold">{{ Str::limit($outcome->outcome_title, 25) }}</small>
                                                        <span class="competency-level badge bg-info">{{ $outcome->competency_level }}</span>
                                                    </div>
                                                    <div class="progress-controls mt-2">
                                                        <div class="row">
                                                            <div class="col-8">
                                                                <select class="form-control form-control-sm progress-selector" 
                                                                        data-trainee="{{ $trainee->id }}" 
                                                                        data-outcome="{{ $outcome->id }}"
                                                                        onchange="updateTraineeOutcomeProgress(this)">
                                                                    <option value="Not Started" {{ $currentLevel == 'Not Started' ? 'selected' : '' }}>Not Started</option>
                                                                    <option value="In Progress" {{ $currentLevel == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                                                    <option value="Achieved" {{ $currentLevel == 'Achieved' ? 'selected' : '' }}>Achieved</option>
                                                                    <option value="Mastered" {{ $currentLevel == 'Mastered' ? 'selected' : '' }}>Mastered</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <input type="number" class="form-control form-control-sm competency-score" 
                                                                       min="0" max="10" placeholder="0-10" 
                                                                       value="{{ $competencyScore }}"
                                                                       data-trainee="{{ $trainee->id }}" 
                                                                       data-outcome="{{ $outcome->id }}"
                                                                       onchange="updateCompetencyScore(this)"
                                                                       style="{{ in_array($currentLevel, ['In Progress', 'Achieved', 'Mastered']) ? '' : 'display: none;' }}">
                                                            </div>
                                                        </div>
                                                        <div class="progress-indicator mt-1" style="height: 4px;">
                                                            <div class="progress">
                                                                <div class="progress-bar {{ $this->getProgressBarClass($currentLevel) }}" 
                                                                     style="width: {{ $this->calculateProgressWidth($currentLevel, $competencyScore) }}%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="progress-actions text-center mt-4">
                            <button class="btn btn-success" onclick="saveAllProgress()">
                                <i class="fas fa-save me-1"></i>Save All Progress
                            </button>
                            <button class="btn btn-info ms-2" onclick="generateProgressReport()">
                                <i class="fas fa-chart-bar me-1"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Session Analytics & Tools -->
        <div class="col-lg-4">
            <!-- Quick Analytics -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Session Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="analytics-summary">
                        <div class="metric-item mb-3">
                            <div class="metric-header d-flex justify-content-between">
                                <span>Overall Progress</span>
                                <span>{{ number_format($outcomeStats['average_progress'], 1) }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: {{ $outcomeStats['average_progress'] }}%"></div>
                            </div>
                        </div>
                        
                        <div class="metric-item mb-3">
                            <div class="metric-header d-flex justify-content-between">
                                <span>Assessment Completion</span>
                                <span>{{ number_format($outcomeStats['completion_rate'], 1) }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: {{ $outcomeStats['completion_rate'] }}%"></div>
                            </div>
                        </div>

                        @if(count($sessionOutcomes) > 0)
                            <div class="metric-breakdown">
                                <h6 class="mb-2">Outcome Distribution</h6>
                                @foreach($availableOutcomes->whereIn('id', $sessionOutcomes) as $outcome)
                                    @php
                                        $outcomeAssessed = $enrolledTrainees->filter(function($trainee) use ($outcome) {
                                            return isset($trainee->session_progress[$outcome->id]);
                                        })->count();
                                        $outcomeRate = $enrolledTrainees->count() > 0 ? ($outcomeAssessed / $enrolledTrainees->count()) * 100 : 0;
                                    @endphp
                                    <div class="outcome-metric mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small">{{ Str::limit($outcome->outcome_title, 20) }}</span>
                                            <span class="small">{{ number_format($outcomeRate) }}%</span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $outcomeRate }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-outline-info btn-sm btn-block" onclick="viewDetailedAnalytics()">
                        <i class="fas fa-chart-line me-1"></i>View Detailed Analytics
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <button class="btn btn-outline-primary btn-block mb-2" onclick="exportProgressData()">
                            <i class="fas fa-download me-1"></i>Export Progress Data
                        </button>
                        <button class="btn btn-outline-success btn-block mb-2" onclick="sendProgressUpdates()">
                            <i class="fas fa-paper-plane me-1"></i>Send Updates to Parents
                        </button>
                        <button class="btn btn-outline-info btn-block mb-2" onclick="copyToNextSession()">
                            <i class="fas fa-copy me-1"></i>Copy to Next Session
                        </button>
                        <button class="btn btn-outline-secondary btn-block" onclick="viewSessionHistory()">
                            <i class="fas fa-history me-1"></i>View Session History
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Learning Outcome Assignment Modal -->
<div class="modal fade" id="outcomeAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-graduation-cap me-2"></i>Assign Learning Outcomes to Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="outcomeAssignmentForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Available Learning Outcomes</h6>
                            <div class="outcomes-selection">
                                @foreach($availableOutcomes as $outcome)
                                    <div class="outcome-selection-card mb-2" data-outcome-id="{{ $outcome->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="selected_outcomes[]" 
                                                   value="{{ $outcome->id }}" 
                                                   id="outcome_{{ $outcome->id }}"
                                                   {{ in_array($outcome->id, $sessionOutcomes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="outcome_{{ $outcome->id }}">
                                                <div class="outcome-details">
                                                    <h6 class="mb-1">{{ $outcome->outcome_title }}</h6>
                                                    <p class="text-muted small mb-1">{{ $outcome->outcome_description }}</p>
                                                    <div class="outcome-tags">
                                                        <span class="badge bg-info">{{ $outcome->competency_level }}</span>
                                                        <span class="badge bg-secondary">{{ $outcome->assessment_criteria }}</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="outcome-weight mt-2">
                                            <label class="form-label small">Weight (%)</label>
                                            <input type="number" class="form-control form-control-sm" 
                                                   name="outcome_weights[{{ $outcome->id }}]" 
                                                   min="0" max="100" value="25" placeholder="25">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="assignment-settings">
                                <h6>Session Settings</h6>
                                <div class="mb-3">
                                    <label class="form-label">Assessment Method</label>
                                    <select class="form-control" name="assessment_method" required>
                                        <option value="observation">Observation</option>
                                        <option value="practical">Practical Assessment</option>
                                        <option value="assessment">Formal Assessment</option>
                                        <option value="mixed">Mixed Methods</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Session Focus (Optional)</label>
                                    <textarea class="form-control" name="session_focus" rows="3" 
                                              placeholder="Describe the main focus of this session..."></textarea>
                                </div>
                                <div class="selection-summary">
                                    <h6>Selection Summary</h6>
                                    <div id="selectionCount" class="text-muted">0 outcomes selected</div>
                                    <div id="weightTotal" class="text-muted">Total weight: 0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveOutcomeAssignment()">
                    <i class="fas fa-save me-1"></i>Save Assignment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Session Learning Outcomes Styles */
.session-learning-header .session-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 12px;
}

.session-details .badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

.session-stats .stat-item {
    text-align: center;
}

.session-stats .stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.session-stats .stat-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
}

/* Outcome Cards */
.outcome-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.outcome-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
}

.outcome-title {
    color: #343a40;
    font-weight: 600;
}

.outcome-description {
    line-height: 1.4;
}

.progress-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0% var(--progress, 0%), #e9ecef var(--progress, 0%) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.progress-circle span {
    font-size: 0.8rem;
    font-weight: bold;
    color: #333;
    background: white;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

/* Trainee Progress Cards */
.trainee-progress-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.trainee-progress-card:hover {
    border-color: #28a745;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
}

.outcomes-progress-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.outcome-mini-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0.75rem;
}

.outcome-mini-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.competency-level {
    font-size: 0.7rem !important;
    padding: 0.2rem 0.4rem !important;
}

.progress-controls .form-control-sm {
    font-size: 0.8rem;
}

.progress-indicator .progress {
    height: 4px;
    border-radius: 2px;
}

/* Analytics */
.analytics-summary .metric-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.metric-header {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.outcome-metric {
    padding: 0.5rem;
    background: white;
    border-radius: 4px;
}

/* Modal Styles */
.outcome-selection-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.outcome-selection-card:hover {
    background: #e9ecef;
    border-color: #007bff;
}

.outcome-selection-card .form-check-input:checked ~ .form-check-label {
    color: #007bff;
}

.assignment-settings {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
}

.selection-summary {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    .outcomes-progress-grid {
        grid-template-columns: 1fr;
    }
    
    .session-stats {
        text-align: center;
        margin-top: 1rem;
    }
    
    .session-stats .stat-item {
        display: inline-block;
        margin: 0 1rem;
    }
    
    .trainee-header {
        flex-direction: column;
        text-align: center;
    }
    
    .trainee-stats {
        margin-top: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Session Learning Outcomes Management
let sessionId = {{ $session->id }};
let availableOutcomes = @json($availableOutcomes);
let currentSessionOutcomes = @json($sessionOutcomes);

document.addEventListener('DOMContentLoaded', function() {
    initializeProgressCircles();
    updateSelectionSummary();
});

// Initialize progress circles
function initializeProgressCircles() {
    document.querySelectorAll('.progress-circle').forEach(function(circle) {
        const progress = circle.getAttribute('data-progress');
        circle.style.setProperty('--progress', progress + '%');
    });
}

// Edit outcome assignment modal
function editOutcomeAssignment() {
    const modal = new bootstrap.Modal(document.getElementById('outcomeAssignmentModal'));
    modal.show();
    updateSelectionSummary();
}

// Update selection summary in modal
function updateSelectionSummary() {
    const checkboxes = document.querySelectorAll('input[name="selected_outcomes[]"]:checked');
    const weights = document.querySelectorAll('input[name^="outcome_weights"]');
    
    let totalWeight = 0;
    weights.forEach(function(weight) {
        if (weight.value) {
            totalWeight += parseInt(weight.value) || 0;
        }
    });
    
    document.getElementById('selectionCount').textContent = checkboxes.length + ' outcomes selected';
    document.getElementById('weightTotal').textContent = 'Total weight: ' + totalWeight + '%';
    document.getElementById('weightTotal').className = totalWeight > 100 ? 'text-danger' : 'text-muted';
}

// Listen for checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.name === 'selected_outcomes[]' || e.target.name.startsWith('outcome_weights')) {
        updateSelectionSummary();
    }
});

// Save outcome assignment
function saveOutcomeAssignment() {
    const form = document.getElementById('outcomeAssignmentForm');
    const formData = new FormData(form);
    
    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    saveBtn.disabled = true;
    
    fetch(`/activities/sessions/${sessionId}/learning-outcomes`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('outcomeAssignmentModal')).hide();
            
            // Show success message
            showNotification('Learning outcomes assigned successfully!', 'success');
            
            // Reload page to show updated assignments
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error assigning outcomes', 'error');
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

// Update individual trainee outcome progress
function updateTraineeOutcomeProgress(selectElement) {
    const traineeId = selectElement.getAttribute('data-trainee');
    const outcomeId = selectElement.getAttribute('data-outcome');
    const progressLevel = selectElement.value;
    
    // Show/hide competency score input
    const scoreInput = document.querySelector(`input[data-trainee="${traineeId}"][data-outcome="${outcomeId}"]`);
    if (progressLevel === 'In Progress' || progressLevel === 'Achieved' || progressLevel === 'Mastered') {
        scoreInput.style.display = 'block';
        
        // Auto-fill default scores
        if (!scoreInput.value) {
            const defaultScores = {
                'In Progress': 5,
                'Achieved': 7,
                'Mastered': 9
            };
            scoreInput.value = defaultScores[progressLevel] || 0;
        }
    } else {
        scoreInput.style.display = 'none';
        scoreInput.value = '';
    }
    
    // Update progress indicator
    updateProgressIndicator(traineeId, outcomeId, progressLevel, scoreInput.value);
}

// Update competency score
function updateCompetencyScore(scoreInput) {
    const traineeId = scoreInput.getAttribute('data-trainee');
    const outcomeId = scoreInput.getAttribute('data-outcome');
    const score = scoreInput.value;
    const selectElement = document.querySelector(`select[data-trainee="${traineeId}"][data-outcome="${outcomeId}"]`);
    const progressLevel = selectElement.value;
    
    updateProgressIndicator(traineeId, outcomeId, progressLevel, score);
}

// Update progress indicator
function updateProgressIndicator(traineeId, outcomeId, progressLevel, score) {
    const progressBar = document.querySelector(
        `.outcome-progress-item[data-trainee-id="${traineeId}"][data-outcome-id="${outcomeId}"] .progress-bar`
    );
    
    if (!progressBar) return;
    
    const levelWeights = {
        'Not Started': 0,
        'In Progress': 25,
        'Achieved': 70,
        'Mastered': 100
    };
    
    let progressWidth = levelWeights[progressLevel] || 0;
    
    // Adjust based on competency score
    if (score > 0) {
        const scorePercentage = (score / 10) * 100;
        progressWidth = Math.min(100, (progressWidth + scorePercentage) / 2);
    }
    
    progressBar.style.width = progressWidth + '%';
    
    // Update color based on progress level
    progressBar.className = 'progress-bar ' + getProgressBarClass(progressLevel);
}

// Get progress bar class based on level
function getProgressBarClass(level) {
    const classes = {
        'Not Started': 'bg-secondary',
        'In Progress': 'bg-warning',
        'Achieved': 'bg-success',
        'Mastered': 'bg-primary'
    };
    return classes[level] || 'bg-secondary';
}

// Save all progress
function saveAllProgress() {
    const progressData = [];
    
    // Collect all progress data
    document.querySelectorAll('.trainee-progress-card').forEach(function(card) {
        const traineeId = card.getAttribute('data-trainee-id');
        
        card.querySelectorAll('.outcome-progress-item').forEach(function(item) {
            const outcomeId = item.getAttribute('data-outcome-id');
            const progressSelector = item.querySelector('.progress-selector');
            const scoreInput = item.querySelector('.competency-score');
            
            if (progressSelector.value !== 'Not Started') {
                progressData.push({
                    trainee_id: traineeId,
                    outcome_id: outcomeId,
                    progress_level: progressSelector.value,
                    competency_score: scoreInput.value || null,
                    session_notes: null // Could add notes field later
                });
            }
        });
    });
    
    if (progressData.length === 0) {
        showNotification('No progress data to save', 'warning');
        return;
    }
    
    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    saveBtn.disabled = true;
    
    fetch(`/activities/sessions/${sessionId}/learning-outcomes/progress`, {
        method: 'POST',
        body: JSON.stringify({
            trainee_progress: progressData
        }),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Refresh page to show updated statistics
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error saving progress', 'error');
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

// Bulk update progress
function bulkUpdateProgress() {
    // Show bulk update modal (to be implemented)
    showNotification('Bulk update feature coming soon!', 'info');
}

// View detailed analytics
function viewDetailedAnalytics() {
    fetch(`/activities/sessions/${sessionId}/learning-outcomes/analytics`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show analytics modal (to be implemented)
                console.log('Analytics data:', data.analytics);
                showNotification('Analytics feature coming soon!', 'info');
            } else {
                showNotification('Error loading analytics', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Network error occurred', 'error');
        });
}

// Export progress data
function exportProgressData() {
    showNotification('Export feature coming soon!', 'info');
}

// Send progress updates to parents
function sendProgressUpdates() {
    showNotification('Parent notification feature coming soon!', 'info');
}

// Copy to next session
function copyToNextSession() {
    showNotification('Copy to next session feature coming soon!', 'info');
}

// View session history
function viewSessionHistory() {
    showNotification('Session history feature coming soon!', 'info');
}

// Generate progress report
function generateProgressReport() {
    showNotification('Progress report generation coming soon!', 'info');
}

// Show notification
function showNotification(message, type) {
    // Simple notification system (can be replaced with toast library)
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
</script>
@endpush

@php
// Helper methods for the view
function getProgressBarClass($level) {
    $classes = [
        'Not Started' => 'bg-secondary',
        'In Progress' => 'bg-warning',
        'Achieved' => 'bg-success',
        'Mastered' => 'bg-primary'
    ];
    return $classes[$level] ?? 'bg-secondary';
}

function calculateProgressWidth($level, $score) {
    $levelWeights = [
        'Not Started' => 0,
        'In Progress' => 25,
        'Achieved' => 70,
        'Mastered' => 100
    ];
    
    $baseWidth = $levelWeights[$level] ?? 0;
    
    if ($score > 0) {
        $scorePercentage = ($score / 10) * 100;
        return min(100, ($baseWidth + $scorePercentage) / 2);
    }
    
    return $baseWidth;
}
@endphp