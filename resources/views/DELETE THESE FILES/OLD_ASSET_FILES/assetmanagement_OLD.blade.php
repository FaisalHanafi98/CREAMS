<!-- resources/views/assets/index.blade.php -->
@extends('layouts.app')

@section('title', 'Assets Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Assets Management</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Asset
        </a>
    </div>
    
    <!-- Asset Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($assets) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Available</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count(array_filter($assets, function($asset) { return $asset['status'] == 'available'; })) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Maintenance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count(array_filter($assets, function($asset) { return $asset['status'] == 'maintenance'; })) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Asset Types</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count(array_unique(array_column($assets, 'type'))) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assets by center -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assets by Centre</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="assetsBycenter"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Main Training Centre
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> East Branch
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> South Campus
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> North Extension
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assets by Type</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="assetsByType"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Computer
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Furniture
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Equipment
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Vehicle
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Books
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assets Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Asset List</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Export Options:</div>
                    <a class="dropdown-item" href="#">Export as CSV</a>
                    <a class="dropdown-item" href="#">Export as PDF</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Print</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Centre</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $asset)
                            <tr>
                                <td>{{ $asset['id'] }}</td>
                                <td>{{ $asset['name'] }}</td>
                                <td>{{ $asset['type'] }}</td>
                                <td>{{ $asset['center_name'] }}</td>
                                <td>{{ $asset['quantity'] }}</td>
                                <td>
                                    @if($asset['quantity'] > 10)
                                        <span class="badge badge-success">In Stock</span>
                                    @elseif($asset['quantity'] > 0)
                                        <span class="badge badge-warning">Low Stock</span>
                                    @else
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route(session('role') . '.assets.show', $asset['id']) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Assets by center Chart
        var ctxcenter = document.getElementById("assetsBycenter");
        var assetsBycenter = new Chart(ctxcenter, {
            type: 'doughnut',
            data: {
                labels: ["Main Training center", "East Branch", "South Campus", "North Extension"],
                datasets: [{
                    data: [
                        {{ count(array_filter($assets, function($asset) { return $asset['center_name'] == 'Main Training center'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['center_name'] == 'East Branch'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['center_name'] == 'South Campus'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['center_name'] == 'North Extension'; })) }}
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
        
        // Assets by Type Chart
        var ctxType = document.getElementById("assetsByType");
        var assetsByType = new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: ["Computer", "Furniture", "Equipment", "Vehicle", "Books"],
                datasets: [{
                    data: [
                        {{ count(array_filter($assets, function($asset) { return $asset['type'] == 'Computer'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['type'] == 'Furniture'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['type'] == 'Equipment'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['type'] == 'Vehicle'; })) }},
                        {{ count(array_filter($assets, function($asset) { return $asset['type'] == 'Books'; })) }}
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be3326'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
    });
</script>
@endsection

<!-- resources/views/assets/show.blade.php -->
@extends('layouts.app')

@section('title', $asset['name'] . ' Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Asset Details: {{ $asset['name'] }}</h1>
        <div>
            <a href="{{ route(session('role') . '.assets') }}" class="btn btn-secondary btn-sm shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Assets
            </a>
            <a href="#" class="btn btn-warning btn-sm shadow-sm mr-2">
                <i class="fas fa-tools fa-sm text-white-50"></i> Maintenance
            </a>
            <a href="#" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Asset
            </a>
        </div>
    </div>
    
    <!-- Asset Details -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Status:</label>
                        @if($asset['quantity'] > 10)
                            <span class="badge badge-success ml-2">In Stock</span>
                        @elseif($asset['quantity'] > 0)
                            <span class="badge badge-warning ml-2">Low Stock</span>
                        @else
                            <span class="badge badge-danger ml-2">Out of Stock</span>
                        @endif
                        
                        <span class="badge badge-{{ $asset['status'] == 'available' ? 'success' : 'warning' }} ml-1">
                            {{ ucfirst($asset['status']) }}
                        </span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="40%">Asset ID</th>
                                    <td>{{ $asset['id'] }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $asset['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $asset['type'] }}</td>
                                </tr>
                                <tr>
                                    <th>Centre</th>
                                    <td>{{ $asset['center_name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ $asset['quantity'] }}</td>
                                </tr>
                                <tr>
                                    <th>Last Maintenance</th>
                                    <td>{{ $asset['last_maintenance'] ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Description</h6>
                </div>
                <div class="card-body">
                    <p>{{ $asset['description'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Quantity</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $asset['quantity'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Status</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ ucfirst($asset['status']) }}</div>
                                </div>
                                <div class="col-auto">
                                    @if($asset['status'] == 'available')
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    @else
                                        <i class="fas fa-tools fa-2x text-warning"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Inventory History</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="#">Export History</a>
                            <a class="dropdown-item" href="#">Print History</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="inventoryHistoryChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Maintenance Records</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($asset['last_maintenance'])
                                    <tr>
                                        <td>{{ $asset['last_maintenance'] }}</td>
                                        <td>Regular</td>
                                        <td>Scheduled maintenance check</td>
                                        <td><span class="badge badge-success">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ date('Y-m-d', strtotime($asset['last_maintenance'] . ' -3 months')) }}</td>
                                        <td>Regular</td>
                                        <td>Quarterly maintenance check</td>
                                        <td><span class="badge badge-success">Completed</span></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">No maintenance records found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Inventory History Chart
        var ctx = document.getElementById("inventoryHistoryChart");
        var inventoryHistoryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Quantity",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: [
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ rand(5, 20) }}, 
                        {{ $asset['quantity'] }}
                    ],
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'month'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 12
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            stepSize: 5,
                            min: 0,
                            max: 30
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10
                }
            }
        });
    });
</script>
@endsection