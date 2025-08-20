@extends('layouts.app')

@section('title', 'Volunteer Application Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Volunteer Application Details</h1>
                    <p class="text-muted">Application #VA{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.volunteers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Applications
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Application Details -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Full Name:</strong>
                                    <p>{{ $application->name ?? 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Email:</strong>
                                    <p>{{ $application->email ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Phone:</strong>
                                    <p>{{ $application->phone ?? 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Date of Birth:</strong>
                                    <p>{{ $application->date_of_birth ? \Carbon\Carbon::parse($application->date_of_birth)->format('F j, Y') : 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Gender:</strong>
                                    <p>{{ ucfirst($application->gender ?? 'Not specified') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Occupation:</strong>
                                    <p>{{ $application->occupation ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <strong>Address:</strong>
                                    <p>{{ $application->address ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Volunteer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>Skills & Qualifications:</strong>
                                    <p>{{ $application->skills ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>Availability:</strong>
                                    <p>{{ $application->availability ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <strong>Motivation:</strong>
                                    <p>{{ $application->motivation ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($application->review_notes)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Review Notes</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ $application->review_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Status & Actions -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Application Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Current Status:</strong>
                                <br>
                                @if($application->status === 'applied')
                                    <span class="badge bg-warning fs-6 mt-2">Applied (Pending Review)</span>
                                @elseif($application->status === 'approved')
                                    <span class="badge bg-success fs-6 mt-2">Approved</span>
                                @elseif($application->status === 'active')
                                    <span class="badge bg-success fs-6 mt-2">Active Volunteer</span>
                                @elseif($application->status === 'reviewed')
                                    <span class="badge bg-info fs-6 mt-2">Under Review</span>
                                @else
                                    <span class="badge bg-danger fs-6 mt-2">Rejected</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>Application Date:</strong>
                                <p>{{ $application->created_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>

                            @if($application->reviewed_by)
                            <div class="mb-3">
                                <strong>Reviewed By:</strong>
                                <p>{{ $application->reviewedByUser->name ?? 'Unknown' }}</p>
                            </div>
                            @endif

                            @if($application->reviewed_at)
                            <div class="mb-3">
                                <strong>Review Date:</strong>
                                <p>{{ $application->reviewed_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                            @endif

                            <!-- Action Buttons -->
                            @if(in_array(session('role'), ['admin', 'supervisor']))
                                <div class="d-grid gap-2">
                                    @if($application->status === 'applied')
                                        <button class="btn btn-success" onclick="approveApplication({{ $application->id }})">
                                            <i class="fas fa-check"></i> Approve Application
                                        </button>
                                        <button class="btn btn-danger" onclick="rejectApplication({{ $application->id }})">
                                            <i class="fas fa-times"></i> Reject Application
                                        </button>
                                    @elseif($application->status === 'approved')
                                        <button class="btn btn-primary" onclick="activateVolunteer({{ $application->id }})">
                                            <i class="fas fa-user-check"></i> Activate Volunteer
                                        </button>
                                    @endif
                                    
                                    @if($application->status !== 'applied')
                                        <button class="btn btn-warning" onclick="updateStatus({{ $application->id }})">
                                            <i class="fas fa-edit"></i> Update Status
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Application Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Application ID:</strong>
                                <span>#VA{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Contact Method:</strong>
                                <span>Email</span>
                            </div>
                            <div class="mb-2">
                                <strong>Centre Assignment:</strong>
                                <span>Unassigned</span>
                            </div>
                            @if($application->skills)
                            <div class="mb-2">
                                <strong>Key Skills:</strong>
                                <span>{{ Str::limit($application->skills, 50) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modals -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle"></i> Approve Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitApproval()">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-times-circle"></i> Reject Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection *</label>
                        <textarea class="form-control" name="notes" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitRejection()">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentApplicationId = {{ $application->id }};

function approveApplication(id) {
    currentApplicationId = id;
    $('#approveModal').modal('show');
}

function rejectApplication(id) {
    currentApplicationId = id;
    $('#rejectModal').modal('show');
}

function submitApproval() {
    const formData = new FormData(document.getElementById('approveForm'));
    
    fetch(`/admin/volunteers/${currentApplicationId}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#approveModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to approve application'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the application');
    });
}

function submitRejection() {
    const formData = new FormData(document.getElementById('rejectForm'));
    
    if (!formData.get('notes').trim()) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    fetch(`/admin/volunteers/${currentApplicationId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#rejectModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to reject application'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the application');
    });
}

function activateVolunteer(id) {
    if (confirm('Are you sure you want to activate this volunteer?')) {
        // Implementation for activating volunteer
        console.log('Activate volunteer:', id);
    }
}

function updateStatus(id) {
    // Implementation for updating status
    console.log('Update status for:', id);
}
</script>
@endsection