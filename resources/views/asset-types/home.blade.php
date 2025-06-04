@extends('layouts.app')

@section('title', 'Asset Type Management')

@section('styles')
    <style>
        .rehab-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
            border-radius: 30px;
        }

        .avatar-container {
            height: 80px;
            width: 80px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 50%;
        }

        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .empty-state {
            padding: 3rem;
            text-align: center;
        }

        .empty-state img {
            max-width: 200px;
            margin-bottom: 1.5rem;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }

        .badge a {
            color: white;
            text-decoration: none;
        }

        .badge a:hover {
            opacity: 0.8;
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

        <!-- Asset Types List -->
        @if ($assetTypes->count())
            @foreach ($assetTypes as $assetType)
                <div class="card shadow mb-4" x-data="{ open: false }">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            {{ $assetType->name }} ({{ $assetType->category }})
                        </h6>
                        <div class="dropdown">
                            <a class="text-secondary" href="#" role="button"
                                id="assetTypeDropdown{{ $assetType->id }}" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right"
                                aria-labelledby="assetTypeDropdown{{ $assetType->id }}">
                                <a class="dropdown-item"
                                    href="{{ route(session('role') . '.asset-types.edit', $assetType->id) }}">
                                    <i class="fas fa-edit fa-sm mr-2 text-gray-500"></i> Edit
                                </a>
                                <form action="{{ route(session('role') . '.asset-types.destroy', $assetType->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this asset type? This will permanently delete the asset type along with all associated asset items. This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash fa-sm mr-2 text-gray-500"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($assetType->image_path)
                            <div class="mb-3 avatar-container">
                                <img src="{{ asset('storage/' . $assetType->image_path) }}" alt="Asset Image">
                            </div>
                        @endif

                        <p><strong>Location:</strong> {{ $assetType->location ?? 'N/A' }}</p>
                        <p><strong>Value:</strong>
                            {{ $assetType->value ? 'RM ' . number_format($assetType->value, 2) : 'N/A' }}</p>
                        <p><strong>Vendor:</strong> {{ $assetType->vendor ?? 'N/A' }}</p>

                        <hr>
                        <h6 class="text-secondary d-flex justify-content-between align-items-center">
                            Asset Items
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="open = !open">
                                <span x-show="!open">Show Items</span>
                                <span x-show="open">Hide Items</span>
                            </button>
                        </h6>

                        <div x-show="open" x-transition class="mt-3">
                            @if ($assetType->assetItems->count())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Tag</th>
                                                <th>Location</th>
                                                <th>Value</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($assetType->assetItems as $item)
                                                <tr>
                                                    <td>{{ $item->tag }}</td>
                                                    <td>{{ $item->location ?? 'N/A' }}</td>
                                                    <td>{{ $item->value ? 'RM ' . number_format($item->value, 2) : 'N/A' }}
                                                    </td>
                                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No asset items available.</p>
                            @endif

                            <div class="mt-2 text-right">
                                <a href="{{ route(session('role') . '.asset-types.edit', $assetType->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus-circle mr-1"></i> Manage Items
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
        });
    </script>
@endsection
