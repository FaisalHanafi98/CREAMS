document.addEventListener('DOMContentLoaded', function () {
    initializePreloader();
    initializeAnimations();
    initializeMultiStepForm();
    initializeCounters();
    initializeBackToTop();
    initializeSmoothScrolling();
    initializeFormValidation();
    initializeVideoHero();
    initializeFormAutoSave();
    console.log('Volunteer page initialized successfully');
});

/** Preloader Management */
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

    if (document.readyState === 'complete') {
        setTimeout(hidePreloader, 500);
    } else {
        window.addEventListener('load', () => setTimeout(hidePreloader, 500));
    }
}

/** Initialize AOS Animations */
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

/** Multi-Step Form Handler */
function initializeMultiStepForm() {
    const form = document.getElementById('volunteerForm');
    if (!form) {
        console.error('Volunteer form not found!');
        return;
    }

    let currentStep = 1;
    const totalSteps = 3;

    document.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const nextStepNum = parseInt(this.dataset.step);
            if (validateCurrentStep(currentStep)) {
                goToStep(nextStepNum);
            }
        });
    });

    document.querySelectorAll('.prev-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const prevStepNum = parseInt(this.dataset.step);
            goToStep(prevStepNum);
        });
    });

    function goToStep(stepNumber) {
        const currentStepElement = document.getElementById(`step${currentStep}`);
        if (currentStepElement) {
            currentStepElement.classList.remove('active');
        }

        const targetStepElement = document.getElementById(`step${stepNumber}`);
        if (targetStepElement) {
            targetStepElement.classList.add('active');
        }

        currentStep = stepNumber;
        updateProgressIndicator();

        const formSection = document.getElementById('volunteer-form');
        if (formSection) {
            formSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function validateCurrentStep(step) {
        const currentStepElement = document.getElementById(`step${step}`);
        if (!currentStepElement) return true;

        const requiredFields = currentStepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        if (step === 2) {
            const availabilityChecked = form.querySelectorAll('input[name="availability[]"]:checked');
            if (availabilityChecked.length === 0) {
                isValid = false;
                const availabilityGroup = form.querySelector('.availability-options');
                if (availabilityGroup && !availabilityGroup.querySelector('.error-message')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-danger small mt-1 error-message';
                    errorDiv.textContent = 'Please select at least one availability option';
                    availabilityGroup.parentNode.appendChild(errorDiv);
                }
            }
        }

        return isValid;
    }

    function validateField(field) {
        const value = field.type === 'checkbox' ? field.checked : field.value.trim();
        const isValid = field.type === 'checkbox' ? field.checked : value !== '';
        toggleFieldError(field, isValid, 'This field is required');
        return isValid;
    }

    function toggleFieldError(field, isValid, message) {
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;

        if (isValid) {
            field.classList.remove('is-invalid');
            const errorDiv = formGroup.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        } else {
            field.classList.add('is-invalid');
            if (!formGroup.querySelector('.invalid-feedback')) {
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

    const interestSelect = document.getElementById('interest');
    const otherInterestGroup = document.getElementById('otherInterestGroup');

    if (interestSelect && otherInterestGroup) {
        interestSelect.addEventListener('change', function () {
            const otherInterestField = document.getElementById('other_interest');
            if (this.value === 'other') {
                otherInterestGroup.style.display = 'block';
                if (otherInterestField) {
                    otherInterestField.setAttribute('required', 'required');
                }
            } else {
                otherInterestGroup.style.display = 'none';
                if (otherInterestField) {
                    otherInterestField.removeAttribute('required');
                    otherInterestField.value = '';
                }
            }
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validateCurrentStep(currentStep)) {
            return false;
        }

        const submitBtn = this.querySelector('.submit-btn') || this.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Application...';
            submitBtn.disabled = true;
        }

        setTimeout(() => {
            this.submit();
        }, 500);
    });
}

/** Counter Animation */
function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    if (counters.length === 0) return;

    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                animateCounter(entry.target);
                entry.target.classList.add('counted');
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

/** Back to Top Button */
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

    backToTop.addEventListener('click', function (e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/** Smooth Scrolling */
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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

/** Enhanced Form Validation */
function initializeFormValidation() {
    const form = document.getElementById('volunteerForm');
    if (!form) return;

    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('blur', function () {
            if (this.hasAttribute('required')) {
                validateField(this);
            }
        });

        field.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    function validateField(field) {
        let isValid = true;
        let message = '';

        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
            message = 'This field is required';
        }

        if (field.type === 'email' && field.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
        }

        if (field.type === 'tel' && field.value.trim()) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
            if (!phoneRegex.test(field.value)) {
                isValid = false;
                message = 'Please enter a valid phone number';
            }
        }

        const formGroup = field.closest('.form-group');
        if (!formGroup) return isValid;

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            const errorDiv = formGroup.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            let errorDiv = formGroup.querySelector('.invalid-feedback');
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

/** Video Hero Section */
function initializeVideoHero() {
    const video = document.getElementById('volunteerVideo');
    if (!video) return;

    video.addEventListener('loadedmetadata', function () {
        this.currentTime = 0;
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                video.play().catch(() => {});
            } else {
                video.pause();
            }
        });
    });
    observer.observe(video);
}

/** Form Auto-save */
function initializeFormAutoSave() {
    const form = document.getElementById('volunteerForm');
    if (!form) return;

    const AUTO_SAVE_KEY = 'volunteer_form_data';
    let autoSaveTimeout;

    loadFormData();

    form.addEventListener('input', function () {
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

    form.addEventListener('submit', function () {
        try {
            localStorage.removeItem(AUTO_SAVE_KEY);
        } catch (e) {
            console.log('Could not clear saved data:', e);
        }
    });
}
