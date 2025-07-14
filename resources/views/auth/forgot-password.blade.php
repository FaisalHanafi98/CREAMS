<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CREAMS</title>
    
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

    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --dark-color: #333;
            --light-color: #f9f9f9;
            --success-color: #28a745;
            --error-color: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        html {
            height: 100%;
            scroll-behavior: smooth;
        }
        
        body {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            background-color: #f0f0f0;
            background-image: url("{{ asset('images/loginpagebackground.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
        
        .logo-container {
            position: fixed;
            top: 30px;
            left: 50px;
            z-index: 100;
        }
        
        .logo-container a {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .logo-container a:hover {
            transform: translateY(-2px);
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .logo-container img {
            height: 40px;
            margin-right: 10px;
        }
        
        .password-reset-card {
            width: 100%;
            max-width: 500px;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 50px;
            position: relative;
            overflow: hidden;
            animation: fade-in 0.6s ease;
            margin: 40px 20px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-header p {
            color: #777;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }
        
        .form-control {
            height: 55px;
            padding: 10px 20px;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
            outline: none;
        }
        
        .btn-reset {
            width: 100%;
            height: 55px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-reset:active {
            transform: translateY(-1px);
        }
        
        .login-link {
            text-align: center;
            font-size: 15px;
            color: #666;
        }
        
        .login-link a {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 15px;
            border: none;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error-color);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        .error-feedback {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 8px;
            display: block;
        }
        
        /* Animations */
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-animation {
            animation: pulse 1.5s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .password-reset-card {
                max-width: 90%;
                padding: 40px 30px;
            }
            
            .logo-container {
                left: 20px;
                top: 20px;
            }
            
            .logo-container a {
                font-size: 28px;
            }
        }
        
        @media (max-width: 576px) {
            .password-reset-card {
                padding: 30px 20px;
            }
            
            .logo-container {
                left: 15px;
                top: 15px;
            }
            
            .logo-container a {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo" onerror="this.style.display='none'">
            CREAMS
        </a>
    </div>
    
    <div class="main-container">
        <div class="password-reset-card">
            <div class="form-header">
                <h2>Forgot Password</h2>
                <p>Enter your email address and we'll send you a link to reset your password.</p>
            </div>
            
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
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('auth.processforgotpassword') }}" id="forgot-form">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                </div>
                
                <button type="submit" class="btn-reset" id="submit-btn">
                    <i class="fas fa-paper-plane me-2"></i> Send Password Reset Link
                </button>
            </form>
            
            <div class="login-link">
                Remember your password? <a href="{{ route('auth.loginpage') }}">Return to Login</a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission animation
            const form = document.getElementById('forgot-form');
            const submitBtn = document.getElementById('submit-btn');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Sending...';
                    submitBtn.disabled = true;
                    
                    // Add animation to the card
                    const card = document.querySelector('.password-reset-card');
                    card.classList.add('success-animation');
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
        });
    </script>
</body>
</html>