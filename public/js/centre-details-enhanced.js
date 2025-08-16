/**
 * Enhanced Centre Details Management JavaScript
 * Provides comprehensive functionality for centre details interface with analytics
 */

class EnhancedCentreDetails {
    constructor(options) {
        this.options = {
            centreId: '',
            centreName: '',
            stats: {},
            userRole: 'guest',
            chartColors: {
                primary: '#32bdea',
                secondary: '#c850c0',
                success: '#2ed573',
                warning: '#ffa726',
                danger: '#ff4757',
                info: '#3742fa'
            },
            ...options
        };

        this.charts = {};
        this.fullscreenMode = false;
        this.currentImageIndex = 0;
        this.images = [];

        this.init();
    }

    init() {
        this.setupCharts();
        this.setupEventListeners();
        this.setupImageGallery();
        this.setupAnalyticsTimeframe();
        this.setupTooltips();
        
        console.log('Enhanced Centre Details initialized for:', this.options.centreName);
    }

    setupEventListeners() {
        // Analytics timeframe selector
        const timeframeSelector = document.getElementById('analyticsTimeframe');
        if (timeframeSelector) {
            timeframeSelector.addEventListener('change', (e) => {
                this.updateAnalytics(e.target.value);
            });
        }

        // Fullscreen modal events
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.fullscreenMode) {
                this.closeFullscreen();
            }
        });

        // Print functionality
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                this.optimizeForPrint();
                setTimeout(() => window.print(), 100);
            }
        });

        // Responsive chart resizing
        window.addEventListener('resize', () => {
            this.debounce(() => {
                this.resizeCharts();
            }, 300)();
        });
    }

    setupCharts() {
        this.initializePerformanceChart();
        this.initializeAssetChart();
    }

    initializePerformanceChart() {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;

        const timeframe = document.getElementById('analyticsTimeframe')?.value || 'month';
        const data = this.generatePerformanceData(timeframe);

        this.charts.performance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Utilization Rate',
                        data: data.utilization,
                        borderColor: this.options.chartColors.primary,
                        backgroundColor: this.options.chartColors.primary + '20',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: this.options.chartColors.primary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'Attendance Rate',
                        data: data.attendance,
                        borderColor: this.options.chartColors.success,
                        backgroundColor: this.options.chartColors.success + '20',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: this.options.chartColors.success,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'Satisfaction Score',
                        data: data.satisfaction,
                        borderColor: this.options.chartColors.warning,
                        backgroundColor: this.options.chartColors.warning + '20',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: this.options.chartColors.warning,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: this.options.chartColors.primary,
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#6c757d'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#6c757d',
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });
    }

    initializeAssetChart() {
        const ctx = document.getElementById('assetChart');
        if (!ctx) return;

        const stats = this.options.stats;
        const data = [
            stats.functional_assets || 0,
            stats.maintenance_assets || 0,
            stats.broken_assets || 0
        ];

        this.charts.assets = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Functional', 'Maintenance', 'Broken'],
                datasets: [{
                    data: data,
                    backgroundColor: [
                        this.options.chartColors.success,
                        this.options.chartColors.warning,
                        this.options.chartColors.danger
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: this.options.chartColors.primary,
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((context.parsed * 100) / total) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                onHover: (event, elements) => {
                    event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                }
            }
        });
    }

    setupImageGallery() {
        // Get all images from the carousel
        const carouselImages = document.querySelectorAll('#centreCarousel .carousel-item img');
        this.images = Array.from(carouselImages).map(img => ({
            src: img.src,
            alt: img.alt
        }));

        // Set up carousel navigation
        this.setupCarouselControls();
    }

    setupCarouselControls() {
        const carousel = document.getElementById('centreCarousel');
        if (!carousel) return;

        // Auto-advance carousel every 5 seconds
        this.carouselInterval = setInterval(() => {
            const nextBtn = carousel.querySelector('.carousel-control-next');
            if (nextBtn && this.images.length > 1) {
                nextBtn.click();
            }
        }, 5000);

        // Pause auto-advance on hover
        carousel.addEventListener('mouseenter', () => {
            if (this.carouselInterval) {
                clearInterval(this.carouselInterval);
            }
        });

        carousel.addEventListener('mouseleave', () => {
            this.carouselInterval = setInterval(() => {
                const nextBtn = carousel.querySelector('.carousel-control-next');
                if (nextBtn && this.images.length > 1) {
                    nextBtn.click();
                }
            }, 5000);
        });
    }

    setupAnalyticsTimeframe() {
        // Initialize with current timeframe
        const selector = document.getElementById('analyticsTimeframe');
        if (selector) {
            this.updateAnalytics(selector.value);
        }
    }

    setupTooltips() {
        // Initialize tooltips for elements with data-toggle="tooltip"
        const tooltipElements = document.querySelectorAll('[data-toggle="tooltip"]');
        tooltipElements.forEach(element => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                new bootstrap.Tooltip(element);
            }
        });
    }

    updateAnalytics(timeframe) {
        // Show loading state
        this.showChartLoading('performanceChart');

        // Simulate data loading (in real app, this would be an API call)
        setTimeout(() => {
            this.updatePerformanceChart(timeframe);
            this.updateMetrics(timeframe);
            this.hideChartLoading('performanceChart');
        }, 800);
    }

    updatePerformanceChart(timeframe) {
        if (!this.charts.performance) return;

        const data = this.generatePerformanceData(timeframe);
        
        this.charts.performance.data.labels = data.labels;
        this.charts.performance.data.datasets[0].data = data.utilization;
        this.charts.performance.data.datasets[1].data = data.attendance;
        this.charts.performance.data.datasets[2].data = data.satisfaction;
        
        this.charts.performance.update('active');
    }

    updateMetrics(timeframe) {
        // Update metric change indicators based on timeframe
        const metrics = document.querySelectorAll('.metric-change');
        metrics.forEach(metric => {
            const randomChange = (Math.random() - 0.5) * 10;
            const isPositive = randomChange > 0;
            
            metric.classList.remove('positive', 'negative');
            metric.classList.add(isPositive ? 'positive' : 'negative');
            
            const icon = metric.querySelector('i');
            icon.className = `fas fa-arrow-${isPositive ? 'up' : 'down'}`;
            
            const text = metric.querySelector('span');
            text.textContent = `${isPositive ? '+' : ''}${randomChange.toFixed(1)}% from last ${timeframe}`;
        });
    }

    generatePerformanceData(timeframe) {
        let labels, dataPoints;
        
        switch (timeframe) {
            case 'week':
                labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                dataPoints = 7;
                break;
            case 'quarter':
                labels = ['Month 1', 'Month 2', 'Month 3'];
                dataPoints = 3;
                break;
            case 'year':
                labels = ['Q1', 'Q2', 'Q3', 'Q4'];
                dataPoints = 4;
                break;
            default: // month
                labels = [];
                const daysInMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).getDate();
                for (let i = 1; i <= Math.min(daysInMonth, 30); i += Math.ceil(daysInMonth / 10)) {
                    labels.push(`Day ${i}`);
                }
                dataPoints = labels.length;
        }

        // Generate realistic data with some variance
        const baseUtilization = parseFloat(this.options.stats.utilization_rate || 75);
        const baseAttendance = parseFloat(this.options.stats.attendance_rate || 85);
        const baseSatisfaction = parseFloat(this.options.stats.satisfaction_rate || 4.2) * 20; // Convert to percentage

        return {
            labels: labels,
            utilization: this.generateDataPoints(baseUtilization, dataPoints, 10),
            attendance: this.generateDataPoints(baseAttendance, dataPoints, 8),
            satisfaction: this.generateDataPoints(baseSatisfaction, dataPoints, 5)
        };
    }

    generateDataPoints(base, count, variance) {
        const points = [];
        for (let i = 0; i < count; i++) {
            const variation = (Math.random() - 0.5) * variance;
            points.push(Math.max(0, Math.min(100, base + variation)));
        }
        return points;
    }

    openFullscreen() {
        if (this.images.length === 0) return;

        const modal = document.getElementById('fullscreenModal');
        const carousel = document.getElementById('fullscreenCarousel');
        
        if (!modal || !carousel) return;

        // Create fullscreen carousel content
        const carouselInner = document.createElement('div');
        carouselInner.className = 'carousel-inner';

        this.images.forEach((image, index) => {
            const item = document.createElement('div');
            item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
            item.innerHTML = `<img src="${image.src}" alt="${image.alt}" class="d-block w-100">`;
            carouselInner.appendChild(item);
        });

        carousel.innerHTML = '';
        carousel.appendChild(carouselInner);

        // Add controls if multiple images
        if (this.images.length > 1) {
            carousel.innerHTML += `
                <a class="carousel-control-prev" href="#fullscreenCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </a>
                <a class="carousel-control-next" href="#fullscreenCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </a>
            `;
        }

        modal.classList.add('show');
        this.fullscreenMode = true;
        document.body.style.overflow = 'hidden';

        // Initialize Bootstrap carousel
        if (typeof $ !== 'undefined') {
            $(carousel).carousel();
        }
    }

    closeFullscreen() {
        const modal = document.getElementById('fullscreenModal');
        if (modal) {
            modal.classList.remove('show');
        }
        
        this.fullscreenMode = false;
        document.body.style.overflow = '';
    }

    generateReport() {
        // Show loading state
        this.showNotification('Generating comprehensive centre report...', 'info');

        // Simulate report generation
        setTimeout(() => {
            const reportData = this.prepareReportData();
            this.downloadReport(reportData);
            this.showNotification('Centre report generated successfully!', 'success');
        }, 2000);
    }

    prepareReportData() {
        const stats = this.options.stats;
        const centre = {
            id: this.options.centreId,
            name: this.options.centreName
        };

        return {
            centre: centre,
            generatedAt: new Date().toISOString(),
            stats: {
                totalStaff: stats.total_staff || 0,
                activeStaff: stats.active_staff || 0,
                totalTrainees: stats.total_trainees || 0,
                totalAssets: stats.total_assets || 0,
                functionalAssets: stats.functional_assets || 0,
                maintenanceAssets: stats.maintenance_assets || 0,
                activeActivities: stats.active_activities || 0,
                utilizationRate: stats.utilization_rate || 0,
                attendanceRate: stats.attendance_rate || 0,
                satisfactionRate: stats.satisfaction_rate || 0,
                completionRate: stats.completion_rate || 0
            },
            performance: this.generatePerformanceData('month')
        };
    }

    downloadReport(data) {
        // Convert to CSV format
        const csv = this.convertReportToCSV(data);
        
        // Create and download file
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${this.options.centreName.replace(/[^a-z0-9]/gi, '_')}_report_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    convertReportToCSV(data) {
        const lines = [];
        
        // Header
        lines.push('Centre Performance Report');
        lines.push(`Centre: ${data.centre.name}`);
        lines.push(`Generated: ${new Date(data.generatedAt).toLocaleDateString()}`);
        lines.push('');
        
        // Stats
        lines.push('Key Metrics');
        lines.push('Metric,Value');
        lines.push(`Total Staff,${data.stats.totalStaff}`);
        lines.push(`Active Staff,${data.stats.activeStaff}`);
        lines.push(`Total Trainees,${data.stats.totalTrainees}`);
        lines.push(`Total Assets,${data.stats.totalAssets}`);
        lines.push(`Functional Assets,${data.stats.functionalAssets}`);
        lines.push(`Assets Needing Maintenance,${data.stats.maintenanceAssets}`);
        lines.push(`Active Activities,${data.stats.activeActivities}`);
        lines.push(`Utilization Rate,${data.stats.utilizationRate}%`);
        lines.push(`Attendance Rate,${data.stats.attendanceRate}%`);
        lines.push(`Satisfaction Score,${data.stats.satisfactionRate}`);
        lines.push(`Completion Rate,${data.stats.completionRate}%`);
        lines.push('');
        
        // Performance data
        lines.push('Performance Trends (Last Month)');
        lines.push('Period,Utilization Rate,Attendance Rate,Satisfaction Score');
        data.performance.labels.forEach((label, index) => {
            lines.push(`${label},${data.performance.utilization[index].toFixed(1)}%,${data.performance.attendance[index].toFixed(1)}%,${data.performance.satisfaction[index].toFixed(1)}%`);
        });
        
        return lines.join('\n');
    }

    exportData() {
        // Prepare comprehensive data export
        this.showNotification('Preparing data export...', 'info');

        setTimeout(() => {
            const exportData = {
                centre: {
                    id: this.options.centreId,
                    name: this.options.centreName,
                    exportedAt: new Date().toISOString()
                },
                statistics: this.options.stats,
                performance: this.generatePerformanceData('month')
            };

            // Convert to JSON and download
            const json = JSON.stringify(exportData, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${this.options.centreName.replace(/[^a-z0-9]/gi, '_')}_data_${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            this.showNotification('Data exported successfully!', 'success');
        }, 1000);
    }

    optimizeForPrint() {
        // Hide interactive elements for better printing
        const hideElements = document.querySelectorAll('.dropdown, .fullscreen-btn, .action-buttons');
        hideElements.forEach(el => {
            el.style.display = 'none';
        });

        // Ensure charts are visible
        this.resizeCharts();

        // Restore after print
        setTimeout(() => {
            hideElements.forEach(el => {
                el.style.display = '';
            });
        }, 1000);
    }

    resizeCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    showChartLoading(chartId) {
        const container = document.getElementById(chartId)?.parentElement;
        if (container) {
            container.classList.add('loading');
        }
    }

    hideChartLoading(chartId) {
        const container = document.getElementById(chartId)?.parentElement;
        if (container) {
            container.classList.remove('loading');
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${this.getNotificationIcon(type)}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 4 seconds
        setTimeout(() => {
            notification.remove();
        }, 4000);
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

    destroy() {
        // Clean up event listeners and intervals
        if (this.carouselInterval) {
            clearInterval(this.carouselInterval);
        }

        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });

        // Remove event listeners
        document.removeEventListener('keydown', this.handleKeydown);
        window.removeEventListener('resize', this.handleResize);
    }
}

// Notification styles (if not already included)
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
        max-width: 350px;
    }
    
    .notification-success { background: #2ed573; }
    .notification-error { background: #ff4757; }
    .notification-warning { background: #ffa726; }
    .notification-info { background: #3742fa; }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .chart-container.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #32bdea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translate(-50%, -50%);
        z-index: 10;
    }

    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
`;

// Inject notification styles if not already present
if (!document.getElementById('centre-details-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'centre-details-notification-styles';
    style.textContent = notificationStyles;
    document.head.appendChild(style);
}

// Export class for use in templates
window.EnhancedCentreDetails = EnhancedCentreDetails;