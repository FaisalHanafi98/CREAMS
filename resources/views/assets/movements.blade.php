@extends('layouts.app')

@section('title', 'Asset Movement & Transfer Tracking - CREAMS')

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

    .movement-header {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .movement-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .movement-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .movement-item {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .movement-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .movement-item.transfer {
        border-left-color: var(--warning-color);
        background: rgba(255, 193, 7, 0.02);
    }

    .movement-item.relocation {
        border-left-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.02);
    }

    .movement-item.assignment {
        border-left-color: var(--success-color);
        background: rgba(40, 167, 69, 0.02);
    }

    .movement-item.return {
        border-left-color: #6c757d;
        background: rgba(108, 117, 125, 0.02);
    }

    .movement-type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .type-transfer {
        background: var(--warning-color);
        color: white;
    }

    .type-relocation {
        background: var(--primary-color);
        color: white;
    }

    .type-assignment {
        background: var(--success-color);
        color: white;
    }

    .type-return {
        background: #6c757d;
        color: white;
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

    .movement-flow {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 10px 0;
    }

    .location-box {
        background: var(--light-bg);
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--dark-color);
    }

    .arrow {
        color: var(--primary-color);
        font-size: 1.2rem;
    }

    .filters-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

    .stat-movements .stat-icon {
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
    }

    .stat-transfers .stat-icon {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .stat-assignments .stat-icon {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .stat-pending .stat-icon {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
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

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -35px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary-color);
    }

    .movement-actions {
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

    .btn-view {
        background: var(--primary-color);
        color: white;
    }

    .btn-approve {
        background: var(--success-color);
        color: white;
    }

    .btn-reject {
        background: var(--danger-color);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="movement-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-exchange-alt me-3"></i>Asset Movement & Transfer Tracking
                </h1>
                <p class="mb-0 opacity-75">Track asset movements, transfers, and assignments across the system</p>
            </div>
            <div class="col-md-4 text-end">
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#createMovementModal">
                    <i class="fas fa-plus me-2"></i>Record Movement
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
        <div class="stat-card stat-movements">
            <div class="stat-icon">
                <i class="fas fa-arrows-alt"></i>
            </div>
            <div class="stat-value">{{ $statistics['total_movements'] }}</div>
            <div class="stat-label">Total Movements</div>
        </div>
        <div class="stat-card stat-transfers">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-value">{{ $statistics['pending_transfers'] }}</div>
            <div class="stat-label">Pending Transfers</div>
        </div>
        <div class="stat-card stat-assignments">
            <div class="stat-icon">
                <i class="fas fa-user-tag"></i>
            </div>
            <div class="stat-value">{{ $statistics['active_assignments'] }}</div>
            <div class="stat-label">Active Assignments</div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">{{ $statistics['this_month'] }}</div>
            <div class="stat-label">This Month</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form id="movementFilters" class="row">
            <div class="col-md-3">
                <label for="typeFilter" class="form-label">Movement Type</label>
                <select class="form-control" id="typeFilter" name="type">
                    <option value="">All Types</option>
                    <option value="transfer">Centre Transfer</option>
                    <option value="relocation">Location Change</option>
                    <option value="assignment">User Assignment</option>
                    <option value="return">Return</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-control" id="statusFilter" name="status">
                    <option value="">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="in_transit">In Transit</option>
                    <option value="cancelled">Cancelled</option>
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
                <label for="dateRange" class="form-label">Date Range</label>
                <select class="form-control" id="dateRange" name="date_range">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 3 months</option>
                    <option value="365">Last year</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Movement History -->
    <div class="movement-card">
        <h4 class="mb-4">
            <i class="fas fa-history me-2"></i>Movement History
        </h4>
        
        <div id="movementList">
            @forelse($movements as $movement)
                <div class="movement-item {{ $movement['type'] }}">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="asset-info">
                                <img src="{{ $movement['asset_image'] }}" 
                                     alt="{{ $movement['asset_name'] }}" 
                                     class="asset-image"
                                     onerror="this.src='{{ asset('images/default-asset.png') }}'">
                                <div class="asset-details">
                                    <h6>{{ $movement['asset_name'] }}</h6>
                                    <small>{{ $movement['asset_id'] }} | {{ $movement['asset_type'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <span class="movement-type-badge type-{{ $movement['type'] }}">
                                {{ ucfirst($movement['type']) }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <div class="movement-flow">
                                <div class="location-box">{{ $movement['from_location'] }}</div>
                                <i class="fas fa-arrow-right arrow"></i>
                                <div class="location-box">{{ $movement['to_location'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <strong>{{ $movement['date'] }}</strong>
                                <br>
                                <small class="text-muted">{{ $movement['performed_by'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="movement-actions">
                                <button class="btn-action btn-view" 
                                        onclick="viewMovementDetails('{{ $movement['id'] }}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($movement['status'] === 'pending' && in_array(session('role'), ['admin']))
                                <button class="btn-action btn-approve" 
                                        onclick="approveMovement('{{ $movement['id'] }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($movement['notes'])
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ $movement['notes'] }}
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Movement Records</h5>
                    <p class="text-muted">No asset movements have been recorded yet.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMovementModal">
                        <i class="fas fa-plus me-2"></i>Record First Movement
                    </button>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- Asset Movement Timeline -->
    <div class="movement-card">
        <h4 class="mb-4">
            <i class="fas fa-clock me-2"></i>Recent Activity Timeline
        </h4>
        
        <div class="timeline">
            @foreach($recentMovements as $movement)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">{{ $movement['asset_name'] }} - {{ ucfirst($movement['type']) }}</h6>
                            <p class="mb-1 text-muted">
                                Moved from <strong>{{ $movement['from_location'] }}</strong> 
                                to <strong>{{ $movement['to_location'] }}</strong>
                            </p>
                            <small class="text-muted">
                                by {{ $movement['performed_by'] }} • {{ $movement['date'] }}
                            </small>
                        </div>
                        <span class="movement-type-badge type-{{ $movement['type'] }}">
                            {{ ucfirst($movement['type']) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Create Movement Modal -->
@if(in_array(session('role'), ['admin', 'supervisor']))
<div class="modal fade" id="createMovementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Record Asset Movement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createMovementForm">
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
                            <label for="movementType" class="form-label">Movement Type</label>
                            <select class="form-control" id="movementType" name="movement_type" required>
                                <option value="">Select type...</option>
                                <option value="transfer">Centre Transfer</option>
                                <option value="relocation">Location Change</option>
                                <option value="assignment">User Assignment</option>
                                <option value="return">Return</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fromLocation" class="form-label">From Location</label>
                            <input type="text" class="form-control" id="fromLocation" name="from_location" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="toLocation" class="form-label">To Location</label>
                            <input type="text" class="form-control" id="toLocation" name="to_location" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="movementDate" class="form-label">Movement Date</label>
                            <input type="date" class="form-control" id="movementDate" name="movement_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <select class="form-control" id="reason" name="reason">
                                <option value="">Select reason...</option>
                                <option value="relocation">Facility Relocation</option>
                                <option value="maintenance">Maintenance Required</option>
                                <option value="assignment">User Assignment</option>
                                <option value="storage">Storage/Archival</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Additional notes about this movement..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Record Movement
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
    // Set maximum date to today
    const movementDateInput = document.getElementById('movementDate');
    if (movementDateInput) {
        movementDateInput.max = new Date().toISOString().split('T')[0];
        movementDateInput.value = new Date().toISOString().split('T')[0];
    }

    // Filter change handling
    document.querySelectorAll('#movementFilters select').forEach(select => {
        select.addEventListener('change', function() {
            filterMovements();
        });
    });

    // Create movement form handling
    const createForm = document.getElementById('createMovementForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            recordMovement();
        });
    }
});

function filterMovements() {
    const type = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    const centre = document.getElementById('centreFilter').value;
    const dateRange = document.getElementById('dateRange').value;

    // Make AJAX request to filter movements
    fetch('/assets/movements/filter?' + new URLSearchParams({
        type: type,
        status: status,
        centre: centre,
        date_range: dateRange
    }))
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMovementList(data.movements);
        }
    })
    .catch(error => {
        console.error('Error filtering movements:', error);
    });
}

function updateMovementList(movements) {
    const listContainer = document.getElementById('movementList');
    
    if (movements.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Movements Found</h5>
                <p class="text-muted">No movements match your current filters.</p>
            </div>
        `;
        return;
    }

    let html = '';
    movements.forEach(movement => {
        html += generateMovementItem(movement);
    });
    
    listContainer.innerHTML = html;
}

function generateMovementItem(movement) {
    return `
        <div class="movement-item ${movement.type}">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="asset-info">
                        <img src="${movement.asset_image}" 
                             alt="${movement.asset_name}" 
                             class="asset-image"
                             onerror="this.src='{{ asset('images/default-asset.png') }}'">
                        <div class="asset-details">
                            <h6>${movement.asset_name}</h6>
                            <small>${movement.asset_id} | ${movement.asset_type}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <span class="movement-type-badge type-${movement.type}">
                        ${movement.type.charAt(0).toUpperCase() + movement.type.slice(1)}
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="movement-flow">
                        <div class="location-box">${movement.from_location}</div>
                        <i class="fas fa-arrow-right arrow"></i>
                        <div class="location-box">${movement.to_location}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <strong>${movement.date}</strong>
                        <br>
                        <small class="text-muted">${movement.performed_by}</small>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="movement-actions">
                        <button class="btn-action btn-view" onclick="viewMovementDetails('${movement.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${movement.status === 'pending' ? `
                            <button class="btn-action btn-approve" onclick="approveMovement('${movement.id}')">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            ${movement.notes ? `
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            ${movement.notes}
                        </small>
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

function recordMovement() {
    const formData = new FormData(document.getElementById('createMovementForm'));
    
    fetch('/assets/movements/record', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Movement recorded successfully!');
            location.reload();
        } else {
            alert('Error recording movement: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while recording the movement.');
    });
}

function viewMovementDetails(movementId) {
    // Open movement details modal or redirect
    alert(`View details for movement ID: ${movementId}`);
    // window.location.href = `/assets/movements/${movementId}`;
}

function approveMovement(movementId) {
    if (confirm('Approve this movement?')) {
        fetch(`/assets/movements/${movementId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Movement approved!');
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
</script>
@endsection