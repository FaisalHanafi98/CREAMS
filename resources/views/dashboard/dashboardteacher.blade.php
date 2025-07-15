<div class="teacher-dashboard-content">
  <div class="row">
    <div class="col-md-12">
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="attendanceChart"></canvas>
      </div>
      <h6 class="text-centre mt-3">Weekly Attendance Rate</h6>
    </div>
  </div>
  
  <!-- Class Schedule -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Today's Schedule</h5>
      <div class="card-options">
        <a href="{{ route('teacher.schedule') }}" class="btn btn-sm btn-primary">View Full Schedule</a>
      </div>
    </div>
    <div class="card-body">
      @if(isset($data['todaySchedule']) && $data['todaySchedule']->count() > 0)
      <div class="today-schedule">
        @foreach($data['todaySchedule'] as $class)
        <div class="schedule-item">
          <div class="time-slot">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</div>
          <div class="class-details">
            <h5>{{ $class->name }}</h5>
            <div class="class-info">
              <span><i class="fas fa-users"></i> {{ $class->trainees()->count() }} Trainees</span>
              <span><i class="fas fa-map-marker-alt"></i> {{ $class->location }}</span>
            </div>
          </div>
          <div class="class-actions">
            <a href="{{ route('teacher.class.attendance', $class->id) }}" class="btn btn-sm btn-primary">Take Attendance</a>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <p>No classes scheduled for today</p>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Trainee Progress -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Trainee Progress Monitoring</h5>
    </div>
    <div class="card-body">
      <div class="trainee-list">
        @foreach($data['classList'] ?? [] as $class)
          @foreach($class->trainees()->get() as $trainee)
          <div class="trainee-item">
            <div class="trainee-info">
              <div class="trainee-avatar">
                <img src="{{ asset($trainee->trainee_avatar ?? 'images/default-avatar.jpg') }}" alt="{{ $trainee->trainee_first_name }}">
              </div>
              <div class="trainee-details">
                <h5>{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</h5>
                <div class="trainee-condition">{{ $trainee->trainee_condition }}</div>
              </div>
            </div>
            <div class="trainee-actions">
              <a href="{{ route('teacher.trainee.view', $trainee->id) }}" class="btn btn-sm btn-info">View Progress</a>
              <a href="{{ route('teacher.trainee.progress.update', $trainee->id) }}" class="btn btn-sm btn-primary">Update Progress</a>
            </div>
          </div>
          @endforeach
        @endforeach
      </div>
    </div>
  </div>
</div>