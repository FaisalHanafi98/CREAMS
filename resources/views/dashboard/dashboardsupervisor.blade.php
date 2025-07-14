<div class="supervisor-dashboard-content">
  <div class="row">
    <div class="col-md-12">
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="teacherPerfChart"></canvas>
      </div>
      <h6 class="text-centre mt-3">Teacher Performance Metrics</h6>
    </div>
  </div>
  
  <!-- Teacher Management Overview -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Teacher Overview</h5>
    </div>
    <div class="card-body">
      <div class="teacher-overview">
        <div class="row">
          @foreach($data['teacherManagement']['teachers'] ?? [] as $teacher)
          <div class="col-md-4">
            <div class="teacher-card">
              <div class="teacher-avatar">
                <img src="{{ asset($teacher->avatar ?? 'images/default-avatar.jpg') }}" alt="{{ $teacher->name }}">
              </div>
              <div class="teacher-info">
                <h5>{{ $teacher->name }}</h5>
                <div class="teacher-class-count">
                  <i class="fas fa-book"></i> {{ $teacher->classes()->count() }} Classes
                </div>
                <div class="teacher-trainee-count">
                  <i class="fas fa-users"></i> {{ $teacher->trainees()->count() }} Trainees
                </div>
                <div class="teacher-actions mt-2">
                  <a href="{{ route('supervisor.teacher.view', $teacher->id) }}" class="btn btn-sm btn-primary">View Profile</a>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  
  <!-- Pending Approvals -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Pending Approvals</h5>
    </div>
    <div class="card-body">
      @if(($data['teacherManagement']['pendingApprovals'] ?? 0) > 0)
      <div class="approval-list">
        <!-- List of activities/classes requiring supervisor approval -->
        <div class="approval-item">
          <div class="approval-info">
            <h5>Communication Skills Workshop</h5>
            <div class="approval-details">
              <span><i class="fas fa-user"></i> Requested by: Dr. Nurul Hafizah</span>
              <span><i class="fas fa-calendar"></i> March 28, 2025</span>
              <span><i class="fas fa-users"></i> 15 Trainees</span>
            </div>
          </div>
          <div class="approval-actions">
            <button class="btn btn-sm btn-success">Approve</button>
            <button class="btn btn-sm btn-danger">Reject</button>
          </div>
        </div>
      </div>
      @else
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <p>No pending approvals at this time</p>
      </div>
      @endif
    </div>
  </div>
</div>