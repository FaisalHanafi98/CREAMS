<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Password - CREAMS</title>
    
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
    <link rel="stylesheet" href="{{ asset("css/confirmpasswordstyle.css") }}">
</head>
<body>
    <div class="logo-container">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo" onerror="this.style.display='none'">
            CREAMS
        </a>
    </div>
    
    <div class="container">
        <div class="confirm-password-card">
            <div class="form-header">
                <div class="icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Confirm Password</h2>
            </div>
            
            <div class="message">
                This is a secure area of the application. Please confirm your password before continuing.
            </div>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf
                
                <div class="form-group password-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    <span class="toggle-password" id="togglePassword">
                        <i class="fas fa-eye-slash"></i>
                    </span>
                </div>
                
                <button type="submit" class="btn-confirm">
                    <i class="fas fa-check-circle me-2"></i> Confirm
                </button>
            </form>
            
            <div class="cancel-link">
                <a href="{{ url()->previous() }}"><i class="fas fa-arrow-left me-1"></i> Go Back</a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye-slash');
                    icon.classList.toggle('fa-eye');
                });
            }
        });
    </script>
</body>
</html>