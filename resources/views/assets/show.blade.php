@extends('layouts.app')

@section('title', $asset->asset_name . ' - Asset Details')

@section('content')
<div class="container-fluid px-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('asset-parents.index') }}">Assets</a></li>
            <li class="breadcrumb-item active">{{ $asset->asset_name }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-box me-2 text-primary"></i>{{ $asset->asset_name }}
        </h2>
        <div class="d-flex gap-2">
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('asset-parents.edit', $asset->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-pencil-alt me-1"></i>Edit
                </a>
            @endif
            <a href="{{ route('asset-parents.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Asset Details Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-info-circle text-primary me-2"></i>Asset Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Asset Tag</label>
                            <div class="fw-semibold">{{ $asset->asset_tag ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Serial Number</label>
                            <div>{{ $asset->serial_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Category</label>
                            <div>{{ $asset->category?->name ?? $asset->category_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Centre</label>
                            <div>{{ $asset->centre?->centre_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Manufacturer</label>
                            <div>{{ $asset->manufacturer ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Model</label>
                            <div>{{ $asset->model_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Purchase Date</label>
                            <div>{{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Purchase Price</label>
                            <div>{{ $asset->purchase_price ? 'RM ' . number_format($asset->purchase_price, 2) : '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Warranty Expiry</label>
                            <div>
                                {{ $asset->warranty_expiry ? $asset->warranty_expiry->format('M d, Y') : '—' }}
                                @if($asset->warranty_expiry)
                                    @php $ws = $asset->warranty_status; @endphp
                                    <span class="badge {{ $ws === 'Active' ? 'bg-success' : ($ws === 'Expiring Soon' ? 'bg-warning text-dark' : 'bg-danger') }} ms-2">{{ $ws }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Location</label>
                            <div>{{ $asset->location_name ?? $asset->location?->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Assigned To</label>
                            <div>{{ $asset->assignedTo?->name ?? 'Unassigned' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Notes</label>
                            <div>{{ $asset->notes ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance History -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-tools text-warning me-2"></i>Maintenance History</span>
                    @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleMaintModal">
                            <i class="fas fa-plus me-1"></i>Schedule
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($asset->maintenance && count($asset->maintenance) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Cost</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asset->maintenance as $maint)
                                    <tr>
                                        <td>{{ $maint->scheduled_date ? \Carbon\Carbon::parse($maint->scheduled_date)->format('M d, Y') : '—' }}</td>
                                        <td>{{ ucfirst($maint->maintenance_type ?? 'General') }}</td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'scheduled' => 'bg-primary',
                                                    'in_progress' => 'bg-warning text-dark',
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-secondary'
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusClasses[$maint->status ?? 'scheduled'] ?? 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $maint->status ?? 'scheduled')) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($maint->priority ?? 'normal') }}</td>
                                        <td>{{ $maint->cost ? 'RM ' . number_format($maint->cost, 2) : '—' }}</td>
                                        <td>{{ $maint->notes ?? $maint->description ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-tools fa-2x mb-2"></i><br>No maintenance records
                        </div>
                    @endif
                </div>
            </div>

            <!-- Movement History -->
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-arrows-alt text-info me-2"></i>Movement History
                </div>
                <div class="card-body p-0">
                    @if($asset->movements && count($asset->movements) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Moved By</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asset->movements as $movement)
                                    <tr>
                                        <td>{{ $movement->movement_date ? \Carbon\Carbon::parse($movement->movement_date)->format('M d, Y') : '—' }}</td>
                                        <td>{{ $movement->from_location_id ?? '—' }}</td>
                                        <td>{{ $movement->to_location_id ?? '—' }}</td>
                                        <td>{{ $movedByUsers[$movement->moved_by_user_id] ?? '—' }}</td>
                                        <td>{{ $movement->reason ?? $movement->notes ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-arrows-alt fa-2x mb-2"></i><br>No movement records
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: Status & Stats -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Status</div>
                <div class="card-body text-center">
                    @php
                        $statusColors = [
                            'available' => 'success',
                            'in_use' => 'primary',
                            'maintenance' => 'warning',
                            'disposed' => 'danger'
                        ];
                        $conditionColors = [
                            'new' => 'success',
                            'good' => 'info',
                            'fair' => 'warning',
                            'poor' => 'danger',
                            'broken' => 'dark'
                        ];
                    @endphp
                    <div class="mb-3">
                        <span class="badge bg-{{ $statusColors[$asset->status ?? 'available'] ?? 'secondary' }} fs-5 px-3 py-2">
                            {{ ucfirst($asset->status ?? 'Unknown') }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        Condition: <span class="badge bg-{{ $conditionColors[$asset->condition ?? 'good'] ?? 'secondary' }}">
                            {{ ucfirst($asset->condition ?? 'Unknown') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Maintenance Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-chart-bar text-warning me-2"></i>Maintenance Summary
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="fw-bold text-primary fs-5">{{ $maintenanceStats['total'] ?? 0 }}</div>
                            <div class="text-muted small">Total</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="fw-bold text-success fs-5">{{ $maintenanceStats['completed'] ?? 0 }}</div>
                            <div class="text-muted small">Completed</div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-primary fs-5">{{ $maintenanceStats['pending'] ?? 0 }}</div>
                            <div class="text-muted small">Pending</div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-danger fs-5">{{ $maintenanceStats['overdue'] ?? 0 }}</div>
                            <div class="text-muted small">Overdue</div>
                        </div>
                    </div>
                    @if(($maintenanceStats['total_cost'] ?? 0) > 0)
                        <hr>
                        <div class="text-center">
                            <div class="text-muted small">Total Maintenance Cost</div>
                            <div class="fw-bold">RM {{ number_format($maintenanceStats['total_cost'], 2) }}</div>
                        </div>
                    @endif

                    @if($upcomingMaintenance)
                        <hr>
                        <div class="small">
                            <div class="text-muted fw-bold mb-1">Next Maintenance</div>
                            <div>{{ \Carbon\Carbon::parse($upcomingMaintenance->scheduled_date)->format('M d, Y') }}</div>
                            <div class="text-muted">{{ ucfirst($upcomingMaintenance->maintenance_type ?? 'General') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Value Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-dollar-sign text-success me-2"></i>Value
                </div>
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-success">{{ $asset->formatted_value }}</div>
                    <div class="text-muted small">Current Estimated Value</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
