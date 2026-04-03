@extends('layouts.app')

@section('title', 'Create Letter')

@section('content')
<div class="modern-letter-creator">
    <!-- Global Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Success!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Warning!</strong> {{ session('warning') }}
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Please correct the following errors:</strong>
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <!-- Header -->
    <div class="creator-header">
        <div class="header-content">
            <div class="header-nav">
                <a href="{{ route('letters.modern.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            <div class="header-title">
                <h1><i class="fas fa-edit"></i> Create New Letter</h1>
                <p>Design professional letters with our intelligent template system</p>
            </div>
        </div>
    </div>

    <!-- Letter Creation Form -->
    <form action="{{ route('letters.modern.generate') }}" method="POST" id="letterCreationForm">
        @csrf
        
        <div class="creator-body">
            <!-- Step Navigation -->
            <div class="step-navigation">
                <div class="step-item active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Template</div>
                </div>
                <div class="step-line"></div>
                <div class="step-item" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Recipient</div>
                </div>
                <div class="step-line"></div>
                <div class="step-item" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Content</div>
                </div>
                <div class="step-line"></div>
                <div class="step-item" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Review</div>
                </div>
            </div>

            <!-- Step 1: Template Selection -->
            <div class="step-content" id="step1">
                <div class="step-header">
                    <h3><i class="fas fa-file-alt"></i> Choose Template</h3>
                    <p>Select a template that best fits your letter purpose</p>
                </div>
                
                <div class="template-selection-grid">
                    @foreach($templates as $template)
                    <div class="template-option" data-template-id="{{ $template->id }}">
                        <div class="template-icon">
                            <i class="{{ $template->template_icon ?? 'fas fa-file-alt' }}"></i>
                        </div>
                        <div class="template-info">
                            <h4>{{ $template->template_name }}</h4>
                            <p>{{ $template->template_description }}</p>
                            <div class="template-category">{{ ucfirst($template->template_category) }}</div>
                        </div>
                        <div class="template-preview-btn">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewTemplate({{ $template->id }})">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <input type="hidden" name="template_id" id="selectedTemplateId" value="{{ $template->id ?? '' }}">
            </div>

            <!-- Step 2: Recipient Information -->
            <div class="step-content" id="step2" style="display: none;">
                <div class="step-header">
                    <h3><i class="fas fa-user"></i> Recipient Information</h3>
                    <p>Specify who will receive this letter</p>
                </div>
                
                <div class="recipient-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipientType">Recipient Type <span class="required">*</span></label>
                                <select name="recipient_type" id="recipientType" class="form-control @error('recipient_type') is-invalid @enderror" required>
                                    <option value="">Select recipient type...</option>
                                    <option value="trainee" {{ old('recipient_type') == 'trainee' ? 'selected' : '' }}>Trainee</option>
                                    <option value="guardian" {{ old('recipient_type') == 'guardian' ? 'selected' : '' }}>Guardian/Parent</option>
                                    <option value="staff" {{ old('recipient_type') == 'staff' ? 'selected' : '' }}>Staff Member</option>
                                    <option value="external" {{ old('recipient_type') == 'external' ? 'selected' : '' }}>External Contact</option>
                                </select>
                                @error('recipient_type')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group" id="recipientSelectGroup" style="display: none;">
                                <label for="recipientSelect">Select Recipient <span class="required">*</span></label>
                                <select name="recipient_id" id="recipientSelect" class="form-control">
                                    <option value="">Choose recipient...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipientName">Full Name <span class="required">*</span></label>
                                <input type="text" name="recipient_name" id="recipientName" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipientEmail">Email Address</label>
                                <input type="email" name="recipient_email" id="recipientEmail" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipientPhone">Phone Number</label>
                                <input type="text" name="recipient_phone" id="recipientPhone" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="letterPriority">Letter Priority <span class="required">*</span></label>
                                <select name="letter_priority" id="letterPriority" class="form-control" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High Priority</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="recipientAddress">Mailing Address <span class="required">*</span></label>
                        <textarea name="recipient_address" id="recipientAddress" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 3: Letter Content -->
            <div class="step-content" id="step3" style="display: none;">
                <div class="step-header">
                    <h3><i class="fas fa-edit"></i> Letter Content</h3>
                    <p>Compose your letter content with template variables</p>
                </div>
                
                <div class="content-editor">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="letterSubject">Letter Subject <span class="required">*</span></label>
                                <input type="text" name="letter_subject" id="letterSubject" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="letterContent">Letter Content <span class="required">*</span></label>
                                <div class="editor-toolbar">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertVariable('@{{recipient_name}}')">
                                        <i class="fas fa-user"></i> Recipient Name
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertVariable('@{{current_date}}')">
                                        <i class="fas fa-calendar"></i> Current Date
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertVariable('@{{centre_name}}')">
                                        <i class="fas fa-building"></i> Centre Name
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertVariable('@{{sender_name}}')">
                                        <i class="fas fa-signature"></i> Your Name
                                    </button>
                                </div>
                                <textarea name="letter_content" id="letterContent" class="form-control content-editor-area" rows="12" required></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="template-preview-panel">
                                <h5><i class="fas fa-eye"></i> Live Preview</h5>
                                <div id="contentPreview" class="preview-content">
                                    <p class="text-muted">Start typing to see preview...</p>
                                </div>
                            </div>
                            
                            <div class="variable-helper">
                                <h6><i class="fas fa-code"></i> Available Variables</h6>
                                <div class="variable-list">
                                    <div class="variable-item" onclick="insertVariable('@{{recipient_name}}')">
                                        <code>@{{recipient_name}}</code>
                                        <span>Recipient's full name</span>
                                    </div>
                                    <div class="variable-item" onclick="insertVariable('@{{recipient_address}}')">
                                        <code>@{{recipient_address}}</code>
                                        <span>Recipient's address</span>
                                    </div>
                                    <div class="variable-item" onclick="insertVariable('@{{current_date}}')">
                                        <code>@{{current_date}}</code>
                                        <span>Today's date</span>
                                    </div>
                                    <div class="variable-item" onclick="insertVariable('@{{centre_name}}')">
                                        <code>@{{centre_name}}</code>
                                        <span>Centre name</span>
                                    </div>
                                    <div class="variable-item" onclick="insertVariable('@{{sender_name}}')">
                                        <code>@{{sender_name}}</code>
                                        <span>Your name</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Review and Generate -->
            <div class="step-content" id="step4" style="display: none;">
                <div class="step-header">
                    <h3><i class="fas fa-check-circle"></i> Review & Generate</h3>
                    <p>Review your letter details before generating</p>
                </div>
                
                <div class="review-section">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="review-preview">
                                <h5>Letter Preview</h5>
                                <div id="finalPreview" class="final-preview-content">
                                    <!-- Final preview will be populated here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="review-details">
                                <h5>Letter Details</h5>
                                <div class="detail-item">
                                    <strong>Template:</strong>
                                    <span id="reviewTemplate">-</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Recipient:</strong>
                                    <span id="reviewRecipient">-</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Subject:</strong>
                                    <span id="reviewSubject">-</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Priority:</strong>
                                    <span id="reviewPriority">-</span>
                                </div>
                            </div>
                            
                            <div class="delivery-options">
                                <h6>Delivery Options</h6>
                                <div class="form-group">
                                    <label for="deliveryMethod">Delivery Method <span class="required">*</span></label>
                                    <select name="delivery_method" id="deliveryMethod" class="form-control" required>
                                        <option value="print">Print Only</option>
                                        <option value="email">Email Only</option>
                                        <option value="both">Print & Email</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="sendCopyTo">Send Copy To (Email)</label>
                                    <input type="email" name="send_copy_to" id="sendCopyTo" class="form-control" placeholder="Optional CC email">
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes">Internal Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Internal notes (not included in letter)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="step-navigation-buttons">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                
                <button type="button" class="btn btn-primary" id="nextBtn">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="submit" class="btn btn-success" id="generateBtn" style="display: none;">
                    <i class="fas fa-magic"></i> Generate Letter
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Template Preview Modal -->
<div class="modal fade" id="templatePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="templatePreviewContent">
                    Loading preview...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="selectCurrentTemplate()">Use This Template</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.modern-letter-creator {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.creator-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.header-nav {
    margin-bottom: 20px;
}

.header-title h1 {
    font-size: 2.5rem;
    margin: 0;
    font-weight: 700;
}

.header-title p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 10px 0 0 0;
}

