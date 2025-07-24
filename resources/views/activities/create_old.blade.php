@extends('layouts.app')

@section('title', 'Create Activity - CREAMS')

@section('content')
<div class="create-activity-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i> Create New Activity
        </h1>
        <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Activities
        </a>
    </div>

    <form action="{{ route('activities.store') }}" method="POST" class="activity-form">
        @csrf
        
        <div class="form-card">
            <div class="form-card-header">
                <h2>Basic Information</h2>
            </div>
            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="activity_name">Activity Name <span class="required">*</span></label>
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
                            <label for="activity_id">Activity ID <span class="required">*</span></label>
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

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="4" 
                              placeholder="Provide a detailed description of the activity..."
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id">Category <span class="required">*</span></label>
                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories->groupBy('type') as $type => $typeCategories)
                                    <optgroup label="{{ ucfirst($type) }} Activities">
                                        @foreach($typeCategories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}
                                                    data-icon="{{ $category->icon_class }}"
                                                    data-color="{{ $category->color_code }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="age_group">Age Group <span class="required">*</span></label>
                            <select class="form-control @error('age_group') is-invalid @enderror" 
                                    id="age_group" 
                                    name="age_group" 
                                    required>
                                <option value="">Select Age Group</option>
                                <option value="3-5 years" {{ old('age_group') == '3-5 years' ? 'selected' : '' }}>3-5 years</option>
                                <option value="6-8 years" {{ old('age_group') == '6-8 years' ? 'selected' : '' }}>6-8 years</option>
                                <option value="9-12 years" {{ old('age_group') == '9-12 years' ? 'selected' : '' }}>9-12 years</option>
                                <option value="13-17 years" {{ old('age_group') == '13-17 years' ? 'selected' : '' }}>13-17 years</option>
                                <option value="All Ages" {{ old('age_group') == 'All Ages' ? 'selected' : '' }}>All Ages</option>
                            </select>
                            @error('age_group')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="difficulty_level">Difficulty Level <span class="required">*</span></label>
                            <select class="form-control @error('difficulty_level') is-invalid @enderror" 
                                    id="difficulty_level" 
                                    name="difficulty_level" 
                                    required>
                                <option value="">Select Level</option>
                                <option value="Beginner" {{ old('difficulty_level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="Intermediate" {{ old('difficulty_level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="Advanced" {{ old('difficulty_level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                            @error('difficulty_level')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <h2>Additional Details</h2>
            </div>
            <div class="form-card-body">
                <div class="form-group">
                    <label for="objectives">Learning Objectives</label>
                    <textarea class="form-control @error('objectives') is-invalid @enderror" 
                              id="objectives" 
                              name="objectives" 
                              rows="3" 
                              placeholder="List the learning objectives for this activity...">{{ old('objectives') }}</textarea>
                    @error('objectives')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="materials_needed">Materials Needed</label>
                    <textarea class="form-control @error('materials_needed') is-invalid @enderror" 
                              id="materials_needed" 
                              name="materials_needed" 
                              rows="3" 
                              placeholder="List any materials or equipment required...">{{ old('materials_needed') }}</textarea>
                    @error('materials_needed')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">
                            Active (Available for scheduling)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Create Activity
            </button>
            <a href="{{ route('activities.home') }}" class="btn btn-outline-secondary btn-lg">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection