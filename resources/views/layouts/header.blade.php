{{-- Enhanced Header Section --}}
<header id="header">
    <div class="header-container">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto d-md-none">
                <button class="mobile-nav-toggle" type="button" id="mobile-nav-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="col-lg-3 col-md-3">
                <div class="logo-wrapper">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo/logo_ppdk.jpg') }}" alt="IIUM Logo" class="header-logo">
                    </a>
                    <a href="{{ route('home') }}">
                        <h1 class="logo">IIUM PD-CARE</h1>
                        <span class="small-text">Community-based Rehabilitation Center</span>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-9 col-md-9 d-none d-md-block">
                <div class="d-flex justify-content-end">
                    <ul class="nav-menu">
                        <li class="{{ Route::currentRouteName() == 'home' ? 'active' : '' }}">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="{{ in_array(Route::currentRouteName(), ['home']) ? '' : 'disabled' }}">
                            <a href="{{ route('home') }}#services" class="support-link">Services</a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}#about">About</a>
                        </li>
                        <li class="{{ Route::currentRouteName() == 'contact' ? 'active' : '' }}">
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>
                    </ul>
                    
                    <div class="action-buttons">
                        <a href="{{ route('volunteer') }}" class="volunteer-btn">
                            <i class="fas fa-hands-helping"></i> Volunteer
                        </a>
                        <a href="{{ route('auth.loginpage') }}" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>