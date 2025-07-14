<div class="admin-dashboard-content">
  <div class="row">
    <div class="col-md-6">
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="userRegistrationChart"></canvas>
      </div>
      <h6 class="text-centre mt-3">Monthly User Registrations</h6>
    </div>
    <div class="col-md-6">
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="userRoleChart"></canvas>
      </div>
      <h6 class="text-centre mt-3">User Role Distribution</h6>
    </div>
  </div>
  
  <!-- Last Accessed Profile Card -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Last Accessed Profile</h5>
    </div>
    <div class="card-body">
      <div class="profile-card">
        <div class="profile-avatar">
          <img src="{{ asset('images/trainee-avatar.jpg') }}" alt="Trainee Profile">
        </div>
        <div class="profile-details">
          <h5>Ahmad Ismail</h5>
          <div class="profile-info">
            <p><strong>Condition:</strong> Autism Spectrum Disorder</p>
            <p><strong>Progress:</strong> 75% Complete</p>
            <p><strong>Last Session:</strong> March 22, 2025</p>
          </div>
          <a href="#" class="btn btn-sm btn-primary">View Profile</a>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Current Courses In Progress -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Current Courses In Progress</h5>
    </div>
    <div class="card-body">
      <div class="course-progress-list">
        <div class="course-item">
          <div class="course-info">
            <h5>Communication Skills Development</h5>
            <div class="course-meta">
              <span><i class="fas fa-user-tie"></i> Dr. Nurul Hafizah</span>
              <span><i class="fas fa-users"></i> 15 Trainees</span>
              <span><i class="fas fa-calendar-alt"></i> Mon, Wed, Fri</span>
            </div>
            <div class="progress mt-2">
              <div class="progress-bar" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">65%</div>
            </div>
          </div>
        </div>
        
        <div class="course-item">
          <div class="course-info">
            <h5>Motor Skills Training</h5>
            <div class="course-meta">
              <span><i class="fas fa-user-tie"></i> Mr. Ismail Rahman</span>
              <span><i class="fas fa-users"></i> 12 Trainees</span>
              <span><i class="fas fa-calendar-alt"></i> Tue, Thu</span>
            </div>
            <div class="progress mt-2">
              <div class="progress-bar" role="progressbar" style="width: 42%" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100">42%</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>