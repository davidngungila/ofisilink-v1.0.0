<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-history me-2"></i>Payroll History</h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshPayrollHistory()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover" id="payrollHistoryTable">
        <thead class="table-light">
            <tr>
                <th>Pay Period</th>
                <th>Pay Date</th>
                <th>Employees</th>
                <th class="text-end">Total Amount</th>
                <th>Status</th>
                <th>Processed By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payrolls as $payroll)
            <tr>
                <td>
                    <strong>{{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</strong>
                </td>
                <td>{{ $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('M d, Y') : 'N/A' }}</td>
                <td>
                    <span class="badge bg-label-info">{{ $payroll->employee_count ?? 0 }}</span>
                </td>
                <td class="text-end">
                    <strong>TZS {{ number_format($payroll->total_amount ?? 0, 2) }}</strong>
                </td>
                <td>
                    @php
                        $statusColors = [
                            'processed' => 'warning',
                            'reviewed' => 'info',
                            'approved' => 'success',
                            'paid' => 'primary',
                            'rejected' => 'danger',
                            'cancelled' => 'secondary'
                        ];
                        $statusColor = $statusColors[$payroll->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }}">{{ ucfirst($payroll->status) }}</span>
                </td>
                <td>{{ $payroll->processor->name ?? 'N/A' }}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="viewPayrollDetails({{ $payroll->id }})" title="View Details">
                            <i class="bx bx-show"></i>
                        </button>
                        @if($payroll->status === 'processed' && $can_review_payroll)
                        <button type="button" class="btn btn-outline-warning" onclick="reviewPayroll({{ $payroll->id }})" title="Review">
                            <i class="bx bx-check"></i>
                        </button>
                        @endif
                        @if($payroll->status === 'reviewed' && $can_approve_payroll)
                        <button type="button" class="btn btn-outline-info" onclick="approvePayroll({{ $payroll->id }})" title="Approve">
                            <i class="bx bx-check-circle"></i>
                        </button>
                        @endif
                        @if($payroll->status === 'approved' && $can_pay_payroll)
                        <button type="button" class="btn btn-outline-success" onclick="markAsPaid({{ $payroll->id }})" title="Mark as Paid">
                            <i class="bx bx-dollar"></i>
                        </button>
                        @endif
                        <a href="{{ route('payroll.export', $payroll->id) }}" class="btn btn-outline-secondary" title="Export">
                            <i class="bx bx-download"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="bx bx-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">No payroll records found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($payrolls->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $payrolls->links() }}
</div>
@endif

