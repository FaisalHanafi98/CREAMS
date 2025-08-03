@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-file-alt mr-2"></i>Letter Archive
                </h2>
                <div>
                    <a href="/profile#letters" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Generate New Letter
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('letters.index') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search by reference or subject..." 
                                   value="{{ request('search') }}"
                                   style="width: 300px;">
                        </div>
                        <button type="submit" class="btn btn-info mr-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('letters.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Previous Letter Templates Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-layer-group mr-2"></i>Previous Letter Templates
                        <small class="text-muted">- Reuse templates with different content</small>
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($templates) && $templates->count() > 0)
                        <div class="row">
                            @foreach($templates->take(6) as $template)
                                <div class="col-md-4 mb-3">
                                    <div class="card template-card h-100">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">{{ $template->template_name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">
                                                {{ $template->template_description ?? 'No description' }}
                                            </p>
                                            <div class="template-info mb-2">
                                                <small class="text-muted">
                                                    Created: {{ $template->created_at->format('d M Y') }}
                                                </small>
                                                @if($template->header_image_path || $template->footer_image_path)
                                                    <br>
                                                    <small class="text-success">
                                                        @if($template->header_image_path)<i class="fas fa-image" title="Has header image"></i>@endif
                                                        @if($template->footer_image_path)<i class="fas fa-image" title="Has footer image"></i>@endif
                                                        Images included
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="mt-auto">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary use-template-btn" 
                                                        data-template-id="{{ $template->id }}"
                                                        data-template-name="{{ $template->template_name }}"
                                                        data-template-description="{{ $template->template_description ?? '' }}"
                                                        data-header-text="{{ $template->header_text ?? '' }}"
                                                        data-footer-text="{{ $template->footer_text ?? '' }}"
                                                        data-header-image="{{ $template->header_image_url ?? '' }}"
                                                        data-footer-image="{{ $template->footer_image_url ?? '' }}">
                                                    <i class="fas fa-copy mr-1"></i>Use Template
                                                </button>
                                                @if($template->is_active)
                                                    <span class="badge badge-success ml-2">Active</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($templates->count() > 6)
                            <div class="text-center">
                                <button class="btn btn-outline-secondary" id="show-all-templates">
                                    <i class="fas fa-eye mr-1"></i>Show All {{ $templates->count() }} Templates
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h6>No Templates Found</h6>
                            <p class="text-muted">You haven't created any letter templates yet.</p>
                            <a href="/profile#letters" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Create Your First Template
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Letter Table -->
            <div class="card">
                <div class="card-body">
                    @if($letters->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Letter Name</th>
                                        <th>Reference</th>
                                        <th>Subject</th>
                                        <th>Recipient</th>
                                        <th>Date</th>
                                        <th>Generated By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($letters as $letter)
                                        @php
                                            // Ensure letter_data is properly decoded
                                            $letterData = $letter->letter_data;
                                            if (is_string($letterData)) {
                                                $letterData = json_decode($letterData, true) ?: [];
                                            }
                                            if (!is_array($letterData)) {
                                                $letterData = [];
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $letter->letter_name ?? 'Unnamed Letter' }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $letter->letter_reference ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ Str::limit($letter->letter_subject ?? 'No Subject', 40) }}</td>
                                            <td>{{ $letterData['recipient_name'] ?? 'Unknown' }}</td>
                                            <td>{{ $letter->letter_date ? date('d M Y', strtotime($letter->letter_date)) : 'N/A' }}</td>
                                            <td>
                                                {{ $letterData['generated_by_name'] ?? 'Unknown' }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $letter->created_at ? $letter->created_at->format('d M Y, h:i A') : '' }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($letter->letter_file_path)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> PDF Ready
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-circle"></i> No PDF
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($letter->letter_file_path)
                                                    <a href="{{ route('profile.letter.download', $letter->id) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Download PDF">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                                
                                                @if(session('role') === 'admin' || $letter->created_by === session('id'))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger delete-letter-btn" 
                                                            data-id="{{ $letter->id }}"
                                                            data-reference="{{ $letter->letter_reference }}"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $letters->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No Letter Found</h5>
                            <p class="text-muted">
                                @if(request('search'))
                                    No letters match your search criteria.
                                @else
                                    You haven't generated any letters yet.
                                @endif
                            </p>
                            <a href="/profile#letters" class="btn btn-primary mt-3">
                                <i class="fas fa-plus mr-2"></i>Generate Your First Letter
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this letter?</p>
                <p><strong>Reference: <span id="deleteReference"></span></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.template-card {
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-color: #32bdea;
}

.template-card .card-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.template-card .card-text {
    font-size: 0.875rem;
    line-height: 1.4;
}

.template-info {
    border-top: 1px solid #f8f9fc;
    padding-top: 0.5rem;
}

.use-template-btn {
    background: linear-gradient(135deg, #32bdea, #25a6cf);
    border: none;
    font-weight: 500;
    transition: transform 0.2s ease;
}

.use-template-btn:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #25a6cf, #1a8aa8);
}

.badge-success {
    background-color: #1cc88a;
}

#show-all-templates {
    border: 2px dashed #32bdea;
    color: #32bdea;
    font-weight: 500;
}

#show-all-templates:hover {
    background-color: #32bdea;
    color: white;
    border-style: solid;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var deleteId = null;
    
    // Delete button click
    $('.delete-letter-btn').on('click', function() {
        deleteId = $(this).data('id');
        var reference = $(this).data('reference');
        $('#deleteReference').text(reference);
        $('#deleteModal').modal('show');
    });

    // Confirm delete
    $('#confirmDelete').on('click', function() {
        if (!deleteId) return;

        $.ajax({
            url: '/letters/' + deleteId,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to delete letter');
                }
            },
            error: function() {
                alert('Error deleting letter');
            }
        });
    });

    // Handle "Use Template" button clicks
    $('.use-template-btn').on('click', function(e) {
        e.preventDefault();
        console.log('Use Template button clicked');
        
        const templateData = {
            id: $(this).data('template-id'),
            name: $(this).data('template-name'),
            description: $(this).data('template-description'),
            headerText: $(this).data('header-text'),
            footerText: $(this).data('footer-text'),
            headerImage: $(this).data('header-image'),
            footerImage: $(this).data('footer-image')
        };

        console.log('Template data:', templateData);

        // Confirm before redirecting
        const confirmMessage = `Use template "${templateData.name}" for a new letter?\n\nThis will take you to the letter generator with this template's header and footer configuration loaded.`;
        
        if (confirm(confirmMessage)) {
            // Store template data in localStorage to be used in the profile page
            localStorage.setItem('selectedTemplate', JSON.stringify(templateData));
            console.log('Template data saved to localStorage');
            
            // Redirect to profile page letter section
            window.location.href = '/profile#letter';
        }
    });

    // Show all templates functionality
    $('#show-all-templates').on('click', function() {
        $('.col-md-4:hidden').show();
        $(this).hide();
    });
});
</script>
@endpush