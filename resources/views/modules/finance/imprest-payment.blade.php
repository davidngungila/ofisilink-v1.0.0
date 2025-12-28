@extends('layouts.app')

@section('title', 'Process Payment for Imprest Request')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Process Payment for Imprest Request</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('imprest.show', $imprestRequest->id) }}">{{ $imprestRequest->request_no }}</a></li>
            <li class="breadcrumb-item active">Process Payment</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .info-card {
        border-left: 4px solid #0d6efd;
        background: #f8f9fa;
    }
    
    .staff-payment-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }
    
    .staff-payment-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .staff-payment-card.paid {
        border-color: #28a745;
        background: #f8fff9;
    }
    
    .amount-input-group {
        position: relative;
    }
    
    .amount-input-group .form-control {
        padding-left: 80px;
    }
    
    .amount-input-group::before {
        content: 'TZS';
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: bold;
        color: #6c757d;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('imprest.show', $imprestRequest->id) }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back to Details
        </a>
    </div>

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <h2 class="fw-bold mb-2 text-white">
                <i class="bx bx-money me-2"></i>Process Payment for Imprest Request
            </h2>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Request Number:</strong> {{ $imprestRequest->request_no }}</p>
                    <p class="mb-1"><strong>Purpose:</strong> {{ $imprestRequest->purpose }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Total Amount:</strong> <strong>TZS {{ number_format($imprestRequest->amount, 2) }}</strong></p>
                    <p class="mb-1"><strong>Assigned Staff:</strong> {{ $imprestRequest->assignments->count() }} member(s)</p>
                    <p class="mb-0"><strong>Equal Amount per Staff:</strong> <strong>TZS {{ number_format($imprestRequest->amount / max($imprestRequest->assignments->count(), 1), 2) }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Mode Selection -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0 text-white"><i class="bx bx-cog me-2"></i>Payment Mode</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check payment-mode-option">
                        <input class="form-check-input" type="radio" name="payment_mode" id="payment_mode_manual" value="manual" checked>
                        <label class="form-check-label" for="payment_mode_manual">
                            <strong>Manual Payment</strong>
                            <br><small class="text-muted">Process individual payments for each staff member</small>
                        </label>
                            </div>
                        </div>
                <div class="col-md-6">
                    <div class="form-check payment-mode-option">
                        <input class="form-check-input" type="radio" name="payment_mode" id="payment_mode_bulk" value="bulk">
                        <label class="form-check-label" for="payment_mode_bulk">
                            <strong>Bulk Payment</strong>
                            <br><small class="text-muted">Process single transaction for full imprest amount</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <form id="paymentForm" method="POST" action="{{ route('imprest.payment', $imprestRequest->id) }}">
        @csrf
        <input type="hidden" name="imprest_id" value="{{ $imprestRequest->id }}">
        <input type="hidden" name="payment_mode" id="hidden_payment_mode" value="manual">
        
        @php
            $equalAmount = $imprestRequest->amount / max($imprestRequest->assignments->count(), 1);
            $totalPaidAmount = 0;
        @endphp

        <!-- Manual Payment Section -->
        <div id="manualPaymentSection">

        @foreach($imprestRequest->assignments as $index => $assignment)
        @php
            $isPaid = $assignment->is_paid ?? false;
            $totalPaidAmount += $assignment->paid_amount ?? 0;
        @endphp
        <div class="card border-0 shadow-sm mb-4 staff-payment-card {{ $isPaid ? 'paid' : '' }}">
            <div class="card-header {{ $isPaid ? 'bg-success text-white' : 'bg-primary text-white' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-user me-2"></i>
                        {{ $assignment->staff->name ?? 'Unknown Staff' }}
                        @if($isPaid)
                            <span class="badge bg-light text-success ms-2">
                                <i class="bx bx-check-circle"></i> Paid
                            </span>
                        @endif
                    </h5>
                    <div>
                        <strong class="text-white">Assigned: TZS {{ number_format($assignment->assigned_amount, 2) }}</strong>
                    </div>
                </div>
                @if($assignment->staff->primaryDepartment)
                <small class="text-white-50">
                    <i class="bx bx-building"></i> {{ $assignment->staff->primaryDepartment->name }}
                </small>
                @endif
            </div>
            <div class="card-body">
                <input type="hidden" name="assignments[{{ $index }}][assignment_id]" value="{{ $assignment->id }}">
                <input type="hidden" name="assignments[{{ $index }}][staff_id]" value="{{ $assignment->staff_id }}">
                
                <div class="row">
                    <!-- Payment Amount -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Payment Amount <span class="text-danger">*</span>
                        </label>
                        <div class="amount-input-group">
                            <input type="number" 
                                   class="form-control payment-amount" 
                                   name="assignments[{{ $index }}][paid_amount]" 
                                   id="paid_amount_{{ $assignment->id }}"
                                   value="{{ $assignment->paid_amount ?? $equalAmount }}" 
                                   step="0.01" 
                                   min="0" 
                                   max="{{ $imprestRequest->amount }}"
                                   data-assignment-id="{{ $assignment->id }}"
                                   data-index="{{ $index }}"
                                   data-default-amount="{{ $equalAmount }}"
                                   required>
                        </div>
                        <small class="text-muted">
                            <i class="bx bx-info-circle"></i> Default: TZS {{ number_format($equalAmount, 2) }} (equal share)
                        </small>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Payment Method <span class="text-danger">*</span>
                        </label>
                        <select class="form-select payment-method" 
                                name="assignments[{{ $index }}][payment_method]" 
                                id="payment_method_{{ $assignment->id }}"
                                data-assignment-id="{{ $assignment->id }}"
                                data-index="{{ $index }}"
                                required>
                            <option value="">Select Method</option>
                            <option value="bank_transfer" {{ ($assignment->payment_method ?? 'bank_transfer') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_money" {{ ($assignment->payment_method ?? '') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="cash" {{ ($assignment->payment_method ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                    </div>
                </div>

                <!-- Bank Transfer Fields (only shown when bank_transfer is selected) -->
                <div class="row bank-transfer-fields-{{ $assignment->id }}" style="display: {{ ($assignment->payment_method ?? 'bank_transfer') === 'bank_transfer' ? 'block' : 'none' }};">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Bank Account <span class="text-danger">*</span>
                        </label>
                        <select class="form-select bank-account-select" 
                                name="assignments[{{ $index }}][bank_account_id]" 
                                id="bank_account_id_{{ $assignment->id }}"
                                data-assignment-id="{{ $assignment->id }}"
                                data-staff-id="{{ $assignment->staff_id }}">
                            <option value="">Select Bank Account (Organization)</option>
                                @foreach($bankAccounts as $bankAccount)
                            <option value="{{ $bankAccount->id }}" 
                                    data-bank-name="{{ $bankAccount->bank_name }}"
                                    data-account-number="{{ $bankAccount->account_number }}"
                                    {{ ($assignment->bank_account_id ?? null) == $bankAccount->id ? 'selected' : '' }}>
                                    {{ $bankAccount->bank_name }} - {{ $bankAccount->account_number }} ({{ $bankAccount->account_name }})
                                </option>
                                @endforeach
                            </select>
                        @if($assignment->staff->primaryBankAccount)
                        <small class="text-muted d-block mt-1">
                            <i class="bx bx-info-circle"></i> Employee's Primary Bank: 
                            <strong>{{ $assignment->staff->primaryBankAccount->bank_name }}</strong> - 
                            <strong>{{ $assignment->staff->primaryBankAccount->account_number }}</strong>
                            @if($assignment->staff->primaryBankAccount->account_name)
                                ({{ $assignment->staff->primaryBankAccount->account_name }})
                            @endif
                        </small>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Bank Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control bank-name" 
                               name="assignments[{{ $index }}][bank_name]" 
                               id="bank_name_{{ $assignment->id }}"
                               value="{{ $assignment->bank_name ?? ($assignment->staff->primaryBankAccount->bank_name ?? '') }}"
                               placeholder="Enter bank name"
                               data-staff-bank-name="{{ $assignment->staff->primaryBankAccount->bank_name ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Account Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control account-number" 
                               name="assignments[{{ $index }}][account_number]" 
                               id="account_number_{{ $assignment->id }}"
                               value="{{ $assignment->account_number ?? ($assignment->staff->primaryBankAccount->account_number ?? '') }}"
                               placeholder="Enter account number"
                               data-staff-account-number="{{ $assignment->staff->primaryBankAccount->account_number ?? '' }}">
                    </div>
                </div>

                <!-- Mobile Money Fields (only shown when mobile_money is selected) -->
                <div class="row mobile-money-fields-{{ $assignment->id }}" style="display: {{ ($assignment->payment_method ?? '') === 'mobile_money' ? 'block' : 'none' }};">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Mobile Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control mobile-number" 
                               name="assignments[{{ $index }}][account_number]" 
                               id="mobile_number_{{ $assignment->id }}"
                               value="{{ $assignment->account_number ?? ($assignment->staff->mobile ?? $assignment->staff->phone ?? '') }}"
                               placeholder="Enter mobile number (e.g., 255712345678)"
                               data-staff-mobile="{{ $assignment->staff->mobile ?? $assignment->staff->phone ?? '' }}">
                        <small class="text-muted d-block mt-1">
                            <i class="bx bx-info-circle"></i> Format: 255XXXXXXXXX (12 digits)
                        </small>
                        </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Mobile Money Provider <span class="text-danger">*</span>
                        </label>
                        <select class="form-select mobile-provider" 
                                name="assignments[{{ $index }}][bank_name]" 
                                id="mobile_provider_{{ $assignment->id }}">
                            <option value="">Select Provider</option>
                            <option value="M-Pesa" {{ ($assignment->bank_name ?? '') === 'M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
                            <option value="Tigo Pesa" {{ ($assignment->bank_name ?? '') === 'Tigo Pesa' ? 'selected' : '' }}>Tigo Pesa</option>
                            <option value="Airtel Money" {{ ($assignment->bank_name ?? '') === 'Airtel Money' ? 'selected' : '' }}>Airtel Money</option>
                            <option value="Halopesa" {{ ($assignment->bank_name ?? '') === 'Halopesa' ? 'selected' : '' }}>Halopesa</option>
                            <option value="T-Pesa" {{ ($assignment->bank_name ?? '') === 'T-Pesa' ? 'selected' : '' }}>T-Pesa</option>
                        </select>
                    </div>
                </div>

                <!-- Payment Date -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Payment Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               class="form-control" 
                               name="assignments[{{ $index }}][payment_date]" 
                               id="payment_date_{{ $assignment->id }}"
                               value="{{ $assignment->payment_date ? \Carbon\Carbon::parse($assignment->payment_date)->format('Y-m-d') : date('Y-m-d') }}" 
                               required>
                    </div>
                    <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Payment Reference</label>
                        <input type="text" 
                               class="form-control" 
                               name="assignments[{{ $index }}][payment_reference]" 
                               id="payment_reference_{{ $assignment->id }}"
                               value="{{ $assignment->payment_reference ?? '' }}"
                               placeholder="Transaction reference number">
                    </div>
                </div>

                <!-- Payment Notes -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Notes</label>
                    <textarea class="form-control" 
                              name="assignments[{{ $index }}][payment_notes]" 
                              id="payment_notes_{{ $assignment->id }}"
                              rows="2" 
                              placeholder="Additional notes about this payment...">{{ $assignment->payment_notes ?? '' }}</textarea>
                </div>
                
                <!-- Individual Payment Button -->
                <div class="d-flex justify-content-end mt-3">
                    @if(!$isPaid)
                    <button type="button" 
                            class="btn btn-success btn-lg process-individual-payment" 
                            data-assignment-id="{{ $assignment->id }}"
                            data-staff-name="{{ $assignment->staff->name ?? 'Staff' }}"
                            data-imprest-id="{{ $imprestRequest->id }}">
                        <i class="bx bx-money me-1"></i>Pay This Staff
                    </button>
                    @else
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="bx bx-check-circle me-1"></i>Already Paid
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

            <!-- Summary Card (Manual Payment) -->
            <div class="card border-0 shadow-sm mb-4 bg-info text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="mb-1">Total Request Amount</h6>
                            <h4 class="mb-0">TZS {{ number_format($imprestRequest->amount, 2) }}</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1">Total Payment Amount</h6>
                            <h4 class="mb-0" id="total-payment-amount">TZS {{ number_format($totalPaidAmount, 2) }}</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1">Remaining Amount</h6>
                            <h4 class="mb-0" id="remaining-amount">TZS {{ number_format($imprestRequest->amount - $totalPaidAmount, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Payment Section -->
        <div id="bulkPaymentSection" style="display: none;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="bx bx-money me-2"></i>Bulk Payment - Single Transaction</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Bulk Payment Mode:</strong> This will process a single payment transaction for the full imprest amount (TZS {{ number_format($imprestRequest->amount, 2) }}). 
                        All assigned staff will be marked as paid with equal amounts.
                    </div>

                    <div class="row">
                        <!-- Payment Amount (Fixed for bulk) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Payment Amount <span class="text-danger">*</span>
                            </label>
                            <div class="amount-input-group">
                                <input type="number" 
                                       class="form-control" 
                                       name="bulk_paid_amount" 
                                       id="bulk_paid_amount"
                                       value="{{ $imprestRequest->amount }}" 
                                       step="0.01" 
                                       min="0" 
                                       max="{{ $imprestRequest->amount }}"
                                       readonly
                                       style="background-color: #f8f9fa;">
                            </div>
                    <small class="text-muted">
                                <i class="bx bx-info-circle"></i> Full imprest amount (read-only)
                            </small>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Payment Method <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="bulk_payment_method" id="bulk_payment_method" required>
                                <option value="">Select Method</option>
                                <option value="bank_transfer" selected>Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bulk Bank Transfer Fields -->
                    <div class="row" id="bulk_bank_fields" style="display: block;">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Bank Account <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="bulk_bank_account_id" id="bulk_bank_account_id">
                                <option value="">Select Bank Account (Organization)</option>
                                @foreach($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}" 
                                        data-bank-name="{{ $bankAccount->bank_name }}"
                                        data-account-number="{{ $bankAccount->account_number }}">
                                    {{ $bankAccount->bank_name }} - {{ $bankAccount->account_number }} ({{ $bankAccount->account_name }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Bank Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="bulk_bank_name" 
                                   id="bulk_bank_name"
                                   placeholder="Enter bank name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Account Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="bulk_account_number" 
                                   id="bulk_account_number"
                                   placeholder="Enter account number">
                        </div>
                    </div>

                    <!-- Bulk Mobile Money Fields -->
                    <div class="row" id="bulk_mobile_fields" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="bulk_mobile_number" 
                                   id="bulk_mobile_number"
                                   placeholder="Enter mobile number (e.g., 255712345678)">
                            <small class="text-muted d-block mt-1">
                                <i class="bx bx-info-circle"></i> Format: 255XXXXXXXXX (12 digits)
                    </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Mobile Money Provider <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="bulk_mobile_provider" id="bulk_mobile_provider">
                                <option value="">Select Provider</option>
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Tigo Pesa">Tigo Pesa</option>
                                <option value="Airtel Money">Airtel Money</option>
                                <option value="Halopesa">Halopesa</option>
                                <option value="T-Pesa">T-Pesa</option>
                            </select>
                        </div>
                    </div>

                    <!-- Payment Date -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   name="bulk_payment_date" 
                                   id="bulk_payment_date"
                                   value="{{ date('Y-m-d') }}" 
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Payment Reference</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="bulk_payment_reference" 
                                   id="bulk_payment_reference"
                                   placeholder="Transaction reference number">
                        </div>
                    </div>

                    <!-- Payment Notes -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Notes</label>
                        <textarea class="form-control" 
                                  name="bulk_payment_notes" 
                                  id="bulk_payment_notes"
                                  rows="2" 
                                  placeholder="Additional notes about this payment..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('imprest.show', $imprestRequest->id) }}" class="btn btn-secondary btn-lg">
                <i class="bx bx-x me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="bx bx-check-circle me-1"></i><span id="submitBtnText">Process All Payments</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const paymentAmounts = document.querySelectorAll('.payment-amount');
    const paymentMethods = document.querySelectorAll('.payment-method');
    const bankAccountSelects = document.querySelectorAll('.bank-account-select');
    const totalRequestAmount = {{ $imprestRequest->amount }};
    const paymentModeRadios = document.querySelectorAll('input[name="payment_mode"]');
    const manualSection = document.getElementById('manualPaymentSection');
    const bulkSection = document.getElementById('bulkPaymentSection');
    const hiddenPaymentMode = document.getElementById('hidden_payment_mode');
    
    // Payment Mode Toggle
    paymentModeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const mode = this.value;
            hiddenPaymentMode.value = mode;
            
            if (mode === 'bulk') {
                if (manualSection) manualSection.style.display = 'none';
                if (bulkSection) bulkSection.style.display = 'block';
            } else {
                if (manualSection) manualSection.style.display = 'block';
                if (bulkSection) bulkSection.style.display = 'none';
            }
        });
    });
    
    // Bulk Payment Method Toggle
    const bulkPaymentMethod = document.getElementById('bulk_payment_method');
    const bulkBankFields = document.getElementById('bulk_bank_fields');
    const bulkMobileFields = document.getElementById('bulk_mobile_fields');
    
    if (bulkPaymentMethod) {
        bulkPaymentMethod.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                if (bulkBankFields) bulkBankFields.style.display = 'block';
                if (bulkMobileFields) bulkMobileFields.style.display = 'none';
                // Make bank fields required
                document.getElementById('bulk_bank_name').required = true;
                document.getElementById('bulk_account_number').required = true;
                document.getElementById('bulk_mobile_number').required = false;
                document.getElementById('bulk_mobile_provider').required = false;
            } else if (this.value === 'mobile_money') {
                if (bulkBankFields) bulkBankFields.style.display = 'none';
                if (bulkMobileFields) bulkMobileFields.style.display = 'block';
                // Make mobile fields required
                document.getElementById('bulk_bank_name').required = false;
                document.getElementById('bulk_account_number').required = false;
                document.getElementById('bulk_mobile_number').required = true;
                document.getElementById('bulk_mobile_provider').required = true;
            } else {
                if (bulkBankFields) bulkBankFields.style.display = 'none';
                if (bulkMobileFields) bulkMobileFields.style.display = 'none';
                document.getElementById('bulk_bank_name').required = false;
                document.getElementById('bulk_account_number').required = false;
                document.getElementById('bulk_mobile_number').required = false;
                document.getElementById('bulk_mobile_provider').required = false;
            }
        });
    }
    
    // Auto-fill bulk bank details when bank account is selected
    const bulkBankAccountSelect = document.getElementById('bulk_bank_account_id');
    if (bulkBankAccountSelect) {
        bulkBankAccountSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset) {
                const bankName = document.getElementById('bulk_bank_name');
                const accountNumber = document.getElementById('bulk_account_number');
                if (bankName && selectedOption.dataset.bankName) {
                    bankName.value = selectedOption.dataset.bankName;
                }
                if (accountNumber && selectedOption.dataset.accountNumber) {
                    accountNumber.value = selectedOption.dataset.accountNumber;
                }
            }
        });
    }
    
    // Calculate and update total payment amount
    function updateTotalPayment() {
        let total = 0;
        paymentAmounts.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        
        document.getElementById('total-payment-amount').textContent = 'TZS ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('remaining-amount').textContent = 'TZS ' + (totalRequestAmount - total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    // Show/hide fields based on payment method and auto-populate from employee's data
    paymentMethods.forEach(select => {
        const assignmentId = select.dataset.assignmentId;
        const bankTransferFields = document.querySelector('.bank-transfer-fields-' + assignmentId);
        const mobileMoneyFields = document.querySelector('.mobile-money-fields-' + assignmentId);
        const bankNameInput = document.getElementById('bank_name_' + assignmentId);
        const accountNumberInput = document.getElementById('account_number_' + assignmentId);
        const mobileNumberInput = document.getElementById('mobile_number_' + assignmentId);
        const mobileProviderInput = document.getElementById('mobile_provider_' + assignmentId);
        
        // Function to populate bank details from employee's primary bank account
        function populateEmployeeBankDetails() {
            if (bankNameInput && accountNumberInput) {
                const staffBankName = bankNameInput.dataset.staffBankName || '';
                const staffAccountNumber = accountNumberInput.dataset.staffAccountNumber || '';
                
                // Only populate if fields are empty
                if (!bankNameInput.value && staffBankName) {
                    bankNameInput.value = staffBankName;
                }
                if (!accountNumberInput.value && staffAccountNumber) {
                    accountNumberInput.value = staffAccountNumber;
                }
            }
        }
        
        // Function to populate mobile number from employee's mobile/phone
        function populateEmployeeMobileDetails() {
            if (mobileNumberInput) {
                const staffMobile = mobileNumberInput.dataset.staffMobile || '';
                if (!mobileNumberInput.value && staffMobile) {
                    mobileNumberInput.value = staffMobile;
                }
            }
        }
        
        // Function to toggle fields based on payment method
        function togglePaymentFields(method) {
            // Hide all fields first
            if (bankTransferFields) bankTransferFields.style.display = 'none';
            if (mobileMoneyFields) mobileMoneyFields.style.display = 'none';
            
            // Remove required from all fields
            const bankAccount = document.getElementById('bank_account_id_' + assignmentId);
            if (bankAccount) bankAccount.required = false;
            if (bankNameInput) bankNameInput.required = false;
            if (accountNumberInput) accountNumberInput.required = false;
            if (mobileNumberInput) mobileNumberInput.required = false;
            if (mobileProviderInput) mobileProviderInput.required = false;
            
            // Show and make required based on method
            if (method === 'bank_transfer') {
                if (bankTransferFields) bankTransferFields.style.display = 'block';
                if (bankAccount) bankAccount.required = true;
                if (bankNameInput) bankNameInput.required = true;
                if (accountNumberInput) accountNumberInput.required = true;
                populateEmployeeBankDetails();
            } else if (method === 'mobile_money') {
                if (mobileMoneyFields) mobileMoneyFields.style.display = 'block';
                if (mobileNumberInput) mobileNumberInput.required = true;
                if (mobileProviderInput) mobileProviderInput.required = true;
                populateEmployeeMobileDetails();
            }
            // For 'cash', all fields remain hidden and not required
        }
        
        // Show fields on page load based on default/selected method
        togglePaymentFields(select.value);
        
        // Handle payment method change
        select.addEventListener('change', function() {
            togglePaymentFields(this.value);
        });
    });
    
    // Auto-fill bank details when bank account is selected (organization bank account)
    bankAccountSelects.forEach(select => {
        const assignmentId = select.dataset.assignmentId;
        const bankNameInput = document.getElementById('bank_name_' + assignmentId);
        const accountNumberInput = document.getElementById('account_number_' + assignmentId);
        
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset) {
                // Only auto-fill if user selects an organization bank account
                // This will override employee's primary bank details
                if (bankNameInput && selectedOption.dataset.bankName) {
                    bankNameInput.value = selectedOption.dataset.bankName;
                }
                if (accountNumberInput && selectedOption.dataset.accountNumber) {
                    accountNumberInput.value = selectedOption.dataset.accountNumber;
                }
            } else {
                // If no organization bank selected, restore employee's primary bank details
                if (bankNameInput && bankNameInput.dataset.staffBankName) {
                    bankNameInput.value = bankNameInput.dataset.staffBankName;
                }
                if (accountNumberInput && accountNumberInput.dataset.staffAccountNumber) {
                    accountNumberInput.value = accountNumberInput.dataset.staffAccountNumber;
                }
            }
        });
    });
    
    // Update total when payment amounts change
    paymentAmounts.forEach(input => {
        input.addEventListener('input', updateTotalPayment);
        input.addEventListener('change', updateTotalPayment);
    });
    
    // Reset to default amount on double-click
    paymentAmounts.forEach(input => {
        input.addEventListener('dblclick', function() {
            const defaultAmount = parseFloat(this.dataset.defaultAmount) || 0;
            this.value = defaultAmount.toFixed(2);
            updateTotalPayment();
        });
    });
    
    // Initial calculation
    updateTotalPayment();
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const paymentMode = hiddenPaymentMode.value || 'manual';
            let isValid = true;
            let errorMessages = [];
            
            if (paymentMode === 'bulk') {
                // Validate bulk payment only
                const bulkPaymentMethod = document.getElementById('bulk_payment_method');
                const bulkPaymentDate = document.getElementById('bulk_payment_date');
                
                if (!bulkPaymentMethod || !bulkPaymentMethod.value) {
                    isValid = false;
                    errorMessages.push('Payment method is required for bulk payment');
                }
                
                if (!bulkPaymentDate || !bulkPaymentDate.value) {
                    isValid = false;
                    errorMessages.push('Payment date is required');
                }
                
                if (bulkPaymentMethod && bulkPaymentMethod.value === 'bank_transfer') {
                    const bulkBankAccount = document.getElementById('bulk_bank_account_id');
                    const bulkBankName = document.getElementById('bulk_bank_name');
                    const bulkAccountNumber = document.getElementById('bulk_account_number');
                    
                    const hasBankAccount = bulkBankAccount && bulkBankAccount.value;
                    const hasBankName = bulkBankName && bulkBankName.value;
                    const hasAccountNumber = bulkAccountNumber && bulkAccountNumber.value;
                    
                    if (!hasBankAccount && (!hasBankName || !hasAccountNumber)) {
                        isValid = false;
                        errorMessages.push('Bank details are required for bank transfer (bulk payment)');
                    }
                } else if (bulkPaymentMethod && bulkPaymentMethod.value === 'mobile_money') {
                    const bulkMobileNumber = document.getElementById('bulk_mobile_number');
                    const bulkMobileProvider = document.getElementById('bulk_mobile_provider');
                    
                    if (!bulkMobileNumber || !bulkMobileNumber.value) {
                        isValid = false;
                        errorMessages.push('Mobile number is required for mobile money (bulk payment)');
                    }
                    if (!bulkMobileProvider || !bulkMobileProvider.value) {
                        isValid = false;
                        errorMessages.push('Mobile money provider is required (bulk payment)');
                    }
                }
                    } else {
                // Validate manual payments only
                paymentMethods.forEach((select, index) => {
                    if (!select) return;
                    const assignmentId = select.dataset.assignmentId;
                    const staffIndex = (select.dataset.index !== undefined) ? parseInt(select.dataset.index) + 1 : index + 1;
                    
                    if (!select.value) {
                        isValid = false;
                        errorMessages.push(`Payment method is required for staff ${staffIndex}`);
                        return; // Skip further validation if no method selected
                    }
                    
                    // Only validate fields for the SELECTED payment method (not all methods)
                    if (select.value === 'bank_transfer') {
                        const bankAccount = document.getElementById('bank_account_id_' + assignmentId);
                        const bankName = document.getElementById('bank_name_' + assignmentId);
                        const accountNumber = document.getElementById('account_number_' + assignmentId);
                        
                        const hasBankAccount = bankAccount && bankAccount.value;
                        const hasBankName = bankName && bankName.value;
                        const hasAccountNumber = accountNumber && accountNumber.value;
                        
                        if (!hasBankAccount && (!hasBankName || !hasAccountNumber)) {
                            isValid = false;
                            errorMessages.push(`Bank details are required for staff ${staffIndex} (bank transfer)`);
                        }
                    } else if (select.value === 'mobile_money') {
                        const mobileNumber = document.getElementById('mobile_number_' + assignmentId);
                        const mobileProvider = document.getElementById('mobile_provider_' + assignmentId);
                        
                        if (!mobileNumber || !mobileNumber.value) {
                            isValid = false;
                            errorMessages.push(`Mobile number is required for staff ${staffIndex} (mobile money)`);
                        }
                        if (!mobileProvider || !mobileProvider.value) {
                            isValid = false;
                            errorMessages.push(`Mobile money provider is required for staff ${staffIndex}`);
                        }
                    }
                    // For 'cash', no additional validation needed
                });
                
                paymentAmounts.forEach((input, index) => {
                    if (!input) return;
                    const value = parseFloat(input.value) || 0;
                    if (value <= 0) {
                        isValid = false;
                        errorMessages.push(`Payment amount must be greater than 0 for staff ${index + 1}`);
                    }
                });
                
                // Check if total exceeds request amount (manual mode only)
                let total = 0;
                paymentAmounts.forEach(input => {
                    if (input && input.value) {
                        total += parseFloat(input.value) || 0;
                    }
                });
                
                if (total > totalRequestAmount) {
                    isValid = false;
                    errorMessages.push(`Total payment amount (TZS ${total.toLocaleString('en-US', {minimumFractionDigits: 2})}) exceeds request amount (TZS ${totalRequestAmount.toLocaleString('en-US', {minimumFractionDigits: 2})})`);
                }
            }
            
            if (!isValid) {
                const errorMsg = errorMessages.join('\n');
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Validation Error:\n' + errorMsg);
                }
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
            
            const formData = new FormData(form);
            
            fetch('{{ route("imprest.payment", $imprestRequest->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const result = await response.json();
                
                if (response.ok && result.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', result.message || 'Payments processed successfully!', { duration: 5000 });
                    } else {
                        alert('Payments processed successfully!');
                    }
                    
                    setTimeout(() => {
                        window.location.href = '{{ route("imprest.show", $imprestRequest->id) }}';
                    }, 1000);
                } else {
                    let errorMsg = result.message || 'Failed to process payments.';
                    
                    if (result.errors) {
                        const errorMessages = [];
                        Object.keys(result.errors).forEach(key => {
                            if (Array.isArray(result.errors[key])) {
                                result.errors[key].forEach(err => {
                                    errorMessages.push(`${key}: ${err}`);
                                });
                            } else {
                                errorMessages.push(`${key}: ${result.errors[key]}`);
                            }
                        });
                        if (errorMessages.length > 0) {
                            errorMsg = errorMessages.join('\n');
                        }
                    }
                    
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || 'Network error occurred';
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Individual Payment Processing
    document.querySelectorAll('.process-individual-payment').forEach(button => {
        button.addEventListener('click', function() {
            const assignmentId = this.dataset.assignmentId;
            const staffName = this.dataset.staffName;
            const imprestId = this.dataset.imprestId;
            
            // Get form values for this assignment
            const paidAmount = parseFloat(document.getElementById('paid_amount_' + assignmentId)?.value || 0);
            const paymentMethod = document.getElementById('payment_method_' + assignmentId)?.value;
            const paymentDate = document.getElementById('payment_date_' + assignmentId)?.value;
            const paymentReference = document.getElementById('payment_reference_' + assignmentId)?.value || '';
            const paymentNotes = document.getElementById('payment_notes_' + assignmentId)?.value || '';
            
            // Validate required fields
            if (!paidAmount || paidAmount <= 0) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Payment amount is required and must be greater than 0', { duration: 5000 });
                } else {
                    alert('Payment amount is required and must be greater than 0');
                }
                return;
            }
            
            if (!paymentMethod) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Payment method is required', { duration: 5000 });
                } else {
                    alert('Payment method is required');
                }
                return;
            }
            
            if (!paymentDate) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Payment date is required', { duration: 5000 });
                } else {
                    alert('Payment date is required');
                }
                return;
            }
            
            // Validate payment method specific fields
            let isValid = true;
            let errorMessage = '';
            
            if (paymentMethod === 'bank_transfer') {
                const bankAccount = document.getElementById('bank_account_id_' + assignmentId);
                const bankName = document.getElementById('bank_name_' + assignmentId);
                const accountNumber = document.getElementById('account_number_' + assignmentId);
                
                const hasBankAccount = bankAccount && bankAccount.value;
                const hasBankName = bankName && bankName.value;
                const hasAccountNumber = accountNumber && accountNumber.value;
                
                if (!hasBankAccount && (!hasBankName || !hasAccountNumber)) {
                    isValid = false;
                    errorMessage = 'Bank details are required for bank transfer payment';
                }
            } else if (paymentMethod === 'mobile_money') {
                const mobileNumber = document.getElementById('mobile_number_' + assignmentId);
                const mobileProvider = document.getElementById('mobile_provider_' + assignmentId);
                
                if (!mobileNumber || !mobileNumber.value) {
                    isValid = false;
                    errorMessage = 'Mobile number is required for mobile money payment';
                }
                if (!mobileProvider || !mobileProvider.value) {
                    isValid = false;
                    errorMessage = 'Mobile money provider is required';
                }
            }
            
            if (!isValid) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', errorMessage, { duration: 5000 });
                } else {
                    alert(errorMessage);
                }
                return;
            }
            
            // Prepare form data
            const formData = new FormData();
            formData.append('paid_amount', paidAmount);
            formData.append('payment_method', paymentMethod);
            formData.append('payment_date', paymentDate);
            formData.append('payment_reference', paymentReference);
            formData.append('payment_notes', paymentNotes);
            
            if (paymentMethod === 'bank_transfer') {
                const bankAccount = document.getElementById('bank_account_id_' + assignmentId);
                const bankName = document.getElementById('bank_name_' + assignmentId);
                const accountNumber = document.getElementById('account_number_' + assignmentId);
                
                if (bankAccount && bankAccount.value) {
                    formData.append('bank_account_id', bankAccount.value);
                }
                if (bankName && bankName.value) {
                    formData.append('bank_name', bankName.value);
                }
                if (accountNumber && accountNumber.value) {
                    formData.append('account_number', accountNumber.value);
                }
            } else if (paymentMethod === 'mobile_money') {
                const mobileNumber = document.getElementById('mobile_number_' + assignmentId);
                const mobileProvider = document.getElementById('mobile_provider_' + assignmentId);
                
                if (mobileNumber && mobileNumber.value) {
                    formData.append('account_number', mobileNumber.value);
                }
                if (mobileProvider && mobileProvider.value) {
                    formData.append('bank_name', mobileProvider.value); // Provider stored in bank_name
                }
            }
            
            // Disable button and show loading
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
            
            // Send AJAX request
            const url = '{{ route("imprest.individual-payment", ["id" => $imprestRequest->id, "assignmentId" => ":assignmentId"]) }}'.replace(':assignmentId', assignmentId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const result = await response.json();
                
                if (response.ok && result.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', result.message || 'Payment processed successfully!', { duration: 5000 });
                    } else {
                        alert('Payment processed successfully!');
                    }
                    
                    // Reload page after short delay to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    let errorMsg = result.message || 'Failed to process payment.';
                    
                    if (result.errors) {
                        const errorMessages = [];
                        Object.keys(result.errors).forEach(key => {
                            if (Array.isArray(result.errors[key])) {
                                result.errors[key].forEach(err => {
                                    errorMessages.push(`${key}: ${err}`);
                                });
                            } else {
                                errorMessages.push(`${key}: ${result.errors[key]}`);
                            }
                        });
                        if (errorMessages.length > 0) {
                            errorMsg = errorMessages.join('\n');
                        }
                    }
                    
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                    
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || 'Network error occurred';
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
});
</script>
@endpush
