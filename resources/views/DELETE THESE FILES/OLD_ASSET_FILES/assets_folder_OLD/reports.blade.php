@extends('layouts.app')

@section('title', 'Asset Reports & Analytics - CREAMS')

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

    .report-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .report-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        position: relative;
        min-height: 400px;
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border-color);
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .overview-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-color);
    }

    .overview-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
    }

    .overview-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 5px;
    }

    .overview-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .report-filters {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-export {
        background: var(--success-color);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .btn-export:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    .status-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-available { background-color: var(--success-color); }
    .status-in-use { background-color: var(--primary-color); }
    .status-maintenance { background-color: var(--warning-color); }
    .status-retired { background-color: #6c757d; }

    #assetByStatusChart,
    #assetByCentreChart,
    #assetByTypeChart,
    #valueDistributionChart {
        max-height: 350px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-chart-bar me-3"></i>Asset Reports & Analytics
                </h1>
                <p class="mb-0 opacity-75">Comprehensive insights and analytics for asset management</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="export-buttons">
                    <button class="btn btn-export me-2" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                    <button class="btn btn-export me-2" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </button>
                    <a href="{{ route('assets.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Asset
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="report-filters">
        <form id="reportFilters" class="row">
            <div class="col-md-3">
                <label for="dateRange" class="form-label">Date Range</label>
                <select class="form-control" id="dateRange" name="date_range">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 3 months</option>
                    <option value="365">Last year</option>
                    <option value="all">All time</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="centreFilter" class="form-label">Centre</label>
                <select class="form-control" id="centreFilter" name="centre">
                    <option value="">All Centre</option>
                    @foreach($centres as $centre)
                        <option value="{{ $centre->centre_name }}">{{ $centre->centre_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-control" id="statusFilter" name="status">
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="in-use">In Use</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="retired">Retired</option>
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

    <!-- Overview Statistics -->
    <div class="stats-overview">
        <div class="overview-card">
            <div class="overview-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="overview-value">{{ $analytics['total_assets'] }}</div>
            <div class="overview-label">Total Asset</div>
        </div>
        <div class="overview-card">
            <div class="overview-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="overview-value">RM {{ number_format($analytics['total_value'], 0) }}</div>
            <div class="overview-label">Total Value</div>
        </div>
        <div class="overview-card">
            <div class="overview-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="overview-value">{{ $analytics['utilization_rate'] }}%</div>
            <div class="overview-label">Utilization Rate</div>
        </div>
        <div class="overview-card">
            <div class="overview-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="overview-value">{{ $analytics['maintenance_due'] }}</div>
            <div class="overview-label">Maintenance Due</div>
        </div>
        <div class="overview-card">
            <div class="overview-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="overview-value">{{ $analytics['active_centres'] }}</div>
            <div class="overview-label">Active Centre</div>
        </div>
        <div class="overview-card">
            <div class="overview-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="overview-value">{{ $analytics['asset_categories'] }}</div>
            <div class="overview-label">Asset Category</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Asset Status Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Asset Status Distribution</div>
                <canvas id="assetByStatusChart"></canvas>
            </div>
        </div>

        <!-- Asset by Centre -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Asset by Centre</div>
                <canvas id="assetByCentreChart"></canvas>
            </div>
        </div>

        <!-- Asset by Type -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Asset by Type</div>
                <canvas id="assetByTypeChart"></canvas>
            </div>
        </div>

        <!-- Value Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Value Distribution by Centre</div>
                <canvas id="valueDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Reports Tables -->
    <div class="row">
        <!-- Asset Utilization Report -->
        <div class="col-12 mb-4">
            <div class="report-card">
                <h4 class="mb-3">
                    <i class="fas fa-chart-line me-2"></i>Asset Utilization Report
                </h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Asset Type</th>
                                <th>Total Units</th>
                                <th>In Use</th>
                                <th>Available</th>
                                <th>Utilization %</th>
                                <th>Avg. Age (Years)</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($utilizationReport as $item)
                            <tr>
                                <td><strong>{{ $item['type'] }}</strong></td>
                                <td>{{ $item['total'] }}</td>
                                <td>
                                    <span class="status-indicator status-in-use"></span>
                                    {{ $item['in_use'] }}
                                </td>
                                <td>
                                    <span class="status-indicator status-available"></span>
                                    {{ $item['available'] }}
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" 
                                             style="width: {{ $item['utilization'] }}%">
                                            {{ $item['utilization'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item['avg_age'] }}</td>
                                <td>RM {{ number_format($item['total_value'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Centre Distribution Report -->
        <div class="col-12 mb-4">
            <div class="report-card">
                <h4 class="mb-3">
                    <i class="fas fa-building me-2"></i>Centre Distribution Report
                </h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Centre</th>
                                <th>Total Asset</th>
                                <th>Asset Types</th>
                                <th>Total Value</th>
                                <th>Maintenance Due</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($centreReport as $centre)
                            <tr>
                                <td><strong>{{ $centre['name'] }}</strong></td>
                                <td>{{ $centre['total_assets'] }}</td>
                                <td>{{ $centre['asset_types'] }}</td>
                                <td>RM {{ number_format($centre['total_value'], 2) }}</td>
                                <td>
                                    @if($centre['maintenance_due'] > 0)
                                        <span class="badge bg-warning">{{ $centre['maintenance_due'] }}</span>
                                    @else
                                        <span class="badge bg-success">0</span>
                                    @endif
                                </td>
                                <td>{{ $centre['last_updated'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- High-Value Asset Report -->
        <div class="col-12 mb-4">
            <div class="report-card">
                <h4 class="mb-3">
                    <i class="fas fa-gem me-2"></i>High-Value Asset (>RM 1,000)
                </h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Asset ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Centre</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Last Maintenance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($highValueAssets as $asset)
                            <tr>
                                <td><code>{{ $asset->asset_id }}</code></td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $asset->asset_type }}</span>
                                </td>
                                <td>{{ $asset->centre_name ?? 'Unassigned' }}</td>
                                <td><strong>RM {{ number_format($asset->asset_price, 2) }}</strong></td>
                                <td>
                                    @if($asset->asset_quantity > 10)
                                        <span class="status-indicator status-available"></span>In Stock
                                    @elseif($asset->asset_quantity > 0)
                                        <span class="status-indicator status-maintenance"></span>Low Stock
                                    @else
                                        <span class="status-indicator status-retired"></span>Out of Stock
                                    @endif
                                </td>
                                <td>{{ $asset->asset_last_updated ? $asset->asset_last_updated->format('M j, Y') : 'Never' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart configuration with CREAMS colors
const creamsPalette = {
    primary: '#32bdea',
    secondary: '#25a6cf', 
    success: '#28a745',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#17a2b8',
    light: '#f8f9fa',
    dark: '#2c3e50'
};

document.addEventListener('DOMContentLoaded', function() {
    // Asset Status Distribution Chart
    const statusCtx = document.getElementById('assetByStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'In Use', 'Maintenance', 'Retired'],
            datasets: [{
                data: [
                    {{ $chartData['status']['available'] }},
                    {{ $chartData['status']['in_use'] }},
                    {{ $chartData['status']['maintenance'] }}, 
                    {{ $chartData['status']['retired'] }}
                ],
                backgroundColor: [
                    creamsPalette.success,
                    creamsPalette.primary,
                    creamsPalette.warning,
                    creamsPalette.dark
                ],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });

    // Asset by Centre Chart
    const centreCtx = document.getElementById('assetByCentreChart').getContext('2d');
    new Chart(centreCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($chartData['centres'])) !!},
            datasets: [{
                label: 'Number of Asset',
                data: {!! json_encode(array_values($chartData['centres'])) !!},
                backgroundColor: creamsPalette.primary,
                borderColor: creamsPalette.secondary,
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Asset by Type Chart  
    const typeCtx = document.getElementById('assetByTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($chartData['types'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($chartData['types'])) !!},
                backgroundColor: [
                    creamsPalette.primary,
                    creamsPalette.success,
                    creamsPalette.warning,
                    creamsPalette.info,
                    creamsPalette.danger,
                    creamsPalette.secondary
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });

    // Value Distribution Chart
    const valueCtx = document.getElementById('valueDistributionChart').getContext('2d');
    new Chart(valueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($chartData['values'])) !!},
            datasets: [{
                label: 'Total Value (RM)',
                data: {!! json_encode(array_values($chartData['values'])) !!},
                backgroundColor: creamsPalette.success,
                borderColor: creamsPalette.dark,
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Filter change handling
    document.querySelectorAll('#reportFilters select').forEach(select => {
        select.addEventListener('change', function() {
            // Update charts based on filters
            updateCharts();
        });
    });
});

function updateCharts() {
    // Get filter values
    const dateRange = document.getElementById('dateRange').value;
    const centre = document.getElementById('centreFilter').value;
    const status = document.getElementById('statusFilter').value;
    const type = document.getElementById('typeFilter').value;

    // Make AJAX request to update data
    fetch('/assets/reports/data?' + new URLSearchParams({
        date_range: dateRange,
        centre: centre,
        status: status,
        type: type
    }))
    .then(response => response.json())
    .then(data => {
        // Update charts with new data
        // This would update the chart data dynamically
        // Data updated successfully
    })
    .catch(error => {
        console.error('Error updating charts:', error);
    });
}

function exportReport(format) {
    const filters = new URLSearchParams({
        date_range: document.getElementById('dateRange').value,
        centre: document.getElementById('centreFilter').value,
        status: document.getElementById('statusFilter').value,
        type: document.getElementById('typeFilter').value,
        format: format
    });

    window.location.href = '/assets/reports/export?' + filters.toString();
}
</script>
@endsection