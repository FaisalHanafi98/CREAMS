@extends('layouts.app')

@section('title', 'Activities Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities-modern.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="activities-modern-container">
    <!-- Modern Header with Gradient Background -->
    <div class="modern-header">
        <div class="header-backdrop"></div>
        <div class="header-content">
            <div class="header-main">
                <div class="header-text">
                    <h1 class="header-title">
                        <span class="title-icon">🎯</span>
                        Activities Dashboard
                    </h1>
                    <p class="header-subtitle">Manage rehabilitation activities and track student progress in real-time</p>
                </div>
                @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                <div class="header-actions">
                    <a href="{{ route('activities.wizard.index') }}" class="btn-modern btn-success">
                        <i class="fas fa-magic"></i>
                        <span>Activity Wizard</span>
                    </a>
                    @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="btn-modern btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>Create Activity</span>
                    </a>
                    <a href="{{ route('activities.categories') }}" class="btn-modern btn-secondary">
                        <i class="fas fa-th-large"></i>
                        <span>Categories</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modern Statistics Cards -->
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
                            <span>+12%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['total_activities'] ?? 0 }}</h3>
                        <p class="stat-label">Total Activities</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-footer-text">All registered activities</span>
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
                            <span>+8%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['active_activities'] ?? 0 }}</h3>
                        <p class="stat-label">Active Now</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-footer-text">{{ round(($stats['active_activities'] / max($stats['total_activities'], 1)) * 100) }}% of all activities</span>
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
                            <span>+15%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['total_sessions'] ?? 0 }}</h3>
                        <p class="stat-label">Sessions</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-footer-text">This semester</span>
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
                            <span>+5%</span>
                        </div>
                    </div>
                    <div class="stat-body">
                        <h3 class="stat-number">{{ $stats['total_enrollments'] ?? 0 }}</h3>
                        <p class="stat-label">Enrollments</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-footer-text">Active participants</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Search and Filter Section -->
    <div class="search-filter-section">
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="globalSearch" class="search-input" placeholder="Search activities, instructors, or categories...">
                <div class="search-loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <div class="filters-container">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">
                    <span>All</span>
                    <span class="tab-count">{{ count($activities) }}</span>
                </button>
                <button class="filter-tab" data-filter="rehabilitation">
                    <span>Rehabilitation</span>
                    <span class="tab-count">{{ $activities->where('category', 'Rehabilitation')->count() }}</span>
                </button>
                <button class="filter-tab" data-filter="academic">
                    <span>Academic</span>
                    <span class="tab-count">{{ $activities->where('category', 'Academic')->count() }}</span>
                </button>
                <button class="filter-tab" data-filter="active">
                    <span>Active</span>
                    <span class="tab-count">{{ $activities->where('is_active', true)->count() }}</span>
                </button>
            </div>

            <div class="filter-dropdown">
                <button class="filter-btn" id="advancedFilters">
                    <i class="fas fa-sliders-h"></i>
                    <span>Filters</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <div class="filter-menu" id="filterMenu">
                    <div class="filter-group">
                        <label class="filter-label">Difficulty Level</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" value="beginner" class="filter-checkbox">
                                <span class="checkmark"></span>
                                Beginner
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" value="intermediate" class="filter-checkbox">
                                <span class="checkmark"></span>
                                Intermediate
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" value="advanced" class="filter-checkbox">
                                <span class="checkmark"></span>
                                Advanced
                            </label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Age Group</label>
                        <select class="modern-select" id="ageGroupFilter">
                            <option value="">All Ages</option>
                            <option value="6-8">6-8 years</option>
                            <option value="9-12">9-12 years</option>
                            <option value="13-16">13-16 years</option>
                            <option value="17+">17+ years</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Instructor</label>
                        <select class="modern-select" id="instructorFilter">
                            <option value="">All Instructors</option>
                            @foreach($activities->pluck('created_by')->unique() as $instructor_id)
                                @php
                                    $instructor = \App\Models\User::find($instructor_id);
                                @endphp
                                @if($instructor)
                                    <option value="{{ $instructor_id }}">{{ $instructor->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Activities Grid -->
    <div class="activities-main">
        <div class="activities-grid" id="activitiesGrid">
            @forelse($activities as $activity)
            <div class="activity-card-modern" 
                 data-category="{{ strtolower($activity->category ?? $activity->activity_type) }}"
                 data-status="{{ $activity->is_active ? 'active' : 'inactive' }}"
                 data-difficulty="{{ strtolower($activity->difficulty_level ?? 'beginner') }}"
                 data-age="{{ $activity->age_group }}"
                 data-instructor="{{ $activity->created_by }}"
                 data-name="{{ strtolower($activity->activity_name) }}">
                
                <div class="card-header">
                    <div class="card-header-main">
                        <div class="activity-avatar">
                            @php
                                $categoryIcons = [
                                    'Physical Therapy' => '🏃‍♂️',
                                    'Occupational Therapy' => '🖐️',
                                    'Speech Therapy' => '🗣️',
                                    'Mathematics' => '🔢',
                                    'Literacy' => '📚',
                                    'Science' => '🔬',
                                    'Art & Creativity' => '🎨',
                                    'Music Therapy' => '🎵',
                                    'Life Skills' => '🏠',
                                    'default' => '🎯'
                                ];
                                $icon = $categoryIcons[$activity->activity_type] ?? $categoryIcons['default'];
                            @endphp
                            <span class="activity-emoji">{{ $icon }}</span>
                        </div>
                        <div class="card-title-section">
                            <h3 class="card-title">{{ $activity->activity_name }}</h3>
                            <p class="card-subtitle">{{ $activity->activity_type }}</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <div class="status-badge {{ $activity->is_active ? 'active' : 'inactive' }}">
                            <div class="status-dot"></div>
                            <span>{{ $activity->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        
                        <div class="card-menu">
                            <button class="menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="menu-dropdown">
                                <a href="{{ route('activities.show', $activity->id) }}" class="menu-item">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                @if(in_array(session('role'), ['admin', 'supervisor']))
                                <a href="{{ route('activities.edit', $activity->id) }}" class="menu-item">
                                    <i class="fas fa-edit"></i>
                                    Edit Activity
                                </a>
                                <a href="{{ route('activities.sessions', $activity->id) }}" class="menu-item">
                                    <i class="fas fa-calendar"></i>
                                    Manage Sessions
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <p class="card-description">
                        {{ Str::limit($activity->description ?? 'No description available', 100) }}
                    </p>

                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-icon difficulty-{{ strtolower($activity->difficulty_level ?? 'beginner') }}">
                                <i class="fas fa-signal"></i>
                            </div>
                            <div class="metric-content">
                                <span class="metric-value">{{ ucfirst($activity->difficulty_level ?? 'Beginner') }}</span>
                                <span class="metric-label">Difficulty</span>
                            </div>
                        </div>

                        <div class="metric">
                            <div class="metric-icon age">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="metric-content">
                                <span class="metric-value">{{ $activity->age_group ?? 'All Ages' }}</span>
                                <span class="metric-label">Age Group</span>
                            </div>
                        </div>

                        <div class="metric">
                            <div class="metric-icon sessions">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="metric-content">
                                <span class="metric-value">{{ $activity->sessions_count ?? 0 }}</span>
                                <span class="metric-label">Sessions</span>
                            </div>
                        </div>

                        <div class="metric">
                            <div class="metric-icon participants">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="metric-content">
                                <span class="metric-value">{{ $activity->enrollments_count ?? 0 }}</span>
                                <span class="metric-label">Enrolled</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="footer-info">
                        @php
                            $instructor = \App\Models\User::find($activity->created_by);
                        @endphp
                        <div class="instructor-info">
                            <div class="instructor-avatar">
                                @if($instructor && $instructor->avatar)
                                    <img src="{{ asset('storage/avatars/' . $instructor->avatar) }}" alt="{{ $instructor->name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ substr($instructor->name ?? 'U', 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="instructor-details">
                                <span class="instructor-name">{{ $instructor->name ?? 'Unknown' }}</span>
                                <span class="instructor-role">{{ ucfirst($instructor->role ?? 'Instructor') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="footer-actions">
                        <a href="{{ route('activities.show', $activity->id) }}" class="btn-card btn-outline">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="empty-title">No Activities Found</h3>
                <p class="empty-description">Start by creating your first activity to begin managing rehabilitation sessions.</p>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="btn-modern btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Create First Activity</span>
                </a>
                @endif
            </div>
            @endforelse
        </div>

        <!-- Load More Button -->
        @if($activities->hasPages())
        <div class="load-more-section">
            <button class="btn-modern btn-load-more" id="loadMoreBtn">
                <span class="btn-text">Load More Activities</span>
                <div class="btn-spinner">
                    <div class="spinner"></div>
                </div>
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Modern JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modern features
    initializeSearch();
    initializeFilters();
    initializeCardAnimations();
    
    // Search functionality
    function initializeSearch() {
        const searchInput = document.getElementById('globalSearch');
        const searchLoading = document.querySelector('.search-loading');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchLoading.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                filterActivities();
                searchLoading.style.display = 'none';
            }, 300);
        });
    }

    // Filter functionality
    function initializeFilters() {
        // Tab filters
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                filterActivities();
            });
        });

        // Advanced filters dropdown
        const advancedFilters = document.getElementById('advancedFilters');
        const filterMenu = document.getElementById('filterMenu');

        advancedFilters.addEventListener('click', function() {
            filterMenu.classList.toggle('show');
        });

        // Filter inputs
        document.querySelectorAll('.filter-checkbox, .modern-select').forEach(input => {
            input.addEventListener('change', filterActivities);
        });
    }

    // Card animations
    function initializeCardAnimations() {
        const cards = document.querySelectorAll('.activity-card-modern');
        
        // Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.transform = 'translateY(0)';
                    entry.target.style.opacity = '1';
                }
            });
        }, { threshold: 0.1 });

        cards.forEach(card => {
            observer.observe(card);
            
            // Card menu functionality
            const menuBtn = card.querySelector('.menu-btn');
            const menuDropdown = card.querySelector('.menu-dropdown');
            
            if (menuBtn && menuDropdown) {
                menuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menuDropdown.classList.toggle('show');
                });
            }
        });

        // Close menus when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
            document.getElementById('filterMenu').classList.remove('show');
        });
    }

    // Filter activities function
    function filterActivities() {
        const searchTerm = document.getElementById('globalSearch').value.toLowerCase();
        const activeTab = document.querySelector('.filter-tab.active').dataset.filter;
        const selectedDifficulties = Array.from(document.querySelectorAll('.filter-checkbox:checked')).map(cb => cb.value);
        const ageGroup = document.getElementById('ageGroupFilter').value;
        const instructor = document.getElementById('instructorFilter').value;

        const cards = document.querySelectorAll('.activity-card-modern');
        let visibleCount = 0;

        cards.forEach(card => {
            let shouldShow = true;

            // Search filter
            if (searchTerm && !card.dataset.name.includes(searchTerm)) {
                shouldShow = false;
            }

            // Tab filter
            if (activeTab !== 'all') {
                if (activeTab === 'active' && card.dataset.status !== 'active') {
                    shouldShow = false;
                } else if (activeTab !== 'active' && !card.dataset.category.includes(activeTab)) {
                    shouldShow = false;
                }
            }

            // Difficulty filter
            if (selectedDifficulties.length > 0 && !selectedDifficulties.includes(card.dataset.difficulty)) {
                shouldShow = false;
            }

            // Age group filter
            if (ageGroup && card.dataset.age !== ageGroup) {
                shouldShow = false;
            }

            // Instructor filter
            if (instructor && card.dataset.instructor !== instructor) {
                shouldShow = false;
            }

            // Show/hide card with animation
            if (shouldShow) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.style.transform = 'translateY(0)';
                    card.style.opacity = '1';
                }, 50);
                visibleCount++;
            } else {
                card.style.transform = 'translateY(20px)';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 200);
            }
        });

        // Update empty state
        const emptyState = document.querySelector('.empty-state');
        if (visibleCount === 0 && !emptyState) {
            const grid = document.getElementById('activitiesGrid');
            grid.innerHTML += `
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-search"></i></div>
                    <h3 class="empty-title">No Activities Found</h3>
                    <p class="empty-description">Try adjusting your search or filter criteria.</p>
                    <button class="btn-modern btn-secondary" onclick="clearFilters()">
                        <i class="fas fa-times"></i>
                        <span>Clear Filters</span>
                    </button>
                </div>
            `;
        }
    }

    // Clear filters function
    window.clearFilters = function() {
        document.getElementById('globalSearch').value = '';
        document.querySelectorAll('.filter-checkbox:checked').forEach(cb => cb.checked = false);
        document.getElementById('ageGroupFilter').value = '';
        document.getElementById('instructorFilter').value = '';
        document.querySelector('.filter-tab[data-filter="all"]').click();
        document.querySelector('.empty-state')?.remove();
    };
});
</script>
@endsection