<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable</title>
    <link rel="stylesheet" href="{{ asset('css/schedulehomestyle.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="container">
        <nav>
            <ul class="sidebar-menu">
                <li class="{{ Route::currentRouteNamed('accountprofile') ? 'active' : '' }}">
                    <a href="{{ route('accountprofile') }}" class="logo">
                        <img src="{{ asset(Auth::user()->user_avatar) }}">
                        <div class="user-details">
                            <span class="nav-item">{{ Auth::user()->user_first_name }}</span>
                            <span class="nav-item role">{{ Auth::user()->role }}</span>
                        </div>
                    </a>
                </li>
                <li class="{{ Route::currentRouteNamed('register') ? 'active' : '' }}">
                    <a href="{{ route('register') }}">
                        <i class="fas fa-exclamation"></i>
                        <span class="nav-item">Register</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteNamed('teachershome', 'updateuserpage') ? 'active' : '' }}">
                    <a href="{{ route('teachershome') }}">
                        <i class="fas fa-person-chalkboard"></i>
                        <span class="nav-item">Staff</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteNamed('traineeshome') ? 'active' : '' }}">
                    <a href="{{ route('traineeshome') }}">
                        <i class="fas fa-address-card"></i>
                        <span class="nav-item">Trainee</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteNamed('teachershome', 'schedulehomepage') ? 'active' : '' }}">
                    <a href="{{ route('schedulehomepage') }}">
                        <i class="fas fa-calendar-days"></i>
                        <span class="nav-item">Schedule</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteNamed('assetmanagementpage') ? 'active' : '' }}">
                    <a href="{{ route('assetmanagementpage') }}">
                        <i class="fas fa-chair"></i>
                        <span class="nav-item">Asset</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteNamed('aboutus') ? 'active' : '' }}">
                    <a href="{{ route('aboutus') }}">
                        <i class="fas fa-info"></i>
                        <span class="nav-item">About</span>
                    </a>
                </li>
                <li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="{{ route('logout') }}" class="logout"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-item">Log out</span>
                    </a>
                </li>
            </ul>
        </nav>

        <section class="activity-schedule">
            <div class="logo">
                <h1 class="logo-heading">CREAMS</h1>
                
            </div>
            <span class="small-text">Community-based REhAbilitation Management System</span>
            <br>
            <div class="page-heading">
                <h2 class="subheading">Activity Schedule</h2>
            </div>
            
            <br>
            <div class="messages">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <a href="{{ route('courseregistration') }}" class="btn btn-primary add-activity-btn">
                <i class="las la-plus mr-3"></i>Add Activity
            </a>

            <br>
            <br>
            <br>
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table id="data-table" class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Course ID</th>
                                <th>Type</th>
                                <th>Teacher</th>
                                <th>Participants</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courses as $index => $course)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $course->course_id }}</td>
                                    <td>{{ $course->course_type }}</td>
                                    <td>{{ $course->teacher->user_first_name }}</td>
                                    <td>{{ $course->participant->trainee_first_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($course->course_day)->format('l') }}</td>
                                    <td>{{ $course->start_time }}</td>
                                    <td>{{ $course->end_time }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
</body>

</html>
