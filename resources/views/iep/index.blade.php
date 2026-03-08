@extends('layouts.app')

@section('title', 'Individual Education Plans - CREAMS')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>Individual Education Plans
        </h2>
        <a href="{{ route('iep.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New IEP
        </a>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Total Plans</div>
                    <div class="fw-bold fs-4 text-primary">{{ $stats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Active</div>
                    <div class="fw-bold fs-4 text-success">{{ $stats['active'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Completed</div>
                    <div class="fw-bold fs-4 text-info">{{ $stats['completed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small fw-bold text-uppercase">Due for Review</div>
                    <div class="fw-bold fs-4 text-warning">{{ $stats['due_for_review'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('iep.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Plan name or trainee..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Active" @selected(request('status') === 'Active')>Active</option>
                        <option value="Completed" @selected(request('status') === 'Completed')>Completed</option>
                        <option value="Suspended" @selected(request('status') === 'Suspended')>Suspended</option>
                        <option value="Under Review" @selected(request('status') === 'Under Review')>Under Review</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Plan Type</label>
                    <select name="plan_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="Annual" @selected(request('plan_type') === 'Annual')>Annual</option>
                        <option value="Quarterly" @selected(request('plan_type') === 'Quarterly')>Quarterly</option>
                        <option value="Custom" @selected(request('plan_type') === 'Custom')>Custom</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Trainee</label>
                    <select name="trainee_id" class="form-select form-select-sm">
                        <option value="">All Trainees</option>
                        @foreach($trainees as $trainee)
                            <option value="{{ $trainee->id }}" @selected(request('trainee_id') == $trainee->id)>
                                {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm me-1">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('iep.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- IEP Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Trainee</th>
                            <th>Plan Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Period</th>
                            <th>Goals</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ieps as $iep)
                        <tr>
                            <td>{{ $iep->id }}</td>
                            <td>
                                {{ $iep->trainee ? $iep->trainee->trainee_first_name . ' ' . $iep->trainee->trainee_last_name : '—' }}
                            </td>
                            <td>{{ $iep->plan_name }}</td>
                            <td>
                                <span class="badge bg-{{ $iep->type_color ?? 'secondary' }}">{{ $iep->plan_type }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $iep->status_color ?? 'secondary' }}">{{ $iep->status }}</span>
                            </td>
                            <td class="small">
                                {{ $iep->start_date ? $iep->start_date->format('M d, Y') : '—' }}
                                –
                                {{ $iep->end_date ? $iep->end_date->format('M d, Y') : '—' }}
                            </td>
                            <td>{{ $iep->goals ? count($iep->goals) : 0 }}</td>
                            <td>
                                <a href="{{ route('iep.show', $iep->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('iep.edit', $iep->id) }}" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('iep.destroy', $iep->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this IEP?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($ieps->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-graduation-cap fa-3x mb-2"></i><br>
                    No education plans found. <a href="{{ route('iep.create') }}">Create one now.</a>
                </div>
            @endif
        </div>
        @if($ieps->hasPages())
            <div class="card-footer bg-white">
                {{ $ieps->withQueryParameters(request()->all())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
