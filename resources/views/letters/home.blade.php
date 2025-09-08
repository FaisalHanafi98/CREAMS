@extends('layouts.app')

@section('title', 'Letters Archive - CREAMS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Letters Archive</h1>
                    <p class="text-muted">Manage your generated letters and templates</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('profile') }}#letters-tab" class="btn btn-outline-primary">
                        <i class="fas fa-plus"></i> Generate New Letter
                    </a>
                    <button class="btn btn-primary" onclick="exportLetters()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <div class="h2 text-primary mb-1">{{ $letters->total() }}</div>
                            <div class="text-muted">Generated Letters</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Filter Letters</h5>
                    <form method="GET" action="{{ route('letters.index') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="form-label">Reference</label>
                                <input type="text" name="reference_search" class="form-control" 
                                       value="{{ request('reference_search') }}" 
                                       placeholder="LTR/2024/01/0001">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Letter Name</label>
                                <input type="text" name="name_search" class="form-control" 
                                       value="{{ request('name_search') }}" 
                                       placeholder="Letter name...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Recipient</label>
                                <input type="text" name="participants" class="form-control" 
                                       value="{{ request('participants') }}" 
                                       placeholder="Recipient name...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject_search" class="form-control" 
                                       value="{{ request('subject_search') }}" 
                                       placeholder="Letter subject...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="start_date" class="form-control" 
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="end_date" class="form-control" 
                                       value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Letters Table -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Generated Letters</h5>
                    
                    @if($letters->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> Reference</th>
                                        <th><i class="fas fa-file-signature"></i> Letter Name</th>
                                        <th><i class="fas fa-user"></i> Recipient</th>
                                        <th><i class="fas fa-tag"></i> Subject</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th><i class="fas fa-calendar"></i> Created</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($letters as $letter)
                                        <tr>
                                            <td>
                                                <code class="reference-code">{{ $letter->letter_id }}</code>
                                            </td>
                                            <td>
                                                <strong>{{ $letter->letter_name ?? 'Untitled Letter' }}</strong>
                                            </td>
                                            <td>
                                                <div class="recipient-info">
                                                    <strong>{{ $letter->recipient_name }}</strong>
                                                    @if($letter->recipient_address)
                                                        <br><small class="text-muted">{{ \Str::limit($letter->recipient_address, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span title="{{ $letter->letter_subject }}">{{ \Str::limit($letter->letter_subject, 40) }}</span>
                                            </td>
                                            <td>
                                                @if($letter->letter_status === 'draft')
                                                    <span class="badge badge-warning">Draft</span>
                                                @elseif($letter->letter_status === 'generated')
                                                    <span class="badge badge-success">Generated</span>
                                                @elseif($letter->letter_status === 'sent')
                                                    <span class="badge badge-primary">Sent</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($letter->letter_status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="generated-info">
                                                    <small class="text-muted">
                                                        {{ $letter->created_at->format('d M Y H:i') }}
                                                        <br>by {{ $letter->generated_by_name }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" 
                                                            onclick="previewLetter({{ $letter->id }})" title="Preview Letter">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if(Route::has('profile.letters.download'))
                                                        <a href="{{ route('profile.letters.download', $letter->id) }}" 
                                                           class="btn btn-outline-success" title="Download Letter">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="deleteLetter({{ $letter->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($letters->hasPages())
                            @include('components.custom-pagination', ['items' => $letters])
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No letters found</h5>
                            <p class="text-muted">Start by generating your first letter from the profile page.</p>
                            <a href="{{ route('profile') }}#letters-tab" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Generate Letter
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Letter preview function
function previewLetter(letterId) {
    console.log('Previewing letter ID:', letterId);
    
    // Create a modal to show the letter preview
    const modalHtml = `
        <div class="modal fade" id="letterPreviewModal" tabindex="-1" role="dialog" aria-labelledby="letterPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="letterPreviewModalLabel">Letter Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="letterPreviewContent">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading letter preview...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#letterPreviewModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#letterPreviewModal').modal('show');
    
    // Fetch letter content
    $.ajax({
        url: `/profile/letter-preview/${letterId}`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.html) {
                $('#letterPreviewContent').html(response.html);
            } else {
                $('#letterPreviewContent').html('<div class="alert alert-warning">Letter preview not available.</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading letter preview:', error);
            $('#letterPreviewContent').html('<div class="alert alert-danger">Error loading letter preview. Please try again.</div>');
        }
    });
}

function deleteLetter(letterId) {
    if (confirm('Are you sure you want to delete this letter?')) {
        // Create a form and submit it for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/admin/letters/${letterId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}


function exportLetters() {
    alert('Export functionality will be implemented in a future update.');
}
</script>

<style>
.reference-code {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.875em;
    font-weight: 600;
}

.recipient-info {
    max-width: 200px;
}

.generated-info {
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

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875em;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.filter-form {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}
</style>

@endsection