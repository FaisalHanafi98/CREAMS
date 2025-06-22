<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Our Volunteer Program - IIUM PD-CARE</title>
{{-- Favicon --}}
<link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">

{{-- CSS Dependencies --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

{{-- Custom Styles --}}
<link rel="stylesheet" href="{{ asset('css/homestyle.css') }}">
<link rel="stylesheet" href="{{ asset('css/headerstyle.css') }}">
<link rel="stylesheet" href="{{ asset('css/volunteerstyle.css') }}">
<link rel="stylesheet" href="{{ asset('css/footerstyle.css') }}">
</head>
<body>
    {{-- Preloader --}}
    <div class="preloader">
        <div class="spinner"></div>
    </div>
{{-- Header --}}
@include('layouts.header')

{{-- Video Hero Section --}}
<section class="video-hero">
    <div class="video-container">
        <video autoplay muted loop playsinline id="volunteerVideo" poster="{{ asset('images/volunteerhero.jpg') }}">
            <source src="{{ asset('videos/volunteerpage.mp4') }}" type="video/mp4">
            Your browser does not support HTML5 video.
        </video>
        <div class="video-overlay"></div>
    </div>
    <div class="hero-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center" data-aos="fade-up" data-aos-delay="100">
                    <h1>Make a Difference in a Child's Life</h1>
                    <p class="lead">At IIUM PD-CARE, we believe every volunteer has the power to transform lives. Join our community of dedicated individuals working together to support children with special needs.</p>
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
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="stats-wrapper">
                    <div class="row text-center">
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
        <div class="section-heading text-center" data-aos="fade-up">
            <span class="section-subtitle">Volunteer Opportunities</span>
            <h2>How You Can Make a Difference</h2>
            <div class="heading-line"></div>
            <p class="section-description">Discover the various ways you can contribute to our mission and help children with special needs reach their full potential.</p>
        </div>
        
        <div class="row mt-5">
            @php
            $opportunities = [
                [
                    'icon' => 'fa-user-friends',
                    'title' => 'Direct Support',
                    'description' => 'Work one-on-one with children in rehabilitation activities, providing personalized care and support in their developmental journey.',
                    'features' => [
                        'Tutoring & homework assistance',
                        'Mobility assistance',
                        'Recreational activities support'
                    ],
                    'time' => '4-6 hours weekly',
                    'featured' => false
                ],
                [
                    'icon' => 'fa-chalkboard-teacher',
                    'title' => 'Skills Sharing',
                    'description' => 'Contribute your professional expertise in education, therapy, healthcare, or other relevant fields to enhance our comprehensive support system.',
                    'features' => [
                        'Speech therapy assistance',
                        'Special education techniques',
                        'Physical therapy support'
                    ],
                    'time' => '2-4 hours weekly',
                    'featured' => true
                ],
                [
                    'icon' => 'fa-calendar-alt',
                    'title' => 'Event Support',
                    'description' => 'Help organize and run special events, workshops, and community activities that raise awareness and support for children with special needs.',
                    'features' => [
                        'Fundraising events',
                        'Community workshops',
                        'Awareness campaigns'
                    ],
                    'time' => 'Flexible hours',
                    'featured' => false
                ],
                [
                    'icon' => 'fa-paint-brush',
                    'title' => 'Creative Arts',
                    'description' => 'Use your artistic talents to engage children in creative expression through art, music, dance, or drama therapy activities.',
                    'features' => [
                        'Art therapy sessions',
                        'Music & movement classes',
                        'Drama & storytelling'
                    ],
                    'time' => '2-3 hours weekly',
                    'featured' => false
                ],
                [
                    'icon' => 'fa-laptop-code',
                    'title' => 'Administrative Support',
                    'description' => 'Assist with administrative tasks, data management, and communication to help our operations run smoothly and efficiently.',
                    'features' => [
                        'Database management',
                        'Communication assistance',
                        'Document preparation'
                    ],
                    'time' => '3-5 hours weekly',
                    'featured' => false
                ],
                [
                    'icon' => 'fa-bullhorn',
                    'title' => 'Advocacy & Outreach',
                    'description' => 'Help spread awareness about disabilities, advocate for inclusive policies, and expand our reach within the community.',
                    'features' => [
                        'Community presentations',
                        'Social media campaigns',
                        'Partnership development'
                    ],
                    'time' => 'Flexible hours',
                    'featured' => false
                ]
            ];
            @endphp
            
            @foreach($opportunities as $opp)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                <div class="help-card {{ $opp['featured'] ? 'featured' : '' }}">
                    @if($opp['featured'])
                    <div class="featured-tag">Most Needed</div>
                    @endif
                    <div class="help-icon">
                        <i class="fas {{ $opp['icon'] }}"></i>
                    </div>
                    <h3>{{ $opp['title'] }}</h3>
                    <p>{{ $opp['description'] }}</p>
                    <ul class="help-features">
                        @foreach($opp['features'] as $feature)
                        <li><i class="fas fa-check-circle"></i> {{ $feature }}</li>
                        @endforeach
                    </ul>
                    <div class="time-commitment">
                        <i class="far fa-clock"></i> {{ $opp['time'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Volunteer Application Form --}}
<section id="volunteer-form" class="form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-wrapper" data-aos="fade-up">
                    <div class="form-header">
                        <div class="form-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h2>Join Our Volunteer Team</h2>
                        <p>Complete the form below to start your volunteer journey with IIUM PD-CARE</p>
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div class="form-progress">
                        <div class="progress">
                            <div class="progress-bar form-progress-bar" role="progressbar" style="width: 33%"></div>
                        </div>
                    </div>

                    {{-- Alert Messages --}}
                    <div id="alert-container">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> Please correct the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif
                    </div>
                    
                    {{-- Main Form --}}
                    <form id="volunteerForm" action="{{ route('volunteer.submit') }}" method="POST">
                        @csrf
                        
                        {{-- Step 1: Personal Information --}}
                        <div class="form-step active" id="step1">
                            <h3 class="step-title">
                                <span class="step-number">1</span> Personal Information
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="last_name">Last Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="+60123456789" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address') }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="city">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="postcode">Postal Code</label>
                                    <input type="text" class="form-control @error('postcode') is-invalid @enderror" 
                                           id="postcode" name="postcode" value="{{ old('postcode') }}">
                                    @error('postcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-navigation text-right">
                                <button type="button" class="btn btn-primary next-btn" data-step="2">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        {{-- Step 2: Volunteer Preferences --}}
                        <div class="form-step" id="step2">
                            <h3 class="step-title">
                                <span class="step-number">2</span> Volunteer Preferences
                            </h3>
                            
                            <div class="form-group">
                                <label for="interest">Area of Interest <span class="required">*</span></label>
                                <select class="form-control @error('interest') is-invalid @enderror" 
                                        id="interest" name="interest" required>
                                    <option value="">Select an area</option>
                                    <option value="direct-support" {{ old('interest') == 'direct-support' ? 'selected' : '' }}>Direct Support</option>
                                    <option value="skills-sharing" {{ old('interest') == 'skills-sharing' ? 'selected' : '' }}>Skills Sharing</option>
                                    <option value="event-support" {{ old('interest') == 'event-support' ? 'selected' : '' }}>Event Support</option>
                                    <option value="creative-arts" {{ old('interest') == 'creative-arts' ? 'selected' : '' }}>Creative Arts</option>
                                    <option value="administrative" {{ old('interest') == 'administrative' ? 'selected' : '' }}>Administrative Support</option>
                                    <option value="advocacy" {{ old('interest') == 'advocacy' ? 'selected' : '' }}>Advocacy & Outreach</option>
                                    <option value="other" {{ old('interest') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('interest')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group" id="otherInterestGroup" style="{{ old('interest') == 'other' ? 'display: block' : 'display: none' }}">
                                <label for="other_interest">Please specify</label>
                                <input type="text" class="form-control @error('other_interest') is-invalid @enderror" 
                                       id="other_interest" name="other_interest" value="{{ old('other_interest') }}">
                                @error('other_interest')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="skills">Relevant Skills & Experience</label>
                                <textarea class="form-control @error('skills') is-invalid @enderror" 
                                          id="skills" name="skills" rows="3" 
                                          placeholder="Tell us about any skills, qualifications or experience that might be relevant">{{ old('skills') }}</textarea>
                                @error('skills')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Availability <span class="required">*</span></label>
                                <div class="availability-options">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input @error('availability') is-invalid @enderror" 
                                               id="availWeekday" name="availability[]" value="weekday"
                                               {{ is_array(old('availability')) && in_array('weekday', old('availability')) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="availWeekday">Weekdays (9am-5pm)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input @error('availability') is-invalid @enderror" 
                                               id="availEvening" name="availability[]" value="evening"
                                               {{ is_array(old('availability')) && in_array('evening', old('availability')) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="availEvening">Evenings (5pm-9pm)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input @error('availability') is-invalid @enderror" 
                                               id="availWeekend" name="availability[]" value="weekend"
                                               {{ is_array(old('availability')) && in_array('weekend', old('availability')) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="availWeekend">Weekends</label>
                                    </div>
                                </div>
                                @error('availability')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="commitment">Time Commitment <span class="required">*</span></label>
                                <select class="form-control @error('commitment') is-invalid @enderror" 
                                        id="commitment" name="commitment" required>
                                    <option value="">Select time commitment</option>
                                    <option value="1-3" {{ old('commitment') == '1-3' ? 'selected' : '' }}>1-3 hours per week</option>
                                    <option value="4-6" {{ old('commitment') == '4-6' ? 'selected' : '' }}>4-6 hours per week</option>
                                    <option value="7-10" {{ old('commitment') == '7-10' ? 'selected' : '' }}>7-10 hours per week</option>
                                    <option value="flexible" {{ old('commitment') == 'flexible' ? 'selected' : '' }}>Flexible/As needed</option>
                                </select>
                                @error('commitment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-navigation">
                                <button type="button" class="btn btn-secondary prev-btn" data-step="1">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-primary next-btn" data-step="3">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        {{-- Step 3: Additional Information --}}
                        <div class="form-step" id="step3">
                            <h3 class="step-title">
                                <span class="step-number">3</span> Additional Information
                            </h3>
                            
                            <div class="form-group">
                                <label for="motivation">What motivates you to volunteer with IIUM PD-CARE? <span class="required">*</span></label>
                                <textarea class="form-control @error('motivation') is-invalid @enderror" 
                                          id="motivation" name="motivation" rows="4" required 
                                          placeholder="Share your motivation for volunteering with us">{{ old('motivation') }}</textarea>
                                @error('motivation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="experience">Do you have any previous experience working with children with special needs?</label>
                                <textarea class="form-control @error('experience') is-invalid @enderror" 
                                          id="experience" name="experience" rows="3" 
                                          placeholder="If yes, please describe your experience">{{ old('experience') }}</textarea>
                                @error('experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="referral">How did you hear about our volunteer program?</label>
                                <select class="form-control @error('referral') is-invalid @enderror" 
                                        id="referral" name="referral">
                                    <option value="">Select an option</option>
                                    <option value="website" {{ old('referral') == 'website' ? 'selected' : '' }}>IIUM PD-CARE Website</option>
                                    <option value="social-media" {{ old('referral') == 'social-media' ? 'selected' : '' }}>Social Media</option>
                                    <option value="friend" {{ old('referral') == 'friend' ? 'selected' : '' }}>Friend/Family Referral</option>
                                    <option value="event" {{ old('referral') == 'event' ? 'selected' : '' }}>Community Event</option>
                                    <option value="other" {{ old('referral') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('referral')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input @error('consent') is-invalid @enderror" 
                                           id="consent" name="consent" value="1" required 
                                           {{ old('consent') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="consent">
                                        I consent to IIUM PD-CARE processing my personal information for volunteer recruitment purposes. I understand that IIUM PD-CARE will keep my information secure and will not share it with third parties. <span class="required">*</span>
                                    </label>
                                    @error('consent')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-navigation">
                                <button type="button" class="btn btn-secondary prev-btn" data-step="2">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button type="submit" class="btn btn-primary submit-btn">
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

{{-- Footer --}}
@include('layouts.footer')

{{-- Back to top button --}}
<a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="{{ asset('js/volunteer.js') }}"></script>
</body>
</html>