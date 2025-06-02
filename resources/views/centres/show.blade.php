@extends('layouts.app')

@section('title', $centre['name'])

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $centre['name'] }}</h1>
        <div>
            <a href="{{ route(session('role') . '.centres') }}" class="btn btn-secondary btn-sm shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Centres
            </a>
            <a href="{{ route(session('role') . '.centres.assets', $centre['id']) }}" class="btn btn-info btn-sm shadow-sm mr-2">
                <i class="fas fa-boxes fa-sm text-white-50"></i> View Assets
            </a>
            <a href="#" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Centre
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Location</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $centre['location'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Staff</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $centre['staff_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Trainees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $centre['trainee_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Assets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $centre['asset_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Centre Details</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">Centre ID</th>
                                    <td>{{ $centre['id'] }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $centre['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td>{{ $centre['location'] }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $centre['status'] == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($centre['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Capacity</h6>
                </div>
                <div class="card-body">
                    <h4 class="small font-weight-bold">Trainees <span class="float-right">{{ $centre['trainee_count'] }} / {{ $centre['capacity'] }}</span></h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($centre['trainee_count'] / $centre['capacity']) * 100 }}%"></div>
                    </div>
                    
                    <h4 class="small font-weight-bold">Staff <span class="float-right">{{ $centre['staff_count'] }}</span></h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($centre['staff_count'] / 15) * 100 }}%"></div>
                    </div>
                    
                    <h4 class="small font-weight-bold">Assets <span class="float-right">{{ $centre['asset_count'] }}</span></h4>
                    <div class="progress">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($centre['asset_count'] / 50) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection