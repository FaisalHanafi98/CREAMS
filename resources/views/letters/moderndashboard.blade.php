@extends('layouts.app')

@section('title', 'Letter Generation - CREAMS')

@section('content')
<div class="letter-dashboard">
    <!-- Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title">
                    <i class="fas fa-file-alt me-3"></i>Letter Generation
                </h1>
                <p class="page-subtitle">Create and manage official letters and documents</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" onclick="openLetterModal()">
                    <i class="fas fa-plus me-2"></i>Generate New Letter
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $recentLetters->count() }}</div>
                    <div class="stat-label">Recent Letters</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-templates"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $templates->count() }}</div>
                    <div class="stat-label">Available Templates</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-download"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">-</div>
                    <div class="stat-label">Downloads Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">-</div>
                    <div class="stat-label">Pending Letters</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Letters -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Letters
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentLetters->count() > 0)
                        <div class="letters-list">
                            @foreach($recentLetters as $letter)
                            <div class="letter-item">
                                <div class="letter-icon">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </div>
                                <div class="letter-details">
                                    <h6 class="letter-title">{{ $letter->letter_subject }}</h6>
                                    <p class="letter-meta">
                                        <span class="letter-ref">{{ $letter->letter_id }}</span>
                                        <span class="letter-date">{{ $letter->created_at->format('M j, Y') }}</span>
                                    </p>
                                </div>
                                <div class="letter-actions">
                                    <a href="{{ route('letters.show', $letter->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('letters.download', $letter->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h5>No Letters Generated Yet</h5>
                            <p>Create your first letter using the button above.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Templates & Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group me-2"></i>Letter Templates
                    </h5>
                </div>
                <div class="card-body">
                    @if($templates->count() > 0)
                        <div class="templates-list">
                            @foreach($templates as $template)
                            <div class="template-item" onclick="selectTemplate({{ $template->id }})">
                                <div class="template-icon">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <div class="template-name">{{ $template->template_name }}</div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state small">
                            <i class="fas fa-layer-group"></i>
                            <p>No templates available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="quickLetter('official')">
                            <i class="fas fa-stamp"></i>
                            <span>Official Letter</span>
                        </button>
                        <button class="quick-action-btn" onclick="quickLetter('certificate')">
                            <i class="fas fa-certificate"></i>
                            <span>Certificate</span>
                        </button>
                        <button class="quick-action-btn" onclick="quickLetter('invitation')">
                            <i class="fas fa-envelope"></i>
                            <span>Invitation</span>
                        </button>
                        <button class="quick-action-btn" onclick="quickLetter('recommendation')">
                            <i class="fas fa-thumbs-up"></i>
                            <span>Recommendation</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Letter Generation Modal -->
<div class="modal fade" id="letterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>Generate New Letter
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="letterForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="recipient_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Letter Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="letter_type" required>
                                    <option value="">Select Type</option>
                                    <option value="official">Official Letter</option>
                                    <option value="invitation">Invitation</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="recommendation">Recommendation</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recipient Address</label>
                        <textarea class="form-control" name="recipient_address" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="letter_subject" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template (Optional)</label>
                        <select class="form-select" name="template_id">
                            <option value="">No Template</option>
                            @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Letter Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="letter_content" rows="8" required 
                            placeholder="Enter the main content of your letter here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-2"></i>Generate Letter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5>Generating Letter...</h5>
                <p>Please wait while we create your PDF document.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.letter-dashboard {
    padding: 0;
}

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.page-subtitle {
    opacity: 0.9;
    margin: 0;
}

/* Stat Cards */
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    display: flex;
    align-items: center;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    margin-right: 15px;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
}

.stat-label {
    color: #718096;
    font-size: 0.9rem;
}

/* Letters List */
.letter-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #e2e8f0;
}

.letter-item:last-child {
    border-bottom: none;
}

.letter-icon {
    margin-right: 15px;
    font-size: 24px;
}

.letter-details {
    flex: 1;
}

.letter-title {
    font-weight: 600;
    margin-bottom: 4px;
    color: #2d3748;
}

.letter-meta {
    font-size: 0.85rem;
    color: #718096;
    margin: 0;
}

.letter-ref {
    margin-right: 15px;
    font-weight: 500;
}

.letter-actions {
    display: flex;
    gap: 8px;
}

/* Templates */
.template-item {
    display: flex;
    align-items: center;
    padding: 12px;
    background: #f7fafc;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.template-item:hover {
    background: #667eea;
    color: white;
}

.template-icon {
    margin-right: 12px;
    color: #667eea;
    font-size: 16px;
}

.template-item:hover .template-icon {
    color: white;
}

.template-name {
    font-size: 0.9rem;
    font-weight: 500;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    background: #f7fafc;
    border: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
    color: #2d3748;
}

.quick-action-btn:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: 1.5rem;
    margin-bottom: 8px;
}

.quick-action-btn span {
    font-size: 0.8rem;
    font-weight: 500;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #718096;
}

.empty-state.small {
    padding: 20px;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state h5 {
    margin-bottom: 10px;
    color: #4a5568;
}

/* Modal Enhancements */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.modal-header {
    background: #f7fafc;
    border-bottom: 1px solid #e2e8f0;
    border-radius: 12px 12px 0 0;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-header {
        text-align: center;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        margin-bottom: 15px;
    }
}
</style>
@endsection

@section('scripts')
<script>
function openLetterModal() {
    $('#letterModal').modal('show');
}

function selectTemplate(templateId) {
    $('select[name="template_id"]').val(templateId);
    openLetterModal();
}

function quickLetter(type) {
    $('select[name="letter_type"]').val(type);
    openLetterModal();
}

// Handle form submission
$('#letterForm').on('submit', function(e) {
    e.preventDefault();
    
    // Show loading modal
    $('#letterModal').modal('hide');
    $('#loadingModal').modal('show');
    
    // Submit form
    $.ajax({
        url: '{{ route('letters.generate') }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            $('#loadingModal').modal('hide');
            
            if (response.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Letter Generated!',
                    text: 'Reference: ' + response.reference,
                    showCancelButton: true,
                    confirmButtonText: 'Download PDF',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(response.download_url, '_blank');
                    }
                    // Reload page to show new letter
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Generation Failed',
                    text: response.error || 'Unknown error occurred'
                });
            }
        },
        error: function(xhr) {
            $('#loadingModal').modal('hide');
            
            let errorMessage = 'Server error occurred';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        }
    });
});

// Reset form when modal is hidden
$('#letterModal').on('hidden.bs.modal', function() {
    $('#letterForm')[0].reset();
});
</script>

<!-- SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection