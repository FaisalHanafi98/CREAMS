@extends('layouts.app')

@section('title', 'Edit Asset - CREAMS')

@section('styles')
    <style>
        :root {
            --primary-color: #c850c0;
            --secondary-color: #32bdea;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .asset-info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .asset-info-header {
            background: linear-gradient(135deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
            padding: 20px 30px;
            border-bottom: 1px solid #f1f3f4;
        }

        .asset-info-header h4 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .asset-profile {
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .asset-avatar {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            box-shadow: 0 5px 15px rgba(200, 80, 192, 0.3);
        }

        .asset-details h3 {
            margin: 0 0 10px 0;
            color: var(--dark-color);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .asset-details p {
            margin: 5px 0;
            color: #6c757d;
            font-weight: 500;
        }

        .asset-details .badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .form-header {
            background: linear-gradient(135deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
            padding: 20px 30px;
            border-bottom: 1px solid #f1f3f4;
        }

        .form-header h4 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .required {
            color: var(--danger-color);
            font-size: 12px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
            background: #fafbfc;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(200, 80, 192, 0.1);
            outline: none;
            background: white;
        }

        .form-control:hover {
            border-color: #bdc3c7;
            background: white;
        }

        .form-control[readonly] {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .form-text {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .edit-toggle-container {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 30px;
            background: var(--light-bg);
            border-top: 1px solid #f1f3f4;
        }

        .edit-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 25px;
            background: #ccc;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-switch.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .toggle-switch::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 21px;
            height: 21px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .toggle-switch.active::before {
            transform: translateX(25px);
        }

        .image-upload-container {
            border: 2px dashed #e9ecef;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            background: #fafbfc;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .image-upload-container:hover {
            border-color: var(--primary-color);
            background: rgba(200, 80, 192, 0.05);
        }

        .image-upload-container.has-image {
            border-color: var(--success-color);
            background: rgba(40, 167, 69, 0.05);
        }

        .upload-icon {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 15px;
        }

        .upload-text {
            color: #6c757d;
            margin-bottom: 10px;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            display: none;
        }

        .form-actions {
            background: var(--light-bg);
            padding: 25px 30px;
            border-top: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(200, 80, 192, 0.4);
            color: white;
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
            color: white;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .input-group-text {
            background: var(--light-bg);
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: var(--dark-color);
            font-weight: 600;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group .form-control:focus {
            border-left: 2px solid var(--primary-color);
        }

        @media (max-width: 768px) {
            .page-header {
                text-align: center;
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .asset-profile {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .form-body {
                padding: 20px;
            }

            .form-actions {
                padding: 20px;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fas fa-edit me-3"></i>Edit Asset
                    </h1>
                    <p>Update asset information and details</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('assets.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Asset
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Current Asset Information -->
        <div class="asset-info-card">
            <div class="asset-info-header">
                <h4><i class="fas fa-info-circle"></i>Current Asset Information</h4>
            </div>
            <div class="asset-profile">
                <img src="{{ $asset->asset_avatar ? asset($asset->asset_avatar) : asset('images/default-asset.png') }}"
                    alt="{{ $asset->asset_name }}" class="asset-avatar"
                    onerror="this.src='{{ asset('images/default-asset.png') }}'">
                <div class="asset-details">
                    <h3>{{ $asset->asset_name }}</h3>
                    <p><strong>Asset ID:</strong> {{ $asset->asset_id }}</p>
                    <p><strong>Brand:</strong> {{ $asset->asset_brand ?? 'N/A' }}</p>
                    <p><strong>Current Price:</strong> RM {{ number_format($asset->asset_price, 2) }}</p>
                    <p><strong>Quantity:</strong> {{ $asset->asset_quantity }} units</p>
                    <p><strong>Centre:</strong> {{ $asset->centre_name ?? ($asset->center_name ?? 'Unassigned') }}</p>
                    <span class="badge">{{ $asset->asset_parent ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Asset Edit Form -->
        <form action="{{ route('assets.update', $asset->asset_id) }}" method="POST"
            enctype="multipart/form-data" id="assetForm">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="form-card">
                <div class="form-header">
                    <h4><i class="fas fa-info-circle"></i>Basic Information</h4>
                </div>
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asset_id">
                                    Asset ID <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="asset_id" name="asset_id"
                                    value="{{ old('asset_id', $asset->asset_id) }}" readonly>
                                <div class="form-text">
                                    <i class="fas fa-lock"></i>
                                    Asset ID cannot be changed after creation
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asset_name">
                                    Asset Name <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control editable-field" id="asset_name" name="asset_name"
                                    placeholder="e.g., Dell Laptop Inspiron 15"
                                    value="{{ old('asset_name', $asset->asset_name) }}" readonly required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Full name or model of the asset
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asset_parent">
                                    Asset Type <span class="required">*</span>
                                </label>
                                <select class="form-control editable-field" id="asset_parent" name="asset_parent" disabled
                                    required>
                                    <option value="">Select Asset Type</option>
                                    <option value="Computer"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Computer' ? 'selected' : '' }}>
                                        Computer</option>
                                    <option value="Furniture"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Furniture' ? 'selected' : '' }}>
                                        Furniture</option>
                                    <option value="Equipment"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Equipment' ? 'selected' : '' }}>
                                        Equipment</option>
                                    <option value="Vehicle"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Vehicle' ? 'selected' : '' }}>
                                        Vehicle</option>
                                    <option value="Books"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Books' ? 'selected' : '' }}>Books
                                    </option>
                                    <option value="Medical"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Medical' ? 'selected' : '' }}>
                                        Medical Equipment</option>
                                    <option value="Sports"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Sports' ? 'selected' : '' }}>
                                        Sports Equipment</option>
                                    <option value="Other"
                                        {{ old('asset_parent', $asset->asset_parent) == 'Other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Category classification for the asset
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asset_brand">
                                    Brand/Manufacturer
                                </label>
                                <input type="text" class="form-control editable-field" id="asset_brand"
                                    name="asset_brand" placeholder="e.g., Dell, HP, Samsung"
                                    value="{{ old('asset_brand', $asset->asset_brand) }}" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Brand or manufacturer of the asset
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quantity & Financial Information -->
            <div class="form-card">
                <div class="form-header">
                    <h4><i class="fas fa-calculator"></i>Quantity & Financial Details</h4>
                </div>
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="asset_quantity">
                                    Quantity <span class="required">*</span>
                                </label>
                                <input type="number" class="form-control editable-field" id="asset_quantity"
                                    name="asset_quantity" placeholder="1" min="0"
                                    value="{{ old('asset_quantity', $asset->asset_quantity) }}" readonly required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Number of units available
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="asset_price">
                                    Unit Price (RM) <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control editable-field" id="asset_price"
                                        name="asset_price" placeholder="0.00" step="0.01" min="0"
                                        value="{{ old('asset_price', $asset->asset_price) }}" readonly required>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Cost per unit in Malaysian Ringgit
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_value">
                                    Total Value (RM)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="text" class="form-control" id="total_value" readonly
                                        value="{{ number_format(($asset->asset_quantity ?? 0) * ($asset->asset_price ?? 0), 2) }}">
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Automatically calculated (Quantity × Price)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Assignment -->
            <div class="form-card">
                <div class="form-header">
                    <h4><i class="fas fa-map-marker-alt"></i>Location & Assignment</h4>
                </div>
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="centre_name">
                                    Centre Assignment <span class="required">*</span>
                                </label>
                                <select class="form-control editable-field" id="centre_name" name="centre_name" disabled
                                    required>
                                    <option value="">Select Centre</option>
                                    @if (isset($centres))
                                        @foreach ($centres as $centre)
                                            <option value="{{ $centre->centre_name }}"
                                                {{ old('centre_name', $asset->centre_name ?? $asset->center_name) == $centre->centre_name ? 'selected' : '' }}>
                                                {{ $centre->centre_name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="Gombak"
                                            {{ old('centre_name', $asset->centre_name ?? $asset->center_name) == 'Gombak' ? 'selected' : '' }}>
                                            Gombak</option>
                                        <option value="Kuantan"
                                            {{ old('centre_name', $asset->centre_name ?? $asset->center_name) == 'Kuantan' ? 'selected' : '' }}>
                                            Kuantan</option>
                                    @endif
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Centre where this asset is located
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-card">
                <div class="form-header">
                    <h4><i class="fas fa-clipboard-list"></i>Additional Information</h4>
                </div>
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="asset_note">
                                    Notes/Description
                                </label>
                                <textarea class="form-control editable-field" id="asset_note" name="asset_note" rows="4"
                                    placeholder="Additional notes, specifications, or important details about this asset..." readonly>{{ old('asset_note', $asset->asset_note) }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Optional description, specifications, or special notes
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asset Image Upload -->
                    <div class="form-group">
                        <label for="asset_avatar">
                            Asset Image
                        </label>
                        <div class="image-upload-container" onclick="document.getElementById('asset_avatar').click()"
                            style="pointer-events: none;">
                            <div class="upload-content">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">
                                    <strong>Click to upload new asset image</strong>
                                </div>
                                <div class="form-text">
                                    Supported formats: JPG, PNG, GIF (Max: 2MB)
                                </div>
                            </div>
                            <img id="image-preview" class="image-preview" alt="Asset Preview"
                                @if ($asset->asset_avatar) src="{{ asset($asset->asset_avatar) }}"
                                 style="display: block;" @endif>
                        </div>
                        <input type="file" id="asset_avatar" name="asset_avatar" accept="image/*"
                            style="display: none;" disabled>
                    </div>
                </div>

                <!-- Edit Mode Toggle -->
                <div class="edit-toggle-container">
                    <div class="edit-toggle">
                        <span>Enable Editing:</span>
                        <div class="toggle-switch" id="editToggle"></div>
                    </div>
                    <small class="text-muted">Toggle to enable or disable editing mode</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-card">
                <div class="form-actions">
                    <div>
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>Cancel
                        </a>
                    </div>
                    <div>
                        <button type="button" id="resetBtn" class="btn btn-secondary" disabled>
                            <i class="fas fa-undo"></i>Reset Changes
                        </button>
                        <button type="submit" id="saveBtn" class="btn btn-primary" disabled>
                            <i class="fas fa-save"></i>Update Asset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editToggle = document.getElementById('editToggle');
            const editableFields = document.querySelectorAll('.editable-field');
            const imageUpload = document.getElementById('asset_avatar');
            const imageUploadContainer = document.querySelector('.image-upload-container');
            const imagePreview = document.getElementById('image-preview');
            const saveBtn = document.getElementById('saveBtn');
            const resetBtn = document.getElementById('resetBtn');
            let isEditMode = false;

            // Store original values
            const originalValues = {};
            editableFields.forEach(field => {
                originalValues[field.name] = field.type === 'checkbox' ? field.checked : field.value;
            });

            // Toggle edit mode
            editToggle.addEventListener('click', function() {
                isEditMode = !isEditMode;
                this.classList.toggle('active', isEditMode);

                editableFields.forEach(field => {
                    if (field.tagName === 'SELECT') {
                        field.disabled = !isEditMode;
                    } else {
                        field.readOnly = !isEditMode;
                    }
                });

                imageUpload.disabled = !isEditMode;
                imageUploadContainer.style.pointerEvents = isEditMode ? 'auto' : 'none';
                saveBtn.disabled = !isEditMode;
                resetBtn.disabled = !isEditMode;

                if (isEditMode) {
                    editableFields[1].focus(); // Focus on asset name
                }
            });

            // Image preview functionality
            imageUpload.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                        imageUploadContainer.classList.add('has-image');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Calculate total value
            const quantityInput = document.getElementById('asset_quantity');
            const priceInput = document.getElementById('asset_price');
            const totalValueInput = document.getElementById('total_value');

            function calculateTotal() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = quantity * price;
                totalValueInput.value = total.toFixed(2);
            }

            quantityInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);

            // Reset form
            resetBtn.addEventListener('click', function() {
                editableFields.forEach(field => {
                    if (field.type === 'checkbox') {
                        field.checked = originalValues[field.name];
                    } else {
                        field.value = originalValues[field.name];
                    }
                });
                calculateTotal();

                // Reset image preview
                if (originalValues.asset_avatar) {
                    imagePreview.src = '{{ asset($asset->asset_avatar ?? '') }}';
                    imagePreview.style.display = 'block';
                } else {
                    imagePreview.style.display = 'none';
                    imageUploadContainer.classList.remove('has-image');
                }
            });

            // Form validation
            const form = document.getElementById('assetForm');
            form.addEventListener('submit', function(e) {
                if (!isEditMode) {
                    e.preventDefault();
                    alert('Please enable editing mode to save changes.');
                    return;
                }

                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = 'var(--danger-color)';
                        isValid = false;
                    } else {
                        field.style.borderColor = '#e9ecef';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>
@endsection
