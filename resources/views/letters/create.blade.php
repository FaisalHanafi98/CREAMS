@extends('layouts.app')

@section('title', 'Create New Letter - CREAMS')

@section('content')
<div class="letter-create-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Create New Letter</h1>
            <p class="subtitle">Generate a new official letter</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Letters
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> Please check the form for errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="form-container">
        <form action="{{ route('letters.store') }}" method="POST" class="letter-form">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    {{-- Letter Details --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Letter Details</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="letter_date">Letter Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('letter_date') is-invalid @enderror" 
                                               id="letter_date" name="letter_date" 
                                               value="{{ old('letter_date', date('Y-m-d')) }}" required>
                                        @error('letter_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="template_id">Letter Template</label>
                                        <select class="form-control @error('template_id') is-invalid @enderror" 
                                                id="template_id" name="template_id">
                                            <option value="">Select Template (Optional)</option>
                                            @foreach($templates as $template)
                                                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                                    {{ $template->template_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('template_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                       id="subject" name="subject" 
                                       value="{{ old('subject') }}" 
                                       placeholder="Enter letter subject" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Recipient Information --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Recipient Information</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label for="recipient_name">Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('recipient_name') is-invalid @enderror" 
                                       id="recipient_name" name="recipient_name" 
                                       value="{{ old('recipient_name') }}" 
                                       placeholder="Enter recipient's full name" required>
                                @error('recipient_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="recipient_address">Recipient Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('recipient_address') is-invalid @enderror" 
                                          id="recipient_address" name="recipient_address" rows="4"
                                          placeholder="Enter complete address including postal code" required>{{ old('recipient_address') }}</textarea>
                                @error('recipient_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Letter Content --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Letter Content</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label for="body_content">Letter Body <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('body_content') is-invalid @enderror" 
                                          id="body_content" name="body_content" rows="12"
                                          placeholder="Enter the main content of the letter..." required>{{ old('body_content') }}</textarea>
                                @error('body_content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Use simple text formatting. Line breaks will be preserved.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Preview Card --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Letter Preview</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="letter-preview" id="letterPreview">
                                <div class="preview-placeholder">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Fill out the form to see a preview of your letter</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Actions</h3>
                        </div>
                        <div class="form-card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i> Create Letter
                            </button>
                            <button type="button" class="btn btn-outline-info btn-block" id="previewBtn">
                                <i class="fas fa-eye mr-2"></i> Update Preview
                            </button>
                            <a href="{{ route('letters.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                        </div>
                    </div>

                    {{-- Tips Card --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Tips</h3>
                        </div>
                        <div class="form-card-body">
                            <ul class="tips-list">
                                <li>Use a clear and concise subject line</li>
                                <li>Include all necessary recipient information</li>
                                <li>Keep the letter content professional and formal</li>
                                <li>Double-check all dates and names</li>
                                <li>Review the preview before creating</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
.letter-create-container {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.subtitle {
    color: #6c757d;
    margin: 0;
    font-size: 1.1rem;
}

.form-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
}

.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    overflow: hidden;
}

.form-card-header {
    background: linear-gradient(135deg, #007bff, #6c757d);
    color: white;
    padding: 15px 20px;
    border-bottom: none;
}

.form-card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.form-card-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.btn {
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-block {
    margin-bottom: 10px;
}

.text-danger {
    color: #dc3545 !important;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
}

.alert {
    border-radius: 8px;
    margin-bottom: 20px;
}

.letter-preview {
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    padding: 20px;
    min-height: 300px;
    background: #fff;
}

.preview-placeholder {
    text-align: center;
    padding: 40px 20px;
}

.preview-content {
    font-family: 'Georgia', serif;
    line-height: 1.6;
    font-size: 0.9rem;
}

.preview-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.preview-subject {
    font-weight: bold;
    margin-bottom: 10px;
}

.preview-date {
    color: #6c757d;
    font-size: 0.85rem;
}

.preview-recipient {
    margin: 15px 0;
    padding: 10px;
    background: #f8f9fa;
    border-left: 3px solid #007bff;
    font-size: 0.85rem;
}

.preview-body {
    margin: 15px 0;
    font-size: 0.85rem;
    line-height: 1.5;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
    position: relative;
    padding-left: 20px;
}

.tips-list li:before {
    content: "💡";
    position: absolute;
    left: 0;
    top: 8px;
}

.tips-list li:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        margin-top: 15px;
    }
    
    .form-container {
        padding: 15px;
    }
    
    .form-card-body {
        padding: 20px;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);

    // Form validation enhancement
    const form = document.querySelector('.letter-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
            }
        });
    }
    
    // Preview functionality
    const previewBtn = document.getElementById('previewBtn');
    const letterPreview = document.getElementById('letterPreview');
    
    if (previewBtn && letterPreview) {
        previewBtn.addEventListener('click', function() {
            updatePreview();
        });
        
        // Auto-update preview when fields change
        const formFields = ['subject', 'recipient_name', 'recipient_address', 'body_content', 'letter_date'];
        formFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', debounce(updatePreview, 500));
            }
        });
    }
    
    function updatePreview() {
        const subject = document.getElementById('subject').value;
        const recipientName = document.getElementById('recipient_name').value;
        const recipientAddress = document.getElementById('recipient_address').value;
        const bodyContent = document.getElementById('body_content').value;
        const letterDate = document.getElementById('letter_date').value;
        
        if (!subject && !recipientName && !bodyContent) {
            letterPreview.innerHTML = `
                <div class="preview-placeholder">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Fill out the form to see a preview of your letter</p>
                </div>
            `;
            return;
        }
        
        const formattedDate = letterDate ? new Date(letterDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'Date not set';
        
        letterPreview.innerHTML = `
            <div class="preview-content">
                <div class="preview-header">
                    <div class="preview-subject">${subject || 'Subject not set'}</div>
                    <div class="preview-date">${formattedDate}</div>
                </div>
                <div class="preview-recipient">
                    <strong>To:</strong><br>
                    ${recipientName || 'Recipient not set'}<br>
                    ${recipientAddress.replace(/\n/g, '<br>') || 'Address not set'}
                </div>
                <div class="preview-body">
                    ${bodyContent.replace(/\n/g, '<br>') || 'Letter content not set'}
                </div>
            </div>
        `;
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
});
</script>
@endsection