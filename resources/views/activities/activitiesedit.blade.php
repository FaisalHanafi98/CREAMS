@extends('layouts.app')

@section('title', 'Edit Activity')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/activities.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Activity</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('activities.update', $activity->id) }}" method="POST" id="editActivityForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
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
                            
                            <div class="col-md-6">
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category') is-invalid @enderror" 
                                            id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category }}" 
                                                {{ old('category', $activity->category) == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="age_group">Age Group <span class="text-danger">*</span></label>
                                    <select class="form-control @error('age_group') is-invalid @enderror" 
                                            id="age_group" name="age_group" required>
                                        <option value="">Select Age Group</option>
                                        <option value="3-6" {{ old('age_group', $activity->age_group) == '3-6' ? 'selected' : '' }}>3-6 years</option>
                                        <option value="7-12" {{ old('age_group', $activity->age_group) == '7-12' ? 'selected' : '' }}>7-12 years</option>
                                        <option value="13-18" {{ old('age_group', $activity->age_group) == '13-18' ? 'selected' : '' }}>13-18 years</option>
                                        <option value="All Ages" {{ old('age_group', $activity->age_group) == 'All Ages' ? 'selected' : '' }}>All Ages</option>
                                    </select>
                                    @error('age_group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="difficulty_level">Difficulty <span class="text-danger">*</span></label>
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
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required>{{ old('description', $activity->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="objectives">Learning Objectives</label>
                            <textarea class="form-control @error('objectives') is-invalid @enderror" 
                                      id="objectives" name="objectives" rows="3" 
                                      placeholder="Enter each objective on a new line">{{ old('objectives', $activity->objectives) }}</textarea>
                            @error('objectives')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="materials_needed">Materials Needed</label>
                            <textarea class="form-control @error('materials_needed') is-invalid @enderror" 
                                      id="materials_needed" name="materials_needed" rows="2" 
                                      placeholder="List required materials">{{ old('materials_needed', $activity->materials_needed) }}</textarea>
                            @error('materials_needed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" 
                                       name="is_active" value="1" 
                                       {{ old('is_active', $activity->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Activity is Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Activity
                            </button>
                            <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/activities.js') }}"></script>
@endsection