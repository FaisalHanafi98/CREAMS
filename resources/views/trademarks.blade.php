<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Trademarks - CREAMS</title>
    
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    
    {{-- CSS Dependencies --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/homestyle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trademarkstyle.css') }}">

</head>
<body>
    {{-- Preloader --}}
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    {{-- Header Section --}}
    <header id="header">
        <div class="container">
            <div class="row align-items-centre justify-content-between">
                <div class="col-auto d-block d-md-none">
                    <button class="navbar-toggler" type="button" id="mobile-nav-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="col-md-2 d-none d-md-block text-left">
                    <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM Logo" class="header-logo">
                </div>
                <div class="col-md-8 text-centre" id="logo">
                    <a href="{{ route('home') }}">
                        <h1 class="logo">CREAMS</h1>
                        <span class="small-text">Community-based REhAbilitation Management System</span>
                    </a>
                </div>
                <div class="col-md-2 col-auto">
                    <div class="nav-menu-right">
                        <a href="{{ route('contact') }}" class="contact-btn"><i class="fas fa-envelope"></i> Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile Navigation Menu --}}
    <div id="mobile-nav">
        <ul class="mobile-nav-menu">
            <li><a href="{{ route('home') }}">HOME</a></li>
            <li><a href="{{ route('home') }}#about">ABOUT</a></li>
            <li><a href="{{ route('contact') }}">CONTACT</a></li>
            <li><a href="{{ route('trademark') }}" class="active">TRADEMARKS</a></li>
            @guest
                <li><a href="{{ route('login') }}">LOGIN</a></li>
            @else
                <li><a href="{{ route('dashboard') }}">DASHBOARD</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">LOGOUT</button>
                    </form>
                </li>
            @endguest
        </ul>
    </div>

    {{-- Page Banner --}}
    <section class="page-banner">
        <div class="container">
            <h1>Trademarks & Legal</h1>
            <div class="breadcrumbs">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('trademark') }}">Trademarks</a>
            </div>
        </div>
    </section>

    {{-- Trademarks Content --}}
    <section class="section section-visible">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="trademark-container">
                        <div class="trademark-section">
                            <h2>CREAMS Trademarks</h2>
                            <p>The CREAMS name, logo, and branding elements are trademarks of the International Islamic University Malaysia (IIUM) in collaboration with Jabatan Kebajikan Masyarakat (JKM). These trademarks are protected intellectual property and may not be used without explicit written permission.</p>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="trademark-logo">
                                        <img src="{{ asset('images/creams-logo.png') }}" alt="CREAMS Logo">
                                        <p>CREAMS Primary Logo</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="trademark-logo">
                                        <img src="{{ asset('images/creams-icon.png') }}" alt="CREAMS Icon">
                                        <p>CREAMS Icon</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="trademark-logo">
                                        <img src="{{ asset('images/creams-wordmark.png') }}" alt="CREAMS Wordmark">
                                        <p>CREAMS Wordmark</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="trademark-section">
                            <h2>Partner Trademarks</h2>
                            <p>CREAMS operates in partnership with the International Islamic University Malaysia (IIUM) and Jabatan Kebajikan Masyarakat (JKM). The IIUM and JKM logos and other branding elements are the trademarks of their respective organizations and are used with permission within the context of the CREAMS initiative.</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="trademark-logo">
                                        <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM Logo">
                                        <p>International Islamic University Malaysia Logo</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="trademark-logo">
                                        <img src="{{ asset('images/logo/logo jkm.png') }}" alt="JKM Logo">
                                        <p>Jabatan Kebajikan Masyarakat Logo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="trademark-section">
                            <h2>Trademark Usage Guidelines</h2>
                            <p>All CREAMS trademarks, including the name, logo, and associated branding elements, may only be used in accordance with the following guidelines:</p>
                            <ul>
                                <li>Any use must be pre-approved in writing by CREAMS management.</li>
                                <li>Trademarks must be used in their complete, unmodified form, including design and colors.</li>
                                <li>Trademarks must appear distinct from other text or graphic elements.</li>
                                <li>Trademarks must be used in a manner that does not imply endorsement of third-party services or products.</li>
                                <li>Trademarks must not be used in a manner that is misleading, defamatory, obscene, or otherwise objectionable.</li>
                                <li>Trademarks must not be used as part of a domain name or social media username without explicit permission.</li>
                            </ul>
                        </div>
                        
                        <div class="trademark-section">
                            <h2>Copyright Information</h2>
                            <p>All content on the CREAMS website, including but not limited to text, graphics, logos, icons, images, audio clips, digital downloads, and software, is the property of CREAMS or its content suppliers and is protected by Malaysian and international copyright laws.</p>
                            <p>The compilation of all content on this site is the exclusive property of CREAMS and is protected by Malaysian and international copyright laws. All software used on this site is the property of CREAMS or its software suppliers and is protected by Malaysian and international copyright laws.</p>
                        </div>
                        
                        <div class="trademark-section">
                            <h2>Privacy Policy</h2>
                            <p>CREAMS is committed to protecting the privacy of all users. Our privacy policy covers the collection, use, and disclosure of personal information that may be collected anytime you interact with CREAMS, such as when you visit our website, use our services, or communicate with our staff.</p>
                            <p>We collect personal information only with your consent and use it only for the purposes for which it was collected. We implement appropriate safeguards to protect your personal information and do not share it with third parties except as necessary to provide our services or as required by law.</p>
                        </div>
                        
                        <div class="trademark-section">
                            <h2>Contact Information</h2>
                            <p>For inquiries regarding the use of CREAMS trademarks or any legal matters, please contact us at:</p>
                            <p>
                                <strong>Email:</strong> legal-creams@iium.edu.my<br>
                                <strong>Address:</strong> Disability Services Unit (DSU), Office of the Deputy Rector (Trainee Development & Community Engagement), Level 1, Muhammad Abdul-Rauf Building, International Islamic University Malaysia, 53100 Kuala Lumpur.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="footer-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>Quick Links</h3>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li><a href="{{ route('home') }}#about">About</a></li>
                            <li><a href="{{ route('contact') }}">Contact Us</a></li>
                            <li><a href="{{ route('trademark') }}">Trademarks</a></li>
                            @guest
                                <li><a href="{{ route('login') }}">Staff Login</a></li>
                            @else
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            @endguest
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>Our Partners</h3>
                        <div class="footer-partners">
                            <div class="footer-partner">
                                <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM Logo">
                                <p>International Islamic University Malaysia</p>
                            </div>
                            <div class="footer-partner">
                                <img src="{{ asset('images/logo/logo jkm.png') }}" alt="JKM Logo">
                                <p>Jabatan Kebajikan Masyarakat</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="footer-widget">
                        <h3>Connect With Us</h3>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                        <div class="newsletter">
                            <h4>Subscribe to our newsletter</h4>
                            <form action="#" method="post" class="newsletter-form">
                                <input type="email" name="email" placeholder="Your email address">
                                <button type="submit"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-centre">
                        <p>&copy; {{ date('Y') }} CREAMS. All rights reserved. A collaborative initiative of International Islamic University Malaysia and Jabatan Kebajikan Masyarakat.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Back to top button --}}
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

    {{-- JavaScript Dependencies --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
    (function() {
        // Preloader Management
        function hidePreloader() {
            const preloader = document.querySelector('.preloader');
            if (preloader) {
                preloader.style.transition = 'opacity 0.5s ease';
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        }

        // Mobile menu toggle
        function setupMobileMenu() {
            const mobileToggle = document.getElementById('mobile-nav-toggle');
            const mobileNav = document.getElementById('mobile-nav');
            
            if (mobileToggle && mobileNav) {
                mobileToggle.addEventListener('click', function() {
                    mobileNav.classList.toggle('mobile-nav-active');
                    this.classList.toggle('active');
                });
            }
        }
        
        // Back to top button functionality
        function setupBackToTop() {
            const backToTop = document.querySelector('.back-to-top');
            
            if (backToTop) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 100) {
                        backToTop.classList.add('active');
                    } else {
                        backToTop.classList.remove('active');
                    }
                });
                
                backToTop.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        }

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            hidePreloader();
            setupMobileMenu();
            setupBackToTop();
            
            // Header scroll effect
            window.addEventListener('scroll', function() {
                const header = document.getElementById('header');
                if (window.scrollY > 50) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }
            });
        });

        // Window load event
        window.addEventListener('load', hidePreloader);
        
        // Fallback preloader removal
        setTimeout(hidePreloader, 3000);
    })();
    </script>
</body>
</html>