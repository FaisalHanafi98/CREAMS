document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializePreloader();
    initializeFormValidation();
    initializeFormEnhancements();
    initializeBackToTop();
    initializeSmoothScrolling();
    initializeAnimations();
    initializeAlerts();
    initializeMobileMenu();
    console.log('Contact page initialized successfully');
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
    setTimeout(hidePreloader, 300);
    } else {
    window.addEventListener('load', () => setTimeout(hidePreloader, 300));
    }
    // Fallback - hide after 3 seconds
    setTimeout(hidePreloader, 3000);
    }
    
    /**
    
    Enhanced Form Validation
    */
    function initializeFormValidation() {
    const form = document.getElementById('contactForm');
    if (!form) return;
    // Real-time validation for all form fields
    const formFields = form.querySelectorAll('input, select, textarea');
    formFields.forEach(field => {
    // Validate on blur (when user leaves field)
    field.addEventListener('blur', function() {
    validateField(this);
    updateFormProgress();
    });
     // Clear errors on input (when user starts typing)
     field.addEventListener('input', function() {
         if (this.classList.contains('is-invalid')) {
             validateField(this);
         }
         updateCharacterCount(this);
         updateFormProgress();
     });
    });
    // Form submission handling
    form.addEventListener('submit', function(e) {
    e.preventDefault();
     if (validateForm()) {
         submitForm();
     }
    });
    function validateField(field) {
    const fieldType = field.type || field.tagName.toLowerCase();
    let isValid = true;
    let message = '';
     // Check required fields
     if (field.hasAttribute('required')) {
         if (!field.value.trim()) {
             isValid = false;
             message = getRequiredMessage(field);
         }
     }
    
     // Field-specific validation
     if (field.value.trim() && isValid) {
         switch (fieldType) {
             case 'email':
                 if (!isValidEmail(field.value)) {
                     isValid = false;
                     message = 'Please enter a valid email address (e.g., name@example.com)';
                 }
                 break;
                 
             case 'tel':
                 if (!isValidPhone(field.value)) {
                     isValid = false;
                     message = 'Please enter a valid phone number (e.g., +60123456789)';
                 }
                 break;
                 
             case 'text':
                 if (field.name === 'name' && !isValidName(field.value)) {
                     isValid = false;
                     message = 'Please enter a valid name (letters, spaces, hyphens only)';
                 }
                 break;
                 
             case 'textarea':
                 if (field.name === 'message') {
                     if (field.value.length < 10) {
                         isValid = false;
                         message = 'Message must be at least 10 characters long';
                     } else if (field.value.length > 2000) {
                         isValid = false;
                         message = 'Message cannot exceed 2000 characters';
                     }
                 }
                 break;
         }
     }
    
     updateFieldState(field, isValid, message);
     return isValid;
    }
    function validateForm() {
    let isFormValid = true;
     formFields.forEach(field => {
         if (!validateField(field)) {
             isFormValid = false;
         }
     });
    
     return isFormValid;
    }
    function updateFieldState(field, isValid, message) {
    const fieldContainer = field.closest('.form-group') || field.parentNode;
     // Remove existing validation classes and messages
     field.classList.remove('is-valid', 'is-invalid');
     const existingFeedback = fieldContainer.querySelector('.invalid-feedback');
     if (existingFeedback) {
         existingFeedback.remove();
     }
    
     if (field.hasAttribute('required') || field.value.trim()) {
         if (isValid) {
             field.classList.add('is-valid');
         } else {
             field.classList.add('is-invalid');
             
             // Add error message
             const feedback = document.createElement('div');
             feedback.className = 'invalid-feedback';
             feedback.textContent = message;
             fieldContainer.appendChild(feedback);
         }
     }
    }
    function getRequiredMessage(field) {
    const messages = {
    'name': 'Please enter your full name',
    'email': 'Please enter your email address',
    'reason': 'Please select a reason for contacting us',
    'message': 'Please enter your message'
    };
    return messages[field.name] || 'This field is required';
    }
    }
    
    /**
    
    Form Enhancement Features
    */
    function initializeFormEnhancements() {
    const form = document.getElementById('contactForm');
    if (!form) return;
    // Character counter for message field
    const messageField = document.getElementById('message');
    if (messageField) {
    addCharacterCounter(messageField, 2000);
    }
    // Dynamic subject generation based on reason
    const reasonSelect = document.getElementById('reason');
    const subjectField = document.getElementById('subject');
    if (reasonSelect && subjectField) {
    reasonSelect.addEventListener('change', function() {
    if (!subjectField.value.trim()) {
    subjectField.value = generateSubject(this.value);
    }
    });
    }
    // Auto-format phone number
    const phoneField = document.getElementById('phone');
    if (phoneField) {
    phoneField.addEventListener('input', function() {
    this.value = formatPhoneNumber(this.value);
    });
    }
    // Form progress indicator
    createFormProgressIndicator();
    // Auto-save form data
    initializeAutoSave();
    function addCharacterCounter(field, maxLength) {
    const container = field.closest('.form-group');
    const counter = document.createElement('small');
    counter.className = 'form-text text-muted character-counter';
    counter.innerHTML = `<span class="current">0</span>/${maxLength} characters`;
    container.appendChild(counter);
     field.addEventListener('input', function() {
         updateCharacterCount(this);
     });
    }
    function generateSubject(reason) {
    const subjects = {
    'services': 'Inquiry About Rehabilitation Services',
    'support': 'Support and Assistance Request',
    'volunteer': 'Volunteer Opportunity Inquiry',
    'partnership': 'Partnership Opportunity',
    'admission': 'Admission Inquiry',
    'complaint': 'Complaint Submission',
    'feedback': 'Feedback Submission',
    'general': 'General Inquiry',
    'other': 'Contact Form Submission'
    };
    return subjects[reason] || '';
    }
    function formatPhoneNumber(phone) {
    // Remove all non-numeric characters except +
    let cleaned = phone.replace(/[^\d+]/g, '');
     // Format Malaysian numbers
     if (cleaned.startsWith('60')) {
         cleaned = '+' + cleaned;
     } else if (cleaned.startsWith('0') && cleaned.length > 1) {
         cleaned = '+60' + cleaned.substring(1);
     } else if (!cleaned.startsWith('+') && cleaned.length > 0 && !cleaned.startsWith('60')) {
         cleaned = '+60' + cleaned;
     }
     
     return cleaned;
    }
    function createFormProgressIndicator() {
    const form = document.getElementById('contactForm');
    const progressContainer = document.createElement('div');
    progressContainer.className = 'form-progress-container mb-3';
    progressContainer.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-2">
      <small class="text-muted">Form Completion</small>
      <small class="text-muted"><span id="progress-percentage">0</span>% Complete</small>
    </div>
    <div class="progress" style="height: 6px;">
      <div class="progress-bar bg-primary" id="form-progress-bar" style="width: 0%"></div>
    </div>
  `;
    const firstFormGroup = form.querySelector('.form-group');
     if (firstFormGroup) {
         firstFormGroup.parentNode.insertBefore(progressContainer, firstFormGroup);
     }
    }
    }
    
    /**
    
    Auto-save functionality
    */
    function initializeAutoSave() {
    const form = document.getElementById('contactForm');
    if (!form) return;
    const AUTO_SAVE_KEY = 'contact_form_data';
    let autoSaveTimeout;
    // Load saved data on page load
    loadSavedFormData();
    // Save form data on input
    form.addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(saveFormData, 1000);
    });
    // Clear saved data on successful submission
    form.addEventListener('submit', function() {
    clearSavedFormData();
    });
    function saveFormData() {
    const formData = new FormData(form);
    const data = {};
     for (let [key, value] of formData.entries()) {
         data[key] = value;
     }
    
     try {
         localStorage.setItem(AUTO_SAVE_KEY, JSON.stringify(data));
         showAutoSaveIndicator();
     } catch (e) {
         console.log('Could not save form data:', e);
     }
    }
    function loadSavedFormData() {
    try {
    const savedData = localStorage.getItem(AUTO_SAVE_KEY);
    if (!savedData) return;
         const data = JSON.parse(savedData);
         let hasData = false;
         
         Object.keys(data).forEach(key => {
             const field = form.querySelector(`[name="${key}"]`);
             if (field && data[key]) {
                 field.value = data[key];
                 hasData = true;
             }
         });
    
         if (hasData) {
             showRestoredDataNotification();
             updateFormProgress();
         }
     } catch (e) {
         console.log('Could not load saved form data:', e);
     }
    }
    function clearSavedFormData() {
    try {
    localStorage.removeItem(AUTO_SAVE_KEY);
    } catch (e) {
    console.log('Could not clear saved form data:', e);
    }
    }
    function showAutoSaveIndicator() {
    // Create or update auto-save indicator
    let indicator = document.getElementById('auto-save-indicator');
    if (!indicator) {
    indicator = document.createElement('small');
    indicator.id = 'auto-save-indicator';
    indicator.className = 'text-muted auto-save-indicator';
    indicator.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 3px; z-index: 1000; opacity: 0; transition: opacity 0.3s;';
    document.body.appendChild(indicator);
    }
     indicator.textContent = 'Draft saved automatically';
     indicator.style.opacity = '1';
     
     setTimeout(() => {
         indicator.style.opacity = '0';
     }, 2000);
    }
    function showRestoredDataNotification() {
    const notification = document.createElement('div');
    notification.className = 'alert alert-info alert-dismissible fade show';
    notification.innerHTML = `
    <i class="fas fa-info-circle"></i>
    We've restored your previously entered information.
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
    `;
     const form = document.getElementById('contactForm');
     form.parentNode.insertBefore(notification, form);
     
     // Auto-dismiss after 5 seconds
     setTimeout(() => {
         if (notification.parentNode) {
             notification.remove();
         }
     }, 5000);
    }
    }
    
    /**
    
    Form submission handling
    */
    function submitForm() {
    const form = document.getElementById('contactForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Message...';
    submitBtn.disabled = true;
    // Add loading class to form
    form.classList.add('form-submitting');
    // Submit after brief delay for UX
    setTimeout(() => {
    form.submit();
    }, 500);
    }
    
    /**
    
    Utility functions
    */
    function updateCharacterCount(field) {
    const counter = field.parentNode.querySelector('.character-counter .current');
    if (counter) {
    const currentLength = field.value.length;
    counter.textContent = currentLength;
     // Change color based on limit
     const maxLength = parseInt(field.getAttribute('maxlength')) || 2000;
     const percentage = (currentLength / maxLength) * 100;
     
     if (percentage > 90) {
         counter.style.color = '#dc3545'; // Red
     } else if (percentage > 75) {
         counter.style.color = '#ffc107'; // Yellow
     } else {
         counter.style.color = '#6c757d'; // Gray
     }
    }
    }
    
    function updateFormProgress() {
    const form = document.getElementById('contactForm');
    const progressBar = document.getElementById('form-progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    if (!progressBar || !progressPercentage) return;
    
    const requiredFields = form.querySelectorAll('[required]');
    const filledFields = Array.from(requiredFields).filter(field => field.value.trim() !== '');
    
    const progress = (filledFields.length / requiredFields.length) * 100;
    
    progressBar.style.width = progress + '%';
    progressPercentage.textContent = Math.round(progress);
    
    // Change color based on progress
    progressBar.className = 'progress-bar';
    if (progress === 100) {
        progressBar.classList.add('bg-success');
    } else if (progress > 50) {
        progressBar.classList.add('bg-warning');
    } else {
        progressBar.classList.add('bg-primary');
    }
    }
    function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+.[^\s@]{2,}$/;
    return emailRegex.test(email.trim());
    }
    function isValidPhone(phone) {
    const phoneRegex = /^[+]?[0-9\s-()]{8,}$/;
    return phoneRegex.test(phone.trim());
    }
    function isValidName(name) {
    const nameRegex = /^[a-zA-Z\s-.']+$/;
    return nameRegex.test(name.trim()) && name.trim().length >= 2;
    }
    /**
    
    Back to Top Button
    */
    function initializeBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    if (!backToTop) return;
    window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
    backToTop.classList.add('show');
    } else {
    backToTop.classList.remove('show');
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
    
    Smooth Scrolling
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
    
    Initialize animations if AOS is available
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
    
    Enhanced alert handling
    */
    function initializeAlerts() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
    // Add close functionality if not present
    const closeBtn = alert.querySelector('.close');
    if (closeBtn) {
    closeBtn.addEventListener('click', function() {
    alert.style.opacity = '0';
    setTimeout(() => {
    if (alert.parentNode) {
    alert.remove();
    }
    }, 300);
    });
    }
     // Auto-dismiss
     setTimeout(() => {
         if (alert && alert.parentNode) {
             alert.style.opacity = '0';
             setTimeout(() => {
                 if (alert.parentNode) {
                     alert.remove();
                 }
             }, 300);
         }
     }, 5000);
    });
    }
    
    /**
    
    Mobile menu integration
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