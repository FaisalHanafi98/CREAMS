@extends('layouts.app')

@section('title', 'Edit Centre - ' . $centre->centre_name . ' - CREAMS')

@section('content')
<div class="centre-edit-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Centre</h1>
            <p class="subtitle">{{ $centre->centre_name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('centres.show', $centre->centre_id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Centre
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
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
        <form action="{{ route('centres.update', $centre->centre_id) }}" method="POST" class="centre-form">
            @csrf
            @method('PUT')

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
                                               value="{{ old('centre_id', $centre->centre_id) }}" 
                                               maxlength="10" required readonly>
                                        @error('centre_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Centre ID cannot be changed</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="centre_name">Centre Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('centre_name') is-invalid @enderror" 
                                               id="centre_name" name="centre_name" 
                                               value="{{ old('centre_name', $centre->centre_name) }}" 
                                               placeholder="Enter centre name" required>
                                        @error('centre_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3"
                                          placeholder="Brief description of the centre and its specializations">{{ old('description', $centre->description) }}</textarea>
                                @error('description')
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
                                <label for="address">Street Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" 
                                       value="{{ old('address', $centre->address) }}" 
                                       placeholder="Enter street address" required>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="city">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                               id="city" name="city" 
                                               value="{{ old('city', $centre->city) }}" 
                                               placeholder="Enter city" required>
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="state">State <span class="text-danger">*</span></label>
                                        <select class="form-control @error('state') is-invalid @enderror" 
                                                id="state" name="state" required>
                                            <option value="">Select State</option>
                                            <option value="Johor" {{ old('state', $centre->state) == 'Johor' ? 'selected' : '' }}>Johor</option>
                                            <option value="Kedah" {{ old('state', $centre->state) == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                                            <option value="Kelantan" {{ old('state', $centre->state) == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                                            <option value="Kuala Lumpur" {{ old('state', $centre->state) == 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
                                            <option value="Labuan" {{ old('state', $centre->state) == 'Labuan' ? 'selected' : '' }}>Labuan</option>
                                            <option value="Melaka" {{ old('state', $centre->state) == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                                            <option value="Negeri Sembilan" {{ old('state', $centre->state) == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                                            <option value="Pahang" {{ old('state', $centre->state) == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                                            <option value="Perak" {{ old('state', $centre->state) == 'Perak' ? 'selected' : '' }}>Perak</option>
                                            <option value="Perlis" {{ old('state', $centre->state) == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                                            <option value="Penang" {{ old('state', $centre->state) == 'Penang' ? 'selected' : '' }}>Penang</option>
                                            <option value="Putrajaya" {{ old('state', $centre->state) == 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
                                            <option value="Sabah" {{ old('state', $centre->state) == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                                            <option value="Sarawak" {{ old('state', $centre->state) == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                                            <option value="Selangor" {{ old('state', $centre->state) == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                                            <option value="Terengganu" {{ old('state', $centre->state) == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                                        </select>
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="postcode">Postcode <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('postcode') is-invalid @enderror" 
                                               id="postcode" name="postcode" 
                                               value="{{ old('postcode', $centre->postcode) }}" 
                                               placeholder="12345" 
                                               maxlength="10" required>
                                        @error('postcode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
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
                                        <label for="phone">Phone Number</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" 
                                               value="{{ old('phone', $centre->phone) }}" 
                                               placeholder="+60123456789">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" 
                                               value="{{ old('email', $centre->email) }}" 
                                               placeholder="centre@example.com">
                                        @error('email')
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
                                <label for="capacity">Capacity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" name="capacity" 
                                       value="{{ old('capacity', $centre->capacity) }}" 
                                       min="1" max="1000" required>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum number of trainees</small>
                            </div>

                            <div class="form-group">
                                <label for="opening_time">Opening Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('opening_time') is-invalid @enderror" 
                                       id="opening_time" name="opening_time" 
                                       value="{{ old('opening_time', $centre->opening_time) }}" required>
                                @error('opening_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="closing_time">Closing Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('closing_time') is-invalid @enderror" 
                                       id="closing_time" name="closing_time" 
                                       value="{{ old('closing_time', $centre->closing_time) }}" required>
                                @error('closing_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $centre->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Centre is Active
                                    </label>
                                </div>
                                <small class="form-text text-muted">Inactive centres won't accept new registrations</small>
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
                                <i class="fas fa-save mr-2"></i> Update Centre
                            </button>
                            <a href="{{ route('centres.show', $centre->centre_id) }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                            <hr>
                            <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Centre
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Centre</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i> 
                    Warning: This action cannot be undone.
                </p>
                <p>Are you sure you want to delete <strong>{{ $centre->centre_name }}</strong>?</p>
                <p>This will also affect all associated staff, trainees, and activities.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('centres.destroy', $centre->centre_id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Centre</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.centre-edit-container {
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

.form-control[readonly] {
    background-color: #f8f9fa;
    color: #6c757d;
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

.form-check-input {
    margin-top: 0.3rem;
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
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
            }
        });
    }
});
</script>
@endsection