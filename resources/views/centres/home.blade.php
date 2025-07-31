@extends('layouts.app')

@section('title', 'Centre Management')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Centre Management</h1>
        @if(in_array(session('role'), ['admin', 'supervisor']))
        <a href="{{ route('centres.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Centre
        </a>
        @endif
    </div>
    
    <div class="row">
        @forelse($centres as $centre)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $centre->centre_name }}</h6>
                        <span class="badge badge-{{ $centre->centre_status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($centre->centre_status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                            <span class="ml-2">{{ $centre->centre_address ?? 'Location not specified' }}</span>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-phone text-gray-400"></i>
                            <span class="ml-2">{{ $centre->centre_phone ?? 'No phone' }}</span>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-users text-gray-400"></i>
                            <span class="ml-2">Capacity: {{ $centre->centre_capacity ?? 0 }}</span>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-xs text-gray-600">Staff</div>
                                <div class="font-weight-bold">{{ $centre->users_count ?? 0 }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-xs text-gray-600">Trainee</div>
                                <div class="font-weight-bold">{{ $centre->trainees_count ?? 0 }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-xs text-gray-600">Asset</div>
                                <div class="font-weight-bold">{{ $centre->assets_count ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <a href="{{ route('centres.show', $centre->centre_id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @if(in_array(session('role'), ['admin', 'supervisor']))
                            <a href="{{ route('centres.edit', $centre->centre_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('centres.attendance.index') }}?centre={{ $centre->centre_id }}" class="btn btn-success">
                                <i class="fas fa-clock"></i> Attendance
                            </a>
                            @endif
                            <a href="{{ route('centres.assets', $centre->centre_id) }}" class="btn btn-secondary">
                                <i class="fas fa-boxes"></i> Asset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-building fa-3x text-gray-400 mb-3"></i>
                        <h5>No centres found</h5>
                        <p class="text-muted">There are no centres registered in the system yet.</p>
                        @if(in_array(session('role'), ['admin', 'supervisor']))
                        <a href="{{ route('centres.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus mr-1"></i>Create First Centre
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection