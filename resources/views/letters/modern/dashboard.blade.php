@extends('layouts.app')

@section('title', 'Letter Generator')

@section('content')
<div class="modern-letter-generator">
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">
                    <i class="fas fa-file-alt"></i>
                    Letter Generator
                </h1>
                <p class="page-subtitle">Create, manage, and send professional letters with ease</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary btn-lg create-letter-btn" data-bs-toggle="modal" data-bs-target="#quickCreateModal">
                    <i class="fas fa-plus"></i>
                    Create New Letter
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['total_letters'] }}</div>
                <div class="stat-label">Total Letters</div>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['this_month'] }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
        
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-template"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['templates_available'] }}</div>
                <div class="stat-label">Templates Available</div>
            </div>
        </div>
        
        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['pending_letters'] }}</div>
                <div class="stat-label">Draft Letters</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Quick Actions Panel -->
        <div class="quick-actions-panel">
            <h3 class="panel-title">
                <i class="fas fa-lightning-bolt"></i>
                Quick Actions
            </h3>
            
            <div class="quick-actions">
                @foreach($templates as $template)
                <div class="quick-action-card" onclick="createFromTemplate({{ $template->id }})">
                    <div class="action-icon">
                        <i class="{{ $template->template_icon ?? 'fas fa-file-alt' }}"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">{{ $template->template_name }}</div>
                        <div class="action-subtitle">{{ $template->template_description }}</div>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="panel-footer">
                <a href="{{ route('letters.modern.create') }}" class="btn btn-outline-primary w-100">
                    <i class="fas fa-plus"></i>
                    Create Custom Letter
                </a>
            </div>
        </div>

        <!-- Recent Letters Panel -->
        <div class="recent-letters-panel">
            <div class="panel-header">
                <h3 class="panel-title">
                    <i class="fas fa-history"></i>
                    Recent Letters
                </h3>
                <div class="panel-actions">
                    <button class="btn btn-sm btn-outline-secondary" id="refreshRecentBtn">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="{{ route('letters.archive') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
            </div>
            
            <div class="recent-letters-list">
                @forelse($recentLetters as $letter)
                <div class="letter-item" data-letter-id="{{ $letter->id }}">
                    <div class="letter-icon">
                        <i class="fas fa-file-pdf text-danger"></i>
                    </div>
                    <div class="letter-content">
                        <div class="letter-title">{{ $letter->letter_subject }}</div>
                        <div class="letter-meta">
                            <span class="letter-ref">{{ $letter->letter_reference }}</span>
                            <span class="letter-date">{{ $letter->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="letter-recipient">
                            To: {{ json_decode($letter->letter_data)->recipient_name ?? 'Unknown' }}
                        </div>
                    </div>
                    <div class="letter-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('letters.modern.show', $letter->id) }}">
                                    <i class="fas fa-eye"></i> View
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('letters.modern.download', $letter->id) }}">
                                    <i class="fas fa-download"></i> Download PDF
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-warning" href="#" onclick="archiveLetter({{ $letter->id }})">
                                    <i class="fas fa-archive"></i> Archive
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>No Letters Yet</h4>
                    <p>Create your first letter to get started!</p>
                    <button class="btn btn-primary" onclick="$('#quickCreateModal').modal('show')">
                        <i class="fas fa-plus"></i>
                        Create Letter
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Search and Filter Panel -->
    <div class="search-panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i class="fas fa-search"></i>
                Search Letters
            </h3>
        </div>
        
        <div class="search-form">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="searchQuery">Search Term</label>
                        <input type="text" id="searchQuery" class="form-control" placeholder="Reference, subject, or content...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="searchCategory">Category</label>
                        <select id="searchCategory" class="form-control">
                            <option value="all">All Categories</option>
                            <option value="official">Official</option>
                            <option value="acknowledgment">Acknowledgment</option>
                            <option value="invitation">Invitation</option>
                            <option value="notification">Notification</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="dateFrom">Date From</label>
                        <input type="date" id="dateFrom" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="dateTo">Date To</label>
                        <input type="date" id="dateTo" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary w-100" onclick="performSearch()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="searchResults" class="search-results" style="display: none;">
            <!-- Search results will be populated here -->
        </div>
    </div>
</div>

