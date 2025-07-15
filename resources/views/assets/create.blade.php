@extends('layouts.app')

@section('title', 'Add New Asset - CREAMS')

@section('styles')
<style>
    .container-fluid {
        background: #f0f2f5;
        min-height: 100vh;
        padding: 20px;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        background: white;
    }
    
    .card-header {
        background: linear-gradient(135deg, #007bff, #6c757d);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px;
        border: none;
    }
    
    .card-header h4 {
        margin: 0;
        font-weight: 600;
        color: white !important;
    }
    
    .card-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        outline: none;
    }
    
    .btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    
    .btn-secondary {
        background: #6c757d;
        border: none;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .required {
        color: #dc3545;
    }
    
    .form-text {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
    }
    
    .row {
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-plus-circle mr-2"></i>Add New Asset</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('assets.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asset_name">Asset Name <span class="required">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('asset_name') is-invalid @enderror" 
                                           id="asset_name" 
                                           name="asset_name" 
                                           value="{{ old('asset_name') }}" 
                                           required>
                                    @error('asset_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asset_type">Asset Type <span class="required">*</span></label>
                                    <select class="form-control @error('asset_type') is-invalid @enderror" 
                                            id="asset_type" 
                                            name="asset_type" 
                                            required>
                                        <option value="">Select Asset Type</option>
                                        @foreach($assetTypes as $type)
                                            <option value="{{ $type }}" {{ old('asset_type') == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('asset_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="centre_name">Centre <span class="required">*</span></label>
                                    <select class="form-control @error('centre_name') is-invalid @enderror" 
                                            id="centre_name" 
                                            name="centre_name" 
                                            required>
                                        <option value="">Select Centre</option>
                                        @foreach($centres as $centre)
                                            <option value="{{ $centre->centre_name }}" 
                                                    {{ old('centre_name', request('centre')) == $centre->centre_name ? 'selected' : '' }}>
                                                {{ $centre->centre_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('centre_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asset_quantity">Quantity <span class="required">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('asset_quantity') is-invalid @enderror" 
                                           id="asset_quantity" 
                                           name="asset_quantity" 
                                           value="{{ old('asset_quantity', 1) }}" 
                                           min="1" 
                                           required>
                                    @error('asset_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asset_price">Price (RM)</label>
                                    <input type="number" 
                                           class="form-control @error('asset_price') is-invalid @enderror" 
                                           id="asset_price" 
                                           name="asset_price" 
                                           value="{{ old('asset_price') }}" 
                                           step="0.01" 
                                           min="0">
                                    @error('asset_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text">Enter the unit price of the asset</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asset_condition">Condition</label>
                                    <select class="form-control @error('asset_condition') is-invalid @enderror" 
                                            id="asset_condition" 
                                            name="asset_condition">
                                        <option value="">Select Condition</option>
                                        <option value="New" {{ old('asset_condition') == 'New' ? 'selected' : '' }}>New</option>
                                        <option value="Good" {{ old('asset_condition') == 'Good' ? 'selected' : '' }}>Good</option>
                                        <option value="Fair" {{ old('asset_condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
                                        <option value="Poor" {{ old('asset_condition') == 'Poor' ? 'selected' : '' }}>Poor</option>
                                    </select>
                                    @error('asset_condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="asset_description">Description</label>
                            <textarea class="form-control @error('asset_description') is-invalid @enderror" 
                                      id="asset_description" 
                                      name="asset_description" 
                                      rows="4">{{ old('asset_description') }}</textarea>
                            @error('asset_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Provide additional details about the asset</small>
                        </div>

                        <div class="form-group">
                            <label for="asset_location">Location</label>
                            <input type="text" 
                                   class="form-control @error('asset_location') is-invalid @enderror" 
                                   id="asset_location" 
                                   name="asset_location" 
                                   value="{{ old('asset_location') }}" 
                                   placeholder="e.g., Room 101, Storage A">
                            @error('asset_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <a href="{{ route('assets.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Add Asset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate total value when quantity or price changes
    const quantityInput = document.getElementById('asset_quantity');
    const priceInput = document.getElementById('asset_price');
    
    function updateTotalValue() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = quantity * price;
        
        // You can add a total value display here if needed
        console.log('Total value:', total);
    }
    
    quantityInput.addEventListener('input', updateTotalValue);
    priceInput.addEventListener('input', updateTotalValue);
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
            }
        });
    }, 5000);
});
</script>
@endsection