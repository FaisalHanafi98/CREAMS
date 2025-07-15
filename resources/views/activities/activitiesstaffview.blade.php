{{-- activities/ajk-view.blade.php --}}
@extends('layouts.app')

@section('title', 'Activities Overview - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tasks"></i> Activities Overview
        </h1>
        <div class="page-actions">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_activities'] }}</h3>
                    <p>Total Activities</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['active_activities'] }}</h3>
                    <p>Active Activities</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_sessions'] }}</h3>
                    <p>Active Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_enrollments'] }}</h3>
                    <p>Total Enrollments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Overview -->
    <div class="card">
        <div class="card-header">
            <h5 class="m-0">Activities Overview</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Activity Name</th>
                            <th>Category</th>
                            <th>Age Group</th>
                            <th>Difficulty</th>
                            <th>Sessions</th>
                            <th>Enrollments</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>{{ $activity->activity_code }}</td>
                                <td>
                                    <strong>{{ $activity->activity_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($activity->description, 50) }}</small>
                                </td>
                                <td><span class="badge badge-info">{{ $activity->category }}</span></td>
                                <td>{{ $activity->age_group }}</td>
                                <td>
                                    <span class="badge badge-{{ $activity->difficulty_level == 'Beginner' ? 'success' : ($activity->difficulty_level == 'Intermediate' ? 'warning' : 'danger') }}">
                                        {{ $activity->difficulty_level }}
                                    </span>
                                </td>
                                <td>{{ $activity->activeSessions->count() }}</td>
                                <td>{{ $activity->activeSessions->sum('current_enrollment') }}</td>
                                <td>
                                    @if($activity->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No activities found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activity Categories Summary -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">Activities by Category</h5>
                </div>
                <div class="card-body">
                    @php
                        $categoryCounts = $activities->groupBy('category')->map->count();
                    @endphp
                    @forelse($categoryCounts as $category => $count)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="font-weight-medium">{{ $category }}</span>
                            <span class="badge badge-primary">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-muted">No categories available.</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">Activities by Difficulty</h5>
                </div>
                <div class="card-body">
                    @php
                        $difficultyCounts = $activities->groupBy('difficulty_level')->map->count();
                    @endphp
                    @forelse($difficultyCounts as $difficulty => $count)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="font-weight-medium">{{ $difficulty }}</span>
                            <span class="badge badge-{{ $difficulty == 'Beginner' ? 'success' : ($difficulty == 'Intermediate' ? 'warning' : 'danger') }}">
                                {{ $count }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted">No difficulty levels available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Schedule Overview -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="m-0">Weekly Schedule Overview</h5>
        </div>
        <div class="card-body">
            @php
                $allSessions = $activities->flatMap->activeSessions;
                $sessionsByDay = $allSessions->groupBy('day_of_week');
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            @endphp
            
            <div class="row">
                @foreach($days as $day)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <h6 class="card-title">{{ $day }}</h6>
                                <p class="card-text">
                                    <strong>{{ $sessionsByDay->get($day, collect())->count() }}</strong> sessions
                                </p>
                                @if($sessionsByDay->has($day))
                                    <small class="text-muted">
                                        {{ $sessionsByDay[$day]->sum('current_enrollment') }} total enrollments
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection