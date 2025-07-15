<!-- resources/views/attendance/report.blade.php -->
@extends('layouts.app')

@section('title', 'Attendance Reports | CREAMS')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboardstyle.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Attendance Reports</h5>
                    <div class="card-options">
                        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Attendance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 text-center py-5">
                            <i class="fas fa-chart-bar fa-5x text-muted mb-4"></i>
                            <h4 class="text-muted">Attendance Reports</h4>
                            <p class="text-muted mb-4">Detailed attendance reporting features are coming soon.</p>
                            <a href="{{ route('attendance.index') }}" class="btn btn-primary">
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