.creator-body {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.step-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 40px;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #718096;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-item.active .step-number {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.step-item.completed .step-number {
    background: #48bb78;
    color: white;
}

.step-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #4a5568;
}

.step-line {
    width: 100px;
    height: 2px;
    background: #e2e8f0;
    margin: 0 20px;
}

.step-content {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.step-header {
    text-align: center;
    margin-bottom: 40px;
}

.step-header h3 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 10px;
}

.step-header p {
    color: #718096;
    font-size: 1.1rem;
}

.template-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.template-option {
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    padding: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.template-option:hover {
    border-color: #667eea;
    background: #f7fafc;
    transform: translateY(-2px);
}

.template-option.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, #667eea15, #764ba215);
}

.template-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-right: 20px;
}

.template-info {
    flex: 1;
}

.template-info h4 {
    font-weight: 600;
    margin-bottom: 8px;
    color: #2d3748;
}

.template-info p {
    color: #718096;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.template-category {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.recipient-form .form-group {
    margin-bottom: 25px;
}

.form-group label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.required {
    color: #e53e3e;
}

.content-editor-area {
    min-height: 300px;
    font-family: 'Georgia', serif;
    line-height: 1.6;
}

.editor-toolbar {
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.template-preview-panel {
    background: #f7fafc;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.preview-content {
    background: white;
    border-radius: 8px;
    padding: 15px;
    min-height: 200px;
    font-family: 'Georgia', serif;
    line-height: 1.6;
    border: 1px solid #e2e8f0;
}

.variable-helper {
    background: #f7fafc;
    border-radius: 10px;
    padding: 20px;
}

.variable-helper h6 {
    font-weight: 600;
    margin-bottom: 15px;
    color: #2d3748;
}

.variable-item {
    display: flex;
    flex-direction: column;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
    margin-bottom: 5px;
}

.variable-item:hover {
    background: #e2e8f0;
}

.variable-item code {
    background: #667eea;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-bottom: 2px;
}

.variable-item span {
    font-size: 0.8rem;
    color: #718096;
}

.review-preview {
    background: #f7fafc;
    border-radius: 10px;
    padding: 20px;
}

.final-preview-content {
    background: white;
    border-radius: 8px;
    padding: 20px;
    min-height: 300px;
    font-family: 'Georgia', serif;
    line-height: 1.6;
    border: 1px solid #e2e8f0;
}

.review-details {
    background: #f7fafc;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}

.delivery-options {
    background: #f7fafc;
    border-radius: 10px;
    padding: 20px;
}

.step-navigation-buttons {
    display: flex;
    justify-content: space-between;
    background: white;
    padding: 20px 40px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .template-selection-grid {
        grid-template-columns: 1fr;
    }
    
    .template-option {
        flex-direction: column;
        text-align: center;
    }
    
    .template-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .step-navigation {
        flex-direction: column;
        gap: 20px;
    }
    
    .step-line {
        width: 2px;
        height: 30px;
        margin: 10px 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentStep = 1;
let totalSteps = 4;
let selectedTemplateId = null;
let templatePreviewData = null;

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Pre-select template if passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const templateId = urlParams.get('template_id');
    if (templateId) {
        selectTemplate(templateId);
    }
    
    // Setup event listeners
    setupEventListeners();
    updateNavigationButtons();
});

function setupEventListeners() {
    // Template selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            selectTemplate(templateId);
        });
    });
    
    // Recipient type change
    document.getElementById('recipientType').addEventListener('change', function() {
        handleRecipientTypeChange(this.value);
    });
    
    // Content editor live preview
    document.getElementById('letterContent').addEventListener('input', function() {
        updateContentPreview();
    });
    
    // Navigation buttons
    document.getElementById('nextBtn').addEventListener('click', nextStep);
    document.getElementById('prevBtn').addEventListener('click', prevStep);
    
    // Form submission
    document.getElementById('letterCreationForm').addEventListener('submit', function(e) {
        if (currentStep !== totalSteps) {
            e.preventDefault();
            nextStep();
        }
    });
}

