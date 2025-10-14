@extends('layouts.app')

@section('title', $centre->centre_name . ' - Assets')

@section('content')
    <div class="asset-parents-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $centre->centre_name }} Asset Parents</h1>
                <p class="subtitle">Asset management for {{ $centre->centre_name }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('centres.show', $centre->centre_id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Centre
                </a>
                @if (in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('asset-parents.create') }}?centre={{ $centre->centre_name }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Asset Parent
                    </a>
                @endif
            </div>
        </div>

        {{-- Standardized Flash Messages --}}
        @include('components.flash-messages')

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stats['total_asset_parents'] ?? 0 }}</h3>
                        <p>Total Asset Parents</p>
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

        {{-- Asset Parents Table --}}
        <div class="asset-parents-table-card">
            <div class="table-header">
                <h3>Asset Inventory</h3>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="assetParentSearch" placeholder="Search asset parents..."
                            class="form-control">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="table-body">
                @if ($asset_parents && count($asset_parents) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="assetParentsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Purchase Price</th>
                                    <th>Manufacturer</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($asset_parents as $parent)
                                    <tr>
                                        <td>
                                            {{-- Name --}}
                                            <div class="asset-info">
                                                <img src="{{ $parent->primary_image_url ?? asset('images/default-asset.png') }}"
                                                    alt="Asset" class="asset-thumbnail">
                                                <div>
                                                    <strong>{{ $parent->name ?? ($parent->asset_parent ?? 'Unnamed Asset') }}</strong>
                                                    @if ($parent->description ?? $parent->asset_note)
                                                        <br><small
                                                            class="text-muted">{{ Str::limit($parent->description ?? $parent->asset_note, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{-- Category --}}
                                            {{ $parent->category->category_name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{-- Purchase Price --}}
                                            @if ($parent->purchase_price ?? $parent->asset_price)
                                                <strong>RM
                                                    {{ number_format($parent->purchase_price ?? $parent->asset_price, 2) }}</strong>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Manufacturer --}}
                                            {{ $parent->manufacturer ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{-- Condition --}}
                                            @php
                                                $condition =
                                                    $parent->condition ?? ($parent->asset_condition ?? 'unknown');
                                            @endphp
                                            <span
                                                class="badge
                                            @if ($condition === 'excellent') badge-success
                                            @elseif($condition === 'good') badge-primary
                                            @elseif($condition === 'fair') badge-warning
                                            @elseif($condition === 'poor') badge-danger
                                            @else badge-secondary @endif">
                                                {{ ucfirst($condition) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- Last Updated --}}
                                            @if ($parent->updated_at)
                                                {{ $parent->updated_at->format('M j, Y') }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Actions --}}
                                            <div class="action-buttons">
                                                <a href="{{ route('asset-parents.show', $parent->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (in_array(session('role'), ['admin', 'supervisor']))
                                                    <a href="{{ route('asset-parents.edit', $parent->id) }}"
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
                    @if ($asset_parents instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="pagination-wrapper">
                            @include('components.custom-pagination', ['items' => $asset_parents])
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h4>No Asset Parents Found</h4>
                        <p>This centre doesn't have any asset parents registered yet.</p>
                        @if (in_array(session('role'), ['admin', 'supervisor']))
                            <a href="{{ route('asset-parents.create') }}?centre={{ $centre->centre_name }}"
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
        .asset-parents-container {
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

        .asset-parents-table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #32bdea, #c850c0);
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
            // Live search functionality
            const searchInput = document.getElementById('assetParentSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#assetParentsTable tbody tr');

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
