@extends('layouts.app')

@section('title', 'Asset Management - CREAMS')

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

    .asset-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .asset-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .asset-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 20px;
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

    .filter-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .asset-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .table th {
        background: var(--light-bg);
        border: none;
        font-weight: 600;
        color: var(--dark-color);
        padding: 15px;
    }

    .table td {
        border: none;
        padding: 15px;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: rgba(50, 189, 234, 0.05);
    }

    .asset-image {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--light-bg);
    }

    .asset-type-badge {
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .quantity-badge {
        background: var(--success-color);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        min-width: 24px;
        text-align: center;
    }

    .quantity-badge.low {
        background: var(--warning-color);
    }

    .quantity-badge.empty {
        background: var(--danger-color);
    }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .btn-action {
        padding: 5px 10px;
        border-radius: 5px;
        border: none;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-view {
        background: var(--primary-color);
        color: white;
    }

    .btn-edit {
        background: var(--warning-color);
        color: white;
    }

    .btn-delete {
        background: var(--danger-color);
        color: white;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        border-radius: 25px;
        padding-left: 45px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
    }

    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="asset-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-boxes me-3"></i>Asset Management
                </h1>
                <p class="mb-0 opacity-75">Comprehensive asset tracking and management system</p>
            </div>
            <div class="col-md-4 text-end">
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('assets.create') }}" class="btn btn-light me-2">
                    <i class="fas fa-plus me-2"></i>Add Asset
                </a>
                @endif
                <a href="{{ route('assets.reports') }}" class="btn btn-light me-2">
                    <i class="fas fa-chart-bar me-2"></i>Reports
                </a>
                <a href="{{ route('assets.maintenance') }}" class="btn btn-light me-2">
                    <i class="fas fa-tools me-2"></i>Maintenance
                </a>
                <a href="{{ route('assets.movements') }}" class="btn btn-light me-2">
                    <i class="fas fa-exchange-alt me-2"></i>Movements
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Asset</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-value">{{ $stats['types'] ?? 0 }}</div>
            <div class="stat-label">Asset Types</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-value">{{ $stats['centres'] ?? 0 }}</div>
            <div class="stat-label">Centre</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="stat-value">{{ $stats['total_quantity'] ?? 0 }}</div>
            <div class="stat-label">Total Quantity</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-value">RM {{ number_format($stats['total_value'] ?? 0, 2) }}</div>
            <div class="stat-label">Total Value</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" action="{{ route('assets.index') }}" class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label for="search" class="form-label">Search Asset</label>
                <div class="search-box">
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           placeholder="Search by name, type, brand..." 
                           value="{{ request('search') }}">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="type" class="form-label">Asset Type</label>
                <select class="form-control" id="type" name="type">
                    <option value="">All Types</option>
                    @foreach(App\Models\Asset::distinct('asset_type')->whereNotNull('asset_type')->pluck('asset_type')->sort() as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="centre" class="form-label">Centre</label>
                <select class="form-control" id="centre" name="centre">
                    <option value="">All Centre</option>
                    @foreach($centres as $centre)
                        <option value="{{ $centre->centre_name }}" {{ request('centre') == $centre->centre_name ? 'selected' : '' }}>
                            {{ $centre->centre_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Asset Table -->
    <div class="asset-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Type</th>
                        <th>Brand</th>
                        <th>Centre</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $asset->image_url }}" 
                                         alt="{{ $asset->asset_name }}" 
                                         class="asset-image me-3"
                                         onerror="this.src='{{ asset('images/default-asset.png') }}'">
                                    <div>
                                        <div class="fw-bold">{{ $asset->asset_name }}</div>
                                        <small class="text-muted">ID: {{ $asset->asset_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="asset-type-badge">{{ $asset->asset_type }}</span>
                            </td>
                            <td>{{ $asset->asset_brand ?? 'N/A' }}</td>
                            <td>{{ $asset->centre_name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="quantity-badge {{ $asset->asset_quantity <= 5 ? ($asset->asset_quantity == 0 ? 'empty' : 'low') : '' }}">
                                    {{ $asset->asset_quantity }}
                                </span>
                            </td>
                            <td>RM {{ number_format($asset->asset_price, 2) }}</td>
                            <td>
                                {{ $asset->asset_last_updated ? $asset->asset_last_updated->format('M j, Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(in_array(session('role'), ['admin', 'supervisor']))
                                    <button class="btn-action btn-edit" title="Edit Asset">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    @if(session('role') === 'admin')
                                    <button class="btn-action btn-delete" title="Delete Asset" 
                                            onclick="confirmDelete('{{ $asset->asset_id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Asset Found</h5>
                                <p class="text-muted">No assets match your current filters.</p>
                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                <a href="{{ route('assets.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add First Asset
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($assets->hasPages())
        <div class="d-flex justify-content-center py-3">
            {{ $assets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(assetId) {
    if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/assets/${assetId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
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

// Auto-submit filter form on change
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const centreSelect = document.getElementById('centre');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (centreSelect) {
        centreSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>
@endsection