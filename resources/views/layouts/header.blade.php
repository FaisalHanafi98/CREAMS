{{-- Enhanced Header Section --}}
<header id="header">
    <div class="header-container">
        <div class="row align-items-centre justify-content-between">
            <div class="col-auto d-md-none">
                <button class="mobile-nav-toggle" type="button" id="mobile-nav-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="col-lg-3 col-md-3">
                <div class="logo-wrapper">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM Logo" class="header-logo">
                    </a>
                    <a href="{{ route('home') }}">
                        <h1 class="logo">CREAMS</h1>
                        <span class="small-text">Community-based REhAbilitation Management System</span>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-9 col-md-9 d-none d-md-block">
                <div class="d-flex justify-content-end">
                    <ul class="nav-menu">
                        <li class="{{ Route::currentRouteName() == 'home' ? 'active' : '' }}">
                            <a href="{{ route('home') }}">Home</a>
                        </li>                       
                        <li class="{{ Route::currentRouteName() == 'supports' ? 'active' : '' }}">
                            <a href="#support">Support</a>
                        </li>
                        <li class="{{ Route::currentRouteName() == 'aboutus' ? 'active' : '' }}">
                            <a href="#about">About</a>
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



{{-- Sticky Volunteer Button --}}
<a href="{{ route('volunteer') }}" class="sticky-volunteer">
    <i class="fas fa-hands-helping"></i>
    <span class="tooltip">Volunteer With Us</span>
</a>