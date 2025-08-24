@extends('layouts.app')

@section('title', $centre->centre_name . ' - CREAMS')

<!-- CREATED_AT: JULY 7 - AUTO BY CLAUDE -->

@section('content')
<div class="centre-detail-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $centre->centre_name }}</h1>
            <p class="subtitle">Centre ID: {{ $centre->centre_id }}</p>
        </div>
        <div class="page-actions">
            @if(session('role') === 'admin')
                <a href="{{ route('centres.edit', $centre->centre_id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit Centre
                </a>
            @endif
            <a href="{{ route('centres.assets', $centre->centre_id) }}" class="btn btn-primary">
                <i class="fas fa-box"></i> View Asset
            </a>
            <a href="{{ route('centres.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Centre
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Centre Image Gallery Carousel --}}
            @if($centre->centre_image || isset($centre_images))
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3>Centre Gallery</h3>
                    <button class="btn btn-sm btn-outline-light" onclick="toggleFullscreen()">
                        <i class="fas fa-expand"></i> Fullscreen
                    </button>
                </div>
                <div class="detail-card-body p-0">
                    <div id="centreCarousel" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            @php
                                $images = [];
                                if($centre->centre_image) {
                                    $images[] = $centre->centre_image;
                                }
                                if(isset($centre_images)) {
                                    $images = array_merge($images, $centre_images);
                                }
                                // Add some placeholder images if no images exist
                                if(empty($images)) {
                                    $images = [
                                        'default-centre-1.jpg',
                                        'default-centre-2.jpg',
                                        'default-centre-3.jpg'
                                    ];
                                }
                            @endphp
                            
                            @foreach($images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img class="d-block w-100 carousel-image" 
                                     src="{{ file_exists(public_path('storage/centres/' . $image)) ? asset('storage/centres/' . $image) : asset('images/centre-placeholder.jpg') }}" 
                                     alt="{{ $centre->centre_name }} - Image {{ $index + 1 }}"
                                     onerror="this.src='{{ asset('images/centre-placeholder.jpg') }}'">
                                <div class="carousel-caption d-none d-md-block">
                                    <h5>{{ $centre->centre_name }}</h5>
                                    <p>{{ $centre->centre_address ?? 'Centre Location' }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Carousel Controls -->
                        @if(count($images) > 1)
                        <a class="carousel-control-prev" href="#centreCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#centreCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                        
                        <!-- Carousel Indicators -->
                        <ol class="carousel-indicators">
                            @foreach($images as $index => $image)
                            <li data-target="#centreCarousel" data-slide-to="{{ $index }}" 
                                class="{{ $index === 0 ? 'active' : '' }}"></li>
                            @endforeach
                        </ol>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Centre Details Card --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3>Centre Information</h3>
                    @if($centre->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </div>
                <div class="detail-card-body">
                    @if($centre->description)
                        <div class="detail-section">
                            <h4>Description</h4>
                            <p>{{ $centre->description }}</p>
                        </div>
                    @endif

                    <div class="detail-section">
                        <h4>Contact Information</h4>
                        <div class="contact-details">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="contact-info">
                                    <strong>Address:</strong>
                                    <span>{{ $centre->address }}, {{ $centre->city }}, {{ $centre->state }} {{ $centre->postcode }}</span>
                                </div>
                            </div>
                            @if($centre->phone)
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <div class="contact-info">
                                        <strong>Phone:</strong>
                                        <span>{{ $centre->phone }}</span>
                                    </div>
                                </div>
                            @endif
                            @if($centre->email)
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <div class="contact-info">
                                        <strong>Email:</strong>
                                        <span>{{ $centre->email }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Operating Hours</h4>
                        <div class="operating-hours">
                            <p><strong>Opening Time:</strong> {{ $centre->opening_time ? \Carbon\Carbon::parse($centre->opening_time)->format('g:i A') : 'Not specified' }}</p>
                            <p><strong>Closing Time:</strong> {{ $centre->closing_time ? \Carbon\Carbon::parse($centre->closing_time)->format('g:i A') : 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Activity Card --}}
            <div class="detail-card mt-4">
                <div class="detail-card-header">
                    <h3>Recent Activity</h3>
                    <a href="{{ route('activities.home') }}?centre={{ $centre->centre_id }}" class="btn btn-sm btn-outline-light">
                        View All
                    </a>
                </div>
                <div class="detail-card-body">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        <div class="activities-list">
                            @foreach($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <span class="activity-name">
                                            <a href="{{ route('activities.show', $activity->id) }}">
                                                {{ $activity->name ?? 'Unknown Activity' }}
                                            </a>
                                        </span>
                                        <span class="activity-date">
                                            {{ $activity->date ? \Carbon\Carbon::parse($activity->date)->format('M d, Y') : 'No date' }}
                                        </span>
                                    </div>
                                    <span class="badge badge-success">Active</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No recent activities</p>
                            @if(session('role') === 'admin')
                                <a href="{{ route('activities.create') }}" class="btn btn-sm btn-primary mt-2">
                                    Create Activity
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Asset Overview Card --}}
            <div class="detail-card mt-4">
                <div class="detail-card-header">
                    <h3>Asset Overview</h3>
                    <a href="{{ route('centres.assets', $centre->centre_id) }}" class="btn btn-sm btn-outline-light">
                        Manage Asset
                    </a>
                </div>
                <div class="detail-card-body">
                    @if(($stats->total_assets ?? $stats['total_assets'] ?? 0) > 0)
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="asset-stat">
                                    <h4 class="text-primary">{{ $stats->total_assets ?? $stats['total_assets'] ?? 0 }}</h4>
                                    <small class="text-muted">Total Asset</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="asset-stat">
                                    <h4 class="text-success">{{ $stats->total_assets ?? $stats['total_assets'] ?? 0 }}</h4>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="asset-stat">
                                    <h4 class="text-info">0</h4>
                                    <small class="text-muted">In Use</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No assets registered</p>
                            @if(session('role') === 'admin')
                                <a href="{{ route('centres.assets', $centre->centre_id) }}" class="btn btn-sm btn-primary mt-2">
                                    Add Asset
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Statistics Card --}}
            <div class="stats-card">
                <div class="stats-card-header">
                    <h3>Statistics</h3>
                </div>
                <div class="stats-card-body">
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats->total_staff ?? $stats['total_staff'] ?? 0 }}</div>
                        <div class="stat-label">Total Staff</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats->total_trainees ?? $stats['total_trainees'] ?? 0 }}</div>
                        <div class="stat-label">Total Trainee</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats->total_assets ?? $stats['total_assets'] ?? 0 }}</div>
                        <div class="stat-label">Total Asset</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $centre->capacity ?? 0 }}</div>
                        <div class="stat-label">Capacity</div>
                    </div>
                    @if(isset($stats->utilization_rate) || isset($stats['utilization_rate']))
                        <div class="stat-item">
                            <div class="stat-value">{{ $stats->utilization_rate ?? $stats['utilization_rate'] ?? 0 }}%</div>
                            <div class="stat-label">Utilization Rate</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            @if(session('role') === 'admin')
            <div class="quick-actions-card mt-4">
                <div class="quick-actions-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-actions-body">
                    <a href="{{ route('centres.assets', $centre->centre_id) }}" class="action-item">
                        <i class="fas fa-box"></i>
                        <span>Manage Asset</span>
                    </a>
                    <a href="{{ route('centres.edit', $centre->centre_id) }}" class="action-item">
                        <i class="fas fa-edit"></i>
                        <span>Edit Centre Details</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.centre-detail-container {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.subtitle {
    color: #6c757d;
    margin: 0;
    font-size: 1.1rem;
}

.detail-card, .stats-card, .quick-actions-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    overflow: hidden;
}

