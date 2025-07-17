@extends('layouts.app')

@section('title', 'Create New Centre - CREAMS')

@section('content')
<div class="centre-create-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Create New Centre</h1>
            <p class="subtitle">Add a new rehabilitation centre to the system</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('centres.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Centres
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> Please check the form for errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="form-container">
        <form action="{{ route('centres.store') }}" method="POST" class="centre-form">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    {{-- Basic Information --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Basic Information</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_id">Centre ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('centre_id') is-invalid @enderror" 
                                               id="centre_id" name="centre_id" 
                                               value="{{ old('centre_id') }}" 
                                               placeholder="e.g., CTR001" 
                                               maxlength="10" required>
                                        @error('centre_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Unique identifier for the centre (max 10 characters)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_name">Centre Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('centre_name') is-invalid @enderror" 
                                               id="centre_name" name="centre_name" 
                                               value="{{ old('centre_name') }}" 
                                               placeholder="Enter centre name" required>
                                        @error('centre_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="centre_description">Description</label>
                                <textarea class="form-control @error('centre_description') is-invalid @enderror" 
                                          id="centre_description" name="centre_description" rows="3"
                                          placeholder="Brief description of the centre and its specializations">{{ old('centre_description') }}</textarea>
                                @error('centre_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Address Information --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Address Information</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label for="centre_address">Centre Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('centre_address') is-invalid @enderror" 
                                          id="centre_address" name="centre_address" rows="3"
                                          placeholder="Enter complete address including street, city, state, and postcode" required>{{ old('centre_address') }}</textarea>
                                @error('centre_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Contact Information</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_phone">Phone Number</label>
                                        <input type="text" class="form-control @error('centre_phone') is-invalid @enderror" 
                                               id="centre_phone" name="centre_phone" 
                                               value="{{ old('centre_phone') }}" 
                                               placeholder="+60123456789">
                                        @error('centre_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_email">Email Address</label>
                                        <input type="email" class="form-control @error('centre_email') is-invalid @enderror" 
                                               id="centre_email" name="centre_email" 
                                               value="{{ old('centre_email') }}" 
                                               placeholder="centre@example.com">
                                        @error('centre_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_manager">Centre Manager</label>
                                        <input type="text" class="form-control @error('centre_manager') is-invalid @enderror" 
                                               id="centre_manager" name="centre_manager" 
                                               value="{{ old('centre_manager') }}" 
                                               placeholder="Manager Name">
                                        @error('centre_manager')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_manager_contact">Manager Contact</label>
                                        <input type="text" class="form-control @error('centre_manager_contact') is-invalid @enderror" 
                                               id="centre_manager_contact" name="centre_manager_contact" 
                                               value="{{ old('centre_manager_contact') }}" 
                                               placeholder="+60123456789">
                                        @error('centre_manager_contact')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Operational Details --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Operational Details</h3>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label for="centre_capacity">Capacity <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('centre_capacity') is-invalid @enderror" 
                                       id="centre_capacity" name="centre_capacity" 
                                       value="{{ old('centre_capacity') }}" 
                                       placeholder="e.g., 100 trainees" required>
                                @error('centre_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum number of trainees</small>
                            </div>

                            <div class="form-group">
                                <label for="centre_status">Centre Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('centre_status') is-invalid @enderror" 
                                        id="centre_status" name="centre_status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('centre_status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('centre_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="maintenance" {{ old('centre_status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('centre_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="centre_facilities">Facilities</label>
                                <textarea class="form-control @error('centre_facilities') is-invalid @enderror" 
                                          id="centre_facilities" name="centre_facilities" rows="3"
                                          placeholder="List available facilities">{{ old('centre_facilities') }}</textarea>
                                @error('centre_facilities')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Actions</h3>
                        </div>
                        <div class="form-card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i> Create Centre
                            </button>
                            <a href="{{ route('centres.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
.centre-create-container {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.subtitle {
    color: #6c757d;
    margin: 0;
    font-size: 1.1rem;
}

.form-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
}

.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    overflow: hidden;
}

.form-card-header {
    background: linear-gradient(135deg, var(--primary-color, #007bff), var(--secondary-color, #6c757d));
    color: white;
    padding: 15px 20px;
    border-bottom: none;
}

.form-card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.form-card-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.btn {
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-block {
    margin-bottom: 10px;
}

.text-danger {
    color: #dc3545 !important;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
}

.alert {
    border-radius: 8px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        margin-top: 15px;
    }
    
    .form-container {
        padding: 15px;
    }
    
    .form-card-body {
        padding: 20px;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);

    // Form validation enhancement
    const form = document.querySelector('.centre-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
            }
        });
    }
    
    // Auto-generate centre ID based on centre name
    const centreNameInput = document.getElementById('centre_name');
    const centreIdInput = document.getElementById('centre_id');
    
    if (centreNameInput && centreIdInput) {
        centreNameInput.addEventListener('input', function() {
            if (!centreIdInput.value) {
                const name = this.value.toUpperCase();
                const words = name.split(' ');
                let id = '';
                
                if (words.length >= 2) {
                    id = words.slice(0, 2).map(word => word.substring(0, 3)).join('');
                } else {
                    id = name.substring(0, 6);
                }
                
                // Add random number
                id += Math.floor(Math.random() * 100).toString().padStart(2, '0');
                
                if (id.length <= 10) {
                    centreIdInput.value = id;
                }
            }
        });
    }
});
</script>
@endsection