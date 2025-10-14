/**
 * Enhanced Centres Management JavaScript
 * Provides comprehensive functionality for centre management interface
 */

class EnhancedCentresManager {
    constructor(options) {
        this.options = {
            itemsPerPage: 25,
            centres: [],
            currentUser: { role: 'guest', id: null },
            ...options
        };

        this.currentPage = 1;
        this.currentView = 'grid';
        this.filteredCentres = [...this.options.centres];
        this.searchTimeout = null;
        this.activeFilters = {
            search: '',
            status: '',
            capacity: '',
            sort: 'name_asc'
        };

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupSearch();
        this.setupFilters();
        this.setupPagination();
        this.setupViewToggle();
        this.renderCentres();

        console.log('Enhanced Centres Manager initialized');
    }

    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('centreSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
            searchInput.addEventListener('focus', () => this.showSearchSuggestions());
        }

        // Filter controls
        const statusFilter = document.getElementById('statusFilter');
        const capacityFilter = document.getElementById('capacityFilter');
        const sortFilter = document.getElementById('sortFilter');

        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => this.handleFilter('status', e.target.value));
        }

        if (capacityFilter) {
            capacityFilter.addEventListener('change', (e) => this.handleFilter('capacity', e.target.value));
        }

        if (sortFilter) {
            sortFilter.addEventListener('change', (e) => this.handleFilter('sort', e.target.value));
        }

        // Dropdown toggles
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('dropdown-toggle')) {
                e.preventDefault();
                this.toggleDropdown(e.target);
            } else {
                this.closeAllDropdowns();
            }
        });

        // Quick view functionality
        document.addEventListener('click', (e) => {
            if (e.target.closest('.dropdown-menu')) {
                return; // Don't close dropdown when clicking inside it
            }
            this.closeAllDropdowns();
        });
    }

    setupSearch() {
        this.searchSuggestions = [
            'Active centres',
            'Inactive centres',
            'High capacity centres',
            'Recently added centres',
            'Centres with most staff',
            'Centres with most trainees'
        ];
    }

    setupFilters() {
        // Initialize filter state
        this.applyFilters();
    }

    setupPagination() {
        this.updatePagination();
    }

    setupViewToggle() {
        const viewBtns = document.querySelectorAll('.view-mode-btn');
        viewBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.target.dataset.view;
                this.switchView(view);
            });
        });
    }

    handleSearch(query) {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.activeFilters.search = query.toLowerCase().trim();
            this.applyFilters();
            this.updateSearchUI(query);
        }, 300);
    }

    updateSearchUI(query) {
        const clearBtn = document.querySelector('.search-clear');
        if (clearBtn) {
            clearBtn.style.display = query ? 'block' : 'none';
        }

        if (query) {
            this.hideSearchSuggestions();
        } else {
            this.showSearchSuggestions();
        }
    }

    showSearchSuggestions() {
        const suggestions = document.getElementById('searchSuggestions');
        const searchInput = document.getElementById('centreSearch');

        if (!suggestions || !searchInput || searchInput.value.trim()) return;

        suggestions.innerHTML = this.searchSuggestions.map(suggestion =>
            `<div class="suggestion-item" onclick="centresManager.applySuggestion('${suggestion}')">${suggestion}</div>`
        ).join('');

        suggestions.style.display = 'block';
    }

    hideSearchSuggestions() {
        const suggestions = document.getElementById('searchSuggestions');
        if (suggestions) {
            suggestions.style.display = 'none';
        }
    }

    applySuggestion(suggestion) {
        const searchInput = document.getElementById('centreSearch');
        if (searchInput) {
            searchInput.value = suggestion;
            this.handleSearch(suggestion);
        }
        this.hideSearchSuggestions();
    }

    handleFilter(type, value) {
        this.activeFilters[type] = value;
        this.applyFilters();
    }

    applyFilters() {
        let filtered = [...this.options.centres];

        // Apply search filter
        if (this.activeFilters.search) {
            filtered = filtered.filter(centre => {
                const searchText = this.activeFilters.search;
                return (
                    centre.centre_name?.toLowerCase().includes(searchText) ||
                    centre.centre_address?.toLowerCase().includes(searchText) ||
                    centre.centre_manager?.toLowerCase().includes(searchText) ||
                    centre.centre_facilities?.toLowerCase().includes(searchText) ||
                    centre.centre_description?.toLowerCase().includes(searchText)
                );
            });
        }

        // Apply status filter
        if (this.activeFilters.status) {
            filtered = filtered.filter(centre => centre.centre_status === this.activeFilters.status);
        }

        // Apply capacity filter
        if (this.activeFilters.capacity) {
            filtered = filtered.filter(centre => {
                const capacity = parseInt(centre.centre_capacity) || 0;
                switch (this.activeFilters.capacity) {
                    case 'small': return capacity <= 50;
                    case 'medium': return capacity > 50 && capacity <= 100;
                    case 'large': return capacity > 100 && capacity <= 200;
                    case 'xlarge': return capacity > 200;
                    default: return true;
                }
            });
        }

        // Apply sorting
        filtered = this.sortCentres(filtered, this.activeFilters.sort);

        this.filteredCentres = filtered;
        this.currentPage = 1; // Reset to first page
        this.renderCentres();
        this.updatePagination();
        this.updateResultsCount();
    }

    sortCentres(centres, sortType) {
        return centres.sort((a, b) => {
            switch (sortType) {
                case 'name_asc':
                    return (a.centre_name || '').localeCompare(b.centre_name || '');
                case 'name_desc':
                    return (b.centre_name || '').localeCompare(a.centre_name || '');
                case 'staff_desc':
                    return (b.users_count || 0) - (a.users_count || 0);
                case 'trainees_desc':
                    return (b.trainees_count || 0) - (a.trainees_count || 0);
                case 'capacity_desc':
                    return (parseInt(b.centre_capacity) || 0) - (parseInt(a.centre_capacity) || 0);
                case 'newest':
                    return new Date(b.created_at || 0) - new Date(a.created_at || 0);
                default:
                    return 0;
            }
        });
    }

    renderCentres() {
        const startIndex = (this.currentPage - 1) * this.options.itemsPerPage;
        const endIndex = startIndex + this.options.itemsPerPage;
        const centresForPage = this.filteredCentres.slice(startIndex, endIndex);

        if (this.currentView === 'grid') {
            this.renderGridView(centresForPage);
        } else {
            this.renderListView(centresForPage);
        }

        // Add loading animation
        this.addLoadingAnimation();
    }

    renderGridView(centres) {
        const gridContainer = document.getElementById('gridView');
        if (!gridContainer) return;

        if (centres.length === 0) {
            gridContainer.innerHTML = this.getEmptyStateHTML();
            return;
        }

        gridContainer.innerHTML = centres.map(centre => this.getCentreCardHTML(centre)).join('');
    }

    renderListView(centres) {
        const listContainer = document.querySelector('#listView .centres-list');
        if (!listContainer) return;

        // Keep the header, replace the items
        const header = listContainer.querySelector('.list-header');
        const headerHTML = header ? header.outerHTML : this.getListHeaderHTML();

        if (centres.length === 0) {
            listContainer.innerHTML = headerHTML + '<div class="list-empty"><p>No centres found matching your criteria.</p></div>';
            return;
        }

        const itemsHTML = centres.map(centre => this.getCentreListItemHTML(centre)).join('');
        listContainer.innerHTML = headerHTML + itemsHTML;
    }

    getCentreCardHTML(centre) {
        const statusClass = centre.centre_status || 'unknown';
        const imageUrl = centre.centre_image ?
            `/storage/centres/${centre.centre_image}` :
            '/images/centre-placeholder.jpg';

        return `
            <div class="centre-card slide-in-up"
                 data-name="${(centre.centre_name || '').toLowerCase()}"
                 data-location="${(centre.centre_address || '').toLowerCase()}"
                 data-status="${centre.centre_status || ''}"
                 data-capacity="${centre.centre_capacity || 0}"
                 data-staff="${centre.users_count || 0}"
                 data-trainees="${centre.trainees_count || 0}">

                <div class="card-header">
                    <div class="centre-status">
                        <span class="status-indicator status-${statusClass}"></span>
                        <span class="status-text">${this.capitalize(statusClass)}</span>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn" onclick="centresManager.showCentreQuickView('${centre.centre_id}')" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="dropdown">
                            <button class="action-btn dropdown-toggle" title="More Actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="/centres/${centre.centre_id}">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
                                ${this.options.currentUser.role === 'admin' || this.options.currentUser.role === 'supervisor' ? `
                                <a class="dropdown-item" href="/centres/${centre.centre_id}/edit">
                                    <i class="fas fa-edit"></i> Edit Centre
                                </a>` : ''}
                                <a class="dropdown-item" href="/centres/${centre.centre_id}/assets">
                                    <i class="fas fa-boxes"></i> Manage Assets
                                </a>
                                <a class="dropdown-item" href="/centres/attendance?centre=${centre.centre_id}">
                                    <i class="fas fa-clock"></i> Attendance
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-image">
                    <img src="${imageUrl}"
                         alt="${centre.centre_name || 'Centre'}"
                         onerror="this.src='/images/centre-placeholder.jpg'">
                    <div class="image-overlay">
                        <div class="overlay-content">
                            <h3 class="centre-name">${centre.centre_name || 'Unnamed Centre'}</h3>
                            <p class="centre-location">
                                <i class="fas fa-map-marker-alt"></i>
                                ${this.truncate(centre.centre_address || 'Location not specified', 50)}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-content">
                    <div class="centre-info">
                        <h4 class="centre-title">${centre.centre_name || 'Unnamed Centre'}</h4>
                        <p class="centre-description">
                            ${this.truncate(centre.centre_description || 'No description available', 100)}
                        </p>
                    </div>

                    <div class="centre-stats">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value">${centre.users_count || 0}</span>
                                <span class="stat-label">Staff</span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value">${centre.trainees_count || 0}</span>
                                <span class="stat-label">Trainees</span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value">${centre.asset-parents_count || 0}</span>
                                <span class="stat-label">Assets</span>
                            </div>
                        </div>
                    </div>

                    <div class="centre-details">
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>${centre.centre_phone || 'No phone'}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user-tie"></i>
                            <span>${centre.centre_manager || 'No manager assigned'}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>Capacity: ${centre.centre_capacity || '0'}</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="action-buttons">
                        <a href="/centres/${centre.centre_id}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i>
                            View
                        </a>
                        ${this.options.currentUser.role === 'admin' || this.options.currentUser.role === 'supervisor' ? `
                        <a href="/centres/${centre.centre_id}/edit" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i>
                            Edit
                        </a>` : ''}
                        <a href="/centres/${centre.centre_id}/assets" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-boxes"></i>
                            Assets
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    getCentreListItemHTML(centre) {
        const statusClass = centre.centre_status || 'unknown';
        const imageUrl = centre.centre_image ?
            `/storage/centres/${centre.centre_image}` :
            '/images/centre-placeholder.jpg';

        return `
            <div class="list-item fade-in"
                 data-name="${(centre.centre_name || '').toLowerCase()}"
                 data-location="${(centre.centre_address || '').toLowerCase()}"
                 data-status="${centre.centre_status || ''}"
                 data-capacity="${centre.centre_capacity || 0}"
                 data-staff="${centre.users_count || 0}"
                 data-trainees="${centre.trainees_count || 0}">

                <div class="list-cell centre-info">
                    <div class="centre-avatar">
                        <img src="${imageUrl}"
                             alt="${centre.centre_name || 'Centre'}"
                             onerror="this.src='/images/centre-placeholder.jpg'">
                    </div>
                    <div class="centre-details">
                        <h4>${centre.centre_name || 'Unnamed Centre'}</h4>
                        <p>${this.truncate(centre.centre_description || 'No description', 60)}</p>
                    </div>
                </div>

                <div class="list-cell">
                    <span class="status-badge status-${statusClass}">
                        ${this.capitalize(statusClass)}
                    </span>
                </div>

                <div class="list-cell">
                    <i class="fas fa-map-marker-alt text-muted"></i>
                    ${this.truncate(centre.centre_address || 'Not specified', 40)}
                </div>

                <div class="list-cell">
                    <span class="metric-value">${centre.users_count || 0}</span>
                </div>

                <div class="list-cell">
                    <span class="metric-value">${centre.trainees_count || 0}</span>
                </div>

                <div class="list-cell">
                    <span class="metric-value">${centre.centre_capacity || 0}</span>
                </div>

                <div class="list-cell actions">
                    <a href="/centres/${centre.centre_id}" class="action-link" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    ${this.options.currentUser.role === 'admin' || this.options.currentUser.role === 'supervisor' ? `
                    <a href="/centres/${centre.centre_id}/edit" class="action-link" title="Edit Centre">
                        <i class="fas fa-edit"></i>
                    </a>` : ''}
                    <a href="/centres/${centre.centre_id}/assets" class="action-link" title="Manage Assets">
                        <i class="fas fa-boxes"></i>
                    </a>
                </div>
            </div>
        `;
    }

    getListHeaderHTML() {
        return `
            <div class="list-header">
                <div class="list-header-cell">Centre</div>
                <div class="list-header-cell">Status</div>
                <div class="list-header-cell">Location</div>
                <div class="list-header-cell">Staff</div>
                <div class="list-header-cell">Trainees</div>
                <div class="list-header-cell">Capacity</div>
                <div class="list-header-cell">Actions</div>
            </div>
        `;
    }

    getEmptyStateHTML() {
        const canCreate = this.options.currentUser.role === 'admin' || this.options.currentUser.role === 'supervisor';

        return `
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>No Centres Found</h3>
                <p>There are no centres matching your current search and filter criteria.</p>
                ${canCreate ? `
                <a href="/centres/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create First Centre
                </a>` : ''}
            </div>
        `;
    }

    switchView(view) {
        if (view === this.currentView) return;

        this.currentView = view;

        // Update view toggle buttons
        document.querySelectorAll('.view-mode-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });

        // Show/hide view containers
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');

        if (view === 'grid') {
            gridView.style.display = 'grid';
            listView.style.display = 'none';
        } else {
            gridView.style.display = 'none';
            listView.style.display = 'block';
        }

        this.renderCentres();
    }

    updatePagination() {
        const totalPages = Math.ceil(this.filteredCentres.length / this.options.itemsPerPage);
        const paginationNumbers = document.getElementById('paginationNumbers');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');

        if (!paginationNumbers || !prevBtn || !nextBtn) return;

        // Update navigation buttons
        prevBtn.disabled = this.currentPage <= 1;
        nextBtn.disabled = this.currentPage >= totalPages;

        // Generate page numbers
        const pageNumbers = this.generatePageNumbers(this.currentPage, totalPages);
        paginationNumbers.innerHTML = pageNumbers.map(page => {
            if (page === '...') {
                return '<span class="pagination-ellipsis">...</span>';
            }
            return `
                <button class="pagination-number ${page === this.currentPage ? 'active' : ''}"
                        data-page="${page}"
                        onclick="centresManager.goToPage(${page})">
                    ${page}
                </button>
            `;
        }).join('');
    }

    generatePageNumbers(current, total) {
        const pages = [];
        const showPages = 5; // Number of page buttons to show

        if (total <= showPages) {
            for (let i = 1; i <= total; i++) {
                pages.push(i);
            }
        } else {
            if (current <= 3) {
                for (let i = 1; i <= 4; i++) {
                    pages.push(i);
                }
                pages.push('...');
                pages.push(total);
            } else if (current >= total - 2) {
                pages.push(1);
                pages.push('...');
                for (let i = total - 3; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                pages.push(1);
                pages.push('...');
                for (let i = current - 1; i <= current + 1; i++) {
                    pages.push(i);
                }
                pages.push('...');
                pages.push(total);
            }
        }

        return pages;
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredCentres.length / this.options.itemsPerPage);
        if (page < 1 || page > totalPages) return;

        this.currentPage = page;
        this.renderCentres();
        this.updatePagination();
        this.updateResultsCount();

        // Scroll to top of results
        document.querySelector('.centres-display-section').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    changePage(direction) {
        const newPage = this.currentPage + direction;
        this.goToPage(newPage);
    }

    updateResultsCount() {
        const resultsCount = document.getElementById('resultsCount');
        const showingCount = document.getElementById('showingCount');

        if (resultsCount) {
            const total = this.filteredCentres.length;
            resultsCount.textContent = `${total} centres found`;
        }

        if (showingCount) {
            const startIndex = (this.currentPage - 1) * this.options.itemsPerPage;
            const endIndex = Math.min(startIndex + this.options.itemsPerPage, this.filteredCentres.length);
            showingCount.textContent = endIndex - startIndex;
        }
    }

    toggleDropdown(trigger) {
        const dropdown = trigger.closest('.dropdown');
        const isOpen = dropdown.classList.contains('show');

        // Close all dropdowns first
        this.closeAllDropdowns();

        // Toggle current dropdown
        if (!isOpen) {
            dropdown.classList.add('show');
        }
    }

    closeAllDropdowns() {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }

    showCentreQuickView(centreId) {
        const centre = this.options.centres.find(c => c.centre_id === centreId);
        if (!centre) return;

        const modal = document.getElementById('quickViewModal');
        const content = document.getElementById('quickViewContent');

        if (!modal || !content) return;

        // Show loading state
        content.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                Loading centre details...
            </div>
        `;

        // Show modal
        $(modal).modal('show');

        // Load content
        setTimeout(() => {
            content.innerHTML = this.getQuickViewHTML(centre);
        }, 800);
    }

    getQuickViewHTML(centre) {
        const statusClass = centre.centre_status || 'unknown';
        const imageUrl = centre.centre_image ?
            `/storage/centres/${centre.centre_image}` :
            '/images/centre-placeholder.jpg';

        return `
            <div class="quick-view-content">
                <div class="row">
                    <div class="col-md-5">
                        <div class="centre-image-container">
                            <img src="${imageUrl}"
                                 alt="${centre.centre_name}"
                                 class="img-fluid rounded"
                                 style="width: 100%; height: 200px; object-fit: cover;">
                        </div>
                        <div class="centre-status-badge mt-3">
                            <span class="status-badge status-${statusClass}">
                                ${this.capitalize(statusClass)}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h4>${centre.centre_name}</h4>
                        <p class="text-muted">${centre.centre_description || 'No description available'}</p>

                        <div class="centre-info-grid">
                            <div class="info-item">
                                <strong><i class="fas fa-map-marker-alt"></i> Address:</strong>
                                <span>${centre.centre_address || 'Not specified'}</span>
                            </div>
                            <div class="info-item">
                                <strong><i class="fas fa-phone"></i> Phone:</strong>
                                <span>${centre.centre_phone || 'Not specified'}</span>
                            </div>
                            <div class="info-item">
                                <strong><i class="fas fa-user-tie"></i> Manager:</strong>
                                <span>${centre.centre_manager || 'Not assigned'}</span>
                            </div>
                            <div class="info-item">
                                <strong><i class="fas fa-expand-arrows-alt"></i> Capacity:</strong>
                                <span>${centre.centre_capacity || 'Not specified'}</span>
                            </div>
                        </div>

                        <div class="centre-stats-row mt-3">
                            <div class="stat-col">
                                <div class="stat-number">${centre.users_count || 0}</div>
                                <div class="stat-label">Staff</div>
                            </div>
                            <div class="stat-col">
                                <div class="stat-number">${centre.trainees_count || 0}</div>
                                <div class="stat-label">Trainees</div>
                            </div>
                            <div class="stat-col">
                                <div class="stat-number">${centre.asset-parents_count || 0}</div>
                                <div class="stat-label">Assets</div>
                            </div>
                        </div>

                        <div class="action-buttons mt-4">
                            <a href="/centres/${centre.centre_id}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Full Details
                            </a>
                            ${this.options.currentUser.role === 'admin' || this.options.currentUser.role === 'supervisor' ? `
                            <a href="/centres/${centre.centre_id}/edit" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>` : ''}
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .centre-info-grid {
                    display: grid;
                    gap: 10px;
                }
                .info-item {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 8px 0;
                    border-bottom: 1px solid #f8f9fa;
                }
                .info-item:last-child {
                    border-bottom: none;
                }
                .info-item strong {
                    min-width: 100px;
                    color: var(--dark-color);
                }
                .info-item i {
                    width: 16px;
                    color: var(--primary-color);
                }
                .centre-stats-row {
                    display: flex;
                    justify-content: space-around;
                    padding: 15px;
                    background: var(--light-color);
                    border-radius: var(--border-radius);
                }
                .stat-col {
                    text-align: center;
                }
                .stat-number {
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: var(--primary-color);
                }
                .stat-label {
                    font-size: 12px;
                    color: #6c757d;
                    text-transform: uppercase;
                }
            </style>
        `;
    }

    addLoadingAnimation() {
        // Add staggered animation to cards
        const cards = document.querySelectorAll('.centre-card, .list-item');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    clearSearch() {
        const searchInput = document.getElementById('centreSearch');
        if (searchInput) {
            searchInput.value = '';
            this.handleSearch('');
        }
    }

    resetFilters() {
        // Reset all filter controls
        document.getElementById('statusFilter').value = '';
        document.getElementById('capacityFilter').value = '';
        document.getElementById('sortFilter').value = 'name_asc';

        // Clear search
        this.clearSearch();

        // Reset active filters
        this.activeFilters = {
            search: '',
            status: '',
            capacity: '',
            sort: 'name_asc'
        };

        this.applyFilters();
    }

    exportCentres() {
        // Prepare data for export
        const exportData = this.filteredCentres.map(centre => ({
            'Centre ID': centre.centre_id,
            'Centre Name': centre.centre_name,
            'Status': centre.centre_status,
            'Address': centre.centre_address,
            'Phone': centre.centre_phone,
            'Manager': centre.centre_manager,
            'Capacity': centre.centre_capacity,
            'Staff Count': centre.users_count || 0,
            'Trainee Count': centre.trainees_count || 0,
            'Asset Count': centre.asset-parents_count || 0
        }));

        // Convert to CSV
        const csv = this.convertToCSV(exportData);

        // Download file
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `centres_export_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        this.showNotification('Centres exported successfully!', 'success');
    }

    convertToCSV(data) {
        if (!data.length) return '';

        const headers = Object.keys(data[0]);
        const csvContent = [
            headers.join(','),
            ...data.map(row =>
                headers.map(header => {
                    const value = row[header] || '';
                    return `"${value.toString().replace(/"/g, '""')}"`;
                }).join(',')
            )
        ].join('\n');

        return csvContent;
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${this.getNotificationIcon(type)}"></i>
            <span>${message}</span>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    // Utility methods
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    truncate(str, length) {
        return str.length > length ? str.substring(0, length) + '...' : str;
    }
}

