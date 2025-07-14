@extends('layouts.app')

@section('title', 'Activities - CREAMS')

@section('content')
<div class="activities-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tasks"></i> Activities Management
        </h1>
        <div class="page-actions">
            @if(in_array($role, ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Activity
                </a>
            @endif
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="stats-row">
        <div class="stat-card stat-card-gradient-1">
            <div class="stat-content">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Activities</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        <div class="stat-card stat-card-gradient-2">
            <div class="stat-content">
                <h3>{{ $stats['active'] }}</h3>
                <p>Active Activities</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card stat-card-gradient-3">
            <div class="stat-content">
                <h3>{{ $stats['rehabilitation'] }}</h3>
                <p>Rehabilitation</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
        </div>
        <div class="stat-card stat-card-gradient-4">
            <div class="stat-content">
                <h3>{{ $stats['academic'] }}</h3>
                <p>Academic</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-item">
                <select id="categoryFilter" class="form-control">
                    <option value="">All Categories</option>
                    <optgroup label="Rehabilitation">
                        <option value="Physical Therapy">Physical Therapy</option>
                        <option value="Occupational Therapy">Occupational Therapy</option>
                        <option value="Speech & Language Therapy">Speech & Language Therapy</option>
                        <option value="Sensory Integration">Sensory Integration</option>
                    </optgroup>
                    <optgroup label="Academic">
                        <option value="Basic Mathematics">Basic Mathematics</option>
                        <option value="Language & Literacy">Language & Literacy</option>
                        <option value="Science Exploration">Science Exploration</option>
                        <option value="Computer Skills">Computer Skills</option>
                    </optgroup>
                </select>
            </div>
            <div class="filter-item">
                <select id="difficultyFilter" class="form-control">
                    <option value="">All Levels</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
            </div>
            <div class="filter-item">
                <select id="statusFilter" class="form-control">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="filter-item ml-auto">
                <div class="search-box">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search activities...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Activities Grid --}}
    <div class="activities-grid">
        @forelse($activities as $activity)
            <div class="activity-card" data-category="{{ $activity->category }}" 
                 data-difficulty="{{ $activity->difficulty_level }}"
                 data-status="{{ $activity->is_active ? 'active' : 'inactive' }}">
                <div class="activity-header">
                    <div class="activity-category {{ Str::slug($activity->category) }}">
                        {{ $activity->category }}
                    </div>
                    @if($activity->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </div>
                <div class="activity-body">
                    <h3 class="activity-title">{{ $activity->activity_name }}</h3>
                    <p class="activity-code">{{ $activity->activity_code }}</p>
                    <p class="activity-description">{{ Str::limit($activity->description, 100) }}</p>
                    <div class="activity-meta">
                        <span class="meta-item">
                            <i class="fas fa-users"></i> {{ $activity->age_group }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-layer-group"></i> {{ $activity->difficulty_level }}
                        </span>
                    </div>
                </div>
                <div class="activity-footer">
                    <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    @if(in_array($role, ['admin', 'supervisor']))
                        <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No Activities Found</h3>
                <p>Start by creating your first activity.</p>
                @if(in_array($role, ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Activity
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrapper">
        {{ $activities->links() }}
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection