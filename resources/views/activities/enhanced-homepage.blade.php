@extends('layouts.app')

@section('title', 'Activities & Programs')

@section('styles')
<style>
    .category-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .category-card {
        padding: 1.5rem;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        position: relative;
    }
    
    .category-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    
    .category-card.selected {
        border-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
    }
    
    .category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .category-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }
    
    .category-icon.speech {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .category-icon.occupational {
        background: linear-gradient(135deg, #2ed573, #17a2b8);
    }
    
    .category-icon.physical {
        background: linear-gradient(135deg, #c850c0, #ffb142);
    }
    
    .category-icon.behavioral {
        background: linear-gradient(135deg, #ffa502, #ff6348);
    }
    
    .category-icon.sensory {
        background: linear-gradient(135deg, #ff4757, #ff3838);
    }
    
    .category-count {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-color);
    }
    
    .category-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    
    .category-subtitle {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .filters-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }
    
    .search-input {
        position: relative;
    }
    
    .search-input i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .search-input input {
        padding-left: 2.5rem;
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .search-input input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
    }
    
    .filter-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
    }
    
    .more-filters-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .more-filters-btn:hover {
        background: #e2e8f0;
    }
    
    .active-filters {
        margin-top: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .filter-tag .remove {
        cursor: pointer;
        font-size: 0.7rem;
    }
    
    .clear-filters {
        color: var(--primary-color);
        cursor: pointer;
        font-size: 0.85rem;
        text-decoration: underline;
    }
    
    .activities-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .results-count {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .sort-options {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .sort-select {
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        font-size: 0.9rem;
    }
    
    .activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }
    
    .activity-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }
    
    .activity-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    
    .activity-header {
        height: 4px;
    }
    
    .activity-header.speech {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .activity-header.occupational {
        background: linear-gradient(135deg, #2ed573, #17a2b8);
    }
    
    .activity-header.physical {
        background: linear-gradient(135deg, #c850c0, #ffb142);
    }
    
    .activity-header.behavioral {
        background: linear-gradient(135deg, #ffa502, #ff6348);
    }
    
    .activity-header.sensory {
        background: linear-gradient(135deg, #ff4757, #ff3838);
    }
    
    .activity-content {
        padding: 1.5rem;
    }
    
    .activity-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .activity-info {
        flex: 1;
    }
    
    .activity-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
    }
    
    .activity-category {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.75rem;
    }
    
    .activity-rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: #fff3cd;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #856404;
    }
    
    .activity-description {
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .activity-details {
        display: grid;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
    }
    
    .detail-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
    }
    
    .detail-row i {
        width: 16px;
        color: #9ca3af;
    }
    
    .enrollment-section {
        margin-bottom: 1rem;
    }
    
    .enrollment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    
    .enrollment-label {
        color: #6c757d;
    }
    
    .spots-left {
        font-weight: 500;
    }
    
    .spots-left.low {
        color: var(--danger-color);
    }
    
    .spots-left.medium {
        color: var(--warning-color);
    }
    
    .spots-left.high {
        color: var(--success-color);
    }
    
    .enrollment-bar {
        width: 100%;
        height: 6px;
        background: #f1f5f9;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }
    
    .enrollment-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .enrollment-fill.low {
        background: var(--danger-color);
    }
    
    .enrollment-fill.medium {
        background: var(--warning-color);
    }
    
    .enrollment-fill.high {
        background: var(--success-color);
    }
    
    .enrollment-count {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .activity-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-view {
        flex: 1;
        padding: 0.75rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
    }
    
    .btn-view:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: white;
        text-decoration: none;
    }
    
    .btn-more {
        width: 40px;
        height: 40px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-more:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        color: #6c757d;
    }
    
    .spinner {
        width: 24px;
        height: 24px;
        border: 2px solid #e2e8f0;
        border-top: 2px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @media (max-width: 768px) {
        .category-stats {
            grid-template-columns: 1fr;
        }
        
        .filters-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .activities-grid {
            grid-template-columns: 1fr;
        }
        
        .activities-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">Activities & Programs</h1>
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="separator">></span>
        <span class="current">Activities</span>
    </div>
    <p style="color: #6c757d; margin-top: 0.5rem;">Discover therapeutic activities tailored for special needs rehabilitation</p>
</div>

<!-- Category Statistics -->
<div class="category-stats" id="categoryStats">
    <div class="category-card" data-category="Speech Therapy" onclick="selectCategory('Speech Therapy')">
        <div class="category-header">
            <div class="category-icon speech">
                <i class="fas fa-comments"></i>
            </div>
            <div class="category-count" id="speechCount">0</div>
        </div>
        <h3 class="category-title">Speech Therapy</h3>
        <p class="category-subtitle">Communication and language development</p>
    </div>
    
    <div class="category-card" data-category="Occupational Therapy" onclick="selectCategory('Occupational Therapy')">
        <div class="category-header">
            <div class="category-icon occupational">
                <i class="fas fa-hands-helping"></i>
            </div>
            <div class="category-count" id="occupationalCount">0</div>
        </div>
        <h3 class="category-title">Occupational Therapy</h3>
        <p class="category-subtitle">Daily living and motor skills</p>
    </div>
    
    <div class="category-card" data-category="Physical Therapy" onclick="selectCategory('Physical Therapy')">
        <div class="category-header">
            <div class="category-icon physical">
                <i class="fas fa-running"></i>
            </div>
            <div class="category-count" id="physicalCount">0</div>
        </div>
        <h3 class="category-title">Physical Therapy</h3>
        <p class="category-subtitle">Movement and strength building</p>
    </div>
    
    <div class="category-card" data-category="Behavioral Therapy" onclick="selectCategory('Behavioral Therapy')">
        <div class="category-header">
            <div class="category-icon behavioral">
                <i class="fas fa-brain"></i>
            </div>
            <div class="category-count" id="behavioralCount">0</div>
        </div>
        <h3 class="category-title">Behavioral Therapy</h3>
        <p class="category-subtitle">Behavior modification and social skills</p>
    </div>
    
    <div class="category-card" data-category="Sensory Integration" onclick="selectCategory('Sensory Integration')">
        <div class="category-header">
            <div class="category-icon sensory">
                <i class="fas fa-eye"></i>
            </div>
            <div class="category-count" id="sensoryCount">0</div>
        </div>
        <h3 class="category-title">Sensory Integration</h3>
        <p class="category-subtitle">Sensory processing and integration</p>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <div class="filters-grid">
        <div class="search-input">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search activities, teachers, or keywords...">
        </div>
        
        <select id="difficultyFilter" class="filter-select">
            <option value="">All Difficulties</option>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
        </select>
        
        <select id="ageGroupFilter" class="filter-select">
            <option value="">All Age Groups</option>
            <option value="3-6 years">3-6 years</option>
            <option value="7-10 years">7-10 years</option>
            <option value="11-14 years">11-14 years</option>
            <option value="15+ years">15+ years</option>
        </select>
        
        <select id="availabilityFilter" class="filter-select">
            <option value="">All Availability</option>
            <option value="available">Available Spots</option>
            <option value="limited">Limited Spots</option>
            <option value="full">Full</option>
        </select>
        
        <div class="more-filters-btn">
            <i class="fas fa-filter"></i>
            More Filters
        </div>
    </div>
    
    <div class="active-filters" id="activeFilters" style="display: none;">
        <span style="font-size: 0.8rem; color: #6c757d;">Active filters:</span>
        <!-- Filter tags will be added here -->
        <span class="clear-filters" onclick="clearAllFilters()">Clear all</span>
    </div>
</div>

<!-- Activities Section -->
<div class="activities-header">
    <div class="results-count" id="resultsCount">0 Activities Found</div>
    <div class="sort-options">
        <span>Sort by:</span>
        <select id="sortBy" class="sort-select">
            <option value="popularity">Popularity</option>
            <option value="name">Name</option>
            <option value="availability">Availability</option>
            <option value="rating">Rating</option>
        </select>
    </div>
</div>

<div id="activitiesContainer">
    <div class="activities-grid" id="activitiesGrid">
        <!-- Activities will be loaded here -->
    </div>
    
    <div class="loading-spinner" id="loadingSpinner" style="display: none;">
        <div class="spinner"></div>
        Loading activities...
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Global variables
    let activities = [];
    let filteredActivities = [];
    let selectedCategory = '';
    let isLoading = false;
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadActivities();
        setupFilters();
    });
    
    // Load activities data
    async function loadActivities() {
        try {
            isLoading = true;
            document.getElementById('loadingSpinner').style.display = 'flex';
            
            // Try to load from API endpoint
            const response = await fetch('/api/activities');
            if (response.ok) {
                activities = await response.json();
            } else {
                // Fallback to mock data
                activities = generateMockActivities();
            }
            
            filteredActivities = [...activities];
            renderActivities();
            updateCategoryStats();
            
        } catch (error) {
            console.error('Error loading activities:', error);
            // Use mock data as fallback
            activities = generateMockActivities();
            filteredActivities = [...activities];
            renderActivities();
            updateCategoryStats();
        } finally {
            isLoading = false;
            document.getElementById('loadingSpinner').style.display = 'none';
        }
    }
    
    // Generate mock activities
    function generateMockActivities() {
        const categories = ['Speech Therapy', 'Occupational Therapy', 'Physical Therapy', 'Behavioral Therapy', 'Sensory Integration'];
        const difficulties = ['Beginner', 'Intermediate', 'Advanced'];
        const ageGroups = ['3-6 years', '7-10 years', '11-14 years', '15+ years'];
        const teachers = ['Dr. Ahmad Hassan', 'Ms. Siti Nurhaliza', 'Mr. Raj Kumar', 'Ms. Chen Wei'];
        const schedules = ['Monday 10:00 AM', 'Wednesday 2:00 PM', 'Friday 3:30 PM', 'Tuesday 1:00 PM'];
        
        const activityNames = [
            'Communication Skills Development',
            'Fine Motor Skills Workshop',
            'Social Interaction Group',
            'Sensory Play Session',
            'Movement and Coordination',
            'Cognitive Development Activities',
            'Art Therapy Session',
            'Music and Rhythm Therapy',
            'Balance and Stability Training',
            'Language Building Blocks',
            'Emotional Regulation Workshop',
            'Adaptive Skills Training'
        ];
        
        return Array.from({ length: 48 }, (_, i) => ({
            id: i + 1,
            name: activityNames[i % activityNames.length],
            category: categories[Math.floor(Math.random() * categories.length)],
            description: 'Comprehensive program designed to enhance developmental skills through engaging activities and personalized interventions.',
            difficulty: difficulties[Math.floor(Math.random() * difficulties.length)],
            age_group: ageGroups[Math.floor(Math.random() * ageGroups.length)],
            duration: [30, 45, 60, 90][Math.floor(Math.random() * 4)],
            max_participants: [5, 8, 10, 12][Math.floor(Math.random() * 4)],
            current_enrollment: Math.floor(Math.random() * 8) + 2,
            teacher: teachers[Math.floor(Math.random() * teachers.length)],
            schedule: schedules[Math.floor(Math.random() * schedules.length)],
            rating: (Math.random() * 1.5 + 3.5).toFixed(1),
            total_sessions: Math.floor(Math.random() * 20) + 10,
            is_active: Math.random() > 0.1
        }));
    }
    
    // Setup filters
    function setupFilters() {
        const searchInput = document.getElementById('searchInput');
        const difficultyFilter = document.getElementById('difficultyFilter');
        const ageGroupFilter = document.getElementById('ageGroupFilter');
        const availabilityFilter = document.getElementById('availabilityFilter');
        const sortBy = document.getElementById('sortBy');
        
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });
        
        [difficultyFilter, ageGroupFilter, availabilityFilter, sortBy].forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });
    }
    
    // Apply filters
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const difficultyFilter = document.getElementById('difficultyFilter').value;
        const ageGroupFilter = document.getElementById('ageGroupFilter').value;
        const availabilityFilter = document.getElementById('availabilityFilter').value;
        const sortBy = document.getElementById('sortBy').value;
        
        filteredActivities = activities.filter(activity => {
            if (!activity.is_active) return false;
            
            const matchesSearch = searchTerm === '' || 
                activity.name.toLowerCase().includes(searchTerm) ||
                activity.description.toLowerCase().includes(searchTerm) ||
                activity.teacher.toLowerCase().includes(searchTerm);
            
            const matchesCategory = selectedCategory === '' || activity.category === selectedCategory;
            const matchesDifficulty = difficultyFilter === '' || activity.difficulty === difficultyFilter;
            const matchesAgeGroup = ageGroupFilter === '' || activity.age_group === ageGroupFilter;
            
            let matchesAvailability = true;
            if (availabilityFilter) {
                const spotsLeft = activity.max_participants - activity.current_enrollment;
                if (availabilityFilter === 'available') {
                    matchesAvailability = spotsLeft > 2;
                } else if (availabilityFilter === 'limited') {
                    matchesAvailability = spotsLeft <= 2 && spotsLeft > 0;
                } else if (availabilityFilter === 'full') {
                    matchesAvailability = spotsLeft <= 0;
                }
            }
            
            return matchesSearch && matchesCategory && matchesDifficulty && matchesAgeGroup && matchesAvailability;
        });
        
        // Apply sorting
        filteredActivities.sort((a, b) => {
            switch (sortBy) {
                case 'name':
                    return a.name.localeCompare(b.name);
                case 'availability':
                    const aSpotsLeft = a.max_participants - a.current_enrollment;
                    const bSpotsLeft = b.max_participants - b.current_enrollment;
                    return bSpotsLeft - aSpotsLeft;
                case 'rating':
                    return parseFloat(b.rating) - parseFloat(a.rating);
                case 'popularity':
                default:
                    return b.current_enrollment - a.current_enrollment;
            }
        });
        
        renderActivities();
        updateActiveFilters();
    }
    
    // Select category
    function selectCategory(category) {
        const cards = document.querySelectorAll('.category-card');
        cards.forEach(card => card.classList.remove('selected'));
        
        if (selectedCategory === category) {
            selectedCategory = '';
        } else {
            selectedCategory = category;
            document.querySelector(`[data-category="${category}"]`).classList.add('selected');
        }
        
        applyFilters();
    }
    
    // Update category statistics
    function updateCategoryStats() {
        const stats = {};
        activities.forEach(activity => {
            if (activity.is_active) {
                stats[activity.category] = (stats[activity.category] || 0) + 1;
            }
        });
        
        document.getElementById('speechCount').textContent = stats['Speech Therapy'] || 0;
        document.getElementById('occupationalCount').textContent = stats['Occupational Therapy'] || 0;
        document.getElementById('physicalCount').textContent = stats['Physical Therapy'] || 0;
        document.getElementById('behavioralCount').textContent = stats['Behavioral Therapy'] || 0;
        document.getElementById('sensoryCount').textContent = stats['Sensory Integration'] || 0;
    }
    
    // Update active filters display
    function updateActiveFilters() {
        const activeFiltersContainer = document.getElementById('activeFilters');
        const filters = [];
        
        if (selectedCategory) {
            filters.push({ type: 'category', value: selectedCategory });
        }
        
        const difficultyFilter = document.getElementById('difficultyFilter').value;
        if (difficultyFilter) {
            filters.push({ type: 'difficulty', value: difficultyFilter });
        }
        
        const ageGroupFilter = document.getElementById('ageGroupFilter').value;
        if (ageGroupFilter) {
            filters.push({ type: 'age_group', value: ageGroupFilter });
        }
        
        const availabilityFilter = document.getElementById('availabilityFilter').value;
        if (availabilityFilter) {
            filters.push({ type: 'availability', value: availabilityFilter });
        }
        
        const searchTerm = document.getElementById('searchInput').value;
        if (searchTerm) {
            filters.push({ type: 'search', value: `Search: ${searchTerm}` });
        }
        
        if (filters.length > 0) {
            const filtersHTML = filters.map(filter => 
                `<span class="filter-tag">
                    ${filter.value}
                    <span class="remove" onclick="removeFilter('${filter.type}', '${filter.value}')">×</span>
                </span>`
            ).join('');
            
            activeFiltersContainer.innerHTML = `
                <span style="font-size: 0.8rem; color: #6c757d;">Active filters:</span>
                ${filtersHTML}
                <span class="clear-filters" onclick="clearAllFilters()">Clear all</span>
            `;
            activeFiltersContainer.style.display = 'flex';
        } else {
            activeFiltersContainer.style.display = 'none';
        }
    }
    
    // Remove specific filter
    function removeFilter(type, value) {
        switch (type) {
            case 'category':
                selectedCategory = '';
                document.querySelectorAll('.category-card').forEach(card => card.classList.remove('selected'));
                break;
            case 'difficulty':
                document.getElementById('difficultyFilter').value = '';
                break;
            case 'age_group':
                document.getElementById('ageGroupFilter').value = '';
                break;
            case 'availability':
                document.getElementById('availabilityFilter').value = '';
                break;
            case 'search':
                document.getElementById('searchInput').value = '';
                break;
        }
        applyFilters();
    }
    
    // Clear all filters
    function clearAllFilters() {
        selectedCategory = '';
        document.querySelectorAll('.category-card').forEach(card => card.classList.remove('selected'));
        document.getElementById('searchInput').value = '';
        document.getElementById('difficultyFilter').value = '';
        document.getElementById('ageGroupFilter').value = '';
        document.getElementById('availabilityFilter').value = '';
        applyFilters();
    }
    
    // Render activities
    function renderActivities() {
        const grid = document.getElementById('activitiesGrid');
        const resultsCount = document.getElementById('resultsCount');
        
        resultsCount.textContent = `${filteredActivities.length} Activities Found`;
        
        if (filteredActivities.length === 0) {
            grid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>No activities found</h3>
                    <p>Try adjusting your filters or search terms</p>
                    <button onclick="clearAllFilters()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer;">Clear Filters</button>
                </div>
            `;
            return;
        }
        
        grid.innerHTML = filteredActivities.map(activity => createActivityCard(activity)).join('');
    }
    
    // Create activity card HTML
    function createActivityCard(activity) {
        const spotsLeft = activity.max_participants - activity.current_enrollment;
        const enrollmentPercentage = (activity.current_enrollment / activity.max_participants) * 100;
        const categoryClass = activity.category.toLowerCase().replace(/\s+/g, '');
        
        let spotsClass = 'high';
        let spotsText = `${spotsLeft} spots left`;
        if (spotsLeft <= 0) {
            spotsClass = 'low';
            spotsText = 'Full';
        } else if (spotsLeft <= 2) {
            spotsClass = 'medium';
        }
        
        return `
            <div class="activity-card">
                <div class="activity-header ${categoryClass}"></div>
                <div class="activity-content">
                    <div class="activity-top">
                        <div class="activity-info">
                            <h3 class="activity-title">${activity.name}</h3>
                            <div class="activity-category">${activity.category}</div>
                        </div>
                        <div class="activity-rating">
                            <i class="fas fa-star"></i>
                            ${activity.rating}
                        </div>
                    </div>
                    
                    <p class="activity-description">${activity.description}</p>
                    
                    <div class="activity-details">
                        <div class="detail-row">
                            <i class="fas fa-user"></i>
                            <span>${activity.teacher}</span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-clock"></i>
                            <span>${activity.duration} minutes • ${activity.schedule}</span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-target"></i>
                            <span>${activity.age_group} • ${activity.difficulty}</span>
                        </div>
                    </div>
                    
                    <div class="enrollment-section">
                        <div class="enrollment-header">
                            <span class="enrollment-label">Enrollment</span>
                            <span class="spots-left ${spotsClass}">${spotsText}</span>
                        </div>
                        <div class="enrollment-bar">
                            <div class="enrollment-fill ${spotsClass}" style="width: ${enrollmentPercentage}%;"></div>
                        </div>
                        <div class="enrollment-count">${activity.current_enrollment}/${activity.max_participants} enrolled</div>
                    </div>
                    
                    <div class="activity-actions">
                        <a href="/activities/${activity.id}" class="btn-view">View Details</a>
                        <button class="btn-more" onclick="showActivityMenu(${activity.id})">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Show activity menu
    function showActivityMenu(activityId) {
        // Implement activity menu functionality
        console.log('Show menu for activity:', activityId);
    }
</script>
@endsection