@extends('layouts.app')

@section('title', 'Add New Asset')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Assets</a></li>
            <li class="breadcrumb-item active">Add New Asset</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle text-primary me-2"></i>Add New Asset
        </h1>
        <a href="{{ route('assets.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Assets
        </a>
    </div>

    <!-- Flash Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Create Asset Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Asset Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Asset Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="asset_id" class="form-label fw-bold">Asset ID</label>
                                <input type="text" class="form-control @error('asset_id') is-invalid @enderror" 
                                       id="asset_id" name="asset_id" value="{{ old('asset_id') }}" 
                                       placeholder="Auto-generated if left blank">
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="type" class="form-label fw-bold">Asset Type *</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Asset Type</option>
                                    <option value="furniture" {{ old('type') == 'furniture' ? 'selected' : '' }}>Furniture</option>
                                    <option value="equipment" {{ old('type') == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="electronics" {{ old('type') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                                    <option value="vehicle" {{ old('type') == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                                    <option value="medical" {{ old('type') == 'medical' ? 'selected' : '' }}>Medical Equipment</option>
                                    <option value="educational" {{ old('type') == 'educational' ? 'selected' : '' }}>Educational Material</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="brand" class="form-label fw-bold">Brand</label>
                                <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                       id="brand" name="brand" value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location and Centre -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="centre_id" class="form-label fw-bold">Centre *</label>
                                <select class="form-select @error('centre_id') is-invalid @enderror" id="centre_id" name="centre_id" required>
                                    <option value="">Select Centre</option>
                                    @foreach($centres as $centre)
                                        <option value="{{ $centre->centre_id }}" {{ old('centre_id') == $centre->centre_id ? 'selected' : '' }}>
                                            {{ $centre->centre_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('centre_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-bold">Specific Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location') }}" 
                                       placeholder="e.g., Room 101, Storage A">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="purchase_price" class="form-label fw-bold">Purchase Price (RM)</label>
                                <input type="number" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror" 
                                       id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}">
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="purchase_date" class="form-label fw-bold">Purchase Date</label>
                                <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="condition" class="form-label fw-bold">Condition *</label>
                                <select class="form-select @error('condition') is-invalid @enderror" id="condition" name="condition" required>
                                    <option value="">Select Condition</option>
                                    <option value="excellent" {{ old('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                                    <option value="damaged" {{ old('condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                                @error('condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Detailed description of the asset...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Asset Image -->
                        <div class="mb-4">
                            <label for="image" class="form-label fw-bold">Asset Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 2MB</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('assets.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Asset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Info Panel -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Quick Info</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Asset Management Tips:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Use descriptive names for easy identification</li>
                            <li>Asset IDs are auto-generated if not provided</li>
                            <li>Keep purchase receipts for financial records</li>
                            <li>Regular condition updates help with maintenance</li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <h6 class="font-weight-bold">Required Fields:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Asset Name</li>
                            <li><i class="fas fa-check text-success me-2"></i>Asset Type</li>
                            <li><i class="fas fa-check text-success me-2"></i>Centre</li>
                            <li><i class="fas fa-check text-success me-2"></i>Condition</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate asset ID based on name and type
    const nameInput = document.getElementById('name');
    const typeSelect = document.getElementById('type');
    const assetIdInput = document.getElementById('asset_id');
    
    function generateAssetId() {
        const name = nameInput.value;
        const type = typeSelect.value;
        
        if (name && type && !assetIdInput.value) {
            const nameCode = name.substring(0, 3).toUpperCase();
            const typeCode = type.substring(0, 3).toUpperCase();
            const timestamp = Date.now().toString().slice(-4);
            
            assetIdInput.value = `${typeCode}-${nameCode}-${timestamp}`;
        }
    }
    
    nameInput.addEventListener('blur', generateAssetId);
    typeSelect.addEventListener('change', generateAssetId);
    
    // Image preview
    const imageInput = document.getElementById('image');
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                // Create or update preview image
                let preview = document.getElementById('imagePreview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'imagePreview';
                    preview.className = 'mt-2 img-thumbnail';
                    preview.style.maxWidth = '200px';
                    imageInput.parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush