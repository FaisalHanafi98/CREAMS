@extends('layouts.app')

@section('title', 'Trainee Management - CREAMS')

@section('styles')
<style>
    /* Trainee-specific styles that enhance the main layout */
    .trainee-stats .card {
        border-left: 4px solid var(--primary-color);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .trainee-stats .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .trainee-card {
        transition: transform 0.2s ease;
        border: 1px solid #e3e6f0;
    }
    
    .trainee-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    
    .avatar-container img {
        border: 3px solid #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .filter-badge {
        border-radius: 15px;
        font-size: 0.85rem;
    }
    
    .search-box {
        border-radius: 25px;
        border: 2px solid #e3e6f0;
        transition: border-color 0.3s ease;
    }
    
    .search-box:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate text-primary mr-2"></i>
            Trainee Management
        </h1>
        <div>
            <a href="{{ route('traineesregistrationpage') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i>Register New Trainee
            </a>
            <a href="#" class="btn btn-info btn-sm shadow-sm ml-2" data-toggle="modal" data-target="#filterModal">
                <i class="fas fa-filter fa-sm text-white-50 mr-1"></i>Filter Trainees
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ $error }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Statistics Row -->
    <div class="row trainee-stats">
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
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search mr-2"></i>Search Trainees
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('traineeshome') }}" method="GET" class="form-inline">
                <div class="form-group mb-2 flex-grow-1">
                    <div class="input-group w-100">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">
                    <i class="fas fa-search mr-1"></i>Search
                </button>
                @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                    <a href="{{ route('traineeshome') }}" class="btn btn-secondary mb-2 ml-2">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter mr-2"></i>Active Filters
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap">
                    @if(request('search'))
                        <span class="badge badge-info filter-badge m-1 p-2">
                            <i class="fas fa-search mr-1"></i>Search: {{ request('search') }}
                            <a href="{{ route('traineeshome', array_merge(request()->except('search'), [])) }}" class="text-white ml-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('centre'))
                        <span class="badge badge-primary filter-badge m-1 p-2">
                            <i class="fas fa-building mr-1"></i>Center: {{ request('centre') }}
                            <a href="{{ route('traineeshome', array_merge(request()->except('centre'), [])) }}" class="text-white ml-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('condition'))
                        <span class="badge badge-success filter-badge m-1 p-2">
                            <i class="fas fa-heartbeat mr-1"></i>Condition: {{ request('condition') }}
                            <a href="{{ route('traineeshome', array_merge(request()->except('condition'), [])) }}" class="text-white ml-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif
                    
                    <a href="{{ route('traineeshome') }}" class="btn btn-sm btn-outline-secondary ml-auto">
                        <i class="fas fa-broom mr-1"></i>Clear All Filters
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building mr-2"></i>{{ $centreName ?? 'Unassigned' }} 
                        <span class="badge badge-primary ml-2">{{ $centerTrainees->count() }}</span>
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink-{{ Str::slug($centreName) }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink-{{ Str::slug($centreName) }}">
                            <div class="dropdown-header">Center Actions:</div>
                            <a class="dropdown-item" href="{{ route('traineeshome', ['centre' => $centreName]) }}">
                                <i class="fas fa-filter mr-2"></i>Filter by Center
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('traineesregistrationpage', ['centre' => $centreName]) }}">
                                <i class="fas fa-plus mr-2"></i>Add Trainee to Center
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($centerTrainees as $trainee)
                            <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                                <div class="card trainee-card h-100">
                                    <div class="card-header bg-light py-2 text-center">
                                        <div class="avatar-container mb-2">
                                            <img src="{{ asset($trainee->trainee_avatar ?? 'images/default-avatar.jpg') }}" 
                                                 class="rounded-circle" width="80" height="80" alt="Trainee Avatar" 
                                                 style="object-fit: cover;">
                                        </div>
                                        <h5 class="card-title mb-0">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h5>
                                    </div>
                                    <div class="card-body pt-2">
                                        <p class="mb-1">
                                            <i class="fas fa-envelope text-muted mr-2"></i>
                                            <strong>Email:</strong> {{ $trainee->trainee_email }}
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-heartbeat text-muted mr-2"></i>
                                            <strong>Condition:</strong> 
                                            <span class="badge badge-{{ $trainee->getConditionBadgeClassAttribute() ?? 'secondary' }}">
                                                {{ $trainee->trainee_condition }}
                                            </span>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-birthday-cake text-muted mr-2"></i>
                                            <strong>Age:</strong> {{ $trainee->getAgeAttribute() ?? 'N/A' }} years
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-calendar text-muted mr-2"></i>
                                            <small class="text-muted">Registered: {{ $trainee->created_at ? $trainee->created_at->format('M d, Y') : 'Unknown' }}</small>
                                        </p>
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
                <div class="text-center py-4">
                    <img src="{{ asset('images/empty-state.svg') }}" alt="No trainees found" class="img-fluid mb-3" style="max-width: 200px;">
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
                    <h5 class="modal-title" id="filterModalLabel">
                        <i class="fas fa-filter mr-2"></i>Filter Trainees
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Center Filter -->
                    <div class="form-group">
                        <label for="centre"><i class="fas fa-building mr-2"></i>Center</label>
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
                        <label for="condition"><i class="fas fa-heartbeat mr-2"></i>Condition</label>
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
                        <label for="search"><i class="fas fa-search mr-2"></i>Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search by name or email..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-1"></i>Apply Filters
                    </button>
                    @if(request()->has('search') || request()->has('centre') || request()->has('condition'))
                        <a href="{{ route('traineeshome') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-broom mr-1"></i>Clear Filters
                        </a>
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
    
    // Enhanced search functionality
    $('#globalSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.trainee-card').each(function() {
            const traineeText = $(this).text().toLowerCase();
            if (traineeText.includes(searchTerm)) {
                $(this).closest('.col-xl-3').show();
            } else {
                $(this).closest('.col-xl-3').hide();
            }
        });
    });
});
</script>
@endsection