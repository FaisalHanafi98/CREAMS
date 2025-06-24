@extends('layouts.main')

@section('title', $categoryInfo['name'] . ' Activities')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route(session('role', 'admin') . '.rehabilitation') }}">Rehabilitation</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $categoryInfo['name'] }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas {{ $categoryInfo['icon'] }} text-primary mr-2"></i>
                {{ $categoryInfo['name'] }} Activities
            </h1>
            <p class="text-muted">{{ $categoryInfo['description'] }}</p>
        </div>
        <div>
            <a href="{{ route(session('role', 'admin') . '.rehabilitation') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-2"></i>Back to Categories
            </a>
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <a href="{{ route(session('role', 'admin') . '.rehabilitation.activities.create') }}?category={{ $categoryInfo['key'] }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>Create New Activity
            </a>
            @endif
        </div>
    </div>

    <!-- Category Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Activities
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activities->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Published
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activities->where('status', 'published')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Draft
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activities->where('status', 'draft')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Recent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activities->where('created_at', '>=', now()->subDays(30))->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ $categoryInfo['name'] }} Activities
            </h6>
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <a href="{{ route(session('role', 'admin') . '.rehabilitation.activities.create') }}?category={{ $categoryInfo['key'] }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i>Add Activity
            </a>
            @endif
        </div>
        <div class="card-body">
            @if($activities->count() > 0)
                <div class="row">
                    @foreach($activities as $activity)
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card h-100 activity-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-1">{{ $activity->name }}</h5>
                                    <span class="badge badge-{{ $activity->status == 'published' ? 'success' : 'warning' }}">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </div>
                                <p class="card-text text-muted">{{ $activity->short_description ?? 'No description available' }}</p>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user mr-1"></i>
                                        Created by {{ $activity->creator->name ?? 'Unknown' }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Unknown date' }}
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if(isset($activity->difficulty_level))
                                        <span class="badge badge-pill badge-{{ 
                                            $activity->difficulty_level == 'easy' ? 'success' : 
                                            ($activity->difficulty_level == 'medium' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($activity->difficulty_level) }}
                                        </span>
                                        @endif
                                        @if(isset($activity->duration))
                                        <span class="badge badge-pill badge-secondary">
                                            {{ $activity->duration }} min
                                        </span>
                                        @endif
                                    </div>
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if(in_array(session('role'), ['admin', 'supervisor']))
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas {{ $categoryInfo['icon'] }} fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No activities available in this category</h5>
                    <p class="text-muted">Get started by creating your first {{ strtolower($categoryInfo['name']) }} activity.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route(session('role', 'admin') . '.rehabilitation.activities.create') }}?category={{ $categoryInfo['key'] }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus mr-2"></i>Create First Activity
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.activity-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: 1px solid #e3e6f0;
}

.activity-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 0.5rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}
</style>
@endsection