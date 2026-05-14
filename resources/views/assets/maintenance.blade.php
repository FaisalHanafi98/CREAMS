@extends('layouts.app')

@section('title', 'Asset Maintenance - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-tools me-2 text-warning"></i>Maintenance Records
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Assets
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-left border-primary">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Total</div>
                    <div class="fw-bold fs-4">{{ $records->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-left border-warning">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Scheduled</div>
                    <div class="fw-bold fs-4 text-warning">{{ $records->where('status', 'scheduled')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-left border-success">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Completed</div>
                    <div class="fw-bold fs-4 text-success">{{ $records->where('status', 'completed')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-left border-danger">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Overdue</div>
                    <div class="fw-bold fs-4 text-danger">{{ $records->where('status', 'scheduled')->filter(fn($r) => $r->scheduled_date && \Carbon\Carbon::parse($r->scheduled_date)->isPast())->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Asset</th>
                            <th>Type</th>
                            <th>Scheduled Date</th>
                            <th>Completed Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Cost</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td>
                                <a href="{{ route('assets.show', $record->asset_id) }}" class="text-decoration-none">
                                    {{ $record->asset?->asset_name ?? 'Unknown' }}
                                </a>
                                <div class="text-muted small">{{ $record->asset?->asset_tag ?? '' }}</div>
                            </td>
                            <td>{{ ucfirst($record->maintenance_type ?? 'General') }}</td>
                            <td>{{ $record->scheduled_date ? \Carbon\Carbon::parse($record->scheduled_date)->format('M d, Y') : '—' }}</td>
                            <td>{{ $record->completed_date ? \Carbon\Carbon::parse($record->completed_date)->format('M d, Y') : '—' }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'scheduled' => 'bg-primary',
                                        'in_progress' => 'bg-warning text-dark',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-secondary'
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$record->status ?? 'scheduled'] ?? 'bg-secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->status ?? 'scheduled')) }}
                                </span>
                            </td>
                            <td>{{ ucfirst($record->priority ?? 'normal') }}</td>
                            <td>{{ $record->cost ? 'RM ' . number_format($record->cost, 2) : '—' }}</td>
                            <td>{{ $record->description ?? $record->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($records->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-tools fa-3x mb-2"></i><br>No maintenance records found
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
