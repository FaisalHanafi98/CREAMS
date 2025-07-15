<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset("css/verifyemailstyle.css") }}">
    
</head>
<body>
    <div class="logo-container">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo" onerror="this.style.display='none'">
            CREAMS
        </a>
    </div>
    
    <div class="container">
        <div class="verify-email-card">
            <div class="form-header">
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h2>Verify Your Email</h2>
            </div>
            
            <div class="message">
                Thanks for signing up! Before getting started, please verify your email address by clicking the link we just emailed to you. If you didn't receive the email, we'll gladly send you another.
            </div>
            
            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i> A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif
            
            <div class="action-buttons">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-verify">
                        <i class="fas fa-paper-plane me-2"></i> Resend Verification Email
                    </button>
                </form>
                
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>