<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IIUM PD-CARE - Community-based Rehabilitation Center</title>
    
    {{-- Preload Critical Resources --}}
    <link rel="preload" href="{{ asset('videos/welcomepage.mp4') }}" as="video" type="video/mp4">
    <link rel="preload" href="{{ asset('css/homestyle.css') }}" as="style">
    
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

    {{-- Header --}}
    @include('layouts.header')

    {{-- Hero Section with Video Background --}}
    <section id="home" class="hero-section">
        <div class="video-container">
            <video autoplay muted loop playsinline poster="{{ asset('images/welcome-img.jpg') }}">
                <source src="{{ asset('videos/welcomepage.mp4') }}" type="video/mp4">
            </video>
            <div class="video-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 text-center">
                        <img src="{{ asset('images/logo/logo_ppdk.jpg') }}" alt="IIUM PD-CARE" class="hero-logo mb-4">
                        <h1 class="hero-title">IIUM PD-CARE</h1>
                        <h2 class="hero-subtitle">Community-Based Rehabilitation Center</h2>
                        <p class="hero-motto">"Weaving Hearts, Unleashing Potentials"</p>
                        <div class="hero-partners mt-4">
                            <img src="{{ asset('images/logo/logo iium.png') }}" alt="IIUM" class="partner-logo">
                            <img src="{{ asset('images/logo/logo jkm.png') }}" alt="JKM" class="partner-logo">
                        </div>
                        <a href="{{ route('auth.loginpage') }}" class="btn btn-primary mt-4">
                            <i class="fas fa-sign-in-alt"></i> Staff Portal
                        </a>
                    </div>
                </div>
            </div>
            <a href="#vision" class="scroll-indicator">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    {{-- Vision & Mission Section --}}
    <section id="vision" class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="vision-box">
                        <div class="section-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h2 class="section-title">Our Vision</h2>
                        <p class="vision-text">
                            To be a leading center of excellence in empowerment and inclusivity with a holistic approach based on Islamic worldview to enhance the wellbeing and potentials of persons with disabilities
                        </p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="mission-box">
                        <div class="section-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h2 class="section-title">Our Mission</h2>
                        <ul class="mission-list">
                            <li>To promote inclusivity by creating an accessible, supportive, and respectful environment where individuals of all abilities are valued and can fully participate in the community.</li>
                            <li>To adopt a holistic approach based on the Islamic worldview, integrating physical, emotional, intellectual and spiritual support in all programs and initiatives.</li>
                            <li>To advocate for the rights and dignity of persons with disabilities, inspiring a compassionate society in accordance with the Qur'an and Sunnah.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Client Charter Section --}}
    <section id="charter" class="section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Client Charter</h2>
                <div class="section-divider"></div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="charter-card">
                        <div class="charter-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Respect & Dignity</h4>
                        <p>Treat every individual with respect, dignity and compassion.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="charter-card">
                        <div class="charter-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h4>Comprehensive Care</h4>
                        <p>Offer comprehensive care addressing physical, emotional, intellectual and spiritual needs fostering empowerment and inclusion.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="charter-card">
                        <div class="charter-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h4>Empowerment</h4>
                        <p>Empower individuals through Islamic principles, promoting independence, resilience.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="charter-card">
                        <div class="charter-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Partnership</h4>
                        <p>Build strong partnerships with families and the community to support the rehabilitation process.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Journey Timeline (Redesigned History Section) --}}
    <section id="journey" class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Journey</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">From vision to reality - our milestones</p>
            </div>
            
            <div class="journey-timeline">
                <div class="timeline-line"></div>
                
                <div class="timeline-block" data-aos="fade-right">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-date">2020</span>
                        <h3>Inception</h3>
                        <p>Initial concept development for IIUM PD-CARE in collaboration with the Disability Services Unit (DSU).</p>
                    </div>
                </div>

                <div class="timeline-block" data-aos="fade-left">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-date">2021</span>
                        <h3>Research & Planning</h3>
                        <p>Comprehensive needs assessment and stakeholder engagement to shape our approach.</p>
                    </div>
                </div>

                <div class="timeline-block" data-aos="fade-right">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-date">2022</span>
                        <h3>Development Phase</h3>
                        <p>System development and pilot programs with core functionalities.</p>
                    </div>
                </div>

                <div class="timeline-block" data-aos="fade-left">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-date">2023</span>
                        <h3>Official Launch</h3>
                        <p>Full-scale launch of IIUM PD-CARE with comprehensive rehabilitation programs.</p>
                    </div>
                </div>

                <div class="timeline-block" data-aos="fade-right">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-date">2024</span>
                        <h3>Expansion & Excellence</h3>
                        <p>Growing our impact through enhanced services and community partnerships.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services Section --}}
    <section id="services" class="section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Services</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">Comprehensive support for children with various disabilities</p>
            </div>
            
            <div class="row">
                @php
                $services = [
                    ['icon' => 'fa-brain', 'title' => 'Autism Spectrum Support', 'desc' => 'Specialized programs for children with ASD focusing on communication and social skills.'],
                    ['icon' => 'fa-deaf', 'title' => 'Hearing Impairment', 'desc' => 'Sign language training and communication enhancement programs.'],
                    ['icon' => 'fa-low-vision', 'title' => 'Visual Impairment', 'desc' => 'Mobility training and adaptive technology support.'],
                    ['icon' => 'fa-wheelchair', 'title' => 'Physical Disabilities', 'desc' => 'Physical therapy and mobility aids training.'],
                    ['icon' => 'fa-graduation-cap', 'title' => 'Learning Support', 'desc' => 'Specialized educational approaches for various learning challenges.'],
                    ['icon' => 'fa-comments', 'title' => 'Speech Therapy', 'desc' => 'Communication development and language therapy programs.']
                ];
                @endphp
                
                @foreach($services as $service)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas {{ $service['icon'] }}"></i>
                        </div>
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    
{{-- Organization Structure Section --}}
<section id="team" class="section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Organization Structure</h2>
            <div class="section-divider"></div>
        </div>
        
        {{-- First Org Chart --}}
        <div class="org-chart-container mb-5">
            <h3 class="text-center mb-4">Management Team</h3>
            <div class="org-structure">
                {{-- Director Level --}}
                <div class="org-level">
                    <div class="org-card director">
                        <div class="position-badge">PENGERUSI</div>
                        <img src="{{ asset('images/team/director1.jpg') }}" alt="Professor Dr. Ruzita">
                        <h4>Professor Dr. Ruzita Binti Mohd Amin</h4>
                    </div>
                </div>
                
                {{-- Deputy Director --}}
                <div class="org-level">
                    <div class="org-card deputy">
                        <div class="position-badge">TIMBALAN PENGERUSI</div>
                        <img src="{{ asset('images/team/deputy1.jpg') }}" alt="Professor Madya Dr. Aida">
                        <h4>Professor Madya Dr. Aida Binti Mokhtar</h4>
                    </div>
                </div>
                
                {{-- Department Heads --}}
                <div class="org-level departments">
                    <div class="org-card">
                        <div class="position-badge">SETIAUSAHA</div>
                        <img src="{{ asset('images/team/secretary.jpg') }}" alt="Professor Madya Dr. Nor Azizan">
                        <h4>Professor Madya Dr. Nor Azizan Binti Che Embi</h4>
                    </div>
                    <div class="org-card">
                        <div class="position-badge">BENDAHARI</div>
                        <img src="{{ asset('images/team/treasurer.jpg') }}" alt="Professor Madya Dr. Wan Rohaida">
                        <h4>Professor Madya Dr. Wan Rohaida Binti Wan Husain</h4>
                    </div>
                </div>
                
                {{-- Assistant Secretary --}}
                <div class="org-level">
                    <div class="org-card">
                        <div class="position-badge">PENOLONG SETIAUSAHA</div>
                        <img src="{{ asset('images/team/assistant.jpg') }}" alt="Dr. Wan Aishah">
                        <h4>Dr. Wan Aishah Hanim Abd Karim</h4>
                    </div>
                </div>
                
                {{-- Committee Members --}}
                <div class="org-level committee">
                    <h4 class="committee-title">AHLI JAWATANKUASA</h4>
                    <div class="committee-members">
                        <div class="org-card">
                            <img src="{{ asset('images/team/member1.jpg') }}" alt="Professor Madya Dr. Radiah">
                            <h4>Professor Madya Dr. Radiah Binti Abdul Ghani</h4>
                        </div>
                        <div class="org-card">
                            <img src="{{ asset('images/team/member2.jpg') }}" alt="Dr. Zunaidah">
                            <h4>Dr. Zunaidah Binti Mohd Marzuki</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Second Org Chart --}}
        <div class="org-chart-container">
            <h3 class="text-center mb-4">Administrative Team</h3>
            <div class="org-structure">
                {{-- Head --}}
                <div class="org-level">
                    <div class="org-card director">
                        <div class="position-badge">PENYELIA</div>
                        <img src="{{ asset('images/team/supervisor.jpg') }}" alt="Nurul Huda">
                        <h4>Nurul Huda Binti Azaman</h4>
                    </div>
                </div>
                
                {{-- Officers --}}
                <div class="org-level">
                    <h4 class="committee-title">PETUGAS</h4>
                    <div class="departments">
                        <div class="org-card">
                            <img src="{{ asset('images/team/officer1.jpg') }}" alt="Nabilah">
                            <h4>Nabilah Binti Mohd Isa</h4>
                        </div>
                        <div class="org-card">
                            <img src="{{ asset('images/team/officer2.jpg') }}" alt="Siti Nadia">
                            <h4>Siti Nadia Binti Ashari</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    {{-- Impact Section --}}
<section id="impact" class="section bg-primary text-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title text-white">Our Impact</h2>
            <div class="section-divider bg-white"></div>
        </div>
        
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-box">
                    <div class="impact-number" data-count="50">0</div>
                    <p>Children Supported</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-box">
                    <div class="impact-number" data-count="30">0</div>
                    <p>Dedicated Volunteers</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-box">
                    <div class="impact-number" data-count="15">0</div>
                    <p>Professional Staff</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="impact-box">
                    <div class="impact-number" data-count="200">0</div>
                    <p>Activities Conducted</p>
                </div>
            </div>
        </div>
    </div>
</section>

    {{-- Footer --}}
@include('layouts.footer')

{{-- Back to top --}}
<a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/homepage.js') }}"></script>

{{-- Add Org Chart Specific Styles --}}
<style>
.org-chart-container {
    margin: 40px 0;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 15px;
}

.org-structure {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 40px;
}

.org-level {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 30px;
    position: relative;
}

.org-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    min-width: 200px;
    position: relative;
}

.org-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.org-card img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin: 15px auto;
    border: 4px solid #f0f0f0;
}

.org-card h4 {
    font-size: 14px;
    margin: 10px 0 5px;
    color: #333;
}

.position-badge {
    background: linear-gradient(135deg, #32bdea, #c850c0);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.org-card.director .position-badge {
    background: linear-gradient(135deg, #1a2a3a, #2c4156);
}

.org-card.deputy .position-badge {
    background: linear-gradient(135deg, #c850c0, #32bdea);
}

.committee-title {
    text-align: center;
    color: #1a2a3a;
    font-weight: 600;
    margin-bottom: 20px;
    padding: 10px 30px;
    background: linear-gradient(135deg, #32bdea, #c850c0);
    color: white;
    border-radius: 25px;
    font-size: 16px;
}

.committee-members, .departments {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    justify-content: center;
}

/* Connecting Lines */
.org-level:not(:last-child)::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 50%;
    width: 2px;
    height: 40px;
    background: #ddd;
    transform: translateX(-50%);
}

@media (max-width: 768px) {
    .org-card {
        min-width: 150px;
    }
    
    .org-card img {
        width: 80px;
        height: 80px;
    }
    
    .org-card h4 {
        font-size: 12px;
    }
}
</style>
</body>
</html>