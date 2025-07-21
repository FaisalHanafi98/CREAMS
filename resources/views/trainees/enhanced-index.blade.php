@extends('layouts.app')

@section('title', 'Enhanced Trainee Management - CREAMS')

@section('styles')
<style>
    .filter-panel {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stats-card {
        border-left: 4px solid var(--primary-color);
        background: white;
        padding: 1.5rem;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 1rem;
    }
    
    .stats-card.success { border-left-color: #1cc88a; }
    .stats-card.info { border-left-color: #36b9cc; }
    .stats-card.warning { border-left-color: #f6c23e; }
    .stats-card.danger { border-left-color: #e74a3b; }
    
    .trainee-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .trainee-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .avatar-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .condition-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
    }
    
    .search-highlight {
        background-color: #fff3cd;
        padding: 0.1rem 0.2rem;
        border-radius: 0.2rem;
    }
    
    .bulk-actions {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-bottom: 1rem;
        display: none;
    }
    
    .bulk-actions.show {
        display: block;
    }
    
    .filter-tag {
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
        display: inline-block;
    }
    
    .progress-indicator {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #1cc88a 0%, #36b9cc 100%);
        transition: width 0.3s ease;
    }
    
    .birthday-reminder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .advanced-search {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users-cog mr-2"></i>Enhanced Trainee Management
        </h1>
        <div class="btn-group">
            <a href="{{ route('enhanced-trainees.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Register Trainee
            </a>
            <button class="btn btn-outline-primary" id="toggleFilters">
                <i class="fas fa-filter mr-1"></i>Filters
            </button>
            <button class="btn btn-outline-success" id="exportBtn">
                <i class="fas fa-download mr-1"></i>Export
            </button>
        </div>
    </div>

    <!-- Birthday Reminders -->
    @if($upcomingBirthdays->isNotEmpty())
    <div class="birthday-reminder">
        <h6 class="mb-2"><i class="fas fa-birthday-cake mr-2"></i>Upcoming Birthdays</h6>
        <div class="row">
            @foreach($upcomingBirthdays->take(3) as $trainee)
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $trainee->avatar_url }}" alt="Avatar" class="rounded-circle mr-2" width="30" height="30">
                    <div>
                        <strong>{{ $trainee->name }}</strong><br>
                        <small>{{ $trainee->trainee_date_of_birth->format('M d') }} ({{ $trainee->age + 1 }} years)</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Trainees</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] ?? 0 }}</div>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Needs Assessment</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['needs_assessment'] ?? 0 }}</div>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card info">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Graduated</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['graduated'] ?? 0 }}</div>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filters -->
    <div class="advanced-search" id="advancedSearch" style="display: none;">
        <form method="GET" action="{{ route('enhanced-trainees.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="Name, email, phone, ID...">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="condition" class="form-label">Condition</label>
                    <select class="form-control" id="condition" name="condition">
                        <option value="">All Conditions</option>
                        @foreach($conditions as $condition)
                        <option value="{{ $condition }}" {{ ($filters['condition'] ?? '') == $condition ? 'selected' : '' }}>
                            {{ $condition }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="age_from" class="form-label">Age From</label>
                    <input type="number" class="form-control" id="age_from" name="age_from" 
                           value="{{ $filters['age_from'] ?? '' }}" min="0" max="100">
                </div>
                
                <div class="col-md-2">
                    <label for="age_to" class="form-label">Age To</label>
                    <input type="number" class="form-control" id="age_to" name="age_to" 
                           value="{{ $filters['age_to'] ?? '' }}" min="0" max="100">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-3">
                    <label for="admission_from" class="form-label">Admission From</label>
                    <input type="date" class="form-control" id="admission_from" name="admission_from" 
                           value="{{ $filters['admission_from'] ?? '' }}">
                </div>
                
                <div class="col-md-3">
                    <label for="admission_to" class="form-label">Admission To</label>
                    <input type="date" class="form-control" id="admission_to" name="admission_to" 
                           value="{{ $filters['admission_to'] ?? '' }}">
                </div>
                
                <div class="col-md-4">
                    <label for="tags" class="form-label">Tags</label>
                    <input type="text" class="form-control" id="tags" name="tags" 
                           value="{{ $filters['tags'] ?? '' }}" 
                           placeholder="Special needs, high priority...">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('enhanced-trainees.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Display -->
    @if(array_filter($filters ?? []))
    <div class="mb-3">
        <h6 class="mb-2">Active Filters:</h6>
        @foreach($filters as $key => $value)
            @if($value)
            <span class="filter-tag">
                {{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}
                <a href="javascript:void(0)" class="text-white ml-1" onclick="removeFilter('{{ $key }}')">×</a>
            </span>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Bulk Actions Panel -->
    <div class="bulk-actions" id="bulkActions">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <strong><span id="selectedCount">0</span></strong> trainees selected
            </div>
            <div class="btn-group">
                <button class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                    <i class="fas fa-check mr-1"></i>Activate
                </button>
                <button class="btn btn-sm btn-warning" onclick="bulkAction('deactivate')">
                    <i class="fas fa-pause mr-1"></i>Deactivate
                </button>
                <button class="btn btn-sm btn-info" onclick="bulkAction('export')">
                    <i class="fas fa-download mr-1"></i>Export
                </button>
                @if(session('role') === 'admin')
                <button class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Trainees List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Trainees ({{ $trainees->total() }} found)
            </h6>
            
            <div class="d-flex align-items-center">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="selectAll">
                    <label class="custom-control-label" for="selectAll">Select All</label>
                </div>
                
                <div class="btn-group btn-group-sm">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-sort-alpha-{{ request('direction') === 'desc' ? 'up' : 'down' }} mr-1"></i>Name
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-sort-numeric-{{ request('direction') === 'desc' ? 'up' : 'down' }} mr-1"></i>Date
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            @if($trainees->count() > 0)
                <div class="row">
                    @foreach($trainees as $trainee)
                    <div class="col-lg-6 col-xl-4">
                        <div class="trainee-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="custom-control custom-checkbox mr-3 mt-1">
                                        <input type="checkbox" class="custom-control-input trainee-checkbox" 
                                               id="trainee_{{ $trainee->id }}" value="{{ $trainee->id }}">
                                        <label class="custom-control-label" for="trainee_{{ $trainee->id }}"></label>
                                    </div>
                                    
                                    <div class="flex-shrink-0 mr-3">
                                        <img src="{{ $trainee->avatar_url }}" alt="Avatar" class="avatar-img">
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('enhanced-trainees.show', $trainee->id) }}" 
                                               class="text-decoration-none">
                                                {{ $trainee->name }}
                                            </a>
                                        </h6>
                                        
                                        <p class="text-muted mb-1">
                                            <small>{{ $trainee->unique_identifier }}</small>
                                        </p>
                                        
                                        <div class="mb-2">
                                            <span class="badge badge-{{ $trainee->status === 'active' ? 'success' : ($trainee->status === 'graduated' ? 'info' : 'secondary') }}">
                                                {{ ucfirst($trainee->status) }}
                                            </span>
                                            
                                            <span class="condition-badge badge badge-outline-primary">
                                                {{ $trainee->trainee_condition }}
                                            </span>
                                        </div>
                                        
                                        <div class="text-muted small">
                                            <div><i class="fas fa-calendar mr-1"></i>Age: {{ $trainee->age }} years</div>
                                            @if($trainee->admission_date)
                                            <div><i class="fas fa-calendar-plus mr-1"></i>Admitted: {{ $trainee->admission_date->format('M Y') }}</div>
                                            @endif
                                            @if($trainee->guardian_phone)
                                            <div><i class="fas fa-phone mr-1"></i>{{ $trainee->guardian_phone }}</div>
                                            @endif
                                        </div>
                                        
                                        @if($trainee->average_progress)
                                        <div class="progress-indicator">
                                            <div class="progress-bar" style="width: {{ $trainee->average_progress }}%"></div>
                                        </div>
                                        <small class="text-muted">Overall Progress: {{ round($trainee->average_progress) }}%</small>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-3 border-top">
                                    <div class="btn-group btn-group-sm w-100">
                                        <a href="{{ route('enhanced-trainees.show', $trainee->id) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('trainees.edit', $trainee->id) }}" 
                                           class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-outline-info" onclick="showProgress({{ $trainee->id }})">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                        @if($trainee->has_expired_documents)
                                        <button class="btn btn-outline-danger" title="Has expired documents">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $trainees->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No trainees found</h5>
                    <p class="text-gray-500">Try adjusting your search criteria or register a new trainee.</p>
                    <a href="{{ route('enhanced-trainees.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Register Trainee
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Toggle filters
    $('#toggleFilters').click(function() {
        $('#advancedSearch').slideToggle();
        $(this).find('i').toggleClass('fa-filter fa-filter-circle-xmark');
    });
    
    // Auto-submit search form
    $('#search').on('input', debounce(function() {
        if ($(this).val().length >= 2 || $(this).val().length === 0) {
            $('#filterForm').submit();
        }
    }, 500));
    
    // Select all functionality
    $('#selectAll').change(function() {
        $('.trainee-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });
    
    // Individual checkbox handling
    $('.trainee-checkbox').change(function() {
        updateBulkActions();
        
        // Update select all state
        const totalCheckboxes = $('.trainee-checkbox').length;
        const checkedCheckboxes = $('.trainee-checkbox:checked').length;
        
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    });
    
    // Real-time search suggestions
    $('#search').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            $.get('{{ route("enhanced-trainees.search") }}', { q: query, limit: 5 })
                .done(function(data) {
                    // Show search suggestions (implement as needed)
                });
        }
    });
});

