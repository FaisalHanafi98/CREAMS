@extends('layouts.app')

@section('title', 'My Schedule')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-user me-2"></i>My Personal Schedule
            </h1>
            <p class="mb-0 text-muted">Your personalized activity schedule calendar</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('activities.schedule') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i>View All Sessions
            </a>
        </div>
    </div>

    <!-- Calendar Controls -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <button id="prevMonth" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 id="currentMonth" class="mb-0 fw-bold text-primary"></h4>
                        <button id="nextMonth" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button id="todayBtn" class="btn btn-primary btn-sm ms-2">Today</button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="calendarView" id="monthView" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="monthView">Month</label>
                        
                        <input type="radio" class="btn-check" name="calendarView" id="weekView">
                        <label class="btn btn-outline-secondary btn-sm" for="weekView">Week</label>
                        
                        <input type="radio" class="btn-check" name="calendarView" id="dayView">
                        <label class="btn btn-outline-secondary btn-sm" for="dayView">Day</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <!-- Month View -->
            <div id="monthViewContainer" class="calendar-view">
                <div class="calendar-header border-bottom p-3 bg-light">
                    <div class="row text-center fw-bold text-muted small">
                        <div class="col">SUN</div>
                        <div class="col">MON</div>
                        <div class="col">TUE</div>
                        <div class="col">WED</div>
                        <div class="col">THU</div>
                        <div class="col">FRI</div>
                        <div class="col">SAT</div>
                    </div>
                </div>
                <div id="calendarGrid" class="calendar-grid"></div>
            </div>

            <!-- Week View -->
            <div id="weekViewContainer" class="calendar-view d-none">
                <div class="week-header border-bottom p-3 bg-light">
                    <div class="row">
                        <div class="col-1 text-center fw-bold small text-muted">TIME</div>
                        <div class="col-11">
                            <div class="row text-center fw-bold small text-muted" id="weekDaysHeader"></div>
                        </div>
                    </div>
                </div>
                <div id="weekGrid" class="week-grid"></div>
            </div>

            <!-- Day View -->
            <div id="dayViewContainer" class="calendar-view d-none">
                <div class="day-header border-bottom p-3 bg-light">
                    <h5 id="dayViewDate" class="mb-0 fw-bold text-center"></h5>
                </div>
                <div id="dayGrid" class="day-grid p-3"></div>
            </div>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div class="modal fade" id="sessionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Session Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sessionModalBody">
                    <!-- Session details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Calendar Styling */
.calendar-grid {
    min-height: 500px;
}

.calendar-day {
    min-height: 120px;
    border-right: 1px solid #e3e6f0;
    border-bottom: 1px solid #e3e6f0;
    padding: 8px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.other-month {
    background-color: #f8f9fc;
    color: #8c8c8c;
}

.calendar-day.today {
    background-color: #e3f2fd;
    border: 2px solid #2196f3;
}

.calendar-day.has-sessions {
    background-color: #fff3e0;
}

.day-number {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}

.session-indicator {
    display: block;
    padding: 2px 6px;
    margin-bottom: 2px;
    border-radius: 3px;
    font-size: 10px;
    color: white;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    cursor: pointer;
}

.session-indicator:hover {
    opacity: 0.8;
    transform: translateY(-1px);
}

.session-count-more {
    font-size: 10px;
    color: #666;
    font-style: italic;
}

/* Week View */
.week-grid {
    max-height: 600px;
    overflow-y: auto;
}

.time-slot {
    border-bottom: 1px solid #f0f0f0;
    height: 60px;
    position: relative;
}

.time-label {
    font-size: 12px;
    color: #666;
    padding: 5px;
    border-right: 1px solid #e3e6f0;
    text-align: center;
}

.week-day-column {
    border-right: 1px solid #e3e6f0;
    position: relative;
    min-height: 60px;
}

/* Day View */
.day-session-item {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.2s ease;
}

.day-session-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

/* Personal Learning & IEP Enhancements */
.learning-outcomes-mini {
    padding: 4px 8px;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 4px;
    margin-top: 4px;
}

.personal-iep-mini {
    padding: 4px 8px;
    background: rgba(255, 193, 7, 0.1);
    border-radius: 4px;
}

.personal-outcome-card {
    background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
    border-left: 4px solid #28a745 !important;
    transition: transform 0.2s ease;
}

.personal-outcome-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
}

