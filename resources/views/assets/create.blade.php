@extends('layouts.app')

@section('title', 'Create Asset - CREAMS')

@section('styles')
<style>
    .asset-create-page {
        padding: 0 0 2rem;
    }

    .asset-create-header {
        background: linear-gradient(135deg, rgba(50, 189, 234, 0.14), rgba(200, 80, 192, 0.14));
        border: 1px solid rgba(50, 189, 234, 0.16);
        border-radius: 20px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
    }

    .asset-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
        font-size: 0.92rem;
        margin-bottom: 0.85rem;
    }

    .asset-breadcrumb a {
        color: #32bdea;
        font-weight: 600;
        text-decoration: none;
    }

    .asset-breadcrumb-current {
        color: #6b7280;
        font-weight: 500;
    }

    .asset-page-title {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    .asset-page-title i {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #32bdea, #c850c0);
        box-shadow: 0 12px 28px rgba(50, 189, 234, 0.2);
    }

    .asset-page-subtitle {
        margin: 0.75rem 0 0;
        color: #556070;
        max-width: 780px;
    }

    .asset-header-actions {
        display: flex;
        justify-content: flex-end;
        align-items: start;
    }

    .asset-outline-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        border: 1px solid #d7dce3;
        background: #fff;
        color: #374151;
        border-radius: 12px;
        padding: 0.8rem 1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .asset-outline-btn:hover {
        color: #32bdea;
        border-color: rgba(50, 189, 234, 0.35);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .asset-alert {
        border: none;
        border-radius: 16px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .asset-alert ul {
        margin-top: 0.6rem;
        margin-bottom: 0;
        padding-left: 1.1rem;
    }

    .asset-form-shell,
    .asset-side-card {
        background: #fff;
        border: 1px solid #e8edf3;
        border-radius: 18px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
    }

    .asset-form-shell {
        overflow: hidden;
    }

    .asset-form-body {
        padding: 1.6rem;
    }

    .asset-section {
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #edf1f6;
    }

    .asset-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .asset-section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
    }

    .asset-section-title {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .asset-section-title i {
        color: #32bdea;
    }

    .asset-section-subtitle {
        margin: 0.4rem 0 0;
        color: #6b7280;
        font-size: 0.92rem;
    }

    .asset-form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.45rem;
    }

    .asset-form-label .required {
        color: #dc3545;
    }

    .asset-input.form-control,
    .asset-input.form-select,
    .asset-input textarea.form-control {
        border-radius: 12px;
        border: 1px solid #d7dce3;
        padding: 0.8rem 0.95rem;
        min-height: 48px;
        box-shadow: none;
    }

    .asset-input.form-control:focus,
    .asset-input.form-select:focus,
    .asset-input textarea.form-control:focus {
        border-color: rgba(50, 189, 234, 0.65);
        box-shadow: 0 0 0 0.18rem rgba(50, 189, 234, 0.15);
    }

    textarea.asset-input.form-control {
        min-height: 120px;
    }

    .asset-field-help {
        margin-top: 0.35rem;
        color: #6b7280;
        font-size: 0.84rem;
    }

    .asset-upload-block {
        border: 1px dashed rgba(50, 189, 234, 0.4);
        border-radius: 16px;
        padding: 1rem;
        background: linear-gradient(180deg, rgba(50, 189, 234, 0.04), rgba(255, 255, 255, 1));
    }

    .asset-form-actions {
        padding: 1.1rem 1.6rem 1.5rem;
        border-top: 1px solid #edf1f6;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        background: #fcfdff;
    }

    .asset-primary-btn,
    .asset-secondary-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        border-radius: 12px;
        padding: 0.82rem 1.15rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .asset-primary-btn {
        border: none;
        color: #fff;
        background: linear-gradient(135deg, #32bdea, #c850c0);
        box-shadow: 0 12px 28px rgba(50, 189, 234, 0.25);
    }

    .asset-primary-btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 30px rgba(50, 189, 234, 0.3);
    }

    .asset-secondary-btn {
        border: 1px solid #d7dce3;
        background: #fff;
        color: #374151;
    }

    .asset-secondary-btn:hover {
        color: #32bdea;
        border-color: rgba(50, 189, 234, 0.35);
        text-decoration: none;
    }

    .asset-side-card {
        padding: 1.4rem;
        margin-bottom: 1rem;
    }

    .asset-side-card h5 {
        margin-bottom: 1rem;
        font-size: 1.02rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .asset-tip-list,
    .asset-requirements {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.8rem;
    }

    .asset-tip-list li,
    .asset-requirements li {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        color: #4b5563;
    }

    .asset-tip-list i,
    .asset-requirements i {
        color: #32bdea;
        margin-top: 0.15rem;
    }

    .asset-side-note {
        border-radius: 14px;
        background: rgba(50, 189, 234, 0.07);
        border: 1px solid rgba(50, 189, 234, 0.12);
        padding: 0.95rem 1rem;
        color: #425466;
        font-size: 0.92rem;
    }

    #imagePreviews {
        gap: 0.75rem;
    }

    @media (max-width: 991.98px) {
        .asset-header-actions {
            justify-content: flex-start;
            margin-top: 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .asset-create-header,
        .asset-form-body,
        .asset-form-actions,
        .asset-side-card {
            padding: 1.1rem;
        }

        .asset-page-title {
            font-size: 1.55rem;
        }

        .asset-page-title i {
            width: 44px;
            height: 44px;
        }

        .asset-form-actions {
            flex-direction: column-reverse;
        }

        .asset-primary-btn,
        .asset-secondary-btn,
        .asset-outline-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid asset-create-page">
    <div class="asset-create-header">
        <div class="row align-items-start g-3">
            <div class="col-lg-8">
                <div class="asset-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <a href="{{ route('asset-parents.index') }}">Assets</a>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="asset-breadcrumb-current">Create Asset</span>
                </div>
                <h1 class="asset-page-title">
                    <i class="fas fa-box-open"></i>
                    Create New Asset
                </h1>
                <p class="asset-page-subtitle">
                    Register a new asset with complete identification, placement, condition, and supporting images so the asset inventory stays accurate and audit-friendly.
                </p>
            </div>
            <div class="col-lg-4 asset-header-actions">
                <a href="{{ route('asset-parents.index') }}" class="asset-outline-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Assets
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger asset-alert mb-4" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Please correct the highlighted fields.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="asset-form-shell">
                <form action="{{ route('asset-parents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="asset-form-body">
                        <section class="asset-section">
                            <div class="asset-section-header">
                                <div>
                                    <h2 class="asset-section-title">
                                        <i class="fas fa-fingerprint"></i>
                                        Identity & Classification
                                    </h2>
                                    <p class="asset-section-subtitle">Set the core record details used for inventory, search, and reporting.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="asset-form-label">Asset Name <span class="required">*</span></label>
                                    <input type="text" class="form-control asset-input @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="asset_id" class="asset-form-label">Asset ID</label>
                                    <input type="text" class="form-control asset-input @error('asset_id') is-invalid @enderror"
                                           id="asset_id" name="asset_id" value="{{ old('asset_id') }}"
                                           placeholder="Auto-generated if left blank">
                                    @error('asset_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="asset-field-help">Leave blank to let the form generate an ID from the asset name and type.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="type" class="asset-form-label">Asset Type <span class="required">*</span></label>
                                    <select class="form-select asset-input @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select asset type</option>
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
                                    <label for="brand" class="asset-form-label">Brand</label>
                                    <input type="text" class="form-control asset-input @error('brand') is-invalid @enderror"
                                           id="brand" name="brand" value="{{ old('brand') }}"
                                           placeholder="Manufacturer or brand name">
                                    @error('brand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="asset-section">
                            <div class="asset-section-header">
                                <div>
                                    <h2 class="asset-section-title">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Placement & Ownership
                                    </h2>
                                    <p class="asset-section-subtitle">Assign the asset to the right centre and internal location.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="centre_id" class="asset-form-label">Centre <span class="required">*</span></label>
                                    <select class="form-select asset-input @error('centre_id') is-invalid @enderror" id="centre_id" name="centre_id" required>
                                        <option value="">Select centre</option>
                                        @foreach($centres as $centre)
                                            <option value="{{ $centre->centre_id }}"
                                                {{ (old('centre_id', $selectedCentre ?? '') == $centre->centre_name || old('centre_id', $selectedCentre ?? '') == $centre->centre_id) ? 'selected' : '' }}>
                                                {{ $centre->centre_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('centre_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="asset-form-label">Specific Location</label>
                                    <input type="text" class="form-control asset-input @error('location') is-invalid @enderror"
                                           id="location" name="location" value="{{ old('location') }}"
                                           placeholder="e.g. Room 101, Therapy Store A">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="asset-section">
                            <div class="asset-section-header">
                                <div>
                                    <h2 class="asset-section-title">
                                        <i class="fas fa-wallet"></i>
                                        Acquisition & Condition
                                    </h2>
                                    <p class="asset-section-subtitle">Capture cost, purchase date, and current condition for lifecycle tracking.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="purchase_price" class="asset-form-label">Purchase Price (RM)</label>
                                    <input type="number" step="0.01" class="form-control asset-input @error('purchase_price') is-invalid @enderror"
                                           id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}"
                                           placeholder="0.00">
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="purchase_date" class="asset-form-label">Purchase Date</label>
                                    <input type="date" class="form-control asset-input @error('purchase_date') is-invalid @enderror"
                                           id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="condition" class="asset-form-label">Condition <span class="required">*</span></label>
                                    <select class="form-select asset-input @error('condition') is-invalid @enderror" id="condition" name="condition" required>
                                        <option value="">Select condition</option>
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
                        </section>

                        <section class="asset-section">
                            <div class="asset-section-header">
                                <div>
                                    <h2 class="asset-section-title">
                                        <i class="fas fa-align-left"></i>
                                        Description
                                    </h2>
                                    <p class="asset-section-subtitle">Add operational context that helps staff identify and manage this asset correctly.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="description" class="asset-form-label">Asset Description</label>
                                    <textarea class="form-control asset-input @error('description') is-invalid @enderror"
                                              id="description" name="description" rows="4"
                                              placeholder="Describe the asset, its purpose, and any identifying notes...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="asset-section">
                            <div class="asset-section-header">
                                <div>
                                    <h2 class="asset-section-title">
                                        <i class="fas fa-images"></i>
                                        Asset Images
                                    </h2>
                                    <p class="asset-section-subtitle">Upload one or more clear photos. The first image becomes the primary gallery image.</p>
                                </div>
                            </div>

                            <div class="asset-upload-block">
                                <label for="images" class="asset-form-label">Image Upload</label>
                                <input type="file" class="form-control asset-input @error('images.*') is-invalid @enderror"
                                       id="images" name="images[]" accept="image/*" multiple>
                                @error('images.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="asset-field-help">
                                    Supported formats: JPG, PNG, GIF. Maximum size is 2MB per image.
                                </div>

                                <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                                    <h6 class="mb-2 text-muted fw-semibold">Preview</h6>
                                    <div id="imagePreviews" class="d-flex flex-wrap"></div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="asset-form-actions">
                        <a href="{{ route('asset-parents.index') }}" class="asset-secondary-btn">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" class="asset-primary-btn">
                            <i class="fas fa-save"></i>
                            Create Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="asset-side-card">
                <h5><i class="fas fa-lightbulb text-warning"></i>Asset Entry Tips</h5>
                <ul class="asset-tip-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Use descriptive names so the asset is easy to find in reports and maintenance logs.</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Assign the correct centre and room now to avoid stock-location mismatches later.</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Include purchase price and date whenever available for asset lifecycle tracking.</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Upload clear photos that help staff identify the exact item during audits or movement requests.</span>
                    </li>
                </ul>
            </div>

            <div class="asset-side-card">
                <h5><i class="fas fa-clipboard-check text-success"></i>Required Fields</h5>
                <ul class="asset-requirements">
                    <li><i class="fas fa-asterisk"></i><span>Asset name</span></li>
                    <li><i class="fas fa-asterisk"></i><span>Asset type</span></li>
                    <li><i class="fas fa-asterisk"></i><span>Centre</span></li>
                    <li><i class="fas fa-asterisk"></i><span>Condition</span></li>
                </ul>
            </div>

            <div class="asset-side-card">
                <h5><i class="fas fa-shield-alt text-info"></i>Before You Save</h5>
                <div class="asset-side-note">
                    Double-check that the asset type, centre assignment, and condition are accurate. Those fields drive filtering, reporting, and later maintenance workflows across the system.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    const imagesInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewsDiv = document.getElementById('imagePreviews');

    imagesInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        previewsDiv.innerHTML = '';

        if (files.length > 0) {
            previewContainer.style.display = 'block';

            files.forEach((file, index) => {
                if (file.size > 2 * 1024 * 1024) {
                    alert(`File ${file.name} is too large. Maximum size is 2MB.`);
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert(`File ${file.name} is not an image.`);
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(loadEvent) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'position-relative';
                    previewDiv.innerHTML = `
                        <img src="${loadEvent.target.result}" class="img-thumbnail" style="width: 104px; height: 104px; object-fit: cover; border-radius: 12px;">
                        <div class="position-absolute top-0 start-0 m-1">
                            ${index === 0 ? '<span class="badge bg-primary">Primary</span>' : ''}
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    `;
                    previewsDiv.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            });
        } else {
            previewContainer.style.display = 'none';
        }
    });
});
</script>
@endpush
