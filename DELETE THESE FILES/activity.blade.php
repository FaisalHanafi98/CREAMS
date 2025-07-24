@extends('trainees.dashboard')

@section('title', 'Trainee Activities')

@section('page-title', 'Trainee Activities')
@section('breadcrumb', 'Activities')

@section('page-actions')
<div class="page-actions">
    <a href="{{ route('traineeshome') }}" class="action-btn">
        <i class="fas fa-arrow-left"></i> Back to Trainees
    </a>
    <button id="addActivityBtn" class="action-btn primary">
        <i class="fas fa-plus"></i> Add Activity
    </button>
</div>
@endsection

@section('styles')
<style>
    .activity-form-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
        display: none;
    }
    
    .activity-form-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(50, 189, 234, 0.1), rgba(200, 80, 192, 0.1));
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-form-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
    }
    
    .activity-form-title i {
        margin-right: 0.75rem;
        color: var(--primary-color);
    }
    
    .activity-form-content {
        padding: 1.5rem;
    }
    
    .activity-filter-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .activity-filter-header {
        padding: 1.5rem;
        background: rgba(0, 0, 0, 0.02);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
    }
    
    .activity-filter-title i {
        margin-right: 0.75rem;
        color: var(--primary-color);
    }
    
    .activity-filter-content {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }
    
    .form-control {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
        outline: none;
    }
    
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23333'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.5rem;
    }
    
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    
    .activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }
    
    .activity-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .activity-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .activity-card-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(50, 189, 234, 0.05), rgba(200, 80, 192, 0.05));
        position: relative;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .activity-date {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.35rem 0.75rem;
        background: var(--primary-gradient);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 50px;
    }
    
    .activity-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        padding-right: 80px;
    }
    
    .activity-type {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        background-color: rgba(0, 0, 0, 0.05);
        color: rgba(0, 0, 0, 0.6);
    }
    
    .activity-type-physical {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .activity-type-cognitive {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
    }
    
    .activity-type-social {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .activity-type-speech {
        background-color: rgba(111, 66, 193, 0.1);
        color: #6f42c1;
    }
    
    .activity-type-occupational {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }
    
    .activity-type-educational {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
    }
    
    .activity-type-recreational {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .activity-card-body {
        padding: 1.5rem;
        flex-grow: 1;
    }
    
    .activity-description {
        margin-bottom: 1rem;
        color: rgba(0, 0, 0, 0.7);
    }
    
    .activity-trainee {
        display: flex;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        font-size: 0.9rem;
    }
    
    .activity-trainee i {
        margin-right: 0.5rem;
        color: var(--primary-color);
    }
    
    .activity-card-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-card-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.9rem;
        border: none;
    }
    
    .btn i {
        margin-right: 0.35rem;
    }
    
    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .btn-primary {
        background: var(--primary-gradient);
        color: #fff;
    }
    
    .btn-primary:hover {
        box-shadow: 0 5px 15px rgba(50, 189, 234, 0.3);
        transform: translateY(-2px);
        color: #fff;
    }
    
    .btn-info {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: #fff;
    }
    
    .btn-info:hover {
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        transform: translateY(-2px);
        color: #fff;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: #fff;
    }
    
    .btn-danger:hover {
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        transform: translateY(-2px);
        color: #fff;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: #fff;
    }
    
    .btn-secondary:hover {
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        transform: translateY(-2px);
        color: #fff;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745, #218838);
        color: #fff;
    }
    
    .btn-success:hover {
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        transform: translateY(-2px);
        color: #fff;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .empty-state i {
        font-size: 3rem;
        color: rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: rgba(0, 0, 0, 0.5);
        margin-bottom: 1.5rem;
    }
    
    .required-field::after {
        content: '*';
        color: #dc3545;
        margin-left: 4px;
    }
    
    /* Activity Modal Styles */
    .modal-content {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .modal-header {
        background: linear-gradient(135deg, rgba(50, 189, 234, 0.1), rgba(200, 80, 192, 0.1));
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
    }
    
    .modal-title {
        font-weight: 600;
        color: #333;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .activity-detail-section {
        margin-bottom: 1.5rem;
    }
    
    .activity-detail-section:last-child {
        margin-bottom: 0;
    }
    
    .activity-detail-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        color: #333;
    }
    
    .activity-detail-item {
        margin-bottom: 0.75rem;
        display: flex;
    }
    
    .activity-detail-label {
        font-weight: 500;
        min-width: 150px;
        color: rgba(0, 0, 0, 0.6);
    }
    
    .activity-detail-value {
        flex: 1;
    }
    
    @media (max-width: 768px) {
        .activities-grid {
            grid-template-columns: 1fr;
        }
        
        .activity-form-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .activity-form-title {
            margin-bottom: 1rem;
        }
        
        .activity-detail-item {
            flex-direction: column;
        }
        
        .activity-detail-label {
            margin-bottom: 0.25rem;
        }
    }
</style>
@endsection

@section('content')
    <!-- Activity Form Card -->
    <div class="activity-form-card" id="activityFormCard">
        <div class="activity-form-header">
            <h3 class="activity-form-title">
                <i class="fas fa-plus-circle"></i> Add New Activity
            </h3>
            <button type="button" id="closeActivityFormBtn" class="btn btn-sm btn-secondary">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        
        <div class="activity-form-content">
            <form id="activityForm" action="{{ route('traineeactivity.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trainee_id" class="form-label required-field">Trainee</label>
                            <select name="trainee_id" id="trainee_id" class="form-control" required>
                                <option value="">-- Select Trainee --</option>
                                @foreach($trainees ?? [] as $trainee)
                                    <option value="{{ $trainee->id }}">
                                        {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }} 
                                        ({{ $trainee->centre_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activity_type" class="form-label required-field">Activity Type</label>
                            <select class="form-control" id="activity_type" name="activity_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="Physical Therapy">Physical Therapy</option>
                                <option value="Cognitive Development">Cognitive Development</option>
                                <option value="Social Skills">Social Skills</option>
                                <option value="Speech Therapy">Speech Therapy</option>
                                <option value="Occupational Therapy">Occupational Therapy</option>
                                <option value="Educational Activity">Educational Activity</option>
                                <option value="Recreational Activity">Recreational Activity</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activity_name" class="form-label required-field">Activity Name</label>
                            <input type="text" class="form-control" id="activity_name" name="activity_name" placeholder="Enter activity name" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activity_date" class="form-label required-field">Activity Date</label>
                            <input type="date" class="form-control" id="activity_date" name="activity_date" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="activity_description" class="form-label required-field">Activity Description</label>
                    <textarea class="form-control" id="activity_description" name="activity_description" rows="3" placeholder="Describe the activity..." required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activity_goals" class="form-label">Goals & Objectives</label>
                            <textarea class="form-control" id="activity_goals" name="activity_goals" rows="2" placeholder="What are the goals of this activity?"></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activity_outcomes" class="form-label">Outcomes & Observations</label>
                            <textarea class="form-control" id="activity_outcomes" name="activity_outcomes" rows="2" placeholder="What were the outcomes and observations?"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save Activity
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Activity Filter Card -->
    <div class="activity-filter-card">
        <div class="activity-filter-header">
            <h3 class="activity-filter-title">
                <i class="fas fa-filter"></i> Filter Activities
            </h3>
        </div>
        
        <div class="activity-filter-content">
            <form id="filterForm" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_trainee" class="form-label">Trainee</label>
                        <select class="form-control" id="filter_trainee" name="filter_trainee">
                            <option value="">All Trainees</option>
                            @foreach($trainees ?? [] as $trainee)
                                <option value="{{ $trainee->id }}">
                                    {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_type" class="form-label">Activity Type</label>
                        <select class="form-control" id="filter_type" name="filter_type">
                            <option value="">All Types</option>
                            <option value="Physical Therapy">Physical Therapy</option>
                            <option value="Cognitive Development">Cognitive Development</option>
                            <option value="Social Skills">Social Skills</option>
                            <option value="Speech Therapy">Speech Therapy</option>
                            <option value="Occupational Therapy">Occupational Therapy</option>
                            <option value="Educational Activity">Educational Activity</option>
                            <option value="Recreational Activity">Recreational Activity</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_date" class="form-label">Date Range</label>
                        <input type="date" class="form-control" id="filter_date" name="filter_date">
                    </div>
                </div>
                
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-secondary" id="resetFilterBtn">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Activities List -->
    <div class="activities-grid" id="activitiesGrid">
        @if(isset($activities) && count($activities) > 0)
            @foreach($activities as $activity)
                <div class="activity-card">
                    <div class="activity-card-header">
                        <div class="activity-date">{{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y') }}</div>
                        <h4 class="activity-title">{{ $activity->activity_name }}</h4>
                        
                        @php
                            $typeClass = '';
                            if (strpos($activity->activity_type, 'Physical') !== false) {
                                $typeClass = 'activity-type-physical';
                            } elseif (strpos($activity->activity_type, 'Cognitive') !== false) {
                                $typeClass = 'activity-type-cognitive';
                            } elseif (strpos($activity->activity_type, 'Social') !== false) {
                                $typeClass = 'activity-type-social';
                            } elseif (strpos($activity->activity_type, 'Speech') !== false) {
                                $typeClass = 'activity-type-speech';
                            } elseif (strpos($activity->activity_type, 'Occupational') !== false) {
                                $typeClass = 'activity-type-occupational';
                            } elseif (strpos($activity->activity_type, 'Educational') !== false) {
                                $typeClass = 'activity-type-educational';
                            } elseif (strpos($activity->activity_type, 'Recreational') !== false) {
                                $typeClass = 'activity-type-recreational';
                            }
                        @endphp
                        
                        <span class="activity-type {{ $typeClass }}">{{ $activity->activity_type }}</span>
                    </div>
                    
                    <div class="activity-card-body">
                        <p class="activity-description">{{ \Illuminate\Support\Str::limit($activity->activity_description, 150) }}</p>
                        
                        <div class="activity-trainee">
                            <i class="fas fa-user"></i>
                            <span>{{ $activity->trainee->trainee_first_name ?? '' }} {{ $activity->trainee->trainee_last_name ?? '' }}</span>
                        </div>
                    </div>
                    
                    <div class="activity-card-footer">
                        <div class="activity-card-actions">
                            <button class="btn btn-sm btn-info view-activity-btn" 
                                    data-id="{{ $activity->id }}"
                                    data-toggle="modal" 
                                    data-target="#activityDetailModal">
                                <i class="fas fa-eye"></i> View
                            </button>
                            
                            <a href="{{ route('traineeactivity.edit', $activity->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            <button class="btn btn-sm btn-danger delete-activity-btn" 
                                    data-id="{{ $activity->id }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state col-12">
                <i class="fas fa-clipboard-list"></i>
                <h4>No Activities Found</h4>
                <p>No activities have been recorded yet. Click the "Add Activity" button to get started.</p>
                <button class="btn btn-primary mt-3" id="emptyStateAddBtn">
                    <i class="fas fa-plus"></i> Add First Activity
                </button>
            </div>
        @endif
    </div>
    
    <!-- For Demo Purpose (if no actual activities exist) -->
    @if(!isset($activities) || count($activities) == 0)
        <div class="activities-grid" id="demoActivitiesGrid">
            <!-- Demo Activity 1 -->
            <div class="activity-card">
                <div class="activity-card-header">
                    <div class="activity-date">{{ now()->subDays(3)->format('d M Y') }}</div>
                    <h4 class="activity-title">Fine Motor Skills Practice</h4>
                    <span class="activity-type activity-type-physical">Physical Therapy</span>
                </div>
                
                <div class="activity-card-body">
                    <p class="activity-description">Practice with threading beads on a string to improve fine motor skills and hand-eye coordination.</p>
                    
                    <div class="activity-trainee">
                        <i class="fas fa-user"></i>
                        <span>Ahmad Ismail</span>
                    </div>
                </div>
                
                <div class="activity-card-footer">
                    <div class="activity-card-actions">
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#demoActivityModal">
                            <i class="fas fa-eye"></i> View
                        </button>
                        
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        
                        <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Demo Activity 2 -->
            <div class="activity-card">
                <div class="activity-card-header">
                    <div class="activity-date">{{ now()->subDays(5)->format('d M Y') }}</div>
                    <h4 class="activity-title">Social Interaction Group</h4>
                    <span class="activity-type activity-type-social">Social Skills</span>
                </div>
                
                <div class="activity-card-body">
                    <p class="activity-description">Group activity focused on taking turns, sharing, and communicating effectively with peers.</p>
                    
                    <div class="activity-trainee">
                        <i class="fas fa-user"></i>
                        <span>Nurul Izzah</span>
                    </div>
                </div>
                
                <div class="activity-card-footer">
                    <div class="activity-card-actions">
                        <button class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </button>
                        
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        
                        <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Demo Activity 3 -->
            <div class="activity-card">
                <div class="activity-card-header">
                    <div class="activity-date">{{ now()->subDays(7)->format('d M Y') }}</div>
                    <h4 class="activity-title">Memory & Attention Exercises</h4>
                    <span class="activity-type activity-type-cognitive">Cognitive Development</span>
                </div>
                
                <div class="activity-card-body">
                    <p class="activity-description">Series of matching games and puzzles designed to improve memory and sustained attention.</p>
                    
                    <div class="activity-trainee">
                        <i class="fas fa-user"></i>
                        <span>Wong Siew Ying</span>
                    </div>
                </div>
                
                <div class="activity-card-footer">
                    <div class="activity-card-actions">
                        <button class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </button>
                        
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        
                        <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Activity Detail Modal -->
    <div class="modal fade" id="activityDetailModal" tabindex="-1" role="dialog" aria-labelledby="activityDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activityDetailModalLabel">Activity Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="activityDetailContent">
                        Loading...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" id="editActivityBtn" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Activity
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Demo Activity Detail Modal -->
    <div class="modal fade" id="demoActivityModal" tabindex="-1" role="dialog" aria-labelledby="demoActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="demoActivityModalLabel">Fine Motor Skills Practice</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="activity-detail-section">
                        <h6 class="activity-detail-title">General Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="activity-detail-item">
                                    <div class="activity-detail-label">Trainee:</div>
                                    <div class="activity-detail-value">Ahmad Ismail</div>
                                </div>
                                <div class="activity-detail-item">
                                    <div class="activity-detail-label">Activity Type:</div>
                                    <div class="activity-detail-value">Physical Therapy</div>
                                </div>
                                <div class="activity-detail-item">
                                    <div class="activity-detail-label">Date:</div>
                                    <div class="activity-detail-value">{{ now()->subDays(3)->format('d M Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="activity-detail-item">
                                    <div class="activity-detail-label">Centre:</div>
                                    <div class="activity-detail-value">Gombak</div>
                                </div>
                                <div class="activity-detail-item">
                                    <div class="activity-detail-label">Conducted By:</div>
                                    <div class="activity-detail-value">Dr. Nurul Hafizah</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="activity-detail-section">
                        <h6 class="activity-detail-title">Description</h6>
                        <p>Practice with threading beads on a string to improve fine motor skills and hand-eye coordination. The session involved sorting beads by colors and sizes, then threading them in specific patterns.</p>
                    </div>
                    
                    <div class="activity-detail-section">
                        <h6 class="activity-detail-title">Goals & Objectives</h6>
                        <p>Improve pincer grasp, enhance hand-eye coordination, develop pattern recognition, and increase attention span during focused tasks.</p>
                    </div>
                    
                    <div class="activity-detail-section">
                        <h6 class="activity-detail-title">Outcomes & Observations</h6>
                        <p>Ahmad showed good improvement in his ability to manipulate smaller beads. He was able to thread 15 beads in sequence without assistance, an improvement from the previous session's 10 beads. He demonstrated frustration when the pattern became more complex but responded well to encouragement.</p>
                    </div>
                    
                    <div class="activity-detail-section">
                        <h6 class="activity-detail-title">Recommendations</h6>
                        <p>Continue with similar activities but gradually increase complexity. Consider adding timed elements to build speed while maintaining accuracy. Recommended home practice with similar activities 2-3 times per week.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Activity
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteActivityModal" tabindex="-1" role="dialog" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteActivityModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this activity? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteActivityForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection