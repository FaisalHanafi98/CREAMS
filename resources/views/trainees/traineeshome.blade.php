```php
@extends('layouts.app')

@section('title', 'Trainees Home - CREAMS')

@section('styles')
<style>
    /* Card Styles */
    .rehab-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
        border-radius: 30px;
    }

    .avatar-container {
        height: 80px;
        width: 80px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 50%;
    }

    .avatar-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
    }

    .empty-state img {
        max-width: 200px;
        margin-bottom: 1.5rem;
    }

    /* Filter badge styles */
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    .badge a {
        color: white;
        text-decoration: none;
    }

    .badge a:hover {
        opacity: 0.8;
    }
    
    /* Stats cards */
    .border-left-primary {
        border-left: 4px solid var(--primary-color) !important;
    }
    
    .border-left-success {
        border-left: 4px solid var(--success-color) !important;
    }
    
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 4px solid var(--warning-color) !important;
    }
    
    .text-xs {
        font-size: .7rem;
    }
    
    .text-primary {
        color: var(--primary-color) !important;
    }
    
    .text-success {
        color: var(--success-color) !important;
    }
    
    .text-info {
        color: #36b9cc !important;
    }
    
    .text-warning {
        color: var(--warning-color) !important;
    }
    
    .text-gray-300 {
        color: #dddfeb !important;
    }
    
    .text-gray-800 {
        color: #5a5c69 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">Trainee Management</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <span class="current">Trainee Management</span>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('traineesregistrationpage') }}" class="btn btn-primary">
                    <i class="fas fa-plus fa-sm mr-1"></i> Register New Trainee
                </a>
                <button class="btn btn-info ml-2" data-toggle="modal" data-target="#filterModal">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Trainees
                </button>
            </div>
        </div>
    </div>
    
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Content Row - Statistics -->
    <div class="row">
        <!-- Total Trainees Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Trainees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTrainees ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Centers Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Centers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ isset($traineesByCenter) ? $traineesByCenter->count() : 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condition Types Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Condition Types</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conditionTypes ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Trainees Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New Trainees (30 days)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $newTraineesCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Box -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search Trainees</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('traineeshome') }}" method="GET" class="form-inline">
                <div class="form-group mb-2 flex-grow-1">
                    <input type="text" name="search" class="form-control w-100" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">Search</button>
                @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                    <a href="{{ route('traineeshome') }}" class="btn btn-secondary mb-2 ml-2">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Active Filters</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap">
                    @if(request('search'))
                        <div class="badge badge-info m-1 p-2">
                            Search: {{ request('search') }}
                            <a href="{{ route('traineeshome', array_merge(request()->except('search'), [])) }}" class="text-white ml-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif
                    
                    @if(request('centre'))
                        <div class="badge badge-primary m-1 p-2">
                            Center: {{ request('centre') }}
                            <a href="{{ route('traineeshome', array_merge(request()->except('centre'), [])) }}" class="text-white ml-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif
                    
                    @if(request('condition'))
                        <div class="badge badge-success m-1 p-2">
                            Condition: {{ request('condition') }}
                            <a href="{{ route('traineeshome', array_merge(request()->except('condition'), [])) }}" class="text-white ml-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif
                    
                    <a href="{{ route('traineeshome') }}" class="btn btn-sm btn-outline-secondary ml-auto">
                        Clear All Filters
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Trainees by Center -->
    @if(isset($traineesByCenter) && $traineesByCenter->count() > 0)
        @foreach($traineesByCenter as $centreName => $centerTrainees)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $centreName ?? 'Unassigned' }} ({{ $centerTrainees->count() }})</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink-{{ Str::slug($centreName) }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink-{{ Str::slug($centreName) }}">
                            <div class="dropdown-header">Center Actions:</div>
                            <a class="dropdown-item" href="{{ route('traineeshome', ['centre' => $centreName]) }}">Filter by Center</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('traineesregistrationpage', ['centre' => $centreName]) }}">Add Trainee to Center</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($centerTrainees as $trainee)
                            <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-light py-2 text-center">
                                        <div class="avatar-container mb-2">
                                            <img src="{{ asset($trainee->trainee_avatar ?? 'images/default-avatar.jpg') }}" class="rounded-circle avatar-img" alt="Trainee Avatar">
                                        </div>
                                        <h5 class="card-title mb-0">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h5>
                                    </div>
                                    <div class="card-body pt-2">
                                        <p class="mb-1"><strong>Email:</strong> {{ $trainee->trainee_email }}</p>
                                        <p class="mb-1"><strong>Condition:</strong> <span class="badge badge-{{ $trainee->getConditionBadgeClassAttribute() ?? 'secondary' }}">{{ $trainee->trainee_condition }}</span></p>
                                        <p class="mb-1"><strong>Age:</strong> {{ $trainee->getAgeAttribute() ?? 'N/A' }} years</p>
                                        <p class="mb-1"><small class="text-muted">Registered: {{ $trainee->created_at ? $trainee->created_at->format('M d, Y') : 'Unknown' }}</small></p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0 text-center">
                                        <a href="{{ route('traineeprofile', ['id' => $trainee->id]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-user mr-1"></i>View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="text-center py-4 empty-state">
                    <img src="{{ asset('images/empty-state.svg') }}" alt="No trainees found" class="img-fluid mb-3">
                    <h5>No trainees found</h5>
                    <p class="text-muted">There are no trainees registered in the system yet, or none match your search criteria.</p>
                    <a href="{{ route('traineesregistrationpage') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus mr-1"></i>Register New Trainee
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('traineeshome') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Trainees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Center Filter -->
                    <div class="form-group">
                        <label for="centre">Center</label>
                        <select name="centre" id="centre" class="form-control">
                            <option value="">All Centers</option>
                            @foreach($centres ?? [] as $centre)
                                <option value="{{ $centre->centre_name }}" {{ request('centre') == $centre->centre_name ? 'selected' : '' }}>
                                    {{ $centre->centre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Condition Filter -->
                    <div class="form-group">
                        <label for="condition">Condition</label>
                        <select name="condition" id="condition" class="form-control">
                            <option value="">All Conditions</option>
                            @foreach($conditions ?? [] as $condition)
                                <option value="{{ $condition }}" {{ request('condition') == $condition ? 'selected' : '' }}>
                                    {{ $condition }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Search by name/email -->
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search by name or email..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                        <a href="{{ route('traineeshome') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Enable search on enter press
        $('#search').keypress(function(e) {
            if (e.which == 13) {
                $(this).closest('form').submit();
                return false;
            }
        });
    });
</script>
@endsection
```