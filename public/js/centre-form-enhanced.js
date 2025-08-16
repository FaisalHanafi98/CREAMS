/**
 * Enhanced Centre Form Management
 * Provides comprehensive functionality for multi-step centre creation form
 */

class EnhancedCentreForm {
    constructor(options) {
        this.options = {
            formId: 'centreForm',
            totalSteps: 5,
            autoSave: true,
            realTimeValidation: true,
            ...options
        };

        this.currentStep = 1;
        this.validationRules = {};
        this.autoSaveTimer = null;
        this.validationTimers = {};

        this.init();
    }

    init() {
        this.setupElements();
        this.setupValidationRules();
        this.setupEventListeners();
        this.setupImageUpload();
        this.setupCharacterCounters();
        this.setupAutoSave();
        this.loadDraftData();
        
        console.log('Enhanced Centre Form initialized');
    }

    setupElements() {
        this.form = document.getElementById(this.options.formId);
        this.steps = document.querySelectorAll('.form-step');
        this.progressSteps = document.querySelectorAll('.step');
        this.progressFill = document.querySelector('.progress-fill');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.submitBtn = document.getElementById('submitBtn');
        this.validationSummary = document.getElementById('validationSummary');
        this.validationList = document.getElementById('validationList');

        // Form fields
        this.fields = {
            centre_id: document.getElementById('centre_id'),
            centre_name: document.getElementById('centre_name'),
            centre_description: document.getElementById('centre_description'),
            centre_address: document.getElementById('centre_address'),
            centre_city: document.getElementById('centre_city'),
            centre_state: document.getElementById('centre_state'),
            centre_postcode: document.getElementById('centre_postcode'),
            centre_phone: document.getElementById('centre_phone'),
            centre_email: document.getElementById('centre_email'),
            centre_manager: document.getElementById('centre_manager'),
            centre_capacity: document.getElementById('centre_capacity'),
            centre_status: document.getElementById('centre_status')
        };
    }

    setupValidationRules() {
        this.validationRules = {
            centre_id: {
                required: true,
                pattern: /^[A-Za-z0-9-_]+$/,
                maxLength: 20,
                unique: true,
                message: 'Centre ID is required and must be unique'
            },
            centre_name: {
                required: true,
                minLength: 3,
                maxLength: 100,
                message: 'Centre name must be between 3 and 100 characters'
            },
            centre_description: {
                maxLength: 500,
                message: 'Description cannot exceed 500 characters'
            },
            centre_address: {
                required: true,
                minLength: 10,
                maxLength: 250,
                message: 'Complete address is required (min 10 characters)'
            },
            centre_city: {
                required: true,
                minLength: 2,
                message: 'City is required'
            },
            centre_state: {
                required: true,
                message: 'Please select a state'
            },
            centre_postcode: {
                required: true,
                pattern: /^\d{5}$/,
                message: 'Valid 5-digit postcode is required'
            },
            centre_phone: {
                pattern: /^\+?[\d\s\-\(\)]+$/,
                message: 'Please enter a valid phone number'
            },
            centre_email: {
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            centre_capacity: {
                required: true,
                numeric: true,
                min: 1,
                max: 1000,
                message: 'Capacity must be between 1 and 1000'
            },
            centre_status: {
                required: true,
                message: 'Please select a status'
            }
        };
    }

    setupEventListeners() {
        // Navigation buttons
        this.nextBtn?.addEventListener('click', (e) => this.nextStep(e));
        this.prevBtn?.addEventListener('click', (e) => this.prevStep(e));
        this.submitBtn?.addEventListener('click', (e) => this.submitForm(e));

        // Form validation
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            if (field) {
                field.addEventListener('blur', () => this.validateField(fieldName));
                field.addEventListener('input', () => this.debounceValidation(fieldName));
                field.addEventListener('change', () => this.handleFieldChange(fieldName));
            }
        });

