<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - CREAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboardstyle.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4><i class="fas fa-lock"></i> Access Denied</h4>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-1 text-danger">403</h1>
                        <h2>You don't have permission to view this page</h2>
                        <p class="lead">
                            {{ $exception->getMessage() ?: 'This page is restricted to other roles. If you believe you should have access, contact your system administrator.' }}
                        </p>

                        <div class="mt-4">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="fas fa-home"></i> Return Home
                            </a>
                            @if(session('role'))
                                <a href="{{ route(session('role') . '.dashboard') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-dashboard"></i> Go to My Dashboard
                                </a>
                            @else
                                <a href="{{ route('auth.loginpage') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt"></i> Staff Login
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
