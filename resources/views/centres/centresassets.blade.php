@extends('layouts.app')

@section('title', $centre['name'] . ' - Assets')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $centre['name'] }} - Assets</h1>
        <div>
            <a href="{{ route(session('role') . '.centres.show', $centre['id']) }}" class="btn btn-secondary btn-sm shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Centre
            </a>
            <a href="#" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Asset
            </a>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Asset List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="assetTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $asset)
                            <tr>
                                <td>{{ $asset['id'] }}</td>
                                <td>{{ $asset['name'] }}</td>
                                <td>{{ $asset['type'] }}</td>
                                <td>{{ $asset['description'] }}</td>
                                <td>{{ $asset['quantity'] }}</td>
                                <td>
                                    <span class="badge badge-{{ $asset['status'] == 'available' ? 'success' : 'warning' }}">
                                        {{ ucfirst($asset['status']) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route(session('role') . '.assets.show', $asset['id']) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#assetTable').DataTable();
    });
</script>
@endsection