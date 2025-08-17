/**
 * CREAMS Form Analytics and Monitoring System
 * Tracks form interactions, validation errors, and user behavior
 */

class FormAnalytics {
    constructor() {
        this.sessionId = this.generateSessionId();
        this.formInteractions = new Map();
        this.validationErrors = [];
        this.submissionAttempts = [];
        this.fieldFocusTime = new Map();
        this.abandonmentTracking = {};
        this.isEnabled = true;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.startSessionTracking();
        this.setupPerformanceMonitoring();
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.trackFormsOnPage();
        });

        // Track page visibility for abandonment
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackAbandonment();
            }
        });

        // Track before page unload
        window.addEventListener('beforeunload', () => {
            this.sendPendingAnalytics();
        });
    }

    trackFormsOnPage() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            this.trackForm(form);
        });
    }

    trackForm(form) {
        const formId = form.id || this.generateFormId();
        if (!form.id) form.id = formId;

        const formData = {
            formId: formId,
            formType: this.detectFormType(form),
            startTime: Date.now(),
            fields: new Map(),
            validationErrors: [],
            submissionAttempts: [],
            isCompleted: false,
            totalInteractionTime: 0
        };

        this.formInteractions.set(formId, formData);

        // Track form fields
        form.querySelectorAll('input, select, textarea').forEach(field => {
            this.trackField(field, formId);
        });

        // Track form submission
        form.addEventListener('submit', (e) => {
            this.trackSubmissionAttempt(formId, true);
        });

        // Track form errors
        form.addEventListener('invalid', () => {
            this.trackSubmissionAttempt(formId, false);
        });

        console.log(`Form analytics initialized for: ${formId}`);
    }

    detectFormType(form) {
        const action = form.action || '';
        const className = form.className || '';
        
        if (action.includes('login') || className.includes('login')) return 'authentication';
        if (action.includes('register') || className.includes('register')) return 'registration';
        if (action.includes('activities') || className.includes('activity')) return 'activity_management';
        if (action.includes('trainees') || className.includes('trainee')) return 'trainee_management';
        if (action.includes('staff') || className.includes('staff')) return 'staff_management';
        if (action.includes('letters') || className.includes('letter')) return 'letter_generation';
        if (action.includes('attendance') || className.includes('attendance')) return 'attendance';
        if (action.includes('centres') || className.includes('centre')) return 'centre_management';
        
        return 'unknown';
    }

    generateFormId() {
        return 'form_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    }

    trackField(field, formId) {
        const fieldName = field.name || field.id || 'unnamed_field';
        const formData = this.formInteractions.get(formId);
        
        const fieldData = {
            fieldName: fieldName,
            fieldType: field.type || field.tagName.toLowerCase(),
            interactions: 0,
            focusTime: 0,
            validationErrors: [],
            lastFocusTime: null,
            hasBeenFocused: false,
            finalValue: ''
        };

        formData.fields.set(fieldName, fieldData);

        // Track focus events
        field.addEventListener('focus', () => {
            fieldData.lastFocusTime = Date.now();
            fieldData.hasBeenFocused = true;
            this.trackFieldFocus(formId, fieldName);
        });

        // Track blur events
        field.addEventListener('blur', () => {
            if (fieldData.lastFocusTime) {
                const focusTime = Date.now() - fieldData.lastFocusTime;
                fieldData.focusTime += focusTime;
                formData.totalInteractionTime += focusTime;
            }
            fieldData.finalValue = field.value;
            this.trackFieldBlur(formId, fieldName);
        });

        // Track input events
        field.addEventListener('input', () => {
            fieldData.interactions++;
            this.trackFieldInteraction(formId, fieldName);
        });

        // Track validation errors
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (field.classList.contains('is-invalid')) {
                        this.trackValidationError(formId, fieldName, 'client_side');
                    }
                }
            });
        });

        observer.observe(field, { attributes: true });
    }

    trackFieldFocus(formId, fieldName) {
        this.logEvent('field_focus', {
            formId: formId,
            fieldName: fieldName,
            timestamp: Date.now()
        });
    }

    trackFieldBlur(formId, fieldName) {
        const formData = this.formInteractions.get(formId);
        const fieldData = formData.fields.get(fieldName);
        
        this.logEvent('field_blur', {
            formId: formId,
            fieldName: fieldName,
            focusTime: fieldData.focusTime,
            interactions: fieldData.interactions,
            finalValue: fieldData.finalValue ? 'has_value' : 'empty',
            timestamp: Date.now()
        });
    }

    trackFieldInteraction(formId, fieldName) {
        this.logEvent('field_interaction', {
            formId: formId,
            fieldName: fieldName,
            timestamp: Date.now()
        });
    }

    trackValidationError(formId, fieldName, errorType, errorMessage = '') {
        const formData = this.formInteractions.get(formId);
        const fieldData = formData.fields.get(fieldName);
        
        const errorData = {
            formId: formId,
            fieldName: fieldName,
            errorType: errorType,
            errorMessage: errorMessage,
            timestamp: Date.now()
        };

        fieldData.validationErrors.push(errorData);
        formData.validationErrors.push(errorData);
        this.validationErrors.push(errorData);

        this.logEvent('validation_error', errorData);
    }

    trackSubmissionAttempt(formId, wasSuccessful) {
        const formData = this.formInteractions.get(formId);
        
        const attemptData = {
            formId: formId,
            wasSuccessful: wasSuccessful,
            timestamp: Date.now(),
            totalErrors: formData.validationErrors.length,
            completionTime: Date.now() - formData.startTime,
            fieldInteractions: Array.from(formData.fields.values()).reduce((sum, field) => sum + field.interactions, 0)
        };

        formData.submissionAttempts.push(attemptData);
        this.submissionAttempts.push(attemptData);

        if (wasSuccessful) {
            formData.isCompleted = true;
        }

        this.logEvent('form_submission_attempt', attemptData);
    }

    trackAbandonment() {
        this.formInteractions.forEach((formData, formId) => {
            if (!formData.isCompleted && formData.totalInteractionTime > 10000) { // 10 seconds minimum
                const abandonmentData = {
                    formId: formId,
                    formType: formData.formType,
                    timeSpent: formData.totalInteractionTime,
                    fieldsInteracted: Array.from(formData.fields.values()).filter(f => f.hasBeenFocused).length,
                    totalFields: formData.fields.size,
                    lastInteractionTime: Date.now(),
                    abandonmentReason: this.detectAbandonmentReason(formData)
                };

                this.logEvent('form_abandonment', abandonmentData);
            }
        });
    }

    detectAbandonmentReason(formData) {
        const totalErrors = formData.validationErrors.length;
        const interactedFields = Array.from(formData.fields.values()).filter(f => f.hasBeenFocused).length;
        const totalFields = formData.fields.size;
        const completionPercentage = (interactedFields / totalFields) * 100;

        if (totalErrors > 5) return 'too_many_errors';
        if (completionPercentage < 25) return 'early_abandonment';
        if (completionPercentage > 75) return 'late_abandonment';
        return 'mid_form_abandonment';
    }

    startSessionTracking() {
        this.sessionStart = Date.now();
        
        // Track session data every 30 seconds
        setInterval(() => {
            this.trackSessionMetrics();
        }, 30000);
    }

    trackSessionMetrics() {
        const sessionData = {
            sessionId: this.sessionId,
            sessionDuration: Date.now() - this.sessionStart,
            formsStarted: this.formInteractions.size,
            formsCompleted: Array.from(this.formInteractions.values()).filter(f => f.isCompleted).length,
            totalValidationErrors: this.validationErrors.length,
            totalSubmissionAttempts: this.submissionAttempts.length,
            userAgent: navigator.userAgent,
            timestamp: Date.now()
        };

        this.logEvent('session_metrics', sessionData);
    }

    setupPerformanceMonitoring() {
        // Monitor form rendering performance
        if (window.performance && window.performance.observer) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.entryType === 'measure' && entry.name.includes('form')) {
                        this.logEvent('performance_metric', {
                            metric: entry.name,
                            duration: entry.duration,
                            timestamp: Date.now()
                        });
                    }
                });
            });

            observer.observe({ entryTypes: ['measure'] });
        }

        // Monitor long tasks that might affect form interaction
        if (window.PerformanceLongTaskTiming) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    this.logEvent('long_task', {
                        duration: entry.duration,
                        startTime: entry.startTime,
                        timestamp: Date.now()
                    });
                });
            });

            observer.observe({ entryTypes: ['longtask'] });
        }
    }

    logEvent(eventType, eventData) {
        if (!this.isEnabled) return;

        const logEntry = {
            eventType: eventType,
            eventData: eventData,
            sessionId: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href,
            userAgent: navigator.userAgent
        };

        // Store locally for batch sending
        this.storeEventLocally(logEntry);

        // Send to server if it's a critical event
        if (this.isCriticalEvent(eventType)) {
            this.sendEventToServer(logEntry);
        }
    }

    isCriticalEvent(eventType) {
        const criticalEvents = [
            'validation_error',
            'form_submission_attempt',
            'form_abandonment',
            'long_task'
        ];
        return criticalEvents.includes(eventType);
    }

    storeEventLocally(logEntry) {
        try {
            const stored = localStorage.getItem('creams_form_analytics') || '[]';
            const events = JSON.parse(stored);
            events.push(logEntry);

            // Keep only last 100 events to prevent storage overflow
            if (events.length > 100) {
                events.splice(0, events.length - 100);
            }

            localStorage.setItem('creams_form_analytics', JSON.stringify(events));
        } catch (error) {
            console.warn('Failed to store analytics event locally:', error);
        }
    }

    sendEventToServer(logEntry) {
        // Only send if we have a valid endpoint
        if (!window.CREAMS_ANALYTICS_ENDPOINT) return;

        fetch(window.CREAMS_ANALYTICS_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(logEntry)
        }).catch(error => {
            console.warn('Failed to send analytics event:', error);
        });
    }

    sendPendingAnalytics() {
        try {
            const stored = localStorage.getItem('creams_form_analytics');
            if (stored && window.CREAMS_ANALYTICS_ENDPOINT) {
                const events = JSON.parse(stored);
                if (events.length > 0) {
                    navigator.sendBeacon(
                        window.CREAMS_ANALYTICS_ENDPOINT + '/batch',
                        JSON.stringify({ events: events })
                    );
                    localStorage.removeItem('creams_form_analytics');
                }
            }
        } catch (error) {
            console.warn('Failed to send pending analytics:', error);
        }
    }

    // Public API methods
    getFormMetrics(formId) {
        return this.formInteractions.get(formId);
    }

    getAllFormMetrics() {
        return Object.fromEntries(this.formInteractions);
    }

    getValidationErrorSummary() {
        const summary = {};
        this.validationErrors.forEach(error => {
            const key = `${error.formId}_${error.fieldName}`;
            if (!summary[key]) {
                summary[key] = {
                    formId: error.formId,
                    fieldName: error.fieldName,
                    errorCount: 0,
                    errorTypes: new Set()
                };
            }
            summary[key].errorCount++;
            summary[key].errorTypes.add(error.errorType);
        });

        // Convert Set to Array for JSON serialization
        Object.values(summary).forEach(item => {
            item.errorTypes = Array.from(item.errorTypes);
        });

        return summary;
    }

    getSessionSummary() {
        return {
            sessionId: this.sessionId,
            sessionDuration: Date.now() - this.sessionStart,
            formsStarted: this.formInteractions.size,
            formsCompleted: Array.from(this.formInteractions.values()).filter(f => f.isCompleted).length,
            totalValidationErrors: this.validationErrors.length,
            totalSubmissionAttempts: this.submissionAttempts.length,
            abandonmentRate: this.calculateAbandonmentRate()
        };
    }

    calculateAbandonmentRate() {
        const totalForms = this.formInteractions.size;
        const completedForms = Array.from(this.formInteractions.values()).filter(f => f.isCompleted).length;
        return totalForms > 0 ? ((totalForms - completedForms) / totalForms) * 100 : 0;
    }

    // Enable/disable analytics
    enable() {
        this.isEnabled = true;
    }

    disable() {
        this.isEnabled = false;
    }

    // Reset analytics data
    reset() {
        this.formInteractions.clear();
        this.validationErrors = [];
        this.submissionAttempts = [];
        this.fieldFocusTime.clear();
        this.sessionStart = Date.now();
        localStorage.removeItem('creams_form_analytics');
    }
}

// Initialize analytics when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.creamsFormAnalytics = new FormAnalytics();
});

// Export for global use
window.FormAnalytics = FormAnalytics;