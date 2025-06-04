<div class="card shadow mb-4" x-data="{ open: true }">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0 text-primary">Asset Items</h5>
        <button class="btn btn-sm btn-outline-primary" @click="open = !open">
            <span x-show="!open">Show Items</span>
            <span x-show="open">Hide Items</span>
        </button>
    </div>
    <div class="card-body" x-show="open" x-transition>
        @if ($assetType->assetItems->count())
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Tag</th>
                            <th>Location</th>
                            <th>Value</th>
                            <th>Created</th>
                            <th>Actions</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assetType->assetItems as $item)
                            <tr x-data="{ editing: false }">
                                <td>
                                    <template x-if="!editing">
                                        <span>{{ $item->tag }}</span>
                                    </template>
                                    <template x-if="editing">
                                        <input type="text" name="tag" form="update-form-{{ $item->id }}"
                                            value="{{ $item->tag }}" class="form-control form-control-sm" required>
                                    </template>
                                </td>
                                <td>
                                    <template x-if="!editing">
                                        <span>{{ $item->location ?? 'N/A' }}</span>
                                    </template>
                                    <template x-if="editing">
                                        <input type="text" name="location" form="update-form-{{ $item->id }}"
                                            value="{{ $item->location }}" class="form-control form-control-sm">
                                    </template>
                                </td>
                                <td>
                                    <template x-if="!editing">
                                        <span>{{ $item->value !== null ? 'RM ' . number_format($item->value, 2) : 'N/A' }}</span>
                                    </template>
                                    <template x-if="editing">
                                        <input type="number" step="0.01" name="value"
                                            form="update-form-{{ $item->id }}" value="{{ $item->value }}"
                                            class="form-control form-control-sm">
                                    </template>
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td class="text-nowrap">
                                    <template x-if="!editing">
                                        <button type="button" class="btn btn-sm btn-primary"
                                            @click="editing = true">Edit</button>
                                    </template>
                                    <template x-if="editing">
                                        <button type="submit" form="update-form-{{ $item->id }}"
                                            class="btn btn-sm btn-success">Save</button>
                                        <button type="button" class="btn btn-sm btn-secondary"
                                            @click="editing = false">Cancel</button>
                                    </template>
                                </td>
                                <td>
                                    <form action="{{ route(session('role') . '.asset-items.destroy', $item->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this asset item? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <form id="update-form-{{ $item->id }}"
                                action="{{ route(session('role') . '.asset-items.update', $item->id) }}" method="POST"
                                class="d-none">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="asset_type_id" value="{{ $assetType->id }}">
                            </form>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No asset items available.</p>
        @endif

        <hr>

        <!-- Add New Item -->
        <h6>Add New Asset Item</h6>
        <form action="{{ route(session('role') . '.asset-items.store') }}" method="POST">
            @csrf
            <input type="hidden" name="asset_type_id" value="{{ $assetType->id }}">

            <div class="form-row">
                <div class="col">
                    <input type="text" name="tag" placeholder="Tag" class="form-control form-control-sm"
                        required>
                </div>
                <div class="col">
                    <input type="text" name="location" placeholder="Location" class="form-control form-control-sm">
                </div>
                <div class="col">
                    <input type="number" step="0.01" name="value" placeholder="Value"
                        class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-success">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>
