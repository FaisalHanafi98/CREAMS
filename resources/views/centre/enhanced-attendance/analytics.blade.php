<!-- resources/views/centre/enhanced-attendance/analytics.blade.php -->
@extends('layouts.app')

@section('title', 'Attendance Analytics | CREAMS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Attendance Analytics</h5>
                    <div class="card-options">
                        <a href="{{ route('centre.attendance.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Attendance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 text-center py-5">
                            <i class="fas fa-chart-line fa-5x text-muted mb-4"></i>
                            <h4 class="text-muted">Attendance Analytics</h4>
                            <p class="text-muted mb-4">
                                Detailed attendance analytics are coming soon.
                                @if(!empty($startDate) && !empty($endDate))
                                    <br><small>Selected range: {{ $startDate }} to {{ $endDate }}</small>
                                @endif
                            </p>
                            <a href="{{ route('centre.attendance.index') }}" class="btn btn-primary">
                                <i class="fas fa-clipboard-list mr-2"></i>Manage Attendance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
