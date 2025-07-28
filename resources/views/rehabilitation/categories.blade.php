@extends('layouts.app')

@section('title', 'Rehabilitation Category')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Rehabilitation Category</h1>
        <a href="{{ route('activities.home') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Activity
        </a>
    </div>

    @foreach($categories as $type => $typeCategories)
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-capitalize mb-3">
                    <i class="fas fa-{{ $type === 'rehabilitation' ? 'heartbeat' : 'graduation-cap' }} mr-2"></i>
                    {{ ucfirst($type) }} Activity
                </h3>
            </div>
        </div>
        <div class="row">
            @foreach($typeCategories as $category)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow h-100 category-card" 
                         style="border-left: 4px solid {{ $category->color_code }}; 
                                transition: transform 0.3s ease, box-shadow 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.1)'">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="{{ $category->icon_class }} fa-3x" style="color: {{ $category->color_code }}"></i>
                            </div>
                            <h5 class="card-title text-center font-weight-bold">{{ $category->name }}</h5>
                            <p class="card-text text-muted text-center mb-4">{{ $category->description }}</p>
                            <div class="text-center mb-3">
                                <span class="badge badge-pill" style="background-color: {{ $category->color_code }}; color: white;">
                                    {{ $category->activities_count }} Activity
                                </span>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('activities.categories.show', $category->slug ?? str_replace(' ', '-', strtolower($category->name))) }}" 
                                   class="btn btn-sm" 
                                   style="background-color: {{ $category->color_code }}; border-color: {{ $category->color_code }}; color: white;"
                                   onmouseover="this.style.opacity='0.8'"
                                   onmouseout="this.style.opacity='1'">
                                    View Activity <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    @if(empty($categories) || count($categories) === 0)
        <div class="card shadow mt-4">
            <div class="card-body text-center">
                <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Category Available</h5>
                <p class="text-muted">Contact your administrator to add activity categories.</p>
            </div>
        </div>
    @endif
</div>
@endsection