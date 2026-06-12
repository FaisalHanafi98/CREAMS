@extends('layouts.app')

@section('title', 'View IEP - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>{{ $iep->plan_name }}
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('iep.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to IEPs
            </a>
            <a href="{{ route('iep.edit', $iep->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <form action="{{ route('iep.destroy', $iep->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this IEP? This action cannot be undone.')">
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Overdue Alert -->
    @if($overdueGoals->count() > 0)
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>{{ $overdueGoals->count() }} goal(s) overdue.</strong> Please review and update progress.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif

    <!-- Progress Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-primary">{{ $progressSummary['total_goals'] }}</div>
                    <div class="text-muted small">Total Goals</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-success">{{ $progressSummary['achieved'] }}</div>
                    <div class="text-muted small">Achieved</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-info">{{ $progressSummary['in_progress'] }}</div>
                    <div class="text-muted small">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-warning">{{ $progressSummary['completion_rate'] }}%</div>
                    <div class="text-muted small">Completion Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Details -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Plan Details</h5>
            <span class="badge bg-{{ $iep->status_color }}">
                <i class="{{ $iep->status_icon }} me-1"></i>{{ $iep->status }}
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="text-muted small fw-semibold">Trainee</div>
                    <div>{{ $iep->trainee->trainee_first_name }} {{ $iep->trainee->trainee_last_name }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted small fw-semibold">Plan Type</div>
                    <div><span class="badge bg-{{ $iep->type_color }}">{{ $iep->plan_type }}</span></div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted small fw-semibold">Created By</div>
                    <div>{{ $iep->creator->name ?? 'System' }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted small fw-semibold">Start Date</div>
                    <div>{{ $iep->start_date->format('d M Y') }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted small fw-semibold">End Date</div>
                    <div>{{ $iep->end_date->format('d M Y') }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted small fw-semibold">Next Review</div>
                    <div>
                        @if($iep->review_date)
                            {{ $iep->review_date->format('d M Y') }}
                            @if($iep->isOverdue())
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @elseif($iep->isDueForReview())
                                <span class="badge bg-warning ms-1">Due Soon</span>
                            @endif
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </div>
                </div>
                @if($iep->plan_description)
                <div class="col-12 mb-3">
                    <div class="text-muted small fw-semibold">Description</div>
                    <div>{{ $iep->plan_description }}</div>
                </div>
                @endif
                @if($iep->strengths && count($iep->strengths) > 0)
                <div class="col-md-6 mb-3">
                    <div class="text-muted small fw-semibold">Strengths</div>
                    <ul class="list-group list-group-flush">
                        @foreach($iep->strengths as $strength)
                            @if($strength)
                            <li class="list-group-item px-0 py-1 small">
                                <i class="fas fa-plus-circle text-success me-1"></i>{{ $strength }}
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif
                @if($iep->challenges && count($iep->challenges) > 0)
                <div class="col-md-6 mb-3">
                    <div class="text-muted small fw-semibold">Challenges</div>
                    <ul class="list-group list-group-flush">
                        @foreach($iep->challenges as $challenge)
                            @if($challenge)
                            <li class="list-group-item px-0 py-1 small">
                                <i class="fas fa-minus-circle text-danger me-1"></i>{{ $challenge }}
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif
                @if($iep->notes)
                <div class="col-12 mb-3">
                    <div class="text-muted small fw-semibold">Notes</div>
                    <div class="small">{{ $iep->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Goals Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Goals</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                <i class="fas fa-plus me-1"></i>Add Goal
            </button>
        </div>
        <div class="card-body">
            @if($iep->goals->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Goal</th>
                            <th>Activity</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th>Assigned To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($iep->goals as $index => $goal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $goal->goal_title }}</div>
                                @if($goal->goal_description)
                                    <div class="text-muted small">{{ Str::limit($goal->goal_description, 80) }}</div>
                                @endif
                            </td>
                            <td>{{ $goal->activity->activity_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $goal->priority_color }}">
                                    <i class="{{ $goal->priority_icon }} me-1"></i>{{ $goal->priority_level }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $goal->status_color }}">
                                    <i class="{{ $goal->status_icon }} me-1"></i>{{ $goal->goal_status }}
                                </span>
                            </td>
                            <td style="min-width: 120px;">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $goal->status_color }}" role="progressbar"
                                         style="width: {{ $goal->current_progress_percentage }}%"
                                         aria-valuenow="{{ $goal->current_progress_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="small text-muted mt-1">{{ $goal->current_progress_percentage }}% / {{ $goal->target_percentage }}%</div>
                            </td>
                            <td>
                                @if($goal->target_completion_date)
                                    {{ $goal->target_completion_date->format('d M Y') }}
                                    @if($goal->isOverdue())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @elseif($goal->isDueSoon())
                                        <span class="badge bg-warning ms-1">Due Soon</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $goal->assignedStaff->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-target fa-2x mb-2 d-block"></i>
                No goals added yet. Click <strong>Add Goal</strong> to get started.
            </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    @if($upcomingDeadlines->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clock text-info me-2"></i>Upcoming Deadlines (Next 30 Days)</h5>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                @foreach($upcomingDeadlines as $goal)
                <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $goal->goal_title }}</div>
                            <div class="small text-muted">{{ $goal->activity->activity_name ?? 'No Activity' }}</div>
                        </div>
                        <div class="text-end">
                            <div class="small fw-semibold">{{ $goal->target_completion_date->format('d M Y') }}</div>
                            <div class="small text-muted">{{ $goal->target_completion_date->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGoalModalLabel">Add New Goal</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="addGoalForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="goal_activity_id" class="form-label fw-semibold">Activity <span class="text-danger">*</span></label>
                            <select name="activity_id" id="goal_activity_id" class="form-select" required>
                                <option value="" disabled selected>Select activity</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}">{{ $activity->activity_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="goal_title" class="form-label fw-semibold">Goal Title <span class="text-danger">*</span></label>
                            <input type="text" name="goal_title" id="goal_title" class="form-control" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="goal_description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea name="goal_description" id="goal_description" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="goal_start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="target_start_date" id="goal_start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="goal_end_date" class="form-label fw-semibold">Completion Date <span class="text-danger">*</span></label>
                            <input type="date" name="target_completion_date" id="goal_end_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="goal_target_pct" class="form-label fw-semibold">Target % <span class="text-danger">*</span></label>
                            <input type="number" name="target_percentage" id="goal_target_pct" class="form-control" min="0" max="100" value="100" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="goal_tracking_method" class="form-label fw-semibold">Tracking Method <span class="text-danger">*</span></label>
                            <select name="progress_tracking_method" id="goal_tracking_method" class="form-select" required>
                                <option value="Attendance">Attendance</option>
                                <option value="Competency">Competency</option>
                                <option value="Assessment">Assessment</option>
                                <option value="Mixed">Mixed</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="goal_priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority_level" id="goal_priority" class="form-select" required>
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="goal_assigned" class="form-label fw-semibold">Assigned Staff</label>
                            <input type="text" name="assigned_user_id" id="goal_assigned" class="form-control" placeholder="Staff ID (optional)">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="goal_notes" class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="goal_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addGoalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch("{{ route('iep.goals.store', $iep->id) }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to add goal.');
        }
    })
    .catch(() => {
        alert('An error occurred. Please try again.');
    });
});
</script>
@endsection
