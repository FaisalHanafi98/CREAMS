@extends('layouts.app')

@section('title', 'Letter Management')

@push('styles')
<style>
    .letter-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .generation-panel {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .letter-form .form-group {
        margin-bottom: 20px;
    }
    
    .letter-form label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    
    .letter-form .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 10px 15px;
    }
    
    .letter-form .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .archive-panel {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 25px;
    }
    
    .letter-table {
        margin-top: 20px;
    }
    
    .letter-table th {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
    }
    
    .letter-table td {
        padding: 12px;
        vertical-align: middle;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-badge.ready {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .action-buttons .btn {
        padding: 4px 12px;
        margin: 0 2px;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .page-title {
        color: #495057;
        font-weight: 300;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="letter-dashboard">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="page-title">
                    <i class="fas fa-file-alt mr-3"></i>Letter Management System
                </h1>
                <p class="text-muted">Generate official letters and manage your correspondence</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number">{{ $letters->total() }}</div>
                <div class="stat-label">Total Letters</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $letters->where('letter_file_path', '!=', null)->count() }}</div>
                <div class="stat-label">With PDF</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $letters->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                <div class="stat-label">This Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $letters->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>

        <!-- Letter Generation Panel -->
        <div class="generation-panel">
            <h3 class="mb-4">Generate New Letter</h3>
            
            <form id="letterGenerationForm" class="letter-form">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="letter_date">Letter Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="letter_date" 
                                   name="letter_date" 
                                   value="{{ date('Y-m-d') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="recipient_type">Recipient Type</label>
                            <select class="form-control" id="recipient_type" name="recipient_type">
                                <option value="external">External</option>
                                <option value="trainee">Trainee</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>

                        <div class="form-group" id="traineeSelect" style="display: none;">
                            <label for="trainee_id">Select Trainee</label>
                            <select class="form-control" id="trainee_id" name="recipient_id">
                                <option value="">-- Select Trainee --</option>
                                @foreach($trainees as $trainee)
                                    <option value="{{ $trainee->id }}" data-name="{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}">
                                        {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }} ({{ $trainee->trainee_email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="recipient_name">Recipient Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="recipient_name" 
                                   name="recipient_name" 
                                   placeholder="Enter recipient name" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="recipient_address">Recipient Address</label>
                            <textarea class="form-control" 
                                      id="recipient_address" 
                                      name="recipient_address" 
                                      rows="3" 
                                      placeholder="Enter address (optional)"></textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="letter_subject">Subject</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="letter_subject" 
                                   name="letter_subject" 
                                   placeholder="Enter letter subject" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="letter_content">Letter Content</label>
                            <textarea class="form-control" 
                                      id="letter_content" 
                                      name="letter_content" 
                                      rows="8" 
                                      placeholder="Enter letter content" 
                                      required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="template_id">Template (Optional)</label>
                            <select class="form-control" id="template_id" name="template_id">
                                <option value="">-- Use Default Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" class="btn btn-secondary btn-lg mr-2" onclick="previewLetter()">
                        <i class="fas fa-eye mr-2"></i>Preview
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-pdf mr-2"></i>Generate Letter
                    </button>
                </div>
            </form>
        </div>

        <!-- Letters Archive Panel -->
        <div class="archive-panel">
            <h3 class="mb-4">Letters Archive</h3>
            
            <!-- Search Bar -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="searchInput" 
                               placeholder="Search by reference, subject, or recipient..."
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchLetters()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Letters Table -->
            <div class="table-responsive letter-table">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Recipient</th>
                            <th>Generated By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($letters as $letter)
                            <tr>
                                <td><strong>{{ $letter->letter_reference }}</strong></td>
                                <td>{{ Carbon\Carbon::parse($letter->letter_date)->format('d M Y') }}</td>
                                <td>{{ Str::limit($letter->letter_subject, 40) }}</td>
                                <td>{{ $letter->letter_data['recipient_name'] ?? 'Unknown' }}</td>
                                <td>
                                    {{ $letter->letter_data['generated_by_name'] ?? 'System' }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $letter->created_at->format('d M Y, h:i A') }}
                                    </small>
                                </td>
                                <td>
                                    @if($letter->letter_file_path)
                                        <span class="status-badge ready">
                                            <i class="fas fa-check-circle mr-1"></i>Ready
                                        </span>
                                    @else
                                        <span class="status-badge pending">
                                            <i class="fas fa-clock mr-1"></i>Processing
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($letter->letter_file_path)
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="viewLetter({{ $letter->id }})" 
                                                    title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('letters.download', $letter->id) }}" 
                                               class="btn btn-primary btn-sm" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        
                                        @if(session('role') === 'admin' || $letter->created_by === session('id'))
                                            <button class="btn btn-danger btn-sm" 
                                                    onclick="deleteLetter({{ $letter->id }}, '{{ $letter->letter_reference }}')" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No letters found. Generate your first letter above!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($letters->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $letters->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Letter Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading preview...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="confirmGenerate()">
                    <i class="fas fa-file-pdf mr-2"></i>Generate Letter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Letter</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewContent">
                <!-- PDF viewer will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle recipient type change
    $('#recipient_type').on('change', function() {
        if ($(this).val() === 'trainee') {
            $('#traineeSelect').show();
            $('#recipient_name').prop('readonly', true);
        } else {
            $('#traineeSelect').hide();
            $('#trainee_id').val('');
            $('#recipient_name').prop('readonly', false).val('');
        }
    });

    // Handle trainee selection
    $('#trainee_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            $('#recipient_name').val(selectedOption.data('name'));
        }
    });

    // Handle form submission
    $('#letterGenerationForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating...');
        
        $.ajax({
            url: '{{ route("letters.generate") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Letter Generated!',
                        text: 'Reference: ' + response.reference_number,
                        showCancelButton: true,
                        confirmButtonText: 'Download Now',
                        cancelButtonText: 'Continue'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = response.download_url;
                        }
                        // Reload page to show new letter
                        location.reload();
                    });
                    
                    // Reset form
                    $('#letterGenerationForm')[0].reset();
                    $('#letter_date').val(new Date().toISOString().split('T')[0]);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Generation Failed',
                        text: response.message || 'An error occurred'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to generate letter'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});

