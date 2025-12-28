<!-- Staff View - Employee Payslip History -->
<div class="row">
    <!-- Employee Info Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white">
                    <i class="bx bx-user me-2"></i>My Payslips
                </h5>
            </div> <br>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Employee Name</h6>
                        <p class="mb-3"><strong>{{ Auth::user()->name ?? 'N/A' }}</strong></p>
                        
                        <h6 class="text-muted mb-1">Employee ID</h6>
                        <p class="mb-3"><strong>{{ Auth::user()->employee_id ?? Auth::user()->id }}</strong></p>
                        
                        <h6 class="text-muted mb-1">Department</h6>
                        <p class="mb-3"><strong>{{ Auth::user()->primaryDepartment->name ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Position</h6>
                        <p class="mb-3"><strong>{{ $employee_details->employee->position ?? 'N/A' }}</strong></p>
                        
                        <h6 class="text-muted mb-1">Employment Type</h6>
                        <p class="mb-3"><strong>{{ $employee_details->employee->employment_type ?? 'N/A' }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payslip History Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bx bx-receipt me-2"></i>Payslip History
                </h5>
            </div>
            <div class="card-body">
                @if($payrolls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Pay Period</th>
                                <th>Pay Date</th>
                                <th class="text-end">Basic Salary</th>
                                <th class="text-end">Gross Salary</th>
                                <th class="text-end">Total Deductions</th>
                                <th class="text-end">Net Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payrolls as $item)
                            @php
                                $payroll = $item->payroll;
                                $gross = $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                                $deductions = $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->paye_amount + $item->deduction_amount;
                            @endphp
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ date('F Y', strtotime($payroll->pay_period . '-01')) }}</strong><br>
                                        <small class="text-muted">{{ $payroll->pay_period }}</small>
                                    </div>
                                </td>
                                <td>
                                    {{ $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="text-end">TZS {{ number_format($item->basic_salary, 0) }}</td>
                                <td class="text-end">
                                    <strong>TZS {{ number_format($gross, 0) }}</strong>
                                </td>
                                <td class="text-end text-danger">
                                    -TZS {{ number_format($deductions, 0) }}
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">TZS {{ number_format($item->net_salary, 0) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($payroll->status) {
                                            'processed' => 'bg-label-warning',
                                            'reviewed' => 'bg-label-info',
                                            'approved' => 'bg-label-success',
                                            'paid' => 'bg-label-primary',
                                            default => 'bg-label-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($payroll->status) }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="viewPayslip({{ $item->id }})">
                                                View Details
                                            </a>
                                            @if(in_array($payroll->status, ['approved', 'paid']))
                                            <a class="dropdown-item" href="{{ route('payroll.payslip.pdf', $item->id) }}" target="_blank">
                                                PDF
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $payrolls->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bx bx-receipt fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No Payslips Found</h5>
                    <p class="text-muted">You don't have any payslips yet. Payslips will appear here once processed by HR.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payslip Details Modal - Advanced View -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen-lg-down" role="document" style="z-index: 10060 !important; max-width: 95%;">
        <div class="modal-content shadow-lg border-0" style="z-index: 10061 !important; border-radius: 10px;">
            <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #940000 0%, #c40000 100%); border-radius: 10px 10px 0 0;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div>
                        <h4 class="modal-title text-white mb-1" id="payslipModalTitle">
                            <i class="bx bx-receipt me-2"></i><strong>Employee Payslip Details</strong>
                        </h4>
                        <small class="text-white-50">Complete payroll breakdown and calculations</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 1;"></button>
                </div>
            </div>
            <div class="modal-body bg-light" id="payslipContent" style="max-height: calc(100vh - 200px); overflow-y: auto; padding: 25px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fs-5">Loading payslip details... Please wait.</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-top" style="padding: 15px 25px; border-radius: 0 0 10px 10px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <a href="#" class="btn btn-danger" id="downloadPayslipPdf" target="_blank" style="display: none;">
                    <i class="bx bx-file-pdf me-2"></i>Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewPayslip(payrollItemId) {
    if (!payrollItemId || payrollItemId <= 0) {
        alert('Invalid payslip ID');
        return;
    }
    
    console.log('Loading payslip for ID:', payrollItemId);
    
    // Ensure jQuery is loaded
    if (typeof $ === 'undefined') {
        alert('jQuery is not loaded. Please refresh the page.');
        return;
    }
    
    // Show loading state
    $('#payslipContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading payslip...</p>
        </div>
    `);
    
    // Show modal first with highest z-index
    const modalElement = document.getElementById('payslipModal');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    
    // Ensure highest z-index
    $(modalElement).css({
        'z-index': '10063'
    });
    
    // Update backdrop z-index
    $('.modal-backdrop').css('z-index', '10062');
    
    modal.show();
    
    // Additional z-index boost after modal is shown
    setTimeout(function() {
        $(modalElement).css('z-index', '10063');
        $('.modal-backdrop').last().css('z-index', '10062');
    }, 100);
    
    // Use Laravel route helper
    const payslipUrl = `{{ route('payroll.payslip', ['payrollItem' => ':id']) }}`.replace(':id', payrollItemId);
    console.log('Requesting payslip URL:', payslipUrl);
    console.log('PayrollItem ID:', payrollItemId);
    
    $.ajax({
        url: payslipUrl,
        type: 'GET',
        dataType: 'json',
        cache: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        timeout: 30000,
        beforeSend: function(xhr) {
            console.log('AJAX request starting...');
            // Add loading indicator
            $('#payslipContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading payslip... Please wait.</p>
                </div>
            `);
        },
        success: function(response, status, xhr) {
            console.log('AJAX Success - Status:', status);
            console.log('Payslip response received:', response);
            console.log('Response type:', typeof response);
            console.log('Response keys:', response ? Object.keys(response) : 'null');
            
            try {
                if (!response) {
                    throw new Error('No response received from server');
                }
                
                if (!response.success) {
                    throw new Error(response.message || 'Server returned unsuccessful response');
                }
                
                if (!response.payslip) {
                    throw new Error('Payslip data not found in response');
                }
                const payslip = response.payslip;
                const payroll = payslip.payroll || {};
                const employee = payslip.employee || {};
                
                // Calculate totals
                const gross = (parseFloat(payslip.basic_salary || 0) + 
                              parseFloat(payslip.overtime_amount || 0) + 
                              parseFloat(payslip.bonus_amount || 0) + 
                              parseFloat(payslip.allowance_amount || 0));
                
                const deductions = (parseFloat(payslip.nssf_amount || 0) + 
                                   parseFloat(payslip.nhif_amount || 0) + 
                                   parseFloat(payslip.heslb_amount || 0) + 
                                   parseFloat(payslip.paye_amount || 0) + 
                                   parseFloat(payslip.deduction_amount || 0));
                
                // Format currency
                const formatCurrency = (amount) => {
                    const num = parseFloat(amount || 0);
                    return 'TZS ' + num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                };
                
                // Format date
                const formatDate = (dateStr) => {
                    if (!dateStr) return 'N/A';
                    try {
                        const date = new Date(dateStr + 'T00:00:00');
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format period
                const formatPeriod = (period) => {
                    if (!period) return 'N/A';
                    try {
                        const date = new Date(period + '-01');
                        return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                    } catch (e) {
                        return period;
                    }
                };
                
                // Build Advanced HTML
                let html = `
                    <!-- Payroll Header Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="bx bx-calendar me-2 text-primary"></i>Payroll Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Pay Period</small>
                                        <h6 class="mb-0 text-primary"><strong>${formatPeriod(payroll.pay_period)}</strong></h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-white d-block mb-1">Pay Date</small>
                                        <h6 class="mb-0 text-white"><strong>${formatDate(payroll.pay_date)}</strong></h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Status</small>
                                        <span class="badge bg-label-${payroll.status === 'paid' ? 'success' : payroll.status === 'approved' ? 'info' : 'warning'} fs-6">${payroll.status ? payroll.status.charAt(0).toUpperCase() + payroll.status.slice(1) : 'N/A'}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Employee</small>
                                        <h6 class="mb-0"><strong>${employee.name || 'N/A'}</strong></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>EARNINGS</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount (TZS)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><i class="bx bx-money me-2 text-muted"></i>Basic Salary</td>
                                            <td class="text-end"><strong class="text-success">${formatCurrency(payslip.basic_salary || 0)}</strong></td>
                                        </tr>
                                        ${parseFloat(payslip.overtime_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-time me-2 text-muted"></i>Overtime Pay ${payslip.overtime_hours ? '<small class="text-muted">(' + payslip.overtime_hours + ' hrs)</small>' : ''}</td>
                                            <td class="text-end"><strong class="text-success">+ ${formatCurrency(payslip.overtime_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.bonus_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-gift me-2 text-muted"></i>Bonus & Incentives</td>
                                            <td class="text-end"><strong class="text-success">+ ${formatCurrency(payslip.bonus_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.allowance_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-coin-stack me-2 text-muted"></i>Allowances</td>
                                            <td class="text-end"><strong class="text-success">+ ${formatCurrency(payslip.allowance_amount)}</strong></td>
                                        </tr>` : ''}
                                        <tr class="table-success">
                                            <td><strong><i class="bx bx-calculator me-2"></i>TOTAL GROSS SALARY</strong></td>
                                            <td class="text-end"><strong class="fs-5">${formatCurrency(gross)}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Deductions Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0 text-white"><i class="bx bx-trending-down me-2"></i>DEDUCTIONS</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount (TZS)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${parseFloat(payslip.paye_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-receipt me-2 text-muted"></i>PAYE Tax</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.paye_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.nssf_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-shield me-2 text-muted"></i>NSSF Contribution</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.nssf_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.nhif_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-plus-medical me-2 text-muted"></i>NHIF Contribution</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.nhif_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.heslb_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-book me-2 text-muted"></i>HESLB Loan</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.heslb_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.wcf_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-buildings me-2 text-muted"></i>WCF Contribution</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.wcf_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.deduction_amount || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-minus-circle me-2 text-muted"></i>Other Deductions</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.deduction_amount)}</strong></td>
                                        </tr>` : ''}
                                        ${parseFloat(payslip.other_deductions || 0) > 0 ? `
                                        <tr>
                                            <td><i class="bx bx-minus-circle me-2 text-muted"></i>Additional Deductions</td>
                                            <td class="text-end"><strong class="text-danger">- ${formatCurrency(payslip.other_deductions)}</strong></td>
                                        </tr>` : ''}
                                        <tr class="table-danger">
                                            <td><strong><i class="bx bx-calculator me-2"></i>TOTAL DEDUCTIONS</strong></td>
                                            <td class="text-end"><strong class="fs-5 text-danger">- ${formatCurrency(deductions)}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Net Salary Highlight Card -->
                    <div class="card border-0 shadow-lg mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <div class="card-body text-center text-white p-4">
                            <h6 class="mb-3 text-white"><i class="bx bx-money me-2"></i>NET SALARY PAYABLE</h6>
                            <h1 class="mb-3 fw-bold text-white" style=" color:white; font-size: 2.5rem; ">${formatCurrency(payslip.net_salary || 0)}</h1>
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <small class="text-white-50 d-block">Gross Salary</small>
                                    <strong class="fs-5">${formatCurrency(gross)}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-white-50 d-block">Total Deductions</small>
                                    <strong class="fs-5">- ${formatCurrency(deductions)}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-white-50 d-block">Net Pay</small>
                                    <strong class="fs-5">${formatCurrency(payslip.net_salary || 0)}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Breakdown -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3"><i class="bx bx-info-circle me-2"></i>Quick Summary</h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Gross Earnings:</span>
                                        <strong class="text-success">${formatCurrency(gross)}</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Deductions:</span>
                                        <strong class="text-danger">- ${formatCurrency(deductions)}</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-bold">Net Pay:</span>
                                        <strong class="text-primary fs-5">${formatCurrency(payslip.net_salary || 0)}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3"><i class="bx bx-user me-2"></i>Employee Details</h6>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Employee ID</small>
                                        <strong>${employee.employee_id || employee.id || 'N/A'}</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Department</small>
                                        <strong>${employee.department || 'N/A'}</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div>
                                        <small class="text-muted d-block">Position</small>
                                        <strong>${employee.position || 'N/A'}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#payslipContent').html(html);
                
                // Set PDF download link and show it
                $('#downloadPayslipPdf').attr('href', `/payroll/payslip/${payrollItemId}/pdf`).show();
                
                // Ensure modal is at the front
                $('#payslipModal').css('z-index', '10062');
                $('.modal-backdrop').css('z-index', '10061');
                
                console.log('Payslip HTML rendered successfully');
                
                // Smooth scroll to top of modal content
                $('#payslipContent').scrollTop(0);
            } catch (e) {
                console.error('Error processing payslip response:', e);
                $('#payslipContent').html(`
                    <div class="alert alert-danger">
                        <h6><i class="bx bx-error-circle me-2"></i><strong>Error Processing Response</strong></h6>
                        <p class="mb-2">${e.message || 'Failed to process payslip data'}</p>
                        <pre class="small bg-light p-2 mt-2" style="max-height: 200px; overflow: auto;">${JSON.stringify(response, null, 2)}</pre>
                        <button class="btn btn-sm btn-primary mt-2" onclick="viewPayslip(${payrollItemId})">
                            <i class="bx bx-refresh me-1"></i>Try Again
                        </button>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Payslip AJAX error:', {
                status: status,
                error: error,
                httpStatus: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                responseJSON: xhr.responseJSON,
                readyState: xhr.readyState
            });
            
            const response = xhr.responseJSON;
            let errorMsg = 'Failed to load payslip details.';
            
            if (status === 'timeout') {
                errorMsg = 'Request timed out after 30 seconds. Please try again.';
            } else if (status === 'parsererror') {
                errorMsg = 'Invalid response from server. Please contact support.';
                console.error('Response text:', xhr.responseText);
            } else if (xhr.status === 0) {
                errorMsg = 'Network error. Please check your connection and try again.';
            } else if (xhr.status === 404) {
                errorMsg = 'Payslip not found. It may have been deleted.';
            } else if (xhr.status === 403) {
                errorMsg = 'You do not have permission to view this payslip.';
            } else if (xhr.status === 500) {
                errorMsg = 'Server error occurred. Please contact support.';
            } else if (response && response.message) {
                errorMsg = response.message;
            } else if (xhr.status) {
                errorMsg = `Failed to load payslip details. (HTTP ${xhr.status})`;
            }
            
            $('#payslipContent').html(`
                <div class="alert alert-danger">
                    <h6><i class="bx bx-error-circle me-2"></i><strong>Error Loading Payslip</strong></h6>
                    <p class="mb-2">${errorMsg}</p>
                    <small class="text-muted">Status: ${xhr.status || 'Unknown'} | Error: ${error || 'Unknown'}</small>
                    <hr>
                    <button class="btn btn-sm btn-primary me-2" onclick="viewPayslip(${payrollItemId})">
                        <i class="bx bx-refresh me-1"></i>Try Again
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#payslipModal').modal('hide')">
                        <i class="bx bx-x me-1"></i>Close
                    </button>
                </div>
            `);
        },
        complete: function(xhr, status) {
            console.log('AJAX Complete - Status:', status, 'HTTP Status:', xhr.status);
        }
    });
}

// PDF Download handler
$(document).on('click', '#downloadPayslipPdf', function(e) {
    e.preventDefault();
    const href = $(this).attr('href');
    if (href && href !== '#') {
        window.open(href, '_blank');
    }
});
</script>
@endpush