function selectTemplate(templateId) {
    // Remove previous selections
    document.querySelectorAll('.template-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Select current template
    const templateOption = document.querySelector(`[data-template-id="${templateId}"]`);
    if (templateOption) {
        templateOption.classList.add('selected');
        selectedTemplateId = templateId;
        document.getElementById('selectedTemplateId').value = templateId;
        
        // Load template content
        loadTemplateContent(templateId);
    }
}

function loadTemplateContent(templateId) {
    fetch(`/letters/modern/template-preview/${templateId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                templatePreviewData = data;
                document.getElementById('letterContent').value = data.content;
                updateContentPreview();
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
        });
}

function previewTemplate(templateId) {
    fetch(`/letters/modern/template-preview/${templateId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('templatePreviewContent').innerHTML = data.content;
                selectedTemplateId = templateId;
                $('#templatePreviewModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error loading template preview:', error);
            alert('Failed to load template preview');
        });
}

function selectCurrentTemplate() {
    if (selectedTemplateId) {
        selectTemplate(selectedTemplateId);
        $('#templatePreviewModal').modal('hide');
    }
}

function handleRecipientTypeChange(type) {
    const selectGroup = document.getElementById('recipientSelectGroup');
    const recipientSelect = document.getElementById('recipientSelect');
    
    if (type === 'trainee' || type === 'staff') {
        selectGroup.style.display = 'block';
        // Load appropriate options based on type
        loadRecipientOptions(type);
    } else {
        selectGroup.style.display = 'none';
        recipientSelect.innerHTML = '<option value="">Choose recipient...</option>';
    }
}

function loadRecipientOptions(type) {
    const recipientSelect = document.getElementById('recipientSelect');
    
    // This would typically fetch from an API endpoint
    // For now, we'll use the trainees data passed from the controller
    @if(isset($trainees))
    if (type === 'trainee') {
        recipientSelect.innerHTML = '<option value="">Choose trainee...</option>';
        @foreach($trainees as $trainee)
        recipientSelect.innerHTML += '<option value="{{ $trainee->id }}" data-name="{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}" data-address="{{ $trainee->trainee_address }}">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</option>';
        @endforeach
    }
    @endif
    
    // Add change event listener to auto-fill recipient details
    recipientSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('recipientName').value = selectedOption.dataset.name || '';
            document.getElementById('recipientAddress').value = selectedOption.dataset.address || '';
        }
    });
}

