@extends('layouts.app')

@section('title', 'Letter Details')

@section('content')
<div class="modern-letter-viewer">
    <!-- Header -->
    <div class="viewer-header">
        <div class="header-content">
            <div class="header-nav">
                <a href="{{ route('letters.modern.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            <div class="header-info">
                <h1>{{ $letter->letter_subject }}</h1>
                <div class="letter-meta">
                    <span class="meta-item">
                        <i class="fas fa-file-alt"></i>
                        {{ $letter->letter_reference }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-calendar"></i>
                        {{ $letter->created_at->format('F j, Y') }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-user"></i>
                        {{ $letter->creator->name ?? 'Unknown' }}
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <div class="btn-group">
                    <a href="{{ route('letters.modern.download', $letter->id) }}" class="btn btn-outline-light">
                        <i class="fas fa-download"></i>
                        Download PDF
                    </a>
                    <button class="btn btn-outline-light" onclick="printLetter()">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="duplicateLetter()">
                                <i class="fas fa-copy"></i> Duplicate Letter
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="editLetter()">
                                <i class="fas fa-edit"></i> Edit Letter
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="shareLetter()">
                                <i class="fas fa-share"></i> Share Letter
                            </a></li>
                            <li><a class="dropdown-item text-warning" href="#" onclick="archiveLetter()">
                                <i class="fas fa-archive"></i> Archive Letter
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Letter Content -->
    <div class="viewer-body">
        <div class="letter-container">
            <!-- Letter Details Panel -->
            <div class="letter-details-panel">
                <h3><i class="fas fa-info-circle"></i> Letter Details</h3>
                
                <div class="detail-section">
                    <h5>Recipient Information</h5>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Name:</label>
                            <span>{{ $letterData['recipient_name'] ?? 'Not specified' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Type:</label>
                            <span class="badge badge-info">{{ ucfirst($letterData['recipient_type'] ?? 'Unknown') }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span>{{ $letterData['recipient_email'] ?? 'Not provided' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Phone:</label>
                            <span>{{ $letterData['recipient_phone'] ?? 'Not provided' }}</span>
                        </div>
                    </div>
                    
                    <div class="detail-item full-width">
                        <label>Address:</label>
                        <span>{{ $letterData['recipient_address'] ?? 'Not specified' }}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h5>Letter Information</h5>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Template:</label>
                            <span>{{ $letter->template->template_name ?? 'Custom' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Priority:</label>
                            <span class="priority-badge priority-{{ $letterData['priority'] ?? 'normal' }}">
                                {{ ucfirst($letterData['priority'] ?? 'Normal') }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="status-badge status-{{ $letter->status }}">
                                {{ ucfirst($letter->status) }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Delivery:</label>
                            <span>{{ ucfirst($letterData['delivery_method'] ?? 'Print') }}</span>
                        </div>
                    </div>
                </div>

                @if(isset($letterData['notes']) && !empty($letterData['notes']))
                <div class="detail-section">
                    <h5>Internal Notes</h5>
                    <div class="notes-content">
                        {{ $letterData['notes'] }}
                    </div>
                </div>
                @endif

                <div class="detail-section">
                    <h5>Actions</h5>
                    <div class="action-buttons">
                        @if($letterData['delivery_method'] === 'email' || $letterData['delivery_method'] === 'both')
                        <button class="btn btn-outline-primary btn-sm" onclick="resendEmail()">
                            <i class="fas fa-envelope"></i>
                            Resend Email
                        </button>
                        @endif
                        
                        <button class="btn btn-outline-secondary btn-sm" onclick="createSimilar()">
                            <i class="fas fa-plus"></i>
                            Create Similar
                        </button>
                        
                        <button class="btn btn-outline-info btn-sm" onclick="generateReport()">
                            <i class="fas fa-chart-bar"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Letter Preview -->
            <div class="letter-preview-panel">
                <h3><i class="fas fa-file-alt"></i> Letter Preview</h3>
                
                <div class="letter-document" id="letterDocument">
                    <div class="letter-header">
                        <div class="letterhead">
                            <div class="centre-info">
                                <h2>{{ session('centre_name', 'CREAMS Centre') }}</h2>
                                <p>{{ session('centre_address', 'Centre Address') }}</p>
                            </div>
                            <div class="letter-ref">
                                <strong>Ref: {{ $letter->letter_reference }}</strong>
                            </div>
                        </div>
                        
                        <div class="letter-date">
                            {{ $letter->created_at->format('F j, Y') }}
                        </div>
                    </div>

                    <div class="letter-recipient">
                        <strong>{{ $letterData['recipient_name'] ?? 'Recipient Name' }}</strong><br>
                        {!! nl2br($letterData['recipient_address'] ?? 'Recipient Address') !!}
                    </div>

                    <div class="letter-subject">
                        <strong>Subject: {{ $letter->letter_subject }}</strong>
                    </div>

                    <div class="letter-body">
                        {!! nl2br($letter->letter_content) !!}
                    </div>

                    <div class="letter-signature">
                        <p>Sincerely,</p>
                        <br>
                        <p>
                            <strong>{{ $letter->creator->name ?? 'Staff Member' }}</strong><br>
                            {{ $letter->creator->position ?? 'Staff' }}<br>
                            {{ session('centre_name', 'CREAMS Centre') }}
                        </p>
                    </div>
                </div>

                <div class="preview-actions">
                    <button class="btn btn-primary" onclick="printLetter()">
                        <i class="fas fa-print"></i>
                        Print Letter
                    </button>
                    <a href="{{ route('letters.modern.download', $letter->id) }}" class="btn btn-success">
                        <i class="fas fa-download"></i>
                        Download PDF
                    </a>
                    <button class="btn btn-outline-secondary" onclick="fullscreenPreview()">
                        <i class="fas fa-expand"></i>
                        Fullscreen
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Letter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="shareEmail">Email Address</label>
                    <input type="email" id="shareEmail" class="form-control" placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label for="shareMessage">Message (Optional)</label>
                    <textarea id="shareMessage" class="form-control" rows="3" placeholder="Add a personal message..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendShare()">
                    <i class="fas fa-share"></i>
                    Share Letter
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.modern-letter-viewer {
    min-height: 100vh;
    background: #f8f9fa;
}

.viewer-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
}

.header-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-info h1 {
    font-size: 2rem;
    margin: 0 0 10px 0;
    font-weight: 600;
}

.letter-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    opacity: 0.9;
}

.header-actions .btn-group {
    display: flex;
    gap: 10px;
}

.viewer-body {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.letter-container {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
}

.letter-details-panel,
.letter-preview-panel {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.letter-details-panel h3,
.letter-preview-panel h3 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
}

.letter-details-panel h3 i,
.letter-preview-panel h3 i {
    margin-right: 10px;
    color: #667eea;
}

.detail-section {
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid #e2e8f0;
}

.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.detail-section h5 {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 3px;
}

.detail-item span {
    color: #2d3748;
    font-weight: 500;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-info {
    background: #667eea;
    color: white;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.priority-normal {
    background: #e2e8f0;
    color: #4a5568;
}

.priority-high {
    background: #fed7d7;
    color: #c53030;
}

.priority-urgent {
    background: #feb2b2;
    color: #9b2c2c;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-generated {
    background: #c6f6d5;
    color: #22543d;
}

.status-sent {
    background: #bee3f8;
    color: #2a69ac;
}

.status-archived {
    background: #e2e8f0;
    color: #4a5568;
}

.notes-content {
    background: #f7fafc;
    border-radius: 8px;
    padding: 15px;
    color: #4a5568;
    line-height: 1.6;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.letter-document {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 40px;
    font-family: 'Georgia', serif;
    line-height: 1.6;
    color: #2d3748;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.letter-header {
    margin-bottom: 40px;
}

.letterhead {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #667eea;
}

.centre-info h2 {
    color: #667eea;
    margin: 0 0 5px 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.centre-info p {
    color: #718096;
    margin: 0;
    font-size: 0.9rem;
}

.letter-ref {
    color: #4a5568;
    font-size: 0.9rem;
}

.letter-date {
    text-align: right;
    color: #4a5568;
    font-weight: 500;
}

.letter-recipient {
    margin-bottom: 30px;
    color: #2d3748;
    line-height: 1.4;
}

.letter-subject {
    margin-bottom: 30px;
    color: #2d3748;
}

.letter-body {
    margin-bottom: 40px;
    text-align: justify;
    color: #2d3748;
}

.letter-signature {
    margin-top: 40px;
    color: #2d3748;
}

.preview-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 1200px) {
    .letter-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .letter-details-panel {
        order: 2;
    }
    
    .letter-preview-panel {
        order: 1;
    }
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .letter-meta {
        justify-content: center;
    }
    
    .header-actions .btn-group {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .letter-document {
        padding: 20px;
    }
    
    .letterhead {
        flex-direction: column;
        text-align: center;
    }
    
    .preview-actions {
        flex-direction: column;
    }
}

@media print {
    .viewer-header,
    .letter-details-panel,
    .preview-actions {
        display: none !important;
    }
    
    .letter-container {
        grid-template-columns: 1fr;
    }
    
    .letter-document {
        box-shadow: none;
        border: none;
        padding: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
function printLetter() {
    window.print();
}

function duplicateLetter() {
    if (confirm('Do you want to create a new letter based on this one?')) {
        window.location.href = '{{ route("letters.modern.create") }}?duplicate={{ $letter->id }}';
    }
}

function editLetter() {
    alert('Edit functionality would be implemented here');
}

function shareLetter() {
    $('#shareModal').modal('show');
}

function sendShare() {
    const email = document.getElementById('shareEmail').value;
    const message = document.getElementById('shareMessage').value;
    
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    // This would typically send a request to share the letter
    alert('Share functionality would be implemented here');
    $('#shareModal').modal('hide');
}

function archiveLetter() {
    if (confirm('Are you sure you want to archive this letter?')) {
        fetch(`{{ route('letters.modern.archive', $letter->id) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Letter archived successfully');
                window.location.href = '{{ route("letters.modern.index") }}';
            } else {
                alert('Failed to archive letter: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while archiving the letter.');
        });
    }
}

function resendEmail() {
    if (confirm('Do you want to resend this letter via email?')) {
        alert('Email resend functionality would be implemented here');
    }
}

function createSimilar() {
    window.location.href = '{{ route("letters.modern.create") }}?template_id={{ $letter->template_id }}&similar={{ $letter->id }}';
}

function generateReport() {
    alert('Report generation functionality would be implemented here');
}

function fullscreenPreview() {
    const letterDocument = document.getElementById('letterDocument');
    if (letterDocument.requestFullscreen) {
        letterDocument.requestFullscreen();
    } else if (letterDocument.webkitRequestFullscreen) {
        letterDocument.webkitRequestFullscreen();
    } else if (letterDocument.mozRequestFullScreen) {
        letterDocument.mozRequestFullScreen();
    }
}
</script>
@endpush