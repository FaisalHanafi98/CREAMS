@extends('layouts.app')

@section('title', 'Centres Management')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Centres Management</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Centre
        </a>
    </div>
    
    <div class="row">
        @foreach($centres as $centre)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $centre['name'] }}</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Centre Actions:</div>
                                <a class="dropdown-item" href="{{ route(session('role') . '.centres.show', $centre['id']) }}">View Details</a>
                                <a class="dropdown-item" href="{{ route(session('role') . '.centres.assets', $centre['id']) }}">View Assets</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Edit Centre</a>
                            </div>
                        </div>
                    </div>
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
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <p class="mb-0 text-muted">Staff</p>
                                <h6>{{ $centre['staff_count'] }}</h6>
                            </div>
                            <div>
                                <p class="mb-0 text-muted">Trainees</p>
                                <h6>{{ $centre['trainee_count'] }}</h6>
                            </div>
                            <div>
                                <p class="mb-0 text-muted">Assets</p>
                                <h6>{{ $centre['asset_count'] }}</h6>
                            </div>
                        </div>
                        
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Capacity</div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($centre['trainee_count'] / $centre['capacity']) * 100 }}%" aria-valuenow="{{ ($centre['trainee_count'] / $centre['capacity']) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small>{{ $centre['trainee_count'] }} / {{ $centre['capacity'] }} ({{ round(($centre['trainee_count'] / $centre['capacity']) * 100) }}%)</small>
                    </div>
                    <div class="card-footer bg-white">
                        <span class="badge badge-{{ $centre['status'] == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($centre['status']) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection