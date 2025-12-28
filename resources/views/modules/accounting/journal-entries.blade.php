@extends('layouts.app')

@section('title', 'Journal Entries')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Journal Entries</h4>
</div>
@endsection

@push('styles')
<style>
    .journal-line {
        border-bottom: 1px solid #dee2e6;
        padding: 10px 0;
    }
    .balance-indicator {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 5px;
    }
    .balance-balanced {
        background-color: #d4edda;
        color: #155724;
    }
    .balance-unbalanced {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Fix modal stacking and responsiveness */
    .modal {
        z-index: 1050 !important;
    }
    
    .modal.show {
        z-index: 1050 !important;
        display: block !important;
        pointer-events: auto !important;
    }
    
    .modal-backdrop {
        z-index: 1040 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
    
    .modal-backdrop.show {
        z-index: 1040 !important;
        opacity: 0.5 !important;
    }
    
    .modal-content {
        pointer-events: auto !important;
        position: relative;
        z-index: 1 !important;
    }
    
    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }
    
    .modal-dialog {
        pointer-events: auto !important;
    }
    
    .modal-body {
        pointer-events: auto !important;
    }
    
    .modal-footer {
        pointer-events: auto !important;
    }
    
    /* Ensure modal body scrolls when content is large */
    #journalModal .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Smooth scrolling */
    #journalModal .modal-body {
        scroll-behavior: smooth;
    }
    
    /* Custom scrollbar styling for better UX */
    #journalModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    #journalModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #journalModal .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    #journalModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

@section('content')
<!-- Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Posted" {{ request('status') == 'Posted' ? 'selected' : '' }}>Posted</option>
                            <option value="Reversed" {{ request('status') == 'Reversed' ? 'selected' : '' }}>Reversed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Journal Entries List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Journal Entries</h5>
                <div>
                    <button class="btn btn-danger btn-sm me-2" onclick="exportJournalPdf()">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </button>
                    <button class="btn btn-primary" onclick="openJournalModal()">
                        <i class="fas fa-plus"></i> New Entry
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Entry No</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Reference</th>
                                <th>Source</th>
                                <th class="text-end">Total Debits</th>
                                <th class="text-end">Total Credits</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                            <tr>
                                <td><strong>{{ $entry->entry_no }}</strong></td>
                                <td>{{ $entry->entry_date->format('d M Y') }}</td>
                                <td>{{ Str::limit($entry->description, 40) }}</td>
                                <td>{{ $entry->reference_no ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $entry->source }}</span></td>
                                <td class="text-end">TZS {{ number_format($entry->total_debits, 2) }}</td>
                                <td class="text-end">TZS {{ number_format($entry->total_credits, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $entry->status === 'Posted' ? 'success' : ($entry->status === 'Reversed' ? 'danger' : 'warning') }}">
                                        {{ $entry->status }}
                                    </span>
                                </td>
                                <td>{{ $entry->creator->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="viewEntry({{ $entry->id }})" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($entry->status === 'Draft')
                                    <button class="btn btn-sm btn-warning" onclick="editEntry({{ $entry->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="postEntry({{ $entry->id }})" title="Post">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No journal entries found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Journal Entry Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="journalModalTitle">New Journal Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="journalForm">
                <div class="modal-body">
                    <input type="hidden" id="journalId" name="id">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="journalDate" name="entry_date" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference No</label>
                            <input type="text" class="form-control" id="journalReference" name="reference_no">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Source</label>
                            <select class="form-select" id="journalSource" name="source">
                                <option value="Manual">Manual</option>
                                <option value="Sales">Sales</option>
                                <option value="Purchase">Purchase</option>
                                <option value="Payroll">Payroll</option>
                                <option value="Petty Cash">Petty Cash</option>
                                <option value="Imprest">Imprest</option>
                                <option value="Bank">Bank</option>
                                <option value="Asset">Asset</option>
                                <option value="Adjustment">Adjustment</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Source Ref</label>
                            <input type="text" class="form-control" id="journalSourceRef" name="source_ref">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="journalDescription" name="description" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="journalNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Entry Lines</h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addJournalLine()">
                            <i class="fas fa-plus"></i> Add Line
                        </button>
                    </div>

                    <div id="journalLines">
                        <!-- Lines will be added here -->
                    </div>

                    <div class="mt-3 p-3 bg-light rounded" id="journalBalance">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Debits:</strong> <span id="totalDebits" class="text-primary">TZS 0.00</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Credits:</strong> <span id="totalCredits" class="text-primary">TZS 0.00</span>
                            </div>
                            <div class="col-12 mt-2">
                                <strong>Difference:</strong> 
                                <span id="balanceDifference" class="balance-indicator">TZS 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveJournal">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="button" class="btn btn-success" id="btnPostJournal" onclick="saveAndPostJournal()" disabled>
                        <i class="fas fa-check"></i> Save & Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Journal Entry Modal -->
<div class="modal fade" id="viewJournalModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Journal Entry Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewJournalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const token = '{{ csrf_token() }}';
let journalLineCount = 0;
const accounts = @json($accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name]));

function openJournalModal(id = null) {
    // Clean up any existing modals
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('journalModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const form = document.getElementById('journalForm');
        const title = document.getElementById('journalModalTitle');
        
        form.reset();
        document.getElementById('journalId').value = '';
        document.getElementById('journalLines').innerHTML = '';
        journalLineCount = 0;
        document.getElementById('btnPostJournal').disabled = true;
        
        addJournalLine();
        addJournalLine();
        
        if (id) {
            title.textContent = 'Edit Journal Entry';
            loadJournalData(id);
        } else {
            title.textContent = 'New Journal Entry';
        }
        
        modal.show();
        
        // Ensure modal is responsive after show
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    }, 100);
}

