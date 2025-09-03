{{-- 
    Standardized Flash Messages Component
    Usage: @include('components.flash-messages')
    
    Supports: success, error, warning, info, validation errors
--}}

@if(session('success') || session('error') || session('warning') || session('info') || session('fail') || $errors->any())
<div class="flash-messages-container" style="margin-bottom: 2rem;">
    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-enhanced" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-text">
                <div class="alert-title">Success!</div>
                <div class="alert-message">{{ session('success') }}</div>
            </div>
            <button type="button" class="alert-dismiss" onclick="this.closest('.alert').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Error Message -->
    @if(session('error') || session('fail'))
    <div class="alert alert-danger alert-enhanced" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-text">
                <div class="alert-title">Error!</div>
                <div class="alert-message">{{ session('error') ?? session('fail') }}</div>
            </div>
            <button type="button" class="alert-dismiss" onclick="this.closest('.alert').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Warning Message -->
    @if(session('warning'))
    <div class="alert alert-warning alert-enhanced" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-text">
                <div class="alert-title">Warning!</div>
                <div class="alert-message">{{ session('warning') }}</div>
            </div>
            <button type="button" class="alert-dismiss" onclick="this.closest('.alert').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Info Message -->
    @if(session('info'))
    <div class="alert alert-info alert-enhanced" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="alert-text">
                <div class="alert-title">Information</div>
                <div class="alert-message">{{ session('info') }}</div>
            </div>
            <button type="button" class="alert-dismiss" onclick="this.closest('.alert').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="alert alert-danger alert-enhanced" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-text">
                <div class="alert-title">Validation Error{{ $errors->count() > 1 ? 's' : '' }}</div>
                <div class="alert-message">
                    @if($errors->count() === 1)
                        {{ $errors->first() }}
                    @else
                        <ul style="margin: 0; padding-left: 1.2rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <button type="button" class="alert-dismiss" onclick="this.closest('.alert').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif
</div>

<style>
/* Enhanced Alert Styling */
.flash-messages-container {
    position: relative;
    z-index: 100;
}

.alert-enhanced {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    padding: 0;
    overflow: hidden;
    animation: slideInUp 0.3s ease-out;
    position: relative;
}

.alert-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    z-index: 1;
}

.alert-success.alert-enhanced {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.02));
    border-left: 4px solid #28a745;
}

.alert-success.alert-enhanced::before {
    background: #28a745;
}

.alert-danger.alert-enhanced {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.02));
    border-left: 4px solid #dc3545;
}

.alert-danger.alert-enhanced::before {
    background: #dc3545;
}

.alert-warning.alert-enhanced {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), rgba(255, 193, 7, 0.02));
    border-left: 4px solid #ffc107;
}

.alert-warning.alert-enhanced::before {
    background: #ffc107;
}

.alert-info.alert-enhanced {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.05), rgba(23, 162, 184, 0.02));
    border-left: 4px solid #17a2b8;
}

.alert-info.alert-enhanced::before {
    background: #17a2b8;
}

.alert-content {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    gap: 12px;
}

.alert-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    margin-top: 2px;
}

.alert-success .alert-icon {
    color: #28a745;
}

.alert-danger .alert-icon {
    color: #dc3545;
}

.alert-warning .alert-icon {
    color: #ffc107;
}

.alert-info .alert-icon {
    color: #17a2b8;
}

.alert-text {
    flex: 1;
    line-height: 1.4;
}

.alert-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.alert-message {
    font-size: 14px;
    color: #495057;
    line-height: 1.5;
}

.alert-dismiss {
    flex-shrink: 0;
    background: none;
    border: none;
    color: rgba(0, 0, 0, 0.4);
    font-size: 14px;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.alert-dismiss:hover {
    background: rgba(0, 0, 0, 0.05);
    color: rgba(0, 0, 0, 0.6);
}

/* Animation */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .alert-content {
        padding: 14px 16px;
        gap: 10px;
    }
    
    .alert-icon {
        width: 20px;
        height: 20px;
        font-size: 14px;
    }
    
    .alert-title {
        font-size: 13px;
    }
    
    .alert-message {
        font-size: 13px;
    }
}
</style>
@endif