@if(isset($vouchers) && $vouchers->count() > 0)
    <div class="row g-3">
        @foreach($vouchers as $voucher)
            @php
                $isDirect = $voucher->created_by === $voucher->accountant_id && 
                           $voucher->accountant_id !== null && 
                           $voucher->accountant_verified_at !== null;
                $statusClass = match($voucher->status) {
                    'pending_accountant' => 'pending',
                    'pending_hod' => 'pending',
                    'pending_ceo' => 'pending',
                    'approved_for_payment' => 'approved',
                    'paid' => 'paid',
                    'rejected' => 'rejected',
                    default => 'pending'
                };
            @endphp
            <div class="col-12">
                <div class="voucher-card {{ $statusClass }} {{ $isDirect ? 'direct' : '' }}" 
                     onclick="viewVoucherDetails({{ $voucher->id }})">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="bx bx-file me-2"></i>
                                        Voucher #{{ $voucher->voucher_no }}
                                        @if($isDirect)
                                            <span class="badge bg-warning badge-custom ms-2">Direct</span>
                                        @endif
                                    </h6>
                                    <div class="text-muted small mb-2">
                                        <i class="bx bx-user me-1"></i>{{ $voucher->creator->name ?? 'N/A' }}
                                        <span class="mx-2">•</span>
                                        <i class="bx bx-calendar me-1"></i>{{ $voucher->date ? $voucher->date->format('M d, Y') : 'N/A' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Payee:</strong> {{ $voucher->payee }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Purpose:</strong> {{ Str::limit($voucher->purpose, 100) }}
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-{{ $voucher->status_badge_class ?? 'secondary' }} badge-custom">
                                            {{ strtoupper(str_replace('_', ' ', $voucher->status)) }}
                                        </span>
                                        <span class="text-muted small">
                                            <i class="bx bx-money me-1"></i>
                                            <strong>TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="mb-2">
                                <div class="progress progress-mini mb-1">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $voucher->progress_percentage ?? 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ (int)($voucher->progress_percentage ?? 0) }}% Complete</small>
                            </div>
                            <div class="btn-group btn-group-sm" role="group" onclick="event.stopPropagation();">
                                <a href="{{ route('petty-cash.show', $voucher->id) }}" class="btn btn-outline-primary">
                                    <i class="bx bx-show"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    @if(method_exists($vouchers, 'links'))
        <div class="mt-4">
            {{ $vouchers->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bx bx-inbox text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">No Vouchers Found</h5>
        <p class="text-muted">There are no vouchers matching your current filter.</p>
    </div>
@endif

