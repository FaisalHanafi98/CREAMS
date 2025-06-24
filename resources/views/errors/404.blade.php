<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - CREAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboardstyle.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="fas fa-search"></i> Page Not Found</h4>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-1 text-warning">404</h1>
                        <h2>Oops! Page not found</h2>
                        <p class="lead">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                        
                        <div class="mt-4">
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="fas fa-home"></i> Return Home
                            </a>
                            @if(session('role'))
                                <a href="{{ route(session('role') . '.dashboard') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-dashboard"></i> Go to Dashboard
                                </a>
                            @endif
                        </div>
                        
                        <div class="mt-4">
                            <h5>Quick Links:</h5>
                            <ul class="list-unstyled">
                                @if(session('role'))
                                    @if(in_array(session('role'), ['admin', 'supervisor', 'teacher']))
                                        <li><a href="{{ route('activities.index') }}">Activities</a></li>
                                        <li><a href="{{ route('teachershome') }}">Staff Directory</a></li>
                                        <li><a href="{{ route('trainees.home') }}">Trainees</a></li>
                                    @endif
                                @else
                                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                                    <li><a href="{{ route('volunteer') }}">Volunteer</a></li>
                                    <li><a href="{{ route('auth.loginpage') }}">Staff Login</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>