function updateBulkActions() {
    const selectedCount = $('.trainee-checkbox:checked').length;
    $('#selectedCount').text(selectedCount);
    
    if (selectedCount > 0) {
        $('#bulkActions').addClass('show');
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function bulkAction(action) {
    const selectedIds = $('.trainee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one trainee.');
        return;
    }
    
    if (action === 'delete' && !confirm('Are you sure you want to delete the selected trainees? This action cannot be undone.')) {
        return;
    }
    
    // Show loading state
    const $button = event.target;
    const originalText = $button.innerHTML;
    $button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    $button.disabled = true;
    
    $.post('{{ route("enhanced-trainees.bulk-operation") }}', {
        _token: '{{ csrf_token() }}',
        trainee_ids: selectedIds,
        action: action
    })
    .done(function(response) {
        if (response.success) {
            if (action === 'export') {
                // Handle file download
                window.location.href = response.download_url;
            } else {
                showAlert('success', response.message);
                location.reload();
            }
        } else {
            showAlert('error', response.message);
        }
    })
    .fail(function(xhr) {
        showAlert('error', 'Operation failed. Please try again.');
    })
    .always(function() {
        $button.innerHTML = originalText;
        $button.disabled = false;
    });
}

function removeFilter(filterName) {
    const url = new URL(window.location);
    url.searchParams.delete(filterName);
    window.location.href = url.toString();
}

function showProgress(traineeId) {
    // Implement progress modal (placeholder)
    alert('Progress tracking modal for trainee ID: ' + traineeId);
}

function showAlert(type, message) {
    // Simple alert implementation - replace with your preferred notification system
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endsection