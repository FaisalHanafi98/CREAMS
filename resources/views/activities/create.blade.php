@extends('layouts.app')

@section('title', 'Create Activity - CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i> Create New Activity
        </h1>
        <div class="page-actions">
            <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Activities
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="{{ route('activities.store') }}" method="POST" id="activityForm" class="activity-form">
                @csrf

                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_code">Activity Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('activity_code') is-invalid @enderror" 
                                       id="activity_code" name="activity_code" value="{{ old('activity_code') }}" 
                                       placeholder="e.g., ACT001" required>
                                @error('activity_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity_name">Activity Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('activity_name') is-invalid @enderror" 
                                       id="activity_name" name="activity_name" value="{{ old('activity_name') }}" 
                                       placeholder="Enter activity name" required>
                                @error('activity_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category <span class="text-danger">*</span></label>
                                <select class="form-control @error('category') is-invalid @enderror" 
                                        id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="age_group">Age Group <span class="text-danger">*</span></label>
                                <select class="form-control @error('age_group') is-invalid @enderror" 
                                        id="age_group" name="age_group" required>
                                    <option value="">Select Age Group</option>
                                    <option value="3-6" {{ old('age_group') == '3-6' ? 'selected' : '' }}>3-6 years</option>
                                    <option value="7-12" {{ old('age_group') == '7-12' ? 'selected' : '' }}>7-12 years</option>
                                    <option value="13-18" {{ old('age_group') == '13-18' ? 'selected' : '' }}>13-18 years</option>
                                    <option value="All Ages" {{ old('age_group') == 'All Ages' ? 'selected' : '' }}>All Ages</option>
                                </select>
                                @error('age_group')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="difficulty_level">Difficulty Level <span class="text-danger">*</span></label>
                        <select class="form-control @error('difficulty_level') is-invalid @enderror" 
                                id="difficulty_level" name="difficulty_level" required>
                            <option value="">Select Difficulty</option>
                            <option value="Beginner" {{ old('difficulty_level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="Intermediate" {{ old('difficulty_level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="Advanced" {{ old('difficulty_level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                        @error('difficulty_level')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Activity Details -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-file-alt"></i> Activity Details
                    </h3>
                    
                    <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Provide a detailed description of the activity" required>{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="objectives">Learning Objectives</label>
                        <textarea class="form-control @error('objectives') is-invalid @enderror" 
                                  id="objectives" name="objectives" rows="3" 
                                  placeholder="List the learning objectives (one per line)">{{ old('objectives') }}</textarea>
                        @error('objectives')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="materials_needed">Materials Needed</label>
                        <textarea class="form-control @error('materials_needed') is-invalid @enderror" 
                                  id="materials_needed" name="materials_needed" rows="3" 
                                  placeholder="List required materials (one per line)">{{ old('materials_needed') }}</textarea>
                        @error('materials_needed')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Activity
                    </button>
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection