@extends('layouts.app')

@section('title', 'Dashboard Error')

@push('styles')
<style>
    .error-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .error-container {
        max-width: 600px;
        width: 100%;
        text-align: center;
    }
    
    .error-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-left: 5px solid #f56565;
    }
    
    .error-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #f56565, #fc8181);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: white;
        margin: 0 auto 25px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .error-title {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
    }
    
    .error-message {
        font-size: 16px;
        color: #718096;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .error-details {
        background: #fed7d7;
        border: 1px solid #f56565;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 30px;
        text-align: left;
    }
    
    .error-details-title {
        font-weight: 600;
        color: #c53030;
        margin-bottom: 8px;
    }
    
    .error-details-text {
        font-size: 14px;
        color: #742a2a;
        font-family: monospace;
        word-break: break-all;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary {
        background: #edf2f7;
        border: 2px solid #e2e8f0;
        padding: 10px 23px;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: #e2e8f0;
        border-color: #cbd5e0;
        color: #2d3748;
        text-decoration: none;
    }
    
    .stats-fallback {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 30px;
    }
    
    .stat-item {
        background: #f7fafc;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #a0aec0;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    @media (max-width: 768px) {
        .error-card {
            padding: 30px 20px;
        }
        
        .error-title {
            font-size: 24px;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-primary,
        .btn-secondary {
            width: 100%;
            max-width: 200px;
        }
    }
</style>
@endpush

@section('content')
<div class="error-dashboard">
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h1 class="error-title">{{ $message ?? 'Dashboard Temporarily Unavailable' }}</h1>
            
            <p class="error-message">
                We're experiencing technical difficulties loading your dashboard. 
                This is usually temporary and should resolve shortly.
            </p>
            
            @if(isset($details) && $details && app()->environment('local'))
                <div class="error-details">
                    <div class="error-details-title">Technical Details (Development Mode):</div>
                    <div class="error-details-text">{{ $details }}</div>
                </div>
            @endif
            
            <!-- Fallback Statistics -->
            @if(isset($stats) && !empty($stats))
                <div class="stats-fallback">
                    @foreach($stats as $key => $value)
                        <div class="stat-item">
                            <div class="stat-value">{{ $value }}</div>
                            <div class="stat-label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- System Alerts -->
            @if(isset($alerts) && !empty($alerts))
                <div class="mt-4">
                    @foreach($alerts as $alert)
                        <div class="alert alert-{{ $alert['type'] ?? 'info' }} mb-2">
                            {{ $alert['message'] ?? 'System notification' }}
                        </div>
                    @endforeach
                </div>
            @endif
            
            <div class="action-buttons">
                <button onclick="location.reload()" class="btn-primary">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh Dashboard
                </button>
                
                <a href="{{ url('/') }}" class="btn-secondary">
                    <i class="fas fa-home mr-2"></i>
                    Return Home
                </a>
                
                @if(session('role') && session('role') !== 'guest')
                    <a href="{{ route('profile') }}" class="btn-secondary">
                        <i class="fas fa-user mr-2"></i>
                        My Profile
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh attempt after 30 seconds
    setTimeout(function() {
        if (confirm('Would you like to try refreshing the dashboard automatically?')) {
            location.reload();
        }
    }, 30000);
    
    // Log error for debugging (if in development)
    @if(app()->environment('local'))
        console.error('Dashboard Error:', {
            message: '{{ $message ?? "Unknown error" }}',
            details: '{{ $details ?? "" }}',
            timestamp: new Date().toISOString()
        });
    @endif
});
</script>
@endpush