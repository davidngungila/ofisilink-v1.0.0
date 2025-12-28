@extends('layouts.app')

@section('title', 'Approve Payroll - ' . $payroll->pay_period . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">
                                <i class="bx bx-check-circle me-2"></i>Approve Payroll
                            </h4>
                            <p class="mb-0 text-white-50">Pay Period: <strong>{{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</strong></p>
                        </div>
                        <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light">
                            <i class="bx bx-arrow-back me-1"></i>Back to Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Summary Cards -->
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
                    <h6 class="mb-0">Total Gross</h6>
                    <h3 class="mb-0">TZS {{ number_format($payroll->items->sum(function($item) { return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount; }), 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="bx bx-minus-circle fs-1 text-danger mb-2"></i>
                    <h6 class="mb-0">Total Deductions</h6>
                    <h3 class="mb-0">TZS {{ number_format($payroll->items->sum(function($item) { return $item->paye_amount + $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->wcf_amount + $item->sdl_amount + $item->deduction_amount; }), 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bx bx-check-circle fs-1 text-info mb-2"></i>
                    <h6 class="mb-0">Total Net</h6>
                    <h3 class="mb-0">TZS {{ number_format($payroll->items->sum('net_salary'), 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Information -->
    @if($payroll->reviewer)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Review Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Reviewed By:</strong> {{ $payroll->reviewer->name ?? 'N/A' }}</p>
                            <p><strong>Reviewed At:</strong> {{ $payroll->reviewed_at ? $payroll->reviewed_at->format('d M Y, h:i A') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            @if($payroll->review_notes)
                            <p><strong>Review Notes:</strong></p>
                            <p class="text-muted">{{ $payroll->review_notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Approve Form -->
    <form id="approvePayrollForm" action="{{ route('payroll.approve', $payroll->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bx bx-check-circle me-2"></i>Payroll Approval</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Approval Instructions:</strong> This payroll has been reviewed by HOD. Please review all details below and approve to proceed with payment processing.
                        </div>

                        <!-- Payroll Items Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th class="text-end">Basic Salary</th>
                                        <th class="text-end">Overtime</th>
                                        <th class="text-end">Bonus</th>
                                        <th class="text-end">Allowance</th>
                                        <th class="text-end">Gross</th>
                                        <th class="text-end">Deductions</th>
                                        <th class="text-end">Net Salary</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payroll->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->employee->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $item->employee->employee->employee_id ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($item->basic_salary, 0) }}</td>
                                        <td class="text-end">{{ number_format($item->overtime_amount, 0) }}</td>
                                        <td class="text-end">{{ number_format($item->bonus_amount, 0) }}</td>
                                        <td class="text-end">{{ number_format($item->allowance_amount, 0) }}</td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount, 0) }}</td>
                                        <td class="text-end text-danger">{{ number_format($item->paye_amount + $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->wcf_amount + $item->sdl_amount + $item->deduction_amount, 0) }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($item->net_salary, 0) }}</td>
                                        <td>
                                            <a href="{{ route('payroll.payslip.page', $item->id) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="bx bx-show"></i> View Payslip
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Totals</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum('basic_salary'), 0) }}</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum('overtime_amount'), 0) }}</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum('bonus_amount'), 0) }}</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum('allowance_amount'), 0) }}</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum(function($item) { return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount; }), 0) }}</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum(function($item) { return $item->paye_amount + $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->wcf_amount + $item->sdl_amount + $item->deduction_amount; }), 0) }}</th>
                                        <th class="text-end">{{ number_format($payroll->items->sum('net_salary'), 0) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Approval Notes -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Approval Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
                                            <textarea class="form-control" id="approval_notes" name="approval_notes" rows="4" placeholder="Enter any notes or comments about this payroll approval..."></textarea>
                                            <small class="text-muted">These notes will be visible to the accountant during payment processing</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="bx bx-check-circle me-1"></i>Approve Payroll
                            </button>
                        </div>
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
    $('#approvePayrollForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Approve Payroll?',
            text: 'Are you sure you want to approve this payroll? This will allow the accountant to process payments.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'swal2-high-z-index'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                
                fetch('{{ route("payroll.approve", $payroll->id) }}', {
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
                            text: data.message || 'Failed to approve payroll',
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
                        text: 'An error occurred while approving the payroll',
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