function addJournalLine() {
    journalLineCount++;
    const lineHtml = `
        <div class="journal-line row g-2" data-line-id="${journalLineCount}">
            <div class="col-md-4">
                <label class="form-label small">Account <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm journal-account" required>
                    <option value="">Select Account</option>
                    ${accounts.map(acc => `<option value="${acc.id}">${acc.code} - ${acc.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm journal-type" required>
                    <option value="">Select</option>
                    <option value="Debit">Debit</option>
                    <option value="Credit">Credit</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control form-control-sm journal-amount" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm journal-desc">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeJournalLine(${journalLineCount})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('journalLines').insertAdjacentHTML('beforeend', lineHtml);
    updateJournalBalance();
    
    // Add event listeners for balance calculation
    document.querySelectorAll('.journal-amount, .journal-type').forEach(el => {
        el.addEventListener('change', updateJournalBalance);
    });
}

function removeJournalLine(lineId) {
    document.querySelector(`[data-line-id="${lineId}"]`).remove();
    updateJournalBalance();
}

function updateJournalBalance() {
    let totalDebits = 0;
    let totalCredits = 0;
    
    document.querySelectorAll('.journal-line').forEach(line => {
        const type = line.querySelector('.journal-type').value;
        const amount = parseFloat(line.querySelector('.journal-amount').value) || 0;
        
        if (type === 'Debit') {
            totalDebits += amount;
        } else if (type === 'Credit') {
            totalCredits += amount;
        }
    });
    
    document.getElementById('totalDebits').textContent = `TZS ${totalDebits.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById('totalCredits').textContent = `TZS ${totalCredits.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    
    const difference = Math.abs(totalDebits - totalCredits);
    const diffEl = document.getElementById('balanceDifference');
    diffEl.textContent = `TZS ${difference.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    
    if (difference < 0.01) {
        diffEl.className = 'balance-indicator balance-balanced';
        document.getElementById('btnPostJournal').disabled = false;
    } else {
        diffEl.className = 'balance-indicator balance-unbalanced';
        document.getElementById('btnPostJournal').disabled = true;
    }
}

async function viewEntry(id) {
    // Clean up any existing modals
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('viewJournalModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const content = document.getElementById('viewJournalContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
        
        modal.show();
        
        // Ensure modal is responsive after show
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    }, 100);
    
    try {
        const response = await fetch(`/modules/accounting/journal-entries/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const entry = data.entry;
            content.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Entry No:</th><td>${entry.entry_no}</td></tr>
                            <tr><th>Date:</th><td>${new Date(entry.entry_date).toLocaleDateString()}</td></tr>
                            <tr><th>Reference:</th><td>${entry.reference_no || '-'}</td></tr>
                            <tr><th>Source:</th><td><span class="badge bg-secondary">${entry.source}</span></td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-${entry.status === 'Posted' ? 'success' : 'warning'}">${entry.status}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Created By:</th><td>${entry.creator?.name || 'N/A'}</td></tr>
                            <tr><th>Posted By:</th><td>${entry.poster?.name || '-'}</td></tr>
                            <tr><th>Posted At:</th><td>${entry.posted_at ? new Date(entry.posted_at).toLocaleString() : '-'}</td></tr>
                            <tr><th>Total Debits:</th><td class="text-end">TZS ${parseFloat(entry.total_debits).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Total Credits:</th><td class="text-end">TZS ${parseFloat(entry.total_credits).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                        </table>
                    </div>
                    <div class="col-12">
                        <strong>Description:</strong>
                        <p>${entry.description}</p>
                        ${entry.notes ? `<strong>Notes:</strong><p>${entry.notes}</p>` : ''}
                    </div>
                </div>
                <h6>Entry Lines</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${entry.lines.map(line => `
                                <tr>
                                    <td>${line.account?.code || ''} - ${line.account?.name || ''}</td>
                                    <td><span class="badge bg-${line.type === 'Debit' ? 'primary' : 'success'}">${line.type}</span></td>
                                    <td class="text-end">TZS ${parseFloat(line.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td>${line.description || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
    } catch (error) {
        content.innerHTML = '<div class="alert alert-danger">Error loading journal entry</div>';
    }
}

function editEntry(id) {
    openJournalModal(id);
}

async function postEntry(id) {
    if (!confirm('Are you sure you want to post this journal entry? This action cannot be reversed.')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('modules.accounting.journal-entries.post', ':id') }}`.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Journal entry posted successfully');
            location.reload();
        } else {
            alert(data.message || 'Error posting entry');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function loadJournalData(id) {
    try {
        const response = await fetch(`/modules/accounting/journal-entries/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const entry = data.entry;
            document.getElementById('journalId').value = entry.id;
            document.getElementById('journalDate').value = entry.entry_date;
            document.getElementById('journalReference').value = entry.reference_no || '';
            document.getElementById('journalSource').value = entry.source;
            document.getElementById('journalSourceRef').value = entry.source_ref || '';
            document.getElementById('journalDescription').value = entry.description;
            document.getElementById('journalNotes').value = entry.notes || '';
            
            // Clear existing lines
            document.getElementById('journalLines').innerHTML = '';
            journalLineCount = 0;
            
            // Load lines
            entry.lines.forEach(line => {
                addJournalLine();
                const lastLine = document.querySelectorAll('.journal-line').lastElementChild;
                lastLine.querySelector('.journal-account').value = line.account_id;
                lastLine.querySelector('.journal-type').value = line.type;
                lastLine.querySelector('.journal-amount').value = line.amount;
                lastLine.querySelector('.journal-desc').value = line.description || '';
            });
            
            updateJournalBalance();
        }
    } catch (error) {
        console.error('Error loading journal:', error);
        alert('Error loading journal entry data');
    }
}

async function saveAndPostJournal() {
    if (await saveJournalEntry(true)) {
        // Entry will be posted after save
    }
}

document.getElementById('journalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    await saveJournalEntry(false);
});

async function saveJournalEntry(autoPost = false) {
    const lines = [];
    let isValid = true;
    
    document.querySelectorAll('.journal-line').forEach(line => {
        const accountId = line.querySelector('.journal-account').value;
        const type = line.querySelector('.journal-type').value;
        const amount = line.querySelector('.journal-amount').value;
        const desc = line.querySelector('.journal-desc').value;
        
        if (!accountId || !type || !amount) {
            isValid = false;
            return;
        }
        
        lines.push({
            account_id: accountId,
            type: type,
            amount: parseFloat(amount),
            description: desc
        });
    });
    
    if (!isValid || lines.length < 2) {
        alert('Please fill in all required fields for all lines. Minimum 2 lines required.');
        return false;
    }
    
    // Check balance
    const totalDebits = lines.filter(l => l.type === 'Debit').reduce((sum, l) => sum + l.amount, 0);
    const totalCredits = lines.filter(l => l.type === 'Credit').reduce((sum, l) => sum + l.amount, 0);
    
    if (Math.abs(totalDebits - totalCredits) > 0.01) {
        alert('Journal entry is not balanced. Total debits must equal total credits.');
        return false;
    }
    
    const journalId = document.getElementById('journalId').value;
    const payload = {
        entry_date: document.getElementById('journalDate').value,
        reference_no: document.getElementById('journalReference').value,
        description: document.getElementById('journalDescription').value,
        source: document.getElementById('journalSource').value,
        source_ref: document.getElementById('journalSourceRef').value,
        notes: document.getElementById('journalNotes').value,
        lines: lines,
        auto_post: autoPost
    };
    
    try {
        const url = journalId 
            ? `/modules/accounting/journal-entries/${journalId}`
            : '{{ route("modules.accounting.journal-entries.store") }}';
        const method = journalId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        if (data.success) {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('journalModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            location.reload();
            return true;
        } else {
            alert(data.message || 'Error saving journal entry');
            return false;
        }
    } catch (error) {
        alert('Error: ' + error.message);
        return false;
    }
}

function exportJournalPdf() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'pdf');
    window.location.href = '{{ route("modules.accounting.journal-entries") }}?' + params.toString();
}

// Global modal cleanup on page load
$(document).ready(function() {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    // Fix modal backdrop stacking
    $(document).on('show.bs.modal', '.modal', function() {
        $('.modal-backdrop').not(':last').remove();
        $(this).css('z-index', 1050);
    });
    
    $(document).on('shown.bs.modal', '.modal', function() {
        $(this).css('z-index', 1050);
        $('.modal-backdrop').last().css('z-index', 1040);
        $(this).find('.modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
    });
    
    $(document).on('hidden.bs.modal', '.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>
@endpush
@endsection
