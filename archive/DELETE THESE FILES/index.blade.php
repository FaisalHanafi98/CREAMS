<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Page Title -->
    <title>Home - CREAMS</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/home/images/logo/favicon.png') }}" type="image/x-icon">
    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/home/font-awesome-4.7.0.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/home/bootstrap-4.1.3.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/home/indexstyle.css') }}">
</head>
<body>
    <!-- Header Area Starts -->
    <header class="header-area">
        <div id="home">
            <div class="container">
                <div class="row align-items-center justify-content-between d-flex">
                    <div id="logo">
                        <a href="{{ url('/index') }}"><h1>CREAMS</h1></a>
                    </div>
                    <nav id="nav-menu-container">
                        <ul class="nav-menu">
                            <li><a href="{{ url('/') }}">Home</a></li>
                            <li><a href="{{ route('auth.loginpage') }}">Login</a></li>
                            <li><a href="{{ route('auth.registerpage') }}">Register</a></li>
                        </ul>
                    </nav><!-- #nav-menu-container -->    
                </div>
            </div>
        </div>
    </header>
    <!-- Header Area End -->
    <!-- Banner Area Starts -->
    <section class="index-banner-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h4>Breaking barriers, building communities</h4>
                    <h1>Leading the way in rehabilitation excellence</h1>
                    <p>Introducing the Community-based Rehabilitation Systems, or more commonly known as CREAMS, 
                        a one-stop rehabilitation centre management system for persons with disabilities that features asset, trainee, personnel, and financial management.
                        We strive to empower and support the OKU community in achieving their goals and leading fulfilling lives.</p>
                    <div class="col-lg-12 text-centre">
                        <a href="{{ route('auth.loginpage') }}" class="get-started-btn">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer Area Starts -->
    <footer class="footer-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-sm-6">
                    <h3>Quick Links</h3>
                    <ul class="list-unstyled">
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ route('auth.loginpage') }}">Login</a></li>
                        <li><a href="{{ route('auth.registerpage') }}">Register</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <h3>&nbsp;</h3>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <h3>Contact Us</h3>
                    <p>
                        Address: Disability Services Unit (DSU), 
                        Office of the Deputy Rector (Trainee Development & Community Engagement), Level 1, Muhammad Abdul-Rauf Building, International Islamic University Malaysia, 53100 Kuala Lumpur.<br>
                    Phone: (+60) 3642 1633 5<br>
                    Email: dsu-creams@iium.edu.my 
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 text-center">
                <p class="copyright">Copyright &copy; {{ date('Y') }} CREAMS. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Area End -->


</body>
</html>

