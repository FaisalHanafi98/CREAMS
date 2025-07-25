@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Asset Management Dashboard</h1>
        <div class="d-flex">
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-plus"></i> Add Asset
                </a>
                <button class="btn btn-info btn-sm" onclick="openBulkModal()">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </button>
            @endif
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Assets
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total_assets'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cube fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Available
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['available'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                In Use
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['in_use'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Maintenance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['maintenance'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Row -->
    @if($statistics['warranty_expiring'] > 0 || $statistics['requires_maintenance'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Asset Alerts</h6>
                <div class="row">
                    @if($statistics['warranty_expiring'] > 0)
                    <div class="col-md-6">
                        <strong>{{ $statistics['warranty_expiring'] }}</strong> assets have warranties expiring within 30 days
                    </div>
                    @endif
                    @if($statistics['requires_maintenance'] > 0)
                    <div class="col-md-6">
                        <strong>{{ $statistics['requires_maintenance'] }}</strong> assets require maintenance
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Asset Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('assets.index') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $search }}" placeholder="Name, code, model...">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $category == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="available" {{ $status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="in_use" {{ $status == 'in_use' ? 'selected' : '' }}>In Use</option>
                        <option value="maintenance" {{ $status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="disposed" {{ $status == 'disposed' ? 'selected' : '' }}>Disposed</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="condition" class="form-label">Condition</label>
                    <select class="form-control" id="condition" name="condition">
                        <option value="">All Conditions</option>
                        <option value="new" {{ $condition == 'new' ? 'selected' : '' }}>New</option>
                        <option value="good" {{ $condition == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="fair" {{ $condition == 'fair' ? 'selected' : '' }}>Fair</option>
                        <option value="poor" {{ $condition == 'poor' ? 'selected' : '' }}>Poor</option>
                        <option value="broken" {{ $condition == 'broken' ? 'selected' : '' }}>Broken</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('assets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Assets Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Assets List</h6>
        </div>
        <div class="card-body">
            @if($assets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="assetsTable">
                        <thead>
                            <tr>
                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                <th width="40">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                @endif
                                <th>Asset Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Condition</th>
                                <th>Assigned To</th>
                                <th>Location</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                            <tr>
                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                <td>
                                    <input type="checkbox" class="asset-checkbox" value="{{ $asset->id }}">
                                </td>
                                @endif
                                <td>
                                    <a href="{{ route('assets.show', $asset->id) }}" class="text-decoration-none">
                                        <strong>{{ $asset->asset_code }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($asset->images && count($asset->images) > 0)
                                            <img src="{{ $asset->primary_image_url }}" 
                                                 class="rounded me-2" width="40" height="40" style="object-fit: cover;">
                                        @endif
                                        <div>
                                            <div class="font-weight-bold">{{ $asset->name }}</div>
                                            @if($asset->brand || $asset->model)
                                                <small class="text-muted">{{ $asset->brand }} {{ $asset->model }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <i class="{{ $asset->category->icon ?? 'fas fa-cube' }}"></i>
                                        {{ $asset->category->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $asset->status_badge_class }}">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $asset->condition_badge_class }}">
                                        {{ ucfirst($asset->condition) }}
                                    </span>
                                </td>
                                <td>
                                    @if($asset->assignedTo)
                                        <div>
                                            <strong>{{ $asset->assignedTo->name }}</strong>
                                            @if($asset->assigned_date)
                                                <br><small class="text-muted">Since: {{ $asset->assigned_date->format('M d, Y') }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>{{ $asset->location ?? 'Not specified' }}</td>
                                <td>{{ $asset->formatted_value }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('assets.show', $asset->id) }}" 
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array(session('role'), ['admin', 'supervisor']))
                                            <a href="{{ route('assets.edit', $asset->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} 
                        of {{ $assets->total() }} results
                    </div>
                    {{ $assets->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-cube fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No assets found</h5>
                    <p class="text-gray-400">Try adjusting your search criteria or add a new asset.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('assets.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Asset
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recentActivity->count() > 0)
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Asset Activity</h6>
                </div>
                <div class="card-body">
                    @foreach($recentActivity as $activity)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <span class="badge {{ $activity->type_badge_class }}">
                                    {{ $activity->movement_type_label }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $activity->asset->name }}</div>
                                <small class="text-muted">{{ $activity->movement_description }}</small>
                                <br><small class="text-muted">{{ $activity->formatted_movement_date }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Maintenance Alerts -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Maintenance Alerts</h6>
                </div>
                <div class="card-body">
                    @if($maintenanceAlerts['overdue']->count() > 0)
                        <h6 class="text-danger">Overdue Maintenance</h6>
                        @foreach($maintenanceAlerts['overdue']->take(5) as $maintenance)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                                <div>
                                    <strong>{{ $maintenance->asset->name }}</strong>
                                    <br><small class="text-muted">
                                        Due: {{ $maintenance->scheduled_date->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if($maintenanceAlerts['due_soon']->count() > 0)
                        <h6 class="text-warning mt-3">Due Soon</h6>
                        @foreach($maintenanceAlerts['due_soon']->take(5) as $maintenance)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-clock text-warning mr-2"></i>
                                <div>
                                    <strong>{{ $maintenance->asset->name }}</strong>
                                    <br><small class="text-muted">
                                        Due: {{ $maintenance->scheduled_date->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if($maintenanceAlerts['overdue']->count() == 0 && $maintenanceAlerts['due_soon']->count() == 0)
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p>No maintenance alerts</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Bulk Actions Modal -->
@if(in_array(session('role'), ['admin', 'supervisor']))
<div class="modal fade" id="bulkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkForm" method="POST" action="{{ route('assets.bulk-update') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Action</label>
                        <select class="form-control" name="action" id="bulkAction" required>
                            <option value="">Select Action</option>
                            <option value="update_status">Update Status</option>
                            <option value="update_location">Update Location</option>
                            <option value="schedule_maintenance">Schedule Maintenance</option>
                        </select>
                    </div>

                    <div id="statusField" class="form-group" style="display: none;">
                        <label>New Status</label>
                        <select class="form-control" name="status">
                            <option value="available">Available</option>
                            <option value="in_use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="disposed">Disposed</option>
                        </select>
                    </div>

                    <div id="locationField" class="form-group" style="display: none;">
                        <label>New Location</label>
                        <input type="text" class="form-control" name="location" placeholder="Enter location">
                    </div>

                    <div id="maintenanceFields" style="display: none;">
                        <div class="form-group">
                            <label>Maintenance Type</label>
                            <select class="form-control" name="maintenance_type">
                                <option value="preventive">Preventive</option>
                                <option value="corrective">Corrective</option>
                                <option value="inspection">Inspection</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Scheduled Date</label>
                            <input type="date" class="form-control" name="maintenance_date" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <span id="selectedCount">0</span> assets selected
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.asset-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    $('.asset-checkbox').change(function() {
        updateSelectedCount();
        
        // Update select all checkbox
        const total = $('.asset-checkbox').length;
        const checked = $('.asset-checkbox:checked').length;
        $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
        $('#selectAll').prop('checked', checked === total);
    });

    // Bulk action form handling
    $('#bulkAction').change(function() {
        const action = $(this).val();
        
        // Hide all fields
        $('#statusField, #locationField, #maintenanceFields').hide();
        
        // Show relevant fields
        if (action === 'update_status') {
            $('#statusField').show();
        } else if (action === 'update_location') {
            $('#locationField').show();
        } else if (action === 'schedule_maintenance') {
            $('#maintenanceFields').show();
        }
    });

    $('#bulkForm').submit(function(e) {
        const selectedAssets = $('.asset-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedAssets.length === 0) {
            e.preventDefault();
            alert('Please select at least one asset');
            return false;
        }

        // Add selected asset IDs to form
        selectedAssets.forEach(function(id) {
            $('<input>').attr({
                type: 'hidden',
                name: 'asset_ids[]',
                value: id
            }).appendTo('#bulkForm');
        });
    });
});

function updateSelectedCount() {
    const count = $('.asset-checkbox:checked').length;
    $('#selectedCount').text(count);
}

function openBulkModal() {
    const selectedCount = $('.asset-checkbox:checked').length;
    if (selectedCount === 0) {
        alert('Please select at least one asset first');
        return;
    }
    $('#bulkModal').modal('show');
}
</script>
@endsection