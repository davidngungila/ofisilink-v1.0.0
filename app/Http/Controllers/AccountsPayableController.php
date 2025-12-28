<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ActivityLogService;

class AccountsPayableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Vendors Management
     */
    public function vendors(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = Vendor::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Apply additional filters from request
        if ($request->has('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->has('payment_terms')) {
            $termsRange = $request->payment_terms;
            if ($termsRange === '0-15') {
                $query->where('payment_terms_days', '>=', 0)->where('payment_terms_days', '<=', 15);
            } elseif ($termsRange === '16-30') {
                $query->where('payment_terms_days', '>=', 16)->where('payment_terms_days', '<=', 30);
            } elseif ($termsRange === '31-60') {
                $query->where('payment_terms_days', '>=', 31)->where('payment_terms_days', '<=', 60);
            } elseif ($termsRange === '60+') {
                $query->where('payment_terms_days', '>', 60);
            }
        }

        $vendors = $query->orderBy('name')->get();
        
        // Apply outstanding filter (calculated field)
        if ($request->has('outstanding')) {
            $outstandingFilter = $request->outstanding;
            $vendors = $vendors->filter(function($vendor) use ($outstandingFilter) {
                $outstanding = (float)($vendor->total_outstanding ?? 0);
                if ($outstandingFilter === 'zero') {
                    return $outstanding == 0;
                } elseif ($outstandingFilter === 'low') {
                    return $outstanding > 0 && $outstanding <= 100000;
                } elseif ($outstandingFilter === 'medium') {
                    return $outstanding > 100000 && $outstanding <= 1000000;
                } elseif ($outstandingFilter === 'high') {
                    return $outstanding > 1000000;
                }
                return true;
            });
        }

        $accounts = ChartOfAccount::where('type', 'Liability')->where('category', 'Current Liability')->active()->get();
        
        // Fetch GL Accounts and Cash Boxes for reference details
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportVendorsPdf($vendors, $request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportVendorsExcel($vendors, $request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getVendorsData($request);
        }

        // Paginate for view - use collection's paginate helper
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 20;
        $items = $vendors->forPage($currentPage, $perPage)->values();
        $paginatedVendors = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $vendors->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('modules.accounting.accounts-payable.vendors', compact('vendors', 'paginatedVendors', 'accounts', 'glAccounts', 'cashBoxes'));
    }

    /**
     * Get Vendors Data (AJAX)
     */
    public function getVendorsData(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive',
                'currency' => 'nullable|string|in:TZS,USD,EUR',
                'outstanding' => 'nullable|string|in:zero,low,medium,high',
                'payment_terms' => 'nullable|string|in:0-15,16-30,31-60,60+',
                'q' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:5|max:100'
            ]);

            $query = Vendor::query();

            // Use 'q' for search if provided, otherwise use 'search'
            $searchTerm = $validated['q'] ?? $validated['search'] ?? '';
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('vendor_code', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%")
                      ->orWhere('mobile', 'like', "%{$searchTerm}%")
                      ->orWhere('tax_id', 'like', "%{$searchTerm}%")
                      ->orWhere('address', 'like', "%{$searchTerm}%");
                });
            }

            if (!empty($validated['status'])) {
                $query->where('is_active', $validated['status'] === 'active');
            }

            if (!empty($validated['currency'])) {
                $query->where('currency', $validated['currency']);
            }

            if (!empty($validated['payment_terms'])) {
                $termsRange = $validated['payment_terms'];
                if ($termsRange === '0-15') {
                    $query->where('payment_terms_days', '>=', 0)->where('payment_terms_days', '<=', 15);
                } elseif ($termsRange === '16-30') {
                    $query->where('payment_terms_days', '>=', 16)->where('payment_terms_days', '<=', 30);
                } elseif ($termsRange === '31-60') {
                    $query->where('payment_terms_days', '>=', 31)->where('payment_terms_days', '<=', 60);
                } elseif ($termsRange === '60+') {
                    $query->where('payment_terms_days', '>', 60);
                }
            }

            // Apply outstanding filter after getting vendors (since it's a calculated field)
            $allVendors = $query->get();
            
            // Filter by outstanding range if specified
            if (!empty($validated['outstanding'])) {
                $outstandingFilter = $validated['outstanding'];
                $allVendors = $allVendors->filter(function($vendor) use ($outstandingFilter) {
                    $outstanding = (float)($vendor->total_outstanding ?? 0);
                    if ($outstandingFilter === 'zero') {
                        return $outstanding == 0;
                    } elseif ($outstandingFilter === 'low') {
                        return $outstanding > 0 && $outstanding <= 100000;
                    } elseif ($outstandingFilter === 'medium') {
                        return $outstanding > 100000 && $outstanding <= 1000000;
                    } elseif ($outstandingFilter === 'high') {
                        return $outstanding > 1000000;
                    }
                    return true;
                });
            }
            
            // Calculate totals
            $totalVendors = $allVendors->count();
            $activeVendors = $allVendors->where('is_active', true)->count();
            $totalOutstanding = round($allVendors->sum('total_outstanding'), 2);
            $totalOverdue = round($allVendors->sum('overdue_amount'), 2);

            // Format vendors
            $formattedVendors = $allVendors->map(function($vendor) {
                return [
                    'id' => $vendor->id,
                    'vendor_code' => $vendor->vendor_code,
                    'name' => $vendor->name,
                    'contact_person' => $vendor->contact_person ?? '-',
                    'email' => $vendor->email ?? '-',
                    'phone' => $vendor->phone ?? $vendor->mobile ?? '-',
                    'outstanding' => (float)($vendor->total_outstanding ?? 0),
                    'overdue' => (float)($vendor->overdue_amount ?? 0),
                    'is_active' => $vendor->is_active,
                    'currency' => $vendor->currency ?? 'TZS',
                    'credit_limit' => (float)($vendor->credit_limit ?? 0),
                    'payment_terms' => $vendor->payment_terms ?? 30,
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedVendors->count();
            $paginatedVendors = $formattedVendors->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_vendors' => $totalVendors,
                    'active_vendors' => $activeVendors,
                    'total_outstanding' => $totalOutstanding,
                    'total_overdue' => $totalOverdue,
                    'count' => $totalEntries
                ],
                'vendors' => $paginatedVendors,
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (\Exception $e) {
            \Log::error('Vendors data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading vendors data: ' . $e->getMessage(),
                'summary' => [
                    'total_vendors' => 0,
                    'active_vendors' => 0,
                    'total_outstanding' => 0,
                    'total_overdue' => 0,
                    'count' => 0
                ],
                'vendors' => []
            ], 500);
        }
    }

    /**
     * Convert payment terms days to enum value
     */
    private function getPaymentTermsEnum($days)
    {
        $days = (int) $days;
        if ($days <= 15) {
            return 'Net 15';
        } elseif ($days <= 30) {
            return 'Net 30';
        } elseif ($days <= 45) {
            return 'Net 45';
        } elseif ($days <= 60) {
            return 'Net 60';
        } else {
            return 'Custom';
        }
    }

    public function storeVendor(Request $request)
    {
        if ($request->has('generate_code')) {
            return response()->json(['code' => Vendor::generateCode()]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        try {
            $paymentTermsDays = (int)($request->payment_terms ?? $request->payment_terms_days ?? 30);
            $paymentTerms = $this->getPaymentTermsEnum($paymentTermsDays);

            $vendor = Vendor::create([
                'vendor_code' => $request->vendor_code ?? Vendor::generateCode(),
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'city' => $request->city,
                'tax_id' => $request->tax_id,
                'account_id' => $request->account_id,
                'credit_limit' => $request->credit_limit ?? 0,
                'payment_terms' => $paymentTerms,
                'payment_terms_days' => $paymentTermsDays,
                'is_active' => $request->has('is_active'),
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Send success notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notify(
                Auth::id(),
                "Vendor '{$vendor->name}' has been created successfully.",
                route('modules.accounting.ap.vendors', ['vendor_id' => $vendor->id])
            );

            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully',
                'vendor' => $vendor
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating vendor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showVendor($id)
    {
        $vendor = Vendor::with(['bills' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'vendor' => $vendor
        ]);
    }

    public function updateVendor(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        try {
            $paymentTermsDays = (int)($request->payment_terms ?? $request->payment_terms_days ?? $vendor->payment_terms_days ?? 30);
            $paymentTerms = $this->getPaymentTermsEnum($paymentTermsDays);

            $vendor->update([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'city' => $request->city,
                'tax_id' => $request->tax_id,
                'credit_limit' => $request->credit_limit ?? $vendor->credit_limit,
                'payment_terms' => $paymentTerms,
                'payment_terms_days' => $paymentTermsDays,
                'is_active' => $request->has('is_active'),
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Send success notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notify(
                Auth::id(),
                "Vendor '{$vendor->name}' has been updated successfully.",
                route('modules.accounting.ap.vendors', ['vendor_id' => $vendor->id])
            );

            return response()->json([
                'success' => true,
                'message' => 'Vendor updated successfully',
                'vendor' => $vendor
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating vendor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bills Management
     */
    public function bills(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = Bill::with(['vendor', 'items']);

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('bill_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('bill_date', '<=', $request->date_to);
        }

        $bills = $query->orderBy('bill_date', 'desc')->paginate(20);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $accounts = ChartOfAccount::where('type', 'Expense')->active()->get();

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportBillsPdf($query->get());
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportBillsExcel($query->get(), $request);
        }

        // Check if AJAX data request (legacy support - now uses separate route)
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getBillsData($request);
        }

        // Fetch GL Accounts and Cash Boxes for reference details
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        return view('modules.accounting.accounts-payable.bills', compact('bills', 'vendors', 'accounts', 'glAccounts', 'cashBoxes'));
    }

    /**
     * Get Bills Data (AJAX)
     */
    public function getBillsData(Request $request)
    {
        try {
            // Only get filter parameters, ignore form submission fields
            $filterData = [];
            $filterKeys = ['vendor_id', 'status', 'date_from', 'date_to', 'q', 'page', 'per_page'];
            
            foreach ($filterKeys as $key) {
                $value = $request->input($key);
                // Convert empty strings to null
                $filterData[$key] = ($value === '' || $value === null) ? null : $value;
            }
            
            // Validate only filter data
            $validator = \Validator::make($filterData, [
                'vendor_id' => 'nullable|integer',
                'status' => 'nullable|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'q' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:5|max:100'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $validated = $validator->validated();

            $query = Bill::with(['vendor', 'items']);

            if (!empty($validated['vendor_id'])) {
                $query->where('vendor_id', $validated['vendor_id']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('bill_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('bill_date', '<=', $validated['date_to']);
            }

            $searchTerm = $validated['q'] ?? '';
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('bill_no', 'like', "%{$searchTerm}%")
                      ->orWhere('reference_no', 'like', "%{$searchTerm}%")
                      ->orWhereHas('vendor', function($vendorQuery) use ($searchTerm) {
                          $vendorQuery->where('name', 'like', "%{$searchTerm}%")
                                     ->orWhere('vendor_code', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Get all bills for summary calculation
            $allBills = $query->get();
            
            // Calculate totals
            $totalBills = $allBills->count();
            $totalAmount = round($allBills->sum('total_amount'), 2);
            $totalPaid = round($allBills->sum('paid_amount'), 2);
            $totalBalance = round($allBills->sum('balance'), 2);
            $totalOverdue = round($allBills->filter(fn($b) => $b->isOverdue())->sum('balance'), 2);

            // Format bills
            $formattedBills = $allBills->map(function($bill) {
                return [
                    'id' => $bill->id,
                    'bill_no' => $bill->bill_no,
                    'vendor_name' => $bill->vendor->name ?? 'N/A',
                    'vendor_id' => $bill->vendor_id,
                    'bill_date' => $bill->bill_date->format('Y-m-d'),
                    'bill_date_display' => $bill->bill_date->format('d M Y'),
                    'due_date' => $bill->due_date->format('Y-m-d'),
                    'due_date_display' => $bill->due_date->format('d M Y'),
                    'is_overdue' => $bill->isOverdue(),
                    'total_amount' => (float)$bill->total_amount,
                    'paid_amount' => (float)$bill->paid_amount,
                    'balance' => (float)$bill->balance,
                    'status' => $bill->status,
                    'reference_no' => $bill->reference_no ?? '-',
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedBills->count();
            $paginatedBills = $formattedBills->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_bills' => $totalBills,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'total_overdue' => $totalOverdue,
                    'count' => $totalEntries
                ],
                'bills' => $paginatedBills,
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (\Exception $e) {
            \Log::error('Bills data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading bills data: ' . $e->getMessage(),
                'summary' => [
                    'total_bills' => 0,
                    'total_amount' => 0,
                    'total_paid' => 0,
                    'total_balance' => 0,
                    'total_overdue' => 0,
                    'count' => 0
                ],
                'bills' => []
            ], 500);
        }
    }

    public function showBill($id)
    {
        $bill = Bill::with(['vendor', 'items', 'payments'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'bill_no' => $bill->bill_no,
                'vendor_id' => $bill->vendor_id,
                'vendor' => $bill->vendor,
                'bill_date' => $bill->bill_date->format('Y-m-d'),
                'due_date' => $bill->due_date->format('Y-m-d'),
                'reference_no' => $bill->reference_no,
                'subtotal' => $bill->subtotal,
                'tax_amount' => $bill->tax_amount,
                'discount_amount' => $bill->discount_amount,
                'total_amount' => $bill->total_amount,
                'paid_amount' => $bill->paid_amount,
                'balance' => $bill->balance,
                'status' => $bill->status,
                'notes' => $bill->notes,
                'terms' => $bill->terms,
                'items' => $bill->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'account_id' => $item->account_id,
                        'line_total' => $item->line_total,
                    ];
                }),
            ]
        ]);
    }

    public function updateBill(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);
        
        if ($bill->status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit a paid bill'
            ], 400);
        }

        // Allow status-only updates for approval/rejection
        if ($request->has('status') && count($request->only(['status', 'notes'])) === count($request->all())) {
            $validated = $request->validate([
                'status' => 'required|in:Draft,Pending,Partially Paid,Paid,Cancelled,Overdue',
                'notes' => 'nullable|string',
            ]);
            
            $bill->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Bill status updated successfully',
                'bill' => $bill->load('vendor', 'items')
            ]);
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Delete existing items
            $bill->items()->delete();

            // Recalculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = $validated['discount_amount'] ?? $request->discount_amount ?? 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $itemTax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
                $subtotal += $lineTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            $newBalance = $totalAmount - $bill->paid_amount;

            // Update bill
            $bill->update([
                'vendor_id' => $validated['vendor_id'],
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'],
                'reference_no' => $request->reference_no ?? '',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance' => max(0, $newBalance),
                'notes' => $request->notes ?? '',
                'terms' => $request->terms ?? '',
                'updated_by' => Auth::id(),
            ]);

            // Create new items
            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $itemTax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;

                BillItem::create([
                    'bill_id' => $bill->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $lineTotal + $itemTax,
                    'account_id' => $item['account_id'] ?? null,
                ]);
            }

            $bill->updateStatus();

            DB::commit();

            // Send success notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notify(
                Auth::id(),
                "Bill '{$bill->bill_no}' has been updated successfully.",
                route('modules.accounting.ap.bills', ['bill_id' => $bill->id])
            );

            return response()->json([
                'success' => true,
                'message' => 'Bill updated successfully',
                'bill' => $bill->load('items', 'vendor')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bill: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating bill: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportBillPdf($id)
    {
        try {
            $bill = Bill::with(['vendor', 'items'])->findOrFail($id);
            
            $data = [
                'bill' => $bill,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.bill', $data);
            $pdf->setPaper('A4', 'portrait');
            $filename = 'Bill_' . $bill->bill_no . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Bill PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    private function exportBillsPdf($bills)
    {
        try {
            $data = [
                'bills' => $bills,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
                'filters' => request()->only(['vendor_id', 'status', 'date_from', 'date_to']),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.bills-list', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Bills_Report_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Bills PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Bills Excel
     */
    private function exportBillsExcel($bills, $request)
    {
        try {
            $filename = 'Bills_List_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($bills) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Bill No', 'Vendor', 'Bill Date', 'Due Date', 
                    'Total Amount', 'Paid Amount', 'Balance', 'Status', 'Reference'
                ]);

                // Data rows
                foreach ($bills as $bill) {
                    fputcsv($file, [
                        $bill->bill_no ?? '',
                        $bill->vendor->name ?? 'N/A',
                        $bill->bill_date->format('Y-m-d'),
                        $bill->due_date->format('Y-m-d'),
                        number_format($bill->total_amount ?? 0, 2),
                        number_format($bill->paid_amount ?? 0, 2),
                        number_format($bill->balance ?? 0, 2),
                        $bill->status ?? '',
                        $bill->reference_no ?? '',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Bills Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    public function storeBill(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'bill_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:bill_date',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = $request->discount_amount ?? 0;

            foreach ($request->items as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $itemTax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
                $subtotal += $lineTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $bill = Bill::create([
                'bill_no' => Bill::generateBillNo(),
                'vendor_id' => $validated['vendor_id'],
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'],
                'reference_no' => $request->reference_no ?? '',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'Pending',
                'notes' => $request->notes ?? '',
                'terms' => $request->terms ?? '',
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $itemTax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;

                BillItem::create([
                    'bill_id' => $bill->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $lineTotal + $itemTax,
                    'account_id' => $item['account_id'] ?? null,
                ]);
            }

            // Post to General Ledger - Double Entry Bookkeeping
            $vendor = Vendor::find($request->vendor_id);
            
            // Get Accounts Payable account (Liability - what we owe)
            $apAccount = $vendor->account_id ?? ChartOfAccount::where('code', 'AP')->first()?->id;
            
            if (!$apAccount) {
                // Create default AP account if it doesn't exist
                $apAccount = ChartOfAccount::firstOrCreate(
                    ['code' => 'AP'],
                    [
                        'name' => 'Accounts Payable',
                        'type' => 'Liability',
                        'category' => 'Current Liability',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Get Expense account from bill items or create default
            $expenseAccount = null;
            
            // Check if any bill items have account_id specified
            foreach ($validated['items'] as $item) {
                if (!empty($item['account_id'])) {
                    $expenseAccount = $item['account_id'];
                    break; // Use first item's account
                }
            }
            
            if (!$expenseAccount) {
                // Find or create default Purchase Expense account
                $expenseAccount = ChartOfAccount::where('code', 'PURCHASE')
                    ->orWhere('code', 'EXPENSE')
                    ->orWhere('name', 'like', '%Purchase Expense%')
                    ->where('type', 'Expense')
                    ->first()?->id;
                
                if (!$expenseAccount) {
                    $expenseAccount = ChartOfAccount::firstOrCreate(
                        ['code' => 'PURCHASE'],
                        [
                            'name' => 'Purchase Expense',
                            'type' => 'Expense',
                            'category' => 'Operating Expense',
                            'is_active' => true,
                        ]
                    )->id;
                }
            }
            
            // Ensure both accounts exist before creating entries
            if (!$apAccount || !$expenseAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Accounts Payable and Expense accounts.');
            }
            
            // Debit: Expense Account (increases expense - we incurred cost)
            GeneralLedger::create([
                'account_id' => $expenseAccount,
                'transaction_date' => $bill->bill_date,
                'reference_type' => 'Bill',
                'reference_id' => $bill->id,
                'reference_no' => $bill->bill_no,
                'type' => 'Debit',
                'amount' => $totalAmount,
                'description' => "Expense from bill {$bill->bill_no} - {$vendor->name}",
                'source' => 'Purchase',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Accounts Payable (increases liability - we owe vendor)
            GeneralLedger::create([
                'account_id' => $apAccount,
                'transaction_date' => $bill->bill_date,
                'reference_type' => 'Bill',
                'reference_id' => $bill->id,
                'reference_no' => $bill->bill_no,
                'type' => 'Credit',
                'amount' => $totalAmount,
                'description' => "Bill from {$vendor->name}",
                'source' => 'Purchase',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logCreated($bill, "Created bill {$bill->bill_no} for TZS " . number_format($bill->total_amount, 2), [
                'bill_no' => $bill->bill_no,
                'vendor_id' => $bill->vendor_id,
                'vendor_name' => $bill->vendor->name ?? 'N/A',
                'total_amount' => $bill->total_amount,
                'bill_date' => $bill->bill_date,
            ]);

            // Send success notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notify(
                Auth::id(),
                "Bill '{$bill->bill_no}' has been created successfully. Amount: TZS " . number_format($bill->total_amount, 2),
                route('modules.accounting.ap.bills', ['bill_id' => $bill->id])
            );

            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully',
                'bill' => $bill->load('items', 'vendor')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bill: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bill Payments
     */
    public function billPayments(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getPaymentsData($request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            $query = BillPayment::with(['bill.vendor', 'bankAccount', 'creator']);
            // Apply filters for export
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('payment_no', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhereHas('bill', function($billQuery) use ($search) {
                          $billQuery->where('bill_no', 'like', "%{$search}%")
                                    ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                                        $vendorQuery->where('name', 'like', "%{$search}%")
                                                    ->orWhere('vendor_code', 'like', "%{$search}%");
                                    });
                      });
                });
            }
            return $this->exportPaymentsExcel($query->get(), $request);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            $query = BillPayment::with(['bill.vendor', 'bankAccount', 'creator']);
            // Apply filters for export
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('payment_no', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhereHas('bill', function($billQuery) use ($search) {
                          $billQuery->where('bill_no', 'like', "%{$search}%")
                                    ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                                        $vendorQuery->where('name', 'like', "%{$search}%")
                                                    ->orWhere('vendor_code', 'like', "%{$search}%");
                                    });
                      });
                });
            }
            return $this->exportPaymentsPdf($query->get());
        }

        $payments = BillPayment::with(['bill.vendor', 'bankAccount', 'creator'])->orderBy('payment_date', 'desc')->paginate(20);
        $bills = Bill::whereIn('status', ['Pending', 'Partially Paid', 'Overdue'])->with('vendor')->get();
        
        // Get bank accounts
        $bankAccounts = \App\Models\BankAccount::all();
        $vendors = \App\Models\Vendor::where('is_active', true)->orderBy('name')->get();

        return view('modules.accounting.accounts-payable.payments', compact('payments', 'bills', 'bankAccounts', 'vendors'));
    }

    /**
     * Get Payments Data (AJAX)
     */
    public function getPaymentsData(Request $request)
    {
        try {
            // Only get filter parameters, ignore form submission fields
            $filterParams = $request->only([
                'bill_id', 'vendor_id', 'payment_method', 'date_from', 'date_to', 'q', 'page', 'per_page'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'bill_id' => 'nullable|exists:bills,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'payment_method' => 'nullable|in:Cash,Bank Transfer,Cheque,Mobile Money,Credit Card,Other',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'q' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            $query = BillPayment::with(['bill.vendor', 'bankAccount', 'creator']);

            // Apply filters
            if (!empty($validated['bill_id'])) {
                $query->where('bill_id', $validated['bill_id']);
            }

            if (!empty($validated['vendor_id'])) {
                $query->whereHas('bill', function($q) use ($validated) {
                    $q->where('vendor_id', $validated['vendor_id']);
                });
            }

            if (!empty($validated['payment_method'])) {
                $query->where('payment_method', $validated['payment_method']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('payment_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('payment_date', '<=', $validated['date_to']);
            }

            // Search filter
            if (!empty($validated['q'])) {
                $searchTerm = $validated['q'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('payment_no', 'like', "%{$searchTerm}%")
                      ->orWhere('reference_no', 'like', "%{$searchTerm}%")
                      ->orWhereHas('bill', function($billQuery) use ($searchTerm) {
                          $billQuery->where('bill_no', 'like', "%{$searchTerm}%")
                                    ->orWhereHas('vendor', function($vendorQuery) use ($searchTerm) {
                                        $vendorQuery->where('name', 'like', "%{$searchTerm}%")
                                                    ->orWhere('vendor_code', 'like', "%{$searchTerm}%");
                                    });
                      });
                });
            }

            // Get all payments for summary calculation
            $allPayments = $query->get();
            
            // Calculate totals
            $totalPayments = $allPayments->count();
            $totalAmount = round($allPayments->sum('amount'), 2);
            $monthAmount = round($allPayments->filter(function($payment) {
                return $payment->payment_date && $payment->payment_date->isCurrentMonth();
            })->sum('amount'), 2);

            // Format payments
            $formattedPayments = $allPayments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'bill_no' => $payment->bill->bill_no ?? 'N/A',
                    'vendor_name' => $payment->bill->vendor->name ?? 'N/A',
                    'vendor_code' => $payment->bill->vendor->vendor_code ?? '',
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '',
                    'payment_date_display' => $payment->payment_date ? $payment->payment_date->format('d M Y') : '',
                    'amount' => (float)$payment->amount,
                    'payment_method' => $payment->payment_method,
                    'reference_no' => $payment->reference_no ?? '-',
                    'bank_account' => $payment->bankAccount ? $payment->bankAccount->name : null,
                    'bill_status' => $payment->bill->status ?? 'N/A',
                    'notes' => $payment->notes ?? null,
                    'created_by' => $payment->creator ? $payment->creator->name : 'N/A',
                    'created_at' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : '',
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedPayments->count();
            $paginatedPayments = $formattedPayments->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_payments' => $totalPayments,
                    'total_amount' => $totalAmount,
                    'month_amount' => $monthAmount,
                    'count' => $totalEntries
                ],
                'payments' => $paginatedPayments,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading payments data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading payments: ' . $e->getMessage(),
                'payments' => []
            ], 500);
        }
    }

    public function showPayment($id)
    {
        $payment = BillPayment::with(['bill.vendor', 'bankAccount', 'creator'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    public function exportPaymentPdf($id)
    {
        try {
            $payment = BillPayment::with(['bill.vendor', 'bankAccount'])->findOrFail($id);
            
            $data = [
                'payment' => $payment,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.bill-payment', $data);
            $pdf->setPaper('A4', 'portrait');
            $filename = 'Payment_' . $payment->payment_no . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Payment PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    private function exportPaymentsPdf($payments)
    {
        try {
            $data = [
                'payments' => $payments,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.bill-payments-list', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Bill_Payments_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Payments PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    private function exportPaymentsExcel($payments, $request)
    {
        try {
            $filename = 'Bill_Payments_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($payments) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Payment No', 'Bill No', 'Vendor', 'Payment Date', 
                    'Amount', 'Payment Method', 'Bank Account', 'Reference No', 
                    'Bill Status', 'Created By', 'Created At'
                ]);

                // Data rows
                foreach ($payments as $payment) {
                    fputcsv($file, [
                        $payment->payment_no ?? '',
                        $payment->bill->bill_no ?? 'N/A',
                        $payment->bill->vendor->name ?? 'N/A',
                        $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '',
                        number_format($payment->amount ?? 0, 2),
                        $payment->payment_method ?? '',
                        $payment->bank_account->name ?? '',
                        $payment->reference_no ?? '',
                        $payment->bill->status ?? 'N/A',
                        $payment->creator->name ?? 'System',
                        $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : '',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Payments Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    public function storeBillPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'bill_id' => 'required|exists:bills,id',
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:Cash,Bank Transfer,Cheque,Mobile Money,Credit Card,Other',
                'reference_no' => 'nullable|string|max:255',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $bill = Bill::findOrFail($validated['bill_id']);

            if ($validated['amount'] > $bill->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds bill balance'
                ], 400);
            }

            $payment = BillPayment::create([
                'payment_no' => BillPayment::generatePaymentNo(),
                'bill_id' => $bill->id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Update bill
            $bill->paid_amount += $validated['amount'];
            $bill->balance = $bill->total_amount - $bill->paid_amount;
            $bill->updateStatus();
            $bill->save();

            // Post to General Ledger - Double Entry Bookkeeping
            $vendor = $bill->vendor;
            
            // Get Accounts Payable account (Liability - what we owe)
            $apAccount = $vendor->account_id ?? ChartOfAccount::where('code', 'AP')->first()?->id;
            
            if (!$apAccount) {
                // Create default AP account if it doesn't exist
                $apAccount = ChartOfAccount::firstOrCreate(
                    ['code' => 'AP'],
                    [
                        'name' => 'Accounts Payable',
                        'type' => 'Liability',
                        'category' => 'Current Liability',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Get Cash/Bank account (Asset - what we're paying from)
            $cashBankAccount = null;
            
            // Try to get from selected bank account
            if (!empty($validated['bank_account_id'])) {
                $bankAccountModel = \App\Models\BankAccount::find($validated['bank_account_id']);
                if ($bankAccountModel && $bankAccountModel->account_id) {
                    $cashBankAccount = $bankAccountModel->account_id;
                }
            }
            
            // If not found, find based on payment method
            if (!$cashBankAccount) {
                if (in_array($validated['payment_method'], ['Cash'])) {
                    $cashBankAccount = ChartOfAccount::where('code', 'LIKE', '%CASH%')
                        ->where('type', 'Asset')
                        ->active()
                        ->first()?->id;
                } else {
                    $cashBankAccount = ChartOfAccount::where('code', 'LIKE', '%BANK%')
                        ->where('type', 'Asset')
                        ->active()
                        ->first()?->id;
                }
            }
            
            // Create default cash/bank account if not found
            if (!$cashBankAccount) {
                $accountCode = in_array($validated['payment_method'], ['Cash']) ? 'CASH' : 'BANK';
                $accountName = in_array($validated['payment_method'], ['Cash']) ? 'Cash on Hand' : 'Bank Account';
                $cashBankAccount = ChartOfAccount::firstOrCreate(
                    ['code' => $accountCode],
                    [
                        'name' => $accountName,
                        'type' => 'Asset',
                        'category' => 'Current Asset',
                        'is_active' => true,
                    ]
                )->id;
            }
            
            // Ensure both accounts exist before creating entries
            if (!$apAccount || !$cashBankAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Accounts Payable and Cash/Bank accounts.');
            }
            
            // Debit: Accounts Payable (reduces what we owe)
            GeneralLedger::create([
                'account_id' => $apAccount,
                'transaction_date' => $payment->payment_date,
                'reference_type' => 'BillPayment',
                'reference_id' => $payment->id,
                'reference_no' => $payment->payment_no,
                'type' => 'Debit',
                'amount' => $validated['amount'],
                'description' => "Payment for bill {$bill->bill_no}",
                'source' => 'Payment',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Cash/Bank Account (reduces our cash/bank balance)
            GeneralLedger::create([
                'account_id' => $cashBankAccount,
                'transaction_date' => $payment->payment_date,
                'reference_type' => 'BillPayment',
                'reference_id' => $payment->id,
                'reference_no' => $payment->payment_no,
                'type' => 'Credit',
                'amount' => $validated['amount'],
                'description' => "Payment for bill {$bill->bill_no}",
                'source' => 'Payment',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logAction('bill_payment', "Processed payment of TZS " . number_format($validated['amount'], 2) . " for bill {$bill->bill_no}", $payment, [
                'bill_no' => $bill->bill_no,
                'payment_no' => $payment->payment_no,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'bill_balance' => $bill->balance,
            ]);

            // Send success notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notify(
                Auth::id(),
                "Payment of TZS " . number_format($request->amount, 2) . " recorded for Bill '{$bill->bill_no}' successfully.",
                route('modules.accounting.ap.bills', ['bill_id' => $bill->id])
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * A/P Aging Report
     */
    public function agingReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $asOfDate = $request->date ?? now()->format('Y-m-d');
        $asOfDateTime = \Carbon\Carbon::parse($asOfDate);

        $query = Bill::with('vendor')
            ->whereIn('status', ['Pending', 'Partially Paid', 'Overdue'])
            ->where('balance', '>', 0);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('vendor_code', 'like', "%{$search}%");
                  });
            });
        }

        // Vendor filter
        if ($request->has('vendor_id') && !empty($request->vendor_id)) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $bills = $query->get()
            ->map(function($bill) use ($asOfDateTime) {
                $daysPastDue = $asOfDateTime->diffInDays($bill->due_date, false);
                if ($daysPastDue < 0) {
                    $daysPastDue = abs($daysPastDue);
                } else {
                    $daysPastDue = 0;
                }
                
                $aging = [
                    'current' => $daysPastDue <= 0 ? $bill->balance : 0,
                    '0-30' => $daysPastDue > 0 && $daysPastDue <= 30 ? $bill->balance : 0,
                    '31-60' => $daysPastDue > 31 && $daysPastDue <= 60 ? $bill->balance : 0,
                    '61-90' => $daysPastDue > 61 && $daysPastDue <= 90 ? $bill->balance : 0,
                    'over_90' => $daysPastDue > 90 ? $bill->balance : 0,
                ];
                $bill->aging = $aging;
                $bill->days_past_due = $daysPastDue;
                return $bill;
            });

        $summary = [
            'current' => $bills->sum(fn($b) => $b->aging['current']),
            '0-30' => $bills->sum(fn($b) => $b->aging['0-30']),
            '31-60' => $bills->sum(fn($b) => $b->aging['31-60']),
            '61-90' => $bills->sum(fn($b) => $b->aging['61-90']),
            'over_90' => $bills->sum(fn($b) => $b->aging['over_90']),
            'total' => $bills->sum('balance'),
        ];

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportAgingReportExcel($bills, $summary, $asOfDate);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportAgingReportPdf($bills, $summary, $asOfDate);
        }

        return view('modules.accounting.accounts-payable.aging-report', compact('bills', 'summary', 'asOfDate'));
    }

    private function exportAgingReportPdf($bills, $summary, $asOfDate)
    {
        try {
            $data = [
                'bills' => $bills,
                'summary' => $summary,
                'asOfDate' => $asOfDate,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.ap-aging-report', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'AP_Aging_Report_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('A/P Aging PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    private function exportAgingReportExcel($bills, $summary, $asOfDate)
    {
        try {
            $filename = 'AP_Aging_Report_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($bills, $summary, $asOfDate) {
                $file = fopen('php://output', 'w');
                
                // Header
                fputcsv($file, ['Accounts Payable Aging Report']);
                fputcsv($file, ['As of Date: ' . \Carbon\Carbon::parse($asOfDate)->format('d M Y')]);
                fputcsv($file, ['Generated: ' . now()->format('d M Y H:i:s')]);
                fputcsv($file, []); // Empty row
                
                // Summary
                fputcsv($file, ['SUMMARY']);
                fputcsv($file, ['Current', '0-30 Days', '31-60 Days', '61-90 Days', 'Over 90 Days', 'Total']);
                fputcsv($file, [
                    number_format($summary['current'], 2),
                    number_format($summary['0-30'], 2),
                    number_format($summary['31-60'], 2),
                    number_format($summary['61-90'], 2),
                    number_format($summary['over_90'], 2),
                    number_format($summary['total'], 2)
                ]);
                fputcsv($file, []); // Empty row
                
                // Headers
                fputcsv($file, [
                    'Bill No', 'Vendor', 'Bill Date', 'Due Date', 'Days Past Due',
                    'Current', '0-30 Days', '31-60 Days', '61-90 Days', 'Over 90 Days', 'Total'
                ]);

                // Data rows
                foreach ($bills as $bill) {
                    fputcsv($file, [
                        $bill->bill_no ?? '',
                        $bill->vendor->name ?? 'N/A',
                        $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('Y-m-d') : '',
                        $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('Y-m-d') : '',
                        $bill->days_past_due ?? 0,
                        number_format($bill->aging['current'] ?? 0, 2),
                        number_format($bill->aging['0-30'] ?? 0, 2),
                        number_format($bill->aging['31-60'] ?? 0, 2),
                        number_format($bill->aging['61-90'] ?? 0, 2),
                        number_format($bill->aging['over_90'] ?? 0, 2),
                        number_format($bill->balance ?? 0, 2),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('A/P Aging Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Vendors PDF
     */
    private function exportVendorsPdf($vendors, $request)
    {
        try {
            $data = [
                'vendors' => $vendors,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
                'filters' => $request->only(['status', 'currency', 'outstanding', 'payment_terms', 'q']),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.vendors', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Vendors_List_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Vendors PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Vendors Excel
     */
    private function exportVendorsExcel($vendors, $request)
    {
        try {
            $filename = 'Vendors_List_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($vendors) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Code', 'Name', 'Contact Person', 'Email', 'Phone', 
                    'Currency', 'Credit Limit', 'Payment Terms', 
                    'Outstanding', 'Overdue', 'Status'
                ]);

                // Data rows
                foreach ($vendors as $vendor) {
                    fputcsv($file, [
                        $vendor->vendor_code ?? '',
                        $vendor->name ?? '',
                        $vendor->contact_person ?? '',
                        $vendor->email ?? '',
                        $vendor->phone ?? $vendor->mobile ?? '',
                        $vendor->currency ?? 'TZS',
                        number_format($vendor->credit_limit ?? 0, 2),
                        ($vendor->payment_terms_days ?? 30) . ' days',
                        number_format($vendor->total_outstanding ?? 0, 2),
                        number_format($vendor->overdue_amount ?? 0, 2),
                        $vendor->is_active ? 'Active' : 'Inactive',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Vendors Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }
}

