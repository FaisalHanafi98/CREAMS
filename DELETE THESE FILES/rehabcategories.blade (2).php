@extends('layouts.main')

@section('title', $categoryInfo['name'] . ' Activities')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rehabilitation-categories.css') }}">
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endpush

@section('content')
<div class="container-fluid" data-user-role="{{ session('role', 'admin') }}" data-category="{{ $categoryInfo['key'] }}">
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
            <a href="{{ route(session('role', 'admin') . '.rehabilitation') }}" 
               class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-2"></i>Back to Categories
            </a>
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <a href="{{ route(session('role', 'admin') . '.rehabilitation.activities.create') }}?category={{ $categoryInfo['key'] }}" 
               class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>Create New Activity
            </a>
            @endif
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="search-container">
                <div class="input-group">
                    <input type="text" class="form-control" id="categorySearch" 
                           placeholder="Search activities..." 
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="filters-row">
                <select class="form-control filter-select mr-2" id="difficultyFilter">
                    <option value="">All Difficulty Levels</option>
                    <option value="beginner" {{ request('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="intermediate" {{ request('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="advanced" {{ request('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
                
                <select class="form-control filter-select mr-2" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                
                <select class="form-control filter-select mr-2" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="individual" {{ request('type') === 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ request('type') === 'group' ? 'selected' : '' }}>Group</option>
                    <option value="both" {{ request('type') === 'both' ? 'selected' : '' }}>Both</option>
                </select>
                
                <button class="btn btn-outline-secondary" id="clearFilters">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value" 
                                 data-count="{{ $activities->total() }}">
                                0
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value" 
                                 data-count="{{ $activities->where('status', 'published')->count() }}">
                                0
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value" 
                                 data-count="{{ $activities->where('status', 'draft')->count() }}">
                                0
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
                                Recent (30 days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value" 
                                 data-count="{{ $activities->where('created_at', '>=', now()->subDays(30))->count() }}">
                                0
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

    <!-- Results Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div id="resultsCount" class="text-muted">
                    Showing {{ $activities->count() }} of {{ $activities->total() }} activities
                </div>
                <div class="view-controls">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary view-toggle active" data-view="grid">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary view-toggle" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Grid -->
    <div class="activities-container" data-view="grid">
        @if($activities->count() > 0)
            <div class="row activities-grid">
                @foreach($activities as $activity)
                <div class="col-lg-6 col-xl-4 mb-4 activity-item" 
                     data-difficulty="{{ $activity->difficulty_level ?? '' }}"
                     data-status="{{ $activity->status ?? '' }}"
                     data-type="{{ $activity->activity_type ?? '' }}">
                    <div class="card h-100 activity-card" data-activity-id="{{ $activity->id }}">
                        <div class="activity-header">
                            <h5 class="activity-title">{{ $activity->name }}</h5>
                            <div class="activity-meta">
                                <span class="badge badge-{{ $activity->status == 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($activity->status ?? 'draft') }}
                                </span>
                                @if(isset($activity->difficulty_level))
                                <span class="difficulty-badge difficulty-{{ $activity->difficulty_level }}">
                                    {{ ucfirst($activity->difficulty_level) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="activity-content">
                            <p class="activity-description">
                                {{ $activity->short_description ?? 'No description available' }}
                            </p>
                            
                            <div class="activity-details">
                                @if(isset($activity->duration))
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ $activity->duration }} minutes</span>
                                </div>
                                @endif
                                
                                @if(isset($activity->activity_type))
                                <div class="detail-item">
                                    <i class="fas fa-users"></i>
                                    <span>{{ ucfirst($activity->activity_type) }}</span>
                                </div>
                                @endif
                                
                                @if(isset($activity->age_range))
                                <div class="detail-item">
                                    <i class="fas fa-child"></i>
                                    <span>Ages {{ $activity->age_range }}</span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="activity-footer">
                                <small class="text-muted">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $activity->creator->name ?? 'Unknown' }}
                                    <br>
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Unknown date' }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="activity-actions">
                            <div class="btn-group w-100" role="group">
                                <button type="button" 
                                        class="btn btn-outline-primary btn-view"
                                        data-activity-id="{{ $activity->id }}"
                                        data-bs-toggle="tooltip" 
                                        title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-edit"
                                        data-activity-id="{{ $activity->id }}"
                                        data-bs-toggle="tooltip" 
                                        title="Edit Activity">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" 
                                        class="btn btn-outline-danger btn-delete"
                                        data-activity-id="{{ $activity->id }}"
                                        data-confirm="Are you sure you want to delete this activity?"
                                        data-bs-toggle="tooltip" 
                                        title="Delete Activity">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $activities->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas {{ $categoryInfo['icon'] }} fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-muted">No activities found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'difficulty', 'status', 'type']))
                        Try adjusting your filters to see more results.
                    @else
                        Get started by creating your first {{ strtolower($categoryInfo['name']) }} activity.
                    @endif
                </p>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route(session('role', 'admin') . '.rehabilitation.activities.create') }}?category={{ $categoryInfo['key'] }}" 
                   class="btn btn-primary mt-3">
                    <i class="fas fa-plus mr-2"></i>Create First Activity
                </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2">Loading activities...</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/rehabilitation.js') }}"></script>
<script src="{{ asset('js/activities.js') }}"></script>
<script>
// Category-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize category filters
    const categoryModule = new CategoryActivityModule();
    categoryModule.init();
});

class CategoryActivityModule {
    init() {
        this.bindFilterEvents();
        this.setupViewToggle();
        this.setupInfiniteScroll();
    }
    
    bindFilterEvents() {
        const filters = ['#categorySearch', '#difficultyFilter', '#statusFilter', '#typeFilter'];
        
        filters.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                element.addEventListener('change', () => this.applyFilters());
                if (element.type === 'text') {
                    element.addEventListener('input', this.debounce(() => this.applyFilters(), 300));
                }
            }
        });
        
        document.getElementById('clearFilters')?.addEventListener('click', () => this.clearFilters());
        document.getElementById('searchBtn')?.addEventListener('click', () => this.applyFilters());
    }
    
    applyFilters() {
        const params = new URLSearchParams();
        
        const search = document.getElementById('categorySearch')?.value;
        const difficulty = document.getElementById('difficultyFilter')?.value;
        const status = document.getElementById('statusFilter')?.value;
        const type = document.getElementById('typeFilter')?.value;
        
        if (search) params.set('search', search);
        if (difficulty) params.set('difficulty', difficulty);
        if (status) params.set('status', status);
        if (type) params.set('type', type);
        
        this.showLoading();
        window.location.search = params.toString();
    }
    
    clearFilters() {
        document.getElementById('categorySearch').value = '';
        document.getElementById('difficultyFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        
        this.showLoading();
        window.location.search = '';
    }
    
    setupViewToggle() {
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.dataset.view;
                this.switchView(view);
                
                // Update active state
                document.querySelectorAll('.view-toggle').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }
    
    switchView(view) {
        const container = document.querySelector('.activities-container');
        const grid = container.querySelector('.activities-grid');
        
        container.dataset.view = view;
        
        if (view === 'list') {
            grid.classList.remove('row');
            grid.querySelectorAll('.col-lg-6, .col-xl-4').forEach(col => {
                col.className = 'col-12 mb-3';
            });
        } else {
            grid.classList.add('row');
            grid.querySelectorAll('.col-12').forEach(col => {
                col.className = 'col-lg-6 col-xl-4 mb-4';
            });
        }
    }
    
    setupInfiniteScroll() {
        // Implement infinite scroll for better UX
        let loading = false;
        
        window.addEventListener('scroll', () => {
            if (loading) return;
            
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
                const nextPageUrl = document.querySelector('.pagination .page-link[rel="next"]')?.href;
                if (nextPageUrl) {
                    this.loadMoreActivities(nextPageUrl);
                }
            }
        });
    }
    
    async loadMoreActivities(url) {
        try {
            loading = true;
            this.showLoading();
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                // Parse and append new activities
                this.appendActivities(html);
            }
        } catch (error) {
            console.error('Failed to load more activities:', error);
        } finally {
            loading = false;
            this.hideLoading();
        }
    }
    
    appendActivities(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newActivities = doc.querySelectorAll('.activity-item');
        const grid = document.querySelector('.activities-grid');
        
        newActivities.forEach(activity => {
            grid.appendChild(activity);
        });
        
        // Update pagination
        const newPagination = doc.querySelector('.pagination');
        if (newPagination) {
            document.querySelector('.pagination')?.replaceWith(newPagination);
        }
    }
    
    showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
    
    hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}
</script>
@endpush