        // Special handlers
        this.setupSpecialHandlers();
    }

    setupSpecialHandlers() {
        // Auto-generate centre ID from name
        const nameField = this.fields.centre_name;
        const idField = this.fields.centre_id;
        
        if (nameField && idField) {
            nameField.addEventListener('input', () => {
                if (!idField.value || idField.dataset.autoGenerated === 'true') {
                    const generatedId = this.generateCentreId(nameField.value);
                    idField.value = generatedId;
                    idField.dataset.autoGenerated = 'true';
                }
            });

            idField.addEventListener('input', () => {
                idField.dataset.autoGenerated = 'false';
            });
        }

        // Coordinate validation
        const coordinatesField = document.getElementById('centre_coordinates');
        if (coordinatesField) {
            coordinatesField.addEventListener('input', () => {
                this.validateCoordinates(coordinatesField);
            });
        }

        // Phone number formatting
        const phoneFields = ['centre_phone', 'centre_fax', 'centre_manager_contact'];
        phoneFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', () => this.formatPhoneNumber(field));
            }
        });
    }

    setupImageUpload() {
        const uploadArea = document.getElementById('imageUploadArea');
        const fileInput = document.getElementById('centre_image');
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (!uploadArea || !fileInput) return;

        // Click to upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // File selection
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.handleImageUpload(file, preview, previewImg);
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                fileInput.files = e.dataTransfer.files;
                this.handleImageUpload(file, preview, previewImg);
            }
        });
    }

    handleImageUpload(file, preview, previewImg) {
        // Validate file
        if (!this.validateImageFile(file)) return;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            document.querySelector('.upload-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    validateImageFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            this.showNotification('Please select a valid image file (JPG, PNG, WebP)', 'error');
            return false;
        }

        if (file.size > maxSize) {
            this.showNotification('Image file must be less than 5MB', 'error');
            return false;
        }

        return true;
    }

    setupCharacterCounters() {
        const textareas = [
            { field: 'centre_description', counter: 'descriptionCounter', max: 500 },
            { field: 'centre_address', counter: 'addressCounter', max: 250 },
            { field: 'centre_facilities', counter: 'facilitiesCounter', max: 1000 }
        ];

        textareas.forEach(({ field, counter, max }) => {
            const textarea = document.getElementById(field);
            const counterElement = document.getElementById(counter);
            
            if (textarea && counterElement) {
                textarea.addEventListener('input', () => {
                    const length = textarea.value.length;
                    counterElement.textContent = length;
                    
                    if (length > max * 0.9) {
                        counterElement.style.color = length > max ? '#ff4757' : '#ffa726';
                    } else {
                        counterElement.style.color = '#6c757d';
                    }
                });
            }
        });
    }

    setupAutoSave() {
        if (!this.options.autoSave) return;

        // Auto-save every 30 seconds
        this.autoSaveTimer = setInterval(() => {
            this.saveDraft();
        }, 30000);

        // Save before unload
        window.addEventListener('beforeunload', () => {
            this.saveDraft();
        });
    }

    nextStep(e) {
        e.preventDefault();
        
        if (this.validateCurrentStep()) {
            if (this.currentStep < this.options.totalSteps) {
                this.currentStep++;
                this.updateStepDisplay();
                this.updateNavigationButtons();
                this.saveDraft();
                
                // Populate review on last step
                if (this.currentStep === this.options.totalSteps) {
                    this.populateReviewSection();
                }
            }
        } else {
            this.showValidationSummary();
        }
    }

    prevStep(e) {
        e.preventDefault();
        
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
            this.updateNavigationButtons();
        }
    }

    updateStepDisplay() {
        // Hide all steps
        this.steps.forEach(step => {
            step.classList.remove('active');
        });

        // Show current step
        const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
        }

        // Update progress indicators
        this.progressSteps.forEach((step, index) => {
            const stepNumber = index + 1;
            step.classList.remove('active', 'completed');
            
            if (stepNumber < this.currentStep) {
                step.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                step.classList.add('active');
            }
        });

        // Update progress bar
        const progressPercentage = (this.currentStep / this.options.totalSteps) * 100;
        if (this.progressFill) {
            this.progressFill.style.width = `${progressPercentage}%`;
        }

        // Hide validation summary
        if (this.validationSummary) {
            this.validationSummary.style.display = 'none';
        }
    }

    updateNavigationButtons() {
        // Previous button
        if (this.prevBtn) {
            this.prevBtn.style.display = this.currentStep > 1 ? 'inline-flex' : 'none';
        }

        // Next/Submit buttons
        if (this.currentStep === this.options.totalSteps) {
            if (this.nextBtn) this.nextBtn.style.display = 'none';
            if (this.submitBtn) this.submitBtn.style.display = 'inline-flex';
        } else {
            if (this.nextBtn) this.nextBtn.style.display = 'inline-flex';
            if (this.submitBtn) this.submitBtn.style.display = 'none';
        }
    }

    validateCurrentStep() {
        const stepFields = this.getStepFields(this.currentStep);
        let isValid = true;

        stepFields.forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                isValid = false;
            }
        });

        return isValid;
    }

    getStepFields(step) {
        const stepFieldMap = {
            1: ['centre_id', 'centre_name'],
            2: ['centre_address', 'centre_city', 'centre_state', 'centre_postcode'],
            3: ['centre_phone', 'centre_email'],
            4: ['centre_capacity', 'centre_status'],
            5: [] // Review step
        };

        return stepFieldMap[step] || [];
    }

    validateField(fieldName) {
        const field = this.fields[fieldName];
        const rules = this.validationRules[fieldName];
        
        if (!field || !rules) return true;

        const value = field.value.trim();
        const errors = [];

        // Required validation
        if (rules.required && !value) {
            errors.push(rules.message || `${fieldName} is required`);
        }

        // Type-specific validations
        if (value) {
            if (rules.pattern && !rules.pattern.test(value)) {
                errors.push(rules.message || 'Invalid format');
            }

            if (rules.minLength && value.length < rules.minLength) {
                errors.push(`Minimum length is ${rules.minLength} characters`);
            }

            if (rules.maxLength && value.length > rules.maxLength) {
                errors.push(`Maximum length is ${rules.maxLength} characters`);
            }

            if (rules.numeric && !this.isNumeric(value)) {
                errors.push('Please enter a valid number');
            }

            if (rules.min && parseFloat(value) < rules.min) {
                errors.push(`Minimum value is ${rules.min}`);
            }

            if (rules.max && parseFloat(value) > rules.max) {
                errors.push(`Maximum value is ${rules.max}`);
            }
        }

        // Update field display
        this.updateFieldDisplay(fieldName, errors);

        return errors.length === 0;
    }

    updateFieldDisplay(fieldName, errors) {
        const field = this.fields[fieldName];
        const errorElement = document.getElementById(`${fieldName}_error`);

        if (errors.length > 0) {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            
            if (errorElement) {
                errorElement.textContent = errors[0];
                errorElement.classList.add('show');
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.classList.remove('show');
            }
        }
    }

    debounceValidation(fieldName) {
        clearTimeout(this.validationTimers[fieldName]);
        
        this.validationTimers[fieldName] = setTimeout(() => {
            this.validateField(fieldName);
        }, 500);
    }

    handleFieldChange(fieldName) {
        this.validateField(fieldName);
        this.saveDraft();

        // Update review section in real-time if on review step
        if (this.currentStep === this.options.totalSteps) {
            this.updateReviewField(fieldName);
        }
    }

    populateReviewSection() {
        // Basic Information
        this.updateReviewElement('review-centre-id', this.fields.centre_id?.value);
        this.updateReviewElement('review-centre-name', this.fields.centre_name?.value);
        this.updateReviewElement('review-description', this.fields.centre_description?.value || 'No description provided');

        // Location Details
        this.updateReviewElement('review-address', this.fields.centre_address?.value);
        this.updateReviewElement('review-city-state', 
            `${this.fields.centre_city?.value || ''}, ${this.getSelectedText('centre_state')}`);
        this.updateReviewElement('review-postcode', this.fields.centre_postcode?.value);

        // Contact Information
        this.updateReviewElement('review-phone', this.fields.centre_phone?.value || 'Not provided');
        this.updateReviewElement('review-email', this.fields.centre_email?.value || 'Not provided');
        this.updateReviewElement('review-manager', 
            document.getElementById('centre_manager')?.value || 'Not assigned');

        // Operational Details
        this.updateReviewElement('review-capacity', this.fields.centre_capacity?.value + ' trainees');
        this.updateReviewElement('review-status', this.getSelectedText('centre_status'));
        this.updateReviewElement('review-hours', this.getOperatingHours());

        // Perform final validation
        this.performFinalValidation();
    }

    updateReviewElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value || '-';
        }
    }

    updateReviewField(fieldName) {
        const reviewMap = {
            centre_id: 'review-centre-id',
            centre_name: 'review-centre-name',
            centre_description: 'review-description',
            centre_address: 'review-address',
            centre_capacity: 'review-capacity'
        };

        const reviewElementId = reviewMap[fieldName];
        if (reviewElementId) {
            let value = this.fields[fieldName]?.value;
            if (fieldName === 'centre_capacity' && value) {
                value += ' trainees';
            }
            this.updateReviewElement(reviewElementId, value);
        }
    }

    getSelectedText(selectId) {
        const select = document.getElementById(selectId);
        if (select && select.selectedIndex > 0) {
            return select.options[select.selectedIndex].text;
        }
        return 'Not selected';
    }

    getOperatingHours() {
        const openingTime = document.getElementById('opening_time')?.value;
        const closingTime = document.getElementById('closing_time')?.value;
        
        if (openingTime && closingTime) {
            return `${openingTime} - ${closingTime}`;
        }
        return 'Not specified';
    }

    performFinalValidation() {
        const validationContainer = document.getElementById('finalValidationItems');
        if (!validationContainer) return;

        const validationItems = [
            { field: 'centre_id', label: 'Centre ID provided', required: true },
            { field: 'centre_name', label: 'Centre name provided', required: true },
            { field: 'centre_address', label: 'Address completed', required: true },
            { field: 'centre_city', label: 'City specified', required: true },
            { field: 'centre_state', label: 'State selected', required: true },
            { field: 'centre_postcode', label: 'Postcode provided', required: true },
            { field: 'centre_capacity', label: 'Capacity defined', required: true },
            { field: 'centre_status', label: 'Status selected', required: true }
        ];

        let html = '';
        let allValid = true;

        validationItems.forEach(item => {
            const field = this.fields[item.field];
            const isValid = field && field.value.trim() !== '';
            
            if (item.required && !isValid) {
                allValid = false;
            }

            html += `
                <div class="validation-item ${isValid ? 'valid' : 'invalid'}">
                    <i class="fas ${isValid ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    <span>${item.label}</span>
                </div>
            `;
        });

        validationContainer.innerHTML = html;

        // Update submit button state
        if (this.submitBtn) {
            this.submitBtn.disabled = !allValid;
        }
    }

    showValidationSummary() {
        const errors = this.getCurrentStepErrors();
        
        if (errors.length === 0) {
            this.validationSummary.style.display = 'none';
            return;
        }

        this.validationList.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            this.validationList.appendChild(li);
        });

        this.validationSummary.style.display = 'block';
        this.validationSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    getCurrentStepErrors() {
        const errors = [];
        const stepFields = this.getStepFields(this.currentStep);

        stepFields.forEach(fieldName => {
            const field = this.fields[fieldName];
            const rules = this.validationRules[fieldName];
            
            if (!field || !rules) return;

            const value = field.value.trim();
            
            if (rules.required && !value) {
                errors.push(rules.message || `${fieldName} is required`);
            }
        });

        return errors;
    }

    submitForm(e) {
        e.preventDefault();

        // Final validation
        if (!this.validateAllSteps()) {
            this.showNotification('Please fix all validation errors before submitting', 'error');
            return;
        }

        // Show loading state
        this.setFormLoading(true);

        // Submit form
        this.form.submit();
    }

    validateAllSteps() {
        let isValid = true;
        
        for (let step = 1; step <= this.options.totalSteps - 1; step++) {
            const stepFields = this.getStepFields(step);
            stepFields.forEach(fieldName => {
                if (!this.validateField(fieldName)) {
                    isValid = false;
                }
            });
        }

        return isValid;
    }

    setFormLoading(loading) {
        if (loading) {
            this.form.classList.add('loading');
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Centre...';
        } else {
            this.form.classList.remove('loading');
            this.submitBtn.disabled = false;
            this.submitBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Centre';
        }
    }

    saveDraft() {
        if (!this.options.autoSave) return;

        const draftKey = 'centre_form_draft';
        const formData = this.getFormData();
        
        try {
            localStorage.setItem(draftKey, JSON.stringify({
                data: formData,
                step: this.currentStep,
                timestamp: new Date().toISOString()
            }));
        } catch (e) {
            console.warn('Failed to save draft:', e);
        }
    }

    loadDraftData() {
        if (!this.options.autoSave) return;

        const draftKey = 'centre_form_draft';
        const draftData = localStorage.getItem(draftKey);
        
        if (draftData) {
            try {
                const draft = JSON.parse(draftData);
                const draftAge = new Date() - new Date(draft.timestamp);
                
                // Only load if draft is less than 24 hours old
                if (draftAge < 24 * 60 * 60 * 1000) {
                    this.populateFormData(draft.data);
                    this.showNotification('Draft data loaded', 'info');
                }
            } catch (e) {
                console.warn('Failed to load draft data:', e);
            }
        }
    }

    getFormData() {
        const data = {};
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            if (field) {
                data[fieldName] = field.value;
            }
        });
        
        // Add other form fields
        const otherFields = [
            'centre_region', 'centre_coordinates', 'centre_fax', 'centre_website',
            'centre_manager_contact', 'centre_supervisor', 'centre_license_number',
            'centre_type', 'opening_time', 'closing_time', 'centre_facilities'
        ];
        
        otherFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                data[fieldName] = field.value;
            }
        });

        return data;
    }

    populateFormData(data) {
        Object.keys(data).forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && data[fieldName]) {
                field.value = data[fieldName];
            }
        });
    }

    clearDraft() {
        const draftKey = 'centre_form_draft';
        localStorage.removeItem(draftKey);
    }

    // Utility methods
    generateCentreId(name) {
        if (!name) return '';
        
        const words = name.toUpperCase().split(' ').filter(word => word.length > 0);
        let id = '';
        
        if (words.length >= 2) {
            id = words.slice(0, 2).map(word => word.substring(0, 3)).join('-');
        } else {
            id = name.toUpperCase().substring(0, 6).replace(/[^A-Z0-9]/g, '');
        }
        
        // Add random number
        id += '-' + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        
        return id.substring(0, 20);
    }

    validateCoordinates(field) {
        const value = field.value.trim();
        if (!value) return true;

        const coordPattern = /^-?\d+\.?\d*,\s*-?\d+\.?\d*$/;
        const errorElement = document.getElementById('centre_coordinates_error');
        
        if (!coordPattern.test(value)) {
            field.classList.add('is-invalid');
            if (errorElement) {
                errorElement.textContent = 'Invalid format. Use: latitude, longitude';
                errorElement.classList.add('show');
            }
            return false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (errorElement) {
                errorElement.classList.remove('show');
            }
            return true;
        }
    }

    formatPhoneNumber(field) {
        let value = field.value.replace(/\D/g, '');
        
        // Add Malaysia country code if not present and number starts with 0
        if (value.startsWith('0') && value.length > 1) {
            value = '60' + value.substring(1);
        }
        
        // Format with country code
        if (value.length > 0 && !value.startsWith('60')) {
            if (value.length <= 10) {
                value = '60' + value;
            }
        }
        
        // Add + prefix
        if (value && !field.value.startsWith('+')) {
            field.value = '+' + value;
        }
    }

    isNumeric(value) {
        return !isNaN(value) && isFinite(value);
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${this.getNotificationIcon(type)}"></i>
            <span>${message}</span>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    destroy() {
        // Clean up
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
        
        Object.values(this.validationTimers).forEach(timer => {
            clearTimeout(timer);
        });
    }
}

// Global functions for template use
function removeImage() {
    const preview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('centre_image');
    const placeholder = document.querySelector('.upload-placeholder');
    
    if (preview) preview.style.display = 'none';
    if (fileInput) fileInput.value = '';
    if (placeholder) placeholder.style.display = 'block';
}

// Notification styles (same as other enhanced forms)
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .notification-success { background: #2ed573; }
    .notification-error { background: #ff4757; }
    .notification-warning { background: #ffa726; }
    .notification-info { background: #3742fa; }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;

// Inject notification styles
if (!document.getElementById('centre-form-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'centre-form-notification-styles';
    style.textContent = notificationStyles;
    document.head.appendChild(style);
}

// Export for use in other modules
window.EnhancedCentreForm = EnhancedCentreForm;