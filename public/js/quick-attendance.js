/**
 * Quick Attendance Modal JavaScript
 * Handles the quick attendance functionality for dashboard
 */

class QuickAttendance {
    constructor() {
        this.modal = null;
        this.currentSessions = [];
        this.init();
    }

    init() {
        // Initialize modal if it doesn't exist
        this.createModal();
        
        // Bind events
        this.bindEvents();
        
        // Load initial data
        this.loadSessions();
    }

    createModal() {
        if (document.getElementById('quickAttendanceModal')) {
            this.modal = document.getElementById('quickAttendanceModal');
            return;
        }

        const modalHTML = `
            <div class="modal fade" id="quickAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="quickAttendanceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="quickAttendanceModalLabel">
                                <i class="fas fa-clock"></i> Quick Attendance - <span id="attendanceDate"></span>
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="loadingSpinner" class="text-center p-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading today's sessions...</p>
                            </div>
                            <div id="sessionsContainer" style="display: none;">
                                <div id="sessionsList"></div>
                            </div>
                            <div id="noSessionsMessage" style="display: none;" class="text-center p-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Sessions Today</h5>
                                <p class="text-muted">There are no active sessions scheduled for today.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" id="refreshSessionsBtn" class="btn btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('quickAttendanceModal');
    }

    bindEvents() {
        // Quick attendance button click
        document.addEventListener('click', (e) => {
            if (e.target.matches('#quickAttendanceBtn, #quickAttendanceBtn *')) {
                e.preventDefault();
                this.openModal();
            }
        });

        // Refresh button
        document.addEventListener('click', (e) => {
            if (e.target.matches('#refreshSessionsBtn')) {
                this.loadSessions();
            }
        });

        // Mark attendance button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mark-attendance-btn')) {
                const sessionId = e.target.dataset.sessionId;
                this.showAttendanceForm(sessionId);
            }
        });

        // Submit attendance form
        document.addEventListener('submit', (e) => {
            if (e.target.matches('#attendanceForm')) {
                e.preventDefault();
                this.submitAttendance();
            }
        });
    }

    async openModal() {
        if (this.modal) {
            $(this.modal).modal('show');
            await this.loadSessions();
        }
    }

    async loadSessions() {
        try {
            this.showLoading();
            
            const response = await fetch('/quick-attendance/', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.currentSessions = data.sessions;
                this.displaySessions(data.sessions, data.date, data.user_role);
            } else {
                this.showError('Failed to load sessions: ' + data.message);
            }
        } catch (error) {
            console.error('Error loading sessions:', error);
            this.showError('Failed to load sessions. Please try again.');
        }
    }

    showLoading() {
        document.getElementById('loadingSpinner').style.display = 'block';
        document.getElementById('sessionsContainer').style.display = 'none';
        document.getElementById('noSessionsMessage').style.display = 'none';
        document.getElementById('attendanceDate').textContent = new Date().toLocaleDateString();
    }

    displaySessions(sessions, date, userRole) {
        document.getElementById('loadingSpinner').style.display = 'none';
        document.getElementById('attendanceDate').textContent = new Date(date).toLocaleDateString();

        if (sessions.length === 0) {
            document.getElementById('noSessionsMessage').style.display = 'block';
            return;
        }

        document.getElementById('sessionsContainer').style.display = 'block';
        const container = document.getElementById('sessionsList');
        
        let html = '<div class="row">';
        
        sessions.forEach((session, index) => {
            const isMarked = session.attendance_marked;
            const canMark = !isMarked && session.enrolled_trainees.length > 0;
            
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card ${isMarked ? 'border-success' : 'border-primary'}">
                        <div class="card-header ${isMarked ? 'bg-success text-white' : 'bg-primary text-white'}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-alt"></i> ${session.activity_name}
                                </h6>
                                ${isMarked ? '<i class="fas fa-check-circle"></i>' : ''}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Time:</small><br>
                                    <strong>${session.start_time} - ${session.end_time}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Location:</small><br>
                                    <strong>${session.location || 'Not specified'}</strong>
                                </div>
                            </div>
                            ${userRole !== 'teacher' && session.teacher_name ? `
                                <div class="mt-2">
                                    <small class="text-muted">Teacher:</small><br>
                                    <strong>${session.teacher_name}</strong>
                                </div>
                            ` : ''}
                            <div class="mt-2">
                                <small class="text-muted">Enrolled Trainees:</small><br>
                                <span class="badge badge-info">${session.enrolled_trainees.length} trainees</span>
                            </div>
                            ${isMarked ? `
                                <div class="mt-3">
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check"></i> Attendance already marked
                                    </div>
                                </div>
                            ` : ''}
                            ${canMark ? `
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary btn-sm mark-attendance-btn" 
                                            data-session-id="${session.id}">
                                        <i class="fas fa-check"></i> Mark Attendance
                                    </button>
                                </div>
                            ` : (!isMarked && session.enrolled_trainees.length === 0 ? `
                                <div class="mt-3">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle"></i> No trainees enrolled
                                    </div>
                                </div>
                            ` : '')}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    showAttendanceForm(sessionId) {
        const session = this.currentSessions.find(s => s.id == sessionId);
        if (!session) {
            this.showError('Session not found');
            return;
        }

        const formHTML = `
            <div class="attendance-form-container">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-users"></i> Mark Attendance - ${session.activity_name}
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="attendanceForm">
                            <input type="hidden" name="session_id" value="${sessionId}">
                            
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Trainee Name</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                            <th>Late</th>
                                            <th>Excused</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${session.enrolled_trainees.map(trainee => `
                                            <tr>
                                                <td>
                                                    <strong>${trainee.name}</strong>
                                                    <input type="hidden" name="attendance_data[${trainee.enrollment_id}][enrollment_id]" value="${trainee.enrollment_id}">
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" 
                                                               id="present_${trainee.enrollment_id}" 
                                                               name="attendance_data[${trainee.enrollment_id}][status]" 
                                                               value="present" checked>
                                                        <label class="custom-control-label text-success" for="present_${trainee.enrollment_id}">
                                                            <i class="fas fa-check"></i>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" 
                                                               id="absent_${trainee.enrollment_id}" 
                                                               name="attendance_data[${trainee.enrollment_id}][status]" 
                                                               value="absent">
                                                        <label class="custom-control-label text-danger" for="absent_${trainee.enrollment_id}">
                                                            <i class="fas fa-times"></i>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" 
                                                               id="late_${trainee.enrollment_id}" 
                                                               name="attendance_data[${trainee.enrollment_id}][status]" 
                                                               value="late">
                                                        <label class="custom-control-label text-warning" for="late_${trainee.enrollment_id}">
                                                            <i class="fas fa-clock"></i>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" 
                                                               id="excused_${trainee.enrollment_id}" 
                                                               name="attendance_data[${trainee.enrollment_id}][status]" 
                                                               value="excused">
                                                        <label class="custom-control-label text-info" for="excused_${trainee.enrollment_id}">
                                                            <i class="fas fa-user-check"></i>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="attendanceNotes">Notes (Optional)</label>
                                <textarea class="form-control" id="attendanceNotes" name="notes" rows="2" 
                                          placeholder="Any additional notes about today's session..."></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="quickAttendance.cancelAttendanceForm()">
                                    <i class="fas fa-arrow-left"></i> Back to Sessions
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Attendance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('sessionsContainer').innerHTML = formHTML;
    }

    cancelAttendanceForm() {
        this.loadSessions();
    }

    async submitAttendance() {
        const form = document.getElementById('attendanceForm');
        const formData = new FormData(form);
        
        // Convert FormData to a more structured format
        const attendanceData = [];
        const sessionId = formData.get('session_id');
        const notes = formData.get('notes');
        
        // Process attendance data
        const attendanceEntries = {};
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('attendance_data[')) {
                const match = key.match(/attendance_data\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const enrollmentId = match[1];
                    const field = match[2];
                    
                    if (!attendanceEntries[enrollmentId]) {
                        attendanceEntries[enrollmentId] = {};
                    }
                    attendanceEntries[enrollmentId][field] = value;
                }
            }
        }
        
        // Convert to array format
        for (let enrollmentId in attendanceEntries) {
            attendanceData.push(attendanceEntries[enrollmentId]);
        }

        const submitData = {
            session_id: sessionId,
            attendance_data: attendanceData,
            notes: notes
        };

        try {
            // Disable submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const response = await fetch('/quick-attendance/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(submitData)
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Attendance marked successfully!', data.summary);
                setTimeout(() => {
                    this.loadSessions();
                }, 2000);
            } else {
                this.showError('Failed to save attendance: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error submitting attendance:', error);
            this.showError('Failed to save attendance. Please try again.');
            
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Attendance';
        }
    }

    showSuccess(message, summary) {
        let summaryText = '';
        if (summary) {
            summaryText = `
                <div class="mt-2">
                    <strong>Summary:</strong><br>
                    Present: ${summary.present}, Absent: ${summary.absent}, 
                    Late: ${summary.late}, Excused: ${summary.excused}
                </div>
            `;
        }

        const alertHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> ${message}
                ${summaryText}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        document.getElementById('sessionsContainer').innerHTML = alertHTML + document.getElementById('sessionsContainer').innerHTML;
    }

    showError(message) {
        const alertHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        const container = document.getElementById('sessionsContainer');
        if (container.style.display !== 'none') {
            container.innerHTML = alertHTML + container.innerHTML;
        } else {
            document.getElementById('loadingSpinner').innerHTML = alertHTML;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a page that should have quick attendance
    if (document.body.classList.contains('dashboard-page') || document.getElementById('quickAttendanceBtn')) {
        window.quickAttendance = new QuickAttendance();
    }
});