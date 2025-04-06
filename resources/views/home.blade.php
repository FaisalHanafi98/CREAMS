<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CREAMS - Community-based REhAbilitation Management System</title>
    
    {{-- Critical CSS and Performance Optimizations --}}
    <link rel="preload" href="{{ asset('videos/welcomepage.mp4') }}" as="video" type="video/mp4">
    
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    
    {{-- CSS Dependencies --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/homestyle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/headerstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footerstyle.css') }}">

</head>
<body>
    {{-- Preloader --}}
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    {{-- Enhanced Header Section --}}
    @include('layouts.header')

    {{-- Mobile Navigation Menu --}}
    <div id="mobile-nav">
        <ul class="mobile-nav-menu">
            <li class="{{ Route::currentRouteName() == 'home' ? 'active' : '' }}">
                <a href="{{ route('home') }}">HOME</a>
            </li>
            <li class="{{ Route::currentRouteName() == 'aboutus' ? 'active' : '' }}">
                <a href="#about">ABOUT</a>
            </li>
            <li class="{{ Route::currentRouteName() == 'services' ? 'active' : '' }}">
                <a href="#services">SERVICES</a>
            </li>
            <li class="{{ Route::currentRouteName() == 'contact' ? 'active' : '' }}">
                <a href="{{ route('contact') }}">CONTACT</a>
            </li>
            <li class="{{ Route::currentRouteName() == 'volunteer' ? 'active' : '' }}">
                <a href="{{ route('volunteer') }}">VOLUNTEER</a>
            </li>
            @guest
                <li><a href="{{ route('auth.loginpage') }}" class="login-mobile-btn"><i class="fas fa-sign-in-alt"></i> LOGIN</a></li>
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

    {{-- Sticky Volunteer Button --}}
    <a href="{{ route('volunteer') }}" class="sticky-volunteer">
        <i class="fas fa-hands-helping"></i>
        <span class="tooltip">Volunteer With Us</span>
    </a>

    {{-- Updated Video Background Section --}}
    <section id="home" class="video-background">
        <div class="video-container">
            <video 
                autoplay 
                muted 
                loop 
                playsinline 
                id="bgVideo" 
                poster="{{ asset('images/welcome-img.jpg') }}"
            >
                <source src="{{ asset('videos/welcomepage.mp4') }}" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
            <div class="overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container text-centre">
                <div class="row justify-content-centre">
                    <div class="col-lg-10">
                        <h1>Empowering Special Children Through Community Support</h1>
                        <p class="lead">A collaborative initiative of IIUM and Jabatan Kebajikan Masyarakat Malaysia to provide rehabilitation services for children with disabilities</p>
                        
                        <div class="hero-partners">
                            <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM" class="partner-logo">
                            <img src="{{ asset('images/logo/logo jkm.png') }}" alt="JKM" class="partner-logo">
                        </div>
                        
                        <a href="{{ route('auth.loginpage') }}" class="get-started-btn">
                            <i class="fas fa-sign-in-alt mr-2"></i> Staff Portal Login
                        </a>
                    </div>
                </div>
            </div>
            <a href="#welcome" class="scroll-down">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    {{-- Welcome Section --}}
    <section id="welcome" class="section section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Welcome to CREAMS</h2>
                <div class="section-divider"></div>
            </div>
            <div class="row align-items-centre">
                <div class="col-lg-6">
                    <div class="welcome-img">
                        <img src="{{ asset('images/welcome-img.jpg') }}" alt="CREAMS Welcome" class="img-fluid rounded shadow">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="welcome-content">
                        <h3>Supporting Children with Special Needs</h3>
                        <p>The Community-based REhAbilitation Management System (CREAMS) is a voluntary initiative developed to address the unique challenges faced by children with disabilities, starting with those of IIUM staff and eventually extending to the surrounding community.</p>
                        <p>Our program is built on the foundation of community support and professional expertise, focused entirely on improving the quality of life and developmental outcomes for special children.</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check-circle"></i> Personalized rehabilitation programs</li>
                            <li><i class="fas fa-check-circle"></i> Volunteer coordination and training</li>
                            <li><i class="fas fa-check-circle"></i> Family education and support</li>
                            <li><i class="fas fa-check-circle"></i> Secure child progress tracking</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="scroll-to-next">
                <a href="#history" class="scroll-arrow">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- History Section --}}
    <section id="history" class="section bg-light section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Our History</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">The journey of CREAMS from concept to reality</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="year">2020</span>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Initial Concept</h3>
                        <p>Initial concept development for CREAMS at International Islamic University Malaysia (IIUM) in collaboration with the Disability Services Unit (DSU).</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="year">2021</span>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Research Phase</h3>
                        <p>Research and analysis of rehabilitation centre needs, identifying key challenges in management and monitoring processes.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="year">2022</span>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>Prototype Development</h3>
                        <p>Development of prototype system with core functionalities for participant and staff management.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="year">2023</span>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>Official Launch</h3>
                        <p>Launch of the full-featured CREAMS platform with comprehensive modules for rehabilitation centre management.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="year">2024</span>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Growth & Expansion</h3>
                        <p>Expansion of services and continued improvements based on user feedback and emerging needs in the community.</p>
                    </div>
                </div>
            </div>
            
            <div class="scroll-to-next">
                <a href="#team" class="scroll-arrow">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Team Section --}}
    <section id="team" class="section section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Our Leadership Team</h2>
                <div class="section-divider"></div>
            </div>
            
            <div class="org-chart-container">
                <!-- Top Level -->
                <div class="org-level director-level">
                    <div class="org-box director-box">
                        <div class="org-avatar">
                            <img src="{{ asset('images/team/director.jpg') }}" alt="Director">
                        </div>
                        <div class="org-info">
                            <h3>Dr. Sarah Ahmad</h3>
                            <p class="org-title">Director</p>
                            <p class="org-desc">Program Director & Head of Operations</p>
                        </div>
                    </div>
                </div>
                
                <!-- Connecting Lines -->
                <div class="org-connectors">
                    <div class="vertical-line"></div>
                    <div class="horizontal-line"></div>
                    <div class="vertical-line-left"></div>
                    <div class="vertical-line-centre"></div>
                    <div class="vertical-line-right"></div>
                </div>
                
                <!-- Middle Level -->
                <div class="org-level department-level">
                    <div class="org-box department-box">
                        <div class="org-avatar">
                            <img src="{{ asset('images/team/admin-head.jpg') }}" alt="Admin Head">
                        </div>
                        <div class="org-info">
                            <h3>Ahmad Razif</h3>
                            <p class="org-title">Administration</p>
                            <p class="org-desc">Resource Planning & Management</p>
                        </div>
                    </div>
                    
                    <div class="org-box department-box">
                        <div class="org-avatar">
                            <img src="{{ asset('images/team/program-head.jpg') }}" alt="Program Head">
                        </div>
                        <div class="org-info">
                            <h3>Dr. Nurul Hafizah</h3>
                            <p class="org-title">Programs</p>
                            <p class="org-desc">Rehabilitation Services & Education</p>
                        </div>
                    </div>
                    
                    <div class="org-box department-box">
                        <div class="org-avatar">
                            <img src="{{ asset('images/team/technical-head.jpg') }}" alt="Technical Head">
                        </div>
                        <div class="org-info">
                            <h3>Mohamad Faisal</h3>
                            <p class="org-title">Technical</p>
                            <p class="org-desc">Systems & Technology Infrastructure</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="team-members-section">
                <h3 class="team-subtitle">Our Specialists</h3>
                
                <div class="row team-members">
                    <div class="col-lg-3 col-md-6">
                        <div class="team-member">
                            <div class="member-img">
                                <img src="{{ asset('images/team/team1.jpg') }}" alt="Team Member">
                                <div class="social-links">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="member-info">
                                <h4>Dr. Fatimah Zahra</h4>
                                <p>Rehabilitation Specialist</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-member">
                            <div class="member-img">
                                <img src="{{ asset('images/team/team2.jpg') }}" alt="Team Member">
                                <div class="social-links">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="member-info">
                                <h4>Mohd Ibrahim</h4>
                                <p>Occupational Therapist</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-member">
                            <div class="member-img">
                                <img src="{{ asset('images/team/team3.jpg') }}" alt="Team Member">
                                <div class="social-links">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="member-info">
                                <h4>Siti Aminah</h4>
                                <p>Education Coordinator</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-member">
                            <div class="member-img">
                                <img src="{{ asset('images/team/team4.jpg') }}" alt="Team Member">
                                <div class="social-links">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="member-info">
                                <h4>Dr. Suriani Sulaiman</h4>
                                <p>Research Advisor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="scroll-to-next">
                <a href="#partners" class="scroll-arrow">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Partners Section --}}
    <section id="partners" class="section bg-light section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Our Partners</h2>
                <div class="section-divider"></div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="partner-box iium">
                        <div class="partner-logo-container">
                            <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM Logo" class="img-fluid">
                        </div>
                        <div class="partner-content">
                            <h3>International Islamic University Malaysia</h3>
                            <p>IIUM provides academic expertise, facilities, and community outreach support for the CREAMS initiative. The university's Disability Services Unit (DSU) works closely with rehabilitation specialists to ensure the highest quality of care for children with special needs.</p>
                            <p>Through the involvement of faculty members, trainees, and staff, IIUM contributes significantly to the development and implementation of rehabilitation programs tailored to the unique needs of each child.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="partner-box jkm">
                        <div class="partner-logo-container">
                            <img src="{{ asset('images/logo/logo jkm.png') }}" alt="JKM Logo" class="img-fluid">
                        </div>
                        <div class="partner-content">
                            <h3>Jabatan Kebajikan Masyarakat</h3>
                            <p>The Department of Social Welfare (JKM) provides essential support through policy guidance, professional training, and resource allocation. Their expertise in community-based rehabilitation services forms a cornerstone of our approach.</p>
                            <p>JKM's commitment to enhancing the welfare of individuals with disabilities aligns perfectly with CREAMS' mission, creating a powerful synergy that benefits the entire community.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="scroll-to-next">
                <a href="#support" class="scroll-arrow">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Support Types Section --}}
    <section id="support" class="section section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Children We Support</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">Our program provides specialized support for children with various disabilities</p>
            </div>
            <div class="services-container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h3>Autism Spectrum Disorder</h3>
                            <p>Specialized support for children with ASD, focusing on communication, social skills, and managing sensory issues.</p>
                            <div class="service-features">
                                <span><i class="fas fa-star"></i> Structured learning</span>
                                <span><i class="fas fa-star"></i> Sensory integration</span>
                                <span><i class="fas fa-star"></i> Social skills development</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-sign-language"></i>
                            </div>
                            <h3>Hearing Impairment</h3>
                            <p>Support for children with hearing loss, including sign language training and communication enhancement.</p>
                            <div class="service-features">
                                <span><i class="fas fa-star"></i> Sign language education</span>
                                <span><i class="fas fa-star"></i> Assistive technology support</span>
                                <span><i class="fas fa-star"></i> Speech therapy</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3>Visual Impairment</h3>
                            <p>Programs to help visually impaired children develop independence and learning skills.</p>
                            <div class="service-features">
                                <span><i class="fas fa-star"></i> Braille instruction</span>
                                <span><i class="fas fa-star"></i> Mobility training</span>
                                <span><i class="fas fa-star"></i> Adaptive technology</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-walking"></i>
                            </div>
                            <h3>Physical Disabilities</h3>
                            <p>Support for children with mobility issues, motor skill challenges, and physical limitations.</p>
                            <div class="service-features">
                                <span><i class="fas fa-star"></i> Physical therapy</span>
                                <span><i class="fas fa-star"></i> Mobility aids training</span>
                                <span><i class="fas fa-star"></i> Adaptive sports</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <h3>Learning Disabilities</h3>
                            <p>Specialized approaches for children with dyslexia, dyscalculia, and other learning challenges.</p>
                            <div class="service-features">
                                <span><i class="fas fa-star"></i> Alternative learning methods</span>
                                <span><i class="fas fa-star"></i> Individualized education plans</span>
                                <span><i class="fas fa-star"></i> Study skills development</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <h3>Speech & Language Disorders</h3>
                            <p>Programs for children with communication difficulties and speech development delays.</p>
                            <div class="service-features">
                                <span><i class="fas fa-star"></i> Speech therapy</span>
                                <span><i class="fas fa-star"></i> Language development</span>
                                <span><i class="fas fa-star"></i> Alternative communication methods</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="scroll-to-next">
                <a href="#about" class="scroll-arrow">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="section bg-light section-visible">
        <div class="container">
            <div class="section-title">
                <h2>About Us</h2>
                <div class="section-divider"></div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="about-box">
                        <div class="icon-box">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <p>To provide comprehensive community-based rehabilitation services for children with disabilities, focusing initially on IIUM staff families and gradually expanding to the surrounding community. We aim to empower these children to reach their full potential through personalized support, education, and therapy.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="about-box">
                        <div class="icon-box">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <p>We envision a community where every child with special needs has access to quality rehabilitation services, support, and educational opportunities. We strive to create an inclusive environment where these children are fully integrated into society and can pursue their dreams with confidence and dignity.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="about-box">
                        <div class="icon-box">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>Our Values</h3>
                        <p>Our work is guided by the principles of compassion, inclusivity, respect, and excellence. We believe in the inherent dignity of every child, the power of community support, and the importance of evidence-based approaches to rehabilitation. We are committed to continuous improvement and collaboration with all stakeholders.</p>
                    </div>
                </div>
            </div>
            <div class="scroll-to-next">
                <a href="#impact" class="scroll-arrow">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Impact Section --}}
    <section id="impact" class="section section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Our Impact</h2>
                <div class="section-divider"></div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="impact-counter">
                        <div class="counter-value" data-count="50">50</div>
                        <h4>Children Supported</h4>
                        <p>Children receiving rehabilitation services through our program</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="impact-counter">
                        <div class="counter-value" data-count="30">30</div>
                        <h4>Dedicated Volunteers</h4>
                        <p>Trained volunteers providing various forms of support</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="impact-counter">
                        <div class="counter-value" data-count="15">15</div>
                        <h4>Professional Staff</h4>
                        <p>Qualified rehabilitation specialists and educators</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="impact-counter">
                        <div class="counter-value" data-count="25">25</div>
                        <h4>IIUM Families</h4>
                        <p>University staff families benefiting from our services</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-container mt-5">
                <div class="row">
                    <div class="col-md-4">
                        <div class="testimonial">
                            <div class="testimonial-text">
                                <p>"The support my son has received through CREAMS has been transformative. The volunteers and professionals work together seamlessly to address his unique needs."</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('images/parent1.jpg') }}" alt="Parent">
                                <h4>Aishah Rahman</h4>
                                <p>Parent of a child with autism</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="testimonial">
                            <div class="testimonial-text">
                                <p>"As an IIUM staff member, I'm grateful for this initiative. The comprehensive approach to my daughter's physical therapy has greatly improved her mobility and confidence."</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('images/parent2.jpg') }}" alt="Parent">
                                <h4>Dr. Ahmad Hassan</h4>
                                <p>IIUM Faculty member and parent</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="testimonial">
                            <div class="testimonial-text">
                                <p>"Volunteering with CREAMS has been a fulfilling experience. Seeing the children's progress and knowing I'm contributing to their development is incredibly rewarding."</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('images/volunteer.jpg') }}" alt="Volunteer">
                                <h4>Nurul Huda</h4>
                                <p>Volunteer speech therapist</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.footer')

    {{-- Back to top button --}}
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

    {{-- JavaScript Dependencies --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/header.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded');

        // Improved Preloader Management
        function initPreloader() {
            const preloader = document.querySelector('.preloader');
            
            if (!preloader) {
                console.error('Preloader element not found');
                return;
            }

            // Smooth fade out with guaranteed removal
            function hidePreloader() {
                console.log('Hiding preloader');
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                    console.log('Preloader hidden');
                }, 500);
            }

            // Hide preloader after DOM is loaded (don't wait for all resources)
            hidePreloader();
            
            // Also try hiding on window load as a backup
            window.addEventListener('load', function() {
                console.log('Window fully loaded');
                hidePreloader();
            });

            // Fallback preloader removal (will definitely hide after 3 seconds)
            setTimeout(hidePreloader, 3000);
        }

        // Mobile Menu Toggle
        function initMobileMenu() {
            const mobileToggle = document.getElementById('mobile-nav-toggle');
            const mobileNav = document.getElementById('mobile-nav');
            
            if (mobileToggle && mobileNav) {
                mobileToggle.addEventListener('click', function() {
                    mobileNav.classList.toggle('mobile-nav-active');
                    this.classList.toggle('active');
                });

                // Close mobile menu when a link is clicked
                const mobileNavLinks = mobileNav.querySelectorAll('a');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileNav.classList.remove('mobile-nav-active');
                        mobileToggle.classList.remove('active');
                    });
                });
            }
        }

        // Smooth Scroll Functionality
        function initSmoothScroll() {
            const scrollLinks = document.querySelectorAll('a[href^="#"]');
            
            scrollLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        // Adjust for fixed header
                        const headerOffset = 80;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }

        // Back to Top Button
        function initBackToTopButton() {
            const backToTop = document.querySelector('.back-to-top');
            
            if (backToTop) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
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

        // Header Scroll Effect
        function initHeaderScrollEffect() {
            const header = document.getElementById('header');
            
            if (header) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        header.classList.add('header-scrolled');
                    } else {
                        header.classList.remove('header-scrolled');
                    }
                });
            }
        }

        // Section Visibility Animations
        function initSectionAnimations() {
            const sections = document.querySelectorAll('.section');
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('section-visible');
                        }
                    });
                }, {
                    threshold: 0.1
                });
                
                sections.forEach(section => {
                    observer.observe(section);
                });
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                sections.forEach(section => {
                    section.classList.add('section-visible');
                });
            }
        }

        // Counter Animation
        function initCounterAnimations() {
            const counters = document.querySelectorAll('.counter-value');
            
            if (counters.length === 0) return;
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const target = entry.target;
                            const targetValue = parseInt(target.getAttribute('data-count') || '0');
                            let count = 0;
                            
                            const updateCounter = () => {
                                const increment = targetValue / 50;
                                if (count < targetValue) {
                                    count += increment;
                                    target.textContent = Math.ceil(count);
                                    requestAnimationFrame(updateCounter);
                                } else {
                                    target.textContent = targetValue;
                                }
                            };
                            
                            updateCounter();
                            observer.unobserve(target);
                        }
                    });
                }, {
                    threshold: 0.5
                });
                
                counters.forEach(counter => {
                    observer.observe(counter);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                counters.forEach(counter => {
                    const targetValue = parseInt(counter.getAttribute('data-count') || '0');
                    counter.textContent = targetValue;
                });
            }
        }

        // Video Error Handling
        function initVideoErrorHandling() {
            const video = document.getElementById('bgVideo');
            
            if (video) {
                // Handle video loading errors
                video.addEventListener('error', function(e) {
                    console.error('Video loading error:', e);
                    const videoContainer = video.closest('.video-container');
                    
                    if (videoContainer) {
                        videoContainer.classList.add('video-error');
                        // Add fallback background
                        videoContainer.style.backgroundImage = "url('" + video.getAttribute('poster') + "')";
                        videoContainer.style.backgroundSize = "cover";
                        videoContainer.style.backgroundPosition = "centre";
                    }
                });

                // Check if video is actually loaded
                video.addEventListener('loadeddata', function() {
                    console.log('Video loaded successfully');
                });
                
                // Set a timeout to check if video is stuck
                setTimeout(function() {
                    if (video.readyState === 0) {
                        console.warn('Video may be stuck, triggering error handler');
                        const event = new Event('error');
                        video.dispatchEvent(event);
                    }
                }, 5000);
            }
        }

        // Timeline Animation
        function initTimelineAnimation() {
            const timelineItems = document.querySelectorAll('.timeline-item');
            
            if (timelineItems.length === 0) return;
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.2,
                    rootMargin: '0px 0px -100px 0px'
                });
                
                timelineItems.forEach(item => {
                    observer.observe(item);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                timelineItems.forEach(item => {
                    item.classList.add('animate');
                });
            }
        }

        // Initialize All Functions
        function init() {
            initPreloader();
            initMobileMenu();
            initSmoothScroll();
            initBackToTopButton();
            initHeaderScrollEffect();
            initSectionAnimations();
            initCounterAnimations();
            initVideoErrorHandling();
            initTimelineAnimation();
        }

        // Run Initialization
        init();
    });
    </script>
</body>
</html>