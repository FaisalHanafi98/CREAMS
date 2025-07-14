<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CREAMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --primary-gradient: linear-gradient(135deg, #32bdea, #c850c0);
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(255,255,255,0.03) 2px,
                rgba(255,255,255,0.03) 4px
            );
            animation: bg-move 20s linear infinite;
        }

        @keyframes bg-move {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Floating Shapes */
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(30px) rotate(240deg); }
        }

        /* Main Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            padding: 40px 30px 20px;
            background: var(--primary-gradient);
            color: white;
            position: relative;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo i {
            font-size: 32px;
            color: white;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Form Styles */
        .login-form {
            padding: 40px 30px 30px;
        }

        .form-floating {
            margin-bottom: 24px;
            position: relative;
        }

        .form-control {
            height: 58px;
            border: 2px solid #e9ecef;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 400;
            background: rgba(248, 249, 250, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(50, 189, 234, 0.15);
            background: white;
            transform: translateY(-2px);
        }

        .form-control.is-invalid {
            border-color: var(--error-color);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .form-floating > label {
            color: #6c757d;
            font-weight: 400;
            transition: all 0.3s ease;
        }

        .form-control:focus ~ label,
        .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            font-size: 18px;
            cursor: pointer;
            z-index: 5;
            transition: all 0.3s ease;
            padding: 5px;
            border-radius: 50%;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.1);
        }

        /* Remember Me */
        .remember-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background: var(--primary-gradient);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 14px;
            color: #6c757d;
            cursor: pointer;
            margin-left: 8px;
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            height: 58px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(50, 189, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn.loading {
            pointer-events: none;
        }

        .btn-text {
            transition: all 0.3s ease;
        }

        .btn-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-btn.loading .btn-text {
            opacity: 0;
        }

        .login-btn.loading .btn-spinner {
            opacity: 1;
        }

        /* Register Link */
        .register-section {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .register-text {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .register-btn {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .register-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }

        .alert-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }
            
            .login-header {
                padding: 30px 20px 15px;
            }
            
            .login-form {
                padding: 30px 20px 20px;
            }
            
            .login-title {
                font-size: 24px;
            }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation"></div>
    
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Main Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                </div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your CREAMS account</p>
            </div>

            <!-- Login Form -->
            <div class="login-form">
                <form id="loginForm">
                    @csrf
                    
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>

                    <!-- Email/IIUM ID Field -->
                    <div class="form-floating">
                        <input type="text" 
                               class="form-control" 
                               id="identifier" 
                               name="identifier" 
                               placeholder="Email or IIUM ID"
                               required>
                        <label for="identifier">
                            <i class="fas fa-user me-2"></i>Email or IIUM ID
                        </label>
                    </div>

                    <!-- Password Field -->
                    <div class="form-floating">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Password"
                               required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="remember-section">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="remember" 
                                   name="remember"
                                   value="1">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="{{ route('auth.forgotpassword') }}" class="forgot-link">
                            Forgot Password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="login-btn" id="loginButton">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </span>
                        <div class="btn-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>

                <!-- Register Section -->
                <div class="register-section">
                    <p class="register-text">Don't have an account?</p>
                    <a href="{{ route('auth.registerpage') }}" class="register-btn">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            const alertContainer = document.getElementById('alertContainer');
            const loadingOverlay = document.getElementById('loadingOverlay');

            // CSRF Token Setup
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Password Toggle
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
                
                // Add rotation animation
                icon.style.transform = 'rotateY(180deg)';
                setTimeout(() => {
                    icon.style.transform = 'rotateY(0deg)';
                }, 200);
            });

            // Form Submission
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Clear previous alerts
                alertContainer.innerHTML = '';
                
                // Show loading state
                loginButton.classList.add('loading');
                loadingOverlay.style.display = 'flex';
                
                // Get form data
                const formData = new FormData(this);
                
                try {
                    const response = await fetch('{{ route("auth.enhanced-login") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Show success message
                        showAlert('success', data.message);
                        
                        // Redirect after short delay
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1000);
                        
                    } else {
                        // Show error message
                        showAlert('danger', data.message);
                        
                        // Shake form on error
                        loginForm.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            loginForm.style.animation = '';
                        }, 500);
                        
                        // Handle rate limiting
                        if (response.status === 429 && data.lockout_time) {
                            startLockoutTimer(data.lockout_time);
                        }
                    }
                    
                } catch (error) {
                    console.error('Login error:', error);
                    showAlert('danger', 'An unexpected error occurred. Please try again.');
                }
                
                // Hide loading state
                loginButton.classList.remove('loading');
                loadingOverlay.style.display = 'none';
            });

            // Show Alert Function
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                alertContainer.appendChild(alertDiv);
                
                // Auto-hide after 5 seconds (except for success)
                if (type !== 'success') {
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.style.animation = 'slideInUp 0.5s ease-out reverse';
                            setTimeout(() => {
                                alertDiv.remove();
                            }, 500);
                        }
                    }, 5000);
                }
            }

            // Lockout Timer
            function startLockoutTimer(seconds) {
                loginButton.disabled = true;
                const originalText = loginButton.querySelector('.btn-text').innerHTML;
                
                const timer = setInterval(() => {
                    loginButton.querySelector('.btn-text').innerHTML = `
                        <i class="fas fa-clock me-2"></i>Wait ${seconds}s
                    `;
                    
                    seconds--;
                    
                    if (seconds < 0) {
                        clearInterval(timer);
                        loginButton.disabled = false;
                        loginButton.querySelector('.btn-text').innerHTML = originalText;
                    }
                }, 1000);
            }

            // Input Animations
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
                
                // Real-time validation
                input.addEventListener('input', function() {
                    if (this.validity.valid) {
                        this.classList.remove('is-invalid');
                    }
                });
            });

            // Auto-focus first input
            document.getElementById('identifier').focus();
        });
    </script>
</body>
</html>