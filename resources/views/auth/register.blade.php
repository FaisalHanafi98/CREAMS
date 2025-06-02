<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CREAMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/registrationstyle.css') }}"> 
    
</head>
<body>
    <body style="background-image: url('{{ asset('images/loginpagebackground.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="logo-container">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="CREAMS Logo" onerror="this.style.display='none'">
            CREAMS
        </a>
    </div>
    <div class="content-container">
        <div class="page-title">
            <h1>Create Your Account</h1>
            <p>Join CREAMS to begin your journey with our community-based rehabilitation services</p>
        </div>
        
        <div class="register-container">
            <div class="registration-form-header">
                <h2>New Registration</h2>
                <p>Please fill in the information to create your account</p>
            </div>
            
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            
            @if (session('fail'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('fail') }}
                </div>
            @endif
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="tab-nav">
                <button class="tab-btn active" id="tab-1">Account Details</button>
                <button class="tab-btn" id="tab-2">Profile Information</button>
                <button class="tab-btn" id="tab-3">Review & Submit</button>
            </div>
            
            <form action="{{ route('auth.save') }}" method="POST" id="registration-form" onsubmit="console.log('Form submitting with center_id:', document.getElementById('center_id').value); return true;">
                @csrf
                
                <div class="form-sections-container">
                    <!-- Section 1: Account Details -->
                    <div class="form-section active" id="section-1">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="email">Email Address*</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="iium_id">IIUM ID*</label>
                                <input type="text" class="form-control @error('iium_id') is-invalid @enderror" id="iium_id" name="iium_id" value="{{ old('iium_id') }}" required maxlength="8">
                                <div class="form-help">Format: 4 letters followed by 4 digits (e.g., ABCD1234)</div>
                                @error('iium_id')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group password-field">
                                <label for="password">Password*</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                                <div class="form-help">Must be at least 5 characters with at least one letter and one number</div>
                                @error('password')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group password-field">
                                <label for="password_confirmation">Confirm Password*</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required>
                                <button type="button" class="toggle-password" id="togglePasswordConfirmation">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <div></div> <!-- Empty div for spacing -->
                            <button type="button" class="btn-next" id="to-section-2">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section 2: Profile Information -->
                    <div class="form-section" id="section-2">
                        <div class="form-grid">
                            <div class="form-group form-grid-full">
                                <label for="name">Full Name*</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group form-grid-full">
                                <label>Select Your Role*</label>
                                <div class="role-options">
                                    <label class="role-option {{ old('role') === 'admin' ? 'selected' : '' }}" id="role-admin">
                                        <input type="radio" name="role" value="admin" class="role-radio" {{ old('role') === 'admin' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-user-cog"></i>
                                        </div>
                                        <div class="role-name">Admin</div>
                                    </label>
                                    
                                    <label class="role-option {{ old('role') === 'supervisor' ? 'selected' : '' }}" id="role-supervisor">
                                        <input type="radio" name="role" value="supervisor" class="role-radio" {{ old('role') === 'supervisor' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="role-name">Supervisor</div>
                                    </label>
                                    
                                    <label class="role-option {{ old('role') === 'teacher' ? 'selected' : '' }}" id="role-teacher">
                                        <input type="radio" name="role" value="teacher" class="role-radio" {{ old('role') === 'teacher' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div class="role-name">Teacher</div>
                                    </label>
                                    
                                    <label class="role-option {{ old('role') === 'ajk' ? 'selected' : '' }}" id="role-ajk">
                                        <input type="radio" name="role" value="ajk" class="role-radio" {{ old('role') === 'ajk' ? 'checked' : '' }}>
                                        <div class="role-icon">
                                            <i class="fas fa-users-cog"></i>
                                        </div>
                                        <div class="role-name">AJK</div>
                                    </label>
                                </div>
                                @error('role')
                                    <div class="form-error mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- center Location dropdown -->
                        <div class="form-group form-grid-full">
                            <label for="center_location">Centre Location*</label>
                            <select class="form-control @error('center_location') is-invalid @enderror" id="center_location" name="center_location" required>
                                <option value="">-- Select a Centre --</option>
                                <option value="Gombak" {{ old('center_location') === 'Gombak' ? 'selected' : '' }}>Gombak</option>
                                <option value="Kuantan" {{ old('center_location') === 'Kuantan' ? 'selected' : '' }}>Kuantan</option>
                                <option value="Pagoh" {{ old('center_location') === 'Pagoh' ? 'selected' : '' }}>Pagoh</option>
                            </select>
                            <div class="form-help">Select the campus centre location</div>
                            @error('center_location')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Hidden Center ID field that gets populated based on selection -->
                        <input type="hidden" id="center_id" name="center_id" value="{{ old('center_id') }}">
                        @error('center_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        
                        <div class="form-buttons">
                            <button type="button" class="btn-prev" id="to-section-1-from-2">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn-next" id="to-section-3">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section 3: Review & Submit -->
                    <div class="form-section" id="section-3">
                        <h3 class="mb-4">Review Your Information</h3>
                        
                        <div class="review-section">
                            <div class="review-item">
                                <div class="review-label">Email Address:</div>
                                <div class="review-value" id="review-email"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">IIUM ID:</div>
                                <div class="review-value" id="review-iium-id"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">Full Name:</div>
                                <div class="review-value" id="review-name"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">Selected Role:</div>
                                <div class="review-value" id="review-role"></div>
                            </div>
                            <div class="review-item">
                                <div class="review-label">Centre:</div>
                                <div class="review-value" id="review-center">Not selected</div>
                            </div>
                        </div>
                        
                        <div class="terms-container">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>
                                </label>
                                @error('terms')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="button" class="btn-prev" id="to-section-2-from-3">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="submit" class="btn-submit" id="submit-button">
                                <i class="fas fa-user-plus"></i> Complete Registration
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="{{ route('auth.loginpage') }}">Login here</a>
            </div>
        </div>
        
        <div class="text-center">
            <a href="{{ url('/') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home Page
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h4>About CREAMS</h4>
                <ul>
                    <li><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="{{ url('/#about') }}"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="{{ url('/#support') }}"><i class="fas fa-hands-helping"></i> Our Services</a></li>
                    <li><a href="{{ url('/#team') }}"><i class="fas fa-users"></i> Our Team</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="{{ route('volunteer') }}"><i class="fas fa-user-plus"></i> Volunteer</a></li>
                    <li><a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact Us</a></li>
                    <li><a href="{{ route('auth.loginpage') }}"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="{{ route('auth.registerpage') }}"><i class="fas fa-user-plus"></i> Register</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Legal</h4>
                <ul>
                    <li><a href="{{ route('trademark') }}"><i class="fas fa-trademark"></i> Trademarks</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    <li><a href="#"><i class="fas fa-gavel"></i> Terms of Service</a></li>
                    <li><a href="#"><i class="fas fa-universal-access"></i>
                        Accessibility</a></li>
                </ul>
            </div>
            
            <div class="footer-column footer-info">
                <h4>Contact Us</h4>
                <p><i class="fas fa-map-marker-alt"></i> Disability Services Unit (DSU), IIUM, 53100 Kuala Lumpur</p>
                <p><i class="fas fa-phone-alt"></i> (+60) 3642 1633 5</p>
                <p><i class="fas fa-envelope"></i> dsu-creams@iium.edu.my</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} CREAMS - Community-based REhAbilitation Management System. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== Center ID mapping setup =====
        // Map locations to IDs
        const locationToCenterId = {
            'Gombak': '1',
            'Kuantan': '2',
            'Pagoh': '3'
        };
        
        const centerLocationSelect = document.getElementById('center_location');
        const centerIdInput = document.getElementById('center_id');
        
        if (centerLocationSelect && centerIdInput) {
            // Set initial value if a location is already selected
            if (centerLocationSelect.value && locationToCenterId[centerLocationSelect.value]) {
                centerIdInput.value = locationToCenterId[centerLocationSelect.value];
                console.log('Initial Center ID set to:', centerIdInput.value, 'for location:', centerLocationSelect.value);
            }
            
            // Update center_id when center_location changes
            centerLocationSelect.addEventListener('change', function() {
                if (this.value && locationToCenterId[this.value]) {
                    centerIdInput.value = locationToCenterId[this.value];
                    console.log('Center ID updated to:', centerIdInput.value, 'for location:', this.value);
                } else {
                    centerIdInput.value = ''; // Clear the value if no location is selected
                    console.log('Center ID cleared because no location was selected');
                }
            });
        }
    
        // ===== Animation Configuration =====
        const animationConfig = {
            duration: 300,            // Base duration for animations (ms)
            easing: 'ease-out',       // Animation easing function
            tabTransitionDelay: 50,   // Delay between tab and content animation (ms)
            formControlDelay: 30,     // Staggered delay between form controls (ms)
            roleOptionsStagger: 50,   // Staggered delay for role options (ms)
            reviewItemStagger: 80,    // Staggered delay for review items (ms)
            validationScale: 0.98,    // Scale factor for validation error animation
            submitAnimationTime: 1500 // Total time for submit animation (ms)
        };

        // ===== Helper Functions =====
        
        /**
         * Animate entrance of form elements with staggered timing
         * @param {HTMLElement} container - The container of elements to animate
         * @param {String} selector - CSS selector for elements to animate
         * @param {Number} staggerDelay - Delay between each element's animation
         * @param {Boolean} clear - Whether to clear existing animations first
         */
        function animateElements(container, selector, staggerDelay = 30, clear = true) {
            const elements = container.querySelectorAll(selector);
            
            if (clear) {
                elements.forEach(el => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(20px)';
                });
            }
            
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.transition = `opacity ${animationConfig.duration}ms ${animationConfig.easing}, transform ${animationConfig.duration}ms ${animationConfig.easing}`;
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * staggerDelay);
            });
        }
        
        /**
         * Add shake animation for validation errors
         * @param {HTMLElement} element - The element to animate
         */
        function shakeElement(element) {
            element.classList.add('validation-shake');
            setTimeout(() => element.classList.remove('validation-shake'), 600);
        }
        
        /**
         * Validate field and show visual feedback
         * @param {HTMLElement} field - The field to validate
         * @param {Function} validationFn - Validation function returning boolean
         * @returns {Boolean} - Whether validation passed
         */
        function validateField(field, validationFn) {
            const isValid = validationFn(field);
            
            if (!isValid) {
                field.classList.add('is-invalid');
                shakeElement(field);
                
                // Add pulse animation to the label
                const label = field.previousElementSibling;
                if (label && label.tagName === 'LABEL') {
                    label.classList.add('validation-pulse');
                    setTimeout(() => label.classList.remove('validation-pulse'), 1000);
                }
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                
                // Add success checkmark animation
                const parent = field.parentElement;
                if (!parent.querySelector('.valid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'valid-feedback animated fadeIn';
                    feedback.innerHTML = '<i class="fas fa-check-circle"></i> Looks good!';
                    parent.appendChild(feedback);
                    
                    // Remove after 2 seconds
                    setTimeout(() => {
                        if (feedback.parentNode) {
                            feedback.classList.add('fadeOut');
                            setTimeout(() => {
                                if (feedback.parentNode) feedback.parentNode.removeChild(feedback);
                            }, 300);
                        }
                    }, 2000);
                }
            }
            
            return isValid;
        }
        
        /**
         * Smooth scroll to an element
         * @param {HTMLElement} element - Element to scroll to
         */
        function smoothScrollTo(element) {
            const headerOffset = 80;
            const elementPosition = element.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }

        // ===== Tab Navigation =====
        const tabs = document.querySelectorAll('.tab-btn');
        const sections = document.querySelectorAll('.form-section');
        
        function navigateToTab(tabIndex) {
            // First, prepare the current section for exit
            const currentActiveSection = document.querySelector('.form-section.active');
            if (currentActiveSection) {
                currentActiveSection.classList.add('section-exit');
                
                // Don't immediately remove active class to allow for animation
                setTimeout(() => {
                    currentActiveSection.classList.remove('active');
                    currentActiveSection.classList.remove('section-exit');
                }, animationConfig.duration);
            }
            
            // Update tab buttons
            tabs.forEach((tab, index) => {
                if (index === tabIndex) {
                    tab.classList.add('active');
                    // Add pulse animation to active tab
                    tab.classList.add('tab-pulse');
                    setTimeout(() => tab.classList.remove('tab-pulse'), 600);
                } else {
                    tab.classList.remove('active');
                }
            });
            
            // Delay showing the new section slightly for a smoother transition
            setTimeout(() => {
                sections.forEach((section, index) => {
                    if (index === tabIndex) {
                        section.classList.add('active', 'section-enter');
                        
                        // Animate form controls within the section
                        animateElements(section, '.form-group, .form-buttons', animationConfig.formControlDelay);
                        
                        // Special animations for specific sections
                        if (index === 1) { // Section with role options
                            animateElements(section, '.role-option', animationConfig.roleOptionsStagger);
                        } else if (index === 2) { // Review section
                            animateElements(section, '.review-item', animationConfig.reviewItemStagger);
                        }
                        
                        // Remove entrance animation class after animation completes
                        setTimeout(() => section.classList.remove('section-enter'), animationConfig.duration);
                        
                        // Scroll to top of section
                        smoothScrollTo(section);
                    }
                });
            }, animationConfig.tabTransitionDelay);
        }
        
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', function() {
                navigateToTab(index);
            });
        });
        
        // ===== Next/Previous Navigation =====
        
        // Section 1 to Section 2
        document.getElementById('to-section-2').addEventListener('click', function() {
            // Validate first section
            const email = document.getElementById('email');
            const iiumId = document.getElementById('iium_id');
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            
            let isValid = true;
            
            // Email validation
            isValid = validateField(email, field => {
                return field.value && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
            }) && isValid;
            
            // IIUM ID validation
            isValid = validateField(iiumId, field => {
                return field.value && /^[A-Z]{4}\d{4}$/.test(field.value);
            }) && isValid;
            
            // Password validation
            isValid = validateField(password, field => {
                return field.value && /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{5,}$/.test(field.value);
            }) && isValid;
            
            // Password confirmation validation
            isValid = validateField(passwordConfirmation, field => {
                return field.value && field.value === password.value;
            }) && isValid;
            
            if (!isValid) {
                // Shake the button to indicate validation error
                shakeElement(this);
                return;
            }
            
            // Add success animation before navigating
            this.innerHTML = '<i class="fas fa-check"></i> Validated!';
            this.classList.add('btn-success-pulse');
            
            setTimeout(() => {
                this.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
                this.classList.remove('btn-success-pulse');
                navigateToTab(1);
            }, 800);
        });
    
        // Validate center selection
        function validateCenterSelection() {
            const centerLocationSelect = document.getElementById('center_location');
            const centerIdInput = document.getElementById('center_id');
            
            if (!centerLocationSelect) return true;
            
            const isValid = centerLocationSelect.value !== '';
            
            if (!isValid) {
                centerLocationSelect.classList.add('is-invalid');
                shakeElement(centerLocationSelect);
                
                // Add a visual indicator that this field is required
                const centerFormGroup = centerLocationSelect.closest('.form-group');
                centerFormGroup.classList.add('highlight-error');
                
                setTimeout(() => {
                    centerFormGroup.classList.remove('highlight-error');
                }, 3000);
            } else {
                centerLocationSelect.classList.remove('is-invalid');
                centerLocationSelect.classList.add('is-valid');
                
                // Make sure center_id is set based on the location
                if (centerIdInput && locationToCenterId[centerLocationSelect.value]) {
                    centerIdInput.value = locationToCenterId[centerLocationSelect.value];
                }
            }
            
            return isValid;
        }

        // Section 2 to Section 3
        document.getElementById('to-section-3').addEventListener('click', function() {
            // Validate second section
            const name = document.getElementById('name');
            const roleSelected = document.querySelector('input[name="role"]:checked');
            const centerValid = validateCenterSelection();
            
            let isValid = true;
            
            // Name validation
            isValid = validateField(name, field => {
                return field.value && field.value.trim().length > 0;
            }) && isValid;
            
            // Role validation
            if (!roleSelected) {
                document.querySelector('.role-options').classList.add('is-invalid');
                shakeElement(document.querySelector('.role-options'));
                isValid = false;
            } else {
                document.querySelector('.role-options').classList.remove('is-invalid');
            }
            
            // Center validation - use the result from validateCenterSelection
            isValid = centerValid && isValid;
            
            if (!isValid) {
                // Shake the button to indicate validation error
                shakeElement(this);
                return;
            }
            
            // Add success animation before navigating
            this.innerHTML = '<i class="fas fa-check"></i> Validated!';
            this.classList.add('btn-success-pulse');
            
            setTimeout(() => {
                // Update review information while button is animating
                updateReviewInformation();
                
                this.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
                this.classList.remove('btn-success-pulse');
                navigateToTab(2);
            }, 800);
        });
        
        // Section 3 to Section 2 (Previous)
        document.getElementById('to-section-2-from-3').addEventListener('click', function() {
            navigateToTab(1);
        });
        
        // Section 2 to Section 1 (Previous)
        document.getElementById('to-section-1-from-2').addEventListener('click', function() {
            navigateToTab(0);
        });
        
        // ===== Update Review Information =====
        function updateReviewInformation() {
            // Add a subtle reveal animation to each review value as it's updated
            const reviewValues = document.querySelectorAll('.review-value');
            reviewValues.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
            });
            
            // Update values with a slight delay for a sequenced reveal
            setTimeout(() => {
                // Basic info
                document.getElementById('review-email').textContent = document.getElementById('email').value;
                document.getElementById('review-iium-id').textContent = document.getElementById('iium_id').value;
                document.getElementById('review-name').textContent = document.getElementById('name').value;
                
                // Selected role
                const selectedRole = document.querySelector('input[name="role"]:checked');
                if (selectedRole) {
                    let roleName = selectedRole.value;
                    roleName = roleName.charAt(0).toUpperCase() + roleName.slice(1);
                    document.getElementById('review-role').textContent = roleName;
                }
                
                // center location and ID
                const centerLocationSelect = document.getElementById('center_location');
                const centerIdInput = document.getElementById('center_id');
                if (centerLocationSelect && centerLocationSelect.value) {
                    let centerText = centerLocationSelect.value;
                    if (centerIdInput && centerIdInput.value) {
                        centerText += ' (ID: ' + centerIdInput.value + ')';
                    }
                    document.getElementById('review-center').textContent = centerText;
                } else {
                    document.getElementById('review-center').textContent = 'Not selected';
                }
                
                // Animate each review value back in with staggered timing
                reviewValues.forEach((el, index) => {
                    setTimeout(() => {
                        el.style.transition = `opacity 400ms ease-out, transform 400ms ease-out`;
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 200);
        }
        
        // ===== IIUM ID Formatting =====
        const iiumIdInput = document.getElementById('iium_id');
        if (iiumIdInput) {
            iiumIdInput.addEventListener('input', function() {
                // Convert to uppercase
                this.value = this.value.toUpperCase();
                
                // Limit to 8 characters
                if (this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                }
                
                // Add real-time formatting guidance
                const validFormat = /^[A-Z]{4}\d{4}$/.test(this.value);
                const partialFormat = /^[A-Z]{0,4}\d{0,4}$/.test(this.value);
                
                if (this.value.length > 0) {
                    if (validFormat) {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                        
                        // Add a subtle success animation
                        this.classList.add('valid-pulse');
                        setTimeout(() => this.classList.remove('valid-pulse'), 500);
                    } else if (!partialFormat) {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.remove('is-invalid');
                    }
                } else {
                    this.classList.remove('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
        }
        
        // ===== Role Selection Animation =====
        const roleOptions = document.querySelectorAll('.role-option');
        if (roleOptions.length > 0) {
            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // First, remove selected class from all options with animation
                    roleOptions.forEach(opt => {
                        if (opt.classList.contains('selected')) {
                            opt.classList.add('deselect-animation');
                            setTimeout(() => {
                                opt.classList.remove('selected');
                                opt.classList.remove('deselect-animation');
                            }, 300);
                        }
                    });
                    
                    // Add selected class to clicked option with animation
                    setTimeout(() => {
                        this.classList.add('select-animation');
                        this.classList.add('selected');
                        
                        setTimeout(() => {
                            this.classList.remove('select-animation');
                        }, 400);
                        
                        // Check the radio button
                        const radioButton = this.querySelector('input[type="radio"]');
                        if (radioButton) {
                            radioButton.checked = true;
                        }
                    }, 150);
                });
            });
            
            // Check if any role is already selected (on page load)
            const checkedRole = document.querySelector('input[name="role"]:checked');
            if (checkedRole) {
                const parentOption = checkedRole.closest('.role-option');
                if (parentOption) {
                    parentOption.classList.add('selected');
                }
            }
        }
        
        // ===== Password Visibility Toggle =====
        const setupPasswordToggle = (toggleId, inputId) => {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            if (!toggle || !input) return;
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // Animate the icon change
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.add('flip-animation');
                    
                    setTimeout(() => {
                        icon.classList.toggle('fa-eye-slash');
                        icon.classList.toggle('fa-eye');
                        icon.classList.remove('flip-animation');
                    }, 150);
                }
                
                // Add tooltip feedback
                const feedbackText = type === 'text' ? 'Password visible' : 'Password hidden';
                const tooltip = document.createElement('div');
                tooltip.className = 'password-toggle-tooltip';
                tooltip.textContent = feedbackText;
                
                this.appendChild(tooltip);
                
                setTimeout(() => {
                    tooltip.classList.add('show-tooltip');
                }, 10);
                
                setTimeout(() => {
                    tooltip.classList.remove('show-tooltip');
                    setTimeout(() => tooltip.remove(), 300);
                }, 1500);
            });
        };
        
        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation');
        
        // ===== Real-time Password Strength Indicator =====
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            // Create strength indicator if it doesn't exist
            if (!document.getElementById('password-strength')) {
                const strengthContainer = document.createElement('div');
                strengthContainer.className = 'password-strength-container';
                strengthContainer.innerHTML = `
                    <div class="password-strength-bar">
                        <div id="password-strength" class="strength-meter"></div>
                    </div>
                    <div id="password-strength-text" class="strength-text"></div>
                `;
                
                passwordInput.parentNode.insertBefore(strengthContainer, 
                    document.querySelector('.form-help'));
            }
            
            const strengthBar = document.getElementById('password-strength');
            const strengthText = document.getElementById('password-strength-text');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = '';
                
                if (password.length >= 5) strength += 20;
                if (password.length >= 8) strength += 10;
                if (/[a-z]/.test(password)) strength += 10;
                if (/[A-Z]/.test(password)) strength += 15;
                if (/\d/.test(password)) strength += 20;
                if (/[@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 25;
                
                // Set the width of the strength meter
                strengthBar.style.width = strength + '%';
                
                // Change color based on strength
                if (strength < 30) {
                    strengthBar.className = 'strength-meter weak';
                    feedback = 'Weak password';
                } else if (strength < 60) {
                    strengthBar.className = 'strength-meter medium';
                    feedback = 'Medium strength';
                } else {
                    strengthBar.className = 'strength-meter strong';
                    feedback = 'Strong password';
                }
                
                strengthText.textContent = feedback;
                
                // Animate the strength change
                strengthBar.classList.add('strength-updated');
                setTimeout(() => strengthBar.classList.remove('strength-updated'), 500);
            });
        }
        
        // ===== Terms Checkbox Animation =====
        const termsCheckbox = document.getElementById('terms');
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', function() {
                const label = this.closest('label');
                if (this.checked) {
                    label.classList.add('terms-checked');
                } else {
                    label.classList.remove('terms-checked');
                }
            });
        }
        
        // ===== Form Submission Animation =====
        const form = document.getElementById('registration-form');
        const submitButton = document.getElementById('submit-button');
        
        if (form && submitButton) {
            form.addEventListener('submit', function(e) {
                // Check if terms are accepted
                if (termsCheckbox && !termsCheckbox.checked) {
                    e.preventDefault();
                    // Shake the terms checkbox and add highlighting
                    const termsContainer = document.querySelector('.terms-container');
                    shakeElement(termsContainer);
                    termsContainer.classList.add('terms-highlight');
                    
                    setTimeout(() => {
                        termsContainer.classList.remove('terms-highlight');
                    }, 2000);
                    
                    return;
                }
                
                // Log form data before submission
                console.log('Submitting form with data:', {
                    email: document.getElementById('email').value,
                    iium_id: document.getElementById('iium_id').value,
                    name: document.getElementById('name').value,
                    role: document.querySelector('input[name="role"]:checked')?.value || 'none',
                    center_location: document.getElementById('center_location').value,
                    center_id: document.getElementById('center_id').value
                });
                
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Creating Your Account...';
                submitButton.classList.add('btn-submitting');
                
                // Add a progress animation to the button
                const progressOverlay = document.createElement('div');
                progressOverlay.className = 'submit-progress';
                submitButton.appendChild(progressOverlay);
                
                // Animate the progress overlay
                setTimeout(() => {
                    progressOverlay.style.width = '100%';
                }, 10);
                
                // Add success animation to the form
                setTimeout(() => {
                    const successOverlay = document.createElement('div');
                    successOverlay.className = 'form-section-overlay';
                    successOverlay.innerHTML = `
                        <div class="success-animation">
                            <div class="checkmark-circle">
                                <div class="background"></div>
                                <div class="checkmark"></div>
                            </div>
                            <h3>Account Being Created!</h3>
                            <p>Please wait while we process your registration...</p>
                        </div>
                    `;
                    
                    const formSection = document.querySelector('.form-section.active');
                    formSection.appendChild(successOverlay);
                    
                    setTimeout(() => {
                        successOverlay.classList.add('visible');
                    }, 50);
                }, animationConfig.submitAnimationTime / 2);
            });
        }
        
        // ===== Initial Animations =====
        // Add entrance animation for the register container
        const registerContainer = document.querySelector('.register-container');
        if (registerContainer) {
            registerContainer.classList.add('container-entrance');
        }
        
        // Animate the first section's form elements with a delay
        setTimeout(() => {
            const activeSection = document.querySelector('.form-section.active');
            if (activeSection) {
                animateElements(activeSection, '.form-group, .form-buttons', animationConfig.formControlDelay);
            }
        }, 500);
        
        // ===== Enhancement: Auto-hide alerts after 5 seconds =====
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.classList.add('alert-fade-out');
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            });
        }, 5000);
    });
</script>