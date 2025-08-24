/**
 * Enhanced Activities Management System
 * CREAMS - Community-based REhAbilitation Management System
 * 
 * Features:
 * - 25 items per page pagination
 * - Advanced filtering and search
 * - Navigation arrows and numbered pagination
 * - Real-time search suggestions
 * - Comprehensive form validation
 */

class EnhancedActivitiesManager {
    constructor(options) {
        this.activities = options.activities || [];
        this.itemsPerPage = options.itemsPerPage || 25;
        this.currentPage = options.currentPage || 1;
        this.totalPages = options.totalPages || Math.ceil(this.activities.length / this.itemsPerPage);
        this.filteredActivities = [...this.activities];
        this.activeFilters = {};
        this.searchTerm = '';
        this.sortBy = options.defaultSort || 'name_asc';
        
        // Bind methods
        this.init = this.init.bind(this);
        this.renderActivities = this.renderActivities.bind(this);
        this.setupEventListeners = this.setupEventListeners.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.applyFilters = this.applyFilters.bind(this);
        this.updatePagination = this.updatePagination.bind(this);
        
        // Debounce search function
        this.debouncedSearch = this.debounce(this.handleSearch, 300);
    }
    
    init() {
        this.setupEventListeners();
        this.applyFilters();
        this.renderActivities();
        this.updatePagination();
        this.updateResultsCount();
        
        // Initialize tooltips and animations
        this.initializeEnhancements();
        
        console.log('Enhanced Activities Manager initialized with', this.activities.length, 'activities');
    }
    
    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('globalSearch');
        const searchClear = document.getElementById('searchClear');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value;
                this.debouncedSearch();
                this.toggleSearchClear();
            });
            
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.handleSearch();
                }
            });
        }
        
        if (searchClear) {
            searchClear.addEventListener('click', () => {
                this.clearSearch();
            });
        }
        
        // Filter tabs
        document.querySelectorAll('.filter-tab-enhanced').forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.handleTabFilter(e.target.closest('.filter-tab-enhanced'));
            });
        });
        
        // Advanced filters toggle
        const advancedFiltersBtn = document.getElementById('advancedFilters');
        const filterMenu = document.getElementById('filterMenu');
        
        if (advancedFiltersBtn && filterMenu) {
            advancedFiltersBtn.addEventListener('click', () => {
                this.toggleAdvancedFilters();
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!advancedFiltersBtn.contains(e.target) && !filterMenu.contains(e.target)) {
                    filterMenu.classList.remove('active');
                    advancedFiltersBtn.classList.remove('active');
                }
            });
        }
        
        // Filter controls
        document.querySelectorAll('.filter-checkbox, .filter-radio').forEach(input => {
            input.addEventListener('change', () => {
                this.handleFilterChange();
            });
        });
        
        document.querySelectorAll('.modern-select-enhanced').forEach(select => {
            select.addEventListener('change', () => {
                this.handleFilterChange();
            });
        });
        
        // Enrollment range slider
        const enrollmentRange = document.getElementById('enrollmentRange');
        if (enrollmentRange) {
            enrollmentRange.addEventListener('input', (e) => {
                document.getElementById('enrollmentValue').textContent = e.target.value + '%';
                this.handleFilterChange();
            });
        }
        
        // Sort functionality
        const sortSelect = document.getElementById('sortOptions');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortBy = e.target.value;
                this.applyFilters();
                this.renderActivities();
                this.updateResultsCount();
            });
        }
        
        // Clear filters
        const clearFiltersBtn = document.getElementById('clearAllFilters');
        const clearFiltersBtn2 = document.getElementById('clearFiltersBtn');
        
        [clearFiltersBtn, clearFiltersBtn2].forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => {
                    this.clearAllFilters();
                });
            }
        });
        
        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.toggleView(e.target.dataset.view);
            });
        });
        
        // Pagination
        this.setupPaginationListeners();
    }
    
    setupPaginationListeners() {
        // Previous/Next buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.goToPage(this.currentPage - 1);
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (this.currentPage < this.totalPages) {
                    this.goToPage(this.currentPage + 1);
                }
            });
        }
        
        // Page jump
        const pageJump = document.getElementById('pageJump');
        const jumpBtn = document.getElementById('jumpBtn');
        
        if (pageJump && jumpBtn) {
            jumpBtn.addEventListener('click', () => {
                const page = parseInt(pageJump.value);
                if (page >= 1 && page <= this.totalPages) {
                    this.goToPage(page);
                }
            });
            
            pageJump.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    jumpBtn.click();
                }
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Don't trigger shortcuts if user is typing in an input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            if (e.key === 'ArrowLeft' && this.currentPage > 1) {
                e.preventDefault();
                this.goToPage(this.currentPage - 1);
            } else if (e.key === 'ArrowRight' && this.currentPage < this.totalPages) {
                e.preventDefault();
                this.goToPage(this.currentPage + 1);
            }
        });
    }
    
    handleSearch() {
        this.currentPage = 1;
        this.applyFilters();
        this.renderActivities();
        this.updatePagination();
        this.updateResultsCount();
        this.showSearchSuggestions();
    }
    
    showSearchSuggestions() {
        // Implement search suggestions based on activity names, categories, instructors
        const suggestions = document.getElementById('searchSuggestions');
        if (!suggestions || !this.searchTerm.trim()) {
            if (suggestions) suggestions.style.display = 'none';
            return;
        }
        
        const term = this.searchTerm.toLowerCase();
        const suggestionItems = [];
        
        // Get unique suggestions from various fields
        const activityNames = [...new Set(this.activities.map(a => a.activity_name).filter(name => 
            name && name.toLowerCase().includes(term)
        ))].slice(0, 3);
        
        const categories = [...new Set(this.activities.map(a => a.category?.category_name || a.activity_type).filter(cat => 
            cat && cat.toLowerCase().includes(term)
        ))].slice(0, 2);
        
        suggestionItems.push(...activityNames.map(name => ({ type: 'activity', text: name })));
        suggestionItems.push(...categories.map(cat => ({ type: 'category', text: cat })));
        
        if (suggestionItems.length > 0) {
            suggestions.innerHTML = suggestionItems.map(item => 
                `<div class="suggestion-item" data-type="${item.type}" data-text="${item.text}">
                    <i class="fas ${item.type === 'activity' ? 'fa-tasks' : 'fa-tag'}"></i>
                    <span>${item.text}</span>
                </div>`
            ).join('');
            
            suggestions.style.display = 'block';
            
            // Add click handlers for suggestions
            suggestions.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    document.getElementById('globalSearch').value = item.dataset.text;
                    this.searchTerm = item.dataset.text;
                    suggestions.style.display = 'none';
                    this.handleSearch();
                });
            });
        } else {
            suggestions.style.display = 'none';
        }
    }
    
    toggleSearchClear() {
        const searchClear = document.getElementById('searchClear');
        if (searchClear) {
            searchClear.style.display = this.searchTerm ? 'block' : 'none';
        }
    }
    
    clearSearch() {
        const searchInput = document.getElementById('globalSearch');
        const suggestions = document.getElementById('searchSuggestions');
        
        if (searchInput) searchInput.value = '';
        if (suggestions) suggestions.style.display = 'none';
        
        this.searchTerm = '';
        this.toggleSearchClear();
        this.handleSearch();
    }
    
    handleTabFilter(tab) {
        // Update active tab
        document.querySelectorAll('.filter-tab-enhanced').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        // Apply filter
        const filter = tab.dataset.filter;
        if (filter === 'all') {
            delete this.activeFilters.category;
        } else {
            this.activeFilters.category = filter;
        }
        
        this.currentPage = 1;
        this.applyFilters();
        this.renderActivities();
        this.updatePagination();
        this.updateResultsCount();
    }
    
    toggleAdvancedFilters() {
        const button = document.getElementById('advancedFilters');
        const menu = document.getElementById('filterMenu');
        
        if (button && menu) {
            button.classList.toggle('active');
            menu.classList.toggle('active');
        }
    }
    
    handleFilterChange() {
        this.collectActiveFilters();
        this.currentPage = 1;
        this.applyFilters();
        this.renderActivities();
        this.updatePagination();
        this.updateResultsCount();
        this.updateActiveFiltersDisplay();
    }
    
    collectActiveFilters() {
        // Difficulty level filters
        const difficultyFilters = Array.from(document.querySelectorAll('.filter-checkbox[data-filter-type=\"difficulty\"]:checked'))
            .map(cb => cb.value);
        if (difficultyFilters.length > 0) {
            this.activeFilters.difficulty = difficultyFilters;
        } else {
            delete this.activeFilters.difficulty;
        }
        
        // Age group filter
        const ageFilter = document.getElementById('ageGroupFilter')?.value;
        if (ageFilter) {
            this.activeFilters.age = ageFilter;
        } else {
            delete this.activeFilters.age;
        }
        
        // Centre filter
        const centreFilter = document.getElementById('centreFilter')?.value;
        if (centreFilter) {
            this.activeFilters.centre = centreFilter;
        } else {
            delete this.activeFilters.centre;
        }
        
        // Instructor filter
        const instructorFilter = document.getElementById('instructorFilter')?.value;
        if (instructorFilter) {
            this.activeFilters.instructor = instructorFilter;
        } else {
            delete this.activeFilters.instructor;
        }
        
        // Schedule status filter
        const scheduleFilter = document.querySelector('.filter-radio[data-filter-type=\"schedule\"]:checked')?.value;
        if (scheduleFilter) {
            this.activeFilters.schedule = scheduleFilter;
        } else {
            delete this.activeFilters.schedule;
        }
        
        // Enrollment filter
        const enrollmentRange = document.getElementById('enrollmentRange')?.value;
        if (enrollmentRange && enrollmentRange > 0) {
            this.activeFilters.enrollment = parseInt(enrollmentRange);
        } else {
            delete this.activeFilters.enrollment;
        }
    }
    
    applyFilters() {
        let filtered = [...this.activities];
        
        // Apply search filter
        if (this.searchTerm.trim()) {
            const term = this.searchTerm.toLowerCase();
            filtered = filtered.filter(activity => {
                return (
                    activity.activity_name?.toLowerCase().includes(term) ||
                    activity.activity_description?.toLowerCase().includes(term) ||
                    activity.activity_type?.toLowerCase().includes(term) ||
                    activity.category?.category_name?.toLowerCase().includes(term) ||
                    activity.instructor?.name?.toLowerCase().includes(term) ||
                    activity.centre?.centre_name?.toLowerCase().includes(term)
                );
            });
        }
        
        // Apply category filter (from tabs)
        if (this.activeFilters.category) {
            filtered = filtered.filter(activity => {
                if (this.activeFilters.category === 'active') {
                    return activity.is_active === true || activity.is_active === 1;
                }
                return activity.category?.category_type === this.activeFilters.category ||
                       activity.activity_type?.toLowerCase() === this.activeFilters.category;
            });
        }
        
        // Apply difficulty filters
        if (this.activeFilters.difficulty) {
            filtered = filtered.filter(activity => 
                this.activeFilters.difficulty.includes(activity.difficulty_level)
            );
        }
        
        // Apply age filter
        if (this.activeFilters.age) {
            filtered = filtered.filter(activity => 
                activity.age_group === this.activeFilters.age
            );
        }
        
        // Apply centre filter
        if (this.activeFilters.centre) {
            filtered = filtered.filter(activity => 
                activity.centre_id === this.activeFilters.centre
            );
        }
        
        // Apply instructor filter
        if (this.activeFilters.instructor) {
            filtered = filtered.filter(activity => 
                activity.instructor_id === this.activeFilters.instructor ||
                activity.created_by === this.activeFilters.instructor
            );
        }
        
        // Apply schedule filter
        if (this.activeFilters.schedule) {
            filtered = filtered.filter(activity => 
                activity.activity_status === this.activeFilters.schedule
            );
        }
        
        // Apply enrollment filter
        if (this.activeFilters.enrollment) {
            filtered = filtered.filter(activity => {
                const enrollmentPercentage = activity.max_participants > 0 
                    ? (activity.current_participants / activity.max_participants) * 100 
                    : 0;
                return enrollmentPercentage >= this.activeFilters.enrollment;
            });
        }
        
        // Apply sorting
        filtered = this.sortActivities(filtered);
        
        this.filteredActivities = filtered;
        this.totalPages = Math.ceil(filtered.length / this.itemsPerPage);
        
        // Adjust current page if needed
        if (this.currentPage > this.totalPages && this.totalPages > 0) {
            this.currentPage = this.totalPages;
        } else if (this.totalPages === 0) {
            this.currentPage = 1;
        }
    }
    
    sortActivities(activities) {
        const sortedActivities = [...activities];
        
        switch (this.sortBy) {
            case 'name_asc':
                return sortedActivities.sort((a, b) => 
                    a.activity_name.localeCompare(b.activity_name)
                );
            case 'name_desc':
                return sortedActivities.sort((a, b) => 
                    b.activity_name.localeCompare(a.activity_name)
                );
            case 'date_newest':
                return sortedActivities.sort((a, b) => 
                    new Date(b.created_at) - new Date(a.created_at)
                );
            case 'date_oldest':
                return sortedActivities.sort((a, b) => 
                    new Date(a.created_at) - new Date(b.created_at)
                );
            case 'popularity':
                return sortedActivities.sort((a, b) => 
                    (b.current_participants || 0) - (a.current_participants || 0)
                );
            case 'capacity':
                return sortedActivities.sort((a, b) => 
                    (b.max_participants || 0) - (a.max_participants || 0)
                );
            default:
                return sortedActivities;
        }
    }
    
    renderActivities() {
        const container = document.getElementById('activitiesGrid');
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        
        if (!container) return;
        
        // Get all current cards
        const allCards = Array.from(container.querySelectorAll('.activity-card'));
        
        // Calculate pagination
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        
        // Hide all cards first
        allCards.forEach(card => card.style.display = 'none');
        
        // Show only the cards that match current filters and pagination
        const filteredCards = this.getFilteredCards(allCards);
        const pageCards = filteredCards.slice(startIndex, endIndex);
        
        if (pageCards.length === 0) {
            if (emptyState) {
                emptyState.style.display = 'flex';
                emptyState.innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No activities found</h4>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                    </div>
                `;
            }
        } else {
            if (emptyState) emptyState.style.display = 'none';
            pageCards.forEach(card => card.style.display = 'block');
        }
        
        // Update the filtered activities count for pagination
        this.filteredActivities = filteredCards.map(card => ({
            id: this.getActivityIdFromCard(card)
        }));
        this.totalPages = Math.ceil(filteredCards.length / this.itemsPerPage);
        
        // Update filter tab counts
        this.updateFilterTabCounts(allCards);
    }
    
    getFilteredCards(allCards) {
        return allCards.filter(card => {
            const cardData = this.extractCardData(card);
            
            // Apply search filter
            if (this.searchTerm.trim()) {
                const term = this.searchTerm.toLowerCase();
                const searchText = [
                    cardData.name,
                    cardData.description,
                    cardData.category
                ].join(' ').toLowerCase();
                
                if (!searchText.includes(term)) return false;
            }
            
            // Apply category filter (from tabs)
            if (this.activeFilters.category) {
                if (this.activeFilters.category === 'active') {
                    if (!cardData.isActive) return false;
                } else if (this.activeFilters.category === 'rehabilitation') {
                    if (!['Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 'Behavioral Therapy'].includes(cardData.category)) return false;
                } else if (this.activeFilters.category === 'academic') {
                    if (!['Mathematics', 'Basic Literacy', 'Life Skills'].includes(cardData.category)) return false;
                } else if (this.activeFilters.category === 'recreational') {
                    if (!['Arts & Crafts', 'Sports & Games', 'Music Therapy'].includes(cardData.category)) return false;
                }
            }
            
            return true;
        });
    }
    
    extractCardData(cardElement) {
        const titleElement = cardElement.querySelector('.activity-name');
        const descriptionElement = cardElement.querySelector('.activity-description');
        const categoryElement = cardElement.querySelector('.activity-category');
        const statusElement = cardElement.querySelector('.status-badge');
        
        return {
            name: titleElement ? titleElement.textContent.trim() : '',
            description: descriptionElement ? descriptionElement.textContent.trim() : '',
            category: categoryElement ? categoryElement.textContent.trim() : '',
            isActive: statusElement ? statusElement.textContent.trim().toLowerCase() === 'active' : false
        };
    }
    
    getActivityIdFromCard(cardElement) {
        const viewButton = cardElement.querySelector('a[href*="/activities/"]');
        if (viewButton) {
            const href = viewButton.getAttribute('href');
            const match = href.match(/\/activities\/(\d+)/);
            return match ? parseInt(match[1]) : null;
        }
        return null;
    }
    
    updateFilterTabCounts(allCards) {
        // Count activities by category
        const counts = {
            all: allCards.length,
            active: 0,
            rehabilitation: 0,
            academic: 0,
            recreational: 0
        };
        
        allCards.forEach(card => {
            const cardData = this.extractCardData(card);
            
            if (cardData.isActive) counts.active++;
            
            if (['Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 'Behavioral Therapy'].includes(cardData.category)) {
                counts.rehabilitation++;
            } else if (['Mathematics', 'Basic Literacy', 'Life Skills'].includes(cardData.category)) {
                counts.academic++;
            } else if (['Arts & Crafts', 'Sports & Games', 'Music Therapy'].includes(cardData.category)) {
                counts.recreational++;
            }
        });
        
        // Update the counts in the filter tabs
        Object.keys(counts).forEach(filter => {
            const tab = document.querySelector(`[data-filter="${filter}"] .tab-count`);
            if (tab) {
                tab.textContent = counts[filter];
            }
        });
    }
    
    createActivityCard(activity) {
        const categoryIcons = {
            'Physical Therapy': '🏃‍♂️',
            'Occupational Therapy': '🖐️',
            'Speech Therapy': '🗣️',
            'Behavioral Therapy': '🧠',
            'Music Therapy': '🎵',
            'Mathematics': '🔢',
            'Basic Literacy': '📚',
            'Life Skills': '🏠',
            'Arts & Crafts': '🎨',
            'Sports & Games': '⚽',
            'Islamic Studies': '🕌',
            'default': '🎯'
        };
        
        const icon = categoryIcons[activity.category?.category_name] || 
                     categoryIcons[activity.activity_type] || 
                     categoryIcons['default'];
        
        const isActive = activity.is_active === true || activity.is_active === 1;
        const enrollmentPercentage = activity.max_participants > 0 
            ? Math.round((activity.current_participants || 0) / activity.max_participants * 100)
            : 0;
        
        return `
            <div class="activity-card-enhanced fade-in" 
                 data-activity-id="${activity.id}"
                 data-category="${activity.category?.category_type || activity.activity_type}"
                 data-status="${isActive ? 'active' : 'inactive'}"
                 data-difficulty="${activity.difficulty_level || 'beginner'}"
                 data-age="${activity.age_group || 'all_ages'}"
                 data-instructor="${activity.instructor_id || activity.created_by}"
                 data-centre="${activity.centre_id}">
                
                <!-- Card Header -->
                <div class="card-header-enhanced">
                    <div class="card-header-main">
                        <div class="activity-avatar-enhanced">
                            <span class="activity-emoji">${icon}</span>
                        </div>
                        <div class="card-title-section">
                            <h3 class="card-title-enhanced">${activity.activity_name}</h3>
                            <p class="card-subtitle-enhanced">${activity.category?.category_name || activity.activity_type || 'General'}</p>
                        </div>
                    </div>
                    
                    <div class="card-actions-enhanced">
                        <div class="status-badge-enhanced ${isActive ? 'active' : 'inactive'}">
                            <div class="status-dot"></div>
                            <span>${isActive ? 'Active' : 'Inactive'}</span>
                        </div>
                        
                        <div class="card-menu-enhanced">
                            <button class="menu-btn-enhanced" data-activity-id="${activity.id}">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="menu-dropdown-enhanced">
                                <a href="/activities/${activity.id}" class="menu-item-enhanced">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                ${this.getUserRole() === 'admin' ? `
                                    <a href="/activities/${activity.id}/edit" class="menu-item-enhanced">
                                        <i class="fas fa-edit"></i>
                                        Edit Activity
                                    </a>
                                    <a href="/activities/${activity.id}/sessions" class="menu-item-enhanced">
                                        <i class="fas fa-calendar"></i>
                                        Manage Sessions
                                    </a>
                                    <button class="menu-item-enhanced delete-btn" data-activity-id="${activity.id}">
                                        <i class="fas fa-trash"></i>
                                        Delete Activity
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Body -->
                <div class="card-body-enhanced">
                    <p class="card-description-enhanced">
                        ${activity.activity_description ? 
                          (activity.activity_description.length > 120 ? 
                           activity.activity_description.substring(0, 120) + '...' : 
                           activity.activity_description) 
                          : 'No description available'}
                    </p>
                    
                    <div class="card-metrics-enhanced">
                        <div class="metric-enhanced">
                            <div class="metric-icon-enhanced difficulty-${activity.difficulty_level || 'beginner'}">
                                <i class="fas fa-signal"></i>
                            </div>
                            <div class="metric-content-enhanced">
                                <span class="metric-value-enhanced">${this.capitalize(activity.difficulty_level || 'Beginner')}</span>
                                <span class="metric-label-enhanced">Difficulty</span>
                            </div>
                        </div>
                        
                        <div class="metric-enhanced">
                            <div class="metric-icon-enhanced age">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="metric-content-enhanced">
                                <span class="metric-value-enhanced">${this.formatAgeGroup(activity.age_group)}</span>
                                <span class="metric-label-enhanced">Age Group</span>
                            </div>
                        </div>
                        
                        <div class="metric-enhanced">
                            <div class="metric-icon-enhanced sessions">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="metric-content-enhanced">
                                <span class="metric-value-enhanced">${activity.sessions_count || 0}</span>
                                <span class="metric-label-enhanced">Sessions</span>
                            </div>
                        </div>
                        
                        <div class="metric-enhanced">
                            <div class="metric-icon-enhanced participants">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="metric-content-enhanced">
                                <span class="metric-value-enhanced">${activity.current_participants || 0}/${activity.max_participants || 0}</span>
                                <span class="metric-label-enhanced">Enrolled</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enrollment Progress Bar -->
                    <div class="enrollment-progress">
                        <div class="progress-bar-enhanced">
                            <div class="progress-fill-enhanced" style="width: ${enrollmentPercentage}%"></div>
                        </div>
                        <span class="progress-text-enhanced">${enrollmentPercentage}% enrolled</span>
                    </div>
                </div>
                
                <!-- Card Footer -->
                <div class="card-footer-enhanced">
                    <div class="footer-info-enhanced">
                        <div class="instructor-info-enhanced">
                            <div class="instructor-avatar-enhanced">
                                ${activity.instructor?.avatar ? 
                                  `<img src="/storage/avatars/${activity.instructor.avatar}" alt="${activity.instructor.name}">` :
                                  `<div class="avatar-placeholder-enhanced">${(activity.instructor?.name || 'U').charAt(0)}</div>`
                                }
                            </div>
                            <div class="instructor-details">
                                <span class="instructor-name">${activity.instructor?.name || 'Unassigned'}</span>
                                <span class="instructor-role">${activity.instructor?.role || 'Instructor'}</span>
                            </div>
                        </div>
                        
                        <div class="footer-actions">
                            <button class="btn-quick-view" data-activity-id="${activity.id}" title="Quick View">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${this.getUserRole() === 'admin' ? `
                                <button class="btn-quick-edit" data-activity-id="${activity.id}" title="Quick Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            ` : ''}
                            <button class="btn-enroll" data-activity-id="${activity.id}" title="Enroll Trainee">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    setupCardListeners() {
        // Since we're using existing Blade cards, we don't need to setup additional listeners
        // The cards already have their own click handlers and buttons
        console.log('Card listeners setup - using existing Blade card structure');
    }
    
    goToPage(page) {
        if (page < 1 || page > this.totalPages) return;
        
        this.currentPage = page;
        this.renderActivities();
        this.updatePagination();
        this.updateResultsCount();
        
        // Smooth scroll to top of activities
        document.getElementById('activitiesGrid').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
    
    updatePagination() {
        const container = document.getElementById('paginationContainer');
        if (!container || this.totalPages <= 1) {
            if (container) container.style.display = 'none';
            return;
        }
        
        container.style.display = 'flex';
        
        // Update pagination info
        document.getElementById('currentPage').textContent = this.currentPage;
        document.getElementById('totalPages').textContent = this.totalPages;
        
        // Update prev/next buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        if (prevBtn) {
            prevBtn.disabled = this.currentPage <= 1;
        }
        
        if (nextBtn) {
            nextBtn.disabled = this.currentPage >= this.totalPages;
        }
        
        // Update page jump input
        const pageJump = document.getElementById('pageJump');
        if (pageJump) {
            pageJump.max = this.totalPages;
            pageJump.value = this.currentPage;
        }
        
        // Generate page numbers
        this.generatePageNumbers();
    }
    
    generatePageNumbers() {
        const container = document.getElementById('paginationNumbers');
        if (!container) return;
        
        const pages = [];
        const maxVisible = 7;
        
        if (this.totalPages <= maxVisible) {
            // Show all pages
            for (let i = 1; i <= this.totalPages; i++) {
                pages.push(i);
            }
        } else {
            // Show pages with ellipsis
            pages.push(1);
            
            if (this.currentPage > 4) {
                pages.push('...');
            }
            
            const start = Math.max(2, this.currentPage - 1);
            const end = Math.min(this.totalPages - 1, this.currentPage + 1);
            
            for (let i = start; i <= end; i++) {
                if (!pages.includes(i)) {
                    pages.push(i);
                }
            }
            
            if (this.currentPage < this.totalPages - 3) {
                pages.push('...');
            }
            
            if (!pages.includes(this.totalPages)) {
                pages.push(this.totalPages);
            }
        }
        
        container.innerHTML = pages.map(page => {
            if (page === '...') {
                return '<span class="page-ellipsis">...</span>';
            }
            
            const isActive = page === this.currentPage;
            return `
                <button class="page-number ${isActive ? 'active' : ''}" 
                        data-page="${page}" 
                        ${isActive ? 'aria-current="page"' : ''}>
                    ${page}
                </button>
            `;
        }).join('');
        
        // Add click listeners to page numbers
        container.querySelectorAll('.page-number').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = parseInt(btn.dataset.page);
                this.goToPage(page);
            });
        });
    }
    
    updateResultsCount() {
        const showingCount = document.getElementById('showingCount');
        const totalCount = document.getElementById('totalCount');
        
        if (showingCount && totalCount) {
            const startIndex = (this.currentPage - 1) * this.itemsPerPage;
            const endIndex = Math.min(startIndex + this.itemsPerPage, this.filteredActivities.length);
            const showing = this.filteredActivities.length === 0 ? 0 : Math.max(1, startIndex + 1);
            
            showingCount.textContent = `${showing}-${endIndex}`;
            totalCount.textContent = this.filteredActivities.length;
        }
    }
    
    updateActiveFiltersDisplay() {
        const container = document.getElementById('activeFiltersDisplay');
        if (!container) return;
        
        const filterTags = [];
        
        if (this.searchTerm) {
            filterTags.push({ type: 'search', text: `Search: "${this.searchTerm}"` });
        }
        
        if (this.activeFilters.category && this.activeFilters.category !== 'all') {
            filterTags.push({ type: 'category', text: `Category: ${this.capitalize(this.activeFilters.category)}` });
        }
        
        if (this.activeFilters.difficulty) {
            filterTags.push({ 
                type: 'difficulty', 
                text: `Difficulty: ${this.activeFilters.difficulty.map(d => this.capitalize(d)).join(', ')}` 
            });
        }
        
        if (this.activeFilters.age) {
            filterTags.push({ type: 'age', text: `Age: ${this.formatAgeGroup(this.activeFilters.age)}` });
        }
        
        if (this.activeFilters.centre) {
            filterTags.push({ type: 'centre', text: `Centre: ${this.activeFilters.centre}` });
        }
        
        if (filterTags.length > 0) {
            container.innerHTML = filterTags.map(tag => 
                `<span class="filter-tag">
                    ${tag.text}
                    <span class="remove" data-filter-type="${tag.type}">×</span>
                </span>`
            ).join('');
            
            container.style.display = 'flex';
            
            // Add remove handlers
            container.querySelectorAll('.remove').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.removeFilter(btn.dataset.filterType);
                });
            });
        } else {
            container.style.display = 'none';
        }
        
        // Update filter count
        const filterCount = document.getElementById('activeFilterCount');
        if (filterCount) {
            const count = Object.keys(this.activeFilters).length + (this.searchTerm ? 1 : 0);
            if (count > 0) {
                filterCount.textContent = count;
                filterCount.style.display = 'inline';
            } else {
                filterCount.style.display = 'none';
            }
        }
    }
    
    removeFilter(type) {
        switch (type) {
            case 'search':
                this.clearSearch();
                break;
            case 'category':
                delete this.activeFilters.category;
                document.querySelector('.filter-tab-enhanced[data-filter="all"]').click();
                break;
            case 'difficulty':
                delete this.activeFilters.difficulty;
                document.querySelectorAll('.filter-checkbox[data-filter-type="difficulty"]').forEach(cb => cb.checked = false);
                break;
            case 'age':
                delete this.activeFilters.age;
                const ageSelect = document.getElementById('ageGroupFilter');
                if (ageSelect) ageSelect.value = '';
                break;
            case 'centre':
                delete this.activeFilters.centre;
                const centreSelect = document.getElementById('centreFilter');
                if (centreSelect) centreSelect.value = '';
                break;
        }
        
        this.handleFilterChange();
    }
    
    clearAllFilters() {
        // Clear search
        this.clearSearch();
        
        // Clear active filters
        this.activeFilters = {};
        
        // Reset form controls
        document.querySelectorAll('.filter-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.filter-radio').forEach(radio => {
            radio.checked = radio.value === '';
        });
        document.querySelectorAll('.modern-select-enhanced').forEach(select => select.value = '');
        
        const enrollmentRange = document.getElementById('enrollmentRange');
        if (enrollmentRange) {
            enrollmentRange.value = 0;
            document.getElementById('enrollmentValue').textContent = '0%';
        }
        
        // Reset to "All" tab
        document.querySelectorAll('.filter-tab-enhanced').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.filter === 'all');
        });
        
        // Close advanced filters
        document.getElementById('filterMenu')?.classList.remove('active');
        document.getElementById('advancedFilters')?.classList.remove('active');
        
        // Apply changes
        this.currentPage = 1;
        this.applyFilters();
        this.renderActivities();
        this.updatePagination();
        this.updateResultsCount();
        this.updateActiveFiltersDisplay();
    }
    
    toggleView(viewType) {
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === viewType);
        });
        
        const grid = document.getElementById('activitiesGrid');
        if (grid) {
            grid.classList.toggle('list-view', viewType === 'list');
            grid.classList.toggle('grid-view', viewType === 'grid');
        }
        
        // Store preference
        localStorage.setItem('activities_view_preference', viewType);
    }
    
    showActivityModal(activityId) {
        const activity = this.activities.find(a => a.id == activityId);
        if (!activity) return;
        
        const modal = document.getElementById('activityDetailModal');
        const modalContent = document.getElementById('modalContent');
        
        if (modal && modalContent) {
            modalContent.innerHTML = `
                <div class="activity-detail-content">
                    <div class="activity-header">
                        <h3>${activity.activity_name}</h3>
                        <span class="badge ${activity.is_active ? 'badge-success' : 'badge-secondary'}">
                            ${activity.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                    <div class="activity-details">
                        <p><strong>Description:</strong> ${activity.activity_description || 'No description available'}</p>
                        <p><strong>Category:</strong> ${activity.category?.category_name || activity.activity_type}</p>
                        <p><strong>Difficulty:</strong> ${this.capitalize(activity.difficulty_level || 'Beginner')}</p>
                        <p><strong>Age Group:</strong> ${this.formatAgeGroup(activity.age_group)}</p>
                        <p><strong>Capacity:</strong> ${activity.current_participants || 0}/${activity.max_participants || 0}</p>
                        <p><strong>Instructor:</strong> ${activity.instructor?.name || 'Unassigned'}</p>
                    </div>
                    <div class="activity-actions">
                        <a href="/activities/${activity.id}" class="btn btn-primary">View Full Details</a>
                        ${this.getUserRole() === 'admin' ? `
                            <a href="/activities/${activity.id}/edit" class="btn btn-secondary">Edit Activity</a>
                        ` : ''}
                    </div>
                </div>
            `;
            
            $(modal).modal('show');
        }
    }
    
    confirmDelete(activityId) {
        if (confirm('Are you sure you want to delete this activity? This action cannot be undone.')) {
            // In a real application, you would make an AJAX call here
            window.location.href = `/activities/${activityId}/delete`;
        }
    }
    
    initializeEnhancements() {
        // Load saved view preference
        const savedView = localStorage.getItem('activities_view_preference') || 'grid';
        this.toggleView(savedView);
        
        // Initialize tooltips if Bootstrap is available
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[title]').tooltip();
        }
        
        // Keyboard shortcuts help
        this.setupKeyboardShortcuts();
    }
    
    setupKeyboardShortcuts() {
        // Show shortcuts modal on Ctrl+?
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                this.showKeyboardShortcuts();
            }
        });
    }
    
    showKeyboardShortcuts() {
        alert(`Keyboard Shortcuts:
        
• Arrow Left/Right: Navigate pages
• Ctrl + /: Show this help
• Enter in search: Search activities
• Enter in page jump: Jump to page
• Esc: Clear search/close dialogs`);
    }
    
    // Utility functions
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
    
    capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }
    
    formatAgeGroup(ageGroup) {
        const ageGroups = {
            'children': 'Children (4-8)',
            'adolescents': 'Adolescents (9-16)',
            'adults': 'Adults (17+)',
            'all_ages': 'All Ages'
        };
        return ageGroups[ageGroup] || 'All Ages';
    }
    
    getUserRole() {
        // In a real application, this would come from authentication
        return document.querySelector('meta[name="user-role"]')?.content || 'teacher';
    }
}

// Initialize when DOM is ready
if (typeof window !== 'undefined') {
    window.EnhancedActivitiesManager = EnhancedActivitiesManager;
}