@extends('layouts.app')

@section('title', $asset->asset_name . ' - Asset Details')

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

        .asset-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
            position: relative;
            overflow: hidden;
        }

        .asset-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .asset-header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }

        .asset-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }

        .asset-overview {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .asset-overview-header {
            background: linear-gradient(135deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
            padding: 25px 30px;
            border-bottom: 1px solid #f1f3f4;
        }

        .asset-overview-header h4 {
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
            gap: 30px;
            align-items: flex-start;
        }

        .asset-image-section {
            flex-shrink: 0;
        }

        .asset-main-image {
            width: 250px;
            height: 250px;
            border-radius: 15px;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            box-shadow: 0 10px 30px rgba(200, 80, 192, 0.3);
        }

        .asset-info-section {
            flex: 1;
        }

        .asset-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0 0 10px 0;
        }

        .asset-id {
            font-size: 1rem;
            color: #6c757d;
            font-family: 'Courier New', monospace;
            background: var(--light-bg);
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .asset-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .meta-item {
            background: var(--light-bg);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .meta-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .meta-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .asset-type-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 2px solid var(--success-color);
        }

        .status-maintenance {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
            border: 2px solid var(--warning-color);
        }

        .status-unavailable {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 2px solid var(--danger-color);
        }

        .quantity-display {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .price-display {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-color);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .detail-card-header {
            background: linear-gradient(135deg, rgba(200, 80, 192, 0.1), rgba(50, 189, 234, 0.1));
            padding: 20px 25px;
            border-bottom: 1px solid #f1f3f4;
        }

        .detail-card-header h5 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-card-body {
            padding: 25px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark-color);
            text-align: right;
        }

        .notes-section {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .notes-section h6 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notes-content {
            color: #6c757d;
            line-height: 1.6;
            font-style: italic;
        }

        .action-buttons {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
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

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
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

        .btn-light {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            color: var(--dark-color);
        }

        .btn-light:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            color: var(--dark-color);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 600;
        }

        .timeline-content {
            color: var(--dark-color);
            font-weight: 500;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .asset-header {
                text-align: center;
                padding: 1.5rem;
            }

            .asset-header h1 {
                font-size: 1.8rem;
            }

            .asset-profile {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }

            .asset-main-image {
                width: 200px;
                height: 200px;
            }

            .asset-meta {
                grid-template-columns: 1fr;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
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
        <!-- Asset Header -->
        <div class="asset-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fas fa-cube me-3"></i>{{ $asset->asset_name }}
                    </h1>
                    <p>Detailed information and specifications for this asset</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('assets.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Asset
                    </a>
                </div>
            </div>
        </div>

        <!-- Asset Overview -->
        <div class="asset-overview">
            <div class="asset-overview-header">
                <h4><i class="fas fa-info-circle"></i>Asset Overview</h4>
            </div>
            <div class="asset-profile">
                <div class="asset-image-section">
                    <img src="{{ $asset->primary_image_url }}" alt="{{ $asset->asset_name }}" class="asset-main-image"
                        id="mainAssetImage" onerror="this.src='{{ asset('images/default-asset.png') }}'">

                    @if ($asset->images && count($asset->images) > 1)
                        <div class="asset-gallery mt-3">
                            <h6 class="text-muted mb-2">Gallery</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($asset->images as $index => $image)
                                    <img src="{{ asset('storage/' . $image) }}" alt="Asset Image {{ $index + 1 }}"
                                        class="gallery-thumb {{ $index === 0 ? 'active' : '' }}"
                                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid {{ $index === 0 ? 'var(--primary-color)' : 'transparent' }};"
                                        onclick="changeMainImage('{{ asset('storage/' . $image) }}', this)"
                                        onerror="this.style.display='none'">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="asset-info-section">
                    <h2 class="asset-title">{{ $asset->asset_name }}</h2>
                    <div class="asset-id">Asset ID: {{ $asset->asset_id }}</div>
                    <div class="asset-type-badge">{{ $asset->asset_parent ?? 'N/A' }}</div>

                    <div class="asset-meta">
                        <div class="meta-item">
                            <div class="meta-label">Brand</div>
                            <div class="meta-value">{{ $asset->asset_brand ?? 'N/A' }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Quantity</div>
                            <div class="meta-value quantity-display">{{ $asset->asset_quantity }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Unit Price</div>
                            <div class="meta-value price-display">RM {{ number_format($asset->asset_price, 2) }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Total Value</div>
                            <div class="meta-value price-display">RM
                                {{ number_format(($asset->asset_quantity ?? 0) * ($asset->asset_price ?? 0), 2) }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Centre</div>
                            <div class="meta-value">{{ $asset->centre_name ?? ($asset->center_name ?? 'Unassigned') }}
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Status</div>
                            <div class="meta-value">
                                @if (($asset->asset_quantity ?? 0) > 10)
                                    <span class="status-badge status-available">Available</span>
                                @elseif(($asset->asset_quantity ?? 0) > 0)
                                    <span class="status-badge status-maintenance">Low Stock</span>
                                @else
                                    <span class="status-badge status-unavailable">Out of Stock</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($asset->asset_note)
                        <div class="notes-section">
                            <h6><i class="fas fa-sticky-note"></i>Notes & Description</h6>
                            <div class="notes-content">{{ $asset->asset_note }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="details-grid">
            <!-- Technical Details -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h5><i class="fas fa-cogs"></i>Technical Details</h5>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Asset ID</span>
                        <span class="detail-value">{{ $asset->asset_id }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Asset Type</span>
                        <span class="detail-value">{{ $asset->asset_parent ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Brand/Manufacturer</span>
                        <span class="detail-value">{{ $asset->asset_brand ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Model/Name</span>
                        <span class="detail-value">{{ $asset->asset_name }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h5><i class="fas fa-dollar-sign"></i>Financial Information</h5>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Unit Price</span>
                        <span class="detail-value">RM {{ number_format($asset->asset_price, 2) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Current Quantity</span>
                        <span class="detail-value">{{ $asset->asset_quantity }} units</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Value</span>
                        <span class="detail-value">RM
                            {{ number_format(($asset->asset_quantity ?? 0) * ($asset->asset_price ?? 0), 2) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Depreciation</span>
                        <span class="detail-value">N/A</span>
                    </div>
                </div>
            </div>

            <!-- Location & Status -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h5><i class="fas fa-map-marker-alt"></i>Location & Status</h5>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Assigned Centre</span>
                        <span
                            class="detail-value">{{ $asset->centre_name ?? ($asset->center_name ?? 'Unassigned') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Current Status</span>
                        <span class="detail-value">
                            @if (($asset->asset_quantity ?? 0) > 10)
                                <span class="status-badge status-available">Available</span>
                            @elseif(($asset->asset_quantity ?? 0) > 0)
                                <span class="status-badge status-maintenance">Low Stock</span>
                            @else
                                <span class="status-badge status-unavailable">Out of Stock</span>
                            @endif
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Updated</span>
                        <span class="detail-value">
                            {{ $asset->updated_at ? $asset->updated_at->format('M j, Y g:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date Added</span>
                        <span class="detail-value">
                            {{ $asset->created_at ? $asset->created_at->format('M j, Y g:i A') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h5><i class="fas fa-history"></i>Recent Activity</h5>
                </div>
                <div class="detail-card-body">
                    <div class="timeline">
                        @if ($asset->updated_at && $asset->created_at && $asset->updated_at != $asset->created_at)
                            <div class="timeline-item">
                                <div class="timeline-date">{{ $asset->updated_at->format('M j, Y g:i A') }}</div>
                                <div class="timeline-content">Asset information updated</div>
                            </div>
                        @endif
                        <div class="timeline-item">
                            <div class="timeline-date">
                                {{ $asset->created_at ? $asset->created_at->format('M j, Y g:i A') : 'N/A' }}</div>
                            <div class="timeline-content">Asset created and added to inventory</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">System Generated</div>
                            <div class="timeline-content">Asset profile generated automatically</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list"></i>View All Asset
                </a>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if (in_array(session('role'), ['admin', 'supervisor']))
                    <a href="{{ route('assets.edit', $asset->asset_id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i>Edit Asset
                    </a>
                @endif
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i>Print Details
                </button>
                @if (session('role') === 'admin')
                    <button class="btn btn-danger" onclick="confirmDelete('{{ $asset->asset_id }}')">
                        <i class="fas fa-trash"></i>Delete Asset
                    </button>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function confirmDelete(assetId) {
            if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/assets/${assetId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Asset image gallery functionality
        function changeMainImage(imageSrc, thumbnail) {
            // Update main image
            const mainImage = document.getElementById('mainAssetImage');
            mainImage.src = imageSrc;

            // Update active thumbnail
            const allThumbs = document.querySelectorAll('.gallery-thumb');
            allThumbs.forEach(thumb => {
                thumb.style.border = '2px solid transparent';
                thumb.classList.remove('active');
            });

            thumbnail.style.border = '2px solid var(--primary-color)';
            thumbnail.classList.add('active');
        }

        // Print styles
        const printStyles = `
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .asset-overview, .asset-overview * {
                visibility: visible;
            }
            .details-grid, .details-grid * {
                visibility: visible;
            }
            .asset-overview {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .action-buttons {
                display: none !important;
            }
            .asset-header {
                background: #c850c0 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
`;

        document.head.insertAdjacentHTML('beforeend', printStyles);
    </script>
@endsection
