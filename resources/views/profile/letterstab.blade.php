<div class="letters-management">
    <!-- Flash Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Template Management Section -->
    <div class="template-section mb-4">
        <h4><i class="fas fa-file-image"></i> Letter Template Settings</h4>
        
        @if(isset($allTemplates) && count($allTemplates) > 0)
            <div class="available-templates alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Available Templates:</strong> {{ count($allTemplates) }} template(s) available
                <div class="template-list mt-2">
                    @foreach($allTemplates as $template)
                        <span class="badge badge-primary mr-2">
                            {{ $template->template_name }} 
                            <small>({{ $template->created_at->format('M d, Y') }})</small>
                        </span>
                    @endforeach
                </div>
            </div>
        @elseif(isset($activeTemplate) && $activeTemplate)
            <div class="current-template alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Current Template:</strong> {{ $activeTemplate->template_name }} 
                (Created: {{ $activeTemplate->created_at->format('d M Y H:i') }})
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>No Templates Found:</strong> Please create a template before generating letters.
            </div>
        @endif
        
        <form action="{{ route('profile.letter.store') }}" method="POST" enctype="multipart/form-data" class="template-form">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="template_name"><i class="fas fa-tag"></i> Template Name</label>
                        <input type="text" name="template_name" id="template_name" class="form-control @error('template_name') is-invalid @enderror" 
                               placeholder="e.g., Official Letterhead 2025" value="{{ old('template_name') }}" required>
                        @error('template_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Give your template a descriptive name</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="header_image"><i class="fas fa-image"></i> Header Image</label>
                        <input type="file" name="header_image" id="header_image" class="form-control @error('header_image') is-invalid @enderror" accept="image/*">
                        @error('header_image')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Upload letterhead/header image (max 2MB, JPG/PNG)</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="footer_image"><i class="fas fa-image"></i> Footer Image</label>
                        <input type="file" name="footer_image" id="footer_image" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Upload footer image/signature (max 2MB, JPG/PNG)</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Preview area could go here -->
                </div>
            </div>
            
            <div class="form-group">
                <label for="header_content"><i class="fas fa-align-center"></i> Additional Header Text (Optional)</label>
                <textarea name="header_content" id="header_content" class="form-control" rows="2" 
                          placeholder="e.g., Additional header information or contact details">{{ old('header_content') }}</textarea>
                <small class="form-text text-muted">This text will appear below the header image</small>
            </div>
            
            <div class="form-group">
                <label for="footer_content"><i class="fas fa-align-center"></i> Footer Text (Optional)</label>
                <textarea name="footer_content" id="footer_content" class="form-control" rows="2" 
                          placeholder="e.g., This is a computer-generated letter">{{ old('footer_content') }}</textarea>
                <small class="form-text text-muted">This text will appear at the bottom of all letters</small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Template
            </button>
            <small class="form-text text-muted mt-2">
                <i class="fas fa-info-circle"></i> Saving will create a new template that you can use for generating letters.
            </small>
        </form>
    </div>
    
    <hr>
    
    <!-- Direct Letter Generation Section -->
    <div class="letter-generation-section">
        <h4><i class="fas fa-plus-circle"></i> Generate New Letter</h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="reference_number"><i class="fas fa-hashtag"></i> Reference Number</label>
                    <input type="text" id="reference_number" class="form-control" 
                           value="Auto-generated on save"
                           placeholder="Auto-generated reference" readonly disabled>
                    <small class="form-text text-muted">Reference will be automatically generated when letter is created</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="letter_date"><i class="fas fa-calendar"></i> Letter Date</label>
                    <input type="date" id="letter_date" class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="recipient_name"><i class="fas fa-user"></i> Recipient Name</label>
            <input type="text" id="recipient_name" class="form-control" 
                   placeholder="Enter recipient's full name" required>
        </div>
        
        <div class="form-group">
            <label for="recipient_address"><i class="fas fa-map-marker-alt"></i> Recipient Address</label>
            <textarea id="recipient_address" class="form-control" rows="3" 
                      placeholder="Enter recipient's complete address (optional)"></textarea>
        </div>
        
        <div class="form-group">
            <label for="subject"><i class="fas fa-tag"></i> Subject</label>
            <input type="text" id="subject" class="form-control" 
                   placeholder="Enter letter subject" required>
        </div>
        
        <div class="form-group">
            <label for="content"><i class="fas fa-edit"></i> Letter Content</label>
            <textarea id="content" class="form-control" rows="10" 
                      placeholder="Type your letter content here..." required></textarea>
            <small class="form-text text-muted">Write your letter content. Use proper paragraphs and formatting.</small>
        </div>
        
        <div class="form-group">
            <button type="button" id="directGenerateBtn" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Generate Letter
            </button>
            <button type="button" id="clearBtn" class="btn btn-outline-secondary">
                <i class="fas fa-eraser"></i> Clear Form
            </button>
        </div>
    </div>
    
    <!-- Recent Letter -->
    <div class="recent-letters mt-5">
        <h5><i class="fas fa-history"></i> Recent Letter</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="thead-light">
                    <tr>
                        <th><i class="fas fa-hashtag"></i> Reference</th>
                        <th><i class="fas fa-calendar"></i> Date</th>
                        <th><i class="fas fa-user"></i> Recipient</th>
                        <th><i class="fas fa-tag"></i> Subject</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(isset($recentLetters) ? $recentLetters : [] as $letter)
                        <tr>
                            <td>
                                <code>{{ $letter->letter_reference }}</code>
                            </td>
                            <td>{{ $letter->letter_date->format('d M Y') }}</td>
                            <td>{{ $letter->letter_data['recipient_name'] ?? 'N/A' }}</td>
                            <td>{{ \Str::limit($letter->letter_subject, 40) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($letter->letter_file_path)
                                        <button class="btn btn-sm btn-info preview-letter-btn" 
                                                data-letter-id="{{ $letter->id }}" 
                                                title="Preview Letter">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('profile.letters.download', $letter->id) }}" 
                                           class="btn btn-sm btn-primary" title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @else
                                        <span class="btn btn-sm btn-secondary disabled" title="PDF not available">
                                            <i class="fas fa-times"></i>
                                        </span>
                                        <span class="btn btn-sm btn-secondary disabled" title="PDF not available">
                                            <i class="fas fa-download"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-inbox"></i> No letters generated yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($recentLetters) && $recentLetters->count() > 0)
            <div class="text-center">
                <a href="{{ route('letters.archive') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list"></i> View All Letter
                </a>
                <button type="button" id="refreshLettersBtn" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sync"></i> Refresh List
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye"></i> Letter Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="generateFromPreview">
                    <i class="fas fa-file-pdf"></i> Generate PDF
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.letters-management {
    max-width: 100%;
}

.template-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.letter-generation-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.current-template {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
}

.form-group label i {
    margin-right: 5px;
    color: #6c757d;
}

.table thead th {
    border-top: none;
    font-weight: 600;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

code {
    background: #e9ecef;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.875em;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

#letterContent {
    font-family: 'Times New Roman', serif;
    line-height: 1.6;
}

.recent-letters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
</style>

<script>
// Direct Letter Generation Function
function directGenerateLetter() {
    console.log('Direct generation started');
    
    const btn = document.getElementById('directGenerateBtn');
    
    // Simple prevention - disable button during generation
    if (btn.disabled) {
        console.log('Button already disabled, preventing duplicate submission');
        return;
    }
    
    // Get form data
    const data = {
        letter_date: document.getElementById('letter_date').value,
        recipient_name: document.getElementById('recipient_name').value,
        recipient_address: document.getElementById('recipient_address').value,
        subject: document.getElementById('subject').value,
        content: document.getElementById('content').value
    };
    
    // Validate required fields
    if (!data.recipient_name || !data.subject || !data.content) {
        alert('Please fill in all required fields (Recipient Name, Subject, and Content)');
        return;
    }
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    
    // Use XMLHttpRequest for direct generation
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/profile/letter-generate', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    
    xhr.onload = function() {
        console.log('XHR Response Status:', xhr.status);
        console.log('XHR Response Text:', xhr.responseText);
        
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Parsed Response:', response);
                
                if (response.success) {
                    alert(`SUCCESS! Letter ${response.reference || 'generated'} successfully! Check your downloads folder.`);
                    
                    // Try to trigger download
                    if (response.download_url) {
                        window.open(response.download_url, '_blank');
                    }
                    
                    // Clear form
                    document.getElementById('recipient_name').value = '';
                    document.getElementById('recipient_address').value = '';
                    document.getElementById('subject').value = '';
                    document.getElementById('content').value = '';
                    
                    // Refresh page to show new letter
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    alert('Error: ' + (response.message || 'Generation failed'));
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                alert('Error: Invalid response from server');
            }
        } else {
            alert('Error: Server returned status ' + xhr.status);
        }
    };
    
    xhr.onerror = function() {
        console.error('XHR Error occurred');
        // Reset button state on error
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Error: Network error occurred');
    };
    
    xhr.send(JSON.stringify(data));
}

document.addEventListener('DOMContentLoaded', function() {
    // Direct generation button
    const directBtn = document.getElementById('directGenerateBtn');
    if (directBtn) {
        directBtn.addEventListener('click', directGenerateLetter);
    }
    
    
    // Clear form
    const clearBtn = document.getElementById('clearBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the form?')) {
                document.getElementById('recipient_name').value = '';
                document.getElementById('recipient_address').value = '';
                document.getElementById('subject').value = '';
                document.getElementById('content').value = '';
                document.getElementById('letter_date').value = new Date().toISOString().split('T')[0];
            }
        });
    }
    
    // Preview existing letters
    document.addEventListener('click', function(e) {
        if (e.target.closest('.preview-letter-btn')) {
            const btn = e.target.closest('.preview-letter-btn');
            const letterId = btn.dataset.letterId;
            
            // Show modal immediately
            if (typeof $ !== 'undefined' && $('#previewModal').length) {
                $('#previewModal').modal('show');
                document.getElementById('previewContent').innerHTML = `
                    <div class="text-center p-4">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading letter preview...</p>
                    </div>
                `;
                
                // Create iframe for PDF preview
                const iframe = document.createElement('iframe');
                iframe.src = `/letters/${letterId}/view-pdf`;
                iframe.style.width = '100%';
                iframe.style.height = '500px';
                iframe.style.border = 'none';
                iframe.style.borderRadius = '8px';
                iframe.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                
                iframe.onload = function() {
                    document.getElementById('previewContent').innerHTML = '';
                    document.getElementById('previewContent').appendChild(iframe);
                };
                
                iframe.onerror = function() {
                    document.getElementById('previewContent').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">Preview Error</h4>
                            <p>Unable to load PDF preview. The file may be corrupted or not available.</p>
                            <hr>
                            <p class="mb-0">Try downloading the PDF file instead.</p>
                        </div>
                    `;
                };
            }
        }
    });
    
    // Auto-save form data to localStorage
    const formFields = ['recipient_name', 'recipient_address', 'subject', 'content'];
    formFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            // Load saved data
            const saved = localStorage.getItem('letter_form_' + field);
            if (saved && !element.value) {
                element.value = saved;
            }
            
            // Save on change
            element.addEventListener('input', function() {
                localStorage.setItem('letter_form_' + field, this.value);
            });
        }
    });
});
</script>