// Preview letter
function previewLetter() {
    var formData = $('#letterGenerationForm').serialize();
    
    $('#previewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading preview...</p></div>');
    $('#previewModal').modal('show');
    
    $.post('{{ route("letters.preview") }}', formData)
        .done(function(response) {
            if (response.success) {
                $('#previewContent').html(response.html);
            } else {
                $('#previewContent').html('<div class="alert alert-danger">Failed to generate preview</div>');
            }
        })
        .fail(function() {
            $('#previewContent').html('<div class="alert alert-danger">Error loading preview</div>');
        });
}

// Confirm generate from preview
function confirmGenerate() {
    $('#previewModal').modal('hide');
    $('#letterGenerationForm').submit();
}

// View letter
function viewLetter(id) {
    $('#viewContent').html('<iframe src="' + '{{ route("letters.view", ":id") }}'.replace(':id', id) + '" style="width: 100%; height: 600px; border: none;"></iframe>');
    $('#viewModal').modal('show');
}

// Delete letter
function deleteLetter(id, reference) {
    Swal.fire({
        title: 'Delete Letter?',
        text: 'Reference: ' + reference,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("letters.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Letter has been deleted.', 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error!', 'Failed to delete letter.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete letter.', 'error');
                }
            });
        }
    });
}

// Search letters
function searchLetters() {
    var searchTerm = $('#searchInput').val();
    if (searchTerm) {
        window.location.href = '{{ route("letters.dashboard") }}?search=' + encodeURIComponent(searchTerm);
    } else {
        window.location.href = '{{ route("letters.dashboard") }}';
    }
}

// Handle enter key in search
$('#searchInput').on('keypress', function(e) {
    if (e.which === 13) {
        searchLetters();
    }
});
</script>
@endpush