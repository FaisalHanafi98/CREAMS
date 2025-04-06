<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CREAMS</title>
    
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

    <link rel="stylesheet" href="{{ asset('css/loginstyle.css') }}">
</head>
<body>
    <div class="logo-container">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo" onerror="this.style.display='none'">
            CREAMS
        </a>
    </div>
    
    <div class="main-container" style="background-image: url('{{ asset('images/pexels-iqwan-alif-493640-1206101.jpg') }}'); background-size: cover; background-position: centre;">
        <div class="login-card">
            <!-- Login Icon at Top -->
            <div class="login-icon">
                <div class="login-icon-circle">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
            </div>
            
            <div class="login-header">
                <h3>Welcome Back</h3>
                <p>Sign in to your account to continue</p>
            </div>
            
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            
            <!-- In login.blade.php -->
                <form action="{{ route('auth.check') }}" method="POST" id="login-form" class="login-form">
                    @csrf
                    
                    <div class="form-group">
                        <input type="text" class="form-control @error('identifier') is-invalid @enderror" 
                            id="identifier" name="identifier" placeholder="Email Address or IIUM ID" 
                            value="{{ old('identifier') }}" required>
                        @error('identifier')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                <div class="form-group password-field">
                    <input type="password" class="form-control
                    @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="toggle-password" id="togglePassword">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                    @error('password')
                        <span class="error-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form  -check-label" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('auth.forgotpassword') }}" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-button" id="loginButton">
                    Sign In
                </button>
                
                <div class="register-link">
                    Don't have an account? <a href="{{ route('auth.registerpage') }}">Register now</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Hidden Footer that appears on scroll -->
    <footer id="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h4>About CREAMS</h4>
                <ul>
                    <li><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="{{ url('/#about') }}"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="{{ url('/#support') }}"><i class="fas fa-hands-helping"></i> Our Services</a></li>
                    <li><a href="{{ url('/#team') }}"><i class="fas fa-users"></i> Our Team</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="{{ route('volunteer') }}"><i class="fas fa-user-plus"></i> Volunteer</a></li>
                    <li><a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact Us</a></li>
                    <li><a href="{{ route('auth.loginpage') }}"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="{{ route('auth.registerpage') }}"><i class="fas fa-user-plus"></i> Register</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Legal</h4>
                <ul>
                    <li><a href="{{ route('trademark') }}"><i class="fas fa-trademark"></i> Trademarks</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    <li><a href="#"><i class="fas fa-gavel"></i> Terms of Service</a></li>
                    <li><a href="#"><i class="fas fa-universal-access"></i> Accessibility</a></li>
                </ul>
            </div>
            
            <div class="footer-column footer-info">
                <h4>Contact Us</h4>
                <p><i class="fas fa-map-marker-alt"></i> Disability Services Unit (DSU), IIUM, 53100 Kuala Lumpur</p>
                <p><i class="fas fa-phone-alt"></i> (+60) 3642 1633 5</p>
                <p><i class="fas fa-envelope"></i> dsu-creams@iium.edu.my</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} CREAMS - Community-based REhAbilitation Management System. All rights reserved.</p>
        </div>
    </footer>
    
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
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        togglePassword.innerHTML = '<i class="fas fa-eye"></i>';
                    } else {
                        passwordInput.type = 'password';
                        togglePassword.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    }
                });
            }
            
            // Animated form submission
            const loginForm = document.getElementById('login-form');
            const loginButton = document.getElementById('loginButton');
            
            if (loginForm && loginButton) {
                loginForm.addEventListener('submit', function() {
                    loginButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Signing In...';
                    loginButton.disabled = true;
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 5000);
            
            // Show footer on scroll
            const footer = document.getElementById('footer');
            
            window.addEventListener('scroll', function() {
                const scrollHeight = document.documentElement.scrollHeight;
                const scrollTop = window.scrollY;
                const clientHeight = document.documentElement.clientHeight;
                
                // Show footer when user scrolls to bottom
                if (scrollTop + clientHeight >= scrollHeight - 50) {
                    footer.classList.add('visible');
                } else {
                    footer.classList.remove('visible');
                }
            });
        });
    </script>
</body>
</html>