@extends('layouts.app')

@section('title', 'Learning Outcomes Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                        Learning Outcomes
                    </h1>
                    <p class="text-muted mb-0">
                        @if($activity)
                            Manage learning outcomes for: <strong>{{ $activity->activity_name }}</strong>
                        @else
                            Manage learning outcomes across all activities
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('activities.learning-outcomes.create', request()->query()) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Learning Outcome
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                    <i class="fas fa-bullseye text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $stats['total'] }}</h5>
                                    <p class="card-text text-muted mb-0">Total Outcomes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="fas fa-check-circle text-success fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $stats['active'] }}</h5>
                                    <p class="card-text text-muted mb-0">Active Outcomes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                    <i class="fas fa-star text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $stats['by_level']['Beginner'] }}</h5>
                                    <p class="card-text text-muted mb-0">Beginner Level</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                    <i class="fas fa-trophy text-danger fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $stats['by_level']['Advanced'] }}</h5>
                                    <p class="card-text text-muted mb-0">Advanced Level</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('activities.learning-outcomes.index') }}" class="row g-3">
                        @if($activity)
                            <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                        @endif
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search outcomes...">
                        </div>
                        
                        @if(!$activity)
                        <div class="col-md-3">
                            <label for="activity_filter" class="form-label">Activity</label>
                            <select class="form-select" id="activity_filter" name="activity_id">
                                <option value="">All Activities</option>
                                @foreach($activities as $act)
                                    <option value="{{ $act->id }}" {{ request('activity_id') == $act->id ? 'selected' : '' }}>
                                        {{ $act->activity_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        
                        <div class="col-md-2">
                            <label for="competency_level" class="form-label">Level</label>
                            <select class="form-select" id="competency_level" name="competency_level">
                                <option value="">All Levels</option>
                                <option value="Beginner" {{ request('competency_level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="Intermediate" {{ request('competency_level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="Advanced" {{ request('competency_level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('activities.learning-outcomes.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Learning Outcomes List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Learning Outcomes</h5>
                </div>
                <div class="card-body p-0">
                    @if($outcomes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Outcome Title</th>
                                        <th width="20%">Activity</th>
                                        <th width="15%">Competency Level</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Progress</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outcomes as $outcome)
                                        <tr>
                                            <td>{{ $outcome->display_order }}</td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">{{ $outcome->outcome_title }}</h6>
                                                    @if($outcome->outcome_description)
                                                        <small class="text-muted">{{ Str::limit($outcome->outcome_description, 80) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $outcome->activity->activity_name }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $outcome->competency_level_color }}">
                                                    {{ $outcome->competency_level }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($outcome->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php $stats = $outcome->progress_statistics @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-success" style="width: {{ $stats['completion_rate'] }}%"></div>
                                                    </div>
                                                    <small class="ms-2">{{ $stats['completion_rate'] }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('activities.learning-outcomes.show', $outcome->id) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('activities.learning-outcomes.edit', $outcome->id) }}" 
                                                       class="btn btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="confirmDelete({{ $outcome->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($outcomes->hasPages())
                            <div class="card-footer bg-white">
                                @include('components.custom-pagination', ['items' => $outcomes])
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-graduation-cap fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Learning Outcomes Found</h5>
                            <p class="text-muted mb-3">Start by creating your first learning outcome.</p>
                            <a href="{{ route('activities.learning-outcomes.create', request()->query()) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Learning Outcome
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Learning Outcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this learning outcome?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(outcomeId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/activities/learning-outcomes/${outcomeId}`;
    modal.show();
}
</script>
@endpush