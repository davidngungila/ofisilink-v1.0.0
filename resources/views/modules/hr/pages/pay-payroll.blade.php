@extends('layouts.app')

@section('title', 'Pay Payroll - ' . $payroll->pay_period . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">
                                <i class="bx bx-dollar me-2"></i>Pay Payroll
                            </h4>
                            <p class="mb-0 text-white-50">Pay Period: <strong>{{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</strong> | Total Net: <strong>TZS {{ number_format($payroll->items->sum('net_salary'), 0) }}</strong></p>
                        </div>
                        <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light">
                            <i class="bx bx-arrow-back me-1"></i>Back to Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bx bx-user fs-1 text-primary mb-2"></i>
                    <h6 class="mb-0">Employees</h6>
                    <h3 class="mb-0">{{ $payroll->items->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bx bx-money fs-1 text-success mb-2"></i>
                    <h6 class="mb-0">Total Net Pay</h6>
                    <h3 class="mb-0">TZS {{ number_format($payroll->items->sum('net_salary'), 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bx bx-building fs-1 text-info mb-2"></i>
                    <h6 class="mb-0">Employer Cost</h6>
                    <h3 class="mb-0">TZS {{ number_format($payroll->items->sum('total_employer_cost'), 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bx bx-calendar fs-1 text-warning mb-2"></i>
                    <h6 class="mb-0">Pay Date</h6>
                    <h6 class="mb-0">{{ $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('d M Y') : 'N/A' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <form id="markPaidForm" action="{{ route('payroll.mark-paid', $payroll->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-dollar me-2"></i>Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Double Entry Bookkeeping:</strong> This will create two GL entries - Debit (Expense) and Credit (Cash/Bank Account)
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Double Entry GL Accounts Selection -->
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-book me-2"></i>General Ledger Accounts (Double Entry)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="debit_account_id" class="form-label">
                                                <i class="bx bx-arrow-to-left text-danger me-1"></i>Debit Account (Expense) <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="debit_account_id" name="debit_account_id" required>
                                                <option value="">-- Select Expense Account --</option>
                                                @if(isset($chartAccounts) && isset($chartAccounts['Expense']))
                                                    @foreach($chartAccounts['Expense'] as $account)
                                                        <option value="{{ $account->id }}" 
                                                                @if(str_contains(strtolower($account->name), 'salary') || str_contains(strtolower($account->code), 'salary')) selected @endif>
                                                            {{ $account->code }} — {{ $account->name }}
                                                            @if($account->category) <small>({{ $account->category }})</small> @endif
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <small class="text-muted">Account to be debited (Salary Expense)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="credit_account_id" class="form-label">
                                                <i class="bx bx-arrow-to-right text-success me-1"></i>Credit Account (Cash/Bank) <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="credit_account_id" name="credit_account_id" required>
                                                <option value="">-- Select Cash/Bank Account --</option>
                                                @if(isset($chartAccounts))
                                                    @if(isset($chartAccounts['Asset']))
                                                        @foreach($chartAccounts['Asset'] as $account)
                                                            <option value="{{ $account->id }}">
                                                                {{ $account->code }} — {{ $account->name }}
                                                                @if($account->category) <small>({{ $account->category }})</small> @endif
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </select>
                                            <small class="text-muted">Account to be credited (Cash/Bank Account)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning mb-0">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Note:</strong> If accounts are not listed, please create them in 
                                    <a href="{{ route('modules.accounting.index') }}" target="_blank" class="alert-link">Chart of Accounts</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_ref" class="form-label">Transaction Reference</label>
                                    <input type="text" class="form-control" id="transaction_ref" name="transaction_ref" placeholder="Enter transaction reference (e.g., cheque number, transfer ref)...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3" id="cashbox_field" style="display: none;">
                                    <label for="cash_box_id" class="form-label">
                                        <i class="bx bx-money me-1"></i>Cash Box (Optional)
                                    </label>
                                    <select class="form-select" id="cash_box_id" name="cash_box_id">
                                        <option value="">-- Not Required --</option>
                                        @if(isset($cashBoxes) && $cashBoxes->count() > 0)
                                            @foreach($cashBoxes as $cb)
                                                <option value="{{ $cb->id }}" data-balance="{{ $cb->current_balance }}">
                                                    {{ $cb->name }} ({{ $cb->currency ?? 'TZS' }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="text-muted">Optional: For cash payments tracking</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="transaction_details" class="form-label">Transaction Details / Description</label>
                            <textarea class="form-control" id="transaction_details" name="transaction_details" rows="3" placeholder="Enter additional transaction details or notes..."></textarea>
                            <small class="text-muted">This will be included in the GL entry description</small>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bx bx-check-circle me-2"></i>Mark as Paid & Post to GL
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Payroll Summary Card -->
                <div class="card shadow-lg border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Payroll Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Pay Period</small>
                            <h6 class="mb-0">{{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</h6>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Total Employees</small>
                            <h6 class="mb-0">{{ $payroll->items->count() }}</h6>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Total Net Pay</small>
                            <h4 class="mb-0 text-success">TZS {{ number_format($payroll->items->sum('net_salary'), 0) }}</h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Total Employer Cost</small>
                            <h6 class="mb-0 text-info">TZS {{ number_format($payroll->items->sum('total_employer_cost'), 0) }}</h6>
                        </div>
                        <hr>
                        <div>
                            <small class="text-muted">Processed By</small>
                            <p class="mb-0">{{ $payroll->processor->name ?? 'N/A' }}</p>
                        </div>
                        @if($payroll->reviewer)
                        <div class="mt-2">
                            <small class="text-muted">Reviewed By</small>
                            <p class="mb-0">{{ $payroll->reviewer->name }}</p>
                        </div>
                        @endif
                        @if($payroll->approver)
                        <div class="mt-2">
                            <small class="text-muted">Approved By</small>
                            <p class="mb-0">{{ $payroll->approver->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Handle payment method change
    $('#payment_method').change(function() {
        if ($(this).val() === 'cash') {
            $('#cashbox_field').show();
        } else {
            $('#cashbox_field').hide();
        }
    });
    
    // Form submission
    $('#markPaidForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Mark Payroll as Paid?',
            text: 'This will mark the payroll as paid and create GL entries. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Mark as Paid',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'swal2-high-z-index'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                
                fetch('{{ route("payroll.mark-paid", $payroll->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            customClass: {
                                popup: 'swal2-high-z-index'
                            }
                        }).then(() => {
                            window.location.href = '{{ route("modules.hr.payroll") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to mark payroll as paid',
                            customClass: {
                                popup: 'swal2-high-z-index'
                            }
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing the payment',
                        customClass: {
                            popup: 'swal2-high-z-index'
                        }
                    });
                });
            }
        });
    });
});
</script>
@endpush
@endsection

