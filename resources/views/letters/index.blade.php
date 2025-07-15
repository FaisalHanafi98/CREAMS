@extends('layouts.app')

@section('title', 'Letters Archive - CREAMS')

@section('styles')
<style>
    .letters-archive {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .archive-header {
        background: linear-gradient(135deg, #007bff, #6c757d);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 10px;
    }
    
    .archive-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 300;
    }
    
    .archive-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }
    
    .letters-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: none;
        margin-bottom: 20px;
    }
    
    .letters-card .card-header {
        background: linear-gradient(135deg, var(--primary-color, #007bff), var(--secondary-color, #6c757d));
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 20px 25px;
        border: none;
    }
    
    .letters-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 18px;
    }
    
    .letters-table {
        margin: 0;
    }
    
    .letters-table thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 15px;
        vertical-align: middle;
    }
    
    .letters-table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
    }
    
    .letters-table tbody tr {
        transition: all 0.3s ease;
    }
    
    .letters-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .reference-code {
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        font-weight: 600;
    }
    
    .btn-group .btn {
        border-radius: 0;
        margin: 0 1px;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-available {
        background: #d4edda;
        color: #155724;
    }
    
    .status-missing {
        background: #f8d7da;
        color: #721c24;
    }
    
    .stats-row {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .stat-card {
        text-align: center;
        padding: 15px;
    }
    
    .stat-number {
        font-size: 2em;
        font-weight: bold;
        color: #007bff;
        display: block;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9em;
        margin-top: 5px;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: rgba(255,255,255,0.7);
    }
    
    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .breadcrumb-item.active {
        color: white;
    }
    
    .search-box {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .pagination {
        justify-content: center;
        margin-top: 30px;
    }
    
    .pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: #007bff;
    }
    
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #007bff, #6c757d);
        border-color: #007bff;
    }
</style>
@endsection

@section('content')
<div class="letters-archive">
    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-archive mr-3"></i>Letters Archive</h1>
                    <p>Manage and access all generated letters</p>
                </div>
                <div class="col-md-4 text-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('profile') }}">Profile</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Letters</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Row -->
        <div class="stats-row">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <span class="stat-number">{{ $letters->total() }}</span>
                        <div class="stat-label">Total Letters</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <span class="stat-number">{{ $letters->where('pdf_path', '!=', null)->count() }}</span>
                        <div class="stat-label">With PDF</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <span class="stat-number">{{ $letters->where('created_at', '>=', now()->startOfMonth())->count() }}</span>
                        <div class="stat-label">This Month</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <span class="stat-number">{{ $letters->where('created_at', '>=', now()->startOfWeek())->count() }}</span>
                        <div class="stat-label">This Week</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-box">
            <form method="GET" action="{{ route('letters.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Search Letters</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Reference, subject, or recipient..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Letters Table -->
        <div class="letters-card">
            <div class="card-header">
                <h5><i class="fas fa-envelope mr-2"></i>Generated Letters</h5>
            </div>
            <div class="card-body p-0">
                @if($letters->count() > 0)
                    <div class="table-responsive">
                        <table class="table letters-table mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag mr-1"></i>Reference</th>
                                    <th><i class="fas fa-calendar mr-1"></i>Date</th>
                                    <th><i class="fas fa-user mr-1"></i>Recipient</th>
                                    <th><i class="fas fa-tag mr-1"></i>Subject</th>
                                    <th><i class="fas fa-user-shield mr-1"></i>Generated By</th>
                                    <th><i class="fas fa-file-pdf mr-1"></i>Status</th>
                                    <th><i class="fas fa-cog mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($letters as $letter)
                                    <tr>
                                        <td>
                                            <span class="reference-code">{{ $letter->reference_number }}</span>
                                        </td>
                                        <td>
                                            {{ $letter->letter_date ? $letter->letter_date->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            <strong>{{ $letter->recipient_name }}</strong>
                                            @if($letter->recipient_address)
                                                <br><small class="text-muted">{{ \Str::limit($letter->recipient_address, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ \Str::limit($letter->subject, 50) }}</td>
                                        <td>
                                            <small>{{ $letter->generated_by_name ?? 'Unknown' }}</small>
                                            <br><span class="text-muted" style="font-size: 0.8em;">{{ $letter->created_at->format('d M Y H:i') }}</span>
                                        </td>
                                        <td>
                                            @if($letter->pdf_path)
                                                <span class="status-badge status-available">
                                                    <i class="fas fa-check-circle mr-1"></i>PDF Ready
                                                </span>
                                            @else
                                                <span class="status-badge status-missing">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>No PDF
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($letter->pdf_path)
                                                    <a href="{{ route('letters.view', $letter->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="View PDF" 
                                                       target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('letters.download', $letter->id) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Download PDF">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @else
                                                    <span class="btn btn-sm btn-secondary disabled" title="PDF not available">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </span>
                                                    <span class="btn btn-sm btn-secondary disabled" title="PDF not available">
                                                        <i class="fas fa-download"></i>
                                                    </span>
                                                @endif
                                                
                                                @if(session('role') === 'admin' || $letter->generated_by === session('id'))
                                                    <form method="POST" action="{{ route('letters.destroy', $letter->id) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this letter? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Letter">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Letters Found</h5>
                        <p class="text-muted">
                            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                                No letters match your search criteria.
                                <br><a href="{{ route('letters.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-times mr-1"></i>Clear Filters
                                </a>
                            @else
                                No letters have been generated yet.
                                <br><a href="{{ route('profile') }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-plus mr-1"></i>Generate Your First Letter
                                </a>
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pagination -->
        @if($letters->hasPages())
            <div class="d-flex justify-content-center">
                {{ $letters->appends(request()->query())->links() }}
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="text-center mt-4">
            <a href="{{ route('profile') }}" class="btn btn-lg btn-primary">
                <i class="fas fa-plus mr-2"></i>Generate New Letter
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-lg btn-outline-secondary ml-2">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);
    
    // Enhanced delete confirmation
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const reference = this.closest('tr').querySelector('.reference-code').textContent;
            
            if (confirm(`Are you sure you want to delete letter ${reference}?\n\nThis will permanently remove:\n- The letter record\n- The PDF file\n- All associated data\n\nThis action cannot be undone.`)) {
                this.submit();
            }
        });
    });
    
    // Search form auto-submit on date change
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Auto-submit after a short delay to allow for both date fields to be set
            setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
    });
});
</script>
@endsection