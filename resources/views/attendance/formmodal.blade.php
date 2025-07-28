<div class="attendance-form-container">
    <div class="session-info mb-4">
        <h6 class="text-primary">{{ $session->activity->activity_name }}</h6>
        <small class="text-muted">
            <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($session->session_date)->format('M j, Y') }}
            <i class="fas fa-clock ms-3 me-1"></i>{{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
        </small>
    </div>

    <form id="attendanceForm" data-session-id="{{ $session->id }}">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        
        <div class="attendees-list">
            @foreach($enrollments as $enrollment)
                <div class="attendee-row mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="trainee-info">
                                <strong>{{ $enrollment->trainee->trainee_first_name }} {{ $enrollment->trainee->trainee_last_name }}</strong>
                                <br>
                                <small class="text-muted">ID: {{ $enrollment->trainee->unique_identifier }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="attendance[{{ $loop->index }}][status]" required>
                                <option value="">Select Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                            </select>
                            <input type="hidden" name="attendance[{{ $loop->index }}][trainee_id]" value="{{ $enrollment->trainee->id }}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" 
                                   class="form-control form-control-sm" 
                                   name="attendance[{{ $loop->index }}][participation_score]" 
                                   placeholder="Score (1-10)" 
                                   min="1" 
                                   max="10">
                        </div>
                        <div class="col-md-3">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="attendance[{{ $loop->index }}][progress_notes]" 
                                   placeholder="Notes (optional)">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-between mt-4">
            <div class="bulk-actions">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="markAllAs('present')">
                        <i class="fas fa-check me-1"></i>All Present
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="markAllAs('absent')">
                        <i class="fas fa-times me-1"></i>All Absent
                    </button>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitAttendance">
                    <i class="fas fa-save me-1"></i>Mark Attendance
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.attendance-form-container {
    max-height: 70vh;
    overflow-y: auto;
}

.attendee-row {
    padding: 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.attendee-row:hover {
    background: #e3f2fd;
    border-color: #2196f3;
}

.trainee-info strong {
    color: #2c3e50;
    font-size: 14px;
}

.form-select-sm, .form-control-sm {
    font-size: 12px;
    padding: 4px 8px;
}

.bulk-actions .btn {
    font-size: 12px;
    padding: 4px 8px;
}

.form-actions .btn {
    min-width: 100px;
}

#submitAttendance:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.loading-spinner {
    display: none;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Bulk actions
function markAllAs(status) {
    $('select[name*="[status]"]').val(status);
}

// Form submission with enhanced error handling
$('#attendanceForm').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = $('#submitAttendance');
    const sessionId = form.data('session-id');
    
    // Validate that at least one status is selected
    const statusSelects = $('select[name*="[status]"]');
    let hasValidation = false;
    
    statusSelects.each(function() {
        if ($(this).val()) {
            hasValidation = true;
            return false; // break
        }
    });
    
    if (!hasValidation) {
        Swal.fire({
            icon: 'warning',
            title: 'No Attendance Marked',
            text: 'Please mark attendance for at least one trainee.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Show loading state
    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="loading-spinner"></span>Saving...');
    $('.loading-spinner').show();
    
    // Submit attendance
    $.ajax({
        url: '{{ route("enhanced-attendance.store") }}',
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#attendanceModal').modal('hide');
                    // Reload the page to refresh the session status
                    location.reload();
                });
            } else {
                throw new Error(response.message || 'Unknown error occurred');
            }
        },
        error: function(xhr) {
            console.error('Attendance submission error:', xhr);
            
            let errorMessage = 'Failed to save attendance. Please try again.';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
            } else if (xhr.responseText) {
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    errorMessage = parsed.message || parsed.error || errorMessage;
                } catch (e) {
                    // If parsing fails, use status text
                    errorMessage = xhr.statusText || errorMessage;
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMessage,
                confirmButtonText: 'OK'
            });
        },
        complete: function() {
            // Reset button state
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="fas fa-save me-1"></i>Mark Attendance');
            $('.loading-spinner').hide();
        }
    });
});
</script>