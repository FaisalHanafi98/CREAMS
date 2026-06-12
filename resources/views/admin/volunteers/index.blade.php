@extends('layouts.app')

@section('title', 'Volunteer Applications')

@section('styles')
<style>
/* ── Volunteer Applications — page-specific styles ─────────────── */

.vol-status-filter { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }

.vol-status-btn {
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    border: 1.5px solid var(--color-border);
    background: var(--color-white);
    color: var(--color-muted);
    font-size: var(--font-size-sm);
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
    white-space: nowrap;
}
.vol-status-btn:hover { border-color: var(--brand-primary); color: var(--brand-primary); background: rgba(50,189,234,0.06); }
.vol-status-btn.active { background: var(--brand-gradient); border-color: transparent; color: white; box-shadow: 0 2px 8px rgba(50,189,234,0.3); }

.vol-stat-card { position: relative; }
.vol-stat-card .stat-accent {
    width: 4px; height: 100%;
    position: absolute; left: 0; top: 0;
    border-radius: var(--card-radius) 0 0 var(--card-radius);
}

/* Table name+email stacked cell */
.applicant-cell { display: flex; align-items: center; gap: 0.75rem; }
.applicant-info .applicant-name { font-weight: 600; color: var(--color-dark); font-size: var(--font-size-base); }
.applicant-info .applicant-email { font-size: var(--font-size-sm); color: var(--color-muted); }

/* Actions group */
.action-group { display: flex; gap: 0.4rem; }

