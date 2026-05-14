@extends('layouts.app')

@section('title', 'Asset Reports - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-chart-bar me-2 text-primary"></i>Asset Reports
        </h2>
        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Assets
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Total Assets</div>
                    <div class="fw-bold fs-4 text-primary">{{ $summary['total_assets'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Available</div>
                    <div class="fw-bold fs-4 text-success">{{ $summary['available'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">In Use</div>
                    <div class="fw-bold fs-4 text-info">{{ $summary['in_use'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Total Value</div>
                    <div class="fw-bold fs-4 text-success">RM {{ number_format($summary['total_value'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- By Category -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="fas fa-tags text-primary me-2"></i>Assets by Category
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                            <th>Total Value</th>
                            <th>Avg Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byCategory as $cat)
                        <tr>
                            <td>{{ $cat->category_name ?? $cat->category ?? 'Uncategorised' }}</td>
                            <td>{{ $cat->count }}</td>
                            <td>RM {{ number_format($cat->total_value ?? 0, 2) }}</td>
                            <td>RM {{ $cat->count > 0 ? number_format(($cat->total_value ?? 0) / $cat->count, 2) : '0.00' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(empty($byCategory))
                <div class="text-center py-4 text-muted">No category data available</div>
            @endif
        </div>
    </div>

    <!-- By Status -->
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="fas fa-tachometer-alt text-warning me-2"></i>Assets by Status
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byStatus as $status)
                        <tr>
                            <td>
                                @php
                                    $statusColors = ['available' => 'success', 'in_use' => 'primary', 'maintenance' => 'warning', 'disposed' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$status->status ?? 'available'] ?? 'secondary' }}">
                                    {{ ucfirst($status->status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td>{{ $status->count }}</td>
                            <td>
                                @php $total = $summary['total_assets'] ?: 1; @endphp
                                <div class="progress" style="height: 8px; min-width: 100px;">
                                    <div class="progress-bar bg-{{ $statusColors[$status->status ?? 'available'] ?? 'secondary' }}"
                                         style="width: {{ ($status->count / $total) * 100 }}%"></div>
                                </div>
                                {{ round(($status->count / $total) * 100, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
