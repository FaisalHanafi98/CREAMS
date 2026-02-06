@extends('layouts.app')

@section('title', 'Schedule Templates')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-check me-2"></i>Schedule Templates
            </h1>
            <p class="mb-0 text-muted">Reusable schedule patterns for quick activity setup</p>
        </div>
        @if(in_array(session('role'), ['admin', 'supervisor']))
        <div>
            <a href="{{ route('activities.templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Template
            </a>
        </div>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Templates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $templates->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Weekly Templates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $templates->where('template_type', 'weekly')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Intensive Templates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $templates->where('template_type', 'intensive')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Custom Templates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $templates->where('template_type', 'custom')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row">
        @foreach($templates as $template)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $template->template_name }}</h6>
                    <span class="badge badge-{{ 
                        $template->template_type === 'weekly' ? 'success' : 
                        ($template->template_type === 'intensive' ? 'danger' : 
                        ($template->template_type === 'flexible' ? 'info' : 'warning')) 
                    }}">
                        {{ ucfirst($template->template_type) }}
                    </span>
                </div>
                
                <div class="card-body">
                    @if($template->description)
                        <p class="text-muted small">{{ Str::limit($template->description, 100) }}</p>
                    @endif
                    
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Sessions/Week</div>
                            <div class="h6 mb-0 text-primary">{{ $template->sessions_per_week }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Duration</div>
                            <div class="h6 mb-0 text-success">{{ $template->duration_weeks }}w</div>
                        </div>
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total</div>
                            <div class="h6 mb-0 text-info">{{ $template->total_sessions }}</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase mb-2">Schedule</div>
                        <div class="d-flex flex-wrap">
                            @foreach($template->days_of_week as $day)
                                <span class="badge badge-light me-1 mb-1">{{ substr($day, 0, 3) }}</span>
                            @endforeach
                        </div>
                        <small class="text-muted">{{ $template->session_length_minutes }} minutes per session</small>
                    </div>
                    
                    @if($template->applications_count > 0)
                        <div class="mb-3">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Used in {{ $template->applications_count }} activities
                            </small>
                        </div>
                    @endif
                </div>
                
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewTemplate({{ $template->id }})">
                            <i class="fas fa-eye me-1"></i>View Details
                        </button>
                        
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="applyTemplate({{ $template->id }})">
                                <i class="fas fa-play me-1"></i>Apply
                            </button>
                            
                            @if(in_array(session('role'), ['admin', 'supervisor']))
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteTemplate({{ $template->id }})">
                                <i class="fas fa-trash"></i>
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
    <div class="d-flex justify-content-center">
        @include('components.custom-pagination', ['items' => $templates])
    </div>
</div>

<!-- Apply Template Modal -->
<div class="modal fade" id="applyTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Schedule Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="applyTemplateForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="template_id" name="template_id">
                    
                    <div class="mb-3">
                        <label for="activity_id" class="form-label">Select Activity</label>
                        <select class="form-select" id="activity_id" name="activity_id" required>
                            <option value="">Choose activity...</option>
                            <!-- Activities will be loaded via AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required min="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="room_number" class="form-label">Room Number (Optional)</label>
                        <input type="text" class="form-control" id="room_number" name="customizations[room_number]" placeholder="e.g., Room 101">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will create all scheduled sessions for the selected template automatically.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Apply Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template Details Modal -->
<div class="modal fade" id="templateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="templateDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Load activities for template application
function loadActivities() {
    fetch('/api/activities')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('activity_id');
            select.innerHTML = '<option value="">Choose activity...</option>';

            data.forEach(activity => {
                // SECURITY: Escape activity name to prevent XSS
                select.innerHTML += `<option value="${escapeHtml(activity.id)}">${escapeHtml(activity.activity_name)}</option>`;
            });
        })
        .catch(error => console.error('Error loading activities:', error));
}

// Apply template
function applyTemplate(templateId) {
    document.getElementById('template_id').value = templateId;
    loadActivities();
    new bootstrap.Modal(document.getElementById('applyTemplateModal')).show();
}

// View template details
function viewTemplate(templateId) {
    fetch(`/activities/templates/${templateId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('templateDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('templateDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error loading template details:', error);
            alert('Failed to load template details.');
        });
}

// Delete template
function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to deactivate this template?')) {
        fetch(`/activities/templates/${templateId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to delete template.');
            }
        })
        .catch(error => {
            console.error('Error deleting template:', error);
            alert('Failed to delete template.');
        });
    }
}

// Handle apply template form submission
document.getElementById('applyTemplateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/activities/templates/apply', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('applyTemplateModal')).hide();
        } else {
            alert(data.message || 'Failed to apply template.');
        }
    })
    .catch(error => {
        console.error('Error applying template:', error);
        alert('Failed to apply template.');
    });
});
</script>
@endpush