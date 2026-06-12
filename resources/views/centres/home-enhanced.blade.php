@extends('layouts.app')

@section('title', 'Centre Management - CREAMS')

@section('content')
    <div class="enhanced-centres-container">
        {{-- Enhanced Header Section --}}
        <div class="centres-header-section">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="page-title">
                        <i class="fas fa-building gradient-icon"></i>
                        Centre Management
                    </h1>
                    <p class="page-subtitle">Comprehensive management of rehabilitation centres across the network</p>
                    <div class="page-breadcrumb">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                        <i class="fas fa-chevron-right"></i>
                        <span class="current">Centre Management</span>
                    </div>
                </div>
                <div class="header-actions">
                    @if (in_array(session('role'), ['admin', 'supervisor']))
                        <div class="action-group">
                            <button type="button" class="btn btn-secondary" onclick="toggleView()">
                                <i class="fas fa-list" id="viewToggleIcon"></i>
                                <span id="viewToggleText">List View</span>
                            </button>
                            <button type="button" class="btn btn-info" onclick="exportCentres()">
                                <i class="fas fa-download"></i>
                                Export
                            </button>
                            <a href="{{ route('centres.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Add New Centre
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Enhanced Statistics Overview --}}
            <div class="statistics-overview">
                <div class="stats-grid">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $centres->count() }}</div>
                            <div class="stat-label">Total Centres</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+2 this month</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stat-success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $centres->where('centre_status', 'active')->count() }}</div>
                            <div class="stat-label">Active Centres</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>{{ number_format(($centres->where('centre_status', 'active')->count() / max($centres->count(), 1)) * 100, 1) }}%
                                    active</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stat-info">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $centres->sum('users_count') ?? 0 }}</div>
                            <div class="stat-label">Total Staff</div>
                            <div class="stat-trend">
                                <i class="fas fa-users"></i>
                                <span>Across all centres</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $centres->sum('trainees_count') ?? 0 }}</div>
                            <div class="stat-label">Total Trainees</div>
                            <div class="stat-trend">
                                <i class="fas fa-chart-line"></i>
                                <span>Currently enrolled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enhanced Search and Filter Section --}}
        <div class="search-filter-section">
            <div class="search-container">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="centreSearch" class="search-input"
                        placeholder="Search centres by name, location, manager, or facilities..." autocomplete="off">
                    <div class="search-clear" onclick="clearSearch()" style="display: none;">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
                <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                    <!-- Dynamic search suggestions will appear here -->
                </div>
            </div>

            <div class="filter-controls">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Capacity</label>
                    <select class="filter-select" id="capacityFilter">
                        <option value="">All Capacities</option>
                        <option value="small">Small (1-50)</option>
                        <option value="medium">Medium (51-100)</option>
                        <option value="large">Large (101-200)</option>
                        <option value="xlarge">Very Large (200+)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Sort By</label>
                    <select class="filter-select" id="sortFilter">
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                        <option value="staff_desc">Most Staff</option>
                        <option value="trainees_desc">Most Trainees</option>
                        <option value="capacity_desc">Largest Capacity</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>

                <button class="filter-reset" onclick="resetFilters()">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
            </div>
        </div>

        {{-- Enhanced Centres Grid/List View --}}
        <div class="centres-display-section">
            <div class="display-header">
                <div class="results-info">
                    <span id="resultsCount">{{ $centres->count() }} centres found</span>
                    <div class="view-mode-toggle">
                        <button class="view-mode-btn active" data-view="grid" onclick="switchView('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-mode-btn" data-view="list" onclick="switchView('list')">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Grid View --}}
            <div id="gridView" class="centres-grid">
                @forelse($centres as $centre)
                    <div class="centre-card" data-name="{{ strtolower($centre->centre_name) }}"
                        data-location="{{ strtolower($centre->centre_address ?? '') }}"
                        data-status="{{ $centre->centre_status }}" data-capacity="{{ $centre->centre_capacity ?? 0 }}"
                        data-staff="{{ $centre->users_count ?? 0 }}" data-trainees="{{ $centre->trainees_count ?? 0 }}">

                        <div class="card-header">
                            <div class="centre-status">
                                <span class="status-indicator status-{{ $centre->centre_status }}"></span>
                                <span class="status-text">{{ ucfirst($centre->centre_status ?? 'unknown') }}</span>
                            </div>
                            <div class="card-actions">
                                <button class="action-btn" onclick="showCentreQuickView('{{ $centre->centre_id }}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="dropdown">
                                    <button class="action-btn dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('centres.show', $centre->centre_id) }}">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </a>
                                        @if (in_array(session('role'), ['admin', 'supervisor']))
                                            <a class="dropdown-item"
                                                href="{{ route('centres.edit', $centre->centre_id) }}">
                                                <i class="fas fa-edit"></i> Edit Centre
                                            </a>
                                        @endif
                                        <a class="dropdown-item"
                                            href="{{ route('centres.assets', $centre->centre_id) }}">
                                            <i class="fas fa-boxes"></i> Manage Assets
                                        </a>
                                        <a class="dropdown-item"
                                            href="{{ route('centres.attendance.index') }}?centre={{ $centre->centre_id }}">
                                            <i class="fas fa-clock"></i> Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-image">
                            <img src="{{ $centre->centre_image ? asset('storage/centres/' . $centre->centre_image) : asset('images/centre-placeholder.jpg') }}"
                                alt="{{ $centre->centre_name }}"
                                onerror="this.src='{{ asset('images/centre-placeholder.jpg') }}'">
                            <div class="image-overlay">
                                <div class="overlay-content">
                                    <h3 class="centre-name">{{ $centre->centre_name }}</h3>
                                    <p class="centre-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ Str::limit($centre->centre_address ?? 'Location not specified', 50) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="card-content">
                            <div class="centre-info">
                                <h4 class="centre-title">{{ $centre->centre_name }}</h4>
                                <p class="centre-description">
                                    {{ Str::limit($centre->centre_description ?? 'No description available', 100) }}
                                </p>
                            </div>

                            <div class="centre-stats">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-details">
                                        <span class="stat-value">{{ $centre->users_count ?? 0 }}</span>
                                        <span class="stat-label">Staff</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="stat-details">
                                        <span class="stat-value">{{ $centre->trainees_count ?? 0 }}</span>
                                        <span class="stat-label">Trainees</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="stat-details">
                                        <span class="stat-value">{{ $centre->assets_count ?? 0 }}</span>
                                        <span class="stat-label">Assets</span>
                                    </div>
                                </div>
                            </div>

                            <div class="centre-details">
                                <div class="detail-item">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $centre->centre_phone ?? 'No phone' }}</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span>{{ $centre->centre_manager ?? 'No manager assigned' }}</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                    <span>Capacity: {{ $centre->centre_capacity ?? '0' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="action-buttons">
                                <a href="{{ route('centres.show', $centre->centre_id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                    View
                                </a>
                                @if (in_array(session('role'), ['admin', 'supervisor']))
                                    <a href="{{ route('centres.edit', $centre->centre_id) }}"
                                        class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                @endif
                                <a href="{{ route('centres.assets', $centre->centre_id) }}"
                                    class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-boxes"></i>
                                    Assets
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>No Centres Found</h3>
                        <p>There are no centres matching your current search and filter criteria.</p>
                        @if (in_array(session('role'), ['admin', 'supervisor']))
                            <a href="{{ route('centres.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Create First Centre
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- List View --}}
            <div id="listView" class="centres-list" style="display: none;">
                <div class="list-header">
                    <div class="list-header-cell">Centre</div>
                    <div class="list-header-cell">Status</div>
                    <div class="list-header-cell">Location</div>
                    <div class="list-header-cell">Staff</div>
                    <div class="list-header-cell">Trainees</div>
                    <div class="list-header-cell">Capacity</div>
                    <div class="list-header-cell">Actions</div>
                </div>

                @forelse($centres as $centre)
                    <div class="list-item" data-name="{{ strtolower($centre->centre_name) }}"
                        data-location="{{ strtolower($centre->centre_address ?? '') }}"
                        data-status="{{ $centre->centre_status }}" data-capacity="{{ $centre->centre_capacity ?? 0 }}"
                        data-staff="{{ $centre->users_count ?? 0 }}" data-trainees="{{ $centre->trainees_count ?? 0 }}">

                        <div class="list-cell centre-info">
                            <div class="centre-avatar">
                                <img src="{{ $centre->centre_image ? asset('storage/centres/' . $centre->centre_image) : asset('images/centre-placeholder.jpg') }}"
                                    alt="{{ $centre->centre_name }}">
                            </div>
                            <div class="centre-details">
                                <h4>{{ $centre->centre_name }}</h4>
                                <p>{{ Str::limit($centre->centre_description ?? 'No description', 60) }}</p>
                            </div>
                        </div>

                        <div class="list-cell">
                            <span class="status-badge status-{{ $centre->centre_status }}">
                                {{ ucfirst($centre->centre_status ?? 'unknown') }}
                            </span>
                        </div>

                        <div class="list-cell">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            {{ Str::limit($centre->centre_address ?? 'Not specified', 40) }}
                        </div>

                        <div class="list-cell">
                            <span class="metric-value">{{ $centre->users_count ?? 0 }}</span>
                        </div>

                        <div class="list-cell">
                            <span class="metric-value">{{ $centre->trainees_count ?? 0 }}</span>
                        </div>

                        <div class="list-cell">
                            <span class="metric-value">{{ $centre->centre_capacity ?? 0 }}</span>
                        </div>

                        <div class="list-cell actions">
                            <a href="{{ route('centres.show', $centre->centre_id) }}" class="action-link"
                                title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if (in_array(session('role'), ['admin', 'supervisor']))
                                <a href="{{ route('centres.edit', $centre->centre_id) }}" class="action-link"
                                    title="Edit Centre">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            <a href="{{ route('centres.assets', $centre->centre_id) }}" class="action-link"
                                title="Manage Assets">
                                <i class="fas fa-boxes"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="list-empty">
                        <p>No centres found matching your criteria.</p>
                    </div>
                @endforelse
            </div>

            {{-- Enhanced Pagination --}}
            <div class="pagination-section">
                <div class="pagination-info">
                    Showing <span id="showingCount">{{ $centres->count() }}</span> of {{ $centres->count() }} centres
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="prevPage" onclick="changePage(-1)" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </button>
                    <div class="pagination-numbers" id="paginationNumbers">
                        <button class="pagination-number active" data-page="1">1</button>
                    </div>
                    <button class="pagination-btn" id="nextPage" onclick="changePage(1)" disabled>
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick View Modal --}}
    <div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Centre Quick View</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="quickViewContent">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        Loading centre details...
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/centres-enhanced.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/centres-enhanced.js') }}"></script>
    <script>
        // Initialize enhanced centres management
        document.addEventListener('DOMContentLoaded', function() {
            const centresManager = new EnhancedCentresManager({
                itemsPerPage: 25,
                centres: @json($centres),
                currentUser: {
                    role: '{{ session('role') }}',
                    id: {{ session('id') ?? 'null' }}
                }
            });
        });
    </script>
@endsection
