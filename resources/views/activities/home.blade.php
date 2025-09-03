@extends('layouts.app')

@section('title', 'Activities Management')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: #f8fafc;
        min-height: 100vh;
    }

    .activities-modern-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
        background: transparent;
    }

    /* Modern Header */
    .modern-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-left p {
        font-size: 1.1rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .quick-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
    }

    .quick-stat {
        text-align: center;
    }

    .quick-stat .number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #667eea;
        display: block;
    }

    .quick-stat .label {
        font-size: 0.875rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .header-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .modern-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.6);
        color: white;
        text-decoration: none;
    }

    .btn-secondary {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border: 1px solid rgba(102, 126, 234, 0.2);
    }

    .btn-secondary:hover {
        background: rgba(102, 126, 234, 0.2);
        color: #667eea;
        text-decoration: none;
    }

    /* Modern Search & Filter */
    .search-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .search-bar {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .search-input {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid rgba(102, 126, 234, 0.2);
        border-radius: 16px;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 1.1rem;
    }

    .filter-tabs {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.75rem 1.5rem;
        border: none;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border-radius: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-tab.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .filter-tab:hover {
        transform: translateY(-1px);
    }

    .filter-count {
        background: rgba(255, 255, 255, 0.2);
        color: inherit;
        padding: 0.2rem 0.5rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .filter-tab.active .filter-count {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Modern Activity Grid - Fixed 3x3 layout (9 items max per page) */
    .activities-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-bottom: 2rem;
        min-height: 600px; /* Ensures consistent height even with fewer items */
    }

    .activity-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .activity-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        position: relative;
    }

    .activity-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-active {
        background: rgba(34, 197, 94, 0.1);
        color: #059669;
    }

    .status-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .activity-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .activity-category {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .category-icon {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-content {
        padding: 1.5rem;
    }

    .activity-description {
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .activity-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .meta-icon {
        color: #667eea;
        width: 16px;
    }

    .card-actions {
        display: flex;
        gap: 0.75rem;
        padding: 0 1.5rem 1.5rem;
    }

    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        flex: 1;
        text-align: center;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(102, 126, 234, 0.3);
        color: #667eea;
    }

    .btn-outline:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        text-decoration: none;
    }

    .btn-ghost {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
    }

    .btn-ghost:hover {
        background: rgba(100, 116, 139, 0.2);
        color: #64748b;
        text-decoration: none;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .empty-icon {
        font-size: 4rem;
        color: #667eea;
        margin-bottom: 1.5rem;
        opacity: 0.7;
    }

    .empty-state h3 {
        color: #1a202c;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    /* Modern Pagination */
    .modern-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-top: 3rem;
    }

    .pagination-info {
        color: #64748b;
        font-size: 0.875rem;
    }

    .pagination-nav {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .page-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: rgba(255, 255, 255, 0.8);
        color: #64748b;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .page-btn:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        text-decoration: none;
    }

    .page-btn.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .activities-modern-container {
            padding: 1rem;
        }

        .modern-header {
            padding: 1.5rem;
        }

        .header-content {
            flex-direction: column;
            gap: 1.5rem;
            text-align: center;
        }

        .header-left h1 {
            font-size: 2rem;
        }

        .quick-stats {
            justify-content: center;
        }

        .activities-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .activities-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .filter-tabs {
            justify-content: center;
        }

        .card-actions {
            flex-direction: column;
        }
    }

    /* Loading Animation */
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 200px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(102, 126, 234, 0.3);
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Fade in animation */
    .fade-in {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }
</style>
@endsection

@section('content')
<div class="activities-modern-container fade-in">
    <!-- Modern Header -->
    <div class="modern-header">
        <div class="header-content">
            <div class="header-left">
                <h1>Activities Management</h1>
                <p>Manage rehabilitation and educational programs with ease</p>
                <div class="quick-stats">
                    <div class="quick-stat">
                        <span class="number">{{ $stats['total_activities'] ?? $activities->count() }}</span>
                        <span class="label">Total Activities</span>
                    </div>
                    <div class="quick-stat">
                        <span class="number">{{ $stats['active_activities'] ?? $activities->where('is_active', true)->count() }}</span>
                        <span class="label">Active</span>
                    </div>
                    <div class="quick-stat">
                        <span class="number">{{ $stats['total_sessions'] ?? 0 }}</span>
                        <span class="label">Sessions</span>
                    </div>
                    <div class="quick-stat">
                        <span class="number">{{ $stats['total_enrollments'] ?? 0 }}</span>
                        <span class="label">Enrolled</span>
                    </div>
                </div>
            </div>
            <div class="header-right">
                @if(in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('activities.create') }}" class="modern-btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create Activity
                    </a>
                @endif
                <a href="{{ route('activities.categories') }}" class="modern-btn btn-secondary">
                    <i class="fas fa-th-large"></i>
                    Categories
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Search & Filter -->
    <div class="search-section">
        <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Search activities by name, instructor, or description...">
        </div>
        
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">
                <i class="fas fa-th-large"></i>
                All Activities
                <span class="filter-count">{{ $activities->count() }}</span>
            </button>
            <button class="filter-tab" data-filter="active">
                <i class="fas fa-play-circle"></i>
                Active
                <span class="filter-count">{{ $activities->where('is_active', true)->count() }}</span>
            </button>
            <button class="filter-tab" data-filter="therapy">
                <i class="fas fa-heartbeat"></i>
                Therapy
                <span class="filter-count">{{ $activities->filter(function($activity) { 
                    $categoryName = strtolower($activity->categoryModel->category_name ?? '');
                    return stripos($categoryName, 'therapy') !== false || 
                           stripos($categoryName, 'speech') !== false || 
                           stripos($categoryName, 'hearing') !== false || 
                           stripos($categoryName, 'visual') !== false ||
                           stripos($categoryName, 'physical') !== false ||
                           stripos($categoryName, 'autism') !== false;
                })->count() }}</span>
            </button>
            <button class="filter-tab" data-filter="academic">
                <i class="fas fa-graduation-cap"></i>
                Academic
                <span class="filter-count">{{ $activities->filter(function($activity) { 
                    $categoryName = strtolower($activity->categoryModel->category_name ?? '');
                    return stripos($categoryName, 'academic') !== false || 
                           stripos($categoryName, 'learning') !== false;
                })->count() }}</span>
            </button>
            <button class="filter-tab" data-filter="faith">
                <i class="fas fa-hands-praying"></i>
                Faith & Values
                <span class="filter-count">{{ $activities->filter(function($activity) { 
                    $categoryName = strtolower($activity->categoryModel->category_name ?? '');
                    return stripos($categoryName, 'faith') !== false || 
                           stripos($categoryName, 'values') !== false;
                })->count() }}</span>
            </button>
        </div>
    </div>

    <!-- Activities Grid -->
    <div class="activities-grid" id="activitiesGrid">
        @if($activities && $activities->count() > 0)
            @foreach($activities as $activity)
            <div class="activity-card" 
                 data-category="{{ strtolower($activity->categoryModel->category_name ?? 'general') }}" 
                 data-status="{{ $activity->is_active ? 'active' : 'inactive' }}"
                 data-name="{{ strtolower($activity->activity_name) }}"
                 data-instructor="{{ strtolower($activity->instructor->name ?? '') }}"
                 data-category-type="@if($activity->categoryModel)
                     @php
                         $catName = strtolower($activity->categoryModel->category_name);
                         if(stripos($catName, 'therapy') !== false || stripos($catName, 'speech') !== false || stripos($catName, 'hearing') !== false || stripos($catName, 'visual') !== false || stripos($catName, 'physical') !== false || stripos($catName, 'autism') !== false) {
                             echo 'therapy';
                         } elseif(stripos($catName, 'academic') !== false || stripos($catName, 'learning') !== false) {
                             echo 'academic';
                         } elseif(stripos($catName, 'faith') !== false || stripos($catName, 'values') !== false) {
                             echo 'faith';
                         } else {
                             echo 'general';
                         }
                     @endphp
                 @else
                     general
                 @endif">
                
                <div class="card-header">
                    <div class="activity-status {{ $activity->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $activity->is_active ? 'Active' : 'Inactive' }}
                    </div>
                    
                    <h3 class="activity-title">{{ $activity->activity_name }}</h3>
                    
                    <div class="activity-category">
                        @php
                            $categoryIcon = 'fas fa-tasks';
                            $categoryName = strtolower($activity->categoryModel->category_name ?? '');
                            if(stripos($categoryName, 'autism') !== false) {
                                $categoryIcon = 'fas fa-brain';
                            } elseif(stripos($categoryName, 'hearing') !== false) {
                                $categoryIcon = 'fas fa-deaf';
                            } elseif(stripos($categoryName, 'visual') !== false) {
                                $categoryIcon = 'fas fa-eye-slash';
                            } elseif(stripos($categoryName, 'physical') !== false) {
                                $categoryIcon = 'fas fa-wheelchair';
                            } elseif(stripos($categoryName, 'learning') !== false || stripos($categoryName, 'academic') !== false) {
                                $categoryIcon = 'fas fa-graduation-cap';
                            } elseif(stripos($categoryName, 'speech') !== false) {
                                $categoryIcon = 'fas fa-comments';
                            } elseif(stripos($categoryName, 'faith') !== false || stripos($categoryName, 'values') !== false) {
                                $categoryIcon = 'fas fa-hands-praying';
                            }
                        @endphp
                        <div class="category-icon">
                            <i class="{{ $categoryIcon }}"></i>
                        </div>
                        <span>{{ $activity->categoryModel->category_name ?? 'General' }}</span>
                    </div>
                </div>

                <div class="card-content">
                    <div class="activity-description">
                        {{ Str::limit($activity->activity_description, 120) ?: 'No description available.' }}
                    </div>

                    <div class="activity-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt meta-icon"></i>
                            <span>{{ $activity->sessions_count ?? $activity->sessions->count() }} Sessions</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-users meta-icon"></i>
                            <span>{{ $activity->enrollments_count ?? $activity->enrollments->count() }} Enrolled</span>
                        </div>
                        @if($activity->instructor)
                        <div class="meta-item">
                            <i class="fas fa-user-tie meta-icon"></i>
                            <span>{{ Str::limit($activity->instructor->name, 15) }}</span>
                        </div>
                        @endif
                        @if($activity->activity_location)
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt meta-icon"></i>
                            <span>{{ Str::limit($activity->activity_location, 15) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('activities.show', $activity->id) }}" class="btn-small btn-primary">
                        <i class="fas fa-eye"></i>
                        View
                    </a>
                    <a href="{{ route('activities.sessions', $activity->id) }}" class="btn-small btn-outline">
                        <i class="fas fa-calendar"></i>
                        Sessions
                    </a>
                    @if(in_array(session('role'), ['admin', 'supervisor']) && $activity->is_active)
                    <a href="{{ route('activities.edit', $activity->id) }}" class="btn-small btn-ghost">
                        <i class="fas fa-edit"></i>
                        Edit
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>No Activities Found</h3>
                <p>Get started by creating your first activity program.</p>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('activities.create') }}" class="modern-btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create First Activity
                </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Loading State -->
    <div class="loading" id="loadingState" style="display: none;">
        <div class="spinner"></div>
    </div>

    <!-- Standardized Pagination -->
    @include('components.custom-pagination', ['items' => $activities])
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const activityCards = document.querySelectorAll('.activity-card');
    const activitiesGrid = document.getElementById('activitiesGrid');
    const loadingState = document.getElementById('loadingState');

    let currentFilter = 'all';

    // Search functionality
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        filterAndSearchActivities(currentFilter, searchTerm);
    });

    // Filter functionality
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            currentFilter = this.dataset.filter;
            const searchTerm = searchInput.value.toLowerCase().trim();
            filterAndSearchActivities(currentFilter, searchTerm);
        });
    });

    function filterAndSearchActivities(filter, searchTerm) {
        showLoading();
        
        setTimeout(() => {
            let visibleCount = 0;
            
            activityCards.forEach(card => {
                let shouldShow = true;
                
                // Apply category filter
                if (filter !== 'all') {
                    const category = card.dataset.category || '';
                    const categoryType = card.dataset.categoryType || '';
                    const status = card.dataset.status || '';
                    
                    switch(filter) {
                        case 'active':
                            shouldShow = status === 'active';
                            break;
                        case 'therapy':
                            shouldShow = categoryType === 'therapy' ||
                                        category.includes('therapy') ||
                                        category.includes('speech') ||
                                        category.includes('hearing') ||
                                        category.includes('visual') ||
                                        category.includes('physical') ||
                                        category.includes('autism');
                            break;
                        case 'academic':
                            shouldShow = categoryType === 'academic' ||
                                        category.includes('academic') ||
                                        category.includes('learning');
                            break;
                        case 'faith':
                            shouldShow = categoryType === 'faith' ||
                                        category.includes('faith') ||
                                        category.includes('values');
                            break;
                    }
                }
                
                // Apply search filter
                if (searchTerm && shouldShow) {
                    const name = card.dataset.name || '';
                    const instructor = card.dataset.instructor || '';
                    const content = card.textContent.toLowerCase();
                    
                    shouldShow = name.includes(searchTerm) || 
                               instructor.includes(searchTerm) || 
                               content.includes(searchTerm);
                }
                
                if (shouldShow) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.3s ease forwards';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show empty state if no results
            const emptyState = document.querySelector('.empty-state');
            if (visibleCount === 0 && !emptyState) {
                showEmptySearchState();
            } else if (visibleCount > 0) {
                hideEmptySearchState();
            }
            
            hideLoading();
        }, 300);
    }

    function showLoading() {
        loadingState.style.display = 'flex';
        activitiesGrid.style.opacity = '0.5';
    }

    function hideLoading() {
        loadingState.style.display = 'none';
        activitiesGrid.style.opacity = '1';
    }

    function showEmptySearchState() {
        const existingEmpty = document.querySelector('.empty-search-state');
        if (existingEmpty) return;

        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty-state empty-search-state';
        emptyDiv.style.gridColumn = '1 / -1';
        emptyDiv.innerHTML = `
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>No Activities Found</h3>
            <p>Try adjusting your search terms or filters to find what you're looking for.</p>
            <button class="modern-btn btn-primary" onclick="clearFilters()">
                <i class="fas fa-refresh"></i>
                Clear Filters
            </button>
        `;
        activitiesGrid.appendChild(emptyDiv);
    }

    function hideEmptySearchState() {
        const emptyState = document.querySelector('.empty-search-state');
        if (emptyState) {
            emptyState.remove();
        }
    }

    // Global function for clear filters button
    window.clearFilters = function() {
        searchInput.value = '';
        currentFilter = 'all';
        filterTabs.forEach(t => t.classList.remove('active'));
        filterTabs[0].classList.add('active');
        filterAndSearchActivities('all', '');
    };

    // Add smooth scroll to cards on load
    activityCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endsection