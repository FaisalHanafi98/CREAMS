@extends('layouts.app')

@section('title', 'Volunteer Applications Management')

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
                                <option value="pending">Pending</option>
                                <option value="active">Approved</option>
                                <option value="inactive">Rejected</option>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applicationsTableBody">
                                @if($applications && $applications->count() > 0)
                                    @foreach($applications as $app)
                                        <tr>
                                            <td><strong>#VA{{ str_pad($app->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                            <td>{{ $app->volunteer_name }}</td>
                                            <td>{{ $app->volunteer_email }}</td>
                                            <td>{{ $app->volunteer_phone }}</td>
                                            <td>{{ $app->created_at->format('M j, Y') }}</td>
                                            <td>
                                                @if($app->volunteer_status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($app->volunteer_status === 'active')
                                                    <span class="badge bg-success">Approved</span>
                                                @else
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $app->centre ? $app->centre->centre_name : 'Unassigned' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="viewApplication({{ $app->id }})" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($app->volunteer_status === 'pending')
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
                    
                    @if($applications && $applications->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $applications->links() }}
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

@push('scripts')
<script>
// Global functions for button clicks
function viewApplication(applicationId) {
    console.log('viewApplication called with ID:', applicationId);
    
    // Use Bootstrap 4 modal method
    $('#applicationModal').modal('show');
    
    $('#applicationModalBody').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div><p class="mt-2">Loading application details...</p></div>');
    
    // Load application details
    $.ajax({
        url: `/volunteer/applications/${applicationId}`,
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                displayApplicationDetails(response.data);
            } else {
                $('#applicationModalBody').html('<div class="alert alert-danger">Error loading application details</div>');
            }
        },
        error: function(xhr) {
            $('#applicationModalBody').html('<div class="alert alert-danger">Error loading application details</div>');
        }
    });
}

function showApproveModal(applicationId) {
    console.log('showApproveModal called with ID:', applicationId);
    $('#approveApplicationId').val(applicationId);
    
    // Use Bootstrap 4 modal method
    $('#approveModal').modal('show');
}

function showRejectModal(applicationId) {
    console.log('showRejectModal called with ID:', applicationId);
    $('#rejectApplicationId').val(applicationId);
    
    // Use Bootstrap 4 modal method
    $('#rejectModal').modal('show');
}

// Application management functions
let currentApplications = [];

// Initialize page
$(document).ready(function() {
    console.log('Document ready - initializing volunteer page');
    
    // Applications are already loaded server-side
    // Initialize other functionality
    loadCentres();
    
    // Test if functions are accessible
    console.log('viewApplication function:', typeof viewApplication);
    console.log('showApproveModal function:', typeof showApproveModal);
    console.log('showRejectModal function:', typeof showRejectModal);
});

function loadApplications() {
    showLoading();
    
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
                renderApplications(currentApplications);
            } else {
                showError('Failed to load applications');
            }
        },
        error: function(xhr) {
            showError('Error loading applications: ' + (xhr.responseJSON?.message || 'Unknown error'));
        },
        complete: function() {
            hideLoading();
        }
    });
}