function insertVariable(variable) {
    const contentArea = document.getElementById('letterContent');
    const cursorPos = contentArea.selectionStart;
    const textBefore = contentArea.value.substring(0, cursorPos);
    const textAfter = contentArea.value.substring(cursorPos);
    
    contentArea.value = textBefore + variable + textAfter;
    contentArea.focus();
    contentArea.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);
    
    updateContentPreview();
}

function updateContentPreview() {
    const content = document.getElementById('letterContent').value;
    const recipientName = document.getElementById('recipientName').value || 'Recipient Name';
    const currentDate = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    // Replace variables with actual values for preview
    let previewContent = content
        .replace(/\{\{recipient_name\}\}/g, recipientName)
        .replace(/\{\{current_date\}\}/g, currentDate)
        .replace(/\{\{centre_name\}\}/g, '{{ session("centre_name", "CREAMS Centre") }}')
        .replace(/\{\{sender_name\}\}/g, '{{ session("name", "Staff Member") }}')
        .replace(/\{\{recipient_address\}\}/g, document.getElementById('recipientAddress').value || 'Recipient Address');
    
    // Convert line breaks to paragraphs
    previewContent = previewContent.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
    previewContent = '<p>' + previewContent + '</p>';
    
    document.getElementById('contentPreview').innerHTML = previewContent;
    
    // Update final preview if on review step
    if (currentStep === 4) {
        document.getElementById('finalPreview').innerHTML = previewContent;
        updateReviewDetails();
    }
}

function updateReviewDetails() {
    const templateName = document.querySelector('.template-option.selected .template-info h4')?.textContent || 'Custom';
    const recipientName = document.getElementById('recipientName').value || '-';
    const subject = document.getElementById('letterSubject').value || '-';
    const priority = document.getElementById('letterPriority').value || 'normal';
    
    document.getElementById('reviewTemplate').textContent = templateName;
    document.getElementById('reviewRecipient').textContent = recipientName;
    document.getElementById('reviewSubject').textContent = subject;
    document.getElementById('reviewPriority').textContent = priority.charAt(0).toUpperCase() + priority.slice(1);
}

function nextStep() {
    if (!validateCurrentStep()) {
        return;
    }
    
    if (currentStep < totalSteps) {
        // Hide current step
        document.getElementById(`step${currentStep}`).style.display = 'none';
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.add('completed');
        
        // Show next step
        currentStep++;
        document.getElementById(`step${currentStep}`).style.display = 'block';
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.add('active');
        
        // Update preview if on review step
        if (currentStep === 4) {
            updateContentPreview();
        }
        
        updateNavigationButtons();
    }
}

function prevStep() {
    if (currentStep > 1) {
        // Hide current step
        document.getElementById(`step${currentStep}`).style.display = 'none';
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.remove('active');
        
        // Show previous step
        currentStep--;
        document.getElementById(`step${currentStep}`).style.display = 'block';
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.remove('completed');
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.add('active');
        
        updateNavigationButtons();
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const generateBtn = document.getElementById('generateBtn');
    
    // Show/hide previous button
    prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
    
    // Show/hide next/generate buttons
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        generateBtn.style.display = 'block';
    } else {
        nextBtn.style.display = 'block';
        generateBtn.style.display = 'none';
    }
}

function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            if (!selectedTemplateId) {
                alert('Please select a template to continue.');
                return false;
            }
            break;
            
        case 2:
            const requiredFields = ['recipientType', 'recipientName', 'recipientAddress', 'letterPriority'];
            for (let field of requiredFields) {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    alert(`Please fill in the ${element.labels[0].textContent.replace(' *', '')} field.`);
                    element.focus();
                    return false;
                }
            }
            break;
            
        case 3:
            const subject = document.getElementById('letterSubject').value.trim();
            const content = document.getElementById('letterContent').value.trim();
            
            if (!subject) {
                alert('Please enter a letter subject.');
                document.getElementById('letterSubject').focus();
                return false;
            }
            
            if (!content) {
                alert('Please enter letter content.');
                document.getElementById('letterContent').focus();
                return false;
            }
            break;
    }
    
    return true;
}
</script>
@endpush