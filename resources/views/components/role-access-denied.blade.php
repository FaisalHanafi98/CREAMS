@if(session('access_denied') && session('warning'))
<div class="alert alert-warning alert-dismissible fade show role-access-alert" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.2em; color: #856404;"></i>
        <div class="flex-grow-1">
            <h6 class="mb-1 fw-bold">Access Restricted</h6>
            <p class="mb-2">{{ session('warning') }}</p>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                If you believe you should have access to this page, please contact your system administrator.
            </small>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<style>
.role-access-alert {
    border-left: 4px solid #ffc107;
    background: linear-gradient(135deg, #fff3cd 0%, #fefbf0 100%);
    border-color: #ffc107;
    margin-bottom: 1.5rem;
}

.role-access-alert .fas {
    color: #856404;
}

.role-access-alert h6 {
    color: #664d03;
}

.role-access-alert .text-muted {
    color: #6c757d !important;
}
</style>
@endif

@if(session('error') && !session('access_denied'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-3" style="font-size: 1.2em;"></i>
        <div class="flex-grow-1">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-check-circle me-3" style="font-size: 1.2em;"></i>
        <div class="flex-grow-1">
            {{ session('success') }}
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif