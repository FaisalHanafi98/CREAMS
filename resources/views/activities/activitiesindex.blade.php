@extends('layouts.app')

@section('title', 'Activities Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="activities-header">
        <div class="activities-header-content">
            <div>
                <h1 class="activities-title">Activities Management</h1>
                <p class="activities-subtitle">Manage rehabilitation activities and sessions</p>
            </div>
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <div>
                <a href="{{ route('activities.create') }}" class="btn btn-light">
                    <i class="fas fa-plus-circle"></i> Create New Activity
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-row row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="activity-stat-card">
                <div class="stat-icon-wrapper">
                    <div>
                        <div class="activity-stat-value">{{ $stats['total_activities'] ?? 0 }}</div>
                        <div class="activity-stat-label">Total Activities</div>
                    </div>
                    <div class="activity-stat-icon primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> All registered activities
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="activity-stat-card">
                <div class="stat-icon-wrapper">
                    <div>
                        <div class="activity-stat-value">{{ $stats['active_activities'] ?? 0 }}</div>
                        <div class="activity-stat-label">Active Activities</div>
                    </div>
                    <div class="activity-stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> {{ round(($stats['active_activities'] / max($stats['total_activities'], 1)) * 100) }}% active
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="activity-stat-card">
                <div class="stat-icon-wrapper">
                    <div>
                        <div class="activity-stat-value">{{ $stats['total_sessions'] ?? 0 }}</div>
                        <div class="activity-stat-label">Total Sessions</div>
                    </div>
                    <div class="activity-stat-icon info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-info">
                        <i class="fas fa-clock"></i> This semester
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="activity-stat-card">
                <div class="stat-icon-wrapper">
                    <div>
                        <div class="activity-stat-value">{{ $stats['total_enrollments'] ?? 0 }}</div>
                        <div class="activity-stat-label">Total Enrollments</div>
                    </div>
                    <div class="activity-stat-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-warning">
                        <i class="fas fa-user-plus"></i> Active enrollments
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">Category</label>
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Age Group</label>
                <select id="ageFilter" class="form-select">
                    <option value="">All Ages</option>
                    <option value="3-6">3-6 years</option>
                    <option value="7-12">7-12 years</option>
                    <option value="13-18">13-18 years</option>
                    <option value="All Ages">All Ages</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Search</label>
                <div class="search-input">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search activities...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Grid -->
    <div class="activities-grid" id="activitiesGrid">
        @forelse($activities as $activity)
        <div class="activity-card fade-in-up" 
             data-category="{{ $activity->category }}"
             data-status="{{ $activity->is_active ? 'active' : 'inactive' }}"
             data-age="{{ $activity->age_group }}"
             data-name="{{ strtolower($activity->activity_name) }}">
            
            <div class="activity-card-header">
                <h3 class="activity-card-title">
                    {{ $activity->activity_name }}
                    <span class="activity-code">{{ $activity->activity_code }}</span>
                </h3>
            </div>
            
            <div class="activity-card-body">
                <div class="activity-category-badge">
                    <i class="fas fa-tag"></i> {{ $activity->category }}
                </div>
                
                <p class="activity-description">
                    {{ Str::limit($activity->description, 120) }}
                </p>
                
                <div class="activity-meta-grid">
                    <div class="activity-meta-item">
                        <i class="fas fa-child"></i>
                        <span>{{ $activity->age_group }}</span>
                    </div>
                    <div class="activity-meta-item">
                        <i class="fas fa-signal"></i>
                        <span>{{ $activity->difficulty_level }}</span>
                    </div>
                    <div class="activity-meta-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>{{ $activity->active_sessions_count }} sessions</span>
                    </div>
                    <div class="activity-meta-item">
                        <i class="fas fa-user"></i>
                        <span>{{ $activity->creator->name ?? 'System' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="activity-card-footer">
                <div class="activity-actions">
                    <a href="{{ route('activities.show', $activity->id) }}" 
                       class="btn-activity btn-activity-primary"
                       data-bs-toggle="tooltip" title="View Details">
                        <i class="fas fa-eye"></i> View
                    </a>
                    
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.edit', $activity->id) }}" 
                       class="btn-activity btn-activity-secondary"
                       data-bs-toggle="tooltip" title="Edit Activity">
                        <i class="fas fa-edit"></i>
                    </a>
                    
                    <a href="{{ route('activities.sessions', $activity->id) }}" 
                       class="btn-activity btn-activity-secondary"
                       data-bs-toggle="tooltip" title="Manage Sessions">
                        <i class="fas fa-calendar"></i>
                    </a>
                    @endif
                </div>
                
                <span class="activity-status-badge {{ $activity->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $activity->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state-card">
                <div class="empty-state-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h4 class="empty-state-title">No Activities Found</h4>
                <p class="empty-state-text">
                    @if(request()->has('search') || request()->has('category'))
                        No activities match your search criteria. Try adjusting your filters.
                    @else
                        There are no activities in the system yet.
                    @endif
                </p>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Create First Activity
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show align-items-center text-white bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

@if(session('warning'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show align-items-center text-dark bg-warning border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('warning') }}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Auto-hide toasts after 5 seconds
    document.querySelectorAll('.toast').forEach(function(toast) {
        setTimeout(function() {
            toast.classList.remove('show');
        }, 5000);
    });
    
    // Enhanced filtering functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const ageFilter = document.getElementById('ageFilter');
    const searchInput = document.getElementById('searchInput');
    const activitiesGrid = document.getElementById('activitiesGrid');
    
    function filterActivities() {
        const category = categoryFilter.value.toLowerCase();
        const status = statusFilter.value;
        const age = ageFilter.value;
        const search = searchInput.value.toLowerCase();
        
        const cards = activitiesGrid.querySelectorAll('.activity-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const cardCategory = card.dataset.category.toLowerCase();
            const cardStatus = card.dataset.status;
            const cardAge = card.dataset.age;
            const cardName = card.dataset.name;
            
            let show = true;
            
            if (category && cardCategory !== category) show = false;
            if (status && cardStatus !== status) show = false;
            if (age && cardAge !== age) show = false;
            if (search && !cardName.includes(search)) show = false;
            
            if (show) {
                card.style.display = '';
                visibleCount++;
                // Add animation
                card.style.animation = 'fadeInUp 0.5s ease-out';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show empty state if no results
        const emptyState = activitiesGrid.querySelector('.empty-state-card');
        if (visibleCount === 0 && !emptyState) {
            const emptyHtml = `
                <div class="col-12">
                    <div class="empty-state-card">
                        <div class="empty-state-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="empty-state-title">No Results Found</h4>
                        <p class="empty-state-text">
                            Try adjusting your filters or search terms.
                        </p>
                        <button onclick="clearFilters()" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Clear Filters
                        </button>
                    </div>
                </div>
            `;
            activitiesGrid.insertAdjacentHTML('beforeend', emptyHtml);
        } else if (visibleCount > 0 && emptyState) {
            emptyState.parentElement.remove();
        }
    }
    
    // Attach event listeners
    categoryFilter.addEventListener('change', filterActivities);
    statusFilter.addEventListener('change', filterActivities);
    ageFilter.addEventListener('change', filterActivities);
    
    // Debounce search input
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterActivities, 300);
    });
    
    // Clear filters function
    window.clearFilters = function() {
        categoryFilter.value = '';
        statusFilter.value = '';
        ageFilter.value = '';
        searchInput.value = '';
        filterActivities();
    }
});
</script>
@endsection