@extends('layouts.app')

@section('title', 'Edit Activity - ' . $activity->activity_name . ' - CREAMS')

<!-- CREATED_AT: JULY 7 - AUTO BY CLAUDE -->

@section('content')
<div class="activity-edit-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Activity</h1>
            <p class="subtitle">{{ $activity->activity_name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Activity
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> Please check the form for errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="form-container">
        <form action="{{ route('activities.update', $activity->id) }}" method="POST" enctype="multipart/form-data" class="activity-form">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    {{-- Basic Information --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Basic Information</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="activity_name">Activity Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('activity_name') is-invalid @enderror" 
                                               id="activity_name" name="activity_name" 
                                               value="{{ old('activity_name', $activity->activity_name) }}" required>
                                        @error('activity_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="activity_code">Activity Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('activity_code') is-invalid @enderror" 
                                               id="activity_code" name="activity_code" 
                                               value="{{ old('activity_code', $activity->activity_code) }}" required>
                                        @error('activity_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" required>{{ old('description', $activity->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="objectives">Learning Objectives</label>
                                <textarea class="form-control @error('objectives') is-invalid @enderror" 
                                          id="objectives" name="objectives" rows="3">{{ old('objectives', $activity->objectives) }}</textarea>
                                @error('objectives')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Category and Level --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Category & Level</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_id">Category <span class="text-danger">*</span></label>
                                        <select class="form-control @error('category_id') is-invalid @enderror" 
                                                id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories->groupBy('type') as $type => $typeCategories)
                                                <optgroup label="{{ ucfirst($type) }} Activities">
                                                    @foreach($typeCategories as $category)
                                                        <option value="{{ $category->id }}" 
                                                                {{ old('category_id', $activity->category_id ?? ($activity->category == $category->name ? $category->id : '')) == $category->id ? 'selected' : '' }}
                                                                data-icon="{{ $category->icon_class }}"
                                                                data-color="{{ $category->color_code }}">
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="difficulty_level">Difficulty Level <span class="text-danger">*</span></label>
                                        <select class="form-control @error('difficulty_level') is-invalid @enderror" 
                                                id="difficulty_level" name="difficulty_level" required>
                                            <option value="">Select Level</option>
                                            <option value="Beginner" {{ old('difficulty_level', $activity->difficulty_level) == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="Intermediate" {{ old('difficulty_level', $activity->difficulty_level) == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="Advanced" {{ old('difficulty_level', $activity->difficulty_level) == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                        </select>
                                        @error('difficulty_level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="age_group">Target Age Group <span class="text-danger">*</span></label>
                                <select class="form-control @error('age_group') is-invalid @enderror" 
                                        id="age_group" name="age_group" required>
                                    <option value="">Select Age Group</option>
                                    <option value="3-5 years" {{ old('age_group', $activity->age_group) == '3-5 years' ? 'selected' : '' }}>3-5 years</option>
                                    <option value="6-8 years" {{ old('age_group', $activity->age_group) == '6-8 years' ? 'selected' : '' }}>6-8 years</option>
                                    <option value="9-12 years" {{ old('age_group', $activity->age_group) == '9-12 years' ? 'selected' : '' }}>9-12 years</option>
                                    <option value="13-17 years" {{ old('age_group', $activity->age_group) == '13-17 years' ? 'selected' : '' }}>13-17 years</option>
                                    <option value="18+ years" {{ old('age_group', $activity->age_group) == '18+ years' ? 'selected' : '' }}>18+ years</option>
                                    <option value="All Ages" {{ old('age_group', $activity->age_group) == 'All Ages' ? 'selected' : '' }}>All Ages</option>
                                </select>
                                @error('age_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Materials and Resources --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Materials & Resources</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label for="materials_needed">Materials Needed</label>
                                <textarea class="form-control @error('materials_needed') is-invalid @enderror" 
                                          id="materials_needed" name="materials_needed" rows="3"
                                          placeholder="List materials, equipment, and resources needed for this activity">{{ old('materials_needed', $activity->materials_needed) }}</textarea>
                                @error('materials_needed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="preparation_notes">Preparation Notes</label>
                                <textarea class="form-control @error('preparation_notes') is-invalid @enderror" 
                                          id="preparation_notes" name="preparation_notes" rows="3"
                                          placeholder="Notes for instructors on how to prepare for this activity">{{ old('preparation_notes', $activity->preparation_notes) }}</textarea>
                                @error('preparation_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Status and Settings --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Status & Settings</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $activity->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Activity is Active
                                    </label>
                                </div>
                                <small class="form-text text-muted">Inactive activities won't be visible to trainees</small>
                            </div>

                            <div class="form-group">
                                <label for="duration_minutes">Duration (minutes)</label>
                                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                       id="duration_minutes" name="duration_minutes" min="1" max="480"
                                       value="{{ old('duration_minutes', $activity->duration_minutes) }}">
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Expected duration per session</small>
                            </div>

                            <div class="form-group">
                                <label for="max_participants">Max Participants</label>
                                <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                                       id="max_participants" name="max_participants" min="1" max="50"
                                       value="{{ old('max_participants', $activity->max_participants) }}">
                                @error('max_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum number of participants per session</small>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Actions</h3>
                        </div>
                        <div class="form-card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i> Update Activity
                            </button>
                            <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                            <hr>
                            @if($role === 'admin')
                                <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteModal">
                                    <i class="fas fa-trash-alt mr-2"></i> Delete Activity
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
@if($role === 'admin')
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Activity</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i> 
                    Warning: This action cannot be undone.
                </p>
                <p>Are you sure you want to delete <strong>{{ $activity->activity_name }}</strong>?</p>
                <p>This will also delete all associated sessions and enrollment data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Activity</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
<style>
.activity-edit-container {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.subtitle {
    color: #6c757d;
    margin: 0;
    font-size: 1.1rem;
}

.form-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
}

.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    overflow: hidden;
}

.form-card-header {
    background: linear-gradient(135deg, var(--primary-color, #007bff), var(--secondary-color, #6c757d));
    color: white;
    padding: 15px 20px;
    border-bottom: none;
}

.form-card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.form-card-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.btn {
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-block {
    margin-bottom: 10px;
}

.text-danger {
    color: #dc3545 !important;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
}

.alert {
    border-radius: 8px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        margin-top: 15px;
    }
    
    .form-container {
        padding: 15px;
    }
    
    .form-card-body {
        padding: 20px;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);

    // Form validation enhancement
    const form = document.querySelector('.activity-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
            }
        });
    }
});
</script>
@endsection