// Global functions for template use
function toggleView() {
    const currentView = centresManager.currentView;
    const newView = currentView === 'grid' ? 'list' : 'grid';
    centresManager.switchView(newView);

    // Update button text
    const toggleBtn = document.querySelector('[onclick="toggleView()"]');
    const icon = document.getElementById('viewToggleIcon');
    const text = document.getElementById('viewToggleText');

    if (toggleBtn && icon && text) {
        if (newView === 'list') {
            icon.className = 'fas fa-th';
            text.textContent = 'Grid View';
        } else {
            icon.className = 'fas fa-list';
            text.textContent = 'List View';
        }
    }
}

function switchView(view) {
    if (window.centresManager) {
        window.centresManager.switchView(view);
    }
}

function showCentreQuickView(centreId) {
    if (window.centresManager) {
        window.centresManager.showCentreQuickView(centreId);
    }
}

function changePage(direction) {
    if (window.centresManager) {
        window.centresManager.changePage(direction);
    }
}

function clearSearch() {
    if (window.centresManager) {
        window.centresManager.clearSearch();
    }
}

function resetFilters() {
    if (window.centresManager) {
        window.centresManager.resetFilters();
    }
}

function exportCentres() {
    if (window.centresManager) {
        window.centresManager.exportCentres();
    }
}

// Global assignment for access from HTML
window.centresManager = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Will be initialized from the template script
});

// Notification styles
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .notification-success { background: #2ed573; }
    .notification-error { background: #ff4757; }
    .notification-warning { background: #ffa726; }
    .notification-info { background: #3742fa; }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;

// Inject notification styles
if (!document.getElementById('centres-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'centres-notification-styles';
    style.textContent = notificationStyles;
    document.head.appendChild(style);
}
