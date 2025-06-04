<form
    action="{{ isset($assetType)
        ? route(session('role') . '.asset-types.update', $assetType->id)
        : route(session('role') . '.asset-types.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if (isset($assetType))
        @method('PUT')
    @endif

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $assetType->name ?? '') }}" class="form-control"
                required>
        </div>

        <div class="form-group col-md-6">
            <label>Category <span class="text-danger">*</span></label>
            <input type="text" name="category" value="{{ old('category', $assetType->category ?? '') }}"
                class="form-control" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Location</label>
            <input type="text" name="location" value="{{ old('location', $assetType->location ?? '') }}"
                class="form-control">
        </div>

        <div class="form-group col-md-2">
            <label>Value</label>
            <input type="number" step="0.01" name="value" value="{{ old('value', $assetType->value ?? '') }}"
                class="form-control">
        </div>

        <div class="form-group col-md-4">
            <label>Vendor</label>
            <input type="text" name="vendor" value="{{ old('vendor', $assetType->vendor ?? '') }}"
                class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label>{{ isset($assetType) ? 'Replace Image (optional)' : 'Asset Image' }}</label>
        <input type="file" name="image_path" class="form-control-file">
        @if (isset($assetType) && $assetType->image_path)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $assetType->image_path) }}" alt="Asset Image" style="max-width: 150px;">
            </div>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">
        {{ isset($assetType) ? 'Update' : 'Create Asset Type' }}
    </button>
    <a href="{{ route(session('role') . '.asset-types.index') }}" class="btn btn-secondary ml-2">Cancel</a>
</form>
