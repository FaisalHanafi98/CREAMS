<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CREAMS</title>
    
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    
    {{-- CSS Dependencies --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    
    {{-- Custom Styles --}}
    <link rel="stylesheet" href="{{ asset('css/contactstyle.css') }}">
    <link rel="stylesheet" href="{{  asset("css/headerstyle.css") }}">
    <link rel="stylesheet" href="{{  asset("css/footerstyle.css") }}">
</head>
<body>
    {{-- Preloader --}}
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    {{-- Header --}}
    @include('layouts.header')

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Contact Hero Section --}}
    <section class="contact-hero">
        <div class="container">
            <h1>Connect with CREAMS</h1>
            <p>We're here to support, listen, and collaborate. Reach out to us for any inquiries about our rehabilitation services.</p>
            <div class="hero-cta">
                <a href="#contact-form" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Get in Touch
                </a>
            </div>
        </div>
    </section>

    {{-- Quick Connect Section --}}
    <section class="quick-connect">
        <div class="container">
            <div class="row">
                <div class="col-md-4 connect-method">
                    <div class="connect-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p>(+60) 3642 1633 5</p>
                </div>
                <div class="col-md-4 connect-method">
                    <div class="connect-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p>dsu-creams@iium.edu.my</p>
                </div>
                <div class="col-md-4 connect-method">
                    <div class="connect-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Us</h3>
                    <p>IIUM, Gombak, Kuala Lumpur</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact Form Section --}}
    <section id="contact-form" class="contact-form-section">
        <div class="container">
            <div class="row justify-content-centre">
                <div class="col-md-8">
                    <div class="contact-form-wrapper">
                        <h2>Send Us a Message</h2>
                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <input type="text" name="name" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    placeholder="Your Name" 
                                    value="{{ old('name') }}" 
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <input type="email" name="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    placeholder="Your Email" 
                                    value="{{ old('email') }}" 
                                    required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <input type="tel" name="phone" 
                                    class="form-control @error('phone') is-invalid @enderror" 
                                    placeholder="Your Phone (Optional)" 
                                    value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <select name="reason" 
                                    class="form-control @error('reason') is-invalid @enderror" 
                                    required>
                                    <option value="" selected disabled>Reason for Contact</option>
                                    <option value="services" {{ old('reason') == 'services' ? 'selected' : '' }}>
                                        Rehabilitation Services
                                    </option>
                                    <option value="support" {{ old('reason') == 'support' ? 'selected' : '' }}>
                                        Support
                                    </option>
                                    <option value="volunteer" {{ old('reason') == 'volunteer' ? 'selected' : '' }}>
                                        Volunteer Opportunities
                                    </option>
                                    <option value="partnership" {{ old('reason') == 'partnership' ? 'selected' : '' }}>
                                        Partnership Inquiry
                                    </option>
                                    <option value="general" {{ old('reason') == 'general' ? 'selected' : '' }}>
                                        General Inquiry
                                    </option>
                                    <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>
                                        Other
                                    </option>
                                </select>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <textarea name="message" 
                                    class="form-control @error('message') is-invalid @enderror" 
                                    rows="5" 
                                    placeholder="Your Message" 
                                    required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Map Section --}}
    <section id="map" class="section section-visible">
        <div class="container">
            <div class="section-title">
                <h2>Find Us</h2>
                <div class="section-divider"></div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.7557133922336!2d101.72881931535646!3d3.2533985976583634!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc38c2f677344b%3A0xf436637f3a77624b!2sInternational%20Islamic%20University%20Malaysia!5e0!3m2!1sen!2smy!4v1647932990357!5m2!1sen!2smy" 
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    @include('layouts.footer')

    {{-- Back to top button --}}
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

    {{-- JavaScript Dependencies --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);

        // Back to top button
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
    });
    </script>
</body>
</html>