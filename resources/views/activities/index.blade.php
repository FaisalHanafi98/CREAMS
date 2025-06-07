@extends('layouts.app')

@section('title', 'Activities - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tasks"></i> Activity Management
        </h1>
        @if(in_array(session('role'), ['admin', 'supervisor']))
            <div class="page-actions">
                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Activity
                </a>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-primary">
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
            <div class="stat-card stat-success">
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
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_sessions'] }}</h3>
                    <p>Total Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['todays_sessions'] }}</h3>
                    <p>Today's Sessions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Filter by Category</label>
                        <select class="form-control" id="categoryFilter">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Filter by Status</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" class="form-control" id="searchFilter" placeholder="Search activities...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="activitiesTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Activity Name</th>
                            <th>Category</th>
                            <th>Age Group</th>
                            <th>Difficulty</th>
                            <th>Sessions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr data-category="{{ $activity->category }}" 
                                data-status="{{ $activity->is_active ? 'active' : 'inactive' }}">
                                <td>{{ $activity->activity_code }}</td>
                                <td>{{ $activity->activity_name }}</td>
                                <td><span class="badge badge-info">{{ $activity->category }}</span></td>
                                <td>{{ $activity->age_group }}</td>
                                <td>
                                    <span class="badge badge-{{ $activity->difficulty_level == 'Beginner' ? 'success' : ($activity->difficulty_level == 'Intermediate' ? 'warning' : 'danger') }}">
                                        {{ $activity->difficulty_level }}
                                    </span>
                                </td>
                                <td>{{ $activity->active_sessions_count }}</td>
                                <td>
                                    @if($activity->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('activities.show', $activity->id) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array(session('role'), ['admin', 'supervisor']))
                                            <a href="{{ route('activities.edit', $activity->id) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('activities.sessions', $activity->id) }}" 
                                               class="btn btn-sm btn-primary" title="Sessions">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection