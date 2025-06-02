
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - IIUM PD-CARE</title>
{{-- Favicon --}}
<link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">

{{-- CSS Dependencies --}}
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

{{-- External Custom Styles --}}
<link rel="stylesheet" href="{{ asset('css/contactstyle.css') }}">
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

{{-- Contact Hero Section --}}
<section class="contacthero">
    <div class="container">
        <h1>Connect with IIUM PD-CARE</h1>
        <p>We're here to support, listen, and collaborate. Reach out to us for any inquiries about our rehabilitation services.</p>
        <div class="herocta">
            <a href="#contactform" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Get in Touch
            </a>
        </div>
    </div>
</section>

{{-- Quick Connect Section --}}
<section class="quickconnect">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="connectmethod">
                    <div class="connecticon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p>(+60) 3-6421-6421</p>
                    <small>Monday - Friday, 9AM - 5PM</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="connectmethod">
                    <div class="connecticon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p>pdcare@iium.edu.my</p>
                    <small>We'll respond within 24 hours</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="connectmethod">
                    <div class="connecticon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Us</h3>
                    <p>IIUM Gombak Campus</p>
                    <small>Selangor, Malaysia</small>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Contact Form Section --}}
<section id="contactform" class="contactformsection">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="contactformwrapper">
                    <h2 class="formtitle">Send Us a Message</h2>
                    <p class="formsubtitle">Fill out the form below and we'll get back to you as soon as possible.</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" id="contactForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="required">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reason">Reason for Contact <span class="required">*</span></label>
                                    <select name="reason" id="reason" 
                                            class="form-control @error('reason') is-invalid @enderror" required>
                                        <option value="">Select a reason</option>
                                        <option value="services" {{ old('reason') == 'services' ? 'selected' : '' }}>
                                            Rehabilitation Services
                                        </option>
                                        <option value="volunteer" {{ old('reason') == 'volunteer' ? 'selected' : '' }}>
                                            Volunteer Inquiry
                                        </option>
                                        <option value="partnership" {{ old('reason') == 'partnership' ? 'selected' : '' }}>
                                            Partnership Opportunity
                                        </option>
                                        <option value="support" {{ old('reason') == 'support' ? 'selected' : '' }}>
                                            Support & Donations
                                        </option>
                                        <option value="admission" {{ old('reason') == 'admission' ? 'selected' : '' }}>
                                            Admission Inquiry
                                        </option>
                                        <option value="complaint" {{ old('reason') == 'complaint' ? 'selected' : '' }}>
                                            Complaint
                                        </option>
                                        <option value="feedback" {{ old('reason') == 'feedback' ? 'selected' : '' }}>
                                            Feedback
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
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject">Subject</label>
                                    <input type="text" name="subject" id="subject" 
                                           class="form-control @error('subject') is-invalid @enderror" 
                                           value="{{ old('subject') }}">
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="urgency">Priority Level</label>
                                    <select name="urgency" id="urgency" 
                                            class="form-control @error('urgency') is-invalid @enderror">
                                        <option value="medium" {{ old('urgency') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('urgency') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('urgency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">Your Message <span class="required">*</span></label>
                            <textarea name="message" id="message" rows="6" maxlength="2000"
                                      class="form-control @error('message') is-invalid @enderror" 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg submitbtn">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Map Section --}}
<section id="map" class="mapsection">
    <div class="container">
        <div class="sectiontitle text-center">
            <h2>Find Us</h2>
            <div class="sectiondivider"></div>
            <p>Visit us at the IIUM Gombak Campus</p>
        </div>
        <div class="mapcontainer">
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
</section>

{{-- Footer --}}
@include('layouts.footer')

{{-- Back to top button --}}
<a href="#" class="backtotop"><i class="fas fa-arrow-up"></i></a>

{{-- External JavaScript --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/contact.js') }}"></script>
</body>
</html>