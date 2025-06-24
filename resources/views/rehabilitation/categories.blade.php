@extends('layouts.app')

@section('title', 'Rehabilitation Categories')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Rehabilitation Categories</h1>
        <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Activities
        </a>
    </div>

    <div class="row">
        @foreach($categories as $categoryName => $categoryData)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow h-100 border-left-primary">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="{{ $categoryData['icon'] }} fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title text-center font-weight-bold">{{ $categoryName }}</h5>
                        <p class="card-text text-muted text-center mb-4">{{ $categoryData['description'] }}</p>
                        <div class="text-center mb-3">
                            <span class="badge badge-primary">{{ $categoryData['count'] }} Activities</span>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('rehabilitation.categories.show', $categoryName) }}" class="btn btn-primary btn-sm">
                                View Activities <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Additional Categories -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Other Activity Categories</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <a href="{{ route('rehabilitation.categories.show', 'Mathematics') }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-calculator"></i> Mathematics
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('rehabilitation.categories.show', 'Literacy') }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-book"></i> Literacy
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('rehabilitation.categories.show', 'Computer Skills') }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-laptop"></i> Computer Skills
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('rehabilitation.categories.show', 'Art & Creativity') }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-palette"></i> Art & Creativity
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection