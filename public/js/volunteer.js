    document.addEventListener('DOMContentLoaded', function () {
    initializePreloader();
    initializeAnimations();
    initializeMultiStepForm();
    initializeCounters();
    initializeBackToTop();
    initializeSmoothScrolling();
    initializeFormValidation();
    initializeFAQ();
    initializeVideoHero();
    initializeFormAutoSave();
    initializeMobileMenu();
    console.log('Volunteer page initialized successfully');
    });

    /**
    
    Preloader Management
    */
    function initializePreloader() {
    const preloader = document.querySelector('.preloader');
    if (!preloader) return;
    const hidePreloader = () => {
    preloader.style.transition = 'opacity 0.5s ease';
    preloader.style.opacity = '0';
    setTimeout(() => {
    preloader.style.display = 'none';
    }, 500);
    };
    // Hide preloader after page load
    if (document.readyState === 'complete') {
    setTimeout(hidePreloader, 500);
    } else {
    window.addEventListener('load', () => setTimeout(hidePreloader, 500));
    }
    // Fallback - hide after 3 seconds
    setTimeout(hidePreloader, 3000);
    }
    
    /**
    
    Initialize AOS Animations
    */
    function initializeAnimations() {
    if (typeof AOS !== 'undefined') {
    AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100
    });
    }
    }
    
    /**
    
    Multi-Step Form Handler
    */
    function initializeMultiStepForm() {
    const form = document.getElementById('volunteerForm');
    if (!form) return;
    let currentStep = 1;
    const totalSteps = 3;
    // Navigation button handlers
    document.querySelectorAll('.next-btn').forEach(btn => {
    btn.addEventListener('click', function() {
    const nextStepNum = parseInt(this.dataset.step);
    if (validateCurrentStep(currentStep)) {
    goToStep(nextStepNum);
    }
    });
    });
    document.querySelectorAll('.prev-btn').forEach(btn => {
    btn.addEventListener('click', function() {
    const prevStepNum = parseInt(this.dataset.step);
    goToStep(prevStepNum);
    });
    });
    function goToStep(stepNumber) {
    // Hide current step
    document.getElementById(`step${currentStep}`).classList.remove('active');
     // Show target step
     document.getElementById(`step${stepNumber}`).classList.add('active');
     
     currentStep = stepNumber;
     
     // Update progress indicator if exists
     updateProgressIndicator();
     
     // Scroll to top of form
     document.getElementById('volunteer-form').scrollIntoView({
         behavior: 'smooth',
         block: 'start'
     });
    }
    function validateCurrentStep(step) {
    const currentStepElement = document.getElementById(`step${step}`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
     requiredFields.forEach(field => {
         if (!validateField(field)) {
             isValid = false;
         }
     });
    
     return isValid;
    }
    function validateField(field) {
    const value = field.type === 'checkbox' ? field.checked : field.value.trim();
    const isValid = field.type === 'checkbox' ? field.checked : value !== '';
     // Special validation for availability checkboxes
     if (field.name === 'availability[]') {
         const checkedBoxes = document.querySelectorAll('input[name="availability[]"]:checked');
         const availabilityValid = checkedBoxes.length > 0;
         
         document.querySelectorAll('input[name="availability[]"]').forEach(checkbox => {
             toggleFieldError(checkbox, availabilityValid, 'Please select at least one availability option');
         });
         
         return availabilityValid;
     }
    
     toggleFieldError(field, isValid, 'This field is required');
     return isValid;
    }
    function toggleFieldError(field, isValid, message) {
    if (isValid) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) errorDiv.remove();
    } else {
    field.classList.add('is-invalid');
    if (!field.parentNode.querySelector('.invalid-feedback')) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
    }
    }
    }
    function updateProgressIndicator() {
    const progress = (currentStep / totalSteps) * 100;
    const progressBar = document.querySelector('.form-progress-bar');
    if (progressBar) {
        progressBar.style.width = `${progress}%`;
    }
    }
    // Show/hide "Other" interest field
    const interestSelect = document.getElementById('interest');
    const otherInterestGroup = document.getElementById('otherInterestGroup');
    if (interestSelect && otherInterestGroup) {
    interestSelect.addEventListener('change', function() {
    if (this.value === 'other') {
    otherInterestGroup.style.display = 'block';
    document.getElementById('otherInterest').setAttribute('required', 'required');
    } else {
    otherInterestGroup.style.display = 'none';
    document.getElementById('otherInterest').removeAttribute('required');
    document.getElementById('otherInterest').value = '';
    }
    });
    }
    // Form submission handler
    form.addEventListener('submit', function(e) {
    e.preventDefault();
     if (!validateCurrentStep(currentStep)) {
         return false;
     }
    
     // Show loading state
     const submitBtn = this.querySelector('.submit-btn');
     const originalText = submitBtn.innerHTML;
     submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Application...';
     submitBtn.disabled = true;
    
     // Submit form after brief delay for UX
     setTimeout(() => {
         this.submit();
     }, 1000);
    });
    }
    
    /**
    
    Counter Animation for Statistics
    */
    function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    if (counters.length === 0) return;
    const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px 0px -100px 0px'
    };
    const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
    if (entry.isIntersecting) {
    animateCounter(entry.target);
    observer.unobserve(entry.target);
    }
    });
    }, observerOptions);
    counters.forEach(counter => observer.observe(counter));
    function animateCounter(element) {
    const target = parseInt(element.dataset.count);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
     const timer = setInterval(() => {
         current += step;
         element.textContent = Math.floor(current);
    
         if (current >= target) {
             element.textContent = target;
             clearInterval(timer);
         }
     }, 16);
    }
    }
    
    /**
    
    Back to Top Button
    */
    function initializeBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    if (!backToTop) return;
    window.addEventListener('scroll', () => {
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
    
    /**
    
    Smooth Scrolling for Anchor Links
    */
    function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
    e.preventDefault();
         const target = document.querySelector(this.getAttribute('href'));
         if (target) {
             const headerOffset = 80;
             const elementPosition = target.getBoundingClientRect().top;
             const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
             
             window.scrollTo({
                 top: offsetPosition,
                 behavior: 'smooth'
             });
         }
     });
    });
    }
    
    /**
    
    Enhanced Form Validation
    */
    function initializeFormValidation() {
    const form = document.getElementById('volunteerForm');
    if (!form) return;
    // Real-time validation
    form.querySelectorAll('input, select, textarea').forEach(field => {
    field.addEventListener('blur', function() {
    validateField(this);
    });
     field.addEventListener('input', function() {
         if (this.classList.contains('is-invalid')) {
             validateField(this);
         }
     });
    });
    function validateField(field) {
    let isValid = true;
    let message = '';
     // Check if field is required and empty
     if (field.hasAttribute('required') && !field.value.trim()) {
         isValid = false;
         message = 'This field is required';
     }
    
     // Email validation
     if (field.type === 'email' && field.value.trim()) {
         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
         if (!emailRegex.test(field.value)) {
             isValid = false;
             message = 'Please enter a valid email address';
         }
     }
    
     // Phone validation
     if (field.type === 'tel' && field.value.trim()) {
         const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
         if (!phoneRegex.test(field.value)) {
             isValid = false;
             message = 'Please enter a valid phone number';
         }
     }
    
     // Update field state
     if (isValid) {
         field.classList.remove('is-invalid');
         field.classList.add('is-valid');
         const errorDiv = field.parentNode.querySelector('.invalid-feedback');
         if (errorDiv) errorDiv.remove();
     } else {
         field.classList.remove('is-valid');
         field.classList.add('is-invalid');
         
         let errorDiv = field.parentNode.querySelector('.invalid-feedback');
         if (!errorDiv) {
             errorDiv = document.createElement('div');
             errorDiv.className = 'invalid-feedback';
             field.parentNode.appendChild(errorDiv);
         }
         errorDiv.textContent = message;
     }
    
     return isValid;
    }
    }
    
    /**
    
    FAQ Accordion Enhancement
    */
    function initializeFAQ() {
    const faqItems = document.querySelectorAll('.accordion .btn-link');
    faqItems.forEach(item => {
    item.addEventListener('click', function() {
    // Toggle icon rotation
    const icon = this.querySelector('i');
    if (icon) {
    setTimeout(() => {
    const isExpanded = this.getAttribute('aria-expanded') === 'true';
    icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(-90deg)';
    }, 100);
    }
    });
    });
    }
    
    /**
    
    Video Hero Section Handler
    */
    function initializeVideoHero() {
    const video = document.getElementById('volunteerVideo');
    if (!video) return;
    // Ensure video plays on mobile devices
    video.addEventListener('loadedmetadata', function() {
    this.currentTime = 0;
    });
    // Pause video when not visible (performance optimization)
    const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
    if (entry.isIntersecting) {
    video.play().catch(console.log);
    } else {
    video.pause();
    }
    });
    });
    observer.observe(video);
    }
    
    /**
    
    Form Auto-save (Optional Enhancement)
    */
    function initializeFormAutoSave() {
    const form = document.getElementById('volunteerForm');
    if (!form) return;
    const AUTO_SAVE_KEY = 'volunteer_form_data';
    let autoSaveTimeout;
    // Load saved data on page load
    loadFormData();
    // Save form data on input
    form.addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(saveFormData, 1000);
    });
    function saveFormData() {
    const formData = new FormData(form);
    const data = {};
     for (let [key, value] of formData.entries()) {
         if (data[key]) {
             if (Array.isArray(data[key])) {
                 data[key].push(value);
             } else {
                 data[key] = [data[key], value];
             }
         } else {
             data[key] = value;
         }
     }
    
     try {
         localStorage.setItem(AUTO_SAVE_KEY, JSON.stringify(data));
     } catch (e) {
         console.log('Could not save form data:', e);
     }
    }
    function loadFormData() {
    try {
    const savedData = localStorage.getItem(AUTO_SAVE_KEY);
    if (!savedData) return;
         const data = JSON.parse(savedData);
         
         Object.keys(data).forEach(key => {
             const field = form.querySelector(`[name="${key}"]`);
             if (field) {
                 if (field.type === 'checkbox' || field.type === 'radio') {
                     const values = Array.isArray(data[key]) ? data[key] : [data[key]];
                     values.forEach(value => {
                         const specificField = form.querySelector(`[name="${key}"][value="${value}"]`);
                         if (specificField) specificField.checked = true;
                     });
                 } else {
                     field.value = data[key];
                 }
             }
         });
     } catch (e) {
         console.log('Could not load form data:', e);
     }
    }
    // Clear saved data on successful submission
    form.addEventListener('submit', function() {
    try {
    localStorage.removeItem(AUTO_SAVE_KEY);
    } catch (e) {
    console.log('Could not clear saved data:', e);
    }
    });
    }
    
    /**
    
    Mobile Menu Integration (if header is present)
    */
    function initializeMobileMenu() {
    const mobileToggle = document.getElementById('mobile-nav-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    if (mobileToggle && mobileNav) {
    mobileToggle.addEventListener('click', function() {
    mobileNav.classList.toggle('mobile-nav-active');
    this.classList.toggle('active');
    });
     // Close mobile menu when clicking outside
     document.addEventListener('click', function(e) {
         if (!mobileNav.contains(e.target) && !mobileToggle.contains(e.target)) {
             mobileNav.classList.remove('mobile-nav-active');
             mobileToggle.classList.remove('active');
         }
     });
    }
    }
