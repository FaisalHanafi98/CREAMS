@extends('layouts.app')

@section('title', 'Create IEP - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>Create Education Plan
        </h2>
        <a href="{{ route('iep.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to IEPs
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('iep.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Trainee Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="trainee_id" class="form-label fw-semibold">Trainee <span class="text-danger">*</span></label>
                        <select name="trainee_id" id="trainee_id" class="form-select @error('trainee_id') is-invalid @enderror" required>
                            <option value="" disabled @unless($selectedTrainee) selected @endunless>Select a trainee</option>
                            @foreach($trainees as $trainee)
                                <option value="{{ $trainee->id }}" @selected($selectedTrainee && $selectedTrainee->id == $trainee->id)>
                                    {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('trainee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Plan Type -->
                    <div class="col-md-6 mb-3">
                        <label for="plan_type" class="form-label fw-semibold">Plan Type <span class="text-danger">*</span></label>
                        <select name="plan_type" id="plan_type" class="form-select @error('plan_type') is-invalid @enderror" required>
                            <option value="Annual" @selected(old('plan_type') === 'Annual')>Annual</option>
                            <option value="Quarterly" @selected(old('plan_type') === 'Quarterly')>Quarterly</option>
                            <option value="Custom" @selected(old('plan_type') === 'Custom')>Custom</option>
                        </select>
                        @error('plan_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Plan Name -->
                    <div class="col-md-12 mb-3">
                        <label for="plan_name" class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" name="plan_name" id="plan_name" class="form-control @error('plan_name') is-invalid @enderror"
                               value="{{ old('plan_name') }}" placeholder="e.g. Annual Education Plan 2026" required>
                        @error('plan_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label for="plan_description" class="form-label fw-semibold">Description</label>
                        <textarea name="plan_description" id="plan_description" class="form-control" rows="3"
                                  placeholder="Describe the plan objectives and scope...">{{ old('plan_description') }}</textarea>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', now()->addMonths(12)->format('Y-m-d')) }}" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="review_date" class="form-label fw-semibold">Next Review Date</label>
                        <input type="date" name="review_date" id="review_date" class="form-control"
                               value="{{ old('review_date') }}">
                    </div>

                    <!-- Goals & Strengths -->
                    <div class="col-md-6 mb-3">
                        <label for="strengths" class="form-label fw-semibold">Strengths</label>
                        <textarea name="strengths" id="strengths" class="form-control" rows="3"
                                  placeholder="List strengths, one per line...">{{ old('strengths') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="challenges" class="form-label fw-semibold">Challenges</label>
                        <textarea name="challenges" id="challenges" class="form-control" rows="3"
                                  placeholder="List challenges, one per line...">{{ old('challenges') }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('iep.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
