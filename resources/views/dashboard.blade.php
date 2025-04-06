<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>{{ $data['title'] }} - CREAMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" />
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
  <link rel="stylesheet" href="{{ asset('css/usermanagementstyle.css') }}">
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <img src="{{ asset('images/favicon.png') }}" alt="CREAMS Logo">
        <span class="logo-text">CREAMS</span>
      </div>
      <div class="toggle-btn">
        <i class="fas fa-chevron-left"></i>
      </div>
    </div>
    
    <div class="admin-profile">
      <div class="admin-avatar">
        @if(isset($user['avatar']) && $user['avatar'])
          <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="User Avatar">
        @else
          <img src="{{ asset('images/admin-avatar.jpg') }}" alt="User Avatar">
        @endif
      </div>
      <div class="admin-info">
        <div class="admin-name">{{ $user['name'] }}</div>
        <div class="admin-role">{{ ucfirst($user['role']) }}</div>
      </div>
    </div>
    
    <ul class="nav-menu">
      @foreach($data['menuItems'] as $item)
      <li class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
        <a href="{{ route($item['route']) }}">
          <i class="fas fa-{{ $item['icon'] }}"></i>
          <span>{{ $item['label'] }}</span>
          <div class="tooltip-sidebar">{{ $item['label'] }}</div>
        </a>
      </li>
      @endforeach
    </ul>
    
    <form method="POST" action="{{ route('logout') }}" class="logout-container">
      @csrf
      <button type="submit" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <div class="header-content">
        <div class="page-info">
          <h1 class="page-title">{{ $data['title'] }}</h1>
          <div class="breadcrumb">
            <span class="current">Dashboard</span>
          </div>
        </div>
        
        <div class="header-actions">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="search" placeholder="Search...">
          </div>
          
          <div class="notification-bell">
            <i class="fas fa-bell"></i>
            @if($notifications->where('read', false)->count() > 0)
              <span class="notification-count">{{ $notifications->where('read', false)->count() }}</span>
            @endif
          </div>
          
          <div class="admin-dropdown">
            <div class="admin-dropdown-toggle">
              @if(isset($user['avatar']) && $user['avatar'])
                <img src="{{ asset('storage/avatars/' . $user['avatar']) }}" alt="User">
              @else
                <img src="{{ asset('images/admin-avatar.jpg') }}" alt="User">
              @endif
              <span>{{ $user['name'] }}</span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="admin-dropdown-menu">
              <a href="{{ route('profile') }}">
                <i class="fas fa-user"></i> My Profile
              </a>
              @if($user['role'] == 'admin')
                <a href="{{ route('admin.settings') }}">
                  <i class="fas fa-cog"></i> Settings
                </a>
              @endif
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                  <i class="fas fa-sign-out-alt"></i> Logout
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Content -->
    <div class="content-section">
      <!-- Stats Cards -->
      <div class="dashboard-cards">
        @foreach($data['stats'] as $stat)
        <div class="card">
          <div class="card-body">
            <div class="stats-card">
              <div class="stats-icon">
                <i class="fas fa-{{ $stat['icon'] }}"></i>
              </div>
              <div class="stats-details">
                <div class="stats-value">{{ $stat['value'] }}</div>
                <div class="stats-label">{{ $stat['title'] }}</div>
                @if(isset($stat['change']))
                <div class="stats-change {{ $stat['type'] ?? 'neutral' }}">
                  @if($stat['type'] == 'positive')
                    <i class="fas fa-arrow-up"></i>
                  @elseif($stat['type'] == 'negative')
                    <i class="fas fa-arrow-down"></i>
                  @else
                    <i class="fas fa-minus"></i>
                  @endif
                  {{ $stat['change'] }}
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      
      <!-- Role-specific dashboard content -->
      <div class="row">
        <div class="col-lg-8">
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="card-title">{{ ucfirst($user['role']) }} Overview</h5>
            </div>
            <div class="card-body">
              <!-- Role-specific content loaded from partial -->
              @include("dashboard.{$user['role']}")
            </div>
          </div>
        </div>
        
        <div class="col-lg-4">
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="card-title">Recent Notifications</h5>
              <div class="card-options">
                <a href="{{ route('notifications') }}" class="btn btn-sm btn-link">View All</a>
              </div>
            </div>
            <div class="card-body">
              @if($notifications->count() > 0)
                <div class="notifications-list">
                  @foreach($notifications as $notification)
                    <div class="notification-item {{ !$notification->read ? 'unread' : '' }}">
                      <div class="notification-icon notification-{{ $notification->type }}">
                        @if($notification->type == 'info')
                          <i class="fas fa-info-circle"></i>
                        @elseif($notification->type == 'success')
                          <i class="fas fa-check-circle"></i>
                        @elseif($notification->type == 'warning')
                          <i class="fas fa-exclamation-triangle"></i>
                        @elseif($notification->type == 'danger')
                          <i class="fas fa-times-circle"></i>
                        @endif
                      </div>
                      <div class="notification-content">
                        <h6 class="notification-title">{{ $notification->title }}</h6>
                        <p class="notification-message">{{ Str::limit($notification->message, 100) }}</p>
                        <span class="notification-time">
                          {{ $notification->created_at->diffForHumans() }}
                        </span>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="empty-state">
                  <div class="empty-state-icon">
                    <i class="fas fa-bell-slash"></i>
                  </div>
                  <p>No notifications yet</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="dashboard-footer">
      <div class="footer-content">
        <div class="footer-logo">
          <img src="{{ asset('images/favicon.png') }}" alt="CREAMS Logo">
          <span>CREAMS</span>
        </div>
        <div class="footer-text">
          Community-based REhAbilitation Management System &copy; {{ date('Y') }} IIUM
        </div>
        <div class="footer-links">
          <a href="#" class="footer-link">Privacy Policy</a>
          <a href="#" class="footer-link">Terms of Service</a>
          <a href="#" class="footer-link">Help Centre</a>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript Dependencies -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Sidebar toggle
      $('.toggle-btn').click(function() {
        $('.sidebar').toggleClass('collapsed');
        $('.main-content').toggleClass('expanded');
      });
      
      // Admin dropdown
      $('.admin-dropdown-toggle').click(function() {
        $('.admin-dropdown-menu').toggleClass('show');
      });
      
      // Close dropdown when clicking outside
      $(document).click(function(event) {
        if (!$(event.target).closest('.admin-dropdown').length) {
          $('.admin-dropdown-menu').removeClass('show');
        }
      });
      
      // Notification bell dropdown
      $('.notification-bell').click(function() {
        window.location.href = "{{ route('notifications') }}";
      });
      
      // Load role-specific charts
      const userRole = "{{ $user['role'] }}";
      if (userRole === 'admin') {
        initializeAdminCharts();
      } else if (userRole === 'supervisor') {
        initializeSupervisorCharts();
      } else if (userRole === 'teacher') {
        initializeTeacherCharts();
      } else if (userRole === 'ajk') {
        initializeAJKCharts();
      }
    });
    
    // Role-specific initialization functions
    function initializeAdminCharts() {
      // User registration chart
      const userRegistrationCtx = document.getElementById('userRegistrationChart')?.getContext('2d');
      if (userRegistrationCtx) {
        const userRegistrationChart = new Chart(userRegistrationCtx, {
          type: 'line',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
              label: 'User Registrations',
              data: [5, 10, 8, 15, 12, 9],
              backgroundColor: 'rgba(50, 189, 234, 0.2)',
              borderColor: 'rgba(50, 189, 234, 1)',
              borderWidth: 2,
              tension: 0.3,
              pointBackgroundColor: 'rgba(50, 189, 234, 1)'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });
      }
      
      // User role distribution chart
      const userRoleCtx = document.getElementById('userRoleChart')?.getContext('2d');
      if (userRoleCtx) {
        const userRoleChart = new Chart(userRoleCtx, {
          type: 'doughnut',
          data: {
            labels: ['Admins', 'Supervisors', 'Teachers', 'AJKs'],
            datasets: [{
              data: [5, 12, 28, 8],
              backgroundColor: [
                'rgba(78, 115, 223, 0.7)',
                'rgba(54, 185, 204, 0.7)',
                'rgba(28, 200, 138, 0.7)',
                'rgba(246, 194, 62, 0.7)'
              ],
              borderColor: [
                'rgba(78, 115, 223, 1)',
                'rgba(54, 185, 204, 1)',
                'rgba(28, 200, 138, 1)',
                'rgba(246, 194, 62, 1)'
              ],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
              legend: {
                position: 'bottom'
              }
            }
          }
        });
      }
    }
    
    function initializeSupervisorCharts() {
      // Teacher performance chart
      const teacherPerfCtx = document.getElementById('teacherPerfChart')?.getContext('2d');
      if (teacherPerfCtx) {
        const teacherPerfChart = new Chart(teacherPerfCtx, {
          type: 'bar',
          data: {
            labels: ['Teacher A', 'Teacher B', 'Teacher C', 'Teacher D', 'Teacher E'],
            datasets: [{
              label: 'Classes',
              data: [3, 5, 2, 4, 3],
              backgroundColor: 'rgba(50, 189, 234, 0.7)',
              borderColor: 'rgba(50, 189, 234, 1)',
              borderWidth: 1
            }, {
              label: 'Tainees',
              data: [25, 40, 18, 35, 30],
              backgroundColor: 'rgba(200, 80, 192, 0.7)',
              borderColor: 'rgba(200, 80, 192, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });
      }
    }
    
    function initializeTeacherCharts() {
      // Tainee attendance chart
      const attendanceCtx = document.getElementById('attendanceChart')?.getContext('2d');
      if (attendanceCtx) {
        const attendanceChart = new Chart(attendanceCtx, {
          type: 'line',
          data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
              label: 'Attendance (%)',
              data: [90, 88, 92, 95, 91, 93],
              backgroundColor: 'rgba(50, 189, 234, 0.2)',
              borderColor: 'rgba(50, 189, 234, 1)',
              borderWidth: 2,
              tension: 0.3,
              pointBackgroundColor: 'rgba(50, 189, 234, 1)'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: false,
                min: 80,
                max: 100
              }
            }
          }
        });
      }
    }
    
    function initializeAJKCharts() {
      // Event participation chart
      const eventParticipationCtx = document.getElementById('eventParticipationChart')?.getContext('2d');
      if (eventParticipationCtx) {
        const eventParticipationChart = new Chart(eventParticipationCtx, {
          type: 'bar',
          data: {
            labels: ['Event A', 'Event B', 'Event C', 'Event D'],
            datasets: [{
              label: 'Participants',
              data: [45, 32, 28, 50],
              backgroundColor: 'rgba(50, 189, 234, 0.7)',
              borderColor: 'rgba(50, 189, 234, 1)',
              borderWidth: 1
            }, {
              label: 'Max Capacity',
              data: [50, 40, 30, 60],
              backgroundColor: 'rgba(200, 80, 192, 0.2)',
              borderColor: 'rgba(200, 80, 192, 1)',
              borderWidth: 1,
              type: 'line'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });
      }
    }
  </script>
</body>
</html>