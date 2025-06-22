@extends('layouts.app')

@section('title', 'Trainee Dashboard')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    
    .stat-card.purple {
        border-left-color: var(--secondary-color);
    }
    
    .stat-card.green {
        border-left-color: var(--success-color);
    }
    
    .stat-card.yellow {
        border-left-color: var(--warning-color);
    }
    
    .stat-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        background: var(--primary-color);
    }
    
    .stat-icon.purple {
        background: var(--secondary-color);
    }
    
    .stat-icon.green {
        background: var(--success-color);
    }
    
    .stat-icon.yellow {
        background: var(--warning-color);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 1rem;
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
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
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
    
    .export-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .trainees-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .trainee-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }
    
    .trainee-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    
    .trainee-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .trainee-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin-right: 1rem;
        position: relative;
    }
    
    .trainee-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .status-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
    }
    
    .status-indicator.active {
        background: var(--success-color);
    }
    
    .status-indicator.inactive {
        background: #6c757d;
    }
    
    .trainee-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
    }
    
    .trainee-id {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .condition-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    
    .condition-badge.autism {
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }
    
    .condition-badge.adhd {
        background: rgba(139, 69, 19, 0.1);
        color: #8b4513;
    }
    
    .condition-badge.dyslexia {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }
    
    .condition-badge.cerebral_palsy {
        background: rgba(34, 197, 94, 0.1);
        color: #059669;
    }
    
    .condition-badge.down_syndrome {
        background: rgba(236, 72, 153, 0.1);
        color: #be185d;
    }
    
    .trainee-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    
    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .detail-label {
        color: #6c757d;
    }
    
    .detail-value {
        font-weight: 500;
        color: var(--dark-color);
    }
    
    .attendance-bar {
        background: #f1f5f9;
        height: 4px;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    
    .attendance-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }
    
    .attendance-fill.high {
        background: var(--success-color);
    }
    
    .attendance-fill.medium {
        background: var(--warning-color);
    }
    
    .attendance-fill.low {
        background: var(--danger-color);
    }
    
    .trainee-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #f0f0f0;
    }
    
    .registration-date {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
        color: #6c757d;
    }
    
    .action-btn:hover {
        background: var(--primary-color);
        color: white;
    }
    
    .pagination-section {
        margin-top: 2rem;
        text-align: center;
    }
    
    .pagination {
        display: inline-flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .pagination button {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: #6c757d;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .pagination button:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .pagination button.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .loading-spinner {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
        margin-top: 2rem;
    }
    
    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #e2e8f0;
        border-top: 2px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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
    
    @media (max-width: 768px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .filters-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .trainees-grid {
            grid-template-columns: 1fr;
        }
        
        .trainee-details {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">Trainee Dashboard</h1>
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="separator">></span>
        <span class="current">Trainees</span>
    </div>
    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
        <a href="{{ route('trainees.create') }}" class="btn btn-primary" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500;">
            <i class="fas fa-plus"></i> Register New Trainee
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value" id="totalTrainees">{{ $stats['total'] ?? 0 }}</div>
        <div class="stat-label">Total Trainees</div>
    </div>
    
    <div class="stat-card green">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value" id="activeTrainees">{{ $stats['active'] ?? 0 }}</div>
        <div class="stat-label">Active Trainees</div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-calendar-plus"></i>
            </div>
        </div>
        <div class="stat-value" id="newTrainees">{{ $stats['new_this_month'] ?? 0 }}</div>
        <div class="stat-label">New (30 days)</div>
    </div>
    
    <div class="stat-card purple">
        <div class="stat-header">
            <div class="stat-icon purple">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="stat-value" id="avgAttendance">{{ $stats['avg_attendance'] ?? 0 }}%</div>
        <div class="stat-label">Avg. Attendance</div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-grid">
    <div class="chart-card">
        <h3 class="chart-title">Trainees by Condition</h3>
        <canvas id="conditionChart" width="400" height="250"></canvas>
    </div>
    
    <div class="chart-card">
        <h3 class="chart-title">Centre Distribution</h3>
        <canvas id="centreChart" width="400" height="250"></canvas>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <div class="filters-grid">
        <div class="search-input">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search trainees..." class="form-control" style="border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>
        
        <select id="centreFilter" class="filter-select">
            <option value="">All Centres</option>
            @foreach($centres ?? [] as $centre)
                <option value="{{ $centre->centre_name }}">{{ $centre->centre_name }}</option>
            @endforeach
        </select>
        
        <select id="conditionFilter" class="filter-select">
            <option value="">All Conditions</option>
            <option value="autism">Autism Spectrum Disorder</option>
            <option value="adhd">ADHD</option>
            <option value="dyslexia">Dyslexia</option>
            <option value="cerebral_palsy">Cerebral Palsy</option>
            <option value="down_syndrome">Down Syndrome</option>
        </select>
        
        <select id="dateRangeFilter" class="filter-select">
            <option value="all">All Time</option>
            <option value="7days">Last 7 Days</option>
            <option value="30days" selected>Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
        </select>
        
        <select id="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        
        <button class="export-btn" onclick="exportData()">
            <i class="fas fa-download"></i>
            Export
        </button>
    </div>
</div>

<!-- Trainees Grid -->
<div id="traineesContainer">
    <div class="trainees-grid" id="traineesGrid">
        <!-- Trainees will be loaded here -->
    </div>
    
    <div class="loading-spinner" id="loadingSpinner" style="display: none;">
        <div class="spinner"></div>
        Loading more trainees...
    </div>
    
    <div class="pagination-section" id="paginationSection" style="display: none;">
        <div class="pagination">
            <button id="prevPage" onclick="changePage(-1)">Previous</button>
            <span id="pageNumbers"></span>
            <button id="nextPage" onclick="changePage(1)">Next</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Global variables
    let trainees = [];
    let filteredTrainees = [];
    let currentPage = 1;
    let itemsPerPage = 12;
    let isLoading = false;
    
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        loadTrainees();
        setupFilters();
        initializeCharts();
    });
    
    // Load trainees data
    async function loadTrainees() {
        try {
            isLoading = true;
            document.getElementById('loadingSpinner').style.display = 'flex';
            
            // Simulate API call - replace with actual endpoint
            const response = await fetch('/api/trainees');
            if (response.ok) {
                trainees = await response.json();
            } else {
                // Fallback to mock data if API not available
                trainees = generateMockTrainees();
            }
            
            filteredTrainees = [...trainees];
            renderTrainees();
            updateStats();
            updateCharts();
            
        } catch (error) {
            console.error('Error loading trainees:', error);
            // Use mock data as fallback
            trainees = generateMockTrainees();
            filteredTrainees = [...trainees];
            renderTrainees();
            updateStats();
            updateCharts();
        } finally {
            isLoading = false;
            document.getElementById('loadingSpinner').style.display = 'none';
        }
    }
    
    // Generate mock trainees data
    function generateMockTrainees() {
        const conditions = ['autism', 'adhd', 'dyslexia', 'cerebral_palsy', 'down_syndrome'];
        const centres = ['IIUM Gombak', 'IIUM Kuantan', 'IIUM Pagoh'];
        const firstNames = ['Ahmad', 'Siti', 'Muhammad', 'Nur', 'Ali', 'Fatimah', 'Hassan', 'Aminah'];
        const lastNames = ['Abdullah', 'Rahman', 'Hassan', 'Ibrahim', 'Ismail', 'Omar', 'Yusof', 'Ahmad'];
        
        return Array.from({ length: 48 }, (_, i) => ({
            id: i + 1,
            first_name: firstNames[Math.floor(Math.random() * firstNames.length)],
            last_name: lastNames[Math.floor(Math.random() * lastNames.length)],
            condition: conditions[Math.floor(Math.random() * conditions.length)],
            centre: centres[Math.floor(Math.random() * centres.length)],
            registered_date: new Date(2024, Math.floor(Math.random() * 12), Math.floor(Math.random() * 28) + 1),
            last_accessed: new Date(2025, 5, Math.floor(Math.random() * 21) + 1),
            attendance: Math.floor(Math.random() * 40) + 60,
            status: Math.random() > 0.2 ? 'active' : 'inactive',
            avatar: null
        }));
    }
    
    // Render trainees
    function renderTrainees() {
        const grid = document.getElementById('traineesGrid');
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const currentTrainees = filteredTrainees.slice(startIndex, endIndex);
        
        if (currentTrainees.length === 0) {
            grid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-users"></i>
                    <h3>No trainees found</h3>
                    <p>Try adjusting your filters or search terms</p>
                    <button onclick="clearFilters()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer;">Clear Filters</button>
                </div>
            `;
            document.getElementById('paginationSection').style.display = 'none';
            return;
        }
        
        grid.innerHTML = currentTrainees.map(trainee => createTraineeCard(trainee)).join('');
        updatePagination();
    }
    
    // Create trainee card HTML
    function createTraineeCard(trainee) {
        const conditionNames = {
            'autism': 'Autism Spectrum Disorder',
            'adhd': 'ADHD',
            'dyslexia': 'Dyslexia',
            'cerebral_palsy': 'Cerebral Palsy',
            'down_syndrome': 'Down Syndrome'
        };
        
        const attendanceClass = trainee.attendance >= 80 ? 'high' : trainee.attendance >= 60 ? 'medium' : 'low';
        const avatarUrl = trainee.avatar ? `/storage/trainee_avatars/${trainee.avatar}` : `https://ui-avatars.com/api/?name=${trainee.first_name}+${trainee.last_name}&background=32bdea&color=fff`;
        
        return `
            <div class="trainee-card">
                <div class="trainee-header">
                    <div class="trainee-avatar">
                        <img src="${avatarUrl}" alt="${trainee.first_name} ${trainee.last_name}" onerror="this.src='https://ui-avatars.com/api/?name=${trainee.first_name}+${trainee.last_name}&background=32bdea&color=fff'">
                        <div class="status-indicator ${trainee.status}"></div>
                    </div>
                    <div class="trainee-info">
                        <h4>${trainee.first_name} ${trainee.last_name}</h4>
                        <div class="trainee-id">ID: ${String(trainee.id).padStart(5, '0')}</div>
                    </div>
                </div>
                
                <div class="condition-badge ${trainee.condition}">
                    ${conditionNames[trainee.condition]}
                </div>
                
                <div class="trainee-details">
                    <div class="detail-item">
                        <span class="detail-label">Centre:</span>
                        <span class="detail-value">${trainee.centre}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" style="text-transform: capitalize;">${trainee.status}</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.25rem;">
                        <span class="detail-label">Attendance:</span>
                        <span class="detail-value" style="color: ${attendanceClass === 'high' ? 'var(--success-color)' : attendanceClass === 'medium' ? 'var(--warning-color)' : 'var(--danger-color)'};">${trainee.attendance}%</span>
                    </div>
                    <div class="attendance-bar">
                        <div class="attendance-fill ${attendanceClass}" style="width: ${trainee.attendance}%;"></div>
                    </div>
                </div>
                
                <div class="trainee-actions">
                    <span class="registration-date">Registered ${trainee.registered_date.toLocaleDateString()}</span>
                    <div class="action-buttons">
                        <button class="action-btn" onclick="viewTrainee(${trainee.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn" onclick="editTrainee(${trainee.id})" title="Edit Trainee">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Setup filters
    function setupFilters() {
        const searchInput = document.getElementById('searchInput');
        const centreFilter = document.getElementById('centreFilter');
        const conditionFilter = document.getElementById('conditionFilter');
        const dateRangeFilter = document.getElementById('dateRangeFilter');
        const statusFilter = document.getElementById('statusFilter');
        
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });
        
        [centreFilter, conditionFilter, dateRangeFilter, statusFilter].forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });
    }
    
    // Apply filters
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const centreFilter = document.getElementById('centreFilter').value;
        const conditionFilter = document.getElementById('conditionFilter').value;
        const dateRangeFilter = document.getElementById('dateRangeFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        
        filteredTrainees = trainees.filter(trainee => {
            const matchesSearch = searchTerm === '' || 
                trainee.first_name.toLowerCase().includes(searchTerm) ||
                trainee.last_name.toLowerCase().includes(searchTerm);
            
            const matchesCentre = centreFilter === '' || trainee.centre === centreFilter;
            const matchesCondition = conditionFilter === '' || trainee.condition === conditionFilter;
            const matchesStatus = statusFilter === '' || trainee.status === statusFilter;
            
            let matchesDate = true;
            if (dateRangeFilter !== 'all') {
                const daysAgo = dateRangeFilter === '7days' ? 7 : dateRangeFilter === '30days' ? 30 : 90;
                const cutoffDate = new Date();
                cutoffDate.setDate(cutoffDate.getDate() - daysAgo);
                matchesDate = trainee.registered_date >= cutoffDate;
            }
            
            return matchesSearch && matchesCentre && matchesCondition && matchesStatus && matchesDate;
        });
        
        currentPage = 1;
        renderTrainees();
        updateStats();
    }
    
    // Clear all filters
    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('centreFilter').value = '';
        document.getElementById('conditionFilter').value = '';
        document.getElementById('dateRangeFilter').value = '30days';
        document.getElementById('statusFilter').value = '';
        
        filteredTrainees = [...trainees];
        currentPage = 1;
        renderTrainees();
        updateStats();
    }
    
    // Update pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredTrainees.length / itemsPerPage);
        const paginationSection = document.getElementById('paginationSection');
        
        if (totalPages <= 1) {
            paginationSection.style.display = 'none';
            return;
        }
        
        paginationSection.style.display = 'block';
        
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pageNumbers = document.getElementById('pageNumbers');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        
        // Generate page numbers
        let pageHTML = '';
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            pageHTML += `<button onclick="goToPage(${i})" ${i === currentPage ? 'class="active"' : ''}>${i}</button>`;
        }
        
        pageNumbers.innerHTML = pageHTML;
    }
    
    // Change page
    function changePage(direction) {
        const totalPages = Math.ceil(filteredTrainees.length / itemsPerPage);
        const newPage = currentPage + direction;
        
        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            renderTrainees();
        }
    }
    
    // Go to specific page
    function goToPage(page) {
        currentPage = page;
        renderTrainees();
    }
    
    // Update statistics
    function updateStats() {
        const stats = {
            total: filteredTrainees.length,
            active: filteredTrainees.filter(t => t.status === 'active').length,
            new_this_month: filteredTrainees.filter(t => {
                const cutoff = new Date();
                cutoff.setDate(cutoff.getDate() - 30);
                return t.registered_date >= cutoff;
            }).length,
            avg_attendance: Math.round(filteredTrainees.reduce((acc, t) => acc + t.attendance, 0) / (filteredTrainees.length || 1))
        };
        
        document.getElementById('totalTrainees').textContent = stats.total;
        document.getElementById('activeTrainees').textContent = stats.active;
        document.getElementById('newTrainees').textContent = stats.new_this_month;
        document.getElementById('avgAttendance').textContent = stats.avg_attendance + '%';
    }
    
    // Initialize charts
    function initializeCharts() {
        // Condition distribution chart
        const conditionCtx = document.getElementById('conditionChart').getContext('2d');
        new Chart(conditionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Autism', 'ADHD', 'Dyslexia', 'Cerebral Palsy', 'Down Syndrome'],
                datasets: [{
                    data: [15, 12, 8, 7, 6],
                    backgroundColor: ['#32bdea', '#c850c0', '#ffa502', '#2ed573', '#ff4757']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Centre distribution chart
        const centreCtx = document.getElementById('centreChart').getContext('2d');
        new Chart(centreCtx, {
            type: 'bar',
            data: {
                labels: ['Gombak', 'Kuantan', 'Pagoh'],
                datasets: [{
                    data: [18, 15, 15],
                    backgroundColor: '#32bdea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Update charts based on filtered data
    function updateCharts() {
        // This would update the charts with filtered data
        // Implementation depends on Chart.js instance management
    }
    
    // Action functions
    function viewTrainee(id) {
        window.location.href = `/trainees/${id}`;
    }
    
    function editTrainee(id) {
        window.location.href = `/trainees/${id}/edit`;
    }
    
    function exportData() {
        // Implement data export functionality
        const csvContent = "data:text/csv;charset=utf-8," 
            + "ID,Name,Centre,Condition,Status,Attendance\n"
            + filteredTrainees.map(t => 
                `${t.id},"${t.first_name} ${t.last_name}",${t.centre},${t.condition},${t.status},${t.attendance}%`
            ).join("\n");
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "trainees_export.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection