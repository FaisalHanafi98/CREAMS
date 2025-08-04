/**
 * Activity Creation Wizard JavaScript
 * Handles multi-step form navigation, validation, and dynamic content
 */

class ActivityWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 5;
        this.formData = {};
        this.learningOutcomeCounter = 0;
        this.prerequisiteCounter = 0;
        this.iepGoalCounter = 0;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeDynamicSections();
        this.addInitialLearningOutcome();
        this.updateNavigation();
    }
    
    bindEvents() {
        // Navigation events
        document.getElementById('nextStep').addEventListener('click', () => this.nextStep());
        document.getElementById('prevStep').addEventListener('click', () => this.prevStep());
        
        // Dynamic section events
        document.getElementById('addOutcome').addEventListener('click', () => this.addLearningOutcome());
        document.getElementById('addPrerequisite').addEventListener('click', () => this.addPrerequisite());
        document.getElementById('addIepGoal').addEventListener('click', () => this.addIepGoal());
        document.getElementById('getSuggestions').addEventListener('click', () => this.getSuggestedIepGoals());
        
        // Form field events
        document.getElementById('category_id').addEventListener('change', (e) => this.updateCategoryPreview(e.target.value));
        document.getElementById('template_id').addEventListener('change', (e) => this.updateTemplatePreview(e.target.value));
        document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.toggleScheduleType(e.target.value));
        });
        
        // Validation events
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
        });
        
        // Form submission
        document.getElementById('activityWizardForm').addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    initializeDynamicSections() {
        // Initialize empty containers
        document.getElementById('learningOutcomes').innerHTML = '';
        document.getElementById('prerequisites').innerHTML = '';
        document.getElementById('iepGoals').innerHTML = '';
    }
    
    // Navigation Methods
    nextStep() {
        if (this.validateCurrentStep()) {
            if (this.currentStep < this.totalSteps) {
                this.hideCurrentStep();
                this.currentStep++;
                this.showCurrentStep();
                this.updateNavigation();
                this.updateProgressSteps();
                
                // Special handling for final step
                if (this.currentStep === this.totalSteps) {
                    this.generateReviewSummary();
                }
            }
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.hideCurrentStep();
            this.currentStep--;
            this.showCurrentStep();
            this.updateNavigation();
            this.updateProgressSteps();
        }
    }
    
    hideCurrentStep() {
        const currentStepElement = document.querySelector(`.wizard-step[data-step="${this.currentStep}"]`);
        if (currentStepElement) {
            currentStepElement.style.display = 'none';
            currentStepElement.classList.remove('active');
        }
    }
    
    showCurrentStep() {
        const nextStepElement = document.querySelector(`.wizard-step[data-step="${this.currentStep}"]`);
        if (nextStepElement) {
            nextStepElement.style.display = 'block';
            nextStepElement.classList.add('active');
        }
    }
    
    updateNavigation() {
        const prevButton = document.getElementById('prevStep');
        const nextButton = document.getElementById('nextStep');
        const stepCounter = document.querySelector('.current-step');
        
        // Update step counter
        if (stepCounter) {
            stepCounter.textContent = this.currentStep;
        }
        
        // Update previous button
        if (this.currentStep === 1) {
            prevButton.style.display = 'none';
        } else {
            prevButton.style.display = 'inline-block';
        }
        
        // Update next button
        if (this.currentStep === this.totalSteps) {
            nextButton.style.display = 'none';
        } else {
            nextButton.style.display = 'inline-block';
            nextButton.innerHTML = this.currentStep === this.totalSteps - 1 ? 
                'Review <i class="fas fa-chevron-right"></i>' : 
                'Next <i class="fas fa-chevron-right"></i>';
        }
    }
    
    updateProgressSteps() {
        document.querySelectorAll('.step-item').forEach((item, index) => {
            const stepNumber = index + 1;
            item.classList.remove('active', 'completed');
            
            if (stepNumber < this.currentStep) {
                item.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                item.classList.add('active');
            }
        });
    }
    
    // Validation Methods
    validateCurrentStep() {
        switch (this.currentStep) {
            case 1:
                return this.validateBasicInformation();
            case 2:
                return this.validateLearningOutcomes();
            case 3:
                return this.validateScheduleConfiguration();
            case 4:
                return this.validatePrerequisites();
            case 5:
                return this.validateIepIntegration();
            default:
                return true;
        }
    }
    
    validateBasicInformation() {
        const requiredFields = [
            'activity_name', 'activity_description', 'category_id', 'centre_id',
            'activity_type', 'difficulty_level', 'min_participants', 'max_participants',
            'duration_minutes', 'age_group'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Additional validation
        const minParticipants = parseInt(document.getElementById('min_participants').value);
        const maxParticipants = parseInt(document.getElementById('max_participants').value);
        
        if (minParticipants && maxParticipants && minParticipants > maxParticipants) {
            this.showFieldError('max_participants', 'Maximum participants must be greater than or equal to minimum participants');
            isValid = false;
        }
        
        return isValid;
    }
    
    validateLearningOutcomes() {
        const outcomes = document.querySelectorAll('.learning-outcome-card');
        
        if (outcomes.length === 0) {
            this.showAlert('Please add at least one learning outcome.', 'warning');
            return false;
        }
        
        let isValid = true;
        
        outcomes.forEach((outcome, index) => {
            const title = outcome.querySelector('input[name*="outcome_title"]');
            const description = outcome.querySelector('textarea[name*="outcome_description"]');
            const competency = outcome.querySelector('select[name*="competency_level"]');
            
            if (!title.value.trim()) {
                this.showFieldError(title, 'Learning outcome title is required');
                isValid = false;
            }
            
            if (!description.value.trim()) {
                this.showFieldError(description, 'Learning outcome description is required');
                isValid = false;
            }
            
            if (!competency.value) {
                this.showFieldError(competency, 'Competency level is required');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateScheduleConfiguration() {
        const requiredFields = ['start_date', 'end_date', 'venue'];
        let isValid = true;
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !this.validateField(field)) {
                isValid = false;
            }
        });
        
        const scheduleType = document.querySelector('input[name="schedule_type"]:checked').value;
        
        if (scheduleType === 'template') {
            const templateId = document.getElementById('template_id');
            if (!templateId.value) {
                this.showFieldError(templateId, 'Please select a schedule template');
                isValid = false;
            }
        } else if (scheduleType === 'custom') {
            const customFields = ['sessions_per_week', 'session_length_minutes', 'session_time'];
            customFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field && !this.validateField(field)) {
                    isValid = false;
                }
            });
            
            const daysChecked = document.querySelectorAll('input[name="days_of_week[]"]:checked');
            if (daysChecked.length === 0) {
                this.showAlert('Please select at least one day of the week.', 'warning');
                isValid = false;
            }
        }
        
        // Date validation
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (startDate < today) {
            this.showFieldError('start_date', 'Start date cannot be in the past');
            isValid = false;
        }
        
        if (endDate <= startDate) {
            this.showFieldError('end_date', 'End date must be after start date');
            isValid = false;
        }
        
        return isValid;
    }
    
    validatePrerequisites() {
        // Prerequisites are optional, so always return true
        // But validate individual prerequisite cards if they exist
        const prerequisites = document.querySelectorAll('.prerequisite-card');
        let isValid = true;
        
        prerequisites.forEach(prerequisite => {
            const activitySelect = prerequisite.querySelector('select[name*="prerequisite_activity_id"]');
            const completionInput = prerequisite.querySelector('input[name*="minimum_completion_percentage"]');
            const competencySelect = prerequisite.querySelector('select[name*="required_competency_level"]');
            
            if (activitySelect && !activitySelect.value) {
                this.showFieldError(activitySelect, 'Please select a prerequisite activity');
                isValid = false;
            }
            
            if (completionInput && (!completionInput.value || completionInput.value < 0 || completionInput.value > 100)) {
                this.showFieldError(completionInput, 'Completion percentage must be between 0 and 100');
                isValid = false;
            }
            
            if (competencySelect && !competencySelect.value) {
                this.showFieldError(competencySelect, 'Please select required competency level');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateIepIntegration() {
        // IEP integration is optional, so always return true
        // But validate individual IEP goal cards if they exist
        const iepGoals = document.querySelectorAll('.iep-goal-card');
        let isValid = true;
        
        iepGoals.forEach(goal => {
            const iepSelect = goal.querySelector('select[name*="iep_id"]');
            const descriptionTextarea = goal.querySelector('textarea[name*="goal_description"]');
            const targetDateInput = goal.querySelector('input[name*="target_completion_date"]');
            const methodSelect = goal.querySelector('select[name*="progress_tracking_method"]');
            
            if (iepSelect && !iepSelect.value) {
                this.showFieldError(iepSelect, 'Please select an IEP plan');
                isValid = false;
            }
            
            if (descriptionTextarea && !descriptionTextarea.value.trim()) {
                this.showFieldError(descriptionTextarea, 'Goal description is required');
                isValid = false;
            }
            
            if (targetDateInput && !targetDateInput.value) {
                this.showFieldError(targetDateInput, 'Target completion date is required');
                isValid = false;
            }
            
            if (methodSelect && !methodSelect.value) {
                this.showFieldError(methodSelect, 'Progress tracking method is required');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        
        // Clear previous errors
        this.clearFieldError(field);
        
        if (isRequired && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        // Type-specific validation
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            this.showFieldError(field, 'Please enter a valid email address');
            return false;
        }
        
        if (field.type === 'number' && value) {
            const num = parseFloat(value);
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            
            if (min && num < parseFloat(min)) {
                this.showFieldError(field, `Value must be at least ${min}`);
                return false;
            }
            
            if (max && num > parseFloat(max)) {
                this.showFieldError(field, `Value must be at most ${max}`);
                return false;
            }
        }
        
        return true;
    }
    
    // Dynamic Content Methods
    addLearningOutcome() {
        this.learningOutcomeCounter++;
        const container = document.getElementById('learningOutcomes');
        const outcomeHtml = this.createLearningOutcomeCard(this.learningOutcomeCounter);
        container.insertAdjacentHTML('beforeend', outcomeHtml);
    }
    
    addInitialLearningOutcome() {
        this.addLearningOutcome();
    }
    
    createLearningOutcomeCard(index) {
        return `
            <div class="learning-outcome-card" data-index="${index}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Learning Outcome ${index}</h6>
                    ${index > 1 ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.learning-outcome-card').remove()"><i class="fas fa-times"></i></button>` : ''}
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label class="form-label required">Outcome Title</label>
                            <input type="text" class="form-control" name="learning_outcomes[${index}][outcome_title]" 
                                   placeholder="What will trainees achieve?" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label required">Description</label>
                            <textarea class="form-control" name="learning_outcomes[${index}][outcome_description]" 
                                    rows="3" placeholder="Detailed description of the learning outcome" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label required">Competency Level</label>
                            <select class="form-control" name="learning_outcomes[${index}][competency_level]" required>
                                <option value="">Select Level</option>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Assessment Criteria</label>
                    <div class="assessment-criteria">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="learning_outcomes[${index}][assessment_criteria][]" 
                                   placeholder="How will this outcome be measured?">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="addAssessmentCriteria(this)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    addPrerequisite() {
        this.prerequisiteCounter++;
        const container = document.getElementById('prerequisites');
        const prerequisiteHtml = this.createPrerequisiteCard(this.prerequisiteCounter);
        container.insertAdjacentHTML('beforeend', prerequisiteHtml);
    }
    
    createPrerequisiteCard(index) {
        const activities = window.availableActivities || [];
        let optionsHtml = '<option value="">Select Activity</option>';
        activities.forEach(activity => {
            optionsHtml += `<option value="${activity.id}">${activity.activity_name} (${activity.category})</option>`;
        });
        
        return `
            <div class="prerequisite-card" data-index="${index}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Prerequisite ${index}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.prerequisite-card').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label required">Required Activity</label>
                            <select class="form-control" name="prerequisites[${index}][prerequisite_activity_id]" required>
                                ${optionsHtml}
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label required">Min Completion %</label>
                            <input type="number" class="form-control" name="prerequisites[${index}][minimum_completion_percentage]" 
                                   min="0" max="100" value="80" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label required">Competency Level</label>
                            <select class="form-control" name="prerequisites[${index}][required_competency_level]" required>
                                <option value="">Select Level</option>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    addIepGoal() {
        this.iepGoalCounter++;
        const container = document.getElementById('iepGoals');
        const iepGoalHtml = this.createIepGoalCard(this.iepGoalCounter);
        container.insertAdjacentHTML('beforeend', iepGoalHtml);
    }
    
    createIepGoalCard(index) {
        const iepPlans = window.activeIepPlans || [];
        let optionsHtml = '<option value="">Select IEP Plan</option>';
        iepPlans.forEach(plan => {
            optionsHtml += `<option value="${plan.id}">${plan.trainee.trainee_name} - ${plan.plan_name}</option>`;
        });
        
        return `
            <div class="iep-goal-card" data-index="${index}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">IEP Goal ${index}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.iep-goal-card').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label required">IEP Plan</label>
                            <select class="form-control" name="iep_goals[${index}][iep_id]" required>
                                ${optionsHtml}
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label required">Target Date</label>
                            <input type="date" class="form-control" name="iep_goals[${index}][target_completion_date]" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label required">Tracking Method</label>
                            <select class="form-control" name="iep_goals[${index}][progress_tracking_method]" required>
                                <option value="">Select Method</option>
                                <option value="Attendance">Attendance</option>
                                <option value="Competency">Competency</option>
                                <option value="Assessment">Assessment</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Goal Description</label>
                    <textarea class="form-control" name="iep_goals[${index}][goal_description]" rows="3" 
                            placeholder="Describe what the trainee will achieve through this activity" required></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        `;
    }
    
    // Preview and Helper Methods
    updateCategoryPreview(categoryId) {
        if (!categoryId) {
            document.getElementById('categoryPreview').style.display = 'none';
            return;
        }
        
        // This would typically fetch category details via AJAX
        // For now, show a placeholder
        const preview = document.getElementById('categoryPreview');
        preview.style.display = 'block';
        preview.querySelector('.category-name').textContent = 'Loading...';
    }
    
    updateTemplatePreview(templateId) {
        if (!templateId) {
            document.getElementById('templatePreview').style.display = 'none';
            return;
        }
        
        // Fetch template details via AJAX
        fetch(`/activities/wizard/template-preview?template_id=${templateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.displayTemplatePreview(data.template);
                }
            })
            .catch(error => {
                console.error('Error fetching template preview:', error);
            });
    }
    
    displayTemplatePreview(template) {
        const preview = document.getElementById('templatePreview');
        preview.style.display = 'block';
        preview.querySelector('.template-details').innerHTML = `
            <strong>${template.name}</strong><br>
            <small class="text-muted">${template.description}</small><br>
            <div class="mt-2">
                <span class="badge badge-primary">${template.sessions_per_week}x per week</span>
                <span class="badge badge-info">${template.duration_weeks} weeks</span>
                <span class="badge badge-success">${template.total_sessions} total sessions</span>
            </div>
        `;
    }
    
    toggleScheduleType(type) {
        const templateSection = document.getElementById('templateSchedule');
        const customSection = document.getElementById('customSchedule');
        
        if (type === 'template') {
            templateSection.style.display = 'block';
            customSection.style.display = 'none';
        } else {
            templateSection.style.display = 'none';
            customSection.style.display = 'block';
        }
    }
    
    getSuggestedIepGoals() {
        const categoryId = document.getElementById('category_id').value;
        if (!categoryId) {
            this.showAlert('Please select an activity category first.', 'warning');
            return;
        }
        
        fetch(`/activities/wizard/suggested-iep-goals?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.suggestions.length > 0) {
                    this.displayIepSuggestions(data.suggestions);
                } else {
                    this.showAlert('No IEP goal suggestions available for this category.', 'info');
                }
            })
            .catch(error => {
                console.error('Error fetching IEP suggestions:', error);
                this.showAlert('Unable to fetch suggestions. Please try again.', 'error');
            });
    }
    
    displayIepSuggestions(suggestions) {
        // Implementation for displaying suggestions
        // This could be a modal or inline suggestions
        console.log('IEP Suggestions:', suggestions);
    }
    
    generateReviewSummary() {
        const container = document.getElementById('reviewSummary');
        if (!container) return;
        
        // Generate comprehensive review summary
        const formData = new FormData(document.getElementById('activityWizardForm'));
        
        let summaryHtml = '<div class="row">';
        
        // Basic Information Summary
        summaryHtml += `
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Activity Name:</dt>
                            <dd class="col-sm-8">${formData.get('activity_name') || 'Not specified'}</dd>
                            <dt class="col-sm-4">Type:</dt>
                            <dd class="col-sm-8">${formData.get('activity_type') || 'Not specified'}</dd>
                            <dt class="col-sm-4">Difficulty:</dt>
                            <dd class="col-sm-8">${formData.get('difficulty_level') || 'Not specified'}</dd>
                            <dt class="col-sm-4">Duration:</dt>
                            <dd class="col-sm-8">${formData.get('duration_minutes') || 'Not specified'} minutes</dd>
                            <dt class="col-sm-4">Participants:</dt>
                            <dd class="col-sm-8">${formData.get('min_participants') || '0'} - ${formData.get('max_participants') || '0'}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        `;
        
        // Learning Outcomes Summary
        const outcomeCards = document.querySelectorAll('.learning-outcome-card');
        summaryHtml += `
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap text-success"></i> Learning Outcomes</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>${outcomeCards.length}</strong> learning outcomes defined</p>
                        <ul class="list-unstyled">
        `;
        
        outcomeCards.forEach((card, index) => {
            const title = card.querySelector('input[name*="outcome_title"]').value;
            const level = card.querySelector('select[name*="competency_level"]').value;
            summaryHtml += `<li><i class="fas fa-check-circle text-success"></i> ${title} <span class="badge badge-info">${level}</span></li>`;
        });
        
        summaryHtml += `
                        </ul>
                    </div>
                </div>
            </div>
        `;
        
        summaryHtml += '</div>';
        
        container.innerHTML = summaryHtml;
    }
    
    // Utility Methods
    showAlert(message, type = 'info') {
        // Implementation for showing alerts
        const alertClass = type === 'error' ? 'danger' : type;
        const alertHtml = `
            <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        // Insert at the top of the current step
        const currentStep = document.querySelector(`.wizard-step[data-step="${this.currentStep}"]`);
        if (currentStep) {
            currentStep.insertAdjacentHTML('afterbegin', alertHtml);
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = currentStep.querySelector('.alert');
                if (alert) alert.remove();
            }, 5000);
        }
    }
    
    showFieldError(field, message) {
        if (typeof field === 'string') {
            field = document.getElementById(field);
        }
        
        if (field) {
            field.classList.add('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = message;
            }
        }
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = '';
        }
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    handleSubmit(e) {
        // Final validation before submission
        if (!this.validateCurrentStep()) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Activity...';
        }
        
        return true;
    }
}

// Global helper functions
function addAssessmentCriteria(button) {
    const container = button.closest('.assessment-criteria');
    const newInput = document.createElement('div');
    newInput.className = 'input-group mb-2';
    newInput.innerHTML = `
        <input type="text" class="form-control" name="${button.closest('.input-group').querySelector('input').name}" 
               placeholder="Additional assessment criteria">
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.input-group').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newInput);
}

// Initialize wizard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('activityWizardForm')) {
        window.activityWizard = new ActivityWizard();
    }
});