/* Modal refresh */
.modal-content { border: none; border-radius: var(--card-radius); box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
.modal-header { border-bottom: 1px solid var(--color-border); padding: 1.25rem 1.5rem; }
.modal-header .modal-title { font-weight: 600; color: var(--color-dark); }
.modal-footer { border-top: 1px solid var(--color-border); padding: 1rem 1.5rem; }
.modal-body { padding: 1.5rem; }

/* Detail table inside modal */
.detail-table { width: 100%; font-size: var(--font-size-base); }
.detail-table th { color: var(--color-muted); font-weight: 500; width: 38%; padding: 0.4rem 0; vertical-align: top; }
.detail-table td { color: var(--color-dark); padding: 0.4rem 0.4rem 0.4rem 0; }

@media (max-width: 768px) {
    .vol-status-filter { gap: 0.35rem; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    /* Card-stack for mobile table */
    .creams-table thead { display: none; }
    .creams-table tbody tr {
        display: block; border: 1px solid var(--color-border);
        border-radius: var(--card-radius); margin-bottom: 0.75rem;
        padding: 1rem;
    }
    .creams-table tbody td {
        display: flex; justify-content: space-between;
        border: none; padding: 0.3rem 0;
    }
    .creams-table tbody td::before {
        content: attr(data-label);
        font-weight: 600; color: var(--color-muted); font-size: var(--font-size-sm);
        text-transform: uppercase; letter-spacing: 0.04em;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Flash messages --}}
    @include('components.flash-messages')

    {{-- Page header --}}
    <div class="page-header-banner d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title mb-1">
                <i class="fas fa-hands-helping me-2 opacity-75"></i>Volunteer Applications
            </h1>
            <p class="page-subtitle mb-0">Review and manage volunteer applications for PPDK centres</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light" onclick="window.location.reload()" title="Refresh">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="stats-grid mb-4">
        <div class="stat-card vol-stat-card ps-4">
            <div class="stat-accent" style="background:var(--color-warning);"></div>
            <div class="stat-icon" style="background:rgba(255,165,2,0.15); color:var(--color-warning);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending Review</div>
        </div>
        <div class="stat-card vol-stat-card ps-4">
            <div class="stat-accent" style="background:var(--color-success);"></div>
            <div class="stat-icon" style="background:rgba(46,213,115,0.15); color:var(--color-success);">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-value">{{ $stats['approved'] ?? 0 }}</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card vol-stat-card ps-4">
            <div class="stat-accent" style="background:var(--color-danger);"></div>
            <div class="stat-icon" style="background:rgba(255,71,87,0.15); color:var(--color-danger);">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-value">{{ $stats['rejected'] ?? 0 }}</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="stat-card vol-stat-card ps-4">
            <div class="stat-accent" style="background:var(--brand-primary);"></div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            {{-- Total comes from the controller (all statuses incl. active), not a partial sum --}}
            <div class="stat-value">{{ $stats['total'] ?? (($stats['pending'] ?? 0) + ($stats['approved'] ?? 0) + ($stats['rejected'] ?? 0) + ($stats['active'] ?? 0)) }}</div>
            <div class="stat-label">Total Applications</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar mb-4">
        <span class="filter-label"><i class="fas fa-filter me-1"></i>Filter</span>

        {{-- Status pill buttons --}}
        <div class="vol-status-filter">
            <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}"
               class="vol-status-btn {{ !request('status') ? 'active' : '' }}">All</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'applied']) }}"
               class="vol-status-btn {{ request('status') === 'applied' ? 'active' : '' }}">Pending</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}"
               class="vol-status-btn {{ request('status') === 'approved' ? 'active' : '' }}">Approved</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}"
               class="vol-status-btn {{ request('status') === 'rejected' ? 'active' : '' }}">Rejected</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}"
               class="vol-status-btn {{ request('status') === 'active' ? 'active' : '' }}">Active</a>
        </div>

        {{-- Centre dropdown (admin only) --}}
        @if(session('role') === 'admin')
        <select id="centreFilter" class="form-select form-select-sm" style="width: auto; min-width: 160px;"
                onchange="applyFilter()">
            <option value="">All Centres</option>
        </select>
        @endif

        {{-- Date range --}}
        <div class="d-flex align-items-center gap-2">
            <input type="date" id="dateFromFilter" class="form-control form-control-sm" style="width: 140px;"
                   value="{{ request('date_from') }}" placeholder="From">
            <span class="text-muted" style="font-size:var(--font-size-sm);">to</span>
            <input type="date" id="dateToFilter" class="form-control form-control-sm" style="width: 140px;"
                   value="{{ request('date_to') }}" placeholder="To">
            <button class="btn btn-sm brand-gradient text-white" onclick="applyFilter()" style="border:none; border-radius:8px;">
                Apply
            </button>
            @if(request()->hasAny(['status','centre_id','date_from','date_to']))
            <a href="{{ route('admin.volunteers.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                Clear
            </a>
            @endif
        </div>
    </div>

    {{-- Applications table --}}
    <div class="content-card">
        <div class="card-header-inner d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-alt me-2 text-muted"></i>Applications</span>
            @if($applications && $applications->total() > 0)
            <span class="text-muted" style="font-size:var(--font-size-sm);">
                Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }}
            </span>
            @endif
        </div>

        @if($applications && $applications->count() > 0)
        <div class="table-responsive">
            <table class="creams-table" id="applicationsTable">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Phone</th>
                        <th>Applied</th>
                        <th>Centre</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="applicationsTableBody">
                    @foreach($applications as $app)
                    <tr>
                        <td data-label="Applicant">
                            <div class="applicant-cell">
                                <div class="avatar-circle" aria-hidden="true">
                                    {{ strtoupper(substr($app->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="applicant-info">
                                    <div class="applicant-name">{{ $app->name }}</div>
                                    <div class="applicant-email">{{ $app->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Phone">{{ $app->phone ?? '—' }}</td>
                        <td data-label="Applied">{{ $app->created_at->format('j M Y') }}</td>
                        <td data-label="Centre">{{ $app->centre ? $app->centre->centre_name : '—' }}</td>
                        <td data-label="Status">
                            @if($app->status === 'applied')
                                <span class="status-pill pending">Pending</span>
                            @elseif($app->status === 'approved')
                                <span class="status-pill active">Approved</span>
                            @elseif($app->status === 'active')
                                <span class="status-pill active">Active</span>
                            @else
                                <span class="status-pill inactive">Rejected</span>
                            @endif
                        </td>
                        <td data-label="Reviewed By">
                            {{ $app->reviewedByUser ? $app->reviewedByUser->name : '—' }}
                        </td>
                        <td data-label="Actions">
                            <div class="action-group justify-content-end">
                                <button class="btn-icon btn-icon-view" onclick="viewApplication({{ $app->id }})"
                                        title="View details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($app->status === 'applied')
                                <button class="btn-icon btn-icon-approve" onclick="showApproveModal({{ $app->id }})"
                                        title="Approve application">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn-icon btn-icon-reject" onclick="showRejectModal({{ $app->id }})"
                                        title="Reject application">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($applications->hasPages())
        <div class="px-4 py-3 border-top" style="border-color:var(--color-border) !important;">
            {{ $applications->appends(request()->query())->links() }}
        </div>
        @endif

        @else
        {{-- Empty state --}}
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <p style="font-size:1.1rem; font-weight:600; color:var(--color-dark); margin-bottom:0.5rem;">
                No applications found
            </p>
            <p>
                @if(request()->hasAny(['status','centre_id','date_from','date_to']))
                    No applications match the current filters.
                    <a href="{{ route('admin.volunteers.index') }}">Clear filters</a>
                @else
                    When volunteers apply to join a PPDK centre, they will appear here for review.
                @endif
            </p>
        </div>
        @endif
    </div>

</div>

{{-- ── Application Detail Modal ─────────────────────────────────── --}}
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationModalLabel">
                    <i class="fas fa-user-circle me-2 text-muted"></i>Application Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="applicationModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <div id="modalActions" class="d-flex gap-2"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Approve Modal ────────────────────────────────────────────── --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2" style="color:var(--color-success);"></i>Approve Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    <input type="hidden" id="approveApplicationId" name="application_id">
                    @if(session('role') === 'admin' && !session('centre_id'))
                    <div class="mb-3">
                        <label for="approveCentreId" class="form-label fw-semibold">Assign to Centre <span class="text-danger">*</span></label>
                        <select class="form-select" id="approveCentreId" name="centre_id" required>
                            <option value="">Select Centre</option>
                        </select>
                    </div>
                    @elseif(session('centre_id'))
                    <div class="mb-3">
                        <div class="alert alert-info d-flex align-items-center gap-2 py-2">
                            <i class="fas fa-info-circle"></i>
                            <span>This volunteer will be assigned to your centre.</span>
                        </div>
                        <input type="hidden" id="approveCentreId" name="centre_id" value="{{ session('centre_id') }}">
                    </div>
                    @endif
                    <div class="mb-3">
                        <label for="approveNotes" class="form-label fw-semibold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control" id="approveNotes" name="notes" rows="3"
                                placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" onclick="submitApproval()">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Reject Modal ─────────────────────────────────────────────── --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2" style="color:var(--color-danger);"></i>Reject Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="rejectApplicationId" name="application_id">
                    <div class="alert alert-warning d-flex align-items-start gap-2 py-2 mb-3">
                        <i class="fas fa-exclamation-triangle mt-1" style="flex-shrink:0;"></i>
                        <span>The applicant will receive an email notification about this decision.</span>
                    </div>
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label fw-semibold">Reason for rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectNotes" name="notes" rows="4" required
                                placeholder="Provide a clear reason — this will be included in the notification email."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="submitRejection()">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── Helpers ────────────────────────────────────────────────────────
function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function statusPill(status) {
    const map = {
        applied:  ['pending', 'Pending'],
        approved: ['active', 'Approved'],
        active:   ['active', 'Active'],
        rejected: ['inactive', 'Rejected'],
    };
    const [cls, label] = map[status] ?? ['pending', 'Unknown'];
    return `<span class="status-pill ${cls}">${label}</span>`;
}

function bsModal(id) {
    return bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
}

// ── Filter ────────────────────────────────────────────────────────
function applyFilter() {
    const params = new URLSearchParams(window.location.search);
    const centreId  = document.getElementById('centreFilter')?.value ?? '';
    const dateFrom  = document.getElementById('dateFromFilter')?.value ?? '';
    const dateTo    = document.getElementById('dateToFilter')?.value ?? '';
    if (centreId)  params.set('centre_id', centreId); else params.delete('centre_id');
    if (dateFrom)  params.set('date_from', dateFrom); else params.delete('date_from');
    if (dateTo)    params.set('date_to', dateTo);     else params.delete('date_to');
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

// ── Load centres ──────────────────────────────────────────────────
function loadCentres() {
    $.ajax({
        url: '{{ route("volunteer.centres") }}',
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(res) {
            if (!res.success || !res.data) return;
            const cf = $('#centreFilter');
            const af = $('#approveCentreId');
            res.data.forEach(function(c) {
                const opt = `<option value="${escapeHtml(c.centre_id)}">${escapeHtml(c.centre_name)}</option>`;
                cf.append(opt);
                af.append(opt);
            });
            // Restore selected filter value
            const current = new URLSearchParams(window.location.search).get('centre_id');
            if (current) cf.val(current);
        }
    });
}

// ── View application ──────────────────────────────────────────────
window.viewApplication = function(id) {
    bsModal('applicationModal').show();
    $('#applicationModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div></div>');
    $('#modalActions').html('');

    $.ajax({
        url: `/volunteer/applications/${id}`,
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 10000,
        success: function(res) {
            if (res && res.success) {
                displayApplicationDetails(res.data);
            } else {
                $('#applicationModalBody').html('<div class="alert alert-danger">Invalid response.</div>');
            }
        },
        error: function(xhr) {
            const msgs = { 401: 'Authentication required.', 403: 'Access denied.', 404: 'Application not found.' };
            const msg = msgs[xhr.status] ?? 'Server error. Please try again.';
            $('#applicationModalBody').html(`<div class="alert alert-danger">${escapeHtml(msg)}</div>`);
        }
    });
};

function displayApplicationDetails(app) {
    const fmt = d => d ? new Date(d).toLocaleDateString('en-MY', { year:'numeric', month:'long', day:'numeric' }) : '—';
    const centre   = app.centre ? escapeHtml(app.centre.centre_name) : 'Unassigned';
    const reviewed = app.reviewed_by_user ? escapeHtml(app.reviewed_by_user.name) : '—';

    const html = `
        <div class="row g-4">
            <div class="col-md-6">
                <p class="text-muted fw-semibold mb-2" style="font-size:var(--font-size-sm);text-transform:uppercase;letter-spacing:.05em;">Personal</p>
                <table class="detail-table">
                    <tr><th>Name</th><td>${escapeHtml(app.name ?? '—')}</td></tr>
                    <tr><th>Email</th><td><a href="mailto:${escapeHtml(app.email ?? '')}">${escapeHtml(app.email ?? '—')}</a></td></tr>
                    <tr><th>Phone</th><td>${escapeHtml(app.phone ?? '—')}</td></tr>
                    <tr><th>Gender</th><td>${escapeHtml(app.gender ?? '—')}</td></tr>
                    <tr><th>Address</th><td>${escapeHtml(app.address ?? '—')}</td></tr>
                    <tr><th>Occupation</th><td>${escapeHtml(app.occupation ?? '—')}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <p class="text-muted fw-semibold mb-2" style="font-size:var(--font-size-sm);text-transform:uppercase;letter-spacing:.05em;">Application</p>
                <table class="detail-table">
                    <tr><th>ID</th><td>#VA${String(app.id).padStart(6,'0')}</td></tr>
                    <tr><th>Status</th><td>${statusPill(app.status)}</td></tr>
                    <tr><th>Centre</th><td>${centre}</td></tr>
                    <tr><th>Applied</th><td>${fmt(app.created_at)}</td></tr>
                    <tr><th>Reviewed by</th><td>${reviewed}</td></tr>
                    <tr><th>Review date</th><td>${fmt(app.reviewed_at)}</td></tr>
                </table>
            </div>
        </div>
        ${app.skills || app.availability || app.motivation ? `
        <hr style="border-color:var(--color-border);">
        <p class="text-muted fw-semibold mb-2" style="font-size:var(--font-size-sm);text-transform:uppercase;letter-spacing:.05em;">Volunteer Profile</p>
        ${app.skills ? `<div class="mb-2"><strong>Skills:</strong><div class="p-2 rounded mt-1" style="background:var(--color-light);">${escapeHtml(app.skills)}</div></div>` : ''}
        ${app.availability ? `<div class="mb-2"><strong>Availability:</strong><div class="p-2 rounded mt-1" style="background:var(--color-light);">${escapeHtml(app.availability)}</div></div>` : ''}
        ${app.motivation ? `<div class="mb-2"><strong>Motivation:</strong><div class="p-2 rounded mt-1" style="background:var(--color-light);">${escapeHtml(app.motivation)}</div></div>` : ''}
        ` : ''}
        ${app.review_notes ? `<hr style="border-color:var(--color-border);"><div class="alert alert-info py-2">${escapeHtml(app.review_notes)}</div>` : ''}
    `;

    $('#applicationModalBody').html(html);

    if (app.status === 'applied') {
        $('#modalActions').html(`
            <button class="btn btn-success btn-sm" onclick="showApproveModal(${app.id})" data-bs-dismiss="modal">
                <i class="fas fa-check me-1"></i>Approve
            </button>
            <button class="btn btn-danger btn-sm" onclick="showRejectModal(${app.id})" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i>Reject
            </button>
        `);
    }
}

// ── Approve / Reject modals ───────────────────────────────────────
window.showApproveModal = function(id) {
    document.getElementById('approveApplicationId').value = id;
    bsModal('approveModal').show();
};

window.showRejectModal = function(id) {
    document.getElementById('rejectApplicationId').value = id;
    document.getElementById('rejectNotes').value = '';
    bsModal('rejectModal').show();
};

// ── Submit approval ───────────────────────────────────────────────
function submitApproval() {
    const id = document.getElementById('approveApplicationId').value;
    const centreEl = document.getElementById('approveCentreId');
    const formData = {
        centre_id: centreEl ? centreEl.value : '',
        notes: document.getElementById('approveNotes').value
    };

    $.ajax({
        url: `/volunteer/applications/${id}/approve`,
        method: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(res) {
            if (res.success) {
                bsModal('approveModal').hide();
                window.location.reload();
            } else {
                alert(res.message || 'Error approving application.');
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Error approving application.');
        }
    });
}

// ── Submit rejection ──────────────────────────────────────────────
function submitRejection() {
    const notes = document.getElementById('rejectNotes').value.trim();
    if (!notes) { alert('Please provide a reason for rejection.'); return; }

    const id = document.getElementById('rejectApplicationId').value;

    $.ajax({
        url: `/volunteer/applications/${id}/reject`,
        method: 'POST',
        data: { notes },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(res) {
            if (res.success) {
                bsModal('rejectModal').hide();
                window.location.reload();
            } else {
                alert(res.message || 'Error rejecting application.');
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Error rejecting application.');
        }
    });
}

// ── Init ──────────────────────────────────────────────────────────
$(document).ready(function() {
    loadCentres();
});
</script>
@endsection
