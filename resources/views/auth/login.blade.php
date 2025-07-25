<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
            background: url('{{ asset('images/pexels-iqwan-alif-493640-1206101.jpg') }}') center center;
            background-size: cover;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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

        /* Background Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        /* Main Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: visible;
            animation: slideInUp 0.8s ease-out;
            max-height: none;
            margin: 10px 0;
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
            padding: 30px 25px 20px;
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
            padding: 30px 25px 25px;
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

        /* Responsive Design */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 10px;
                max-width: 100%;
            }
            
            .login-header {
                padding: 25px 20px 15px;
            }
            
            .login-form {
                padding: 25px 20px 20px;
            }
            
            .login-title {
                font-size: 24px;
            }
            
            .form-floating {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation"></div>

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
                <form id="loginForm" action="{{ route('auth.check') }}" method="POST">
                    @csrf
                    
                    <!-- Alert Container -->
                    <div id="alertContainer">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                @foreach($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Email/Identifier Field -->
                    <div class="form-floating">
                        <input type="text" 
                               class="form-control @error('identifier') is-invalid @enderror" 
                               id="identifier" 
                               name="identifier" 
                               placeholder="Email or IIUM ID"
                               value="{{ old('identifier') }}"
                               required>
                        <label for="identifier">
                            <i class="fas fa-envelope me-2"></i>Email Address or IIUM ID
                        </label>
                    </div>

                    <!-- Password Field -->
                    <div class="form-floating">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
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
                                   value="1"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="forgot-link">
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
            loginForm.addEventListener('submit', function(e) {
                // Show loading state
                loginButton.classList.add('loading');
                
                // Let the form submit normally for now
                // You can enhance this later with AJAX if needed
            });

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

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.animation = 'slideInUp 0.5s ease-out reverse';
                    setTimeout(function() {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>