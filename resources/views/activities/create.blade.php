@extends('layouts.app')

@section('title', 'Create Activity - CREAMS')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-plus-circle"></i> Create New Activity</h2>
                    <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary btn-sm float-right">
                        <i class="fas fa-arrow-left"></i> Back to Activities
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('activities.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="activity_name">Activity Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('activity_name') is-invalid @enderror" 
                                           id="activity_name" 
                                           name="activity_name" 
                                           value="{{ old('activity_name') }}" 
                                           placeholder="e.g., Basic Motor Skills Development"
                                           required>
                                    @error('activity_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="activity_id">Activity ID <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('activity_id') is-invalid @enderror" 
                                           id="activity_id" 
                                           name="activity_id" 
                                           value="{{ old('activity_id') }}" 
                                           placeholder="e.g., PHY-001"
                                           required>
                                    @error('activity_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="activity_description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('activity_description') is-invalid @enderror" 
                                      id="activity_description" 
                                      name="activity_description" 
                                      rows="4" 
                                      placeholder="Provide a detailed description of the activity..."
                                      required>{{ old('activity_description') }}</textarea>
                            @error('activity_description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Category and Type -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                            id="category_id" 
                                            name="category_id">
                                        <option value="">Select Category (Optional)</option>
                                        @if(isset($categories))
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->category_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="activity_type">Activity Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('activity_type') is-invalid @enderror" 
                                            id="activity_type" 
                                            name="activity_type" 
                                            required>
                                        <option value="">Select Type</option>
                                        <option value="Individual" {{ old('activity_type') == 'Individual' ? 'selected' : '' }}>Individual</option>
                                        <option value="Group" {{ old('activity_type') == 'Group' ? 'selected' : '' }}>Group</option>
                                        <option value="Both" {{ old('activity_type') == 'Both' ? 'selected' : '' }}>Both</option>
                                        <option value="Education" {{ old('activity_type') == 'Education' ? 'selected' : '' }}>Education</option>
                                        <option value="Therapy" {{ old('activity_type') == 'Therapy' ? 'selected' : '' }}>Therapy</option>
                                        <option value="Training" {{ old('activity_type') == 'Training' ? 'selected' : '' }}>Training</option>
                                    </select>
                                    @error('activity_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="activity_date">Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('activity_date') is-invalid @enderror" 
                                           id="activity_date" 
                                           name="activity_date" 
                                           value="{{ old('activity_date') }}" 
                                           min="{{ date('Y-m-d') }}"
                                           required>
                                    @error('activity_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="activity_start_time">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('activity_start_time') is-invalid @enderror" 
                                           id="activity_start_time" 
                                           name="activity_start_time" 
                                           value="{{ old('activity_start_time') }}" 
                                           required>
                                    @error('activity_start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="activity_end_time">End Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('activity_end_time') is-invalid @enderror" 
                                           id="activity_end_time" 
                                           name="activity_end_time" 
                                           value="{{ old('activity_end_time') }}" 
                                           required>
                                    @error('activity_end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location and Participants -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="activity_location">Location <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('activity_location') is-invalid @enderror" 
                                           id="activity_location" 
                                           name="activity_location" 
                                           value="{{ old('activity_location') }}" 
                                           placeholder="e.g., Room A, Therapy Room 1"
                                           required>
                                    @error('activity_location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_participants">Max Participants <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('max_participants') is-invalid @enderror" 
                                           id="max_participants" 
                                           name="max_participants" 
                                           value="{{ old('max_participants') }}" 
                                           min="1" 
                                           max="100"
                                           required>
                                    @error('max_participants')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Goals and Outcomes -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="activity_goals">Goals</label>
                                    <textarea class="form-control @error('activity_goals') is-invalid @enderror" 
                                              id="activity_goals" 
                                              name="activity_goals" 
                                              rows="3" 
                                              placeholder="What are the goals of this activity?">{{ old('activity_goals') }}</textarea>
                                    @error('activity_goals')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="activity_outcomes">Expected Outcomes</label>
                                    <textarea class="form-control @error('activity_outcomes') is-invalid @enderror" 
                                              id="activity_outcomes" 
                                              name="activity_outcomes" 
                                              rows="3" 
                                              placeholder="What outcomes are expected?">{{ old('activity_outcomes') }}</textarea>
                                    @error('activity_outcomes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Required Resources -->
                        <div class="form-group">
                            <label for="required_resources">Required Resources</label>
                            <textarea class="form-control @error('required_resources') is-invalid @enderror" 
                                      id="required_resources" 
                                      name="required_resources" 
                                      rows="3" 
                                      placeholder="List any materials, equipment, or resources needed...">{{ old('required_resources') }}</textarea>
                            @error('required_resources')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Activity
                            </button>
                            <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Time validation
document.getElementById('activity_start_time').addEventListener('change', function() {
    const startTime = this.value;
    const endTimeInput = document.getElementById('activity_end_time');
    
    if (startTime) {
        endTimeInput.min = startTime;
    }
});

document.getElementById('activity_end_time').addEventListener('change', function() {
    const endTime = this.value;
    const startTime = document.getElementById('activity_start_time').value;
    
    if (startTime && endTime && endTime <= startTime) {
        alert('End time must be after start time');
        this.value = '';
    }
});
</script>
@endsection