function renderApplications(applications) {
    const tbody = $('#applicationsTableBody');
    tbody.empty();

    if (applications.length === 0) {
        $('#emptyState').show();
        return;
    }

    $('#emptyState').hide();

    applications.forEach(function(app) {
        const statusBadge = getStatusBadge(app.volunteer_status);
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

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending</span>',
        'active': '<span class="badge bg-success">Approved</span>',
        'inactive': '<span class="badge bg-danger">Rejected</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function displayApplicationDetails(app) {
    const statusBadge = app.volunteer_status === 'pending' ? '<span class="badge bg-warning">Pending</span>' :
                        app.volunteer_status === 'active' ? '<span class="badge bg-success">Approved</span>' :
                        '<span class="badge bg-danger">Rejected</span>';
    
    const centreName = app.centre ? app.centre.centre_name : 'Unassigned';
    const approvedBy = app.approved_by_user ? app.approved_by_user.name : 'N/A';
    
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
                <h5>Personal Information</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Name:</th><td>${app.volunteer_name}</td></tr>
                    <tr><th>Email:</th><td><a href="mailto:${app.volunteer_email}">${app.volunteer_email}</a></td></tr>
                    <tr><th>Phone:</th><td><a href="tel:${app.volunteer_phone}">${app.volunteer_phone}</a></td></tr>
                    <tr><th>Gender:</th><td>${app.volunteer_gender || 'Not specified'}</td></tr>
                    <tr><th>Birth Date:</th><td>${formatDate(app.volunteer_birth_date)}</td></tr>
                    <tr><th>Address:</th><td>${app.volunteer_address || 'Not specified'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5>Application Details</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Status:</th><td>${statusBadge}</td></tr>
                    <tr><th>Centre:</th><td>${centreName}</td></tr>
                    <tr><th>Applied:</th><td>${formatDate(app.created_at)}</td></tr>
                    <tr><th>Approved By:</th><td>${approvedBy}</td></tr>
                    <tr><th>Availability:</th><td>${app.volunteer_availability || 'Not specified'}</td></tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h5>Skills & Experience</h5>
                <div class="mb-3">
                    <strong>Skills:</strong>
                    <div class="p-2 bg-light rounded">${app.volunteer_skills || 'Not specified'}</div>
                </div>
                <div class="mb-3">
                    <strong>Experience:</strong>
                    <div class="p-2 bg-light rounded">${app.volunteer_experience || 'Not specified'}</div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h5>Emergency Contact</h5>
                <div class="p-2 bg-light rounded">
                    <p class="mb-1"><strong>Name:</strong> ${app.emergency_contact_name || 'Not provided'}</p>
                    <p class="mb-0"><strong>Phone:</strong> ${app.emergency_contact_phone ? `<a href="tel:${app.emergency_contact_phone}">${app.emergency_contact_phone}</a>` : 'Not provided'}</p>
                </div>
            </div>
        </div>
        
        ${app.admin_notes ? `
        <div class="row mt-3">
            <div class="col-12">
                <h5>Admin Notes</h5>
                <div class="alert alert-info">${app.admin_notes}</div>
            </div>
        </div>
        ` : ''}
    `;
    
    $('#applicationModalBody').html(html);
    
    // Add action buttons if pending
    if (app.volunteer_status === 'pending') {
        $('#modalActions').html(`
            <button type="button" class="btn btn-success" onclick="showApproveModal(${app.id})" data-dismiss="modal">
                <i class="fas fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-danger" onclick="showRejectModal(${app.id})" data-dismiss="modal">
                <i class="fas fa-times"></i> Reject
            </button>
        `);
    } else {
        $('#modalActions').html('');
    }
}

function submitApproval() {
    const formData = {
        centre_id: $('#approveCentreId').val(),
        notes: $('#approveNotes').val()
    };

    const applicationId = $('#approveApplicationId').val();

    $.ajax({
        url: `/volunteer/applications/${applicationId}/approve`,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Application approved successfully!');
                
                // Hide modal using Bootstrap 4 method
                $('#approveModal').modal('hide');
                
                // Reload page to show updated data
                window.location.reload();
            } else {
                showError(response.message || 'Error approving application');
            }
        },
        error: function(xhr) {
            showError('Error approving application: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
}

function submitRejection() {
    const formData = {
        notes: $('#rejectNotes').val()
    };

    if (!formData.notes.trim()) {
        showError('Please provide a reason for rejection');
        return;
    }

    const applicationId = $('#rejectApplicationId').val();

    $.ajax({
        url: `/volunteer/applications/${applicationId}/reject`,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Application rejected');
                
                // Hide modal using Bootstrap 4 method
                $('#rejectModal').modal('hide');
                
                // Reload page to show updated data
                window.location.reload();
            } else {
                showError(response.message || 'Error rejecting application');
            }
        },
        error: function(xhr) {
            showError('Error rejecting application: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
}

function filterApplications() {
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

function refreshApplications() {
    window.location.reload();
}

function showLoading() {
    $('#loadingSpinner').show();
    $('#applicationsTableBody').empty();
    $('#emptyState').hide();
}

function hideLoading() {
    $('#loadingSpinner').hide();
}

function showSuccess(message) {
    // You can replace this with your preferred notification system
    alert('Success: ' + message);
}

function showError(message) {
    // You can replace this with your preferred notification system
    alert('Error: ' + message);
}

// Load centres for filters and approval modal
function loadCentres() {
    // This would typically load from an API endpoint
    // For now, we'll assume the centres are available
}
</script>
@endpush
@endsection