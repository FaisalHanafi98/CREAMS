@extends('layouts.app')

@section('title', 'Asset Maintenance Schedule - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #25a6cf;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .maintenance-header {
        background: linear-gradient(135deg, var(--warning-color), #e0a800);
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .maintenance-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .maintenance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .schedule-item {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid var(--warning-color);
        transition: all 0.3s ease;
    }

    .schedule-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .schedule-item.overdue {
        border-left-color: var(--danger-color);
        background: rgba(220, 53, 69, 0.02);
    }

    .schedule-item.upcoming {
        border-left-color: var(--warning-color);
        background: rgba(255, 193, 7, 0.02);
    }

    .schedule-item.completed {
        border-left-color: var(--success-color);
        background: rgba(40, 167, 69, 0.02);
    }

    .priority-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .priority-high {
        background: var(--danger-color);
        color: white;
    }

    .priority-medium {
        background: var(--warning-color);
        color: white;
    }

    .priority-low {
        background: var(--success-color);
        color: white;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-scheduled {
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
    }

    .status-in_progress {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .status-completed {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-overdue {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }

    .calendar-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .filters-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .asset-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .asset-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--light-bg);
    }

    .asset-details h6 {
        margin: 0;
        color: var(--dark-color);
        font-weight: 600;
    }

    .asset-details small {
        color: #6c757d;
    }

    .maintenance-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-complete {
        background: var(--success-color);
        color: white;
    }

    .btn-reschedule {
        background: var(--warning-color);
        color: white;
    }

    .btn-details {
        background: var(--primary-color);
        color: white;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 20px;
    }

    .stat-overdue .stat-icon {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }

    .stat-due .stat-icon {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .stat-completed .stat-icon {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .stat-scheduled .stat-icon {
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="maintenance-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-tools me-3"></i>Asset Maintenance Schedule
                </h1>
                <p class="mb-0 opacity-75">Manage and track asset maintenance schedules and history</p>
            </div>
            <div class="col-md-4 text-end">
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#scheduleMaintenanceModal">
                    <i class="fas fa-plus me-2"></i>Schedule Maintenance
                </button>
                @endif
                <a href="{{ route('assets.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Assets
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-overview">
        <div class="stat-card stat-overdue">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $statistics['overdue'] }}</div>
            <div class="stat-label">Overdue</div>
        </div>
        <div class="stat-card stat-due">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">{{ $statistics['due_soon'] }}</div>
            <div class="stat-label">Due This Week</div>
        </div>
        <div class="stat-card stat-scheduled">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-value">{{ $statistics['scheduled'] }}</div>
            <div class="stat-label">Scheduled</div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value">{{ $statistics['completed_this_month'] }}</div>
            <div class="stat-label">Completed This Month</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form id="maintenanceFilters" class="row">
            <div class="col-md-3">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-control" id="statusFilter" name="status">
                    <option value="">All Statuses</option>
                    <option value="overdue">Overdue</option>
                    <option value="due_soon">Due Soon</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="priorityFilter" class="form-label">Priority</label>
                <select class="form-control" id="priorityFilter" name="priority">
                    <option value="">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="centreFilter" class="form-label">Centre</label>
                <select class="form-control" id="centreFilter" name="centre">
                    <option value="">All Centres</option>
                    @foreach($centres as $centre)
                        <option value="{{ $centre->centre_name }}">{{ $centre->centre_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="typeFilter" class="form-label">Asset Type</label>
                <select class="form-control" id="typeFilter" name="type">
                    <option value="">All Types</option>
                    @foreach($assetTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Maintenance Schedule List -->
    <div class="maintenance-card">
        <h4 class="mb-4">
            <i class="fas fa-list me-2"></i>Maintenance Schedule
        </h4>
        
        <div id="maintenanceList">
            @forelse($maintenanceSchedule as $maintenance)
                <div class="schedule-item {{ $maintenance['status_class'] }}">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="asset-info">
                                <img src="{{ $maintenance['asset_image'] }}" 
                                     alt="{{ $maintenance['asset_name'] }}" 
                                     class="asset-image"
                                     onerror="this.src='{{ asset('images/default-asset.png') }}'">
                                <div class="asset-details">
                                    <h6>{{ $maintenance['asset_name'] }}</h6>
                                    <small>{{ $maintenance['asset_id'] }} | {{ $maintenance['asset_type'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <span class="status-badge status-{{ $maintenance['status'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $maintenance['status'])) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <span class="priority-badge priority-{{ $maintenance['priority'] }}">
                                    {{ ucfirst($maintenance['priority']) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <strong>{{ $maintenance['due_date'] }}</strong>
                                <br>
                                <small class="text-muted">{{ $maintenance['maintenance_type'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="maintenance-actions">
                                @if($maintenance['status'] === 'scheduled' && in_array(session('role'), ['admin', 'supervisor']))
                                <button class="btn-action btn-complete" 
                                        onclick="markCompleted('{{ $maintenance['id'] }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                <button class="btn-action btn-reschedule" 
                                        onclick="reschedule('{{ $maintenance['id'] }}')">
                                    <i class="fas fa-calendar"></i>
                                </button>
                                @endif
                                <button class="btn-action btn-details" 
                                        onclick="viewDetails('{{ $maintenance['id'] }}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    @if($maintenance['description'])
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ $maintenance['description'] }}
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Maintenance Scheduled</h5>
                    <p class="text-muted">No maintenance activities are currently scheduled.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleMaintenanceModal">
                        <i class="fas fa-plus me-2"></i>Schedule First Maintenance
                    </button>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Schedule Maintenance Modal -->
@if(in_array(session('role'), ['admin', 'supervisor']))
<div class="modal fade" id="scheduleMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>Schedule Maintenance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleMaintenanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assetSelect" class="form-label">Select Asset</label>
                            <select class="form-control" id="assetSelect" name="asset_id" required>
                                <option value="">Choose an asset...</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">
                                        {{ $asset->asset_name }} ({{ $asset->asset_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="maintenanceType" class="form-label">Maintenance Type</label>
                            <select class="form-control" id="maintenanceType" name="maintenance_type" required>
                                <option value="">Select type...</option>
                                <option value="routine">Routine Maintenance</option>
                                <option value="preventive">Preventive Maintenance</option>
                                <option value="corrective">Corrective Maintenance</option>
                                <option value="emergency">Emergency Repair</option>
                                <option value="inspection">Inspection</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="scheduledDate" class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control" id="scheduledDate" name="scheduled_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Describe the maintenance work to be performed..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estimatedCost" class="form-label">Estimated Cost (RM)</label>
                            <input type="number" class="form-control" id="estimatedCost" name="estimated_cost" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assignedTo" class="form-label">Assigned To</label>
                            <input type="text" class="form-control" id="assignedTo" name="assigned_to" 
                                   placeholder="Technician or vendor name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-1"></i>Schedule Maintenance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const scheduledDateInput = document.getElementById('scheduledDate');
    if (scheduledDateInput) {
        scheduledDateInput.min = new Date().toISOString().split('T')[0];
    }

    // Filter change handling
    document.querySelectorAll('#maintenanceFilters select').forEach(select => {
        select.addEventListener('change', function() {
            filterMaintenance();
        });
    });

    // Schedule maintenance form handling
    const scheduleForm = document.getElementById('scheduleMaintenanceForm');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            scheduleMaintenance();
        });
    }
});

function filterMaintenance() {
    const status = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    const centre = document.getElementById('centreFilter').value;
    const type = document.getElementById('typeFilter').value;

    // Make AJAX request to filter maintenance
    fetch('/assets/maintenance/filter?' + new URLSearchParams({
        status: status,
        priority: priority,
        centre: centre,
        type: type
    }))
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMaintenanceList(data.maintenance);
        }
    })
    .catch(error => {
        console.error('Error filtering maintenance:', error);
    });
}

function updateMaintenanceList(maintenance) {
    const listContainer = document.getElementById('maintenanceList');
    
    if (maintenance.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Maintenance Found</h5>
                <p class="text-muted">No maintenance activities match your current filters.</p>
            </div>
        `;
        return;
    }

    let html = '';
    maintenance.forEach(item => {
        html += generateMaintenanceItem(item);
    });
    
    listContainer.innerHTML = html;
}

function generateMaintenanceItem(maintenance) {
    return `
        <div class="schedule-item ${maintenance.status_class}">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="asset-info">
                        <img src="${maintenance.asset_image}" 
                             alt="${maintenance.asset_name}" 
                             class="asset-image"
                             onerror="this.src='{{ asset('images/default-asset.png') }}'">
                        <div class="asset-details">
                            <h6>${maintenance.asset_name}</h6>
                            <small>${maintenance.asset_id} | ${maintenance.asset_type}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <span class="status-badge status-${maintenance.status}">
                            ${maintenance.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                        </span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <span class="priority-badge priority-${maintenance.priority}">
                            ${maintenance.priority.charAt(0).toUpperCase() + maintenance.priority.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <strong>${maintenance.due_date}</strong>
                        <br>
                        <small class="text-muted">${maintenance.maintenance_type}</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="maintenance-actions">
                        ${generateActionButtons(maintenance)}
                    </div>
                </div>
            </div>
            ${maintenance.description ? `
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            ${maintenance.description}
                        </small>
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

function generateActionButtons(maintenance) {
    let buttons = '';
    
    @if(in_array(session('role'), ['admin', 'supervisor']))
    if (maintenance.status === 'scheduled') {
        buttons += `
            <button class="btn-action btn-complete" onclick="markCompleted('${maintenance.id}')">
                <i class="fas fa-check"></i>
            </button>
        `;
    }
    
    buttons += `
        <button class="btn-action btn-reschedule" onclick="reschedule('${maintenance.id}')">
            <i class="fas fa-calendar"></i>
        </button>
    `;
    @endif
    
    buttons += `
        <button class="btn-action btn-details" onclick="viewDetails('${maintenance.id}')">
            <i class="fas fa-eye"></i>
        </button>
    `;
    
    return buttons;
}

function scheduleMaintenance() {
    const formData = new FormData(document.getElementById('scheduleMaintenanceForm'));
    
    fetch('/assets/maintenance/schedule', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Maintenance scheduled successfully!');
            location.reload();
        } else {
            alert('Error scheduling maintenance: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while scheduling maintenance.');
    });
}

function markCompleted(maintenanceId) {
    if (confirm('Mark this maintenance as completed?')) {
        fetch(`/assets/maintenance/${maintenanceId}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Maintenance marked as completed!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function reschedule(maintenanceId) {
    const newDate = prompt('Enter new scheduled date (YYYY-MM-DD):');
    if (newDate && /^\d{4}-\d{2}-\d{2}$/.test(newDate)) {
        fetch(`/assets/maintenance/${maintenanceId}/reschedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ scheduled_date: newDate })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Maintenance rescheduled successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function viewDetails(maintenanceId) {
    // Open maintenance details modal or redirect
    window.location.href = `/assets/maintenance/${maintenanceId}`;
}
</script>
@endsection