<div class="letters-management">
    <!-- Flash Messages -->
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
        
        @if(isset($activeTemplate) && $activeTemplate)
            <div class="current-template alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Current Template:</strong> {{ $activeTemplate->template_name }} 
                (Active since: {{ $activeTemplate->created_at->format('d M Y H:i') }})
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>No Active Template:</strong> Please create a template before generating letters.
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
                <i class="fas fa-save"></i> Update Template
            </button>
        </form>
    </div>
    
    <hr>
    
    <!-- Letter Generation Section -->
    <div class="letter-generation-section">
        <h4><i class="fas fa-plus-circle"></i> Generate New Letter</h4>
        
        <form id="letterForm">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reference_number"><i class="fas fa-hashtag"></i> Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number" class="form-control" 
                               value="{{ \App\Models\Letter::generateReferenceNumber() }}"
                               placeholder="Auto-generated reference" required>
                        <small class="form-text text-muted">Unique reference for this letter</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="letter_date"><i class="fas fa-calendar"></i> Letter Date</label>
                        <input type="date" name="letter_date" id="letter_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="recipient_name"><i class="fas fa-user"></i> Recipient Name</label>
                <input type="text" name="recipient_name" id="recipient_name" class="form-control" 
                       placeholder="Enter recipient's full name" required>
            </div>
            
            <div class="form-group">
                <label for="recipient_address"><i class="fas fa-map-marker-alt"></i> Recipient Address</label>
                <textarea name="recipient_address" id="recipient_address" class="form-control" rows="3" 
                          placeholder="Enter recipient's complete address (optional)"></textarea>
            </div>
            
            <div class="form-group">
                <label for="subject"><i class="fas fa-tag"></i> Subject</label>
                <input type="text" name="subject" id="subject" class="form-control" 
                       placeholder="Enter letter subject" required>
            </div>
            
            <div class="form-group">
                <label for="content"><i class="fas fa-edit"></i> Letter Content</label>
                <textarea name="content" id="letterContent" class="form-control" rows="10" 
                          placeholder="Type your letter content here..." required></textarea>
                <small class="form-text text-muted">Write your letter content. Use proper paragraphs and formatting.</small>
            </div>
            
            <div class="form-group">
                <button type="button" id="previewBtn" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Generate Letter
                </button>
                <button type="button" id="clearBtn" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser"></i> Clear Form
                </button>
            </div>
        </form>
    </div>
    
    <!-- Recent Letters -->
    <div class="recent-letters mt-5">
        <h5><i class="fas fa-history"></i> Recent Letters</h5>
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
                                <code>{{ $letter->reference_number }}</code>
                            </td>
                            <td>{{ $letter->letter_date->format('d M Y') }}</td>
                            <td>{{ $letter->recipient_name }}</td>
                            <td>{{ \Str::limit($letter->subject, 40) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($letter->pdf_path)
                                        <a href="{{ route('admin.letters.download', $letter->id) }}" 
                                           class="btn btn-sm btn-info" title="View PDF" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.letters.download', $letter->id) }}" 
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
                <a href="{{ route('admin.letters.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list"></i> View All Letters
                </a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Letter generation form
    const letterForm = document.getElementById('letterForm');
    const previewBtn = document.getElementById('previewBtn');
    const clearBtn = document.getElementById('clearBtn');
    
    // Form submission
    letterForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(letterForm);
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        
        try {
            const response = await fetch('{{ route("profile.letter.generate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Trigger automatic download
                if (data.download_url) {
                    // Method 1: Try programmatic download using the download route
                    try {
                        const downloadLink = document.createElement('a');
                        downloadLink.href = data.download_url;
                        downloadLink.download = `${data.reference_number}.pdf`;
                        downloadLink.style.display = 'none';
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                        
                        // Method 2: Fallback - open in new window which should trigger download
                        setTimeout(() => {
                            window.open(data.download_url, '_blank');
                        }, 500);
                    } catch (error) {
                        // Auto-download failed, user can use manual download button
                        console.error('Auto-download failed:', error);
                    }
                } else if (data.pdf_url) {
                    // Fallback to direct PDF URL
                    window.open(data.pdf_url, '_blank');
                }
                
                // Show success message with download confirmation
                Swal.fire({
                    icon: 'success',
                    title: 'Letter Generated & Downloaded!',
                    html: `
                        <p>Letter <strong>${data.reference_number}</strong> has been generated successfully and should be downloading to your computer.</p>
                        ${data.download_url ? `<div class="mt-3">
                            <a href="${data.download_url}" 
                               class="btn btn-primary btn-sm" 
                               target="_blank">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                            ${data.pdf_url ? `<a href="${data.pdf_url}" 
                               class="btn btn-secondary btn-sm ml-2" 
                               target="_blank">
                                <i class="fas fa-eye"></i> View PDF
                            </a>` : ''}
                        </div>` : ''}
                    `,
                    confirmButtonText: 'Generate Another',
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    footer: `<small class="text-muted">PDF saved to: storage/letters/</small>`
                }).then((result) => {
                    if (result.isConfirmed) {
                        resetForm();
                    }
                    // Refresh the page to show the new letter in recent letters
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to generate letter', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate Letter';
        }
    });
    
    // Preview functionality
    previewBtn.addEventListener('click', function() {
        const formData = new FormData(letterForm);
        
        // Basic validation
        if (!formData.get('recipient_name') || !formData.get('subject') || !formData.get('content')) {
            Swal.fire('Error', 'Please fill in all required fields before previewing', 'error');
            return;
        }
        
        previewBtn.disabled = true;
        previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        
        fetch('{{ route("profile.letter.preview") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                $('#previewModal').modal('show');
            } else {
                Swal.fire('Error', data.message || 'Failed to generate preview', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to generate preview', 'error');
        })
        .finally(() => {
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview';
        });
    });
    
    // Clear form
    clearBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the form?')) {
            resetForm();
        }
    });
    
    // Reset form function
    function resetForm() {
        letterForm.reset();
        // Set new reference number
        fetch('{{ route("profile.letter.newReference") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reference_number').value = data.reference;
                } else {
                    document.getElementById('reference_number').value = data.reference || 'LTR/' + new Date().getFullYear() + '/' + 
                        String(new Date().getMonth() + 1).padStart(2, '0') + '/0001';
                }
            })
            .catch(() => {
                // Fallback if API fails
                document.getElementById('reference_number').value = 'LTR/' + new Date().getFullYear() + '/' + 
                    String(new Date().getMonth() + 1).padStart(2, '0') + '/0001';
            });
        // Set today's date
        document.getElementById('letter_date').value = new Date().toISOString().split('T')[0];
    }
    
    // Generate from preview modal
    document.getElementById('generateFromPreview').addEventListener('click', function() {
        $('#previewModal').modal('hide');
        letterForm.dispatchEvent(new Event('submit'));
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
    
    // Clear auto-saved data when form is submitted successfully
    letterForm.addEventListener('submit', function() {
        // Clear after a delay to ensure submission was successful
        setTimeout(() => {
            formFields.forEach(field => {
                localStorage.removeItem('letter_form_' + field);
            });
        }, 2000);
    });
});
</script>