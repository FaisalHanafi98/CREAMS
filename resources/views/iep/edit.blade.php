@extends('layouts.app')

@section('title', 'Edit IEP - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Education Plan
        </h2>
        <a href="{{ route('iep.show', $iep->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Plan
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('iep.update', $iep->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Trainee (read-only display) -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Trainee</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            {{ $iep->trainee->trainee_first_name }} {{ $iep->trainee->trainee_last_name }}
                        </div>
                    </div>

                    <!-- Plan Type -->
                    <div class="col-md-6 mb-3">
                        <label for="plan_type" class="form-label fw-semibold">Plan Type <span class="text-danger">*</span></label>
                        <select name="plan_type" id="plan_type" class="form-select @error('plan_type') is-invalid @enderror" required>
                            <option value="Annual" @selected($iep->plan_type === 'Annual')>Annual</option>
                            <option value="Quarterly" @selected($iep->plan_type === 'Quarterly')>Quarterly</option>
                            <option value="Custom" @selected($iep->plan_type === 'Custom')>Custom</option>
                        </select>
                        @error('plan_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Plan Name -->
                    <div class="col-md-12 mb-3">
                        <label for="plan_name" class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" name="plan_name" id="plan_name" class="form-control @error('plan_name') is-invalid @enderror"
                               value="{{ old('plan_name', $iep->plan_name) }}" required>
                        @error('plan_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label for="plan_description" class="form-label fw-semibold">Description</label>
                        <textarea name="plan_description" id="plan_description" class="form-control" rows="3"
                                  placeholder="Describe the plan objectives and scope...">{{ old('plan_description', $iep->plan_description) }}</textarea>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $iep->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $iep->end_date->format('Y-m-d')) }}" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="review_date" class="form-label fw-semibold">Next Review Date</label>
                        <input type="date" name="review_date" id="review_date" class="form-control"
                               value="{{ old('review_date', $iep->review_date ? $iep->review_date->format('Y-m-d') : '') }}">
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Active" @selected(old('status', $iep->status) === 'Active')>Active</option>
                            <option value="Completed" @selected(old('status', $iep->status) === 'Completed')>Completed</option>
                            <option value="Suspended" @selected(old('status', $iep->status) === 'Suspended')>Suspended</option>
                            <option value="Under Review" @selected(old('status', $iep->status) === 'Under Review')>Under Review</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Target Completion % -->
                    <div class="col-md-6 mb-3">
                        <label for="target_completion_percentage" class="form-label fw-semibold">Target Completion %</label>
                        <input type="number" name="target_completion_percentage" id="target_completion_percentage"
                               class="form-control @error('target_completion_percentage') is-invalid @enderror"
                               value="{{ old('target_completion_percentage', $iep->target_completion_percentage) }}"
                               min="0" max="100" step="0.01">
                        @error('target_completion_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Strengths & Challenges -->
                    <div class="col-md-6 mb-3">
                        <label for="strengths" class="form-label fw-semibold">Strengths</label>
                        <textarea name="strengths" id="strengths" class="form-control" rows="3"
                                  placeholder="List strengths, one per line...">{{ old('strengths', $iep->strengths ? implode("\n", $iep->strengths) : '') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="challenges" class="form-label fw-semibold">Challenges</label>
                        <textarea name="challenges" id="challenges" class="form-control" rows="3"
                                  placeholder="List challenges, one per line...">{{ old('challenges', $iep->challenges ? implode("\n", $iep->challenges) : '') }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $iep->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('iep.show', $iep->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