<!-- Quick Create Modal -->
<div class="modal fade" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Letter Creation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="template-selection">
                    <h6>Choose a Template</h6>
                    <div class="template-grid">
                        @foreach($templates as $template)
                        <div class="template-card" onclick="selectTemplate({{ $template->id }})">
                            <div class="template-icon">
                                <i class="{{ $template->template_icon ?? 'fas fa-file-alt' }}"></i>
                            </div>
                            <div class="template-name">{{ $template->template_name }}</div>
                            <div class="template-description">{{ $template->template_description }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('letters.modern.create') }}" class="btn btn-outline-primary">
                    Create Custom Letter
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.modern-letter-generator {
    padding: 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2.5rem;
    margin: 0;
    font-weight: 700;
}

.page-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 10px 0 0 0;
}

.create-letter-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.create-letter-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    display: flex;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 24px;
    color: white;
}

.stat-primary .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-success .stat-icon { background: linear-gradient(135deg, #11998e, #38ef7d); }
.stat-info .stat-icon { background: linear-gradient(135deg, #3f51b5, #5a67d8); }
.stat-warning .stat-icon { background: linear-gradient(135deg, #f093fb, #f5576c); }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: #718096;
    margin-top: 5px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.quick-actions-panel,
.recent-letters-panel,
.search-panel {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.panel-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.panel-title i {
    margin-right: 10px;
    color: #667eea;
}

.quick-action-card {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-action-card:hover {
    border-color: #667eea;
    background: #f7fafc;
    transform: translateX(5px);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
}

.action-title {
    font-weight: 600;
    color: #2d3748;
}

.action-subtitle {
    font-size: 0.85rem;
    color: #718096;
}

.action-content {
    flex: 1;
}

.action-arrow {
    color: #cbd5e0;
    transition: color 0.3s ease;
}

.quick-action-card:hover .action-arrow {
    color: #667eea;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.panel-actions {
    display: flex;
    gap: 10px;
}

.letter-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.letter-item:hover {
    border-color: #667eea;
    background: #f7fafc;
}

.letter-icon {
    margin-right: 15px;
    font-size: 20px;
}

.letter-content {
    flex: 1;
}

.letter-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
}

.letter-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #718096;
    margin-bottom: 3px;
}

.letter-recipient {
    font-size: 0.85rem;
    color: #4a5568;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.search-panel {
    grid-column: 1 / -1;
}

.search-form {
    background: #f7fafc;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.template-card {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.template-card:hover {
    border-color: #667eea;
    background: #f7fafc;
}

.template-icon {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 10px;
}

.template-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.template-description {
    font-size: 0.85rem;
    color: #718096;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .header-actions {
        margin-top: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
function createFromTemplate(templateId) {
    window.location.href = `{{ route('letters.modern.create') }}?template_id=${templateId}`;
}

function selectTemplate(templateId) {
    $('#quickCreateModal').modal('hide');
    setTimeout(() => {
        createFromTemplate(templateId);
    }, 300);
}

function archiveLetter(letterId) {
    if (confirm('Are you sure you want to archive this letter?')) {
        fetch(`/letters/modern/${letterId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to archive letter: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while archiving the letter.');
        });
    }
}

function performSearch() {
    const query = document.getElementById('searchQuery').value;
    const category = document.getElementById('searchCategory').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const params = new URLSearchParams({
        q: query,
        category: category,
        date_from: dateFrom,
        date_to: dateTo
    });
    
    fetch(`{{ route('letters.modern.search') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.letters);
            } else {
                alert('Search failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during search.');
        });
}

function displaySearchResults(letters) {
    const resultsContainer = document.getElementById('searchResults');
    
    if (letters.length === 0) {
        resultsContainer.innerHTML = '<div class="empty-state"><p>No letters found matching your criteria.</p></div>';
    } else {
        let html = '<h6>Search Results (' + letters.length + ' found)</h6><div class="search-results-list">';
        
        letters.forEach(letter => {
            const letterData = JSON.parse(letter.letter_data);
            html += `
                <div class="letter-item">
                    <div class="letter-icon">
                        <i class="fas fa-file-pdf text-danger"></i>
                    </div>
                    <div class="letter-content">
                        <div class="letter-title">${letter.letter_subject}</div>
                        <div class="letter-meta">
                            <span class="letter-ref">${letter.letter_reference}</span>
                            <span class="letter-date">${new Date(letter.created_at).toLocaleDateString()}</span>
                        </div>
                        <div class="letter-recipient">To: ${letterData.recipient_name || 'Unknown'}</div>
                    </div>
                    <div class="letter-actions">
                        <a href="/letters/modern/${letter.id}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        resultsContainer.innerHTML = html;
    }
    
    resultsContainer.style.display = 'block';
}

// Auto-refresh recent letters
document.getElementById('refreshRecentBtn').addEventListener('click', function() {
    location.reload();
});
</script>
@endpush