<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Our Volunteer Program | CREAMS</title>
    
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    
    {{-- CSS Dependencies --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="{{ asset('css/homestyle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/headerstyle.css')}}">
    <link rel="stylesheet" href="{{ asset('css/volunteerstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footerstyle.css') }}">
</head>
<body>
    {{-- Preloader --}}
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    @include('layouts.header')

{{-- Sticky Volunteer Button --}}
<a href="{{ route('volunteer') }}" class="sticky-volunteer">
    <i class="fas fa-hands-helping"></i>
    <span class="tooltip">Volunteer With Us</span>
</a>

    {{-- Video Hero Section --}}
    <section class="video-hero">
        <div class="video-container">
            <video autoplay muted loop playsinline id="volunteerVideo" poster="{{ asset('images/volunteer-hero-bg.jpg') }}">
                <source src="{{ asset('videos/volunteerpage.mp4') }}" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
            <div class="video-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container">
                <div class="row justify-content-centre">
                    <div class="col-lg-10 text-centre" data-aos="fade-up" data-aos-delay="100">
                        <h1>Make a Difference in a Child's Life</h1>
                        <p class="lead">At CREAMS, we believe every volunteer has the power to transform lives. Join our community of dedicated individuals working together to support children with special needs.</p>
                        <a href="#volunteer-form" class="hero-btn">
                            <span>Become a Volunteer</span>
                            <i class="fas fa-arrow-down"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Impact Stats Section --}}
    <section class="impact-stats">
        <div class="container">
            <div class="row justify-content-centre">
                <div class="col-lg-10">
                    <div class="stats-wrapper">
                        <div class="row text-centre">
                            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-child"></i>
                                    </div>
                                    <div class="stat-number" data-count="50">0</div>
                                    <div class="stat-label">Children Supported</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <div class="stat-number" data-count="30">0</div>
                                    <div class="stat-label">Active Volunteers</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="stat-number" data-count="120">0</div>
                                    <div class="stat-label">Monthly Sessions</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-number" data-count="5000">0</div>
                                    <div class="stat-label">Volunteer Hours</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How You Can Help Section --}}
    <section class="how-you-help">
        <div class="container">
            <div class="section-heading text-centre" data-aos="fade-up">
                <span class="section-subtitle">Volunteer Opportunities</span>
                <h2>How You Can Make a Difference</h2>
                <div class="heading-line"></div>
                <p class="section-description">Discover the various ways you can contribute to our mission and help children with special needs reach their full potential.</p>
            </div>
            
            <div class="row mt-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h3>Direct Support</h3>
                        <p>Work one-on-one with children in rehabilitation activities, providing personalized care and support in their developmental journey.</p>
                        <ul class="help-features">
                            <li><i class="fas fa-check-circle"></i> Tutoring & homework assistance</li>
                            <li><i class="fas fa-check-circle"></i> Mobility assistance</li>
                            <li><i class="fas fa-check-circle"></i> Recreational activities support</li>
                        </ul>
                        <div class="time-commitment">
                            <i class="far fa-clock"></i> 4-6 hours weekly
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="help-card featured">
                        <div class="featured-tag">Most Needed</div>
                        <div class="help-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3>Skills Sharing</h3>
                        <p>Contribute your professional expertise in education, therapy, healthcare, or other relevant fields to enhance our comprehensive support system.</p>
                        <ul class="help-features">
                            <li><i class="fas fa-check-circle"></i> Speech therapy assistance</li>
                            <li><i class="fas fa-check-circle"></i> Special education techniques</li>
                            <li><i class="fas fa-check-circle"></i> Physical therapy support</li>
                        </ul>
                        <div class="time-commitment">
                            <i class="far fa-clock"></i> 2-4 hours weekly
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3>Event Support</h3>
                        <p>Help organize and run special events, workshops, and community activities that raise awareness and support for children with special needs.</p>
                        <ul class="help-features">
                            <li><i class="fas fa-check-circle"></i> Fundraising events</li>
                            <li><i class="fas fa-check-circle"></i> Community workshops</li>
                            <li><i class="fas fa-check-circle"></i> Awareness campaigns</li>
                        </ul>
                        <div class="time-commitment">
                            <i class="far fa-clock"></i> Flexible hours
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h3>Creative Arts</h3>
                        <p>Use your artistic talents to engage children in creative expression through art, music, dance, or drama therapy activities.</p>
                        <ul class="help-features">
                            <li><i class="fas fa-check-circle"></i> Art therapy sessions</li>
                            <li><i class="fas fa-check-circle"></i> Music & movement classes</li>
                            <li><i class="fas fa-check-circle"></i> Drama & storytelling</li>
                        </ul>
                        <div class="time-commitment">
                            <i class="far fa-clock"></i> 2-3 hours weekly
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3>Administrative Support</h3>
                        <p>Assist with administrative tasks, data management, and communication to help our operations run smoothly and efficiently.</p>
                        <ul class="help-features">
                            <li><i class="fas fa-check-circle"></i> Database management</li>
                            <li><i class="fas fa-check-circle"></i> Communication assistance</li>
                            <li><i class="fas fa-check-circle"></i> Document preparation</li>
                        </ul>
                        <div class="time-commitment">
                            <i class="far fa-clock"></i> 3-5 hours weekly
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3>Advocacy & Outreach</h3>
                        <p>Help spread awareness about disabilities, advocate for inclusive policies, and expand our reach within the community.</p>
                        <ul class="help-features">
                            <li><i class="fas fa-check-circle"></i> Community presentations</li>
                            <li><i class="fas fa-check-circle"></i> Social media campaigns</li>
                            <li><i class="fas fa-check-circle"></i> Partnership development</li>
                        </ul>
                        <div class="time-commitment">
                            <i class="far fa-clock"></i> Flexible hours
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Volunteer Testimonials --}}
    <section class="testimonial-section">
        <div class="container">
            <div class="section-heading text-centre" data-aos="fade-up">
                <span class="section-subtitle">Volunteer Stories</span>
                <h2>Hear From Our Volunteers</h2>
                <div class="heading-line"></div>
            </div>
            
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="testimonial-slider" data-aos="fade-up" data-aos-delay="100">
                        <div class="testimonial-item">
                            <div class="testimonial-content">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>
                                <p>"Volunteering with CREAMS has been one of the most rewarding experiences of my life. Seeing the children's progress week after week fills my heart with joy. Every moment spent here reminds me that small acts of kindness can truly make a big difference."</p>
                            </div>
                            <div class="testimonial-person">
                                <div class="person-img">
                                    <img src="{{ asset('images/volunteers/volunteer1.jpg') }}" alt="Nurul Izzah">
                                </div>
                                <div class="person-info">
                                    <h4>Nurul Izzah</h4>
                                    <p>Direct Support Volunteer <span>•</span> 2 years</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="testimonial-item">
                            <div class="testimonial-content">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>
                                <p>"As a speech therapist, I wanted to use my skills to help those who need it most. At CREAMS, I found the perfect opportunity to do just that. The structured approach and supportive team environment makes volunteering here both impactful and enjoyable."</p>
                            </div>
                            <div class="testimonial-person">
                                <div class="person-img">
                                    <img src="{{ asset('images/volunteers/volunteer2.jpg') }}" alt="Ahmad Rizal">
                                </div>
                                <div class="person-info">
                                    <h4>Ahmad Rizal</h4>
                                    <p>Skills Sharing Volunteer <span>•</span> 1.5 years</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="testimonial-item">
                            <div class="testimonial-content">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>
                                <p>"I started volunteering during my university studies to gain experience, but it quickly became more than that. The children's resilience and determination inspire me daily, and being part of their journey has been a profound learning experience."</p>
                            </div>
                            <div class="testimonial-person">
                                <div class="person-img">
                                    <img src="{{ asset('images/volunteers/volunteer3.jpg') }}" alt="Sarah Tan">
                                </div>
                                <div class="person-info">
                                    <h4>Sarah Tan</h4>
                                    <p>Event Support Volunteer <span>•</span> 1 year</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Training & Support Section --}}
    <section class="training-section">
        <div class="container">
            <div class="row align-items-centre">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="training-img">
                        <img src="{{ asset('images/volunteer-training.jpg') }}" alt="Volunteer Training" class="img-fluid rounded">
                        <div class="training-badge">
                            <div class="badge-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div class="badge-text">
                                Professional Training Provided
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="training-content">
                        <span class="section-subtitle">We Support Our Volunteers</span>
                        <h2>Comprehensive Training & Ongoing Support</h2>
                        <div class="heading-line"></div>
                        <p>We believe in equipping our volunteers with the knowledge and skills they need to make a meaningful impact. When you join our team, you'll receive:</p>
                        
                        <div class="support-features">
                            <div class="support-item">
                                <div class="support-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="support-text">
                                    <h4>Initial Training Program</h4>
                                    <p>A comprehensive orientation and training program that covers working with children with special needs, safeguarding practices, and role-specific skills.</p>
                                </div>
                            </div>
                            
                            <div class="support-item">
                                <div class="support-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="support-text">
                                    <h4>Mentorship & Guidance</h4>
                                    <p>Ongoing mentorship from experienced staff and volunteers to support your development and answer any questions.</p>
                                </div>
                            </div>
                            
                            <div class="support-item">
                                <div class="support-icon">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <div class="support-text">
                                    <h4>Regular Workshops</h4>
                                    <p>Access to regular skill-building workshops and educational sessions to enhance your effectiveness as a volunteer.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Volunteer Application Form --}}
    <section id="volunteer-form" class="form-section">
        <div class="container">
            <div class="row justify-content-centre">
                <div class="col-lg-8">
                    <div class="form-wrapper" data-aos="fade-up">
                        <div class="form-header">
                            <div class="form-icon">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h2>Join Our Volunteer Team</h2>
                            <p>Complete the form below to start your volunteer journey with CREAMS</p>
                        </div>
                        
                        <form id="volunteerForm" action="{{ route('volunteer.submit') }}" method="POST">
                            @csrf
                            <div class="form-step active" id="step1">
                                <h3 class="step-title">
                                    <span class="step-number">1</span> Personal Information
                                </h3>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="firstName">First Name <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="lastName">Last Name <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="email">Email Address <span class="required">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="phone">Phone Number <span class="required">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control" id="address" name="address">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="postcode">Postal Code</label>
                                        <input type="text" class="form-control" id="postcode" name="postcode">
                                    </div>
                                </div>
                                
                                <div class="form-navigation text-right">
                                    <button type="button" class="btn next-btn" data-step="2">
                                        Next <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-step" id="step2">
                                <h3 class="step-title">
                                    <span class="step-number">2</span> Volunteer Preferences
                                </h3>
                                
                                <div class="form-group">
                                    <label for="interest">Area of Interest <span class="required">*</span></label>
                                    <select class="form-control" id="interest" name="interest" required>
                                        <option value="">Select an area</option>
                                        <option value="direct-support">Direct Support</option>
                                        <option value="skills-sharing">Skills Sharing</option>
                                        <option value="event-support">Event Support</option>
                                        <option value="creative-arts">Creative Arts</option>
                                        <option value="administrative">Administrative Support</option>
                                        <option value="advocacy">Advocacy & Outreach</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="otherInterestGroup" style="display: none;">
                                    <label for="otherInterest">Please specify</label>
                                    <input type="text" class="form-control" id="otherInterest" name="other_interest">
                                </div>
                                
                                <div class="form-group">
                                    <label for="skills">Relevant Skills & Experience</label>
                                    <textarea class="form-control" id="skills" name="skills" rows="3" placeholder="Tell us about any skills, qualifications or experience that might be relevant"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Availability <span class="required">*</span></label>
                                    <div class="availability-options">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="availWeekday" name="availability[]" value="weekday">
                                            <label class="custom-control-label" for="availWeekday">Weekdays (9am-5pm)</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="availEvening" name="availability[]" value="evening">
                                            <label class="custom-control-label" for="availEvening">Evenings (5pm-9pm)</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="availWeekend" name="availability[]" value="weekend">
                                            <label class="custom-control-label" for="availWeekend">Weekends</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="commitment">Time Commitment <span class="required">*</span></label>
                                    <select class="form-control" id="commitment" name="commitment" required>
                                        <option value="">Select time commitment</option>
                                        <option value="1-3">1-3 hours per week</option>
                                        <option value="4-6">4-6 hours per week</option>
                                        <option value="7-10">7-10 hours per week</option>
                                        <option value="flexible">Flexible/As needed</option>
                                    </select>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn prev-btn" data-step="1">
                                        <i class="fas fa-arrow-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn next-btn" data-step="3">
                                        Next <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-step" id="step3">
                                <h3 class="step-title">
                                    <span class="step-number">3</span> Additional Information
                                </h3>
                                
                                <div class="form-group">
                                    <label for="motivation">What motivates you to volunteer with CREAMS? <span class="required">*</span></label>
                                    <textarea class="form-control" id="motivation" name="motivation" rows="4" required placeholder="Share your motivation for volunteering with us"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="experience">Do you have any previous experience working with children with special needs?</label>
                                    <textarea class="form-control" id="experience" name="experience" rows="3" placeholder="If yes, please describe your experience"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="referral">How did you hear about our volunteer program?</label>
                                    <select class="form-control" id="referral" name="referral">
                                        <option value="">Select an option</option>
                                        <option value="website">CREAMS Website</option>
                                        <option value="social-media">Social Media</option>
                                        <option value="friend">Friend/Family Referral</option>
                                        <option value="event">Community Event</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="consent" name="consent" required>
                                        <label class="custom-control-label" for="consent">
                                            I consent to CREAMS processing my personal information for volunteer recruitment purposes. I understand that CREAMS will keep my information secure and will not share it with third parties. <span class="required">*</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn prev-btn" data-step="2">
                                        <i class="fas fa-arrow-left"></i> Previous
                                    </button>
                                    <button type="submit" class="btn submit-btn">
                                        Submit Application <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="faq-section">
        <div class="container">
            <div class="section-heading text-centre" data-aos="fade-up">
                <span class="section-subtitle">Common Questions</span>
                <h2>Frequently Asked Questions</h2>
                <div class="heading-line"></div>
            </div>
            
            <div class="row justify-content-centre">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="accordion" id="faqAccordion">
                        <div class="card">
                            <div class="card-header" id="faqHeading1">
                                <h3 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                        Do I need previous experience to volunteer?
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </h3>
                            </div>
                            <div id="faqCollapse1" class="collapse show" aria-labelledby="faqHeading1" data-parent="#faqAccordion">
                                <div class="card-body">
                                    <p>No, previous experience is not required for all volunteer roles. We provide comprehensive training for all volunteers. Your willingness to learn and commitment to helping children with special needs is what matters most. However, for some specialized roles (like skills sharing), relevant professional experience or qualifications may be beneficial.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" id="faqHeading2">
                                <h3 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                        What is the minimum time commitment?
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </h3>
                            </div>
                            <div id="faqCollapse2" class="collapse" aria-labelledby="faqHeading2" data-parent="#faqAccordion">
                                <div class="card-body">
                                    <p>We ask for a minimum commitment of 2-3 hours per week for at least 3 months. This helps provide consistency for the children we serve and allows you to develop meaningful relationships. However, we understand that schedules can vary, and we offer flexible options for those with limited availability, including event-based volunteer opportunities.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" id="faqHeading3">
                                <h3 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                        What training will I receive?
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </h3>
                            </div>
                            <div id="faqCollapse3" class="collapse" aria-labelledby="faqHeading3" data-parent="#faqAccordion">
                                <div class="card-body">
                                    <p>All volunteers receive a comprehensive orientation and training program covering:
                                    <ul>
                                        <li>Understanding different types of disabilities</li>
                                        <li>Effective communication techniques</li>
                                        <li>Child protection and safeguarding</li>
                                        <li>Role-specific skills training</li>
                                        <li>Health and safety procedures</li>
                                    </ul>
                                    Additionally, ongoing training and support are provided throughout your volunteer journey.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" id="faqHeading4">
                                <h3 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                        Can I volunteer if I'm a trainee?
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </h3>
                            </div>
                            <div id="faqCollapse4" class="collapse" aria-labelledby="faqHeading4" data-parent="#faqAccordion">
                                <div class="card-body">
                                    <p>Yes! We welcome trainee volunteers. Many trainees find volunteering with CREAMS to be a valuable experience that complements their studies, especially those in fields like education, psychology, social work, healthcare, and therapy. We can provide documentation of your volunteer hours for academic requirements if needed.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" id="faqHeading5">
                                <h3 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                        What happens after I submit my application?
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </h3>
                            </div>
                            <div id="faqCollapse5" class="collapse" aria-labelledby="faqHeading5" data-parent="#faqAccordion">
                                <div class="card-body">
                                    <p>After submitting your application, our volunteer coordinator will review it and contact you within 7-10 business days. The process typically includes:
                                    <ol>
                                        <li>Initial application review</li>
                                        <li>Brief phone or in-person interview</li>
                                        <li>Reference checks</li>
                                        <li>Background screening (as appropriate for the role)</li>
                                        <li>Orientation and training</li>
                                        <li>Placement in a suitable volunteer role</li>
                                    </ol>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Call to Action --}}
    <section class="cta-section">
        <div class="container">
            <div class="cta-wrapper" data-aos="fade-up">
                <div class="row align-items-centre">
                    <div class="col-lg-8">
                        <h2>Ready to make a difference?</h2>
                        <p>Join our community of dedicated volunteers today and help create a brighter future for children with special needs.</p>
                    </div>
                    <div class="col-lg-4 text-lg-right">
                        <a href="#volunteer-form" class="cta-btn">Apply Now <i class="fas fa-arrow-right"></i></a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="{{ asset('js/header.js') }} "></script>
    
    <script>
        $(document).ready(function() {
            // Initialize AOS
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
            
            // Hide preloader after page load
            setTimeout(function() {
                $('.preloader').fadeOut(500);
            }, 1000);
            
            // Multi-step form navigation
            $('.next-btn').click(function() {
                let currentStep = $(this).closest('.form-step');
                let nextStepNum = $(this).data('step');
                let nextStep = $('#step' + nextStepNum);
                
                // Basic validation
                let isValid = true;
                currentStep.find('input[required], select[required], textarea[required]').each(function() {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (isValid) {
                    currentStep.removeClass('active');
                    nextStep.addClass('active');
                }
            });
            
            $('.prev-btn').click(function() {
                let currentStep = $(this).closest('.form-step');
                let prevStepNum = $(this).data('step');
                let prevStep = $('#step' + prevStepNum);
                
                currentStep.removeClass('active');
                prevStep.addClass('active');
            });
            
            // Show/hide "Other" field for area of interest
            $('#interest').change(function() {
                if ($(this).val() === 'other') {
                    $('#otherInterestGroup').show();
                } else {
                    $('#otherInterestGroup').hide();
                }
            });
            
            // Counter animation for statistics
            function startCounters() {
                $('.stat-number').each(function() {
                    const $this = $(this);
                    const countTo = $this.data('count');
                    
                    $({ countNum: 0 }).animate({
                        countNum: countTo
                    }, {
                        duration: 2000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.floor(this.countNum));
                        },
                        complete: function() {
                            $this.text(this.countNum);
                        }
                    });
                });
            }
            
            // Start counters when they come into view
            const statsSection = document.querySelector('.impact-stats');
            if (statsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            startCounters();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                observer.observe(statsSection);
            }
            
            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('.back-to-top').addClass('active');
                } else {
                    $('.back-to-top').removeClass('active');
                }
            });
            
            $('.back-to-top').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
            
            // Smooth scrolling for anchor links
            $('a[href^="#"]').click(function(e) {
                e.preventDefault();
                
                const target = $(this.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800);
                }
            });
            
            // Form submission handling
            $('#volunteerForm').submit(function(e) {
                e.preventDefault();
                
                // Simulate form submission (replace with actual AJAX submission)
                alert('Thank you for your application! We will review it and get back to you soon.');
                $(this).trigger('reset');
                $('#step3').removeClass('active');
                $('#step1').addClass('active');
            });
        });
    </script>
</body>
</html>