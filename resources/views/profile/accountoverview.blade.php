@extends('layouts.app')

@section('title', 'User Profile - CREAMS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="dashboard-title">User Profile</h1>
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <span class="current">User Profile</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-circle mr-2"></i>Profile Information
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Profile content here -->
                </div>
            </div>
        </div>
        
        <!-- Update Credentials Card -->
        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-key mr-2"></i>Update Credentials
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Form content here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection