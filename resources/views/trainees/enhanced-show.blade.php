@extends('layouts.app')

@section('title', 'Trainee Details - ' . $trainee->name . ' - CREAMS')

@section('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #667eea 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0.5rem;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        object-fit: cover;
    }
    
    .info-card {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: transform 0.2s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
    }
    
    .info-card h6 {
        color: var(--primary-color);
        border-bottom: 2px solid #e3e6f0;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
    }
    
    .progress-ring {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    
    .progress-ring svg {
        transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
        stroke-dasharray: 251.2; /* 2 * π * 40 */
        stroke-dashoffset: 251.2;
        transition: stroke-dashoffset 0.5s ease-in-out;
        stroke: var(--primary-color);
        stroke-width: 6;
        fill: transparent;
        r: 40;
        cx: 50;
        cy: 50;
    }
    
    .progress-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary-color);
    }
    
    .document-item {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }
    
    .document-item:hover {
        background: #e3f2fd;
        border-color: var(--primary-color);
    }
    
    .document-expired {
        border-color: #dc3545;
        background: #fff5f5;
    }
    
    .document-expiring {
        border-color: #ffc107;
        background: #fffdf5;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
        display: block;
    }
    
    .stat-label {
        color: #858796;
        font-size: 0.875rem;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .medical-alert {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .quick-actions {
        position: sticky;
        top: 2rem;
        z-index: 100;
    }
    
    .action-btn {
        width: 100%;
        margin-bottom: 0.5rem;
        border-radius: 0.375rem;
        font-weight: 600;
    }
    
    .tag {
        display: inline-block;
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
    }
    
    .assessment-score {
        background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.5rem;
        border-radius: 0.375rem;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    
    .guardian-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
    }
    
    .emergency-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="{{ $trainee->avatar_url }}" alt="Avatar" class="profile-avatar">
                </div>
                <div class="col">
                    <h2 class="mb-1">{{ $trainee->name }}</h2>
                    <p class="mb-2 opacity-75">
                        <strong>ID:</strong> {{ $trainee->unique_identifier }} |
                        <strong>Age:</strong> {{ $trainee->age }} years |
                        <strong>Condition:</strong> {{ $trainee->trainee_condition }}
                    </p>
                    <div>
                        <span class="status-badge badge-{{ $trainee->status === 'active' ? 'success' : ($trainee->status === 'graduated' ? 'info' : 'secondary') }}">
                            {{ ucfirst($trainee->status) }}
                        </span>
                        @if($trainee->tags)
                            @foreach($trainee->tags as $tag)
                            <span class="tag">{{ $tag }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-right">
                        <small class="opacity-75">Admitted</small><br>
                        <strong>{{ $trainee->admission_date ? $trainee->admission_date->format('M d, Y') : 'N/A' }}</strong>
                        @if($trainee->graduation_date)
                        <br><small class="opacity-75">Graduated</small><br>
                        <strong>{{ $trainee->graduation_date->format('M d, Y') }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Statistics Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-value">{{ $progressStats['assessments_count'] ?? 0 }}</span>
                    <span class="stat-label">Assessments</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value">{{ round($progressStats['average_progress'] ?? 0) }}%</span>
                    <span class="stat-label">Avg Progress</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value">{{ $trainee->documents->count() }}</span>
                    <span class="stat-label">Documents</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value">{{ $trainee->auditLogs->count() }}</span>
                    <span class="stat-label">Activity Logs</span>
                </div>
            </div>

            <!-- Medical Alerts -->
            @if($trainee->medical_info && (isset($trainee->medical_info['allergies']) || isset($trainee->medical_info['medications'])))
            <div class="medical-alert">
                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Medical Information</h6>
                @if(isset($trainee->medical_info['allergies']))
                <p><strong>Allergies:</strong> {{ $trainee->medical_info['allergies'] }}</p>
                @endif
                @if(isset($trainee->medical_info['medications']))
                <p class="mb-0"><strong>Current Medications:</strong> {{ $trainee->medical_info['medications'] }}</p>
                @endif
            </div>
            @endif

            <!-- Progress Assessment -->
            @if($progressStats['latest_assessment'])
            <div class="info-card">
                <h6><i class="fas fa-chart-line mr-2"></i>Latest Progress Assessment</h6>
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="progress-ring">
                            <svg width="100" height="100">
                                <circle class="progress-ring-circle" r="40" cx="50" cy="50"
                                        style="stroke-dashoffset: {{ 251.2 - (251.2 * ($progressStats['latest_assessment']->current_score / 100)) }}"></circle>
                            </svg>
                            <div class="progress-value">{{ $progressStats['latest_assessment']->current_score }}%</div>
                        </div>
                        <small class="text-muted">Current Score</small>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>Skill Area:</strong> {{ $progressStats['latest_assessment']->skill_area }}<br>
                                <strong>Assessment Date:</strong> {{ $progressStats['latest_assessment']->assessment_date->format('M d, Y') }}<br>
                                <strong>Assessed By:</strong> {{ $progressStats['latest_assessment']->assessor->name ?? 'Unknown' }}
                            </div>
                            <div class="col-sm-6">
                                @if($progressStats['latest_assessment']->baseline_score)
                                <div class="assessment-score">
                                    <small>Baseline</small><br>
                                    <strong>{{ $progressStats['latest_assessment']->baseline_score }}%</strong>
                                </div>
                                @endif
                                @if($progressStats['latest_assessment']->target_score)
                                <div class="assessment-score">
                                    <small>Target</small><br>
                                    <strong>{{ $progressStats['latest_assessment']->target_score }}%</strong>
                                </div>
                                @endif
                            </div>
                        </div>
                        @if($progressStats['latest_assessment']->notes)
                        <div class="mt-3">
                            <strong>Notes:</strong>
                            <p class="mb-0">{{ $progressStats['latest_assessment']->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($progressStats['needs_assessment'])
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-clock mr-2"></i>This trainee needs a progress assessment (last assessed over 3 months ago).
                </div>
                @endif
            </div>
            @endif

            <!-- Personal Information -->
            <div class="info-card">
                <h6><i class="fas fa-user mr-2"></i>Personal Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td>{{ $trainee->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date of Birth:</strong></td>
                                <td>{{ $trainee->trainee_date_of_birth ? $trainee->trainee_date_of_birth->format('M d, Y') : 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Age:</strong></td>
                                <td>{{ $trainee->age }} years</td>
                            </tr>
                            <tr>
                                <td><strong>Gender:</strong></td>
                                <td>{{ ucfirst($trainee->gender ?? 'Not specified') }}</td>
                            </tr>
                            <tr>
                                <td><strong>IC Number:</strong></td>
                                <td>{{ $trainee->ic_number ?? 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $trainee->trainee_email ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $trainee->trainee_phone_number ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>{{ $trainee->trainee_address ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Condition:</strong></td>
                                <td>
                                    <span class="badge badge-outline-primary">{{ $trainee->trainee_condition }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $trainee->status === 'active' ? 'success' : ($trainee->status === 'graduated' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($trainee->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($trainee->medical_history)
                <div class="mt-3">
                    <strong>Medical History:</strong>
                    <p class="mb-0">{{ $trainee->medical_history }}</p>
                </div>
                @endif
            </div>

            <!-- Documents -->
            <div class="info-card">
                <h6><i class="fas fa-file-alt mr-2"></i>Documents 
                    @if($trainee->documents->where('expiry_date', '<', now())->count() > 0)
                    <span class="badge badge-danger ml-2">{{ $trainee->documents->where('expiry_date', '<', now())->count() }} Expired</span>
                    @endif
                    @if($trainee->documents->where('expiry_date', '>', now())->where('expiry_date', '<', now()->addDays(30))->count() > 0)
                    <span class="badge badge-warning ml-2">{{ $trainee->documents->where('expiry_date', '>', now())->where('expiry_date', '<', now()->addDays(30))->count() }} Expiring Soon</span>
                    @endif
                </h6>
                
                @if($trainee->documents->count() > 0)
                    @foreach($trainee->documents as $document)
                    <div class="document-item {{ $document->expiry_date && $document->expiry_date < now() ? 'document-expired' : ($document->expiry_date && $document->expiry_date < now()->addDays(30) ? 'document-expiring' : '') }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <i class="fas fa-file mr-2"></i>{{ $document->document_name }}
                                    @if($document->is_verified)
                                    <i class="fas fa-check-circle text-success ml-2" title="Verified"></i>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    Type: {{ ucwords(str_replace('_', ' ', $document->document_type)) }} |
                                    Size: {{ $document->formatted_file_size }} |
                                    Uploaded: {{ $document->created_at->format('M d, Y') }}
                                    @if($document->expiry_date)
                                    | Expires: {{ $document->expiry_date->format('M d, Y') }}
                                    @endif
                                </small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ Storage::url($document->file_path) }}" download class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No documents uploaded.</p>
                @endif
            </div>

            <!-- Activity Log -->
            <div class="info-card">
                <h6><i class="fas fa-history mr-2"></i>Recent Activity</h6>
                @if($trainee->auditLogs->count() > 0)
                <div class="timeline">
                    @foreach($trainee->auditLogs->take(10) as $log)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ ucfirst($log->action) }}</h6>
                                <p class="mb-1 text-muted">{{ $log->notes ?? 'No additional notes' }}</p>
                                <small class="text-muted">
                                    by {{ $log->user->name ?? 'System' }} on {{ $log->created_at->format('M d, Y H:i') }}
                                </small>
                            </div>
                            @if($log->old_values || $log->new_values)
                            <button class="btn btn-sm btn-outline-info" onclick="showAuditDetails({{ $log->id }})">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted mb-0">No activity recorded.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="info-card">
                    <h6><i class="fas fa-bolt mr-2"></i>Quick Actions</h6>
                    
                    <a href="{{ route('trainees.edit', $trainee->id) }}" class="btn btn-primary action-btn">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                    
                    <button class="btn btn-info action-btn" onclick="recordAssessment()">
                        <i class="fas fa-chart-line mr-2"></i>Record Assessment
                    </button>
                    
                    <button class="btn btn-success action-btn" onclick="uploadDocument()">
                        <i class="fas fa-upload mr-2"></i>Upload Document
                    </button>
                    
                    <button class="btn btn-warning action-btn" onclick="recordAttendance()">
                        <i class="fas fa-calendar-check mr-2"></i>Record Attendance
                    </button>
                    
                    <button class="btn btn-outline-primary action-btn" onclick="generateReport()">
                        <i class="fas fa-file-pdf mr-2"></i>Generate Report
                    </button>
                    
                    @if(session('role') === 'admin')
                    <button class="btn btn-outline-danger action-btn" onclick="deleteTrainee()">
                        <i class="fas fa-trash mr-2"></i>Delete Trainee
                    </button>
                    @endif
                </div>

                <!-- Guardian Information -->
                @if($trainee->guardian_info || $trainee->guardian_name)
                <div class="guardian-card mb-3">
                    <h6><i class="fas fa-users mr-2"></i>Guardian Information</h6>
                    @php
                        $guardianInfo = $trainee->guardian_info ?? [];
                        $guardianName = $guardianInfo['guardian_name'] ?? $trainee->guardian_name ?? 'Not provided';
                        $guardianPhone = $guardianInfo['guardian_phone'] ?? $trainee->guardian_phone ?? 'Not provided';
                        $guardianEmail = $guardianInfo['guardian_email'] ?? $trainee->guardian_email ?? null;
                        $guardianRelationship = $guardianInfo['guardian_relationship'] ?? $trainee->guardian_relationship ?? 'Not specified';
                    @endphp
                    
                    <p class="mb-2"><strong>{{ $guardianName }}</strong></p>
                    <p class="mb-1 opacity-75">{{ $guardianRelationship }}</p>
                    <p class="mb-1">
                        <i class="fas fa-phone mr-2"></i>
                        <a href="tel:{{ $guardianPhone }}" class="text-white">{{ $guardianPhone }}</a>
                    </p>
                    @if($guardianEmail)
                    <p class="mb-0">
                        <i class="fas fa-envelope mr-2"></i>
                        <a href="mailto:{{ $guardianEmail }}" class="text-white">{{ $guardianEmail }}</a>
                    </p>
                    @endif
                </div>
                @endif

                <!-- Emergency Contact -->
                @if($trainee->emergency_contact || $trainee->emergency_contact_name)
                <div class="emergency-card mb-3">
                    <h6><i class="fas fa-phone-alt mr-2"></i>Emergency Contact</h6>
                    @php
                        $emergencyContact = $trainee->emergency_contact ?? [];
                        $emergencyName = $emergencyContact['name'] ?? $trainee->emergency_contact_name ?? 'Not provided';
                        $emergencyPhone = $emergencyContact['phone'] ?? $trainee->emergency_contact_phone ?? 'Not provided';
                        $emergencyRelationship = $emergencyContact['relationship'] ?? $trainee->emergency_contact_relationship ?? 'Not specified';
                    @endphp
                    
                    <p class="mb-2"><strong>{{ $emergencyName }}</strong></p>
                    <p class="mb-1 opacity-75">{{ $emergencyRelationship }}</p>
                    <p class="mb-0">
                        <i class="fas fa-phone mr-2"></i>
                        <a href="tel:{{ $emergencyPhone }}" class="text-white">{{ $emergencyPhone }}</a>
                    </p>
                </div>
                @endif

                <!-- Recent Progress -->
                @if($trainee->progress->count() > 0)
                <div class="info-card">
                    <h6><i class="fas fa-trophy mr-2"></i>Progress Overview</h6>
                    @foreach($trainee->progress->take(5) as $progress)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="font-weight-bold">{{ $progress->skill_area }}</small>
                            <small class="text-muted">{{ $progress->assessment_date->format('M d') }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" style="width: {{ $progress->current_score }}%"></div>
                        </div>
                        <small class="text-muted">{{ $progress->current_score }}% ({{ $progress->assessor->name ?? 'Unknown' }})</small>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- System Information -->
                <div class="info-card">
                    <h6><i class="fas fa-info-circle mr-2"></i>System Information</h6>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Unique ID:</strong></td>
                            <td>{{ $trainee->unique_identifier }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $trainee->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $trainee->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @if($trainee->lastUpdatedBy)
                        <tr>
                            <td><strong>Updated By:</strong></td>
                            <td>{{ $trainee->lastUpdatedBy->name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals and additional content here -->

@endsection

@section('scripts')
<script>
function recordAssessment() {
    // Implement assessment recording modal
    alert('Assessment recording functionality will be implemented here.');
}

function uploadDocument() {
    // Implement document upload modal
    alert('Document upload functionality will be implemented here.');
}

function recordAttendance() {
    // Implement attendance recording
    alert('Attendance recording functionality will be implemented here.');
}

function generateReport() {
    // Generate comprehensive trainee report
    window.open(`/trainees/{{ $trainee->id }}/report.pdf`, '_blank');
}

function deleteTrainee() {
    if (confirm('Are you sure you want to delete this trainee? This action cannot be undone.')) {
        $.ajax({
            url: '{{ route("trainees.destroy", $trainee->id) }}',
            type: 'DELETE',
            data: {
                '_token': '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('Trainee deleted successfully.');
                window.location.href = '{{ route("enhanced-trainees.index") }}';
            },
            error: function() {
                alert('Failed to delete trainee. Please try again.');
            }
        });
    }
}

function showAuditDetails(logId) {
    // Show detailed audit information in modal
    alert('Audit details for log ID: ' + logId);
}

// Auto-refresh progress statistics every 5 minutes
setInterval(function() {
    $.get('{{ route("enhanced-trainees.statistics") }}')
        .done(function(data) {
            // Update statistics if needed
        });
}, 300000);
</script>
@endsection