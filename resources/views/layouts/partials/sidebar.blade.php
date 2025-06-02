{{-- This would typically be in resources/views/layouts/partials/sidebar.blade.php --}}

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo" class="sidebar-logo">
        <span class="sidebar-brand-text">CREAMS</span>
    </div>
    
    <div class="sidebar-divider"></div>
    
    <!-- Dashboard -->
    <div class="sidebar-heading">Main</div>
    
    <a href="{{ route(session('role') . '.dashboard') }}" class="sidebar-link {{ Route::is(session('role') . '.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
    
    <!-- Staff Management Section -->
    <div class="sidebar-heading">Staff</div>
    
    @php
        $role = session('role');
        $usersRoute = null;
        
        // Determine the best route to use for Users
        if (Route::has($role . '.users')) {
            $usersRoute = route($role . '.users');
        } elseif (Route::has('users')) {
            $usersRoute = route('users');
        } else {
            $usersRoute = route($role . '.dashboard');
        }
        
        // Teachers route for supervisors
        $teachersRoute = null;
        if ($role === 'supervisor' && Route::has('supervisor.teachers')) {
            $teachersRoute = route('supervisor.teachers');
        } elseif ($role === 'admin' && Route::has('admin.users.teachers')) {
            $teachersRoute = route('admin.users.teachers');
        } elseif (Route::has($role . '.users')) {
            $teachersRoute = route($role . '.users');
        } else {
            $teachersRoute = route($role . '.dashboard');
        }
    @endphp
    
    <a href="{{ $usersRoute }}" class="sidebar-link {{ Route::is($role . '.users') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <span>Staff Management</span>
    </a>
    
    @if($role === 'admin' || $role === 'supervisor')
    <a href="{{ $teachersRoute }}" class="sidebar-link {{ Route::is($role . '.teachers') || Route::is($role . '.users.teachers') ? 'active' : '' }}">
        <i class="fas fa-chalkboard-teacher"></i>
        <span>Teachers</span>
    </a>
    @endif
    
    <!-- Trainee Management Section -->
    <div class="sidebar-heading">Trainees</div>
    
    <a href="{{ route('traineeshome') }}" class="sidebar-link {{ Route::is('traineeshome') ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i>
        <span>Trainees</span>
    </a>
    
    <a href="{{ route('traineesregistrationpage') }}" class="sidebar-link {{ Route::is('traineesregistrationpage') ? 'active' : '' }}">
        <i class="fas fa-user-plus"></i>
        <span>Add Trainee</span>
    </a>
    
    <a href="{{ route('traineeactivity') }}" class="sidebar-link {{ Route::is('traineeactivity') ? 'active' : '' }}">
        <i class="fas fa-tasks"></i>
        <span>Trainee Activities</span>
    </a>
    
    <!-- Center Management Section -->
    <div class="sidebar-heading">Centers</div>
    
    @php
        $centresRoute = null;
        
        // Determine the best route to use for Centres
        if (Route::has($role . '.centres')) {
            $centresRoute = route($role . '.centres');
        } elseif (Route::has('centres')) {
            $centresRoute = route('centres');
        } else {
            $centresRoute = route($role . '.dashboard');
        }
    @endphp
    
    <a href="{{ $centresRoute }}" class="sidebar-link {{ Route::is($role . '.centres') || Route::is('centres') ? 'active' : '' }}">
        <i class="fas fa-building"></i>
        <span>Centres</span>
    </a>
    
    @php
        $assetsRoute = null;
        
        // Determine the best route to use for Assets
        if (Route::has($role . '.assets')) {
            $assetsRoute = route($role . '.assets');
        } elseif (Route::has('assets')) {
            $assetsRoute = route('assets');
        } else {
            $assetsRoute = route($role . '.dashboard');
        }
    @endphp
    
    <a href="{{ $assetsRoute }}" class="sidebar-link {{ Route::is($role . '.assets') || Route::is('assets') ? 'active' : '' }}">
        <i class="fas fa-boxes"></i>
        <span>Assets</span>
    </a>
    
    <!-- Activities Section -->
    <div class="sidebar-heading">Activities</div>
    
    <a href="{{ route('rehabilitation.categories') }}" class="sidebar-link {{ Route::is('rehabilitation.categories') || Route::is('rehabilitation.categories.*') ? 'active' : '' }}">
        <i class="fas fa-heartbeat"></i>
        <span>Rehabilitation</span>
    </a>
    
    @php
        $activitiesRoute = null;
        
        // Determine the best route to use for Activities
        if (Route::has($role . '.activities')) {
            $activitiesRoute = route($role . '.activities');
        } elseif (Route::has('activities.index')) {
            $activitiesRoute = route('activities.index');
        } else {
            $activitiesRoute = route($role . '.dashboard');
        }
    @endphp
    
    <a href="{{ $activitiesRoute }}" class="sidebar-link {{ Route::is($role . '.activities') || Route::is('activities.index') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i>
        <span>Activities</span>
    </a>
    
    @if($role === 'teacher')
    <a href="{{ route('teacher.schedule') }}" class="sidebar-link {{ Route::is('teacher.schedule') ? 'active' : '' }}">
        <i class="fas fa-calendar-week"></i>
        <span>Schedule</span>
    </a>
    @endif
    
    @if($role === 'ajk')
    <a href="{{ route('ajk.events') }}" class="sidebar-link {{ Route::is('ajk.events') || Route::is('ajk.event.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-day"></i>
        <span>Events</span>
    </a>
    @endif
    
    <!-- Reports and Settings Section -->
    <div class="sidebar-heading">System</div>
    
    @php
        $reportsRoute = null;
        
        // Determine the best route to use for Reports
        if (Route::has($role . '.reports')) {
            $reportsRoute = route($role . '.reports');
        } else {
            $reportsRoute = route($role . '.dashboard');
        }
    @endphp
    
    <a href="{{ $reportsRoute }}" class="sidebar-link {{ Route::is($role . '.reports') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i>
        <span>Reports</span>
    </a>
    
    @php
        $settingsRoute = null;
        
        // Determine the best route to use for Settings
        if (Route::has($role . '.settings')) {
            $settingsRoute = route($role . '.settings');
        } else {
            $settingsRoute = route($role . '.dashboard');
        }
    @endphp
    
    <a href="{{ $settingsRoute }}" class="sidebar-link {{ Route::is($role . '.settings') ? 'active' : '' }}">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
    </a>
    
    <!-- User Menu Section -->
    <div class="sidebar-divider"></div>
    
    <a href="{{ route('profile') }}" class="sidebar-link {{ Route::is('profile') ? 'active' : '' }}">
        <i class="fas fa-user-circle"></i>
        <span>My Profile</span>
    </a>
    
    <a href="{{ route('logout') }}" class="sidebar-link" 
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>
<!-- End of Sidebar -->