.detail-card-header, .stats-card-header, .quick-actions-header {
    background: linear-gradient(135deg, var(--primary-color, #007bff), var(--secondary-color, #6c757d));
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-card-header h3, .stats-card-header h3, .quick-actions-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.detail-card-body, .stats-card-body, .quick-actions-body {
    padding: 25px;
}

.detail-section {
    margin-bottom: 25px;
}

.detail-section h4 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.contact-item i {
    color: var(--primary-color, #007bff);
    width: 20px;
    text-align: center;
}

.contact-info strong {
    margin-right: 10px;
    color: #495057;
}

.operating-hours p {
    margin-bottom: 8px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    border-bottom: 1px solid #f8f9fa;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color, #007bff);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    color: #495057;
    text-decoration: none;
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.3s ease;
}

.action-item:last-child {
    border-bottom: none;
}

.action-item:hover {
    color: var(--primary-color, #007bff);
    text-decoration: none;
    padding-left: 10px;
}

.action-item i {
    color: var(--primary-color, #007bff);
    width: 20px;
    text-align: center;
}

.badge {
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

.activities-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-name {
    font-weight: 600;
    color: #2c3e50;
}

.activity-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.btn {
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-outline-primary {
    border: 2px solid var(--primary-color, #007bff);
    color: var(--primary-color, #007bff);
    background: transparent;
}

.btn-outline-primary:hover {
    background: var(--primary-color, #007bff);
    color: white;
}

.btn-primary {
    background: var(--primary-color, #007bff);
    color: white;
    border: 2px solid var(--primary-color, #007bff);
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    background: transparent;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

/* Carousel Specific Styles */
.carousel-image {
    height: 400px;
    object-fit: cover;
    border-radius: 0;
}

.carousel-caption {
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    border-radius: 0 0 10px 10px;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 30px 20px 20px;
}

.carousel-caption h5 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
}

.carousel-caption p {
    font-size: 1rem;
    margin-bottom: 0;
    opacity: 0.9;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
}

.carousel-control-prev,
.carousel-control-next {
    width: 8%;
    opacity: 0;
    transition: all 0.3s ease;
}

.carousel:hover .carousel-control-prev,
.carousel:hover .carousel-control-next {
    opacity: 0.8;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 1;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 30px;
    height: 30px;
    background-size: 100%;
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.8);
}

.carousel-indicators {
    bottom: 15px;
    margin-bottom: 0;
}

.carousel-indicators li {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.5);
    border: 2px solid rgba(255,255,255,0.8);
    margin: 0 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-indicators .active {
    background-color: var(--primary-color, #007bff);
    border-color: var(--primary-color, #007bff);
    transform: scale(1.2);
}

/* Fullscreen Modal Styles */
.fullscreen-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.95);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}

.fullscreen-modal.active {
    display: flex;
}

.fullscreen-carousel {
    width: 90%;
    height: 90%;
    max-width: 1200px;
}

.fullscreen-carousel .carousel-image {
    height: 80vh;
    object-fit: contain;
}

.fullscreen-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    z-index: 10000;
    padding: 10px;
    border-radius: 50%;
    background: rgba(0,0,0,0.5);
    transition: all 0.3s ease;
}

.fullscreen-close:hover {
    background: rgba(0,0,0,0.8);
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        margin-top: 15px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .detail-card-body, .stats-card-body, .quick-actions-body {
        padding: 20px;
    }
    
    .contact-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .carousel-image {
        height: 250px;
    }
    
    .carousel-caption {
        padding: 20px 15px 15px;
    }
    
    .carousel-caption h5 {
        font-size: 1.2rem;
    }
    
    .carousel-caption p {
        font-size: 0.9rem;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 12%;
    }
    
    .fullscreen-carousel .carousel-image {
        height: 60vh;
    }
}
</style>
@endsection

<!-- Fullscreen Modal -->
<div id="fullscreenModal" class="fullscreen-modal">
    <div class="fullscreen-close" onclick="toggleFullscreen()">
        <i class="fas fa-times"></i>
    </div>
    <div id="fullscreenCarousel" class="carousel slide fullscreen-carousel" data-ride="carousel">
        <!-- Content will be dynamically populated -->
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel with auto-play
    $('#centreCarousel').carousel({
        interval: 5000,
        pause: 'hover',
        wrap: true
    });
    
    // Enhanced carousel controls
    initializeCarouselKeyboard();
    initializeCarouselTouch();
    initializeCarouselProgressIndicator();
});

// Fullscreen functionality
function toggleFullscreen() {
    const modal = document.getElementById('fullscreenModal');
    const isActive = modal.classList.contains('active');
    
    if (isActive) {
        closeFullscreen();
    } else {
        openFullscreen();
    }
}

function openFullscreen() {
    const modal = document.getElementById('fullscreenModal');
    const fullscreenCarousel = document.getElementById('fullscreenCarousel');
    const originalCarousel = document.getElementById('centreCarousel');
    
    // Clone the carousel content
    const carouselInner = originalCarousel.querySelector('.carousel-inner').cloneNode(true);
    const carouselControls = originalCarousel.querySelectorAll('.carousel-control-prev, .carousel-control-next');
    const carouselIndicators = originalCarousel.querySelector('.carousel-indicators');
    
    // Clear and populate fullscreen carousel
    fullscreenCarousel.innerHTML = '';
    fullscreenCarousel.appendChild(carouselInner);
    
    // Clone controls if they exist
    if (carouselControls.length > 0) {
        carouselControls.forEach(control => {
            const clonedControl = control.cloneNode(true);
            clonedControl.setAttribute('href', '#fullscreenCarousel');
            fullscreenCarousel.appendChild(clonedControl);
        });
    }
    
    // Clone indicators if they exist
    if (carouselIndicators) {
        const clonedIndicators = carouselIndicators.cloneNode(true);
        const indicatorItems = clonedIndicators.querySelectorAll('li');
        indicatorItems.forEach(item => {
            item.setAttribute('data-target', '#fullscreenCarousel');
        });
        fullscreenCarousel.appendChild(clonedIndicators);
    }
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Initialize fullscreen carousel
    setTimeout(() => {
        $('#fullscreenCarousel').carousel({
            interval: 8000,
            pause: 'hover',
            wrap: true
        });
    }, 100);
}

function closeFullscreen() {
    const modal = document.getElementById('fullscreenModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Keyboard navigation for carousel
function initializeCarouselKeyboard() {
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('fullscreenModal');
        const isFullscreen = modal.classList.contains('active');
        const targetCarousel = isFullscreen ? '#fullscreenCarousel' : '#centreCarousel';
        
        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                $(targetCarousel).carousel('prev');
                break;
            case 'ArrowRight':
                e.preventDefault();
                $(targetCarousel).carousel('next');
                break;
            case 'Escape':
                if (isFullscreen) {
                    e.preventDefault();
                    closeFullscreen();
                }
                break;
            case ' ':
                if (isFullscreen) {
                    e.preventDefault();
                    $(targetCarousel).carousel('next');
                }
                break;
        }
    });
}

// Touch gesture support for mobile
function initializeCarouselTouch() {
    let startX = null;
    let startY = null;
    
    document.addEventListener('touchstart', function(e) {
        const touch = e.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
    });
    
    document.addEventListener('touchend', function(e) {
        if (!startX || !startY) return;
        
        const touch = e.changedTouches[0];
        const deltaX = touch.clientX - startX;
        const deltaY = touch.clientY - startY;
        
        // Check if it's a horizontal swipe (more horizontal than vertical)
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            const modal = document.getElementById('fullscreenModal');
            const isFullscreen = modal.classList.contains('active');
            const targetCarousel = isFullscreen ? '#fullscreenCarousel' : '#centreCarousel';
            
            if (deltaX > 0) {
                $(targetCarousel).carousel('prev');
            } else {
                $(targetCarousel).carousel('next');
            }
        }
        
        startX = null;
        startY = null;
    });
}

// Progress indicator for auto-play
function initializeCarouselProgressIndicator() {
    const carousel = $('#centreCarousel');
    let progressInterval;
    
    function startProgress() {
        const activeIndicator = document.querySelector('.carousel-indicators .active');
        if (activeIndicator) {
            activeIndicator.style.transform = 'scale(1.2)';
            activeIndicator.style.transition = 'transform 5s linear';
        }
    }
    
    function resetProgress() {
        const indicators = document.querySelectorAll('.carousel-indicators li');
        indicators.forEach(indicator => {
            indicator.style.transform = 'scale(1)';
            indicator.style.transition = 'all 0.3s ease';
        });
    }
    
    carousel.on('slide.bs.carousel', function() {
        resetProgress();
    });
    
    carousel.on('slid.bs.carousel', function() {
        startProgress();
    });
    
    // Start initial progress
    startProgress();
}

// Auto-hide controls after inactivity
let controlsTimeout;
function showControls() {
    const modal = document.getElementById('fullscreenModal');
    if (modal.classList.contains('active')) {
        const controls = modal.querySelectorAll('.carousel-control-prev, .carousel-control-next, .carousel-indicators, .fullscreen-close');
        controls.forEach(control => {
            control.style.opacity = '1';
        });
        
        clearTimeout(controlsTimeout);
        controlsTimeout = setTimeout(() => {
            controls.forEach(control => {
                if (!control.classList.contains('fullscreen-close')) {
                    control.style.opacity = '0.3';
                }
            });
        }, 3000);
    }
}

// Show controls on mouse movement in fullscreen
document.addEventListener('mousemove', showControls);
document.addEventListener('touchstart', showControls);
</script>
@endsection