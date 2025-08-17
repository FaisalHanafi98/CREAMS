@extends('layouts.app')

@section('title', 'Activity Schedule Repository')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt me-2"></i>Activity Schedule Repository
            </h1>
            <p class="mb-0 text-muted">Complete archive of all activity sessions across all centres</p>
        </div>
        <div class="text-end">
            <span class="badge bg-info fs-6">{{ $sessions->total() }} Total Sessions</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('activities.schedule') }}" id="scheduleFilters">
                <div class="row g-3 align-items-end">
                    <!-- Search Type & Value -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">SEARCH BY</label>
                        <div class="input-group">
                            <select name="search_type" class="form-select form-select-sm" style="flex: 0 0 auto; width: 120px;">
                                <option value="activity" {{ request('search_type') == 'activity' ? 'selected' : '' }}>Activity</option>
                                <option value="staff" {{ request('search_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="trainee" {{ request('search_type') == 'trainee' ? 'selected' : '' }}>Trainee</option>
                                <option value="room" {{ request('search_type') == 'room' ? 'selected' : '' }}>Room</option>
                            </select>
                            <input type="text" name="search_value" class="form-control form-control-sm" 
                                   placeholder="Enter search term..." 
                                   value="{{ request('search_value') }}">
                        </div>
                    </div>

                    <!-- Centre Filter -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">CENTRE</label>
                        <select name="centre" class="form-select form-select-sm">
                            <option value="">All Centres</option>
                            @foreach($centres as $centre)
                                <option value="{{ $centre->centre_id }}" {{ request('centre') == $centre->centre_id ? 'selected' : '' }}>
                                    {{ $centre->centre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">CATEGORY</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">STATUS</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="future" {{ request('status') == 'future' ? 'selected' : '' }}>Future</option>
                            <option value="progress" {{ request('status') == 'progress' ? 'selected' : '' }}>Progress</option>
                            <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">DATE RANGE</label>
                        <select name="date_range" class="form-select form-select-sm">
                            <option value="">All Dates</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>Past Sessions</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <!-- Clear Filters -->
                @if(request()->hasAny(['search_type', 'search_value', 'centre', 'category', 'status', 'date_range']))
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('activities.schedule') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Clear All Filters
                            </a>
                            <span class="ms-2 small text-muted">
                                Showing {{ $sessions->count() }} of {{ $sessions->total() }} sessions
                            </span>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($sessions->count() > 0)
                <div class="table-responsive">
                    <div class="sessions-list">
                        @foreach($sessions as $session)
                            <div class="session-item border-bottom">
                                <div class="row align-items-center py-3 px-4">
                                    <!-- Activity Info -->
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-start">
                                            <div class="activity-color-indicator me-3" 
                                                 style="background-color: {{ $session->color_code }}; width: 4px; height: 40px; border-radius: 2px;"></div>
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark">{{ $session->activity->activity_name ?? 'Unknown Activity' }}</h6>
                                                <div class="activity-badges mb-1">
                                                    <span class="badge bg-light text-dark border small">
                                                        {{ $session->activity->activity_type ?? 'General' }}
                                                    </span>
                                                    @if($session->activity->difficulty_level)
                                                        <span class="badge bg-info text-white small ms-1">
                                                            <i class="fas fa-layer-group me-1"></i>{{ $session->activity->difficulty_level }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Learning Outcomes Indicator -->
                                                @if($session->activity->learning_outcomes && $session->activity->learning_outcomes->count() > 0)
                                                    <div class="learning-outcomes-preview">
                                                        <small class="text-muted">
                                                            <i class="fas fa-graduation-cap me-1"></i>
                                                            {{ $session->activity->learning_outcomes->count() }} Learning Outcomes
                                                        </small>
                                                        <div class="outcomes-quick-view mt-1">
                                                            @foreach($session->activity->learning_outcomes->take(2) as $outcome)
                                                                <span class="badge bg-success badge-sm text-white me-1" title="{{ is_array($outcome) ? ($outcome['description'] ?? $outcome) : $outcome }}">
                                                                    {{ Str::limit(is_array($outcome) ? ($outcome['title'] ?? $outcome['description'] ?? 'Outcome') : $outcome, 15) }}
                                                                </span>
                                                            @endforeach
                                                            @if($session->activity->learning_outcomes->count() > 2)
                                                                <span class="badge bg-secondary badge-sm text-white">
                                                                    +{{ $session->activity->learning_outcomes->count() - 2 }} more
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date & Time -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="fw-bold text-primary">
                                                {{ \Carbon\Carbon::parse($session->display_date)->format('M d, Y') }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} - 
                                                {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-clock me-1"></i>{{ $session->duration_minutes }}min
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Staff & Location -->
                                    <div class="col-md-2">
                                        <div>
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-user-tie me-1"></i>INSTRUCTOR
                                            </div>
                                            <div class="fw-medium">{{ $session->teacher->name ?? 'Not Assigned' }}</div>
                                        </div>
                                        <div class="mt-2">
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-map-marker-alt me-1"></i>LOCATION
                                            </div>
                                            <div class="small">{{ $session->room_details ?: 'Location TBD' }}</div>
                                        </div>
                                    </div>

                                    <!-- Participants -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="position-relative">
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: {{ $session->capacity_percentage }}%"></div>
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    {{ $session->current_participants }}/{{ $session->max_participants }} participants
                                                </div>
                                            </div>
                                            <!-- IEP Goals Progress -->
                                            @php
                                                $enrollments = $session->enrollments ?? collect();
                                                $iepGoalsCount = $enrollments->sum(function($enrollment) {
                                                    return optional($enrollment->trainee)->iep ? optional($enrollment->trainee->iep)->iepGoals->count() ?? 0 : 0;
                                                });
                                                $activeIepGoals = $enrollments->sum(function($enrollment) {
                                                    return optional($enrollment->trainee)->iep ? optional($enrollment->trainee->iep)->iepGoals->where('goal_status', 'active')->count() ?? 0 : 0;
                                                });
                                            @endphp
                                            @if($iepGoalsCount > 0)
                                                <div class="iep-progress-indicator mt-2">
                                                    <small class="text-primary">
                                                        <i class="fas fa-target me-1"></i>{{ $activeIepGoals }}/{{ $iepGoalsCount }} IEP Goals
                                                    </small>
                                                    <div class="progress mt-1" style="height: 4px;">
                                                        <div class="progress-bar bg-warning" role="progressbar" 
                                                             style="width: {{ $iepGoalsCount > 0 ? ($activeIepGoals / $iepGoalsCount) * 100 : 0 }}%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status & Actions -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                @php
                                                    $statusMap = [
                                                        'scheduled' => ['badge' => 'bg-primary', 'text' => 'Future'],
                                                        'ongoing' => ['badge' => 'bg-warning', 'text' => 'Progress'],
                                                        'completed' => ['badge' => 'bg-success', 'text' => 'Done'],
                                                        'cancelled' => ['badge' => 'bg-danger', 'text' => 'Cancelled']
                                                    ];
                                                    $statusInfo = $statusMap[$session->status] ?? ['badge' => 'bg-secondary', 'text' => 'Unknown'];
                                                @endphp
                                                <span class="badge {{ $statusInfo['badge'] }} px-3 py-2">
                                                    {{ $statusInfo['text'] }}
                                                </span>
                                                @if($session->activity->centre)
                                                    <div class="small text-muted mt-1">
                                                        <i class="fas fa-building me-1"></i>{{ $session->activity->centre->centre_name }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="action-buttons">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('activities.show', $session->activity_id) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                    @if($session->activity->learning_outcomes && $session->activity->learning_outcomes->count() > 0)
                                                        <button class="btn btn-outline-success btn-sm" 
                                                                onclick="showLearningOutcomes({{ $session->activity_id }})"
                                                                title="View Learning Outcomes">
                                                            <i class="fas fa-graduation-cap"></i>
                                                        </button>
                                                    @endif
                                                    @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                                                        <a href="{{ route('activities.sessions.learning-outcomes.index', $session->id) }}" 
                                                           class="btn btn-outline-info btn-sm"
                                                           title="Manage Session Learning Outcomes">
                                                            <i class="fas fa-tasks"></i>
                                                        </a>
                                                    @endif
                                                    @if($activeIepGoals > 0)
                                                        <button class="btn btn-outline-warning btn-sm" 
                                                                onclick="showIepGoals({{ $session->id }})"
                                                                title="View IEP Goals">
                                                            <i class="fas fa-target"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                <!-- Template Modification Dropdown -->
                                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                                    <div class="btn-group template-actions ms-2" role="group">
                                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown" title="Template Actions">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="modifySessionTemplate({{ $session->id }})">
                                                                <i class="fas fa-edit me-2"></i>Modify Template
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="applyTemplateToSimilar({{ $session->activity_id }})">
                                                                <i class="fas fa-copy me-2"></i>Apply to Similar Sessions
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="createTemplateFromSession({{ $session->id }})">
                                                                <i class="fas fa-plus me-2"></i>Create Template
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item" href="#" onclick="bulkSessionActions({{ $session->activity_id }})">
                                                                <i class="fas fa-layer-group me-2"></i>Bulk Actions
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                @endif
                                                @if($enrollments->count() > 0)
                                                    <div class="competency-progress-mini mt-1">
                                                        @php
                                                            $competencyLevels = ['beginner' => '#ffc107', 'intermediate' => '#17a2b8', 'advanced' => '#28a745'];
                                                            $sessionCompetencyData = $enrollments->flatMap(function($enrollment) {
                                                                return optional($enrollment->trainee)->learningProgress ?? [];
                                                            })->groupBy('competency_level');
                                                        @endphp
                                                        @if($sessionCompetencyData->count() > 0)
                                                            <div class="competency-bars">
                                                                @foreach($competencyLevels as $level => $color)
                                                                    @if($sessionCompetencyData->has($level))
                                                                        <div class="competency-bar" style="background-color: {{ $color }}; width: {{ ($sessionCompetencyData[$level]->count() / max($enrollments->count(), 1)) * 30 }}px; height: 3px; display: inline-block; margin-right: 2px;" title="{{ ucfirst($level) }}: {{ $sessionCompetencyData[$level]->count() }} trainees"></div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-4 border-top bg-light">
                    <div class="small text-muted">
                        Showing {{ $sessions->firstItem() }} to {{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions
                    </div>
                    <div>
                        {{ $sessions->appends(request()->query())->links() }}
                    </div>
                </div>

            @else
                <!-- No Sessions -->
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-times fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No sessions found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search_type', 'search_value', 'centre', 'category', 'status', 'date_range']))
                            Try adjusting your filters to see more sessions.
                        @else
                            No activity sessions have been scheduled yet.
                        @endif
                    </p>
                    @if(request()->hasAny(['search_type', 'search_value', 'centre', 'category', 'status', 'date_range']))
                        <a href="{{ route('activities.schedule') }}" class="btn btn-outline-primary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.session-item:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.activity-color-indicator {
    min-width: 4px;
    flex-shrink: 0;
}

.sessions-list .session-item:last-child {
    border-bottom: none !important;
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
}

/* Filter form styling */
.form-select-sm, .form-control-sm {
    font-size: 0.875rem;
}

.input-group .form-select {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

/* Learning Outcomes & Educational Context */
.learning-outcomes-preview {
    margin-top: 8px;
}

.outcomes-quick-view .badge {
    font-size: 0.75rem;
    max-width: 80px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    display: inline-block;
}

.iep-progress-indicator {
    background: rgba(255, 193, 7, 0.1);
    border-radius: 4px;
    padding: 4px 6px;
}

.competency-progress-mini {
    margin-top: 5px;
}

.competency-bars {
    display: flex;
    align-items: center;
    gap: 1px;
}

.competency-bar {
    border-radius: 1px;
    min-width: 2px;
    cursor: help;
}

.action-buttons .btn-group {
    margin-bottom: 5px;
}

.action-buttons .btn-sm {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
}

/* Modal Enhancements */
.outcome-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-left: 4px solid #28a745 !important;
    transition: transform 0.2s ease;
}

.outcome-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.progress-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0% var(--progress, 0%), #e9ecef var(--progress, 0%) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-circle span {
    font-size: 0.7rem;
    font-weight: bold;
    color: #333;
    background: white;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.iep-goal-card {
    background: linear-gradient(135deg, #fff9c4 0%, #ffffff 100%);
    border-left: 4px solid #ffc107 !important;
}

.activity-badges .badge {
    margin-right: 4px;
}

/* Enhanced Progress Indicators */
.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .session-item .row > div {
        margin-bottom: 1rem;
    }
    
    .session-item .row > div:last-child {
        margin-bottom: 0;
    }
    
    .learning-outcomes-preview {
        margin-top: 6px;
    }
    
    .outcomes-quick-view .badge {
        font-size: 0.65rem;
        max-width: 60px;
    }
    
    .competency-bars {
        justify-content: center;
        margin-top: 3px;
    }
    
    .action-buttons .btn-group {
        flex-direction: column;
    }
    
    .action-buttons .btn-sm {
        margin-bottom: 2px;
    }
}

/* Template Modification Interface */
.template-actions .dropdown-toggle::after {
    margin-left: 0.5rem;
}

.template-modification-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
}

.template-actions-panel {
    background: white;
    border-radius: 6px;
    padding: 1rem;
    border: 1px solid #e9ecef;
}

.template-actions-panel .form-check {
    margin-bottom: 0.5rem;
}

.template-actions-panel .form-check-label {
    font-size: 0.9rem;
    color: #495057;
}

/* Bulk Actions Modal */
.bulk-actions-grid {
    padding: 1rem;
}

.bulk-action-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.bulk-action-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    transform: translateY(-2px);
}

.bulk-action-card h6 {
    margin-bottom: 0.5rem;
    color: #343a40;
    font-weight: 600;
}

.bulk-action-card p {
    margin-bottom: 0;
    line-height: 1.3;
}

/* Template Preview Styles */
.template-preview {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 1rem;
    border-left: 4px solid #007bff;
}

.template-preview h6 {
    color: #007bff;
    margin-bottom: 0.75rem;
}

.preview-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.preview-item:last-child {
    border-bottom: none;
}

.preview-label {
    font-weight: 600;
    color: #495057;
    flex: 1;
}

.preview-value {
    color: #6c757d;
    flex: 2;
}

.preview-changes {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 0.5rem;
    margin-top: 0.5rem;
}

.preview-changes small {
    color: #856404;
}

/* Enhanced Action Buttons */
.template-actions {
    transition: all 0.3s ease;
}

.template-actions:hover {
    transform: scale(1.05);
}

.template-actions .dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 6px;
}

.template-actions .dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.template-actions .dropdown-item:hover {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    transform: translateX(5px);
}

.template-actions .dropdown-item i {
    width: 16px;
    text-align: center;
}

/* Mobile Responsiveness for Template Interface */
@media (max-width: 768px) {
    .template-modification-form {
        padding: 1rem;
    }
    
    .template-actions-panel {
        margin-top: 1rem;
    }
    
    .bulk-action-card {
        height: 120px;
        padding: 1rem;
    }
    
    .bulk-action-card i {
        font-size: 1.5rem !important;
    }
    
    .template-actions {
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .template-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#scheduleFilters select:not([name="search_type"])');
    
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            document.getElementById('scheduleFilters').submit();
        });
    });
    
    // Update search placeholder based on search type
    const searchType = document.querySelector('select[name="search_type"]');
    const searchValue = document.querySelector('input[name="search_value"]');
    
    function updateSearchPlaceholder() {
        const placeholders = {
            'activity': 'Enter activity name...',
            'staff': 'Enter staff/instructor name...',
            'trainee': 'Enter trainee name...',
            'room': 'Enter room or venue...'
        };
        
        searchValue.placeholder = placeholders[searchType.value] || 'Enter search term...';
    }
    
    searchType.addEventListener('change', updateSearchPlaceholder);
    updateSearchPlaceholder(); // Set initial placeholder
});

// Learning Outcomes Modal
function showLearningOutcomes(activityId) {
    fetch(`/activities/${activityId}/learning-outcomes`)
        .then(response => response.json())
        .then(data => {
            let modalContent = `
                <div class="modal fade" id="learningOutcomesModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i>Learning Outcomes</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="learning-outcomes-list">
            `;
            
            data.outcomes.forEach(outcome => {
                modalContent += `
                    <div class="outcome-card mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold">${outcome.outcome_title}</h6>
                                <p class="text-muted small mb-2">${outcome.outcome_description}</p>
                                <span class="badge bg-info">${outcome.competency_level}</span>
                                <span class="badge bg-secondary">${outcome.assessment_criteria}</span>
                            </div>
                            <div class="outcome-progress">
                                <div class="progress-circle" data-progress="${outcome.average_progress || 0}">
                                    <span>${outcome.average_progress || 0}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            modalContent += `
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('learningOutcomesModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to body and show
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('learningOutcomesModal')).show();
        })
        .catch(error => {
            console.error('Error fetching learning outcomes:', error);
            alert('Error loading learning outcomes. Please try again.');
        });
}

// IEP Goals Modal
function showIepGoals(sessionId) {
    fetch(`/sessions/${sessionId}/iep-goals`)
        .then(response => response.json())
        .then(data => {
            let modalContent = `
                <div class="modal fade" id="iepGoalsModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-target me-2"></i>IEP Goals Progress</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="iep-goals-grid">
            `;
            
            data.goals.forEach(goal => {
                const progressWidth = (goal.current_progress / goal.target_value) * 100;
                modalContent += `
                    <div class="iep-goal-card mb-3 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="fw-bold">${goal.trainee_name}</h6>
                                <p class="mb-2">${goal.goal_description}</p>
                                <div class="goal-details">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>Target: ${goal.target_completion_date}
                                        <span class="ms-3"><i class="fas fa-flag me-1"></i>Priority: ${goal.priority_level}</span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="goal-progress">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small>Progress</small>
                                        <small>${goal.current_progress}/${goal.target_value}</small>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: ${progressWidth}%"></div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-${goal.goal_status === 'active' ? 'primary' : goal.goal_status === 'achieved' ? 'success' : 'warning'}">${goal.goal_status}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            modalContent += `
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="updateIepProgress(${sessionId})">Update Progress</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('iepGoalsModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to body and show
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('iepGoalsModal')).show();
        })
        .catch(error => {
            console.error('Error fetching IEP goals:', error);
            alert('Error loading IEP goals. Please try again.');
        });
}

function updateIepProgress(sessionId) {
    // This would redirect to a dedicated IEP progress update page
    window.location.href = `/sessions/${sessionId}/iep-progress-update`;
}

// Template Modification Functions
function modifySessionTemplate(sessionId) {
    fetch(`/activities/sessions/${sessionId}/template-data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTemplateModificationModal(sessionId, data.template);
            } else {
                alert('Error loading session template data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
        });
}

function showTemplateModificationModal(sessionId, templateData) {
    let modalContent = `
        <div class="modal fade" id="templateModificationModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modify Session Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="template-modification-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Session Details</h6>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Session Date</label>
                                        <input type="date" class="form-control" id="sessionDate" value="${templateData.session_date || ''}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Start Time</label>
                                                <input type="time" class="form-control" id="startTime" value="${templateData.start_time || ''}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">End Time</label>
                                                <input type="time" class="form-control" id="endTime" value="${templateData.end_time || ''}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Venue</label>
                                        <input type="text" class="form-control" id="venue" value="${templateData.venue || ''}" placeholder="Enter venue">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Room Number (Optional)</label>
                                        <input type="text" class="form-control" id="roomNumber" value="${templateData.room_number || ''}" placeholder="Room number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Template Options</h6>
                                    <div class="template-actions-panel">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Apply Changes To</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="applyScope" id="thisSession" value="this" checked>
                                                <label class="form-check-label" for="thisSession">This session only</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="applyScope" id="futureSession" value="future">
                                                <label class="form-check-label" for="futureSession">This and future sessions</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="applyScope" id="allSessions" value="all">
                                                <label class="form-check-label" for="allSessions">All sessions in this activity</label>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="preserveEnrollments">
                                                <label class="form-check-label" for="preserveEnrollments">
                                                    Preserve existing enrollments
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="preserveLearningOutcomes">
                                                <label class="form-check-label" for="preserveLearningOutcomes">
                                                    Preserve learning outcome assignments
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifyParticipants">
                                                <label class="form-check-label" for="notifyParticipants">
                                                    Notify enrolled participants of changes
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Modification Notes</label>
                                        <textarea class="form-control" id="modificationNotes" rows="3" placeholder="Describe the changes made and reason..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" onclick="previewTemplateChanges(${sessionId})">
                            <i class="fas fa-eye me-1"></i>Preview Changes
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveTemplateModifications(${sessionId})">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('templateModificationModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to body and show
    document.body.insertAdjacentHTML('beforeend', modalContent);
    new bootstrap.Modal(document.getElementById('templateModificationModal')).show();
}

function previewTemplateChanges(sessionId) {
    const formData = collectTemplateFormData();
    
    fetch(`/activities/sessions/${sessionId}/template-preview`, {
        method: 'POST',
        body: JSON.stringify(formData),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showTemplatePreview(data.preview);
        } else {
            alert('Error generating preview: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
}

function saveTemplateModifications(sessionId) {
    const formData = collectTemplateFormData();
    
    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    saveBtn.disabled = true;
    
    fetch(`/activities/sessions/${sessionId}/template-modify`, {
        method: 'POST',
        body: JSON.stringify(formData),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('templateModificationModal')).hide();
            
            // Show success message and reload
            alert('Template modifications saved successfully!');
            window.location.reload();
        } else {
            alert('Error saving modifications: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function collectTemplateFormData() {
    return {
        session_date: document.getElementById('sessionDate').value,
        start_time: document.getElementById('startTime').value,
        end_time: document.getElementById('endTime').value,
        venue: document.getElementById('venue').value,
        room_number: document.getElementById('roomNumber').value,
        apply_scope: document.querySelector('input[name="applyScope"]:checked').value,
        preserve_enrollments: document.getElementById('preserveEnrollments').checked,
        preserve_learning_outcomes: document.getElementById('preserveLearningOutcomes').checked,
        notify_participants: document.getElementById('notifyParticipants').checked,
        modification_notes: document.getElementById('modificationNotes').value
    };
}

function applyTemplateToSimilar(activityId) {
    if (confirm('Apply this session template to all similar sessions in this activity?')) {
        fetch(`/activities/${activityId}/apply-template-similar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Template applied to ${data.affected_sessions} sessions successfully!`);
                window.location.reload();
            } else {
                alert('Error applying template: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
        });
    }
}

function createTemplateFromSession(sessionId) {
    const templateName = prompt('Enter a name for this template:');
    if (templateName) {
        fetch(`/activities/sessions/${sessionId}/create-template`, {
            method: 'POST',
            body: JSON.stringify({
                template_name: templateName,
                description: `Template created from session ${sessionId}`
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
                alert('Template created successfully!');
                if (confirm('Do you want to view the new template?')) {
                    window.open(`/activities/templates/${data.template_id}`, '_blank');
                }
            } else {
                alert('Error creating template: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
        });
    }
}

// NEW: Bulk Operations Functions
function bulkReschedule(activityId) {
    const selectedSessions = getSelectedSessions();
    if (selectedSessions.length === 0) {
        alert('Please select sessions to reschedule');
        return;
    }

    const newDate = prompt('Enter new date (YYYY-MM-DD):');
    if (!newDate) return;

    const timeOffset = prompt('Time offset in hours (optional, can be negative):');
    const reason = prompt('Reason for rescheduling:');

    const payload = {
        session_ids: selectedSessions,
        new_date: newDate,
        time_offset_hours: timeOffset ? parseInt(timeOffset) : null,
        preserve_enrollments: true,
        preserve_learning_outcomes: true,
        notify_participants: confirm('Notify participants about the changes?'),
        reason: reason || 'Bulk rescheduling'
    };

    fetch('/activities/bulk/reschedule', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully rescheduled ${data.data.rescheduled_sessions} sessions!`);
            if (data.conflicts && data.conflicts.length > 0) {
                alert(`Note: ${data.conflicts.length} sessions had conflicts and were skipped.`);
            }
            window.location.reload();
        } else {
            alert('Error during bulk rescheduling: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
}

function bulkChangeVenue(activityId) {
    const selectedSessions = getSelectedSessions();
    if (selectedSessions.length === 0) {
        alert('Please select sessions to change venue');
        return;
    }

    const newVenue = prompt('Enter new venue:');
    if (!newVenue) return;

    const newRoomNumber = prompt('Enter new room number (optional):');
    const reason = prompt('Reason for venue change:');

    const payload = {
        session_ids: selectedSessions,
        new_venue: newVenue,
        new_room_number: newRoomNumber || null,
        preserve_enrollments: true,
        preserve_learning_outcomes: true,
        notify_participants: confirm('Notify participants about the venue change?'),
        reason: reason || 'Bulk venue change'
    };

    fetch('/activities/bulk/change-venue', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully updated venue for ${data.data.updated_sessions} sessions!`);
            if (data.conflicts && data.conflicts.length > 0) {
                alert(`Note: ${data.conflicts.length} sessions had venue conflicts and were skipped.`);
            }
            window.location.reload();
        } else {
            alert('Error during bulk venue change: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
}

function bulkCancel(activityId) {
    const selectedSessions = getSelectedSessions();
    if (selectedSessions.length === 0) {
        alert('Please select sessions to cancel');
        return;
    }

    const reason = prompt('Reason for cancellation (required):');
    if (!reason) return;

    if (!confirm(`Are you sure you want to cancel ${selectedSessions.length} sessions? This action cannot be undone.`)) {
        return;
    }

    const offerAlternative = confirm('Do you want to offer an alternative session?');
    let alternativeSessionId = null;
    
    if (offerAlternative) {
        alternativeSessionId = prompt('Enter alternative session ID:');
        if (!alternativeSessionId) {
            alert('Alternative session ID is required when offering alternatives');
            return;
        }
    }

    const payload = {
        session_ids: selectedSessions,
        cancellation_reason: reason,
        preserve_enrollments: true,
        preserve_learning_outcomes: true,
        notify_participants: confirm('Notify participants about the cancellation?'),
        offer_alternative: offerAlternative,
        alternative_session_id: alternativeSessionId
    };

    fetch('/activities/bulk/cancel', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = `Successfully cancelled ${data.data.cancelled_sessions} sessions!`;
            if (data.data.enrollment_transfers > 0) {
                message += ` ${data.data.enrollment_transfers} enrollments were transferred to the alternative session.`;
            }
            alert(message);
            window.location.reload();
        } else {
            alert('Error during bulk cancellation: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
}

function getSelectedSessions() {
    const checkboxes = document.querySelectorAll('input[name="session_ids[]"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

function bulkSessionActions(activityId) {
    // Show bulk actions modal
    showBulkActionsModal(activityId);
}

function showBulkActionsModal(activityId) {
    let modalContent = `
        <div class="modal fade" id="bulkActionsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Bulk Session Actions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="bulk-actions-grid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="bulk-action-card" onclick="bulkReschedule(${activityId})">
                                        <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                        <h6>Bulk Reschedule</h6>
                                        <p class="text-muted small">Reschedule multiple sessions with one action</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bulk-action-card" onclick="bulkChangeVenue(${activityId})">
                                        <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                                        <h6>Change Locations</h6>
                                        <p class="text-muted small">Update venue/room for multiple sessions</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bulk-action-card" onclick="bulkCancel(${activityId})">
                                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                        <h6>Cancel Sessions</h6>
                                        <p class="text-muted small">Cancel multiple sessions with proper notifications</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bulk-action-card" onclick="bulkLearningOutcomes(${activityId})">
                                        <i class="fas fa-graduation-cap fa-2x text-info mb-2"></i>
                                        <h6>Learning Outcomes</h6>
                                        <p class="text-muted small">Apply learning outcomes to multiple sessions</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bulk-action-card" onclick="bulkEnrollmentManagement(${activityId})">
                                        <i class="fas fa-users fa-2x text-warning mb-2"></i>
                                        <h6>Enrollment Management</h6>
                                        <p class="text-muted small">Manage trainee enrollments across sessions</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('bulkActionsModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to body and show
    document.body.insertAdjacentHTML('beforeend', modalContent);
    new bootstrap.Modal(document.getElementById('bulkActionsModal')).show();
}

// Bulk action functions (to be implemented)
function bulkReschedule(activityId) {
    alert('Bulk reschedule feature coming soon!');
}

function bulkLocationChange(activityId) {
    alert('Bulk location change feature coming soon!');
}

function bulkLearningOutcomes(activityId) {
    alert('Bulk learning outcomes management feature coming soon!');
}

function bulkEnrollmentManagement(activityId) {
    alert('Bulk enrollment management feature coming soon!');
}
</script>
@endsection