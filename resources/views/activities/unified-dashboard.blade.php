@extends('layouts.app')

@section('title', 'Activity Management')

@push('styles')
<style>
    .activity-dashboard {
        background: #f5f6fa;
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: #f0f3ff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 24px;
        color: #667eea;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #718096;
        font-size: 14px;
    }
    
    .activity-filters {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .filter-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }
    
    .filter-group .form-group {
        flex: 1;
        min-width: 200px;
        margin-bottom: 0;
    }
    
    .activity-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }
    
    .activity-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .activity-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .activity-header {
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .activity-code {
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 5px;
    }
    
    .activity-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .activity-category {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 12px;
    }
    
    .activity-body {
        padding: 20px;
    }
    
    .activity-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 14px;
        color: #718096;
    }
    
    .activity-sessions {
        border-top: 1px solid #e2e8f0;
        padding-top: 15px;
        margin-top: 15px;
    }
    
    .session-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 13px;
    }
    
    .session-date {
        color: #4a5568;
        font-weight: 500;
    }
    
    .session-status {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .session-status.scheduled {
        background: #e6fffa;
        color: #047857;
    }
    
    .session-status.ongoing {
        background: #fef3c7;
        color: #92400e;
    }
    
    .session-status.completed {
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .activity-actions {
        padding: 15px 20px;
        background: #f7fafc;
        display: flex;
        gap: 10px;
    }
    
    .btn-activity {
        flex: 1;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-view {
        background: #4299e1;
        color: white;
    }
    
    .btn-view:hover {
        background: #3182ce;
    }
    
    .btn-schedule {
        background: #48bb78;
        color: white;
    }
    
    .btn-schedule:hover {
        background: #38a169;
    }
    
    .upcoming-sessions {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-top: 30px;
    }
    
    .sessions-timeline {
        margin-top: 20px;
    }
    
    .timeline-item {
        display: flex;
        gap: 20px;
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .timeline-item:last-child {
        border-bottom: none;
    }
    
    .timeline-date {
        min-width: 100px;
        text-align: right;
        color: #718096;
        font-size: 14px;
    }
    
    .timeline-content {
        flex: 1;
    }
    
    .timeline-activity {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .timeline-details {
        font-size: 13px;
        color: #718096;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-icon {
        font-size: 64px;
        color: #cbd5e0;
        margin-bottom: 20px;
    }
    
    .empty-title {
        font-size: 20px;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 10px;
    }
    
    .empty-text {
        color: #718096;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="activity-dashboard">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">Activity Management</h1>
                <p class="text-muted">Manage rehabilitation and academic activities</p>
            </div>
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <button class="btn btn-primary" onclick="createActivity()">
                    <i class="fas fa-plus mr-2"></i>Create Activity
                </button>
            @endif
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value">{{ $stats['total_activities'] ?? 0 }}</div>
                <div class="stat-label">Total Activities</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ $stats['active_activities'] ?? 0 }}</div>
                <div class="stat-label">Active Activities</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value">{{ $stats['today_sessions'] ?? 0 }}</div>
                <div class="stat-label">Today's Sessions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $stats['active_trainees'] ?? 0 }}</div>
                <div class="stat-label">Active Trainees</div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="activity-filters">
            <form method="GET" action="{{ route('activities.index') }}" class="filter-group">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $groupName => $group)
                            <optgroup label="{{ $groupName }}">
                                @foreach($group as $category => $meta)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Activity name or code..." 
                           value="{{ request('search') }}">
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
                
                @if(request()->hasAny(['category', 'search']))
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Activities Grid -->
        @if($activities->count() > 0)
            <div class="activity-grid">
                @foreach($activities as $activity)
                    <div class="activity-card">
                        <div class="activity-header">
                            <div class="activity-code">{{ $activity->activity_code }}</div>
                            <div class="activity-name">{{ $activity->name }}</div>
                            <span class="activity-category">{{ $activity->category }}</span>
                        </div>
                        
                        <div class="activity-body">
                            <div class="activity-info">
                                <span><i class="fas fa-layer-group mr-1"></i> {{ $activity->difficulty_level }}</span>
                                <span><i class="fas fa-users mr-1"></i> Max: {{ $activity->max_participants }}</span>
                                <span><i class="fas fa-clock mr-1"></i> {{ $activity->formatted_duration }}</span>
                            </div>
                            
                            @if($activity->description)
                                <p class="text-muted small mb-3">{{ Str::limit($activity->description, 100) }}</p>
                            @endif
                            
                            @if($activity->sessions->count() > 0)
                                <div class="activity-sessions">
                                    <h6 class="mb-2">Recent Sessions</h6>
                                    @foreach($activity->sessions->take(3) as $session)
                                        <div class="session-item">
                                            <span class="session-date">
                                                {{ Carbon\Carbon::parse($session->session_date)->format('d M') }}
                                            </span>
                                            <span class="session-status {{ $session->status }}">
                                                {{ ucfirst($session->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <div class="activity-actions">
                            <button class="btn-activity btn-view" onclick="viewActivity({{ $activity->id }})">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            @if(in_array(session('role'), ['admin', 'supervisor']))
                                <button class="btn-activity btn-schedule" onclick="scheduleSession({{ $activity->id }})">
                                    <i class="fas fa-calendar-plus mr-1"></i> Schedule
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $activities->appends(request()->query())->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="empty-title">No Activities Found</div>
                <div class="empty-text">
                    @if(request()->hasAny(['category', 'search']))
                        No activities match your search criteria.
                    @else
                        Start by creating your first activity.
                    @endif
                </div>
                @if(in_array(session('role'), ['admin', 'supervisor']))
                    <button class="btn btn-primary" onclick="createActivity()">
                        <i class="fas fa-plus mr-2"></i>Create First Activity
                    </button>
                @endif
            </div>
        @endif

        <!-- Upcoming Sessions -->
        @if(isset($upcomingSessions) && $upcomingSessions->count() > 0)
            <div class="upcoming-sessions">
                <h3 class="mb-3">Upcoming Sessions</h3>
                <div class="sessions-timeline">
                    @foreach($upcomingSessions as $session)
                        <div class="timeline-item">
                            <div class="timeline-date">
                                {{ Carbon\Carbon::parse($session->session_date)->format('d M Y') }}<br>
                                <small>{{ Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</small>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-activity">{{ $session->activity->name }}</div>
                                <div class="timeline-details">
                                    <i class="fas fa-user mr-1"></i> {{ $session->teacher->name ?? 'Unassigned' }} • 
                                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $session->venue }} • 
                                    <i class="fas fa-users mr-1"></i> {{ $session->enrollments->count() }}/{{ $session->capacity }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modals will be added here -->
@endsection

@push('scripts')
<script>
// Activity management functions
function createActivity() {
    // Open create activity modal
    $('#createActivityModal').modal('show');
}

function viewActivity(id) {
    window.location.href = '/activities/' + id;
}

function scheduleSession(id) {
    // Open schedule session modal
    $('#scheduleSessionModal').modal('show');
    $('#schedule_activity_id').val(id);
}
</script>
@endpush