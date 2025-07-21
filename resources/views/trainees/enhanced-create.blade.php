@extends('layouts.app')

@section('title', 'Register New Trainee - CREAMS')

@section('styles')
<style>
    .form-step {
        display: none;
    }
    
    .form-step.active {
        display: block;
    }
    
    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }
    
    .step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 1rem;
        font-weight: bold;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .step.completed {
        background: #28a745;
        color: white;
    }
    
    .step.active {
        background: var(--primary-color);
        color: white;
        transform: scale(1.1);
    }
    
    .step::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 100%;
        width: 2rem;
        height: 2px;
        background: #e9ecef;
        transform: translateY(-50%);
    }
    
    .step:last-child::after {
        display: none;
    }
    
    .step.completed::after {
        background: #28a745;
    }
    
    .duplicate-warning {
        border: 2px solid #f39c12;
        border-radius: 0.5rem;
        background: #fef9e7;
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: #f8f9fa;
    }
    
    .file-upload-area.dragover {
        border-color: var(--primary-color);
        background: #e3f2fd;
    }
    
    .preview-image {
        max-width: 150px;
        max-height: 150px;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .medical-info-section {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    .tag-input {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.5rem;
        min-height: 38px;
        cursor: text;
    }
    
    .tag {
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .tag .remove {
        cursor: pointer;
        font-weight: bold;
    }
    
    .validation-error {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    .progress-bar-container {
        background: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .form-completion {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .completion-bar {
        height: 100%;
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        width: 0%;
        transition: width 0.3s ease;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus mr-2"></i>Register New Trainee
        </h1>
        <div>
            <a href="{{ route('enhanced-trainees.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-bar-container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold">Form Completion</span>
            <span id="completionPercentage">0%</span>
        </div>
        <div class="form-completion">
            <div class="completion-bar" id="completionBar"></div>
        </div>
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active" data-step="1">1</div>
        <div class="step" data-step="2">2</div>
        <div class="step" data-step="3">3</div>
        <div class="step" data-step="4">4</div>
        <div class="step" data-step="5">5</div>
    </div>

    <!-- Multi-step Form -->
    <div class="card shadow">
        <div class="card-body">
            <form id="traineeRegistrationForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Step 1: Basic Information -->
                <div class="form-step active" data-step="1">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-user mr-2"></i>Basic Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="required">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_of_birth" class="required">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gender" class="required">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Optional - for communication purposes</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="ic_number">IC Number</label>
                                <input type="text" class="form-control" id="ic_number" name="ic_number">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Home Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="admission_date" class="required">Admission Date</label>
                        <input type="date" class="form-control" id="admission_date" name="admission_date" 
                               value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Step 2: Condition & Medical Information -->
                <div class="form-step" data-step="2">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-heartbeat mr-2"></i>Condition & Medical Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="condition" class="required">Primary Condition</label>
                                <select class="form-control" id="condition" name="condition" required>
                                    <option value="">Select Condition</option>
                                    @foreach($conditions as $condition)
                                    <option value="{{ $condition }}">{{ $condition }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="medical_history">Medical History</label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="medical-info-section">
                        <h6 class="mb-3"><i class="fas fa-pills mr-2"></i>Detailed Medical Information</h6>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="medical_allergies">Allergies</label>
                                    <textarea class="form-control" id="medical_allergies" name="medical_allergies" rows="3" placeholder="List any known allergies..."></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="medications">Current Medications</label>
                                    <textarea class="form-control" id="medications" name="medications" rows="3" placeholder="List current medications..."></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dietary_restrictions">Dietary Restrictions</label>
                                    <textarea class="form-control" id="dietary_restrictions" name="dietary_restrictions" rows="3" placeholder="Any dietary needs or restrictions..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Guardian Information -->
                <div class="form-step" data-step="3">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-users mr-2"></i>Guardian Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guardian_name" class="required">Guardian Full Name</label>
                                <input type="text" class="form-control" id="guardian_name" name="guardian_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="guardian_relationship" class="required">Relationship</label>
                                <select class="form-control" id="guardian_relationship" name="guardian_relationship" required>
                                    <option value="">Select Relationship</option>
                                    <option value="Parent">Parent</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Grandparent">Grandparent</option>
                                    <option value="Sibling">Sibling</option>
                                    <option value="Uncle/Aunt">Uncle/Aunt</option>
                                    <option value="Cousin">Cousin</option>
                                    <option value="Family Friend">Family Friend</option>
                                    <option value="Legal Guardian">Legal Guardian</option>
                                    <option value="Foster Parent">Foster Parent</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="guardian_phone" class="required">Phone Number</label>
                                <input type="tel" class="form-control" id="guardian_phone" name="guardian_phone" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guardian_email">Email Address</label>
                                <input type="email" class="form-control" id="guardian_email" name="guardian_email">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guardian_address">Address</label>
                                <textarea class="form-control" id="guardian_address" name="guardian_address" rows="2"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-phone-alt mr-2"></i>Emergency Contact</h6>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="emergency_contact_name" class="required">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="emergency_contact_phone" class="required">Phone Number</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="emergency_contact_relationship" class="required">Relationship</label>
                                <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Photo & Documents -->
                <div class="form-step" data-step="4">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-camera mr-2"></i>Photo & Documents
                    </h5>
                    
                    <!-- Avatar Upload -->
                    <div class="form-group">
                        <label for="avatar">Trainee Photo</label>
                        <div class="file-upload-area" id="avatarUpload">
                            <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Click to upload photo or drag and drop</p>
                            <small class="text-muted">JPG, PNG up to 2MB</small>
                            <input type="file" class="d-none" id="avatar" name="avatar" accept="image/*">
                        </div>
                        <div class="mt-3" id="avatarPreview" style="display: none;">
                            <img id="avatarImage" class="preview-image" src="" alt="Preview">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Document Upload -->
                    <div class="form-group mt-4">
                        <label for="documents">Supporting Documents</label>
                        <div class="file-upload-area" id="documentsUpload">
                            <i class="fas fa-file-upload fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Click to upload documents or drag and drop</p>
                            <small class="text-muted">Birth certificate, medical reports, etc. (PDF, DOC, JPG up to 5MB each)</small>
                            <input type="file" class="d-none" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <div class="mt-3" id="documentsPreview"></div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Step 5: Consent & Additional Information -->
                <div class="form-step" data-step="5">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-check-circle mr-2"></i>Consent & Additional Information
                    </h5>
                    
                    <!-- Tags -->
                    <div class="form-group">
                        <label for="tagInput">Tags (Optional)</label>
                        <div class="tag-input" id="tagContainer">
                            <input type="text" id="tagInput" placeholder="Add tags (press Enter)..." style="border: none; outline: none; flex: 1;">
                        </div>
                        <small class="form-text text-muted">Add tags like "special needs", "high priority", etc. Press Enter to add each tag.</small>
                        <input type="hidden" name="tags" id="tagsHidden">
                    </div>
                    
                    <!-- Consent Checkboxes -->
                    <div class="card mt-4" style="background: #f8f9fc;">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-shield-alt mr-2"></i>Consent & Permissions
                            </h6>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="services_consent" name="services_consent" required>
                                <label class="form-check-label" for="services_consent">
                                    <strong>I consent to enrollment and participation in rehabilitation services</strong>
                                    <br><small class="text-muted">This consent is required for registration</small>
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="photo_consent" name="photo_consent">
                                <label class="form-check-label" for="photo_consent">
                                    I consent to the use of photos/videos for educational and promotional purposes
                                    <br><small class="text-muted">This is optional and can be changed later</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Verification -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle mr-2"></i>Data Verification</h6>
                        <p class="mb-0">Please review all information carefully before submitting. Once registered, some information may require admin approval to modify.</p>
                    </div>
                </div>

                <!-- Duplicate Warning (Hidden by default) -->
                <div class="duplicate-warning" id="duplicateWarning" style="display: none;">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i>Potential Duplicate Detected</h6>
                    <p>We found similar trainees in the system. Please verify this is not a duplicate registration:</p>
                    <div id="duplicateList"></div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-warning" onclick="proceedWithDuplicate()">
                            <i class="fas fa-check mr-1"></i>Proceed Anyway
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="hideDuplicateWarning()">
                            <i class="fas fa-edit mr-1"></i>Edit Information
                        </button>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                        <i class="fas fa-arrow-left mr-1"></i>Previous
                    </button>
                    
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                            Next <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                            <i class="fas fa-save mr-1"></i>Register Trainee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentStep = 1;
const totalSteps = 5;
let tags = [];
let duplicateCheckPassed = false;

$(document).ready(function() {
    // File upload handling
    setupFileUploads();
    
    // Tag system
    setupTagSystem();
    
    // Form validation
    setupFormValidation();
    
    // Auto-fill guardian info from trainee info
    $('#name, #date_of_birth, #guardian_phone').on('change', debounce(checkForDuplicates, 500));
    
    // Update completion percentage
    $('input, select, textarea').on('input change', updateCompletion);
    
    updateStepDisplay();
    updateCompletion();
});

function changeStep(direction) {
    const targetStep = currentStep + direction;
    
    if (direction > 0 && !validateCurrentStep()) {
        return false;
    }
    
    if (targetStep < 1 || targetStep > totalSteps) {
        return false;
    }
    
    // Special handling for step 3 to 4 (duplicate check)
    if (currentStep === 3 && direction > 0 && !duplicateCheckPassed) {
        checkForDuplicates();
        return false;
    }
    
    currentStep = targetStep;
    updateStepDisplay();
    updateCompletion();
}

function updateStepDisplay() {
    // Hide all steps
    $('.form-step').removeClass('active');
    $('.step').removeClass('active completed');
    
    // Show current step
    $(`.form-step[data-step="${currentStep}"]`).addClass('active');
    $(`.step[data-step="${currentStep}"]`).addClass('active');
    
    // Mark completed steps
    for (let i = 1; i < currentStep; i++) {
        $(`.step[data-step="${i}"]`).addClass('completed');
    }
    
    // Update navigation buttons
    $('#prevBtn').toggle(currentStep > 1);
    $('#nextBtn').toggle(currentStep < totalSteps);
    $('#submitBtn').toggle(currentStep === totalSteps);
    
    // Scroll to top
    $('html, body').animate({ scrollTop: 0 }, 300);
}

function validateCurrentStep() {
    const currentStepElement = $(`.form-step[data-step="${currentStep}"]`);
    const requiredFields = currentStepElement.find('[required]');
    let isValid = true;
    
    requiredFields.each(function() {
        const field = $(this);
        const value = field.val().trim();
        
        field.removeClass('validation-error');
        field.siblings('.invalid-feedback').text('');
        
        if (!value) {
            field.addClass('validation-error');
            field.siblings('.invalid-feedback').text('This field is required.');
            isValid = false;
        }
        
        // Additional validation
        if (field.attr('type') === 'email' && value && !isValidEmail(value)) {
            field.addClass('validation-error');
            field.siblings('.invalid-feedback').text('Please enter a valid email address.');
            isValid = false;
        }
        
        if (field.attr('name') === 'date_of_birth' && value) {
            const birthDate = new Date(value);
            const today = new Date();
            if (birthDate >= today) {
                field.addClass('validation-error');
                field.siblings('.invalid-feedback').text('Date of birth must be in the past.');
                isValid = false;
            }
        }
    });
    
    if (!isValid) {
        showAlert('error', 'Please fill in all required fields correctly.');
    }
    
    return isValid;
}

function updateCompletion() {
    const allFields = $('input:not([type="hidden"]), select, textarea');
    const filledFields = allFields.filter(function() {
        return $(this).val().trim() !== '';
    });
    
    const percentage = Math.round((filledFields.length / allFields.length) * 100);
    $('#completionPercentage').text(percentage + '%');
    $('#completionBar').css('width', percentage + '%');
}

function setupFileUploads() {
    // Avatar upload
    $('#avatarUpload').on('click', function() {
        $('#avatar').click();
    });
    
    $('#avatar').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatarImage').attr('src', e.target.result);
                $('#avatarPreview').show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Documents upload
    $('#documentsUpload').on('click', function() {
        $('#documents').click();
    });
    
    $('#documents').on('change', function() {
        const files = Array.from(this.files);
        const preview = $('#documentsPreview');
        preview.empty();
        
        files.forEach(function(file, index) {
            const fileDiv = $(`
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                    <div>
                        <i class="fas fa-file mr-2"></i>
                        <span>${file.name}</span>
                        <small class="text-muted">(${formatFileSize(file.size)})</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDocument(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            preview.append(fileDiv);
        });
    });
    
    // Drag and drop
    $('.file-upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $('.file-upload-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $('.file-upload-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        const input = $(this).find('input[type="file"]')[0];
        input.files = files;
        $(input).trigger('change');
    });
}

function setupTagSystem() {
    $('#tagInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            addTag($(this).val().trim());
            $(this).val('');
        }
    });
    
    $('#tagContainer').on('click', function() {
        $('#tagInput').focus();
    });
}

function addTag(tagText) {
    if (!tagText || tags.includes(tagText)) return;
    
    tags.push(tagText);
    
    const tagElement = $(`
        <div class="tag">
            <span>${tagText}</span>
            <span class="remove" onclick="removeTag('${tagText}')">&times;</span>
        </div>
    `);
    
    $('#tagInput').before(tagElement);
    $('#tagsHidden').val(JSON.stringify(tags));
}

function removeTag(tagText) {
    tags = tags.filter(tag => tag !== tagText);
    $('#tagsHidden').val(JSON.stringify(tags));
    
    // Remove from DOM
    $('.tag').each(function() {
        if ($(this).find('span:first').text() === tagText) {
            $(this).remove();
        }
    });
}

function setupFormValidation() {
    $('#traineeRegistrationForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateCurrentStep()) {
            return false;
        }
        
        submitForm();
    });
}

function checkForDuplicates() {
    const name = $('#name').val().trim();
    const dateOfBirth = $('#date_of_birth').val();
    const guardianPhone = $('#guardian_phone').val().trim();
    
    if (!name || !dateOfBirth || !guardianPhone) {
        return;
    }
    
    $.post('{{ route("enhanced-trainees.store") }}', {
        _token: '{{ csrf_token() }}',
        name: name,
        date_of_birth: dateOfBirth,
        guardian_phone: guardianPhone,
        check_duplicates: true
    })
    .done(function(response) {
        if (response.duplicate_warning) {
            showDuplicateWarning(response.duplicates);
        } else {
            duplicateCheckPassed = true;
            changeStep(1);
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 409) { // Duplicate detected
            const response = JSON.parse(xhr.responseText);
            showDuplicateWarning(response.duplicates);
        } else {
            duplicateCheckPassed = true;
            changeStep(1);
        }
    });
}

function showDuplicateWarning(duplicates) {
    const list = $('#duplicateList');
    list.empty();
    
    duplicates.forEach(function(trainee) {
        const item = $(`
            <div class="border rounded p-2 mb-2">
                <strong>${trainee.name}</strong> - Born: ${trainee.date_of_birth}<br>
                <small>ID: ${trainee.id} | Status: ${trainee.status} | Guardian: ${trainee.guardian_phone}</small>
            </div>
        `);
        list.append(item);
    });
    
    $('#duplicateWarning').show();
    $('html, body').animate({ scrollTop: $('#duplicateWarning').offset().top - 100 }, 300);
}

function hideDuplicateWarning() {
    $('#duplicateWarning').hide();
}

function proceedWithDuplicate() {
    duplicateCheckPassed = true;
    hideDuplicateWarning();
    changeStep(1);
}

function submitForm() {
    const formData = new FormData($('#traineeRegistrationForm')[0]);
    
    // Show loading state
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Registering...');
    
    $.ajax({
        url: '{{ route("enhanced-trainees.store") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Trainee registered successfully!');
                setTimeout(function() {
                    window.location.href = '{{ route("enhanced-trainees.index") }}';
                }, 2000);
            } else {
                showAlert('error', response.message || 'Registration failed.');
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Register Trainee');
            }
        },
        error: function(xhr) {
            const response = JSON.parse(xhr.responseText);
            
            if (response.errors) {
                // Handle validation errors
                Object.keys(response.errors).forEach(function(field) {
                    const fieldElement = $(`[name="${field}"]`);
                    fieldElement.addClass('validation-error');
                    fieldElement.siblings('.invalid-feedback').text(response.errors[field][0]);
                });
                
                showAlert('error', 'Please correct the highlighted errors.');
            } else {
                showAlert('error', response.message || 'Registration failed.');
            }
            
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Register Trainee');
        }
    });
}

function removeDocument(index) {
    // This is a simplified implementation
    // In a real application, you'd need to handle file removal more carefully
    showAlert('info', 'Please re-select your documents.');
    $('#documents').val('');
    $('#documentsPreview').empty();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
    const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    $('.container-fluid').prepend(alert);
    
    setTimeout(function() {
        alert.alert('close');
    }, 5000);
    
    $('html, body').animate({ scrollTop: 0 }, 300);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endsection