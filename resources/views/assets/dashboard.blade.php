@extends('layouts.app')

@section('title', 'Asset Management - CREAMS')

@push('styles')
    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --success-color: #2ed573;
            --warning-color: #ffa502;
            --danger-color: #ff4757;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
        }

        .asset-dashboard {
            background: var(--light-bg);
            min-height: 100vh;
            padding: 20px 0;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(50, 189, 234, 0.3);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .asset-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .asset-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .asset-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-rented {
            background: #fff3cd;
            color: #856404;
        }

        .status-maintenance {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin: 2px;
            transition: all 0.3s ease;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid var(--border-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .section-divider {
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin: 20px 0 10px 0;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
    </style>
@endpush

@section('content')
    <div class="asset-dashboard">
        <div class="container-fluid">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2 font-weight-bold">Asset Management</h1>
                            <p class="mb-0 opacity-90">Manage equipment, tools, and resources for
                                {{ session('centre_name', 'your centre') }}</p>
                        </div>
                        <div class="col-md-4 text-md-right">
                            <button class="btn btn-light btn-lg" data-toggle="modal" data-target="#addAssetModal">
                                <i class="fas fa-plus mr-2"></i>Add New Asset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-primary">{{ $stats['total'] ?? 0 }}</h3>
                                <p class="mb-0">Total Asset</p>
                            </div>
                            <i class="fas fa-boxes fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-success">{{ $stats['available'] ?? 0 }}</h3>
                                <p class="mb-0">Available</p>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-warning">{{ $stats['rented'] ?? 0 }}</h3>
                                <p class="mb-0">In Use</p>
                            </div>
                            <i class="fas fa-hand-paper fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-danger">{{ $stats['maintenance'] ?? 0 }}</h3>
                                <p class="mb-0">Maintenance</p>
                            </div>
                            <i class="fas fa-tools fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-3">
                        <label>Search Asset</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or ID..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Category</option>
                            <option value="equipment" {{ request('category') == 'equipment' ? 'selected' : '' }}>Equipment
                            </option>
                            <option value="tools" {{ request('category') == 'tools' ? 'selected' : '' }}>Tools</option>
                            <option value="furniture" {{ request('category') == 'furniture' ? 'selected' : '' }}>Furniture
                            </option>
                            <option value="technology" {{ request('category') == 'technology' ? 'selected' : '' }}>
                                Technology</option>
                        </select>
                    </div>
                    @if (session('role') === 'admin')
                        <div class="col-md-2">
                            <label>Centre</label>
                            <select name="centre" class="form-control">
                                <option value="">All Centres</option>
                                @foreach (\App\Models\Centre::where('centre_status', 'active')->get() as $centre)
                                    <option value="{{ $centre->centre_id }}"
                                        {{ request('centre') == $centre->centre_id ? 'selected' : '' }}>
                                        {{ $centre->centre_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available
                            </option>
                            <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>
                                Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times mr-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Asset Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Asset ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Centre</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                    <th>Current User</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets ?? [] as $asset)
                                    <tr>
                                        <td><strong>{{ $asset->asset_tag ?? $asset->id }}</strong></td>
                                        <td>{{ $asset->asset_name ?? 'Unnamed Asset' }}</td>
                                        <td>
                                            <span
                                                class="badge bg-info">{{ ucfirst($asset->category->name ?? 'General') }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-secondary">{{ $asset->centre->centre_name ?? 'Unassigned' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $condition = $asset->condition ?? 'unknown';
                                                $conditionClass =
                                                    $condition === 'new'
                                                        ? 'bg-success'
                                                        : ($condition === 'good'
                                                            ? 'bg-primary'
                                                            : ($condition === 'fair'
                                                                ? 'bg-warning text-dark'
                                                                : 'bg-danger'));
                                            @endphp
                                            <span class="badge {{ $conditionClass }}">{{ ucfirst($condition) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $status = $asset->status ?? 'available';
                                                $statusClass =
                                                    $status === 'available'
                                                        ? 'status-available'
                                                        : ($status === 'in_use'
                                                            ? 'status-rented'
                                                            : 'status-maintenance');
                                                $statusDisplay = str_replace('_', ' ', ucwords($status, '_'));
                                            @endphp
                                            <span class="asset-status {{ $statusClass }}">{{ $statusDisplay }}</span>
                                        </td>
                                        <td>
                                            @if ($asset->assigned_to && $asset->assignedTo)
                                                <small
                                                    class="text-muted">{{ $asset->assignedTo->name ?? 'User #' . $asset->assigned_to }}</small>
                                            @else
                                                <small class="text-muted">None</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if (($asset->status ?? 'available') === 'available')
                                                <button class="btn btn-success btn-action btn-sm"
                                                    onclick="rentAsset('{{ $asset->id ?? '' }}')">
                                                    <i class="fas fa-hand-paper mr-1"></i>Use
                                                </button>
                                            @elseif(($asset->status ?? 'available') === 'rented')
                                                <button class="btn btn-warning btn-action btn-sm"
                                                    onclick="returnAsset('{{ $asset->id ?? '' }}')">
                                                    <i class="fas fa-undo mr-1"></i>Return
                                                </button>
                                            @endif
                                            <button class="btn btn-primary btn-action btn-sm"
                                                onclick="editAsset('{{ $asset->id ?? '' }}')">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                                            <button class="btn btn-danger btn-action btn-sm"
                                                onclick="deleteAsset('{{ $asset->id ?? '' }}')">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-boxes fa-3x mb-3 opacity-50"></i>
                                                <h5>No assets found</h5>
                                                <p>Add your first asset to get started!</p>
                                                <button class="btn btn-primary" data-toggle="modal"
                                                    data-target="#addAssetModal">
                                                    <i class="fas fa-plus mr-2"></i>Add New Asset
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if (isset($assets) && method_exists($assets, 'links'))
                @include('components.custom-pagination', ['items' => $assets])
            @endif
        </div>
    </div>

    <!-- Add Asset Modal -->
    <div class="modal fade" id="addAssetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Add New Asset</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addAssetForm" method="POST" action="{{ route('centre.assets.store') }}">
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- Basic Information -->
                        <h6 class="text-primary mb-3"><i class="fas fa-info-circle mr-2"></i>Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required
                                        placeholder="Enter asset name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Code</label>
                                    <input type="text" name="asset_tag" class="form-control"
                                        placeholder="Auto-generated if empty">
                                    <small class="form-text text-muted">Leave blank for auto-generation</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">Select Category</option>
                                        @if (isset($categories))
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="1">Equipment</option>
                                            <option value="2">Tools</option>
                                            <option value="3">Furniture</option>
                                            <option value="4">Technology</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            @if (session('role') === 'admin' && !session('centre_id'))
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Centre <span class="text-danger">*</span></label>
                                        <select name="centre_id" class="form-control" required>
                                            <option value="">Select Centre</option>
                                            @foreach (\App\Models\Centre::where('centre_status', 'active')->get() as $centre)
                                                <option value="{{ $centre->centre_id }}">{{ $centre->centre_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Centre</label>
                                        <input type="text" class="form-control"
                                            value="{{ \App\Models\Centre::where('centre_id', session('centre_id', '01'))->first()->centre_name ?? 'Unknown Centre' }}"
                                            readonly>
                                        <input type="hidden" name="centre_id" value="{{ session('centre_id', '01') }}">
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="available" selected>Available</option>
                                        <option value="in_use">In Use</option>
                                        <option value="maintenance">Under Maintenance</option>
                                        <option value="retired">Retired</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the asset"></textarea>
                        </div>

                        <!-- Technical Details -->
                        <h6 class="text-primary mb-3 mt-4"><i class="fas fa-cogs mr-2"></i>Technical Details</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" name="brand" class="form-control"
                                        placeholder="e.g., Dell, HP">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Model</label>
                                    <input type="text" name="model" class="form-control"
                                        placeholder="Model number">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Serial Number</label>
                                    <input type="text" name="serial_number" class="form-control"
                                        placeholder="Serial/ID number">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Specifications</label>
                            <textarea name="specifications" class="form-control" rows="2"
                                placeholder="Technical specifications, features, etc."></textarea>
                        </div>

                        <!-- Financial Information -->
                        <h6 class="text-primary mb-3 mt-4"><i class="fas fa-dollar-sign mr-2"></i>Financial Information
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Purchase Price</label>
                                    <input type="number" name="purchase_price" class="form-control" min="0"
                                        step="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Purchase Date</label>
                                    <input type="date" name="purchase_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Warranty (Months)</label>
                                    <input type="number" name="warranty_months" class="form-control" min="0"
                                        placeholder="12">
                                </div>
                            </div>
                        </div>

                        <!-- Physical Condition & Location -->
                        <h6 class="text-primary mb-3 mt-4"><i class="fas fa-map-marker-alt mr-2"></i>Condition & Location
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Condition <span class="text-danger">*</span></label>
                                    <select name="condition" class="form-control" required>
                                        <option value="">Select Condition</option>
                                        <option value="new">New</option>
                                        <option value="excellent">Excellent</option>
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                        <option value="broken">Broken</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" class="form-control"
                                        placeholder="e.g., Room 101, Storage Area">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes or comments"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Add Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rent Asset Modal -->
    <div class="modal fade" id="rentAssetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-paper mr-2"></i>Use Asset</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rentAssetForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>User Name <span class="text-danger">*</span></label>
                            <input type="text" name="user_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Quantity Needed <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Expected Return Date</label>
                            <input type="date" name="expected_return_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Purpose</label>
                            <textarea name="purpose" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-2"></i>Confirm Use
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function rentAsset(assetId) {
            document.getElementById('rentAssetForm').action = `/assets/${assetId}/rent`;
            $('#rentAssetModal').modal('show');
        }

        function returnAsset(assetId) {
            if (confirm('Are you sure you want to mark this asset as returned?')) {
                // Create a form to submit the return request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/assets/${assetId}/return`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editAsset(assetId) {
            window.location.href = `/assets/${assetId}/edit`;
        }

        function deleteAsset(assetId) {
            if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/assets/${assetId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@endpush
