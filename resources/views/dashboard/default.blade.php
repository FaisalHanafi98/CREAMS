@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .default-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 30px 0;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    
    .welcome-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .welcome-subtitle {
        font-size: 18px;
        opacity: 0.9;
        margin-bottom: 30px;
    }
    
    .role-badge {
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        backdrop-filter: blur(10px);
    }
    
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        margin-bottom: 15px;
    }
    
    .stat-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
    }
    
    .stat-description {
        font-size: 14px;
        color: #718096;
        line-height: 1.5;
    }
    
    .navigation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .nav-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        text-decoration: none;
        color: #4a5568;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        border: 2px solid transparent;
    }
    
    .nav-card:hover {
        color: #667eea;
        text-decoration: none;
        transform: translateY(-5px);
        border-color: #667eea;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    }
    
    .nav-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #667eea;
        margin: 0 auto 15px;
        transition: all 0.3s ease;
    }
    
    .nav-card:hover .nav-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    
    .nav-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .nav-description {
        font-size: 13px;
        color: #718096;
    }
    
    .system-status {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        margin-top: 30px;
    }
    
    .status-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        background: #48bb78;
        border-radius: 50%;
        margin-right: 12px;
    }
    
    .status-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .status-message {
        color: #718096;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .quick-stats {
            grid-template-columns: 1fr;
        }
        
        .navigation-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .welcome-card {
            padding: 30px 20px;
        }
        
        .welcome-title {
            font-size: 24px;
        }
    }
</style>
@endpush

@section('content')
<div class="default-dashboard">
    <div class="container-fluid">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome to CREAMS</h1>
            <p class="welcome-subtitle">Community-based REhAbilitation Management System</p>
            <div class="role-badge">
                {{ ucfirst(session('role', 'User')) }} • {{ session('name', 'Guest') }}
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="stat-title">Performance Optimized</div>
                <div class="stat-description">
                    Enhanced dashboard with caching and real-time updates for better performance.
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="stat-title">Mobile Responsive</div>
                <div class="stat-description">
                    Fully responsive design that works seamlessly across all devices and screen sizes.
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="stat-title">Real-time Updates</div>
                <div class="stat-description">
                    Live data updates and notifications to keep you informed of important changes.
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-title">Secure & Reliable</div>
                <div class="stat-description">
                    Role-based access control with comprehensive security and data protection.
                </div>
            </div>
        </div>

        <!-- Navigation Grid -->
        <div class="navigation-grid">
            @if(in_array(session('role'), ['admin', 'supervisor']))
                <a href="{{ route('users.index') }}" class="nav-card">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="nav-title">User Management</div>
                    <div class="nav-description">Manage users, roles, and permissions</div>
                </a>
            @endif
            
            <a href="{{ route('traineeshome') }}" class="nav-card">
                <div class="nav-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="nav-title">Trainees</div>
                <div class="nav-description">View and manage trainee information</div>
            </a>
            
            <a href="{{ route('activities.index') }}" class="nav-card">
                <div class="nav-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="nav-title">Activities</div>
                <div class="nav-description">Schedule and track rehabilitation activities</div>
            </a>
            
            @if(in_array(session('role'), ['admin', 'supervisor', 'ajk']))
                <a href="{{ route('assets.index') }}" class="nav-card">
                    <div class="nav-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="nav-title">Assets</div>
                    <div class="nav-description">Manage equipment and resources</div>
                </a>
            @endif
            
            @if(in_array(session('role'), ['admin']))
                <a href="{{ route('letters.index') }}" class="nav-card">
                    <div class="nav-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="nav-title">Letters</div>
                    <div class="nav-description">Generate and manage official letters</div>
                </a>
            @endif
            
            <a href="{{ route('profile') }}" class="nav-card">
                <div class="nav-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="nav-title">Profile</div>
                <div class="nav-description">Update your personal information</div>
            </a>
        </div>

        <!-- System Status -->
        <div class="system-status">
            <div class="status-header">
                <div class="status-indicator"></div>
                <h3 class="status-title">System Status</h3>
            </div>
            <p class="status-message">
                All systems are operating normally. Dashboard loaded successfully with optimized performance.
                @if(isset($performance))
                    Load time: {{ $performance['load_time'] ?? '0' }}ms
                @endif
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Simple page load analytics
document.addEventListener('DOMContentLoaded', function() {
    console.log('Default dashboard loaded successfully');
    
    // Add subtle animations to cards
    const cards = document.querySelectorAll('.stat-card, .nav-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush