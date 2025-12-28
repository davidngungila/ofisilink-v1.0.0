@extends('layouts.app')

@section('title', 'Review Payroll - ' . $payroll->pay_period . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">
                                <i class="bx bx-check-circle me-2"></i>Review Payroll
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

    <!-- Review Form -->
    <form id="reviewPayrollForm" action="{{ route('payroll.review', $payroll->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bx bx-check-circle me-2"></i>Payroll Review</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Review Instructions:</strong> Please carefully review all payroll items below. You can approve or reject this payroll. If rejected, HR will need to make adjustments.
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

                        <!-- Review Action -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Review Action</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Review Decision <span class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="review_action" id="review_approve" value="approve" checked>
                                                <label class="btn btn-outline-success" for="review_approve">
                                                    <i class="bx bx-check-circle me-1"></i>Approve for CEO Review
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="review_action" id="review_reject" value="reject">
                                                <label class="btn btn-outline-danger" for="review_reject">
                                                    <i class="bx bx-x-circle me-1"></i>Reject (Return to HR)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="review_notes" class="form-label">Review Notes</label>
                                            <textarea class="form-control" id="review_notes" name="review_notes" rows="4" placeholder="Enter any notes or comments about this payroll review..."></textarea>
                                            <small class="text-muted">These notes will be visible to HR and CEO</small>
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
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bx bx-check-circle me-1"></i>Submit Review
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
    $('#reviewPayrollForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Submit Review?',
            text: 'Are you sure you want to submit this review?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Submit Review',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'swal2-high-z-index'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                
                fetch('{{ route("payroll.review", $payroll->id) }}', {
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
                            text: data.message || 'Failed to submit review',
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
                        text: 'An error occurred while submitting the review',
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

