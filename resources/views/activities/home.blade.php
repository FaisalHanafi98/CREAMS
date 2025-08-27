@extends('layouts.app')

@section('title', 'Activities Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities-enhanced.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="activities-enhanced-container">
    <!-- Enhanced Header with System Colors -->
    <div class="enhanced-header">
        <div class="header-backdrop"></div>
        <div class="header-content">
            <div class="header-main">
                <div class="header-text">
                    <h1 class="header-title">
                        <span class="title-icon">🎯</span>
                        Activities Management
                    </h1>
                    <p class="header-subtitle">Comprehensive rehabilitation and educational program management with real-time tracking</p>
                </div>
                @if(session('role') === 'admin')
                <div class="header-actions">
                    <a href="{{ route('activities.create') }}" class="btn-enhanced btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        <span>Create New Activity</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn-enhanced btn-tertiary dropdown-toggle" type="button" id="moreActions" data-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                            <span>More</span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="moreActions">
                            <a class="dropdown-item" href="{{ route('activities.categories') }}">
                                <i class="fas fa-th-large"></i> Manage Categories
                            </a>
                            <a class="dropdown-item" href="{{ route('activities.templates.index') }}">
                                <i class="fas fa-file-copy"></i> Activity Templates
                            </a>
                            <a class="dropdown-item" href="{{ route('activities.schedule') }}">
                                <i class="fas fa-calendar-alt"></i> Schedule Overview
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Cards with System Colors -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ rand(8, 15) }}%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['total_activities'] ?? 0 }}</h3>
                        <p class="stat-label">Total Activities</p>
                        <span class="stat-description">All registered programs</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ rand(5, 12) }}%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['active_activities'] ?? 0 }}</h3>
                        <p class="stat-label">Active Programs</p>
                        <span class="stat-description">Currently running</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ rand(10, 20) }}%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['total_sessions'] ?? 0 }}</h3>
                        <p class="stat-label">Total Sessions</p>
                        <span class="stat-description">This semester</span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-content">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ rand(3, 8) }}%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['total_enrollments'] ?? 0 }}</h3>
                        <p class="stat-label">Enrollments</p>
                        <span class="stat-description">Unique active participants</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Search and Filter Section -->
    <div class="search-filter-section">
        <div class="search-container">
            <div class="search-box-enhanced">
                <i class="fas fa-search search-icon"></i>
                <input type="text" 
                       id="globalSearch" 
                       class="search-input-enhanced" 
                       placeholder="Search activities, instructors, categories, or descriptions..."
                       autocomplete="off">
                <div class="search-loading">
                    <div class="spinner"></div>
                </div>
                <div class="search-clear" id="searchClear" style="display: none;">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                <!-- Dynamic suggestions will be populated here -->
            </div>
        </div>

        <div class="filters-container-enhanced">
            <!-- Quick Filter Tabs -->
            <div class="filter-tabs-enhanced">
                <button class="filter-tab-enhanced active" data-filter="all">
                    <span class="tab-icon"><i class="fas fa-th-large"></i></span>
                    <span class="tab-text">All Activities</span>
                    <span class="tab-count">{{ $categoryCounts['total'] ?? 0 }}</span>
                </button>
                <button class="filter-tab-enhanced" data-filter="rehabilitation">
                    <span class="tab-icon"><i class="fas fa-heartbeat"></i></span>
                    <span class="tab-text">Rehabilitation</span>
                    <span class="tab-count">{{ $categoryCounts['rehabilitation'] ?? 0 }}</span>
                </button>
                <button class="filter-tab-enhanced" data-filter="academic">
                    <span class="tab-icon"><i class="fas fa-graduation-cap"></i></span>
                    <span class="tab-text">Academic</span>
                    <span class="tab-count">{{ $categoryCounts['academic'] ?? 0 }}</span>
                </button>
                <button class="filter-tab-enhanced" data-filter="recreational">
                    <span class="tab-icon"><i class="fas fa-gamepad"></i></span>
                    <span class="tab-text">Recreational</span>
                    <span class="tab-count">{{ $categoryCounts['creative_social'] ?? 0 }}</span>
                </button>
                <button class="filter-tab-enhanced" data-filter="active">
                    <span class="tab-icon"><i class="fas fa-play-circle"></i></span>
                    <span class="tab-text">Active</span>
                    <span class="tab-count">{{ $categoryCounts['active'] ?? 0 }}</span>
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <button class="filter-btn-enhanced" id="advancedFilters">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Filters</span>
                    <span class="filter-count" id="activeFilterCount" style="display: none;">0</span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </button>
                
                <div class="filter-menu-enhanced" id="filterMenu">
                    <div class="filter-header">
                        <h4>Filter Options</h4>
                        <button class="btn-clear-filters" id="clearAllFilters">
                            <i class="fas fa-eraser"></i> Clear All
                        </button>
                    </div>
                    
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-signal"></i> Difficulty Level
                            </label>
                            <div class="checkbox-group">
                                <label class="checkbox-label-enhanced">
                                    <input type="checkbox" value="beginner" class="filter-checkbox" data-filter-type="difficulty">
                                    <span class="checkmark-enhanced"></span>
                                    <span class="checkbox-text">Beginner</span>
                                    <span class="checkbox-count">({{ $activities->where('difficulty_level', 'beginner')->count() }})</span>
                                </label>
                                <label class="checkbox-label-enhanced">
                                    <input type="checkbox" value="intermediate" class="filter-checkbox" data-filter-type="difficulty">
                                    <span class="checkmark-enhanced"></span>
                                    <span class="checkbox-text">Intermediate</span>
                                    <span class="checkbox-count">({{ $activities->where('difficulty_level', 'intermediate')->count() }})</span>
                                </label>
                                <label class="checkbox-label-enhanced">
                                    <input type="checkbox" value="advanced" class="filter-checkbox" data-filter-type="difficulty">
                                    <span class="checkmark-enhanced"></span>
                                    <span class="checkbox-text">Advanced</span>
                                    <span class="checkbox-count">({{ $activities->where('difficulty_level', 'advanced')->count() }})</span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-child"></i> Age Group
                            </label>
                            <select class="modern-select-enhanced" id="ageGroupFilter" data-filter-type="age">
                                <option value="">All Age Groups</option>
                                <option value="children">Children (4-8 years)</option>
                                <option value="adolescents">Adolescents (9-16 years)</option>
                                <option value="adults">Adults (17+ years)</option>
                                <option value="all_ages">All Ages</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-map-marker-alt"></i> Centre Location
                            </label>
                            <select class="modern-select-enhanced" id="centreFilter" data-filter-type="centre">
                                <option value="">All Centres</option>
                                @foreach(\App\Models\Centre::where('centre_status', 'active')->get() as $centre)
                                    <option value="{{ $centre->centre_id }}">{{ $centre->centre_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-user-tie"></i> Instructor
                            </label>
                            <select class="modern-select-enhanced" id="instructorFilter" data-filter-type="instructor">
                                <option value="">All Instructors</option>
                                @foreach($activities->pluck('instructor_id')->unique()->filter() as $instructor_id)
                                    @php
                                        $instructor = \App\Models\User::find($instructor_id);
                                    @endphp
                                    @if($instructor)
                                        <option value="{{ $instructor_id }}">{{ $instructor->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-calendar-alt"></i> Schedule Status
                            </label>
                            <div class="radio-group">
                                <label class="radio-label-enhanced">
                                    <input type="radio" name="scheduleStatus" value="" checked class="filter-radio" data-filter-type="schedule">
                                    <span class="radio-mark"></span>
                                    <span>All Activities</span>
                                </label>
                                <label class="radio-label-enhanced">
                                    <input type="radio" name="scheduleStatus" value="scheduled" class="filter-radio" data-filter-type="schedule">
                                    <span class="radio-mark"></span>
                                    <span>Scheduled</span>
                                </label>
                                <label class="radio-label-enhanced">
                                    <input type="radio" name="scheduleStatus" value="completed" class="filter-radio" data-filter-type="schedule">
                                    <span class="radio-mark"></span>
                                    <span>Completed</span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-users"></i> Enrollment Status
                            </label>
                            <div class="range-slider">
                                <label class="range-label">Enrollment Capacity:</label>
                                <input type="range" 
                                       id="enrollmentRange" 
                                       min="0" 
                                       max="100" 
                                       value="0" 
                                       class="slider"
                                       data-filter-type="enrollment">
                                <div class="range-labels">
                                    <span>0%</span>
                                    <span id="enrollmentValue">0%</span>
                                    <span>100%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="sort-container">
                <select class="sort-select-enhanced" id="sortOptions">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="date_newest">Newest First</option>
                    <option value="date_oldest">Oldest First</option>
                    <option value="popularity">Most Popular</option>
                    <option value="capacity">By Capacity</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="results-summary">
        <div class="results-info">
            <span class="results-count">
                Showing <span id="showingCount">0</span> of <span id="totalCount">{{ $activities->count() }}</span> activities
            </span>
            <span class="results-filters" id="activeFiltersDisplay" style="display: none;">
                <!-- Active filters will be shown here -->
            </span>
        </div>
        <div class="view-options">
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid" title="Grid View">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="list" title="List View">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Activities Grid -->
    <div class="activities-section">
        <div class="activities-grid-clean" id="activitiesGrid">
            <!-- Fallback: Show activities using PHP/Blade while JS loads -->
            @if($activities && $activities->count() > 0)
                @foreach($activities as $activity)
                <div class="activity-card-clean">
                    <!-- Card Header -->
                    <div class="card-header-clean">
                        <div class="activity-title-wrapper">
                            <h3 class="activity-title">{{ $activity->activity_name }}</h3>
                            <span class="activity-status {{ $activity->is_active ? 'active' : 'inactive' }}">
                                {{ $activity->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="activity-category">
                            @php
                                $categoryIcon = 'fas fa-tasks';
                                if(isset($activity->category)) {
                                    if(stripos($activity->category, 'rehabilitation') !== false) {
                                        $categoryIcon = 'fas fa-heartbeat';
                                    } elseif(stripos($activity->category, 'academic') !== false) {
                                        $categoryIcon = 'fas fa-graduation-cap';
                                    } elseif(stripos($activity->category, 'creative') !== false || stripos($activity->category, 'social') !== false) {
                                        $categoryIcon = 'fas fa-palette';
                                    } elseif(stripos($activity->category, 'physical') !== false) {
                                        $categoryIcon = 'fas fa-running';
                                    }
                                }
                            @endphp
                            <i class="{{ $categoryIcon }}"></i>
                            <span>{{ $activity->category->category_name ?? 'General' }}</span>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="card-content-clean">
                        <div class="activity-description">
                            <p>{{ Str::limit($activity->activity_description, 120) }}</p>
                        </div>

                        <div class="activity-stats">
                            <div class="stat">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ $activity->sessions_count ?? $activity->sessions->count() }} Sessions</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-users"></i>
                                <span>{{ $activity->enrollments_count ?? $activity->enrollments->count() }} Enrolled</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-clock"></i>
                                <span>{{ $activity->session_duration_minutes ?? 60 }}min</span>
                            </div>
                        </div>

                        <div class="activity-meta">
                            @if($activity->instructor)
                            <div class="meta-item">
                                <i class="fas fa-user-tie"></i>
                                <span>{{ $activity->instructor->name }}</span>
                            </div>
                            @endif
                            @if($activity->activity_location)
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $activity->activity_location }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="card-actions-clean">
                        <a href="{{ route('activities.show', $activity->id) }}" class="btn-clean btn-primary">
                            View Details
                        </a>
                        <a href="{{ route('activities.sessions', $activity->id) }}" class="btn-clean btn-secondary">
                            Sessions
                        </a>
                        @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                        <a href="{{ route('activities.edit', $activity->id) }}" class="btn-clean btn-tertiary">
                            Edit
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div class="empty-state-clean">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>No Activities Found</h3>
                    <p>No activities are currently available for your role.</p>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="btn-clean btn-primary">
                        Create First Activity
                    </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="loadingState" style="display: none;">
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <p>Loading activities...</p>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>No Activities Found</h3>
            <p>Try adjusting your search criteria or filters to find what you're looking for.</p>
            <button class="btn-enhanced btn-primary" id="clearFiltersBtn">
                <i class="fas fa-eraser"></i>
                Clear All Filters
            </button>
        </div>
    </div>

    <!-- Simple Pagination -->
    <div class="text-center mt-4">
        <div class="mb-2">
            <small class="text-muted">
                Page {{ $activities->currentPage() }} of {{ $activities->lastPage() }} • {{ $activities->total() }} total activities
            </small>
        </div>
        
        @if($activities->lastPage() > 1)
        <div class="d-inline-flex">
            @php
                $current = $activities->currentPage();
                $last = $activities->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp
            
            {{-- Previous --}}
            @if(!$activities->onFirstPage())
                <a href="{{ $activities->previousPageUrl() }}" class="text-decoration-none mx-1" style="color: #667eea;">‹ Prev</a>
            @endif
            
            {{-- First page --}}
            @if($start > 1)
                <a href="{{ $activities->url(1) }}" class="text-decoration-none mx-1 px-2 py-1 rounded {{ $current == 1 ? 'bg-primary text-white' : 'text-secondary' }}">1</a>
                @if($start > 2)
                    <span class="mx-1 text-muted">…</span>
                @endif
            @endif
            
            {{-- Page range --}}
            @for($page = $start; $page <= $end; $page++)
                @if($page == $current)
                    <span class="mx-1 px-2 py-1 rounded bg-primary text-white">{{ $page }}</span>
                @else
                    <a href="{{ $activities->url($page) }}" class="text-decoration-none mx-1 px-2 py-1 rounded text-secondary hover-bg-light">{{ $page }}</a>
                @endif
            @endfor
            
            {{-- Last page --}}
            @if($end < $last)
                @if($end < $last - 1)
                    <span class="mx-1 text-muted">…</span>
                @endif
                <a href="{{ $activities->url($last) }}" class="text-decoration-none mx-1 px-2 py-1 rounded text-secondary">{{ $last }}</a>
            @endif
            
            {{-- Next --}}
            @if($activities->hasMorePages())
                <a href="{{ $activities->nextPageUrl() }}" class="text-decoration-none mx-1" style="color: #667eea;">Next ›</a>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Activity Detail Modal -->
<div class="modal fade" id="activityDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities-enhanced.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the enhanced activities system
    const activitiesSystem = new EnhancedActivitiesManager({
        activities: @json($activitiesForJs ?? $activities),
        itemsPerPage: {{ $activities->perPage() }},
        currentPage: {{ $activities->currentPage() }},
        totalPages: {{ $activities->lastPage() }},
        defaultSort: 'name_asc'
    });
    
    activitiesSystem.init();
    
    // Simple filter functionality
    const filterTabs = document.querySelectorAll('.filter-tab-enhanced');
    const activityCards = document.querySelectorAll('.activity-card-clean');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            activityCards.forEach(card => {
                let shouldShow = true;
                
                if (filter === 'active') {
                    shouldShow = card.querySelector('.activity-status.active') !== null;
                } else if (filter === 'rehabilitation') {
                    shouldShow = card.textContent.toLowerCase().includes('rehabilitation');
                } else if (filter === 'academic') {
                    shouldShow = card.textContent.toLowerCase().includes('academic');
                } else if (filter === 'recreational') {
                    shouldShow = card.textContent.toLowerCase().includes('creative') || 
                              card.textContent.toLowerCase().includes('social');
                }
                
                card.style.display = (shouldShow || filter === 'all') ? 'block' : 'none';
            });
        });
    });
});
</script>
@endsection