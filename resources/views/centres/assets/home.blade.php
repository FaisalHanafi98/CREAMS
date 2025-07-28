@extends('layouts.app')

@section('title', 'Asset Management - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .asset-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
    }

    .asset-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .asset-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border-left: 5px solid var(--primary-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        opacity: 0.1;
        border-radius: 50%;
        transform: translate(25px, -25px);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 24px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .filter-section h5 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .asset-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .table-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 45px;
        border: none;
        border-radius: 25px;
        background: rgba(255,255,255,0.15);
        color: white;
        placeholder-color: rgba(255,255,255,0.7);
        transition: all 0.3s ease;
    }

    .search-box input::placeholder {
        color: rgba(255,255,255,0.7);
    }

    .search-box input:focus {
        background: rgba(255,255,255,0.25);
        outline: none;
    }

    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255,255,255,0.7);
        font-size: 16px;
    }

    .table th {
        background: var(--light-bg);
        border: none;
        font-weight: 600;
        color: var(--dark-color);
        padding: 20px 15px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        border: none;
        padding: 20px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(200, 80, 192, 0.05), rgba(50, 189, 234, 0.05));
        transform: scale(1.01);
        transition: all 0.3s ease;
    }

    .asset-image {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        object-fit: cover;
        background: var(--light-bg);
        border: 2px solid #e9ecef;
    }

    .asset-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .asset-details h6 {
        margin: 0;
        font-weight: 600;
        color: var(--dark-color);
    }

    .asset-details small {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .asset-type-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .quantity-badge {
        background: var(--success-color);
        color: white;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 700;
        min-width: 35px;
        text-align: center;
        display: inline-block;
    }

    .quantity-badge.low {
        background: var(--warning-color);
    }

    .quantity-badge.empty {
        background: var(--danger-color);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-view {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(200, 80, 192, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(200, 80, 192, 0.4);
    }

    .btn-light {
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        color: var(--dark-color);
        transition: all 0.3s ease;
    }

    .btn-light:hover {
        background: white;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state .empty-icon {
        font-size: 4rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .asset-header {
            text-align: center;
            padding: 1.5rem;
        }

        .asset-header h1 {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .search-box {
            width: 100%;
            margin-top: 15px;
        }

        .table-header {
            flex-direction: column;
            gap: 15px;
        }

        .asset-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .action-buttons {
            flex-wrap: wrap;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="asset-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>
                    <i class="fas fa-boxes me-3"></i>Asset Management
                </h1>
                <p>Comprehensive asset tracking and management system for {{ $centre->centre_name ?? 'All Centre' }}</p>
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
                <a href="{{ route('dashboard') }}" class="btn btn-light">
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
        <h5><i class="fas fa-filter me-2"></i>Filter Asset</h5>
        <form method="GET" action="{{ route('centres.assets.index') }}" class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label for="search" class="form-label">Search Asset</label>
                <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           placeholder="Search by name, type, brand..." 
                           value="{{ request('search') }}">
                    <i class="fas fa-search position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #adb5bd;"></i>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="type" class="form-label">Asset Type</label>
                <select class="form-control" id="type" name="type">
                    <option value="">All Types</option>
                    @if(isset($assetTypes))
                        @foreach($assetTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="centre" class="form-label">Centre</label>
                <select class="form-control" id="centre" name="centre">
                    <option value="">All Centre</option>
                    @if(isset($centres))
                        @foreach($centres as $centre)
                            <option value="{{ $centre->centre_name }}" {{ request('centre') == $centre->centre_name ? 'selected' : '' }}>
                                {{ $centre->centre_name }}
                            </option>
                        @endforeach
                    @endif
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
        <div class="table-header">
            <h3><i class="fas fa-list me-2"></i>Asset Inventory</h3>
            <div class="search-box">
                <input type="text" id="quickSearch" placeholder="Quick search assets...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="assetsTable">
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
                    @forelse($assets ?? [] as $asset)
                        <tr>
                            <td>
                                <div class="asset-info">
                                    <img src="{{ $asset->asset_avatar ? asset($asset->asset_avatar) : asset('images/default-asset.png') }}" 
                                         alt="{{ $asset->asset_name }}" 
                                         class="asset-image"
                                         onerror="this.src='{{ asset('images/default-asset.png') }}'">
                                    <div class="asset-details">
                                        <h6>{{ $asset->asset_name }}</h6>
                                        <small>ID: {{ $asset->asset_id }}</small>
                                        @if($asset->asset_note)
                                            <br><small class="text-muted">{{ Str::limit($asset->asset_note, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="asset-type-badge">{{ $asset->asset_type ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $asset->asset_brand ?? 'N/A' }}</td>
                            <td>{{ $asset->centre_name ?? $asset->center_name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="quantity-badge {{ $asset->asset_quantity <= 5 ? ($asset->asset_quantity == 0 ? 'empty' : 'low') : '' }}">
                                    {{ $asset->asset_quantity }}
                                </span>
                            </td>
                            <td>
                                @if($asset->asset_price)
                                    <strong>RM {{ number_format($asset->asset_price, 2) }}</strong>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->updated_at)
                                    {{ $asset->updated_at->format('M j, Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="View Details" onclick="viewAsset('{{ $asset->asset_id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(in_array(session('role'), ['admin', 'supervisor']))
                                    <button class="btn-action btn-edit" title="Edit Asset" onclick="editAsset('{{ $asset->asset_id }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    @if(session('role') === 'admin')
                                    <button class="btn-action btn-delete" title="Delete Asset" onclick="confirmDelete('{{ $asset->asset_id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <h4>No Asset Found</h4>
                                    <p>No assets match your current filters or none have been added yet.</p>
                                    @if(in_array(session('role'), ['admin', 'supervisor']))
                                    <a href="{{ route('assets.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add First Asset
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($assets) && $assets->hasPages())
        <div class="d-flex justify-content-center py-3">
            {{ $assets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// Quick search functionality
document.addEventListener('DOMContentLoaded', function() {
    const quickSearch = document.getElementById('quickSearch');
    const tableRows = document.querySelectorAll('#assetsTable tbody tr');
    
    if (quickSearch) {
        quickSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Auto-submit filter form on select change
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

// Asset actions
function viewAsset(assetId) {
    window.location.href = `/assets/${assetId}`;
}

function editAsset(assetId) {
    window.location.href = `/assets/${assetId}/edit`;
}

function confirmDelete(assetId) {
    if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
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
</script>
@endsection