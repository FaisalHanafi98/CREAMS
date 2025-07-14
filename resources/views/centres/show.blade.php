@extends('layouts.dashboard')

@section('title', $centre['name'])

@section('content')
<div class="content-section">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">{{ $centre['name'] }}</h5>
                    <div class="card-options">
                        <a href="{{ route('centres.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Centres
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="centre-details">
                        <div class="centre-description">
                            <p>{{ $centre['description'] }}</p>
                        </div>
                        
                        <h6 class="section-title">Contact Information</h6>
                        <div class="contact-details">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-info">
                                    <span class="contact-label">Address</span>
                                    <span class="contact-value">{{ $centre['address'] }}</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-info">
                                    <span class="contact-label">Phone</span>
                                    <span class="contact-value">{{ $centre['phone'] }}</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-info">
                                    <span class="contact-label">Email</span>
                                    <span class="contact-value">{{ $centre['email'] }}</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="contact-info">
                                    <span class="contact-label">Website</span>
                                    <span class="contact-value">{{ $centre['website'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="section-title">Facilities</h6>
                        <div class="facilities-list">
                            @foreach($centre['facilities'] as $facility)
                                <div class="facility-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>{{ $facility }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Centre Staff</h5>
                </div>
                <div class="card-body">
                    <div class="staff-list">
                        @foreach($centre['staff'] as $staff)
                            <div class="staff-item">
                                <div class="staff-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="staff-details">
                                    <h6 class="staff-name">{{ $staff['name'] }}</h6>
                                    <span class="staff-role">{{ $staff['role'] }}</span>
                                    <span class="staff-email">{{ $staff['email'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Centre Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="centre-stats">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ $centre['staff_count'] }}</span>
                                <span class="stat-label">Staff Members</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ $centre['trainee_count'] }}</span>
                                <span class="stat-label">Tainees</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .section-title {
        margin: 25px 0 15px;
        font-weight: 600;
        color: var(--dark-color);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding-bottom: 8px;
    }
    
    .centre-description {
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .contact-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }
    
    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    
    .contact-icon {
        width: 40px;
        height: 40px;
        background: var(--primary-gradient);
        border-radius: 10px;
        display: flex;
        align-items: centre;
        justify-content: centre;
        color: white;
        flex-shrink: 0;
    }
    
    .contact-info {
        display: flex;
        flex-direction: column;
    }
    
    .contact-label {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.5);
    }
    
    .contact-value {
        font-weight: 500;
    }
    
    .facilities-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }
    
    .facility-item {
        display: flex;
        align-items: centre;
        gap: 10px;
        padding: 8px 15px;
        background-color: var(--light-bg);
        border-radius: 8px;
    }
    
    .facility-item i {
        color: var(--primary-color);
    }
    
    .staff-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .staff-item {
        display: flex;
        align-items: centre;
        gap: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .staff-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .staff-avatar {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 10px;
        display: flex;
        align-items: centre;
        justify-content: centre;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .staff-details {
        display: flex;
        flex-direction: column;
    }
    
    .staff-name {
        margin-bottom: 3px;
        font-weight: 600;
    }
    
    .staff-role {
        font-size: 12px;
        color: var(--primary-color);
        margin-bottom: 3px;
    }
    
    .staff-email {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.5);
    }
    
    .centre-stats {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .stat-item {
        display: flex;
        align-items: centre;
        gap: 15px;
        padding: 15px;
        background-color: var(--light-bg);
        border-radius: 10px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 10px;
        display: flex;
        align-items: centre;
        justify-content: centre;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .stat-info {
        display: flex;
        flex-direction: column;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--dark-color);
    }
    
    .stat-label {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.5);
    }
</style>
@endsection