.personal-iep-goal-card {
    background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
    border-left: 4px solid #ffc107 !important;
    transition: transform 0.2s ease;
}

.personal-iep-goal-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
}

.progress-stat {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
}

.progress-stat h3 {
    margin-bottom: 0.25rem;
    font-weight: 700;
}

.competency-score {
    display: flex;
    align-items: center;
    justify-content: center;
}

.score-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.8rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.instructor-notes {
    border-left: 3px solid #17a2b8;
    font-style: italic;
}

.goal-meta .badge {
    margin-right: 0.5rem;
}

.personal-progress-overview {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #e9ecef;
}

.personal-iep-overview {
    background: linear-gradient(135deg, #fff9c4 0%, #ffffff 100%);
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #ffc107;
}

.learning-outcomes-section,
.personal-iep-section {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
}

/* Enhanced Calendar Session Items */
.day-session-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.day-session-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
    border-color: #007bff;
}

/* Responsive */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
        padding: 4px;
    }
    
    .session-indicator {
        font-size: 9px;
        padding: 1px 4px;
    }
    
    .learning-outcomes-mini,
    .personal-iep-mini {
        margin-top: 6px;
        padding: 3px 6px;
    }
    
    .learning-outcomes-mini small,
    .personal-iep-mini small {
        font-size: 0.7rem;
    }
    
    .progress-stat {
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .score-circle {
        width: 35px;
        height: 35px;
        font-size: 0.7rem;
    }
    
    .day-session-item {
        padding: 10px;
        margin-bottom: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date();
    let currentView = 'month';
    let sessionsData = @json($sessions);
    
    // Initialize calendar
    updateCalendar();
    
    // Event listeners
    document.getElementById('prevMonth').addEventListener('click', function() {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() - 1);
        } else if (currentView === 'week') {
            currentDate.setDate(currentDate.getDate() - 7);
        } else {
            currentDate.setDate(currentDate.getDate() - 1);
        }
        updateCalendar();
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() + 1);
        } else if (currentView === 'week') {
            currentDate.setDate(currentDate.getDate() + 7);
        } else {
            currentDate.setDate(currentDate.getDate() + 1);
        }
        updateCalendar();
    });
    
    document.getElementById('todayBtn').addEventListener('click', function() {
        currentDate = new Date();
        updateCalendar();
    });
    
    // View switcher
    document.querySelectorAll('input[name="calendarView"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            currentView = this.id.replace('View', '');
            switchView(currentView);
            updateCalendar();
        });
    });
    
    function updateCalendar() {
        updateHeader();
        
        if (currentView === 'month') {
            renderMonthView();
        } else if (currentView === 'week') {
            renderWeekView();
        } else {
            renderDayView();
        }
    }
    
    function updateHeader() {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        
        if (currentView === 'month') {
            document.getElementById('currentMonth').textContent = 
                monthNames[currentDate.getMonth()] + ' ' + currentDate.getFullYear();
        } else if (currentView === 'week') {
            const weekStart = getWeekStart(currentDate);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            document.getElementById('currentMonth').textContent = 
                formatDate(weekStart) + ' - ' + formatDate(weekEnd);
        } else {
            document.getElementById('currentMonth').textContent = formatDate(currentDate);
        }
    }
    
    function switchView(view) {
        document.querySelectorAll('.calendar-view').forEach(function(el) {
            el.classList.add('d-none');
        });
        document.getElementById(view + 'ViewContainer').classList.remove('d-none');
    }
    
    function renderMonthView() {
        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';
        
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        
        const today = new Date();
        
        for (let week = 0; week < 6; week++) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'row g-0';
            
            for (let day = 0; day < 7; day++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + (week * 7) + day);
                
                const dayDiv = document.createElement('div');
                dayDiv.className = 'col calendar-day';
                
                // Add classes
                if (date.getMonth() !== currentDate.getMonth()) {
                    dayDiv.classList.add('other-month');
                }
                if (isSameDay(date, today)) {
                    dayDiv.classList.add('today');
                }
                
                // Get sessions for this date
                const daySessions = getSessionsForDate(date);
                if (daySessions.length > 0) {
                    dayDiv.classList.add('has-sessions');
                }
                
                // Day number
                const dayNumber = document.createElement('div');
                dayNumber.className = 'day-number';
                dayNumber.textContent = date.getDate();
                dayDiv.appendChild(dayNumber);
                
                // Sessions
                daySessions.slice(0, 3).forEach(function(session) {
                    const sessionDiv = document.createElement('div');
                    sessionDiv.className = 'session-indicator';
                    sessionDiv.style.backgroundColor = session.color_code || '#3498db';
                    sessionDiv.textContent = formatTime(session.start_time) + ' ' + (session.activity?.activity_name || 'Session');
                    sessionDiv.addEventListener('click', function(e) {
                        e.stopPropagation();
                        showSessionDetails(session);
                    });
                    dayDiv.appendChild(sessionDiv);
                });
                
                if (daySessions.length > 3) {
                    const moreDiv = document.createElement('div');
                    moreDiv.className = 'session-count-more';
                    moreDiv.textContent = '+' + (daySessions.length - 3) + ' more';
                    dayDiv.appendChild(moreDiv);
                }
                
                // Click handler for day
                dayDiv.addEventListener('click', function() {
                    if (daySessions.length > 0) {
                        showDayDetails(date, daySessions);
                    }
                });
                
                rowDiv.appendChild(dayDiv);
            }
            
            grid.appendChild(rowDiv);
            
            // Stop if we've filled the month
            if (week === 4 && startDate.getDate() + 35 > lastDay.getDate()) {
                break;
            }
        }
    }
    
    function renderWeekView() {
        // Implementation for week view
        const grid = document.getElementById('weekGrid');
        grid.innerHTML = '<div class="p-4 text-center text-muted">Week view implementation coming soon...</div>';
    }
    
    function renderDayView() {
        const grid = document.getElementById('dayGrid');
        const daySessions = getSessionsForDate(currentDate);
        
        document.getElementById('dayViewDate').textContent = formatDate(currentDate);
        
        if (daySessions.length === 0) {
            grid.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-calendar-times fa-2x mb-3"></i><br>No sessions scheduled for this day</div>';
            return;
        }
        
        grid.innerHTML = '';
        daySessions.forEach(function(session) {
            const sessionDiv = document.createElement('div');
            sessionDiv.className = 'day-session-item';
            sessionDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start mb-2">
                            <h6 class="mb-0 fw-bold me-2">${session.activity?.activity_name || 'Session'}</h6>
                            ${session.activity?.difficulty_level ? `<span class="badge bg-info text-white small"><i class="fas fa-layer-group me-1"></i>${session.activity.difficulty_level}</span>` : ''}
                        </div>
                        <p class="mb-2 text-muted small">${session.session_description || 'No description'}</p>
                        <div class="d-flex gap-3 small text-muted mb-2">
                            <span><i class="fas fa-clock me-1"></i>${formatTime(session.start_time)} - ${formatTime(session.end_time)}</span>
                            <span><i class="fas fa-map-marker-alt me-1"></i>${session.venue || 'Location TBD'}</span>
                        </div>
                        <!-- Learning Outcomes Preview -->
                        ${session.activity?.learning_outcomes_count > 0 ? `
                            <div class="learning-outcomes-mini">
                                <small class="text-success">
                                    <i class="fas fa-graduation-cap me-1"></i>${session.activity.learning_outcomes_count} Learning Outcomes
                                </small>
                                <button class="btn btn-link btn-sm p-0 ms-2" onclick="showPersonalOutcomes(${session.activity_id})" style="font-size: 0.75rem;">
                                    View My Progress
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-${getStatusBadgeClass(session.status)} mb-2">${getStatusText(session.status)}</span>
                        <div class="small text-muted mb-2">${session.current_participants || 0}/${session.max_participants || 0} participants</div>
                        <!-- Personal IEP Progress -->
                        ${session.personal_iep_goals_count > 0 ? `
                            <div class="personal-iep-mini">
                                <small class="text-warning">
                                    <i class="fas fa-target me-1"></i>${session.personal_iep_goals_count} My IEP Goals
                                </small>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: ${session.personal_iep_progress || 0}%"></div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            sessionDiv.addEventListener('click', function() {
                showSessionDetails(session);
            });
            grid.appendChild(sessionDiv);
        });
    }
    
    // Helper functions
    function getSessionsForDate(date) {
        const dateStr = formatDateForComparison(date);
        return sessionsData.filter(function(session) {
            const sessionDate = session.display_date || session.session_date || session.scheduled_date;
            return sessionDate === dateStr;
        });
    }
    
    function formatDateForComparison(date) {
        return date.getFullYear() + '-' + 
               String(date.getMonth() + 1).padStart(2, '0') + '-' + 
               String(date.getDate()).padStart(2, '0');
    }
    
    function formatDate(date) {
        return date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    
    function formatTime(timeString) {
        if (!timeString) return '';
        const time = new Date('2000-01-01 ' + timeString);
        return time.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit', 
            hour12: true 
        });
    }
    
    function isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getFullYear() === date2.getFullYear();
    }
    
    function getWeekStart(date) {
        const result = new Date(date);
        result.setDate(result.getDate() - result.getDay());
        return result;
    }
    
    function getStatusBadgeClass(status) {
        const map = {
            'scheduled': 'primary',
            'ongoing': 'warning',
            'completed': 'success',
            'cancelled': 'danger'
        };
        return map[status] || 'secondary';
    }
    
    function getStatusText(status) {
        const map = {
            'scheduled': 'Future',
            'ongoing': 'Progress',
            'completed': 'Done',
            'cancelled': 'Cancelled'
        };
        return map[status] || 'Unknown';
    }
    
    function showSessionDetails(session) {
        const modalBody = document.getElementById('sessionModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Activity Information</h6>
                    <p><strong>Name:</strong> ${session.activity?.activity_name || 'Unknown'}</p>
                    <p><strong>Type:</strong> ${session.activity?.activity_type || 'N/A'}</p>
                    ${session.activity?.difficulty_level ? `<p><strong>Difficulty:</strong> <span class="badge bg-info">${session.activity.difficulty_level}</span></p>` : ''}
                    <p><strong>Description:</strong> ${session.session_description || 'No description'}</p>
                    ${session.activity?.learning_outcomes_count > 0 ? `
                        <div class="learning-outcomes-section mt-3">
                            <h6>Learning Outcomes <span class="badge bg-success">${session.activity.learning_outcomes_count}</span></h6>
                            <button class="btn btn-outline-success btn-sm" onclick="showPersonalProgress(${session.activity_id})">
                                <i class="fas fa-chart-line me-1"></i>View My Progress
                            </button>
                        </div>
                    ` : ''}
                </div>
                <div class="col-md-6">
                    <h6>Session Details</h6>
                    <p><strong>Date:</strong> ${formatDate(new Date(session.display_date))}</p>
                    <p><strong>Time:</strong> ${formatTime(session.start_time)} - ${formatTime(session.end_time)}</p>
                    <p><strong>Location:</strong> ${session.venue || 'TBD'}${session.room_number ? ' - Room ' + session.room_number : ''}</p>
                    <p><strong>Participants:</strong> ${session.current_participants || 0}/${session.max_participants || 0}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeClass(session.status)}">${getStatusText(session.status)}</span></p>
                    ${session.personal_iep_goals_count > 0 ? `
                        <div class="personal-iep-section mt-3">
                            <h6>My IEP Goals <span class="badge bg-warning">${session.personal_iep_goals_count}</span></h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" style="width: ${session.personal_iep_progress || 0}%">${session.personal_iep_progress || 0}%</div>
                            </div>
                            <button class="btn btn-outline-warning btn-sm" onclick="showPersonalIepGoals(${session.id})">
                                <i class="fas fa-target me-1"></i>View My Goals
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('sessionModal')).show();
    }
    
    function showDayDetails(date, sessions) {
        // Switch to day view and navigate to the selected date
        currentDate = new Date(date);
        document.getElementById('dayView').checked = true;
        currentView = 'day';
        switchView('day');
        updateCalendar();
    }
    
    // Personal Learning Progress Functions
    function showPersonalOutcomes(activityId) {
        fetch(`/my-learning-progress/${activityId}`)
            .then(response => response.json())
            .then(data => {
                let modalContent = `
                    <div class="modal fade" id="personalProgressModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>My Learning Progress</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="personal-progress-overview mb-4">
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <div class="progress-stat">
                                                    <h3 class="text-success">${data.achieved_count || 0}</h3>
                                                    <small class="text-muted">Achieved</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="progress-stat">
                                                    <h3 class="text-info">${data.in_progress_count || 0}</h3>
                                                    <small class="text-muted">In Progress</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="progress-stat">
                                                    <h3 class="text-warning">${data.not_started_count || 0}</h3>
                                                    <small class="text-muted">Not Started</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="personal-outcomes-list">
                `;
                
                data.outcomes.forEach(outcome => {
                    const progressClass = outcome.my_progress === 'Achieved' ? 'success' : 
                                        outcome.my_progress === 'In Progress' ? 'info' : 
                                        outcome.my_progress === 'Mastered' ? 'primary' : 'warning';
                    
                    modalContent += `
                        <div class="personal-outcome-card mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold">${outcome.outcome_title}</h6>
                                    <p class="text-muted small mb-2">${outcome.outcome_description}</p>
                                    <div class="outcome-badges">
                                        <span class="badge bg-info">${outcome.competency_level}</span>
                                        <span class="badge bg-${progressClass}">${outcome.my_progress || 'Not Started'}</span>
                                    </div>
                                </div>
                                <div class="personal-competency-level">
                                    ${outcome.my_competency_score ? `
                                        <div class="competency-score">
                                            <div class="score-circle bg-${progressClass}">
                                                ${outcome.my_competency_score}/10
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                            ${outcome.instructor_notes ? `
                                <div class="instructor-notes mt-2 p-2 bg-light rounded">
                                    <small><strong>Instructor Notes:</strong> ${outcome.instructor_notes}</small>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                
                modalContent += `
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('personalProgressModal');
                if (existingModal) existingModal.remove();
                
                // Add modal to body and show
                document.body.insertAdjacentHTML('beforeend', modalContent);
                new bootstrap.Modal(document.getElementById('personalProgressModal')).show();
            })
            .catch(error => {
                console.error('Error fetching personal progress:', error);
                alert('Error loading your learning progress. Please try again.');
            });
    }
    
    function showPersonalIepGoals(sessionId) {
        fetch(`/my-iep-goals/${sessionId}`)
            .then(response => response.json())
            .then(data => {
                let modalContent = `
                    <div class="modal fade" id="personalIepModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-target me-2"></i>My IEP Goals</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="personal-iep-overview mb-4">
                                        <div class="overall-progress">
                                            <h6>Overall IEP Progress</h6>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-success" style="width: ${data.overall_progress || 0}%">
                                                    ${data.overall_progress || 0}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="personal-iep-goals">
                `;
                
                data.goals.forEach(goal => {
                    const progressWidth = (goal.current_progress / goal.target_value) * 100;
                    const statusClass = goal.goal_status === 'active' ? 'primary' : 
                                      goal.goal_status === 'achieved' ? 'success' : 'warning';
                    
                    modalContent += `
                        <div class="personal-iep-goal-card mb-3 p-3 border rounded">
                            <div class="goal-header mb-2">
                                <h6 class="fw-bold mb-1">${goal.goal_description}</h6>
                                <div class="goal-meta">
                                    <span class="badge bg-${statusClass}">${goal.goal_status}</span>
                                    <span class="badge bg-info">${goal.priority_level} Priority</span>
                                    <small class="text-muted ms-2">Target: ${goal.target_completion_date}</small>
                                </div>
                            </div>
                            <div class="goal-progress mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small>Progress</small>
                                    <small>${goal.current_progress}/${goal.target_value} ${goal.measurement_unit || 'units'}</small>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: ${progressWidth}%"></div>
                                </div>
                            </div>
                            ${goal.recent_notes ? `
                                <div class="goal-notes">
                                    <small><strong>Recent Notes:</strong> ${goal.recent_notes}</small>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                
                modalContent += `
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('personalIepModal');
                if (existingModal) existingModal.remove();
                
                // Add modal to body and show
                document.body.insertAdjacentHTML('beforeend', modalContent);
                new bootstrap.Modal(document.getElementById('personalIepModal')).show();
            })
            .catch(error => {
                console.error('Error fetching personal IEP goals:', error);
                alert('Error loading your IEP goals. Please try again.');
            });
    }
    
    // Shortcut functions for inline buttons
    function showPersonalProgress(activityId) {
        showPersonalOutcomes(activityId);
    }
});
</script>
@endsection