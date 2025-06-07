// Activity Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    initializeFilters();
    
    // Initialize form handlers
    initializeFormHandlers();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

// Filter functionality
function initializeFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchFilter = document.getElementById('searchFilter');
    const table = document.getElementById('activitiesTable');
    
    if (!table) return;
    
    function filterTable() {
        const category = categoryFilter?.value.toLowerCase() || '';
        const status = statusFilter?.value.toLowerCase() || '';
        const search = searchFilter?.value.toLowerCase() || '';
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const rowCategory = row.getAttribute('data-category')?.toLowerCase() || '';
            const rowStatus = row.getAttribute('data-status')?.toLowerCase() || '';
            const rowText = row.textContent.toLowerCase();
            
            const categoryMatch = !category || rowCategory.includes(category);
            const statusMatch = !status || rowStatus === status;
            const searchMatch = !search || rowText.includes(search);
            
            row.style.display = categoryMatch && statusMatch && searchMatch ? '' : 'none';
        });
    }
    
    categoryFilter?.addEventListener('change', filterTable);
    statusFilter?.addEventListener('change', filterTable);
    searchFilter?.addEventListener('input', filterTable);
}

// Form handlers
function initializeFormHandlers() {
    // Activity form validation
    const activityForm = document.getElementById('activityForm');
    if (activityForm) {
        activityForm.addEventListener('submit', function(e) {
            if (!validateActivityForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Session form handling
    const sessionForm = document.getElementById('sessionForm');
    if (sessionForm) {
        // Time validation
        const startTime = document.getElementById('start_time');
        const duration = document.getElementById('duration_hours');
        
        if (startTime && duration) {
            [startTime, duration].forEach(input => {
                input.addEventListener('change', validateSessionTime);
            });
        }
    }
}

// Validate activity form
function validateActivityForm() {
    const form = document.getElementById('activityForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Validate session time
function validateSessionTime() {
    const startTime = document.getElementById('start_time').value;
    const duration = parseFloat(document.getElementById('duration_hours').value);
    
    if (startTime && duration) {
        const [hours, minutes] = startTime.split(':').map(Number);
        const startMinutes = hours * 60 + minutes;
        const endMinutes = startMinutes + (duration * 60);
        
        // Check if end time exceeds 5 PM (17:00)
        if (endMinutes > 17 * 60) {
            alert('Session end time cannot exceed 5:00 PM');
            document.getElementById('duration_hours').value = '';
        }
    }
}

// Mark attendance
function markAttendance(traineeId, status) {
    const button = event.target.closest('button');
    const buttons = button.parentElement.querySelectorAll('button');
    
    // Update button states
    buttons.forEach(btn => {
        btn.classList.remove('btn-success', 'btn-danger', 'btn-warning', 'btn-secondary');
        if (btn === button) {
            switch(status) {
                case 'Present':
                    btn.classList.add('btn-success');
                    break;
                case 'Absent':
                    btn.classList.add('btn-danger');
                    break;
                case 'Excused':
                    btn.classList.add('btn-warning');
                    break;
                case 'Late':
                    btn.classList.add('btn-secondary');
                    break;
            }
        } else {
            btn.classList.add('btn-outline-secondary');
        }
    });
    
    // Update hidden input
    const hiddenInput = document.getElementById(`attendance_${traineeId}_status`);
    if (hiddenInput) {
        hiddenInput.value = status;
    }
}

// Delete confirmation
function confirmDelete(activityId) {
    if (confirm('Are you sure you want to deactivate this activity? All associated sessions will also be deactivated.')) {
        document.getElementById(`delete-form-${activityId}`).submit();
    }
}