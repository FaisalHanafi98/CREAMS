@extends('layouts.app')

@section('title', 'Create Schedule Template')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-plus me-2"></i>Create Schedule Template
            </h1>
            <p class="mb-0 text-muted">Design a reusable schedule pattern for activities</p>
        </div>
        <div>
            <a href="{{ route('activities.templates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Templates
            </a>
        </div>
    </div>

    <!-- Template Creation Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <form action="{{ route('activities.templates.store') }}" method="POST" id="templateForm">
                    @csrf
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0">Template Configuration</h6>
                    </div>
                    
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label for="template_name" class="form-label fw-bold">Template Name *</label>
                                <input type="text" class="form-control" id="template_name" name="template_name" 
                                       value="{{ old('template_name') }}" required
                                       placeholder="e.g., Monday-Wednesday-Friday Morning">
                                @error('template_name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="template_type" class="form-label fw-bold">Template Type *</label>
                                <select class="form-select" id="template_type" name="template_type" required>
                                    <option value="">Choose type...</option>
                                    @foreach($templateTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('template_type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('template_type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Describe when and how this template should be used...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Schedule Configuration -->
                        <div class="border rounded p-3 mb-4 bg-light">
                            <h6 class="fw-bold mb-3">Schedule Configuration</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="sessions_per_week" class="form-label fw-bold">Sessions per Week *</label>
                                    <select class="form-select" id="sessions_per_week" name="sessions_per_week" required>
                                        <option value="">Select...</option>
                                        @for($i = 1; $i <= 7; $i++)
                                            <option value="{{ $i }}" {{ old('sessions_per_week') == $i ? 'selected' : '' }}>
                                                {{ $i }} session{{ $i > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('sessions_per_week')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="duration_weeks" class="form-label fw-bold">Duration (Weeks) *</label>
                                    <input type="number" class="form-control" id="duration_weeks" name="duration_weeks" 
                                           value="{{ old('duration_weeks', 8) }}" min="1" max="52" required>
                                    @error('duration_weeks')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="session_length_minutes" class="form-label fw-bold">Session Length *</label>
                                    <select class="form-select" id="session_length_minutes" name="session_length_minutes" required>
                                        <option value="">Select...</option>
                                        <option value="30" {{ old('session_length_minutes') == '30' ? 'selected' : '' }}>30 minutes</option>
                                        <option value="45" {{ old('session_length_minutes') == '45' ? 'selected' : '' }}>45 minutes</option>
                                        <option value="60" {{ old('session_length_minutes', '60') == '60' ? 'selected' : '' }}>1 hour</option>
                                        <option value="90" {{ old('session_length_minutes') == '90' ? 'selected' : '' }}>1.5 hours</option>
                                        <option value="120" {{ old('session_length_minutes') == '120' ? 'selected' : '' }}>2 hours</option>
                                        <option value="180" {{ old('session_length_minutes') == '180' ? 'selected' : '' }}>3 hours</option>
                                    </select>
                                    @error('session_length_minutes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Days of Week Selection -->
                        <div class="border rounded p-3 mb-4">
                            <h6 class="fw-bold mb-3">Days of Week *</h6>
                            <p class="text-muted small">Select which days of the week this template should run</p>
                            
                            <!-- Quick Select Buttons -->
                            <div class="mb-3">
                                <div class="btn-group flex-wrap" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectDays(['Monday', 'Wednesday', 'Friday'])">
                                        Mon-Wed-Fri
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectDays(['Tuesday', 'Thursday'])">
                                        Tue-Thu
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectDays(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])">
                                        Weekdays
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectDays(['Saturday', 'Sunday'])">
                                        Weekends
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearDays()">
                                        Clear All
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Individual Day Checkboxes -->
                            <div class="row">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input day-checkbox" type="checkbox" name="days_of_week[]" 
                                               value="{{ $day }}" id="day_{{ strtolower($day) }}"
                                               {{ in_array($day, old('days_of_week', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                            {{ $day }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('days_of_week')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time Slots -->
                        <div class="border rounded p-3 mb-4">
                            <h6 class="fw-bold mb-3">Time Slots *</h6>
                            <p class="text-muted small">Define the time slots for each session day</p>
                            
                            <div id="time-slots-container">
                                <!-- Initial time slot -->
                                <div class="time-slot-row row align-items-end mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" class="form-control" name="time_slots[0][start]" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Time</label>
                                        <input type="time" class="form-control" name="time_slots[0][end]" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTimeSlot(this)" disabled>
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTimeSlot()">
                                <i class="fas fa-plus me-1"></i>Add Time Slot
                            </button>
                            @error('time_slots')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Preview Section -->
                        <div class="border rounded p-3 mb-4 bg-info bg-opacity-10">
                            <h6 class="fw-bold mb-3">Template Preview</h6>
                            <div id="template-preview">
                                <p class="text-muted">Configure the template above to see the preview</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('activities.templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let timeSlotIndex = 1;

// Add new time slot
function addTimeSlot() {
    const container = document.getElementById('time-slots-container');
    const newRow = document.createElement('div');
    newRow.className = 'time-slot-row row align-items-end mb-3';
    newRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Start Time</label>
            <input type="time" class="form-control" name="time_slots[${timeSlotIndex}][start]" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">End Time</label>
            <input type="time" class="form-control" name="time_slots[${timeSlotIndex}][end]" required>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTimeSlot(this)">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    `;
    container.appendChild(newRow);
    timeSlotIndex++;
    updateRemoveButtons();
}

// Remove time slot
function removeTimeSlot(button) {
    button.closest('.time-slot-row').remove();
    updateRemoveButtons();
    updatePreview();
}

// Update remove buttons (disable if only one slot)
function updateRemoveButtons() {
    const rows = document.querySelectorAll('.time-slot-row');
    const removeButtons = document.querySelectorAll('.time-slot-row button');
    removeButtons.forEach(button => {
        button.disabled = rows.length <= 1;
    });
}

// Select specific days
function selectDays(days) {
    const checkboxes = document.querySelectorAll('.day-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = days.includes(checkbox.value);
    });
    updatePreview();
}

// Clear all days
function clearDays() {
    document.querySelectorAll('.day-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updatePreview();
}

// Update template preview
function updatePreview() {
    const templateName = document.getElementById('template_name').value;
    const sessionsPerWeek = document.getElementById('sessions_per_week').value;
    const durationWeeks = document.getElementById('duration_weeks').value;
    const sessionLength = document.getElementById('session_length_minutes').value;
    
    const selectedDays = Array.from(document.querySelectorAll('.day-checkbox:checked'))
                             .map(cb => cb.value);
    
    const timeSlots = Array.from(document.querySelectorAll('.time-slot-row'))
                          .map(row => {
                              const start = row.querySelector('input[name*="[start]"]').value;
                              const end = row.querySelector('input[name*="[end]"]').value;
                              return start && end ? `${start} - ${end}` : null;
                          })
                          .filter(slot => slot !== null);
    
    let preview = '';
    
    if (templateName) {
        preview += `<h6 class="text-primary">${templateName}</h6>`;
    }
    
    if (sessionsPerWeek && durationWeeks) {
        const totalSessions = sessionsPerWeek * durationWeeks;
        preview += `<p><strong>Total Sessions:</strong> ${totalSessions} (${sessionsPerWeek}/week × ${durationWeeks} weeks)</p>`;
    }
    
    if (selectedDays.length > 0) {
        preview += `<p><strong>Days:</strong> ${selectedDays.join(', ')}</p>`;
    }
    
    if (timeSlots.length > 0) {
        preview += `<p><strong>Time Slots:</strong> ${timeSlots.join(', ')}</p>`;
    }
    
    if (sessionLength) {
        preview += `<p><strong>Session Length:</strong> ${sessionLength} minutes</p>`;
    }
    
    if (!preview) {
        preview = '<p class="text-muted">Configure the template above to see the preview</p>';
    }
    
    document.getElementById('template-preview').innerHTML = preview;
}

// Add event listeners for live preview
document.addEventListener('DOMContentLoaded', function() {
    // Add listeners to all form elements
    document.getElementById('template_name').addEventListener('input', updatePreview);
    document.getElementById('sessions_per_week').addEventListener('change', updatePreview);
    document.getElementById('duration_weeks').addEventListener('input', updatePreview);
    document.getElementById('session_length_minutes').addEventListener('change', updatePreview);
    
    // Add listeners to day checkboxes
    document.querySelectorAll('.day-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updatePreview);
    });
    
    // Add listeners to existing time inputs
    document.addEventListener('change', function(e) {
        if (e.target.type === 'time') {
            updatePreview();
        }
    });
    
    // Initial preview update
    updatePreview();
});

// Form validation
document.getElementById('templateForm').addEventListener('submit', function(e) {
    const selectedDays = document.querySelectorAll('.day-checkbox:checked');
    const timeSlots = document.querySelectorAll('input[name*="[start]"]');
    
    if (selectedDays.length === 0) {
        e.preventDefault();
        alert('Please select at least one day of the week.');
        return;
    }
    
    let hasValidTimeSlot = false;
    timeSlots.forEach(startInput => {
        const endInput = startInput.closest('.time-slot-row').querySelector('input[name*="[end]"]');
        if (startInput.value && endInput.value) {
            hasValidTimeSlot = true;
        }
    });
    
    if (!hasValidTimeSlot) {
        e.preventDefault();
        alert('Please add at least one complete time slot.');
        return;
    }
});
</script>
@endpush