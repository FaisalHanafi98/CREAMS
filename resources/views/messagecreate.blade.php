@extends('layouts.app')

@section('title', 'Compose Message - CREAMS')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<style>
    .compose-container {
        background: var(--light-color);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .compose-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: var(--light-bg);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .compose-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--light-color);
        background: linear-gradient(135deg, var(--primary-color), #00d2ff);
    }
    
    .compose-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0;
    }
    
    .compose-content {
        padding: 20px;
    }
    
    .recipient-type-tabs {
        display: flex;
        margin-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .recipient-type-tab {
        padding: 10px 15px;
        font-size: 14px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        position: relative;
        transition: all var(--transition-speed) ease;
    }
    
    .recipient-type-tab:hover {
        color: var(--primary-color);
    }
    
    .recipient-type-tab.active {
        color: var(--primary-color);
    }
    
    .recipient-type-tab.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
    }
    
    .recipient-type-content {
        display: none;
    }
    
    .recipient-type-content.active {
        display: block;
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    
    .select2-container--default .select2-selection--single {
        height: 44px;
        padding: 8px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }
    
    .tips-container {
        background: var(--light-color);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-top: 20px;
    }
    
    .tips-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .tips-list {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }
    
    .tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 14px;
        color: var(--text-color);
    }
    
    .tips-list li i {
        color: var(--primary-color);
        margin-top: 3px;
    }
    
    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">Compose New Message</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="{{ route('messages.index') }}">Messages</a>
                    <span class="separator">/</span>
                    <span class="current">Compose</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="compose-container">
        <div class="compose-header">
            <div class="compose-icon">
                <i class="fas fa-pen-to-square"></i>
            </div>
            <h2 class="compose-title">Compose New Message</h2>
        </div>
        
        <div class="compose-content">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf
                
                <div class="recipient-type-tabs">
                    <div class="recipient-type-tab active" data-tab="admin">Administrators</div>
                    <div class="recipient-type-tab" data-tab="supervisor">Supervisors</div>
                    <div class="recipient-type-tab" data-tab="teacher">Teachers</div>
                    <div class="recipient-type-tab" data-tab="ajk">AJKs</div>
                </div>
                
                <div class="form-group">
                    <label for="recipient">Select Recipient</label>
                    <input type="hidden" name="recipient_type" id="recipient_type" value="admin">
                    
                    <div class="recipient-type-content active" id="admin-tab">
                        <select class="form-control recipient-select" id="admin-select" name="recipient_id">
                            <option value="">Select Administrator</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="recipient-type-content" id="supervisor-tab">
                        <select class="form-control recipient-select" id="supervisor-select">
                            <option value="">Select Supervisor</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }} ({{ $supervisor->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="recipient-type-content" id="teacher-tab">
                        <select class="form-control recipient-select" id="teacher-select">
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="recipient-type-content" id="ajk-tab">
                        <select class="form-control recipient-select" id="ajk-select">
                            <option value="">Select AJK</option>
                            @foreach($ajks as $ajk)
                                <option value="{{ $ajk->id }}">{{ $ajk->name }} ({{ $ajk->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Message</label>
                    <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="tips-container">
        <h3 class="tips-title">Messaging Tips</h3>
        <ul class="tips-list">
            <li>
                <i class="fas fa-info-circle"></i>
                <span>Be clear and concise in your message to ensure effective communication.</span>
            </li>
            <li>
                <i class="fas fa-info-circle"></i>
                <span>Use a descriptive subject line that briefly summarizes the purpose of your message.</span>
            </li>
            <li>
                <i class="fas fa-info-circle"></i>
                <span>Administrators handle system-wide issues, Supervisors manage centres, and Teachers work directly with trainees.</span>
            </li>
            <li>
                <i class="fas fa-info-circle"></i>
                <span>For urgent matters, consider following up with direct communication after sending your message.</span>
            </li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.recipient-select').select2({
            placeholder: "Select a recipient",
            width: '100%'
        });
        
        // Handle recipient type tab switching
        $('.recipient-type-tab').click(function() {
            // Update tabs
            $('.recipient-type-tab').removeClass('active');
            $(this).addClass('active');
            
            // Update content
            const tabId = $(this).data('tab');
            $('.recipient-type-content').removeClass('active');
            $(`#${tabId}-tab`).addClass('active');
            
            // Update hidden input for recipient type
            $('#recipient_type').val(tabId);
            
            // Update form to use the correct select
            $('.recipient-select').removeAttr('name');
            $(`#${tabId}-select`).attr('name', 'recipient_id');
        });
        
        // Form validation
        $('form').submit(function(e) {
            const recipientType = $('#recipient_type').val();
            const recipientId = $(`#${recipientType}-select`).val();
            
            if (!recipientId) {
                e.preventDefault();
                alert('Please select a recipient');
                return false;
            }
            
            const subject = $('#subject').val().trim();
            if (!subject) {
                e.preventDefault();
                alert('Please enter a subject');
                $('#subject').focus();
                return false;
            }
            
            const content = $('#content').val().trim();
            if (!content) {
                e.preventDefault();
                alert('Please enter a message');
                $('#content').focus();
                return false;
            }
            
            return true;
        });
    });
</script>
@endsection