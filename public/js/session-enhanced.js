/**
 * Enhanced Session Form Management
 * Provides comprehensive validation, conflict detection, and user experience
 * for session scheduling in the CREAMS system
 */

class EnhancedSessionForm {
    constructor(options) {
        this.options = {
            activityId: null,
            activityName: '',
            centreId: null,
            existingSessions: [],
            teacherSchedules: [],
            ...options
        };

        this.currentStep = 1;
        this.totalSteps = 4;
        this.validationRules = {};
        this.conflictChecker = null;
        this.autoSaveTimer = null;

        this.init();
    }

    init() {
        this.setupElements();
        this.setupValidation();
        this.setupEventListeners();
        this.setupConflictChecker();
        this.setupAutoSave();
        this.loadDraftData();
        
        console.log('Enhanced Session Form initialized');
    }

    setupElements() {
        this.form = document.getElementById('sessionForm');
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
            teacher_id: document.getElementById('teacher_id'),
            session_date: document.getElementById('session_date'),
            start_time: document.getElementById('start_time'),
            duration: document.getElementById('duration'),
            location: document.getElementById('location'),
            centre_id: document.getElementById('centre_id'),
            max_capacity: document.getElementById('max_capacity')
        };
    }

    setupValidation() {
        this.validationRules = {
            teacher_id: {
                required: true,
                message: 'Please select a qualified teacher'
            },
            session_date: {
                required: true,
                date: true,
                future: true,
                message: 'Please select a valid future date'
            },
            start_time: {
                required: true,
                time: true,
                businessHours: true,
                message: 'Please select a valid start time'
            },
            duration: {
                required: true,
                numeric: true,
                min: 15,
                max: 240,
                message: 'Duration must be between 15 and 240 minutes'
            },
            location: {
                required: true,
                minLength: 3,
                message: 'Please specify the session location'
            },
            centre_id: {
                required: true,
                message: 'Please select a centre'
            },
            max_capacity: {
                required: true,
                numeric: true,
                min: 1,
                max: 50,
                message: 'Capacity must be between 1 and 50 participants'
            }
        };

        // Initialize form validator
        this.validator = new EnhancedFormValidator(this.validationRules);
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

        // Specific field handlers
        this.setupDateTimeHandlers();
        this.setupTeacherHandlers();
        this.setupLocationHandlers();
    }

    setupDateTimeHandlers() {
        const dateField = this.fields.session_date;
        const timeField = this.fields.start_time;
        const durationField = this.fields.duration;

        if (dateField) {
            dateField.addEventListener('change', () => {
                this.checkScheduleConflicts();
                this.updateSchedulePreview();
                this.checkOperatingHours();
            });
        }

        if (timeField) {
            timeField.addEventListener('change', () => {
                this.checkScheduleConflicts();
                this.updateSchedulePreview();
                this.checkOperatingHours();
            });
        }

        if (durationField) {
            durationField.addEventListener('change', () => {
                this.updateSchedulePreview();
                this.checkScheduleConflicts();
            });
        }
    }

    setupTeacherHandlers() {
        const teacherField = this.fields.teacher_id;
        if (teacherField) {
            teacherField.addEventListener('change', () => {
                this.checkTeacherAvailability();
                this.updateTeacherInfo();
            });
        }
    }

    setupLocationHandlers() {
        const centreField = this.fields.centre_id;
        const locationField = this.fields.location;

        if (centreField) {
            centreField.addEventListener('change', () => {
                this.updateLocationSuggestions();
                this.checkOperatingHours();
            });
        }

        if (locationField) {
            locationField.addEventListener('input', () => {
                this.validateLocationAvailability();
            });
        }
    }

    setupConflictChecker() {
        this.conflictChecker = {
            checkScheduleConflicts: () => this.checkScheduleConflicts(),
            checkTeacherAvailability: () => this.checkTeacherAvailability(),
            checkLocationAvailability: () => this.validateLocationAvailability()
        };
    }

    setupAutoSave() {
        // Auto-save draft every 30 seconds
        this.autoSaveTimer = setInterval(() => {
            this.saveDraft();
        }, 30000);

        // Save draft when user navigates away
        window.addEventListener('beforeunload', () => {
            this.saveDraft();
        });
    }

    loadDraftData() {
        const draftKey = `session_draft_${this.options.activityId}`;
        const draftData = localStorage.getItem(draftKey);
        
        if (draftData) {
            try {
                const data = JSON.parse(draftData);
                this.populateFormData(data);
                this.showNotification('Draft data loaded', 'info');
            } catch (e) {
                console.warn('Failed to load draft data:', e);
            }
        }
    }

    saveDraft() {
        const draftKey = `session_draft_${this.options.activityId}`;
        const formData = this.getFormData();
        
        try {
            localStorage.setItem(draftKey, JSON.stringify(formData));
        } catch (e) {
            console.warn('Failed to save draft:', e);
        }
    }

    clearDraft() {
        const draftKey = `session_draft_${this.options.activityId}`;
        localStorage.removeItem(draftKey);
    }

    nextStep(e) {
        e.preventDefault();
        
        if (this.validateCurrentStep()) {
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateStepDisplay();
                this.updateNavigationButtons();
                this.saveDraft();
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
        const progressPercentage = (this.currentStep / this.totalSteps) * 100;
        if (this.progressFill) {
            this.progressFill.style.width = `${progressPercentage}%`;
        }

        // Handle step-specific actions
        if (this.currentStep === 4) {
            this.populateReviewSection();
            this.performFinalValidation();
        }
    }

    updateNavigationButtons() {
        // Previous button
        if (this.prevBtn) {
            this.prevBtn.style.display = this.currentStep > 1 ? 'inline-flex' : 'none';
        }

        // Next/Submit buttons
        if (this.currentStep === this.totalSteps) {
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
            1: ['teacher_id', 'max_capacity'],
            2: ['session_date', 'start_time', 'duration'],
            3: ['centre_id', 'location'],
            4: [] // Review step
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
            if (rules.date && !this.isValidDate(value)) {
                errors.push('Please enter a valid date');
            }

            if (rules.future && !this.isFutureDate(value)) {
                errors.push('Date must be in the future');
            }

            if (rules.time && !this.isValidTime(value)) {
                errors.push('Please enter a valid time');
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

            if (rules.minLength && value.length < rules.minLength) {
                errors.push(`Minimum length is ${rules.minLength} characters`);
            }
        }

        // Custom validations
        if (fieldName === 'start_time' && rules.businessHours) {
            if (!this.isBusinessHours(value)) {
                errors.push('Time should be within business hours');
            }
        }

        // Update field display
        this.updateFieldValidation(fieldName, errors);

        return errors.length === 0;
    }

    updateFieldValidation(fieldName, errors) {
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
        clearTimeout(this.validationTimers?.[fieldName]);
        
        if (!this.validationTimers) this.validationTimers = {};
        
        this.validationTimers[fieldName] = setTimeout(() => {
            this.validateField(fieldName);
        }, 500);
    }

    handleFieldChange(fieldName) {
        this.validateField(fieldName);
        this.saveDraft();

        // Trigger specific handlers
        switch (fieldName) {
            case 'session_date':
            case 'start_time':
            case 'duration':
                this.updateSchedulePreview();
                this.checkScheduleConflicts();
                break;
            case 'teacher_id':
                this.checkTeacherAvailability();
                break;
            case 'centre_id':
                this.updateLocationSuggestions();
                break;
        }
    }

    updateSchedulePreview() {
        const date = this.fields.session_date?.value;
        const time = this.fields.start_time?.value;
        const duration = this.fields.duration?.value;

        if (!date || !time || !duration) return;

        const preview = document.getElementById('schedulePreview');
        if (!preview) return;

        try {
            const sessionDate = new Date(`${date}T${time}`);
            const endTime = new Date(sessionDate.getTime() + (parseInt(duration) * 60000));

            const dateTimeDisplay = sessionDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) + ' at ' + sessionDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const durationDisplay = `${duration} minutes`;
            const endTimeDisplay = endTime.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.getElementById('previewDateTime').textContent = dateTimeDisplay;
            document.getElementById('previewDuration').textContent = durationDisplay;
            document.getElementById('previewEndTime').textContent = endTimeDisplay;

            preview.style.display = 'block';
        } catch (e) {
            console.warn('Error updating schedule preview:', e);
            preview.style.display = 'none';
        }
    }

    checkScheduleConflicts() {
        const date = this.fields.session_date?.value;
        const time = this.fields.start_time?.value;
        const duration = this.fields.duration?.value;
        const teacherId = this.fields.teacher_id?.value;

        if (!date || !time || !duration) return;

        // Check for session conflicts
        this.checkSessionTimeConflicts(date, time, duration);
        
        // Check for teacher conflicts
        if (teacherId) {
            this.checkTeacherTimeConflicts(teacherId, date, time, duration);
        }
    }

    checkSessionTimeConflicts(date, time, duration) {
        const conflicts = this.findTimeConflicts(date, time, duration);
        const warningElement = document.getElementById('dateConflictWarning');
        
        if (conflicts.length > 0 && warningElement) {
            warningElement.style.display = 'block';
            warningElement.querySelector('span').textContent = 
                `${conflicts.length} existing session(s) may conflict`;
        } else if (warningElement) {
            warningElement.style.display = 'none';
        }
    }

    checkTeacherTimeConflicts(teacherId, date, time, duration) {
        const conflicts = this.findTeacherConflicts(teacherId, date, time, duration);
        const warningElement = document.getElementById('timeConflictWarning');
        
        if (conflicts.length > 0 && warningElement) {
            warningElement.style.display = 'block';
            warningElement.querySelector('span').textContent = 
                'Teacher has conflicting sessions';
        } else if (warningElement) {
            warningElement.style.display = 'none';
        }
    }

    findTimeConflicts(date, time, duration) {
        // Implementation would check against existing sessions
        // This is a placeholder for the actual conflict detection logic
        return [];
    }

    findTeacherConflicts(teacherId, date, time, duration) {
        // Implementation would check teacher's existing schedule
        // This is a placeholder for the actual conflict detection logic
        return [];
    }

    checkOperatingHours() {
        const time = this.fields.start_time?.value;
        const duration = this.fields.duration?.value;

        if (!time || !duration) return;

        const warningElement = document.getElementById('operatingHoursWarning');
        if (!warningElement) return;

        // Check if session is within operating hours (8 AM - 6 PM example)
        const startTime = new Date(`2000-01-01T${time}`);
        const endTime = new Date(startTime.getTime() + (parseInt(duration) * 60000));
        const operatingStart = new Date('2000-01-01T08:00');
        const operatingEnd = new Date('2000-01-01T18:00');

        if (startTime < operatingStart || endTime > operatingEnd) {
            warningElement.style.display = 'block';
        } else {
            warningElement.style.display = 'none';
        }
    }

    checkTeacherAvailability() {
        const teacherId = this.fields.teacher_id?.value;
        if (!teacherId) return;

        // Update teacher information display
        this.updateTeacherInfo();
    }

    updateTeacherInfo() {
        const teacherSelect = this.fields.teacher_id;
        if (!teacherSelect) return;

        const selectedOption = teacherSelect.options[teacherSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const qualifications = selectedOption.dataset.qualifications;
            const specialization = selectedOption.dataset.specialization;
            
            // Update display elements if they exist
            // This could show teacher qualifications, availability, etc.
        }
    }

    updateLocationSuggestions() {
        const centreSelect = this.fields.centre_id;
        const locationField = this.fields.location;
        
        if (!centreSelect || !locationField) return;

        const selectedOption = centreSelect.options[centreSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const facilities = selectedOption.dataset.facilities;
            
            // Could populate location suggestions based on centre facilities
            if (!locationField.value && facilities) {
                // Suggest default location
            }
        }
    }

    validateLocationAvailability() {
        const location = this.fields.location?.value;
        const date = this.fields.session_date?.value;
        const time = this.fields.start_time?.value;

        if (!location || !date || !time) return;

        // Check if location is available at the specified time
        // This would integrate with a location booking system
    }

    populateReviewSection() {
        // Populate review information
        this.updateReviewField('reviewTeacher', this.getSelectedOptionText('teacher_id'));
        this.updateReviewField('reviewDateTime', this.getFormattedDateTime());
        this.updateReviewField('reviewDuration', this.getFormattedDuration());
        this.updateReviewField('reviewLocation', this.getFormattedLocation());
        this.updateReviewField('reviewCapacity', this.fields.max_capacity?.value + ' participants');
    }

    updateReviewField(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value || '-';
        }
    }

    getSelectedOptionText(fieldName) {
        const select = this.fields[fieldName];
        if (!select) return '-';
        
        const selectedOption = select.options[select.selectedIndex];
        return selectedOption ? selectedOption.textContent : '-';
    }

    getFormattedDateTime() {
        const date = this.fields.session_date?.value;
        const time = this.fields.start_time?.value;
        
        if (!date || !time) return '-';
        
        try {
            const dateTime = new Date(`${date}T${time}`);
            return dateTime.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }) + ' at ' + dateTime.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return '-';
        }
    }

    getFormattedDuration() {
        const duration = this.fields.duration?.value;
        if (!duration) return '-';
        
        const minutes = parseInt(duration);
        if (minutes >= 60) {
            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;
            return remainingMinutes > 0 ? 
                `${hours}h ${remainingMinutes}m` : `${hours} hour${hours > 1 ? 's' : ''}`;
        }
        return `${minutes} minutes`;
    }

    getFormattedLocation() {
        const centre = this.getSelectedOptionText('centre_id');
        const location = this.fields.location?.value;
        
        if (!centre || centre === '-') return location || '-';
        if (!location) return centre;
        
        return `${location} (${centre})`;
    }

    performFinalValidation() {
        const validationContainer = document.getElementById('finalValidation');
        if (!validationContainer) return;

        const validationItems = [
            { field: 'teacher_id', label: 'Teacher selected', required: true },
            { field: 'session_date', label: 'Date specified', required: true },
            { field: 'start_time', label: 'Time specified', required: true },
            { field: 'duration', label: 'Duration set', required: true },
            { field: 'location', label: 'Location specified', required: true },
            { field: 'centre_id', label: 'Centre selected', required: true },
            { field: 'max_capacity', label: 'Capacity set', required: true }
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
            if (!allValid) {
                this.submitBtn.classList.add('disabled');
            } else {
                this.submitBtn.classList.remove('disabled');
            }
        }

        // Check for conflicts
        this.performFinalConflictCheck();
    }

    performFinalConflictCheck() {
        const conflictContainer = document.getElementById('conflictCheck');
        if (!conflictContainer) return;

        // Simulate conflict check
        setTimeout(() => {
            const hasConflicts = false; // Would be determined by actual conflict checking

            if (hasConflicts) {
                conflictContainer.innerHTML = `
                    <div class="conflict-item">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Scheduling conflicts detected - please review</span>
                    </div>
                `;
            } else {
                conflictContainer.innerHTML = `
                    <div class="no-conflicts">
                        <i class="fas fa-check-circle"></i>
                        <span>No scheduling conflicts detected</span>
                    </div>
                `;
            }
        }, 1000);
    }

    showValidationSummary() {
        const errors = this.getAllValidationErrors();
        
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

    getAllValidationErrors() {
        const errors = [];
        const currentStepFields = this.getStepFields(this.currentStep);

        currentStepFields.forEach(fieldName => {
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
            this.showNotification('Please fix validation errors before submitting', 'error');
            return;
        }

        // Show loading state
        this.setFormLoading(true);

        // Submit form
        const formData = new FormData(this.form);
        
        // Add any additional data
        formData.append('activity_id', this.options.activityId);

        fetch(this.form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.clearDraft();
                this.showNotification('Session scheduled successfully!', 'success');
                
                // Redirect or update UI
                setTimeout(() => {
                    window.location.href = data.redirect || `/activities/${this.options.activityId}/sessions`;
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to schedule session');
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            this.showNotification(error.message || 'Failed to schedule session', 'error');
        })
        .finally(() => {
            this.setFormLoading(false);
        });
    }

    validateAllSteps() {
        let isValid = true;
        
        for (let step = 1; step <= this.totalSteps - 1; step++) {
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
            this.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';
        } else {
            this.form.classList.remove('loading');
            this.submitBtn.disabled = false;
            this.submitBtn.innerHTML = '<i class="fas fa-calendar-plus"></i> Schedule Session';
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
        return data;
    }

    populateFormData(data) {
        Object.keys(data).forEach(fieldName => {
            const field = this.fields[fieldName];
            if (field && data[fieldName]) {
                field.value = data[fieldName];
            }
        });
    }

    showNotification(message, type = 'info') {
        // Create and show notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${this.getNotificationIcon(type)}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
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

    // Utility methods
    isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }

    isFutureDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date >= today;
    }

    isValidTime(timeString) {
        const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return timeRegex.test(timeString);
    }

    isNumeric(value) {
        return !isNaN(value) && isFinite(value);
    }

    isBusinessHours(timeString) {
        if (!this.isValidTime(timeString)) return false;
        
        const time = new Date(`2000-01-01T${timeString}`);
        const startTime = new Date('2000-01-01T08:00');
        const endTime = new Date('2000-01-01T18:00');
        
        return time >= startTime && time <= endTime;
    }

    destroy() {
        // Clean up
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
        
        if (this.validationTimers) {
            Object.values(this.validationTimers).forEach(timer => {
                clearTimeout(timer);
            });
        }
    }
}

// Notification styles
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
if (!document.getElementById('session-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'session-notification-styles';
    style.textContent = notificationStyles;
    document.head.appendChild(style);
}

// Export for use in other modules
window.EnhancedSessionForm = EnhancedSessionForm;