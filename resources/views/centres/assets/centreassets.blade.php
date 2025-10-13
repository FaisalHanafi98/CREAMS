@extends('layouts.app')

@section('title', $centre->centre_name . ' - Asset')

@section('content')
    <div class="assets-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $centre->centre_name }} Asset</h1>
                <p class="subtitle">Asset management for {{ $centre->centre_name }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('centres.show', $centre->centre_id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Centre
                </a>
                @if (in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('assets.create') }}?centre={{ $centre->centre_name }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Asset
                    </a>
                @endif
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stats['total_assets'] ?? 0 }}</h3>
                        <p>Total Asset</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stats['available'] ?? 0 }}</h3>
                        <p>Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stats['maintenance'] ?? 0 }}</h3>
                        <p>Maintenance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign text-info"></i>
                    </div>
                    <div class="stat-content">
                        <h3>RM {{ number_format($stats['total_value'] ?? 0, 2) }}</h3>
                        <p>Total Value</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Asset Table --}}
        <div class="assets-table-card">
            <div class="table-header">
                <h3>Asset Inventory</h3>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="assetSearch" placeholder="Search assets..." class="form-control">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="table-body">
                @if ($assets && count($assets) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="assetsTable">
                            <thead>
                                <tr>
                                    <th>Asset ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Brand</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assets as $asset)
                                    <tr>
                                        <td>
                                            <code>{{ $asset->asset_id }}</code>
                                        </td>
                                        <td>
                                            <div class="asset-info">
                                                <img src="{{ asset('images/default-asset.png') }}" alt="Asset"
                                                    class="asset-thumbnail">
                                                <div>
                                                    <strong>{{ $asset->asset_name }}</strong>
                                                    @if ($asset->asset_note)
                                                        <br><small
                                                            class="text-muted">{{ Str::limit($asset->asset_note, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">{{ $asset->asset_type ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $asset->asset_brand ?? 'N/A' }}</td>
                                        <td>
                                            <span
                                                class="quantity-badge {{ $asset->asset_quantity <= 5 ? 'quantity-low' : ($asset->asset_quantity <= 10 ? 'quantity-medium' : 'quantity-high') }}">
                                                {{ $asset->asset_quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($asset->asset_price)
                                                <strong>RM {{ number_format($asset->asset_price, 2) }}</strong>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($asset->asset_last_updated)
                                                {{ $asset->asset_last_updated->format('M j, Y') }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('assets.show', $asset->asset_id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (in_array(session('role'), ['admin', 'supervisor']))
                                                    <a href="{{ route('assets.edit', $asset->asset_id) }}"
                                                        class="btn btn-sm btn-outline-warning" title="Edit Asset">
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

                    {{-- Pagination --}}
                    @if ($assets instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="pagination-wrapper">
                            @include('components.custom-pagination', ['items' => $assets])
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h4>No Asset Found</h4>
                        <p>This centre doesn't have any assets registered yet.</p>
                        @if (in_array(session('role'), ['admin', 'supervisor']))
                            <a href="{{ route('assets.create') }}?centre={{ $centre->centre_name }}"
                                class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Asset
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .assets-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .subtitle {
            color: #6c757d;
            margin: 0;
            font-size: 1.1rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-icon {
            font-size: 2rem;
            padding: 15px;
            border-radius: 10px;
            background: rgba(0, 123, 255, 0.1);
        }

        .stat-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            color: #2c3e50;
        }

        .stat-content p {
            margin: 0;
            color: #6c757d;
            font-weight: 500;
        }

        .assets-table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #007bff, #6c757d);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .search-box {
            position: relative;
            width: 250px;
        }

        .search-box input {
            padding-right: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 20px;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-box i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }

        .table-body {
            padding: 25px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
        }

        .asset-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .asset-thumbnail {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }

        .quantity-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .quantity-high {
            background: #d4edda;
            color: #155724;
        }

        .quantity-medium {
            background: #fff3cd;
            color: #856404;
        }

        .quantity-low {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-actions {
                margin-top: 15px;
                display: flex;
                gap: 10px;
            }

            .search-box {
                width: 200px;
            }

            .asset-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable if jQuery and DataTables are available
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#assetsTable').DataTable({
                    responsive: true,
                    pageLength: 15,
                    order: [
                        [6, 'desc']
                    ], // Sort by last updated
                    columnDefs: [{
                            orderable: false,
                            targets: [7]
                        } // Disable sorting on actions column
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search assets..."
                    }
                });
            }

            // Live search functionality
            const searchInput = document.getElementById('assetSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#assetsTable tbody tr');

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

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(alert => {
                    if (alert.classList.contains('show')) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                });
            }, 5000);
        });
    </script>
@endsection
