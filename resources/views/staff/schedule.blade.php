@extends('layouts.app')

@section('title')
{{ $staffMember->name }} - Schedule | CREAMS
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #25a6cf;
        --success-color: #1cc88a;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .schedule-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .schedule-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .schedule-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .day-header {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        font-weight: 600;
        text-align: center;
    }

    .schedule-item {
        background: var(--light-bg);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .schedule-item:hover {
        background: white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .time-badge {
        background: var(--success-color);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .activity-title {
        font-weight: 600;
        color: var(--dark-color);
        margin: 0.5rem 0;
    }

    .no-schedule {
        text-align: center;
        color: #6c757d;
        padding: 3rem;
        background: var(--light-bg);
        border-radius: 10px;
        border: 2px dashed var(--border-color);
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teachershome') }}">Staff Directory</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.view', $staffMember->id) }}">{{ $staffMember->name }}</a></li>
            <li class="breadcrumb-item active">Schedule</li>
        </ol>
    </nav>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Header -->
    <div class="schedule-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-calendar me-3"></i>{{ $staffMember->name }}'s Schedule
                </h1>
                <p class="mb-0 opacity-75">Weekly activity schedule and commitments</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('staff.view', $staffMember->id) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Weekly Schedule -->
        <div class="col-lg-8">
            <div class="schedule-card">
                <h3 class="mb-4">
                    <i class="fas fa-clock me-2 text-primary"></i>Weekly Schedule
                </h3>

                @if(count($schedules) > 0)
                    @php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        $groupedSchedules = collect($schedules)->groupBy('day_of_week');
                    @endphp

                    @foreach($days as $index => $day)
                        <div class="day-header">{{ $day }}</div>
                        
                        @if(isset($groupedSchedules[$index + 1]))
                            @foreach($groupedSchedules[$index + 1] as $schedule)
                                <div class="schedule-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="time-badge">
                                                {{ date('g:i A', strtotime($schedule->start_time)) }} - 
                                                {{ date('g:i A', strtotime($schedule->end_time)) }}
                                            </span>
                                            <div class="activity-title">{{ $schedule->activity_name }}</div>
                                            @if(isset($schedule->location))
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $schedule->location }}
                                                </small>
                                            @endif
                                        </div>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                <p>No activities scheduled</p>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="no-schedule">
                        <i class="fas fa-calendar-plus fa-3x mb-3 text-muted"></i>
                        <h4>No Schedule Available</h4>
                        <p class="mb-0">No activities have been scheduled for this staff member yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activities Summary -->
        <div class="col-lg-4">
            <div class="schedule-card">
                <h3 class="mb-4">
                    <i class="fas fa-tasks me-2 text-primary"></i>Activities Summary
                </h3>

                @if(count($activities) > 0)
                    @foreach($activities as $activity)
                        <div class="schedule-item">
                            <div class="activity-title">{{ $activity->activity_name }}</div>
                            <small class="text-muted">{{ $activity->category ?? 'General' }}</small>
                            <div class="mt-2">
                                <span class="badge bg-primary">{{ $activity->duration_minutes ?? 60 }} min</span>
                                @if($activity->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <p>No activities assigned</p>
                    </div>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="schedule-card">
                <h3 class="mb-4">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Weekly Stats
                </h3>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-primary mb-1">{{ count($schedules) }}</div>
                            <small class="text-muted">Scheduled Sessions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-success mb-1">{{ count($activities) }}</div>
                            <small class="text-muted">Total Activities</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-center">
                            @php
                                $totalMinutes = collect($schedules)->sum(function($schedule) {
                                    return (strtotime($schedule->end_time) - strtotime($schedule->start_time)) / 60;
                                });
                                $totalHours = round($totalMinutes / 60, 1);
                            @endphp
                            <div class="h4 text-warning mb-1">{{ $totalHours }}h</div>
                            <small class="text-muted">Weekly Hours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection