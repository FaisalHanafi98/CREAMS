@extends('layouts.app')

@section('title', 'Asset Movements - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-arrows-alt me-2 text-info"></i>Asset Movements
        </h2>
        <a href="{{ route('asset-parents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Assets
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Asset</th>
                            <th>Movement Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Moved By</th>
                            <th>Reason</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td>
                                <a href="{{ route('asset-parents.show', $record->asset_id) }}" class="text-decoration-none">
                                    {{ $record->asset?->asset_name ?? 'Unknown' }}
                                </a>
                            </td>
                            <td>{{ $record->movement_date ? \Carbon\Carbon::parse($record->movement_date)->format('M d, Y') : '—' }}</td>
                            <td>{{ $record->from_location_id ?? '—' }}</td>
                            <td>{{ $record->to_location_id ?? '—' }}</td>
                            <td>{{ $record->moved_by_user_id ?? '—' }}</td>
                            <td>{{ $record->reason ?? '—' }}</td>
                            <td>{{ $record->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($records->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-arrows-alt fa-3x mb-2"></i><br>No movement records found
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
