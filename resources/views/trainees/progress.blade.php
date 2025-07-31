@extends('layouts.app')

@section('title', 'Trainee Progress - ' . $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h1>
                    <p class="text-muted">Academic Progress Overview</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('trainees.progress.schedule', encrypt($trainee->id)) }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar"></i> Weekly Schedule
                    </a>
                    <a href="{{ route('trainees.home') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Trainees
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $overallStats['average_progress'] }}%</h5>
                            <p class="card-text">Average Progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $overallStats['passing_activities'] }}</h5>
                            <p class="card-text">Passing Activities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $overallStats['at_risk_activities'] }}</h5>
                            <p class="card-text">At Risk Activities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $overallStats['current_activities'] }}</h5>
                            <p class="card-text">Current Activities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Activities -->
    @if(count($currentActivities) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Current Activities</h4>
            @foreach($currentActivities as $item)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="card-title mb-1">
                                <a href="{{ route('activities.show', $item['activity']->id) }}">
                                    {{ $item['activity']->activity_name }}
                                </a>
                            </h5>
                            <p class="text-muted mb-2">{{ $item['activity']->activity_type }}</p>
                            <small class="text-muted">
                                {{ $item['attended_sessions'] }}/{{ $item['total_sessions'] }} sessions attended
                            </small>
                        </div>
                        <div class="col-md-4">
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar {{ $item['pass_status'] === 'passing' ? 'bg-success' : 'bg-warning' }}" 
                                     role="progressbar" 
                                     style="width: {{ $item['progress'] }}%"
                                     aria-valuenow="{{ $item['progress'] }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $item['progress'] }}%
                                </div>
                            </div>
                            <small class="text-muted">
                                Pass Threshold: {{ $item['activity']->pass_threshold }}%
                            </small>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-end">
                                <span class="badge {{ $item['pass_status'] === 'passing' ? 'bg-success' : 'bg-warning' }} me-2">
                                    {{ $item['pass_status'] === 'passing' ? 'Passing' : 'At Risk' }}
                                </span>
                                <a href="{{ route('activities.show', $item['activity']->id) }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Past Activities -->
    @if(count($pastActivities) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Completed Activities</h4>
            @foreach($pastActivities as $item)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-1">{{ $item['activity']->activity_name }}</h5>
                            <p class="text-muted mb-0">{{ $item['activity']->activity_type }}</p>
                        </div>
                        <div class="col-md-3">
                            <div class="progress mb-0" style="height: 20px;">
                                <div class="progress-bar bg-secondary" 
                                     role="progressbar" 
                                     style="width: {{ $item['progress'] }}%">
                                    {{ $item['progress'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex justify-content-end">
                                <span class="badge {{ $item['progress'] >= $item['activity']->pass_threshold ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item['progress'] >= $item['activity']->pass_threshold ? 'Passed' : 'Failed' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Future Activities -->
    @if(count($futureActivities) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Upcoming Activities</h4>
            @foreach($futureActivities as $item)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-1">{{ $item['activity']->activity_name }}</h5>
                            <p class="text-muted mb-0">
                                {{ $item['activity']->activity_type }} • 
                                Starts: {{ $item['activity']->start_date ? $item['activity']->start_date->format('M j, Y') : 'TBA' }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-end">
                                <span class="badge bg-secondary">Upcoming</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection