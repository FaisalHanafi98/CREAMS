<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - CREAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboardstyle.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4><i class="fas fa-exclamation-triangle"></i> Server Error</h4>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-1 text-danger">500</h1>
                        <h2>Something went wrong</h2>
                        <p class="lead">{{ $message ?? 'An unexpected error occurred while processing your request.' }}</p>
                        
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
                            <small class="text-muted">
                                If this problem persists, please contact the administrator.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>