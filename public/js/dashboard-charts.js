/**
 * Dashboard Charts Manager
 * CREAMS - Care Rehabilitation Centre Management System
 */

class DashboardCharts {
    constructor() {
        this.charts = new Map();
        this.chartConfigs = new Map();
        this.isInitialized = false;
        
        // Default chart colors
        this.colors = {
            primary: '#32bdea',
            success: '#2ed573',
            warning: '#ffa502',
            danger: '#ff4757',
            info: '#1e90ff',
            secondary: '#c850c0',
            light: '#f8f9fa',
            dark: '#2c3e50'
        };
        
        // Chart.js default configuration
        this.defaultConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We'll use custom legends
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderColor: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#7f8c8d'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderColor: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#7f8c8d'
                    }
                }
            }
        };
    }

    /**
     * Initialize the charts manager
     */
    init() {
        if (this.isInitialized) return;
        
        console.log('Initializing Dashboard Charts...');
        
        // Wait for Chart.js to load
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded, retrying in 500ms...');
            setTimeout(() => this.init(), 500);
            return;
        }
        
        // Set Chart.js defaults
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#7f8c8d';
        
        // Initialize existing charts
        this.initializeExistingCharts();
        
        // Listen for chart update events
        this.setupEventListeners();
        
        this.isInitialized = true;
        console.log('Dashboard Charts initialized successfully');
    }

    /**
     * Initialize charts that are already in the DOM with lazy loading
     */
    initializeExistingCharts() {
        const chartElements = document.querySelectorAll('[id^="chart-"], [data-chart]');
        
        // Use Intersection Observer for lazy loading charts
        if ('IntersectionObserver' in window) {
            this.setupLazyLoading(chartElements);
        } else {
            // Fallback: load all charts with delay for older browsers
            this.loadChartsWithDelay(chartElements);
        }
    }

    /**
     * Setup lazy loading for charts using Intersection Observer
     * @param {NodeList} chartElements - Chart elements
     */
    setupLazyLoading(chartElements) {
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const chartObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    
                    // Add loading indicator
                    this.showChartLoading(element);
                    
                    // Load chart with slight delay to prevent overwhelming the browser
                    setTimeout(() => {
                        try {
                            const chartData = this.getChartData(element);
                            if (chartData) {
                                this.createChart(element, chartData);
                            } else {
                                this.showChartEmpty(element);
                            }
                        } catch (error) {
                            console.error('Error loading chart:', error, element);
                            this.showChartError(element, 'Failed to load chart');
                        }
                    }, 200);
                    
                    // Stop observing this element
                    chartObserver.unobserve(element);
                }
            });
        }, observerOptions);

        chartElements.forEach(element => {
            // Mark element as lazy-loadable
            element.classList.add('chart-lazy');
            chartObserver.observe(element);
        });
    }

    /**
     * Load charts with progressive delay (fallback for older browsers)
     * @param {NodeList} chartElements - Chart elements
     */
    loadChartsWithDelay(chartElements) {
        chartElements.forEach((element, index) => {
            // Add loading indicator
            this.showChartLoading(element);
            
            // Progressive delay to prevent browser lag
            setTimeout(() => {
                try {
                    const chartData = this.getChartData(element);
                    if (chartData) {
                        this.createChart(element, chartData);
                    } else {
                        this.showChartEmpty(element);
                    }
                } catch (error) {
                    console.error('Error initializing chart:', error, element);
                    this.showChartError(element, 'Failed to load chart');
                }
            }, index * 300); // 300ms delay between each chart
        });
    }

    /**
     * Get chart data from element
     * @param {Element} element - Chart canvas element
     * @returns {Object|null} Chart data
     */
    getChartData(element) {
        try {
            // Try to get data from data-chart attribute
            const dataAttr = element.getAttribute('data-chart');
            if (dataAttr) {
                return JSON.parse(dataAttr);
            }
            
            // Try to get data from parent container
            const container = element.closest('[data-chart]');
            if (container) {
                const containerData = container.getAttribute('data-chart');
                if (containerData) {
                    return JSON.parse(containerData);
                }
            }
            
            return null;
        } catch (error) {
            console.error('Error parsing chart data:', error);
            return null;
        }
    }

    /**
     * Create a chart
     * @param {Element} element - Canvas element
     * @param {Object} chartData - Chart configuration data
     * @returns {Chart|null} Created chart instance
     */
    createChart(element, chartData) {
        try {
            const ctx = element.getContext('2d');
            const chartId = element.id || `chart-${Date.now()}`;
            
            // Generate chart configuration
            const config = this.generateChartConfig(chartData);
            
            // Destroy existing chart if it exists
            if (this.charts.has(chartId)) {
                this.charts.get(chartId).destroy();
            }
            
            // Create new chart
            const chart = new Chart(ctx, config);
            
            // Store chart instance and config
            this.charts.set(chartId, chart);
            this.chartConfigs.set(chartId, chartData);
            
            // Add chart to container classes
            const container = element.closest('.chart-container');
            if (container) {
                container.classList.add('chart-loaded');
                container.classList.add(`chart-${chartData.type || 'line'}`);
            }
            
            console.log(`Chart created: ${chartId}`, chartData.type);
            return chart;
            
        } catch (error) {
            console.error('Error creating chart:', error);
            this.showChartError(element, 'Failed to create chart');
            return null;
        }
    }

    /**
     * Generate Chart.js configuration from data
     * @param {Object} chartData - Chart data
     * @returns {Object} Chart.js configuration
     */
    generateChartConfig(chartData) {
        const type = chartData.type || 'line';
        const data = this.processChartData(chartData.data || {});
        const options = { ...this.defaultConfig, ...(chartData.options || {}) };
        
        // Type-specific configurations
        switch (type) {
            case 'pie':
            case 'doughnut':
                delete options.scales; // Pie charts don't use scales
                options.plugins.legend = {
                    display: true,
                    position: 'bottom',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                };
                break;
                
            case 'bar':
                options.scales.y.beginAtZero = true;
                break;
                
            case 'line':
                options.elements = {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    },
                    line: {
                        tension: 0.4
                    }
                };
                break;
        }
        
        return {
            type: type,
            data: data,
            options: options
        };
    }

    /**
     * Process chart data and apply colors
     * @param {Object} data - Raw chart data
     * @returns {Object} Processed chart data
     */
    processChartData(data) {
        const processedData = { ...data };
        
        if (processedData.datasets && Array.isArray(processedData.datasets)) {
            processedData.datasets = processedData.datasets.map((dataset, index) => {
                const colorKey = dataset.colorScheme || 'primary';
                const baseColor = this.colors[colorKey] || this.colors.primary;
                
                return {
                    ...dataset,
                    backgroundColor: dataset.backgroundColor || this.generateColors(baseColor, 0.2, dataset.data?.length || 1),
                    borderColor: dataset.borderColor || this.generateColors(baseColor, 1, dataset.data?.length || 1),
                    borderWidth: dataset.borderWidth || 2,
                    fill: dataset.fill !== undefined ? dataset.fill : false
                };
            });
        }
        
        return processedData;
    }

    /**
     * Generate colors array for charts
     * @param {string} baseColor - Base color
     * @param {number} alpha - Alpha transparency
     * @param {number} count - Number of colors needed
     * @returns {string|Array} Color or array of colors
     */
    generateColors(baseColor, alpha, count = 1) {
        if (count === 1) {
            return this.hexToRgba(baseColor, alpha);
        }
        
        const colors = [];
        const colorKeys = Object.keys(this.colors);
        
        for (let i = 0; i < count; i++) {
            const colorIndex = i % colorKeys.length;
            const color = this.colors[colorKeys[colorIndex]];
            colors.push(this.hexToRgba(color, alpha));
        }
        
        return colors;
    }

    /**
     * Convert hex color to rgba
     * @param {string} hex - Hex color
     * @param {number} alpha - Alpha value
     * @returns {string} RGBA color string
     */
    hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    /**
     * Update a chart with new data
     * @param {string} chartId - Chart ID
     * @param {Object} newData - New chart data
     */
    updateChart(chartId, newData) {
        const chart = this.charts.get(chartId);
        if (!chart) {
            console.warn(`Chart not found: ${chartId}`);
            return;
        }
        
        try {
            // Update data
            if (newData.data) {
                Object.assign(chart.data, this.processChartData(newData.data));
            }
            
            // Update options if provided
            if (newData.options) {
                Object.assign(chart.options, newData.options);
            }
            
            // Update the chart
            chart.update('active');
            
            // Update stored config
            this.chartConfigs.set(chartId, { ...this.chartConfigs.get(chartId), ...newData });
            
            console.log(`Chart updated: ${chartId}`);
            
        } catch (error) {
            console.error('Error updating chart:', error);
        }
    }

    /**
     * Update all charts with new data
     * @param {Object} chartsData - Object with chart IDs as keys and chart data as values
     */
    updateAllCharts(chartsData) {
        Object.keys(chartsData).forEach(chartId => {
            this.updateChart(chartId, chartsData[chartId]);
        });
    }

    /**
     * Destroy a chart
     * @param {string} chartId - Chart ID
     */
    destroyChart(chartId) {
        const chart = this.charts.get(chartId);
        if (chart) {
            chart.destroy();
            this.charts.delete(chartId);
            this.chartConfigs.delete(chartId);
            console.log(`Chart destroyed: ${chartId}`);
        }
    }

    /**
     * Destroy all charts
     */
    destroyAllCharts() {
        this.charts.forEach((chart, chartId) => {
            chart.destroy();
        });
        this.charts.clear();
        this.chartConfigs.clear();
        console.log('All charts destroyed');
    }

    /**
     * Show chart loading state
     * @param {Element} element - Chart container element
     */
    showChartLoading(element) {
        const container = element.closest('.chart-container') || element.parentElement;
        if (container && !container.classList.contains('chart-loaded')) {
            container.innerHTML = `
                <div class="chart-loading" style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 200px;
                    color: #6c757d;
                ">
                    <div class="chart-loading-spinner" style="
                        width: 40px;
                        height: 40px;
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #32bdea;
                        border-radius: 50%;
                        animation: chart-spin 1s linear infinite;
                        margin-bottom: 15px;
                    "></div>
                    <div class="chart-loading-text" style="
                        font-size: 14px;
                        font-weight: 500;
                    ">Loading chart...</div>
                </div>
                <style>
                    @keyframes chart-spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            `;
        }
    }

    /**
     * Show chart error state
     * @param {Element} element - Chart container element
     * @param {string} message - Error message
     */
    showChartError(element, message = 'Failed to load chart') {
        const container = element.closest('.chart-container') || element.parentElement;
        if (container) {
            container.innerHTML = `
                <div class="chart-empty">
                    <div class="chart-empty-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="chart-empty-text">${message}</div>
                    <div class="chart-empty-subtitle">Please try refreshing the page</div>
                </div>
            `;
        }
    }

    /**
     * Show empty chart state
     * @param {Element} element - Chart container element
     */
    showChartEmpty(element) {
        const container = element.closest('.chart-container') || element.parentElement;
        if (container) {
            container.innerHTML = `
                <div class="chart-empty">
                    <div class="chart-empty-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="chart-empty-text">No data available</div>
                    <div class="chart-empty-subtitle">Chart will appear when data is available</div>
                </div>
            `;
        }
    }

    /**
     * Setup event listeners for chart interactions
     */
    setupEventListeners() {
        // Listen for dashboard chart updates
        document.addEventListener('dashboard:charts-updated', (event) => {
            const chartsData = event.detail.charts;
            if (chartsData && typeof chartsData === 'object') {
                this.updateAllCharts(chartsData);
            }
        });
        
        // Listen for window resize to update charts
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.charts.forEach(chart => {
                    chart.resize();
                });
            }, 250);
        });
        
        // Handle chart filter buttons
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('chart-filter')) {
                this.handleChartFilter(event.target);
            }
        });
        
        // Handle chart action buttons
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('chart-action-btn')) {
                this.handleChartAction(event.target);
            }
        });
    }

    /**
     * Handle chart filter clicks
     * @param {Element} filterButton - Filter button element
     */
    handleChartFilter(filterButton) {
        const container = filterButton.closest('.chart-container');
        if (!container) return;
        
        const chartCanvas = container.querySelector('canvas');
        if (!chartCanvas) return;
        
        const chartId = chartCanvas.id;
        const filterValue = filterButton.dataset.filter;
        
        // Update active filter
        const allFilters = container.querySelectorAll('.chart-filter');
        allFilters.forEach(filter => filter.classList.remove('active'));
        filterButton.classList.add('active');
        
        // Apply filter logic here
        console.log(`Chart filter applied: ${chartId} - ${filterValue}`);
        
        // You can implement specific filtering logic based on your needs
        // For example, call an API to get filtered data
    }

    /**
     * Handle chart action buttons
     * @param {Element} actionButton - Action button element
     */
    handleChartAction(actionButton) {
        const container = actionButton.closest('.chart-container');
        if (!container) return;
        
        const chartCanvas = container.querySelector('canvas');
        if (!chartCanvas) return;
        
        const chartId = chartCanvas.id;
        const action = actionButton.dataset.action;
        
        switch (action) {
            case 'refresh':
                this.refreshChart(chartId);
                break;
            case 'download':
                this.downloadChart(chartId);
                break;
            case 'fullscreen':
                this.toggleChartFullscreen(container);
                break;
            default:
                console.log(`Chart action: ${chartId} - ${action}`);
        }
    }

    /**
     * Refresh a specific chart
     * @param {string} chartId - Chart ID
     */
    refreshChart(chartId) {
        const chart = this.charts.get(chartId);
        if (chart) {
            // Add loading state
            const container = chart.canvas.closest('.chart-container');
            container.classList.add('loading');
            
            // Simulate refresh (you can implement actual data fetching here)
            setTimeout(() => {
                chart.update('active');
                container.classList.remove('loading');
                console.log(`Chart refreshed: ${chartId}`);
            }, 1000);
        }
    }

    /**
     * Download chart as image
     * @param {string} chartId - Chart ID
     */
    downloadChart(chartId) {
        const chart = this.charts.get(chartId);
        if (chart) {
            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.href = url;
            link.download = `chart-${chartId}-${Date.now()}.png`;
            link.click();
            console.log(`Chart downloaded: ${chartId}`);
        }
    }

    /**
     * Toggle chart fullscreen
     * @param {Element} container - Chart container element
     */
    toggleChartFullscreen(container) {
        if (container.classList.contains('chart-fullscreen')) {
            container.classList.remove('chart-fullscreen');
            document.body.classList.remove('chart-fullscreen-active');
        } else {
            container.classList.add('chart-fullscreen');
            document.body.classList.add('chart-fullscreen-active');
        }
        
        // Resize chart after fullscreen toggle
        setTimeout(() => {
            const canvas = container.querySelector('canvas');
            if (canvas) {
                const chart = this.charts.get(canvas.id);
                if (chart) {
                    chart.resize();
                }
            }
        }, 300);
    }

    /**
     * Get chart instance by ID
     * @param {string} chartId - Chart ID
     * @returns {Chart|null} Chart instance
     */
    getChart(chartId) {
        return this.charts.get(chartId) || null;
    }

    /**
     * Get all chart instances
     * @returns {Map} Map of all chart instances
     */
    getAllCharts() {
        return new Map(this.charts);
    }
}

// Create global instance
window.DashboardCharts = new DashboardCharts();

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.DashboardCharts.init();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardCharts;
}