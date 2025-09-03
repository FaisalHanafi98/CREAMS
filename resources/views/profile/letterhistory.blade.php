@extends('layouts.app')

@section('title', 'Letter History - CREAMS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">
                    <i class="fas fa-history"></i> Letter History
                </h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('profile.show') }}">Profile</a>
                    <span class="separator">/</span>
                    <span class="current">Letter History</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.letters.history') }}" class="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="Reference, recipient, or subject...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">From Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" 
                                   value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">To Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" 
                                   value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.letters.history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Letter Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-file-alt"></i> Letter 
                <span class="badge badge-secondary">{{ $letters->total() }}</span>
            </h5>
            <a href="{{ route('admin.letters.index') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Generate New Letter
            </a>
        </div>
        <div class="card-body p-0">
            @if($letters->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Reference</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-user"></i> Recipient</th>
                                <th><i class="fas fa-tag"></i> Subject</th>
                                <th><i class="fas fa-file-pdf"></i> PDF</th>
                                <th><i class="fas fa-clock"></i> Generated</th>
                                <th><i class="fas fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($letters as $letter)
                                <tr>
                                    <td>
                                        <code class="reference-code">{{ $letter->reference_number }}</code>
                                    </td>
                                    <td>
                                        <span class="text-nowrap">{{ $letter->letter_date->format('d M Y') }}</span>
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
                                        <span title="{{ $letter->subject }}">{{ \Str::limit($letter->subject, 40) }}</span>
                                    </td>
                                    <td>
                                        @if($letter->hasPdf())
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Available
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Missing
                                            </span>
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
                                        <div class="btn-group" role="group">
                                            @if($letter->hasPdf())
                                                <a href="{{ route('admin.letters.download', $letter->id) }}" 
                                                   class="btn btn-sm btn-primary" title="Download PDF">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-info view-letter" 
                                                    data-letter-id="{{ $letter->id }}" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#letterModal"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($letter->generated_by === session('id'))
                                                <button type="button" class="btn btn-sm btn-danger delete-letter" 
                                                        data-letter-id="{{ $letter->id }}" 
                                                        data-reference="{{ $letter->reference_number }}"
                                                        title="Delete Letter">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer">
                    @include('components.custom-pagination', ['items' => $letters])
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Letter Found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'start_date', 'end_date']))
                            No letters match your current filters.
                        @else
                            You haven't generated any letters yet.
                        @endif
                    </p>
                    <a href="{{ route('admin.letters.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Generate Your First Letter
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Letter Details Modal -->
<div class="modal fade" id="letterModal" tabindex="-1" role="dialog" aria-labelledby="letterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="letterModalLabel">
                    <i class="fas fa-file-alt"></i> Letter Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="letterDetails">
                <!-- Letter details will be loaded here -->
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Loading letter details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="downloadFromModal" class="btn btn-primary" style="display: none;">
                    <i class="fas fa-download"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

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

.dashboard-header {
    margin-bottom: 2rem;
}

.breadcrumb {
    font-size: 0.875em;
    color: #6c757d;
}

.breadcrumb a {
    color: #007bff;
    text-decoration: none;
}

.breadcrumb .separator {
    margin: 0 0.5rem;
    color: #dee2e6;
}

.breadcrumb .current {
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View letter details
    document.querySelectorAll('.view-letter').forEach(button => {
        button.addEventListener('click', function() {
            const letterId = this.dataset.letterId;
            loadLetterDetails(letterId);
        });
    });
    
    // Delete letter
    document.querySelectorAll('.delete-letter').forEach(button => {
        button.addEventListener('click', function() {
            const letterId = this.dataset.letterId;
            const reference = this.dataset.reference;
            
            Swal.fire({
                title: 'Delete Letter',
                text: `Are you sure you want to delete letter ${reference}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteLetter(letterId);
                }
            });
        });
    });
    
    function loadLetterDetails(letterId) {
        // In a real implementation, you would fetch letter details via AJAX
        document.getElementById('letterDetails').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-info-circle fa-2x text-primary mb-3"></i>
                <p class="text-muted">Letter details functionality would be implemented here.</p>
                <p class="text-muted">This would show the full letter content, metadata, and generation history.</p>
            </div>
        `;
    }
    
    function deleteLetter(letterId) {
        fetch(`/admin/letters/${letterId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Deleted!', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        });
    }
});
</script>
@endsection