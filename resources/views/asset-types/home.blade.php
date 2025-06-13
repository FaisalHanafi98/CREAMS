@extends('layouts.app')

@section('title', 'Asset Type Management')

@section('styles')
    <style>
        .asset-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .table-search {
            max-width: 300px;
        }

        .empty-state {
            padding: 3rem;
            text-align: center;
        }

        .empty-state img {
            max-width: 200px;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="dashboard-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="dashboard-title">Asset Type Management</h1>
                    <div class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <span class="current">Asset Types</span>
                    </div>
                </div>
                <div class="col-auto">
                    <a href="{{ route(session('role') . '.asset-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus fa-sm mr-1"></i> Add New Asset Type
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Search -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control table-search" placeholder="Search asset types...">
        </div>

        @if ($assetTypes->count())
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="assetTypeTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Image</th>
                            <th>Name & Category</th>
                            <th>Location</th>
                            <th>Value</th>
                            <th>Vendor</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assetTypes as $assetType)
                            <tr>
                                <td>
                                    @if ($assetType->image_path)
                                        <img src="{{ asset('storage/' . $assetType->image_path) }}" class="asset-thumbnail"
                                            alt="Asset Image">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $assetType->name }}</strong><br>
                                    <small class="text-muted">{{ $assetType->category }}</small>
                                </td>
                                <td>{{ $assetType->location ?? 'N/A' }}</td>
                                <td>{{ $assetType->value ? 'RM ' . number_format($assetType->value, 2) : 'N/A' }}</td>
                                <td>{{ $assetType->vendor ?? 'N/A' }}</td>
                                <td>{{ $assetType->assetItems->count() }}</td>
                                <td>
                                    <a href="{{ route(session('role') . '.asset-types.edit', $assetType->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>
                                    <form action="{{ route(session('role') . '.asset-types.destroy', $assetType->id) }}"
                                        method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Delete this asset type and all items?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <img src="{{ asset('images/no-data.svg') }}" alt="No Asset Types">
                <p>No asset types found.</p>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $(".alert").fadeOut("slow");
            }, 5000);

            // Simple client-side search filter
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#assetTypeTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().includes(value));
                });
            });
        });
    </script>
@endsection
