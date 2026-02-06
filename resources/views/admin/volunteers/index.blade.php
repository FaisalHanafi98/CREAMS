@extends('layouts.app')

@section('title', 'Volunteer Applications Management')

@section('styles')
<style>
/* Enhanced Pagination Styling */
.pagination-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.pagination-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
    margin-right: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.pagination-info .info-highlight {
    font-weight: 700;
    color: var(--primary-color, #007bff);
}

.pagination {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
}

.page-item {
    margin: 0;
}

.page-link {
    border: none;
    background: transparent;
    color: #6c757d;
    padding: 10px 15px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 45px;
    height: 45px;
    text-decoration: none;
}

.page-link:hover {
    background: rgba(0, 123, 255, 0.1);
    color: var(--primary-color, #007bff);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary-color, #007bff), var(--secondary-color, #6f42c1));
    color: white;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    transform: translateY(-2px);
}

.page-item.disabled .page-link {
    color: #cbd5e0;
    background: #f8f9fa;
    cursor: not-allowed;
}

.page-item.disabled .page-link:hover {
    background: #f8f9fa;
    color: #cbd5e0;
    transform: none;
    box-shadow: none;
}

/* Navigation buttons styling */
.page-item:first-child .page-link,
.page-item:last-child .page-link {
    background: rgba(0, 123, 255, 0.05);
    color: var(--primary-color, #007bff);
    font-weight: 700;
}

.page-item:first-child .page-link:hover,
.page-item:last-child .page-link:hover {
    background: rgba(0, 123, 255, 0.15);
    color: var(--primary-color, #007bff);
}

/* Responsive pagination */
@media (max-width: 768px) {
    .pagination-wrapper {
        flex-direction: column;
        gap: 15px;
    }

    .pagination-info {
        margin-right: 0;
        text-align: center;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }

    .page-link {
        padding: 8px 12px;
        font-size: 13px;
        min-width: 40px;
        height: 40px;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Volunteer Applications</h1>
                    <p class="text-muted">Manage and review volunteer applications for your centre</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshApplications()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="exportApplications()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Standardized Flash Messages -->
            @include('components.flash-messages')

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <div class="h2 text-warning mb-1">{{ $stats['pending'] }}</div>
                            <div class="text-muted">Pending Applications</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="h2 text-success mb-1">{{ $stats['approved'] }}</div>
                            <div class="text-muted">Approved Volunteers</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <div class="h2 text-danger mb-1">{{ $stats['rejected'] }}</div>
                            <div class="text-muted">Rejected Applications</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <div class="h2 text-info mb-1">{{ $stats['pending'] + $stats['approved'] + $stats['rejected'] }}</div>
                            <div class="text-muted">Total Applications</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Filters</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-select" onchange="filterApplications()">
                                <option value="">All Statuses</option>
                                <option value="applied">Applied (Pending)</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                        @if(session('role') === 'admin')
                        <div class="col-md-3">
                            <label class="form-label">Centre</label>
                            <select id="centreFilter" class="form-select" onchange="filterApplications()">
                                <option value="">All Centres</option>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <input type="date" id="dateFromFilter" class="form-control" onchange="filterApplications()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <input type="date" id="dateToFilter" class="form-control" onchange="filterApplications()">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="applicationsTable">
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Centre</th>
                                    <th>Reviewed By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applicationsTableBody">
                                @if($applications && $applications->count() > 0)
                                    @foreach($applications as $app)
                                        <tr>
                                            <td><strong>#VA{{ str_pad($app->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                            <td>{{ $app->name }}</td>
                                            <td>{{ $app->email }}</td>
                                            <td>{{ $app->phone }}</td>
                                            <td>{{ $app->created_at->format('M j, Y') }}</td>
                                            <td>
                                                @if($app->status === 'applied')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($app->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($app->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $app->centre ? $app->centre->centre_name : 'Unassigned' }}</td>
                                            <td>{{ $app->reviewedByUser ? $app->reviewedByUser->name : 'Not reviewed' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="viewApplication({{ $app->id }})" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($app->status === 'applied')
                                                        <button class="btn btn-outline-success" onclick="showApproveModal({{ $app->id }})" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="showRejectModal({{ $app->id }})" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div id="loadingSpinner" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    @if(!$applications || $applications->count() === 0)
                        <div id="emptyState" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No applications found</h5>
                            <p class="text-muted">Try adjusting your filters or check back later.</p>
                        </div>
                    @endif
                    
                    <!-- Enhanced Pagination -->
                    @if($applications && $applications->hasPages())
                    <div class="pagination-container">
                        <div class="pagination-wrapper">
                            <div class="pagination-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing <span class="info-highlight">{{ $applications->firstItem() }}</span> to 
                                <span class="info-highlight">{{ $applications->lastItem() }}</span> of 
                                <span class="info-highlight">{{ $applications->total() }}</span> applications
                            </div>
                            
                            <nav aria-label="Volunteer applications pagination">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($applications->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">
                                                <i class="fas fa-chevron-left me-1"></i>Previous
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $applications->previousPageUrl() }}" rel="prev">
                                                <i class="fas fa-chevron-left me-1"></i>Previous
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $start = max($applications->currentPage() - 2, 1);
                                        $end = min($start + 4, $applications->lastPage());
                                        $start = max($end - 4, 1);
                                    @endphp

                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $applications->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        @if ($i == $applications->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $applications->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if($end < $applications->lastPage())
                                        @if($end < $applications->lastPage() - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $applications->url($applications->lastPage()) }}">{{ $applications->lastPage() }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($applications->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $applications->nextPageUrl() }}" rel="next">
                                                Next<i class="fas fa-chevron-right ms-1"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">
                                                Next<i class="fas fa-chevron-right ms-1"></i>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Detail Modal -->
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationModalLabel">Application Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="applicationModalBody">
                <!-- Will be populated via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <div id="modalActions">
                    <!-- Action buttons will be added here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="approveModalLabel">
                    <i class="fas fa-check-circle"></i> Approve Application
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    <input type="hidden" id="approveApplicationId" name="application_id">
                    
                    @if(session('role') === 'admin' && !session('centre_id'))
                    <div class="mb-3">
                        <label for="approveCentreId" class="form-label">Assign to Centre *</label>
                        <select class="form-select" id="approveCentreId" name="centre_id" required>
                            <option value="">Select Centre</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    @elseif(session('centre_id'))
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Auto-assignment:</strong> This volunteer will be assigned to your centre.
                        </div>
                        <input type="hidden" id="approveCentreId" name="centre_id" value="{{ session('centre_id') }}">
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="approveNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approveNotes" name="notes" rows="3" 
                                placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitApproval()">
                    <i class="fas fa-check"></i> Approve Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="rejectModalLabel">
                    <i class="fas fa-times-circle"></i> Reject Application
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="rejectApplicationId" name="application_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> Rejecting this application will send a notification email to the applicant.
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label">Reason for Rejection *</label>
                        <textarea class="form-control" id="rejectNotes" name="notes" rows="4" required
                                placeholder="Please provide a clear reason for rejection. This will be included in the email to the applicant."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitRejection()">
                    <i class="fas fa-times"></i> Reject Application
                </button>
            </div>
        </div>
    </div>
</div>


<script>
// Helper function to display application details (must be defined first)
window.displayApplicationDetails = function(app) {
    const getStatusBadge = (status) => {
        if (status === 'applied') return '<span class="badge bg-warning">Applied (Pending)</span>';
        if (status === 'approved') return '<span class="badge bg-success">Approved</span>';
        if (status === 'active') return '<span class="badge bg-success">Active</span>';
        if (status === 'rejected') return '<span class="badge bg-danger">Rejected</span>';
        return '<span class="badge bg-secondary">Unknown</span>';
    };
    
    const statusBadge = getStatusBadge(app.status);
    const centreName = app.centre ? app.centre.centre_name : 'Unassigned';
    const reviewedBy = app.reviewed_by_user ? app.reviewed_by_user.name : 'Not reviewed';
    
    const formatDate = (dateStr) => {
        if (!dateStr || dateStr === 'Not specified') return 'Not specified';
        try {
            return new Date(dateStr).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long', 
                day: 'numeric'
            });
        } catch (e) {
            return dateStr;
        }
    };
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-user text-primary"></i> Personal Information</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Name:</th><td>${app.name || 'Not provided'}</td></tr>
                    <tr><th>Email:</th><td><a href="mailto:${app.email || ''}">${app.email || 'Not provided'}</a></td></tr>
                    <tr><th>Phone:</th><td><a href="tel:${app.phone || ''}">${app.phone || 'Not provided'}</a></td></tr>
                    <tr><th>Gender:</th><td>${app.gender ? app.gender.charAt(0).toUpperCase() + app.gender.slice(1) : 'Not specified'}</td></tr>
                    <tr><th>Birth Date:</th><td>${formatDate(app.date_of_birth)}</td></tr>
                    <tr><th>Address:</th><td>${app.address || 'Not specified'}</td></tr>
                    <tr><th>Occupation:</th><td>${app.occupation || 'Not specified'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-clipboard-list text-info"></i> Application Details</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Application ID:</th><td>#VA${String(app.id).padStart(6, '0')}</td></tr>
                    <tr><th>Status:</th><td>${statusBadge}</td></tr>
                    <tr><th>Centre:</th><td>${centreName}</td></tr>
                    <tr><th>Applied:</th><td>${formatDate(app.created_at)}</td></tr>
                    <tr><th>Reviewed By:</th><td>${reviewedBy}</td></tr>
                    <tr><th>Review Date:</th><td>${app.reviewed_at ? formatDate(app.reviewed_at) : 'Not reviewed'}</td></tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h5><i class="fas fa-tools text-success"></i> Skills & Volunteer Information</h5>
                <div class="mb-3">
                    <strong>Skills & Qualifications:</strong>
                    <div class="p-2 bg-light rounded">${app.skills || 'Not specified'}</div>
                </div>
                <div class="mb-3">
                    <strong>Availability:</strong>
                    <div class="p-2 bg-light rounded">${app.availability || 'Not specified'}</div>
                </div>
                <div class="mb-3">
                    <strong>Motivation:</strong>
                    <div class="p-2 bg-light rounded">${app.motivation || 'Not specified'}</div>
                </div>
            </div>
        </div>
        
        ${app.review_notes ? `
        <div class="row mt-3">
            <div class="col-12">
                <h5><i class="fas fa-sticky-note text-warning"></i> Review Notes</h5>
                <div class="alert alert-info">${app.review_notes}</div>
            </div>
        </div>
        ` : ''}
    `;
    
    $('#applicationModalBody').html(html);
    
    // Add action buttons if pending
    if (app.status === 'applied') {
        $('#modalActions').html(`
            <button type="button" class="btn btn-success" onclick="window.showApproveModal(${app.id})" data-bs-dismiss="modal">
                <i class="fas fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-danger" onclick="window.showRejectModal(${app.id})" data-bs-dismiss="modal">
                <i class="fas fa-times"></i> Reject
            </button>
        `);
    } else {
        $('#modalActions').html('');
    }
};

// Global functions for button clicks - explicitly declare in window scope
window.viewApplication = function(applicationId) {
    console.log('viewApplication called with ID:', applicationId);
    
    // Validate applicationId
    if (!applicationId || applicationId === 'undefined' || applicationId === 'null') {
        console.error('Invalid applicationId:', applicationId);
        alert('Error: Invalid application ID');
        return;
    }
    
    // Check if modal exists
    const modal = $('#applicationModal');
    if (modal.length === 0) {
        console.error('Modal #applicationModal not found');
        alert('Error: Modal not found on page');
        return;
    }
    
    // Show modal first
    modal.modal('show');
    
    // Set loading content
    $('#applicationModalBody').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div><p class="mt-2">Loading application details...</p></div>');
    
    // Prepare AJAX call
    const ajaxUrl = `/volunteer/applications/${applicationId}`;
    console.log('Making AJAX call to:', ajaxUrl);
    
    // Load application details with better error handling
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 10000,
        success: function(response) {
            console.log('AJAX success response:', response);
            if (response && response.success) {
                window.displayApplicationDetails(response.data);
            } else {
                console.error('Invalid response format:', response);
                $('#applicationModalBody').html('<div class="alert alert-danger">Error: Invalid response format</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error Details:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            let errorMessage = 'Error loading application details';
            if (xhr.status === 401) {
                errorMessage = 'Authentication required. Please log in again.';
            } else if (xhr.status === 403) {
                errorMessage = 'Access denied. You do not have permission to view this application.';
            } else if (xhr.status === 404) {
                errorMessage = 'Application not found.';
            } else if (xhr.status >= 500) {
                errorMessage = 'Server error. Please try again later.';
            }
            
            // SECURITY: Escape error messages to prevent XSS
            $('#applicationModalBody').html(`<div class="alert alert-danger">${escapeHtml(errorMessage)}<br><small>Status: ${xhr.status} ${escapeHtml(error)}</small></div>`);
        }
    });
}

window.showApproveModal = function(applicationId) {
    console.log('🟢 showApproveModal called with ID:', applicationId);
    
    try {
        $('#approveApplicationId').val(applicationId);
        $('#approveModal').modal('show');
        console.log('✅ Modal opened successfully');
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error: ' + error.message);
    }
}

window.showRejectModal = function(applicationId) {
    console.log('🔴 showRejectModal called with ID:', applicationId);
    
    try {
        $('#rejectApplicationId').val(applicationId);
        $('#rejectNotes').val('');
        $('#rejectModal').modal('show');
        console.log('✅ Reject modal opened successfully');
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error: ' + error.message);
    }
}

// Refresh and Export functions - define early
window.refreshApplications = function() {
    console.log('Refresh button clicked');
    window.location.reload();
}

window.exportApplications = function() {
    console.log('Export button clicked');
    // Show warning that export will be implemented in the future
    if (confirm('📊 Export Functionality\n\nThe export feature is planned for future implementation. This will allow you to export volunteer applications data in various formats (Excel, PDF, CSV).\n\nWould you like to be notified when this feature becomes available?')) {
        alert('Success: Thank you for your interest! You will be notified when the export functionality is implemented.');
    }
}

// Application management functions
let currentApplications = [];

// Initialize page - wait for jQuery to be available
$(document).ready(function() {
    console.log('Document ready - initializing volunteer page');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Bootstrap loaded:', typeof $.fn.modal);
    
    // Test if functions are accessible
    console.log('viewApplication function:', typeof viewApplication);
    console.log('showApproveModal function:', typeof showApproveModal);
    console.log('showRejectModal function:', typeof showRejectModal);
    
    // Check if modals exist
    console.log('Application modal exists:', $('#applicationModal').length > 0);
    console.log('Approve modal exists:', $('#approveModal').length > 0);
    console.log('Reject modal exists:', $('#rejectModal').length > 0);
    
    // Check if CSRF token exists
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    console.log('CSRF token exists:', !!csrfToken);
    console.log('CSRF token:', csrfToken ? csrfToken.substring(0, 10) + '...' : 'NOT FOUND');
    
    // Check if buttons exist
    const buttons = $('.btn[onclick*="viewApplication"], .btn[onclick*="showApproveModal"], .btn[onclick*="showRejectModal"]');
    console.log('Action buttons found:', buttons.length);
    
    // Add click listeners for debugging
    $(document).on('click', 'button[onclick*="showApproveModal"]', function(e) {
        console.log('🟢 Approve button ACTUALLY clicked!', this);
        console.log('Button onclick:', $(this).attr('onclick'));
    });
    
    $(document).on('click', 'button[onclick*="showRejectModal"]', function(e) {
        console.log('🔴 Reject button ACTUALLY clicked!', this);
        console.log('Button onclick:', $(this).attr('onclick'));
    });
    
    // Add test button for debugging
    if (window.location.search.includes('debug=1')) {
        const testButton = '<button class="btn btn-info btn-sm" onclick="testButtonFunctionality()">🔧 Test Buttons</button>';
        $('.page-header .d-flex').append(testButton);
    }
    
    // Add simple test buttons for troubleshooting
    const debugButtons = `
        <div class="alert alert-info mt-3">
            <strong>Debug Tools:</strong>
            <button class="btn btn-sm btn-primary ms-2" onclick="window.showApproveModal(3)">Test Approve Modal</button>
            <button class="btn btn-sm btn-danger ms-2" onclick="window.showRejectModal(3)">Test Reject Modal</button>
            <button class="btn btn-sm btn-info ms-2" onclick="console.log('Functions loaded:', {approve: typeof window.showApproveModal, reject: typeof window.showRejectModal})">Check Functions</button>
        </div>
    `;
    $('.page-header').after(debugButtons);
    
    // Applications are already loaded server-side
    // Initialize other functionality
    window.loadCentres();
    
    // Verify functions are in global scope
    console.log('✅ Functions loaded in global scope:');
    console.log('- viewApplication:', typeof window.viewApplication);
    console.log('- showApproveModal:', typeof window.showApproveModal);
    console.log('- showRejectModal:', typeof window.showRejectModal);
    console.log('- submitApproval (global):', typeof submitApproval);
    console.log('- submitRejection (global):', typeof submitRejection);
    console.log('- refreshApplications:', typeof window.refreshApplications);
    console.log('- exportApplications:', typeof window.exportApplications);
    
    // Functions should be available in global scope already
});

// Test function for debugging
window.testButtonFunctionality = function() {
    console.log('=== BUTTON FUNCTIONALITY TEST ===');
    
    // Test if we can find the first volunteer
    const firstButton = $('button[onclick*="viewApplication"]').first();
    if (firstButton.length > 0) {
        const onclickAttr = firstButton.attr('onclick');
        console.log('First button onclick:', onclickAttr);
        
        // Extract ID from onclick
        const match = onclickAttr.match(/viewApplication\((\d+)\)/);
        if (match) {
            const testId = match[1];
            console.log('Testing with ID:', testId);
            
            // Test each function
            console.log('Testing viewApplication...');
            window.viewApplication(testId);
        } else {
            console.error('Could not extract ID from onclick');
        }
    } else {
        console.error('No view buttons found');
    }
}

window.loadApplications = function() {
    window.showLoading();
    
    const filters = {
        status: $('#statusFilter').val(),
        centre_id: $('#centreFilter').val(),
        date_from: $('#dateFromFilter').val(),
        date_to: $('#dateToFilter').val()
    };

    $.ajax({
        url: '{{ route("volunteer.applications") }}',
        method: 'GET',
        data: filters,
        success: function(response) {
            if (response.success) {
                currentApplications = response.data.data;
                window.renderApplications(currentApplications);
            } else {
                window.showError('Failed to load applications');
            }
        },
        error: function(xhr) {
            window.showError('Error loading applications: ' + (xhr.responseJSON?.message || 'Unknown error'));
        },
        complete: function() {
            window.hideLoading();
        }
    });
}

window.renderApplications = function(applications) {
    const tbody = $('#applicationsTableBody');
    tbody.empty();

    if (applications.length === 0) {
        $('#emptyState').show();
        return;
    }

    $('#emptyState').hide();

    applications.forEach(function(app) {
        const statusBadge = window.getStatusBadge(app.volunteer_status);
        const centreName = app.centre ? app.centre.centre_name : 'Unassigned';
        const appliedDate = new Date(app.created_at).toLocaleDateString();

        const row = `
            <tr>
                <td><strong>#VA${String(app.id).padStart(6, '0')}</strong></td>
                <td>${app.volunteer_name}</td>
                <td>${app.volunteer_email}</td>
                <td>${app.volunteer_phone}</td>
                <td>${appliedDate}</td>
                <td>${statusBadge}</td>
                <td>${centreName}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewApplication(${app.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${app.volunteer_status === 'pending' ? `
                            <button class="btn btn-outline-success" onclick="showApproveModal(${app.id})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="showRejectModal(${app.id})" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

window.getStatusBadge = function(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending</span>',
        'active': '<span class="badge bg-success">Approved</span>',
        'inactive': '<span class="badge bg-danger">Rejected</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}


// Old submitApproval function removed - using global function instead

// Old submitRejection function removed - using global function instead

window.filterApplications = function() {
    // Since we're using server-side rendering, reload the page with filters
    const status = $('#statusFilter').val();
    const centreId = $('#centreFilter').val();
    const dateFrom = $('#dateFromFilter').val();
    const dateTo = $('#dateToFilter').val();
    
    let url = window.location.pathname;
    let params = new URLSearchParams();
    
    if (status) params.append('status', status);
    if (centreId) params.append('centre_id', centreId);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    window.location.href = url;
}

window.showLoading = function() {
    $('#loadingSpinner').show();
    $('#applicationsTableBody').empty();
    $('#emptyState').hide();
}

window.hideLoading = function() {
    $('#loadingSpinner').hide();
}

window.showSuccess = function(message) {
    // You can replace this with your preferred notification system
    alert('Success: ' + message);
}

window.showError = function(message) {
    // You can replace this with your preferred notification system
    alert('Error: ' + message);
}

// Load centres for filters and approval modal
window.loadCentres = function() {
    $.ajax({
        url: '{{ route("volunteer.centres") }}',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.data) {
                const centreFilter = $('#centreFilter');
                const approveCentreId = $('#approveCentreId');
                
                // Populate filter dropdown
                centreFilter.empty().append('<option value="">All Centres</option>');
                
                // Populate approve modal dropdown
                approveCentreId.empty().append('<option value="">Select Centre</option>');
                
                response.data.forEach(function(centre) {
                    centreFilter.append(`<option value="${centre.centre_id}">${centre.centre_name}</option>`);
                    approveCentreId.append(`<option value="${centre.centre_id}">${centre.centre_name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading centres:', xhr.responseJSON?.message || 'Unknown error');
        }
    });
}

// Create global functions for onclick handlers
function submitApproval() {
    console.log('🟢 Global submitApproval called');
    
    if (typeof $ === 'undefined') {
        alert('jQuery is not available. Please refresh the page.');
        return;
    }
    
    const formData = {
        centre_id: document.getElementById('approveCentreId').value,
        notes: document.getElementById('approveNotes').value
    };
    
    const applicationId = document.getElementById('approveApplicationId').value;
    
    console.log('Submitting approval for ID:', applicationId, 'with data:', formData);
    
    // Use jQuery for AJAX
    $.ajax({
        url: `/volunteer/applications/${applicationId}/approve`,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                alert('Application approved successfully!');
                $('#approveModal').modal('hide');
                window.location.reload();
            } else {
                alert('Error: ' + (response.message || 'Error approving application'));
            }
        },
        error: function(xhr) {
            alert('Error approving application: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
}

function submitRejection() {
    console.log('🔴 Global submitRejection called');
    
    if (typeof $ === 'undefined') {
        alert('jQuery is not available. Please refresh the page.');
        return;
    }
    
    const formData = {
        notes: document.getElementById('rejectNotes').value
    };
    
    if (!formData.notes.trim()) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    const applicationId = document.getElementById('rejectApplicationId').value;
    
    console.log('Submitting rejection for ID:', applicationId, 'with data:', formData);
    
    // Use jQuery for AJAX
    $.ajax({
        url: `/volunteer/applications/${applicationId}/reject`,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                alert('Application rejected successfully!');
                $('#rejectModal').modal('hide');
                window.location.reload();
            } else {
                alert('Error: ' + (response.message || 'Error rejecting application'));
            }
        },
        error: function(xhr) {
            alert('Error rejecting application: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
}

console.log('🔧 Global functions defined:');
console.log('submitApproval:', typeof submitApproval);
console.log('submitRejection:', typeof submitRejection);

</script>

@endsection