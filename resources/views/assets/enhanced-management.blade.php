@extends('layouts.app')

@section('title', 'Assets Management')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    
    .stat-header {
        display: flex;
        justify-content: space-between;
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
        color: white;
        font-size: 1.2rem;
    }
    
    .stat-icon.value {
        background: linear-gradient(135deg, var(--success-color), #17a2b8);
    }
    
    .stat-icon.low-stock {
        background: linear-gradient(135deg, var(--danger-color), #ff6b6b);
    }
    
    .stat-icon.in-use {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }
    
    .stat-icon.repair {
        background: linear-gradient(135deg, var(--warning-color), #ffa726);
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark-color);
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .tabs-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .tabs-header {
        display: flex;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .tab-button {
        padding: 1rem 1.5rem;
        background: transparent;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
    }
    
    .tab-button.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }
    
    .tab-button:hover {
        background: #f8fafc;
    }
    
    .tab-content {
        display: none;
        padding: 2rem;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .category-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.3s ease;
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
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .category-emoji {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .category-count {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-color);
        background: rgba(50, 189, 234, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .category-title {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    
    .category-subtitle {
        font-size: 0.85rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
    
    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        color: #6c757d;
        text-decoration: none;
    }
    
    .action-btn:hover {
        background: #f8fafc;
        color: #374151;
        text-decoration: none;
    }
    
    .action-btn.primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: var(--primary-color);
    }
    
    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: white;
    }
    
    .assets-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .table-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th {
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .table tr:hover {
        background: #f8fafc;
    }
    
    .asset-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .asset-icon {
        width: 40px;
        height: 40px;
        background: #f8fafc;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }
    
    .asset-details h4 {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .asset-sku {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .category-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: rgba(50, 189, 234, 0.1);
        color: var(--primary-color);
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .quantity-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .quantity-number {
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .stock-bar {
        width: 60px;
        height: 4px;
        background: #f1f5f9;
        border-radius: 2px;
        overflow: hidden;
    }
    
    .stock-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }
    
    .stock-fill.high {
        background: var(--success-color);
    }
    
    .stock-fill.medium {
        background: var(--warning-color);
    }
    
    .stock-fill.low {
        background: var(--danger-color);
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge.available {
        background: rgba(46, 213, 115, 0.1);
        color: #2ed573;
    }
    
    .status-badge.in-use {
        background: rgba(50, 189, 234, 0.1);
        color: #32bdea;
    }
    
    .status-badge.low-stock {
        background: rgba(255, 71, 87, 0.1);
        color: #ff4757;
    }
    
    .condition-text {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .condition-text.excellent {
        color: var(--success-color);
    }
    
    .condition-text.good {
        color: var(--primary-color);
    }
    
    .condition-text.fair {
        color: var(--warning-color);
    }
    
    .condition-text.repair {
        color: var(--danger-color);
    }
    
    .value-text {
        font-weight: 500;
        color: var(--dark-color);
    }
    
    .table-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .table-action-btn {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
        color: #6c757d;
    }
    
    .table-action-btn:hover {
        background: var(--primary-color);
        color: white;
    }
    
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    
    .pagination-info {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .pagination-controls {
        display: flex;
        gap: 0.5rem;
    }
    
    .pagination-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.85rem;
    }
    
    .pagination-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .pagination-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
        .filters-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .categories-grid {
            grid-template-columns: 1fr;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">Assets Management</h1>
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="separator">></span>
        <span class="current">Assets</span>
    </div>
    <p style="color: #6c757d; margin-top: 0.5rem;">Manage inventory and track asset allocation</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-value" id="totalValue">$0</div>
                <div class="stat-label">Total Value</div>
            </div>
            <div class="stat-icon value">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-value" id="lowStockItems">0</div>
                <div class="stat-label">Low Stock Items</div>
            </div>
            <div class="stat-icon low-stock">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-value" id="inUseItems">0</div>
                <div class="stat-label">In Use</div>
            </div>
            <div class="stat-icon in-use">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-value" id="needsRepair">0</div>
                <div class="stat-label">Needs Repair</div>
            </div>
            <div class="stat-icon repair">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Container -->
<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-button active" onclick="switchTab('assets')">All Assets</button>
        <button class="tab-button" onclick="switchTab('categories')">Categories</button>
    </div>
    
    <!-- Assets Tab -->
    <div class="tab-content active" id="assetsTab">
        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-grid">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search assets by name or SKU...">
                </div>
                
                <select id="categoryFilter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="Therapy Equipment">Therapy Equipment</option>
                    <option value="Educational Materials">Educational Materials</option>
                    <option value="Sensory Tools">Sensory Tools</option>
                    <option value="Communication Devices">Communication Devices</option>
                    <option value="Mobility Aids">Mobility Aids</option>
                    <option value="Medical Supplies">Medical Supplies</option>
                </select>
                
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="In Use">In Use</option>
                    <option value="Low Stock">Low Stock</option>
                </select>
                
                <select id="conditionFilter" class="filter-select">
                    <option value="">All Conditions</option>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Needs Repair">Needs Repair</option>
                </select>
                
                <div class="action-buttons">
                    <a href="#" class="action-btn">
                        <i class="fas fa-upload"></i>
                        Import
                    </a>
                    <a href="#" class="action-btn">
                        <i class="fas fa-download"></i>
                        Export
                    </a>
                    <a href="#" class="action-btn primary">
                        <i class="fas fa-plus"></i>
                        Add Asset
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Assets Table -->
        <div class="assets-table">
            <div class="table-header">Assets Inventory</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Condition</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assetsTableBody">
                        <!-- Assets will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <div class="pagination">
                <div class="pagination-info">
                    Showing <span id="startItem">1</span> to <span id="endItem">10</span> of <span id="totalItems">0</span> results
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="prevPage" onclick="changePage(-1)">Previous</button>
                    <span id="pageNumbers"></span>
                    <button class="pagination-btn" id="nextPage" onclick="changePage(1)">Next</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categories Tab -->
    <div class="tab-content" id="categoriesTab">
        <div class="categories-grid" id="categoriesGrid">
            <!-- Categories will be loaded here -->
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Global variables
    let assets = [];
    let categories = [];
    let filteredAssets = [];
    let currentPage = 1;
    let itemsPerPage = 10;
    let activeTab = 'assets';
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadData();
        setupFilters();
    });
    
    // Load data
    async function loadData() {
        try {
            // Try to load from API
            const [assetsResponse, categoriesResponse] = await Promise.all([
                fetch('/api/assets'),
                fetch('/api/asset-categories')
            ]);
            
            if (assetsResponse.ok && categoriesResponse.ok) {
                assets = await assetsResponse.json();
                categories = await categoriesResponse.json();
            } else {
                // Fallback to mock data
                const mockData = generateMockData();
                assets = mockData.assets;
                categories = mockData.categories;
            }
        } catch (error) {
            console.error('Error loading data:', error);
            // Use mock data as fallback
            const mockData = generateMockData();
            assets = mockData.assets;
            categories = mockData.categories;
        }
        
        filteredAssets = [...assets];
        renderAssets();
        renderCategories();
        updateStats();
    }
    
    // Generate mock data
    function generateMockData() {
        const assetCategories = [
            { id: 1, name: 'Therapy Equipment', emoji: '🏃', count: 45, color: 'blue' },
            { id: 2, name: 'Educational Materials', emoji: '📚', count: 78, color: 'green' },
            { id: 3, name: 'Sensory Tools', emoji: '🎨', count: 34, color: 'purple' },
            { id: 4, name: 'Communication Devices', emoji: '💬', count: 23, color: 'yellow' },
            { id: 5, name: 'Mobility Aids', emoji: '♿', count: 19, color: 'red' },
            { id: 6, name: 'Medical Supplies', emoji: '🏥', count: 56, color: 'teal' }
        ];
        
        const assetItems = Array.from({ length: 120 }, (_, i) => ({
            id: i + 1,
            name: [
                'Weighted Blanket',
                'Communication Board',
                'Balance Ball',
                'Sensory Swing',
                'Picture Cards Set',
                'Therapy Putty',
                'Walking Frame',
                'AAC Device',
                'Fidget Tools Kit',
                'Visual Timer'
            ][i % 10],
            category: assetCategories[Math.floor(Math.random() * assetCategories.length)].name,
            sku: `SKU-${String(i + 1000).padStart(4, '0')}`,
            quantity: Math.floor(Math.random() * 50) + 1,
            min_quantity: 5,
            location: ['Store Room A', 'Store Room B', 'Therapy Room 1', 'Therapy Room 2'][Math.floor(Math.random() * 4)],
            condition: ['Excellent', 'Good', 'Fair', 'Needs Repair'][Math.floor(Math.random() * 4)],
            status: Math.random() > 0.8 ? 'Low Stock' : Math.random() > 0.5 ? 'Available' : 'In Use',
            last_checked: new Date(2025, 5, Math.floor(Math.random() * 21) + 1),
            value: Math.floor(Math.random() * 500) + 50,
            assigned_to: Math.random() > 0.5 ? ['Centre A', 'Centre B', 'Centre C'][Math.floor(Math.random() * 3)] : null
        }));
        
        return { categories: assetCategories, assets: assetItems };
    }
    
    // Setup filters
    function setupFilters() {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const conditionFilter = document.getElementById('conditionFilter');
        
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });
        
        [categoryFilter, statusFilter, conditionFilter].forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });
    }
    
    // Apply filters
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const categoryFilter = document.getElementById('categoryFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const conditionFilter = document.getElementById('conditionFilter').value;
        
        filteredAssets = assets.filter(asset => {
            const matchesSearch = searchTerm === '' || 
                asset.name.toLowerCase().includes(searchTerm) ||
                asset.sku.toLowerCase().includes(searchTerm);
            
            const matchesCategory = categoryFilter === '' || asset.category === categoryFilter;
            const matchesStatus = statusFilter === '' || asset.status === statusFilter;
            const matchesCondition = conditionFilter === '' || asset.condition === conditionFilter;
            
            return matchesSearch && matchesCategory && matchesStatus && matchesCondition;
        });
        
        currentPage = 1;
        renderAssets();
        updateStats();
    }
    
    // Switch tabs
    function switchTab(tab) {
        activeTab = tab;
        
        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
        
        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(tab + 'Tab').classList.add('active');
    }
    
    // Render assets
    function renderAssets() {
        const tbody = document.getElementById('assetsTableBody');
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const currentAssets = filteredAssets.slice(startIndex, endIndex);
        
        if (currentAssets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-boxes"></i>
                            <h3>No assets found</h3>
                            <p>Try adjusting your filters</p>
                        </div>
                    </td>
                </tr>
            `;
            updatePagination(0);
            return;
        }
        
        tbody.innerHTML = currentAssets.map(asset => createAssetRow(asset)).join('');
        updatePagination(filteredAssets.length);
    }
    
    // Create asset row
    function createAssetRow(asset) {
        const stockPercentage = (asset.quantity / (asset.min_quantity * 3)) * 100;
        const stockClass = stockPercentage < 33 ? 'low' : stockPercentage < 66 ? 'medium' : 'high';
        
        return `
            <tr>
                <td>
                    <div class="asset-info">
                        <div class="asset-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="asset-details">
                            <h4>${asset.name}</h4>
                            <div class="asset-sku">${asset.sku}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="category-badge">${asset.category}</span>
                </td>
                <td>
                    <div class="quantity-info">
                        <span class="quantity-number">${asset.quantity}</span>
                        <div class="stock-bar">
                            <div class="stock-fill ${stockClass}" style="width: ${Math.min(stockPercentage, 100)}%;"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="status-badge ${asset.status.toLowerCase().replace(/\s+/g, '-')}">${asset.status}</span>
                </td>
                <td>${asset.location}</td>
                <td>
                    <span class="condition-text ${asset.condition.toLowerCase().replace(/\s+/g, '-')}">${asset.condition}</span>
                </td>
                <td>
                    <span class="value-text">$${asset.value}</span>
                </td>
                <td>
                    <div class="table-actions">
                        <button class="table-action-btn" onclick="viewAsset(${asset.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="table-action-btn" onclick="editAsset(${asset.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="table-action-btn" onclick="deleteAsset(${asset.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    // Render categories
    function renderCategories() {
        const grid = document.getElementById('categoriesGrid');
        
        grid.innerHTML = categories.map(category => `
            <div class="category-card" onclick="filterByCategory('${category.name}')">
                <div class="category-header">
                    <div class="category-emoji">${category.emoji}</div>
                    <div class="category-count">${category.count}</div>
                </div>
                <h3 class="category-title">${category.name}</h3>
                <div class="category-subtitle">
                    <i class="fas fa-box"></i>
                    ${Math.floor(category.count * 0.7)} available
                </div>
            </div>
        `).join('');
    }
    
    // Update statistics
    function updateStats() {
        const stats = {
            totalValue: filteredAssets.reduce((sum, asset) => sum + (asset.value * asset.quantity), 0),
            lowStockItems: filteredAssets.filter(asset => asset.quantity <= asset.min_quantity).length,
            inUseItems: filteredAssets.filter(asset => asset.status === 'In Use').length,
            needsRepair: filteredAssets.filter(asset => asset.condition === 'Needs Repair').length
        };
        
        document.getElementById('totalValue').textContent = '$' + stats.totalValue.toLocaleString();
        document.getElementById('lowStockItems').textContent = stats.lowStockItems;
        document.getElementById('inUseItems').textContent = stats.inUseItems;
        document.getElementById('needsRepair').textContent = stats.needsRepair;
    }
    
    // Update pagination
    function updatePagination(totalItems) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const startItem = (currentPage - 1) * itemsPerPage + 1;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);
        
        document.getElementById('startItem').textContent = totalItems > 0 ? startItem : 0;
        document.getElementById('endItem').textContent = endItem;
        document.getElementById('totalItems').textContent = totalItems;
        
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pageNumbers = document.getElementById('pageNumbers');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        // Generate page numbers
        let pageHTML = '';
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            pageHTML += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        }
        
        pageNumbers.innerHTML = pageHTML;
    }
    
    // Change page
    function changePage(direction) {
        const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
        const newPage = currentPage + direction;
        
        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            renderAssets();
        }
    }
    
    // Go to specific page
    function goToPage(page) {
        currentPage = page;
        renderAssets();
    }
    
    // Filter by category
    function filterByCategory(categoryName) {
        switchTab('assets');
        document.getElementById('categoryFilter').value = categoryName;
        applyFilters();
    }
    
    // Asset actions
    function viewAsset(id) {
        window.location.href = `/assets/${id}`;
    }
    
    function editAsset(id) {
        window.location.href = `/assets/${id}/edit`;
    }
    
    function deleteAsset(id) {
        if (confirm('Are you sure you want to delete this asset?')) {
            // Implement delete functionality
            console.log('Delete asset:', id);
        }
    }
</script>
@endsection