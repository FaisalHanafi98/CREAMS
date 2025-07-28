@extends('layouts.app')

@section('title', $category->name . ' Activity')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="{{ $category->icon_class ?? 'fas fa-tasks' }} mr-2" style="color: {{ $category->color_code ?? '#8B5CF6' }}"></i>
            {{ $category->name }} Activity
        </h1>
        <div>
            <a href="{{ route('activities.categories') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Category
            </a>
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create New Activity
                </a>
            @endif
        </div>
    </div>

    @if($activities->count() > 0)
        <div class="row">
            @foreach($activities as $activity)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow h-100" style="border-left: 4px solid {{ $category->color_code ?? '#8B5CF6' }}">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: {{ $category->color_code ?? '#8B5CF6' }}">
                                        {{ $activity->category->name ?? $activity->category }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $activity->activity_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Code: {{ $activity->activity_code }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="{{ $category->icon_class ?? 'fas fa-tasks' }} fa-2x" style="color: {{ $category->color_code ?? '#8B5CF6' }}; opacity: 0.3;"></i>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <p class="text-sm text-gray-700 mb-3">
                                    {{ Str::limit($activity->description, 100) }}
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> {{ $activity->duration_minutes }} min
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-users"></i> {{ $activity->min_participants }}-{{ $activity->max_participants }}
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge badge-{{ $activity->difficulty_level === 'Beginner' ? 'success' : ($activity->difficulty_level === 'Intermediate' ? 'warning' : 'danger') }}">
                                        {{ $activity->difficulty_level }}
                                    </span>
                                    <span class="badge badge-{{ $activity->is_active ? 'success' : 'secondary' }}">
                                        {{ $activity->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                
                                <div class="text-center">
                                    <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-sm" style="background-color: {{ $category->color_code ?? '#8B5CF6' }}; border-color: {{ $category->color_code ?? '#8B5CF6' }}; color: white;">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    @if(in_array(session('role'), ['admin', 'supervisor']))
                                        <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($activity->creator)
                            <div class="card-footer bg-light">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> Created by {{ $activity->creator->name }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $activities->links() }}
        </div>
    @else
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="{{ $category->icon_class ?? 'fas fa-tasks' }} fa-3x" style="color: {{ $category->color_code ?? '#8B5CF6' }}; opacity: 0.3;"></i>
                </div>
                <h5 class="card-title">No Activity Found</h5>
                <p class="card-text text-muted">
                    There are currently no activities in the {{ $category->name }} category.
                </p>
                <p class="text-muted mb-4">{{ $category->description }}</p>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Activity
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection