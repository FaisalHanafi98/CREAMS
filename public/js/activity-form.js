/**
 * Activity Forms JavaScript
 * Handles create and edit forms for activities
 */

class ActivityFormManager {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.formData = {};
        this.validationRules = {};
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeComponents();
        this.setupValidation();
        this.loadSavedData();
    }

    bindEvents() {
        // Wizard navigation
        document.querySelectorAll('.wizard-step-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const step = parseInt(link.dataset.step);
                this.goToStep(step);
            });
        });

        // Form navigation buttons
        document.getElementById('btnPrevious')?.addEventListener('click', () => this.previousStep());
        document.getElementById('btnNext')?.addEventListener('click', () => this.nextStep());
        document.getElementById('btnSave')?.addEventListener('click', () => this.saveForm());
        document.getElementById('btnSaveExit')?.addEventListener('click', () => this.saveAndExit());

        // Auto-save functionality
        this.setupAutoSave();

        // Dynamic lists
        this.setupDynamicLists();

        // File uploads
        this.setupFileUploads();

        // Category selector
        this.setupCategorySelector();
    }

    initializeComponents() {
        // Initialize sortable lists
        this.initializeSortable();

        // Setup rich text editors
        this.initializeEditors();

        // Initialize date/time pickers
        this.initializeDatePickers();

        // Setup tooltips and help
        this.initializeTooltips();
    }

    setupValidation() {
        this.validationRules = {
            step1: {
                name: { required: true, minLength: 3, maxLength: 255 },
                category: { required: true },
                short_description: { required: true, maxLength: 255 }
            },
            step2: {
                full_description: { required: true, minLength: 50 },
                objectives: { required: true, minItems: 1 }
            },
            step3: {
                difficulty_level: { required: true },
                age_range: { required: true },
                activity_type: { required: true },
                duration: { required: true, min: 5, max: 180 }
            },
            step4: {
                progress_metrics: { required: true, minLength: 20 }
            }
        };

        // Real-time validation
        document.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });
    }

    goToStep(step) {
        if (step < 1 || step > this.totalSteps) return;
        
        // Validate current step before moving
        if (step > this.currentStep && !this.validateCurrentStep()) {
            return;
        }

        // Save current step data
        this.saveStepData();

        // Hide current step
        document.querySelector(`#step${this.currentStep}`)?.classList.add('d-none');
        document.querySelector(`.wizard-step:nth-child(${this.currentStep})`)?.classList.remove('active');

        // Show new step
        this.currentStep = step;
        document.querySelector(`#step${this.currentStep}`)?.classList.remove('d-none');
        document.querySelector(`.wizard-step:nth-child(${this.currentStep})`)?.classList.add('active');

        // Update step indicators
        this.updateStepIndicators();

        // Update navigation buttons
        this.updateNavigationButtons();

        // Animate step transition
        this.animateStepTransition();

        // Focus first input in new step
        this.focusFirstInput();
    }

    nextStep() {
        if (this.validateCurrentStep()) {
            this.goToStep(this.currentStep + 1);
        }
    }

    previousStep() {
        this.goToStep(this.currentStep - 1);
    }

    validateCurrentStep() {
        const stepRules = this.validationRules[`step${this.currentStep}`];
        if (!stepRules) return true;

        let isValid = true;
        const stepElement = document.querySelector(`#step${this.currentStep}`);

        Object.keys(stepRules).forEach(fieldName => {
            const field = stepElement.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);
            if (field && !this.validateField(field, stepRules[fieldName])) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field, rules = null) {
        if (!rules) {
            const stepRules = this.validationRules[`step${this.currentStep}`];
            rules = stepRules?.[field.name];
        }

        if (!rules) return true;

        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (rules.required && !value) {
            isValid = false;
            errorMessage = 'This field is required.';
        }

        // Length validation
        if (isValid && rules.minLength && value.length < rules.minLength) {
            isValid = false;
            errorMessage = `Minimum length is ${rules.minLength} characters.`;
        }

        if (isValid && rules.maxLength && value.length > rules.maxLength) {
            isValid = false;
            errorMessage = `Maximum length is ${rules.maxLength} characters.`;
        }

        // Number validation
        if (isValid && rules.min && parseFloat(value) < rules.min) {
            isValid = false;
            errorMessage = `Minimum value is ${rules.min}.`;
        }

        if (isValid && rules.max && parseFloat(value) > rules.max) {
            isValid = false;
            errorMessage = `Maximum value is ${rules.max}.`;
        }

        // Array validation (for dynamic lists)
        if (rules.minItems && field.name.includes('[]')) {
            const items = document.querySelectorAll(`[name="${field.name}"]`);
            const filledItems = Array.from(items).filter(item => item.value.trim());
            if (filledItems.length < rules.minItems) {
                isValid = false;
                errorMessage = `Minimum ${rules.minItems} items required.`;
            }
        }

        this.showFieldValidation(field, isValid, errorMessage);
        return isValid;
    }

    showFieldValidation(field, isValid, errorMessage) {
        const formGroup = field.closest('.form-group');
        const feedback = formGroup?.querySelector('.invalid-feedback') || this.createFeedbackElement(formGroup);

        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid && field.value.trim());

        if (feedback) {
            feedback.textContent = errorMessage;
            feedback.style.display = !isValid ? 'block' : 'none';
        }
    }

    createFeedbackElement(formGroup) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        formGroup?.appendChild(feedback);
        return feedback;
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const formGroup = field.closest('.form-group');
        const feedback = formGroup?.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
    }

    setupDynamicLists() {
        // Objectives list
        this.setupDynamicList('objectives', 'Add Objective');
        
        // Resources list
        this.setupDynamicList('resources', 'Add Resource');
        
        // Steps list
        this.setupDynamicList('steps', 'Add Step');
    }

    setupDynamicList(listName, addButtonText) {
        const container = document.getElementById(`${listName}Container`);
        if (!container) return;

        const addButton = container.querySelector('.btn-add-item');
        if (addButton) {
            addButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.addListItem(listName, container);
            });
        }

        // Bind existing remove buttons
        container.querySelectorAll('.btn-remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeListItem(btn.closest('.list-item'));
            });
        });
    }

    addListItem(listName, container) {
        const template = container.querySelector('.list-item-template');
        if (!template) return;

        const clone = template.cloneNode(true);
        clone.classList.remove('list-item-template', 'd-none');
        clone.classList.add('list-item');

        // Update input names and IDs
        const inputs = clone.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            const currentName = input.name || input.id;
            if (currentName.includes('[]')) {
                input.name = currentName;
            } else {
                input.name = `${listName}[]`;
            }
            input.value = '';
        });

        // Bind remove button
        const removeBtn = clone.querySelector('.btn-remove-item');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeListItem(clone);
            });
        }

        // Insert before add button
        const addButton = container.querySelector('.btn-add-item');
        addButton.parentNode.insertBefore(clone, addButton);

        // Animate in
        clone.style.opacity = '0';
        clone.style.transform = 'translateY(-10px)';
        requestAnimationFrame(() => {
            clone.style.transition = 'all 0.3s ease';
            clone.style.opacity = '1';
            clone.style.transform = 'translateY(0)';
        });

        // Focus new input
        const firstInput = clone.querySelector('input, textarea');
        if (firstInput) firstInput.focus();
    }

    removeListItem(item) {
        item.style.transition = 'all 0.3s ease';
        item.style.opacity = '0';
        item.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            item.remove();
        }, 300);
    }

    setupFileUploads() {
        const uploadAreas = document.querySelectorAll('.file-upload');
        
        uploadAreas.forEach(area => {
            const input = area.querySelector('input[type="file"]');
            if (!input) return;

            // Click to upload
            area.addEventListener('click', () => input.click());

            // Drag and drop
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });

            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });

            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                
                const files = Array.from(e.dataTransfer.files);
                this.handleFileUpload(files, area);
            });

            // File input change
            input.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                this.handleFileUpload(files, area);
            });
        });
    }

    handleFileUpload(files, uploadArea) {
        const previewContainer = uploadArea.querySelector('.file-preview') || this.createPreviewContainer(uploadArea);
        
        files.forEach(file => {
            if (this.validateFile(file)) {
                this.createFilePreview(file, previewContainer);
            }
        });
    }

    validateFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            this.showNotification('Invalid file type. Please upload images or PDF files.', 'error');
            return false;
        }

        if (file.size > maxSize) {
            this.showNotification('File too large. Maximum size is 5MB.', 'error');
            return false;
        }

        return true;
    }

    createFilePreview(file, container) {
        const preview = document.createElement('div');
        preview.className = 'preview-item';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'preview-image';
            img.src = URL.createObjectURL(file);
            preview.appendChild(img);
        } else {
            const icon = document.createElement('div');
            icon.className = 'preview-icon';
            icon.innerHTML = '<i class="fas fa-file-pdf fa-3x"></i>';
            preview.appendChild(icon);
        }

        const removeBtn = document.createElement('button');
        removeBtn.className = 'preview-remove';
        removeBtn.innerHTML = '×';
        removeBtn.addEventListener('click', () => {
            preview.remove();
            URL.revokeObjectURL(preview.querySelector('img')?.src);
        });
        
        preview.appendChild(removeBtn);
        container.appendChild(preview);
    }

    createPreviewContainer(uploadArea) {
        const container = document.createElement('div');
        container.className = 'file-preview';
        uploadArea.appendChild(container);
        return container;
    }

    setupCategorySelector() {
        const categories = document.querySelectorAll('.category-option');
        const hiddenInput = document.getElementById('selectedCategory');

        categories.forEach(option => {
            option.addEventListener('click', () => {
                // Remove previous selection
                categories.forEach(cat => cat.classList.remove('selected'));
                
                // Select current
                option.classList.add('selected');
                
                // Update hidden input
                if (hiddenInput) {
                    hiddenInput.value = option.dataset.category;
                }

                // Trigger change event for validation
                hiddenInput?.dispatchEvent(new Event('change'));
            });
        });
    }

    setupAutoSave() {
        let saveTimeout;
        const autoSaveInterval = 30000; // 30 seconds

        // Auto-save on form changes
        document.addEventListener('input', () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                this.autoSave();
            }, 2000); // Save 2 seconds after last change
        });

        // Periodic auto-save
        setInterval(() => {
            this.autoSave();
        }, autoSaveInterval);

        // Save before page unload
        window.addEventListener('beforeunload', () => {
            this.saveStepData();
        });
    }

    autoSave() {
        this.saveStepData();
        const formData = this.collectFormData();
        
        if (Object.keys(formData).length > 0) {
            localStorage.setItem('activityFormData', JSON.stringify({
                data: formData,
                timestamp: Date.now(),
                currentStep: this.currentStep
            }));
            
            this.showNotification('Draft saved', 'info', 2000);
        }
    }

    loadSavedData() {
        const saved = localStorage.getItem('activityFormData');
        if (!saved) return;

        try {
            const { data, timestamp, currentStep } = JSON.parse(saved);
            
            // Check if data is recent (less than 24 hours old)
            if (Date.now() - timestamp < 24 * 60 * 60 * 1000) {
                this.populateForm(data);
                this.currentStep = currentStep || 1;
                this.goToStep(this.currentStep);
                
                this.showNotification('Draft restored', 'success');
            }
        } catch (error) {
            console.error('Failed to load saved data:', error);
        }
    }

    collectFormData() {
        const formData = {};
        const form = document.querySelector('form[data-validate="true"]');
        
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.name && input.value) {
                    if (input.name.endsWith('[]')) {
                        const baseName = input.name.slice(0, -2);
                        if (!formData[baseName]) formData[baseName] = [];
                        formData[baseName].push(input.value);
                    } else {
                        formData[input.name] = input.value;
                    }
                }
            });
        }
        
        return formData;
    }

    populateForm(data) {
        Object.keys(data).forEach(key => {
            const value = data[key];
            
            if (Array.isArray(value)) {
                // Handle array values (dynamic lists)
                value.forEach((item, index) => {
                    const input = document.querySelector(`[name="${key}[]"]:nth-of-type(${index + 1})`);
                    if (input) input.value = item;
                });
            } else {
                // Handle single values
                const input = document.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = value;
                    
                    // Trigger change event for category selector
                    if (key === 'category') {
                        const categoryOption = document.querySelector(`[data-category="${value}"]`);
                        if (categoryOption) {
                            categoryOption.click();
                        }
                    }
                }
            }
        });
    }

    saveForm() {
        if (this.validateAllSteps()) {
            const form = document.querySelector('form[data-validate="true"]');
            if (form) {
                // Clear auto-saved data
                localStorage.removeItem('activityFormData');
                
                // Show loading state
                this.showFormLoading(true);
                
                // Submit form
                form.submit();
            }
        }
    }

    saveAndExit() {
        this.autoSave();
        window.location.href = document.referrer || '/rehabilitation/categories';
    }

    validateAllSteps() {
        for (let step = 1; step <= this.totalSteps; step++) {
            const stepRules = this.validationRules[`step${step}`];
            if (!stepRules) continue;

            const stepElement = document.querySelector(`#step${step}`);
            let stepValid = true;

            Object.keys(stepRules).forEach(fieldName => {
                const field = stepElement.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                if (field && !this.validateField(field, stepRules[fieldName])) {
                    stepValid = false;
                }
            });

            if (!stepValid) {
                this.goToStep(step);
                this.showNotification(`Please complete Step ${step} before saving.`, 'error');
                return false;
            }
        }
        
        return true;
    }

    saveStepData() {
        const stepData = {};
        const currentStepElement = document.querySelector(`#step${this.currentStep}`);
        
        if (currentStepElement) {
            const inputs = currentStepElement.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.name && input.value) {
                    stepData[input.name] = input.value;
                }
            });
        }
        
        this.formData[`step${this.currentStep}`] = stepData;
    }

    updateStepIndicators() {
        const steps = document.querySelectorAll('.wizard-step');
        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            
            step.classList.toggle('active', stepNumber === this.currentStep);
            step.classList.toggle('completed', stepNumber < this.currentStep);
        });
    }

    updateNavigationButtons() {
        const btnPrevious = document.getElementById('btnPrevious');
        const btnNext = document.getElementById('btnNext');
        const btnSave = document.getElementById('btnSave');

        if (btnPrevious) {
            btnPrevious.style.display = this.currentStep === 1 ? 'none' : 'inline-block';
        }

        if (btnNext) {
            btnNext.style.display = this.currentStep === this.totalSteps ? 'none' : 'inline-block';
        }

        if (btnSave) {
            btnSave.style.display = this.currentStep === this.totalSteps ? 'inline-block' : 'none';
        }
    }

    animateStepTransition() {
        const currentStepElement = document.querySelector(`#step${this.currentStep}`);
        if (currentStepElement) {
            currentStepElement.classList.add('fade-in-up');
            setTimeout(() => {
                currentStepElement.classList.remove('fade-in-up');
            }, 600);
        }
    }

    focusFirstInput() {
        const currentStepElement = document.querySelector(`#step${this.currentStep}`);
        const firstInput = currentStepElement?.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    showFormLoading(show) {
        const form = document.querySelector('form[data-validate="true"]');
        if (form) {
            form.classList.toggle('form-loading', show);
            
            if (show && !form.querySelector('.loading-spinner')) {
                const spinner = document.createElement('div');
                spinner.className = 'loading-spinner';
                spinner.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
                form.appendChild(spinner);
            } else if (!show) {
                form.querySelector('.loading-spinner')?.remove();
            }
        }
    }

    initializeSortable() {
        // Initialize drag-and-drop sorting for lists
        const sortableLists = document.querySelectorAll('.sortable-list');
        
        sortableLists.forEach(list => {
            this.makeSortable(list);
        });
    }

    makeSortable(element) {
        let draggedElement = null;

        element.addEventListener('dragstart', (e) => {
            draggedElement = e.target.closest('.list-item');
            e.dataTransfer.effectAllowed = 'move';
        });

        element.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        element.addEventListener('drop', (e) => {
            e.preventDefault();
            const targetElement = e.target.closest('.list-item');
            
            if (targetElement && targetElement !== draggedElement) {
                const rect = targetElement.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    targetElement.parentNode.insertBefore(draggedElement, targetElement);
                } else {
                    targetElement.parentNode.insertBefore(draggedElement, targetElement.nextSibling);
                }
            }
        });
    }

    initializeEditors() {
        // Initialize rich text editors for description fields
        const editors = document.querySelectorAll('.rich-editor');
        
        editors.forEach(editor => {
            // Simple rich text editor implementation
            this.setupRichEditor(editor);
        });
    }

    setupRichEditor(textarea) {
        // Create toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'editor-toolbar';
        
        const buttons = [
            { icon: 'fas fa-bold', command: 'bold' },
            { icon: 'fas fa-italic', command: 'italic' },
            { icon: 'fas fa-list-ul', command: 'insertUnorderedList' },
            { icon: 'fas fa-list-ol', command: 'insertOrderedList' }
        ];
        
        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-outline-secondary';
            button.innerHTML = `<i class="${btn.icon}"></i>`;
            button.addEventListener('click', () => {
                document.execCommand(btn.command, false, null);
            });
            toolbar.appendChild(button);
        });
        
        textarea.parentNode.insertBefore(toolbar, textarea);
    }

    initializeDatePickers() {
        // Initialize date/time pickers if needed
        const datePickers = document.querySelectorAll('.date-picker');
        
        datePickers.forEach(picker => {
            // Simple date picker setup
            picker.type = 'date';
        });
    }

    initializeTooltips() {
        // Initialize tooltips
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        
        tooltipElements.forEach(element => {
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Tooltip(element);
            }
        });
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;

        document.body.appendChild(notification);

        if (duration > 0) {
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const activityForm = new ActivityFormManager();
    
    // Make available globally for debugging
    window.activityForm = activityForm;
});

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ActivityFormManager;
}