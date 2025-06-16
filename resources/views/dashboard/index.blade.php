{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', ucfirst($role) . ' Dashboard - CREAMS')

@section('content')
<div class="dashboard-container" data-role="{{ $role }}">
    {{-- Dashboard Header --}}
    <div class="dashboard-header">
        <div class="dashboard-welcome">
            <h1 class="dashboard-title">Welcome back, <span class="user-name">{{ session('name') }}</span></h1>
            <p class="dashboard-subtitle">{{ ucfirst($role) }} Dashboard - {{ Carbon\Carbon::now()->format('l, F j, Y') }}</p>
        </div>
        <div class="dashboard-actions">
            <button class="btn-refresh" onclick="DashboardManager.refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn-customize" onclick="DashboardManager.openCustomization()">
                <i class="fas fa-cog"></i> Customize
            </button>
        </div>
    </div>

    {{-- System Alerts (if any) --}}
    @if(isset($systemAlerts) && count($systemAlerts) > 0)
    <div class="system-alerts">
        @foreach($systemAlerts as $alert)
        <div class="alert alert-{{ $alert['type'] ?? 'info' }} alert-dismissible">
            <i class="fas fa-{{ $alert['icon'] ?? 'info-circle' }}"></i>
            <span>{{ $alert['message'] ?? 'System notification' }}</span>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Quick Stats Section --}}
    <div class="stats-section">
        @switch($role)
            @case('admin')
                @include('dashboard.partials.admin-stats', ['stats' => $stats])
                @break
            @case('supervisor')
                @include('dashboard.partials.supervisor-stats', ['stats' => $stats])
                @break
            @case('teacher')
                @include('dashboard.partials.teacher-stats', ['stats' => $stats])
                @break
            @case('trainee')
                @include('dashboard.partials.trainee-stats', ['stats' => $stats])
                @break
            @case('ajk')
                @include('dashboard.partials.ajk-stats', ['stats' => $stats])
                @break
            @case('parent')
                @include('dashboard.partials.parent-stats', ['stats' => $stats])
                @break
        @endswitch
    </div>

    {{-- Main Dashboard Content --}}
    <div class="dashboard-content">
        <div class="row">
            {{-- Left Column - Main Content --}}
            <div class="col-lg-8">
                @switch($role)
                    @case('admin')
                        @include('dashboard.content.admin-main', compact('charts', 'recentActivities'))
                        @break
                    @case('supervisor')
                        @include('dashboard.content.supervisor-main', compact('charts', 'teacherManagement'))
                        @break
                    @case('teacher')
                        @include('dashboard.content.teacher-main', compact('todaySchedule', 'studentList', 'charts'))
                        @break
                    @case('trainee')
                        @include('dashboard.content.trainee-main', compact('myActivities', 'weeklySchedule', 'charts'))
                        @break
                    @case('ajk')
                        @include('dashboard.content.ajk-main', compact('upcomingEvents', 'volunteerApplications', 'charts'))
                        @break
                    @case('parent')
                        @include('dashboard.content.parent-main', compact('childProgress', 'teacherFeedback', 'charts'))
                        @break
                @endswitch
            </div>

            {{-- Right Column - Sidebar Widgets --}}
            <div class="col-lg-4">
                {{-- Notifications Widget --}}
                @include('dashboard.widgets.notifications', ['notifications' => $notifications])

                {{-- Quick Actions Widget --}}
                @include('dashboard.widgets.quick-actions', ['quickActions' => $quickActions])

                {{-- Calendar Widget --}}
                @include('dashboard.widgets.calendar')

                {{-- System Health Widget (Admin only) --}}
                @if($role === 'admin' && isset($systemHealth))
                    @include('dashboard.widgets.system-health', ['health' => $systemHealth])
                @endif

                {{-- Role-specific widgets --}}
                @switch($role)
                    @case('supervisor')
                        @include('dashboard.widgets.pending-approvals', ['count' => $stats['pending_approvals'] ?? 0])
                        @break
                    @case('teacher')
                        @include('dashboard.widgets.attendance-reminder', ['pending' => $stats['pending_attendance'] ?? 0])
                        @break
                    @case('trainee')
                        @include('dashboard.widgets.achievements', ['achievements' => $stats['achievements'] ?? []])
                        @break
                @endswitch
            </div>
        </div>
    </div>

    {{-- Chart Containers (Hidden, populated by JS) --}}
    <div class="chart-containers hidden">
        @foreach($charts ?? [] as $chartId => $chartData)
            <div id="chart-{{ $chartId }}" class="chart-container" data-chart='@json($chartData)'></div>
        @endforeach
    </div>
</div>

{{-- Dashboard Customization Modal --}}
<div class="modal fade" id="dashboardCustomizationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customize Dashboard</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('dashboard.partials.customization-options')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="DashboardManager.saveCustomization()">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-widgets.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-charts.css') }}">
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('js/dashboard-manager.js') }}"></script>
<script src="{{ asset('js/dashboard-charts.js') }}"></script>
<script src="{{ asset('js/dashboard-widgets.js') }}"></script>
<script>
    // Initialize dashboard with server data
    document.addEventListener('DOMContentLoaded', function() {
        DashboardManager.init({
            role: '{{ $role }}',
            userId: '{{ $userId ?? session("id") }}',
            refreshInterval: 300000, // 5 minutes
            apiEndpoints: {
                refresh: '{{ route("dashboard.refresh") }}',
                customize: '{{ route("dashboard.customize") }}',
                notifications: '{{ route("notifications.fetch") }}'
            }
        });
    });
</script>
@endsection