@extends('layouts.app')

@section('title', 'Enroll Trainees - ' . $activity->activity_name)

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
    }
    
    .enrollment-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .enrollment-form {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .trainee-selection {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e3e6f0;
        border-radius: 10px;
        padding: 1rem;
    }
    
    .trainee-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f8f9fc;
        transition: background-color 0.2s;
    }
    
    .trainee-item:hover {
        background-color: #f8f9fc;
    }
    
    .trainee-item:last-child {
        border-bottom: none;
    }
    
    .trainee-checkbox {
        margin-right: 1rem;
    }
    
    .trainee-info h6 {
        margin: 0;
        color: var(--dark-color);
    }
    
    .trainee-info small {
        color: #6c757d;
    }
    
    .current-enrollments {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .enrolled-trainee {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        background: white;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .enrolled-trainee:last-child {
        margin-bottom: 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="enrollment-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-user-plus"></i> Enroll Trainees</h1>
                <p class="mb-0">Activity: {{ $activity->activity_name }}</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to Activity
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Enrollment Form -->
            <div class="enrollment-form">
                <h3><i class="fas fa-users"></i> Select Trainees to Enroll</h3>
                
                <form action="{{ route('activities.enroll.submit', $activity->id) }}" method="POST" id="enrollmentForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="enrollment_date" class="form-label">Enrollment Date</label>
                            <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="goals" class="form-label">Goals (Optional)</label>
                            <input type="text" class="form-control" id="goals" name="goals" 
                                   placeholder="Enter specific goals for enrolled trainees">
                        </div>
                    </div>
                    
                    @if($availableTrainees->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Available Trainees</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                    <i class="fas fa-check-double"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                    <i class="fas fa-times"></i> Deselect All
                                </button>
                            </div>
                            
                            <div class="trainee-selection">
                                @foreach($availableTrainees as $trainee)
                                    <div class="trainee-item">
                                        <div class="form-check trainee-checkbox">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="trainee_ids[]" value="{{ $trainee->id }}" 
                                                   id="trainee_{{ $trainee->id }}">
                                        </div>
                                        <div class="trainee-info">
                                            <h6>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h6>
                                            <small>
                                                ID: {{ $trainee->trainee_id ?? 'N/A' }} | 
                                                Age: {{ $trainee->date_of_birth ? \Carbon\Carbon::parse($trainee->date_of_birth)->age : 'N/A' }} | 
                                                Condition: {{ $trainee->trainee_condition ?? 'Not specified' }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Enroll Selected Trainees
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>No Available Trainees</strong><br>
                            All trainees are already enrolled in this activity, or there are no trainees in the system.
                        </div>
                    @endif
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Current Enrollments -->
            <div class="current-enrollments">
                <h4><i class="fas fa-list"></i> Current Enrollments</h4>
                <p class="text-muted">{{ $activity->activeEnrollments->count() }} trainees enrolled</p>
                
                @if($activity->activeEnrollments->count() > 0)
                    @foreach($activity->activeEnrollments as $enrollment)
                        <div class="enrolled-trainee">
                            <div class="me-2">
                                <i class="fas fa-user-check text-success"></i>
                            </div>
                            <div>
                                <strong>{{ $enrollment->trainee->trainee_first_name }} {{ $enrollment->trainee->trainee_last_name }}</strong><br>
                                <small class="text-muted">
                                    Enrolled: {{ $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->format('M d, Y') : 'Unknown' }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-users fa-2x mb-2 opacity-50"></i>
                        <p>No trainees enrolled yet</p>
                    </div>
                @endif
            </div>
            
            <!-- Activity Info -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Activity Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $activity->activity_name }}</p>
                    <p><strong>Type:</strong> {{ $activity->activity_type ?? 'Not specified' }}</p>
                    <p><strong>Location:</strong> {{ $activity->activity_location ?? 'Not specified' }}</p>
                    <p><strong>Max Participants:</strong> {{ $activity->max_participants ?? 'Unlimited' }}</p>
                    <p><strong>Current Participants:</strong> {{ $activity->current_participants ?? 0 }}</p>
                    @if($activity->activity_description)
                        <p><strong>Description:</strong></p>
                        <p class="text-muted">{{ $activity->activity_description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAll() {
    const checkboxes = document.querySelectorAll('input[name="trainee_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('input[name="trainee_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

// Form validation
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="trainee_ids[]"]:checked');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Please select at least one trainee to enroll.');
        return false;
    }
});
</script>
@endsection