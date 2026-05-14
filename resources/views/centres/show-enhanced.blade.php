@extends('layouts.app')

@section('title', $centre->centre_name . ' - Centre Details')

@section('content')
    <div class="enhanced-centre-details-container">
        {{-- Enhanced Header Section --}}
        <div class="centre-header-section">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('centres.index') }}" class="breadcrumb-link">Centre Management</a>
                        <i class="fas fa-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">{{ $centre->centre_name }}</span>
                    </div>
                    <div class="centre-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-building gradient-icon"></i>
                            {{ $centre->centre_name }}
                        </h1>
                        <div class="centre-meta">
                            <span class="centre-id">ID: {{ $centre->centre_id }}</span>
                            <span class="status-badge status-{{ $centre->centre_status ?? 'unknown' }}">
                                <i class="fas fa-circle"></i>
                                {{ ucfirst($centre->centre_status ?? 'Unknown') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="action-group">
                        @if (in_array(session('role'), ['admin', 'supervisor']))
                            <a href="{{ route('centres.edit', $centre->centre_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Edit Centre
                            </a>
                        @endif
                        <a href="{{ route('centres.assets', $centre->centre_id) }}" class="btn btn-info">
                            <i class="fas fa-boxes"></i>
                            Manage Assets
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                                More Actions
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item"
                                    href="{{ route('centres.attendance.index') }}?centre={{ $centre->centre_id }}">
                                    <i class="fas fa-clock"></i> View Attendance
                                </a>
                                <a class="dropdown-item" href="#" onclick="generateReport()">
                                    <i class="fas fa-chart-line"></i> Generate Report
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportData()">
                                    <i class="fas fa-download"></i> Export Data
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="printView()">
                                    <i class="fas fa-print"></i> Print Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enhanced Statistics Dashboard --}}
            <div class="statistics-dashboard">
                <div class="stats-grid">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats->total_staff ?? 0 }}</div>
                            <div class="stat-label">Total Staff</div>
                            <div class="stat-detail">
                                <i class="fas fa-user-tie"></i>
                                <span>{{ $stats->active_staff ?? 0 }} active</span>
                            </div>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up text-success"></i>
                            <span>+5% this month</span>
                        </div>
                    </div>

                    <div class="stat-card stat-success">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats->total_trainees ?? 0 }}</div>
                            <div class="stat-label">Total Trainees</div>
                            <div class="stat-detail">
                                <i class="fas fa-chart-pie"></i>
                                <span>{{ number_format((($stats->total_trainees ?? 0) / max($centre->centre_capacity ?? 1, 1)) * 100, 1) }}%
                                    capacity</span>
                            </div>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up text-success"></i>
                            <span>+12 this week</span>
                        </div>
                    </div>

                    <div class="stat-card stat-info">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats->total_assets ?? 0 }}</div>
                            <div class="stat-label">Total Assets</div>
                            <div class="stat-detail">
                                <i class="fas fa-tools"></i>
                                <span>{{ $stats->functional_assets ?? 0 }} functional</span>
                            </div>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <span>{{ $stats->maintenance_assets ?? 0 }} need maintenance</span>
                        </div>
                    </div>

                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats->active_activities ?? 0 }}</div>
                            <div class="stat-label">Active Activities</div>
                            <div class="stat-detail">
                                <i class="fas fa-clock"></i>
                                <span>{{ $stats->scheduled_sessions ?? 0 }} sessions this week</span>
                            </div>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-calendar-plus text-info"></i>
                            <span>{{ $stats->upcoming_sessions ?? 0 }} upcoming</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="main-content-grid">
            {{-- Left Column --}}
            <div class="left-column">
                {{-- Centre Information Card --}}
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Centre Information</h3>
                        <span class="last-updated">Updated
                            {{ $centre->updated_at ? $centre->updated_at->diffForHumans() : 'recently' }}</span>
                    </div>
                    <div class="card-content">
                        @if ($centre->centre_image || isset($centre_images))
                            <div class="centre-gallery">
                                <div id="centreCarousel" class="carousel slide" data-ride="carousel">
                                    <div class="carousel-inner">
                                        @php
                                            $images = [];
                                            if ($centre->centre_image) {
                                                $images[] = $centre->centre_image;
                                            }
                                            if (isset($centre_images)) {
                                                $images = array_merge($images, $centre_images);
                                            }
                                            if (empty($images)) {
                                                $images = ['default-centre.jpg'];
                                            }
                                        @endphp

                                        @foreach ($images as $index => $image)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                <img class="d-block w-100 carousel-image"
                                                    src="{{ file_exists(public_path('storage/centres/' . $image)) ? asset('storage/centres/' . $image) : asset('images/centre-placeholder.jpg') }}"
                                                    alt="{{ $centre->centre_name }} - Image {{ $index + 1 }}"
                                                    onerror="this.src='{{ asset('images/centre-placeholder.jpg') }}'">
                                            </div>
                                        @endforeach
                                    </div>

                                    @if (count($images) > 1)
                                        <a class="carousel-control-prev" href="#centreCarousel" role="button"
                                            data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        </a>
                                        <a class="carousel-control-next" href="#centreCarousel" role="button"
                                            data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        </a>
                                    @endif
                                </div>
                                <button class="fullscreen-btn" onclick="openFullscreen()">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        @endif

                        <div class="centre-details-grid">
                            <div class="detail-section">
                                <h4><i class="fas fa-building"></i> Basic Information</h4>
                                <div class="detail-items">
                                    <div class="detail-item">
                                        <label>Centre Name:</label>
                                        <span>{{ $centre->centre_name }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Centre ID:</label>
                                        <span class="monospace">{{ $centre->centre_id }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status:</label>
                                        <span class="status-badge status-{{ $centre->centre_status ?? 'unknown' }}">
                                            {{ ucfirst($centre->centre_status ?? 'Unknown') }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Type:</label>
                                        <span>{{ ucfirst($centre->centre_type ?? 'General') }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Capacity:</label>
                                        <span class="capacity-display">
                                            {{ $centre->centre_capacity ?? 'Not specified' }}
                                            @if ($centre->centre_capacity)
                                                <small>({{ number_format((($stats->total_trainees ?? 0) / $centre->centre_capacity) * 100, 1) }}%
                                                    utilized)</small>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if ($centre->centre_description)
                                <div class="detail-section">
                                    <h4><i class="fas fa-file-alt"></i> Description</h4>
                                    <div class="description-content">
                                        <p>{{ $centre->centre_description }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="detail-section">
                                <h4><i class="fas fa-map-marker-alt"></i> Location Details</h4>
                                <div class="detail-items">
                                    <div class="detail-item">
                                        <label>Address:</label>
                                        <span>{{ $centre->centre_address ?? 'Not specified' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>City:</label>
                                        <span>{{ $centre->centre_city ?? 'Not specified' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>State:</label>
                                        <span>{{ $centre->centre_state ?? 'Not specified' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Postcode:</label>
                                        <span class="monospace">{{ $centre->centre_postcode ?? 'Not specified' }}</span>
                                    </div>
                                    @if ($centre->centre_region)
                                        <div class="detail-item">
                                            <label>Region:</label>
                                            <span>{{ $centre->centre_region }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4><i class="fas fa-address-book"></i> Contact Information</h4>
                                <div class="detail-items">
                                    <div class="detail-item">
                                        <label>Phone:</label>
                                        <span>
                                            @if ($centre->centre_phone)
                                                <a href="tel:{{ $centre->centre_phone }}" class="contact-link">
                                                    <i class="fas fa-phone"></i>
                                                    {{ $centre->centre_phone }}
                                                </a>
                                            @else
                                                Not provided
                                            @endif
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Email:</label>
                                        <span>
                                            @if ($centre->centre_email)
                                                <a href="mailto:{{ $centre->centre_email }}" class="contact-link">
                                                    <i class="fas fa-envelope"></i>
                                                    {{ $centre->centre_email }}
                                                </a>
                                            @else
                                                Not provided
                                            @endif
                                        </span>
                                    </div>
                                    @if ($centre->centre_fax)
                                        <div class="detail-item">
                                            <label>Fax:</label>
                                            <span class="monospace">{{ $centre->centre_fax }}</span>
                                        </div>
                                    @endif
                                    @if ($centre->centre_website)
                                        <div class="detail-item">
                                            <label>Website:</label>
                                            <span>
                                                <a href="{{ $centre->centre_website }}" target="_blank"
                                                    class="contact-link">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    {{ $centre->centre_website }}
                                                </a>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4><i class="fas fa-user-tie"></i> Management</h4>
                                <div class="detail-items">
                                    <div class="detail-item">
                                        <label>Centre Manager:</label>
                                        <span>{{ $centre->centre_manager ?? 'Not assigned' }}</span>
                                    </div>
                                    @if ($centre->centre_manager_contact)
                                        <div class="detail-item">
                                            <label>Manager Contact:</label>
                                            <span>
                                                <a href="tel:{{ $centre->centre_manager_contact }}" class="contact-link">
                                                    <i class="fas fa-mobile-alt"></i>
                                                    {{ $centre->centre_manager_contact }}
                                                </a>
                                            </span>
                                        </div>
                                    @endif
                                    @if ($centre->centre_supervisor)
                                        <div class="detail-item">
                                            <label>Clinical Supervisor:</label>
                                            <span>{{ $centre->centre_supervisor }}</span>
                                        </div>
                                    @endif
                                    @if ($centre->centre_license_number)
                                        <div class="detail-item">
                                            <label>License Number:</label>
                                            <span class="monospace">{{ $centre->centre_license_number }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                                <div class="operating-hours">
                                    <div class="hours-display">
                                        <div class="hours-item">
                                            <i class="fas fa-sun"></i>
                                            <span>Opens:
                                                {{ $centre->opening_time ? \Carbon\Carbon::parse($centre->opening_time)->format('g:i A') : 'Not specified' }}</span>
                                        </div>
                                        <div class="hours-item">
                                            <i class="fas fa-moon"></i>
                                            <span>Closes:
                                                {{ $centre->closing_time ? \Carbon\Carbon::parse($centre->closing_time)->format('g:i A') : 'Not specified' }}</span>
                                        </div>
                                    </div>
                                    @if ($centre->operating_days)
                                        <div class="operating-days">
                                            <label>Operating Days:</label>
                                            <div class="days-list">
                                                @foreach (json_decode($centre->operating_days, true) ?? [] as $day)
                                                    <span class="day-badge">{{ ucfirst($day) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($centre->centre_facilities)
                                <div class="detail-section">
                                    <h4><i class="fas fa-tools"></i> Facilities & Services</h4>
                                    <div class="facilities-content">
                                        <p>{{ $centre->centre_facilities }}</p>
                                    </div>
                                    @if ($centre->specialized_services)
                                        <div class="specialized-services">
                                            <label>Specialized Services:</label>
                                            <div class="services-list">
                                                @foreach (json_decode($centre->specialized_services, true) ?? [] as $service)
                                                    <span
                                                        class="service-badge">{{ ucwords(str_replace('_', ' ', $service)) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Recent Activities Card --}}
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-check"></i> Recent Activities</h3>
                        <a href="{{ route('activities.index') }}?centre={{ $centre->centre_id }}"
                            class="view-all-link">View All</a>
                    </div>
                    <div class="card-content">
                        @if (isset($recentActivities) && count($recentActivities) > 0)
                            <div class="activities-timeline">
                                @foreach ($recentActivities as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="activity-header">
                                                <h5>
                                                    <a href="{{ route('activities.show', $activity->id) }}">
                                                        {{ $activity->activity_name ?? 'Unknown Activity' }}
                                                    </a>
                                                </h5>
                                                <span class="activity-date">
                                                    {{ $activity->created_at ? $activity->created_at->format('M d, Y') : 'Unknown date' }}
                                                </span>
                                            </div>
                                            <p class="activity-description">
                                                {{ Str::limit($activity->activity_description ?? 'No description available', 100) }}
                                            </p>
                                            <div class="activity-meta">
                                                <span class="activity-status status-{{ $activity->status ?? 'active' }}">
                                                    {{ ucfirst($activity->status ?? 'Active') }}
                                                </span>
                                                <span class="activity-participants">
                                                    <i class="fas fa-users"></i>
                                                    {{ $activity->participants_count ?? 0 }} participants
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h4>No Recent Activities</h4>
                                <p>No activities have been scheduled for this centre recently.</p>
                                @if (in_array(session('role'), ['admin', 'supervisor']))
                                    <a href="{{ route('activities.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i>
                                        Schedule Activity
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="right-column">
                {{-- Performance Analytics Card --}}
                <div class="analytics-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Performance Analytics</h3>
                        <div class="time-selector">
                            <select id="analyticsTimeframe" class="form-control form-control-sm">
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="analytics-grid">
                            <div class="metric-item">
                                <div class="metric-icon">
                                    <i class="fas fa-percentage text-success"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value">{{ number_format($stats->utilization_rate ?? 0, 1) }}%
                                    </div>
                                    <div class="metric-label">Utilization Rate</div>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +3.2% from last month
                                    </div>
                                </div>
                            </div>

                            <div class="metric-item">
                                <div class="metric-icon">
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value">{{ number_format($stats->satisfaction_rate ?? 0, 1) }}
                                    </div>
                                    <div class="metric-label">Satisfaction Score</div>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +0.3 from last month
                                    </div>
                                </div>
                            </div>

                            <div class="metric-item">
                                <div class="metric-icon">
                                    <i class="fas fa-clock text-info"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value">{{ $stats->attendance_rate ?? 0 }}%</div>
                                    <div class="metric-label">Attendance Rate</div>
                                    <div class="metric-change negative">
                                        <i class="fas fa-arrow-down"></i>
                                        -1.5% from last month
                                    </div>
                                </div>
                            </div>

                            <div class="metric-item">
                                <div class="metric-icon">
                                    <i class="fas fa-trophy text-primary"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value">{{ $stats->completion_rate ?? 0 }}%</div>
                                    <div class="metric-label">Program Completion</div>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +5.1% from last month
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Chart Container --}}
                        <div class="chart-container">
                            <canvas id="performanceChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Asset Overview Card --}}
                <div class="analytics-card">
                    <div class="card-header">
                        <h3><i class="fas fa-boxes"></i> Asset Overview</h3>
                        <a href="{{ route('centres.assets', $centre->centre_id) }}" class="view-all-link">Manage
                            Assets</a>
                    </div>
                    <div class="card-content">
                        @if (($stats->total_assets ?? 0) > 0)
                            <div class="asset-summary">
                                <div class="asset-stats">
                                    <div class="asset-stat">
                                        <div class="stat-value">{{ $stats->total_assets ?? 0 }}</div>
                                        <div class="stat-label">Total Assets</div>
                                    </div>
                                    <div class="asset-stat">
                                        <div class="stat-value text-success">{{ $stats->functional_assets ?? 0 }}</div>
                                        <div class="stat-label">Functional</div>
                                    </div>
                                    <div class="asset-stat">
                                        <div class="stat-value text-warning">{{ $stats->maintenance_assets ?? 0 }}</div>
                                        <div class="stat-label">Maintenance</div>
                                    </div>
                                    <div class="asset-stat">
                                        <div class="stat-value text-danger">{{ $stats->broken_assets ?? 0 }}</div>
                                        <div class="stat-label">Broken</div>
                                    </div>
                                </div>

                                {{-- Asset Distribution Chart --}}
                                <div class="asset-chart">
                                    <canvas id="assetChart" width="300" height="150"></canvas>
                                </div>
                            </div>

                            @if (isset($recentAssets) && count($recentAssets) > 0)
                                <div class="recent-assets">
                                    <h4>Recent Asset Updates</h4>
                                    <div class="asset-list">
                                        @foreach ($recentAssets as $asset)
                                            <div class="asset-item">
                                                <div class="asset-icon">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="asset-info">
                                                    <div class="asset-name">{{ $asset->asset_name ?? 'Unknown Asset' }}
                                                    </div>
                                                    <div class="asset-status">
                                                        Status: <span
                                                            class="status-{{ $asset->status ?? 'unknown' }}">{{ ucfirst($asset->status ?? 'Unknown') }}</span>
                                                    </div>
                                                </div>
                                                <div class="asset-date">
                                                    {{ $asset->updated_at ? $asset->updated_at->format('M d') : 'Unknown' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h4>No Assets Registered</h4>
                                <p>No assets have been registered for this centre yet.</p>
                                @if (in_array(session('role'), ['admin', 'supervisor']))
                                    <a href="{{ route('centres.assets', $centre->centre_id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i>
                                        Add Assets
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions Card --}}
                @if (in_array(session('role'), ['admin', 'supervisor']))
                    <div class="quick-actions-card">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-content">
                            <div class="actions-grid">
                                <a href="{{ route('centres.edit', $centre->centre_id) }}" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="action-content">
                                        <div class="action-title">Edit Centre</div>
                                        <div class="action-description">Update centre information</div>
                                    </div>
                                </a>

                                <a href="{{ route('centres.assets', $centre->centre_id) }}" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="action-content">
                                        <div class="action-title">Manage Assets</div>
                                        <div class="action-description">View and manage centre assets</div>
                                    </div>
                                </a>

                                <a href="{{ route('activities.create') }}?centre={{ $centre->centre_id }}"
                                    class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                    <div class="action-content">
                                        <div class="action-title">Create Activity</div>
                                        <div class="action-description">Schedule new activity</div>
                                    </div>
                                </a>

                                <a href="#" onclick="generateReport()" class="action-item">
                                    <div class="action-icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="action-content">
                                        <div class="action-title">Generate Report</div>
                                        <div class="action-description">Create performance report</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Fullscreen Modal for Images --}}
    <div id="fullscreenModal" class="fullscreen-modal">
        <div class="fullscreen-close" onclick="closeFullscreen()">
            <i class="fas fa-times"></i>
        </div>
        <div id="fullscreenCarousel" class="carousel slide fullscreen-carousel" data-ride="carousel">
            <!-- Content will be dynamically populated -->
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/centre-details-enhanced.css') }}">
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/centre-details-enhanced.js') }}"></script>
    <script>
        // Initialize enhanced centre details
        document.addEventListener('DOMContentLoaded', function() {
            const centreDetails = new EnhancedCentreDetails({
                centreId: '{{ $centre->centre_id }}',
                centreName: '{{ $centre->centre_name }}',
                stats: @json($stats ?? []),
                userRole: '{{ session('role') }}'
            });

            // Make globally accessible
            window.centreDetails = centreDetails;
        });

        // Global action functions
        function generateReport() {
            if (window.centreDetails) {
                window.centreDetails.generateReport();
            }
        }

        function exportData() {
            if (window.centreDetails) {
                window.centreDetails.exportData();
            }
        }

        function printView() {
            window.print();
        }

        function openFullscreen() {
            if (window.centreDetails) {
                window.centreDetails.openFullscreen();
            }
        }

        function closeFullscreen() {
            if (window.centreDetails) {
                window.centreDetails.closeFullscreen();
            }
        }
    </script>
@endsection
