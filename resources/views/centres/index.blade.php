@extends('layouts.dashboard')

@section('title', 'Centres')

@section('content')
<div class="content-section">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Rehabilitation Centres</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($centres as $centre)
                <div class="col-md-4 mb-4">
                    <div class="centre-card">
                        <div class="centre-header">
                            <h5 class="centre-name">{{ $centre['name'] }}</h5>
                            <div class="centre-stats">
                                <div class="stat">
                                    <span class="stat-value">{{ $centre['staff_count'] }}</span>
                                    <span class="stat-label">Staff</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">{{ $centre['trainee_count'] }}</span>
                                    <span class="stat-label">Tainees</span>
                                </div>
                            </div>
                        </div>
                        <div class="centre-body">
                            <div class="centre-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $centre['address'] }}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $centre['phone'] }}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ $centre['email'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="centre-footer">
                            <a href="{{ route('centres.show', $centre['id']) }}" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .centre-card {
        background-color: var(--light-color);
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .centre-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .centre-header {
        padding: 20px;
        background: var(--primary-gradient);
        color: var(--light-color);
    }
    
    .centre-name {
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .centre-stats {
        display: flex;
        gap: 20px;
    }
    
    .stat {
        text-align: centre;
    }
    
    .stat-value {
        font-size: 20px;
        font-weight: 700;
        display: block;
    }
    
    .stat-label {
        font-size: 12px;
        opacity: 0.8;
    }
    
    .centre-body {
        padding: 20px;
        flex: 1;
    }
    
    .centre-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .info-item {
        display: flex;
        gap: 10px;
        font-size: 14px;
    }
    
    .info-item i {
        color: var(--primary-color);
        width: 16px;
    }
    
    .centre-footer {
        padding: 15px 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        text-align: centre;
    }
</style>
@endsection