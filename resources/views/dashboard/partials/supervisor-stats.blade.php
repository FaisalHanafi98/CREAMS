<div class="stat-card stat-card-primary">
    <div class="stat-icon">
        <i class="fas fa-chalkboard-teacher"></i>
    </div>
    <div class="stat-content">
        <h3 class="stat-value">{{ $mySessions }}</h3>
        <p class="stat-label">My Sessions</p>
    </div>
</div>

<div class="stat-card stat-card-success">
    <div class="stat-icon">
        <i class="fas fa-user-graduate"></i>
    </div>
    <div class="stat-content">
        <h3 class="stat-value">{{ $totalStudents }}</h3>
        <p class="stat-label">Total Students</p>
    </div>
</div>

<div class="stat-card stat-card-warning">
    <div class="stat-icon">
        <i class="fas fa-clock"></i>
    </div>
    <div class="stat-content">
        <h3 class="stat-value">{{ count($todaySessions) }}</h3>
        <p class="stat-label">Today's Sessions</p>
    </div>
</div>

<div class="stat-card stat-card-danger">
    <div class="stat-icon">
        <i class="fas fa-clipboard-check"></i>
    </div>
    <div class="stat-content">
        <h3 class="stat-value">{{ $attendanceToMark }}</h3>
        <p class="stat-label">Pending Attendance</p>
    </div>
</div>