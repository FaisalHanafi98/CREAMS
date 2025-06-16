// ===========================
// CREAMS Activity Module JS
// ===========================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    initializeFilters();
    initializeSearch();
    initializeActivityCards();
    initializeSessionFilters();
    initializeAttendance();
    initializeModals();
    initializeFormValidation();
    initializeTooltips();
});

// Filter Functionality
function initializeFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!categoryFilter || !difficultyFilter || !statusFilter) return;
    
    // Add event listeners
    categoryFilter.addEventListener('change', filterActivities);
    difficultyFilter.addEventListener('change', filterActivities);
    statusFilter.addEventListener('change', filterActivities);
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    let searchTimer;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            filterActivities();
        }, 300);
    });
}

// Filter Activities
function filterActivities() {
    const categoryValue = document.getElementById('categoryFilter')?.value.toLowerCase() || '';
    const difficultyValue = document.getElementById('difficultyFilter')?.value.toLowerCase() || '';
    const statusValue = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    
    const cards = document.querySelectorAll('.activity-card');
    
    cards.forEach(card => {
        const category = card.getAttribute('data-category')?.toLowerCase() || '';
        const difficulty = card.getAttribute('data-difficulty')?.toLowerCase() || '';
        const status = card.getAttribute('data-status')?.toLowerCase() || '';
        const text = card.textContent.toLowerCase();
        
        const categoryMatch = !categoryValue || category.includes(categoryValue);
        const difficultyMatch = !difficultyValue || difficulty === difficultyValue;
        const statusMatch = !statusValue || status === statusValue;
        const searchMatch = !searchValue || text.includes(searchValue);
        
        if (categoryMatch && difficultyMatch && statusMatch && searchMatch) {
            card.style.display = '';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
        }
    });
    
    // Check if no results
    const visibleCards = document.querySelectorAll('.activity-card:not([style*="display: none"])');
    const emptyState = document.querySelector('.empty-state');
    
    if (visibleCards.length === 0 && !emptyState) {
        const grid = document.querySelector('.activities-grid');
        if (grid) {
            grid.innerHTML += `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No activities found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
            `;
        }
    } else if (visibleCards.length > 0 && emptyState) {
        emptyState.remove();
    }
}

// Activity Card Animations
function initializeActivityCards() {
    const cards = document.querySelectorAll('.activity-card, .stat-card, .form-card, .detail-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Session Filters
function initializeSessionFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            filterSessions(filter);
        });
    });
}

// Filter Sessions
function filterSessions(filter) {
    const rows = document.querySelectorAll('.session-row');
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        
        if (filter === 'all' || status === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Attendance Functionality
function initializeAttendance() {
    const attendanceOptions = document.querySelectorAll('.attendance-option input');
    
    attendanceOptions.forEach(option => {
        option.addEventListener('change', function() {
            const row = this.closest('tr');
            const noteInput = row.querySelector('input[type="text"]');
            
            // Add animation
            row.style.backgroundColor = '#f0f8ff';
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 500);
            
            // Enable/disable notes based on status
            if (this.value === 'absent' || this.value === 'excused') {
                noteInput.placeholder = 'Please provide a reason...';
                noteInput.focus();
            } else {
                noteInput.placeholder = 'Optional notes...';
            }
        });
    });
}

// Modal Functionality
function initializeModals() {
    // Session scheduling modal
    const createSessionModal = document.getElementById('createSessionModal');
    if (!createSessionModal) return;
    
    // Teacher availability check
    const teacherSelect = document.getElementById('teacher_id');
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    
    if (teacherSelect && dateInput && startTimeInput) {
        [dateInput, startTimeInput, teacherSelect].forEach(input => {
            input.addEventListener('change', checkTeacherAvailability);
        });
    }
}

// Check Teacher Availability
async function checkTeacherAvailability() {
    const teacherId = document.getElementById('teacher_id').value;
    const date = document.getElementById('date').value;
    const startTime = document.getElementById('start_time').value;
    
    if (!teacherId || !date || !startTime) return;
    
    // Show loading state
    const availabilityMessage = document.getElementById('availabilityMessage');
    if (!availabilityMessage) {
        const messageDiv = document.createElement('div');
        messageDiv.id = 'availabilityMessage';
        messageDiv.className = 'alert mt-2';
        document.getElementById('start_time').parentElement.appendChild(messageDiv);
    }
    
    // Simulate API call
    setTimeout(() => {
        const message = document.getElementById('availabilityMessage');
        const isAvailable = Math.random() > 0.3; // Simulate availability
        
        if (isAvailable) {
            message.className = 'alert alert-success mt-2';
            message.innerHTML = '<i class="fas fa-check-circle"></i> Teacher is available at this time';
        } else {
            message.className = 'alert alert-danger mt-2';
            message.innerHTML = '<i class="fas fa-times-circle"></i> Teacher has a conflict at this time';
        }
    }, 500);
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.activity-form, form[action*="session"], form[action*="attendance"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
    
    // Real-time validation for activity code
    const activityCodeInput = document.getElementById('activity_code');
    if (activityCodeInput) {
        activityCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
        });
    }
}

// Initialize Tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        // Bootstrap tooltip initialization if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(tooltip);
        }
    });
}

// Cancel Session Function
function cancelSession(sessionId) {
    if (!confirm('Are you sure you want to cancel this session? This action cannot be undone.')) {
        return;
    }
    
    // Show loading state
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Update UI
        const row = btn.closest('tr');
        const statusBadge = row.querySelector('.badge');
        statusBadge.className = 'badge badge-danger';
        statusBadge.textContent = 'Cancelled';
        
        // Remove action buttons
        btn.parentElement.innerHTML = '<span class="text-muted">Cancelled</span>';
        
        // Show success message
        showNotification('Session cancelled successfully', 'success');
    }, 1000);
}

// Show Notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    // Add CSS for toast positioning
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 300px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Activity Code Generator
function generateActivityCode() {
    const categorySelect = document.getElementById('category');
    const codeInput = document.getElementById('activity_code');
    
    if (!categorySelect || !codeInput) return;
    
    categorySelect.addEventListener('change', function() {
        if (codeInput.value) return; // Don't override existing code
        
        const category = this.value;
        let prefix = '';
        
        // Generate prefix based on category
        const categoryPrefixes = {
            'Physical Therapy': 'PHY',
            'Occupational Therapy': 'OCC',
            'Speech & Language Therapy': 'SPE',
            'Sensory Integration': 'SEN',
            'Social Skills Training': 'SOC',
            'Daily Living Skills': 'DLS',
            'Basic Mathematics': 'MAT',
            'Language & Literacy': 'LIT',
            'Science Exploration': 'SCI',
            'Art & Creativity': 'ART',
            'Music Therapy': 'MUS',
            'Computer Skills': 'COM'
        };
        
        prefix = categoryPrefixes[category] || 'ACT';
        
        // Generate random number
        const randomNum = Math.floor(Math.random() * 900) + 100;
        
        codeInput.value = `${prefix}-${randomNum}`;
    });
}

// Initialize code generator
document.addEventListener('DOMContentLoaded', generateActivityCode);

// Handle dynamic content loading
function reinitializeComponents() {
    initializeTooltips();
    initializeActivityCards();
}

// Export functions for use in other scripts
window.ActivityModule = {
    filterActivities,
    filterSessions,
    cancelSession,
    showNotification,
    reinitializeComponents
};